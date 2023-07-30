<?php defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_orders extends MY_Controller
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
        $this->lang->load('purchase_orders', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('purchase_order_model');
		$this->load->model('purchase_request_model');
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
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => '#', 'page' => lang('purchase_orders')));
        $meta = array('page_title' => lang('purchase_orders'), 'bc' => $bc);
        $this->core_page('purchase_orders/index', $meta, $this->data);

    }

    public function getPurchaseOrders($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions('index');
        $detail_link = anchor('purchase_orders/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_order_details'));
        $email_link = anchor('purchase_orders/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase_order'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $edit_link = anchor('purchase_orders/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase_order'), ' class="edit_purchase_order" ');
        $add_deposit_link = anchor('purchase_orders/add_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_deposit'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_deposit" data-target="#myModal"');
		$write_off_deposit_link = anchor('purchase_orders/add_deposit/$1/write_off', '<i class="fa fa-money"></i> ' . lang('write_off_deposit'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="write_off_deposit" data-target="#myModal"');
		$deposit_link = anchor('purchase_orders/deposits/$1', '<i class="fa fa-money"></i> ' . lang('view_deposits'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_deposit" data-target="#myModal"');
		$delete_link = "<a href='#' class='po delete_purchase_order' title='<b>" . $this->lang->line("delete_purchase_order") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchase_orders/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_purchase_order') . "</a>";
		if($this->Admin || $this->Owner || $this->GP['approve_purchase_orders']){
			$approve_link = "<a href='#' class='po approve_purchase_order' title='<b>" . $this->lang->line("approve_purchase_order") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . site_url('purchase_orders/approve/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_purchase_order') . "</a>";
			
			$unapprove_link = "<a href='#' class='po unapprove_purchase_order' title='<b>" . $this->lang->line("unapprove_purchase_order") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchase_orders/unapprove/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_purchase_order') . "</a>";
			
			
			$reject_link = "<a href='#' class='po reject_purchase_order' title='<b>" . $this->lang->line("reject_purchase_order") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchase_orders/reject/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('reject_purchase_order') . "</a>";
		}
		$rc_link = '';
		$pc_link = '';
		if($this->config->item('po_receive_item') && ($this->Admin || $this->Owner || $this->GP['purchases-add_receive'])){
			$rc_link = anchor('purchases/add_receive/$1/1', '<i class="fa fa-plus"></i> ' . lang('add_receive_item'), ' class="add_receive_item" ');
		}
		if($this->Admin || $this->Owner || $this->GP['purchases-add']){
			$pc_link = anchor('purchases/add/$1/1', '<i class="fa fa-plus"></i> ' . lang('add_purchase'), ' class="create_purchase" ');
		}
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $detail_link . '</li>
						<li>' . $add_deposit_link . '</li>
						<li>' . $deposit_link . '</li>
                        <li>' . $edit_link . '</li>
						<li>' . $approve_link . '</li>
						<li>' . $unapprove_link . '</li>
						<li>' . $reject_link . '</li>
                        <li>' . $pc_link . '</li>	
						<li>' . $rc_link . '</li>			
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
       
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("purchase_orders.id as id, purchase_orders.date, purchase_orders.reference_no, purchase_orders.pr_reference_no,projects.name, purchase_orders.supplier, purchase_orders.grand_total,IFNULL(payments.deposit,0) as deposit, purchase_orders.status, purchase_orders.attachment,purchase_orders.received")
                ->from('purchase_orders')
				->join('(select sum(amount) as deposit,purchase_order_id from '.$this->db->dbprefix('payments').' where purchase_order_id > 0 GROUP BY purchase_order_id) as payments','payments.purchase_order_id = purchase_orders.id','left')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("purchase_orders.id as id, purchase_orders.date, purchase_orders.reference_no, purchase_orders.pr_reference_no, projects.name, purchase_orders.supplier, purchase_orders.grand_total,IFNULL(payments.deposit,0) as deposit, purchase_orders.status, purchase_orders.attachment,purchase_orders.received")
                ->join('(select sum(amount) as deposit,purchase_order_id from '.$this->db->dbprefix('payments').' where purchase_order_id > 0 GROUP BY purchase_order_id) as payments','payments.purchase_order_id = purchase_orders.id','left')
				->from('purchase_orders');
        }
		$this->datatables->join("projects","projects.id=purchase_orders.project_id","left");
		if ($warehouse_id) {
            $this->datatables->where('purchase_orders.warehouse_id', $warehouse_id);
        }
		if ($biller_id) {
             $this->datatables->where('purchase_orders.biller_id', $biller_id);
        }
		if($this->input->get("status")){
			$this->datatables->where("status", trim($this->input->get("status")));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('purchase_orders.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('purchase_orders.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('purchase_orders.created_by', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function modal_view($purchase_order_id = null)
    {
        $this->cus->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_order_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchase_order_model->getPurchaseOrderByID($purchase_order_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->purchase_order_model->getAllPurchaseOrderItems($purchase_order_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
		$this->data['deposit_payment'] = $this->purchase_order_model->getTotalDeposit($inv->id);
        $this->data['inv'] = $inv;
		
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Purchase Order',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Purchase Order',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'purchase_orders/modal_view', $this->data);

    }

    public function view($purchase_order_id = null)
    {
        $this->cus->checkPermissions('index');

        if ($this->input->get('id')) {
            $purchase_order_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchase_order_model->getPurchaseOrderByID($purchase_order_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchase_order_model->getAllPurchaseOrderItems($purchase_order_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;

        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => site_url('purchase_orders'), 'page' => lang('purchase_orders')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_purchase_order_details'), 'bc' => $bc);
        $this->core_page('purchase_orders/view', $meta, $this->data);

    }

    public function pdf($purchase_order_id = null, $view = null, $save_bufffer = null)
    {
        $this->cus->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_order_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchase_order_model->getPurchaseOrderByID($purchase_order_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchase_order_model->getAllPurchaseOrderItems($purchase_order_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $name = $this->lang->line("purchase_order") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'purchase_orders/pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'purchase_orders/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->cus->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->cus->generate_pdf($html, $name);
        }
    }

    public function combine_pdf($purchase_orders_id)
    {
        $this->cus->checkPermissions('pdf');

        foreach ($purchase_orders_id as $purchase_order_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->purchase_order_model->getPurchaseOrderByID($purchase_order_id);
            if (!$this->session->userdata('view_right')) {
                $this->cus->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->purchase_order_model->getAllPurchaseOrderItems($purchase_order_id);
            $this->data['customer'] = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;

            $html[] = array(
                'content' => $this->load->view($this->theme . 'purchase_orders/pdf', $this->data, true),
                'footer' => '',
            );
        }

        $name = lang("purchase_orders") . ".pdf";
        $this->cus->generate_pdf($html, $name);

    }

    public function email($purchase_order_id = null)
    {
        $this->cus->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $purchase_order_id = $this->input->get('id');
        }
        $inv = $this->purchase_order_model->getPurchaseOrderByID($purchase_order_id);
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
            $customer = $this->site->getCompanyByID($inv->supplier_id);
            $biller = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $customer->name,
                'company' => $customer->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->name != '-' ? $biller->name : $biller->company) . '"/>'
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($purchase_order_id, null, 'S'); //delete_files($attachment);
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->cus->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->db->update('purchase_orders', array('status' => 'sent'), array('id' => $purchase_order_id));
            $this->session->set_flashdata('message', $this->lang->line("email_sent"));
            redirect("purchase_orders");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/purchase_order.html')) {
                $purchase_order_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/purchase_order.html');
            } else {
                $purchase_order_temp = file_get_contents('./themes/default/views/email_templates/purchase_order.html');
            }

            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('purchase_order').' (' . $inv->reference_no . ') '.lang('from').' '.$this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $purchase_order_temp),
            );
            $this->data['customer'] = $this->site->getCompanyByID($inv->supplier_id);

            $this->data['id'] = $purchase_order_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchase_orders/email', $this->data);

        }
    }

    public function add($purchase_request_id = null)
    {
        $this->cus->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('puo',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['purchase_orders-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$purchase_request_id = $this->input->post('purchase_request_id');
            $warehouse_id = $this->input->post('warehouse');
            $project_id = $this->input->post('project');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            if ($supplier_id) {
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            } else {
                $supplier = NULL;
            }
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
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$item_note = $_POST['pnote'][$r];
				
				if($this->Settings->cbm==1){
					$total_cbm = $_POST['total_cbm'][$r];
				}else{
					$total_cbm = 0;
				}
				
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->purchase_order_model->getProductByCode($item_code) : null;
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
						'product_note' => $item_note,
						'total_cbm' => $total_cbm
                    );

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
                'biller_id' => $biller_id,
                'biller' => $biller,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
				'project_id' => $project_id,
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
			
			if ($purchase_request_id) {				
				$pr_reference_no = $this->purchase_request_model->getPurchaseRequestByID($purchase_request_id)->reference_no;
				$data['pr_reference_no'] = $pr_reference_no;
				$data['pr_id'] = $purchase_request_id;
			}
        }

        if ($this->form_validation->run() == true && $this->purchase_order_model->addPurchaseOrder($data, $products, $purchase_request_id)) {
            $this->session->set_userdata('remove_porls', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_order_added") ." ". $reference);
			if($this->input->post('add_purchase_order_next')){
				redirect('purchase_orders/add');
			}else{
				redirect('purchase_orders');
			}
			
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			/*=============*/
			$this->data['inv'] = $this->purchase_request_model->getPurchaseRequestByID($purchase_request_id);
            $inv_items = $this->purchase_request_model->getAllPurchaseRequestItems($purchase_request_id);
            $pr = array();
            if ($inv_items != false) {
                krsort($inv_items);
                $c = rand(100000, 9999999);
                foreach ($inv_items as $item) {
					if($item->quantity > $item->po_quantity){
						$convert_unit = false;
						if($item->po_quantity > 0){
							$item->quantity = $item->quantity - $item->po_quantity;
							$price = $item->unit_price;
							$unit = $this->site->getProductUnit($item->product_id,$item->product_unit_id);
							if($unit && $unit->unit_qty > 0){
								$price = $item->unit_price / $unit->unit_qty;
							}
							$convert_unit = $this->cus->convertUnit($item->product_id,$item->quantity, $price);
							$item->product_unit_id = $convert_unit['unit_id'];
							$item->unit_quantity = $convert_unit['quantity'];
							$item->unit_price = $convert_unit['price'];
						}
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
						$row->id = $item->product_id;
						$row->base_quantity = $item->quantity;
						$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
						$row->base_unit_price = $item->real_unit_price;
						$row->unit = $item->product_unit_id;
						$row->qty = $item->unit_quantity;
						$row->discount = $item->discount ? $item->discount : '0';
						$row->price = $this->cus->formatDecimalRaw($item->net_unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity));
						$row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity) + $this->cus->formatDecimalRaw($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
						$row->real_unit_price = $item->real_unit_price;
						$row->tax_rate = $item->tax_rate_id;
						$row->option = $item->option_id;
						$options = $this->purchase_request_model->getProductOptions($row->id, $item->warehouse_id);

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
							$combo_items = $this->purchase_request_model->getProductComboItems($row->id, $item->warehouse_id);
							foreach ($combo_items as $combo_item) {
								$combo_item->quantity = $combo_item->qty * $item->quantity;
							}
						}
						//$units = $this->site->getUnitsByBUID($row->base_unit);
						$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
						$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
						$ri = $this->Settings->item_addition ? $row->id : $c;

						$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
						$c++;
					}
                }
            }
            
			
			if($this->config->item('purchase_request')){
				$this->data['purchase_requests'] = $this->site->getRefPurchaseRequests('approved');
			}
			
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $purchase_request_id;
            $this->data['billers'] = $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => site_url('purchase_orders'), 'page' => lang('purchase_orders')), array('link' => '#', 'page' => lang('add_purchase_order')));
            $meta = array('page_title' => lang('add_purchase_order'), 'bc' => $bc);
            $this->core_page('purchase_orders/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->cus->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->purchase_order_model->getPurchaseOrderByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->cus->view_rights($inv->created_by);
        }				
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        if ($this->form_validation->run() == true) {
            $quantity = "quantity";
            $product = "product";
            $unit_cost = "unit_cost";
            $tax_rate = "tax_rate";
			$biller_id = $this->input->post('biller');
			$biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('puo',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['purchase_orders-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
			$project_id = $this->input->post('project');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            if ($supplier_id) {
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            } else {
                $supplier = NULL;
            }
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
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$item_note = $_POST['pnote'][$r];
				if($this->Settings->cbm==1){
					$total_cbm = $_POST['total_cbm'][$r];
				}else{
					$total_cbm = 0;
				}
                if (isset($item_code) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->purchase_order_model->getProductByCode($item_code) : null;
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
						'product_note' => $item_note,
						'total_cbm' => $total_cbm
                    );

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
                'biller_id' => $biller_id,
                'biller' => $biller,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
				'project_id' => $project_id,
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

        if ($this->form_validation->run() == true && $this->purchase_order_model->updatePurchaseOrder($id, $data, $products)) {
            $this->session->set_userdata('remove_porls', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_order_added"));
            redirect('purchase_orders');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->purchase_order_model->getPurchaseOrderByID($id);
            $inv_items = $this->purchase_order_model->getAllPurchaseOrderItems($id);
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
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $item->real_unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $this->cus->formatDecimalRaw($item->net_unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity));
                $row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity) + $this->cus->formatDecimalRaw($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate = $item->tax_rate_id;
                $row->option = $item->option_id;
				$row->pnote = $item->product_note;
                $options = $this->purchase_order_model->getProductOptions($row->id, $item->warehouse_id);

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
                    $combo_items = $this->purchase_order_model->getProductComboItems($row->id, $item->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
				
				if($this->Settings->cbm==1 && $row->p_length > 0 && $row->p_width > 0 && $row->p_height){
					$cmb_unit = $this->site->getProductUnit($row->id,$row->purchase_unit);
					if($cmb_unit->unit_qty > 1){
						$row->p_unit_qty = $cmb_unit->unit_qty;
					}else{
						$row->p_unit_qty = 1;
					}
				}
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $c++;
            }
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['billers'] = $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] =$this->site->getWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => site_url('purchase_orders'), 'page' => lang('purchase_orders')), array('link' => '#', 'page' => lang('edit_purchase_order')));
            $meta = array('page_title' => lang('edit_purchase_order'), 'bc' => $bc);
            $this->core_page('purchase_orders/edit', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->cus->checkPermissions(NULL, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchase_order_model->deletePurchaseOrder($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("purchase_order_deleted");die();
            }
            $this->session->set_flashdata('message', lang('purchase_order_deleted'));
            redirect('welcome');
        }
    }
	
	public function approve($id = null)
    {
        if($this->Admin || $this->Owner || $this->GP['approve_purchase_orders']){
			if ($this->input->get('id')) {
				$id = $this->input->get('id');
			}
			$data = array("status" => "approved",
							"approved_by" => $this->session->userdata('user_id'),
							"approved_at" => date('Y-m-d H:i:s')
						);
			if ($this->purchase_order_model->approvePurchaseOrder($id, $data)) {
				if ($this->input->is_ajax_request()) {
					echo lang("purchase_order_approved");die();
				}
				$this->session->set_flashdata('message', lang('purchase_order_approved'));
				redirect('welcome');
			}
		}else{
		   $this->session->set_flashdata('error', lang("access_denied"));
           die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
		}
    }
	
	public function unapprove($id = null)
    {
        if($this->Admin || $this->Owner || $this->GP['approve_purchase_orders']){

			if ($this->input->get('id')) {
				$id = $this->input->get('id');
			}

			if ($this->purchase_order_model->unApprovePurchaseOrder($id)) {
				if ($this->input->is_ajax_request()) {
					echo lang("purchase_order_unapproved");die();
				}
				$this->session->set_flashdata('message', lang('purchase_order_unapproved'));
				redirect('welcome');
			}
		}else{
		   $this->session->set_flashdata('error', lang("access_denied"));
           die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
		}	
    }
	
	public function reject($id = null)
    {
        if($this->Admin || $this->Owner || $this->GP['approve_purchase_orders']){

			if ($this->input->get('id')) {
				$id = $this->input->get('id');
			}

			if ($this->purchase_order_model->rejectPurchaseOrder($id)) {
				if ($this->input->is_ajax_request()) {
					echo lang("purchase_order_rejected");die();
				}
				$this->session->set_flashdata('message', lang('purchase_order_rejected'));
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
        $supplier_id = $this->input->get('supplier_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->purchase_order_model->getProductNames($sr,$warehouse_id);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option = false;
				$row->qty = 1;
                $row->discount = '0';
                $row->item_tax_method = $row->tax_method;
                $options = $this->purchase_order_model->getProductOptions($row->id,$warehouse_id);				
                
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->purchase_order_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = FALSE;
                }
                $row->option = $option_id;
                $row->supplier_part_no = '';
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                //$row->cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
				$row->price = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                $row->real_unit_price = $row->cost;
				$row->unit_price = $row->cost;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->cost;
                $row->unit = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->purchase_order_model->getProductComboItems($row->id, $warehouse_id);
                }  
					
				if($this->Settings->cbm==1 && $row->p_length > 0 && $row->p_width > 0 && $row->p_height){
					$cmb_unit = $this->site->getProductUnit($row->id,$row->purchase_unit);
					if($cmb_unit->unit_qty > 1){
						$row->p_unit_qty = $cmb_unit->unit_qty;
					}else{
						$row->p_unit_qty = 1;
					}
				}
			
				
				$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $r++;
            }
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	public function getSupplierCost($supplier_id, $product)
    {
        switch ($supplier_id) {
            case $product->supplier1:
                $cost =  $product->supplier1price > 0 ? $product->supplier1price : $product->cost;
                break;
            case $product->supplier2:
                $cost =  $product->supplier2price > 0 ? $product->supplier2price : $product->cost;
                break;
            case $product->supplier3:
                $cost =  $product->supplier3price > 0 ? $product->supplier3price : $product->cost;
                break;
            case $product->supplier4:
                $cost =  $product->supplier4price > 0 ? $product->supplier4price : $product->cost;
                break;
            case $product->supplier5:
                $cost =  $product->supplier5price > 0 ? $product->supplier5price : $product->cost;
                break;
            default:
                $cost = $product->cost;
        }
        return $cost;
    }

    public function purchase_order_action()
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
                        $this->purchase_order_model->deletePurchaseOrder($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("purchase_orders_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('purchase_orders'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $qu = $this->purchase_order_model->getPurchaseOrderByID($id);
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
                    $filename = 'purchase_orders_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_purchase_order_selected"));
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

        if ($this->form_validation->run() == true && $this->purchase_order_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {

            $this->data['inv'] = $this->purchase_order_model->getPurchaseOrderByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'purchase_orders/update_status', $this->data);

        }
    }
	
	public function get_project()
	{
		$id = $this->input->get("biller");
		$project_id = $this->input->get("project");
		$rows = $this->site->getAllProjectByBillerID($id);
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$project = json_decode($user->project_ids);
		$pl = array();
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
		$opt = form_dropdown('project', $pl, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="project" class="form-control select"');
		echo json_encode(array("result" => $opt));
	}
	
	
	public function deposits($id)
    {
        $this->cus->checkPermissions('deposits', true, 'suppliers');
		$this->data['payments'] = $this->purchase_order_model->getDeposits($id);
		$this->data['inv'] = $this->purchase_order_model->getPurchaseOrderByID($id);
        $this->load->view($this->theme . 'purchase_orders/deposits', $this->data);
    }
	
	public function deposit_note($id = null)
    {
		$this->cus->checkPermissions('deposits', true, 'suppliers');	
        $payment = $this->purchase_order_model->getDepositByID($id);
        $inv = $this->purchase_order_model->getPurchaseOrderByID($payment->purchase_order_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
		$this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line("deposit_note");
		
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Purchase Order Deposit',$payment->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Purchase Order Deposit',$payment->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'purchase_orders/deposit_note', $this->data);
    }
	
	
	public function add_deposit($id = false, $write_off = false)
    {
        $this->cus->checkPermissions('deposits', true, 'suppliers');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		$purchase_order = $this->purchase_order_model->getPurchaseOrderByID($id);
        if ($this->form_validation->run() == true) {
			$write_off = $this->input->post('write_off') ? $this->input->post('write_off') : null;
			if ($this->Owner || $this->Admin || $this->cus->GP['purchase_order-date'] ) {
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
			if($write_off && $write_off == "write_off"){
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$purchase_order->biller_id);
				$type = "received";
				$narrative = "Write-off Purchase Order Deposit ";
				$amount_paid = $this->input->post('amount-paid') * (-1);
			}else{
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$purchase_order->biller_id);
				$type = "sent";
				$narrative = "Purchase Order Deposit ";
				$amount_paid = $this->input->post('amount-paid');
			}
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_account = $cash_account->account_code;
            $payment = array(
                'date' => $date,
				'purchase_order_id' => $id,
                'reference_no' => $reference_no,
                'amount' => $amount_paid,
				'transaction' => "PO Deposit",
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->cus->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
                'type' => $type,
				'account_code' => $paying_account,
				'currencies' => json_encode($currencies),
            );
			
			//=====accountig=====//
					if($this->Settings->accounting == 1){
						$paymentAcc = $this->site->getAccountSettingByBiller($purchase_order->biller_id);
						$accTranPayments[] = array(
								'transaction' => 'Purchase Order Deposit',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' =>$paymentAcc->supplier_deposit_acc,
								'amount' => $amount_paid,
								'narrative' => $narrative.$purchase_order->reference_no,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase_order->biller_id,
								'project_id' => $purchase_order->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase_order->supplier_id,
							);
						$accTranPayments[] = array(
								'transaction' => 'Purchase Order Deposit',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $paying_account,
								'amount' => $amount_paid * (-1),
								'narrative' => $narrative.$purchase_order->reference_no,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase_order->biller_id,
								'project_id' => $purchase_order->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase_order->supplier_id,
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

        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchase_order_model->addDeposit($payment,$accTranPayments)) {
            if($write_off && $write_off == "write_off"){
				$this->session->set_flashdata('message', lang("deposit_write_off_added"));
			}else{
				$this->session->set_flashdata('message', lang("deposit_added"));
			}
			
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			if($write_off && $write_off == "write_off"){
				$this->data['po_deposit'] = $this->purchase_order_model->getTotalDeposit($purchase_order->id)->amount;
			}
			$this->data['write_off'] = $write_off;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $purchase_order;
            $this->data['payment_ref'] = '';
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'purchase_orders/add_deposit', $this->data);
        }
    }
	
	
	public function edit_deposit($id = null)
    {
        $this->cus->checkPermissions('deposits', true, 'suppliers');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$payment_info = $this->purchase_order_model->getDepositByID($id);
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			$purchase_order = $this->purchase_order_model->getPurchaseOrderByID($payment_info->purchase_order_id);
			if ($this->Owner || $this->Admin || $this->cus->GP['purchase_order-date'] ) {
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
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
            $payment = array(
                'date' => $date,
                'purchase_order_id' => $this->input->post('purchase_order_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
				'transaction' => "PO Deposit",
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->cus->clear_tags($this->input->post('note')),
				'currencies' => json_encode($currencies),
				'account_code' => $paying_from,
            );
			
				//=====accountig=====//
					if($this->Settings->accounting == 1){
						$paymentAcc = $this->site->getAccountSettingByBiller($purchase_order->biller_id);
						$accTranPayments[] = array(
								'transaction' => 'Purchase Order Deposit',
								'transaction_id' => $id,
								'transaction_date' => $date,
								'reference' => $this->input->post('reference_no'),
								'account' => $paymentAcc->supplier_deposit_acc,
								'amount' => $this->input->post('amount-paid'),
								'narrative' => 'Purchase Order Deposit '.$purchase_order->reference_no,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase_order->biller_id,
								'project_id' => $purchase_order->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase_order->supplier_id,
							);
						$accTranPayments[] = array(
								'transaction' => 'Purchase Order Deposit',
								'transaction_id' => $id,
								'transaction_date' => $date,
								'reference' => $this->input->post('reference_no'),
								'account' => $paying_from,
								'amount' => $this->input->post('amount-paid') * (-1),
								'narrative' => 'Purchase Order Deposit '.$purchase_order->reference_no,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase_order->biller_id,
								'project_id' => $purchase_order->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase_order->supplier_id,
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

        if ($this->form_validation->run() == true && $this->purchase_order_model->updateDeposit($id, $payment, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("deposit_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $this->purchase_order_model->getDepositByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$payment_info->account_code,'1');
			}
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'purchase_orders/edit_deposit', $this->data);
        }
    }
	
	
	public function delete_deposit($id = null)
    {
		$this->cus->checkPermissions('delete_deposit', true, 'suppliers');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->purchase_order_model->deleteDeposit($id)) {
            $this->session->set_flashdata('message', lang("deposit_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
}
