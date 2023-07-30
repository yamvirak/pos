<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Billers extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->cus->md('login');
        }
        if (!$this->Owner && !$this->GP['billers-index']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('billers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('companies_model');
    }

    function index($action = NULL)
    {
        $this->cus->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('billers')));
        $meta = array('page_title' => lang('billers'), 'bc' => $bc);
        $this->core_page('billers/index', $meta, $this->data);
    }

    function getBillers()
    {
        $this->cus->checkPermissions('index');
		$delete_biller = "";
		if(!$this->config->item('one_biller')){
			$delete_biller = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_biller") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('billers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a>";
		}
		
        $this->load->library('datatables');
        $this->datatables
            ->select("id, company, name, vat_no, phone, email, city, country")
            ->from("companies")
            ->where('group_name', 'biller')
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_biller") . "' href='" . site_url('billers/edit/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a> {$delete_biller}</div>", "id");
        //->unset_column('id');
        echo $this->datatables->generate();
    }

    function add()
    {
        $this->cus->checkPermissions('index', true);
		$this->form_validation->set_rules('company', lang("company"), 'required');
        if ($this->form_validation->run('companies/add') == true) {
			$prefix = $this->input->post('prefix');
            $data = array('name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => NULL,
                'group_name' => 'biller',
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'logo' => $this->input->post('logo'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'invoice_footer' => $this->input->post('invoice_footer'),
				'default_cash' => $this->input->post('default_cash'),
				'postal_code' => $this->input->post('postal_code')
            );
			if($this->config->item('concretes')){
				$data['start_hour'] = $this->input->post('start_hour');
				$data['end_hour'] = $this->input->post('end_hour');
				$data['office_commission_rate'] = $this->input->post('office_commission_rate');
				$data['fuel_price'] = $this->input->post('fuel_price');
				$data['pump_commission_rate'] = $this->input->post('pump_commission_rate');
				$data['truck_commission_rate'] = $this->input->post('truck_commission_rate');
				$data['pump_commission_rate_assistant'] = $this->input->post('pump_commission_rate_assistant');
				$data['truck_commission_rate_ot'] = $this->input->post('truck_commission_rate_ot');
				$data['big_truck_commission_rate'] = $this->input->post('big_truck_commission_rate');
				$data['big_truck_commission_rate_ot'] = $this->input->post('big_truck_commission_rate_ot');
			}
			if($this->Settings->accounting == 1){
				$accounting_config = array(
					'ar_acc' => $this->input->post('receivable_account'),
					'ap_acc' => $this->input->post('payable_account'),
					'sale_discount_acc' => $this->input->post('sale_discount_account'),
					'purchase_discount_acc' => $this->input->post('purchase_discount_account'),
					'sale_return_acc' => $this->input->post('sale_return_account'),
					'purchase_return_acc' => $this->input->post('purchase_return_account'),
					'open_balance_acc' => $this->input->post('opening_balance_account'),
					'customer_deposit_acc' => $this->input->post('customer_deposit_account'),
					'supplier_deposit_acc' => $this->input->post('supplier_deposit_account'),
					'vat_input' => $this->input->post('vat_input_account'),
					'vat_output' => $this->input->post('vat_output_account'),
					'salary_payable_acc' => $this->input->post('salary_payable_account'),
					'salary_expense_acc' => $this->input->post('salary_expense_account'),
					'cash_advance_acc' => $this->input->post('cash_advance_account'),
					'salary_13_acc' => $this->input->post('salary_13_account'),
					'compensate_acc' => $this->input->post('compensate_account'),
					'overtime_acc' => $this->input->post('overtime_account'),
					'shipping_acc' => $this->input->post('shipping_account'),
					'other_income_acc' => $this->input->post('other_income_account'),
					'pawn_stock_acc' => $this->input->post('pawn_stock_account'),
					'customer_stock_acc' => $this->input->post('customer_stock_account'),
					'installment_interest_acc' => $this->input->post('installment_interest_account'),
					'installment_outstanding_acc' => $this->input->post('installment_outstanding_account'),
					'saleman_commission_acc' => $this->input->post('saleman_commission_account'),
					'agency_commission_acc' => $this->input->post('agency_commission_account'),
					'consignment_acc' => $this->input->post('consignment_account'),
					'prepaid_acc' => $this->input->post('prepaid_account'),
					'fuel_expense_acc' => $this->input->post('fuel_expense_account'),
				);
			}					
			
        } elseif ($this->input->post('add_biller')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('billers');
        }

        if ($this->form_validation->run() == true && $id = $this->companies_model->addCompany($data,$accounting_config)) {
			$this->companies_model->addPrefix($id,$prefix);
            $this->session->set_flashdata('message', $this->lang->line("biller_added"));
            redirect("billers");
        } else {
            $this->data['logos'] = $this->getLogoList();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			if($this->Settings->accounting == 1){
				$this->data['receivable_account'] = $this->site->getAccount(array('AS'));
				$this->data['prepaid_account'] = $this->site->getAccount(array('AS'));
				$this->data['payable_account'] = $this->site->getAccount(array('LI'));
				$this->data['purchase_discount_account'] = $this->site->getAccount(array('RE','OI','CO','EX','OX'));
				$this->data['sale_discount_account'] = $this->site->getAccount(array('RE','OI','EX','LI'));
				$this->data['purchase_return_account'] = $this->site->getAccount(array('EX','CO'));
				$this->data['sale_return_account'] = $this->site->getAccount(array('AS','RE'));
				$this->data['opening_balance_account'] = $this->site->getAccount(array('EQ'));
				$this->data['supplier_deposit_account'] = $this->site->getAccount(array('AS'));
				$this->data['customer_deposit_account'] = $this->site->getAccount(array('LI'));
				$this->data['salary_payable_account'] = $this->site->getAccount(array('LI'));
				$this->data['overtime_account'] = $this->site->getAccount(array('EX'));
				$this->data['salary_expense_account'] = $this->site->getAccount(array('EX'));
				$this->data['cash_advance_account'] = $this->site->getAccount(array('AS'));
				$this->data['salary_13_account'] = $this->site->getAccount(array('EX'));
				$this->data['compensate_account'] = $this->site->getAccount(array('EX'));
				$this->data['vat_input_account'] = $this->site->getAccount(array('AS'));
				$this->data['vat_output_account'] = $this->site->getAccount(array('LI'));
				$this->data['shipping_account'] = $this->site->getAccount(array('RE','OI'));
				$this->data['other_income_account'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'));
				$this->data['pawn_stock_account'] = $this->site->getAccount(array('AS'));
				$this->data['saleman_commission_account'] = $this->site->getAccount(array('EX','OX'));
				$this->data['agency_commission_account'] = $this->site->getAccount(array('EX','OX'));
				$this->data['customer_stock_account'] = $this->site->getAccount(array('CO','EX'));
				$this->data['installment_interest_account'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'));
				$this->data['installment_outstanding_account'] = $this->site->getAccount(array('AS'));
				$this->data['consignment_account'] = $this->site->getAccount(array('AS'));
				$this->data['fuel_expense_account'] = $this->site->getAccount(array('EX','CO','OX'));
			}
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'billers/add', $this->data);
        }
    }

    function edit($id = NULL)
    {
        $this->cus->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
		$prefix = $this->companies_model->getPrefixByBill($id);
		$this->form_validation->set_rules('company', lang("company"), 'required');
        if ($this->form_validation->run('companies/add') == true) {
			$prefix = array('bill_prefix' => $this->input->post('prefix'));
            $data = array('name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => NULL,
                'group_name' => 'biller',
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'logo' => $this->input->post('logo'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'invoice_footer' => $this->input->post('invoice_footer'),
				'default_cash' => $this->input->post('default_cash'),
				'postal_code' => $this->input->post('postal_code')
            );
			if($this->config->item('concretes')){
				$data['start_hour'] = $this->input->post('start_hour');
				$data['end_hour'] = $this->input->post('end_hour');
				$data['office_commission_rate'] = $this->input->post('office_commission_rate');
				$data['fuel_price'] = $this->input->post('fuel_price');
				$data['pump_commission_rate'] = $this->input->post('pump_commission_rate');
				$data['truck_commission_rate'] = $this->input->post('truck_commission_rate');
				$data['pump_commission_rate_assistant'] = $this->input->post('pump_commission_rate_assistant');
				$data['truck_commission_rate_ot'] = $this->input->post('truck_commission_rate_ot');
				$data['big_truck_commission_rate'] = $this->input->post('big_truck_commission_rate');
				$data['big_truck_commission_rate_ot'] = $this->input->post('big_truck_commission_rate_ot');
			}
			if($this->Settings->accounting == 1){
				$accounting_config = array(
						'ar_acc' => $this->input->post('receivable_account'),
						'ap_acc' => $this->input->post('payable_account'),
						'sale_discount_acc' => $this->input->post('sale_discount_account'),
						'purchase_discount_acc' => $this->input->post('purchase_discount_account'),
						'sale_return_acc' => $this->input->post('sale_return_account'),
						'purchase_return_acc' => $this->input->post('purchase_return_account'),
						'open_balance_acc' => $this->input->post('opening_balance_account'),
						'customer_deposit_acc' => $this->input->post('customer_deposit_account'),
						'supplier_deposit_acc' => $this->input->post('supplier_deposit_account'),
						'vat_input' => $this->input->post('vat_input_account'),
						'vat_output' => $this->input->post('vat_output_account'),
						'salary_payable_acc' => $this->input->post('salary_payable_account'),
						'salary_expense_acc' => $this->input->post('salary_expense_account'),
						'overtime_acc' => $this->input->post('overtime_account'),
						'cash_advance_acc' => $this->input->post('cash_advance_account'),
						'salary_13_acc' => $this->input->post('salary_13_account'),
						'compensate_acc' => $this->input->post('compensate_account'),
						'shipping_acc' => $this->input->post('shipping_account'),
						'other_income_acc' => $this->input->post('other_income_account'),
						'pawn_stock_acc' => $this->input->post('pawn_stock_account'),
						'customer_stock_acc' => $this->input->post('customer_stock_account'),
						'installment_interest_acc' => $this->input->post('installment_interest_account'),
						'installment_outstanding_acc' => $this->input->post('installment_outstanding_account'),
						'saleman_commission_acc' => $this->input->post('saleman_commission_account'),
						'agency_commission_acc' => $this->input->post('agency_commission_account'),
						'consignment_acc' => $this->input->post('consignment_account'),
						'prepaid_acc' => $this->input->post('prepaid_account'),
						'fuel_expense_acc' => $this->input->post('fuel_expense_account'),
						);
			}			
        } elseif ($this->input->post('edit_biller')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('billers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data, $accounting_config)) {
			$this->companies_model->updatePrefix($id, $prefix);
            $this->session->set_flashdata('message', $this->lang->line("biller_updated"));
            redirect("billers");
        } else {
			if($this->Settings->accounting == 1){
				$accountSetting = $this->companies_model->getAccountSettingByBiller($id);
				$this->data['receivable_account'] = $this->site->getAccount(array('AS'),$accountSetting->ar_acc);
				$this->data['prepaid_account'] = $this->site->getAccount(array('AS'),$accountSetting->prepaid_acc);
				$this->data['payable_account'] = $this->site->getAccount(array('LI'),$accountSetting->ap_acc);
				$this->data['purchase_discount_account'] = $this->site->getAccount(array('RE','OI','CO','EX','OX'),$accountSetting->purchase_discount_acc);
				$this->data['sale_discount_account'] = $this->site->getAccount(array('RE','OI','EX','LI'),$accountSetting->sale_discount_acc);
				$this->data['purchase_return_account'] = $this->site->getAccount(array('EX','CO'),$accountSetting->purchase_return_acc);
				$this->data['sale_return_account'] = $this->site->getAccount(array('AS','RE'),$accountSetting->sale_return_acc);
				$this->data['opening_balance_account'] = $this->site->getAccount(array('EQ'),$accountSetting->open_balance_acc);
				$this->data['supplier_deposit_account'] = $this->site->getAccount(array('AS'),$accountSetting->supplier_deposit_acc);
				$this->data['customer_deposit_account'] = $this->site->getAccount(array('LI'),$accountSetting->customer_deposit_acc);
				$this->data['salary_payable_account'] = $this->site->getAccount(array('LI'),$accountSetting->salary_payable_acc);
				$this->data['salary_expense_account'] = $this->site->getAccount(array('EX'),$accountSetting->salary_expense_acc);
				$this->data['overtime_account'] = $this->site->getAccount(array('EX'),$accountSetting->overtime_acc);
				$this->data['cash_advance_account'] = $this->site->getAccount(array('AS'),$accountSetting->cash_advance_acc);
				$this->data['salary_13_account'] = $this->site->getAccount(array('EX'),$accountSetting->salary_13_acc);
				$this->data['compensate_account'] = $this->site->getAccount(array('EX'),$accountSetting->compensate_acc);
				$this->data['vat_input_account'] = $this->site->getAccount(array('AS'),$accountSetting->vat_input);
				$this->data['vat_output_account'] = $this->site->getAccount(array('LI'),$accountSetting->vat_output);
				$this->data['shipping_account'] = $this->site->getAccount(array('RE','OI'),$accountSetting->shipping_acc);
				$this->data['other_income_account'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$accountSetting->other_income_acc);
				$this->data['pawn_stock_account'] = $this->site->getAccount(array('AS'),$accountSetting->pawn_stock_acc);
				$this->data['saleman_commission_account'] = $this->site->getAccount(array('EX','OX'),$accountSetting->saleman_commission_acc);
				$this->data['agency_commission_account'] = $this->site->getAccount(array('EX','OX'),$accountSetting->agency_commission_acc);
				$this->data['customer_stock_account'] = $this->site->getAccount(array('CO','EX'), $accountSetting->customer_stock_acc);
				$this->data['installment_interest_account'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'), $accountSetting->installment_interest_acc);
				$this->data['installment_outstanding_account'] = $this->site->getAccount(array('AS'), $accountSetting->installment_outstanding_acc);
				$this->data['consignment_account'] = $this->site->getAccount(array('AS'),$accountSetting->consignment_acc);
				$this->data['fuel_expense_account'] = $this->site->getAccount(array('EX','CO','OX'),$accountSetting->fuel_expense_acc);
			}
			
            $this->data['biller'] = $company_details;
			$this->data['prefix'] = $prefix;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['logos'] = $this->getLogoList();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'billers/edit', $this->data);
        }
    }

    function delete($id = NULL)
    {
        $this->cus->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->companies_model->deleteBiller($id)) {
            echo $this->lang->line("biller_deleted");
        } else {
            $this->session->set_flashdata('warning', lang('biller_x_deleted_have_sales'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    function suggestions($term = NULL, $limit = NULL)
    {
        $this->cus->checkPermissions('index');

        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->companies_model->getBillerSuggestions($term, $limit);
        $this->cus->send_json($rows);
    }

    function getBiller($id = NULL)
    {
        $this->cus->checkPermissions('index');

        $row = $this->companies_model->getCompanyByID($id);
        $this->cus->send_json(array(array('id' => $row->id, 'text' => $row->company)));
    }

    public function getLogoList()
    {
        $this->load->helper('directory');
        $dirname = "assets/uploads/logos";
        $ext = array("jpg", "png", "jpeg", "gif");
        $files = array();
        if ($handle = opendir($dirname)) {
            while (false !== ($file = readdir($handle)))
                for ($i = 0; $i < sizeof($ext); $i++)
                    if (stristr($file, "." . $ext[$i])) //NOT case sensitive: OK with JpeG, JPG, ecc.
                        $files[] = $file;
            closedir($handle);
        }
        sort($files);
        return $files;
    }

    function biller_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
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
                        if (!$this->companies_model->deleteBiller($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('billers_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("billers_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('billers'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name_kh'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('address'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->phone);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $customer->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->address);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'branchs_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_biller_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

}
