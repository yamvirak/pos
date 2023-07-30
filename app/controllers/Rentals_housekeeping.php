<?php defined('BASEPATH') or exit('No direct script access allowed');

class Rentals_housekeeping extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->cus->md('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('rentals', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('rentals_model');
        $this->load->model('db_model');
        $this->digital_upload_path = 'files/';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

    public function index($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
    {
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $this->data['billers'] = $this->site->getBillers();
        $this->data['payment_status'] = $payment_status;
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('rentals')));
        $meta = array('page_title' => lang('rentals'), 'bc' => $bc);
        $this->core_page('rentals/housekeepings', $meta, $this->data);
    }

    public function getRentalsHousekeepings($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
    {
        $this->load->library('datatables');
        $detail_link = anchor('rentals/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'));
        $create_sale = '';
        $deposit_link = '';
        $return_deposit_link = '';
        $view_deposit_link = '';
        if(($this->Admin || $this->Owner) || $this->GP['rentals-add']){

            $return_check_in = "<a href='#' class='po rentals-checked_in' title='<b>" . lang("return_check_in") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('rentals/checked_in/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"icon fa fa-sign-in tip\"></i> "
            . lang('return_check_in') . "</a>";
            $deposit_link = anchor('rentals/add_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_deposit'), ' class="rentals-deposit" data-toggle="modal" data-target="#myModal"');
            $return_deposit_link = anchor('rentals/add_return_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_return_deposit'), ' class="rentals-return_deposit" data-toggle="modal" data-target="#myModal"');
            $view_deposit_link = anchor('rentals/view_deposits/$1', '<i class="fa fa-money"></i> ' . lang('view_deposits'), ' class="rentals-view_deposit1" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        }
        $edit_link = '';
        if(($this->Admin || $this->Owner) || $this->GP['rentals-edit']){
            $edit_link = anchor('rentals_housekeeping/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_housekeepings'), ' class="rentals-edit" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        }
        $delete_link = '';
        if(($this->Admin || $this->Owner) || $this->GP['rentals-delete']){
            $delete_link = "<a href='#' class='po rentals-delete' title='<b>" . lang("delete_housekeepings") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('rentals_housekeeping/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_housekeepings') . "</a>";
        }
        $action = '<div class="text-center"><div class="btn-group text-left">'
                    . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                    . lang('actions') . ' <span class="caret"></span></button>
                        <ul class="dropdown-menu pull-right" role="menu">
                            <li>' . $detail_link . '</li>
                            <li>' . $edit_link . '</li>
                            <li>' . $delete_link . '</li>
                        </ul>
                    </div></div>';
        $this->datatables->select("
                    rentals.id as id,
                    rentals.date,
                    rentals.reference_no,
                    rentals.staff,
                    companies.phone as phone,
                    rental_rooms.room_type_name as room_type_name,
                    rental_rooms.name as room_name,
                    rentals.from_date,
                    rentals.to_date,
                    rentals.note,
                    rentals.status,
                    rentals.attachment,
                    rentals.room_id as room_id", false)
            ->from("rentals")
            ->join('rental_rooms', 'rental_rooms.id=rentals.room_id', 'left')
            ->join('companies', 'companies.id=rentals.staff_id', 'left')
            ->where('rentals.status','maintenance') 
            ->or_where('rentals.status','cleaned') 
            ->group_by('rentals.id')
            ->order_by('rentals.from_date DESC')
            ->add_column("Actions", $action, "id,room_id");
        
        if($payment_status){
            $this->datatables->where('DATE_SUB(from_date, INTERVAL 0 DAY) <=', date("Y-m-d"));
            $this->datatables->where('rentals.status','checked_in');
        }
        if($biller_id){
            $this->datatables->where("rentals.biller_id", $biller_id);
        }
        if ($warehouse_id) {
            $this->datatables->where('rentals.warehouse_id', $warehouse_id);
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->datatables->where('rentals.biller_id', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('rentals.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('rentals.created_by', $this->session->userdata('user_id'));
        }
            $this->datatables->unset_column("room_id,count");
        echo $this->datatables->generate();
    }
    public function add()
    {
        $this->cus->checkPermissions('edit');
        
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'is_unique[rental_rooms.name]');
        $this->form_validation->set_rules('floor', $this->lang->line("floor"), 'required');
        if ($this->form_validation->run() == true) {
            $status = $this->site->getHousekeepingStatusByID($this->input->post('status'));
            $biller_id = $this->input->post('biller');
            $rooms = $this->site->getRoomsByID($this->input->post('room'));
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ren',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['rentals-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $staff_id = $this->input->post('staff');
            $floor_id = $this->input->post('floor');
            $room_id = $this->input->post('room');
            $staff_details = $this->site->getCompanyByID($staff_id);
            $staff = $staff_details->name != '-'  ? $staff_details->name : $staff_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $frequency = $this->input->post('frequency');
            $from_date = $this->cus->fld($this->input->post('from_date'));
            $to_date = $this->cus->fld($this->input->post('to_date'));
            $note = $this->input->post('note');
            $staff_note = $this->cus->clear_tags($this->input->post('staff_note'));
            $data = array(
                'date' => $date,
                'reference_no' => $reference,
                'staff_id' => $staff_id,
                'staff' => $staff,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'floor_id' => $floor_id,
                'room_id' => $this->input->post('room'),
                'room_name' => $rooms->name,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'note' => $note,
                'status_id' => $this->input->post('status'),
                'status' => $status->code,
                'created_by' => $this->session->userdata('user_id'),
            );
        }else if($this->input->post('add_housekeepings')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->rentals_model->addHousekeepings($data)) {
            $this->session->set_flashdata('message', lang("housekeepings_added"));
            redirect("rentals_housekeeping/index");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] =  $this->site->getBillers();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['staffs'] = $this->site->getAllStaffs();
            $this->data['rooms'] = $this->rentals_model->getAllRooms();
            $this->data['floors'] = $this->rentals_model->getRoomFloors();
            $this->data['housekeeping_types'] = $this->rentals_model->getAllHousekeepingTypes();
            $this->data['rtnumber'] = '';
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'rentals/add_housekeepings', $this->data);
        }
    }
    
    public function edit($id = NULL)
    {
       $this->cus->checkPermissions('edit');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->rentals_model->getRentalByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
        if ($this->form_validation->run() == true) {
            $status = $this->site->getHousekeepingStatusByID($this->input->post('status'));
            $rooms = $this->site->getRoomsByID($this->input->post('room'));
            $biller_id = $this->input->post('biller');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rental',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['rentals-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $staff_id = $this->input->post('staff');
            $floor_id = $this->input->post('floor');
    
            $staff_details = $this->site->getCompanyByID($staff_id);
            $staff = $staff_details->name != '-'  ? $staff_details->name : $staff_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->name != '-' ? $biller_details->name : $biller_details->name;
            $room_id = $this->input->post('room');
            $frequency = $this->input->post('frequency');
            $from_date = $this->cus->fld($this->input->post('from_date'));
            $to_date = $this->cus->fld($this->input->post('to_date'));
            $note = $this->input->post('note');

            $data = array(
                'date' => $date,
                'reference_no' => $reference,
                'staff_id' => $staff_id,
                'staff' => $staff,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'floor_id' => $floor_id,
                'room_id' => $this->input->post('room'),
                'room_name' => $rooms->name,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'note' => $note,
                'status_id' => $this->input->post('status'),
                'status' => $status->code,
            );

        }else if($this->input->post('edit_room')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->rentals_model->updateHousekeepings($id, $data)) {
            $this->session->set_flashdata('message', lang("housekeepings_updated"));
            redirect("rentals_housekeeping/index");
        } else {
            $this->data['id'] = $id;
            $this->data['inv'] = $this->rentals_model->getRentalByID($id);
            $this->data['floors'] = $this->rentals_model->getRoomFloors();
            $this->data['rooms'] = $this->rentals_model->getAllRooms();
            $this->data['staffs'] = $this->site->getAllStaffs();
            $this->data['housekeeping_types'] = $this->rentals_model->getAllHousekeepingTypes();
            $this->data['billers'] = $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['frequencies'] = $this->rentals_model->getFrequencies();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'rentals/edit_housekeepings', $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->cus->checkPermissions("delete");
        if ($this->rentals_model->deleteRentalHousekeeping($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("housekeepings_deleted"); exit;
            }
        }
        $this->session->set_flashdata('message', lang("housekeepings_deleted"));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function index_housekeeping($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('rooms')));
        $meta = array('page_title' => lang('rooms'), 'bc' => $bc);

        $this->data['todayCheckIn'] = $this->db_model->getCheckIns(true,false,false);
        $this->data['yesterdayCheckIn'] = $this->db_model->getCheckIns(false,true,false);
        $this->data['todayCheckOut'] = $this->db_model->getCheckOuts(true,false,false);
        $this->data['yesterdayCheckOut'] = $this->db_model->getCheckOuts(false,true,false);
        $this->data['todayReservation'] = $this->db_model->getReservations(true,false,false);
        $this->data['yesterdayReservation'] = $this->db_model->getReservations(false,true,false);


        $this->data['todayOccupied'] = $this->db_model->getAllOccuppys(true,false,false);
        $this->data['todayAvailable'] = $this->db_model->getAllAvailables(true,false,false);
        $this->data['todayMaintenances'] = $this->db_model->getAllMaintenances(true,false,false);

        $this->data['totalrooms'] = $this->rentals_model->getTotalRooms();
        $this->data['TotalRoomTypes'] = $this->rentals_model->getTotalRoomsTypes();
        $this->data['TotalFloors'] = $this->rentals_model->getTotalFloors();
        $this->data['TotalServices'] = $this->rentals_model->getTotalServices();
        $this->core_page('rentals/index_housekeeping', $meta, $this->data);
    }
	public function getHousekeepingRooms($warehouse_id = null, $biller_id = NULL)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
					rental_rooms.id as id,
                    rental_rooms.room_type_name,
                    rental_floors.floor,
					rental_rooms.name,
					rental_rooms.availability,
					rental_rooms.housekeeping_status,
					rental_housekeepings.staff,
					rental_rooms.status
					")
            ->from("rental_rooms")
			->join("rental_floors","rental_floors.id = rental_rooms.floor","left")
            ->join("rental_housekeepings","rental_housekeepings.room_id = rental_rooms.id","left")
            ->group_by('rental_rooms.id')
            ->order_by('rental_rooms.id ASC')
			->add_column("Actions", "<center>
				<a class=\"tip\" title='" . $this->lang->line("view") . "' href='" . site_url('#') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-eye\"></i></a>");

        if($biller_id){
            $this->datatables->where("rental_rooms.biller_id", $biller_id);
        }
        if ($warehouse_id) {
            $this->datatables->where('rental_rooms.warehouse_id', $warehouse_id);
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->datatables->where('rental_rooms.biller_id', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('rental_rooms.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
          
        echo $this->datatables->generate();
    }

    public function view_rooms($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('rooms')));
        $meta = array('page_title' => lang('rooms'), 'bc' => $bc);
        $this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['floors'] = $this->rentals_model->getRoomFloors();

        $this->data['todayCheckIn'] = $this->db_model->getCheckIns(true,false,false);
        $this->data['yesterdayCheckIn'] = $this->db_model->getCheckIns(false,true,false);
        $this->data['todayCheckOut'] = $this->db_model->getCheckOuts(true,false,false);
        $this->data['yesterdayCheckOut'] = $this->db_model->getCheckOuts(false,true,false);
        $this->data['todayReservation'] = $this->db_model->getReservations(true,false,false);
        $this->data['yesterdayReservation'] = $this->db_model->getReservations(false,true,false);


        $this->data['todayOccupied'] = $this->db_model->getAllOccuppys(true,false,false);
        $this->data['todayAvailable'] = $this->db_model->getAllAvailables(true,false,false);
        $this->data['todayMaintenances'] = $this->db_model->getAllMaintenances(true,false,false);

        $this->data['totalrooms'] = $this->rentals_model->getTotalRooms();
        $this->data['TotalRoomTypes'] = $this->rentals_model->getTotalRoomsTypes();
        $this->data['TotalFloors'] = $this->rentals_model->getTotalFloors();
        $this->data['TotalServices'] = $this->rentals_model->getTotalServices();
        $this->core_page('rentals/housekeeping_view_room', $meta, $this->data);
    }
    public function getViewRooms($warehouse_id = null, $biller_id = NULL)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    rental_rooms.id as id,
                    rental_rooms.room_type_name,
                    rental_floors.floor,
                    rental_rooms.name,
                    rental_rooms.availability,
                    rental_rooms.housekeeping_status,
                    rental_housekeepings.staff,
                    rental_rooms.status
                    ")
            ->from("rental_rooms")
            ->join("rental_floors","rental_floors.id = rental_rooms.floor","left")
            ->join("rental_housekeepings","rental_housekeepings.room_id = rental_rooms.id","left")
            ->group_by('rental_rooms.id')
            ->order_by('rental_rooms.id ASC')
            ->add_column("Actions", "<center>
                <a class=\"tip\" title='" . $this->lang->line("view") . "' href='" . site_url('#') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-eye\"></i></a>");

        if($biller_id){
            $this->datatables->where("rental_rooms.biller_id", $biller_id);
        }
        if ($warehouse_id) {
            $this->datatables->where('rental_rooms.warehouse_id', $warehouse_id);
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->datatables->where('rental_rooms.biller_id', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('rental_rooms.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
          
        echo $this->datatables->generate();
    }

    public function housekeepings()
    {
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('room_type')));
        $meta = array('page_title' => lang('room_type'), 'bc' => $bc);
        $this->core_page('rentals/housekeeping_list', $meta, $this->data);
    }

    public function getHousekeeping1()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    rental_room_types.id as id,
                    rental_room_types.name")
            ->from("rental_room_types")
            ->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_room_type") . "' href='" . site_url('rentals_configuration/edit_room_type/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_room_type") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('rentals_configuration/delete_room_type/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

    public function add_room_housekeeping()
    {
        $this->cus->checkPermissions('rooms');
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'is_unique[rental_rooms.name]');
		$this->form_validation->set_rules('floor', $this->lang->line("floor"), 'required');
        if ($this->form_validation->run() == true) {
            $room_type = $this->site->getRoomTypesByID($this->input->post('room_type'));
            $data = array(
				'floor' => $this->input->post('floor'),
                'name' => $this->input->post('name'),
                'room_type_id' => $this->input->post('room_type'),
                'room_type_name' => $room_type->name,
                'price' => $this->input->post('price'),
				'product_id' => $this->input->post('product_id'),
                'biller_id' => $this->input->post('biller'),
				'warehouse_id' => $this->input->post('warehouse'),
                'bed_number_id' => $this->input->post('bed_number'),
				'status' => 'active'
            );
        }else if($this->input->post('add_room')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->rentals_model->addRoom($data)) {
            $this->session->set_flashdata('message', lang("room_added"));
            redirect("rentals_configuration/rooms");
        } else {
			$this->data['products'] = $this->rentals_model->getRoomItems();
			$this->data['floors'] = $this->rentals_model->getAllFloors();
            $this->data['billers'] =  $this->site->getBillers();
            $this->data['room_types'] = $this->rentals_model->getAllRoomTypes();
            $this->data['bed_numbers'] = $this->rentals_model->getAllBedNumbers();
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'rentals/add_room_housekeeping', $this->data);
        }
    }

    public function ajax_rooms()
    {
        $warehouse = $this->input->get("warehouse", true);
        $floor = $this->input->get("floor",true);
        $html = '';
        $rooms = $this->rentals_model->getAllRooms($floor, $warehouse);
        if($rooms){
            foreach($rooms as $room){
                $busy_room = $this->rentals_model->getRoomStatus($room->id);
                if($busy_room && $busy_room->status=='checked_in'){
                    $html .= '<a href="'.site_url('rentals/modal_view/'.$busy_room->id).'" class="" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal">
                                <div class="bg_green">
                                    <div class="ellipsis">
                                        <button class="ellipsis-btn">&#8942;</button>
                                    </div>
                                    <div class="house_status">
                                        '.lang('checked_in').'
                                    </div>
                                    <div class="select"><?=lang("Clean")?> </div>
                                    <div class="num_room_green">
                                        <h1 class="num_room_name">'.$room->name.'</h1>
                                        <h4>'.$room->room_type_name.'</h4>
                                    </div>
                                    <div class="date"><i class="fa fa-calendar-plus-o" aria-hidden="true"></i>'.$this->cus->hrsd($busy_room->from_date).'</div>
                                    <div><i class="fa fa-sign-out"></i>'.$this->cus->hrsd($busy_room->to_date).'</div>
                                    <div class="box_footer_checked_in room_rental">
                                         <span class="icon_bed_available">'.$room->bed_number_name.'</span>
                                    </div>
                                </div>
                              </a>';
                }else if($busy_room && $busy_room->status=='reservation'){
                    $html .= '<a href="'.site_url('rentals/modal_view/'.$busy_room->id).'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal">
                                 <div class="bg_orange">
                                    <div class="ellipsis">
                                        <button class="ellipsis-btn">&#8942;</button>
                                    </div>
                                    <div class="house_status">
                                        '.lang('reservation').'
                                    </div>
                                    <div class="select"><?=lang("Clean")?> </div>
                                    <div class="num_room_reservation">
                                        <h1 class="num_room_name">'.$room->name.'</h1>
                                        <h4>'.$room->room_type_name.'</h4>
                                    </div>
                                    <div class="date"><i class="fa fa-calendar-plus-o" aria-hidden="true"></i>'.$this->cus->hrsd($busy_room->from_date).'</div>
                                    <div><i class="fa fa-sign-out"></i>'.$this->cus->hrsd($busy_room->to_date).'</div>
                                    <div class="box_footer_reservation room_rental">
                                         <span class="icon_bed_available">'.$room->bed_number_name.'</span>
                                    </div>
                                </div>
                              </a>';
                }else if($busy_room && $busy_room->status=='maintenance'){
                    $html .= '<a href="'.site_url('rentals/modal_view/'.$busy_room->id).'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal">
                                 <div class="bg_red">
                                    <div class="ellipsis">
                                        <button class="ellipsis-btn">&#8942;</button>
                                    </div>
                                    <div class="house_status">
                                     '.lang('maintenance').'
                                        
                                    </div>
                                    <div class="select"><?=lang("Clean")?> </div>
                                    <div class="num_room_red">
                                        <h1 class="num_room_name">'.$room->name.'</h1>
                                        <h4>'.$room->room_type_name.'</h4>
                                    </div>
                                    <div class="date"><i class="fa fa-calendar-plus-o" aria-hidden="true"></i>'.$this->cus->hrsd($busy_room->from_date).'</div>
                                    <div><i class="fa fa-sign-out"></i>'.$this->cus->hrsd($busy_room->to_date).'</div>
                                    <div class="box_footer_maintanance room_rental">
                                         <span class="icon_bed_available">'.$room->bed_number_name.'</span>
                                    </div>
                                </div>
                              </a>';
                }else{
                    $html .= '<a href="'.site_url('rentals/add').'">
                                <div class="bg_blue">
                                    <div class="ellipsis">
                                        <button class="ellipsis-btn">&#8942;</button>
                                    </div>
                                    <div class="house_status">
                                         '.lang('room_free').'
                                    </div>
                                    <div class="select"><?=lang("Clean")?> </div>
                                    <div class="num_room_available">
                                        <h1 class="num_room_name">'.$room->name.'</h1>
                                        <h4>'.$room->room_type_name.'</h4>
                                    </div>
                                    <div class="date"><i class="fa fa-calendar-plus-o" aria-hidden="true"></i>N/A</div>
                                    <div><i class="fa fa-sign-out"></i>N/A</div>
                                    <div class="box_footer_list room_rental">
                                         <span class="icon_bed_available">'.$room->bed_number_name.'</span>
                                    </div>
                                </div>
                              </a>';
                }
            }
        }
        echo json_encode($html);
    }

    public function update_status($id)
    {
        $this->form_validation->set_rules('status', lang("status"), 'required');
        $rentalRoom = $this->rentals_model->getRentalRoomsByID($id);
        if($rental->status == 'checked_out'){
            $this->session->set_flashdata('error', lang('rental_already_checked_out'));
            $this->cus->md();
        }
        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $period = $this->input->post('period');
            $ckidate = $this->cus->fld($this->input->post('ckidate'));
            $ckodate = $this->cus->fld($this->input->post('ckodate'));
            $fdrdate = $this->cus->fld($this->input->post('from_date'));
            $note = $this->input->post('note');
            $updated_by = $this->session->userdata('user_id');
            $updated_at = date('Y-m-d H:i:s');
            if($status == 'checked_out'){
                redirect('sales/add?rental_id='.$id."&checked_out_date=".$this->input->post('ckodate'));
            }
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'rentals');
        }
        if ($this->form_validation->run() == true && $this->rentals_model->updateStatusRooms($id, $status, $fdrdate, $ckidate, $ckodate,$note,$updated_by,$updated_at)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'rentals');
        } else {
            $this->data['room'] = $rentalRoom;
            $this->data['customer'] = $this->site->getCompanyByID($rentalRoom->customer_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'rentals/update_status_housekeeping', $this->data);
        }
    }

}
