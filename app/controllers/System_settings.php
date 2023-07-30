<?php defined('BASEPATH') OR exit('No direct script access allowed');

class system_settings extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->cus->md('login');
        }

        if (!$this->Owner && !$this->Admin && !$this->GP['settings']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->lang->load('settings', $this->Settings->user_language);
        $this->load->library('form_validation');
		$this->load->model('products_model');
        $this->load->model('settings_model');
		$this->load->model('companies_model');
        $this->upload_path = 'assets/uploads/';
		$this->digital_upload_path = 'files/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif';
        $this->allowed_file_size = '10240';
    }

    function index()
    {
        $this->form_validation->set_rules('site_name', lang('site_name'), 'trim|required');
        $this->form_validation->set_rules('dateformat', lang('dateformat'), 'trim|required');
        $this->form_validation->set_rules('timezone', lang('timezone'), 'trim|required');
        $this->form_validation->set_rules('mmode', lang('maintenance_mode'), 'trim|required');
        //$this->form_validation->set_rules('logo', lang('logo'), 'trim');
        $this->form_validation->set_rules('iwidth', lang('image_width'), 'trim|numeric|required');
        $this->form_validation->set_rules('iheight', lang('image_height'), 'trim|numeric|required');
        $this->form_validation->set_rules('twidth', lang('thumbnail_width'), 'trim|numeric|required');
        $this->form_validation->set_rules('theight', lang('thumbnail_height'), 'trim|numeric|required');
        $this->form_validation->set_rules('display_all_products', lang('display_all_products'), 'trim|numeric|required');
        $this->form_validation->set_rules('watermark', lang('watermark'), 'trim|required');
        $this->form_validation->set_rules('currency', lang('default_currency'), 'trim|required');
        $this->form_validation->set_rules('email', lang('default_email'), 'trim|required');
        $this->form_validation->set_rules('language', lang('language'), 'trim|required');
        $this->form_validation->set_rules('warehouse', lang('default_warehouse'), 'trim|required');
        $this->form_validation->set_rules('biller', lang('default_biller'), 'trim|required');
        $this->form_validation->set_rules('tax_rate', lang('product_tax'), 'trim|required');
        $this->form_validation->set_rules('tax_rate2', lang('invoice_tax'), 'trim|required');
        $this->form_validation->set_rules('sales_prefix', lang('sales_prefix'), 'trim');
		$this->form_validation->set_rules('sale_order_prefix', lang('sale_order_prefix'), 'trim');
        $this->form_validation->set_rules('quote_prefix', lang('quote_prefix'), 'trim');
        $this->form_validation->set_rules('purchase_prefix', lang('purchase_prefix'), 'trim');
		$this->form_validation->set_rules('purchase_order_prefix', lang('purchase_order_prefix'), 'trim');
		$this->form_validation->set_rules('purchase_request_prefix', lang('purchase_request_prefix'), 'trim');
        $this->form_validation->set_rules('transfer_prefix', lang('transfer_prefix'), 'trim');
        $this->form_validation->set_rules('delivery_prefix', lang('delivery_prefix'), 'trim');
        $this->form_validation->set_rules('payment_prefix', lang('payment_prefix'), 'trim');
        $this->form_validation->set_rules('return_prefix', lang('return_prefix'), 'trim');
        $this->form_validation->set_rules('expense_prefix', lang('expense_prefix'), 'trim');
        $this->form_validation->set_rules('detect_barcode', lang('detect_barcode'), 'trim|required');
        $this->form_validation->set_rules('theme', lang('theme'), 'trim|required');
        $this->form_validation->set_rules('rows_per_page', lang('rows_per_page'), 'trim|required|greater_than[9]|less_than[501]');
        $this->form_validation->set_rules('accounting_method', lang('accounting_method'), 'trim|required');
        $this->form_validation->set_rules('product_serial', lang('product_serial'), 'trim|required');
        $this->form_validation->set_rules('product_discount', lang('product_discount'), 'trim|required');
        $this->form_validation->set_rules('bc_fix', lang('bc_fix'), 'trim|numeric|required');
        $this->form_validation->set_rules('protocol', lang('email_protocol'), 'trim|required');
        if ($this->input->post('protocol') == 'smtp') {
            $this->form_validation->set_rules('smtp_host', lang('smtp_host'), 'required');
            $this->form_validation->set_rules('smtp_user', lang('smtp_user'), 'required');
            $this->form_validation->set_rules('smtp_port', lang('smtp_port'), 'required');
        }
        if ($this->input->post('protocol') == 'sendmail') {
            $this->form_validation->set_rules('mailpath', lang('mailpath'), 'required');
        }
        $this->form_validation->set_rules('decimals', lang('decimals'), 'trim|required');
        $this->form_validation->set_rules('decimals_sep', lang('decimals_sep'), 'trim|required');
        $this->form_validation->set_rules('thousands_sep', lang('thousands_sep'), 'trim|required');
        $this->load->library('encrypt');

        if ($this->form_validation->run() == true) {

            $language = $this->input->post('language');

            if ((file_exists(APPPATH.'language'.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.'cus_lang.php') && is_dir(APPPATH.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.$language)) || $language == 'english') {
                $lang = $language;
            } else {
                $this->session->set_flashdata('error', lang('language_x_found'));
                redirect("system_settings");
                $lang = 'english';
            }

            $tax1 = ($this->input->post('tax_rate') != 0) ? 1 : 0;
            $tax2 = ($this->input->post('tax_rate2') != 0) ? 1 : 0;
			$products = '';
			if($this->Settings->accounting_method != $this->input->post('accounting_method')){
				if($this->input->post('accounting_method')=='2'){
					$products = $this->products_model->getAllProducts();
					if($products){
						foreach($products as $product){
							$stockmoves[]= array(
								'transaction' => 'CostAdjustment',
								'transaction_id' => '0',
								'product_id' => $product->id,
								'product_code' => $product->code,
								'warehouse_id' => 0,
								'date' => date('Y-m-d H:i:s'),
								'real_unit_cost' => $product->cost,
								'user_id' => $this->session->userdata('user_id'),
							);
						}
					}
				}else{
					$products = $this->products_model->getAllProducts();
				}

			}

            $data = array(
				'site_name' => DEMO ? 'WeERP .v3' : $this->input->post('site_name'),
                'rows_per_page' => $this->input->post('rows_per_page'),
                'dateformat' => $this->input->post('dateformat'),
                'timezone' => $this->input->post('timezone'),
                'mmode' => trim($this->input->post('mmode')),
                'iwidth' => $this->input->post('iwidth'),
                'iheight' => $this->input->post('iheight'),
                'twidth' => $this->input->post('twidth'),
                'theight' => $this->input->post('theight'),
                'watermark' => $this->input->post('watermark'),
				'alert_qty_by_warehouse' => $this->input->post('alert_qty_by_warehouse'),
				'date_with_time' => $this->input->post('date_with_time'),
                // 'reg_ver' => $this->input->post('reg_ver'),
                // 'allow_reg' => $this->input->post('allow_reg'),
                // 'reg_notification' => $this->input->post('reg_notification'),
                'accounting_method' => $this->input->post('accounting_method'),
                'default_email' => DEMO ? 'vern.veasna@gmail.com' : $this->input->post('email'),
                'language' => $lang,
                'default_warehouse' => $this->input->post('warehouse'),
				'reference_reset' => $this->input->post('reference_reset'),
                'default_tax_rate' => $this->input->post('tax_rate'),
                'default_tax_rate2' => $this->input->post('tax_rate2'),
                'sales_prefix' => $this->input->post('sales_prefix'),
                'sale_tax_prefix' => $this->input->post('sale_tax_prefix'),
				'sale_order_prefix' => $this->input->post('sale_order_prefix'),
                'quote_prefix' => $this->input->post('quote_prefix'),
                'purchase_prefix' => $this->input->post('purchase_prefix'),
				'purchase_order_prefix' => $this->input->post('purchase_order_prefix'),
				'purchase_request_prefix' => $this->input->post('purchase_request_prefix'),
                'transfer_prefix' => $this->input->post('transfer_prefix'),
                'delivery_prefix' => $this->input->post('delivery_prefix'),
                'payment_prefix' => $this->input->post('payment_prefix'),
                'ppayment_prefix' => $this->input->post('ppayment_prefix'),
                'qa_prefix' => $this->input->post('qa_prefix'),
                'return_prefix' => $this->input->post('return_prefix'),
                'returnp_prefix' => $this->input->post('returnp_prefix'),
                'expense_prefix' => $this->input->post('expense_prefix'),
				'pawn_prefix' => $this->input->post('pawn_prefix'),
				'count_stock_prefix' => $this->input->post('count_stock_prefix'),
				'ca_prefix' => $this->input->post('ca_prefix'),
				'us_prefix' => $this->input->post('us_prefix'),
				'rus_prefix' => $this->input->post('rus_prefix'),
                'auto_detect_barcode' => trim($this->input->post('detect_barcode')),
                'theme' => trim($this->input->post('theme')),
                'product_serial' => $this->input->post('product_serial'),
                'customer_group' => $this->input->post('customer_group'),
                'product_expiry' => $this->input->post('product_expiry'),
                'product_discount' => $this->input->post('product_discount'),
                'default_currency' => $this->input->post('currency'),
                'bc_fix' => $this->input->post('bc_fix'),
                'tax1' => $tax1,
                'tax2' => $tax2,
                'overselling' => $this->input->post('restrict_sale'),
                'reference_format' => $this->input->post('reference_format'),
                'racks' => $this->input->post('racks'),
                'attributes' => $this->input->post('attributes'),
                'restrict_calendar' => $this->input->post('restrict_calendar'),
                'captcha' => $this->input->post('captcha'),
                'item_addition' => $this->input->post('item_addition'),
                'protocol' => DEMO ? 'mail' : $this->input->post('protocol'),
                'mailpath' => $this->input->post('mailpath'),
                'smtp_host' => $this->input->post('smtp_host'),
                'smtp_user' => $this->input->post('smtp_user'),
                'smtp_port' => $this->input->post('smtp_port'),
                'smtp_crypto' => $this->input->post('smtp_crypto') ? $this->input->post('smtp_crypto') : NULL,
                'decimals' => $this->input->post('decimals'),
                'decimals_sep' => $this->input->post('decimals_sep'),
                'thousands_sep' => $this->input->post('thousands_sep'),
                'default_biller' => $this->input->post('biller'),
                'invoice_view' => $this->input->post('invoice_view'),
                'rtl' => $this->input->post('rtl'),
                'each_spent' => $this->input->post('each_spent') ? $this->input->post('each_spent') : NULL,
                'ca_point' => $this->input->post('ca_point') ? $this->input->post('ca_point') : NULL,
                'each_sale' => $this->input->post('each_sale') ? $this->input->post('each_sale') : NULL,
                'sa_point' => $this->input->post('sa_point') ? $this->input->post('sa_point') : NULL,
                'sac' => $this->input->post('sac'),
                'qty_decimals' => $this->input->post('qty_decimals'),
                'display_all_products' => $this->input->post('display_all_products'),
                'display_symbol' => $this->input->post('display_symbol'),
                'symbol' => $this->input->post('symbol'),
                'remove_expired' => $this->input->post('remove_expired'),
                'barcode_separator' => $this->input->post('barcode_separator'),
                'set_focus' => $this->input->post('set_focus'),
                'disable_editing' => $this->input->post('disable_editing'),
                'price_group' => $this->input->post('price_group'),
                'barcode_img' => $this->input->post('barcode_renderer'),
                'update_cost' => $this->input->post('update_cost'),
				'car_operation' => $this->input->post('car_operation'),
                'qty_operation' => $this->input->post('qty_operation'),
				'product_formulation' => $this->input->post('product_formulation'),
				'installment_alert_days' => $this->input->post('installment_alert_days'),
				'installment_holiday' => $this->input->post('installment_holiday'),
				'installment_prefix' => $this->input->post('installment_prefix'),
				'loan_prefix' => $this->input->post('loan_prefix'),
				'app_prefix' => $this->input->post('app_prefix'),
				'sav_prefix' => $this->input->post('sav_prefix'),
				'sav_tr_prefix' => $this->input->post('sav_tr_prefix'),
				'loan_alert_days' => $this->input->post('loan_alert_days'),
				'single_login' => $this->input->post('single_login'),
				'login_time' => $this->input->post('login_time'),
				'set_custom_field' => $this->input->post('set_custom_field'),
				'customer_deposit_alerts' => $this->input->post('deposit_amount_alerts'),
				'default_payment_term' => $this->input->post('payment_term'),
				'receive_prefix' => $this->input->post('receive_prefix'),
				'customer_prefix' => $this->input->post('customer_prefix'),
				'supplier_prefix' => $this->input->post('supplier_prefix'),
				'bill_prefix' => $this->input->post('bill_prefix'),
				'cv_prefix'	=> $this->input->post('cv_prefix'),
				'customer_stock_prefix'	=> $this->input->post('customer_stock_prefix'),
				'products' => $products,
				'show_warehouse_qty' => $this->input->post('show_warehouse_qty'),
				'default_cash' => $this->input->post('default_cash'),
				'manual_category' => $this->input->post('manual_category'),
				'manual_unit' => $this->input->post('manual_unit'),
				'payment_expense' => $this->input->post('payment_expense'),
				'customer_price' => $this->input->post('customer_price'),
				'limit_print' => $this->input->post('limit_print'),
				'retainearning_acc' => $this->input->post('retainearning_acc'),
                'default_receivable_account' => $this->input->post('default_receivable_account'),
                'default_payable_account' => $this->input->post('default_payable_account'),
                'search_by_category' => $this->input->post('search_by_category'),
                'project_id' => $this->input->post('project'),
				'cbm'	=> $this->input->post('cbm'),
				'product_additional'	=> $this->input->post('product_additional'),
				'fuel_prefix'	=> $this->input->post('fuel_prefix'),
				'product_commission'	=> $this->input->post('product_commission'),
				'product_license'	=> $this->input->post('product_license'),
				'foc'	=> $this->input->post('foc'),
				'show_unit'	=> $this->input->post('show_unit'),
				'show_qoh'	=> $this->input->post('show_qoh'),
				'receive_item_vat'	=> $this->input->post('receive_item_vat'),
				'csm_prefix'	=> $this->input->post('csm_prefix'),
				'rcsm_prefix'	=> $this->input->post('rcsm_prefix'),
                'repair_prefix'	=> $this->input->post('repair_prefix'),
                'check_prefix'	=> $this->input->post('check_prefix'),
				'rental_prefix'	=> $this->input->post('rental_prefix'),
				'default_floor'	=> $this->input->post('default_floor'),
				'approval_expense'	=> $this->input->post('approval_expense'),
				'cdn_prefix'	=> $this->input->post('cdn_prefix'),
				'csale_prefix'	=> $this->input->post('csale_prefix'),
				'cfuel_prefix'	=> $this->input->post('cfuel_prefix'),
				'cer_prefix'	=> $this->input->post('cer_prefix'),
				'cmw_prefix'	=> $this->input->post('cmw_prefix'),
				'cms_prefix'	=> $this->input->post('cms_prefix'),
				'cfe_prefix'	=> $this->input->post('cfe_prefix'),
				'ccms_prefix'	=> $this->input->post('ccms_prefix'),
				'cabsent_prefix'	=> $this->input->post('cabsent_prefix'),
				'default_cash_account'	=> $this->input->post('default_cash_account'),
				'rp_prefix'	=> $this->input->post('rp_prefix'),
            );
            if ($this->input->post('smtp_pass')) {
                $data['smtp_pass'] = $this->encrypt->encode($this->input->post('smtp_pass'));
            }
			if($this->config->item('concretes')){
				$data["moving_waitings"] = $this->input->post('moving_waitings');
				$data["missions"] = $this->input->post('missions');
				$data["fuel_expenses"] = $this->input->post('fuel_expenses');
				$data["errors"] = $this->input->post('errors');
				$data["absents"] = $this->input->post('absents');
			}
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateSetting($data,$stockmoves)) {
			if($this->settings_model->updateViewStyle($this->session->userdata('user_id'),$this->input->post('style_view'))){
				$_SESSION['style_view'] = $this->input->post('style_view');
			}
            if ( ! DEMO && TIMEZONE != $data['timezone']) {
                if ( ! $this->write_index($data['timezone'])) {
                    $this->session->set_flashdata('error', lang('setting_updated_timezone_failed'));
                    redirect('system_settings');
                }
            }

            $this->session->set_flashdata('message', lang('setting_updated'));
            redirect("system_settings");
        } else {

            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['settings'] = $this->settings_model->getSettings();

			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$this->data['settings']->default_cash,'1');
				$this->data['retainearning_account'] = $this->site->getAccount('EQ',$this->data['settings']->retainearning_acc);
                $this->data['receivable_account'] = $this->site->getAccount('AS',$this->data['settings']->default_receivable_account);
                $this->data['payable_account'] = $this->site->getAccount('LI',$this->data['settings']->default_payable_account);
			}
			$this->data['tanks'] = $this->site->getTanks();
			$this->data['floors'] = $this->settings_model->getRoomFloors();
			$this->data['manual_categories'] = $this->settings_model->getParentCategories();
			$this->data['manual_units'] = $this->site->getAllBaseUnits();
            $this->data['currencies'] = $this->settings_model->getAllCurrencies();
            $this->data['date_formats'] = $this->settings_model->getDateFormats();
            $this->data['tax_rates'] = $this->settings_model->getAllTaxRates();
            $this->data['customer_groups'] = $this->settings_model->getAllCustomerGroups();
            $this->data['price_groups'] = $this->settings_model->getAllPriceGroups();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['projects'] = $this->site->getAllProjects();
			$this->data['payment_terms'] = $this->site->getAllPaymentTerms();
            $this->data['smtp_pass'] = $this->encrypt->decode($this->data['settings']->smtp_pass);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('system_settings')));
            $meta = array('page_title' => lang('system_settings'), 'bc' => $bc);
            $this->core_page('settings/index', $meta, $this->data);
        }
    }

    function paypal()
    {

        $this->form_validation->set_rules('active', $this->lang->line('activate'), 'trim');
        $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'trim|valid_email');
        if ($this->input->post('active')) {
            $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'required');
        }
        $this->form_validation->set_rules('fixed_charges', $this->lang->line('fixed_charges'), 'trim');
        $this->form_validation->set_rules('extra_charges_my', $this->lang->line('extra_charges_my'), 'trim');
        $this->form_validation->set_rules('extra_charges_other', $this->lang->line('extra_charges_others'), 'trim');

        if ($this->form_validation->run() == true) {

            $data = array('active' => $this->input->post('active'),
                'account_email' => $this->input->post('account_email'),
                'fixed_charges' => $this->input->post('fixed_charges'),
                'extra_charges_my' => $this->input->post('extra_charges_my'),
                'extra_charges_other' => $this->input->post('extra_charges_other')
            );
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatePaypal($data)) {
            $this->session->set_flashdata('message', $this->lang->line('paypal_setting_updated'));
            redirect("system_settings/paypal");
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['paypal'] = $this->settings_model->getPaypalSettings();

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('paypal_settings')));
            $meta = array('page_title' => lang('paypal_settings'), 'bc' => $bc);
            $this->core_page('settings/paypal', $meta, $this->data);
        }
    }

    function skrill()
    {

        $this->form_validation->set_rules('active', $this->lang->line('activate'), 'trim');
        $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'trim|valid_email');
        if ($this->input->post('active')) {
            $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'required');
        }
        $this->form_validation->set_rules('fixed_charges', $this->lang->line('fixed_charges'), 'trim');
        $this->form_validation->set_rules('extra_charges_my', $this->lang->line('extra_charges_my'), 'trim');
        $this->form_validation->set_rules('extra_charges_other', $this->lang->line('extra_charges_others'), 'trim');

        if ($this->form_validation->run() == true) {

            $data = array('active' => $this->input->post('active'),
                'account_email' => $this->input->post('account_email'),
                'fixed_charges' => $this->input->post('fixed_charges'),
                'extra_charges_my' => $this->input->post('extra_charges_my'),
                'extra_charges_other' => $this->input->post('extra_charges_other')
            );
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateSkrill($data)) {
            $this->session->set_flashdata('message', $this->lang->line('skrill_setting_updated'));
            redirect("system_settings/skrill");
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['skrill'] = $this->settings_model->getSkrillSettings();

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('skrill_settings')));
            $meta = array('page_title' => lang('skrill_settings'), 'bc' => $bc);
            $this->core_page('settings/skrill', $meta, $this->data);
        }
    }

    function change_logo()
    {
        // if (DEMO) {
            // $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            // $this->cus->md();
        // }
        $this->load->helper('security');
        $this->form_validation->set_rules('site_logo', lang("site_logo"), 'xss_clean');
        $this->form_validation->set_rules('login_logo', lang("login_logo"), 'xss_clean');
        $this->form_validation->set_rules('biller_logo', lang("biller_logo"), 'xss_clean');
        if ($this->form_validation->run() == true) {

            if ($_FILES['site_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = 1000;
                $config['max_height'] = 1000;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('site_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $site_logo = $this->upload->file_name;
                $this->db->update('settings', array('logo' => $site_logo), array('setting_id' => 1));
            }

            if ($_FILES['login_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = 1000;
                $config['max_height'] = 1000;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('login_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $login_logo = $this->upload->file_name;
                $this->db->update('settings', array('logo2' => $login_logo), array('setting_id' => 1));
            }

            if ($_FILES['biller_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = 1000;
                $config['max_height'] = 1000;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('biller_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
            }

            $this->session->set_flashdata('message', lang('logo_uploaded'));
            redirect($_SERVER["HTTP_REFERER"]);

        } elseif ($this->input->post('upload_logo')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/change_logo', $this->data);
        }
    }

    public function write_index($timezone)
    {

        $template_path = './assets/config_dumps/index.php';
        $output_path = SELF;
        $index_file = file_get_contents($template_path);
        $new = str_replace("%TIMEZONE%", $timezone, $index_file);
        $handle = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new)) {
                @chmod($output_path, 0644);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function updates()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->form_validation->set_rules('purchase_code', lang("purchase_code"), 'required');
        $this->form_validation->set_rules('envato_username', lang("envato_username"), 'required');
        if ($this->form_validation->run() == true) {
            $this->db->update('settings', array('purchase_code' => $this->input->post('purchase_code', TRUE), 'envato_username' => $this->input->post('envato_username', TRUE)), array('setting_id' => 1));
            redirect('system_settings/updates');
        } else {
            $fields = array('version' => $this->Settings->version, 'code' => $this->Settings->purchase_code, 'username' => $this->Settings->envato_username, 'site' => base_url());
            $this->load->helper('update');
            $protocol = is_https() ? 'https://' : 'http://';
            $updates = get_remote_contents($protocol.'api.sunfixconsulting.com/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('updates')));
            $meta = array('page_title' => lang('updates'), 'bc' => $bc);
            $this->core_page('settings/updates', $meta, $this->data);
        }
    }

    function install_update($file, $m_version, $version)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->load->helper('update');
        save_remote_file($file . '.zip');
        $this->cus->unzip('./files/updates/' . $file . '.zip');
        if ($m_version) {
            $this->load->library('migration');
            if (!$this->migration->latest()) {
                $this->session->set_flashdata('error', $this->migration->error_string());
                redirect("system_settings/updates");
            }
        }
        $this->db->update('settings', array('version' => $version, 'update' => 0), array('setting_id' => 1));
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        redirect("system_settings/updates");
    }


	
	
	function backup_database(){
		if (!$this->Owner && !$this->Admin && !$this->GP['backups-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
		$backup = $this->settings_model->backupData();
		$db_name = 'db-backup-on-' . date("Y-m-d-H-i-s") . '.txt';
		$save = './files/backups/' . $db_name;
        $this->load->helper('file');
        write_file($save, $backup);
        $this->session->set_flashdata('messgae', lang('db_saved'));
        redirect("system_settings/backups");
	}

    /*function backup_database()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['backups-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->load->dbutil();
        $prefs = array(
            'format' => 'txt',
            'filename' => 'cus_db_backup.sql'
        );
        $back = $this->dbutil->backup($prefs);
        $backup =& $back;
        $db_name = 'db-backup-on-' . date("Y-m-d-H-i-s") . '.txt';
        $save = './files/backups/' . $db_name;
        $this->load->helper('file');
        write_file($save, $backup);
        $this->session->set_flashdata('messgae', lang('db_saved'));
        redirect("system_settings/backups");
    }*/

    function restore_database($dbfile)
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['backups-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $file = file_get_contents('./files/backups/' . $dbfile . '.txt');
        $this->db->conn_id->multi_query($file);
        $this->db->conn_id->close();
        redirect('logout/db');
    }

    function download_database($dbfile)
    {
         if (!$this->Owner && !$this->Admin && !$this->GP['backups-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->load->library('zip');
        $this->zip->read_file('./files/backups/' . $dbfile . '.txt');
        $name = $dbfile . '.zip';
        $this->zip->download($name);
        exit();
    }

    function download_backup($zipfile)
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['backups-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->load->helper('download');
        force_download('./files/backups/' . $zipfile . '.zip', NULL);
        exit();
    }

    function restore_backup($zipfile)
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['backups-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $file = './files/backups/' . $zipfile . '.zip';
        $this->cus->unzip($file, './');
        $this->session->set_flashdata('success', lang('files_restored'));
        redirect("system_settings/backups");
        exit();
    }

    function delete_database($dbfile)
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['backups-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        unlink('./files/backups/' . $dbfile . '.txt');
        $this->session->set_flashdata('messgae', lang('db_deleted'));
        redirect("system_settings/backups");
    }

    function delete_backup($zipfile)
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['backups-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        unlink('./files/backups/' . $zipfile . '.zip');
        $this->session->set_flashdata('messgae', lang('backup_deleted'));
        redirect("system_settings/backups");
    }

    function email_templates($template = "credentials")
    {

        $this->form_validation->set_rules('mail_body', lang('mail_message'), 'trim|required');
        $this->load->helper('file');
        $temp_path = is_dir('./themes/' . $this->theme . 'email_templates/');
        $theme = $temp_path ? $this->theme : 'default';
        if ($this->form_validation->run() == true) {
            $data = $_POST["mail_body"];
            if (write_file('./themes/' . $this->theme . 'email_templates/' . $template . '.html', $data)) {
                $this->session->set_flashdata('message', lang('message_successfully_saved'));
                redirect('system_settings/email_templates#' . $template);
            } else {
                $this->session->set_flashdata('error', lang('failed_to_save_message'));
                redirect('system_settings/email_templates#' . $template);
            }
        } else {

            $this->data['credentials'] = file_get_contents('./themes/' . $this->theme . 'email_templates/credentials.html');
            $this->data['sale'] = file_get_contents('./themes/' . $this->theme . 'email_templates/sale.html');
            $this->data['quote'] = file_get_contents('./themes/' . $this->theme . 'email_templates/quote.html');
            $this->data['purchase'] = file_get_contents('./themes/' . $this->theme . 'email_templates/purchase.html');
            $this->data['transfer'] = file_get_contents('./themes/' . $this->theme . 'email_templates/transfer.html');
            $this->data['payment'] = file_get_contents('./themes/' . $this->theme . 'email_templates/payment.html');
            $this->data['forgot_password'] = file_get_contents('./themes/' . $this->theme . 'email_templates/forgot_password.html');
            $this->data['activate_email'] = file_get_contents('./themes/' . $this->theme . 'email_templates/activate_email.html');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('email_templates')));
            $meta = array('page_title' => lang('email_templates'), 'bc' => $bc);
            $this->core_page('settings/email_templates', $meta, $this->data);
        }
    }

    function create_group()
    {

        $this->form_validation->set_rules('group_name', lang('group_name'), 'required|is_unique[groups.name]');

        if ($this->form_validation->run() == TRUE) {
            $data = array('name' => $this->input->post('group_name'), 'description' => $this->input->post('description'));
        } elseif ($this->input->post('create_group')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/user_groups");
        }

        if ($this->form_validation->run() == TRUE && ($new_group_id = $this->settings_model->addGroup($data))) {
            $this->session->set_flashdata('message', lang('group_added'));
            redirect("system_settings/permissions/" . $new_group_id);

        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['group_name'] = array(
                'name' => 'group_name',
                'id' => 'group_name',
                'type' => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('group_name'),
            );
            $this->data['description'] = array(
                'name' => 'description',
                'id' => 'description',
                'type' => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('description'),
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/create_group', $this->data);
        }
    }

    function edit_group($id)
    {

        if (!$id || empty($id)) {
            redirect('system_settings/user_groups');
        }

        $group = $this->settings_model->getGroupByID($id);

        $this->form_validation->set_rules('group_name', lang('group_name'), 'required');

		if ($this->input->post('group_name') != $group->name) {
            $this->form_validation->set_rules('group_name', lang("group_name"), 'is_unique[units.code]');
        }

        if ($this->form_validation->run() === TRUE) {
            $data = array(
                'name' => $this->input->post('group_name'), 
                'description' => $this->input->post('description'));
            $group_update = $this->settings_model->updateGroup($id, $data);

            if ($group_update) {
                $this->session->set_flashdata('message', lang('group_udpated'));
            } else {
                $this->session->set_flashdata('error', lang('attempt_failed'));
            }
            redirect("system_settings/user_groups");
        } else {


            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['group'] = $group;

            $this->data['group_name'] = array(
                'name' => 'group_name',
                'id' => 'group_name',
                'type' => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('group_name', $group->name),
            );
            $this->data['group_description'] = array(
                'name' => 'group_description',
                'id' => 'group_description',
                'type' => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('group_description', $group->description),
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_group', $this->data);
        }
    }

    function permissions($id = NULL)
    {

        $this->form_validation->set_rules('group', lang("group"), 'is_natural_no_zero');
        if ($this->form_validation->run() == true) {
            $data = array(
                'products-index' => $this->input->post('products-index'),
                'products-edit' => $this->input->post('products-edit'),
                'products-add' => $this->input->post('products-add'),
                'products-delete' => $this->input->post('products-delete'),
                'products-cost' => $this->input->post('products-cost'),
                'products-price' => $this->input->post('products-price'),
				'products-price_list' => $this->input->post('products-price_list'),
				'products-import' => $this->input->post('products-import'),
                'customers-index' => $this->input->post('customers-index'),
                'customers-edit' => $this->input->post('customers-edit'),
                'customers-add' => $this->input->post('customers-add'),
                'customers-delete' => $this->input->post('customers-delete'),
                'suppliers-index' => $this->input->post('suppliers-index'),
                'suppliers-edit' => $this->input->post('suppliers-edit'),
                'suppliers-add' => $this->input->post('suppliers-add'),
                'suppliers-delete' => $this->input->post('suppliers-delete'),
                'sales-index' => $this->input->post('sales-index'),
                'sales-edit' => $this->input->post('sales-edit'),
                'sales-add' => $this->input->post('sales-add'),
                'sales-delete' => $this->input->post('sales-delete'),
                'sales-email' => $this->input->post('sales-email'),
				'sales-import_sale' => $this->input->post('sales-import_sale'),
                'sales-pdf' => $this->input->post('sales-pdf'),
				'sales-assign_sales' => $this->input->post('sales-assign_sales'),
                'sales-deliveries' => $this->input->post('sales-deliveries'),
                'sales-edit_delivery' => $this->input->post('sales-edit_delivery'),
                'sales-add_delivery' => $this->input->post('sales-add_delivery'),
				'deliveries-add' => $this->input->post('sales-add_delivery'),
                'sales-delete_delivery' => $this->input->post('sales-delete_delivery'),
                'sales-email_delivery' => $this->input->post('sales-email_delivery'),
                'sales-pdf_delivery' => $this->input->post('sales-pdf_delivery'),
                'sales-gift_cards' => $this->input->post('sales-gift_cards'),
                'sales-edit_gift_card' => $this->input->post('sales-edit_gift_card'),
                'sales-add_gift_card' => $this->input->post('sales-add_gift_card'),
                'sales-delete_gift_card' => $this->input->post('sales-delete_gift_card'),

                'reports-agency_commission' => $this->input->post('reports-agency_commission'),
                'sales-agency_commission-index' => $this->input->post('sales-agency_commission-index'),
                'sales-agency_commission-edit' => $this->input->post('sales-agency_commission-edit'),
                'sales-agency_commission-add' => $this->input->post('sales-agency_commission-add'),
                'sales-agency_commission-delete' => $this->input->post('sales-agency_commission-delete'),
                'sales-agency_commission-date' => $this->input->post('sales-agency_commission-date'),

				'sales-fuel_sale-index' => $this->input->post('sales-fuel_sale-index'),
                'sales-fuel_sale-edit' => $this->input->post('sales-fuel_sale-edit'),
                'sales-fuel_sale-add' => $this->input->post('sales-fuel_sale-add'),
                'sales-fuel_sale-delete' => $this->input->post('sales-fuel_sale-delete'),
                'sales-fuel_sale-date' => $this->input->post('sales-fuel_sale-date'),
                
				'system_settings-vehicles' => $this->input->post('system_settings-vehicles'),
				'system_settings-tanks' => $this->input->post('system_settings-tanks'),
				'system_settings-frequencies' => $this->input->post('system_settings-frequencies'),

				'rentals-index' => $this->input->post('rentals-index'),
                'rentals-add' => $this->input->post('rentals-add'),
                'rentals-edit' => $this->input->post('rentals-edit'),
                'rentals-delete' => $this->input->post('rentals-delete'),
                'rentals-date' => $this->input->post('rentals-date'),
                'rentals-edit_price' => $this->input->post('rentals-edit_price'),
				'rentals-rooms' => $this->input->post('rentals-rooms'),
                'rentals-floors' => $this->input->post('rentals-floors'),
                'rentals-services' => $this->input->post('rentals-services'),

                'quotes-index' => $this->input->post('quotes-index'),
                'quotes-edit' => $this->input->post('quotes-edit'),
                'quotes-add' => $this->input->post('quotes-add'),
                'quotes-delete' => $this->input->post('quotes-delete'),
                'quotes-email' => $this->input->post('quotes-email'),
                'quotes-pdf' => $this->input->post('quotes-pdf'),
				'sale_orders-index' => $this->input->post('sale_orders-index'),
                'sale_orders-edit' => $this->input->post('sale_orders-edit'),
                'sale_orders-add' => $this->input->post('sale_orders-add'),
                'sale_orders-delete' => $this->input->post('sale_orders-delete'),
                'sale_orders-pdf' => $this->input->post('sale_orders-pdf'),
				'approve_sale_orders' => $this->input->post('approve_sale_orders'),
                'purchases-index' => $this->input->post('purchases-index'),
                'purchases-edit' => $this->input->post('purchases-edit'),
                'purchases-add' => $this->input->post('purchases-add'),
                'purchases-delete' => $this->input->post('purchases-delete'),
                'purchases-email' => $this->input->post('purchases-email'),
                'purchases-pdf' => $this->input->post('purchases-pdf'),
				'purchases-import_purchase' => $this->input->post('purchases-import_purchase'),
				'purchases-receives' => $this->input->post('purchases-receives'),
				'purchases-add_receive' => $this->input->post('purchases-add_receive'),
				'purchases-edit_receive' => $this->input->post('purchases-edit_receive'),
				'purchases-delete_receive' => $this->input->post('purchases-delete_receive'),
				'purchases-receive_date' => $this->input->post('purchases-receive_date'),
				'purchase_requests-index' => $this->input->post('purchase_requests-index'),
                'purchase_requests-edit' => $this->input->post('purchase_requests-edit'),
                'purchase_requests-add' => $this->input->post('purchase_requests-add'),
                'purchase_requests-delete' => $this->input->post('purchase_requests-delete'),
                'purchase_requests-pdf' => $this->input->post('purchase_requests-pdf'),
				'approve_purchase_requests' => $this->input->post('approve_purchase_requests'),
				'purchase_orders-index' => $this->input->post('purchase_orders-index'),
                'purchase_orders-edit' => $this->input->post('purchase_orders-edit'),
                'purchase_orders-add' => $this->input->post('purchase_orders-add'),
                'purchase_orders-delete' => $this->input->post('purchase_orders-delete'),
                'purchase_orders-pdf' => $this->input->post('purchase_orders-pdf'),
				'approve_purchase_orders' => $this->input->post('approve_purchase_orders'),
                'transfers-index' => $this->input->post('transfers-index'),
                'transfers-edit' => $this->input->post('transfers-edit'),
                'transfers-add' => $this->input->post('transfers-add'),
                'transfers-delete' => $this->input->post('transfers-delete'),
                'transfers-email' => $this->input->post('transfers-email'),
                'transfers-pdf' => $this->input->post('transfers-pdf'),
                'sales-return_sales' => $this->input->post('sales-return_sales'),
				'pawns-index' => $this->input->post('pawns-index'),
                'pawns-edit' => $this->input->post('pawns-edit'),
                'pawns-add' => $this->input->post('pawns-add'),
                'pawns-delete' => $this->input->post('pawns-delete'),
                'pawns-payments' => $this->input->post('pawns-payments'),
                'pawns-date' => $this->input->post('pawns-date'),
				'pawns-closes' => $this->input->post('pawns-closes'),
				'pawns-returns' => $this->input->post('pawns-returns'),
				'pawns-purchases' => $this->input->post('pawns-purchases'),
				'pawns-products' => $this->input->post('pawns-products'),
				'installments-index' => $this->input->post('installments-index'),
                'installments-edit' => $this->input->post('installments-edit'),
                'installments-add' => $this->input->post('installments-add'),
                'installments-delete' => $this->input->post('installments-delete'),
                'installments-payments' => $this->input->post('installments-payments'),
                'installments-date' => $this->input->post('installments-date'),
				'installments-inactive' => $this->input->post('installments-inactive'),
				'installments-payoff' => $this->input->post('installments-payoff'),
				'loans-index' => $this->input->post('loans-index'),
				'loans-date' => $this->input->post('loans-date'),
				'loans-schedule-add' => $this->input->post('loans-schedule-add'),
				'loans-schedule-edit' => $this->input->post('loans-schedule-edit'),
				'loans-payment-schedule' => $this->input->post('loans-payment-schedule'),
				'loans-charges' => $this->input->post('loans-charges'),
                'loans-payments' => $this->input->post('loans-payments'),
				'loans-payoff' => $this->input->post('loans-payoff'),
				'loans-borrowers' => $this->input->post('loans-borrowers'),
				'loans-borrower_types' => $this->input->post('loans-borrower_types'),
				'loans-loan_products' => $this->input->post('loans-loan_products'),
				'loans-collaterals' => $this->input->post('loans-collaterals'),
				'loans-guarantors' => $this->input->post('loans-guarantors'),
				'loans-working_status' => $this->input->post('loans-working_status'),

				'loans-applications-index' => $this->input->post('loans-applications-index'),
				'loans-applications-add' => $this->input->post('loans-applications-add'),
				'loans-applications-edit' => $this->input->post('loans-applications-edit'),
				'loans-applications-delete' => $this->input->post('loans-applications-delete'),
				'loans-applications-date' => $this->input->post('loans-applications-date'),
				'loans-applications-approve' => $this->input->post('loans-applications-approve'),
				'loans-applications-decline' => $this->input->post('loans-applications-decline'),
				'loans-applications-disburse' => $this->input->post('loans-applications-disburse'),

				'savings-index' => $this->input->post('savings-index'),
				'savings-add' => $this->input->post('savings-add'),
				'savings-edit' => $this->input->post('savings-edit'),
				'savings-delete' => $this->input->post('savings-delete'),
				'savings-date' => $this->input->post('savings-date'),
				'savings-add_deposit' => $this->input->post('savings-add_deposit'),
				'savings-add_withdraw' => $this->input->post('savings-add_withdraw'),
				'savings-add_transfer' => $this->input->post('savings-add_transfer'),
				'savings-saving_products' => $this->input->post('savings-saving_products'),

                'reports-warehouse_stock' => $this->input->post('reports-warehouse_stock'),
                'reports-best_sellers' => $this->input->post('reports-best_sellers'),
                'reports-quantity_alerts' => $this->input->post('reports-quantity_alerts'),
                'reports-expiry_alerts' => $this->input->post('reports-expiry_alerts'),
				'reports-product_license_alerts' => $this->input->post('reports-product_license_alerts'),
                'reports-brands' => $this->input->post('reports-brands'),
                'reports-categories' => $this->input->post('reports-categories'),
				'reports-categories_chart' => $this->input->post('reports-categories_chart'),
                'reports-products' => $this->input->post('reports-products'),
                'reports-inventory_in_out' => $this->input->post('reports-inventory_in_out'),
				'reports-inventory_valuation_report' => $this->input->post('reports-inventory_valuation_report'),
                'reports-adjustments' => $this->input->post('reports-adjustments'),
				'reports-cost_adjustments' => $this->input->post('reports-cost_adjustments'),
                'reports-product_sales_report' => $this->input->post('reports-product_sales_report'),
				'reports-product_purchases_report' => $this->input->post('reports-product_purchases_report'),
				'reports-products_promotion_report' => $this->input->post('reports-products_promotion_report'),
				'reports-register' => $this->input->post('reports-register'),
                'reports-saleman_report' => $this->input->post('reports-saleman_report'),
                'reports-daily_sales' => $this->input->post('reports-daily_sales'),
				'reports-monthly_sales' => $this->input->post('reports-monthly_sales'),
				'reports-ar_aging' => $this->input->post('reports-ar_aging'),
				'reports-ap_aging' => $this->input->post('reports-ap_aging'),
				'reports-ar_customer' => $this->input->post('reports-ar_customer'),
				'reports-ap_supplier' => $this->input->post('reports-ap_supplier'),
				'reports-sales' => $this->input->post('reports-sales'),
				'reports-sales_detail' => $this->input->post('reports-sales_detail'),
				'reports-daily_purchases' => $this->input->post('reports-daily_purchases'),
				'reports-monthly_purchases' => $this->input->post('reports-monthly_purchases'),
				'reports-purchases' => $this->input->post('reports-purchases'),
				'reports-purchases_detail' => $this->input->post('reports-purchases_detail'),
				'reports-expenses' => $this->input->post('reports-expenses'),
				'reports-payments' => $this->input->post('reports-payments'),
				'reports-profit_loss' => $this->input->post('reports-profit_loss'),
				'reports-customers' => $this->input->post('reports-customers'),
				'reports-suppliers' => $this->input->post('reports-suppliers'),
				'reports-saleman_detail_report' => $this->input->post('reports-saleman_detail_report'),
				'reports-users' => $this->input->post('reports-users'),
				'reports-loans' => $this->input->post('reports-loans'),
				'reports-loan_collection' => $this->input->post('reports-loan_collection'),
				'reports-loan_disbursement' => $this->input->post('reports-loan_disbursement'),
				'reports-installments' => $this->input->post('reports-installments'),
				'reports-installment_products' => $this->input->post('reports-installment_products'),
				'reports-installment_payments' => $this->input->post('reports-installment_payments'),
				'reports-pawn' => $this->input->post('reports-pawn'),
				'reports-print_history' => $this->input->post('reports-print_history'),
				'reports-product_variants' => $this->input->post('reports-variants'),
				'reports-audit_trails' => $this->input->post('reports-audit_trails'),
				'reports-customer_stocks' => $this->input->post('reports-customer_stocks'),
				'reports-saleman_products' => $this->input->post('reports-saleman_products'),
				'reports-transfers' => $this->input->post('reports-transfers'),
				'reports-using_stocks' => $this->input->post('reports-using_stocks'),
				'reports-bill_details' => $this->input->post('reports-bill_details'),
				'reports-product_serial_report' => $this->input->post('reports-product_serial_report'),
				'reports-product_monthly_sale' => $this->input->post('reports-product_monthly_sale'),
				'reports-product_yearly_sale' => $this->input->post('reports-product_yearly_sale'),
				'reports-fuel_sales' => $this->input->post('reports-fuel_sales'),
				'reports-tanks' => $this->input->post('reports-tanks'),
				'reports-deliveries' => $this->input->post('reports-deliveries'),
				'reports-products_free_report' => $this->input->post('reports-products_free_report'),
				'reports-fuel_customers_report' => $this->input->post('reports-fuel_customers_report'),

                'sales-payments' => $this->input->post('sales-payments'),
                'purchases-payments' => $this->input->post('purchases-payments'),
                'purchases-expenses' => $this->input->post('purchases-expenses'),
				'purchases-expenses-add' => $this->input->post('purchases-expenses-add'),
				'purchases-expenses-edit' => $this->input->post('purchases-expenses-edit'),
				'purchases-expenses-delete' => $this->input->post('purchases-expenses-delete'),
				'purchases-expenses-date' => $this->input->post('purchases-expenses-date'),
				'purchases-approve_expense' => $this->input->post('purchases-approve_expense'),
                'products-adjustments' => $this->input->post('products-adjustments'),
				'products-cost_adjustments' => $this->input->post('products-cost_adjustments'),
                'bulk_actions' => $this->input->post('bulk_actions'),
                'reference_no' => $this->input->post('reference_no'),
				'customers-deposits' => $this->input->post('customers-deposits'),
                'customers-delete_deposit' => $this->input->post('customers-delete_deposit'),
				'suppliers-deposits' => $this->input->post('suppliers-deposits'),
                'suppliers-delete_deposit' => $this->input->post('suppliers-delete_deposit'),
                'products-barcode' => $this->input->post('products-barcode'),
				'products-serial' => $this->input->post('products-serial'),
                'purchases-return_purchases' => $this->input->post('purchases-return_purchases'),

                'products-stock_count' => $this->input->post('products-stock_count'),
				'products-using_stocks' => $this->input->post('products-using_stocks'),

                'edit_price' => $this->input->post('edit_price'),
				'system_settings' => $this->input->post('system_settings'),
				'pos_settings' => $this->input->post('pos_settings'),
				'change_logo' => $this->input->post('change_logo'),
				'billers-index' => $this->input->post('billers-index'),
				'warehouses-index' => $this->input->post('warehouses-index'),
				'areas-index' => $this->input->post('areas-index'),
				'projects-index' => $this->input->post('projects-index'),
				'expense_categories-index' => $this->input->post('expense_categories-index'),
				'categories-index' => $this->input->post('categories-index'),
				'tables-index' => $this->input->post('tables-index'),
				'units-index' => $this->input->post('units-index'),
				'brands-index' => $this->input->post('brands-index'),
				'variants-index' => $this->input->post('variants-index'),
				'system_settings-boms' => $this->input->post('system_settings-boms'),
				'customer_groups-index' => $this->input->post('customer_groups-index'),
				'price_groups-index' => $this->input->post('price_groups-index'),
				'customer_price-index' => $this->input->post('customer_price-index'),
				'payment_terms-index' => $this->input->post('payment_terms-index'),
				'saleman_targets-index' => $this->input->post('saleman_targets-index'),
				'currencies-index' => $this->input->post('currencies-index'),
				'system_settings-inventory_opening_balances' => $this->input->post('system_settings-inventory_opening_balances'),
				'system_settings-product_promotions' => $this->input->post('system_settings-product_promotions'),
				'customer_opening_balances-index' => $this->input->post('customer_opening_balances-index'),
				'supplier_opening_balances-index' => $this->input->post('supplier_opening_balances-index'),
				'tax_rates-index' => $this->input->post('tax_rates-index'),
				'list_printers-index' => $this->input->post('list_printers-index'),
				'email_templates-index' => $this->input->post('email_templates-index'),
				'group_permissions-index' => $this->input->post('group_permissions-index'),
				'backups-index' => $this->input->post('backups-index'),
				'system_settings-salesman_groups' => $this->input->post('system_settings-salesman_groups'),
				'system_settings-cash_account' => $this->input->post('system_settings-cash_account'),

				'products-date' => $this->input->post('products-date'),
				'transfers-date' => $this->input->post('transfers-date'),
				'quotes-date' => $this->input->post('quotes-date'),
				'sale_orders-date' => $this->input->post('sale_orders-date'),
				'sales-date' => $this->input->post('sales-date'),
				'sales-date_delivery' => $this->input->post('sales-date_delivery'),
				'purchase_requests-date' => $this->input->post('purchase_requests-date'),
				'purchase_orders-date' => $this->input->post('purchase_orders-date'),
				'purchases-date' => $this->input->post('purchases-date'),
				'pos-show_items' => $this->input->post('pos-show_items'),
				'pos-print_bill' => $this->input->post('pos-print_bill'),
				'pos-move_table' => $this->input->post('pos-move_table'),
				'pos-delete_table' => $this->input->post('pos-delete_table'),
				'pos-delete_order' => $this->input->post('pos-delete_order'),
				'pos-return_order' => $this->input->post('pos-return_order'),
				'pos-customer_stock' => $this->input->post('pos-customer_stock'),
				'po-num_alerts' => $this->input->post('po-num_alerts'),
				'so-num_alerts' => $this->input->post('so-num_alerts'),
				'accountings-index' => $this->input->post('accountings-index'),
				'accountings-add' => $this->input->post('accountings-add'),
				'accountings-edit' => $this->input->post('accountings-edit'),
				'accountings-delete' => $this->input->post('accountings-delete'),
				'accountings-enter_journals' => $this->input->post('accountings-enter_journals'),
				'accountings-journals' => $this->input->post('accountings-journals'),
				'accountings-general_ledger' => $this->input->post('accountings-general_ledger'),
				'accountings-cash_books' => $this->input->post('accountings-cash_books'),
				'accountings-trial_balance' => $this->input->post('accountings-trial_balance'),
				'accountings-balance_sheet' => $this->input->post('accountings-balance_sheet'),
				'accountings-income_statement' => $this->input->post('accountings-income_statement'),
				'accountings-cash_flow' => $this->input->post('accountings-cash_flow'),
				'accountings-enter_journals-add' => $this->input->post('accountings-enter_journals-add'),
				'accountings-enter_journals-edit' => $this->input->post('accountings-enter_journals-edit'),
				'accountings-enter_journals-delete' => $this->input->post('accountings-enter_journals-delete'),
				'accountings-enter_journals-date' => $this->input->post('accountings-enter_journals-date'),

				'accountings-bank_reconciliation-add' => $this->input->post('accountings-bank_reconciliation-add'),
				'accountings-bank_reconciliation-edit' => $this->input->post('accountings-bank_reconciliation-edit'),
				'accountings-bank_reconciliation-delete' => $this->input->post('accountings-bank_reconciliation-delete'),
				'accountings-bank_reconciliation-date' => $this->input->post('accountings-bank_reconciliation-date'),
				'accountings-bank_reconciliations' => $this->input->post('accountings-bank_reconciliations'),

				'auth-index' => $this->input->post('auth-index'),
                'auth-edit' => $this->input->post('auth-edit'),
                'auth-add' => $this->input->post('auth-add'),
                'auth-delete' => $this->input->post('auth-delete'),
				'auth-saleman' => $this->input->post('auth-saleman'),
                'auth-saleman-edit' => $this->input->post('auth-saleman-edit'),
                'auth-saleman-add' => $this->input->post('auth-saleman-add'),
                'auth-saleman-delete' => $this->input->post('auth-saleman-delete'),

				'auth-agency' => $this->input->post('auth-agency'),
                'auth-agency-edit' => $this->input->post('auth-agency-edit'),
                'auth-agency-add' => $this->input->post('auth-agency-add'),
                'auth-agency-delete' => $this->input->post('auth-agency-delete'),

				'unlimited-print' => $this->input->post('unlimited-print'),
                'customers-send_sms' => $this->input->post('customers-send_sms'),
				'customers-print_card' => $this->input->post('customers-print_card'),
				'settings' => $this->input->post('settings'),
				'constructions-index-constructor' => $this->input->post('constructions-index-constructor'),
				'constructions-add-constructor' => $this->input->post('constructions-add-constructor'),
				'constructions-edit-constructor' => $this->input->post('constructions-edit-constructor'),
				'constructions-delete-constructor' => $this->input->post('constructions-delete-constructor'),

				'consignments-date' => $this->input->post('consignments-date'),
				'products-consignments' => $this->input->post('products-consignments'),
                'products-add_consignment' => $this->input->post('products-add_consignment'),
                'products-edit_consignment' => $this->input->post('products-edit_consignment'),
                'products-delete_consignment' => $this->input->post('products-delete_consignment'),
				'reports-consignments' => $this->input->post('reports-consignments'),
                'reports-consignment_details' => $this->input->post('reports-consignment_details'),

				'repairs-index' => $this->input->post('repairs-index'),
				'repairs-add' => $this->input->post('repairs-add'),
				'repairs-edit' => $this->input->post('repairs-edit'),
				'repairs-delete' => $this->input->post('repairs-delete'),
				'repairs-date' => $this->input->post('repairs-date'),
				'repairs-pdf' => $this->input->post('repairs-pdf'),
                'repairs-update_status' => $this->input->post('repairs-update_status'),
				'repairs-view_status' => $this->input->post('repairs-view_status'),
				'repairs-items' => $this->input->post('repairs-items'),
                'repairs-problems' => $this->input->post('repairs-problems'),

                'repairs-checks' => $this->input->post('repairs-checks'),
				'repairs-add_check' => $this->input->post('repairs-add_check'),
				'repairs-edit_check' => $this->input->post('repairs-edit_check'),
				'repairs-delete_check' => $this->input->post('repairs-delete_check'),
				'repairs-date_check' => $this->input->post('repairs-date_check'),
				'repairs-diagnostics' => $this->input->post('repairs-diagnostics'),
				'repairs-machine_types' => $this->input->post('repairs-machine_types'),

				'sales-member_cards' => $this->input->post('sales-member_cards'),
                'sales-edit_member_card' => $this->input->post('sales-edit_member_card'),
                'sales-add_member_card' => $this->input->post('sales-add_member_card'),
                'sales-delete_member_card' => $this->input->post('sales-delete_member_card'),
                'system_settings-models' => $this->input->post('system_settings-models'),
				'products-converts' => $this->input->post('products-converts'),
				'products-converts-add' => $this->input->post('products-converts-add'),
				'products-converts-edit' => $this->input->post('products-converts-edit'),
				'products-converts-delete' => $this->input->post('products-converts-delete'),
				'products-converts-date' => $this->input->post('products-converts-date'),
				'products-adjustments-add' => $this->input->post('products-adjustments-add'),
				'products-adjustments-edit' => $this->input->post('products-adjustments-edit'),
				'products-adjustments-delete' => $this->input->post('products-adjustments-delete'),
				'products-adjustments-date' => $this->input->post('products-adjustments-date'),
				'products-using_stocks-add' => $this->input->post('products-using_stocks-add'),
				'products-using_stocks-edit' => $this->input->post('products-using_stocks-edit'),
				'products-using_stocks-delete' => $this->input->post('products-using_stocks-delete'),
				'products-using_stocks-date' => $this->input->post('products-using_stocks-date'),
				'products-cost_adjustments-add' => $this->input->post('products-cost_adjustments-add'),
				'products-cost_adjustments-edit' => $this->input->post('products-cost_adjustments-edit'),
				'products-cost_adjustments-delete' => $this->input->post('products-cost_adjustments-delete'),
				'products-cost_adjustments-date' => $this->input->post('products-cost_adjustments-date'),
				'reports-daily_rentals' => $this->input->post('reports-daily_rentals'),
				'reports-rentals' => $this->input->post('reports-rentals'),
				'reports-rental_details' => $this->input->post('reports-rental_details'),
                'products-approve_adjustment' => $this->input->post('products-approve_adjustment'),
                'reports-repairs' => $this->input->post('reports-repairs'),
				'system_settings-date' => $this->input->post('system_settings-date'),
				'reports-receive_items_report' => $this->input->post('reports-receive_items_report')
            );
			
			if($this->config->item('saleman_commission')){
				$data["sales-salesman_commissions"] = $this->input->post('sales-salesman_commissions');
				$data["sales-add_salesman_commission"] = $this->input->post('sales-add_salesman_commission');
				$data["sales-edit_salesman_commission"] = $this->input->post('sales-edit_salesman_commission');
				$data["sales-delete_salesman_commission"] = $this->input->post('sales-delete_salesman_commission');
				$data["sales-salesman_commission-date"] = $this->input->post('sales-salesman_commission-date');
				$data["reports-salesman_commission_report"] = $this->input->post('reports-salesman_commission_report');
			}
			if($this->config->item('receive_payment')){
				$data["sales-receive_payments"] = $this->input->post('sales-receive_payments');
				$data["sales-add_receive_payment"] = $this->input->post('sales-add_receive_payment');
				$data["sales-edit_receive_payment"] = $this->input->post('sales-edit_receive_payment');
				$data["sales-delete_receive_payment"] = $this->input->post('sales-delete_receive_payment');
				$data["sales-receive_payments-date"] = $this->input->post('sales-receive_payments-date');
			}
			
			if($this->config->item('hr')){
				$data["hr-index"] = $this->input->post('hr-index');
				$data["hr-add"] = $this->input->post('hr-add');
				$data["hr-edit"] = $this->input->post('hr-edit');
				$data["hr-delete"] = $this->input->post('hr-delete');
				$data["hr-departments"] = $this->input->post('hr-departments');
				$data["hr-positions"] = $this->input->post('hr-positions');
				$data["hr-groups"] = $this->input->post('hr-groups');
				$data["hr-employee_types"] = $this->input->post('hr-employee_types');
				$data["hr-employees_relationships"] = $this->input->post('hr-employees_relationships');
				$data["hr-tax_conditions"] = $this->input->post('hr-tax_conditions');
				$data["hr-leave_types"] = $this->input->post('hr-leave_types');
				$data["hr-kpi_types"] = $this->input->post('hr-kpi_types');
				$data["hr-kpi_index"] = $this->input->post('hr-kpi_index');
				$data["hr-kpi_add"] = $this->input->post('hr-kpi_add');
				$data["hr-kpi_edit"] = $this->input->post('hr-kpi_edit');
				$data["hr-kpi_delete"] = $this->input->post('hr-kpi_delete');
				$data["hr-kpi_report"] = $this->input->post('hr-kpi_report');
				$data["hr-employees_report"] = $this->input->post('hr-employees_report');
				$data["hr-banks_report"] = $this->input->post('hr-banks_report');
				
				$data["hr-sample_id_cards"] = $this->input->post('hr-sample_id_cards');
				$data["hr-id_cards"] = $this->input->post('hr-id_cards');
				$data["hr-id_cards_date"] = $this->input->post('hr-id_cards_date');
				$data["hr-add_id_card"] = $this->input->post('hr-add_id_card');
				$data["hr-edit_id_card"] = $this->input->post('hr-edit_id_card');
				$data["hr-delete_id_card"] = $this->input->post('hr-delete_id_card');
				$data["hr-approve_id_card"] = $this->input->post('hr-approve_id_card');
				$data["hr-id_cards_report"] = $this->input->post('hr-id_cards_report');
				
				
				$data["hr-salary_reviews"] = $this->input->post('hr-salary_reviews');
				$data["hr-add_salary_review"] = $this->input->post('hr-add_salary_review');
				$data["hr-edit_salary_review"] = $this->input->post('hr-edit_salary_review');
				$data["hr-delete_salary_review"] = $this->input->post('hr-delete_salary_review');
				$data["hr-approve_salary_review"] = $this->input->post('hr-approve_salary_review');
				$data["hr-salary_reviews_report"] = $this->input->post('hr-salary_reviews_report');
				$data["hr-salary_reviews_date"] = $this->input->post('hr-salary_reviews_date');
			
			}
			if($this->config->item('attendance')){
				$data["attendances-check_in_outs"] = $this->input->post('attendances-check_in_outs');
				$data["attendances-add_check_in_out"] = $this->input->post('attendances-add_check_in_out');
				$data["attendances-edit_check_in_out"] = $this->input->post('attendances-edit_check_in_out');
				$data["attendances-delete_check_in_out"] = $this->input->post('attendances-delete_check_in_out');
				$data["attendances-generate_attendances"] = $this->input->post('attendances-generate_attendances');
				$data["attendances-take_leaves"] = $this->input->post('attendances-take_leaves');
				$data["attendances-approve_attendances"] = $this->input->post('attendances-approve_attendances');
				$data["attendances-cancel_attendances"] = $this->input->post('attendances-cancel_attendances');
				$data["attendances-approve_ot"] = $this->input->post('attendances-approve_ot');
				$data["attendances-policies"] = $this->input->post('attendances-policies');
				$data["attendances-ot_policies"] = $this->input->post('attendances-ot_policies');
				$data["attendances-list_devices"] = $this->input->post('attendances-list_devices');
				$data["attendances-check_in_out_report"] = $this->input->post('attendances-check_in_out_report');
				$data["attendances-daily_attendance_report"] = $this->input->post('attendances-daily_attendance_report');
				$data["attendances-montly_attendance_report"] = $this->input->post('attendances-montly_attendance_report');
				$data["attendances-attendance_department_report"] = $this->input->post('attendances-attendance_department_report');
				$data["attendances-employee_leave_report"] = $this->input->post('attendances-employee_leave_report');
				$data["attendances-approve_take_leave"] = $this->input->post('attendances-approve_take_leave');
				$data["attendances-date"] = $this->input->post('attendances-date');
			}	
			if($this->config->item('payroll')){
				$data["payrolls-cash_advances"] = $this->input->post('payrolls-cash_advances');
				$data["payrolls-add_cash_advance"] = $this->input->post('payrolls-add_cash_advance');
				$data["payrolls-edit_cash_advance"] = $this->input->post('payrolls-edit_cash_advance');
				$data["payrolls-delete_cash_advance"] = $this->input->post('payrolls-delete_cash_advance');
				$data["payrolls-approve_cash_advance"] = $this->input->post('payrolls-approve_cash_advance');
				$data["payrolls-payback"] = $this->input->post('payrolls-payback');
				$data["payrolls-cash_advances_date"] = $this->input->post('payrolls-cash_advances_date');
				$data["payrolls-cash_advances_report"] = $this->input->post('payrolls-cash_advances_report');
				$data["payrolls-benefits"] = $this->input->post('payrolls-benefits');
				$data["payrolls-add_benefit"] = $this->input->post('payrolls-add_benefit');
				$data["payrolls-edit_benefit"] = $this->input->post('payrolls-edit_benefit');
				$data["payrolls-delete_benefit"] = $this->input->post('payrolls-delete_benefit');
				$data["payrolls-approve_benefit"] = $this->input->post('payrolls-approve_benefit');
				$data["payrolls-additions"] = $this->input->post('payrolls-additions');
				$data["payrolls-deductions"] = $this->input->post('payrolls-deductions');
				$data["payrolls-benefits_date"] = $this->input->post('payrolls-benefits_date');
				$data["payrolls-benefits_report"] = $this->input->post('payrolls-benefits_report');
				$data["payrolls-benefit_details_report"] = $this->input->post('payrolls-benefit_details_report');
				$data["payrolls-salaries"] = $this->input->post('payrolls-salaries');
				$data["payrolls-add_salary"] = $this->input->post('payrolls-add_salary');
				$data["payrolls-edit_salary"] = $this->input->post('payrolls-edit_salary');
				$data["payrolls-delete_salary"] = $this->input->post('payrolls-delete_salary');
				$data["payrolls-approve_salary"] = $this->input->post('payrolls-approve_salary');
				$data["payrolls-salaries_date"] = $this->input->post('payrolls-salaries_date');
				$data["payrolls-salaries_report"] = $this->input->post('payrolls-salaries_report');
				$data["payrolls-salary_details_report"] = $this->input->post('payrolls-salary_details_report');
				$data["payrolls-salary_banks_report"] = $this->input->post('payrolls-salary_banks_report');
				$data["payrolls-payslips_report"] = $this->input->post('payrolls-payslips_report');
				$data["payrolls-payments"] = $this->input->post('payrolls-payments');
				$data["payrolls-add_payment"] = $this->input->post('payrolls-add_payment');
				$data["payrolls-edit_payment"] = $this->input->post('payrolls-edit_payment');
				$data["payrolls-delete_payment"] = $this->input->post('payrolls-delete_payment');
				$data["payrolls-payments_date"] = $this->input->post('payrolls-payments_date');
				$data["payrolls-payments_report"] = $this->input->post('payrolls-payments_report');
				$data["payrolls-payment_details_report"] = $this->input->post('payrolls-payment_details_report');
				
			}
			if($this->config->item('schools')){
				$data["schools-index"] = $this->input->post('schools-index');
				$data["schools-add"] = $this->input->post('schools-add');
				$data["schools-edit"] = $this->input->post('schools-edit');
				$data["schools-delete"] = $this->input->post('schools-delete');
				$data["schools-teachers"] = $this->input->post('schools-teachers');
				$data["schools-teachers-add"] = $this->input->post('schools-teachers-add');
				$data["schools-teachers-edit"] = $this->input->post('schools-teachers-edit');
				$data["schools-teachers-delete"] = $this->input->post('schools-teachers-delete');
				$data["schools-skills"] = $this->input->post('schools-skills');
				$data["schools-skills-add"] = $this->input->post('schools-skills-add');
				$data["schools-skills-edit"] = $this->input->post('schools-skills-edit');
				$data["schools-skills-delete"] = $this->input->post('schools-skills-delete');
				$data["schools-sections"] = $this->input->post('schools-sections');
				$data["schools-sections-add"] = $this->input->post('schools-sections-add');
				$data["schools-sections-edit"] = $this->input->post('schools-sections-edit');
				$data["schools-sections-delete"] = $this->input->post('schools-sections-delete');
				$data["schools-levels"] = $this->input->post('schools-levels');
				$data["schools-levels-add"] = $this->input->post('schools-levels-add');
				$data["schools-levels-edit"] = $this->input->post('schools-levels-edit');
				$data["schools-levels-delete"] = $this->input->post('schools-levels-delete');
				$data["schools-rooms"] = $this->input->post('schools-rooms');
				$data["schools-rooms-add"] = $this->input->post('schools-rooms-add');
				$data["schools-rooms-edit"] = $this->input->post('schools-rooms-edit');
				$data["schools-rooms-delete"] = $this->input->post('schools-rooms-delete');
				$data["schools-subjects"] = $this->input->post('schools-subjects');
				$data["schools-subjects-add"] = $this->input->post('schools-subjects-add');
				$data["schools-subjects-edit"] = $this->input->post('schools-subjects-edit');
				$data["schools-subjects-delete"] = $this->input->post('schools-subjects-delete');
				$data["schools-credit_scores"] = $this->input->post('schools-credit_scores');
				$data["schools-credit_scores-add"] = $this->input->post('schools-credit_scores-add');
				$data["schools-credit_scores-edit"] = $this->input->post('schools-credit_scores-edit');
				$data["schools-credit_scores-delete"] = $this->input->post('schools-credit_scores-delete');
				$data["schools-classes"] = $this->input->post('schools-classes');
				$data["schools-classes-add"] = $this->input->post('schools-classes-add');
				$data["schools-classes-edit"] = $this->input->post('schools-classes-edit');
				$data["schools-classes-delete"] = $this->input->post('schools-classes-delete');
				$data["schools-time_tables"] = $this->input->post('schools-time_tables');
				$data["schools-time_tables-add"] = $this->input->post('schools-time_tables-add');
				$data["schools-time_tables-edit"] = $this->input->post('schools-time_tables-edit');
				$data["schools-time_tables-delete"] = $this->input->post('schools-time_tables-delete');
				$data["schools-class_years"] = $this->input->post('schools-class_years');
				$data["schools-class_years-add"] = $this->input->post('schools-class_years-add');
				$data["schools-class_years-edit"] = $this->input->post('schools-class_years-edit');
				$data["schools-class_years-delete"] = $this->input->post('schools-class_years-delete');
				$data["schools-examinations"] = $this->input->post('schools-examinations');
				$data["schools-examinations-add"] = $this->input->post('schools-examinations-add');
				$data["schools-examinations-edit"] = $this->input->post('schools-examinations-edit');
				$data["schools-examinations-delete"] = $this->input->post('schools-examinations-delete');
				$data["schools-attendances"] = $this->input->post('schools-attendances');
				$data["schools-attendances-add"] = $this->input->post('schools-attendances-add');
				$data["schools-attendances-edit"] = $this->input->post('schools-attendances-edit');
				$data["schools-attendances-delete"] = $this->input->post('schools-attendances-delete');
				$data["schools-teacher_attendances"] = $this->input->post('schools-teacher_attendances');
				$data["schools-teacher_attendances-add"] = $this->input->post('schools-teacher_attendances-add');
				$data["schools-teacher_attendances-edit"] = $this->input->post('schools-teacher_attendances-edit');
				$data["schools-teacher_attendances-delete"] = $this->input->post('schools-teacher_attendances-delete');
				$data["schools-teacher_attendance_report"] = $this->input->post('schools-teacher_attendance_report');
				$data["schools-attendance_report"] = $this->input->post('schools-attendance_report');
				$data["schools-study_info_report"] = $this->input->post('schools-study_info_report');
				$data["schools-examanition_report"] = $this->input->post('schools-examanition_report');
				$data["schools-monthly_class_result_report"] = $this->input->post('schools-monthly_class_result_report');
				$data["schools-monthly_top_five_report"] = $this->input->post('schools-monthly_top_five_report');
				$data["schools-section_by_month_report"] = $this->input->post('schools-section_by_month_report');
				$data["schools-sectionly_class_result_report"] = $this->input->post('schools-sectionly_class_result_report');
				$data["schools-class_result_report"] = $this->input->post('schools-class_result_report');
				$data["schools-yearly_class_result_report"] = $this->input->post('schools-yearly_class_result_report');
				$data["schools-yearly_top_five_report"] = $this->input->post('schools-yearly_top_five_report');
				$data["schools-yearly_subject_result_report"] = $this->input->post('schools-yearly_subject_result_report');
				$data["schools-sectionly_subject_result_report"] = $this->input->post('schools-sectionly_subject_result_report');
				$data["schools-result_by_student_form"] = $this->input->post('schools-result_by_student_form');
				$data["schools-monthly_top_five_form"] = $this->input->post('schools-monthly_top_five_form');
				$data["schools-yearly_top_five_form"] = $this->input->post('schools-yearly_top_five_form');
				$data["schools-student_report"] = $this->input->post('schools-student_report');
				$data["schools-teacher_report"] = $this->input->post('schools-teacher_report');
				$data["schools-best_student_by_level_report"] = $this->input->post('schools-best_student_by_level_report');
				$data["schools-failure_student_by_year_report"] = $this->input->post('schools-failure_student_by_year_report');
				$data["schools-overview_chart"] = $this->input->post('schools-overview_chart');
			}
			if($this->config->item('concretes')){
				$data["concretes-drivers"] = $this->input->post('concretes-drivers');
				$data["concretes-trucks"] = $this->input->post('concretes-trucks');
				$data["concretes-slumps"] = $this->input->post('concretes-slumps');
				$data["concretes-casting_types"] = $this->input->post('concretes-casting_types');
				$data["concretes-officers"] = $this->input->post('concretes-officers');
				$data["concretes-deliveries"] = $this->input->post('concretes-deliveries');
				$data["concretes-deliveries-date"] = $this->input->post('concretes-deliveries-date');
				$data["concretes-add_delivery"] = $this->input->post('concretes-add_delivery');
				$data["concretes-edit_delivery"] = $this->input->post('concretes-edit_delivery');
				$data["concretes-delete_delivery"] = $this->input->post('concretes-delete_delivery');
				
				$data["concretes-moving_waitings"] = $this->input->post('concretes-moving_waitings');
				$data["concretes-moving_waitings-date"] = $this->input->post('concretes-moving_waitings-date');
				$data["concretes-add_moving_waiting"] = $this->input->post('concretes-add_moving_waiting');
				$data["concretes-edit_moving_waiting"] = $this->input->post('concretes-edit_moving_waiting');
				$data["concretes-delete_moving_waiting"] = $this->input->post('concretes-delete_moving_waiting');

				$data["concretes-missions"] = $this->input->post('concretes-missions');
				$data["concretes-missions-date"] = $this->input->post('concretes-missions-date');
				$data["concretes-add_mission"] = $this->input->post('concretes-add_mission');
				$data["concretes-edit_mission"] = $this->input->post('concretes-edit_mission');
				$data["concretes-delete_mission"] = $this->input->post('concretes-delete_mission');
				
				$data["concretes-fuel_expenses"] = $this->input->post('concretes-fuel_expenses');
				$data["concretes-fuel_expenses-date"] = $this->input->post('concretes-fuel_expenses-date');
				$data["concretes-add_fuel_expense"] = $this->input->post('concretes-add_fuel_expense');
				$data["concretes-edit_fuel_expense"] = $this->input->post('concretes-edit_fuel_expense');
				$data["concretes-delete_fuel_expense"] = $this->input->post('concretes-delete_fuel_expense');
				$data["concretes-fuel_expense_payments"] = $this->input->post('concretes-fuel_expense_payments');
				$data["concretes-fuel_expenses_report"] = $this->input->post('concretes-fuel_expenses_report');
				$data["concretes-fuel_expense_details_report"] = $this->input->post('concretes-fuel_expense_details_report');
				
				$data["concretes-commissions"] = $this->input->post('concretes-commissions');
				$data["concretes-commissions-date"] = $this->input->post('concretes-commissions-date');
				$data["concretes-add_commission"] = $this->input->post('concretes-add_commission');
				$data["concretes-edit_commission"] = $this->input->post('concretes-edit_commission');
				$data["concretes-delete_commission"] = $this->input->post('concretes-delete_commission');
				$data["concretes-commission_payments"] = $this->input->post('concretes-commission_payments');
				
				$data["concretes-absents"] = $this->input->post('concretes-absents');
				$data["concretes-absents-date"] = $this->input->post('concretes-absents-date');
				$data["concretes-add_absent"] = $this->input->post('concretes-add_absent');
				$data["concretes-edit_absent"] = $this->input->post('concretes-edit_absent');
				$data["concretes-delete_absent"] = $this->input->post('concretes-delete_absent');
				$data["concretes-absents_report"] = $this->input->post('concretes-absents_report');
				
				$data["concretes-fuels"] = $this->input->post('concretes-fuels');
				$data["concretes-fuels-date"] = $this->input->post('concretes-fuels-date');
				$data["concretes-add_fuel"] = $this->input->post('concretes-add_fuel');
				$data["concretes-edit_fuel"] = $this->input->post('concretes-edit_fuel');
				$data["concretes-delete_fuel"] = $this->input->post('concretes-delete_fuel');
				$data["concretes-sales"] = $this->input->post('concretes-sales');
				$data["concretes-sales-date"] = $this->input->post('concretes-sales-date');
				$data["concretes-add_sale"] = $this->input->post('concretes-add_sale');
				$data["concretes-edit_sale"] = $this->input->post('concretes-edit_sale');
				$data["concretes-delete_sale"] = $this->input->post('concretes-delete_sale');
				$data["concretes-adjustments"] = $this->input->post('concretes-adjustments');
				$data["concretes-add_adjustment"] = $this->input->post('concretes-add_adjustment');
				$data["concretes-approve_adjustment"] = $this->input->post('concretes-approve_adjustment');
				$data["concretes-delete_adjustment"] = $this->input->post('concretes-delete_adjustment');
				$data["concretes-errors"] = $this->input->post('concretes-errors');
				$data["concretes-errors-date"] = $this->input->post('concretes-errors-date');
				$data["concretes-add_error"] = $this->input->post('concretes-add_error');
				$data["concretes-edit_error"] = $this->input->post('concretes-edit_error');
				$data["concretes-delete_error"] = $this->input->post('concretes-delete_error');
				$data["concretes-deliveries_report"] = $this->input->post('concretes-deliveries_report');
				$data["concretes-daily_deliveries"] = $this->input->post('concretes-daily_deliveries');
				$data["concretes-daily_stock_ins"] = $this->input->post('concretes-daily_stock_ins');
				$data["concretes-daily_stock_outs"] = $this->input->post('concretes-daily_stock_outs');
				$data["concretes-inventory_in_outs"] = $this->input->post('concretes-inventory_in_outs');
				$data["concretes-fuels_report"] = $this->input->post('concretes-fuels_report');
				$data["concretes-fuel_summaries_report"] = $this->input->post('concretes-fuel_summaries_report');
				$data["concretes-fuel_details_report"] = $this->input->post('concretes-fuel_details_report');
				$data["concretes-fuel_by_customer_report"] = $this->input->post('concretes-fuel_by_customer_report');
				$data["concretes-sales_report"] = $this->input->post('concretes-sales_report');
				$data["concretes-sale_details_report"] = $this->input->post('concretes-sale_details_report');
				$data["concretes-product_sales_report"] = $this->input->post('concretes-product_sales_report');
				$data["concretes-product_customers_report"] = $this->input->post('concretes-product_customers_report');
				$data["concretes-adjustments_report"] = $this->input->post('concretes-adjustments_report');
				$data["concretes-daily_errors"] = $this->input->post('concretes-daily_errors');
				$data["concretes-daily_error_materials"] = $this->input->post('concretes-daily_error_materials');
				$data["concretes-truck_commissions"] = $this->input->post('concretes-truck_commissions');
				$data["concretes-pump_commissions"] = $this->input->post('concretes-pump_commissions');
				$data["concretes-officer_commissions"] = $this->input->post('concretes-officer_commissions');
				$data["concretes-mission_types"] = $this->input->post('concretes-mission_types');
				$data["concretes-commissions_report"] = $this->input->post('concretes-commissions_report');
				$data["concretes-moving_waitings_report"] = $this->input->post('concretes-moving_waitings_report');
				$data["concretes-missions_report"] = $this->input->post('concretes-missions_report');
				
			}
			if($this->config->item('user_by_category')){
				$data["categories"] = json_encode($this->input->post('category'));
			}

            if (POS) {
                $data['pos-index'] = $this->input->post('pos-index');
            }
        }
        if ($this->form_validation->run() == true && $this->settings_model->updatePermissions($id, $data)) {
            $this->session->set_flashdata('message', lang("group_permissions_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['id'] = $id;
            $this->data['p'] = $this->settings_model->getGroupPermissions($id);
            $this->data['group'] = $this->settings_model->getGroupByID($id);
			if($this->config->item("user_by_category")){
				$this->data['categories'] = $this->settings_model->getParentCategories();
			}
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => site_url('system_settings/user_groups'), 'page' => lang('groups')),  array('link' => '#', 'page' => lang('group_permissions')));
            $meta = array('page_title' => lang('group_permissions'), 'bc' => $bc);
            $this->core_page('settings/permissions', $meta, $this->data);
        }
    }

    function user_groups()
    {

		if (!$this->Owner && !$this->Admin && !$this->GP['group_permissions-index']) {
            $this->session->set_flashdata('error', lang("access_denied"));
            redirect('auth');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->data['groups'] = $this->settings_model->getGroups();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('groups')));
        $meta = array('page_title' => lang('groups'), 'bc' => $bc);
        $this->core_page('settings/user_groups', $meta, $this->data);
    }

    function delete_group($id = NULL)
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['group_permissions-index']) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect('welcome', 'refresh');
        }

        if ($this->settings_model->checkGroupUsers($id)) {
            $this->session->set_flashdata('error', lang("group_x_b_deleted"));
            redirect("system_settings/user_groups");
        }

        if ($this->settings_model->deleteGroup($id)) {
            $this->session->set_flashdata('message', lang("group_deleted"));
            redirect("system_settings/user_groups");
        }
    }

    function currencies()
    {

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('currencies')));
        $meta = array('page_title' => lang('currencies'), 'bc' => $bc);
        $this->core_page('settings/currencies', $meta, $this->data);
    }

    function getCurrencies()
    {

        $this->load->library('datatables');
        $this->datatables
            ->select("id, code, name, rate")
            ->from("currencies")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_currency/$1') . "' class='tip' title='" . lang("edit_currency") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_currency") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_currency/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    function add_currency()
    {

        $this->form_validation->set_rules('code', lang("currency_code"), 'trim|is_unique[currencies.code]|required');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('rate', lang("exchange_rate"), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = array('code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'rate' => $this->input->post('rate'),
            );
        } elseif ($this->input->post('add_currency')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/currencies");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCurrency($data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang("currency_added"));
            redirect("system_settings/currencies");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("new_currency");
            $this->load->view($this->theme . 'settings/add_currency', $this->data);
        }
    }

    function edit_currency($id = NULL)
    {

        $this->form_validation->set_rules('code', lang("currency_code"), 'trim|required');
        $cur_details = $this->settings_model->getCurrencyByID($id);
        if ($this->input->post('code') != $cur_details->code) {
            $this->form_validation->set_rules('code', lang("currency_code"), 'is_unique[currencies.code]');
        }
        $this->form_validation->set_rules('name', lang("currency_name"), 'required');
        $this->form_validation->set_rules('rate', lang("exchange_rate"), 'required|numeric');

        if ($this->form_validation->run() == true) {

            $data = array('code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'rate' => $this->input->post('rate'),
            );
        } elseif ($this->input->post('edit_currency')) {
            $this->session->set_flashdata('error', validation_errors());
            //redirect("system_settings/currencies");
			redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateCurrency($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang("currency_updated"));
            //redirect("system_settings/currencies");
			redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['currency'] = $this->settings_model->getCurrencyByID($id);
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_currency', $this->data);
        }
    }

    function delete_currency($id = NULL)
    {

        if ($this->settings_model->deleteCurrency($id)) {
            echo lang("currency_deleted");
        }
    }

    function currency_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('currencies'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('rate'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getCurrencyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->rate);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'currencies_' . date('Y_m_d_H_i_s');
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

    function categories()
    {

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('categories')));
        $meta = array('page_title' => lang('categories'), 'bc' => $bc);
        $this->core_page('settings/categories', $meta, $this->data);
    }

    function getCategories()
    {
        $print_barcode = anchor('products/print_barcodes/?category=$1', '<i class="fa fa-print"></i>'.lang('print_barcodes'));
        $category_products = anchor('system_settings/category_products/$1', '<i class="fa fa-check-square-o"></i>'.lang('category_products'));
        $category_discount = anchor('system_settings/category_discount/$1/$2', '<i class="fa fa-th-large"></i>'.lang('category_discount'));
        $duplicate_category = anchor('system_settings/add_category/$1', '<i class="fa fa-plus"></i> ' . lang('duplicate_category'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $edit_category = anchor('system_settings/edit_category/$1', '<i class="fa fa-edit"></i> ' . lang('edit_category'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_category") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('system_settings/delete_category/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_category') . "</a>";
		
		$allow_category = $this->site->getCategoryByProject();
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('categories')}.id as id, {$this->db->dbprefix('categories')}.image, {$this->db->dbprefix('categories')}.code, {$this->db->dbprefix('categories')}.name, c.name as parent,categories.parent_id as parent_id", FALSE)
            ->from("categories")
            ->join("categories c", 'c.id=categories.parent_id', 'left')
            ->group_by('categories.id');
			
		if($allow_category){
			$this->datatables->where_in("categories.id",$allow_category);
		}	
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $print_barcode . '</li>
                <li>' . $category_products . '</li>
                <li>' . $category_discount . '</li>
                <li>' . $duplicate_category . '</li>
                <li>' . $edit_category . '</li>
                <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->datatables->add_column("Actions", $action, "id,parent_id");
        $this->datatables->unset_column('parent_id');
		echo $this->datatables->generate();
    }

    function add_category($id = NULL)
    {

        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang("category_code"), 'trim|is_unique[categories.code]|required');
        $this->form_validation->set_rules('name', lang("name"), 'required|min_length[3]');
        $this->form_validation->set_rules('userfile', lang("category_image"), 'xss_clean');

        if ($this->form_validation->run() == true) {

			$type = $this->settings_model->getTypeByID($this->input->post('type'));
            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'parent_id' => $this->input->post('parent'),
				'warehouse_id' => $this->input->post('warehouse'),
				'type_id' => $type->id,
				'type' => $type->name,
				'installment' => $this->input->post('installment'),
				'stock_acc' => $this->input->post('stock_account'),
				'adjustment_acc' => $this->input->post('adjustment_account'),
				'convert_acc' => $this->input->post('convert_account'),
				'usage_acc' => $this->input->post('usage_account'),
				'cost_acc' => $this->input->post('cost_of_sale_account'),
				'sale_acc' => $this->input->post('sale_account'),
				'pawn_acc' => $this->input->post('pawn_account'),
            );

			$category_projects = false;
			if(!$this->config->item('one_biller')){
				$data['project'] = json_encode($this->input->post('project_multi'));
				$data['biller'] = json_encode($this->input->post('biller'));
				if($this->Settings->project == 1 && $this->input->post('project_multi')){
					foreach($this->input->post('project_multi') as $project){
						$project_info = $this->site->getProjectByID($project);
						$category_projects[] = array("project_id" => $project,"biller_id" => $project_info->biller_id);
					}
				} 
				if($this->input->post('biller')){
					foreach($this->input->post(biller) as $biller){
						$category_projects[] = array("biller_id" => $biller);
					}
				}
			}

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'right';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }

        } elseif ($this->input->post('add_category')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCategory($data, $category_projects)) {
            $this->session->set_flashdata('message', lang("category_added")." ".$data['code']." ".$data['name']);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            if($id){
                $category = $this->settings_model->getCategoryByID($id);
                if($this->Settings->accounting == 1){
                    $this->data['stock_accounts'] = $this->site->getAccount(array('AS'),$category->stock_acc);
                    $this->data['adjustment_accounts'] = $this->site->getAccount(array('CO','EX'),$category->adjustment_acc);
                    $this->data['usage_accounts'] = $this->site->getAccount(array('CO','EX'),$category->usage_acc);
                    $this->data['convert_accounts'] = $this->site->getAccount(array('CO','EX'),$category->convert_acc);
                    $this->data['cost_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'),$category->cost_acc);
                    $this->data['discount_accounts'] = $this->site->getAccount(array('RE','EX','GL'),$category->discount_acc);
                    $this->data['sale_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$category->sale_acc);
                    $this->data['expense_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'),$category->expense_acc);
                    if($this->config->item("pawn")){
                        $this->data['pawn_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$category->pawn_acc);
                    }
                }
            }else{
                $category = false;
                if($this->Settings->accounting == 1){
                    $this->data['stock_accounts'] = $this->site->getAccount(array('AS'));
                    $this->data['adjustment_accounts'] = $this->site->getAccount(array('CO','EX'));
                    $this->data['usage_accounts'] = $this->site->getAccount(array('CO','EX'));
                    $this->data['convert_accounts'] = $this->site->getAccount(array('CO','EX'));
                    $this->data['cost_accounts'] = $this->site->getAccount(array('CO'));
                    $this->data['sale_accounts'] = $this->site->getAccount(array('RE','OI'));
                    if($this->config->item("pawn")){
                        $this->data['pawn_accounts'] = $this->site->getAccount(array('RE','OI'));
                    }
                }
            }
            $this->data['category'] = $category;
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories'] = $this->settings_model->getParentCategories();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['types'] = $this->settings_model->getAllTypes();
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_category', $this->data);

        }
    }

    function edit_category($id = NULL)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang("category_code"), 'trim|required');
        $pr_details = $this->settings_model->getCategoryByID($id);
        if ($this->input->post('code') != $pr_details->code) {
            $this->form_validation->set_rules('code', lang("category_code"), 'is_unique[categories.code]');
        }
        $this->form_validation->set_rules('name', lang("category_name"), 'required|min_length[3]');
        $this->form_validation->set_rules('userfile', lang("category_image"), 'xss_clean');

        if ($this->form_validation->run() == true) {

			$type = $this->settings_model->getTypeByID($this->input->post('type'));
			$parent_id = $this->input->post('parent');
			if($parent_id){
				$cat_products = $this->settings_model->getProductsByCategoryId($id);
				if($cat_products){
					foreach($cat_products as $cat_prduct){
						$data = array(
									"category_id" => $parent_id,
									"subcategory_id" => $id,
								);
						$this->db->where("category_id",$id)->update("products", $data);
					}
				}
			}else{
				$sub_catproducts = $this->settings_model->getProductsBySubCategoryId($id);
				if($sub_catproducts){
					foreach($sub_catproducts as $sub_catproduct){
						$data = array(
									"category_id" => $id,
									"subcategory_id" => 0,
								);
						$this->db->where("subcategory_id",$id)->update("products", $data);
					}
				}
			}
            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'parent_id' => $this->input->post('parent'),
				'warehouse_id' => $this->input->post('warehouse'),
				'type_id' => $type->id,
				'type' => $type->name,
				'installment' => $this->input->post('installment'),
				'stock_acc' => $this->input->post('stock_account'),
				'adjustment_acc' => $this->input->post('adjustment_account'),
				'usage_acc' => $this->input->post('usage_account'),
				'convert_acc' => $this->input->post('convert_account'),
				'cost_acc' => $this->input->post('cost_of_sale_account'),
				'sale_acc' => $this->input->post('sale_account'),
				'pawn_acc' => $this->input->post('pawn_account'),
            );
			$category_projects = false;
			if(!$this->config->item('one_biller')){
				$data['project'] = json_encode($this->input->post('project_multi'));
				$data['biller'] = json_encode($this->input->post('biller'));
				if($this->Settings->project == 1 && $this->input->post('project_multi')){
					foreach($this->input->post('project_multi') as $project){
						$project_info = $this->site->getProjectByID($project);
						$category_projects[] = array("category_id"=>$id,"project_id" => $project,"biller_id" => $project_info->biller_id);
					}
				}
				if($this->input->post('biller')){
					
					foreach($this->input->post(biller) as $biller){
						$category_projects[] = array("category_id"=>$id,"biller_id" => $biller);
					}
				}
			}

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'right';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }

        } elseif ($this->input->post('edit_category')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/categories");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateCategory($id, $data, $category_projects)) {
            $this->session->set_flashdata('message', lang("category_updated")." ".$data['code']." ".$data['name']);
            redirect("system_settings/categories");
        } else {
			$category = $this->settings_model->getCategoryByID($id);
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['category'] = $category;
            $this->data['categories'] = $this->settings_model->getParentCategories();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['types'] = $this->settings_model->getAllTypes();
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['modal_js'] = $this->site->modal_js();
			
			if($this->Settings->accounting == 1){
				$this->data['stock_accounts'] = $this->site->getAccount(array('AS'),$category->stock_acc);
				$this->data['adjustment_accounts'] = $this->site->getAccount(array('CO','EX'),$category->adjustment_acc);
				$this->data['usage_accounts'] = $this->site->getAccount(array('CO','EX'),$category->usage_acc);
				$this->data['convert_accounts'] = $this->site->getAccount(array('CO','EX'),$category->convert_acc);
				$this->data['cost_accounts'] = $this->site->getAccount(array('CO'),$category->cost_acc);
				$this->data['sale_accounts'] = $this->site->getAccount(array('RE','OI'),$category->sale_acc);
				if($this->config->item("pawn")){
					$this->data['pawn_accounts'] = $this->site->getAccount(array('RE','OI'),$category->pawn_acc);
				}
			}
	
            $this->load->view($this->theme . 'settings/edit_category', $this->data);

        }
    }

    function delete_category($id = NULL)
    {

        if ($this->site->getSubCategories($id)) {
            $this->session->set_flashdata('error', lang("category_has_subcategory"));
            redirect("system_settings/categories");
        }

        if ($this->settings_model->deleteCategory($id)) {
            $this->session->set_flashdata('message', lang("category_deleted")." ".$id['code']." ".$id['name']);
            redirect("system_settings/categories");
        }
    }

    function category_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCategory($id);
                    }
                    $this->session->set_flashdata('message', lang("categories_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('categories'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('image'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('parent_actegory'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getCategoryByID($id);
                        $parent_actegory = '';
                        if ($sc->parent_id) {
                            $pc = $this->settings_model->getCategoryByID($sc->parent_id);
                            $parent_actegory = $pc->code;
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->image);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $parent_actegory);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'categories_' . date('Y_m_d_H_i_s');
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

    function tax_rates()
    {

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('tax_rates')));
        $meta = array('page_title' => lang('tax_rates'), 'bc' => $bc);
        $this->core_page('settings/tax_rates', $meta, $this->data);
    }

    function getTaxRates()
    {

        $this->load->library('datatables');
        $this->datatables
            ->select("id, name, code, rate, type")
            ->from("tax_rates")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_tax_rate/$1') . "' class='tip' title='" . lang("edit_tax_rate") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_tax_rate") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_tax_rate/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    function add_tax_rate()
    {

        $this->form_validation->set_rules('name', lang("name"), 'trim|is_unique[tax_rates.name]|required');
        $this->form_validation->set_rules('type', lang("type"), 'required');
        $this->form_validation->set_rules('rate', lang("tax_rate"), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = array('name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'type' => $this->input->post('type'),
                'rate' => $this->input->post('rate'),
            );
        } elseif ($this->input->post('add_tax_rate')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/tax_rates");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addTaxRate($data)) {
            $this->session->set_flashdata('message', lang("tax_rate_added"));
            redirect("system_settings/tax_rates");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_tax_rate', $this->data);
        }
    }

    function edit_tax_rate($id = NULL)
    {

        $this->form_validation->set_rules('name', lang("name"), 'trim|required');
        $tax_details = $this->settings_model->getTaxRateByID($id);
        if ($this->input->post('name') != $tax_details->name) {
            $this->form_validation->set_rules('name', lang("name"), 'is_unique[tax_rates.name]');
        }
        $this->form_validation->set_rules('type', lang("type"), 'required');
        $this->form_validation->set_rules('rate', lang("tax_rate"), 'required|numeric');

        if ($this->form_validation->run() == true) {

            $data = array('name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'type' => $this->input->post('type'),
                'rate' => $this->input->post('rate'),
            );
        } elseif ($this->input->post('edit_tax_rate')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/tax_rates");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateTaxRate($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang("tax_rate_updated"));
            redirect("system_settings/tax_rates");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['tax_rate'] = $this->settings_model->getTaxRateByID($id);

            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_tax_rate', $this->data);
        }
    }

    function delete_tax_rate($id = NULL)
    {
        if ($this->settings_model->deleteTaxRate($id)) {
            echo lang("tax_rate_deleted");
        }
    }

    function tax_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteTaxRate($id);
                    }
                    $this->session->set_flashdata('message', lang("tax_rates_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tax_rates'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('tax_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('type'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $tax = $this->settings_model->getTaxRateByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $tax->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $tax->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $tax->rate);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($tax->type == 1) ? lang('percentage') : lang('fixed'));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'tax_rates_' . date('Y_m_d_H_i_s');
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

	function payment_terms()
    {

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('payment_terms')));
        $meta = array('page_title' => lang('payment_terms'), 'bc' => $bc);
        $this->core_page('settings/payment_terms', $meta, $this->data);
    }

	function getPaymentTerms()
    {

        $this->load->library('datatables');
        $this->datatables
            ->select("id, description, due_day, due_day_discount, concat(discount,' ',discount_type) as discount")
            ->from("payment_terms")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_payment_term/$1') . "' class='tip' title='" . lang("edit_payment_term") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_payment_term") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_payment_term/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }

	function add_payment_term()
    {
        $this->form_validation->set_rules('description', lang("payment_description"), 'trim|is_unique[customer_groups.name]|required');
        $this->form_validation->set_rules('due_day', lang("payment_due_day"), 'required|numeric');
        if ($this->form_validation->run() == true) {
            $data = array(
				'description' => $this->input->post('description'),
                'due_day' => $this->input->post('due_day'),
				'due_day_discount' => $this->input->post('due_day_discount'),
				'discount_type' => $this->input->post('discount_type'),
				'discount' => $this->input->post('discount'),
				'term_type' => $this->input->post('term_type'),
            );
        } elseif ($this->input->post('add_payment_term')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/payment_terms");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addPaymentTerm($data)) {
            $this->session->set_flashdata('message', lang("payment_term_added"));
            redirect("system_settings/payment_terms");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_payment_term', $this->data);
        }
    }

	function delete_payment_term($id = NULL)
    {
        if ($this->settings_model->deletePaymentTerm($id)) {
            echo lang("payment_term_deleted");
        }
    }

	function edit_payment_term($id = NULL)
    {
        $this->form_validation->set_rules('description', lang("payment_description"), 'trim|required');
        $pg_details = $this->settings_model->getPaymentTermById($id);
        if ($this->input->post('description') != $pg_details->description) {
            $this->form_validation->set_rules('description', lang("payment_description"), 'is_unique[tax_rates.name]');
        }
        $this->form_validation->set_rules('due_day', lang("payment_due_day"), 'required|numeric');

        if ($this->form_validation->run() == true) {

            $data = array(
				'description' => $this->input->post('description'),
                'due_day' => $this->input->post('due_day'),
				'due_day_discount' => $this->input->post('due_day_discount'),
				'discount_type' => $this->input->post('discount_type'),
				'discount' => $this->input->post('discount'),
				'term_type' => $this->input->post('term_type'),
            );
        } elseif ($this->input->post('edit_payment_term')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/payment_terms");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatePaymentTerm($id, $data)) {
            $this->session->set_flashdata('message', lang("payment_term_updated"));
            redirect("system_settings/payment_terms");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['payment_term'] = $this->settings_model->getPaymentTermById($id);

            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_payment_term', $this->data);
        }
    }

	function payment_term_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deletePaymentTerm($id);
                    }
                    $this->session->set_flashdata('message', lang("payment_term_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tax_rates'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('description'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('due_day'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $pg = $this->settings_model->getPaymentTermById($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pg->description);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pg->due_day);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'payment_term_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_payment_term_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }


    function customer_groups()
    {

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('customer_groups')));
        $meta = array('page_title' => lang('customer_groups'), 'bc' => $bc);
        $this->core_page('settings/customer_groups', $meta, $this->data);
    }

    function getCustomerGroups()
    {

        $this->load->library('datatables');
        $this->datatables
            ->select("id, name, percent")
            ->from("customer_groups")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_customer_group/$1') . "' class='tip' title='" . lang("edit_customer_group") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_customer_group") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_customer_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    function add_customer_group()
    {

        $this->form_validation->set_rules('name', lang("group_name"), 'trim|is_unique[customer_groups.name]|required');
        $this->form_validation->set_rules('percent', lang("group_percentage"), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = array('name' => $this->input->post('name'),
                'percent' => $this->input->post('percent'),
            );
        } elseif ($this->input->post('add_customer_group')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/customer_groups");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCustomerGroup($data)) {
            $this->session->set_flashdata('message', lang("customer_group_added"));
            redirect("system_settings/customer_groups");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_customer_group', $this->data);
        }
    }

    function edit_customer_group($id = NULL)
    {

        $this->form_validation->set_rules('name', lang("group_name"), 'trim|required');
        $pg_details = $this->settings_model->getCustomerGroupByID($id);
        if ($this->input->post('name') != $pg_details->name) {
            $this->form_validation->set_rules('name', lang("group_name"), 'is_unique[tax_rates.name]');
        }
        $this->form_validation->set_rules('percent', lang("group_percentage"), 'required|numeric');

        if ($this->form_validation->run() == true) {

            $data = array('name' => $this->input->post('name'),
                'percent' => $this->input->post('percent'),
            );
        } elseif ($this->input->post('edit_customer_group')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/customer_groups");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateCustomerGroup($id, $data)) {
            $this->session->set_flashdata('message', lang("customer_group_updated"));
            redirect("system_settings/customer_groups");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['customer_group'] = $this->settings_model->getCustomerGroupByID($id);

            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_customer_group', $this->data);
        }
    }

    function delete_customer_group($id = NULL)
    {
        if ($this->settings_model->deleteCustomerGroup($id)) {
            echo lang("customer_group_deleted");
        }
    }

    function customer_group_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCustomerGroup($id);
                    }
                    $this->session->set_flashdata('message', lang("customer_groups_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tax_rates'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('group_name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('group_percentage'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $pg = $this->settings_model->getCustomerGroupByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pg->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pg->percent);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'customer_groups_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_customer_group_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function warehouses()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('warehouses')));
        $meta = array('page_title' => lang('warehouses'), 'bc' => $bc);
        $this->core_page('settings/warehouses', $meta, $this->data);
    }

    function getWarehouses()
    {

        $this->load->library('datatables');

		if(!$this->config->item('one_warehouse')){
			$delete_warehouse = "<a href='#' class='tip po' title='<b>" . lang("delete_warehouse") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete111111' href='" . site_url('system_settings/delete_warehouse/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a>";
        }

        if($this->Settings->alert_qty_by_warehouse == 1){
            $link_alert = "<a href='" . site_url('system_settings/product_alert_warehouse/$1') . "' class='tip' title='" . lang("product_alert_warehouse") . "'><i class=\"fonts fa fa-eye\"></i></a> ";
        }else{
            $link_alert = "";
        }

        $this->datatables
            ->select("{$this->db->dbprefix('warehouses')}.id as id, map, code, {$this->db->dbprefix('warehouses')}.name as name, {$this->db->dbprefix('price_groups')}.name as price_group, phone, email, address")
            ->from("warehouses")
            ->join('price_groups', 'price_groups.id=warehouses.price_group_id', 'left')
            ->add_column("Actions", "<div class=\"text-center\"> ".$link_alert." <a href='" . site_url('system_settings/edit_warehouse/$1') . "' class='tip' title='" . lang("edit_warehouse") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a> {$delete_warehouse}</div>", "id");

        echo $this->datatables->generate();
    }

    function add_warehouse()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang("code"), 'trim|is_unique[warehouses.code]|required');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('address', lang("address"), 'required');
        $this->form_validation->set_rules('userfile', lang("map_image"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if ($_FILES['userfile']['size'] > 0) {

                $this->load->library('upload');

                $config['upload_path'] = 'assets/uploads/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = '2000';
                $config['max_height'] = '2000';
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('message', $error);
                    redirect("system_settings/warehouses");
                }

                $map = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = 'assets/uploads/' . $map;
                $config['new_image'] = 'assets/uploads/thumbs/' . $map;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 76;
                $config['height'] = 76;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            } else {
                $map = NULL;
            }
            $data = array('code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email'),
                'address' => $this->input->post('address'),
                'price_group_id' => $this->input->post('price_group'),
                'map' => $map,
            );
        } elseif ($this->input->post('add_warehouse')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/warehouses");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addWarehouse($data)) {
            $this->session->set_flashdata('message', lang("warehouse_added")." - ".$data ['code']." - ".$data ['name']);
            redirect("system_settings/warehouses");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['price_groups'] = $this->settings_model->getAllPriceGroups();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_warehouse', $this->data);
        }
    }

    function edit_warehouse($id = NULL)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang("code"), 'trim|required');
        $wh_details = $this->settings_model->getWarehouseByID($id);
        if ($this->input->post('code') != $wh_details->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[warehouses.code]');
        }
        $this->form_validation->set_rules('address', lang("address"), 'required');
        $this->form_validation->set_rules('map', lang("map_image"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $data = array('code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email'),
                'address' => $this->input->post('address'),
                'price_group_id' => $this->input->post('price_group'),
            );

            if ($_FILES['userfile']['size'] > 0) {

                $this->load->library('upload');

                $config['upload_path'] = 'assets/uploads/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = '2000';
                $config['max_height'] = '2000';
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('message', $error);
                    redirect("system_settings/warehouses");
                }

                $data['map'] = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = 'assets/uploads/' . $data['map'];
                $config['new_image'] = 'assets/uploads/thumbs/' . $data['map'];
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 76;
                $config['height'] = 76;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
        } elseif ($this->input->post('edit_warehouse')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/warehouses");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateWarehouse($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang("warehouse_updated")." - ".$data['code']." - ".$data['name']);
            redirect("system_settings/warehouses");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouse'] = $this->settings_model->getWarehouseByID($id);
            $this->data['price_groups'] = $this->settings_model->getAllPriceGroups();
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_warehouse', $this->data);
        }
    }

    function delete_warehouse($id = NULL)
    {
		$row = $this->settings_model->getWarehouseByID($id);
        if ($this->settings_model->deleteWarehouse($id)) {
            //echo lang("warehouse_deleted". " ".$row->code . " ".$row->name);
			$this->session->set_flashdata('message', lang('warehouse_deleted')." - ". $row->name);
        }
		redirect('system_settings/warehouses');
    }

    function warehouse_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
						$row = $this->settings_model->getWarehouseByID($id);
                        $this->settings_model->deleteWarehouse($id);
                    }
                    $this->session->set_flashdata('message', lang("warehouses_deleted")." - ". $row->name ." - ". $row->code);
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('warehouses'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('city'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $wh = $this->settings_model->getWarehouseByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $wh->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $wh->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $wh->address);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $wh->city);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'warehouses_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_warehouse_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function variants()
    {

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('variants')));
        $meta = array('page_title' => lang('variants'), 'bc' => $bc);
        $this->core_page('settings/variants', $meta, $this->data);
    }

    function getVariants()
    {

        $this->load->library('datatables');
        $this->datatables
            ->select("id, name")
            ->from("variants")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_variant/$1') . "' class='tip' title='" . lang("edit_variant") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_variant") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_variant/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    function add_variant()
    {

        $this->form_validation->set_rules('name', lang("name"), 'trim|is_unique[variants.name]|required');

        if ($this->form_validation->run() == true) {
            $data = array('name' => $this->input->post('name'));
        } elseif ($this->input->post('add_variant')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/variants");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addVariant($data)) {
            $this->session->set_flashdata('message', lang("variant_added"));
            redirect("system_settings/variants");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_variant', $this->data);
        }
    }

    function edit_variant($id = NULL)
    {

        $this->form_validation->set_rules('name', lang("name"), 'trim|required');
        $tax_details = $this->settings_model->getVariantByID($id);
        if ($this->input->post('name') != $tax_details->name) {
            $this->form_validation->set_rules('name', lang("name"), 'is_unique[variants.name]');
        }

        if ($this->form_validation->run() == true) {
            $data = array('name' => $this->input->post('name'));
        } elseif ($this->input->post('edit_variant')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/variants");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateVariant($id, $data)) {
            $this->session->set_flashdata('message', lang("variant_updated"));
            redirect("system_settings/variants");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['variant'] = $tax_details;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_variant', $this->data);
        }
    }

    function delete_variant($id = NULL)
    {
        if ($this->settings_model->deleteVariant($id)) {
            echo lang("variant_deleted");
        }
    }

    function expense_categories()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('expense_categories')));
        $meta = array('page_title' => lang('categories'), 'bc' => $bc);
        $this->core_page('settings/expense_categories', $meta, $this->data);
    }

    function getExpenseCategories()
    {

        $this->load->library('datatables');
        $this->datatables
            ->select("expense_categories.id as id, expense_categories.code, expense_categories.name, c.name as parent")
            ->from("expense_categories")
			->join("expense_categories c", 'c.id=expense_categories.parent_id', 'left')
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_expense_category/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal' class='tip' title='" . lang("edit_expense_category") . "'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_expense_category") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1111' href='" . site_url('system_settings/delete_expense_category/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id");

        echo $this->datatables->generate();
    }

    function add_expense_category()
    {
        $this->form_validation->set_rules('code', lang("category_code"), 'trim|is_unique[expense_categories.code]|required');
        $this->form_validation->set_rules('name', lang("name"), 'required|min_length[3]');
        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
				'parent_id' => $this->input->post('parent'),
				'expense_account' => $this->input->post('expense_account'),
				'note' => $this->input->post('note'),
            );

        } elseif ($this->input->post('add_expense_category')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/expense_categories");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addExpenseCategory($data)) {
            $this->session->set_flashdata('message', lang("expense_category_added")." " .$data['code']." ".$data['name']);
            redirect("system_settings/expense_categories");
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['expenses_categories'] = $this->settings_model->getAllExpenseCategories();
			if($this->Settings->accounting == 1){
				$this->data['expense_accounts'] = $this->site->getAccount(array('CO','EX','OX'));
			}
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_expense_category', $this->data);
        }
    }

    function edit_expense_category($id = NULL)
    {
        $this->form_validation->set_rules('code', lang("category_code"), 'trim|required');
        $category = $this->settings_model->getExpenseCategoryByID($id);
        if ($this->input->post('code') != $category->code) {
            $this->form_validation->set_rules('code', lang("category_code"), 'is_unique[expense_categories.code]');
        }
        $this->form_validation->set_rules('name', lang("category_name"), 'required|min_length[3]');

        if ($this->form_validation->run() == true) {

            $data = array(
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
				'parent_id' => $this->input->post('parent'),
				'expense_account' => $this->input->post('expense_account'),
				'note' => $this->input->post('note'),
            );

        } elseif ($this->input->post('edit_expense_category')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/expense_categories");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateExpenseCategory($id, $data, $photo)) {
            $this->session->set_flashdata('message', lang("expense_category_updated"." " .$data['code']." ".$data['name']));
            redirect("system_settings/expense_categories");
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['expenses_categories'] = $this->settings_model->getAllExpenseCategories();
			$this->data['category'] = $category;
			if($this->Settings->accounting == 1){
				$this->data['expense_accounts'] = $this->site->getAccount(array('CO','EX','OX'),$category->expense_account);
			}
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_expense_category', $this->data);
        }
    }

    function delete_expense_category($id = NULL)
    {

        if ($this->settings_model->hasExpenseCategoryRecord($id)) {
            $this->session->set_flashdata('error', lang("category_has_expenses"));
            redirect("system_settings/expense_categories", 'refresh');
        }


        if ($this->settings_model->deleteExpenseCategory($id)) {
           // echo lang("expense_category_deleted");
		   $row = $this->settings_model->deleteExpenseCategory($id);
        }
		 $this->session->set_flashdata('message', lang("expense_category_deleted")." ".$id['code']." ".$id['name']);
		 redirect("system_settings/expense_categories", 'refresh');
    }

    function expense_category_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteExpenseCategory($id);
                    }
                    $this->session->set_flashdata('message', lang("categories_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('categories'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('parent'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getExpenseCategoryByID($id);
						$ps = $this->settings_model->getExpenseCategoryByID($sc->parent_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $ps->name);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'categories_' . date('Y_m_d_H_i_s');
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

    function import_categories()
    {

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
					for($row=2; $row<=$highestRow; $row++){
						 $code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						 $name = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						 $code_pare = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						 $acc_stock = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						 $acc_adjus = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
						 $acc_usage = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
						 $acc_costs = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
						 $acc_sales = $worksheet->getCellByColumnAndRow(7, $row)->getValue();

						 $final[] = array(
							  'code'  			=> trim($code),
							  'name'   			=> trim($name),
							  'code_pare'    	=> trim($code_pare),
							  'stock_acc'  		=> trim($acc_stock),
							  'adjustment_acc'  => trim($acc_adjus),
							  'usage_acc'   	=> trim($acc_usage),
							  'cost_acc'   		=> trim($acc_costs),
							  'sale_acc'   		=> trim($acc_sales),
						 );
					}
				}

				$rw = 2;
                foreach ($final as $csv_pr) {
					if($csv_pr['code']=='' || $csv_pr['name']==''){
						$this->session->set_flashdata('error', lang("code_name_required"));
						redirect("system_settings/categories");
					}else if (!$this->products_model->getCategoryByCode($csv_pr['code'])) {
						if($csv_pr['code_pare']){
							$parentCate = $this->settings_model->getCategoryByCode($csv_pr['code_pare']);
							if(!$parentCate){
								$this->session->set_flashdata('error', lang("check_parent_category_code") . " (" . $csv_pr['code_pare'] . "). " . lang("parent_category_code_x_exist") . " " . lang("line_no") . " " . $rw);
								redirect("system_settings/categories");
							}
							$csv_pr['parent_id'] = $parentCate->id;
						}
						if($csv_pr['stock_acc']){
							$stock_acc = $this->settings_model->getAccountByCode($csv_pr['stock_acc']);
							if(!$stock_acc){
								$this->session->set_flashdata('error', lang("account_code_x_exist") . " (" . $csv_pr['stock_acc'] . ")");
								redirect("system_settings/categories");
							}
						}

						if($csv_pr['adjustment_acc']){
							$adjustment_acc = $this->settings_model->getAccountByCode($csv_pr['adjustment_acc']);
							if(!$adjustment_acc){
								$this->session->set_flashdata('error', lang("account_code_x_exist") . " (" . $csv_pr['adjustment_acc'] . ")");;
								redirect("system_settings/categories");
							}
						}

						if($csv_pr['usage_acc']){
							$usage_acc = $this->settings_model->getAccountByCode($csv_pr['usage_acc']);
							if(!$usage_acc){
								$this->session->set_flashdata('error', lang("account_code_x_exist") . " (" . $csv_pr['usage_acc'] . ")");
								redirect("system_settings/categories");
							}
						}

						if($csv_pr['cost_acc']){
							$cost_acc = $this->settings_model->getAccountByCode($csv_pr['cost_acc']);
							if(!$cost_acc){
								$this->session->set_flashdata('error', lang("account_code_x_exist") . " (" . $csv_pr['cost_acc'] . ")");
								redirect("system_settings/categories");
							}
						}
						if($csv_pr['sale_acc']){
							$sale_acc = $this->settings_model->getAccountByCode($csv_pr['sale_acc']);
							if(!$sale_acc){
								$this->session->set_flashdata('error', lang("account_code_x_exist") . " (" . $csv_pr['sale_acc'] . ")");
								redirect("system_settings/categories");
							}
						}
						$codes[]			= $csv_pr['code'];
						$names[] 			= $csv_pr['name'];
						$parent_ids[] 		= $csv_pr['parent_id'];
						$stock_accs[] 		= $csv_pr['stock_acc'];
						$adjustment_accs[] 	= $csv_pr['adjustment_acc'];
						$usage_accs[] 		= $csv_pr['usage_acc'];
						$cost_accs[] 		= $csv_pr['cost_acc'];
						$sale_accs[] 		= $csv_pr['sale_acc'];
					}else{
						$this->session->set_flashdata('error', lang("category_code_already_exist") . " (" . $csv_pr['code'] . ")");
						redirect("system_settings/categories");
					}

                    $rw++;
                }

				$ikeys = array('code', 'name', 'parent_id', 'stock_acc', 'adjustment_acc', 'usage_acc', 'cost_acc', 'sale_acc');
				$items = array();
				foreach (array_map(null, $codes, $names, $parent_ids, $stock_accs, $adjustment_accs, $usage_accs, $cost_accs, $sale_accs) as $ikey => $value) {
					$items[] = array_combine($ikeys, $value);
				}
			}

		}

		if ($this->form_validation->run() == true && $this->settings_model->addCategories($items)) {
			$this->session->set_flashdata('message', lang("categories_added"));
			redirect('system_settings/categories');
		} else {

			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['userfile'] = array('name' => 'userfile',
				'id' => 'userfile',
				'type' => 'text',
				'value' => $this->form_validation->set_value('userfile')
			);
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme.'settings/import_categories', $this->data);

		}

	}

    function import_subcategories()
    {

        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');
                $config['upload_path'] = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("system_settings/categories");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen('files/' . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('code', 'name', 'category_code', 'image');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                $rw = 2;
                foreach ($final as $csv_ct) {
                    if ( ! $this->settings_model->getSubcategoryByCode(trim($csv_ct['code']))) {
                        if ($parent_actegory = $this->settings_model->getCategoryByCode(trim($csv_ct['category_code']))) {
                            $data[] = array(
                                'code' => trim($csv_ct['code']),
                                'name' => trim($csv_ct['name']),
                                'image' => trim($csv_ct['image']),
                                'category_id' => $parent_actegory->id,
                                );
                        } else {
                            $this->session->set_flashdata('error', lang("check_category_code") . " (" . $csv_ct['category_code'] . "). " . lang("category_code_x_exist") . " " . lang("line_no") . " " . $rw);
                            redirect("system_settings/categories");
                        }
                    }
                    $rw++;
                }
            }

            // $this->cus->print_arrays($data);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addSubCategories($data)) {
            $this->session->set_flashdata('message', lang("subcategories_added"));
            redirect('system_settings/categories');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/import_subcategories', $this->data);

        }
    }

	function import_expense_categories()
    {
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
					for($row=2; $row<=$highestRow; $row++){
						$code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$name = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$parent_code = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						$expense_acc = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						$final[] = array(
							  'code'  			=> trim($code),
							  'name'   			=> trim($name),
							  'parent_code'    	=> trim($parent_code),
							  'expense_acc'  	=> trim($expense_acc),
						 );
					}
				}
				$rw = 2;
                foreach ($final as $csv_pr) {
					if($csv_pr['code']=='' || $csv_pr['name']==''){
						$this->session->set_flashdata('error', lang("code_name_required"));
						redirect("system_settings/expense_categories");
					}else if (!$this->settings_model->getExpenseCategoryByCode($csv_pr['code'])) {
						if($csv_pr['expense_acc']){
							$expense_acc = $this->settings_model->getAccountByCode($csv_pr['expense_acc']);
							if(!$expense_acc){
								$this->session->set_flashdata('error', lang("account_code_x_exist") . " (" . $csv_pr['expense_acc'] . ")");
								redirect("system_settings/categories");
							}
						}
						if(trim($csv_pr['parent_code'])){
							$parent = $this->settings_model->getExpenseCategoryByCode(trim($csv_pr['parent_code']));
							$parent_id = $parent->id;
						}else{
							$parent_id = 0;
						}
						$codes[]			= $csv_pr['code'];
						$names[] 			= $csv_pr['name'];
						$parent_ids[] 		= $parent_id;
						$expense_accs[] 	= $csv_pr['expense_acc'];
					}else{
						$this->session->set_flashdata('error', lang("category_code_already_exist") . " (" . $csv_pr['code'] . ")");
						redirect("system_settings/expense_categories");
					}
                    $rw++;
                }

				$ikeys = array('code', 'name', 'parent_id', 'expense_account');
				$items = array();
				foreach (array_map(null, $codes, $names, $parent_ids, $expense_accs) as $ikey => $value) {
					$items[] = array_combine($ikeys, $value);
				}
			}
		}

		if ($this->form_validation->run() == true && $this->settings_model->addExpenseCategories($items)) {
			$this->session->set_flashdata('message', lang("categories_added"));
			redirect('system_settings/expense_categories');
		} else {

			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['userfile'] = array('name' => 'userfile',
				'id' => 'userfile',
				'type' => 'text',
				'value' => $this->form_validation->set_value('userfile')
			);
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme.'settings/import_expense_categories', $this->data);

		}

	}

    function units()
    {

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('units')));
        $meta = array('page_title' => lang('units'), 'bc' => $bc);
        $this->core_page('settings/units', $meta, $this->data);
    }

    function getUnits()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('units')}.id as id, {$this->db->dbprefix('units')}.code, {$this->db->dbprefix('units')}.name, b.name as base_unit, {$this->db->dbprefix('units')}.operator, {$this->db->dbprefix('units')}.operation_value", FALSE)
            ->from("units")
            ->join("units b", 'b.id=units.base_unit', 'left')
            ->group_by('units.id')
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_unit/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal' class='tip' title='" . lang("edit_unit") . "'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_unit") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete124' href='" . site_url('system_settings/delete_unit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id");

        echo $this->datatables->generate();
    }

    function add_unit()
    {

        $this->form_validation->set_rules('code', lang("unit_code"), 'trim|is_unique[units.code]|required');
        $this->form_validation->set_rules('name', lang("unit_name"), 'trim|required');
        if ($this->input->post('base_unit')) {
            $this->form_validation->set_rules('operator', lang("operator"), 'required');
            $this->form_validation->set_rules('operation_value', lang("operation_value"), 'trim|required');
        }

        if ($this->form_validation->run() == true) {

            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'base_unit' => $this->input->post('base_unit') ? $this->input->post('base_unit') : NULL,
                'operator' => $this->input->post('base_unit') ? $this->input->post('operator') : NULL,
                'operation_value' => $this->input->post('operation_value'),
                );

        } elseif ($this->input->post('add_unit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
			//redirect("system_settings/units");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addUnit($data)) {
            $this->session->set_flashdata('message', lang("unit_added")." ".$data['code']." ".$data['name']);
           //redirect($_SERVER['HTTP_REFERER']);
		   redirect("system_settings/units");

        } else {

            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_unit', $this->data);

        }
    }

    function edit_unit($id = NULL)
    {

        $this->form_validation->set_rules('code', lang("code"), 'trim|required');
        $unit_details = $this->site->getUnitByID($id);
        if ($this->input->post('code') != $unit_details->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[units.code]');
        }
        $this->form_validation->set_rules('name', lang("name"), 'trim|required');
        if ($this->input->post('base_unit')) {
            $this->form_validation->set_rules('operator', lang("operator"), 'required');
            $this->form_validation->set_rules('operation_value', lang("operation_value"), 'trim|required');
        }

        if ($this->form_validation->run() == true) {

            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'base_unit' => $this->input->post('base_unit') ? $this->input->post('base_unit') : NULL,
                'operator' => $this->input->post('base_unit') ? $this->input->post('operator') : NULL,
                'operation_value' => $this->input->post('operation_value'),
                );

        } elseif ($this->input->post('edit_unit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/units");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateUnit($id, $data)) {
            $this->session->set_flashdata('message', lang("unit_updated")." ".$data['code']);
            redirect("system_settings/units");
        } else {

            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['unit'] = $unit_details;
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->load->view($this->theme . 'settings/edit_unit', $this->data);

        }
    }

    function delete_unit($id = NULL)
    {
        if ($this->site->getUnitsByBUID($id,$id)) {
            $this->session->set_flashdata('error', lang("unit_has_subunit"));
			header('HTTP/1.1 404 Bad Request');
			header('Content-Type: application/json; charset=UTF-8');
			exit;
        }

        if ($this->settings_model->deleteUnit($id)) {
           // echo lang("unit_deleted");
        }
		$this->session->set_flashdata('message', lang("unit_deleted")." ".$id['code']." ".$id['name']);
        redirect("system_settings/units");
    }

    function unit_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteUnit($id);
                    }
                    $this->session->set_flashdata('message', lang("units_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('categories'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('base_unit'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('operator'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('operation_value'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $unit = $this->site->getUnitByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $unit->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $unit->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $unit->base_unit);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $unit->operator);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $unit->operation_value);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'units_' . date('Y_m_d_H_i_s');
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

    function price_groups()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('price_groups')));
        $meta = array('page_title' => lang('price_groups'), 'bc' => $bc);
        $this->core_page('settings/price_groups', $meta, $this->data);
    }

    function getPriceGroups()
    {
        $this->load->library('datatables');
		$this->datatables->select("id, name");
		$this->datatables->from("price_groups");
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/group_product_prices/$1') . "' class='tip' title='" . lang("group_product_prices") . "'><i class=\"fonts fa fa-eye\"></i></a>  <a href='" . site_url('system_settings/edit_price_group/$1') . "' class='tip' title='" . lang("edit_price_group") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_price_group") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_price_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id,category_id");
        echo $this->datatables->generate();
    }

	function customer_prices()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('customer_price')));
        $meta = array('page_title' => lang('customer_price'), 'bc' => $bc);
        $this->core_page('settings/customer_price', $meta, $this->data);
    }

	function getCustomerPrices()
    {
        $this->load->library('datatables');
		$this->datatables->select("id, name");
		$this->datatables->from("companies");
		$this->datatables->where("group_id",3);
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/customer_product_prices/$1') . "' class='tip' title='" . lang("customer_product_prices") . "'><i class=\"fa fa-eye\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }

	function customer_product_prices($customer_id = NULL)
    {
        if (!$customer_id) {
            $this->session->set_flashdata('error', lang('no_customer_selected'));
            redirect('system_settings/customer_prices');
        }

		$this->data['id'] = $customer_id;
		$this->data['customer'] = $this->site->getCompanyByID($customer_id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),  array('link' => site_url('system_settings/customer_prices'), 'page' => lang('customer_prices')), array('link' => '#', 'page' => lang('customer_product_prices')));
        $meta = array('page_title' => lang('customer_product_prices'), 'bc' => $bc);
        $this->core_page('settings/customer_product_prices', $meta, $this->data);
    }

	function customer_product_price_actions($customer_id)
    {
        if (!$customer_id) {
            $this->session->set_flashdata('error', lang('no_customer_selected'));
            redirect('system_settings/customer_prices');
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_price') {

                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->setProductPriceForCustomer($id, $customer_id, $this->input->post('price'.$id));
                    }
                    $this->session->set_flashdata('message', lang("customer_products_price_updated"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'delete') {

                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCustomerProductPrice($id, $customer_id);
                    }
                    $this->session->set_flashdata('message', lang("customer_products_price_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer_product_prices'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('product_price'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
                    $row = 2;
                    $customer = $this->site->getCompanyByID($customer_id);
                    foreach ($_POST['val'] as $id) {
                        $pgp = $this->settings_model->getCustomerProductPriceByPID($id, $customer_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pgp->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pgp->name);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $pgp->product_price);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pgp->price);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->company);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'customer_price_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_customer_product_price_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

	function getCustomerProductPrices($customer_id = NULL)
    {
        if (!$customer_id) {
            $this->session->set_flashdata('error', lang('no_customer_selected'));
            redirect('system_settings/customer_prices');
        }

        $pp = "( SELECT {$this->db->dbprefix('customer_product_prices')}.product_id as product_id, {$this->db->dbprefix('customer_product_prices')}.price as price  FROM {$this->db->dbprefix('customer_product_prices')} WHERE customer_id = {$customer_id} ) PP";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id,{$this->db->dbprefix('categories')}.`name` as category_name, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('products')}.name as product_name, {$this->db->dbprefix('products')}.price as product_price, PP.price as price")
            ->from("products")
			->join("categories","categories.id= products.category_id")
            ->join($pp, 'PP.product_id=products.id', 'left')
            ->edit_column("price", "$1__$2", 'id, price')
            ->add_column("Actions", "<div class=\"text-center\"><button class=\"btn btn-primary btn-xs form-submit\" type=\"button\"><i class=\"fa fa-check\"></i></button></div>", "id");

        echo $this->datatables->generate();
    }

	function update_customer_product_price($customer_id = NULL)
    {
        if (!$customer_id) {
            $this->cus->send_json(array('status' => 0));
        }
        $product_id = $this->input->post('product_id', TRUE);
        $price = $this->input->post('price', TRUE);
        if (!empty($product_id)) {
            if ($this->settings_model->setProductPriceForCustomer($product_id, $customer_id, $price)) {
                $this->cus->send_json(array('status' => 1));
            }
        }

        $this->cus->send_json(array('status' => 0));
    }

	public function formula($str)
	{
	   if (preg_match('/[0-9]+%/', $str, $matches)) {
			if (strpos($str, '-') !== false) {
				return '-'.$matches[0];
			}else{
				return $matches[0];
			}

	   }else if(is_numeric($str)){
		   return (float)$str;
	   }
	   return FALSE;
	}

    function add_price_group()
    {

        $this->form_validation->set_rules('name', lang("group_name"), 'trim|is_unique[price_groups.name]|required');

        if ($this->form_validation->run() == true) {
            $data['name'] = $this->input->post('name');
        } elseif ($this->input->post('add_price_group')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/price_groups");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addPriceGroup($data)) {
            $this->session->set_flashdata('message', lang("price_group_added"));
            redirect("system_settings/price_groups");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['customer_groups'] = $this->settings_model->getAllCustomerGroups();
			$this->data['categories'] = $this->site->getAllCategories();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_price_group', $this->data);
        }
    }

    function edit_price_group($id = NULL)
    {

        $this->form_validation->set_rules('name', lang("group_name"), 'trim|required');
		$pg_details = $this->settings_model->getPriceGroupByID($id);
        if ($this->input->post('name') != $pg_details->name) {
            $this->form_validation->set_rules('name', lang("group_name"), 'is_unique[price_groups.name]');
        }
        if ($this->form_validation->run() == true) {
            $data['name'] = $this->input->post('name');
        } elseif ($this->input->post('edit_price_group')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/price_groups");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatePriceGroup($id, $data)) {
            $this->session->set_flashdata('message', lang("price_group_updated"));
            redirect("system_settings/price_groups");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['customer_groups'] = $this->settings_model->getAllCustomerGroups();
			$this->data['categories'] = $this->site->getAllCategories();
            $this->data['price_group'] = $pg_details;
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_price_group', $this->data);
        }
    }

    function delete_price_group($id = NULL)
    {
        if ($this->settings_model->deletePriceGroup($id)) {
            echo lang("price_group_deleted");
        }
    }

    function product_group_price_actions($group_id)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            redirect('system_settings/price_groups');
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_price') {

                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->setProductPriceForPriceGroup($id, $group_id, $this->input->post('price'.$id));
                    }
                    $this->session->set_flashdata('message', lang("products_group_price_updated"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'delete') {

                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteProductGroupPrice($id, $group_id);
                    }
                    $this->session->set_flashdata('message', lang("products_group_price_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tax_rates'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('group_name'));
                    $row = 2;
                    $group = $this->settings_model->getPriceGroupByID($group_id);
                    foreach ($_POST['val'] as $id) {
                        $pgp = $this->settings_model->getProductGroupPriceByPID($id, $group_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pgp->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pgp->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pgp->price);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $group->name);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'price_groups_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_price_group_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function group_product_prices($group_id = NULL)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            redirect('system_settings/price_groups');
        }

		$this->data['id'] = $group_id;
        $this->data['price_group'] = $this->settings_model->getPriceGroupByID($group_id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),  array('link' => site_url('system_settings/price_groups'), 'page' => lang('price_groups')), array('link' => '#', 'page' => lang('group_product_prices')));
        $meta = array('page_title' => lang('group_product_prices'), 'bc' => $bc);
        $this->core_page('settings/group_product_prices', $meta, $this->data);
    }

    function getProductPrices($group_id = NULL)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            redirect('system_settings/price_groups');
        }

        $pp = "( SELECT {$this->db->dbprefix('product_prices')}.product_id as product_id, {$this->db->dbprefix('product_prices')}.price as price, price_group_id  FROM {$this->db->dbprefix('product_prices')} WHERE price_group_id = {$group_id} ) PP";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id,{$this->db->dbprefix('categories')}.`name` as category_name, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('products')}.name as product_name, PP.price as price")
            ->from("products")
			->join("categories","categories.id= products.category_id")
            ->join($pp, 'PP.product_id=products.id', 'left')
            ->edit_column("price", "$1__$2", 'id, price')
            ->add_column("Actions", "<div class=\"text-center\"><button class=\"btn btn-primary btn-xs form-submit\" type=\"button\"><i class=\"fa fa-check\"></i></button></div>", "id");

        echo $this->datatables->generate();
    }

	function group_category_prices($group_id = NULL)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            redirect('system_settings/price_groups');
        }

		$this->data['id'] = $group_id;
        $this->data['price_group'] = $this->settings_model->getPriceGroupByID($group_id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),  array('link' => site_url('system_settings/price_groups'), 'page' => lang('price_groups')), array('link' => '#', 'page' => lang('group_category_prices')));
        $meta = array('page_title' => lang('group_category_prices'), 'bc' => $bc);
        $this->core_page('settings/group_category_prices', $meta, $this->data);
    }

	function getCategoryPrices($group_id = NULL)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            redirect('system_settings/price_groups');
        }
		$this->load->library('datatables');

		if(isset($_GET['cat'])){
			$this->datatables->where_in("category_id", json_decode($_GET['cat']));
		}

        $pp = "( SELECT {$this->db->dbprefix('product_prices')}.product_id as product_id, {$this->db->dbprefix('product_prices')}.price as price, price_group_id  FROM {$this->db->dbprefix('product_prices')} WHERE price_group_id = {$group_id} ) PP";
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id,{$this->db->dbprefix('categories')}.`name` as category_name, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('products')}.name as product_name, PP.price as price, products.price as rprice, PP.price as dprice")
            ->from("products")
			->join("categories","categories.id= products.category_id")
            ->join($pp, 'PP.product_id=products.id', 'left')
            ->edit_column("price", "$1__$2__$3", 'id, price, rprice')
			->unset_column("rprice")
            ->add_column("Actions", "<div class=\"text-center\"><button class=\"btn btn-primary btn-xs form-submit\" type=\"button\"><i class=\"fa fa-check\"></i></button></div>", "id");

        echo $this->datatables->generate();
    }

    function update_product_group_price($group_id = NULL)
    {
        if (!$group_id) {
            $this->cus->send_json(array('status' => 0));
        }

        $product_id = $this->input->post('product_id', TRUE);
        $price = $this->input->post('price', TRUE);
        if (!empty($product_id) && !empty($price)) {
            if ($this->settings_model->setProductPriceForPriceGroup($product_id, $group_id, $price)) {
                $this->cus->send_json(array('status' => 1));
            }
        }

        $this->cus->send_json(array('status' => 0));
    }

    function update_prices_csv($group_id = NULL)
    {

        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (DEMO) {
                $this->session->set_flashdata('message', lang("disabled_in_demo"));
                redirect('welcome');
            }

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');
                $config['upload_path'] = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("system_settings/group_product_prices/".$group_id);
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen('files/' . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'price');

                $final = array();

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if ($product = $this->site->getProductByCode(trim($csv_pr['code']))) {
                    $data[] = array(
                        'product_id' => $product->id,
                        'price' => $csv_pr['price'],
                        'price_group_id' => $group_id
                        );
                    } else {
                        $this->session->set_flashdata('message', lang("check_product_code") . " (" . $csv_pr['code'] . "). " . lang("code_x_exist") . " " . lang("line_no") . " " . $rw);
                        redirect("system_settings/group_product_prices/".$group_id);
                    }
                    $rw++;
                }
            }

        } elseif ($this->input->post('update_price')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/group_product_prices/".$group_id);
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            $this->settings_model->updateGroupPrices($data);
            $this->session->set_flashdata('message', lang("price_updated"));
            redirect("system_settings/group_product_prices/".$group_id);
        } else {

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['group'] = $this->site->getPriceGroupByID($group_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/update_price', $this->data);

        }
    }

    function brands()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('brands')));
        $meta = array('page_title' => lang('brands'), 'bc' => $bc);
        $this->core_page('settings/brands', $meta, $this->data);
    }

    function getBrands()
    {

        $this->load->library('datatables');
        $this->datatables
            ->select("id, image, code, name", FALSE)
            ->from("brands")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_brand/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal' class='tip' title='" . lang("edit_brand") . "'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_brand") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_brand/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id");

        echo $this->datatables->generate();
    }

    function add_brand()
    {

        $this->form_validation->set_rules('name', lang("brand_name"), 'trim|required|is_unique[brands.name]|alpha_numeric_spaces');

        if ($this->form_validation->run() == true) {

            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                $this->image_lib->clear();
            }

        } elseif ($this->input->post('add_brand')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addBrand($data)) {
            $this->session->set_flashdata('message', lang("brand_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {

            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_brand', $this->data);

        }
    }

    function edit_brand($id = NULL)
    {

        $this->form_validation->set_rules('name', lang("brand_name"), 'trim|required|alpha_numeric_spaces');
        $brand_details = $this->site->getBrandByID($id);
        if ($this->input->post('name') != $brand_details->name) {
            $this->form_validation->set_rules('name', lang("brand_name"), 'is_unique[brands.name]');
        }

        if ($this->form_validation->run() == true) {

            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                $this->image_lib->clear();
            }

        } elseif ($this->input->post('edit_brand')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/brands");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateBrand($id, $data)) {
            $this->session->set_flashdata('message', lang("brand_updated"));
            redirect("system_settings/brands");
        } else {

            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['brand'] = $brand_details;
            $this->load->view($this->theme . 'settings/edit_brand', $this->data);

        }
    }

    function delete_brand($id = NULL)
    {

        if ($this->settings_model->brandHasProducts($id)) {
            $this->session->set_flashdata('error', lang("brand_has_products"));
            redirect("system_settings/brands");
        }

        if ($this->settings_model->deleteBrand($id)) {
            echo lang("brand_deleted");
        }
    }

    function import_brands()
    {

        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');
                $config['upload_path'] = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("system_settings/brands");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen('files/' . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('name', 'code', 'image');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                foreach ($final as $csv_ct) {
                    if ( ! $this->settings_model->getBrandByName(trim($csv_ct['name']))) {
                        $data[] = array(
                            'code' => trim($csv_ct['code']),
                            'name' => trim($csv_ct['name']),
                            'image' => trim($csv_ct['image']),
                            );
                    }
                }
            }

            // $this->cus->print_arrays($data);
        }

        if ($this->form_validation->run() == true && !empty($data) && $this->settings_model->addBrands($data)) {
            $this->session->set_flashdata('message', lang("brands_added"));
            redirect('system_settings/brands');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/import_brands', $this->data);

        }
    }

    function brand_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteBrand($id);
                    }
                    $this->session->set_flashdata('message', lang("brands_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('brands'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('image'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $brand = $this->site->getBrandByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $brand->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $brand->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $brand->image);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'categories_' . date('Y_m_d_H_i_s');
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

	function category_discount($category_id = NULL, $parent_id = NULL)
	{
		$this->form_validation->set_rules('end_date', lang("end_date"), 'required');
		$this->form_validation->set_rules('start_date', lang("start_date"), 'required');

        if ($this->form_validation->run() == true) {
			$price = $this->input->post("price");
			$product_id = $this->input->post("product_id");
			$data = array();
			foreach($price as $i => $p){
				$data[] = array(
						'id' => $product_id[$i],
						'promotion' => 1,
						'promo_price' => $price[$i],
						'start_date' => $this->cus->fld($this->input->post('start_date')),
						'end_date' => $this->cus->fld($this->input->post('end_date')),
					);
			}
			$result = $this->db->update_batch("products",$data,"id");
			if($result){
				$this->session->set_flashdata('message', lang("discount_by_category_added"));
				redirect("system_settings/category_discount/".$category_id."/".$parent_id);
			}
		}
		$this->data['id'] = $category_id;
		$this->data['parent_id'] = $parent_id;
		$this->data['category'] = $this->products_model->getCategoryByID($category_id);
		$this->lang->load('products', $this->Settings->user_language);
		$this->data['products'] = $this->products_model->getCategoryProducts($category_id, $parent_id);
		$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('category_discount')));
        $meta = array('page_title' => lang('category_discount'), 'bc' => $bc);
        $this->core_page('settings/category_discount', $meta, $this->data);
	}

	// Category Product Update

	function category_products($id = NULL)
	{
		$this->data['id'] = $id;
		$this->data['category'] = $this->site->getCategoryByID($id);
		$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('category_products')));
        $meta = array('page_title' => lang('category_products'), 'bc' => $bc);
        $this->core_page('settings/category_products', $meta, $this->data);
	}

	function getCategoryProducts($id = NULL)
    {
		$this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id,
			{$this->db->dbprefix('categories')}.`name` as category_name,
			{$this->db->dbprefix('products')}.code as product_code,
			{$this->db->dbprefix('products')}.name as product_name,
			{$this->db->dbprefix('products')}.price as price")
            ->from("products")
			->join("categories","categories.id= products.category_id")
			->where("category_id", $id)
            ->add_column("Actions", "<div class=\"text-center\"><button class=\"btn btn-primary btn-xs form-submit\" type=\"button\"><i class=\"fa fa-check\"></i></button></div>", "id");

        echo $this->datatables->generate();
    }

	function update_category_product($category_id = NULL)
    {
        if (!$category_id) {
            $this->cus->send_json(array('status' => 0));
        }
        $id = $this->input->post('product_id', TRUE);
        $code = $this->input->post('product_code', TRUE);
		$name = $this->input->post('product_name', TRUE);
		$price = $this->input->post('product_price', TRUE);

        if (!empty($code) && !empty($name)) {
            if ($this->settings_model->updateCategoryProduct($id, $code, $name, $price)) {
                $this->cus->send_json(array('status' => 1));
            }
        }
        $this->cus->send_json(array('status' => 0));
    }

	function category_product_actions($category_id)
    {
        if (!$category_id) {
            $this->session->set_flashdata('error', lang('no_product_selected'));
            redirect('system_settings/price_groups');
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_product') {
                    foreach ($_POST['val'] as $i => $id) {
					   $code = ($_POST['code'][$i]);
					   $name = ($_POST['name'][$i]);
					   $price = ($_POST['price'][$i]);
                       $this->settings_model->updateCategoryProduct($id, $code, $name, $price);
                    }
                    $this->session->set_flashdata('message', lang("products_updated"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
			}
		}
		$this->session->set_flashdata('error', lang('no_product_selected'));
		redirect($_SERVER["HTTP_REFERER"]);
	}

	public function tables()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('tables')));
        $meta = array('page_title' => lang('tables'), 'bc' => $bc);
        $this->core_page('settings/tables', $meta, $this->data);
	}

	public function getTables()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
					suspended_tables.id as id,
					suspended_tables.name,
					suspended_floors.floor,
					warehouses.name as warehouse_name,
					description,
					status")
            ->from("suspended_tables")
			->join("suspended_floors","suspended_floors.id = suspended_tables.floor","left")
			->join("warehouses","warehouses.id = suspended_tables.warehouse_id","left")
			->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_table") . "' href='" . site_url('system_settings/edit_table/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_table") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_table/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

	public function add_table()
    {
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'is_unique[suspended_tables.name]');
        if ($this->form_validation->run() == true) {
            $data = array(
				'floor' => $this->input->post('floor'),
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
				'status' => $this->input->post('status'),
				'warehouse_id' => $this->input->post('warehouse'),
				'product_id' => $this->input->post('product_id'),
            );
        }else if($this->input->post('add_table')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addTable($data)) {
            $this->session->set_flashdata('message', lang("table_added")." ".$data['name']);
            redirect("system_settings/tables");
        } else {
			$this->data['floors'] = $this->settings_model->getAllFloors();
			$this->data['products'] = $this->settings_model->getAllProducts();
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_table', $this->data);
        }
    }

	public function edit_table($id = NULL)
    {
		$table = $this->settings_model->getTableByID($id);
		$this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
		if ($this->input->post('name') != $table->name) {
            $this->form_validation->set_rules('name', lang("name"), 'is_unique[suspended_tables.name]');
        }
        if ($this->form_validation->run() == true) {
            $data = array(
				'floor' => $this->input->post('floor'),
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
				'status' => $this->input->post('status'),
				'warehouse_id' => $this->input->post('warehouse'),
				'product_id' => $this->input->post('product_id'),
            );

        }else if($this->input->post('edit_table')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $this->settings_model->updateTable($id, $data)) {
            $this->session->set_flashdata('message', lang("table_edited")." ".$data['name']);
            redirect("system_settings/tables");
        } else {
			$this->data['id'] = $id;
			$this->data['floors'] = $this->settings_model->getAllFloors();
			$this->data['products'] = $this->settings_model->getAllProducts();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['row'] = $this->settings_model->getTableByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_table', $this->data);
        }
    }

	public function delete_table($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteTable($id)) {
            echo $this->lang->line("table_deleted"); exit;
        }
		$this->session->set_flashdata('message', lang("table_deleted")." ".$id['name']);
        redirect("system_settings/tables");
    }

	public function table_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteTable($id);
                    }
                    $this->session->set_flashdata('message', lang("tables_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tables'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('floor'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('description'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getTableByID($id);
						$fl = $this->settings_model->getFloorByID($sc->floor);
						$wh = $this->settings_model->getWarehouseByID($sc->warehouse_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $fl->floor);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $wh->name);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->description);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($sc->status));
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'tables_' . date('Y_m_d_H_i_s');
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

	public function import_tables()
	{
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
					for($row=2; $row<=$highestRow; $row++){
						$name = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$description = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$floor = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						$warehouse_id = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						$product_id = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
						$customer_id = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
						$status = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
						$final[] = array(
						  'name'   			=> $name,
						  'description'   	=> $description,
						  'floor'   		=> $floor,
						  'warehouse_id'  	=> $warehouse_id,
						  'product_id'   	=> $product_id,
						  'customer_id'   	=> $customer_id,
						  'status'   		=> $status,
						);
					}
				}

                $rw = 2;
                foreach ($final as $csv_pr) {
					$table = $this->settings_model->getTableByName(trim($csv_pr['name']));
					if($table){
						$this->session->set_flashdata('error', lang("name_exist"));
						redirect("system_settings/tables");
					}
					$pr_name[] = trim($csv_pr['name']);
					$pr_description[] = trim($csv_pr['description']);
					$pr_floor[] = trim($csv_pr['floor']);
					$pr_warehouse_id[] = trim($csv_pr['warehouse_id']);
					$pr_product_id[] = trim($csv_pr['product_id']);
					$pr_customer_id[] = trim($csv_pr['customer_id']);
					$pr_status[] = trim($csv_pr['status']);
                    $rw++;
				}
            }
            $ikeys = array('name','description','floor','warehouse_id', 'product_id','customer_id','status');
            $items = array();
            foreach (array_map(null,$pr_name,$pr_description,$pr_floor,$pr_warehouse_id,$pr_product_id,$pr_customer_id, $pr_status) as $ikey => $value) {
                $items[] = array_combine($ikeys, $value);
            }

        }
        if ($this->form_validation->run() == true && $this->settings_model->addTables($items)) {
            $this->session->set_flashdata('message', lang("tables_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/import_tables', $this->data);
        }
    }

	public function floors()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('floors')));
        $meta = array('page_title' => lang('floors'), 'bc' => $bc);
        $this->core_page('settings/floors', $meta, $this->data);
	}

	public function getFloors()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
					suspended_floors.id as id,
					suspended_floors.floor")
            ->from("suspended_floors")
			->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_floor") . "' href='" . site_url('system_settings/edit_floor/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("edit_floor") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_rental_floor/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

	public function delete_floor($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteFloor($id)) {
            echo $this->lang->line("floor_deleted"); exit;
        }
		$this->session->set_flashdata('message', lang("floor_deleted")." ".$id['floor']);
            redirect("system_settings/floors");
    }

	public function add_floor()
    {
        $this->form_validation->set_rules('name', $this->lang->line("floor"), 'is_unique[suspended_floors.floor]');
        if ($this->form_validation->run() == true) {
            $data = array(
					'floor' => $this->input->post('name')
				);
        }else if($this->input->post('add_floor')){
			$this->session->set_flashdata('error', validation_errors());
			redirect("system_settings/floors");
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addFloor($data)) {
            $this->session->set_flashdata('message', lang("floor_added")." ".$data['floor']);
            redirect("system_settings/floors");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_floor', $this->data);
        }
    }

	public function edit_floor($id = false)
    {
		$floor = $this->settings_model->getFloorByID($id);
		$this->form_validation->set_rules('name', $this->lang->line("floor"), 'required');
		if ($this->input->post('name') != $floor->floor) {
            $this->form_validation->set_rules('name', lang("floor"), 'is_unique[suspended_floors.floor]');
        }
        if ($this->form_validation->run() == true) {
            $data = array(
					'floor' => $this->input->post('name')
				);
        }
		else if($this->input->post('edit_floor')){
			$this->session->set_flashdata('error', validation_errors());
			redirect("system_settings/floors");
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateFloor($id, $data)) {
            $this->session->set_flashdata('message', lang("floor_updated")." ".$data['floor']);
            redirect("system_settings/floors");
        } else {
			$this->data['id'] = $id;
			$this->data['row'] = $this->settings_model->getFloorByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_floor', $this->data);
        }
    }

	public function floor_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteFloor($id);
                    }
                    $this->session->set_flashdata('message', lang("floors_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('floor'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getFloorByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->floor);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'floors_' . date('Y_m_d_H_i_s');
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

	public function getDrivers()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("id,
					code,
					name,
					phone,
					address")
            ->from("drivers")
			->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_driver") . "' href='" . site_url('system_settings/edit_driver/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_driver") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_driver/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

	public function drivers()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('settings')));
        $meta = array('page_title' => lang('drivers'), 'bc' => $bc);
        $this->core_page('settings/drivers', $meta, $this->data);
	}

	public function add_driver()
    {
        $this->form_validation->set_rules('code', $this->lang->line("code"), 'required|is_unique[drivers.code]');
		$this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'code' => $this->input->post('code'),
						'name' => $this->input->post('name'),
						'phone' => $this->input->post('phone'),
						'address' => $this->input->post('address')
					);
        }else if($this->input->post('add_driver')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addDriver($data)) {
            $this->session->set_flashdata('message', lang("driver_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_driver', $this->data);
        }
    }

	public function edit_driver($id = false)
    {
		$driver = $this->settings_model->getDriverByID($id);
		$code = $this->input->post('code',true);
		$valid_code = '';
		if($driver && $driver->code != $this->input->post('code',true)){
			$valid_code .= '|is_unique[drivers.code]';
		}
		$this->form_validation->set_rules('code', $this->lang->line("code"), 'required'.$valid_code);
		$this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
					'code' => $code,
					'name' => $this->input->post('name'),
					'phone' => $this->input->post('phone'),
					'address' => $this->input->post('address')
				);
        }else if($this->input->post('edit_driver')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateDriver($id, $data)) {
            $this->session->set_flashdata('message', lang("driver_updated"));
            redirect("system_settings/drivers");
        } else {
			$this->data['id'] = $id;
			$this->data['row'] = $this->settings_model->getDriverByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_driver', $this->data);
        }
    }

	function delete_driver($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteDriver($id)) {
            echo $this->lang->line("driver_deleted");
        }
    }

	public function driver_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteDriver($id);
                    }
                    $this->session->set_flashdata('message', lang("drivers_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('drivers'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('address'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getDriverByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->phone);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->address);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'drivers_' . date('Y_m_d_H_i_s');
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

	public function projects()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('settings')));
        $meta = array('page_title' => lang('projects'), 'bc' => $bc);
        $this->core_page('settings/projects', $meta, $this->data);
	}

	public function getProjects()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("projects.id as id,
					projects.name,
					companies.name as biller,
					description,
					start_date,
					end_date")
            ->from("projects")
			->join("companies","companies.id=projects.biller_id","left")
			->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_project") . "' href='" . site_url('system_settings/edit_project/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_project") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_project/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

	public function add_project()
    {
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'is_unique[projects.name]');
        if ($this->form_validation->run() == true) {
            $data = array(
						'name' => $this->input->post('name'),
						'start_date' => $this->cus->fld($this->input->post('start_date')),
						'end_date' => $this->cus->fld($this->input->post('end_date')),
						'description' => $this->input->post('description'),
						'address' => $this->input->post('address'),
						'biller_id' => $this->input->post('biller'),
						'created_by' => $this->session->userdata('user_id'),
						'created_date' => date("Y-m-d H:i"),
					);
        }else if($this->input->post('add_project')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addProject($data)) {
            $this->session->set_flashdata('message', lang("project_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_project', $this->data);
        }
    }

	public function edit_project($id = false)
    {
		$project = $this->settings_model->getProjectByID($id);
		$this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
		if ($this->input->post('name') != $project->name) {
            $this->form_validation->set_rules('name', lang("name"), 'is_unique[projects.name]');
        }

        if ($this->form_validation->run() == true) {
            $data = array(
					'name' => $this->input->post('name'),
					'start_date' => $this->cus->fld($this->input->post('start_date')),
					'end_date' => $this->cus->fld($this->input->post('end_date')),
					'biller_id' => $this->input->post('biller'),
					'address' => $this->input->post('address'),
					'description' => $this->input->post('description')
				);
        }else if($this->input->post('edit_project')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateProject($id, $data)) {
            $this->session->set_flashdata('message', lang("project_updated"));
            redirect("system_settings/projects");
        } else {
			$this->data['id'] = $id;
			$this->data['row'] = $this->settings_model->getProjectByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_project', $this->data);
        }
    }

	public function project_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteProject($id);
                    }
                    $this->session->set_flashdata('message', lang("projects_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
				if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('projects'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('description'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('start_date'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('end_date'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getProjectByID($id);
						$bill = $this->site->getCompanyByID($sc->biller_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, ucfirst($bill->company));
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->description);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->cus->hrsd($sc->start_date));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->cus->hrsd($sc->end_date));
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'projects_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
			}else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

	public function delete_project($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteProject($id)) {
            echo $this->lang->line("project_deleted"); exit;
        }
    }

	public function get_project()
	{
		$id = $this->input->get("biller");
		$user_id = $this->input->get("user");
		$user = $this->site->getUser($user_id);
		$rows = $this->settings_model->getProjectByBillerID($id);
		$pl['all'] = lang("all");
		foreach($rows as $row){
			$pl[$row->id] = $row->name;
		}
		$project = json_decode($user->project_ids);
		$opt = form_dropdown('project[]', $pl, (isset($_POST['project']) ? $_POST['project'] : $project), 'id="project" class="form-control select" multiple');
		echo json_encode(array("result" => $opt));
	}
	
	public function get_multi_project()
	{
		$biller_multi = $this->input->get("biller");	
		if($biller_multi){
			$project_id = $this->input->get("project");
			$project_multi = explode("#",$this->input->get("project_multi"));
			$rows = $this->settings_model->getProjectByBillers($biller_multi);
			$user = $this->site->getUser($this->session->userdata("user_id"));
			$project = json_decode($user->project_ids);
			$pl = array(lang('select')." ".lang('project'));
			if ($this->Owner || $this->Admin || $project[0] === 'all') {
				foreach($rows as $row){
					$pl[$row->id] = $row->name;
					$mpl[$row->id] = $row->name;
				}
			}else{
				foreach($rows as $row){
					if(in_array($row->id, $project)){
						$pl[$row->id] = $row->name;
						$mpl[$row->id] = $row->name;
					}
				}
			}
			$opt = form_dropdown('project', $pl, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="project" class="form-control"');
			$mult_opt = form_dropdown('project_multi[]', $mpl, (isset($_POST['project_multi']) ? $_POST['project_multi'] : $project_multi), 'id="project_multi" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '"  style="width:100%;" multiple');
			echo json_encode(array("result" => $opt,"multi_resultl" => $mult_opt));
		}
		
	}

	public function login_time($id = NULL)
	{
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('settings')));
        $meta = array('page_title' => lang('login_time'), 'bc' => $bc);
        $this->core_page('settings/login_time', $meta, $this->data);
	}

	public function getLoginTimes()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("login_permissions.id as id,
					upper(day) as day,
					upper(cus_groups.name) as name,
					time_in,
					time_out")
            ->from("login_permissions")
			->join("groups","groups.id=group_id","left")
			->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_login_time") . "' href='" . site_url('system_settings/edit_login_time/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_login_time") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_login_time/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

	public function add_login_time()
    {
        $this->form_validation->set_rules('group', lang("group"), 'required');
        if ($this->form_validation->run() == true) {
			$group_id = $this->input->post('group');
			$group = $this->settings_model->getLoginTimeByDayGroup($day, $group_id);
			if($group){
				$this->session->set_flashdata('error', lang("double_data_please_check"));
				redirect("system_settings/login_time");
			}
			$data = array();
			if(isset($_POST['day'])){
				foreach($_POST['day'] as $day){
					$data[] = array(
						'group_id' => $group_id,
						'day' => $day,
						'time_in' => $this->input->post('time_in'),
						'time_out' => $this->input->post('time_out'),
						'description' => $this->input->post('description'),
					);
				}
			}

        }else if($this->input->post('add_login_time')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addLoginTime($data)) {
            $this->session->set_flashdata('message', lang("login_time_added"));
            redirect("system_settings/login_time");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['groups'] = $this->settings_model->getGroups();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_login_time', $this->data);
        }
    }

	public function edit_login_time($id = false)
    {
        $this->form_validation->set_rules('group', lang("group"), 'required');
        if ($this->form_validation->run() == true) {
			$group_id = $this->input->post('group');
			$day = $this->input->post('day');
			$group = $this->settings_model->getLoginTimeByDayGroup($day, $group_id);
			if($group && $group->id !=$id){
				$this->session->set_flashdata('error', lang("double_data_please_check"));
				redirect("system_settings/login_time");
			}
            $data = array(
						'group_id' => $group_id,
						'day' => $day,
						'time_in' => $this->input->post('time_in'),
						'time_out' => $this->input->post('time_out'),
						'description' => $this->input->post('description'),
					);
        }else if($this->input->post('edit_login_time')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateLoginTime($id, $data)) {
            $this->session->set_flashdata('message', lang("login_time_updated"));
            redirect("system_settings/login_time");
        } else {
			$this->data['id'] = $id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['login'] = $this->settings_model->getLoginTimeById($id);
			$this->data['groups'] = $this->settings_model->getGroups();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_login_time', $this->data);
        }
    }

	public function delete_login_time($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteLoginTime($id)) {
            echo $this->lang->line("login_time_deleted"); exit;
        }
    }

	public function login_time_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteLoginTime($id);
                    }
                    $this->session->set_flashdata('message', lang("login_times_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
				if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('login_times'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('day'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('user'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('time_in'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('time_out'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('description'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getLoginTimeById($id);
						$us = $this->settings_model->getGroupByID($sc->group_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->day);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, ucfirst($us->name));
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->time_in);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->time_out);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->description);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'login_times_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
			}else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

	public function frequencies()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('settings')));
        $meta = array('page_title' => lang('frequencies'), 'bc' => $bc);
        $this->core_page('settings/frequencies', $meta, $this->data);
	}

	public function add_frequency()
    {
        $this->form_validation->set_rules('description', $this->lang->line("description"), 'required|is_unique[frequency.description]');
		$this->form_validation->set_rules('day', $this->lang->line("day"), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = array(
						'description' => $this->input->post('description'),
						'day' => $this->input->post('day'),
					);
        }else if($this->input->post('add_frequency')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addFrequency($data)) {
            $this->session->set_flashdata('message', lang("frequency_added")." ".$data['description']." ".$data['day']);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_frequency', $this->data);
        }
    }

	public function edit_frequency($id = false)
    {
        $this->form_validation->set_rules('description', $this->lang->line("description"), 'required');
        $this->form_validation->set_rules('day', $this->lang->line("day"), 'required|numeric');

		if ($this->form_validation->run() == true) {
            $data = array(
						'description' => $this->input->post('description'),
						'day' => $this->input->post('day'),
					);
        }else if($this->input->post('edit_frequency')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateFrequency($id, $data)) {
            $this->session->set_flashdata('message', lang("frequency_updated")." ".$data['description']." ".$data['day']);
			redirect($_SERVER['HTTP_REFERER']);
        } else {
			$this->data['id'] = $id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['row'] = $this->settings_model->getFrequencyById($id);
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_frequency', $this->data);
        }
    }

	public function delete_frequency($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteFrequency($id)) {
            echo $this->lang->line("frequency_deleted"); exit;
        }
		$this->session->set_flashdata('message', lang("frequency_deleted")." ".$id['description']." ".$id['day']);
		redirect("system_settings/frequencies");
    }

	public function getFrequencies()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
					frequency.id as id,
					frequency.description,
					frequency.day")
            ->from("frequency")
			->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_frequency") . "' href='" . site_url('system_settings/edit_frequency/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_frequency") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_frequency/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

	public function frequency_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteFrequency($id);
                    }
                    $this->session->set_flashdata('message', lang("frequencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('frequencies'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('description'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('day'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getFrequencyById($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->description);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->day);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'frequencies_' . date('Y_m_d_H_i_s');
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

	public function import_frequencies()
	{
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
					for($row=2; $row<=$highestRow; $row++){
						$description = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$day = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$final[] = array(
						  'description'   => $description,
						  'day'   		=> $day,
						);
					}
				}
                $rw = 2;
                foreach ($final as $csv_pr) {
					$pr_description[] = trim($csv_pr['description']);
					$pr_day[] = trim($csv_pr['day']);
                    $rw++;
                }
            }
            $ikeys = array('description', 'day');
            $items = array();
            foreach (array_map(null, $pr_description, $pr_day) as $ikey => $value) {
                $items[] = array_combine($ikeys, $value);
            }
        }
        if ($this->form_validation->run() == true && $this->settings_model->addFrequencies($items)) {
            $this->session->set_flashdata('message', lang("frequencies_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/import_frequencies', $this->data);
        }
    }

	public function getVehicles()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("id,
					plate_no,
					type,
					description")
            ->from("vehicles")
			->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_vehicle") . "' href='" . site_url('system_settings/edit_vehicle/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_vehicle") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_vehicle/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

	public function vehicles()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('settings')));
        $meta = array('page_title' => lang('vehicles'), 'bc' => $bc);
        $this->core_page('settings/vehicles', $meta, $this->data);
	}

	public function add_vehicle()
    {
		$this->form_validation->set_rules('plate_no', $this->lang->line("plate_no"), 'required|is_unique[vehicles.plate_no]');
        if ($this->form_validation->run() == true) {
            $data = array(
						'plate_no' => trim($this->input->post('plate_no')),
						'type' => trim($this->input->post('type')),
						'description' => $this->input->post('description')
					);
        }else if($this->input->post('add_vehicle')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addVehicle($data)) {
            $this->session->set_flashdata('message', lang("vehicle_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_vehicle', $this->data);
        }
    }

	public function edit_vehicle($id = false)
    {
		$unique = "";
		$vehicle = $this->settings_model->getVehicleByID($id);
		$plate_no = trim($this->input->post('plate_no'));
		if($vehicle->plate_no != $plate_no){
			$unique .= "|is_unique[vehicles.plate_no]";
		}
        $this->form_validation->set_rules('plate_no', $this->lang->line("plate_no"), 'required'.$unique);

		if ($this->form_validation->run() == true) {
            $data = array(
					'plate_no' => $this->input->post('plate_no'),
					'type' => $this->input->post('type'),
					'description' => $this->input->post('description')
				);
        }else if($this->input->post('edit_vehicle')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateVehicle($id, $data)) {
            $this->session->set_flashdata('message', lang("vehicle_updated"));
            redirect("system_settings/vehicles");
        } else {
			$this->data['id'] = $id;
			$this->data['row'] = $this->settings_model->getVehicleByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_vehicle', $this->data);
        }
    }

	public function delete_vehicle($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteVehicle($id)) {
            echo $this->lang->line("vehicle_deleted"); exit;
        }
    }

	public function vehicle_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteVehicle($id);
                    }
                    $this->session->set_flashdata('message', lang("vehicles_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('vehicles'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('plate_no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('type'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('description'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getVehicleByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->plate_no);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->type);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->description);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'vehicles_' . date('Y_m_d_H_i_s');
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

	public function import_vehicles()
	{
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
					for($row=2; $row<=$highestRow; $row++){
						$plate_no = trim($worksheet->getCellByColumnAndRow(0, $row)->getValue());
						$type = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$description = $worksheet->getCellByColumnAndRow(2, $row)->getValue();

						$vehicle = $this->settings_model->getVehicleByPlateNo($plate_no);
						if($vehicle){
							$this->session->set_flashdata('error', lang("plate_no_duplicated"));
							redirect($_SERVER["HTTP_REFERER"]);
						}

						$final[] = array(
						  'plate_no'   => $plate_no,
						  'type'   		=> $type,
						  'description' => $description,
						);
					}
				}
                $rw = 2;
                foreach ($final as $csv_pr) {
					$pr_plate_no[] = trim($csv_pr['plate_no']);
					$pr_type[] = trim($csv_pr['type']);
					$pr_description[] = trim($csv_pr['description']);
                    $rw++;
                }
            }
            $ikeys = array('plate_no', 'type', 'description');
            $items = array();
            foreach (array_map(null, $pr_plate_no, $pr_type, $pr_description) as $ikey => $value) {
                $items[] = array_combine($ikeys, $value);
            }
        }
        if ($this->form_validation->run() == true && $this->settings_model->addVehicles($items)) {
            $this->session->set_flashdata('message', lang("vehicles_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/import_vehicles', $this->data);
        }
    }

	public function holidays()
	{
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('settings')));
        $meta = array('page_title' => lang('holidays'), 'bc' => $bc);
        $this->core_page('settings/holidays', $meta, $this->data);
	}

	public function getHolidays()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("id, from_date, to_date, description")
            ->from("cus_holiday")
			->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_holiday") . "' href='" . site_url('system_settings/edit_holiday/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_holiday") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_holiday/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id,description");
        echo $this->datatables->generate();
    }

	public function delete_holiday($id = false)
	{
		if($this->settings_model->deleteHoliday($id)){
			echo lang("holiday_deleted");
		}
	}

	public function add_holiday()
    {
        $this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'from_date' => $this->cus->fld($this->input->post('from_date')),
						'to_date' => $this->cus->fld($this->input->post('to_date')),
						'description' => $this->input->post('description')
					);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addHoliday($data)) {
            $this->session->set_flashdata('message', lang("holiday_added"));
            redirect("system_settings/holidays");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_holiday', $this->data);
        }
    }

	public function edit_holiday($id = false)
    {
        $this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'from_date' => $this->cus->fld($this->input->post('from_date')),
						'to_date' => $this->cus->fld($this->input->post('to_date')),
						'description' => $this->input->post('description')
					);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateHoliday($id, $data)) {
            $this->session->set_flashdata('message', lang("holiday_updated"));
            redirect("system_settings/holidays");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['id'] = $id;
			$this->data['row'] = $this->settings_model->getHolidayByID($id);
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_holiday', $this->data);
        }
    }

	public function clear($id = NULL)
    {
		$this->cus->checkPermissions();
        $this->form_validation->set_rules('clear', lang("clear"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
                'list_billers' => $this->input->post('clear-list_billers'),
				'list_projects' => $this->input->post('clear-list_projects'),
				'warehouses' => $this->input->post('clear-warehouses'),
				'expense_categories' => $this->input->post('clear-expense_categories'),
				'categories' => $this->input->post('clear-categories'),
				'frequencies' => $this->input->post('clear-frequencies'),
				'units' => $this->input->post('clear-units'),
				'brands' => $this->input->post('clear-brands'),
				'boms' => $this->input->post('clear-boms'),
				'customer_group' => $this->input->post('clear-customer_group'),
				'price_group' => $this->input->post('clear-price_group'),
				'payment_terms' => $this->input->post('clear-payment_terms'),
				'currencies' => $this->input->post('clear-currencies'),
				'customer_opening_balances' => $this->input->post('clear-customer_opening_balances'),
				'supplier_opening_balances' => $this->input->post('clear-supplier_opening_balances'),
				'tax_rates' => $this->input->post('clear-tax_rates'),
				'user_groups' => $this->input->post('clear-user_groups'),
				'calendar_lists' => $this->input->post('clear-calendar_lists'),
				'list_products' => $this->input->post('clear-list_products'),
				'using_stocks' => $this->input->post('clear-using_stocks'),
				'stock_counts' => $this->input->post('clear-stock_counts'),
				'quantity_adjustments' => $this->input->post('clear-quantity_adjustments'),
				'cost_adjustments' => $this->input->post('clear-cost_adjustments'),
				'converts' => $this->input->post('clear-converts'),
				'list_transfers' => $this->input->post('clear-list_transfers'),
				'list_quotations' => $this->input->post('clear-list_quotations'),
				'list_sale_orders' => $this->input->post('clear-list_sale_orders'),
				'list_sales' => $this->input->post('clear-list_sales'),
				'pos' => $this->input->post('clear-pos'),
				'deliveries' => $this->input->post('clear-deliveries'),
				'list_returns' => $this->input->post('clear-list_returns'),
				'list_gift_cards' => $this->input->post('clear-list_gift_cards'),
				'list_purchase_requests' => $this->input->post('clear-list_purchase_requests'),
				'list_purchase_orders' => $this->input->post('clear-list_purchase_orders'),
				'list_purchases' => $this->input->post('clear-list_purchases'),
				'list_receives' => $this->input->post('clear-list_receives'),
				'purchase_returns' => $this->input->post('clear-purchase_returns'),
				'list_expenses' => $this->input->post('clear-list_expenses'),
				'list_pawns' => $this->input->post('clear-list_pawns'),
				'list_pawn_returns' => $this->input->post('clear-list_pawn_returns'),
				'list_pawn_purchases' => $this->input->post('clear-list_pawn_purchases'),
				'list_customers' => $this->input->post('clear-list_customers'),
				'list_suppliers' => $this->input->post('clear-list_suppliers'),
				'list_chart_accounts' => $this->input->post('clear-list_chart_accounts'),
				'list_enter_journals' => $this->input->post('clear-list_enter_journals'),
            );
        }
        if ($this->form_validation->run() == true && $this->settings_model->clearSystem($data)) {
            $this->session->set_flashdata('message', lang("system_is_cleared"));
            redirect("welcome");
        } else {
			if(date("hi")!=$id){
				$this->session->set_flashdata('error', lang("access_denied"));
				redirect("welcome");
			}
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('clear')));
            $meta = array('page_title' => lang('clear'), 'bc' => $bc);
            $this->core_page('settings/clear', $meta, $this->data);
        }
    }

	public function customer_opening_balances($biller_id = null)
	{
		$this->cus->checkPermissions('index',null,'customer_opening_balances');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('customer_opening_balances')));
        $meta = array('page_title' => lang('customer_opening_balances'), 'bc' => $bc);
        $this->core_page('settings/customer_opening_balances', $meta, $this->data);
	}

	public function getCustomerOpeningBalances($biller_id = null)
    {
        $this->cus->checkPermissions('index',null,'customer_opening_balances');

		if(($this->Settings->installment && isset($this->GP['installments-add'])) || ($this->Settings->installment && ($this->Owner || $this->Admin) ) ){
			$simulate_link = anchor('installments/add/$1', '<i class="fa fa-plus-circle"></i> ' . lang('add_installment'),'');
		}

		$add_link = anchor('sales/add_payment/$1', '<i class="fa fa-edit"></i> ' . lang('add_payment_customer_opening_balance'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$view_link = anchor('sales/payments/$1', '<i class="fa fa-edit"></i> ' . lang('view_payment_customer_opening_balances'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $edit_link = anchor('system_settings/edit_customer_opening_balance/$1', '<i class="fa fa-edit"></i> ' . lang('edit_customer_opening_balance'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("customer_opening_balance") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_customer_opening_balance/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_customer_opening_balance') . "</a>";

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
			<li>' . $simulate_link . '</li>
            <li>' . $add_link . '</li>
			<li>' . $view_link . '</li>
			<li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('sales') . ".id as id,
				DATE_FORMAT(date, '%Y-%m-%d %T') as date,
				reference_no,
				customer,
				grand_total,
				paid,
				(grand_total - paid) as balance", false)
            ->from('sales')
            ->where('suspend_note','CUSTOMER_OPENING_BALANCE')
			->group_by('sales.id');
		if ($biller_id) {
            $this->datatables->where('sales.biller_id', $biller_id);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }

		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}

        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

	public function delete_customer_opening_balance($id = null)
    {
        $this->cus->checkPermissions('index',null,'customer_opening_balances');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->settings_model->deleteCustomerOpeningBalance($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("customer_opening_balance_deleted");
				die();
            }
            $this->session->set_flashdata('message', lang('customer_opening_balance_deleted'));
            redirect('system_settings/customer_opening_balances');
        }
    }

	public function add_customer_opening_balance()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }

			$biller_id = $this->input->post('biller', true);
			$customer_id = $this->input->post('customer', true);
			$warehouse_id = $this->input->post('warehouse', true);
			$reference_no = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('customer_opening',$biller_id);
			$note = $this->input->post('note', true);
			$total = $this->input->post('amount', true);
			$user_id = $this->session->userdata('user_id');
			$project_id = $this->input->post('project', true);
			$customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$openAcc = $this->site->getAccountSettingByBiller($biller_id);
            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'created_by' => $user_id,
				'warehouse_id' => $warehouse_id,
                'note' => $note,
				'total' => $total,
				'grand_total' => $total,
				'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'project_id' => $project_id,
				'payment_status' => 'pending',
				'sale_status' => 'draft',
				'account_code' => $this->input->post('opening_account'),
				'ar_account' => $openAcc->ar_acc,
				'suspend_note' => 'CUSTOMER_OPENING_BALANCE',
            );
			//=======acounting=========//
			if($this->Settings->accounting == 1){
				$accTrans[] = array(
					'transaction' => 'CustomerOpening',
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $openAcc->ar_acc,
					'amount' => $total,
					'narrative' => 'Customer Opening Balance '.$customer,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'user_id' => $this->session->userdata('user_id'),
					'customer_id' => $customer_id,
				);

				$accTrans[] = array(
					'transaction' => 'CustomerOpening',
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $this->input->post('opening_account'),
					'amount' => -$total,
					'narrative' => 'Customer Opening Balance '.$customer,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'user_id' => $this->session->userdata('user_id'),
					'customer_id' => $customer_id,
				);

			}
			//============end accounting=======//
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCustomerOpeningBalance($data,$accTrans)) {
		   $this->session->set_flashdata('message', lang("customer_opening_balance_added"));
           redirect('system_settings/customer_opening_balances');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber'] = $this->site->getReference('customer_opening', isset($biller_id)? $biller_id: '');
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['customers'] = $this->companies_model->getAllCustomerCompanies();
			$this->data['billers'] = $this->companies_model->getAllBillerCompanies();
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount(array('EQ'));
			}
			if($this->Settings->project == 1){
				$this->data['projects'] = $this->site->getAllProjects();
			}
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_customer_opening_balance', $this->data);
        }
    }

	public function add_customer_opening_balance_excel()
    {
		$this->cus->checkPermissions('index',null,'customer_opening_balances');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
		$this->form_validation->set_rules('biller', lang("biller"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$project_id = $this->input->post('project');
			$warehouse_id = $this->input->post('warehouse');
			$opening_account = $this->input->post('opening_account');
			$user_id = $this->session->userdata('user_id');
			$openAcc = $this->site->getAccountSettingByBiller($biller_id);
            if ($this->Owner || $this->Admin || $this->GP['system_settings-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }

			if (isset($_FILES["userfile"])) {
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++)
					{
						$customer_code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$amount = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$reference = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						$final[] = array(
							'customer_code'  => trim($customer_code),
							'amount'   => (float) $amount,
							'reference'   => trim($reference),
						);
					}
				}
				foreach($final as $row){
					if($row['customer_code']!=''){
						if($customer_details = $this->site->getCustomerByCode($row['customer_code'])){
							$customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
							$datas[] = array(
								'date' => $date,
								'created_by' => $user_id,
								'warehouse_id' => $warehouse_id,
								'total' => $row['amount'],
								'grand_total' => $row['amount'],
								'reference_no' => $row['reference'],
								'customer_id' => $customer_details->id,
								'customer' => $customer,
								'biller_id' => $biller_id,
								'biller' => $biller,
								'project_id' => $project_id,
								'payment_status' => 'pending',
								'sale_status' => 'draft',
								'account_code' => $opening_account,
								'suspend_note' => 'CUSTOMER_OPENING_BALANCE',
							);
						}else{
							$datas = false;
							$error = lang('customer_not_found').' '.$row['customer_code'];
							$this->session->set_flashdata('error', $error);
							redirect($_SERVER["HTTP_REFERER"]);
						}
					}
				}

				if($datas){
					foreach($datas as $row){
						if($row['reference_no']==""){
							$reference_no = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('customer_opening',$biller_id);
							$row['reference_no'] = $reference_no;
						}
						$data[] = $row;
					}
				}
            }
        }
        if ($this->form_validation->run() == true && $this->settings_model->addCustomerOpeningBalanceExcel($data)) {
            $this->session->set_flashdata('message', lang("customer_opening_balance_added"));
            redirect('system_settings/customer_opening_balances');
		}else {
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount(array('EQ'));
			}
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/add_customer_opening_balance_excel', $this->data);

        }
    }

	public function edit_customer_opening_balance($id = null)
    {
        $this->cus->checkPermissions('index',null,'customer_opening_balances');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
		$customer_opening_balance = $this->settings_model->getCustomerOpeningBalanceByID($id);
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = $customer_opening_balance->date;
            }

            $biller_id = $this->input->post('biller', true);
			$project_id = $this->input->post('project', true);
			$customer_id = $this->input->post('customer', true);
			$warehouse_id = $this->input->post('warehouse', true);
			$reference_no = $this->input->post('reference');
			$note = $this->input->post('note', true);
			$total = $this->input->post('amount', true);
			$user_id = $this->session->userdata('user_id');

			$customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$openAcc = $this->site->getAccountSettingByBiller($biller_id);
            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $user_id,
				'warehouse_id' => $warehouse_id,
                'note' => $note,
				'total' => $total,
				'grand_total' => $total,
				'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
				'project_id' => $project_id,
                'biller' => $biller,
				'suspend_note' => 'CUSTOMER_OPENING_BALANCE',
				'account_code' => $this->input->post('opening_account'),
				'ar_account' => $openAcc->ar_acc,
            );

			//=======acounting=========//
			if($this->Settings->accounting == 1){
				$accTrans[] = array(
					'transaction' => 'CustomerOpening',
					'transaction_id' => $id,
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $openAcc->ar_acc,
					'amount' => $total,
					'narrative' => 'Customer Opening Balance '.$customer,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'user_id' => $this->session->userdata('user_id'),
					'customer_id' => $customer_id,
				);

				$accTrans[] = array(
					'transaction' => 'CustomerOpening',
					'transaction_id' => $id,
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $this->input->post('opening_account'),
					'amount' => -$total,
					'narrative' => 'Customer Opening Balance '.$customer,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'user_id' => $this->session->userdata('user_id'),
					'customer_id' => $customer_id,
				);

			}
			//============end accounting=======//

        }

        if ($this->form_validation->run() == true && $this->settings_model->updateCustomerOpeningBalance($id, $data, $accTrans)) {
            $this->session->set_flashdata('message', lang("customer_opening_balance_updated"));
            redirect("system_settings/customer_opening_balances");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['customer_opening_balance'] = $customer_opening_balance;
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['projects'] = $this->site->getAllProjects();
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount(array('EQ'),$customer_opening_balance->account_code);
			}
			$this->data['customers'] = $this->companies_model->getAllCustomerCompanies();
			$this->data['billers'] = $this->companies_model->getAllBillerCompanies();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_customer_opening_balance', $this->data);
        }
    }

	public function customer_opening_balance_actions()
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
						$this->settings_model->deleteCustomerOpeningBalance($id);
					}
					$this->session->set_flashdata('message', lang("customer_opening_balance_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				}else if ($this->input->post('form_action') == 'export_excel') {

					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('customer_opening_balances'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('amount'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('balance'));

					$row = 2;
					foreach ($_POST['val'] as $id) {
						$customer_opening = $this->settings_model->getCustomerOpeningBalanceByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($customer_opening->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer_opening->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer_opening->biller);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $customer_opening->customer);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer_opening->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $customer_opening->paid);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, ($customer_opening->grand_total - abs($customer_opening->paid)));
						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'customer_opening_balance_' . date('Y_m_d_H_i_s');
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

	public function supplier_opening_balances($biller_id = NULL)
	{
		$this->cus->checkPermissions('index',null,'supplier_opening_balances');
		$this->data['billers'] = $this->site->getBillers();
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('supplier_opening_balances')));
        $meta = array('page_title' => lang('supplier_opening_balances'), 'bc' => $bc);
        $this->core_page('settings/supplier_opening_balances', $meta, $this->data);
	}

	public function getSupplierOpeningBalances($biller_id = NULL)
    {
		$this->cus->checkPermissions('index',null,'supplier_opening_balances');
		$add_link = anchor('purchases/add_payment/$1', '<i class="fa fa-plus-circle"></i> ' . lang('add_payment_supplier_opening_balance'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$view_link = anchor('purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payment_supplier_opening_balances'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $edit_link = anchor('system_settings/edit_supplier_opening_balance/$1', '<i class="fa fa-edit"></i> ' . lang('edit_supplier_opening_balance'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("supplier_opening_balance") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_supplier_opening_balance/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_supplier_opening_balance') . "</a>";

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $add_link . '</li>
			<li>' . $view_link . '</li>
			<li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select($this->db->dbprefix('purchases') . ".id as id,
				DATE_FORMAT(date, '%Y-%m-%d %T') as date,
				reference_no,
				supplier,
				grand_total,
				paid,
				(grand_total - paid) as balance", false)
            ->from('purchases')
			->where('status','draft')
            ->group_by('purchases.id');
		if ($biller_id) {
			$this->datatables->where('purchases.biller_id', $biller_id);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}


        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

	public function delete_supplier_opening_balance($id = null)
    {
		$this->cus->checkPermissions('index',null,'supplier_opening_balances');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteSupplierOpeningBalance($id)) {
            echo lang("supplier_opening_balance_deleted");
        }
    }

	public function add_supplier_opening_balance()
    {
		$this->cus->checkPermissions('index',null,'supplier_opening_balances');
        $this->load->helper('security');
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->cus->GP['purchases-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }

			$biller_id = $this->input->post('biller', true);
			$project_id = $this->input->post('project', true);
			$supplier_id = $this->input->post('supplier', true);
			$warehouse_id = $this->input->post('warehouse', true);
			$reference_no = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('supplier_opening',$biller_id);
			$note = $this->input->post('note', true);
			$total = $this->input->post('amount', true);
			$user_id = $this->session->userdata('user_id');

			$supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$openAcc = $this->site->getAccountSettingByBiller($biller_id);
            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'created_by' => $user_id,
				'warehouse_id' => $warehouse_id,
                'note' => $note,
				'total' => $total,
				'grand_total' => $total,
				'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'project_id' => $project_id,
				'account_code' => $this->input->post('opening_account'),
				'ap_account' => $openAcc->ap_acc,
				'status' => 'draft',
            );
			//=======acounting=========//
			if($this->Settings->accounting == 1){

				$accTrans[] = array(
					'transaction' => 'SupplierOpening',
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $openAcc->ap_acc,
					'amount' => -$total,
					'narrative' => 'Supplier Opening Balance '.$supplier,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'user_id' => $this->session->userdata('user_id'),
					'supplier_id' => $supplier_id,
				);

				$accTrans[] = array(
					'transaction' => 'SupplierOpening',
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $this->input->post('opening_account'),
					'amount' => $total,
					'narrative' => 'Supplier Opening Balance '.$supplier,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'user_id' => $this->session->userdata('user_id'),
					'supplier_id' => $supplier_id,
				);

			}
			//============end accounting=======//

        }

        if ($this->form_validation->run() == true && $this->settings_model->addSupplierOpeningBalance($data,$accTrans)) {
            $this->session->set_flashdata('message', lang("supplier_opening_balance_added"));
            redirect("system_settings/supplier_opening_balances");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['suppliers'] = $this->companies_model->getAllSupplierCompanies();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['billers'] = $this->companies_model->getAllBillerCompanies();
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount(array('EQ'));
			}
			if($this->Settings->project == 1){
				$this->data['projects'] = $this->site->getAllProjects();
			}
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_supplier_opening_balance', $this->data);
        }
    }

	function add_supplier_opening_balance_excel()
    {
		$this->cus->checkPermissions('index',null,'supplier_opening_balances');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
		$this->form_validation->set_rules('biller', lang("biller"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$project_id = $this->input->post('project');
			$warehouse_id = $this->input->post('warehouse');
			$opening_account = $this->input->post('opening_account');
			$user_id = $this->session->userdata('user_id');
			$openAcc = $this->site->getAccountSettingByBiller($biller_id);
            if ($this->Owner || $this->Admin || $this->GP['system_settings-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }

			if (isset($_FILES["userfile"])) {
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++)
					{
						$supplier_code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$amount = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$reference = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						$final[] = array(
							'supplier_code'  => trim($supplier_code),
							'amount'   => (float) $amount,
							'reference'   => trim($reference),
						);
					}
				}
				foreach($final as $row){
					if($row['supplier_code']!=''){
						if($supplier_details = $this->site->getSupplierByCode($row['supplier_code'])){
							$supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
							$datas[] = array(
								'date' => $date,
								'created_by' => $user_id,
								'warehouse_id' => $warehouse_id,
								'total' => $row['amount'],
								'grand_total' => $row['amount'],
								'reference_no' => $row['reference'],
								'supplier_id' => $supplier_details->id,
								'supplier' => $supplier,
								'biller_id' => $biller_id,
								'biller' => $biller,
								'project_id' => $project_id,
								'status' => 'draft',
								'account_code' => $opening_account,
							);

						}else{
							$datas = false;
							$error = lang('supplier_not_found').' '.$row['supplier_code'];
							$this->session->set_flashdata('error', $error);
							redirect($_SERVER["HTTP_REFERER"]);
						}
					}
				}

				if($datas){
					foreach($datas as $row){
						if($row['reference_no']==""){
							$reference_no = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('supplier_opening',$biller_id);
							$row['reference_no'] = $reference_no;
						}
						$data[] = $row;
					}
				}
            }
        }
        if ($this->form_validation->run() == true && $this->settings_model->addSupplierOpeningBalanceExcel($data)) {
            $this->session->set_flashdata('message', lang("supplier_opening_balance_added"));
            redirect('system_settings/supplier_opening_balances');
		}else {
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount(array('EQ'));
			}
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/add_supplier_opening_balance_excel', $this->data);

        }
    }

	public function edit_supplier_opening_balance($id = null)
    {
        $this->cus->checkPermissions('index',null,'supplier_opening_balances');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
		$supplier_opening_balance = $this->settings_model->getSupplierOpeningBalanceByID($id);
        if ($this->form_validation->run() == true) {

			if ($this->Owner || $this->Admin || $this->cus->GP['purchases-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = $supplier_opening_balance->date;
            }

            $biller_id = $this->input->post('biller', true);
			$project_id = $this->input->post('project', true);
			$supplier_id = $this->input->post('supplier', true);
			$warehouse_id = $this->input->post('warehouse', true);
			$reference_no = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('supplier_opening',$biller_id);
			$note = $this->input->post('note', true);
			$total = $this->input->post('amount', true);
			$user_id = $this->session->userdata('user_id');

			$supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$openAcc = $this->site->getAccountSettingByBiller($biller_id);
            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'updated_by' => $user_id,
				'warehouse_id' => $warehouse_id,
                'note' => $note,
				'total' => $total,
				'grand_total' => $total,
				'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'biller_id' => $biller_id,
				'project_id' => $project_id,
                'biller' => $biller,
				'status' => 'draft',
				'account_code' => $this->input->post('opening_account'),
				'ap_account' => $openAcc->ap_acc,
            );
			//=======acounting=========//
			if($this->Settings->accounting == 1){
				$accTrans[] = array(
					'transaction' => 'SupplierOpening',
					'transaction_id' => $id,
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $openAcc->ap_acc,
					'amount' => -$total,
					'narrative' => 'Supplier Opening Balance '.$supplier,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'user_id' => $this->session->userdata('user_id'),
					'supplier_id' => $supplier_id,
				);

				$accTrans[] = array(
					'transaction' => 'SupplierOpening',
					'transaction_id' => $id,
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $this->input->post('opening_account'),
					'amount' => $total,
					'narrative' => 'Supplier Opening Balance '.$supplier,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'user_id' => $this->session->userdata('user_id'),
					'supplier_id' => $supplier_id,
				);

			}
			//============end accounting=======//

        }

        if ($this->form_validation->run() == true && $this->settings_model->updateSupplierOpeningBalance($id, $data, $accTrans)) {
            $this->session->set_flashdata('message', lang("supplier_opening_balance_updated"));
            redirect("system_settings/supplier_opening_balances");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['supplier_opening_balance'] = $supplier_opening_balance;
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['projects'] = $this->site->getAllProjects();
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount(array('EQ'),$supplier_opening_balance->account_code);
			}
			$this->data['suppliers'] = $this->companies_model->getAllSupplierCompanies();
			$this->data['billers'] = $this->companies_model->getAllBillerCompanies();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_supplier_opening_balance', $this->data);
        }
    }

	public function supplier_opening_balance_actions()
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
						$this->settings_model->deleteSupplierOpeningBalance($id);
					}
					$this->session->set_flashdata('message', lang("supplier_opening_balance_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				}else if ($this->input->post('form_action') == 'export_excel') {

					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('supplier_opening_balance'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('supplier'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('amount'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('balance'));

					$row = 2;
					foreach ($_POST['val'] as $id) {
						$supplier_opening = $this->settings_model->getSupplierOpeningBalanceByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($supplier_opening->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $supplier_opening->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $supplier_opening->biller);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $supplier_opening->supplier);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $supplier_opening->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $supplier_opening->paid);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, ($supplier_opening->grand_total - abs($supplier_opening->paid)));
						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'supplier_opening_' . date('Y_m_d_H_i_s');
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

	public function tanks()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('tanks')));
        $meta = array('page_title' => lang('tanks'), 'bc' => $bc);
        $this->core_page('settings/tanks', $meta, $this->data);
	}

	public function getTanks()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
						tanks.id as id,
						tanks.code,
						tanks.name,
						warehouses.name as warehouse_name
					")
            ->from("tanks")
			->join("warehouses","warehouses.id = tanks.warehouse_id","left")
			->add_column("Actions", "<center><a class=\"tip\" title='" . lang("view_nozzle_start_no") . "' href='" . site_url('system_settings/view_nozzle_start_no/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> <a class=\"tip\" title='" . lang("add_nozzle_start_no") . "' href='" . site_url('system_settings/add_nozzle_start_no/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-plus\"></i></a> <a class=\"tip\" title='" . $this->lang->line("edit_tank") . "' href='" . site_url('system_settings/edit_tank/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("edit_tank") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_tank/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

	public function delete_tank($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteTank($id)) {
            echo $this->lang->line("tank_deleted"); exit;
        }
		$this->session->set_flashdata('message', lang("tank_deleted"));
        redirect("system_settings/floors");
    }

	public function add_tank()
    {
        $this->form_validation->set_rules('name', $this->lang->line("code"), 'is_unique[tanks.code]');
        if ($this->form_validation->run() == true) {
            $data = array(
					'code' => $this->input->post('code'),
					'name' => $this->input->post('name'),
					'warehouse_id' => $this->input->post('warehouse')
				);
        }else if($this->input->post('add_tank')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addTank($data)) {
            $this->session->set_flashdata('message', lang("tank_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->load->view($this->theme . 'settings/add_tank', $this->data);
        }
    }

	public function edit_tank($id = false)
    {
		$tank = $this->settings_model->getTankByID($id);
		$this->form_validation->set_rules('code', $this->lang->line("code"), 'required');
		if ($this->input->post('code') != $tank->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[tanks.code]');
        }
        if ($this->form_validation->run() == true) {
            $data = array(
					'code' => $this->input->post('code'),
					'name' => $this->input->post('name'),
					'warehouse_id' => $this->input->post('warehouse'),
					'inactive' => $this->input->post('inactive')
				);
        }else if($this->input->post('edit_tank')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateTank($id, $data)) {
             $this->session->set_flashdata('message', lang("tank_updated"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
			$this->data['id'] = $id;
			$this->data['row'] = $tank;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->load->view($this->theme . 'settings/edit_tank', $this->data);
        }
    }

	public function tank_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteTank($id);
                    }
                    $this->session->set_flashdata('message', lang("tanks_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tanks'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getTankByID($id);
						$warehouse = $this->site->getWarehouseByID($sc->warehouse_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
						$this->excel->getActiveSheet()->SetCellValue('c' . $row, $warehouse->name);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'tanks_' . date('Y_m_d_H_i_s');
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

    function getProductWarehouseAlert($warehouse_id = NULL)
    {
        if (!$warehouse_id) {
            $this->session->set_flashdata('error', lang('no_warehouse_selected'));
            redirect('system_settings/warehouses');
        }

        $pp = "( SELECT {$this->db->dbprefix('warehouses_products')}.product_id as product_id, {$this->db->dbprefix('warehouses_products')}.alert_quantity as alert_quantity  FROM {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id} ) PP";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id,{$this->db->dbprefix('categories')}.`name` as category_name, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('products')}.name as product_name, {$this->db->dbprefix('products')}.alert_quantity as alert_quantity, IFNULL(PP.alert_quantity,0) as wh_alert_qty")
            ->from("products")
			->join("categories","categories.id= products.category_id")
            ->join($pp, 'PP.product_id=products.id', 'left')
            ->edit_column("wh_alert_qty", "$1__$2", 'id, wh_alert_qty')
            ->add_column("Actions", "<div class=\"text-center\"><button class=\"btn btn-primary btn-xs form-submit\" type=\"button\"><i class=\"fa fa-check\"></i></button></div>", "id");

        echo $this->datatables->generate();
    }

    function product_alert_warehouse($warehouse_id = NULL)
    {
        if (!$warehouse_id) {
            $this->session->set_flashdata('error', lang('no_warehouse_selected'));
            redirect('system_settings/warehouses');
        }

		$this->data['id'] = $warehouse_id;
		$this->data['warehouse'] = $this->site->getWarehouseByID($warehouse_id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),  array('link' => site_url('system_settings/product_alert_warehouse'), 'page' => lang('product_alert_warehouse')), array('link' => '#', 'page' => lang('product_alert_warehouse')));
        $meta = array('page_title' => lang('product_alert_warehouse'), 'bc' => $bc);
        $this->core_page('settings/product_alert_warehouse', $meta, $this->data);
    }

    function update_product_alert_warehouse($warehouse_id = NULL)
    {
        if (!$warehouse_id) {
            $this->cus->send_json(array('status' => 0));
        }
        $product_id = $this->input->post('product_id');
        $alert_qty = $this->input->post('alert_qty');

        if (!empty($product_id)) {
            if ($this->settings_model->updateProductWarehouseAlertQty($product_id, $warehouse_id, $alert_qty)) {
                $this->cus->send_json(array('status' => 1));
            }
        }

        $this->cus->send_json(array('status' => 0));
    }

    function product_alert_warehouse_actions($warehouse_id)
    {
        if (!$warehouse_id) {
            $this->session->set_flashdata('error', lang('no_warehouse_selected'));
            redirect('system_settings/customer_prices');
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_alert') {

                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->updateProductWarehouseAlertQty($id, $warehouse_id, $this->input->post('alert_qty'.$id));
                    }
                    $this->session->set_flashdata('message', lang("product_warehouse_alert_updated"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('product_alert_warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('product_alert_qty'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse_alert_qty'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('warehouse'));
                    $row = 2;
                    $warehouse = $this->site->getWarehouseByID($warehouse_id);
                    foreach ($_POST['val'] as $id) {
                        $pgp = $this->settings_model->getProductWarehouseAlert($id, $warehouse_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pgp->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pgp->name);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $pgp->product_alert);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pgp->warehouse_alert);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $warehouse->name);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'product_alert_warehouse_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_customer_product_price_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function update_warehouse_alert_excel($warehouse_id = NULL)
    {
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
					for($row=2; $row<=$highestRow; $row++)
					{
						$code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$alert_qty = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						if(trim($code)!=''){
							$final[] = array(
							  'code'  => $code,
							  'alert_qty'   => $alert_qty,

							);
						}

					}
				}
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if ($product = $this->site->getProductByCode(trim($csv_pr['code']))) {
                    $data[] = array(
                        'product_id' => $product->id,
                        'alert_quantity' => $csv_pr['alert_qty']
                        );
                    } else {
                        $this->session->set_flashdata('error', lang("check_product_code") . " (" . $csv_pr['code'] . "). " . lang("code_x_exist") . " " . lang("line_no") . " " . $rw);
                        redirect("system_settings/product_alert_warehouse/".$warehouse_id);
                    }
                    $rw++;
                }
            }else{
				$this->session->set_flashdata('error', lang("check_file"));
                redirect("system_settings/product_alert_warehouse/".$warehouse_id);
			}

        } elseif ($this->input->post('update_alert')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/product_alert_warehouse/".$warehouse_id);
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            if($data){
                foreach($data as $row){
                    $this->settings_model->updateProductWarehouseAlertQty($row['product_id'], $warehouse_id, $row['alert_quantity']);
                }
                $this->session->set_flashdata('message', lang("product_warehouse_alert_updated"));
                redirect("system_settings/product_alert_warehouse/".$warehouse_id);
            }

        } else {

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['warehouse'] = $this->site->getWarehouseByID($warehouse_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/update_warehouse_alert_excel', $this->data);

        }
    }

	function add_nozzle_start_no($id = false)
    {
		$this->form_validation->set_rules('nozzle_no', $this->lang->line("nozzle_no"), 'required');
        if ($this->form_validation->run() == true){
            $data = array(
				'tank_id'	=> $id,
				'product_id'		=> $this->input->post('product'),
				'nozzle_no' 			=> $this->input->post('nozzle_no'),
				'nozzle_start_no' 	=> $this->input->post('nozzle_start_no'),
				'saleman_id' => json_encode($this->input->post('saleman')),
			);
			$salesman = false;
			if($this->input->post('saleman')){
				foreach($this->input->post('saleman') as $saleman){
					$salesman[] = array("tank_id"=>$id,"saleman_id" => $saleman);
				}
			}
        }else if($this->input->post('add_nozzle_start_no')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addNozzleStartNo($data,$salesman)) {
            $this->session->set_flashdata('message', lang("nozzle_start_no_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['id'] = $id;
			$this->data['tank'] = $this->settings_model->getTankByID($id);
			$this->data['products'] = $this->settings_model->getAllProducts();
			$this->data['salemans'] = $this->site->getSalemans();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_nozzle_start_no', $this->data);
        }
    }

	function edit_nozzle_start_no($id = false)
    {
		$row = $this->settings_model->getNozzleStartNoByID($id);
		$this->form_validation->set_rules('nozzle_no', $this->lang->line("nozzle_no"), 'required');
        if ($this->form_validation->run() == true){
            $data = array(
				'tank_id'	=> $row->tank_id,
				'product_id'		=> $this->input->post('product'),
				'nozzle_no' 			=> $this->input->post('nozzle_no'),
				'nozzle_start_no' 	=> $this->input->post('nozzle_start_no'),
				'saleman_id' => json_encode($this->input->post('saleman')),
			);
			$salesman = false;
			if($this->input->post('saleman')){
				foreach($this->input->post('saleman') as $saleman){
					$salesman[] = array("tank_id"=>$row->tank_id,"nozzle_id"=>$row->id,"saleman_id" => $saleman);
				}
			}
			
        }else if($this->input->post('edit_nozzle_start_no')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateNozzleStartNo($id, $data, $salesman)) {
            $this->session->set_flashdata('message', lang("nozzle_start_no_updated"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['id'] = $id;
			$this->data['row'] = $row;
			$this->data['products'] = $this->settings_model->getAllProducts();
			$this->data['salemans'] = $this->site->getSalemans();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_nozzle_start_no', $this->data);
        }
    }

	function view_nozzle_start_no($id = false)
	{
		$this->data['id'] = $id;
		$this->data['tank'] = $this->settings_model->getTankByID($id);
		$this->data['modal_js'] = $this->site->modal_js();
		$this->load->view($this->theme . 'settings/view_nozzle_start_no', $this->data);
	}

	function getNozzleStartNo($id = NULL)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
					tank_nozzles.id as id,
					products.name as product_name,
					tank_nozzles.nozzle_no,
					tank_nozzles.nozzle_start_no", false)
            ->from("tank_nozzles")
			->join("products","products.id=product_id","left")
            ->where($this->db->dbprefix('tank_nozzles').'.tank_id', $id)
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("edit_nozzle_start_no") . "' href='" . site_url('system_settings/edit_nozzle_start_no/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_nozzle_start_no") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_nozzle_start_no/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
		$this->datatables->unset_column("id");
		echo $this->datatables->generate();
    }

	public function delete_nozzle_start_no($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteNozzleStartNo($id)) {
            echo $this->lang->line("nozzle_start_no_deleted"); exit;
        }
		$this->session->set_flashdata('message', lang("nozzle_start_no_deleted"));
        redirect($_SERVER['HTTP_REFERER']);
    }

	function product_promotions()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('product_promotions')));
        $meta = array('page_title' => lang('product_promotions'), 'bc' => $bc);
        $this->core_page('settings/product_promotions', $meta, $this->data);
    }

	function getProductPromotions()
    {
        $this->load->library('datatables');
		$this->datatables->select("id, name, start_date, end_date, IF(status=1,'active','inactive') as status");
		$this->datatables->from("product_promotions");
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/product_promotion_items/$1') . "' class='tip' title='" . lang("product_promotion_items") . "'><i class=\"fonts fa fa-eye\"></i></a>  <a href='" . site_url('system_settings/edit_product_promotion/$1') . "' class='tip' title='" . lang("edit_product_promotion") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_product_promotion") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_product_promotion/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id,category_id");
        echo $this->datatables->generate();
    }

	function add_product_promotion()
    {
        $this->form_validation->set_rules('name', lang("product_promotion"), 'trim|is_unique[product_promotions.name]|required');
        $this->form_validation->set_rules('from_date', lang("from_date"), 'trim|required');
        $this->form_validation->set_rules('to_date', lang("to_date"), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = array();
            $data['name'] = $this->input->post('name');
            $data['start_date'] = $this->cus->fld($this->input->post('from_date'));
            $data['end_date'] = $this->cus->fld($this->input->post('to_date'));
            $data['description'] = $this->input->post('description');
            $data['status'] = $this->input->post('status');
        } elseif ($this->input->post('add_product_promotion')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/product_promotions");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addProductPromotion($data)) {
            $this->session->set_flashdata('message', lang("product_promotion_added"));
            redirect("system_settings/product_promotions");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_product_promotion', $this->data);
        }
    }

	function edit_product_promotion($id = NULL)
    {

        $this->form_validation->set_rules('name', lang("product_promotion"), 'trim|required');
		$pg_details = $this->settings_model->getProductPromationByID($id);
        if ($this->input->post('name') != $pg_details->name) {
            $this->form_validation->set_rules('name', lang("product_promotion"), 'is_unique[product_promotions.name]');
        }
        if ($this->form_validation->run() == true) {
            $data['name'] = $this->input->post('name');
			$data['start_date'] = $this->cus->fld($this->input->post('from_date'));
            $data['end_date'] = $this->cus->fld($this->input->post('to_date'));
            $data['description'] = $this->input->post('description');
            $data['status'] = $this->input->post('status');
        } elseif ($this->input->post('edit_product_promotion')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/product_promotions");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateProductPromotion($id, $data)) {
            $this->session->set_flashdata('message', lang("product_promotion_updated"));
            redirect("system_settings/product_promotions");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['product_promotion'] = $pg_details;
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_product_promotion', $this->data);
        }
    }

	function delete_product_promotion($id = NULL)
    {
        if ($this->settings_model->deleteProductPromotion($id)) {
            echo lang("product_promotion_deleted");
        }
    }

	function product_promotion_items($promotion_id = NULL)
    {
        if (!$promotion_id) {
            $this->session->set_flashdata('error', lang('no_product_promotions_selected'));
            redirect('system_settings/product_promotions');
        }

		$this->data['id'] = $promotion_id;
        $this->data['product_promotion'] = $this->settings_model->getProductPromationByID($promotion_id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),  array('link' => site_url('system_settings/product_promotions'), 'page' => lang('product_promotions')), array('link' => '#', 'page' => lang('product_promotion_items')));
        $meta = array('page_title' => lang('product_promotion_items'), 'bc' => $bc);
        $this->core_page('settings/product_promotion_items', $meta, $this->data);
    }

    function getProductPromotionItems($promotion_id = NULL)
    {
		$allow_category = $this->site->getCategoryByProject();
        $this->load->library('datatables');
        $this->datatables
            ->select("products.id as id, categories.name, products.code, products.name  as product_name")
            ->from("products")
			->join("categories","categories.id= products.category_id");
			
		if($allow_category){
			$this->datatables->where_in("products.category_id",$allow_category);
		}	
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/product_promotion_formulations/'.$promotion_id.'/$1') . "' class='tip' title='" . lang("product_promotion_formulations") . "'><i class=\"fa fa-barcode\"></i></a></div>", "id");

        echo $this->datatables->generate();
    }

	function product_promotion_formulations($promotion_id = NULL, $product_id = NULL)
	{

		$this->form_validation->set_rules('promotion_id', lang("promotion_id"), 'required');
		$this->form_validation->set_rules('product_id', lang("product_id"), 'required');
		if ($this->form_validation->run() == true) {
			$promotion_id = $this->input->post('promotion_id');
			$product_id = $this->input->post('product_id');
			$i = isset($_POST['for_product_id']) ? sizeof($_POST['for_product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
				$for_product_id = $_POST['for_product_id'][$r];
				if($for_product_id){
					$for_min_quantity = $_POST['for_min_quantity'][$r];
					$for_max_quantity = $_POST['for_max_quantity'][$r];
					$for_free_quantity = $_POST['for_free_quantity'][$r];
					$data[] = array(
								'promotion_id' => $promotion_id,
								'main_product_id' => $product_id,
								'for_product_id' => $for_product_id,
								'for_min_quantity' => $for_min_quantity,
								'for_max_quantity' => $for_max_quantity,
								'for_free_quantity' => $for_free_quantity,
							);
				}
			}
		}
		if ($this->form_validation->run() == true && $this->settings_model->addProductPromotionItem($data, $promotion_id, $product_id)) {
            $this->session->set_flashdata('message', lang("product_promotion_formulation_added"));
            redirect('system_settings/product_promotion_formulations/'.$promotion_id.'/'.$product_id);
        }else{
			$promotion = $this->settings_model->getProductPromationByID($promotion_id);
			$product = $this->site->getProductByID($product_id);
			if($promotion && $product) {
				$this->data['promotion'] = $promotion;
				$this->data['product'] = $product;
				$product_promotions = $this->settings_model->getProductPromationItems($promotion_id, $product_id);
				if($product_promotions){
					$this->data['product_promotions'] = $product_promotions;
				}else{
					$product_promotions[] = (object) array('for_product_id'=>$product_id,'product_name'=>$product->name);
					$this->data['product_promotions'] = $product_promotions;
				}

				$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
				$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),  array('link' => site_url('system_settings/product_promotions'), 'page' => lang('product_promotions')), array('link' => site_url('system_settings/product_promotion_items/'.$promotion_id.''), 'page' => lang('product_promotion_items')), array('link' => '#', 'page' => lang('product_promotion_formulations')));
				$meta = array('page_title' => lang('product_promotion_formulations'), 'bc' => $bc);
				$this->core_page('settings/product_promotion_formulations', $meta, $this->data);
            }else{
				$this->session->set_flashdata('error', lang('no_product_promotions_selected'));
				redirect('system_settings/product_promotions');
			}
		}
	}

	function saleman_targets()
    {

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('saleman_targets')));
        $meta = array('page_title' => lang('saleman_targets'), 'bc' => $bc);
        $this->core_page('settings/saleman_targets', $meta, $this->data);
    }

    function getSalemanTargets()
    {

        $this->load->library('datatables');
        $this->datatables
            ->select("saleman_targets.id as id,salesman_groups.name as group_name, saleman_targets.description, saleman_targets.min_amount, saleman_targets.max_amount, saleman_targets.commission")
            ->from("saleman_targets")
			->join("salesman_groups","salesman_groups.id = saleman_targets.group_id","left")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_saleman_target/$1') . "' class='tip' title='" . lang("edit_saleman_target") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_saleman_target") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_saleman_target/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id");

        echo $this->datatables->generate();
    }

    function add_saleman_target()
    {
        $this->form_validation->set_rules('description', lang("description"), 'required');
		$this->form_validation->set_rules('commission', lang("commission"), 'required');
        $this->form_validation->set_rules('min_amount', lang("min_amount"), 'required|numeric');
		$this->form_validation->set_rules('max_amount', lang("max_amount"), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = array(
							'description' => $this->input->post('description'),
							'min_amount' => $this->input->post('min_amount'),
							'max_amount' => $this->input->post('max_amount'),
							'commission' => $this->input->post('commission'),
							'group_id' => $this->input->post('group'),
						);
        } elseif ($this->input->post('add_saleman_target')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/saleman_targets");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addSalemanTarget($data)) {
            $this->session->set_flashdata('message', lang("saleman_target_added"));
            redirect("system_settings/saleman_targets");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['groups'] = $this->site->getSalesmanGroups();
            $this->data['page_title'] = lang("add_saleman_target");
            $this->load->view($this->theme . 'settings/add_saleman_target', $this->data);
        }
    }

    function edit_saleman_target($id = NULL)
    {
		$saleman_target = $this->settings_model->getSalemanTargetByID($id);
        $this->form_validation->set_rules('description', lang("description"), 'required');
		$this->form_validation->set_rules('commission', lang("commission"), 'required');
        $this->form_validation->set_rules('min_amount', lang("min_amount"), 'required|numeric');
		$this->form_validation->set_rules('max_amount', lang("max_amount"), 'required|numeric');
        if ($this->form_validation->run() == true) {
            $data = array(
							'description' => $this->input->post('description'),
							'min_amount' => $this->input->post('min_amount'),
							'max_amount' => $this->input->post('max_amount'),
							'commission' => $this->input->post('commission'),
							'group_id' => $this->input->post('group'),
						);
        } elseif ($this->input->post('edit_saleman_target')) {
            $this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateSalemanTarget($id, $data)) {
            $this->session->set_flashdata('message', lang("saleman_target_updated"));
			redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['saleman_target'] = $saleman_target;
			$this->data['groups'] = $this->site->getSalesmanGroups();
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_saleman_target', $this->data);
        }
    }

    function delete_saleman_target($id = NULL)
    {

        if ($this->settings_model->deleteSalemanTarget($id)) {
            echo lang("saleman_target_deleted");
        }
    }

    function saleman_target_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteSalemanTarget($id);
                    }
                    $this->session->set_flashdata('message', lang("saleman_target_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('saleman_targets'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('description'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('min_amount'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('max_amount'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('commission'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getSalemanTargetByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->description);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->min_amount);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->max_amount);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->commission);
                        $row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'saleman_targets_' . date('Y_m_d_H_i_s');
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

	function modal_ivo($id)
	{
		$this->cus->checkPermissions('inventory_opening_balances', TRUE);
		$opening_balance = $this->settings_model->getInvntoryOpeningBlanceByID($id);
        if (!$id || !$opening_balance) {
            $this->session->set_flashdata('error', lang('inventory_opening_balance_not_found'));
            $this->cus->md();
        }
        $this->data['inv'] = $opening_balance;
		$this->data['biller'] = $this->site->getCompanyByID($opening_balance->biller_id);
        $this->data['rows'] = $this->settings_model->getInvntoryOpeningBlanceitems($id);
        $this->data['created_by'] = $this->site->getUser($opening_balance->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($opening_balance->warehouse_id);
        $this->load->view($this->theme.'settings/modal_ivo', $this->data);
	}

	function inventory_opening_balances($warehouse_id=NULL, $biller_id = NULL)
    {
        $this->cus->checkPermissions('inventory_opening_balances');
		$this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('inventory_opening_balances')));
        $meta = array('page_title' => lang('inventory_opening_balances'), 'bc' => $bc);
        $this->core_page('settings/inventory_opening_balances', $meta, $this->data);
    }

	function getInventoryOpeningBalance($warehouse_id=NULL, $biller_id = NULL)
    {
        $this->cus->checkPermissions('inventory_opening_balances');

		$delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete11' href='" . site_url('system_settings/delete_inventory_opening_balance/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('inventory_opening_balances')}.id as id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by, note, attachment")
            ->from('inventory_opening_balances')
            ->join('users', 'users.id=inventory_opening_balances.created_by', 'left')
            ->group_by("inventory_opening_balances.id");

			if ($biller_id) {
                $this->datatables->where('inventory_opening_balances.biller_id', $biller_id);
            }
			if ($warehouse_id) {
                $this->datatables->where('inventory_opening_balances.warehouse_id', $warehouse_id);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('inventory_opening_balances.biller_id =', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('inventory_opening_balances.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('inventory_opening_balances.created_by', $this->session->userdata('user_id'));
			}
        $this->datatables->add_column("Actions", "<div class='text-center'><a href='" . site_url('system_settings/edit_inventory_opening_balance/$1') . "' class='tip' title='" . lang("edit") . "'><i class='fonts fa fa-edit'></i></a> " . $delete_link . "</div>", "id");
        echo $this->datatables->generate();

    }

	    function add_inventory_opening_balance()
    {
        $this->cus->checkPermissions('inventory_opening_balances', true);
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        if ($this->form_validation->run() == true) {
            $biller_id = $this->input->post('biller');
            $project_id = $this->input->post('project');
            $warehouse_id = $this->input->post('warehouse');
            if ($this->Owner || $this->Admin || $this->GP['system_settings-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('io',$biller_id);
            $note = $this->cus->clear_tags($this->input->post('note'));
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            $openAcc = $this->site->getAccountSettingByBiller($biller_id);
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r];
                $unit_qty = $_POST['unit_qty'][$r];
                $cost = $_POST['cost'][$r];
                $unit_cost = $_POST['unit_cost'][$r];
                $unit_id = $_POST['unit'][$r];
                $product_details = $this->products_model->getProductByID($product_id);
                $unit = $this->site->getProductUnit($product_details->id,$unit_id);
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->cus->fsd($_POST['expiry'][$r]) : null;
                $products[] = array(
                    'product_id' => $product_id,
                    'quantity' =>$quantity,
                    'unit_qty' =>$unit_qty,
                    'cost' =>$cost,
                    'unit_cost' =>$unit_cost,
                    'unit_id' =>$unit_id,
                    'expiry' => $item_expiry
                );
                $stockmoves[] = array(
                        'transaction' => 'OpeningBalance',
                        'product_id' => $product_id,
                        'product_code' => $product_details->code,
                        'warehouse_id' => $warehouse_id,
                        'quantity' => $quantity,
                        'unit_quantity' => $unit->unit_qty,
                        'unit_code' => $unit->code,
                        'unit_id' => $unit_id,
                        'date' => $date,
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $cost,
                        'reference_no' => $reference_no,
                        'user_id' => $this->session->userdata('user_id'),
                    );

                if($this->Settings->accounting == 1 && $cost > 0){
                    $productAcc = $this->site->getProductAccByProductId($product_details->id);
                    $amount = $quantity * $cost;
                    $accTrans[] = array(
                        'transaction' => 'OpeningBalance',
                        'transaction_date' => $date,
                        'reference' => $reference_no,
                        'account' => $productAcc->stock_acc,
                        'amount' => $amount,
                        'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                    );
                    $accTrans[] = array(
                        'transaction' => 'OpeningBalance',
                        'transaction_date' => $date,
                        'reference' => $reference_no,
                        'account' => $openAcc->open_balance_acc,
                        'amount' => -$amount,
                        'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                    );

                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'biller_id' => $biller_id,
                'project_id' => $project_id,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

        }
        if ($this->form_validation->run() == true && $this->settings_model->addInventoryOpeningBlanace($data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_ivols', 1);
            $this->session->set_flashdata('message', lang("inventory_opening_balance_added")." - ".$data['reference_no']);
            redirect('system_settings/inventory_opening_balances');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),array('link' => site_url('system_settings/inventory_opening_balances'), 'page' => lang('inventory_opening_balances')), array('link' => '#', 'page' => lang('add_inventory_opening_balance')));
            $meta = array('page_title' => lang('add_inventory_opening_balance'), 'bc' => $bc);
            $this->core_page('settings/add_inventory_opening_balance', $meta, $this->data);

        }
    }

    function edit_inventory_opening_balance($id)
    {
        $this->cus->checkPermissions('inventory_opening_balances', true);
        $opening_balance = $this->settings_model->getInvntoryOpeningBlanceByID($id);
        if (!$id || !$opening_balance) {
            $this->session->set_flashdata('error', lang('inventory_opening_balance_not_found'));
            $this->cus->md();
        }
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        if ($this->form_validation->run() == true) {
            $biller_id = $this->input->post('biller');
            $project_id = $this->input->post('project');
            $openAcc = $this->site->getAccountSettingByBiller($biller_id);
            if ($this->Owner || $this->Admin || $this->GP['system_settings-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = $opening_balance->date;
            }
            $warehouse_id = $this->input->post('warehouse');
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('io',$biller_id);
            $note = $this->cus->clear_tags($this->input->post('note'));

            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r];
                $unit_qty = $_POST['unit_qty'][$r];
                $cost = $_POST['cost'][$r];
                $unit_cost = $_POST['unit_cost'][$r];
                $unit_id = $_POST['unit'][$r];
                $product_details = $this->products_model->getProductByID($product_id);
                $unit = $this->site->getProductUnit($product_details->id,$unit_id);
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->cus->fsd($_POST['expiry'][$r]) : null;
                $products[] = array(
                    'opening_id' => $id,
                    'product_id' => $product_id,
                    'quantity' =>$quantity,
                    'unit_qty' =>$unit_qty,
                    'cost' =>$cost,
                    'unit_cost' =>$unit_cost,
                    'unit_id' =>$unit_id,
                    'expiry' => $item_expiry
                );
                $stockmoves[] = array(
                        'transaction' => 'OpeningBalance',
                        'transaction_id' => $id,
                        'product_id' => $product_id,
                        'product_code' => $product_details->code,
                        'warehouse_id' => $warehouse_id,
                        'quantity' => $quantity,
                        'unit_quantity' => $unit->unit_qty,
                        'unit_code' => $unit->code,
                        'unit_id' => $unit_id,
                        'date' => $date,
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $cost,
                        'reference_no' => $reference_no,
                        'user_id' => $this->session->userdata('user_id'),
                    );
                if($this->Settings->accounting == 1 && $cost > 0){
                    $productAcc = $this->site->getProductAccByProductId($product_details->id);
                    $amount = $quantity * $cost ;
                    $accTrans[] = array(
                        'transaction' => 'OpeningBalance',
                        'transaction_id' => $id,
                        'transaction_date' => $date,
                        'reference' => $reference_no,
                        'account' => $productAcc->stock_acc,
                        'amount' => $amount,
                        'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                    );
                    $accTrans[] = array(
                        'transaction' => 'OpeningBalance',
                        'transaction_id' => $id,
                        'transaction_date' => $date,
                        'reference' => $reference_no,
                        'account' => $openAcc->open_balance_acc,
                        'amount' => -$amount,
                        'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                    );

                }

            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'biller_id' => $biller_id,
                'project_id' => $project_id,
                'note' => $note,
                'warehouse_id' => $warehouse_id,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
                );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateInventoryOpeningBalance($id, $data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_ivols', 1);
            $this->session->set_flashdata('message', lang("inventory_opening_balance_updated")." - ".$data['reference_no']);
            redirect('system_settings/inventory_opening_balances');
        } else {
            $inv_items = $this->settings_model->getInvntoryOpeningBlanceitems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $product = $this->site->getProductByID($item->product_id);
                $row = json_decode('{}');
                $row->id = $item->product_id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->quantity = $item->quantity;
                $row->unit_qty = $item->unit_qty;
                $row->cost = $item->cost;
                $row->unit_cost = $item->unit_cost;
                $row->unit = $item->unit_id;
                $units = $this->site->getUnitbyProduct($product->id,$product->unit);
                if($item->expiry!='0000-00-00' && $item->expiry !=''){
                    $row->expiry = $this->cus->hrsd($item->expiry);
                }else{
                    $row->expiry = '';
                }
                $ri = $this->Settings->item_addition ? $product->id : $c;       
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row,'units' => $units);
                $c++;
            }

            $this->data['opening_balance'] = $opening_balance;
            $this->data['opening_items'] = json_encode($pr);
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),array('link' => site_url('system_settings/inventory_opening_balances'), 'page' => lang('inventory_opening_balances')), array('link' => '#', 'page' => lang('edit_inventory_opening_balance')));
            $meta = array('page_title' => lang('edit_inventory_opening_balance'), 'bc' => $bc);
            $this->core_page('settings/edit_inventory_opening_balance', $meta, $this->data);

        }
    }

	public function delete_inventory_opening_balance($id)
	{
		$this->cus->checkPermissions('inventory_opening_balances', TRUE);
		$row = $this->settings_model->getInvntoryOpeningBlanceByID($id);
		if ($this->settings_model->deleteInventoryOpeningBalance($id)) {
			if($this->input->is_ajax_request()) {
				echo lang("inventory_opening_balance_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('inventory_opening_balance_deleted')." - ". $row->reference_no);
		}
		redirect('system_settings/inventory_opening_balances');
	}

	function inventory_opening_balance_actions()
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
						$this->settings_model->deleteInventoryOpeningBalance($id);
					}
					$this->session->set_flashdata('message', lang("inventory_opening_balance_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				}else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('inventory_opening_balance');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('items'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $opening = $this->settings_model->getInvntoryOpeningBlanceByID($id);
                        $created_by = $this->site->getUser($opening->created_by);
						$biller = $this->site->getCompanyByID($opening->biller_id);
						$warehouse = $this->site->getWarehouseByID($opening->warehouse_id);
						$items = $this->settings_model->getInvntoryOpeningBlanceitems($id);
                        $products = '';
                        if ($items) {
                            foreach ($items as $item) {
								$product_detail = $this->site->getProductByID($item->product_id);
								$products .= $product_detail->name.' ('.lang('quantity').' = '.$item->quantity.')'."\n";
                            }
                        }

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($opening->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $opening->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $biller->company);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $warehouse->name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $created_by->last_name.' ' .$created_by->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->decode_html($opening->note));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $products);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'inventory_opening_balance_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

	function add_inventory_opening_balance_excel()
    {
		$this->cus->checkPermissions('inventory_opening_balances', TRUE);
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
		$this->form_validation->set_rules('biller', lang("biller"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$warehouse_id = $this->input->post('warehouse');
			$openAcc = $this->site->getAccountSettingByBiller($biller_id);
            if ($this->Owner || $this->Admin || $this->GP['system_settings-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$products = false;
			if (isset($_FILES["userfile"])) {
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++)
					{
						$code = $worksheet->getCellByColumnAndRow(0, $row)->getFormattedValue();
						$item_expiry = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$quantity = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						$unit_code = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						$unit_cost = $worksheet->getCellByColumnAndRow(4, $row)->getValue();

						if ($item_expiry !='' && strpos($item_expiry, '/') == false) {
							$item_expiry = PHPExcel_Shared_Date::ExcelToPHP($item_expiry);
							$item_expiry = date('d/m/Y',$item_expiry);
						}
						$final[] = array(
							'code'  => trim($code),
							'quantity'   => (float) $quantity,
							'item_expiry'   => $item_expiry,
							'unit_code'   => trim($unit_code),
							'unit_cost'   => trim($unit_cost),
						);
					}
				}
				foreach($final as $row){
					if($row['code']!=''){
						if($product_details = $this->products_model->getProductByCode($row['code'])){
							if($row['unit_cost'] > 0){
								$unit_cost = $row['unit_cost'];
							}else{
								$unit_cost = $product_details->cost;
							}
							$cost = $unit_cost;
							$unit_qty = $row['quantity'];
							if($row['unit_code']){
								$unit = $this->settings_model->getUnitQtyByCode($product_details->id,$row['unit_code']);
								if(!$unit){
									$this->session->set_flashdata('error', lang("unit_code") . " (" . $product_details->code." - ".$row['unit_code'] . "). " . lang("code__exist"));
									redirect("system_settings/inventory_opening_balances");
								}else{
									$quantity = $unit_qty * $unit->unit_qty;
									$cost = $unit_cost / $unit->unit_qty;
									$unit_id = $unit->unit_id;;
								}
							}else{
								$unit = $this->site->getProductUnit($product_details->id,$product_details->unit);
								$quantity = $row['quantity'];
								$unit_id = $product_details->unit;;
							}
							
							$item_expiry = $row['item_expiry'];
							if($quantity > 0){
								$products[] = array(
													'product_id' => $product_details->id,
													'quantity' =>$quantity,
													'unit_qty' =>$unit_qty,
													'cost' =>$cost,
													'unit_cost' =>$unit_cost,
													'unit_id' =>$unit_id,	
													'expiry' => $this->cus->fsd($item_expiry),

													);
								$stockmoves[] = array(
													'transaction' => 'OpeningBalance',
													'product_id' => $product_details->id,
													'product_code' => $product_details->code,
													'warehouse_id' => $warehouse_id,
													'quantity' => $quantity,
													'unit_quantity' => $unit->unit_qty,
													'unit_code' => $unit->code,
													'unit_id' => $unit_id,
													'date' => $date,
													'expiry' => $this->cus->fsd($item_expiry),
													'real_unit_cost' => $cost,
													'reference_no' => $reference_no,
													'user_id' => $this->session->userdata('user_id'),
												);

								if($this->Settings->accounting == 1 && $cost > 0){
									$productAcc = $this->site->getProductAccByProductId($product_details->id);
									$amount = $quantity * $cost ;
									$accTrans[] = array(
										'transaction' => 'OpeningBalance',
										'transaction_date' => $date,
										'reference' => $reference_no,
										'account' => $productAcc->stock_acc,
										'amount' => $amount,
										'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
									);
									$accTrans[] = array(
										'transaction' => 'OpeningBalance',
										'transaction_date' => $date,
										'reference' => $reference_no,
										'account' => $openAcc->open_balance_acc,
										'amount' => -$amount,
										'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
									);

								}
							}
						}else{
							$products = false;
							$error = lang('no_product_found').' '.$row['code'];
							$this->session->set_flashdata('error', $error);
							redirect($_SERVER["HTTP_REFERER"]);
						}
					}
				}
            }

			if($products){
				$reference_no = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('io',$biller_id);
				$data = array(
					'date' => $date,
					'reference_no' => $reference_no,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'warehouse_id' => $warehouse_id,
					'note' => $note,
					'created_by' => $this->session->userdata('user_id'),
				);
				if ($_FILES['document']['size'] > 0) {
					$this->load->library('upload');
					$config['upload_path'] = $this->digital_upload_path;
					$config['allowed_types'] = $this->digital_file_types;
					$config['max_size'] = $this->allowed_file_size;
					$config['overwrite'] = false;
					$config['encrypt_name'] = true;
					$this->upload->initialize($config);
					if (!$this->upload->do_upload('document')) {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$photo = $this->upload->file_name;
					$data['attachment'] = $photo;
				}
			}else{
				$this->form_validation->set_rules('product', lang("products"), 'required');
			}
        }
        if ($this->form_validation->run() == true && $this->settings_model->addInventoryOpeningBlanace($data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_ivols', 1);
            $this->session->set_flashdata('message', lang("inventory_opening_balance_added")." - ".$data['reference_no']);
            redirect('system_settings/inventory_opening_balances');
		}else {
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/inventory_opening_balance_excel', $this->data);

        }
    }

	public function suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $rows = $this->settings_model->getAllProductNames($sr);
        if ($rows) {
            foreach ($rows as $row) {
				$row->quantity = 1;
				$row->unit_cost = $row->cost;
				$row->unit_qty = 1;
				$units = $this->site->getUnitbyProduct($row->id,$row->unit);
				$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'units'=> $units);
			}
			$this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

	public function fuel_times()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('fuel_times')));
        $meta = array('page_title' => lang('fuel_times'), 'bc' => $bc);
        $this->core_page('settings/fuel_times', $meta, $this->data);
	}

	public function getFuelTimes()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
					fuel_times.id as id,
					fuel_times.open_time,
					fuel_times.close_time")
            ->from("fuel_times")
			->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_fuel_time") . "' href='" . site_url('system_settings/edit_fuel_time/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_fuel_time") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_fuel_time/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

	public function add_fuel_time()
    {
        $this->form_validation->set_rules('open_time', $this->lang->line("open_time"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
					'open_time' => $this->input->post('open_time'),
					'close_time' => $this->input->post('close_time')
				);
        }else if($this->input->post('add_fuel_time')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addFuelTime($data)) {
            $this->session->set_flashdata('message', lang("fuel_time_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_fuel_time', $this->data);
        }
    }

	public function edit_fuel_time($id = false)
    {
		$fuel = $this->settings_model->getFuelTimesByID($id);
		$this->form_validation->set_rules('open_time', $this->lang->line("open_time"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
					'open_time' => $this->input->post('open_time'),
					'close_time' => $this->input->post('close_time')
				);
        }else if($this->input->post('edit_fuel_time')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateFuelTime($id, $data)) {
             $this->session->set_flashdata('message', lang("fuel_time_updated"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
			$this->data['id'] = $id;
			$this->data['fuel'] = $fuel;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_fuel_time', $this->data);
        }
    }

	public function fuel_time_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteFuelTime($id);
                    }
                    $this->session->set_flashdata('message', lang("fuel_time_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('fuel_time'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('open_time'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('close_time'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $fuel = $this->settings_model->getFuelTimesByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $fuel->open_time);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $fuel->close_time);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'fuel_time_' . date('Y_m_d_H_i_s');
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

	public function delete_fuel_time($id = NULL)
    {
        if ($this->settings_model->deleteFuelTime($id)) {
            echo lang("fuel_time_deleted");
        }
    }

	function areas()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('areas')));
        $meta = array('page_title' => lang('areas'), 'bc' => $bc);
        $this->core_page('settings/areas', $meta, $this->data);
    }

    function getAreas()
    {
        $this->load->library('datatables');
		$delete_area = "<a href='#' class='tip po' title='<b>" . lang("delete_area") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete111111' href='" . site_url('system_settings/delete_area/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";
        $this->datatables
            ->select("areas.id as id, areas.name, cities.name as city, districts.name as district, commune.name as commune")
            ->from("areas")
			->join("(SELECT id,name FROM ".$this->db->dbprefix('areas')." WHERE IFNULL(city_id,0) = 0) as cities","cities.id = areas.city_id","left")
			->join("(SELECT id,name FROM ".$this->db->dbprefix('areas')." WHERE city_id > 0 AND IFNULL(district_id,0) = 0) as districts","districts.id = areas.district_id","left")
            ->join("(SELECT id,name FROM ".$this->db->dbprefix('areas')." WHERE district_id > 0 AND IFNULL(commune_id,0) = 0) as commune","commune.id = areas.commune_id","left")
			->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_area/$1') . "' class='tip' title='" . lang("edit_area") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> {$delete_area}</div>", "id");
        echo $this->datatables->generate();
    }

    function add_area()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
				'name' => $this->input->post('name'),
				'city_id' => ($this->input->post('city_id') ? $this->input->post('city_id') : 0),
				'district_id' => ($this->input->post('district_id') ? $this->input->post('district_id') : 0),
				'commune_id' => ($this->input->post('commune_id') ? $this->input->post('commune_id') : 0)
			);
        } elseif ($this->input->post('add_area')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/areas");
        }
        if ($this->form_validation->run() == true && $this->settings_model->addArea($data)) {
            $this->session->set_flashdata('message', lang("area_added"));
            redirect("system_settings/areas");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['cities'] = $this->site->getCities();
            $this->load->view($this->theme . 'settings/add_area', $this->data);
        }
    }

    function edit_area($id = NULL)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
			$data = array(
				'name' => $this->input->post('name'),
				'city_id' => ($this->input->post('city_id') ? $this->input->post('city_id') : 0),
				'district_id' => ($this->input->post('district_id') ? $this->input->post('district_id') : 0),
				'commune_id' => ($this->input->post('commune_id') ? $this->input->post('commune_id') : 0)
			);
        } elseif ($this->input->post('edit_area')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/areas");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateArea($id, $data)) {
            $this->session->set_flashdata('message', lang("area_updated"));
            redirect("system_settings/areas");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $area = $this->settings_model->getAreaByID($id);
			$this->data['id'] = $id;
			$this->data['area'] = $area;
			$this->data['cities'] = $this->site->getCities();
			$this->data['districts'] = $area->city_id > 0 ? $this->site->getDistricts($area->city_id) : false;
            $this->data['communes'] = $area->district_id > 0 ? $this->site->getCommunes($area->district_id) : false;
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_area', $this->data);
        }
    }

    function delete_area($id = NULL)
    {
        if ($this->settings_model->deleteArea($id)) {
			$this->session->set_flashdata('message', lang('area_deleted'));
        }
		redirect('system_settings/areas');
    }

    function area_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteArea($id);
                    }
                    $this->session->set_flashdata('message', lang("areas_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('areas'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('city'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('district'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('commune'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $area = $this->settings_model->getAreaByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $area->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $area->city);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $area->district);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $area->commune);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'areas_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_area_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function get_district(){
		$city_id = $this->input->get('city_id');
		$districts = $this->site->getDistricts($city_id);
		echo json_encode($districts);
	}
	public function get_commune(){
		$district_id = $this->input->get('district_id');
		$communes = $this->site->getCommunes($district_id);
		echo json_encode($communes);
	}

	function salesman_groups()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('salesman_groups')));
        $meta = array('page_title' => lang('salesman_groups'), 'bc' => $bc);
        $this->core_page('settings/salesman_groups', $meta, $this->data);
    }

    function getGroupSalesman()
    {
        $this->load->library('datatables');
		$delete_salesman_group = "<a href='#' class='tip po' title='<b>" . lang("delete_salesman_group") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete111111' href='" . site_url('system_settings/delete_salesman_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a>";
        $this->datatables
            ->select("id as id, name, description")
            ->from("salesman_groups")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_salesman_group/$1') . "' class='tip' title='" . lang("edit_salesman_group") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a> {$delete_salesman_group}</div>", "id");
        echo $this->datatables->generate();
    }

    function add_salesman_group()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('name', lang("name"), 'trim|is_unique[salesman_groups.name]|required');
        if ($this->form_validation->run() == true) {
            $data = array(
				'name' => $this->input->post('name'),
				'description' => $this->input->post('description'),

			);
        } elseif ($this->input->post('add_salesman_group')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/salesman_groups");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addSalesmanGroup($data)) {
            $this->session->set_flashdata('message', lang("salesman_group_added"));
            redirect("system_settings/salesman_groups");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_salesman_group', $this->data);
        }
    }

    function edit_salesman_group($id = NULL)
    {
        $this->load->helper('security');
        $salesman_group = $this->settings_model->getSalesmanGroupByID($id);

		$this->form_validation->set_rules('name', lang("name"), 'trim|required');
        if ($this->input->post('name') != $salesman_group->name) {
            $this->form_validation->set_rules('name', lang("name"), 'is_unique[salesman_groups.name]');
        }
        if ($this->form_validation->run() == true) {
			$data = array(
				'name' => $this->input->post('name'),
				'description' => $this->input->post('description'),
			);
        } elseif ($this->input->post('edit_salesman_group')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/salesman_groups");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateSalesmanGroup($id, $data)) {
            $this->session->set_flashdata('message', lang("salesman_group_updated"));
            redirect("system_settings/salesman_groups");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['salesman_group'] = $salesman_group;
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_salesman_group', $this->data);
        }
    }

    function delete_salesman_group($id = NULL)
    {
        if ($this->settings_model->deleteSalesmanGroup($id)) {
			$this->session->set_flashdata('message', lang('salesman_group_deleted'));
        }
		redirect('system_settings/salesman_groups');
    }

    function salesman_group_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteSalesmanGroup($id);
                    }
                    $this->session->set_flashdata('message', lang("salesman_group_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('salesman_groups'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('description'));


                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $salesman_group = $this->settings_model->getSalesmanGroupByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $salesman_group->name);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $salesman_group->description);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'salesman_group_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_area_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

	public function models()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('models')));
        $meta = array('page_title' => lang('models'), 'bc' => $bc);
        $this->core_page('settings/models', $meta, $this->data);
	}

	public function getModels()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
					models.id as id,
					models.code,
					models.name,
					brands.name as brand
					")
            ->from("models")
			->join('brands','brands.id=models.brand_id','left')
			->add_column("Actions", "<center> <a class=\"tip\" title='" . $this->lang->line("edit_model") . "' href='" . site_url('system_settings/edit_model/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("edit_model") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_model/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

	public function model_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteModel($id);
                    }
                    $this->session->set_flashdata('message', lang("models_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('models'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('brand'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getModelByID($id);
						$bd = $this->settings_model->getBrandByID($sc->brand_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $bd->name);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'models_' . date('Y_m_d_H_i_s');
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

	public function delete_model($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteModel($id)) {
            echo $this->lang->line("model_deleted"); exit;
        }
		$this->session->set_flashdata('message', lang("model_deleted"));
        redirect("system_settings/floors");
    }

	public function add_model()
    {
        $this->form_validation->set_rules('code', $this->lang->line("code"), 'is_unique[models.code]');
        if ($this->form_validation->run() == true) {
            $data = array(
					'code' => $this->input->post('code'),
					'name' => $this->input->post('name'),
					'brand_id' => $this->input->post('brand')
				);
        }else if($this->input->post('add_model')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addModel($data)) {
            $this->session->set_flashdata('message', lang("model_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['brands'] = $this->site->getBrands();
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_model', $this->data);
        }
    }

	public function edit_model($id = false)
    {
		$model = $this->settings_model->getModelByID($id);
		$this->form_validation->set_rules('code', $this->lang->line("code"), 'required');
		if ($this->input->post('code') != $model->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[models.code]');
        }
        if ($this->form_validation->run() == true) {
            $data = array(
					'code' => $this->input->post('code'),
					'name' => $this->input->post('name'),
					'brand_id' => $this->input->post('brand')
				);
        }else if($this->input->post('edit_model')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateModel($id, $data)) {
             $this->session->set_flashdata('message', lang("model_updated"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
			$this->data['id'] = $id;
			$this->data['row'] = $model;
			$this->data['brands'] = $this->site->getBrands();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_model', $this->data);
        }
    }

	function import_models()
    {

        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');
                $config['upload_path'] = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("system_settings/models");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen('files/' . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('name', 'code', 'brand');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                foreach ($final as $csv_ct) {
                    if ( ! $this->settings_model->getBrandByName(trim($csv_ct['name']))) {
						$brand = $this->settings_model->getBrandByCode(trim($csv_ct['brand']));
                        $data[] = array(
                            'code' => trim($csv_ct['code']),
                            'name' => trim($csv_ct['name']),
                            'brand_id' => (int)$brand->id,
                            );
                    }
                }
            }

            // $this->cus->print_arrays($data);
        }

        if ($this->form_validation->run() == true && !empty($data) && $this->settings_model->addModels($data)) {
            $this->session->set_flashdata('message', lang("models_added"));
            redirect('system_settings/models');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/import_models', $this->data);

        }
    }

	public function modal_product_promotion($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['rows'] = $this->settings_model->getMainProductPromotion($id);
        $this->data['product_promotion'] = $this->settings_model->getProductPromotionByID($id);
		$company = $this->site->getAllCompanies('biller');
		$biller_id = $company[0]->id;
		$this->data['biller'] = $this->site->getCompanyByID($biller_id);
        $this->load->view($this->theme . 'settings/modal_product_promotion', $this->data);

    }

	function cash_accounts()
    {
		$this->cus->checkPermissions("cash_account");
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('cash_accounts')));
        $meta = array('page_title' => lang('cash_accounts'), 'bc' => $bc);
        $this->core_page('settings/cash_accounts', $meta, $this->data);
    }
    function getCashAccounts()
    {
		$this->cus->checkPermissions("cash_account");
        $this->load->library('datatables');
        $this->datatables
            ->select("cash_accounts.id as id, cash_accounts.code, cash_accounts.name,cash_accounts.type", FALSE)
            ->from("cash_accounts")
            ->group_by('cash_accounts.id')
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_cash_account/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal' class='tip' title='" . lang("edit_cash_account") . "'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_cash_account") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete124' href='" . site_url('system_settings/delete_cash_account/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id");

        echo $this->datatables->generate();
    }
	
    function add_cash_account()
    {
		$this->cus->checkPermissions("cash_account",true);	
        $this->form_validation->set_rules('code', lang("cash_account_code"), 'trim|is_unique[cash_accounts.code]|required');
        $this->form_validation->set_rules('name', lang("cash_account_name"), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = array(
							'name' => $this->input->post('name'),
							'code' => $this->input->post('code'),
							'account_code' => $this->input->post('account_code'),
							'type' => $this->input->post('type')
						);
        } elseif ($this->input->post('add_cash_account')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->settings_model->addCashAccount($data)) {
            $this->session->set_flashdata('message', lang("cash_account_added")." ".$data['code']." ".$data['name']);
		   redirect("system_settings/cash_accounts");
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['account'] = $this->site->getAccount('','','1');
            $this->load->view($this->theme . 'settings/add_cash_account', $this->data);
        }
    }
	
    function edit_cash_account($id = NULL)
    {
		$this->cus->checkPermissions("cash_account",true);	
        $this->form_validation->set_rules('code', lang("code"), 'trim|required');
        $cash_account_details = $this->site->getCashAccountByID($id);
        if ($this->input->post('code') != $cash_account_details->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[cash_accounts.code]');
        }
        $this->form_validation->set_rules('name', lang("name"), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = array(
							'name' => $this->input->post('name'),
							'code' => $this->input->post('code'),
							'account_code' => $this->input->post('account_code'),
							'type' => $this->input->post('type')
						);
        } elseif ($this->input->post('edit_cash_account')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/cash_accounts");
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateCashAccount($id, $data)) {
            $this->session->set_flashdata('message', lang("cash_account_updated")." ".$data['code']);
            redirect("system_settings/cash_accounts");
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['cash_account'] = $cash_account_details;
			if($this->Settings->accounting == 1){
				$this->data['account'] = $this->site->getAccount('',$cash_account_details->account_code,'1');
			}
            $this->load->view($this->theme . 'settings/edit_cash_account', $this->data);
        }
    }
	
    function delete_cash_account($id = NULL)
    {
		$this->cus->checkPermissions("cash_account",true);	
        if ($this->settings_model->deleteCashAccount($id)) {
            echo lang("cash_account_deleted");
        }
		$this->session->set_flashdata('message', lang("cash_account_deleted")." ".$id['code']." ".$id['name']);
        redirect("system_settings/cash_accounts");
    }
	
    function cash_account_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCashAccount($id);
                    }
                    $this->session->set_flashdata('message', lang("cash_accounts_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('cash_accounts'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('type'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $cash_account = $this->site->getCashAccountByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $cash_account->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $cash_account->name);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, lang($cash_account->type));
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'cash_accounts_' . date('Y_m_d_H_i_s');
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
	
	function boms() {
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('boms')));
        $meta = array('page_title' => lang('boms'), 'bc' => $bc);
        $this->core_page('settings/boms', $meta, $this->data);
    }

	function getBoms(){
        $this->cus->checkPermissions('boms');
		$delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete11' href='" . site_url('system_settings/delete_bom/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("boms.id as id, boms.name, CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by,  attachment")
            ->from('boms')
            ->join('users', 'users.id=boms.created_by', 'left')
            ->group_by("boms.id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('boms.created_by', $this->session->userdata('user_id'));
			}
        $this->datatables->add_column("Actions", "<div class='text-center'><a href='" . site_url('system_settings/edit_bom/$1') . "' class='tip' title='" . lang("edit") . "'><i class='fonts fa fa-edit'></i></a> " . $delete_link . "</div>", "id");
        echo $this->datatables->generate();
    }
	
    function add_bom(){
        $this->cus->checkPermissions('boms', true);
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $name = $this->input->post('name');
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r];
                $unit_qty = $_POST['unit_qty'][$r];
                $unit_id = $_POST['unit'][$r];
                $unit = $this->site->getProductUnit($product_id,$unit_id);
                $products[] = array(
                    'product_id' => $product_id,
                    'quantity' =>$quantity,
                    'unit_qty' =>$unit_qty,
                    'unit_id' =>$unit_id,
                    'type' =>'raw_material'
                );
            }
            
            $f = isset($_POST['fproduct_id']) ? sizeof($_POST['fproduct_id']) : 0;
            for ($r = 0; $r < $f; $r++) {
                $product_id = $_POST['fproduct_id'][$r];
                $quantity = $_POST['fquantity'][$r];
                $unit_qty = $_POST['funit_qty'][$r];
                $unit_id = $_POST['funit'][$r];
                $unit = $this->site->getProductUnit($product_id,$unit_id);
                $finished_goods[] = array(
                    'product_id' => $product_id,
                    'quantity' =>$quantity,
                    'unit_qty' =>$unit_qty,
                    'unit_id' =>$unit_id,
                    'type' =>'finished_good'
                );
            }
            if (empty($products) || empty($finished_goods)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
                krsort($finished_goods);
            }
            
            $data = array(
                'name' => $name,
                'created_by' => $this->session->userdata('user_id'),
            );
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

        }
        if ($this->form_validation->run() == true && $this->settings_model->addBom($data, $products, $finished_goods)) {
            $this->session->set_userdata('remove_bomls', 1);
            $this->session->set_flashdata('message', lang("bom_added"));
            redirect('system_settings/boms');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),array('link' => site_url('system_settings/boms'), 'page' => lang('boms')), array('link' => '#', 'page' => lang('add_bom')));
            $meta = array('page_title' => lang('add_bom'), 'bc' => $bc);
            $this->core_page('settings/add_bom', $meta, $this->data);
        }
    }

    function branch_prefix()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('branch_prefix')));
        $meta = array('page_title' => lang('branch_prefix'), 'bc' => $bc);
        $this->core_page('settings/branch_prefix', $meta, $this->data);
    }

    function getBranchPrefix()
    {

        $this->load->library('datatables');
        $this->datatables
            ->select("order_ref.ref_id as id, date, bill_prefix, companies.company,companies.phone, companies.address", FALSE)
            ->from("order_ref")
            ->join("companies","companies.id=order_ref.bill_id","left")

            ->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_branch_prefix") . "' href='" . site_url('system_settings/edit_branch_prefix/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_branch_prefix") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_branch_prefix/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></center>", "id");

        echo $this->datatables->generate();
    }

    public function edit_branch_prefix($id = false)
    {
        $branch_prefix = $this->settings_model->getBranchPrefixByID($id);
        $this->form_validation->set_rules('bill_prefix', $this->lang->line("bill_prefix"), 'required');
        if ($this->input->post('bill_prefix') != $branch_prefix->bill_prefix) {
            $this->form_validation->set_rules('bill_prefix', lang("bill_prefix"), 'is_unique[branch_prefix.name]');
        }

        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin || $this->GP['system_settings-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $data = array(
                    'date' => $date,
                    'bill_prefix' => $this->input->post('bill_prefix'),
                    'qu' => $this->input->post('qu'),
                    'so' => $this->input->post('so'),
                    'pos' => $this->input->post('pos'),
                    'pay' => $this->input->post('pay'),
                    'ppay' => $this->input->post('ppay'),
                    'ex' => $this->input->post('ex'),
                    'jn' => $this->input->post('jn'),
                    'inst' => $this->input->post('inst'),
                    'po' => $this->input->post('po')


                );

        }else if($this->input->post('edit_branch_prefix')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateBranchPrefix($id, $data)) {
            $this->session->set_flashdata('message', lang("branch_prefix_updated"));
            redirect("system_settings/branch_prefix");
        } else {
            $this->data['id'] = $id;
            $branch_prefix_id = $this->settings_model->getBranchPrefixByID($id);
            $this->data['branch_prefix_id'] = $branch_prefix_id;
            $this->data['row'] = $this->settings_model->getBranchPrefixByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_branch_prefix', $this->data);
        }
    }

        function delete_branch_prefix($id = NULL)
    {
        if ($this->settings_model->getBranchPrefixByID($id,$id)) {
            $this->session->set_flashdata('error', lang("unit_has_subunit"));
            header('HTTP/1.1 404 Bad Request');
            header('Content-Type: application/json; charset=UTF-8');
            exit;
        }

        if ($this->settings_model->deleteBranchPrefixs($id)) {
           // echo lang("unit_deleted");
        }
        $this->session->set_flashdata('message', lang("unit_deleted")." ".$id['code']." ".$id['name']);
        redirect("system_settings/units");
    }


    function delete_branch_prefixss($id = NULL)
    {
        $this->cus->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $BranchPrefix = $this->settings_model->getBranchPrefixByID($id);

        if ($this->settings_model->deleteBranchPrefixs($id)) {
            echo lang("branch_prefix_deleted");
        } else {
            $this->session->set_flashdata('warning', lang('customer_x_deleted_have_sales'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }


	
	function edit_bom($id = false){
		
        $this->cus->checkPermissions('boms', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
			$name = $this->input->post('name');
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
				$quantity = $_POST['quantity'][$r];
				$unit_qty = $_POST['unit_qty'][$r];
				$unit_id = $_POST['unit'][$r];
				$unit = $this->site->getProductUnit($product_id,$unit_id);
                $products[] = array(
					'bom_id' => $id,
                    'product_id' => $product_id,
					'quantity' =>$quantity,
					'unit_qty' =>$unit_qty,
					'unit_id' =>$unit_id,
					'type' =>'raw_material'
				);
            }
			
			$f = isset($_POST['fproduct_id']) ? sizeof($_POST['fproduct_id']) : 0;
            for ($r = 0; $r < $f; $r++) {
                $product_id = $_POST['fproduct_id'][$r];
				$quantity = $_POST['fquantity'][$r];
				$unit_qty = $_POST['funit_qty'][$r];
				$unit_id = $_POST['funit'][$r];
				$unit = $this->site->getProductUnit($product_id,$unit_id);
                $finished_goods[] = array(
					'bom_id' => $id,
                    'product_id' => $product_id,
					'quantity' =>$quantity,
					'unit_qty' =>$unit_qty,
					'unit_id' =>$unit_id,
					'type' =>'finished_good'
				);
            }
            if (empty($products) || empty($finished_goods)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
				krsort($finished_goods);
            }
			
            $data = array(
                'name' => $name,
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
            );
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateBom($id, $data, $products, $finished_goods)) {
            $this->session->set_userdata('remove_bomls', 1);
            $this->session->set_flashdata('message', lang("bom_edited"));
            redirect('system_settings/boms');
        } else {
			$bom_items = $this->settings_model->getBomItems($id);
            krsort($bom_items);
            $c = rand(100000, 9999999);
            foreach ($bom_items as $bom_item) {
                $product = $this->site->getProductByID($bom_item->product_id);
                $row = json_decode('{}');
				$row->id = $product->id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->quantity = $bom_item->quantity;
				$row->unit_qty = $bom_item->unit_qty;
				$row->unit = $bom_item->unit_id;
				$units = $this->site->getUnitbyProduct($product->id,$product->unit);		
				if($bom_item->type == "raw_material"){
					$pr[$c] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row,'units' => $units);
				}else{
					$pf[$c] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row,'units' => $units);
				}
                $c++;
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['raw_materials'] = json_encode($pr);
			$this->data['finished_goods'] = json_encode($pf);
			$this->data['bom'] = $this->settings_model->getBomByID($id);
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),array('link' => site_url('system_settings/boms'), 'page' => lang('boms')), array('link' => '#', 'page' => lang('edit_bom')));
            $meta = array('page_title' => lang('edit_bom'), 'bc' => $bc);
            $this->session->set_userdata('remove_bomls', 1);
			$this->core_page('settings/edit_bom', $meta, $this->data);
        }
    }
	public function delete_bom($id)
	{
		$this->cus->checkPermissions('boms', true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->settings_model->deleteBom($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("bom_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('bom_deleted'));
			redirect('system_settings/boms');
		}
	}
	
	function bom_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					foreach ($_POST['val'] as $id) {
						$this->settings_model->deleteBom($id);
					}
					$this->session->set_flashdata('message', lang("bom_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				}else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('boms');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('created_by'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $boms = $this->settings_model->getBomByID($id);
                        $created_by = $this->site->getUser($boms->created_by);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $boms->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $created_by->last_name.' ' .$created_by->first_name);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'boms_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }


    function tax_validation()
    {

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('tax_validation')));
        $meta = array('page_title' => lang('tax_validation'), 'bc' => $bc);
        $this->core_page('settings/tax_validation', $meta, $this->data);
    }
    function getTaxValidation()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
                id,
                name, 
                code, 
                vat_name, 
                acc_name,
                sc_name,
                plt_name,
                spc_name,
                total_rate,
                type
            ")
            ->from("tax_validations")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_tax_validation/$1') . "' class='tip' title='" . lang("edit_tax_validation") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_tax_validation") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_tax_validation/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    function add_tax_validation()
    {

        $this->form_validation->set_rules('name', lang("name"), 'trim|is_unique[tax_validations.name]|required');

        if ($this->form_validation->run() == true) {
            $rax_rate_vat = $this->site->getTaxRateByID($this->input->post('tax_validation_vat'));
            $rax_rate_acc = $this->site->getTaxRateByID($this->input->post('tax_validation_acc'));
            $rax_rate_sc = $this->site->getTaxRateByID($this->input->post('tax_validation_sc'));
            $rax_rate_plt = $this->site->getTaxRateByID($this->input->post('tax_validation_plt'));
            $rax_rate_spc = $this->site->getTaxRateByID($this->input->post('tax_validation_spc'));
            $total_rate = $tax_rate_vat->rate + $tax_rate_acc->rate + $tax_rate_sc->rate  + $tax_rate_plt->rate +  $taxt_rate_spc->rate;
            $data = array('name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'vat_id' => $this->input->post('tax_validation_vat'),
                'vat_name' => $rax_rate_vat->rate,
                'acc_id' => $this->input->post('tax_validation_acc'),
                'acc_name' => $rax_rate_acc->rate,
                'sc_id' => $this->input->post('tax_validation_sc'),
                'sc_name' => $rax_rate_sc->rate,
                'plt_id' => $this->input->post('tax_validation_plt'),
                'plt_name' => $rax_rate_plt->rate,
                'spc_id' => $this->input->post('tax_validation_spc'),
                'spc_name' => $rax_rate_spc->rate,
                'total_rate' => $total_rate,
                'type' => $this->input->post('type')
                
            );
            
        } elseif ($this->input->post('add_tax_validation')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/tax_validation");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addTaxValidation($data)) {
            $this->session->set_flashdata('message', lang("tax_validation_added"));
            redirect("system_settings/tax_validation");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['tax_rates'] = $this->site->getAllTaxRates(); 
            $this->load->view($this->theme . 'settings/add_tax_validation', $this->data);
        }
    }

    function edit_tax_validation($id = NULL)
    {

        $this->form_validation->set_rules('name', lang("name"), 'trim|required');
        $tax_details = $this->settings_model->getTaxValidationByID($id);

        if ($this->form_validation->run() == true) {
            $tax_rate_vat = $this->site->getTaxRateByID($this->input->post('tax_validation_vat'));
            $tax_rate_acc = $this->site->getTaxRateByID($this->input->post('tax_validation_acc'));
            $tax_rate_sc = $this->site->getTaxRateByID($this->input->post('tax_validation_sc'));
            $tax_rate_plt = $this->site->getTaxRateByID($this->input->post('tax_validation_plt'));
            $tax_rate_spc = $this->site->getTaxRateByID($this->input->post('tax_validation_spc'));
            
            $total_rate = $tax_rate_vat->rate + $tax_rate_acc->rate + $tax_rate_sc->rate  + $tax_rate_plt->rate +  $taxt_rate_spc->rate;

            $data = array(
            'code' => $this->input->post('code'),
            'name' => $this->input->post('name'),
            'vat_id' => $this->input->post('tax_validation_vat'),
            'vat_name' => $tax_rate_vat->rate,
            'acc_id' => $this->input->post('tax_validation_acc'),
            'acc_name' => $tax_rate_acc->rate,
            'sc_id' => $this->input->post('tax_validation_sc'),
            'sc_name' => $tax_rate_sc->rate,
            'plt_id' => $this->input->post('tax_validation_plt'),
            'plt_name' => $tax_rate_plt->rate,
            'spc_id' => $this->input->post('tax_validation_spc'),
            'spc_name' => $tax_rate_spc->rate,
            'total_rate' => $total_rate,
            'type' => $this->input->post('type')
            );
        } elseif ($this->input->post('edit_tax_validation')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/tax_validation");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateTaxValidation($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang("tax_validation_updated"));
            redirect("system_settings/tax_validation");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['tax_validation'] = $this->settings_model->getTaxValidationByID($id);
            $this->data['id'] = $id;
            $this->data['tax_rates'] = $this->site->getAllTaxRates(); 
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_tax_validation', $this->data);
        }
    }

    function delete_tax_validation($id = NULL)
    {
        if ($this->settings_model->deleteTaxValidation($id)) {
            echo lang("tax_validation_deleted");
        }
    }

    function tax_validation_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteTaxValidation($id);
                    }
                    $this->session->set_flashdata('message', lang("tax_validation_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tax_validation'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('tax_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('type'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $tax = $this->settings_model->getTaxValidationByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $tax->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $tax->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $tax->rate);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($tax->type == 1) ? lang('percentage') : lang('fixed'));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'tax_validation_' . date('Y_m_d_H_i_s');
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
