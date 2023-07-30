<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Suppliers extends MY_Controller
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
        $this->lang->load('suppliers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('companies_model');
		$this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
		$this->digital_upload_path = 'files/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '102400';
    }

    function index($action = NULL)
    {
        $this->cus->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('suppliers')));
        $meta = array('page_title' => lang('suppliers'), 'bc' => $bc);
        $this->core_page('suppliers/index', $meta, $this->data);
    }

    function getSuppliers()
    {
        $this->cus->checkPermissions('index');
        $this->load->library('datatables');
		$product_link = anchor('products?supplier=$1', '<i class="fa fa-list"></i> ' . lang('list_products'), ' class="list_products"');
		$add_deposit_link = anchor('suppliers/add_deposit/$1', '<i class="fa fa-plus"></i> ' . lang('add_deposit'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$deposit_link = anchor('suppliers/deposits/$1', '<i class="fa fa-money"></i> ' . lang('list_deposits'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_link = anchor('suppliers/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_supplier'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$delete_link = "<a href='#' class='delete_supplier po' title='<b>" . $this->lang->line("delete_supplier") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('suppliers/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_supplier') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $add_deposit_link . '</li>
						<li>' . $deposit_link . '</li>
						<li>' . $product_link . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		
        $this->datatables
            ->select("id as id, code, company, name, email, phone, city, country, deposit_amount")
            ->from("companies")
            ->where('group_name', 'supplier');
         $this->datatables->add_column("Actions", $action, "id");    
        echo $this->datatables->generate();
    }

    function view($id = NULL)
    {
        $this->cus->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['supplier'] = $this->companies_model->getCompanyByID($id);
        $this->load->view($this->theme.'suppliers/view',$this->data);
    }

    function add()
    {
        $this->cus->checkPermissions(false, true);

		$this->form_validation->set_rules('code', $this->lang->line("code"), 'is_unique[companies.code]');
        $this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[companies.email]');

        if ($this->form_validation->run('companies/add') == true) {

            $data = array(
				'code' => $this->input->post('code'),
				'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => '4',
                'group_name' => 'supplier',
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
            );
        } elseif ($this->input->post('add_supplier')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $sid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', $this->lang->line("supplier_added"));
			$this->site->updateCompanyReference('supplier');
            $ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : NULL;
            redirect($ref[0] . '?supplier=' . $sid);
        } else {
			$this->data['suppliers'] = $this->site->getCompanyReference('supplier');
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/add', $this->data);
        }
    }

    function edit($id = NULL)
    {
        $this->cus->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('code', lang("email_address"), 'is_unique[companies.email]');
        }
		
		if ($this->input->post('code') != $company_details->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[companies.code]');
        }

        if ($this->form_validation->run('companies/add') == true) {
            $data = array(
				'code' => $this->input->post('code'),
				'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => '4',
                'group_name' => 'supplier',
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
            );
        } elseif ($this->input->post('edit_supplier')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line("supplier_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['supplier'] = $company_details;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/edit', $this->data);
        }
    }

    function users($company_id = NULL)
    {
        $this->cus->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }


        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->data['users'] = $this->companies_model->getCompanyUsers($company_id);
        $this->load->view($this->theme . 'suppliers/users', $this->data);

    }

    function add_user($company_id = NULL)
    {
        $this->cus->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('confirm_password'), 'required');

        if ($this->form_validation->run('companies/add_user') == true) {
            $active = $this->input->post('status');
            $notify = $this->input->post('notify');
            list($username, $domain) = explode("@", $this->input->post('email'));
            $email = strtolower($this->input->post('email'));
            $password = $this->input->post('password');
            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'phone' => $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
                'company_id' => $company->id,
                'company' => $company->company,
                'group_id' => 3
            );
            $this->load->library('ion_auth');
        } elseif ($this->input->post('add_user')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', $this->lang->line("user_added"));
            redirect("suppliers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'suppliers/add_user', $this->data);
        }
    }

    function import_csv()
    {
        $this->cus->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', $this->lang->line("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('warning', $this->lang->line("disabled_in_demo"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if (isset($_FILES["excel_file"]))  {
				$this->load->library('excel');
				$path = $_FILES["excel_file"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++)
					{
					 $code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
					 $company = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
					 $name = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
					 $email = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
					 $phone = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
					 $address = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
					 $city = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
					 $state = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
					 $postal_code = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
					 $country = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
					 $vat_no = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
					 $cf1s = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
					 $cf2s = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
					 $cf3s = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
					 $cf4s = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
					 $cf5s = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
					 $cf6s = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
						if(empty($code)){
							$this->session->set_flashdata('error', lang("check_supplier_code") . " (" . $code . "). " . lang("supplier_already_exist") . " (" . lang("line_no") . " " . $row . ")");
							redirect("suppliers");
						}
						if(empty($phone)){
							$this->session->set_flashdata('error', lang("check_supplier_phone") . " (" . $phone . "). " . lang("supplier_already_exist") . " (" . lang("line_no") . " " . $row . ")");
							redirect("suppliers");
						}
						
						$data[] = array(
						  'code'  => $code,
						  'company'  => $company,
						  'name'   => $name,
						  'email'    => $email,
						  'phone'  => $phone,
						  'address'   => $address,
						  'city'   => $city,
						  'state'   => $state,
						  'postal_code'   => $postal_code,
						  'country'   => $country,
						  'vat_no'   => $vat_no,
						  'cf1'   => $cf1s,
						  'cf2'   => $cf2s,
						  'cf3'   => $cf3s,
						  'cf4'   => $cf4s,
						  'cf5'   => $cf5s,
						  'cf6'   => $cf6s,
						  'group_id'   => 4,
						  'group_name'   => 'supplier',
						 );
					}
				}
				
				$rw = 2;
				$checkCode = false;
				$checkPhone = false;
                foreach ($data as $csv_com) {
                    if(!$this->companies_model->getCompanyByCodeGroupName(trim($csv_com['code'],'supplier'))) {
						if ($csv_com['email'] && $this->companies_model->getCompanyByEmail($csv_com['email'])) {
							$this->session->set_flashdata('error', lang("check_customer_email") . " (" . $email . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
							redirect("suppliers");
						}
						if(isset($checkCode[trim($csv_com['code'])]) && $checkCode[trim($csv_com['code'])]){
							$this->session->set_flashdata('error', lang("check_supplier_code") . " (" . $csv_com['code'] . "). " . lang("supplier_duplicate_exist") . " (" . lang("line_no") . " " . $rw . ")");
							redirect("suppliers");
						}
						if(isset($checkPhone[trim($csv_com['phone'])]) && $checkPhone[trim($csv_com['phone'])]){
							$this->session->set_flashdata('error', lang("check_supplier_phone") . " (" . $csv_com['phone'] . "). " . lang("supplier_duplicate_exist") . " (" . lang("line_no") . " " . $rw . ")");
							redirect("suppliers");
						}
						if ($this->companies_model->getCompanyByPhone($csv_com['phone'],'supplier')) {
							$this->session->set_flashdata('error', lang("check_supplier_phone") . " (" . $csv_com['phone'] . "). " . lang("supplier_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
							redirect("suppliers");
						}
							$checkCode[trim($csv_com['code'])] = true;
							$checkPhone[trim($csv_com['phone'])] = true;
							
							$company_code[] = trim($csv_com['code']);
							$company_company[] = trim($csv_com['company']);
							$company_name[] = trim($csv_com['name']);
							$company_email[] = trim($csv_com['email']);
							$company_phone[] = trim($csv_com['phone']);
							$company_address[] = trim($csv_com['address']);
							$company_city[] = trim($csv_com['city']);
							$company_state[] = trim($csv_com['state']);
							$company_postal_code[] = trim($csv_com['postal_code']);
							$company_country[] = trim($csv_com['country']);
							$company_vat_no[] = trim($csv_com['vat_no']);
							$cf1[] = trim($csv_com['cf1']);
							$cf2[] = trim($csv_com['cf2']);
							$cf3[] = trim($csv_com['cf3']);
							$cf4[] = trim($csv_com['cf4']);
							$cf5[] = trim($csv_com['cf5']);
							$cf6[] = trim($csv_com['cf6']);
							$group_id[] = 4;
							$group_name[] = 'supplier';
						
                    }else{
						$this->session->set_flashdata('error', lang("check_supplier_code") . " (" . $csv_com['code'] . "). " . lang("supplier_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
						redirect("suppliers");
                    }

                    $rw++;
                }
				$ikeys = array('code', 'company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6', 'group_id','group_name');

				$companies = array();
				foreach (array_map(null, $company_code, $company_company, $company_name, $company_email, $company_phone,$company_address, $company_city, $company_state, $company_postal_code, $company_country, $company_vat_no, $cf1, $cf2, $cf3, $cf4, $cf5, $cf6, $group_id,$group_name) as $ikey => $value) {
					$companies[] = array_combine($ikeys, $value);
					
				}
            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('suppliers');
        }

        if ($this->form_validation->run() == true && !empty($companies)) {
            if ($this->companies_model->addCompanies($companies)) {
                $this->session->set_flashdata('message', $this->lang->line("suppliers_added"));
                redirect('suppliers');
            }
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/import', $this->data);
        }
    }

    function delete($id = NULL)
    {
        $this->cus->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->companies_model->deleteSupplier($id)) {
            echo $this->lang->line("supplier_deleted");
        } else {
            $this->session->set_flashdata('warning', lang('supplier_x_deleted_have_purchases'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    function suggestions($term = NULL, $limit = NULL)
    {
        // $this->cus->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->companies_model->getSupplierSuggestions($term, $limit);
        $this->cus->send_json($rows);
    }

    function getSupplier($id = NULL)
    {
        $row = $this->companies_model->getCompanyByID($id);
        $this->cus->send_json(array(array('id' => $row->id, 'text' => $row->company)));
    }

    function supplier_actions()
    {
        if (!$this->Owner && $this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->companies_model->deleteSupplier($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("suppliers_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('supplier'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('city'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('state'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('postal_code'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('country'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('vat_no'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('scf1'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('scf2'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('scf3'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('scf4'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('scf5'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('scf6'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $supplier = $this->site->getCompanyByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $supplier->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $supplier->company);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $supplier->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $supplier->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $supplier->phone);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $supplier->address);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $supplier->city);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $supplier->state);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $supplier->postal_code);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $supplier->country);
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $supplier->vat_no);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $supplier->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $supplier->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $supplier->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $supplier->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, $supplier->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $supplier->cf6);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'suppliers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function deposit_note($id = null)
    {
        $this->cus->checkPermissions('deposits', true);
        $deposit = $this->companies_model->getDepositByID($id);
        $this->data['supplier'] = $this->companies_model->getCompanyByID($deposit->company_id);
        $this->data['deposit'] = $deposit;
        $this->data['page_title'] = $this->lang->line("deposit_note");
        $this->load->view($this->theme . 'suppliers/deposit_note', $this->data);
    }
	
	function deposits($company_id = NULL)
    {
        $this->cus->checkPermissions(false, true);
        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->load->view($this->theme . 'suppliers/deposits', $this->data);

    }

    public function get_deposits($company_id = NULL)
    {
        $this->cus->checkPermissions('deposits');
        $this->load->library('datatables');
        $this->datatables
            ->select("deposits.id as id, date, amount, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('deposits').".paid_by) as paid_by, CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by,deposits.attachment", false)
            ->from("deposits")
			->join("cash_accounts","cash_accounts.id = deposits.paid_by","left")
            ->join('users', 'users.id=deposits.created_by', 'left')
            ->where($this->db->dbprefix('deposits').'.company_id', $company_id)
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("deposit_note") . "' href='" . site_url('suppliers/deposit_note/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-file-text-o\"></i></a> <a class=\"tip\" title='" . lang("edit_deposit") . "' href='" . site_url('suppliers/edit_deposit/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_deposit") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('suppliers/delete_deposit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
        ->unset_column('id');
        echo $this->datatables->generate();
    }
	
	public function delete_deposit($id)
    {
        $this->cus->checkPermissions(NULL, TRUE);
		if($id && $id > 0){
			if ($this->companies_model->deleteDeposit($id)) {
				$this->site->deleteAccTran('SupplierDeposit',$id);
				echo lang("deposit_deleted");
			}
		}
        
    }

	public function add_deposit($company_id = NULL)
    {
        $this->cus->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        
        if ($this->form_validation->run() == true) {
            $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
            $data = array(
                'date' => $date,
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'company_id' => $company->id,
                'created_by' => $this->session->userdata('user_id'),
				'biller_id' => $this->input->post('biller'),
				'project_id' => $this->input->post('project'),
				'account' => $paying_from,
            );
			if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$depositAcc = $this->site->getAccountSettingByBiller($this->input->post('biller'));
					$accTranDeposit[] = array(
							'transaction' => 'SupplierDeposit',
							'transaction_date' => $date,
							'reference' => $company->name,
							'account' => $depositAcc->supplier_deposit_acc,
							'amount' => $this->input->post('amount'),
							'narrative' => 'Supplier Deposit '.$company->name,
							'description' => $this->input->post('note'),
							'biller_id' => $this->input->post('biller'),
							'project_id' => $this->input->post('project'),
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $company->id,
						);
					$accTranDeposit[] = array(
							'transaction' => 'SupplierDeposit',
							'transaction_date' => $date,
							'reference' => $company->name,
							'account' => $paying_from,
							'amount' => -($this->input->post('amount')),
							'narrative' => 'Supplier Deposit '.$company->name,
							'description' => $this->input->post('note'),
							'biller_id' => $this->input->post('biller'),
							'project_id' => $this->input->post('project'),
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $company->id,
						);
				}
			//=====end accountig=====//

            $cdata = array(
                'deposit_amount' => ($company->deposit_amount+$this->input->post('amount'))
            );

        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addDeposit($data, $cdata, $accTranDeposit)) {
            $this->session->set_flashdata('message', lang("deposit_added"));
            redirect("suppliers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
			
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('','','1');
				$this->data['projects'] = $this->site->getAllProjects();
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
			
            $this->load->view($this->theme . 'suppliers/add_deposit', $this->data);
        }
    }
	
	public function edit_deposit($id = NULL)
    {
        $this->cus->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->companies_model->getDepositByID($id);
        $company = $this->companies_model->getCompanyByID($deposit->company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        
        if ($this->form_validation->run() == true) {
            $date = $this->cus->fld(trim($this->input->post('date')));
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
            $data = array(
                'date' => $date,
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'company_id' => $deposit->company_id,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
				'biller_id' => $this->input->post('biller'),
				'project_id' => $this->input->post('project'),
				'account' => $paying_from,
            );
			if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$depositAcc = $this->site->getAccountSettingByBiller($this->input->post('biller'));
					$accTranDeposit[] = array(
							'transaction' => 'SupplierDeposit',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $company->name,
							'account' => $depositAcc->supplier_deposit_acc,
							'amount' => $this->input->post('amount'),
							'narrative' => 'Supplier Deposit '.$company->name,
							'description' => $this->input->post('note'),
							'biller_id' => $this->input->post('biller'),
							'project_id' => $this->input->post('project'),
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $company->id,
						);
					$accTranDeposit[] = array(
							'transaction' => 'SupplierDeposit',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $company->name,
							'account' => $paying_from,
							'amount' => -($this->input->post('amount')),
							'narrative' => 'Supplier Deposit '.$company->name,
							'description' => $this->input->post('note'),
							'biller_id' => $this->input->post('biller'),
							'project_id' => $this->input->post('project'),
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $company->id,
						);
				}
			//=====end accountig=====//
			
            $cdata = array(
                'deposit_amount' => (($company->deposit_amount-$deposit->amount)+$this->input->post('amount'))
            );

        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateSupplierDeposit($id, $data, $cdata, $accTranDeposit)) {
            $this->session->set_flashdata('message', lang("deposit_updated"));
            redirect("suppliers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->data['deposit'] = $deposit;
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$deposit->account,'1');
				$this->data['projects'] = $this->site->getAllProjects();
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
            $this->load->view($this->theme . 'suppliers/edit_deposit', $this->data);
        }
    }

}
