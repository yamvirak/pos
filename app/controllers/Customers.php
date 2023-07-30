<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller
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
        $this->lang->load('customers', $this->Settings->user_language);
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
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('customers')));
        $meta = array('page_title' => lang('customers'), 'bc' => $bc);
        $this->core_page('customers/index', $meta, $this->data);
    }

    function getCustomers()
    {
        $this->cus->checkPermissions('index');
        $this->load->library('datatables');
		$truck_link = "";
		if($this->config->item('customer_truck')){
			$truck_link = anchor('customers/trucks/$1', '<i class="fa fa-car"></i> ' . lang('list_trucks'), ' class="truck"');
		}
		$address_link = anchor('customers/addresses/$1', '<i class="fa fa-location-arrow"></i> ' . lang('list_addresses'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$add_deposit_link = anchor('customers/add_deposit/$1', '<i class="fa fa-plus"></i> ' . lang('add_deposit'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$deposit_link = anchor('customers/deposits/$1', '<i class="fa fa-money"></i> ' . lang('list_deposits'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_link = anchor('customers/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_customer'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$delete_link = "<a href='#' class='delete_customer po' title='<b>" . $this->lang->line("delete_customer") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_customer') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $truck_link . '</li>
						<li>' . $address_link . '</li>
						<li>' . $add_deposit_link . '</li>
						<li>' . $deposit_link . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		
		
        $this->datatables
            ->select("id as id, code, company, name, phone, vat_no, address, price_group_name, customer_group_name, saleman_name, deposit_amount,customer_status")
            ->from("companies")			
            ->where('group_name', 'customer');
           
        $this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
    }

    function view($id = NULL)
    {
        $this->cus->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer'] = $this->companies_model->getCompanyByID($id);
        $this->load->view($this->theme.'customers/view',$this->data);
    }

    function add()
    {
        $this->cus->checkPermissions(false, true);
		$this->form_validation->set_rules('code', lang("code"), 'is_unique[companies.code]');
        $this->form_validation->set_rules('email', lang("email_address"), 'is_unique[companies.email]');
		$this->form_validation->set_rules('phone', lang("phone"), 'is_unique[companies.phone]');
		
        if ($this->form_validation->run('companies/add') == true) {
            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
			$sm = $this->site->getUser($this->input->post('saleman_id'));
			$ct = $this->site->getCardType($this->input->post('card_type_id'));
            $nation = $this->site->getNationalityType($this->input->post('nationality_id'));
            $data = array(
				'code' => $this->input->post('code'),
				'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => '3',
                'group_name' => 'customer',
                'customer_group_id' => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
				'saleman_id' => $this->input->post('saleman_id'),
                'saleman_name' => $sm->last_name.' '.$sm->first_name,
                'card_type_id' => $this->input->post('card_type_id'),
                'card_types' => $ct->name,
                'nationality_id' => $this->input->post('nationality_id'),
                'nationality' => $nation->name,
                'price_group_id' => $this->input->post('price_group') ? $this->input->post('price_group') : NULL,
                'price_group_name' => $this->input->post('price_group') ? $pg->name : NULL,
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
				'nric' => $this->input->post('nric'),
				'occupation' => $this->input->post('occupation'),
				'gender' => $this->input->post('gender'),
				'dob' => $this->cus->fsd($this->input->post('dob')),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'credit_day' => $this->input->post('credit_day'),
                'credit_amount' => $this->input->post('credit_amount'),
				'credit_quantity' => $this->input->post('credit_quantity'),
				'product_promotion_id' => $this->input->post('product_promotion_id'),
                'customer_status' => $this->input->post('customer_status'),
            );

			if($this->config->item('customer_orders')){
				$data['username'] = $this->input->post('username');
				$password = $this->input->post('password');
				if($password && $password!=''){
					$salt = $this->config->item('store_salt', 'ion_auth') ? substr(md5(uniqid(rand(), true)), 0, $this->config->item('salt_length', 'ion_auth')) : FALSE;
					$this->load->model('auth_model');
					$password = $this->auth_model->hash_password($password, $salt);
					$data['password'] = $password;
				}
			}
			
			
			if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
				if (!$this->upload->do_upload('photo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['logo'] = $photo;
            }
			
        } elseif ($this->input->post('add_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $cid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', lang("customer_added"));
			$this->site->updateCompanyReference('customer');
            $ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : NULL;
            redirect($ref[0] . '?customer=' . $cid);
        } else {
            $this->data['customers'] = $this->site->getRandomReferenceCustomer();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['nationality_types'] = $this->companies_model->getAllNationalityTypes();
            $this->data['card_types'] = $this->companies_model->getAllCardTypes();
            $this->data['price_groups'] = $this->companies_model->getAllPriceGroups();
			if($this->config->item('product_promotions')){
				$this->data['product_promotions'] = $this->site->getProductPromotions();
			}
			$this->data['salemans'] = $this->site->getSalemans();
            $this->load->view($this->theme . 'customers/add', $this->data);
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
		if ($this->input->post('phone') != $company_details->phone) {
            $this->form_validation->set_rules('phone', lang("phone"), 'is_unique[companies.phone]');
        }
		
        if ($this->form_validation->run('companies/add') == true) {
            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
			$sm = $this->site->getUser($this->input->post('saleman_id'));
            $ct = $this->site->getCardType($this->input->post('card_type_id'));
            $nation = $this->site->getNationalityType($this->input->post('nationality_id'));
			
            $data = array(
				'code' => $this->input->post('code'),
				'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => '3',
                'group_name' => 'customer',
				'saleman_id' => $this->input->post('saleman_id'),
                'saleman_name' => $sm->last_name.' '.$sm->first_name,
                'card_type_id' => $this->input->post('card_type_id'),
                'card_types' => $ct->name,
                'nationality_id' => $this->input->post('nationality_id'),
                'nationality' => $nation->name,
                'customer_group_id' => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id' => $this->input->post('price_group') ? $this->input->post('price_group') : NULL,
                'price_group_name' => $this->input->post('price_group') ? $pg->name : NULL,
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
				'nric' => $this->input->post('nric'),
				'occupation' => $this->input->post('occupation'),
				'gender' => $this->input->post('gender'),
				'dob' => $this->cus->fsd($this->input->post('dob')),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'award_points' => $this->input->post('award_points'),
                'credit_day' => $this->input->post('credit_day'),
                'credit_amount' => $this->input->post('credit_amount'),
				'credit_quantity' => $this->input->post('credit_quantity'),
				'product_promotion_id' => $this->input->post('product_promotion_id'),
                'customer_status' => $this->input->post('customer_status'),
            );
			
			
			if($this->config->item('customer_orders')){
				$data['username'] = $this->input->post('username');
				$password = $this->input->post('password');
				if($password && $password!=''){
					$salt = $this->config->item('store_salt', 'ion_auth') ? substr(md5(uniqid(rand(), true)), 0, $this->config->item('salt_length', 'ion_auth')) : FALSE;
					$this->load->model('auth_model');
					$password = $this->auth_model->hash_password($password, $salt);
					$data['password'] = $password;
				}
			}
			
			if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
				if (!$this->upload->do_upload('photo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['logo'] = $photo;
            }
	
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', lang("customer_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['customer'] = $company_details;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['nationality_types'] = $this->companies_model->getAllNationalityTypes();
            $this->data['card_types'] = $this->companies_model->getAllCardTypes();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups'] = $this->companies_model->getAllPriceGroups();
			$this->data['salemans'] = $this->site->getSalemans();
			if($this->config->item('product_promotions')){
				$this->data['product_promotions'] = $this->site->getProductPromotions();
			}
            $this->load->view($this->theme . 'customers/edit', $this->data);
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
        $this->load->view($this->theme . 'customers/users', $this->data);

    }

    function add_user($company_id = NULL)
    {
        $this->cus->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', lang("email_address"), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');

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
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', lang("user_added"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'customers/add_user', $this->data);
        }
    }

    function import_csv()
    {
        $this->cus->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('excel_file', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (DEMO) {
                $this->session->set_flashdata('warning', lang("disabled_in_demo"));
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
					 $customer_group_name = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
					 $price_group_name = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
					 $address = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
					 $city = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
					 $state = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
					 $postal_code = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
					 $country = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
					 $vat_no = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
					 $cf1s = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
					 $cf2s = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
					 $cf3s = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
					 $cf4s = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
					 $cf5s = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
					 $cf6s = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
					 
					if(empty($code)){
						$this->session->set_flashdata('error', lang("check_customer_code") . " (" . $code . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $row . ")");
						redirect("customers");
					}
					if(empty($phone)){
						$this->session->set_flashdata('error', lang("check_customer_phone") . " (" . $phone . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $row . ")");
						redirect("customers");
					}
					 
					 if($cust_group = $this->companies_model->getCustomerGroupByName(trim($customer_group_name))){
						$cust_group_id = $cust_group->id;
						$cust_group_name = $cust_group->name;
					 }
					 if($price_group = $this->companies_model->getPriceGroupByName(trim($price_group_name))){
						 $price_group_id = $price_group->id;
						 $price_group_name = $price_group->name;
					 }
					 
						$data[] = array(
						  'code'  => $code,
						  'company'  => $company,
						  'name'   => $name,
						  'email'    => $email,
						  'phone'  => $phone,
						  'address'   => $address,
						  'customer_group_id'   => $cust_group_id,
						  'customer_group_name'   => $cust_group_name,
						  'price_group_id'   => $price_group_id,
						  'price_group_name'   => $price_group_name,
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
						  'group_id'   => 3,
						  'group_name'   => 'customer',
						 );
					}
				}
				
				$rw = 2;
				$checkCode = false;
				$checkPhone = false;
                foreach ($data as $csv_com) {
                    if(!$this->companies_model->getCompanyByCodeGroupName(trim($csv_com['code'],'customer'))) {
						if ($csv_com['email'] && $this->companies_model->getCompanyByEmail($csv_com['email'])) {
							$this->session->set_flashdata('error', lang("check_customer_email") . " (" . $email . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
							redirect("customers");
						}
						if(isset($checkCode[trim($csv_com['code'])]) && $checkCode[trim($csv_com['code'])]){
							$this->session->set_flashdata('error', lang("check_customer_code") . " (" . $csv_com['code'] . "). " . lang("customer_duplicate_exist") . " (" . lang("line_no") . " " . $rw . ")");
							redirect("customers");
						}
						if(isset($checkPhone[trim($csv_com['phone'])]) && $checkPhone[trim($csv_com['phone'])]){
							$this->session->set_flashdata('error', lang("check_customer_phone") . " (" . $csv_com['phone'] . "). " . lang("customer_duplicate_exist") . " (" . lang("line_no") . " " . $rw . ")");
							redirect("customers");
						}
						if ($this->companies_model->getCompanyByPhone($csv_com['phone'],'customer')) {
							$this->session->set_flashdata('error', lang("check_customer_phone") . " (" . $csv_com['phone'] . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
							redirect("customers");
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
						$this->session->set_flashdata('error', lang("check_customer_code") . " (" . $csv_com['code'] . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
						redirect("customers");
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
            redirect('customers');
        }
        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->addCompanies($data)) {
                $this->session->set_flashdata('message', lang("customers_added"));
                redirect('customers');
            }
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/import', $this->data);
        }
    }

    function delete($id = NULL)
    {
        $this->cus->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$customer = $this->companies_model->getCompanyByID($id);
		if ($customer->student_id > 0) {
            $this->session->set_flashdata('error', lang('customer_link_to_student'));
            $this->cus->md();
        }
        if ($this->input->get('id') == 1) {
            $this->session->set_flashdata('error', lang('customer_x_deleted'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
        if ($this->companies_model->deleteCustomer($id)) {
            echo lang("customer_deleted");
        } else {
            $this->session->set_flashdata('warning', lang('customer_x_deleted_have_sales'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    function suggestions($term = NULL, $limit = NULL)
    {
        // $this->cus->checkPermissions('index');
        if ($this->input->get('term') || $this->input->get('term') == 0) {
            $term = $this->input->get('term', TRUE);
        }
        if (strlen($term) < 1) {
            return FALSE;
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->companies_model->getCustomerSuggestions($term, $limit);
        $this->cus->send_json($rows);
    }

    function getCustomer($id = NULL)
    {
        $row = $this->companies_model->getCompanyByID($id);
        $this->cus->send_json(array(array('id' => $row->id, 'text' => ($row->name != '-' ? $row->name : $row->name).' ('.$row->phone.')')));
    }

    function get_customer_details($id = NULL)
    {
        $this->cus->send_json($this->companies_model->getCompanyByID($id));
    }

    function get_award_points($id = NULL)
    {
        $this->cus->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->cus->send_json(array('ca_points' => $row->award_points));
    }

    function customer_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
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
                        if (!$this->companies_model->deleteCustomer($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('customers_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', lang("customers_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer'));
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
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('deposit_amount'));
					$this->excel->getActiveSheet()->SetCellValue('M1', lang('price_group'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('customer_group'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('saleman'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('ccf1'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('ccf2'));
                    $this->excel->getActiveSheet()->SetCellValue('R1', lang('ccf3'));
                    $this->excel->getActiveSheet()->SetCellValue('S1', lang('ccf4'));
                    $this->excel->getActiveSheet()->SetCellValue('T1', lang('ccf5'));
                    $this->excel->getActiveSheet()->SetCellValue('U1', lang('ccf6'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->company);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $customer->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->phone);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $customer->address);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $customer->city);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $customer->state);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $customer->postal_code);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $customer->country);
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $customer->vat_no);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $customer->deposit_amount);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $customer->price_group_name);
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $customer->customer_group_name);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $customer->saleman_name);
						$this->excel->getActiveSheet()->SetCellValue('P' . $row, $customer->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $customer->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('R' . $row, $customer->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('S' . $row, $customer->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('T' . $row, $customer->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('U' . $row, $customer->cf6);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'customers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_customer_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
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
        $this->load->view($this->theme . 'customers/deposits', $this->data);

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
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("deposit_note") . "' href='" . site_url('customers/deposit_note/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-file-text-o\"></i></a> <a class=\"tip\" title='" . lang("edit_deposit") . "' href='" . site_url('customers/edit_deposit/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_deposit") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete_deposit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
        ->unset_column('id');
        echo $this->datatables->generate();
    }

    public function add_deposit($company_id = NULL)
    {
        $this->cus->checkPermissions('deposits', true);
        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        if ($this->Owner || $this->Admin || $this->GP['sales-date']) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        if ($this->form_validation->run() == true) {
            $date = $this->cus->fld(trim($this->input->post('date')));
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_to = $cash_account->account_code;
			
            $data = array(
                'date' => $date,
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'company_id' => $company->id,
                'created_by' => $this->session->userdata('user_id'),
				'biller_id' => $this->input->post('biller'),
				'project_id' => $this->input->post('project'),
				'account' => $paying_to,
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
							'transaction' => 'CustomerDeposit',
							'transaction_date' => $date,
							'reference' => $company->name,
							'account' => $depositAcc->customer_deposit_acc,
							'amount' => -($this->input->post('amount')),
							'narrative' => 'Customer Deposit '.$company->name,
							'description' => $this->input->post('note'),
							'biller_id' => $this->input->post('biller'),
							'project_id' => $this->input->post('project'),
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $company->id,
						);
					$accTranDeposit[] = array(
							'transaction' => 'CustomerDeposit',
							'transaction_date' => $date,
							'reference' => $company->name,
							'account' => $paying_to,
							'amount' => $this->input->post('amount'),
							'narrative' => 'Customer Deposit '.$company->name,
							'description' => $this->input->post('note'),
							'biller_id' => $this->input->post('biller'),
							'project_id' => $this->input->post('project'),
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $company->id,
						);
				}
			//=====end accountig=====//

            $cdata = array(
                'deposit_amount' => ($company->deposit_amount+$this->input->post('amount'))
            );

        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addDeposit($data, $cdata, $accTranDeposit)) {
            $this->session->set_flashdata('message', lang("deposit_added"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
			$this->data['projects'] = $this->site->getAllProjects();
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->load->view($this->theme . 'customers/add_deposit', $this->data);
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
        if ($this->Owner || $this->Admin || $this->GP['sales-date']) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        if ($this->form_validation->run() == true) {
            $date = $this->cus->fld(trim($this->input->post('date')));
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_to = $cash_account->account_code;
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
				'account' => $paying_to,
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
							'transaction' => 'CustomerDeposit',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $company->name,
							'account' => $depositAcc->customer_deposit_acc,
							'amount' => -($this->input->post('amount')),
							'narrative' => 'Customer Deposit '.$company->name,
							'description' => $this->input->post('note'),
							'biller_id' => $this->input->post('biller'),
							'project_id' => $this->input->post('project'),
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $company->id,
						);
					$accTranDeposit[] = array(
							'transaction' => 'CustomerDeposit',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $company->name,
							'account' => $paying_to,
							'amount' => $this->input->post('amount'),
							'narrative' => 'Customer Deposit '.$company->name,
							'description' => $this->input->post('note'),
							'biller_id' => $this->input->post('biller'),
							'project_id' => $this->input->post('project'),
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $company->id,
						);
				}
			//=====end accountig=====//
			
            $cdata = array(
                'deposit_amount' => (($company->deposit_amount-$deposit->amount)+$this->input->post('amount'))
            );

        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateDeposit($id, $data, $cdata, $accTranDeposit)) {
            $this->session->set_flashdata('message', lang("deposit_updated"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->data['deposit'] = $deposit;
			$this->data['projects'] = $this->site->getAllProjects();
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->load->view($this->theme . 'customers/edit_deposit', $this->data);
        }
    }

    public function delete_deposit($id)
    {
        $this->cus->checkPermissions(NULL, TRUE);

        if ($this->companies_model->deleteDeposit($id)) {
			$this->site->deleteAccTran('CustomerDeposit',$id);
            echo lang("deposit_deleted");
        }
    }

    public function deposit_note($id = null)
    {
        $this->cus->checkPermissions('deposits', true);
        $deposit = $this->companies_model->getDepositByID($id);
        $this->data['customer'] = $this->companies_model->getCompanyByID($deposit->company_id);
        $this->data['deposit'] = $deposit;
        $this->data['page_title'] = $this->lang->line("deposit_note");
        $this->load->view($this->theme . 'customers/deposit_note', $this->data);
    }

    public function addresses($company_id = NULL)
    {
        $this->cus->checkPermissions('index', true);
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->data['addresses'] = $this->companies_model->getCompanyAddresses($company_id);
        $this->load->view($this->theme . 'customers/addresses', $this->data);

    }

	
	function add_address($company_id = NULL)
	{	
        $this->cus->checkPermissions('add');
        $company = $this->companies_model->getCompanyByID($company_id);
        $this->form_validation->set_rules('address', lang("address"), 'required');
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
				'name' => $this->input->post('name'),
                'address' => $this->input->post('address'),
                'latitude' => $this->input->post('latitude'),
                'longitude' => $this->input->post('longitude'),
                'color_marker' => $this->input->post('color_marker'),
                'company_id' => $company_id,
				'contact_person' => $this->input->post('contact_person'),
				'kilometer' => $this->input->post('kilometer'),
				'phone' => $this->input->post('phone'),
                'updated_at' => date('Y-m-d h:i:sa'),
            );
        } elseif ($this->input->post('add_address')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addAddress($data)) {
            $this->session->set_flashdata('message', lang("address_added"));
            redirect("customers");
        }  else {
			$this->data['company'] = $company;
            $bc = array(array('link' => site_url('home'), 'page' => lang('home')), array('link' => site_url('customers'), 'page' => lang('customers')), array('link' => '#', 'page' => lang('add_address')));
            $meta = array('page_title' => lang('add_address'), 'bc' => $bc);
            $this->core_page('customers/add_address', $meta, $this->data);
        }
    }
	
	function edit_address($id = NULL)
	{	
        $this->cus->checkPermissions('edit');
        $this->form_validation->set_rules('address', lang("address"), 'required');
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
				'name' => $this->input->post('name'),
                'address' => $this->input->post('address'),
                'latitude' => $this->input->post('latitude'),
                'longitude' => $this->input->post('longitude'),
                'color_marker' => $this->input->post('color_marker'),
				'contact_person' => $this->input->post('contact_person'),
				'kilometer' => $this->input->post('kilometer'),
				'phone' => $this->input->post('phone'),
                'updated_at' => date('Y-m-d h:i:sa'),
            );
        } elseif ($this->input->post('edit_address')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateAddress($id, $data)) {
            $this->session->set_flashdata('message', lang("address_updated"));
            redirect("customers");
        }  else {
			$address = $this->companies_model->getAddressByID($id);
			$this->data['address'] = $address;
			$this->data['company'] = $this->companies_model->getCompanyByID($address->company_id);	
            $bc = array(array('link' => site_url('home'), 'page' => lang('home')), array('link' => site_url('customers'), 'page' => lang('customers')), array('link' => '#', 'page' => lang('edit_address')));
            $meta = array('page_title' => lang('edit_address'), 'bc' => $bc);
            $this->core_page('customers/edit_address', $meta, $this->data);
        }
    }
	
	function view_address($id = false, $company_id = false, $color_marker = false)
	{	
        $this->cus->checkPermissions('index');
		if($this->input->post("customer")){
			$company_id = $this->input->post("customer");
		}
		if($this->input->post("color_marker")){
			$color_marker = $this->input->post("color_marker");
		}
        $addresses = $this->companies_model->getAddresses($id, $company_id, $color_marker);
		if($company_id && $company_id != "false"){
			$this->data['company'] = $this->companies_model->getCompanyByID($company_id);
		}else{
			$this->data['company'] = false;
		}	
		$this->data['customer_id'] = $company_id;
		$this->data['customers'] = $this->site->getAllCompanies('customer');
		$this->data['addresses'] = $addresses;
		$bc = array(array('link' => site_url('home'), 'page' => lang('home')), array('link' => site_url('customers'), 'page' => lang('customers')), array('link' => '#', 'page' => lang('view_address')));
		$meta = array('page_title' => lang('view_address'), 'bc' => $bc);
		$this->core_page('customers/view_address', $meta, $this->data);
        
    }

    public function delete_address($id)
    {
        $this->cus->checkPermissions('delete', TRUE);

        if ($this->companies_model->deleteAddress($id)) {
            $this->session->set_flashdata('message', lang("address_deleted"));
            redirect("customers");
        }
    }
	
	public function get_project()
	{
		$id = $this->input->get("biller");
		$project_id = $this->input->get("project");
		$rows = $this->site->getAllProjectByBillerID($id);
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$project = json_decode($user->project_ids);
			
		$pl = array(lang('select')." ".lang('project'));
		if ($this->Owner || $this->Admin || $project[0] === 'all') {
			foreach($rows as $row){
				$pl[$row->id] = $row->name;
			}
		}else{
			foreach($rows as $row){
				if(in_array($row->id, $project)){
					$pl[$row->id] = $row->name;
				}
			}
		}
		$opt = form_dropdown('project', $pl, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="project" class="form-control"');
		echo json_encode(array("result" => $opt));
	}

	public function send_sms()
    {
        $this->cus->checkPermissions("send_sms");
		$this->form_validation->set_rules('name', lang("code"), 'required');
		$this->form_validation->set_rules('message', lang("message"), 'required');
		$ids = explode(",", $this->input->get("ids", true));
		
        if ($this->form_validation->run() == true) {
			$data = array(
						"date"	   	   => date("Y-m-d H:i:s"),
						"user_id"	   => $this->session->userdata("user_id"),
						"name" 		   => $this->input->post("name"),
						"message" 	   => $this->input->post("message"),
						"customer_ids" => json_encode($this->input->post("customer_id")),
					);
			
        } elseif ($this->input->post('send_sms')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers/send_sms');
        }
        if ($this->form_validation->run() == true && $this->companies_model->addMessage($data)) {
            $this->session->set_flashdata('message', lang("message_added"));
            redirect('customers');
        } else {
			$this->data['ids'] = $ids;
			$this->data['customers'] = $this->site->getCompanyReference('customer');
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/send_sms', $this->data);
        }
    }
	
	public function view_message()
	{
		$this->cus->checkPermissions("send_sms");
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->load->view($this->theme . 'customers/message', $this->data);
	}
	
	public function get_messages()
    {
        $this->cus->checkPermissions("send_sms");
        $this->load->library('datatables');
        $this->datatables
            ->select("
					messages.id as id, 
					date, 
					name, 
					message, 
					CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by", false)
            ->from("messages")
            ->join('users', 'users.id=messages.user_id', 'left')
            ->add_column("Actions", "<div class=\"text-center\"><a href='#' class='tip po' title='<b>" . lang("delete_message") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete_message/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
			->unset_column('id');
        echo $this->datatables->generate();
    }
	
	public function delete_message($id)
    {
		$this->cus->checkPermissions("send_sms");
        if ($this->companies_model->deleteMessage($id)) {
            echo lang("message_deleted");
        }
    }
	
	
	function trucks($customer_id = NULL)
    {
		$this->cus->checkPermissions("index");
        if (!$customer_id) {
            $this->session->set_flashdata('error', lang('no_customer_selected'));
            redirect('customers');
        }
        $this->data['customer'] = $this->companies_model->getCompanyByID($customer_id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('customers'), 'page' => lang('customers')),  array('link' => '#', 'page' => lang('trucks')));
        $meta = array('page_title' => lang('trucks'), 'bc' => $bc);
        $this->core_page('customers/trucks', $meta, $this->data);
    }

    function getTrucks($customer_id = NULL)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("customer_trucks.id as id, customer_trucks.name, customer_trucks.plate_number", FALSE)
            ->from("customer_trucks")
			->where("customer_trucks.customer_id",$customer_id)
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('customers/edit_truck/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal' class='tip' title='" . lang("edit_truck") . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_truck") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete124' href='" . site_url('customers/delete_truck/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	
	function add_truck($customer_id = false)
    {
		$this->cus->checkPermissions("add", true);
        $this->form_validation->set_rules('name', lang("name"), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = array(
				'customer_id' => $customer_id,
                'name' => $this->input->post('name'),
                'plate_number' => $this->input->post('plate_number'),
                );
        } elseif ($this->input->post('add_truck')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("customers/trucks/".$customer_id);
        }
        if ($this->form_validation->run() == true && $this->companies_model->addCustomerTruck($data)) {
            $this->session->set_flashdata('message', lang("truck_added")." ".$data['name']);
			redirect("customers/trucks/".$customer_id);
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['customer_id'] = $customer_id;
            $this->load->view($this->theme . 'customers/add_truck', $this->data);

        }
    }
	function edit_truck($id = NULL)
    {
		$this->cus->checkPermissions("edit", true);
        $this->form_validation->set_rules('name', lang("name"), 'trim|required');
		$truck = $this->companies_model->getCustomerTruckByID($id);		
        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => $this->input->post('name'),
                'plate_number' => $this->input->post('plate_number'),
                );
        } elseif ($this->input->post('edit_truck')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("customers/trucks".$truck->customer_id);
        }
        if ($this->form_validation->run() == true && $this->companies_model->updateCustomerTruck($id, $data)) {
            $this->session->set_flashdata('message', lang("truck_edited"));
            redirect("customers/trucks/".$truck->customer_id);
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['truck'] = $truck;
            $this->load->view($this->theme . 'customers/edit_truck', $this->data);
        }
    }
	
	function delete_truck($id = NULL)
    {
        $this->cus->checkPermissions("delete", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$truck = $this->companies_model->getCustomerTruckByID($id);	
        if ($this->companies_model->deleteCustomerTruck($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("truck_deleted");die();
            }
            $this->session->set_flashdata('message', lang('truck_deleted'));
            redirect("customers/trucks/".$truck->customer_id);
        }
    }
	
	function truck_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->companies_model->deleteCustomerTruck($id);
                    }
                    $this->session->set_flashdata('message', lang("truck_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('truck'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('plate_number'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $truck = $this->companies_model->getCustomerTruckByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $truck->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $truck->plate_number);
                        $row++;
                    }
                    $filename = 'truck_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	

}
