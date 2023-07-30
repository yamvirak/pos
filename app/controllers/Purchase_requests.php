<?php defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_requests extends MY_Controller
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
        $this->lang->load('purchase_requests', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('purchase_request_model');
        $this->digital_upload_path = 'files/';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;

    }

    public function index($warehouse_id = NULL, $biller_id = NULL)
    {
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => '#', 'page' => lang('purchase_requests')));
        $meta = array('page_title' => lang('purchase_requests'), 'bc' => $bc);
        $this->core_page('purchase_requests/index', $meta, $this->data);

    }

    public function getPurchaseRequests($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions('index');
        $detail_link = anchor('purchase_requests/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_request_details'));
        $email_link = anchor('purchase_requests/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase_request'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $edit_link = anchor('purchase_requests/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase_request'), ' class="edit_purchase_request" ');
        $pc_link = anchor('purchase_orders/add/$1', '<i class="fa fa-star"></i> ' . lang('add_purchase_order'), ' class="create_purchase" ');
		$delete_link = "<a href='#' class='po delete_purchase_request' title='<b>" . $this->lang->line("delete_purchase_request") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchase_requests/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_purchase_request') . "</a>";
		if($this->Admin || $this->Owner || $this->GP['approve_purchase_requests']){
			$approve_link = "<a href='#' class='po approve_purchase_request' title='<b>" . $this->lang->line("approve_purchase_request") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . site_url('purchase_requests/approve/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_purchase_request') . "</a>";
			
			$unapprove_link = "<a href='#' class='po unapprove_purchase_request' title='<b>" . $this->lang->line("unapprove_purchase_request") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchase_requests/unapprove/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_purchase_request') . "</a>";
			
			
			$reject_link = "<a href='#' class='po reject_purchase_request' title='<b>" . $this->lang->line("reject_purchase_request") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchase_requests/reject/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('reject_purchase_request') . "</a>";
		}
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $detail_link . '</li>
                        <li>' . $edit_link . '</li>
						<li>' . $approve_link . '</li>
						<li>' . $unapprove_link . '</li>
						<li>' . $reject_link . '</li>
                        <li>' . $pc_link . '</li>						
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
       
        $this->load->library('datatables');
		$this->datatables
			->select("purchase_requests.id as id, purchase_requests.date, purchase_requests.reference_no, projects.name, purchase_requests.note, purchase_requests.grand_total, purchase_requests.status, purchase_requests.attachment")
			->from('purchase_requests');
		$this->datatables->join("projects","projects.id=purchase_requests.project_id","left");
		if ($warehouse_id) {
            $this->datatables->where('purchase_requests.warehouse_id', $warehouse_id);
        }
		if ($biller_id) {
            $this->datatables->where('purchase_requests.biller_id', $biller_id);
        }	
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('purchase_requests.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchase_requests.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('purchase_requests.created_by', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function modal_view($purchase_request_id = null)
    {
        $this->cus->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_request_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchase_request_model->getPurchaseRequestByID($purchase_request_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->purchase_request_model->getAllPurchaseRequestItems($purchase_request_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
		
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Purchase Request',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Purchase Request',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'purchase_requests/modal_view', $this->data);
    }

    public function view($purchase_request_id = null)
    {
        $this->cus->checkPermissions('index');

        if ($this->input->get('id')) {
            $purchase_request_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchase_request_model->getPurchaseRequestByID($purchase_request_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchase_request_model->getAllPurchaseRequestItems($purchase_request_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;

        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => site_url('purchase_requests'), 'page' => lang('purchase_requests')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_purchase_request_details'), 'bc' => $bc);
        $this->core_page('purchase_requests/view', $meta, $this->data);

    }

    public function pdf($purchase_request_id = null, $view = null, $save_bufffer = null)
    {
        $this->cus->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_request_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchase_request_model->getPurchaseRequestByID($purchase_request_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchase_request_model->getAllPurchaseRequestItems($purchase_request_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $name = $this->lang->line("purchase_request") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'purchase_requests/pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'purchase_requests/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->cus->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->cus->generate_pdf($html, $name);
        }
    }

    public function combine_pdf($purchase_requests_id)
    {
        $this->cus->checkPermissions('pdf');

        foreach ($purchase_requests_id as $purchase_request_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->purchase_request_model->getPurchaseRequestByID($purchase_request_id);
            if (!$this->session->userdata('view_right')) {
                $this->cus->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->purchase_request_model->getAllPurchaseRequestItems($purchase_request_id);
            $this->data['customer'] = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;

            $html[] = array(
                'content' => $this->load->view($this->theme . 'purchase_requests/pdf', $this->data, true),
                'footer' => '',
            );
        }

        $name = lang("purchase_requests") . ".pdf";
        $this->cus->generate_pdf($html, $name);

    }

    public function email($purchase_request_id = null)
    {
        $this->cus->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $purchase_request_id = $this->input->get('id');
        }
        $inv = $this->purchase_request_model->getPurchaseRequestByID($purchase_request_id);
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
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->name != '-' ? $biller->name : $biller->company) . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($purchase_request_id, null, 'S'); //delete_files($attachment);
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->cus->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->db->update('purchase_requests', array('status' => 'sent'), array('id' => $purchase_request_id));
            $this->session->set_flashdata('message', $this->lang->line("email_sent"));
            redirect("purchase_requests");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/purchase_request.html')) {
                $purchase_request_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/purchase_request.html');
            } else {
                $purchase_request_temp = file_get_contents('./themes/default/views/email_templates/purchase_request.html');
            }

            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('purchase_request').' (' . $inv->reference_no . ') '.lang('from').' '.$this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $purchase_request_temp),
            );
            $this->data['customer'] = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['id'] = $purchase_request_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchase_requests/email', $this->data);

        }
    }

    public function add()
    {
        $this->cus->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));        
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project'); 
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pr',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['purchase_requests-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
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

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->purchase_request_model->getProductByCode($item_code) : null;
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

        }

        if ($this->form_validation->run() == true && $this->purchase_request_model->addPurchaseRequest($data, $products)) {
            $this->session->set_userdata('remove_prls', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_request_added") ." ". $reference);
            
			if($this->input->post('add_purchase_request_next')){
				redirect('purchase_requests/add');
			}else{
				redirect('purchase_requests');
			}
			
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => site_url('purchase_requests'), 'page' => lang('purchase_requests')), array('link' => '#', 'page' => lang('add_purchase_request')));
            $meta = array('page_title' => lang('add_purchase_request'), 'bc' => $bc);
            $this->core_page('purchase_requests/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->cus->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->purchase_request_model->getPurchaseRequestByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->cus->view_rights($inv->created_by);
        }				
		
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        //$this->form_validation->set_rules('reference_no', $this->lang->line("reference_no"), 'required');
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
       
        if ($this->form_validation->run() == true) {
            $quantity = "quantity";
            $product = "product";
            $unit_cost = "unit_cost";
            $tax_rate = "tax_rate";
            $biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project'); 
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pr',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['purchase_requests-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
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

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->purchase_request_model->getProductByCode($item_code) : null;
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

        if ($this->form_validation->run() == true && $this->purchase_request_model->updatePurchaseRequest($id, $data, $products)) {
            $this->session->set_userdata('remove_prls', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_request_added"));
            redirect('purchase_requests');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->purchase_request_model->getPurchaseRequestByID($id);
            $inv_items = $this->purchase_request_model->getAllPurchaseRequestItems($id);
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
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['billers'] =   $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => site_url('purchase_requests'), 'page' => lang('purchase_requests')), array('link' => '#', 'page' => lang('edit_purchase_request')));
            $meta = array('page_title' => lang('edit_purchase_request'), 'bc' => $bc);
            $this->core_page('purchase_requests/edit', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->cus->checkPermissions(NULL, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchase_request_model->deletePurchaseRequest($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("purchase_request_deleted");die();
            }
            $this->session->set_flashdata('message', lang('purchase_request_deleted'));
            redirect('welcome');
        }
    }
	
	public function approve($id = null)
    {
       if($this->Admin || $this->Owner || $this->GP['approve_purchase_requests']){
			if ($this->input->get('id')) {
				$id = $this->input->get('id');
			}
			$data = array("status" => "approved",
							"approved_by" => $this->session->userdata('user_id'),
							"approved_at" => date('Y-m-d H:i:s')
						);

			if ($this->purchase_request_model->approvePurchaseRequest($id,$data)) {
				if ($this->input->is_ajax_request()) {
					echo lang("purchase_request_approved");die();
				}
				$this->session->set_flashdata('message', lang('purchase_request_approved'));
				redirect('welcome');
			}
	   }else{
		   $this->session->set_flashdata('error', lang("access_denied"));
           die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
	   }
    }
	
	public function unapprove($id = null)
    {
		if($this->Admin || $this->Owner || $this->GP['approve_purchase_requests']){
			if ($this->input->get('id')) {
				$id = $this->input->get('id');
			}

			if ($this->purchase_request_model->unApprovePurchaseRequest($id)) {
				if ($this->input->is_ajax_request()) {
					echo lang("purchase_request_unapproved");die();
				}
				$this->session->set_flashdata('message', lang('purchase_request_unapproved'));
				redirect('welcome');
			}
		}else{
		   $this->session->set_flashdata('error', lang("access_denied"));
           die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
		}
    }
	
	public function reject($id = null)
    {
        if($this->Admin || $this->Owner || $this->GP['approve_purchase_requests']){

			if ($this->input->get('id')) {
				$id = $this->input->get('id');
			}

			if ($this->purchase_request_model->rejectPurchaseRequest($id)) {
				if ($this->input->is_ajax_request()) {
					echo lang("purchase_request_rejected");die();
				}
				$this->session->set_flashdata('message', lang('purchase_request_rejected'));
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

        $rows = $this->purchase_request_model->getProductNames($sr,$warehouse_id);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option = false;
				$row->qty = 1;
                $row->discount = '0';
                $row->item_tax_method = $row->tax_method;
                $options = $this->purchase_request_model->getProductOptions($row->id,$warehouse_id);				
                
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->purchase_request_model->getProductOptionByID($option_id) : current($options);
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
                    $combo_items = $this->purchase_request_model->getProductComboItems($row->id, $warehouse_id);
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

    public function purchase_request_action()
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
                        $this->purchase_request_model->deletePurchaseRequest($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("purchase_requests_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('purchase_requests'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $qu = $this->purchase_request_model->getPurchaseRequestByID($id);
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
                    $filename = 'purchase_requests_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_purchase_request_selected"));
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

        if ($this->form_validation->run() == true && $this->purchase_request_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {

            $this->data['inv'] = $this->purchase_request_model->getPurchaseRequestByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'purchase_requests/update_status', $this->data);

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

	public function add_purchase_request_by_excel()
    {
		$this->cus->checkPermissions('add');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project'); 
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pr',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['purchase_requests-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
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
            $order_discount = 0;
            $percentage = '%';
			$finals = false;
			if (isset($_FILES["userfile"])) {
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++){
						$product_code = trim($worksheet->getCellByColumnAndRow(0, $row)->getValue());
						$unit_cost = trim($worksheet->getCellByColumnAndRow(1, $row)->getValue());
						$unit_qty = trim($worksheet->getCellByColumnAndRow(2, $row)->getValue());
						$unit_code = trim($worksheet->getCellByColumnAndRow(3, $row)->getValue());
						$finals[] = array(
							'product_code'	=> $product_code,
							'unit_cost'		=> $unit_cost,
							'unit_qty' 		=> $unit_qty,
							'unit_code'  	=> $unit_code,
	
						);
					}
				}
				
				$products = false;
				$total = 0;
				if($finals){
					foreach($finals as $final) {
						$product = $this->purchase_request_model->getProductByCode($final['product_code']);
						if(!$product){
							$this->session->set_flashdata('error', lang("product_code") . " (" . $final['product_code'] . "). " . lang("code__exist"));
							redirect("purchase_requests/add_purchase_request_by_excel");
						}
						if($final['unit_code'] && $final['unit_code'] != ""){
							$product_unit = $this->purchase_request_model->getProductUnitByCode($product->id,$final['unit_code']);
							if(!$product_unit){
								$this->session->set_flashdata('error', lang("product_unit") . " (" . $final['product_code'] . ") - (" . $final['product_unit'] . "). " . lang("code__exist"));
								redirect("purchase_requests/add_purchase_request_by_excel");
							}
						}else{
							$product_unit = $this->site->getProductUnit($product->id,$product->unit);
						}
						

						$products[] = array(
							'product_id' => $product->id,
							'product_code' => $product->code,
							'product_name' => $product->name,
							'product_type' => $product->type,
							'net_unit_price' => $final['unit_cost'],
							'unit_price'  => $final['unit_cost'],
							'quantity' => ($final['unit_qty'] * $product_unit->unit_qty),
							'product_unit_id' => $product_unit->id,
							'product_unit_code' => $product_unit->code,
							'unit_quantity' => $final['unit_qty'],
							'warehouse_id' => $warehouse_id,
							'subtotal' => ($final['unit_cost'] * $final['unit_qty']),
							'real_unit_price' => ($final['unit_cost'] /  $product_unit->unit_qty)
						);
						$total += ($final['unit_cost'] * $final['unit_qty']);
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
						$order_discount = $this->cus->formatDecimalRaw((($total * (Float) ($ods[0])) / 100), 4);

					} else {
						$order_discount = $this->cus->formatDecimalRaw($order_discount_id);
					}
				} else {
					$order_discount_id = null;
				}
				$total_discount = $order_discount;

				if ($this->Settings->tax2 != 0) {
					$order_tax_id = $this->input->post('order_tax');
					if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
						if ($order_tax_details->type == 2) {
							$order_tax = $order_tax_details->rate;
						}
						if ($order_tax_details->type == 1) {
							$order_tax = (($total - $order_discount) * $order_tax_details->rate) / 100;
						}
					}
				} else {
					$order_tax_id = null;
				}

				$total_tax = $this->cus->formatDecimalRaw($order_tax, 4); 
				$grand_total = $this->cus->formatDecimalRaw(($total + $total_tax - $order_discount), 4);
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
					'order_discount_id' => $order_discount_id,
					'order_discount' => $order_discount,
					'total_discount' => $total_discount,
					'order_tax_id' => $order_tax_id,
					'order_tax' => $order_tax,
					'total_tax' => $total_tax,
					'grand_total' => $grand_total,
					'status' => $status,
					'created_by' => $this->session->userdata('user_id'),
				);
				
				if ($_FILES['userfile']['size'] > 0) {
					$this->load->library('upload');
					$config['upload_path'] = $this->digital_upload_path;
					$config['allowed_types'] = $this->digital_file_types;
					$config['max_size'] = $this->allowed_file_size;
					$config['overwrite'] = false;
					$config['encrypt_name'] = true;
					$this->upload->initialize($config);
					if (!$this->upload->do_upload('userfile')) {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$photo = $this->upload->file_name;
					$data['attachment'] = $photo;
				}
			}
	
        }
		
		if ($this->form_validation->run() == true && $this->purchase_request_model->addPurchaseRequest($data, $products)) {
            $this->session->set_flashdata('message', $this->lang->line("purchase_request_added") ." ". $reference);
			redirect('purchase_requests');
        } else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => site_url('purchase_requests'), 'page' => lang('purchase_requests')), array('link' => '#', 'page' => lang('add_purchase_request_by_excel')));
            $meta = array('page_title' => lang('add_purchase_request_by_excel'), 'bc' => $bc);
            $this->core_page('purchase_requests/add_purchase_request_by_excel', $meta, $this->data);		
        }
    }
	
	
	
	
}
