<?php defined('BASEPATH') or exit('No direct script access allowed');

class Sale_orders extends MY_Controller
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
        $this->lang->load('sale_orders', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('sale_order_model');
		$this->load->model('quotes_model');
        $this->digital_upload_path = 'files/';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

    public function index($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('sale_orders')));
		$meta = array('page_title' => lang('sale_orders'), 'bc' => $bc);
        $this->core_page('sale_orders/index', $meta, $this->data);

    }

    public function getSaleOrders($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions('index');
		$add_delivery_link = anchor('deliveries/add/$1', '<i class="fa fa-truck"></i> ' . lang('create_delivery'), ' class="add_delivery"  ');
        $detail_link = anchor('sale_orders/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_order_details'));
        $email_link = anchor('sale_orders/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale_order'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $edit_link = anchor('sale_orders/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale_order'), ' class="edit_sale_order" ');
        $convert_link = anchor('sales/add/$1/1', '<i class="fa fa-heart"></i> ' . lang('create_sale'), ' class="create_sale" ');        
        $add_deposit_link = anchor('sale_orders/add_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_deposit'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_deposit" data-target="#myModal"');
		$view_deposit_link = anchor('sale_orders/deposits/$1', '<i class="fa fa-money"></i> ' . lang('view_deposits'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_deposit" data-target="#myModal"');
		//$add_scan_stock = anchor('products/add_scan_stock/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('add_scan_stock'));
		$delete_link = "<a href='#' class='po delete_sale_order' title='<b>" . $this->lang->line("delete_sale_order") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sale_orders/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale_order') . "</a>";
		if($this->Admin || $this->Owner || $this->GP['approve_sale_orders']){
			$approve_link = "<a href='#' class='po approve_sale_order' title='<b>" . $this->lang->line("approve_sale_order") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . site_url('sale_orders/approve/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_sale_order') . "</a>";
			
			$unapprove_link = "<a href='#' class='po unapprove_sale_order' title='<b>" . $this->lang->line("unapprove_sale_order") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sale_orders/unapprove/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_sale_order') . "</a>";
			
			
			$reject_link = "<a href='#' class='po reject_sale_order' title='<b>" . $this->lang->line("reject_sale_order") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sale_orders/reject/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('reject_sale_order') . "</a>";
		}
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $detail_link . '</li>
						<li>' . $view_deposit_link . '</li>
						<li>' . $add_deposit_link . '</li>
                        <li>' . $edit_link . '</li>
						<li>' . $approve_link . '</li>
						<li>' . $unapprove_link . '</li>
						<li>' . $reject_link . '</li>
                        <li>' . $convert_link . '</li>
						<li>' . $add_delivery_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
       
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("id, date, reference_no, qo_reference_no,customer, grand_total,IFNULL(payments.deposit,0) as deposit, status, attachment")
                ->from('sale_orders')
				->join('(select sum(amount) as deposit,sale_order_id from '.$this->db->dbprefix('payments').' where sale_order_id > 0 GROUP BY sale_order_id) as payments','payments.sale_order_id = sale_orders.id','left')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("id, date, reference_no, qo_reference_no,customer, grand_total,IFNULL(payments.deposit,0) as deposit, status, attachment")
                ->from('sale_orders')
				->join('(select sum(amount) as deposit,sale_order_id from '.$this->db->dbprefix('payments').' where sale_order_id > 0 GROUP BY sale_order_id) as payments','payments.sale_order_id = sale_orders.id','left');
        }
	    if ($warehouse_id) {
			$this->datatables->where('sale_orders.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
			$this->datatables->where('sale_orders.biller_id', $biller_id);
		}	
		if($this->input->get("status")){
			$this->datatables->where("status", trim($this->input->get("status")));
		}else if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('sale_orders.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
			$this->datatables->where_in('sale_orders.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function modal_view($sale_order_id = null)
    {
        $this->cus->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $sale_order_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sale_order_model->getSaleOrderByID($sale_order_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->sale_order_model->getAllSaleOrderItems($sale_order_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
		$this->data['deposit_payment'] = $this->sale_order_model->getTotalDeposit($inv->id);
        $this->data['inv'] = $inv;
		
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Sale Order',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Sale Order',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'sale_orders/modal_view', $this->data);
    }

    public function view($sale_order_id = null)
    {
        $this->cus->checkPermissions('index');

        if ($this->input->get('id')) {
            $sale_order_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sale_order_model->getSaleOrderByID($sale_order_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->sale_order_model->getAllSaleOrderItems($sale_order_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sale_orders'), 'page' => lang('sale_orders')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_sale_order_details'), 'bc' => $bc);
        $this->core_page('sale_orders/view', $meta, $this->data);

    }

    public function pdf($sale_order_id = null, $view = null, $save_bufffer = null)
    {
        $this->cus->checkPermissions();

        if ($this->input->get('id')) {
            $sale_order_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sale_order_model->getSaleOrderByID($sale_order_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->sale_order_model->getAllSaleOrderItems($sale_order_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $name = $this->lang->line("sale_order") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'sale_orders/pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'sale_orders/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->cus->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->cus->generate_pdf($html, $name);
        }
    }

    public function combine_pdf($sale_orders_id)
    {
        $this->cus->checkPermissions('pdf');

        foreach ($sale_orders_id as $sale_order_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->sale_order_model->getSaleOrderByID($sale_order_id);
            if (!$this->session->userdata('view_right')) {
                $this->cus->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->sale_order_model->getAllSaleOrderItems($sale_order_id);
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;

            $html[] = array(
                'content' => $this->load->view($this->theme . 'sale_orders/pdf', $this->data, true),
                'footer' => '',
            );
        }

        $name = lang("sale_orders") . ".pdf";
        $this->cus->generate_pdf($html, $name);

    }

    public function email($sale_order_id = null)
    {
        $this->cus->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $sale_order_id = $this->input->get('id');
        }
        $inv = $this->sale_order_model->getSaleOrderByID($sale_order_id);
        $this->form_validation->set_rules('to', $this->lang->line("to") . " " . $this->lang->line("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', $this->lang->line("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', $this->lang->line("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', $this->lang->line("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', $this->lang->line("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->cus->view_rights($inv->created_by);
            }
            $to = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $customer->name,
                'company' => $customer->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->name != '-' ? $biller->name : $biller->company) . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($sale_order_id, null, 'S'); //delete_files($attachment);
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->cus->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->db->update('sale_orders', array('status' => 'sent'), array('id' => $sale_order_id));
            $this->session->set_flashdata('message', $this->lang->line("email_sent"));
            redirect("sale_orders");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/sale_order.html')) {
                $sale_order_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/sale_order.html');
            } else {
                $sale_order_temp = file_get_contents('./themes/default/views/email_templates/sale_order.html');
            }

            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('sale_order').' (' . $inv->reference_no . ') '.lang('from').' '.$this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $sale_order_temp),
            );
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);

            $this->data['id'] = $sale_order_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sale_orders/email', $this->data);

        }
    }

    public function add($quote_id = null)
    {
        $this->cus->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');

        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sao',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['sale_orders-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$quote_id = $this->input->post('quote_id');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $payment_term = $this->input->post('payment_term');		
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            if ($supplier_id) {
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            } else {
                $supplier = NULL;
            }
			
			$type = $this->input->post('type');
			$start_date = $this->input->post('start_date');
			$end_date = $this->input->post('end_date');
			$saleman = $this->site->getUser($this->input->post('saleman_id'));
            $note = $this->cus->clear_tags($this->input->post('note'));
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
				$item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$parent_id = $_POST['parent_id'][$r];
				$foc = $_POST['foc'][$r];
				
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
					$currency_rate = 1;
					if($this->config->item('product_currency')==true){
						$currency_rate = $_POST['currency_rate'][$r];
						$currency_code = $_POST['currency_code'][$r];
						if($currency_rate > 1){
							$real_unit_price = $real_unit_price / $currency_rate;
							$unit_price = $unit_price / $currency_rate;
							$item_discount = $item_discount / $currency_rate;
							$item_tax_rate = $item_tax_rate / $currency_rate;
						}
					}
					
					$product_details = $item_type != 'manual' ? $this->sale_order_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->cus->formatDecimalRaw(((($this->cus->formatDecimalRaw($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->cus->formatDecimalRaw($discount);
                        }
                    }

                    $unit_price = $this->cus->formatDecimalRaw($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->cus->formatDecimalRaw($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                            $item_tax = $this->cus->formatDecimalRaw($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->cus->formatDecimalRaw($item_tax * $item_unit_quantity, 4);

                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->cus->formatDecimalRaw($subtotal),
                        'real_unit_price' => $real_unit_price,
						'parent_id' => $parent_id,
						'comment' => $item_comment,
						'currency_rate' => $currency_rate,
						'currency_code' => $currency_code,
						'foc' => $foc
                    );
					
					if($this->Settings->qty_operation == 1){												
						$width = $_POST['swidth'][$r];
						$height = $_POST['sheight'][$r];
						$square = $_POST['square'][$r];
						$square_qty = $_POST['square_qty'][$r];
						$products[$r]['width'] = $width;
						$products[$r]['height'] = $height;
						$products[$r]['square'] = $square;
						$products[$r]['square_qty'] = $square_qty;
						if($this->Settings->product_formulation == 1){
                            $products[$r]['pro_formulations'] = $_POST['product_formulation'][$r];
                            if($_POST['product_formulation'][$r] != ''){
                                $extraProducts = $this->cus->productFormulation($_POST['product_formulation'][$r],$width,$height,$square,$square_qty);
                                if($extraProducts){
                                    $products[$r]['extract_product'] = json_encode($extraProducts);
                                }
                            }
						}
					}
                    $total += $this->cus->formatDecimalRaw(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->cus->formatDecimalRaw(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->cus->formatDecimalRaw($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
			
            $total_tax = $this->cus->formatDecimalRaw(($product_tax + $order_tax), 4); 
            $grand_total = $this->cus->formatDecimalRaw(($total + $total_tax + $this->cus->formatDecimalRaw($shipping) - $order_discount), 4);
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'project_id' => $project_id,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->cus->formatDecimalRaw($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
				'payment_term' => $payment_term,
				'saleman_id' => $saleman->id,
				'saleman' => $saleman->last_name.' '.$saleman->first_name,
                'created_by' => $this->session->userdata('user_id')
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
			
			if ($quote_id) {				
				$qo_reference_no = $this->quotes_model->getQuoteByID($quote_id)->reference_no;
				$data['qo_reference_no'] = $qo_reference_no;
			}
        }

        if ($this->form_validation->run() == true && $this->sale_order_model->addSaleOrder($data, $products)) {
            $this->session->set_userdata('remove_sols', 1);
			$this->sale_order_model->updateRequestQuote($quote_id);
            $this->session->set_flashdata('message', $this->lang->line("sale_order_added") ." ". $reference);
           
		   if($this->input->post('add_sale_order_next')){
				redirect('sale_orders/add');
			}else{
				redirect('sale_orders');
			}
			
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			if($quote_id){
				$this->data['inv'] = $this->quotes_model->getQuoteByID($quote_id);
                $inv_items = $this->quotes_model->getAllQuoteItems($quote_id);
                if($this->Settings->product_formulation == 1){
                    $product_formulations = $this->quotes_model->getProductFormulation();
                }else{
                    $product_formulations = false;
                }
				krsort($inv_items);
				$c = rand(100000, 9999999);
				foreach ($inv_items as $item) {
					$row = $this->site->getProductByID($item->product_id);
					if (!$row) {
						$row = json_decode('{}');
						$row->tax_method = 0;
					} else {
						unset($row->details, $row->product_details, $row->cost, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
					}
					$row->quantity = 0;
					$pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id);
					if ($pis) {
						foreach ($pis as $pi) {
							$row->quantity += $pi->quantity_balance;
						}
					}
					$row->fup = 1;
					$row->id = $item->product_id;
					$row->code = $item->product_code;
					$row->name = $item->product_name;
					$row->type = $item->product_type;
					$row->base_quantity = $item->quantity;
					$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
					$row->base_unit_price = $row->price ? $row->price : $item->real_unit_price;
					$row->unit = $item->product_unit_id;
					$row->qty = $item->unit_quantity;
					$row->discount = $item->discount ? $item->discount : '0';
					$row->price = $this->cus->formatDecimalRaw($item->net_unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity));
					$row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity) + $this->cus->formatDecimalRaw($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
					$row->real_unit_price = $item->real_unit_price;
					$row->tax_rate = $item->tax_rate_id;
					$row->option = $item->option_id;
					$row->swidth = $item->width;
					$row->sheight = $item->height;
					$row->square = $item->square;
					$row->square_qty = $item->square_qty;
                    $row->comment = $item->comment;
                    $row->product_formulation = $item->pro_formulations;
					$row->foc = $item->foc;
					if($this->config->item('product_currency')==true){
						$currency_rate = $row->currency_rate;
						$row->base_unit_price = $row->base_unit_price * $currency_rate;
						$row->real_unit_price = $row->real_unit_price * $currency_rate;
						$row->unit_price = $row->unit_price * $currency_rate;
						$row->discount = strval($item->discount * $currency_rate);
					}
					
					$options = $this->quotes_model->getProductOptions($row->id, $item->warehouse_id);
					if ($options) {
						$option_quantity = 0;
						foreach ($options as $option) {
							$pis = $this->site->getStockmoves($row->id, $item->warehouse_id, $item->option_id);
							if ($pis) {
								foreach ($pis as $pi) {
									$option_quantity += $pi->quantity_balance;
								}
							}
							if ($option->quantity > $option_quantity) {
								$option->quantity = $option_quantity;
							}
						}
					}

					$combo_items = false;
					if ($row->type == 'combo') {
						$combo_items = $this->quotes_model->getProductComboItems($row->id, $item->warehouse_id);
						foreach ($combo_items as $combo_item) {
							$combo_item->quantity = $combo_item->qty * $item->quantity;
						}
					}
					
					
					$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
					$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
					$ri = $this->Settings->item_addition ? $row->id : $c;
					$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'product_formulations' => $product_formulations);
					$c++;
				}
				$this->data['inv_items'] = json_encode($pr);
				$this->data['id'] = $quote_id;
				
			}else{
				$this->data['inv'] = false;
			}
			if($this->config->item('quotation')){
				$this->data['quotations'] = $this->site->getRefQuotations();	
			}
            $this->data['billers'] = $this->site->getBillers();
			$this->data['salemans'] = $this->site->getSalemans();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['qunumber'] = ''; 
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('sale_orders'), 'page' => lang('sale_orders')), array('link' => '#', 'page' => lang('add_sale_order')));
			$meta = array('page_title' => lang('add_sale_order'), 'bc' => $bc);
            $this->core_page('sale_orders/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->cus->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sale_order_model->getSaleOrderByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->cus->view_rights($inv->created_by);
        }				
		
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        //$this->form_validation->set_rules('note', $this->lang->line("note"), 'xss_clean');

        if ($this->form_validation->run() == true) {
			
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sao',$biller_id);
            $quantity = "quantity";
            $product = "product";
            $unit_cost = "unit_cost";
            $tax_rate = "tax_rate";

            if ($this->Owner || $this->Admin || $this->cus->GP['sale_orders-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
			$payment_term = $this->input->post('payment_term');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            if ($supplier_id) {
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            } else {
                $supplier = NULL;
            }
			$type = $this->input->post('type');
			$start_date = $this->input->post('start_date');
			$end_date = $this->input->post('end_date');
            $note = $this->cus->clear_tags($this->input->post('note'));
			$saleman = $this->site->getUser($this->input->post('saleman_id'));
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
				$item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$parent_id = $_POST['parent_id'][$r];
				$foc = $_POST['foc'][$r];
				
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
					$currency_rate = 1;
					if($this->config->item('product_currency')==true){
						$currency_rate = $_POST['currency_rate'][$r];
						$currency_code = $_POST['currency_code'][$r];
						if($currency_rate > 1){
							$real_unit_price = $real_unit_price / $currency_rate;
							$unit_price = $unit_price / $currency_rate;
							$item_discount = $item_discount / $currency_rate;
							$item_tax_rate = $item_tax_rate / $currency_rate;
						}
					}
					
                    $product_details = $item_type != 'manual' ? $this->sale_order_model->getProductByCode($item_code) : null;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->cus->formatDecimalRaw(((($this->cus->formatDecimalRaw($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->cus->formatDecimalRaw($discount);
                        }
                    }

                    $unit_price = $this->cus->formatDecimalRaw($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->cus->formatDecimalRaw($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                            $item_tax = $this->cus->formatDecimalRaw($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->cus->formatDecimalRaw($item_tax * $item_unit_quantity, 4);

                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->cus->formatDecimalRaw($subtotal),
                        'real_unit_price' => $real_unit_price,
						'parent_id' => $parent_id,
						'comment' => $item_comment,
						'currency_rate' => $currency_rate,
						'currency_code' => $currency_code,
						'foc' => $foc
                    );
					
					if($this->Settings->qty_operation == 1){												
						$width = $_POST['swidth'][$r];
						$height = $_POST['sheight'][$r];
						$square = $_POST['square'][$r];
						$square_qty = $_POST['square_qty'][$r];
						$products[$r]['width'] = $width;
						$products[$r]['height'] = $height;
						$products[$r]['square'] = $square;
						$products[$r]['square_qty'] = $square_qty;
						
						if($this->Settings->product_formulation == 1){
                            $products[$r]['pro_formulations'] = $_POST['product_formulation'][$r];
                            if($_POST['product_formulation'][$r] != ''){
                                $extraProducts = $this->cus->productFormulation($_POST['product_formulation'][$r],$width,$height,$square,$square_qty);
                                if($extraProducts){
                                    $products[$r]['extract_product'] = json_encode($extraProducts);
                                }
                            }
						}
					}
                    $total += $this->cus->formatDecimalRaw(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->cus->formatDecimalRaw(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->cus->formatDecimalRaw($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->cus->formatDecimalRaw(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->cus->formatDecimalRaw(($product_tax + $order_tax), 4); 
            $grand_total = $this->cus->formatDecimalRaw(($total + $total_tax + $this->cus->formatDecimalRaw($shipping) - $order_discount), 4);
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'project_id' => $project_id,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $shipping,
                'grand_total' => $grand_total,
                'status' => $status,
				'payment_term' => $payment_term,
				'saleman_id' => $saleman->id,
				'saleman' => $saleman->last_name.' '.$saleman->first_name,
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

            // $this->cus->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->sale_order_model->updateQuote($id, $data, $products)) {
            $this->session->set_userdata('remove_sols', 1);
            $this->session->set_flashdata('message', $this->lang->line("sale_order_added"));
            redirect('sale_orders');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $this->sale_order_model->getSaleOrderByID($id);
            $inv_items = $this->sale_order_model->getAllSaleOrderItems($id);
            if($this->Settings->product_formulation == 1){
                $product_formulations = $this->sale_order_model->getProductFormulation();
            }else{
                $product_formulations = false;
            }
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                } else {
                    unset($row->details, $row->product_details, $row->cost, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
                }
                $row->quantity = 0;
                $pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
				$row->fup =  1;
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
				$row->base_unit_price = $row->price ? $row->price : $item->real_unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $this->cus->formatDecimalRaw($item->net_unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity));
                $row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity) + $this->cus->formatDecimalRaw($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate = $item->tax_rate_id;
                $row->option = $item->option_id;
				$row->parent_id = $item->parent_id;
				$row->swidth = $item->width;
				$row->sheight = $item->height;
				$row->square = $item->square;
				$row->square_qty = $item->square_qty;
                $row->comment = $item->comment;
                $row->product_formulation = $item->pro_formulations;
				$row->foc = $item->foc;
				if($this->config->item('product_currency')==true){
					$currency_rate = $item->currency_rate;
					$row->currency_rate = $currency_rate;
					$row->base_unit_price = $row->base_unit_price * $currency_rate;
					$row->real_unit_price = $row->real_unit_price * $currency_rate;
					$row->unit_price = $row->unit_price * $currency_rate;
					$row->discount = strval($item->discount * $currency_rate);
				}
				
                $options = $this->sale_order_model->getProductOptions($row->id, $item->warehouse_id);
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getStockmoves($row->id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
						if($option->id == $item->option_id){
							 $option->quantity += $item->quantity;
						}
                    }
                }

                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sale_order_model->getProductComboItems($row->id, $item->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
				
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options,  'product_formulations'=> $product_formulations);
                $c++;
            }
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
			$this->data['salemans'] = $this->site->getSalemans();
            $this->data['billers'] = $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$this->session->set_userdata('remove_sols', 1);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('sale_orders'), 'page' => lang('sale_orders')), array('link' => '#', 'page' => lang('edit_sale_order')));
			$meta = array('page_title' => lang('edit_sale_order'), 'bc' => $bc);
            $this->core_page('sale_orders/edit', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->cus->checkPermissions(NULL, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sale_order_model->deleteSaleOrder($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("sale_order_deleted");die();
            }
            $this->session->set_flashdata('message', lang('sale_order_deleted'));
            redirect('welcome');
        }
    }
	
	public function approve($id = null)
    {
		if($this->Admin || $this->Owner || $this->GP['approve_sale_orders']){
			if ($this->input->get('id')) {
				$id = $this->input->get('id');
			}

			if ($this->sale_order_model->approveSaleOrder($id)) {
				if ($this->input->is_ajax_request()) {
					echo lang("sale_order_approved");die();
				}
				$this->session->set_flashdata('message', lang('sale_order_approved'));
				redirect('welcome');
			}
		}else{
			$this->session->set_flashdata('error', lang("access_denied"));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
		}
    }
	
	public function unapprove($id = null)
    {
		if($this->Admin || $this->Owner || $this->GP['approve_sale_orders']){
			if ($this->input->get('id')) {
				$id = $this->input->get('id');
			}

			if ($this->sale_order_model->unApproveSaleOrder($id)) {
				if ($this->input->is_ajax_request()) {
					echo lang("sale_order_unapproved");die();
				}
				$this->session->set_flashdata('message', lang('sale_order_unapproved'));
				redirect('welcome');
			}
		}else{
			$this->session->set_flashdata('error', lang("access_denied"));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
		}
       

        
    }
	
	public function reject($id = null)
    {
        if($this->Admin || $this->Owner || $this->GP['approve_sale_orders']){
			if ($this->input->get('id')) {
				$id = $this->input->get('id');
			}

			if ($this->sale_order_model->rejectSaleOrder($id)) {
				if ($this->input->is_ajax_request()) {
					echo lang("sale_order_rejected");die();
				}
				$this->session->set_flashdata('message', lang('sale_order_rejected'));
				redirect('welcome');
			}
		}else{
			$this->session->set_flashdata('error', lang("access_denied"));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
		}
    }	

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->sale_order_model->getProductNames($sr, $warehouse_id);
        if ($rows) {
            if($this->Settings->product_formulation == 1){
                $product_formulations = $this->sale_order_model->getProductFormulation();
            }else{
                $product_formulations = false;
            }
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option = false;
                $row->quantity = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty = 1;
                $row->discount = '0';
                $options = $this->sale_order_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->sale_order_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = FALSE;
                }
                $row->option = $option_id;
                $pis = $this->site->getStockmoves($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
				$currency_rate = false;
				if($this->config->item('product_currency')==true){
					$currency_now  = $this->site->getCurrencyByCode($row->currency_code);
					$currency_rate = $row->currency_rate;
					$row->price = $row->price * $currency_rate;
					//=========Currency Now=========//
					$row->currency_code = $currency_now->code;
					$row->currency_rate = $currency_now->rate;
				}
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
				$combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sale_order_model->getProductComboItems($row->id, $warehouse_id);
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getStockmoves($row->id, $warehouse_id, $row->option);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        if ($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                    }
                }
				if ($row->promotion && date('Y-m-d') >= $row->start_date && date('Y-m-d') <= $row->end_date) {
					$row->discount = (100-(($row->promo_price / $row->price) * 100)).'%';
                }else if($this->Settings->customer_price == 1 && $customer_price = $this->sale_order_model->getCustomerPrice($row->id,$customer_id)){
					if (isset($customer_price) && $customer_price != false) {
						if($customer_price->price > 0){
							$row->price = $customer_price->price;
						}
					}
				} else if ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                } else if ($warehouse->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                }
				$row->price = $row->price + (($row->price * $customer_group->percent) / 100);
				$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);				
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				$row->real_unit_price = $row->price;
				$row->unit_price = $row->price;
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'product_formulations'=>$product_formulations, 'currency_rate' => $currency_rate);
                $r++;
            }
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function sale_order_action()
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
                    foreach ($_POST['val'] as $id) {
                        $this->sale_order_model->deleteSaleOrder($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("sale_orders_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sale_orders'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $qu = $this->sale_order_model->getSaleOrderByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($qu->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $qu->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $qu->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $qu->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $qu->total);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $qu->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sale_orders_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_sale_order_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function update_status($id)
    {

        $this->form_validation->set_rules('status', lang("status"), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note = $this->cus->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        }

        if ($this->form_validation->run() == true && $this->sale_order_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {

            $this->data['inv'] = $this->sale_order_model->getSaleOrderByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'sale_orders/update_status', $this->data);

        }
    }
	
	
	public function deposits($id = null)
    {
        $this->cus->checkPermissions('deposits', true, 'customers');
        $this->data['deposits'] = $this->sale_order_model->getSODeposits($id);
        $this->data['saleorder'] = $this->sale_order_model->getSaleOrderByID($id);
        $this->load->view($this->theme . 'sale_orders/deposits', $this->data);
    }
	
	public function deposit_note($id = null)
    {
        $this->cus->checkPermissions('deposits', true, 'customers');
        $deposit = $this->sale_order_model->getDepositByID($id);
		$sale_order = $this->sale_order_model->getSaleOrderByID($deposit->sale_order_id);
        $this->data['biller'] = $this->site->getCompanyByID($sale_order->biller_id);
        $this->data['customer'] = $this->site->getCompanyByID($sale_order->customer_id);
        $this->data['sale_order'] = $sale_order;
		$this->data['deposit'] = $deposit;
        $this->data['page_title'] = lang("deposit_note");
		
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Deposit Note',$deposit->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Deposit Note',$deposit->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'sale_orders/deposit_note', $this->data);
    }
	
	public function add_deposit($id = null)
    {
        $this->cus->checkPermissions('deposits', true, 'customers');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale_order = $this->sale_order_model->getSaleOrderByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
                $sale_order = $this->sale_order_model->getSaleOrderByID($this->input->post('sale_order_id'));
                $customer_id = $sale_order->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin  || $this->cus->GP['sale_orders-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$sale_order->biller_id);
            $paymentAcc = $this->site->getAccountSettingByBiller($sale_order->biller_id);
			if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
				$paying_to = $paymentAcc->customer_deposit_acc;
			}else{
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_to = $cash_account->account_code;
			}
			$payment = array(
                'date' => $date,
                'sale_order_id' => $this->input->post('sale_order_id'),
				'transaction' => "SO Deposit",
                'reference_no' => $reference_no,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : '',
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'received',
				'currencies' => json_encode($currencies),
				'account_code' => $paying_to,
            );

			//=====accountig=====//
					if($this->Settings->accounting == 1){
						$accTranPayments[] = array(
								'transaction' => 'Sale Order Deposit',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $paymentAcc->customer_deposit_acc,
								'amount' => -($this->input->post('amount-paid')+$this->input->post('discount')),
								'narrative' => 'Sale Order Deposit '.$sale_order->reference_no,
								'description' => $this->input->post('note'),
								'biller_id' => $sale_order->biller_id,
								'project_id' => $sale_order->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale_order->customer_id,
							);
						$accTranPayments[] = array(
								'transaction' => 'Sale Order Deposit',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $paying_to,
								'amount' => $this->input->post('amount-paid'),
								'narrative' => 'Sale Order Deposit '.$sale_order->reference_no,
								'description' => $this->input->post('note'),
								'biller_id' => $sale_order->biller_id,
								'project_id' => $sale_order->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale_order->customer_id,
							);
					}
				//=====end accountig=====//

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
                $payment['attachment'] = $photo;
            }

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sale_order_model->addDeposit($payment, $customer_id, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("deposit_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['sale_order'] = $sale_order;
            $this->data['payment_ref'] = '';
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sale_orders/add_deposit', $this->data);
        }
    }
	
	public function edit_deposit($id = null)
    {
        $this->cus->checkPermissions('deposits', true, 'customers');
        $this->load->helper('security');
		if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$deposit = $this->sale_order_model->getDepositByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
			$sale_order = $this->sale_order_model->getSaleOrderByID($this->input->post('sale_order_id'));
			if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale_order->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin  || $this->cus->GP['sale_orders-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$sale_order->biller_id);
			$paymentAcc = $this->site->getAccountSettingByBiller($sale_order->biller_id);
			if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
				$paying_to = $paymentAcc->customer_deposit_acc;
			}else{
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_to = $cash_account->account_code;
			}
            $payment = array(
                'date' => $date,
                'sale_order_id' => $this->input->post('sale_order_id'),
				'transaction' => "SO Deposit",
                'reference_no' => $reference_no,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : '',
                'note' => $this->input->post('note'),
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date("Y-m-d H:i"),
                'type' => 'received',
				'currencies' => json_encode($currencies),
				'account_code' => $paying_to,
            );

			//=====accountig=====//
					if($this->Settings->accounting == 1){
						$accTranPayments[] = array(
								'transaction_id' => $id,
								'transaction' => 'Sale Order Deposit',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $paymentAcc->customer_deposit_acc,
								'amount' => -($this->input->post('amount-paid')+$this->input->post('discount')),
								'narrative' => 'Sale Order Deposit '.$sale_order->reference_no,
								'description' => $this->input->post('note'),
								'biller_id' => $sale_order->biller_id,
								'project_id' => $sale_order->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale_order->customer_id,
							);
						$accTranPayments[] = array(
								'transaction_id' => $id,
								'transaction' => 'Sale Order Deposit',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $paying_to,
								'amount' => $this->input->post('amount-paid'),
								'narrative' => 'Sale Order Deposit '.$sale_order->reference_no,
								'description' => $this->input->post('note'),
								'biller_id' => $sale_order->biller_id,
								'project_id' => $sale_order->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale_order->customer_id,
							);
					}
				//=====end accountig=====//

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
                $payment['attachment'] = $photo;
            }
        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sale_order_model->updateDeposit($id, $payment, $customer_id, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("deposit_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

			$this->data['sale_order'] = $this->sale_order_model->getSaleOrderByID($deposit->sale_order_id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['deposit'] = $deposit;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sale_orders/edit_deposit', $this->data);
        }
    }
	
	public function delete_deposit($id = null)
    {
        $this->cus->checkPermissions('delete_deposit', true, 'customers');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$opay = $this->sale_order_model->getDepositByID($id);
        if ($this->sale_order_model->deleteDeposit($id)) {
            $this->session->set_flashdata('message', lang("deposit_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
}
