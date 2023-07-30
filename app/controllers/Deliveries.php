<?php defined('BASEPATH') or exit('No direct script access allowed');

class Deliveries extends MY_Controller
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
        $this->lang->load('deliveries', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('sale_order_model');
		$this->load->model('sales_model');
		$this->load->model('deliveries_model');
        $this->digital_upload_path = 'files/';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;

    }

	public function index($biller_id = null)
	{
	    if(!isset($this->GP['sales-deliveries']) && (!$this->Admin && !$this->Owner)){
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }  
		$this->data['billers'] = $this->site->getBillers();
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;	
        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('deliveries')));
        $meta = array('page_title' => lang('deliveries'), 'bc' => $bc);
        $this->core_page('deliveries/index', $meta, $this->data);
	}
	
	public function getDeliveries($biller_id = null)
    {
        if(!isset($this->GP['sales-deliveries']) && (!$this->Admin && !$this->Owner)){
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }
        $detail_link = anchor('deliveries/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_details'), 'data-toggle="modal" data-target="#myModal"');
        $convert_link = anchor('sales/add/$1/2', '<i class="fa fa-heart"></i> ' . lang('create_sale'), ' class="create_sale" ');  
		//$edit_link = anchor('deliveries/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_delivery'), ' class="edit_delivery"');
        $delete_link = "<a href='#' class='po delete_delivery' title='<b>" . lang("delete_delivery") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('deliveries/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_delivery') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
				. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
				. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_link . '</li>
				<li>' . $convert_link . '</li>
				<li>' . $delete_link . '</li>
			</ul>
		</div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select("deliveries.id as id, deliveries.date, do_reference_no, sale_reference_no, so_reference_no,  deliveries.customer, users.username, deliveries.status, deliveries.attachment")
            ->from('deliveries')
			->join('users','users.id=delivered_by','left')
            ->group_by('deliveries.id');
		
		
		if($this->input->get("status")){
			$this->datatables->where("deliveries.status", trim($this->input->get("status")));
		}else if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('deliveries.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('deliveries.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if ($biller_id) {
            $this->datatables->where('deliveries.biller_id', $biller_id);
        }			
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('deliveries.biller_id =', $this->session->userdata('biller_id'));
		}
		
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function delete($id = null)
    {
        if(!isset($this->GP['sales-delete_delivery']) && (!$this->Admin && !$this->Owner)){
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->deliveries_model->deleteDelivery($id)) {
            echo lang("delivery_deleted");
        }
    }
	
    public function add($so_id = null, $inv_id = null)
    {
        if(!isset($this->GP['sales-add_delivery']) && (!$this->Admin && !$this->Owner)){
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }
		if($so_id || $inv_id){
			if($inv_id){
				$sale = $this->sales_model->getInvoiceByID($inv_id);
				$project_id = $sale->project_id;
				$warehouse_id = $sale->warehouse_id;
			}else{
				$sale_order = $this->sale_order_model->getSaleOrderByID($so_id);
				$project_id = $sale_order->project_id;
				$warehouse_id = $sale_order->warehouse_id;
			}
		}
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->GP['sales-date_delivery']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			
			$so_id = $this->input->post('sale_order_id');
			$inv_id = $this->input->post('sale_id');
			
			if($inv_id){
				$sale = $this->sales_model->getInvoiceByID($inv_id);
				$project_id = $sale->project_id;
			}else{
				$sale_order = $this->sale_order_model->getSaleOrderByID($so_id);
				$project_id = $sale_order->project_id;
			}
			$warehouse_id = $sale->warehouse_id ? $sale->warehouse_id : $sale_order->warehouse_id;
			
			$customer_id = $this->input->post('customer');
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
			
			$biller_id = $sale->biller_id ? $sale->biller_id : $sale_order->biller_id;
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$reference_no = $this->input->post('do_reference_no') ? $this->input->post('do_reference_no') : $this->site->getReference('do',$biller_id);
			$i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            $percentage = '%';
			for ($r = 0; $r < $i; $r++) {
				$item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['unit'][$r];
                $item_quantity = $_POST['base_quantity'][$r];
				$parent_id = $_POST['parent_id'][$r];
				$item_serial = isset($_POST['serial_no'][$r]) ? $_POST['serial_no'][$r] : '';

				if($_POST['expired_data'][$r] && $_POST['expired_data'][$r] != '00/00/0000'){
					$expired_data = $this->cus->fsd($_POST['expired_data'][$r]);
				}else{
					$expired_data = null;
				}
				
                if (isset($item_code)) {
                    $product_details = $item_type != 'manual' ? $this->sale_order_model->getProductByCode($item_code) : null;
                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->cus->formatDecimal(((($this->cus->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->cus->formatDecimal($discount);
                        }
                    }
                    $unit_price = $this->cus->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->cus->formatDecimal($pr_discount * $item_unit_quantity);
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
                                $item_tax = $this->cus->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->cus->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }
                            $item_tax = $this->cus->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->cus->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
					$unit = $this->site->getProductUnit($product_details->id, $item_unit);
                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->cus->formatDecimal($item_net_price + $item_tax),
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
                        'subtotal' => $this->cus->formatDecimal($subtotal),
                        'real_unit_price' => $real_unit_price,
						'parent_id' => $parent_id,
						'expiry' => $expired_data,
						'serial_no' => $item_serial
                    );
					
					if($this->Settings->foc == 1){
						$sale_qty = $_POST['sale_qty'][$r];
						if($item_quantity <= $sale_qty){
							$products[$r]['foc_qty'] = 0;
						}else{
							$products[$r]['foc_qty'] = $item_quantity - $sale_qty;
						}
					}
					
                }
					
				if($so_id || $sale->stock_deduction==0){
					
					if($product_details->type=='bom'){
						$product_boms = $this->sales_model->getBomProductByStandProduct($item_id);
						if($product_boms){
							$product_bom_cost = 0;
							foreach($product_boms as $product_bom){
								if($this->Settings->accounting_method == '0'){
									$costs = $this->site->getFifoCost($product_bom->product_id,($item_quantity * $product_bom->quantity),$stockmoves);
								}else if($this->Settings->accounting_method == '1'){
									$costs = $this->site->getLifoCost($product_bom->product_id,($item_quantity * $product_bom->quantity),$stockmoves);
								}else if($this->Settings->accounting_method == '3'){
									$costs = $this->site->getProductMethod($product_bom->product_id,($item_quantity * $product_bom->quantity),$stockmoves);
								}
								if($costs){
									$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
									$item_cost_qty  = 0;
									$item_cost_total = 0;
									$item_costs = '';
									foreach($costs as $cost_item){
										$item_cost_qty += $cost_item['quantity'];
										$item_cost_total += $cost_item['cost'] * $cost_item['quantity'];
										
										$stockmoves[] = array(
											'transaction' => 'Delivery',
											'product_id' => $product_bom->product_id,
											'product_type'    => $product_bom->product_type,
											'product_code' => $product_bom->product_code,
											'quantity' => $cost_item['quantity'] * (-1),
											'unit_quantity' => $product_bom->unit_qty,
											'unit_code' => $product_bom->code,
											'unit_id' => $product_bom->unit_id,
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $cost_item['cost'],
											'reference_no' => $reference_no,
											'user_id' => $this->session->userdata('user_id'),
											'expiry' => $expired_data
										);
										//========accounting=========//
											if($this->Settings->accounting == 1 && $product_bom->product_type){		
												$accTrans[] = array(
													'transaction' => 'Delivery',
													'transaction_date' => $date,
													'reference' => $reference_no,
													'account' => $productAcc->stock_acc,
													'amount' => -($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
				
												);
												$accTrans[] = array(
													'transaction' => 'Delivery',
													'transaction_date' => $date,
													'reference' => $reference_no,
													'account' => $productAcc->cost_acc,
													'amount' => ($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
											}
										//============end accounting=======//
										$item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
									}
									$product_bom_cost += ($item_cost_total / $item_cost_qty);
								}else{
									$product_bom_cost += ($product_bom->quantity * $product_bom->cost);
									$stockmoves[] = array(
												'transaction' => 'Delivery',
												'product_id' => $product_bom->product_id,
												'product_type'    => $product_bom->product_type,
												'product_code' => $product_bom->product_code,
												'quantity' => ($item_quantity * $product_bom->quantity) * -1,
												'unit_quantity' => $product_bom->unit_qty,
												'unit_code' => $product_bom->code,
												'unit_id' => $product_bom->unit_id,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $product_bom->cost,
												'reference_no' => $reference_no,
												'user_id' => $this->session->userdata('user_id'),
												'expiry' => $expired_data
											);
									//=======accounting=========//
										if($this->Settings->accounting == 1 && $product_bom->product_type != 'manual'){	
											$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
											$accTrans[] = array(
												'transaction' => 'Delivery',
												'transaction_date' => $date,
												'reference' => $reference_no,
												'account' => $productAcc->stock_acc,
												'amount' => -($product_bom->cost * ($item_quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.($item_quantity * $product_bom->quantity).'#'.'Cost: '.$product_bom->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											$accTrans[] = array(
												'transaction' => 'Delivery',
												'transaction_date' => $date,
												'reference' => $reference_no,
												'account' => $productAcc->cost_acc,
												'amount' => ($product_bom->cost * ($item_quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.($item_quantity * $product_bom->quantity).'#'.'Cost: '.$product_bom->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
										}
									//============end accounting=======//		
								}
								$raw_materials[] = array(
									"product_id" => $product_bom->product_id,
									"quantity" => ($item_quantity * $product_bom->quantity)
								);
							}
							$cost  = $product_bom_cost;
						}else{
							$error = lang('please_check_product').' '.$item_code;
							$this->session->set_flashdata('error', $error);
							redirect($_SERVER["HTTP_REFERER"]);
						}
					}else{
						if($this->Settings->accounting_method == '0'){
							$costs = $this->site->getFifoCost($item_id,$item_quantity,$stockmoves);
						}else if($this->Settings->accounting_method == '1'){
							$costs = $this->site->getLifoCost($item_id,$item_quantity,$stockmoves);
						}else if($this->Settings->accounting_method == '3'){
							$costs = $this->site->getProductMethod($item_id,$item_quantity,$stockmoves);
						}
						
						if($costs && $item_serial==''){
							$productAcc = $this->site->getProductAccByProductId($item_id);
							$item_cost_qty  = 0;
							$item_cost_total = 0;
							$item_costs = '';
							foreach($costs as $cost_item){
								$item_cost_qty += $cost_item['quantity'];
								$item_cost_total += $cost_item['cost'] * $cost_item['quantity'];
			
								$stockmoves[] = array(
									'transaction' => 'Delivery',
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'quantity' => $cost_item['quantity'] * (-1),
									'unit_quantity' => $unit->unit_qty,
									'unit_code' => $unit->code,
									'unit_id' => $item_unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => $cost_item['cost'],
									'serial_no' => $item_serial,
									'reference_no' => $reference_no,
									'user_id' => $this->session->userdata('user_id'),
									'expiry' => $expired_data
								);
								//========accounting=========//
									if($this->Settings->accounting == 1 && $item_type != 'manual'){	
										$accTrans[] = array(
											'transaction' => 'Delivery',
											'transaction_date' => $date,
											'reference' => $reference_no,
											'account' => $productAcc->stock_acc,
											'amount' => -($cost_item['cost'] * $cost_item['quantity']),
											'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
										$accTrans[] = array(
											'transaction' => 'Delivery',
											'transaction_date' => $date,
											'reference' => $reference_no,
											'account' => $productAcc->cost_acc,
											'amount' => ($cost_item['cost'] * $cost_item['quantity']),
											'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
									}
								//============end accounting=======//
								$item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
							}
							$cost = $item_cost_total / $item_cost_qty;
							
						}else{
							if($item_serial!=""){
								$item_serials = explode("#",$item_serial);
								if(count($item_serials) > 0){
									for($b = 0; $b<= count($item_serials); $b++){
										if($item_serials[$b]!=''){
											if($product_serial_detail = $this->sales_model->getProductSerial($item_serials[$b],$item_id,$warehouse_id)){
												$product_details->cost = $product_serial_detail->cost;
											}
											$stockmoves[] = array(
												'transaction' => 'Delivery',
												'product_id' => $item_id,
												'product_code' => $item_code,
												'product_type' => $item_type,
												'option_id' => $item_option,
												'quantity' => (-1),
												'unit_quantity' => $unit->unit_qty,
												'unit_code' => $unit->code,
												'expiry' => $expired_data,
												'unit_id' => $item_unit,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $product_details->cost,
												'serial_no' => $item_serials[$b],
												'reference_no' => $reference_no,
												'user_id' => $this->session->userdata('user_id'),
												'expiry' => $expired_data
											);
										}
									}
								}else{
									if($product_serial_detail = $this->sales_model->getProductSerial($item_serial,$item_id,$warehouse_id)){
										$product_details->cost = $product_serial_detail->cost;
									}
									$cost = $product_details->cost;
									$stockmoves[] = array(
										'transaction' => 'Delivery',
										'product_id' => $item_id,
										'product_code' => $item_code,
										'product_type' => $item_type,
										'option_id' => $item_option,
										'quantity' => $item_quantity * (-1),
										'unit_quantity' => $unit->unit_qty,
										'unit_code' => $unit->code,
										'unit_id' => $item_unit,
										'warehouse_id' => $warehouse_id,
										'date' => $date,
										'real_unit_cost' => $cost,
										'serial_no' => $item_serial,
										'reference_no' => $reference_no,
										'user_id' => $this->session->userdata('user_id'),
										'expiry' => $expired_data
									);
								}
								
							}else{
								$cost = $product_details->cost;
								$stockmoves[] = array(
									'transaction' => 'Delivery',
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'quantity' => $item_quantity * (-1),
									'unit_quantity' => $unit->unit_qty,
									'unit_code' => $unit->code,
									'unit_id' => $item_unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => $cost,
									'serial_no' => $item_serial,
									'reference_no' => $reference_no,
									'user_id' => $this->session->userdata('user_id'),
									'expiry' => $expired_data
								);
							}
							//========accounting=========//
								$productAcc = $this->site->getProductAccByProductId($item_id);
								if($this->Settings->accounting == 1 && $item_type != 'manual'){		
									$accTrans[] = array(
										'transaction' => 'Delivery',
										'transaction_date' => $date,
										'reference' => $reference_no,
										'account' => $productAcc->stock_acc,
										'amount' => -($cost * $item_quantity),
										'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
									$accTrans[] = array(
										'transaction' => 'Delivery',
										'transaction_date' => $date,
										'reference' => $reference_no,
										'account' => $productAcc->cost_acc,
										'amount' => ($cost * $item_quantity),
										'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
								}
							//============end accounting=======//
						}
						
					}
				}
			}
			
			$dlDetails = array(
				'date' => $date,
				'biller_id' => $biller_details->id,
				'biller' => $biller,
				'customer_id' => $customer_id,
				'customer' => $customer,
				'warehouse_id' => $warehouse_id,
				'sale_id' => $sale->id,
				'sale_order_id' => $sale_order->id,
				'do_reference_no' => $reference_no,
				'sale_reference_no' => $sale->reference_no,
				'so_reference_no' => $sale_order->reference_no,
				'address' => $this->input->post('address'),
				'delivered_by' => $this->input->post('delivered_by'),
				'received_by' => $this->input->post('received_by'),
				'note' => $this->cus->clear_tags($this->input->post('note')),
				'created_by' => $this->session->userdata('user_id'),
			);
			
			if($so_id){
				$dlDetails['status'] = "pending";
			}
			if($inv_id){
				$dlDetails['status'] = "completed";
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
				$dlDetails['attachment'] = $photo;
			}
			if($this->Settings->product_expiry == '1' && $stockmoves && $products){
				$checkExpiry = $this->site->checkExpiry($stockmoves, $products,'Delivery');
				$stockmoves = $checkExpiry['expiry_stockmoves'];
				$products = $checkExpiry['expiry_items'];
			}
        }

        if ($this->form_validation->run() == true && $this->deliveries_model->addDelivery($dlDetails, $products, $stockmoves, $accTrans)) {
            $this->session->set_flashdata('message', $this->lang->line("delivery_added"));
			$this->session->set_userdata('remove_dols', 1);
            redirect('deliveries');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			if($inv_id || $so_id){
				if($so_id > 0){
					$this->data['inv'] = $this->sale_order_model->getSaleOrderByID($so_id);
					$inv_items = $this->deliveries_model->getAllSOItemsWithDeliveries($so_id);
				} elseif ($inv_id > 0) {
					$this->data['inv'] = $this->sales_model->getInvoiceByID($inv_id);
					$inv_items = $this->deliveries_model->getAllInvoiceItemsWithDeliveries($inv_id);
				}
				
				if($this->data['inv']->delivery_status == "completed"){
					$this->session->set_flashdata('error', $this->lang->line("delivery_already_added"));
					redirect($_SERVER['HTTP_REFERER']);
				}

				krsort($inv_items);
				$c = rand(100000, 9999999);
				foreach ($inv_items as $item) {
					$row = $this->site->getProductByID($item->product_id);
					if (!$row) {
						$row = json_decode('{}');
						$row->tax_method = 0;
					} else {
						unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
					}

					if($so_id > 0){
						$row->quantity = 0;
						$pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id);
						if ($pis) {
							foreach ($pis as $pi) {
								$row->quantity += $pi->quantity_balance;
							}
						}
						//$sale_order = $this->sales_model->getSaleOrderByApproval($row->id, $so_id);
						//$row->quantity -= $sale_order->quantity;
						
					}else if($inv_id > 0){
						$row->quantity = ($item->quantity + $item->foc);
					}
					if($item->delivered_quantity == ""){
						$item->delivered_quantity = 0;
					}
					$row->balanace_qty = (($item->quantity + $item->foc) - $item->delivered_quantity);
					$row->balance_unit_qty = $this->cus->convertQty($item->product_id,$row->balanace_qty);
					$row->squantity = $this->cus->convertQty($item->product_id,($item->quantity + $item->foc));
					$row->dquantity = $this->cus->convertQty($item->product_id,$item->delivered_quantity);
					$row->qty = $row->balanace_qty;
					$convert_unit = false;
					if($row->unit != $item->product_unit_id){
						$convert_unit = $this->cus->convertUnit($item->product_id,$row->balanace_qty);
						$row->qty = $convert_unit['quantity'];
					}

					if(($row->balanace_qty - $item->foc) > 0){
						$row->sale_qty = $row->balanace_qty - $item->foc;
					}else{
						$row->sale_qty = 0;
					}

					$row->id = $item->product_id;
					$row->code = $item->product_code;
					$row->name = $item->product_name;
					$row->type = $item->product_type;
					$row->base_unit = $row->unit;
					$row->base_unit_price = $row->price ? $row->price : $item->real_unit_price;
					$row->unit = ($convert_unit ?  $convert_unit['unit_id'] : $row->unit);
					$row->discount = $item->discount ? $item->discount : '0';
					$row->price = $this->cus->formatDecimal($item->net_unit_price + $this->cus->formatDecimal($item->item_discount / $item->quantity));
					$row->cost = isset($row->cost)? $row->cost: 0;
					$row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimal($item->item_discount / $item->quantity) + $this->cus->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
					$row->real_unit_price = $item->real_unit_price;
					$row->tax_rate = $item->tax_rate_id;
					$row->serial = $item->serial_no;
					$row->option = $item->option_id;
					$row->swidth = $item->width;
					$row->sheight = $item->height;
					$row->square = $item->square;
					$row->unit_name = ($this->site->getUnitByID($item->product_unit_id) ? $this->site->getUnitByID($item->product_unit_id)->name : '');
					
					if($so_id > 0){
						$options = $this->sale_order_model->getProductOptions($row->id, $item->warehouse_id);
					}else if($inv_id > 0){
						$options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
					}
					if ($options) {
						$option_quantity = 0;
						foreach ($options as $option) {
							$pis = $this->site->getStockmoves($row->id, $item->warehouse_id, $item->option_id);
							if ($pis) {
								foreach ($pis as $pi) {
									$option_quantity += $pi->quantity_balance;
								}
							}
						}
					}

					if($this->Settings->product_expiry == '1'){
						if($so_id > 0 || $sale->stock_deduction==0){
							$product_expiries = $this->site->getProductExpiredByProductID($row->id, $item->warehouse_id);
							foreach($product_expiries as $product_expirie){
								if(isset($item->expiry) && $item->expiry !='' && $item->expiry != '0000-00-00'){
									$row->expired = $this->cus->hrsd($item->expiry);
									break;
								}else if($product_expirie->quantity > 0){
									$row->expired = $product_expirie->expiry;
									break; 
								}
							}
						}else{
							$s_expiry['quantity'] = $item->quantity;
							$s_expiry['expiry'] = $this->cus->hrsd($item->expiry);
							$product_expiries[$this->cus->hrsd($item->expiry)] = (object) $s_expiry;
						}
						
					}else{
						$product_expiries = false;
					}

					$combo_items = false;
					if ($row->type == 'combo') {
						if($so_id > 0){
							$combo_items = $this->sale_order_model->getProductComboItems($row->id, $item->warehouse_id);
						}else if($inv_id > 0){
							$combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
						}
					}
					$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
					$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
					$ri = $this->Settings->item_addition ? $row->id : $c;
					$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'product_expiries' => $product_expiries);
					$c++;
					
				}
				$this->data['inv_items'] = json_encode($pr);
				$this->data['customer'] = $this->site->getCompanyByID($this->data['inv']->customer_id);
				$this->data['sale_id'] = $inv_id;
				$this->data['sale_order_id'] = $so_id;
			}	
			$this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->data['allUsers'] = $this->site->getAllUsers();
			$this->data['saleorders'] = $this->site->getRefSaleOrders('approved');
			$this->data['sales'] = $this->site->getRefSales('completed');
			$this->data['do_reference_no'] = '';
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('deliveries'), 'page' => lang('deliveries')), array('link' => '#', 'page' => lang('add_delivery')));
            $meta = array('page_title' => lang('add_delivery'), 'bc' => $bc);
            $this->core_page('deliveries/add', $meta, $this->data);
        }
    }
	
	public function edit($id = null)
    {
        if(!$this->GP['sales-edit_delivery'] && (!$this->Admin && !$this->Owner)){
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {
			
			$delivery_id = $this->input->post('delivery_id');
			$customer_id = $this->input->post('customer');
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
			$i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
				$item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['base_quantity'][$r];
				$parent_id = $_POST['parent_id'][$r];
                if (isset($item_code)) {
                    $product_details = $item_type != 'manual' ? $this->sale_order_model->getProductByCode($item_code) : null;
                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->cus->formatDecimal(((($this->cus->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->cus->formatDecimal($discount);
                        }
                    }
                    $unit_price = $this->cus->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->cus->formatDecimal($pr_discount * $item_unit_quantity);
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
                                $item_tax = $this->cus->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->cus->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }
                            $item_tax = $this->cus->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->cus->formatDecimal($item_tax * $item_unit_quantity, 4);
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
                        'unit_price' => $this->cus->formatDecimal($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->cus->formatDecimal($subtotal),
                        'real_unit_price' => $real_unit_price,
						'parent_id' => $parent_id
                    );
                }
				
				$delivery = $this->deliveries_model->getDeliveryByID($id);
				if($delivery->sale_order_id > 0){
					// If from so deduct stock
					$stockmoves[] = array(
						'transaction' => 'Delivery',
                        'product_id' => $item_id,
						'product_code' => $item_code,
						'product_type' => $item_type,
                        'option_id' => $item_option,
                        'quantity' => $item_quantity * (-1),
                        'unit_quantity' => $unit->unit_qty,
						'unit_code' => $unit->code,
						'unit_id' => $item_unit,
                        'warehouse_id' => $delivery->warehouse_id,
                        'date' => $date,
						'real_unit_cost' => $product_details->cost,
						'reference_no' => $delivery->do_reference_no,
						'user_id' => $this->session->userdata('user_id'),
                    );
				}
			}
			
			$dlDetails = array(
				'date' => $date,
				'customer_id' => $customer_id,
				'customer' => $customer,
				'address' => $this->input->post('address'),
				'delivered_by' => $this->input->post('delivered_by'),
				'received_by' => $this->input->post('received_by'),
				'note' => $this->cus->clear_tags($this->input->post('note')),
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
				$dlDetails['attachment'] = $photo;
			}
			
        }
        if ($this->form_validation->run() == true && $this->deliveries_model->updateDelivery($delivery_id, $dlDetails, $products, $stockmoves)) {
            $this->session->set_flashdata('message', $this->lang->line("delivery_updated"));
            redirect('deliveries');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $this->deliveries_model->getDeliveryByID($id);
            $inv_items = $this->deliveries_model->getAllDeliveryItems($id);
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
				if($item->sale_order_id){
					$pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id);
					if ($pis) {
						foreach ($pis as $pi) {
							$row->quantity += $pi->quantity_balance;
						}
					}
				}
				
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
				$row->dquantity = $item->unit_quantity;
				$row->quantity = $item->quantity;
				$row->qty = $item->unit_quantity;
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                $row->unit = $item->product_unit_id;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $this->cus->formatDecimal($item->net_unit_price + $this->cus->formatDecimal($item->item_discount / $item->quantity));
                $row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimal($item->item_discount / $item->quantity) + $this->cus->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate = $item->tax_rate_id;
                $row->option = $item->option_id;
				$row->parent_id = $item->parent_id;
				$row->swidth = $item->width;
				$row->sheight = $item->height;
				$row->square = $item->square;
				
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
				if($row->qty > 0){
					$ri = $this->Settings->item_addition ? $row->id : $c;
					$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
					$c++;
				}
            }
			$this->data['id'] = $id;
            $this->data['inv_items'] = json_encode($pr);
            $this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->data['allUsers'] = $this->site->getAllUsers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
            $this->data['warehouses'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllWarehouses() : null;
            $this->data['do_reference_no'] = '';
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('deliveries'), 'page' => lang('deliveries')), array('link' => '#', 'page' => lang('edit_delivery')));
			$meta = array('page_title' => lang('edit_delivery'), 'bc' => $bc);
            $this->core_page('deliveries/edit', $meta, $this->data);
        }
    }
	
	public function view($id = null, $type = null)
    {
        if(!isset($this->GP['sales-deliveries']) && (!$this->Admin && !$this->Owner)){
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->deliveries_model->getDeliveryByID($id);
        $this->data['delivery'] = $deli;
        $this->data['biller'] = $this->site->getCompanyByID($deli->biller_id);
        $this->data['rows'] = $this->deliveries_model->getAllDeliveryItems($id);
        $this->data['user'] = $this->site->getUser($deli->created_by);
        $this->data['page_title'] = lang("delivery_order");
		if($this->Owner || $this->Admin || $this->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Delivery',$deli->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Delivery',$deli->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		if($type=="small"){
			$this->load->view($this->theme . 'deliveries/view_small', $this->data);
		}else{
			$this->load->view($this->theme . 'deliveries/view', $this->data);
		}
        
    }
	
	public function delivery_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete_delivery');
                    foreach ($_POST['val'] as $id) {
                        $this->deliveries_model->deleteDelivery($id);
                    }
                    $this->session->set_flashdata('message', lang("deliveries_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
				
				if ($this->input->post('form_action') == 'create_sale') {
					$ids = array(); 
					$warehouse_id = 0;
                    foreach ($_POST['val'] as $id) {
                       $row = $this->deliveries_model->getDeliveryByID($id);
					   if($row->sale_id){
						   $this->session->set_flashdata('error', lang("cannot_delivery"));
						   redirect($_SERVER["HTTP_REFERER"]);
					   }
					   $ids[] = $id;
                    }
					redirect('sales/add?groups_delivery='.json_encode($ids));
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('deliveries'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('do_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('so_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $delivery = $this->deliveries_model->getDeliveryByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($delivery->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $delivery->do_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, ($delivery->sale_reference_no));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, ($delivery->so_reference_no));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $delivery->customer);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $delivery->address);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($delivery->status));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);

                    $filename = 'deliveries_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_delivery_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
}
