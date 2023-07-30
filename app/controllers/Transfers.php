<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Transfers extends MY_Controller
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
        $this->lang->load('transfers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('transfers_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

    function index($biller_id = NULL)
    {
        $this->cus->checkPermissions();
		
        if($this->Owner || $this->Admin){
			$this->data['billers'] = $this->site->getAllBiller();
            $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		}else{
			if(!$this->session->userdata('biller_id')){
				$this->data['billers'] = $this->site->getAllBiller();
			}else{
				$this->data['billers'] = null;
			}
			$this->data['biller'] = $this->session->userdata('biller_id') ? $this->site->getCompanyByID($this->session->userdata('biller_id')) : null;
		}	
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('transfers')));
		$meta = array('page_title' => lang('transfers'), 'bc' => $bc);
        $this->core_page('transfers/index', $meta, $this->data);
    }

    function getTransfers($biller_id = NULL)
    {
        $this->cus->checkPermissions('index');

        $detail_link = anchor('transfers/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('transfer_details'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $email_link = anchor('transfers/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_transfer'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $edit_link = anchor('transfers/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_transfer'));
        $print_barcode = anchor('products/print_barcodes/?transfer=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . lang("delete_transfer") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete11' id='a__$1' href='" . site_url('transfers/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_transfer') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $print_barcode . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select("transfers.id as id, transfers.date, transfers.transfer_no, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode,  status, attachment")
            ->from('transfers')
			->join('companies', 'companies.id=transfers.biller_id', 'left')
            ->edit_column("fname", "$1 ($2)", "fname, fcode")
            ->edit_column("tname", "$1 ($2)", "tname, tcode");
		if ($biller_id) {
			$this->datatables->where("(".$this->db->dbprefix("transfers").".biller_id = ".$biller_id." OR ".$this->db->dbprefix("transfers").".to_biller_id = ".$biller_id.")");
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("(".$this->db->dbprefix("transfers").".biller_id = ".$this->session->userdata('biller_id')." OR ".$this->db->dbprefix("transfers").".to_biller_id = ".$this->session->userdata('biller_id').")");
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$this->datatables->where('(transfers.from_warehouse_id IN '.$warehouse_ids.' OR transfers.to_warehouse_id IN '.$warehouse_ids.')');
		}
        $this->datatables->add_column("Actions", $action, "id")
            ->unset_column('fcode')
            ->unset_column('tcode'); 
        echo $this->datatables->generate();
    }

    function add(){
        $this->cus->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('to_warehouse', lang("warehouse") . ' (' . lang("to") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang("warehouse") . ' (' . lang("from") . ')', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('to_biller', lang("to_biller") . ' (' . lang("to_biller") . ')', 'required|is_natural_no_zero');

        if ($this->form_validation->run()) {
			$biller_id = $this->input->post('biller');
			$fr_project = $this->input->post('project');
            $transfer_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to',$biller_id);
            if ($this->Owner || $this->Admin  || $this->cus->GP['transfers-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$to_biller = $this->input->post('to_biller');
			$to_project = $this->input->post('to_project');
            $to_warehouse = $this->input->post('to_warehouse');
            $from_warehouse = $this->input->post('from_warehouse');
            $note = $this->cus->clear_tags($this->input->post('note'));
            $status = $this->input->post('status');
            $from_warehouse_details = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_code = $from_warehouse_details->code;
            $from_warehouse_name = $from_warehouse_details->name;
            $to_warehouse_details = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_code = $to_warehouse_details->code;
            $to_warehouse_name = $to_warehouse_details->name;
			$update_serials = array();
			$accTrans = false;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product_code'][$r];
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
				if($_POST['expired_data'][$r] && $_POST['expired_data'][$r] != '00/00/0000'){
					$item_expiry = $this->cus->fsd($_POST['expired_data'][$r]);
				}else{
					$item_expiry = null;
				}
				$item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$item_serial = $_POST['serial'][$r];
			
                if (isset($item_code) && isset($item_quantity)) {
                    $product_details = $this->transfers_model->getProductByCode($item_code);
					$warehouse_quantity = $this->transfers_model->getWarehouseProduct($from_warehouse_details->id, $product_details->id, $item_option);
					
					if ($warehouse_quantity->quantity < $item_quantity) {
						$this->session->set_flashdata('error', lang("no_match_found") . " (" . lang('product_name') . " <strong>" . $product_details->name . "</strong> " . lang('product_code') . " <strong>" . $product_details->code . "</strong>)");
						redirect("transfers/add");
					}
					
					$reactive = 0;
					if($item_serial){
						$fr_product_serial = $this->transfers_model->getProductSerial($item_serial,$product_details->id,$from_warehouse);
						if($fr_product_serial){
							$to_product_serial = $this->transfers_model->getProductSerial($item_serial,$product_details->id,$to_warehouse);
							if($to_product_serial){
								if($to_product_serial->inactive==0){
									$this->session->set_flashdata('error', lang("serial_is_existed").' ('.$item_serial.') ');
									redirect($_SERVER["HTTP_REFERER"]);
								}else {
									$reactive = 1;
									$update_serials[] = array(
												'product_id' => $product_details->id,
												'warehouse_id' => $to_warehouse,
												'serial' => $item_serial,
												'date' => $date,
												'cost' => $fr_product_serial->cost,
												'color' => $fr_product_serial->color,
												'price' => $fr_product_serial->price,
												'description' => $fr_product_serial->description,
												'supplier_id' => $fr_product_serial->supplier_id,
												'supplier' => $fr_product_serial->supplier,
												);
								}
							}else{
								$product_serials[] = array(
									'product_id' => $product_details->id,
									'warehouse_id' => $to_warehouse,
									'date' => $date,
									'serial' => $item_serial,
									'cost' => $fr_product_serial->cost,
									'color' => $fr_product_serial->color,
									'price' => $fr_product_serial->price,
									'description' => $fr_product_serial->description,
									'supplier_id' => $fr_product_serial->supplier_id,
									'supplier' => $fr_product_serial->supplier,
								);
							}
						}else{
							$item_serial = '';
						}
					}
					$unit = $this->site->getProductUnit($product_details->id,$item_unit);
                    $products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $to_warehouse,
                        'expiry' => $item_expiry,
                        'date' => $date,
						'serial_no' => $item_serial
                    );
					$stockmoves[] = array(
						'transaction' => 'Transfer',
                        'product_id' => $product_details->id,
						'product_code' => $item_code,
                        'option_id' => $item_option,
                        'quantity' => $item_quantity,
                        'unit_quantity' => $unit->unit_qty,
						'unit_code' => $unit->code,
						'unit_id' => $item_unit,
                        'date' => $date,
						'expiry' => $item_expiry,
						'serial_no' => $item_serial,
						'real_unit_cost' => $product_details->cost,
						'reactive' => $reactive,
						'reference_no' => $transfer_no,
						'user_id' => $this->session->userdata('user_id'),
                    ); 
					
					if($this->Settings->accounting == 1 && ($biller_id != $to_biller || ($this->Settings->project && $to_project != $fr_project)) && $status != "pending"){
						$productAcc = $this->site->getProductAccByProductId($product_details->id);
						$accTrans[] = array(
							'transaction' => 'Transfer',
							'transaction_date' => $date,
							'reference' => $transfer_no,
							'account' => $productAcc->stock_acc,
							'amount' => ($product_details->cost * $item_quantity)  * (-1),
							'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $fr_project,
							'user_id' => $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'transaction' => 'Transfer',
							'transaction_date' => $date,
							'reference' => $transfer_no,
							'account' => $productAcc->adjustment_acc,
							'amount' => ($product_details->cost * $item_quantity),
							'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $fr_project,
							'user_id' => $this->session->userdata('user_id'),
						);
						if($status=="completed"){
							$accTrans[] = array(
								'transaction' => 'Transfer',
								'transaction_date' => $date,
								'reference' => $transfer_no,
								'account' => $productAcc->stock_acc,
								'amount' => ($product_details->cost * $item_quantity),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $to_biller,
								'project_id' => $to_project,
								'user_id' => $this->session->userdata('user_id'),
							);
							$accTrans[] = array(
								'transaction' => 'Transfer',
								'transaction_date' => $date,
								'reference' => $transfer_no,
								'account' => $productAcc->adjustment_acc,
								'amount' => ($product_details->cost * $item_quantity) * (-1),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $to_biller,
								'project_id' => $to_project,
								'user_id' => $this->session->userdata('user_id'),
							);
						}
					}
					
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
				'transfer_no' => $transfer_no,
                'date' => $date,
                'from_warehouse_id' => $from_warehouse,
                'from_warehouse_code' => $from_warehouse_code,
				'biller_id' => $biller_id,
				'to_biller_id' => $to_biller,
                'from_warehouse_name' => $from_warehouse_name,
                'to_warehouse_id' => $to_warehouse,
                'to_warehouse_code' => $to_warehouse_code,
                'to_warehouse_name' => $to_warehouse_name,
				'from_project' => $fr_project,
                'to_project' => $to_project,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'status' => $status,
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
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

        if ($this->form_validation->run() == true && $this->transfers_model->addTransfer($data, $products, $stockmoves, $product_serials, $update_serials, $accTrans)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang("transfer_added")." - ".$data['transfer_no']);
            redirect("transfers");
        } else {


            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['name'] = array('name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('name'),
            );
            $this->data['quantity'] = array('name' => 'quantity',
                'id' => 'quantity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('quantity'),
            );

            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['to_warehouses'] = $this->transfers_model->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['rnumber'] = ''; 
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['to_billers'] = $this->site->getAllBiller();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('transfers'), 'page' => lang('transfers')), array('link' => '#', 'page' => lang('add_transfer')));
			$meta = array('page_title' => lang('transfer_quantity'), 'bc' => $bc);
            $this->core_page('transfers/add', $meta, $this->data);
        }
    }

    function edit($id = NULL)
    {
        $this->cus->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $transfer = $this->transfers_model->getTransferByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->cus->view_rights($transfer->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('to_warehouse', lang("warehouse") . ' (' . lang("to") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang("warehouse") . ' (' . lang("from") . ')', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('to_biller', lang("to_biller") . ' (' . lang("to_biller") . ')', 'required|is_natural_no_zero');
        if ($this->form_validation->run()) {
			$biller_id = $this->input->post('biller');
			$fr_project = $this->input->post('project');
            $transfer_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to',$biller_id);
            if ($this->Owner || $this->Admin  || $this->cus->GP['transfers-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$to_biller = $this->input->post('to_biller');
			$to_project = $this->input->post('to_project');
            $to_warehouse = $this->input->post('to_warehouse');
            $from_warehouse = $this->input->post('from_warehouse');
            $note = $this->cus->clear_tags($this->input->post('note'));
            $status = $this->input->post('status');
            $from_warehouse_details = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_code = $from_warehouse_details->code;
            $from_warehouse_name = $from_warehouse_details->name;
            $to_warehouse_details = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_code = $to_warehouse_details->code;
            $to_warehouse_name = $to_warehouse_details->name;
			$accTrans = false;
			$product_serials = array();
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product_code'][$r];
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
				if($_POST['expired_data'][$r] && $_POST['expired_data'][$r] != '00/00/0000'){
					$item_expiry = $this->cus->fsd($_POST['expired_data'][$r]);
				}else{
					$item_expiry = null;
				}
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$item_serial = $_POST['serial'][$r];
                if (isset($item_code) && isset($item_quantity)) {
					$product_details = $this->transfers_model->getProductByCode($item_code);
                    $unit = $this->site->getProductUnit($product_details->id,$item_unit);
					
					$reactive = 0;
					if($item_serial){
						$fr_product_serial = $this->transfers_model->getProductSerial($item_serial,$product_details->id,$from_warehouse);
						if($fr_product_serial){
							$to_product_serial = $this->transfers_model->getProductSerial($item_serial,$product_details->id,$to_warehouse, $id);
							if($to_product_serial){
								if($to_product_serial->inactive==0){
									if($this->transfers_model->getTransferItemSerial($product_details->id,$id,$item_serial)){
										$reactive = 1;
									}else{
										$this->session->set_flashdata('error', lang("serial_is_existed").' ('.$item_serial.') ');
										redirect($_SERVER["HTTP_REFERER"]);
									}

								}else {
									$reactive = 1;
								}
							}else{
								$product_serials[] = array(
									'product_id' => $product_details->id,
									'warehouse_id' => $to_warehouse,
									'date' => $date,
									'serial' => $item_serial,
									'cost' => $fr_product_serial->cost,
									'color' => $fr_product_serial->color,
									'price' => $fr_product_serial->price,
									'supplier_id' => $fr_product_serial->supplier_id,
									'supplier' => $fr_product_serial->supplier,
								);
							}
						}else{
							$item_serial = '';
						}
					}
					
					
                    $products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $to_warehouse,
                        'expiry' => $item_expiry,
                        'date' => date('Y-m-d', strtotime($date)),
						'serial_no' => $item_serial,
						
                    );
					$stockmoves[] = array(
						'transaction' => 'Transfer',
                        'product_id' => $product_details->id,
						'product_code' => $item_code,
                        'option_id' => $item_option,
                        'quantity' => $item_quantity,
                        'unit_quantity' => $unit->unit_qty,
						'unit_code' => $unit->code,
						'unit_id' => $item_unit,
                        'date' => $date,
						'expiry' => $item_expiry,
						'serial_no' => $item_serial,
						'real_unit_cost' => $product_details->cost,
						'reactive' => $reactive,
						'reference_no' => $transfer_no,
						'user_id' => $this->session->userdata('user_id'),
                    );
					if($this->Settings->accounting == 1 && ($biller_id != $to_biller || ($this->Settings->project && $to_project != $fr_project)) && $status != "pending"){	
						$productAcc = $this->site->getProductAccByProductId($product_details->id);
						$accTrans[] = array(
							'transaction_id' => $id,
							'transaction' => 'Transfer',
							'transaction_date' => $date,
							'reference' => $transfer_no,
							'account' => $productAcc->stock_acc,
							'amount' => ($product_details->cost * $item_quantity)  * (-1),
							'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $fr_project,
							'user_id' => $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'transaction_id' => $id,
							'transaction' => 'Transfer',
							'transaction_date' => $date,
							'reference' => $transfer_no,
							'account' => $productAcc->adjustment_acc,
							'amount' => ($product_details->cost * $item_quantity),
							'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $fr_project,
							'user_id' => $this->session->userdata('user_id'),
						);
						if($status=="completed"){
							$accTrans[] = array(
								'transaction_id' => $id,
								'transaction' => 'Transfer',
								'transaction_date' => $date,
								'reference' => $transfer_no,
								'account' => $productAcc->stock_acc,
								'amount' => ($product_details->cost * $item_quantity),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $to_biller,
								'project_id' => $to_project,
								'user_id' => $this->session->userdata('user_id'),
							);
							$accTrans[] = array(
								'transaction_id' => $id,
								'transaction' => 'Transfer',
								'transaction_date' => $date,
								'reference' => $transfer_no,
								'account' => $productAcc->adjustment_acc,
								'amount' => ($product_details->cost * $item_quantity) * (-1),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $to_biller,
								'project_id' => $to_project,
								'user_id' => $this->session->userdata('user_id'),
							);
						}
					}
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
						'transfer_no' => $transfer_no,
						'biller_id' => $biller_id,
						'to_biller_id' => $to_biller,
						'date' => $date,
						'from_warehouse_id' => $from_warehouse,
						'from_warehouse_code' => $from_warehouse_code,
						'from_warehouse_name' => $from_warehouse_name,
						'to_warehouse_id' => $to_warehouse,
						'to_warehouse_code' => $to_warehouse_code,
						'to_warehouse_name' => $to_warehouse_name,
						'from_project' => $fr_project,
						'to_project' => $to_project,
						'note' => $note,
						'updated_by' => $this->session->userdata('user_id'),
						'updated_at' => date('Y-m-d H:i:s'),
						'status' => $status
					);

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
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

        if ($this->form_validation->run() == true && $this->transfers_model->updateTransfer($id, $data, $products, $stockmoves, $product_serials, $accTrans)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang("transfer_updated")." - ".$data['transfer_no']);
            redirect("transfers");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['transfer'] = $this->transfers_model->getTransferByID($id);
            $transfer_items = $this->transfers_model->getAllTransferItems($id);
            if($transfer_items){
				krsort($transfer_items);
				$c = rand(100000, 9999999);
				foreach ($transfer_items as $item) {
					$row = $this->site->getProductByID($item->product_id);
					if (!$row) {
						$row = json_decode('{}');
					} else {
						unset($row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
					}
					
					if($this->Settings->product_expiry == '1'){
						$product_expiries = $this->site->getProductExpiredByProductID($row->id, $this->data['transfer']->from_warehouse_id, 'Transfer', $id);
					}else{
						$product_expiries = false;
					}
					$product_qty = $this->transfers_model->getProductQuantity($item->product_id,$this->data['transfer']->from_warehouse_id);
					$row->expired = $this->cus->hrsd($item->expiry);
					$row->base_quantity = $item->quantity;
					$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
					$row->unit = $item->product_unit_id;
					$row->qty = $item->unit_quantity;
					$row->quantity_balance = $product_qty['quantity'];
					$row->ordered_quantity = $item->quantity;
					$row->quantity = $product_qty['quantity'];
					$row->option = $item->option_id;
					$row->serial = $item->serial_no;
					$product_serials = $this->transfers_model->getActiveProductSerialID($row->id,$this->data['transfer']->from_warehouse_id, $item->serial_no);
				   
					$options = $this->site->getProductOptions($row->id);
					
					if ($options) {
						foreach ($options as $option) {
							$ops = $this->site->getWarehouseProductsOptionByID($row->id,$this->data['transfer']->from_warehouse_id,$option->id);
							$option->quantity = $ops->quantity;
							if($option->id == $item->option_id){
								$option->quantity += $item->quantity;
							}
						}
					}
					
					$row->quantity += $item->quantity;
					
					$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
					$ri = $this->Settings->item_addition ? $row->id : $c;
									
					$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
						'row' => $row,  'units' => $units, 'options' => $options,'product_serials' => $product_serials, 'product_expiries'=> $product_expiries);
					$c++;
				}
				$this->data['transfer_items'] = json_encode($pr);
			}
			

            $this->data['id'] = $id;
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['to_warehouses'] = $this->transfers_model->getAllWarehouses();
			$this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->data['to_billers'] = $this->site->getAllBiller();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('transfers'), 'page' => lang('transfers')), array('link' => '#', 'page' => lang('edit_transfer')));
			$meta = array('page_title' => lang('edit_transfer_quantity'), 'bc' => $bc);
            $this->core_page('transfers/edit', $meta, $this->data);
        }
    }

    function transfer_by_csv()
    {
        $this->cus->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('to_warehouse', lang("warehouse") . ' (' . lang("to") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang("warehouse") . ' (' . lang("from") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run()) {

            $transfer_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to');
            if ($this->Owner || $this->Admin  || $this->cus->GP['transfers-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $to_warehouse = $this->input->post('to_warehouse');
            $from_warehouse = $this->input->post('from_warehouse');
            $note = $this->cus->clear_tags($this->input->post('note'));
            $shipping = $this->input->post('shipping');
            $status = $this->input->post('status');
            $from_warehouse_details = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_code = $from_warehouse_details->code;
            $from_warehouse_name = $from_warehouse_details->name;
            $to_warehouse_details = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_code = $to_warehouse_details->code;
            $to_warehouse_name = $to_warehouse_details->name;

            $total = 0;
            $product_tax = 0;

            if (isset($_FILES["userfile"])) {

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;

                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {

                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("transfers/transfer_bt_csv");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('product', 'net_cost', 'quantity', 'variant', 'expiry');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                $rw = 2;
                foreach ($final as $csv_pr) {

                    $item_code = $csv_pr['product'];
                    $item_net_cost = $csv_pr['net_cost'];
                    $item_quantity = $csv_pr['quantity'];
                    $variant = isset($csv_pr['variant']) ? $csv_pr['variant'] : NULL;
                    $item_expiry = isset($csv_pr['expiry']) ? $this->cus->fsd($csv_pr['expiry']) : NULL;

                    if (isset($item_code) && isset($item_net_cost) && isset($item_quantity)) {
                        if (!($product_details = $this->transfers_model->getProductByCode($item_code))) {
                            $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $csv_pr['product'] . " ). " . lang("line_no") . " " . $rw);
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                        if ($variant) {
                            $item_option = $this->transfers_model->getProductVariantByName($variant, $product_details->id);
                            if (!$item_option) {
                                $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $csv_pr['product'] . " - " . $csv_pr['variant'] . " ). " . lang("line_no") . " " . $rw);
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        } else {
                            $item_option = json_decode('{}');
                            $item_option->id = NULL;
                        }

                        if (!$this->Settings->overselling) {
                            $warehouse_quantity = $this->transfers_model->getWarehouseProduct($from_warehouse_details->id, $product_details->id, $item_option->id);
                            if ($warehouse_quantity->quantity < $item_quantity) {
                                $this->session->set_flashdata('error', lang("no_match_found") . " (" . lang('product_name') . " <strong>" . $product_details->name . "</strong> " . lang('product_code') . " <strong>" . $product_details->code . "</strong>) " . lang("line_no") . " " . $rw);
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        }
                        if (isset($product_details->tax_rate)) {
                            $pr_tax = $product_details->tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);

                            if ($tax_details->type == 1 && $tax_details->rate != 0) {
                                $item_tax = ((($item_quantity * $item_net_cost) * $tax_details->rate) / 100);
                                $product_tax += $item_tax;
                            } else {
                                $item_tax = $tax_details->rate;
                                $product_tax += $item_tax;
                            }

                            if ($tax_details->type == 1)
                                $tax = $tax_details->rate . "%";
                            else
                                $tax = $tax_details->rate;
                        } else {
                            $pr_tax = 0;
                            $item_tax = 0;
                            $tax = "";
                        }

                        $subtotal = (($item_net_cost * $item_quantity) + $item_tax);

                        $products[] = array(
                            'product_id' => $product_details->id,
                            'product_code' => $item_code,
                            'product_name' => $product_details->name,
                            'option_id' => $item_option->id,
                            'net_unit_cost' => $item_net_cost,
                            'quantity' => $item_quantity,
                            'quantity_balance' => $item_quantity,
                            'item_tax' => $item_tax,
                            'tax_rate_id' => $pr_tax,
                            'tax' => $tax,
                            'expiry' => $item_expiry,
                            'subtotal' => $subtotal,
                            'real_unit_cost' => $this->cus->formatDecimal($item_net_cost+($item_tax/$item_quantity))
                        );

                        $total += $item_net_cost * $item_quantity;
                    }
                    $rw++;
                }
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_item"), 'required');
            } else {
                krsort($products);
            }
            $grand_total = $total + $shipping + $product_tax;
            $data = array('transfer_no' => $transfer_no,
                'date' => $date,
                'from_warehouse_id' => $from_warehouse,
                'from_warehouse_code' => $from_warehouse_code,
                'from_warehouse_name' => $from_warehouse_name,
                'to_warehouse_id' => $to_warehouse,
                'to_warehouse_code' => $to_warehouse_code,
                'to_warehouse_name' => $to_warehouse_name,
                'note' => $note,
                'total_tax' => $product_tax,
                'total' => $total,
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'status' => $status,
                'shipping' => $shipping
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
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

        if ($this->form_validation->run() == true && $this->transfers_model->addTransfer($data, $products)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang("transfer_added"));
            redirect("transfers");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['name'] = array('name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('name'),
            );
            $this->data['quantity'] = array('name' => 'quantity',
                'id' => 'quantity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('quantity'),
            );

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['rnumber'] = $this->site->getReference('to');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('transfers'), 'page' => lang('transfers')), array('link' => '#', 'page' => lang('transfer_by_csv')));
            $meta = array('page_title' => lang('add_transfer_by_csv'), 'bc' => $bc);
            $this->core_page('transfers/transfer_by_csv', $meta, $this->data);
        }
    }

    function view($transfer_id = NULL)
    {
        $this->cus->checkPermissions('index', TRUE);

        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer = $this->transfers_model->getTransferByID($transfer_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($transfer->created_by, true);
        }
        $this->data['rows'] = $this->transfers_model->getAllTransferItems($transfer_id);
        $this->data['from_warehouse'] = $this->site->getWarehouseByID($transfer->from_warehouse_id);
        $this->data['to_warehouse'] = $this->site->getWarehouseByID($transfer->to_warehouse_id);
        $this->data['transfer'] = $transfer;
		$this->data['biller'] = $this->site->getCompanyByID($transfer->biller_id);
        $this->data['tid'] = $transfer_id;
        $this->data['created_by'] = $this->site->getUser($transfer->created_by);
		$this->data['fr_project'] = ($transfer->from_project ? $this->site->getProjectByID($transfer->from_project) : false);
		$this->data['to_project'] = ($transfer->to_project ? $this->site->getProjectByID($transfer->to_project) : false);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Transfer',$transfer->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Transfer',$transfer->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'transfers/view', $this->data);
    }

    function pdf($transfer_id = NULL, $view = NULL, $save_bufffer = NULL)
    {
        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer = $this->transfers_model->getTransferByID($transfer_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($transfer->created_by);
        }
        $this->data['rows'] = $this->transfers_model->getAllTransferItems($transfer_id);
        $this->data['from_warehouse'] = $this->site->getWarehouseByID($transfer->from_warehouse_id);
        $this->data['to_warehouse'] = $this->site->getWarehouseByID($transfer->to_warehouse_id);
        $this->data['transfer'] = $transfer;
        $this->data['tid'] = $transfer_id;
        $this->data['created_by'] = $this->site->getUser($transfer->created_by);
        $name = lang("transfer") . "_" . str_replace('/', '_', $transfer->transfer_no) . ".pdf";
        $html = $this->load->view($this->theme . 'transfers/pdf', $this->data, TRUE);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'transfers/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->cus->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->cus->generate_pdf($html, $name);
        }

    }

    public function combine_pdf($transfers_id)
    {
        $this->cus->checkPermissions('pdf');

        foreach ($transfers_id as $transfer_id) {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $transfer = $this->transfers_model->getTransferByID($transfer_id);
            if (!$this->session->userdata('view_right')) {
                $this->cus->view_rights($transfer->created_by);
            }
            $this->data['rows'] = $this->transfers_model->getAllTransferItems($transfer_id);
            $this->data['from_warehouse'] = $this->site->getWarehouseByID($transfer->from_warehouse_id);
            $this->data['to_warehouse'] = $this->site->getWarehouseByID($transfer->to_warehouse_id);
            $this->data['transfer'] = $transfer;
            $this->data['tid'] = $transfer_id;
            $this->data['created_by'] = $this->site->getUser($transfer->created_by);

            $html[] = array(
                'content' => $this->load->view($this->theme . 'transfers/pdf', $this->data, TRUE),
                'footer' => '',
            );
        }

        $name = lang("transfers") . ".pdf";
        $this->cus->generate_pdf($html, $name);

    }

    function email($transfer_id = NULL)
    {
        $this->cus->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }
        $transfer = $this->transfers_model->getTransferByID($transfer_id);
        $this->form_validation->set_rules('to', lang("to") . " " . lang("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', lang("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->cus->view_rights($transfer->created_by);
            }
            $to = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = NULL;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = NULL;
            }

            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $transfer->transfer_no,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            //$name = lang("transfer") . "_" . str_replace('/', '_', $transfer->transfer_no) . ".pdf";
            //$file_content = $this->pdf($transfer_id, NULL, 'S');
            //$attachment = array('file' => $file_content, 'name' => $name, 'mime' => 'application/pdf');
            $attachment = $this->pdf($transfer_id, NULL, 'S'); //delete_files($attachment);
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }


        if ($this->form_validation->run() == true && $this->cus->send_email($to, $subject, $message, NULL, NULL, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->session->set_flashdata('message', lang("email_sent"));
            redirect("transfers");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/transfer.html')) {
                $transfer_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/transfer.html');
            } else {
                $transfer_temp = file_get_contents('./themes/default/views/email_templates/transfer.html');
            }
            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('transfer_order').' (' . $transfer->transfer_no . ') '.lang('from').' ' . $transfer->from_warehouse_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $transfer_temp),
            );
            $this->data['warehouse'] = $this->site->getWarehouseByID($transfer->to_warehouse_id);

            $this->data['id'] = $transfer_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'transfers/email', $this->data);

        }
    }

    function delete($id = NULL)
    {
        $this->cus->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		
		$row = $this->transfers_model->getTransferByID($id);
        if ($this->transfers_model->deleteTransfer($id)) {
            if($this->input->is_ajax_request()) {
                echo lang("transfer_deleted"); die();
            }
            $this->session->set_flashdata('message', lang('transfer_deleted')." - ".$row->transfer_no);
            redirect('transfers');
        }
    }

    function suggestions()
    {
        //$this->cus->checkPermissions('index', TRUE);
        $term = $this->input->get('term', TRUE);
        $warehouse_id = $this->input->get('warehouse_id', TRUE);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->transfers_model->getProductNames($sr, $warehouse_id);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
				$product_qty = $this->transfers_model->getProductQuantity($row->id,$warehouse_id);
				$option = FALSE;
                $row->quantity = 0;
                $row->item_tax_method = $row->tax_method;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_cost = $row->cost;
                $row->unit = isset($row->purchase_unit) ? $row->purchase_unit : $row->unit;
                $row->qty = 1;
                $row->discount = '0';
                $row->expiry = '';
				$row->quantity += $product_qty['quantity'];
                $row->quantity_balance = 0;
                $row->ordered_quantity = 0;
                if(!isset($row->serial)){
                    $row->serial = '';
                }

                $options = $this->transfers_model->getProductOptions($row->id, $warehouse_id);
				if ($options) {
                    $opt = $option_id && $r == 0 ? $this->transfers_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = FALSE;
                }
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
				
				if($this->Settings->product_expiry == '1'){
					$product_expiries = $this->site->getProductExpiredByProductID($row->id, $warehouse_id);
					if($product_expiries){
						foreach($product_expiries as $product_expirie){
							if($product_expirie->quantity > 0){
								$row->expired = $product_expirie->expiry;
								break; 
							}
						}
					}
				}else{
					$product_expiries = false;
				}
				$row->option = $option_id;
                $row->real_unit_cost = $row->cost;
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				$product_serials = $this->transfers_model->getActiveProductSerialID($row->id, $warehouse_id);
 
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'product_serials'=>$product_serials, 'product_expiries' => $product_expiries);
                $r++;
            }
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    function transfer_actions()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$transfer_details = "";
                    foreach ($_POST['val'] as $id) {
						$row = $this->transfers_model->getTransferByID($id);
						$transfer_details .="(" . $row->transfer_no . "),";
                        $this->transfers_model->deleteTransfer($id);
                    }
                    $this->session->set_flashdata('message', lang("transfers_deleted")." - ". $transfer_details);
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('transfers'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('from_warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('to_warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $tansfer = $this->transfers_model->getTransferByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($tansfer->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $tansfer->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $tansfer->from_warehouse);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $tansfer->to_warehouse);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $tansfer->grand_total);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $tansfer->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'tansfers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_transfer_selected"));
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

        if ($this->form_validation->run() == true && $this->transfers_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {

            $this->data['inv'] = $this->transfers_model->getTransferByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'transfers/update_status', $this->data);

        }
    }

	public function get_to_project()
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
		$opt = form_dropdown('to_project', $pl, (isset($_POST['to_project']) ? $_POST['to_project'] : $project_id), 'id="to_project" class="form-control"');
		echo json_encode(array("result" => $opt));
	}

}
