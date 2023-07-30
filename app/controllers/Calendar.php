<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->cus->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->load->library('form_validation');
        $this->load->model('calendar_model');
    }

    public function index()
    {
        $this->data['cal_lang'] = $this->get_cal_lang();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('calendar')));
        $meta = array('page_title' => lang('calendar'), 'bc' => $bc);
        $this->core_page('calendar', $meta, $this->data);
    }

    public function get_events()
    {
        $cal_lang = $this->get_cal_lang();
        $this->load->library('fc', array('lang' => $cal_lang));

        if (!isset($_GET['start']) || !isset($_GET['end'])) {
            die("Please provide a date range.");
        }

        if ($cal_lang == 'ar') {
            $start = $this->fc->convert2($this->input->get('start', true));
            $end = $this->fc->convert2($this->input->get('end', true));
        } else {
            $start = $this->input->get('start', true); 
            $end = $this->input->get('end', true); 
        }

        $input_arrays = $this->calendar_model->getEvents($start, $end);
        $start = $this->fc->parseDateTime($start);
        $end = $this->fc->parseDateTime($end);
        $output_arrays = array();
        foreach ($input_arrays as $array) {
            $this->fc->load_event($array);
            if ($this->fc->isWithinDayRange($start, $end)) {
                $output_arrays[] = $this->fc->toArray();
            }
        }
        $this->cus->send_json($output_arrays);
    }

    public function add_event()
    {

        $this->form_validation->set_rules('title', lang("title"), 'trim|required');
        $this->form_validation->set_rules('start', lang("start"), 'required');

        if ($this->form_validation->run() == true) {
            $data = array(
                'title' => $this->input->post('title'),
                'start' => $this->cus->fld($this->input->post('start')),
                'end' => $this->input->post('end') ? $this->cus->fld($this->input->post('end')) : NULL,
                'description' => $this->input->post('description'),
                'color' => $this->input->post('color') ? $this->input->post('color') : '#000000',
                'user_id' => $this->session->userdata('user_id'),
				'holiday' => $this->input->post("holiday"),
                );

            if ($this->calendar_model->addEvent($data)) {
                $res = array('error' => 0, 'msg' => lang('event_added'));
                $this->cus->send_json($res);
            } else {
                $res = array('error' => 1, 'msg' => lang('action_failed'));
                $this->cus->send_json($res);
            }
        }

    }

    public function update_event()
    {

        $this->form_validation->set_rules('title', lang("title"), 'trim|required');
        $this->form_validation->set_rules('start', lang("start"), 'required');

        if ($this->form_validation->run() == true) {
            $id = $this->input->post('id');
            if($event = $this->calendar_model->getEventByID($id)) {
                if(!$this->Owner && $event->user_id != $this->session->userdata('user_id')) {
                    $res = array('error' => 1, 'msg' => lang('access_denied'));
                    $this->cus->send_json($res);
                }
            }
            $data = array(
                'title' => $this->input->post('title'),
                'start' => $this->cus->fld($this->input->post('start')),
                'end' => $this->input->post('end') ? $this->cus->fld($this->input->post('end')) : NULL,
                'description' => $this->input->post('description'),
                'color' => $this->input->post('color') ? $this->input->post('color') : '#000000',
                'user_id' => $this->session->userdata('user_id'),
				'holiday' => $this->input->post("holiday"),
                );

            if ($this->calendar_model->updateEvent($id, $data)) {
                $res = array('error' => 0, 'msg' => lang('event_updated'));
                $this->cus->send_json($res);
            } else {
                $res = array('error' => 1, 'msg' => lang('action_failed'));
                $this->cus->send_json($res);
            }
        }

    }

    public function delete_event($id)
    {
        if($this->input->is_ajax_request()) {
            if($event = $this->calendar_model->getEventByID($id)) {
                if(!$this->Owner && $event->user_id != $this->session->userdata('user_id')) {
                    $res = array('error' => 1, 'msg' => lang('access_denied'));
                    $this->cus->send_json($res);
                }
                $this->db->delete('calendar', array('id' => $id));
                $res = array('error' => 0, 'msg' => lang('event_deleted'));
                $this->cus->send_json($res);
            }
        }
    }

    public function get_cal_lang() 
	{
        switch ($this->Settings->user_language) {
            case 'arabic':
                $cal_lang = 'ar-ma';
                break;
            case 'spanish':
            $cal_lang = 'es';
            break;
            case 'german':
            $cal_lang = 'de';
            break;
            case 'thai':
            $cal_lang = 'th';
            break;
            case 'vietnamese':
            $cal_lang = 'vi';
            break;
            case 'italian':
            $cal_lang = 'it';
            break;
            case 'simplified-chinese':
            $cal_lang = 'zh-tw';
            break;
            case 'traditional-chinese':
            $cal_lang = 'zh-cn';
            break;
            case 'turkish':
            $cal_lang = 'tr';
            break;
            default:
            $cal_lang = 'en';
            break;
        }
        return $cal_lang;
    }

	public function import_csv()
	{
		$this->cus->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
			
			if (isset($_FILES["userfile"])) {
				
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<= $highestRow; $row++){
						$title = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$from_date = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$to_date = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						$description = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						
						$final[] = array(
							  'title'   => $title,
							  'from_date'   => $from_date,
							  'to_date'   => $to_date,
							  'description'   => $description,
						  );
						  
					}
					
				}
				
				$rw = 2;
                foreach ($final as $csv_pr) {
					$pr_title[] = trim($csv_pr['title']);
					$pr_from_date[] = trim($csv_pr['from_date']);
					$pr_to_date[] = trim($csv_pr['to_date']);
					$pr_description[] = trim($csv_pr['description']);
				}
				$ikeys = array('title','start','end','description');
				$items = array();
				foreach (array_map(null,$pr_title, $pr_from_date, $pr_to_date, $pr_description) as $ikey => $value) {
					$items[] = array_combine($ikeys, $value);
				}
			}
		}
		
		if ($this->form_validation->run() == true && $this->calendar_model->addCalendar($items)) {
			
            $this->session->set_flashdata('message', sprintf(lang("calendar_added")));
            redirect('calendar/calendar_lists');
			
        } else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['userfile'] = array(
												'name' => 'userfile',
												'id' => 'userfile',
												'type' => 'text',
												'value' => $this->form_validation->set_value('userfile')
											);
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('installments'), 'page' => lang('calendar')), array('link' => '#', 'page' => lang('import_csv')));
			$meta = array('page_title' => lang('import_csv'), 'bc' => $bc);
			$this->core_page('calendar_import', $meta, $this->data);
		}
	}

	public function calendar_lists()
	{
		$this->data['users'] = $this->site->getAllUsers();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('calendar'), 'page' => lang('calendar')), array('link' => '#', 'page' => lang('holidays')));
        $meta = array('page_title' => lang('holidays'), 'bc' => $bc);
        $this->core_page('calendar_lists', $meta, $this->data);
	}

	public function getCalendarLists()
	{
		$delete_link = "<a href='#' class='po delete-installment_item' title='<b>" . lang("delete_calendar") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('calendar/delete_calendar/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_calendar') . "</a>";
		
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$delete_link.'</li>
					</ul>
				</div></div>';
				
        $this->load->library('datatables');
        $this->datatables
            ->select("
					cus_calendar.id as id,
					title,
					description,
					date(start) as start,
					date(end) as end, 
					concat(cus_users.last_name,'',cus_users.first_name) as user")
            ->from('cus_calendar')
			->join('users','users.id=user_id','left')
			->where("cus_calendar.holiday",1);
			
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	
	public function delete_calendar($id = NULL)
	{
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->calendar_model->deleteCalendar($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("calendar_deleted");
				die();
            }
            $this->session->set_flashdata('message', lang('calendar_deleted'));
            redirect('welcome');
        }
	}
	
	public function calendar_actions()
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
			
			 if (!empty($_POST['val'])) {
				 
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->calendar_model->deleteCalendar($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("calendar_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
					
                }elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('calendar');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('title'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('description'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('start_date'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('end_date'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('created_by'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						$calendar = $this->calendar_model->getEventByID($id);
						$user = $this->site->getUser($calendar->user_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $calendar->title);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $calendar->description);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $calendar->start);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $calendar->end);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, ($user->last_name."".$user->first_name));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
					
                    $filename = 'calendars_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_calendar_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
		}
	}
	
	
	
	
	
	
}
