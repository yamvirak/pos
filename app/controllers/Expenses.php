<?php defined('BASEPATH') or exit('No direct script access allowed');

class Expenses extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->cus->md('login');
        }
        if ($this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('purchases', $this->Settings->user_language);
        $this->load->library('form_validation');
		if($this->config->item("send_telegram")){
			$this->load->library('telegrambot');
		}
        $this->load->model('purchases_model');
		$this->load->model('purchase_order_model');
		$this->load->model('companies_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '10240';
        $this->data['logo'] = true;
    }
    /* ------------------------------------------------------------------------- */
	
    public function index($warehouse_id = null, $biller_id = NULL, $payment_status = null)
    {
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		if($biller_id == 0){
			$biller_id = null;
		}
        $this->cus->checkPermissions();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$this->data['payment_status'] = $payment_status;
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('expenses'), 'page' => lang('expenses')), array('link' => '#', 'page' => lang('expenses')));
        $meta = array('page_title' => lang('expenses'), 'bc' => $bc);
        $this->core_page('expenses/index', $meta, $this->data);

    }

	

    /* ----------------------------------------------------------------------------- */

    public function modal_view($purchase_id = null)
    {
        $this->cus->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
		
		if($inv->status=='freight'){
			if($inv->purchase_id > 0){
				$this->data['purchase'] = $this->purchases_model->getPurchaseByID($inv->purchase_id);
				$this->data['rows'] = $this->purchases_model->getPurchaseShippingItems($inv->purchase_id);
			}else{
				$this->data['purchase'] = $this->purchases_model->getReceiveByID($inv->receive_id);
				$this->data['rows'] = $this->purchases_model->getPurchaseShippingItems(false,$inv->receive_id);
			}
			
		}else{
            if($this->Settings->product_serial == 1){
                $this->data['rows'] = $this->purchases_model->getAllPurchaseItemsWithSerial($purchase_id);
            }else{
                $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
            }
			
			$this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
			$this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
		}
       
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
		$this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $this->purchases_model->getPaymentByPurchaseID($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
		
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Purchase',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Purchase',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
	   
		if($inv->status=='freight'){
			$this->load->view($this->theme . 'purchases/modal_view_freight', $this->data);
		}else{
			$this->load->view($this->theme . 'purchases/modal_view', $this->data);
		}
    }

    public function view($purchase_id = null)
    {
        $this->cus->checkPermissions('index');

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;

        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_purchase_details'), 'bc' => $bc);
        $this->core_page('purchases/view', $meta, $this->data);

    }

    /* ----------------------------------------------------------------------------- */

	//generate pdf and force to download

    public function pdf($purchase_id = null, $view = null, $save_bufffer = null)
    {
        $this->cus->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['inv'] = $inv;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
        $name = $this->lang->line("purchase") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'purchases/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->cus->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->cus->generate_pdf($html, $name);
        }

    }

    public function combine_pdf($purchases_id)
    {
        $this->cus->checkPermissions('pdf');

        foreach ($purchases_id as $purchase_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->purchases_model->getPurchaseByID($purchase_id);
            if (!$this->session->userdata('view_right')) {
                $this->cus->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['created_by'] = $this->site->getUser($inv->created_by);
            $this->data['inv'] = $inv;
            $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
            $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
            $inv_html = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
            if (! $this->Settings->barcode_img) {
                $inv_html = preg_replace("'\<\?xml(.*)\?\>'", '', $inv_html);
            }
            $html[] = array(
                'content' => $inv_html,
                'footer' => '',
            );
        }
		
        $name = lang("purchases") . ".pdf";
        $file = $this->cus->generate_pdf($html, $name, "S");
		if($file){
			redirect(base_url($file));
		}
    }
	

    public function email($purchase_id = null)
    {
        $this->cus->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
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
            $supplier = $this->site->getCompanyByID($inv->supplier_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $supplier->name,
                'company' => $supplier->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($purchase_id, null, 'S');
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->cus->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->db->update('purchases', array('status' => 'ordered'), array('id' => $purchase_id));
            $this->session->set_flashdata('message', $this->lang->line("email_sent"));
            redirect("purchases");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/purchase.html')) {
                $purchase_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/purchase.html');
            } else {
                $purchase_temp = file_get_contents('./themes/default/views/email_templates/purchase.html');
            }
            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('purchase_order').' (' . $inv->reference_no . ') '.lang('from').' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $purchase_temp),
            );
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);

            $this->data['id'] = $purchase_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/email', $this->data);

        }
    }


    public function delete($id = null)
    {
        $this->cus->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deletePurchase($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("purchase_deleted");die();
            }
            $this->session->set_flashdata('message', lang('purchase_deleted'));
            redirect('welcome');
        }
    }

    /* --------------------------------------------------------------------------- */
	
	public function suggestion_expenses()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $rows = $this->purchases_model->getExpenseNames($sr);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option = false;
				$row->quantity = 1;
                $row->unit_cost = 1;
				$row->description = $this->cus->remove_tag($row->note);
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
                $r++;
            }
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id', true);
		$warehouse_id = $this->input->get('warehouse_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->purchases_model->getProductNames($sr);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 1;
            foreach ($rows as $row) {
				$pr = array();
				if($row->type=='combo'){
					$combo_items = $this->site->getProductComboItems($row->id, $warehouse_id);
					if($combo_items){
						foreach($combo_items as $combo_item){
							$combo_item = $this->purchases_model->getProductByCode($combo_item->code);
							$option = false;
							$combo_item->item_tax_method = $combo_item->tax_method;
							$options = $this->purchases_model->getProductOptions($combo_item->id);
							if ($options) {
								$opt = $option_id && $r == 0 ? $this->purchases_model->getProductOptionByID($option_id) : current($options);
								if (!$option_id || $r > 0) {
									$option_id = $opt->id;
								}
							} else {
								$opt = json_decode('{}');
								$opt->cost = 0;
								$option_id = FALSE;
							}
							$combo_item->option = $option_id;
							$combo_item->supplier_part_no = '';
							if ($opt->cost != 0) {
								$combo_item->cost = $opt->cost;
							}
							$combo_item->cost = $supplier_id ? $this->getSupplierCost($supplier_id, $combo_item) : $combo_item->cost;
							$combo_item->real_unit_cost = $combo_item->cost;
							$combo_item->unit_cost = $combo_item->cost;
							$combo_item->base_quantity = 1;
							$combo_item->base_unit = $combo_item->unit;
							$combo_item->base_unit_cost = $combo_item->cost;
							$combo_item->unit = $combo_item->purchase_unit ? $combo_item->purchase_unit : $combo_item->unit;
							$combo_item->new_entry = 1;
							$combo_item->expiry = '';
							$combo_item->qty = 1;
							$combo_item->quantity_balance = '';
							$combo_item->discount = '0';
							$combo_item->serial_no = '';
							unset($combo_item->details, $combo_item->product_details, $combo_item->price, $combo_item->file, $combo_item->supplier1price, $combo_item->supplier2price, $combo_item->supplier3price, $combo_item->supplier4price, $combo_item->supplier5price, $combo_item->supplier1_part_no, $combo_item->supplier2_part_no, $combo_item->supplier3_part_no, $combo_item->supplier4_part_no, $combo_item->supplier5_part_no);
							
							if($this->Settings->product_serial == 1){
								$product_serials = $this->purchases_model->getProductSerialDetailsByProductId($combo_item->id, $warehouse_id);
							}else{
								$product_serials = false;
							}
							if($this->Settings->cbm==1 && $combo_item->p_length > 0 && $combo_item->p_width > 0 && $combo_item->p_height){
								$cmb_unit = $this->site->getProductUnit($combo_item->id,$combo_item->purchase_unit);
								if($cmb_unit->unit_qty > 1){
									$combo_item->p_unit_qty = $cmb_unit->unit_qty;
								}else{
									$combo_item->p_unit_qty = 1;
								}
							}
							
							$units = $this->site->getUnitbyProduct($combo_item->id,$combo_item->base_unit);
							$tax_rate = $this->site->getTaxRateByID($combo_item->tax_rate);

							$pr[] = array('id' => ($c + $r), 'item_id' => $combo_item->id, 'label' => $combo_item->name . " (" . $combo_item->code . ")", 
								'row' => $combo_item, 'product_serials'=>$product_serials, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
							$r++;
							$c++;
						}
						$ct[] = array('id' => $r, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'products' =>$pr);
						$r++;
					}
				}else{
					$option = false;
					$row->item_tax_method = $row->tax_method;
					$options = $this->purchases_model->getProductOptions($row->id);
					if ($options) {
						$opt = $option_id && $r == 0 ? $this->purchases_model->getProductOptionByID($option_id) : current($options);
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
					$row->cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
					$row->real_unit_cost = $row->cost;
					$row->unit_cost = $row->cost;
					$row->base_quantity = 1;
					$row->base_unit = $row->unit;
					$row->base_unit_cost = $row->cost;
					$row->unit = $row->purchase_unit ? $row->purchase_unit : $row->unit;
					$row->new_entry = 1;
					$row->expiry = '';
					$row->qty = 1;
					$row->quantity_balance = '';
					$row->discount = '0';
					$row->serial_no = '';
					unset($row->details, $row->product_details, $row->price, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
					
					if($this->Settings->product_serial == 1){
						$product_serials = $this->purchases_model->getProductSerialDetailsByProductId($row->id, $warehouse_id);
					}else{
						$product_serials = false;
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

					$pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
						'row' => $row, 'product_serials'=>$product_serials, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
					$ct[] = array('id' => $r, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'products' =>$pr);
					$r++;
				}
            }
            $this->cus->send_json($ct);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function purchase_actions()
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
                        $this->purchases_model->deletePurchase($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("purchases_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('purchases'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('returned'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('purchase_status'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));
					
					$q = $this->db->select("purchases.id as id, 
											DATE_FORMAT(".$this->db->dbprefix('purchases').".date, '%Y-%m-%d %T') as date, 
											purchases.reference_no,
											purchases.supplier, 
											purchases.grand_total,
											abs(IFNULL(cus_purchases.return_purchase_total,0)) as return_purchase_total,
											(IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0)) as paid, 
											round((cus_purchases.grand_total-(IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))-abs(cus_purchases.return_purchase_total)),2) as balance, 
											purchases.status, 
											IF(
												(round((cus_purchases.grand_total-(IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))-abs(cus_purchases.return_purchase_total)),2))=0,'paid',
												IF(
													(abs(IFNULL(cus_purchases.return_purchase_total,0)) + IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))<>0,'partial',
													'pending'
												)
											) as payment_status,
											purchases.attachment")
					->join("(select purchase_id,abs(paid) as return_paid from cus_purchases WHERE purchase_id > 0 AND status <> 'draft' AND status <> 'freight') as pur_return","pur_return.purchase_id = purchases.id","left");
					$this->db->order_by("purchases.id","desc");
					$this->db->where_in("purchases.id",$_POST['val']);
					$q = $this->db->get("purchases");
					$row = 2;
					if ($q->num_rows() > 0) {
						foreach (($q->result()) as $purchase) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($purchase->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $purchase->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $purchase->supplier);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->cus->formatDecimal($purchase->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->cus->formatDecimal($purchase->return_purchase_total));
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->formatDecimal($purchase->paid));
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->formatDecimal($purchase->balance));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($purchase->status));
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($purchase->payment_status));
							$row++;
						}
					}

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'purchases_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_purchase_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function payments($id = null, $ex_id = null)
    {
        $this->cus->checkPermissions(false, true);
		if($id){
			$this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
			$this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
		}else{
			$expense = $this->purchases_model->getExpenseByID($ex_id); 
			$this->data['payments'] = $this->purchases_model->getExpensePayments($ex_id);
			$this->data['inv'] = $expense;
		}
        $this->load->view($this->theme . 'purchases/payments', $this->data);
    }
	
	public function payment_returns($id = null)
    {
        $this->cus->checkPermissions(false, true);
		$this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
		$this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
        $this->load->view($this->theme . 'purchases/payment_returns', $this->data);
    }
	

    public function payment_note($id = null)
    {
        $this->cus->checkPermissions('payments', true);
        $payment = $this->purchases_model->getPaymentByID($id);
		$inv_payments = $this->purchases_model->getPaymentsByRef($payment->reference_no,$payment->date);
        if($payment->purchase_id){
			$inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
		}else{
			$inv = $this->purchases_model->getExpenseByID($payment->expense_id);
			$inv->grand_total = $inv->amount;
		}
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
		$this->data['inv_payments'] = $inv_payments;
		$this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line("payment_note");
		
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Purchase Payment',$payment->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Purchase Payment',$payment->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'purchases/payment_note', $this->data);
    }

    public function email_payment($id = null)
    {
        $this->cus->checkPermissions('payments', true);
        $payment = $this->purchases_model->getPaymentByID($id);
        $inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
        $supplier = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        if ( ! $supplier->email) {
            $this->cus->send_json(array('msg' => lang("update_supplier_email")));
        }
        $this->data['supplier'] =$supplier;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = lang("payment_note");
        $html = $this->load->view($this->theme . 'purchases/payment_note', $this->data, TRUE);

        $html = str_replace(array('<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>'.lang("stamp_sign").'</p>'), '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);
        // $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">'.$html.'</div>';

        $this->load->library('parser');
        $parse_data = array(
            'stylesheet' => '<link href="'.$this->data['assets'].'styles/helpers/bootstrap.min.css" rel="stylesheet"/>',
            'name' => $supplier->company && $supplier->company != '-' ? $supplier->company :  $supplier->name,
            'email' => $supplier->email,
            'heading' => lang('payment_note').'<hr>',
            'msg' => $html,
            'site_link' => base_url(),
            'site_name' => $this->Settings->site_name,
            'logo' => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>'
        );
        $msg = file_get_contents('./themes/' . $this->Settings->theme . '/views/email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->Settings->site_name;

        if ($this->cus->send_email($supplier->email, $subject, $message)) {
            $this->cus->send_json(array('msg' => lang("email_sent")));
        } else {
            $this->cus->send_json(array('msg' => lang("email_failed")));
        }
    }
	
	public function add_multi_payment($id = null)
    {
        $this->cus->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$ids = explode('PurchaseID',$id);		
        $purchase = $this->purchases_model->getMultiPurchaseByID($ids);
		$multiple = $this->purchases_model->getPurhcaseByBillers($ids);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			
            if ($this->Owner || $this->Admin || $this->cus->GP['purchases-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$total_amount = $this->input->post('amount-paid');
			$camounts = $this->input->post("c_amount");
			
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
            }

			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$multiple->row()->biller_id);
            $camounts = $this->input->post("c_amount");
            $paid_currencies = array();
            if(!empty($camounts)){
                foreach($camounts as $key => $camount){
                    $currency = $this->input->post("currency");
                    $rate = $this->input->post("rate");
                    $paid_currencies[$currency[$key]] = array(
                                "amount" => $camounts[$key],
                                "currency" => $currency[$key],
                                "rate" => $rate[$key],
                            );
                }
            }
			$cur_def = $this->site->getCurrencyByCode($this->Settings->default_currency);
			for($i=0; $i<count($ids); $i++){
				if($total_amount > 0){
					$purchaseInfo = $this->purchases_model->getPurchaseBalanceByID($ids[$i]);
					if($purchaseInfo){
						$total = ($purchaseInfo->grand_total+$purchaseInfo->return_paid) - ($purchaseInfo->paid+$purchaseInfo->discount+$purchaseInfo->return_total);
						$grand_total = $total;
						if($total_amount > $grand_total){
							$pay_amount = $grand_total;
							$total_amount = $total_amount - $grand_total;
						}else{
							$pay_amount = $total_amount;
							$total_amount = 0;
						}
					
						$currencies = array();
						if(!empty($camounts)){
							$total_paid = $pay_amount;
						
							foreach($paid_currencies as $cur_code => $paid_currencie){
								$paid_cur = $paid_currencie['amount'];
								if($paid_cur > 0){
									if($cur_code != $cur_def->code){
										if($paid_currencie['rate'] > $cur_def->rate){
											$paid_cur = $paid_cur / $paid_currencie['rate'];
										}else{
											$paid_cur = $paid_cur * $cur_def->rate;
										}
									}
					
									if($paid_cur >= $total_paid && $total_paid > 0){
										$paid_currencie['amount'] = $total_paid;
										if($cur_code != $cur_def->code){
											if($paid_currencie['rate'] > $cur_def->rate){
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) * $paid_currencie['rate'];
											}else{
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) / $cur_def->rate;
											}
										}else{
											$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid);
										}
										$total_paid = 0;
									}else{
										if($total_paid > 0){
											$paid_currencie['amount'] = $paid_cur;
											$paid_currencies[$cur_code]['amount'] = 0;
											$total_paid = $total_paid - $paid_cur;
										}else{
											$paid_currencie['amount'] = 0;
										}
									}
								}								
								if($cur_code != $cur_def->code){
									if($paid_currencie['rate'] > $cur_def->rate){
										$paid_currencie['amount'] = $paid_currencie['amount'] * $paid_currencie['rate'];
									}else{
										$paid_currencie['amount'] = $paid_currencie['amount'] / $cur_def->rate;
									}
								}
								$currencies[] = $paid_currencie;
							}
						}
						$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
						$paying_from = $cash_account->account_code;
						$payment[] = array(
							'date' => $date,
							'purchase_id' => $purchaseInfo->id,
							'reference_no' => $reference_no,
							'amount' => $pay_amount,
							'paid_by' => $this->input->post('paid_by'),
							'note' => $this->input->post('note'),
							'created_by' => $this->session->userdata('user_id'),
							'type' => 'sent',
							'currencies' => json_encode($currencies),
							'account_code' => $paying_from,
							'attachment' => $photo,
						);
						
						//=====accountig=====//
							if($this->Settings->accounting == 1){
								$paymentAcc = $this->site->getAccountSettingByBiller($purchaseInfo->biller_id);
								$accTranPayments[$purchaseInfo->id][] = array(
										'transaction' => 'Payment',
										'transaction_date' => $date,
										'reference' => $reference_no,
										'account' => (($this->Settings->default_payable_account==0 || !$purchaseInfo->ap_account) ? $paymentAcc->ap_acc : $purchaseInfo->ap_account),
										'amount' => $pay_amount,
										'narrative' => 'Purchase Payment '.$purchaseInfo->reference_no,
										'description' => $this->input->post('note'),
										'biller_id' => $purchaseInfo->biller_id,
										'project_id' => $purchaseInfo->project_id,
										'user_id' => $this->session->userdata('user_id'),
										'supplier_id' => $purchaseInfo->supplier_id,
									);
								$accTranPayments[$purchaseInfo->id][] = array(
										'transaction' => 'Payment',
										'transaction_date' => $date,
										'reference' => $reference_no,
										'account' => $paying_from,
										'amount' => $pay_amount * (-1),
										'narrative' => 'Purchase Payment '.$purchaseInfo->reference_no,
										'description' => $this->input->post('note'),
										'biller_id' => $purchaseInfo->biller_id,
										'project_id' => $purchaseInfo->project_id,
										'user_id' => $this->session->userdata('user_id'),
										'supplier_id' => $purchaseInfo->supplier_id,
									);
							}
						//=====end accountig=====//
						
					}
				}
				
			}
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->purchases_model->addMultiPayment($payment, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if (!$purchase) {
                $this->session->set_flashdata('warning', lang('purchases_already_paid'));
                $this->cus->md();
            }
			if($multiple->num_rows() > 1){
				$this->session->set_flashdata('error', lang("biller_multi_cannot_add"));
				$this->cus->md();
			}
            $this->data['inv'] = $purchase;
            $this->data['payment_ref'] = ''; 
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'purchases/add_multi_payment', $this->data);
        }
    }
	
    public function add_payment($id = null, $ex_id = null)
    {
        $this->cus->checkPermissions('payments', true);
		$this->cus->checkPermissions('add', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if($id){
			$this->data['type'] = "purchase";
			$purchase = $this->purchases_model->getPurchaseByID($id);
			if($purchase->status=='freight' || $purchase->status=='draft'){
				if($purchase->grand_total==$purchase->paid){
					$this->session->set_flashdata('error', lang("purchase_already_paid"));
					$this->cus->md();
				}
			}else{
				if ((($purchase->grand_total+$purchase->return_paid)-($purchase->paid+$purchase->return_total)) <= 0) {
					$this->session->set_flashdata('error', lang("purchase_already_paid"));
					$this->cus->md();
				}
			}
			
			if($purchase->return_purchase_ref){
				$narrative = 'Purchase Payment '.$purchase->return_purchase_ref;
			}else{
				$narrative = 'Purchase Payment '.$purchase->reference_no;
			}
			if($purchase->purchase_id && $purchase->status != "draft" && $purchase->status != "freight"){
				$payment_type="returned";
			}else{
				$payment_type="sent";
			}
			
		}else{
			$this->data['type'] = "expense";
			$purchase = $this->purchases_model->getExpenseByID($ex_id);
			if ($purchase->payment_status == 'paid' && $purchase->grand_total == $purchase->paid) {
				$this->session->set_flashdata('error', lang("expense_already_paid"));
				$this->cus->md();
			}
			$narrative = 'Expense Payment '.$purchase->reference;
			$payment_type="expense";
		}
        
        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
                if ( ! $this->site->check_customer_deposit($purchase->supplier_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_sup_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
			
			if ($this->Owner || $this->Admin || $this->cus->GP['purchases-date'] ) {
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
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$purchase->biller_id);
			$paymentAcc = $this->site->getAccountSettingByBiller($purchase->biller_id);
			if($this->input->post('paid_by')=='deposit'){
				$paying_from = $paymentAcc->supplier_deposit_acc;
			}else{
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_from = $cash_account->account_code;
			}
			$payment = array(
                'date' => $date,
				'purchase_id' => $this->input->post('purchase_id'),
				'expense_id' => $this->input->post('expense_id'),
                'reference_no' => $reference_no,
                'amount' => $this->input->post('amount-paid'),
				'discount' => $this->input->post('discount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->cus->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
                'type' => $payment_type,
				'account_code' => $paying_from,
				'currencies' => json_encode($currencies),
            );
			
			//=====accountig=====//
					if($this->Settings->accounting == 1){
						$accTranPayments[] = array(
								'transaction' => 'Payment',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => (($this->Settings->default_payable_account==0 || !$purchase->ap_account) ? $paymentAcc->ap_acc : $purchase->ap_account),
								'amount' => ($this->input->post('amount-paid')+$this->input->post('discount')),
								'narrative' => $narrative,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase->biller_id,
								'project_id' => $purchase->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase->supplier_id,
							);
						$accTranPayments[] = array(
								'transaction' => 'Payment',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $paying_from,
								'amount' => $this->input->post('amount-paid') * (-1),
								'narrative' => $narrative,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase->biller_id,
								'project_id' => $purchase->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase->supplier_id,
							);
						if($this->input->post('discount')>0){
							$accTranPayments[] = array(
								'transaction' => 'Payment',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $paymentAcc->purchase_discount_acc,
								'amount' => $this->input->post('discount') * (-1),
								'narrative' => 'Purchase Payment Discount '.$purchase->reference_no,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase->biller_id,
								'project_id' => $purchase->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase->supplier_id,
							);
						}	
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

        if ($this->form_validation->run() == true && $this->purchases_model->addPayment($payment,$accTranPayments,$purchase->supplier_id)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $purchase;
            $this->data['payment_ref'] = '';
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'purchases/add_payment', $this->data);
        }
    }
	
	public function edit_payment($id = null)
    {
		$this->cus->checkPermissions('payments', true);
        $this->cus->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$payment_info = $this->purchases_model->getPaymentByID($id);
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			if($payment_info->purchase_id){
				$purchase = $this->purchases_model->getPurchaseByID($payment_info->purchase_id);
				if($purchase->return_purchase_ref){
					$narrative = 'Purchase Payment '.$purchase->return_purchase_ref;
				}else{
					$narrative = 'Purchase Payment '.$purchase->reference_no;
				}
			}else{
				$purchase = $this->purchases_model->getExpenseByID($payment_info->expense_id);
				$narrative = 'Expense Payment '.$purchase->reference;
			}
			
			if ($this->input->post('paid_by') == 'deposit') {
                $amount = $this->input->post('amount-paid')- $payment_info->amount;
                if (!$this->site->check_customer_deposit($purchase->supplier_id, $amount)) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_sup_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
			
			if ($this->Owner || $this->Admin || $this->cus->GP['purchases-date'] ) {
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

			$paymentAcc = $this->site->getAccountSettingByBiller($purchase->biller_id);
			if($this->input->post('paid_by')=='deposit'){
				$paying_from = $paymentAcc->supplier_deposit_acc;
			}else{
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_from = $cash_account->account_code;
			}
			
			
            $payment = array(
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
				'expense_id' => $this->input->post('expense_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
				'discount' => $this->input->post('discount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->cus->clear_tags($this->input->post('note')),
				'currencies' => json_encode($currencies),
				'account_code' => $paying_from,
				'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
            );
			
				//=====accountig=====//
					if($this->Settings->accounting == 1){
						if($payment_info->transaction=="PO Deposit"){
							$paying_from = $paymentAcc->supplier_deposit_acc;
						}
						$accTranPayments[] = array(
								'transaction' => 'Payment',
								'transaction_id' => $id,
								'transaction_date' => $date,
								'reference' => $this->input->post('reference_no'),
								'account' => (($this->Settings->default_payable_account==0 || !$purchase->ap_account) ? $paymentAcc->ap_acc : $purchase->ap_account),
								'amount' => ($this->input->post('amount-paid')+$this->input->post('discount')),
								'narrative' => $narrative,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase->biller_id,
								'project_id' => $purchase->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase->supplier_id,
							);
						$accTranPayments[] = array(
								'transaction' => 'Payment',
								'transaction_id' => $id,
								'transaction_date' => $date,
								'reference' => $this->input->post('reference_no'),
								'account' => $paying_from,
								'amount' => $this->input->post('amount-paid') * (-1),
								'narrative' => $narrative,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase->biller_id,
								'project_id' => $purchase->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase->supplier_id,
							);
						if($this->input->post('discount')>0){
							$accTranPayments[] = array(
								'transaction' => 'Payment',
								'transaction_id' => $id,
								'transaction_date' => $date,
								'reference' => $this->input->post('reference_no'),
								'account' => $paymentAcc->purchase_discount_acc,
								'amount' => $this->input->post('discount') * (-1),
								'narrative' => 'Purchase Payment Discount '.$purchase->reference_no,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase->biller_id,
								'project_id' => $purchase->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase->supplier_id,
							);
						}		
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

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updatePayment($id, $payment, $accTranPayments, $purchase->supplier_id)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $this->purchases_model->getPaymentByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'purchases/edit_payment', $this->data);
        }
    }
	
	
	public function add_payment_return($id = null)
    {
        $this->cus->checkPermissions('payments', true);
		$this->cus->checkPermissions('add', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if($id){
			$this->data['type'] = "purchase";
			$purchase = $this->purchases_model->getPurchaseReturnByID($id);
			if($this->cus->formatDecimalRaw($purchase->pur_total-$purchase->pur_paid-abs($purchase->grand_total)) >= 0){
				$purchase->grand_total = 0;
			}else{
				$purchase->grand_total = (abs($this->cus->formatDecimalRaw($purchase->pur_total-$purchase->pur_paid-abs($purchase->grand_total))))-abs($purchase->paid);
			}
			if ($purchase->grand_total == 0) {
				$this->session->set_flashdata('error', lang("purchase_already_paid"));
				$this->cus->md();
			}else{
				$purchase->grand_total = $purchase->grand_total + $purchase->paid;
			}

			$narrative = 'Purchase Payment Return '.$purchase->return_purchase_ref;
			$payment_type="returned";
			
		}else{
			$this->session->set_flashdata('error', lang("purchase_not_found"));
			$this->cus->md();
		}
        
        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->cus->GP['purchases-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			
			$amount_paid = $this->input->post('amount-paid') * (-1);
			
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
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$purchase->biller_id);
            $cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_to = $cash_account->account_code;
			$payment = array(
                'date' => $date,
				'purchase_id' => $this->input->post('purchase_id'),
				'expense_id' => $this->input->post('expense_id'),
                'reference_no' => $reference_no,
                'amount' => $amount_paid,
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->cus->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
                'type' => $payment_type,
				'account_code' => $paying_to,
				'currencies' => json_encode($currencies),
            );
			
			//=====accountig=====//
					if($this->Settings->accounting == 1){
						$paymentAcc = $this->site->getAccountSettingByBiller($purchase->biller_id);
						$accTranPayments[] = array(
								'transaction' => 'Payment',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => (($this->Settings->default_payable_account==0 || !$purchase->ap_account) ? $paymentAcc->ap_acc : $purchase->ap_account),
								'amount' => $amount_paid,
								'narrative' => $narrative,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase->biller_id,
								'project_id' => $purchase->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase->supplier_id,
							);
						$accTranPayments[] = array(
								'transaction' => 'Payment',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $paying_to,
								'amount' => $amount_paid * (-1),
								'narrative' => $narrative,
								'description' => $this->input->post('note'),
								'biller_id' => $purchase->biller_id,
								'project_id' => $purchase->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'supplier_id' => $purchase->supplier_id,
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

        if ($this->form_validation->run() == true && $this->purchases_model->addPayment($payment,$accTranPayments)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $purchase;
            $this->data['payment_ref'] = '';
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'purchases/add_payment_return', $this->data);
        }
    }
	
	public function edit_payment_return($id = null)
    {
		$this->cus->checkPermissions('payments', true);
        $this->cus->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$payment_info = $this->purchases_model->getPaymentByID($id);
		if($id){
			$purchase = $this->purchases_model->getPurchaseReturnByID($payment_info->purchase_id);
			$this->data['type'] = "purchase";
			$narrative = 'Purchase Payment Return '.$purchase->return_purchase_ref;
			$payment_type="returned";
			
		}else{
			$this->session->set_flashdata('error', lang("purchase_not_found"));
			$this->cus->md();
		}
        
        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->cus->GP['purchases-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			
			$amount_paid = $this->input->post('amount-paid') * (-1);
			
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
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$purchase->biller_id);
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_to = $cash_account->account_code;
            $payment = array(
                'date' => $date,
				'purchase_id' => $this->input->post('purchase_id'),
				'expense_id' => $this->input->post('expense_id'),
                'reference_no' => $reference_no,
                'amount' => $amount_paid,
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->cus->clear_tags($this->input->post('note')),
				'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
                'type' => $payment_type,
				'account_code' => $paying_to,
				'currencies' => json_encode($currencies),
            );
			
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$paymentAcc = $this->site->getAccountSettingByBiller($purchase->biller_id);
					$accTranPayments[] = array(
							'transaction' => 'Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => (($this->Settings->default_payable_account==0 || !$purchase->ap_account) ? $paymentAcc->ap_acc : $purchase->ap_account),
							'amount' => $amount_paid,
							'narrative' => $narrative,
							'description' => $this->input->post('note'),
							'biller_id' => $purchase->biller_id,
							'project_id' => $purchase->project_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $purchase->supplier_id,
						);
					$accTranPayments[] = array(
							'transaction' => 'Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paying_to,
							'amount' => $amount_paid * (-1),
							'narrative' => $narrative,
							'description' => $this->input->post('note'),
							'biller_id' => $purchase->biller_id,
							'project_id' => $purchase->project_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $purchase->supplier_id,
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

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updatePayment($id, $payment, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment_info;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'purchases/edit_payment_return', $this->data);
        }
    }
	
    public function delete_payment($id = null)
    {
		$this->cus->checkPermissions('payments', true);
        $this->cus->checkPermissions('delete', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->purchases_model->deletePayment($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }


    public function getExpenses($warehouse_id = NULL, $biller_id = NULL)
    {
        $this->cus->checkPermissions('expenses');
		$payments_link = anchor('expenses/payments/0/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), ' class="expense_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$add_payment_link = anchor('expenses/add_payment/0/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'class="expense_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $detail_link = anchor('expenses/expense_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('expense_note'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal2"');
        $edit_link = anchor('expenses/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense'), ' class="edit_expense" ');
        $delete_link = "<a href='#' class='po delete_expense' title='<b>" . $this->lang->line("delete_expense") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('expenses/delete_expense/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_expense') . "</a>";
		$approve_link = "";
		$unapprove_link = "";
		if($this->Settings->approval_expense==1 && ($this->Admin || $this->Owner || $this->GP['expenses-approve_expense'])){
			$approve_link = "<a href='#' class='po approve_expense' title='" . $this->lang->line("approve_expense") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . site_url('expenses/approve_expense/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_expense') . "</a>";
			$unapprove_link = "<a href='#' class='po unapprove_expense' title='" . $this->lang->line("unapprove_expense") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . site_url('expenses/unapprove_expense/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('unapprove_expense') . "</a>";
		}
		
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
			<li>' . $approve_link . '</li>
			<li>' . $unapprove_link . '</li>
			<li>' . $payments_link . '</li>
			<li>' . $add_payment_link . '</li>
			<li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select($this->db->dbprefix('expenses') . ".id as id, date, reference, projects.name, supplier,  grand_total, IFNULL(paid,0), (grand_total- IFNULL(paid,0)) as balance, status, payment_status, attachment", false)
            ->from('expenses')
            ->join('users', 'users.id=expenses.created_by', 'left')
			->join('projects', 'projects.id=expenses.project_id', 'left')
            ->group_by('expenses.id');
		if ($biller_id) {
            $this->datatables->where('expenses.biller_id', $biller_id);
        }
		if ($warehouse_id) {
            $this->datatables->where('expenses.warehouse_id', $warehouse_id);
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('expenses.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('expenses.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('expenses.created_by', $this->session->userdata('user_id'));
        }
        //$this->datatables->edit_column("attachment", $attachment_link, "attachment");
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }


        public function expense_note($id = null)
    {
        $this->cus->checkPermissions('expenses');
        $expense = $this->purchases_model->getExpenseByID($id);
        $this->data['items'] = $this->purchases_model->getExpenseItems($id);
        $this->data['created_by'] = $this->site->getUser($expense->created_by);
        $this->data['supplier'] = $this->site->getCompanyByID($expense->supplier_id);
        $this->data['requester'] = $this->site->getUser($expense->request_id);
        $this->data['approver'] = $this->site->getUser($expense->approved_by);
        $this->data['warehouse'] = $expense->warehouse_id ? $this->site->getWarehouseByID($expense->warehouse_id) : NULL;
        $this->data['biller'] = $this->site->getCompanyByID($expense->biller_id);
        $this->data['expense'] = $expense;
        $this->data['page_title'] = $this->lang->line("expense_note");
        if($this->Owner || $this->Admin || $this->cas->GP['unlimited-print']){
            $this->data['print'] = 0;
        }else{
            if($this->Settings->limit_print=='1' && $this->site->checkPrint('Expense',$expense->id)){
                $this->data['print'] = 1;
            }else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Expense',$expense->id)){
                $this->data['print'] = 2;
            }else{
                $this->data['print'] = 0;
            }
        }
        
        $this->load->view($this->theme . 'expenses/expense_note', $this->data);
    }
	
	public function add(){
		$this->cus->checkPermissions('expenses-add', true);
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		if($this->Settings->payment_expense == 1){
			$this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
		}
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$project_id = $this->input->post('project');
			$supplier_id = $this->input->post('supplier');
			$supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
			if ($this->Owner || $this->Admin || $this->cus->GP['purchases-expenses-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$accTrans = false;
			$payment = false;
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ex',$biller_id);
			$payment_status = 'pending';
			$payment_amount = 0;
			$total = 0;
			$grand_total = 0;
			$order_tax = 0;
			$order_discount = 0;
			$percentage = '%';
			$status = ($this->Settings->approval_expense==1 ? "pending" : "approved");
			$i = isset($_POST['expense_id']) ? sizeof($_POST['expense_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$subtotal = 0;
				$id = $_POST['expense_id'][$r];
				$code = $_POST['expense_code'][$r];
				$name = $_POST['expense_name'][$r];
				$desc = $_POST['description'][$r];
				$unit_cost = $_POST['unit_cost'][$r];
				$quantity = $_POST['quantity'][$r];
				if (isset($id) && isset($unit_cost) && isset($quantity)) {
					$subtotal = $unit_cost * $quantity;
					$total += $subtotal;
					$items[] = array(
                        'category_id' => $id,
                        'category_code' => $code,
                        'category_name' => $name,
                        'description' => $desc,
                        'unit_cost' => $unit_cost,
						'quantity' => $quantity,
						'subtotal' => $subtotal
                    );
					if($this->Settings->accounting == 1 && $this->Settings->approval_expense==0){
						$expenseCategory = $this->purchases_model->getExpenseCategoryByID($id);
						$accTrans[] = array(
							'transaction' => 'Expense',
							'transaction_date' => $date,
							'reference' => $reference,
							'account' =>  $expenseCategory->expense_account,
							'amount' => $subtotal,
							'narrative' => 'Expense '.$supplier,
							'description' => $desc,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $supplier_id,
						);
					}
				}
			}
			
			if (empty($items)) {
				$this->form_validation->set_rules('expense', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			
			if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->cus->formatDecimalRaw(((($total) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->cus->formatDecimalRaw($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
			
			if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total-$order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
			$grand_total = $total + $order_tax - $order_discount;
			
			$expenseAcc = $this->site->getAccountSettingByBiller($biller_id);
			if($this->Settings->payment_expense == 0  && $this->Settings->approval_expense==0){
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_from = $cash_account->account_code;
				$payment = array(
							'date' => $date,
							'reference_no' => $reference,
							'amount' => $grand_total,
							'paid_by' => $this->input->post('paid_by'),
							'note' => $this->input->post('note', true),
							'created_by' => $this->session->userdata('user_id'),
							'type' => 'expense',
							'currencies' => json_encode($currencies),
							'account_code' => $paying_from,
						);
				$payment_status = 'paid';
				$payment_amount = $grand_total;				
			}
			
			$data = array(
                'date' => $date,
                'reference' => $reference,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'project_id' => $project_id,
				'supplier_id' => $supplier_id,
				'supplier' => $supplier,
				'account_from' => $paying_from,
				'amount' => $total,
				'grand_total' => $grand_total,
				'paid' => $payment_amount,
				'payment_status' => $payment_status,
				'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
                'note' => $this->input->post('note', true),
                'warehouse_id' => $this->input->post('warehouse', true),
                'ap_account' => ($this->Settings->default_payable_account == 0 ? $expenseAcc->ap_acc : $this->input->post('payable_account')),
				'order_tax' => $order_tax,
				'order_tax_id' => $order_tax_id,
				'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
				'paid_by' => $this->input->post('paid_by'),
            );
			
			if($this->config->item("vehicle")){
				$data['vehicle_id'] = $this->input->post("vehicle");
			}
			if($this->config->item("room_rent")){
				$data['table_id'] = $this->input->post("room");
			}
			
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
			
			
			
			if($this->Settings->accounting == 1 && $this->Settings->approval_expense==0){           
                if($this->Settings->payment_expense == 0){
                    $accTrans[] = array(
                        'transaction' => 'Expense',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => $paying_from,
                        'amount' => -$grand_total,
                        'narrative' => 'Expense Payment',
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
                }else{
                    $accTrans[] = array(
                        'transaction' => 'Expense',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => ($this->Settings->default_payable_account == 0 ? $expenseAcc->ap_acc : $this->input->post('payable_account')),
                        'amount' => -$grand_total,
                        'narrative' => 'Expense '.$supplier,
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
                }
				
				if($order_discount > 0){
                    $accTrans[] = array(
                        'transaction' => 'Expense',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => $expenseAcc->purchase_discount_acc,
                        'amount' => -$order_discount,
                        'narrative' => 'Order Discount '.$supplier,
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
                }
				
				if($order_tax > 0){
					$accTrans[] = array(
                        'transaction' => 'Expense',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => $expenseAcc->vat_input,
                        'amount' => $order_tax,
                        'narrative' => 'Tax Expense '.$supplier,
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
				}
				
            }

        }
		

		if ($this->form_validation->run() == true && $this->purchases_model->addExpense($data,$items, $accTrans,$payment)) {	
            $this->session->set_userdata('remove_expls', 1);
            $this->session->set_flashdata('message', $this->lang->line("expense_added") ." ". $reference);          
			if($this->config->item("send_telegram")){
				$this->telegrambot->sendmsg("Expense ".$data["reference"]." (".$this->cus->formatMoney($data["grand_total"]).") has been added by ".$this->session->userdata("username"));
			}
			if($this->input->post('add_expense_next')){
				redirect('expenses/add');
			}else{
				redirect('expenses');
			}
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['suppliers'] = $this->site->getAllCompanies('supplier');
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['categories'] = $this->purchases_model->getExpenseCategories();
			$this->data['billers'] = $this->site->getBillers();
			$this->data['tax_rates'] = $this->site->getAllTaxRates();
			if($this->config->item("vehicle")){
				$this->data['vehicles'] = $this->site->getAllVehicles();
			}
			if($this->config->item("room_rent")){
				$this->data['rooms'] = $this->purchases_model->getAllRooms();
			} 
			if($this->Settings->project == 1){
				$this->data['projects'] = $this->site->getAllProjects();
			}
			$this->data['payable_account'] = false;
			if($this->Settings->payment_expense == 0){
				$this->data['cash_account'] = true;
			}else if($this->Settings->default_payable_account != 0){
				$this->data['payable_account'] = $this->site->getAccount('LI',$this->Settings->default_payable_account);
			}
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases/expenses'), 'page' => lang('expenses')), array('link' => '#', 'page' => lang('add_expense')));
            $meta = array('page_title' => lang('add_expense'), 'bc' => $bc);
            $this->core_page('expenses/add', $meta, $this->data);
        }
	}
	
	public function edit($id = null){
		$this->cus->checkPermissions('expenses-edit', true);
		if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		if($this->Settings->payment_expense == 1){
			$this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
		}
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$project_id = $this->input->post('project');
			$supplier_id = $this->input->post('supplier');
			$supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
			if ($this->Owner || $this->Admin || $this->cus->GP['purchases-expenses-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ex',$biller_id);
			$payment_status = 'pending';
			$payment_amount = 0;
			$total = 0;
			$grand_total = 0;
			$order_tax = 0;
			$order_discount = 0;
			$percentage = '%';
			$payment = false;
			$accTrans = false;
			$i = isset($_POST['expense_id']) ? sizeof($_POST['expense_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$subtotal = 0;
				$cat_id = $_POST['expense_id'][$r];
				$code = $_POST['expense_code'][$r];
				$name = $_POST['expense_name'][$r];
				$desc = $_POST['description'][$r];
				$unit_cost = $_POST['unit_cost'][$r];
				$quantity = $_POST['quantity'][$r];
				if (isset($id) && isset($unit_cost) && isset($quantity)) {
					$subtotal = $unit_cost * $quantity;
					$total += $subtotal;
					$items[] = array(
						'expense_id' => $id,
                        'category_id' => $cat_id,
                        'category_code' => $code,
                        'category_name' => $name,
                        'description' => $desc,
                        'unit_cost' => $unit_cost,
						'quantity' => $quantity,
						'subtotal' => $subtotal
                    );
					if($this->Settings->accounting == 1 && $this->Settings->approval_expense==0){
						$expenseCategory = $this->purchases_model->getExpenseCategoryByID($cat_id);
						$accTrans[] = array(
							'transaction_id' => $id,
							'transaction' => 'Expense',
							'transaction_date' => $date,
							'reference' => $reference,
							'account' =>  $expenseCategory->expense_account,
							'amount' => $subtotal,
							'narrative' => 'Expense '.$supplier,
							'description' => $desc,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $supplier_id,
						);
					}
				}
			}
			
			if (empty($items)) {
				$this->form_validation->set_rules('expense', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			
			if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->cus->formatDecimalRaw(((($total) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->cus->formatDecimalRaw($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
			
			if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total-$order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
			$grand_total = $total + $order_tax - $order_discount;
			
			$expenseAcc = $this->site->getAccountSettingByBiller($biller_id);
			$data = array(
                'date' => $date,
                'reference' => $reference,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'project_id' => $project_id,
				'supplier_id' => $supplier_id,
				'supplier' => $supplier,
				'account_from' => $this->input->post('paying_from'),
				'amount' => $total,
				'grand_total' => $grand_total,
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date("Y-m-d H:i"),
                'note' => $this->input->post('note', true),
                'warehouse_id' => $this->input->post('warehouse', true),
                'ap_account' => ($this->Settings->default_payable_account == 0 ? $expenseAcc->ap_acc : $this->input->post('payable_account')),
				'order_tax' => $order_tax,
				'order_tax_id' => $order_tax_id,
				'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
				'paid_by' => $this->input->post('paid_by'),
            );
			if($this->Settings->payment_expense == 0 && $this->Settings->approval_expense==0){
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_from = $cash_account->account_code;
				$payment = array(
							'expense_id' => $id,
							'date' => $date,
							'reference_no' => $reference,
							'amount' => $grand_total,
							'paid_by' => $this->input->post('paid_by'),
							'note' => $this->input->post('note', true),
							'updated_by' => $this->session->userdata('user_id'),
							'updated_at' => date("Y-m-d H:i"),
							'type' => 'expense',
							'currencies' => json_encode($currencies),
							'account_code' => $paying_from,
						);
				
				$payment_status = 'paid';
				$data['payment_status']	= $payment_status;
				$data['paid']	= $grand_total;				
			}
			
			if($this->config->item("vehicle")){
				$data['vehicle_id'] = $this->input->post("vehicle");
			}
			if($this->config->item("room_rent")){
				$data['table_id'] = $this->input->post("room");
			}
			
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
			
			
			
			if($this->Settings->accounting == 1 && $this->Settings->approval_expense==0){           
                if($this->Settings->payment_expense == 0){
                    $accTrans[] = array(
						'transaction_id' => $id,
                        'transaction' => 'Expense',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => $paying_from,
                        'amount' => -$grand_total,
                        'narrative' => 'Expense Payment',
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
                }else{
                    $accTrans[] = array(
						'transaction_id' => $id,
                        'transaction' => 'Expense',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => ($this->Settings->default_payable_account == 0 ? $expenseAcc->ap_acc : $this->input->post('payable_account')),
                        'amount' => -$grand_total,
                        'narrative' => 'Expense '.$supplier,
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
                }
				
				if($order_discount > 0){
                    $accTrans[] = array(
						'transaction_id' => $id,
                        'transaction' => 'Expense',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => $expenseAcc->purchase_discount_acc,
                        'amount' => -$order_discount,
                        'narrative' => 'Order Discount '.$supplier,
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
                }
				
				if($order_tax > 0){
					$accTrans[] = array(
						'transaction_id' => $id,
                        'transaction' => 'Expense',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => $expenseAcc->vat_input,
                        'amount' => $order_tax,
                        'narrative' => 'Tax Expense '.$supplier,
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
				}
				
            }
        }
		
		
		if ($this->form_validation->run() == true && $this->purchases_model->updateExpense($id,$data,$items,$accTrans,$payment)) {	
            $this->session->set_userdata('remove_expls', 1);
            $this->session->set_flashdata('message', $this->lang->line("expense_updated") ." ". $reference);    
			if($this->config->item("send_telegram")){
				$this->telegrambot->sendmsg("Expense ".$data["reference"]." (".$this->cus->formatMoney($data["grand_total"]).") has been edited by ".$this->session->userdata("username"));
			}			
			redirect('expenses');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $expenses = $this->purchases_model->getExpenseByID($id);
			$expense_items = $this->purchases_model->getExpenseItems($id);
			krsort($expense_items);
            $c = rand(100000, 9999999);
			foreach ($expense_items as $expense_item) {
				$row = json_decode('{}');
				$row->id = $expense_item->category_id;
				$row->code = $expense_item->category_code;
				$row->name = $expense_item->category_name;
				$row->description = $expense_item->description;
				$row->unit_cost = $expense_item->unit_cost;
				$row->quantity = $expense_item->quantity;
				$ri = $c;
				$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
                $c++;
			}
			$this->session->set_userdata('remove_expls', 1);
			$this->data['expenses'] = $expenses;
			$this->data['expense_items'] = json_encode($pr);
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['suppliers'] = $this->site->getAllCompanies('supplier');
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['categories'] = $this->purchases_model->getExpenseCategories();
			$this->data['billers'] = $this->site->getBillers();
			$this->data['tax_rates'] = $this->site->getAllTaxRates();
			
			
			if($this->config->item("vehicle")){
				$this->data['vehicles'] = $this->site->getAllVehicles();
			}
			if($this->config->item("room_rent")){
				$this->data['rooms'] = $this->purchases_model->getAllRooms();
			}
			if($this->Settings->project == 1){
				$this->data['projects'] = $this->site->getAllProjects();
			}
			$this->data['payable_account'] = false;
			if($this->Settings->payment_expense == 0){
				$this->data['cash_account'] = true;
			}else if($this->Settings->default_payable_account != 0){
				$this->data['payable_account'] = $this->site->getAccount('LI',$this->Settings->default_payable_account);
			}
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases/expenses'), 'page' => lang('expenses')), array('link' => '#', 'page' => lang('edit_expense')));
            $meta = array('page_title' => lang('edit_expense'), 'bc' => $bc);
            $this->core_page('expenses/edit', $meta, $this->data);
        }
	}

	public function approve_expense($id = null)
    {
		$this->cus->checkPermissions('approve_expense', true);
		$expense = $this->purchases_model->getExpenseByID($id);
		$expense_items = $this->purchases_model->getExpenseItems($id);
		$payment = false;
		$accTrans = false;
		if($expense && $expense_items){
			$data = array(
						'status' =>'approved',
						'approved_by' => $this->session->userdata('user_id'),
						'approved_at' => date('Y-m-d H:i:s')
					);
			if($this->Settings->accounting == 1){
				foreach($expense_items as $expense_item){
					$expenseCategory = $this->purchases_model->getExpenseCategoryByID($expense_item->category_id);
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Expense',
						'transaction_date' => $expense->date,
                        'reference' => $expense->reference,
						'account' =>  $expenseCategory->expense_account,
						'amount' => $expense_item->subtotal,
						'narrative' => 'Expense '.$expense->supplier,
						'description' => $expense_item->description,
						'biller_id' => $expense->biller_id,
                        'project_id' => $expense->project_id,
                        'user_id' => $expense->created_by,
                        'supplier_id' => $expense->supplier_id,
					);
				}
				$expenseAcc = $this->site->getAccountSettingByBiller($expense->biller_id);
                if($this->Settings->payment_expense == 0){
					if($this->Settings->payment_expense == 0  && $this->Settings->approval_expense==0){
						$payment = array(
									'date' => $date,
									'reference_no' => $expense->reference,
									'amount' => $expense->grand_total,
									'paid_by' => 'cash',
									'note' => $expense->note,
									'created_by' => $expense->created_by,
									'type' => 'expense',
									'account_code' => $expense->account_from,
								);
						$data["payment_status"]	= "paid";
						$data["payment_amount"]	= $expense->grand_total;
					}
					$accTrans[] = array(
						'transaction_id' => $id,
                        'transaction' => 'Expense',
                        'transaction_date' => $expense->date,
                        'reference' => $expense->reference,
                        'account' => $expense->account_from,
                        'amount' => $expense->grand_total * (-1),
                        'narrative' => 'Expense Payment',
                        'description' => $expense->note,
                        'biller_id' => $expense->biller_id,
                        'project_id' => $expense->project_id,
                        'user_id' => $expense->created_by,
                        'supplier_id' => $expense->supplier_id,
                    );
                }else{
					$accTrans[] = array(
						'transaction_id' => $id,
                        'transaction' => 'Expense',
                        'transaction_date' => $expense->date,
                        'reference' => $expense->reference,
						'account' => ($this->Settings->default_payable_account == 0 ? $expenseAcc->ap_acc : $expense->ap_account),
                        'amount' => $expense->grand_total * (-1),
                        'narrative' => 'Expense '.$expense->supplier,
                        'description' => $expense->note,
                        'biller_id' => $expense->biller_id,
                        'project_id' => $expense->project_id,
                        'user_id' => $expense->created_by,
                        'supplier_id' => $expense->supplier_id,
                    );
                }

				if($expense->order_discount > 0){
					$accTrans[] = array(
						'transaction_id' => $id,
                        'transaction' => 'Expense',
                        'transaction_date' => $expense->date,
                        'reference' => $expense->reference,
						'account' => $expenseAcc->purchase_discount_acc,
                        'amount' => -$expense->order_discount,
                        'narrative' => 'Order Discount '.$expense->supplier,
                        'description' => $expense->note,
                        'biller_id' => $expense->biller_id,
                        'project_id' => $expense->project_id,
                        'user_id' => $expense->created_by,
                        'supplier_id' => $expense->supplier_id,
                    );
				}
				
				if($expense->order_tax > 0){
					$accTrans[] = array(
						'transaction_id' => $id,
                        'transaction' => 'Expense',
                        'transaction_date' => $expense->date,
                        'reference' => $expense->reference,
						'account' => $expenseAcc->vat_input,
                        'amount' => $expense->order_tax,
                        'narrative' => 'Tax Expense '.$expense->supplier,
                        'description' => $expense->note,
                        'biller_id' => $expense->biller_id,
                        'project_id' => $expense->project_id,
                        'user_id' => $expense->created_by,
                        'supplier_id' => $expense->supplier_id,
                    );
				}
				
            }	
			if ($this->purchases_model->approveExpense($id, $data, $accTrans, $payment)) {
				if ($this->input->is_ajax_request()) {
					echo lang("expense_approved");die();
				}else{
					$this->session->set_flashdata('error', lang("expense_cannot_approved"));
					die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
				}
			}				
		}else{
			$this->session->set_flashdata('error', lang("expense_cannot_approved"));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
		}
    }
	
	public function unapprove_expense($id = null){
		if ($this->purchases_model->unapproveExpense($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("expense_unapproved");die();
			}else{
				$this->session->set_flashdata('error', lang("expense_cannot_unapproved"));
				die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
			}
		}
	}

    public function delete_expense($id = null)
    {
        $this->cus->checkPermissions('expenses-delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $expense = $this->purchases_model->getExpenseByID($id);
        if ($this->purchases_model->deleteExpense($id)) {
            if ($expense->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
            echo lang("expense_deleted");
        }
    }

    public function expense_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('purchases-expenses-delete');
                    foreach ($_POST['val'] as $id) {
                        $this->purchases_model->deleteExpense($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("expenses_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('expenses'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('project'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
					if($this->Settings->accounting==1){
						$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
						$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
						$this->excel->getActiveSheet()->SetCellValue('I1', lang('note'));
						$this->excel->getActiveSheet()->SetCellValue('J1', lang('created_by'));						
					}else{
						$this->excel->getActiveSheet()->SetCellValue('G1', lang('note'));
						$this->excel->getActiveSheet()->SetCellValue('H1', lang('created_by'));
					}
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $expense = $this->purchases_model->getExpenseByID($id);
						$project = $this->site->getProjectByID($expense->project_id);
                        $user = $this->site->getUser($expense->created_by);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($expense->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $expense->reference);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $expense->biller);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $project->name);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $expense->supplier);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->formatMoney($expense->grand_total));
						if($this->Settings->accounting==1){
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->formatMoney($expense->paid));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->formatMoney($expense->grand_total- $expense->paid));
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->cus->remove_tag($expense->note));
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, $user->last_name . ' ' . $user->first_name);
						}else{
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->remove_tag($expense->note));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $user->last_name . ' ' . $user->first_name);
						}
						
                        
						$row++;                                       
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					if($this->Settings->accounting==1){
						$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
						$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
						$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
						$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
					}else{
						$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
						$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
					}
					
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'expenses_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_expense_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }


    public function view_return($id = null)
    {
        $this->cus->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->purchases_model->getAllReturnItems($id);
        $this->data['purchase'] = $this->purchases_model->getPurchaseByID($inv->purchase_id);
        $this->load->view($this->theme.'purchases/view_return', $this->data);
    }

    public function return_purchase($id = null)
    {
        $this->cus->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $purchase = $this->purchases_model->getPurchaseByID($id);
        $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');

        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep',$purchase->biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['purchases-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_details = $this->site->getCompanyByID($purchase->biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $note = $this->cus->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            for ($r = 0; $r < $i; $r++) {
				
                $item_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
                $purchase_item_id = $_POST['purchase_item_id'][$r];
                $item_option = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_cost = $this->cus->formatDecimalRaw($_POST['real_unit_cost'][$r]);
                $unit_cost = $this->cus->formatDecimalRaw($_POST['unit_cost'][$r]);
                $item_unit_quantity = (0-$_POST['quantity'][$r]);
                $item_expiry = isset($_POST['expiry'][$r]) ? $this->cus->fsd($_POST['expiry'][$r]) : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = (0-$_POST['product_base_quantity'][$r]);
				$serial_no = $_POST['serial_no'][$r];
                if (abs($item_quantity) > 0 && isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);

                    $item_type = $product_details->type;
                    $item_name = $product_details->name;

                    // $unit_cost = $this->cus->formatDecimalRaw($unit_cost - $pr_discount);

					$pr_item_discount = isset($_POST['item_discount'][$r]) ? $_POST['item_discount'][$r] : 0;
					$pr_discount = $pr_item_discount;
                    $product_discount += $pr_item_discount;

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if (!$product_details->tax_method) {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            }

                        } elseif ($tax_details->type == 2) {

                            $item_tax = $this->cus->formatDecimalRaw($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->cus->formatDecimalRaw($item_tax * $item_unit_quantity, 4);

                    } else {
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $tax = "";
                    }

                    $item_net_cost = $product_details->tax_method ? $this->cus->formatDecimalRaw(($unit_cost - $pr_discount), 4) : $this->cus->formatDecimalRaw(($unit_cost - $item_tax - $pr_discount), 4);
                    $product_tax += $pr_item_tax;
                    $subtotal = $this->cus->formatDecimalRaw((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getProductUnit($item_id,$item_unit);
					
					if($this->Settings->accounting_method == '0'){
						$costs = $this->site->getFifoCost($item_id,abs($item_quantity),$stockmoves);
					}else if($this->Settings->accounting_method == '1'){
						$costs = $this->site->getLifoCost($item_id,abs($item_quantity),$stockmoves);
					}else if($this->Settings->accounting_method == '3'){
						$costs = $this->site->getProductMethod($item_id,abs($item_quantity),$stockmoves);
					}
					
					if($costs){
						$productAcc = $this->site->getProductAccByProductId($item_id);
						foreach($costs as $cost_item){
							$stockmoves[] = array(
								'purchase_item_id' => $purchase_item_id,
								'transaction' => 'Purchases',
								'product_id' => $item_id,
								'product_code' => $item_code,
								'option_id' => $item_option,
								'quantity' => -($cost_item['quantity']),
								'unit_quantity' => $unit->unit_qty,
								'unit_code' => $unit->code,
								'unit_id' => $item_unit,
								'warehouse_id' => $purchase->warehouse_id,
								'expiry' => $item_expiry,
								'date' => $date,
								'serial_no' => $serial_no,
								'real_unit_cost' => $cost_item['cost'],
								'reference_no' => $reference,
								'user_id' =>  $this->session->userdata('user_id'),
							);
							//=======accounting=========//
								if($this->Settings->accounting == 1){		
									if($cost_item['cost']!=$real_unit_cost){
										$acc_cost_amount = ($cost_item['cost'] - $real_unit_cost) * $cost_item['quantity'] ;
										$accTrans[] = array(
											'transaction' => 'Purchases',
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->stock_acc,
											'amount' => $acc_cost_amount * (-1),
											'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.($cost_item['cost'] - $real_unit_cost),
											'description' => $note,
											'biller_id' => $purchase->biller_id,
											'project_id' => $purchase->project_id,
											'user_id' => $this->session->userdata('user_id'),
											'supplier_id' => $purchase->supplier_id,
										);
										$accTrans[] = array(
											'transaction' => 'Purchases',
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->cost_acc,
											'amount' => $acc_cost_amount,
											'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.($cost_item['cost'] - $real_unit_cost),
											'description' => $note,
											'biller_id' => $purchase->biller_id,
											'project_id' => $purchase->project_id,
											'user_id' => $this->session->userdata('user_id'),
											'supplier_id' => $purchase->supplier_id,
										);
									}
									
									$accTrans[] = array(
										'transaction' => 'Purchases',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->stock_acc,
										'amount' => -($real_unit_cost * $cost_item['quantity']),
										'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$real_unit_cost,
										'description' => $note,
										'biller_id' => $purchase->biller_id,
										'project_id' => $purchase->project_id,
										'user_id' => $this->session->userdata('user_id'),
										'supplier_id' => $purchase->supplier_id,
									);
									
									
									
								}
							//============end accounting=======//
						}
						
					}else{
						$stockmoves[] = array(
							'purchase_item_id' => $purchase_item_id,
							'transaction' => 'Purchases',
							'product_id' => $item_id,
							'product_code' => $item_code,
							'option_id' => $item_option,
							'quantity' => $item_quantity,
							'unit_quantity' => $unit->unit_qty,
							'unit_code' => $unit->code,
							'unit_id' => $item_unit,
							'warehouse_id' => $purchase->warehouse_id,
							'expiry' => $item_expiry,
							'serial_no' => $serial_no,
							'date' => $date,
							'real_unit_cost' => $real_unit_cost,
							'reference_no' => $reference,
							'user_id' =>  $this->session->userdata('user_id'),
						);
						//=======accounting=========//
							if($this->Settings->accounting == 1){		
								$productAcc = $this->site->getProductAccByProductId($item_id);
								$accTrans[] = array(
									'transaction' => 'Purchases',
									'transaction_date' => $date,
									'reference' => $reference,
									'account' => $productAcc->stock_acc,
									'amount' => ($real_unit_cost * $item_quantity),
									'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$real_unit_cost,
									'description' => $note,
									'biller_id' => $purchase->biller_id,
									'project_id' => $purchase->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'supplier_id' => $purchase->supplier_id,
								);
							}
							
							
							
						//============end accounting=======//
					}
					
                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->cus->formatDecimalRaw($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'warehouse_id' => $purchase->warehouse_id,
                        'item_tax' => $pr_item_tax,
						'expiry' => $item_expiry,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->cus->formatDecimalRaw($subtotal),
                        'real_unit_cost' => $real_unit_cost,
                        'purchase_item_id' => $purchase_item_id,
                        'status' => 'received',
						'serial_no' => $serial_no,
						'reference_no' => $reference,
                        'user_id' => $this->session->userdata('user_id'),
                    );
					
					if($pr_item_discount < 0){
						$accTrans[] = array(
							'transaction' => 'Purchases',
							'transaction_date' => $date,
							'reference' => $reference,
							'account' => $productAcc->discount_acc,
							'amount' => $pr_item_discount * (-1),
							'narrative' => 'Purchase Product Discount',
							'description' => $note,
							'biller_id' => $purchase->biller_id,
							'project_id' => $purchase->project_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $purchase->supplier_id,
						);
					}

                    $total += $this->cus->formatDecimalRaw(($item_net_cost * $item_unit_quantity), 4);
                }
            }
			
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = ($this->input->post('discount'))*-1;
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->cus->formatDecimalRaw(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->cus->formatDecimalRaw($order_discount_id, 4);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->cus->formatDecimalRaw($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->cus->formatDecimalRaw(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->cus->formatDecimalRaw(($product_tax + $order_tax), 4);
            $grand_total = $this->cus->formatDecimalRaw(($total + $total_tax + $this->cus->formatDecimalRaw($return_surcharge) - $order_discount), 4);
            
            //=======acounting=========//   
            if($this->Settings->accounting == 1){           
                $purchaseAcc = $this->site->getAccountSettingByBiller($purchase->biller_id);
                $accTrans[] = array(
                    'transaction' => 'Purchases',
                    'transaction_date' => $date,
                    'reference' => $reference,
					'account' => (($this->Settings->default_payable_account==0 || !$purchase->ap_account) ? $purchaseAcc->ap_acc : $purchase->ap_account),
                    'amount' => abs($grand_total),
                    'narrative' => 'Purchases Return',
                    'description' => $note,
                    'biller_id' => $purchase->biller_id,
                    'project_id' => $purchase->project_id,
                    'user_id' => $this->session->userdata('user_id'),
                    'supplier_id' => $purchase->supplier_id,
                );
                
                if(abs($order_discount) > 0){
                    $accTrans[] = array(
                        'transaction' => 'Purchases',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => $purchaseAcc->purchase_discount_acc,
                        'amount' => abs($order_discount),
                        'narrative' => 'Order Discount',
                        'description' => $note,
                        'biller_id' => $purchase->biller_id,
                        'project_id' => $purchase->project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $purchase->supplier_id,
                    );
                }
                if(abs($order_tax) > 0){
                    $accTrans[] = array(
                        'transaction' => 'Purchases',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => $purchaseAcc->vat_input,
                        'amount' => $order_tax,
                        'narrative' => 'Order Tax',
                        'description' => $note,
                        'biller_id' => $purchase->biller_id,
                        'project_id' => $purchase->project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $purchase->supplier_id,
                    );
                }
                if($return_surcharge > 0){
                    $accTrans[] = array(
                        'transaction' => 'Purchases',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => $purchaseAcc->purchase_return_acc,
                        'amount' => $return_surcharge,
                        'narrative' => 'Surcharge Return '.$purchase->reference_no,
                        'description' => $note,
                        'biller_id' => $purchase->biller_id,
                        'project_id' => $purchase->project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $purchase->supplier_id,
                    );
                }
                
            }   
            //============end accounting=======//

            $data = array('date' => $date,
                'purchase_id' => $id,
                'reference_no' => $purchase->reference_no,
                'supplier_id' => $purchase->supplier_id,
                'supplier' => $purchase->supplier,
                'warehouse_id' => $purchase->warehouse_id,
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
                'surcharge' => $this->cus->formatDecimalRaw($return_surcharge),
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'return_purchase_ref' => $reference,
                'status' => 'returned',
                'payment_status' => $purchase->payment_status == 'paid' ? 'due' : 'pending',
				'biller_id' => $purchase->biller_id,
				'biller' => $biller,
				'project_id' => $purchase->project_id,
				'ap_account' => (($this->Settings->default_payable_account==0 || !$purchase->ap_account) ? $purchaseAcc->ap_acc : $purchase->ap_account),
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

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products, $accTrans, $stockmoves, false, $purchase->return_id)) {
            $this->session->set_flashdata('message', lang("return_purchase_added"));
            redirect("purchases/purchase_return");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            
			$this->data['inv'] = $purchase;
			
            if ($this->data['inv']->status != 'received' && $this->data['inv']->status != 'partial') {
                $this->session->set_flashdata('error', lang("purchase_status_x_received"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            
            if ($this->data['inv']->date <= date('Y-m-d', strtotime('-3 months'))) {
                $this->session->set_flashdata('error', lang("purchase_x_edited_older_than_3_months"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
			
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->cus->hrsd($item->expiry) : '');
                $row->base_quantity = $item->return_qty_base;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
				$row->return_unit = $item->return_unit_id ? $item->return_unit_id : $item->product_unit_id;
                $row->qty = $item->return_qty;
				$row->serial_no = $item->serial_no;
                $row->oqty = $item->unit_quantity;
                $row->purchase_item_id = $item->id;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                $row->discount = $item->discount ? $item->discount : '0';
				$row->item_discount = $item->item_discount ? ($item->item_discount / $item->unit_quantity) : 0;
				$options = $this->purchases_model->getProductOptions($row->id);
                $row->option = !empty($item->option_id) ? $item->option_id : '';
                $row->real_unit_cost = $item->real_unit_cost;
				$row->unit_cost = $row->tax_method ? $item->unit_cost + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity) + $this->cus->formatDecimalRaw($item->item_tax / $item->quantity) : $item->unit_cost + ($item->item_discount / $item->quantity);
                $row->cost = $this->cus->formatDecimalRaw($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate = $item->tax_rate_id;
				//$row->qty_with_unit = $item->unit_quantity.' '.$item->unit_name;
				
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                //$units = $this->site->getUnitsByBUID($row->base_unit);
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				$unit = $this->site->getProductUnit($row->id,$item->product_unit_id);
				if($unit && $item->real_unit_cost > 0){
					if($row->item_discount > 0){
						$row->item_discount = ($row->item_discount / $unit->unit_qty);
					}
				}
				foreach($units as $unit){
					if($unit->id == $item->product_unit_id){
						$row->qty_with_unit = $item->unit_quantity.' '.$unit->name;
						if($row->received > 0){
							$row->rec_with_unit = ($row->received / $unit->operation_value).' '.$unit->name;
						}else{
							$row->rec_with_unit = 0;
						}
						
					}
				}
				
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options);

                $c++;
            }
			
			if ($purchase->return_id) {
				$this->data['return'] = $this->purchases_model->getPurchaseByID($purchase->return_id);
			}
			
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['reference'] = '';
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('return_purchase')));
            $meta = array('page_title' => lang('return_purchase'), 'bc' => $bc);
            $this->core_page('purchases/return_purchase', $meta, $this->data);
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

    public function update_status($id)
    {

        $this->form_validation->set_rules('status', lang("status"), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note = $this->cus->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'purchases');
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'purchases');
        } else {

            $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
            $this->data['returned'] = FALSE;
            if ($this->data['inv']->status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned'] = TRUE;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'purchases/update_status', $this->data);

        }
    }
	
	
	public function freights($biller_id = NULL)
	{
		$this->cus->checkPermissions('index');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => '#', 'page' => lang('freights')));
        $meta = array('page_title' => lang('freights'), 'bc' => $bc);
        $this->core_page('purchases/freights', $meta, $this->data);
	}

    public function getFreights($biller_id = NULL)
    {
		$this->cus->checkPermissions('index');
		$detail_link = anchor('purchases/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('freight_note'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal2"');
		$add_payment_link = anchor('purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$payments_link = anchor('purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_link . '</li>
				<li>' . $payments_link . '</li>
				<li>' . $add_payment_link . '</li>
			</ul>
		</div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select(
						$this->db->dbprefix('purchases') . ".id as id, 
						DATE_FORMAT(".$this->db->dbprefix('purchases').".date, '%Y-%m-%d %T') as date,
						companies.company,
						{$this->db->dbprefix('warehouses')}.name as wname,						
						purchases.reference_no,
						IFNULL(purchase.reference_no,".$this->db->dbprefix('receives').".re_reference_no) as pur_reference,
						purchases.supplier, 
						purchases.grand_total,
						purchases.paid, 
						".$this->db->dbprefix('purchases').".grand_total - IFNULL({$this->db->dbprefix('purchases')}.paid,0) as balance,
						IF(
							".$this->db->dbprefix('purchases').".grand_total = ".$this->db->dbprefix('purchases').".paid,'paid',
							IF(".$this->db->dbprefix('purchases').".paid > 0,'partial','pending')
						) as payment_status", false
					)
				->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
				->join('companies', 'companies.id=purchases.biller_id', 'left')
				->join('receives', 'receives.id=purchases.receive_id', 'left')
				->join('(SELECT id, reference_no FROM '.$this->db->dbprefix('purchases').') as purchase','purchase.id = purchases.purchase_id','left')
				->from('purchases');
		$this->datatables->where('purchases.status', 'freight');	
		$this->datatables->where('purchases.grand_total >', 0);	
		if ($biller_id) {
            $this->datatables->where('purchases.biller_id', $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('purchases.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	
	public function freight_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('freight'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('reference_no_to'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $freight = $this->purchases_model->getPurcahseFreightByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($freight->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $freight->company);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $freight->wname);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $freight->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $freight->pur_reference);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $freight->supplier);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->formatDecimal($freight->grand_total));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->formatDecimal($freight->paid));
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->cus->formatDecimal($freight->balance));		
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($freight->payment_status));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);

                    $filename = 'freights_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_freight_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

	public function add_freight($purchase_id = null)
    {        
		$this->cus->checkPermissions('add',true);
		$this->form_validation->set_rules('supplier', lang("supplier"), 'trim');        
        if ($this->form_validation->run() == true) {
			if(count($_POST['supplier']) <= 0){
				$this->session->set_flashdata('error', lang("freight_cannot_add"));
				redirect("purchases");
			}
			$biller_details = $this->purchases_model->getPurchaseByID($purchase_id);
			$biller_id = $this->input->post('biller');
			
			
			$i = sizeof($_POST['amount']);			
			for ($r = 0; $r < $i; $r++) {
				$supplier_id = $_POST['supplier'][$r];
				$supplier_details = $this->site->getCompanyByID($supplier_id);
				$total = $_POST['amount'][$r];
				$f_cost = $_POST['f_cost'][$r];
				$tax = $_POST['tax'][$r];
				$reference_no = $_POST['reference_no'][$r];
				$old_id = $_POST['old_id'][$r];
				$date = $this->cus->fld(trim($_POST['freight_date'][$r]));
				$order_tax = $total - $f_cost;
				$data[] = array(
					'purchase_id' => $purchase_id,
					'reference_no' => $reference_no,
					'date' => $date,
					'supplier_id' => $supplier_id,
					'supplier' => $supplier_details->name,		
					'f_cost' => $f_cost,	
					'order_tax_id' => $tax,
					'order_tax' => $order_tax,
					'total' => $total,
					'old_id' => $old_id,
					'created_by' => $this->session->userdata('user_id'),
				);	
			}
			
			$a = sizeof($_POST['quantity']);			
			for ($f = 0; $f < $a; $f++) {
				$product_code = $_POST['product_code'][$f];
				$unit_cost = $_POST['unit_cost'][$f];
				$unit_percent = $_POST['percent'][$f];
				$quantity = $_POST['quantity'][$f];
				$purchase_item = $_POST['purchase_item'][$f];				
				$product_details = $this->purchases_model->getProductByCode($product_code);				
				$data2[] = array(
						'purchase_id' => $purchase_id,
						'purchase_item_id' => $purchase_item,
                        'product_id' => $product_details->id,
                        'product_code' => $product_code,
                        'product_name' => $product_details->name,                        
                        'unit_cost' => $unit_cost,
						'unit_percent' => $unit_percent,
                        'quantity' => $quantity,                        
                        'date' => date('Y-m-d', strtotime($date)),
                    );
			}
		}	
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchaseShipping($purchase_id, $data, $data2, $biller_details->biller_id, $biller_details->project_id, $biller_details->reference_no, $biller_details->warehouse_id)) {
            $this->session->set_flashdata('message', lang("freight_added"));
            redirect("purchases/freights");
        } else {					
			$this->data['id'] = $purchase_id;
			$this->data['shippings'] = $this->purchases_model->getAllPurchaseShippingByPurchaseId($purchase_id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));            
			$this->data['suppliers'] = $this->companies_model->getAllSupplierCompanies();
            $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
			$this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['freight_type'] = false;
			$this->data['totalItem'] = $this->purchases_model->getAllTotalAmountPurchaseItems($purchase_id);					
            $this->data['modal_js'] = $this->site->modal_js();			
            $this->load->view($this->theme . 'purchases/add_freight', $this->data);
        }
    }
	
	public function add_rec_freight($receive_id = null)
    {        
		$this->form_validation->set_rules('supplier', lang("supplier"), 'trim');        
        if ($this->form_validation->run() == true) {
			if(count($_POST['supplier']) <= 0){
				$this->session->set_flashdata('error', lang("freight_cannot_add"));
				redirect("purchases/receives");
			}
			$biller_details = $this->purchases_model->getReceiveByID($receive_id);
			$biller_id = $this->input->post('biller');
			$i = sizeof($_POST['amount']);			
			for ($r = 0; $r < $i; $r++) {
				$supplier_id = $_POST['supplier'][$r];
				$supplier_details = $this->site->getCompanyByID($supplier_id);
				$total = $_POST['amount'][$r];
				$reference_no = $_POST['reference_no'][$r];
				$f_cost = $_POST['f_cost'][$r];
				$old_id = $_POST['old_id'][$r];
				$tax = $_POST['tax'][$r];
				$order_tax = $total - $f_cost;
				if ($this->Owner || $this->Admin || $this->cus->GP['purchases-date'] ) {
					$date = $this->cus->fld(trim($_POST['freight_date'][$r]));
				} else {
					$date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
				}
				$data[] = array(
					'receive_id' => $receive_id,
					'reference_no' => $reference_no,
					'date' => $date,
					'supplier_id' => $supplier_id,
					'supplier' => $supplier_details->name,		
					'f_cost' => $f_cost,	
					'order_tax_id' => $tax,
					'order_tax' => $order_tax,	
					'total' => $total,
					'old_id' => $old_id,
					'created_by' => $this->session->userdata('user_id'),
				);	
			}
			$a = sizeof($_POST['quantity']);			
			for ($f = 0; $f < $a; $f++) {
				$product_code = $_POST['product_code'][$f];
				$unit_cost = $_POST['unit_cost'][$f];
				$unit_percent = $_POST['percent'][$f];
				$quantity = $_POST['quantity'][$f];
				$purchase_item = $_POST['purchase_item'][$f];				
				$product_details = $this->purchases_model->getProductByCode($product_code);				
				$data2[] = array(
						'receive_id' => $receive_id,
						'purchase_item_id' => $purchase_item,
                        'product_id' => $product_details->id,
                        'product_code' => $product_code,
                        'product_name' => $product_details->name,                        
                        'unit_cost' => $unit_cost,
						'unit_percent' => $unit_percent,
                        'quantity' => $quantity,                        
                        'date' => date('Y-m-d', strtotime($date)),
                    );
			}			
		}	
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchaseRecShipping($receive_id, $data, $data2, $biller_details->biller_id, $biller_details->project_id, $biller_details->reference_no, $biller_details->warehouse_id)) {
            $this->session->set_flashdata('message', lang("freight_added"));
			redirect("purchases/freights");
        } else {
			$receive = $this->purchases_model->getReceiveByID($receive_id);
			$this->data['id'] = $receive_id;
			$this->data['shippings'] = $this->purchases_model->getAllReceiveShippingByReceiveId($receive_id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));            
			$this->data['suppliers'] = $this->companies_model->getAllSupplierCompanies();
			if($receive->purchase_order_id > 0){
				$this->data['rows'] = $this->purchases_model->getAllReceivePOItems($receive_id);
			}else{
				$this->data['rows'] = $this->purchases_model->getAllReceiveItems($receive_id);
			}
			$this->data['freight_type'] = 'freight_receive';
			$this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['totalItem'] = $this->purchases_model->getAllTotalAmountReceiveItems($receive_id);					
            $this->data['modal_js'] = $this->site->modal_js();			
            $this->load->view($this->theme . 'purchases/add_freight', $this->data);
        }
    }
	
	public function get_expense_category()
	{
		$category_id = $this->input->get(category_id);
		$category = $this->purchases_model->getExpenseCategoryByID($category_id);
		if($category){
			$this->cus->send_json($category);
		}else{
			$this->cus->send_json(array('id'=>0));
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
		$opt = form_dropdown('project', $pl, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="project" class="form-control select"');
		echo json_encode(array("result" => $opt));
	}

	public function receives($biller_id = NULL)
	{
		$this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => '#', 'page' => lang('receives')));
        $meta = array('page_title' => lang('receives'), 'bc' => $bc);
        $this->core_page('purchases/receives', $meta, $this->data);
	}
	
	public function add_receive($id = false, $po_id = false)
	{
		$this->cus->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
		$this->form_validation->set_rules('receive_by', $this->lang->line("receive_by"), 'required');
		
        if ($this->form_validation->run() == true){
			$purchase_order_id = $this->input->post("purchase_order_id");
			if($purchase_order_id > 0){
				$purchase_details = $this->purchase_order_model->getPurchaseOrderByID($purchase_order_id);
			}else{
				$purchase_details = $this->purchases_model->getPurchaseByID($id);
			}
			$biller_id = ($purchase_details->biller_id?$purchase_details->biller_id:0);
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rec',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['purchases-receive_date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$receive_by = $this->input->post('receive_by');
			if ($receive_by <= 0) {
				$this->session->set_flashdata('error', lang('receive_required'));
				redirect($_SERVER["HTTP_REFERER"]);
			}
			$project_id = $purchase_details->project_id;
            $warehouse_id = $this->input->post('warehouse');
			$status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
			$supplier_id = $this->input->post('supplier');
			$si_reference_no = $this->input->post('si_reference_no');
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->cus->clear_tags($this->input->post('note'));		
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = sizeof($_POST['product_code']);
            for ($r = 0; $r < $i; $r++) {
				$item_code = $_POST['product_code'][$r];
				$item_name = $_POST['product_name'][$r];
				$item_type = $_POST['product_type'][$r];
				$item_comment = $_POST['product_comment'][$r];
                $item_net_cost = ($_POST['net_cost'][$r]);
                $unit_cost = $_POST['unit_cost'][$r];
                $real_unit_cost = ($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$item_note = $_POST['pnote'][$r];
				$serial_no = $_POST['serial_no'][$r];
				$parent_id = $_POST['parent_id'][$r];
				$total_cbm =  $_POST['cbm'][$r] * $item_unit_quantity;
				$item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->cus->fsd($_POST['expiry'][$r]) : null;
				$sup_qty = $_POST['sup_qty'][$r];
				if (isset($item_code)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = ((($unit_cost) * (Float) ($pds[0])) / 100);
                        } else {
                            $pr_discount = ($discount);
                        }
                    }

                    $unit_cost = ($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = ($pr_discount * $item_unit_quantity);
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
                                $item_tax = ((($unit_cost) * $tax_details->rate) / 100);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate));
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / 100);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate));
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                            $item_tax = ($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = ($item_tax * $item_unit_quantity);
                    }
					$product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getProductUnit($product_details->id, $item_unit);
                    $products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => ($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
						'unit_qty' => $unit->unit_qty,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $purchase_details->warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => ($subtotal),
                        'real_unit_cost' => $real_unit_cost,
						'product_type' => $item_type,
						'parent_id' => $parent_id,
						'serial_no' => $serial_no,
						'comment' => $item_comment,
						'reference_no' => $reference,
						'expiry' => $item_expiry,
						'total_cbm' => $total_cbm,
						'sup_qty' => $sup_qty,
						'user_id' => $this->session->userdata('user_id'),
                    );
					$total += ($item_net_cost * $item_unit_quantity);
				}
			}
			$data = array(
				'date' => $date,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
				'biller' => $biller,
				'supplier_id' => $supplier_id,
				'supplier' => $supplier,
				'warehouse_id' => $purchase_details->warehouse_id,
				're_reference_no' => $reference,
				'si_reference_no' => $si_reference_no,
				'pu_reference_no' => $purchase_details->reference_no,
				'address' => $this->input->post('address'),
				'received_by' => $receive_by,
				'note' => $this->cus->clear_tags($this->input->post('note')),
				'created_by' => $this->session->userdata('user_id'),
				'status' => ($purchase_order_id > 0 ? 'pending' : 'completed'),
				'dn_reference' => $this->input->post('dn_reference'),
				'truck' => $this->input->post('truck'),
			);
			if($purchase_order_id > 0){
				$data['purchase_order_id'] = $purchase_details->id;
			}else{
				$data['purchase_id'] = $purchase_details->id;
			}
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
		if ($this->form_validation->run() == true && $this->purchases_model->addReceive($data, $products)) {
            $this->session->set_flashdata('message', $this->lang->line("receive_added"));
			$this->session->set_userdata('remove_rels', 1);
            redirect('purchases/receives');
        } else {
			if($id){
				if($po_id==1){
					$purchase = $this->purchase_order_model->getPurchaseOrderByID($id);
					if($purchase->status=='completed'){
						$this->session->set_flashdata('error', lang('purchase_order_is_already_received'));
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$inv_items = $this->purchases_model->getReceiveItemsByPOID($id);
					$this->data['purchase_order_id'] = $id;
				}else{
					$purchase = $this->purchases_model->getPurchaseByID($id);
					if($purchase->status=='received'){
						$this->session->set_flashdata('error', lang('purchase_is_already_received'));
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$inv_items = $this->purchases_model->getReceiveItemsByPurchaseID($id);
					$this->data['purchase_id'] = $id;
				}
				krsort($inv_items);
				$c = rand(100000, 9999999);
				foreach ($inv_items as $item) {				
					$row = $this->site->getProductByID($item->product_id);
					if (!$row) {
						$row = json_decode('{}');
						$row->quantity = 0;
					} else {
						unset($row->details, $row->product_details);
					}
					$row->received = ($item->unit_quantity - $item->received);
					if($row->received != 0){
						$row->qty = $row->received;
						$row->parent_id = (isset($item->parent_id) ? $item->parent_id : '');
						$row->id = $item->product_id;
						$row->code = $row->code;
						$row->name = $item->product_name;
						$row->unit_quantity = $item->unit_quantity;
						$row->base_quantity = $row->received;
						$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
						$row->base_unit_cost = $item->real_unit_cost;
						$row->unit = $item->product_unit_id;
						$row->option = $item->option_id;
						$row->discount = $item->discount ? $item->discount : '0';
						$supplier_cost = isset($supplier_id) ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
						$row->cost = $supplier_cost ? $supplier_cost : 0;
						$row->tax_rate = $item->tax_rate_id;
						$row->expiry = ((isset($item->expiry) && $item->expiry && $item->expiry != '0000-00-00') ? $this->cus->hrsd($item->expiry) : '');
						$row->real_unit_cost = $item->real_unit_cost;
						$row->unit_cost = $row->tax_method ? $item->unit_cost + ($item->item_discount / $item->quantity) + ($item->item_tax / $item->quantity) : $item->unit_cost + ($item->item_discount / $item->quantity);
						$row->total_cbm = $item->total_cbm;
						$row->sup_qty = $row->qty;
						$row->serial_no = $item->serial_no;
						$options = $this->purchases_model->getProductOptions($row->id);
						$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
						$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
						$ri = $this->Settings->item_addition ? $row->id : $c;
						$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
							'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
						$c++;
					}
	
				}
				$this->data['inv'] = $purchase;
				$this->data['inv_items'] = json_encode($pr);
				$this->data['supplier'] = $this->site->getCompanyByID($purchase->supplier_id);
			}
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['purchases'] = $this->site->getRefPurchases('received');
			if($this->config->item("po_receive_item")){
				$this->data['purchase_orders'] = $this->purchases_model->getRefPurchaseRC();
			}
			$this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['users'] = $this->site->getAllUsers();
            $this->data['re_reference_no'] = '';
			$bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => site_url('purchases/receives'), 'page' => lang('receive_items')), array('link' => '#', 'page' => lang('add_receive')));
            $meta = array('page_title' => lang('add_receive'), 'bc' => $bc);
            $this->core_page('purchases/add_receive', $meta, $this->data);
		}
	}
	public function edit_receive($id = false)
	{
		$this->cus->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
		$this->form_validation->set_rules('receive_by', $this->lang->line("receive_by"), 'required');
        if ($this->form_validation->run() == true){
			$purchase_order_id = $this->input->post("purchase_order_id");
			if($purchase_order_id > 0){
				$purchase_details = $this->purchase_order_model->getPurchaseOrderByID($purchase_order_id);
			}else{
				$purchase_id = $this->input->post("purchase_id");
				$purchase_details = $this->purchases_model->getPurchaseByID($purchase_id);
			}
			$biller_id = ($purchase_details->biller_id?$purchase_details->biller_id:0);
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rec',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['purchases-receive_date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$receive_by = $this->input->post('receive_by');
			if ($receive_by <= 0) {
				$this->session->set_flashdata('error', lang('receive_required'));
				redirect($_SERVER["HTTP_REFERER"]);
			}
			$si_reference_no = $this->input->post('si_reference_no');			
			$project_id = $this->input->post('project');
            $warehouse_id = $this->input->post('warehouse');
			$status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
			$supplier_id = $this->input->post('supplier');
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->cus->clear_tags($this->input->post('note'));		
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = sizeof($_POST['product_code']);
            for ($r = 0; $r < $i; $r++) {
				$item_code = $_POST['product_code'][$r];
				$item_name = $_POST['product_name'][$r];
				$item_type = $_POST['product_type'][$r];
				$item_comment = $_POST['product_comment'][$r];
                $item_net_cost = ($_POST['net_cost'][$r]);
                $unit_cost = ($_POST['unit_cost'][$r]);
                $real_unit_cost = ($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$item_note = $_POST['pnote'][$r];
				$serial_no = $_POST['serial_no'][$r];
				$parent_id = $_POST['parent_id'][$r];
				$sup_qty = $_POST['sup_qty'][$r];
				$item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->cus->fsd($_POST['expiry'][$r]) : null;	
				if (isset($item_code)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = ((($unit_cost) * (Float) ($pds[0])) / 100);
                        } else {
                            $pr_discount = ($discount);
                        }
                    }
                    $unit_cost = ($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = ($pr_discount * $item_unit_quantity);
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
                                $item_tax = ((($unit_cost) * $tax_details->rate) / 100);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate));
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / 100);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate));
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                            $item_tax = ($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = ($item_tax * $item_unit_quantity);
                    }
					$product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getProductUnit($product_details->id, $item_unit);
                    $products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => ($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
						'unit_qty' => $unit->unit_qty,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $purchase_details->warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => ($subtotal),
                        'real_unit_cost' => $real_unit_cost,
						'product_type' => $item_type,
						'parent_id' => $parent_id,
						'serial_no' => $serial_no,
						'comment' => $item_comment,
						'reference_no' => $reference,
						'expiry' => $item_expiry,
						'sup_qty' => $sup_qty,
						'user_id' => $this->session->userdata('user_id'),
                    );
					$total += ($item_net_cost * $item_unit_quantity);
				}
			}
			$data = array(
				'date' => $date,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'supplier_id' => $supplier_id,
				'supplier' => $supplier,
				'warehouse_id' => $purchase_details->warehouse_id,
				're_reference_no' => $reference,
				'si_reference_no' => $si_reference_no,
				'pu_reference_no' => $purchase_details->reference_no,
				'address' => $this->input->post('address'),
				'received_by' => $receive_by,
				'note' => $this->cus->clear_tags($this->input->post('note')),
				'created_by' => $this->session->userdata('user_id'),
				'updated_at' => date("Y-m-d H:i"),
				'dn_reference' => $this->input->post('dn_reference'),
				'truck' => $this->input->post('truck'),
			);
			if($purchase_order_id > 0){
				$data['purchase_order_id'] = $purchase_details->id;
			}else{
				$data['purchase_id'] = $purchase_details->id;
			}
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
		if ($this->form_validation->run() == true && $this->purchases_model->updateReceive($id, $data, $products)) {
            $this->session->set_flashdata('message', $this->lang->line("receive_added"));
            redirect('purchases/receives');
        } else {
			$this->data['inv'] = $this->purchases_model->getReceiveByID($id);
			if($this->data['inv']->purchase_id > 0 && $this->data['inv']->purchase_order_id > 0){
				$this->session->set_flashdata('error', lang("receive_cannot_edit"));
				$this->cus->md();
			}
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			if($this->data['inv']->purchase_id > 0){
				$inv_items = $this->purchases_model->getAllReceiveItems($id);
			}else{
				$inv_items = $this->purchases_model->getAllReceivePOItems($id);
			}
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {				
				$row = $this->site->getProductByID($item->product_id);
				if (!$row) {
					$row = json_decode('{}');
					$row->quantity = 0;
				} else {
					unset($row->details, $row->product_details);
				}
				$row->received = $item->purchase_qty + $item->quantity;
				$row->qty = $item->unit_quantity;
				$row->parent_id = $item->parent_id;
				$row->id = $item->product_id;
				$row->code = $item->product_code;
				$row->name = $item->product_name;
				$row->base_quantity = $item->quantity;
				$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
				$row->base_unit_cost = $item->real_unit_cost;
				$row->unit = $item->product_unit_id;
				$row->option = $item->option_id;
				$row->discount = $item->discount ? $item->discount : '0';
				$supplier_cost = isset($supplier_id) ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
				$row->cost = $supplier_cost ? $supplier_cost : 0;
				$row->tax_rate = $item->tax_rate_id;
				$row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->cus->hrsd($item->expiry) : '');
				$row->comment = $item->comment;
				$row->sup_qty = $item->sup_qty;
				$row->serial_no = $item->serial_no;
				$row->real_unit_cost = $item->real_unit_cost;
				$row->unit_cost = $row->tax_method ? $item->unit_cost + ($item->item_discount / $item->quantity) + ($item->item_tax / $item->quantity) : $item->unit_cost + ($item->item_discount / $item->quantity);
				$options = $this->purchases_model->getProductOptions($row->id);
				$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				$ri = $this->Settings->item_addition ? $row->id : $c;
				$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
					'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
				$c++;
			}
			$this->data['inv_items'] = json_encode($pr);
			$this->data['id'] = $id;
			$this->data['billers'] = $this->site->getBillers();
			$this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['supplier'] = $this->site->getCompanyByID($this->data['inv']->supplier_id);
			$this->data['users'] = $this->site->getAllUsers();
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['re_reference_no'] = '';
			$bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('purchases'), 'page' => lang('purchase')), array('link' => site_url('purchases/receives'), 'page' => lang('receive_items')), array('link' => '#', 'page' => lang('edit_receive')));
            $meta = array('page_title' => lang('edit_receive'), 'bc' => $bc);
            $this->core_page('purchases/edit_receive', $meta, $this->data);
		}
	}
	
	public function getReceives($biller_id = NULL)
    {
		$this->cus->checkPermissions('receives');
		$detail_link = anchor('purchases/receive_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('receive_note'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal2"');
		$freight_link = anchor('purchases/add_rec_freight/$1', '<i class="fa fa-money"></i> ' . lang('add_freight'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_link = anchor('purchases/edit_receive/$1', '<i class="fa fa-edit"></i> ' . lang('edit_receive'), ' ');
	    $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_expense") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchases/delete_receive/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_receive') . "</a>";
		
		$add_receive_vat = '';
		if($this->Settings->receive_item_vat == 1){
			$add_receive_vat = anchor('purchases/add_receive_vat/$1', '<i class="fa fa-money"></i> ' . lang('add_receive_vat'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		}
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_link . '</li>
				<li>' . $add_receive_vat . '</li>
				<li>' . $freight_link . '</li>
				<li>' . $edit_link . '</li>
				<li>' . $delete_link . '</li>
			</ul>
		</div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select(
			$this->db->dbprefix('receives') . ".id as id, 
			date, 
			re_reference_no,
			pu_reference_no,
			supplier,
			note,
			CONCAT(last_name,' ',first_name) as received_by,  
			status, 
			attachment", false)
            ->from('receives')
            ->join('users', 'users.id=receives.received_by', 'left')
            ->group_by('receives.id');

		if ($biller_id) {
            $this->datatables->where('receives.biller_id', $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('receives.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('receives.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function delete_receive($id = null)
    {
        $this->cus->checkPermissions('delete_receive', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $receive = $this->purchases_model->getReceiveByID($id);
        if ($this->purchases_model->deleteReceive($id)) {
            if ($receive->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
            echo lang("receive_deleted");
        }
    }
	
	public function receive_note($id = null)
    {
		$this->cus->checkPermissions('receives',true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $receive = $this->purchases_model->getReceiveByID($id);
		$this->data['supplier'] = $this->site->getCompanyByID($receive->supplier_id);
        $this->data['receive'] = $receive;
        $this->data['biller'] = $this->site->getCompanyByID($receive->biller_id);
		if($receive->purchase_order_id > 0){
			$this->data['rows'] = $this->purchases_model->getAllReceivePOItems($id);
		}else{
			$this->data['rows'] = $this->purchases_model->getAllReceiveItems($id);
		}
        $this->data['user'] = $this->site->getUser($receive->created_by);
        $this->data['page_title'] = lang("receive_note");
		
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Receive',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Receive',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
        $this->load->view($this->theme . 'purchases/receive_note', $this->data);
    }
	
	public function receive_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'add_purchase') {
					$ids = false; 
					$supplier_id = "";
					$biller_id = "";
					$warehouse_id = "";
                    foreach ($_POST['val'] as $id) {
						$row = $this->purchases_model->getReceiveByID($id);
						if(($warehouse_id == "" || $warehouse_id == $row->warehouse_id) && ($biller_id == "" || $biller_id == $row->biller_id) && ($supplier_id == "" || $supplier_id == $row->supplier_id) && $row->status != "completed"){
							$supplier_id = $row->supplier_id;
							$biller_id = $row->biller_id;
							$warehouse_id = $row->warehouse_id;
							$ids[] = $id;
						}
						if(!$ids){
							$this->session->set_flashdata('error', lang("cannot_add_purchase"));
							redirect($_SERVER["HTTP_REFERER"]);
						}
                    }
					redirect('purchases/add?receive_ids='.json_encode($ids));
                }else if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete_receive');
                    foreach ($_POST['val'] as $id) {
                        $this->purchases_model->deleteReceive($id);
                    }
                    $this->session->set_flashdata('message', lang("receive_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('receives'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('pu_reference'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('received_by'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $receive = $this->purchases_model->getReceiveByID($id);
						$user = $this->site->getUser($receive->received_by);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($receive->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $receive->biller);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, ($receive->re_reference_no));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, ($receive->pu_reference_no));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, ($receive->supplier));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $receive->note);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $user->last_name . " " .$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($receive->status));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                    $filename = 'receives_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_receive_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function add_multi_expayment($id = null)
    {
        $this->cus->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$ids = explode('ExpenseID',$id);		
        $expense  = $this->purchases_model->getMultiExpenseByID($ids);
		$multiple = $this->purchases_model->getExpenseByBillers($ids);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->cus->GP['purchases-expenses-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$total_amount = $this->input->post('amount-paid');
			$camounts = $this->input->post("c_amount");
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
            }
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$multiple->row()->biller_id);
			$paid_currencies = array();
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$paid_currencies[$currency[$key]] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$cur_def = $this->site->getCurrencyByCode($this->Settings->default_currency);
			for($i=0; $i < count($ids); $i++){
				if($total_amount > 0){
					$expenseInfo = $this->purchases_model->getExpenseBalanceByID($ids[$i]);
					if($expenseInfo){
						$total = ($expenseInfo->grand_total) - ($expenseInfo->paid+$expenseInfo->discount);
						$grand_total = $total;
						if($total_amount > $grand_total){
							$pay_amount = $grand_total;
							$total_amount = $total_amount - $grand_total;
						}else{
							$pay_amount = $total_amount;
							$total_amount = 0;
						}
						$currencies = array();
						if(!empty($camounts)){
							$total_paid = $pay_amount;
						
							foreach($paid_currencies as $cur_code => $paid_currencie){
								$paid_cur = $paid_currencie['amount'];
								if($paid_cur > 0){
									if($cur_code != $cur_def->code){
										if($paid_currencie['rate'] > $cur_def->rate){
											$paid_cur = $paid_cur / $paid_currencie['rate'];
										}else{
											$paid_cur = $paid_cur * $cur_def->rate;
										}
									}
									if($paid_cur >= $total_paid && $total_paid > 0){
										$paid_currencie['amount'] = $total_paid;
										if($cur_code != $cur_def->code){
											if($paid_currencie['rate'] > $cur_def->rate){
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) * $paid_currencie['rate'];
											}else{
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) / $cur_def->rate;
											}
										}else{
											$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid);
										}
										$total_paid = 0;
									}else{
										if($total_paid > 0){
											$paid_currencie['amount'] = $paid_cur;
											$paid_currencies[$cur_code]['amount'] = 0;
											$total_paid = $total_paid - $paid_cur;
										}else{
											$paid_currencie['amount'] = 0;
										}
									}
								}								
								if($cur_code != $cur_def->code){
									if($paid_currencie['rate'] > $cur_def->rate){
										$paid_currencie['amount'] = $paid_currencie['amount'] * $paid_currencie['rate'];
									}else{
										$paid_currencie['amount'] = $paid_currencie['amount'] / $cur_def->rate;
									}
								}
								$currencies[] = $paid_currencie;
							}
						}
						$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
						$paying_from = $cash_account->account_code;
						$payment[] = array(
							'date' 			=> $date,
							'expense_id' 	=> $expenseInfo->id,
							'reference_no' 	=> $reference_no,
							'amount' 		=> $pay_amount,
							'paid_by' 		=> $this->input->post('paid_by'),
							'note' 			=> $this->input->post('note'),
							'created_by' 	=> $this->session->userdata('user_id'),
							'type' 			=> 'sent',
							'currencies' 	=> json_encode($currencies),
							'account_code' 	=> $this->input->post('paying_from'),
							'attachment' 	=> $photo,
						);
						if($this->Settings->accounting == 1){
							$paymentAcc = $this->site->getAccountSettingByBiller($expenseInfo->biller_id);
							$accTranPayments[$expenseInfo->id][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => (($this->Settings->default_payable_account==0 || !$expenseInfo->ap_account) ? $paymentAcc->ap_acc : $expenseInfo->ap_account),
									'amount' => $pay_amount,
									'narrative' => 'Expense Payment '.$expenseInfo->reference_no,
									'description' => $this->input->post('note'),
									'biller_id' => $expenseInfo->biller_id,
									'project_id' => $expenseInfo->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'supplier_id' => $expenseInfo->supplier_id,
								);
							$accTranPayments[$expenseInfo->id][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $paying_from,
									'amount' => $pay_amount * (-1),
									'narrative' => 'Expense Payment '.$expenseInfo->reference_no,
									'description' => $this->input->post('note'),
									'biller_id' => $expenseInfo->biller_id,
									'project_id' => $expenseInfo->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'supplier_id' => $expenseInfo->supplier_id,
								);
						}
					}
				}
			}
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->purchases_model->addMultiExPayment($payment, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if (!$expense) {
                $this->session->set_flashdata('warning', lang('expenses_already_paid'));
                $this->cus->md();
            }
			if($multiple->num_rows() > 1){
				$this->session->set_flashdata('error', lang("biller_multi_cannot_add"));
				$this->cus->md();
			}
            $this->data['inv'] = $expense;
            $this->data['payment_ref'] = ''; 
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'purchases/add_multi_expayment', $this->data);
        }
    }
	
	function import_expense() {
		$this->cus->checkPermissions('expenses-add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
				$expenses = false;
				$expense_items = false;
				$accTrans = false;
				$biller_id = $this->input->post("biller");
				$biller_details = $this->site->getCompanyByID($biller_id);
				$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
				$warehouse_id = $this->input->post("warehouse");
				$expenseAcc = $this->site->getAccountSettingByBiller($biller_id);
				$cash_account = $this->site->getCashAccountByID($this->Settings->default_cash_account);
				$paying_from = $cash_account->account_code;
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				$o_date = "";
				$o_supplier_code = "";
				$o_reference = "";
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++){
						$date = trim($worksheet->getCellByColumnAndRow(0, $row)->getValue());
						$reference = trim($worksheet->getCellByColumnAndRow(1, $row)->getFormattedValue());
						$supplier = trim($worksheet->getCellByColumnAndRow(2, $row)->getValue());
						$category = trim($worksheet->getCellByColumnAndRow(3, $row)->getValue());
						$description = trim($worksheet->getCellByColumnAndRow(4, $row)->getValue());
						$cost = trim($worksheet->getCellByColumnAndRow(5, $row)->getValue());
						$quantity = trim($worksheet->getCellByColumnAndRow(6, $row)->getValue());
						$order_tax = trim($worksheet->getCellByColumnAndRow(7, $row)->getValue());
						if (strpos($date, '/') == false) {
							$date = PHPExcel_Shared_Date::ExcelToPHP($date);
							$date = date('d/m/Y',$date);
						}
						if($date=="" || $reference==""){
							$date = $o_date;
							$reference = $o_reference;
							$supplier = $o_supplier;
							$order_tax = $o_order_tax;
						}
						if($date!='' && $reference!='' && $category!=''){
							$finals[] = array(
								'date'			=> $date,
								'reference'		=> $reference,
								'supplier'		=> $supplier,
								'category' 		=> $category,
								'description'  	=> $description,
								'cost'  		=> $cost,
								'quantity'  	=> $quantity,
								'order_tax'   	=> $order_tax,
							);	
							$o_date = $date;
							$o_reference = $reference;
							$o_supplier = $supplier;
							$o_order_tax = $order_tax;
						}
					}
				}
				if($finals){
					foreach($finals as $final){
						$index = $final['date']."_".$final['reference']."_".$final['supplier'];
						$supplier = $this->purchases_model->getSupplierByCompany($final['supplier']);
						if(!$supplier && $this->Settings->payment_expense != 0){
							$this->session->set_flashdata('error', lang("supplier_company") . " (" . $final['supplier_company'] . "). " . lang("company__exist"));
							redirect("purchases/import_expense");
						}
						$category = $this->purchases_model->getExpenseCategoryByName($final['category']);
						if(!$category){
							$this->session->set_flashdata('error', lang("category_name") . " (" . $final['category'] . "). " . lang("name__exist"));
							redirect("purchases/import_expense");
						}

						if (isset($final['order_tax']) && $final['order_tax']) {
							$order_tax = $this->site->getTaxRateByCode($final['order_tax']);
							if(!$order_tax){
								$this->session->set_flashdata('error', lang("order_tax") . " (" . $final['order_tax'] . "). " . lang("code__exist"));
								redirect("purchases/import_expense");
							}
						}else{
							$order_tax = "";
						}
						$expense = array(
										'date' => $this->cus->fsd($final['date']),
										'reference' => $final['reference'],
										'biller_id' => $biller_id,
										'biller' => $biller,
										'supplier_id' => $supplier->id,
										'supplier' => $supplier->company,
										'payment_status'    => "pending",
										'created_by' => $this->session->userdata('user_id'),
										'warehouse_id' => $warehouse_id,
										'ap_account' => $expenseAcc->ap_acc,
										'order_tax' => $order_tax,
										
									);			
						$subtotal = $final['cost'] * $final['quantity'];
						$purchase_item = array(
									'product_id'      	=> $product->id,
									'product_code'    	=> $product->code,
									'product_name'    	=> $product->name,
									'product_type'    	=> $product->type,
									'real_unit_price' 	=> $final['price'],
									'net_unit_price'  	=> $unit_price,
									'unit_price'      	=> $unit_price,
									'quantity'        	=> $final['quantity'] * ($unit->unit_qty > 1 ? $unit->unit_qty : 1),
									'product_unit_id' 	=> $unit->unit_id,
									'product_unit_code' => $unit->code,
									'unit_quantity'   	=> $final['quantity'],
									'warehouse_id'    	=> $warehouse_id,
									'discount'        	=> $final['discount'],
									'item_discount'   	=> $pr_discount * $final['quantity'],
									'subtotal'        	=> $subtotal,
									'cost' 				=> $product->cost,
									'unit_qty'			=> $unit->unit_qty
								);	
						$expense_item = array(
										'category_id' => $category->id,
										'category_code' => $category->code,
										'category_name' => $category->name,
										'description' => $final['description'],
										'unit_cost' => $final['cost'],
										'quantity' => $final['quantity'],
										'subtotal' => $subtotal
									);		
						$expenses[$index] = $expense;
						$expense_items[$index][] = $expense_item;
					}
					
					
					if($expenses && $expense_items){
						foreach($expenses as $index => $expense){
							$total = 0;
							if($expense_items[$index]){
								foreach($expense_items[$index] as $expense_item){
									$total += $expense_item['subtotal'];	
									if($this->Settings->accounting == 1){
										$expenseCategory = $this->purchases_model->getExpenseCategoryByID($expense_item['category_id']);
										$accTrans[$index][] = array(
														'transaction' => 'Expense',
														'transaction_date' => $expense['date'],
														'reference' => $expense['reference'],
														'account' =>  $expenseCategory->expense_account,
														'amount' => $expense_item['subtotal'],
														'narrative' => 'Expense '.$expense['supplier'],
														'description' => $expense_item['description'],
														'biller_id' => $biller_id,
														'user_id' => $this->session->userdata('user_id'),
														'supplier_id' => $expense['supplier_id'],
													);
									}
									
								}
								$order_tax = 0;
								$expense["amount"] = $total;
								if($expense["order_tax"]){
									$order_tax_id = $expense["order_tax"]->id;
									if ($expense["order_tax"]->type == 2) {
										$order_tax = $this->cus->formatDecimal($expense["order_tax"]->rate);
									}
									if ($expense["order_tax"]->type == 1) {
										$order_tax = $this->cus->formatDecimal(((($total) * $expense["order_tax"]->rate) / 100), 4);
									}
									$expense["order_tax_id"] = $order_tax_id;
									$expense["order_tax"] = $order_tax;
								}
								$grand_total = $this->cus->formatDecimal(($total + $order_tax), 4);
								$expense["grand_total"] = $grand_total;
			
								if($this->Settings->payment_expense == 0){
									$payment[$index] = array(
												'date' => $expense['date'],
												'reference_no' => $expense['reference'],
												'amount' => $grand_total,
												'paid_by' => $this->Settings->default_cash_account,
												'created_by' => $this->session->userdata('user_id'),
												'type' => 'expense',
												'account_code' => $paying_from,
											);
									$expense["payment_status"] = 'paid';
									$expense["paid"] = $grand_total;	
								}
								$expenses[$index] = $expense;
								
								if($this->Settings->accounting == 1){ 
									if($this->Settings->payment_expense == 0){
										$accTrans[$index][] = array(
																'transaction' => 'Expense',
																'transaction_date' => $expense['date'],
																'reference' => $expense['reference'],
																'account' => $paying_from,
																'amount' => -$grand_total,
																'narrative' => 'Expense '.$expense['supplier'],
																'biller_id' => $biller_id,
																'user_id' => $this->session->userdata('user_id'),
																'supplier_id' => $expense['supplier_id']
															);
									}else{
										$accTrans[$index][] = array(
																'transaction' => 'Expense',
																'transaction_date' => $expense['date'],
																'reference' => $expense['reference'],
																'account' => $expenseAcc->ap_acc,
																'amount' => -$grand_total,
																'narrative' => 'Expense '.$expense['supplier'],
																'biller_id' => $biller_id,
																'user_id' => $this->session->userdata('user_id'),
																'supplier_id' => $expense['supplier_id']
															);
									}

									if($order_tax > 0){
										$accTrans[$index][] = array(
																'transaction' => 'Expense',
																'transaction_date' => $expense['date'],
																'reference' => $expense['reference'],
																'account' => $expenseAcc->vat_input,
																'amount' => $order_tax,
																'narrative' => 'Tax Expense '.$expense['supplier'],
																'biller_id' => $biller_id,
																'user_id' => $this->session->userdata('user_id'),
																'supplier_id' => $expense['supplier_id']
															);
									}
									
								}
								
							}
						}
					}
				}
			}
			if (empty($expenses) || empty($expense_items)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            }
			
		}
        if ($this->form_validation->run() == true && $this->purchases_model->importExpense($expenses, $expense_items, $accTrans, $payment)) {
            $this->session->set_flashdata('message', lang("expense_imported"));
            redirect(site_url('purchases/expenses'));
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('import_expense')));
			$meta = array('page_title' => lang('import_expense'), 'bc' => $bc);
            $this->core_page('purchases/import_expense', $meta, $this->data);
        }
    }
	
	public function add_receive_vat($receive_id = null)
    {        
		$this->cus->checkPermissions('add',true);
		$this->form_validation->set_rules('supplier', lang("supplier"), 'trim');        
        if ($this->form_validation->run() == true) {
			$accTrans = false;
			$receive = $this->purchases_model->getReceiveByID($receive_id);
			$purchase = $this->purchases_model->getPurchaseByID($receive->purchase_id);
			$i = sizeof($_POST['amount']);			
			for ($r = 0; $r < $i; $r++) {
				$amount = $_POST['amount'][$r];
				$reference = $_POST['reference'][$r];
				$vat_date = $this->cus->fld(trim($_POST['vat_date'][$r]));
				$data[] = array(
					'purchase_id' => $receive->purchase_id,
					'receive_id' => $receive_id,
					'reference' => $reference,
					'date' => $vat_date,
					'amount' => $amount,
					'created_by' => $this->session->userdata('user_id'),
				);	
				if($this->Settings->accounting == 1){
					$receiveAcc = $this->site->getAccountSettingByBiller($purchase->biller_id);
					$accTrans[] = array(
						'transaction' => 'ReceivesVAT',
						'transaction_id' => $receive_id,
						'transaction_date' => $vat_date,
						'reference' => $reference,
						'account' => ($this->Settings->default_payable_account==0 ? $receiveAcc->ap_acc : $purchase->ap_account),
						'amount' => -$amount,
						'narrative' => 'Receive Item VAT',
						'biller_id' => $purchase->biller_id,
						'project_id' => $purchase->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'supplier_id' => $purchase->supplier_id,
					);
					$accTrans[] = array(
						'transaction' => 'ReceivesVAT',
						'transaction_id' => $receive_id,
						'transaction_date' => $vat_date,
						'reference' => $reference,
						'account' => $receiveAcc->vat_input,
						'amount' => $amount,
						'narrative' => 'Receive Item VAT',
						'biller_id' => $purchase->biller_id,
						'project_id' => $purchase->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'supplier_id' => $purchase->supplier_id,
					);
				}
			}
		}	
        if ($this->form_validation->run() == true && $this->purchases_model->addReceiveVAT($receive_id,$receive->purchase_id, $data, $accTrans)) {
            $this->session->set_flashdata('message', lang("receive_vat_added"));
            redirect("purchases/receives");
        } else {					
			$this->data['id'] = $receive_id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));        
			$this->data['receive_vats'] = $this->purchases_model->getReceiveVATByReceiveID($receive_id);
            $this->data['modal_js'] = $this->site->modal_js();			
            $this->load->view($this->theme . 'purchases/add_receive_vat', $this->data);
        }
    }
	
	function import_purchase()
    {
        $this->cus->checkPermissions();
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
				$purchases = false;
				$purchase_items = false;
				$products = false;
				$stockmoves = false;
				$accTrans = false;

				$biller_id = $this->input->post("biller");
				$biller_details = $this->site->getCompanyByID($biller_id);
				$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
				$warehouse_id = $this->input->post("warehouse");
				$project_id = $this->input->post("project");
				$purchaseAcc = $this->site->getAccountSettingByBiller($biller_id);
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				$o_date = "";
				$o_supplier_code = "";
				$o_reference = "";
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++){
						$date = trim($worksheet->getCellByColumnAndRow(0, $row)->getValue());
						$reference = trim($worksheet->getCellByColumnAndRow(1, $row)->getFormattedValue());
						$supplier_code = trim($worksheet->getCellByColumnAndRow(2, $row)->getValue());
						$product_code = trim($worksheet->getCellByColumnAndRow(4, $row)->getValue());
						$quantity = trim($worksheet->getCellByColumnAndRow(6, $row)->getValue());
						$unit = trim($worksheet->getCellByColumnAndRow(7, $row)->getValue());
						$cost = trim($worksheet->getCellByColumnAndRow(8, $row)->getValue());
						$discount = trim($worksheet->getCellByColumnAndRow(9, $row)->getFormattedValue());
						$order_discount = trim($worksheet->getCellByColumnAndRow(10, $row)->getFormattedValue());
						$order_tax = trim($worksheet->getCellByColumnAndRow(11, $row)->getFormattedValue());
						if (strpos($date, '/') == false) {
							$date = PHPExcel_Shared_Date::ExcelToPHP($date);
							$date = date('d/m/Y',$date);
						}
						if($date=="" || $reference=="" || $supplier_code==""){
							$date = $o_date;
							$reference = $o_reference;
							$supplier_code = $o_supplier_code;
							$order_discount = $o_order_discount;
							$order_tax = $o_order_tax;
						}
						if($date!='' && $reference!='' && $supplier_code!='' && $product_code!=''){
							$finals[] = array(
								'date'			=> $date,
								'reference'		=> $reference,
								'supplier_code' => $supplier_code,
								'product_code'  => $product_code,
								'quantity'  	=> $quantity,
								'unit'   		=> $unit,
								'cost'  		=> $cost,
								'discount'   	=> $discount,
								'order_discount'=> $order_discount,
								'order_tax'   	=> $order_tax,
							);	
							$o_date = $date;
							$o_reference = $reference;
							$o_supplier_code = $supplier_code;
							$o_order_discount = $order_discount;
							$o_order_tax = $order_tax;
						}
					}
				}
				
				if($finals){

					foreach($finals as $final){
						$index = $final['date']."_".$final['reference']."_".$final['supplier_code'];
						$supplier = $this->site->getSupplierByCode($final['supplier_code']);
						if(!$supplier){
							$this->session->set_flashdata('error', lang("supplier_code") . " (" . $final['supplier_code'] . "). " . lang("code__exist"));
							redirect("purchases/import_purchase");
						}
						$product = $this->site->getProductByCode($final['product_code']);
						if(!$product){
							$this->session->set_flashdata('error', lang("product_code") . " (" . $final['product_code'] . "). " . lang("code__exist"));
							redirect("purchases/import_purchase");
						}
						if (isset($final['unit']) && $final['unit']) {
							$unit = $this->site->getProductUnitByCodeName($product->id,$final['unit']);
							if(!$unit){
								$this->session->set_flashdata('error', lang("unit_code") . " (" . $final['product_code']." - ".$final['unit'] . "). " . lang("code__exist"));
								redirect("purchases/import_purchase");
							}
						}else{
							$unit = $this->site->getProductUnitByCodeName($product->id,$product->unit);
						}
						if (isset($final['order_tax']) && $final['order_tax']) {
							$order_tax = $this->site->getTaxRateByCode($final['order_tax']);
							if(!$order_tax){
								$this->session->set_flashdata('error', lang("order_tax") . " (" . $final['order_tax'] . "). " . lang("code__exist"));
								redirect("purchases/import_purchase");
							}
						}else{
							$order_tax = "";
						}

						$purchase = array(	
										'date' 			  	=> $this->cus->fsd($final['date']),
										'reference_no'      => $final['reference'],
										'supplier_id'       => $supplier->id,
										'supplier'          => $supplier->company,
										'biller_id'         => $biller_id,
										'biller'            => $biller,
										'project_id'        => $project_id,
										'warehouse_id'      => $warehouse_id,
										'order_discount_id' => $final['order_discount'],
										'status'       		=> "received",
										'payment_status'    => "pending",
										'created_by'        => $this->session->userdata('user_id'),
										'order_tax'			=> $order_tax,
										'ap_account'        => $purchaseAcc->ap_acc,		  
									);	
						$pr_discount = 0;			
						if (isset($final['discount']) && $final['discount']) {
							$discount = $final['discount'];
							$dpos = strpos($discount, '%');
							if ($dpos !== FALSE) {
								$pds = explode("%", $discount);
								$pr_discount = $this->cus->formatDecimalRaw(((($this->cus->formatDecimalRaw($final['cost'])) * (Float)($pds[0])) / 100), 11);
							} else {
								$pr_discount = $this->cus->formatDecimalRaw($discount,11);
							}
						}
						$unit_cost = $final['cost'] - $pr_discount;
						$subtotal = $unit_cost * $final['quantity'];
						$purchase_item = array(
									'product_id'      	=> $product->id,
									'product_code'    	=> $product->code,
									'product_name'    	=> $product->name,
									'product_type'    	=> $product->type,
									'net_unit_cost'  	=> $unit_cost,
									'unit_cost'      	=> $unit_cost,
									'real_unit_cost' 	=> ($unit_cost / ($unit->unit_qty > 1 ? $unit->unit_qty : 1)),
									'quantity'        	=> $final['quantity'] * ($unit->unit_qty > 1 ? $unit->unit_qty : 1),
									'product_unit_id' 	=> $unit->unit_id,
									'product_unit_code' => $unit->code,
									'unit_quantity'   	=> $final['quantity'],
									'warehouse_id'    	=> $warehouse_id,
									'discount'        	=> $final['discount'],
									'item_discount'   	=> $pr_discount * $final['quantity'],
									'subtotal'        	=> $subtotal,
									'unit_qty'			=> $unit->unit_qty
								);					
						$purchases[$index] = $purchase;
						$purchase_items[$index][] = $purchase_item;
					}
					if($purchases && $purchase_items){
						foreach($purchases as $index => $purchase){
							$total = 0;
							$product_discount = 0;
							if($purchase_items[$index]){
								foreach($purchase_items[$index] as $purchase_item){
									$total += $purchase_item['subtotal'];
									$product_discount += $purchase_item['item_discount'];
									$stockmoves[$index][] = array(
										'transaction' => 'Purchases',
										'product_id' => $purchase_item["product_id"],
										'product_type' => $purchase_item["product_type"],
										'product_code' => $purchase_item["product_code"],
										'quantity' => $purchase_item["quantity"],
										'unit_quantity' => $purchase_item["unit_qty"],
										'unit_code' => $purchase_item["product_unit_code"],
										'unit_id' => $purchase_item["product_unit_id"],
										'warehouse_id' => $warehouse_id,
										'date' => $purchase["date"],
										'real_unit_cost' => $purchase_item["real_unit_cost"],
										'reference_no' => $purchase["reference_no"],
										'user_id' => $this->session->userdata('user_id'),
									);
									
									if($this->Settings->accounting == 1){		
										$productAcc = $this->site->getProductAccByProductId($purchase_item["product_id"]);
										$accTrans[$index][] = array(
											'transaction' => 'Purchases',
											'transaction_date' => $purchase["date"],
											'reference' => $purchase["reference_no"],
											'account' => $productAcc->stock_acc,
											'amount' => ($purchase_item["real_unit_cost"] * $purchase_item["quantity"]),
											'narrative' => 'Product Code: '.$purchase_item["product_code"].'#'.'Qty: '.$purchase_item["quantity"].'#'.'Cost: '.$purchase_item["real_unit_cost"],
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'supplier_id' => $purchase["supplier_id"],
										);
									}
									
								}
								
								$order_discount = 0;
								$order_tax = 0;
								if (isset($purchase['order_discount_id']) && $purchase['order_discount_id']) {
									$ordiscount = $purchase['order_discount_id'];
									$dpos = strpos($ordiscount, '%');
									if ($dpos !== FALSE) {
										$rds = explode("%", $ordiscount);
										$order_discount = $this->cus->formatDecimalRaw(((($this->cus->formatDecimalRaw($total)) * (Float)($rds[0])) / 100), 11);
									} else {
										$order_discount = $this->cus->formatDecimalRaw($ordiscount,11);
									}
								}
								$purchase["total"] = $total;
								$purchase["product_discount"] = $product_discount;
								$purchase["order_discount"] = $order_discount;
								$purchase["total_discount"] = $product_discount + $order_discount;
								if($purchase["order_tax"]){
									$order_tax_id = $purchase["order_tax"]->id;
									if ($purchase["order_tax"]->type == 2) {
										$order_tax = $this->cus->formatDecimal($purchase["order_tax"]->rate);
									}
									if ($purchase["order_tax"]->type == 1) {
										$order_tax = $this->cus->formatDecimal(((($total - $order_discount) * $purchase["order_tax"]->rate) / 100), 4);
									}
									$purchase["order_tax_id"] = $order_tax_id;
									$purchase["order_tax"] = $order_tax;
									$purchase["total_tax"] = $order_tax;
								}
								$grand_total = $this->cus->formatDecimal(($total + $order_tax  - $order_discount), 4);
								$purchase["grand_total"] = $grand_total;
								
								
								if($this->Settings->accounting == 1){           
									$purchaseAcc = $this->site->getAccountSettingByBiller($biller_id);
									$accTrans[$index][] = array(
										'transaction' => 'Purchases',
										'transaction_date' => $purchase["date"],
										'reference' => $purchase["reference_no"],
										'account' => $purchaseAcc->ap_acc,
										'amount' => $grand_total * (-1),
										'narrative' => 'Purchase',
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'supplier_id' => $purchase["supplier_id"],
									);
									
									if($order_discount > 0){
										$accTrans[$index][] = array(
											'transaction' => 'Purchases',
											'transaction_date' => $purchase["date"],
											'reference' => $purchase["reference_no"],
											'account' => $purchaseAcc->purchase_discount_acc,
											'amount' => $order_discount * (-1),
											'narrative' => 'Order Discount',
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'supplier_id' => $purchase["supplier_id"],
										);
									}
									if($order_tax > 0){
										$accTrans[$index][] = array(
											'transaction' => 'Purchases',
											'transaction_date' => $purchase["date"],
											'reference' => $purchase["reference_no"],
											'account' => $purchaseAcc->vat_input,
											'amount' => $order_tax,
											'narrative' => 'Order Tax',
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'supplier_id' => $purchase["supplier_id"],
										);
									}
									
								}
								$purchases[$index] = $purchase;
							}
						}
					}
					
				}
			}
			if (empty($purchases) || empty($purchase_items)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            }
		}
        if ($this->form_validation->run() == true && $this->purchases_model->importPurchase($purchases,$purchase_items,$stockmoves,$accTrans)) {
            $this->session->set_flashdata('message', lang("purchase_imported"));
            redirect(site_url('purchases'));
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('import_purchase')));
			$meta = array('page_title' => lang('import_purchase'), 'bc' => $bc);
            $this->core_page('purchases/import_purchase', $meta, $this->data);
        }
    }
	
	
	
}
