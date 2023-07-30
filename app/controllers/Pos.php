<?php defined('BASEPATH') or exit('No direct script access allowed');

class Pos extends MY_Controller
{
    public function __construct()
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

        $this->load->model('pos_model');
		$this->load->model('products_model');
        $this->load->helper('text');
        $this->pos_settings = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : NULL;
        $this->data['pos_settings'] = $this->pos_settings;
        $this->session->set_userdata('last_activity', now());
        $this->lang->load('pos', $this->Settings->user_language);
        $this->load->library('form_validation');
    }

    public function sales($warehouse_id = NULL,$biller_id = null, $payment_status = null)
    {
        $this->cus->checkPermissions('index');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getAllBiller();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')),  array('link' => '#', 'page' => lang('pos_sales')));
		$meta = array('page_title' => lang('pos_sales'), 'bc' => $bc);
        $this->core_page('pos/sales', $meta, $this->data);
    }
	
	public function getSales($warehouse_id = null ,$biller_id = null, $payment_status = null)
    {
        $this->cus->checkPermissions('index');
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		$packaging_link =''; 
		$undo_packaging_link =''; 
		$add_delivery_link ='';
		if($this->pos_settings->pos_delivery==1){
			$packaging_link = "<a href='#' class='po packaging' title='<b>" . lang("packaging_sale") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('pos/packaging/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa fa-check\"></i> "
			. lang('packaging_sale') . "</a>";
			$undo_packaging_link = "<a href='#' class='po undo_packaging' title='<b>" . lang("undo_packaging_sale") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('pos/undo_packaging/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa fa-times\"></i> "
			. lang('undo_packaging_sale') . "</a>";
			$add_delivery_link = anchor('deliveries/add/0/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), ' class="add_delivery" ');
		}
		
        $detail_link = anchor('pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $duplicate_link = anchor('sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));		
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . "</a>";
		
		$action = '<div class="text-center"><div class="btn-group text-left">'
		. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
		. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_link . '</li>
				<li>' . $payments_link . '</li>			
				<li>' . $add_payment_link . '</li>
				<li>' . $add_delivery_link . '</li>
				<li>' . $packaging_link . '</li>
				<li>' . $undo_packaging_link . '</li>
				<li>' . $edit_link . '</li>
				<li>' . $return_link . '</li>
				<li>' . $delete_link . '</li>				
			</ul>
		</div></div>';
		
        $this->load->library('datatables');
		$this->datatables
			->select("sales.id as id, 
			DATE_FORMAT(date, '%Y-%m-%d %T') as date, 
			reference_no, 
			customer,
			vehicle_model,
			vehicle_plate,
			vehicle_vin_no,
			mechanic,
			IFNULL(cus_suspended_tables.name,'') as table_name,
			grand_total,
			IFNULL(total_return,0),
			IFNULL(cus_payments.paid + IFNULL(total_return_paid,0),0) as paid, 
			IFNULL(cus_payments.discount,0) as discount, 
			ROUND((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),2) as balance,			
			IF (
				(
					round((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),2) = 0
				),
				'paid',
				IF (
				(
					(grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))) = grand_total
				),
				'pending',
				'partial'
			)) AS payment_status,
			delivery_status
			")
			->from('sales')
			->join('(SELECT
						sum(abs(grand_total)) AS total_return,
						sum(paid) AS total_return_paid,
						sale_id
					FROM
						'.$this->db->dbprefix('sales').'
					WHERE sale_status = "returned"
					GROUP BY
						sale_id) as cus_return', 'cus_return.sale_id=sales.id', 'left')
			->join('(SELECT
						sale_id,
						IFNULL(sum(amount),0) AS paid,
						IFNULL(sum(discount),0) AS discount
					FROM
						'.$this->db->dbprefix('payments').'
					GROUP BY
						sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left');
        
		$this->datatables->join('suspended_tables', 'suspended_tables.id=sales.table_id', 'left');
		
		if ($warehouse_id && $warehouse_id != '0') {
            $this->datatables->where('sales.warehouse_id', $warehouse_id);
        }
		if ($biller_id) {
            $this->datatables->where('sales.biller_id', $biller_id);
         }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
			$this->datatables->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if ($payment_status=='due') {
			$this->datatables->where('DATE_ADD('.$this->db->dbprefix("sales").'.`date`, INTERVAL payment_term DAY) <=', date('Y-m-d'));
			$this->datatables->where('sales.payment_status !=', 'paid');
			$this->datatables->where('sales.payment_term >', 0);
		}
        $this->datatables->where('pos', 1);
		$this->datatables->where('sale_status !=', 'draft');
		$this->datatables->where('sale_status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function index($sid = NULL)
    {	
        $this->cus->checkPermissions();
        if (!$this->pos_settings->default_biller || !$this->pos_settings->default_customer || !$this->pos_settings->default_category) {
            $this->session->set_flashdata('warning', lang('please_update_settings'));
            redirect('pos/settings');
        }
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = array('register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date);
            $this->session->set_userdata($register_data);
        } else {
            $this->session->set_flashdata('error', lang('register_not_open'));
            redirect('pos/open_register');
        }

        $this->data['sid'] = $this->input->get('suspend_id') ? $this->input->get('suspend_id') : $sid;
        $did = $this->input->post('delete_id') ? $this->input->post('delete_id') : NULL;
        $suspend = $this->input->post('suspend') ? TRUE : FALSE;
        $count = $this->input->post('count') ? $this->input->post('count') : NULL;

        //validate form input
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'trim|required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
	
        if ($this->form_validation->run() == TRUE) {
            if ($this->pos_settings->quick_pos != 1 && ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) && $this->input->post('date')) {
				$date = $this->cus->fld($this->input->post('date'),1);
				$date = $date.date(':s');
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            $total_items = $this->input->post('total_items');
            $sale_status = 'completed';
            $payment_status = 'due';
            $payment_term = 0;
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            
			if($customer_details->credit_day > 0 || $customer_details->credit_amount > 0){
				if($customer_details->credit_day > 0){
					$credit_balance = $this->pos_model->getCreditLimit($customer_id,$customer_details->credit_day);
					if($credit_balance->balance > 0){
						$this->session->set_flashdata('error', lang('customer_have_over_credit_day'));
						$this->cus->md();
					}
				}
				if($customer_details->credit_amount > 0){
					$credit_balance = $this->pos_model->getCreditLimit($customer_id);
					if($credit_balance->balance >= $customer_details->credit_amount){
						$this->session->set_flashdata('error', lang('customer_have_over_credit_amount'));
						$this->cus->md();
					}
				}	
			}
			
			$customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->cus->clear_tags($this->input->post('pos_note'));
            $staff_note = $this->cus->clear_tags($this->input->post('staff_note'));
			$delivery_status = $this->input->post('delivery_status');

			$reference = "";
			if (isset($_POST['submit-sale']) || !$suspend) {
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pos',$biller_id);
			}
			$saleman = $this->site->getUser($this->input->post('saleman_id'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $digital = FALSE;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_service_types = "ABC";
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : NULL;
                $real_unit_price = $this->cus->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$suspend_item_id = $_POST['suspend_item_id'][$r];
				$item_ordered = $_POST['item_ordered'][$r];
				$ordered_by = $_POST['ordered_by'][$r];
				$return_qty = $_POST['return_qty'][$r];
				$product_cost = $_POST['cost'][$r];
				$currency_code =  isset($_POST['currency_code'][$r]) ? $_POST['currency_code'][$r] : NULL;
				$currency_rate = isset($_POST['currency_rate'][$r]) ? $_POST['currency_rate'][$r] : NULL;
				$bom_type = "";
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
					if ($item_type == 'manual') {
						$add_product = $_POST['add_product'][$r];
						if($add_product==1){
							if($this->site->getProductByCode($item_code)){
								$item_code = rand(10000000,99999999);
							};
							$addProduct = array(
									'code' => $item_code,
									'barcode_symbology' => 'code128',
									'name' => $item_name,
									'type' => 'standard',
									'category_id' => $this->Settings->manual_category,
									'cost' => $product_cost,
									'price' => $unit_price,
									'unit' => $this->Settings->manual_unit,
									'sale_unit' => $this->Settings->manual_unit,
									'purchase_unit' => $this->Settings->manual_unit,
									'alert_quantity' => 0,
									'manual_product' => 1
								);
							$item_id = $this->pos_model->addProduct($addProduct);	
							$item_type = 'standard';
							$item_unit = $this->Settings->manual_unit;
						}
                        $item_quantity = $item_unit_quantity;
                    }
					$product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : NULL;
                    if(!$product_details){
						$product_details->cost = $product_cost;
					}
					$pr_discount = 0;
                    if ($item_type == 'digital') {
                        $digital = TRUE;
                    }

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== FALSE) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->cus->formatDecimalRaw(((($this->cus->formatDecimalRaw($unit_price)) * (Float)($pds[0])) / 100), 11);
                        } else {
                            $pr_discount = $this->cus->formatDecimalRaw($discount,11);
                        }
                    }
					
					if($this->Settings->product_serial == 1 && $item_serial==''){
						$qty_warehouse = $this->pos_model->getProductQuantity($item_id,$warehouse_id);
						$qty_serial = $this->pos_model->getProductSerialQuantity($item_id,$warehouse_id);
						$avalible_qty = $qty_warehouse['quantity'] - $qty_serial['serial_qty'];
	
						if($avalible_qty < $item_quantity && $qty_serial['serial_qty'] > 0){
							$this->session->set_flashdata('error', lang("product_serial_is_required"));
                            redirect($_SERVER["HTTP_REFERER"]);
						}
					}
					
                    $unit_price = $this->cus->formatDecimalRaw($unit_price - $pr_discount);
					// Product Currency
					if($this->config->item('product_currency')==true){
						if($currency_code && $currency_rate){
							$real_unit_price = $real_unit_price / $currency_rate;
							$unit_price = $unit_price / $currency_rate;
							$pr_discount = $pr_discount / $currency_rate;
							if ($dpos !== false) {
								 $item_discount = $item_discount;
							}else{
								 $item_discount = $pr_discount;
							}
						}
					}
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->cus->formatDecimalRaw($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";
                    $service_types_opt = $product_details->service_types;
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
                        $pr_item_tax = $this->cus->formatDecimal(($item_tax * $item_unit_quantity), 4);
                    }
					
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getProductUnit($item_id,$item_unit);
                    
                   
					
					if(!empty($product_cost)){
						$product_details->cost = $product_cost;
					}
					
					$raw_materials = array();
					$combo_products = json_decode($_POST['product_combo'][$r]);
					
					if($product_details->type=='combo' && $combo_products){
						$price_combo = 0;
						$qty_combo = count($combo_products);
						$dicount = 0;
						foreach($combo_products as $combo_product){
							$price_combo += $combo_product->price * $combo_product->qty;
						}
						if($this->cus->formatDecimal($price_combo) <> $this->cus->formatDecimal($item_net_price)){
							$dicount = (($price_combo - $item_net_price) * 100) / $price_combo;
						}
						$product_combo_cost = 0;
						foreach($combo_products as $combo_product){
							$combo_id = $combo_product->id;
							$combo_code = $combo_product->code;
							$combo_name = $combo_product->name;
							$combo_qty = $combo_product->qty;
							$combo_price = $combo_product->price;
							
							if($dicount > 0){
								$combo_price = $combo_price - (($combo_price * $dicount) / 100);
							}else if($dicount < 0){
								$combo_price = $combo_price + (($combo_price * abs($dicount)) / 100);
							}
							
							if($price_combo==0 && $item_net_price > 0){
								$combo_price = $item_net_price / $qty_combo;
							}
							
							$combo_detail = $this->site->getProductByID($combo_id);
							if($combo_detail){
								$combo_unit = $this->site->getProductUnit($combo_id, $combo_detail->unit);
								if($this->Settings->accounting_method == '0'){
									$costs = $this->site->getFifoCost($combo_id,($item_quantity * $combo_qty),$stockmoves);
								}else if($this->Settings->accounting_method == '1'){
									$costs = $this->site->getLifoCost($combo_id,($item_quantity * $combo_qty),$stockmoves);
								}else if($this->Settings->accounting_method == '3'){
									$costs = $this->site->getProductMethod($combo_id($item_quantity * $combo_qty),$stockmoves);
								}

								if($costs){
									$productAcc = $this->site->getProductAccByProductId($combo_id);
									$item_cost_qty  = 0;
									$item_cost_total = 0;
									$item_costs = '';
									foreach($costs as $cost_item){
										$item_cost_qty += $cost_item['quantity'];
										$item_cost_total += $cost_item['cost'] * $cost_item['quantity'];

										$stockmoves[] = array(
											'transaction' => 'Sale',
											'product_id' => $combo_detail->id,
											'product_type'    => $combo_detail->type,
											'product_code' => $combo_detail->code,
											'quantity' => $cost_item['quantity'] * (-1),
											'expiry' => $expired_data,
											'unit_quantity' => $combo_unit->unit_qty,
											'unit_code' => $combo_unit->code,
											'unit_id' => $combo_detail->unit,
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $cost_item['cost'],
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);
										//========accounting=========//
											if($this->Settings->accounting == 1 &&  $sale_status=='completed'){
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,

												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
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
									
									$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->sale_acc,
												'amount' => -($combo_price * $combo_qty),
												'narrative' => 'Sale',
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
									
									$product_combo_cost += ($item_cost_total / $item_cost_qty);
								}else{
									$product_combo_cost += ($combo_qty * $combo_detail->cost);
									$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $combo_detail->id,
												'product_type'    => $combo_detail->type,
												'product_code' => $combo_detail->code,
												'quantity' => ($item_quantity * $combo_qty) * -1,
												'unit_quantity' => $combo_unit->unit_qty,
												'expiry' => $expired_data,
												'unit_code' => $combo_unit->code,
												'unit_id' => $combo_detail->unit,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $combo_detail->cost,
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
									//=======accounting=========//
										$productAcc = $this->site->getProductAccByProductId($combo_detail->id);
										if($this->Settings->accounting == 1 &&  $sale_status=='completed'){
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->stock_acc,
												'amount' => -($combo_detail->cost * ($item_quantity * $combo_qty)),
												'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.($item_quantity * $combo_qty).'#'.'Cost: '.$combo_detail->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->cost_acc,
												'amount' => ($combo_detail->cost * ($item_quantity * $combo_qty)),
												'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.($item_quantity * $combo_qty).'#'.'Cost: '.$combo_detail->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											

											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->sale_acc,
												'amount' => -($combo_price * $combo_qty),
												'narrative' => 'Sale',
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
									"product_id" => $combo_detail->id,
									"quantity" => ($item_quantity * $combo_qty)
								);
							}
							
						}
						$product_details->cost  = $product_combo_cost;	

					}else if($product_details->type=='bom'){
                        $bom_type = $_POST['bom_type'][$r];
						$product_boms = $this->pos_model->getBomProductByStandProduct($item_id,$bom_type);
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
											'transaction' => 'Sale',
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
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);
										//========accounting=========//
											if($this->Settings->accounting == 1 && $product_bom->product_type){		
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
				
												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
												);
											}
										//============end accounting=======//
										$item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
									}
									$product_bom_cost += ($item_cost_total / $item_cost_qty);
								}else{
									$product_bom_cost += ($product_bom->quantity * $product_bom->cost);
									$stockmoves[] = array(
												'transaction' => 'Sale',
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
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
									//=======accounting=========//
										if($this->Settings->accounting == 1 && $product_bom->product_type != 'manual'){		
											$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->stock_acc,
												'amount' => -($product_bom->cost * ($item_quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.($item_quantity * $product_bom->quantity).'#'.'Cost: '.$product_bom->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
											);
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->cost_acc,
												'amount' => ($product_bom->cost * ($item_quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.($item_quantity * $product_bom->quantity).'#'.'Cost: '.$product_bom->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
											);
										}
									//============end accounting=======//		
								}
								$raw_materials[] = array(
									"product_id" => $product_bom->product_id,
									"quantity" => ($item_quantity * $product_bom->quantity)
								);
							}
							$product_details->cost  = $product_bom_cost;
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
						}else{
							$costs = false;
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
									'transaction' => 'Sale',
									'product_id' => $item_id,
									'product_type'    => $item_type,
									'product_code' => $item_code,
									'option_id' => $item_option,
									'quantity' => $cost_item['quantity'] * (-1),
									'unit_quantity' => $unit->unit_qty,
									'unit_code' => $unit->code,
									'unit_id' => $item_unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => $cost_item['cost'],
									'serial_no' => $item_serial,
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);
								//========accounting=========//
									if($this->Settings->accounting == 1 && $item_type != 'manual'){		
										$accTrans[] = array(
											'transaction' => 'Sale',
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->stock_acc,
											'amount' => -($cost_item['cost'] * $cost_item['quantity']),
											'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
		
										);
										$accTrans[] = array(
											'transaction' => 'Sale',
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->cost_acc,
											'amount' => ($cost_item['cost'] * $cost_item['quantity']),
											'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
										);
									}
								//============end accounting=======//
								$item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
							}
							$product_details->cost = $item_cost_total / $item_cost_qty;
							
						}else{
							$item_costs = '';
							if($item_serial!=''){
								$item_serials = explode("#",$item_serial);
								if(count($item_serials) > 0){
									for($b = 0; $b<= count($item_serials); $b++){
										if($item_serials[$b]!=''){
											if($product_serial_detail = $this->pos_model->getProductSerial($item_serials[$b],$item_id,$warehouse_id)){
												$product_details->cost = $product_serial_detail->cost;
											}
											$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $item_id,
												'product_type'    => $item_type,
												'product_code' => $item_code,
												'option_id' => $item_option,
												'quantity' => (-1),
												'unit_quantity' => $unit->unit_qty,
												'unit_code' => $unit->code,
												'unit_id' => $item_unit,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $product_details->cost,
												'serial_no' => $item_serials[$b],
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
										}
									}
								}else{
									if($product_serial_detail = $this->pos_model->getProductSerial($item_serial,$item_id,$warehouse_id)){
										$product_details->cost = $product_serial_detail->cost;
									}
									$stockmoves[] = array(
										'transaction' => 'Sale',
										'product_id' => $item_id,
										'product_type'    => $item_type,
										'product_code' => $item_code,
										'option_id' => $item_option,
										'quantity' => (-1),
										'unit_quantity' => $unit->unit_qty,
										'unit_code' => $unit->code,
										'unit_id' => $item_unit,
										'warehouse_id' => $warehouse_id,
										'date' => $date,
										'real_unit_cost' => $product_details->cost,
										'serial_no' => $item_serial,
										'reference_no' => $reference,
										'user_id' => $this->session->userdata('user_id'),
									);
								}
							}else{
								$stockmoves[] = array(
									'transaction' => 'Sale',
									'product_id' => $item_id,
									'product_type'    => $item_type,
									'product_code' => $item_code,
									'option_id' => $item_option,
									'quantity' => $item_quantity * (-1),
									'unit_quantity' => $unit->unit_qty,
									'unit_code' => $unit->code,
									'unit_id' => $item_unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => $product_details->cost,
									'serial_no' => $item_serial,
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);
							}
							//=======accounting=========//
								if($this->Settings->accounting == 1 && $item_type != 'manual'){		
									$productAcc = $this->site->getProductAccByProductId($item_id);
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->stock_acc,
										'amount' => -($product_details->cost * $item_quantity),
										'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
									);
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->cost_acc,
										'amount' => ($product_details->cost * $item_quantity),
										'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
									);
								}
							//============end accounting=======//
						}
						
					}
					
					if($item_serial!=''){
						$item_serials = explode("#",$item_serial);
						for($b = 0; $b<= count($item_serials); $b++){
							if($item_serials[$b]!=''){
								if($product_serial_detail = $this->pos_model->getProductSerial($item_serials[$b],$item_id,$warehouse_id)){
									$product_details->cost = $product_serial_detail->cost;
								}
								
								$products[] = array(
												'product_id'      => $item_id,
												'product_code'    => $item_code,
												'service_types'   => $service_types_opt,
												'product_name'    => $item_name,
												'product_type'    => $item_type,
												'option_id'       => $item_option,
												'net_unit_price'  => $item_net_price,
												'unit_price'      => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
												'quantity'        => 1,
												'product_unit_id' => $item_unit,
												'product_unit_code' => $unit ? $unit->code : NULL,
												'unit_quantity'   => 1,
												'warehouse_id'    => $warehouse_id,
												'item_tax'        => $pr_item_tax,
												'tax_rate_id'     => $pr_tax,
												'tax'             => $tax,
												'discount'        => $item_discount,
												'item_discount'   => $pr_item_discount,
												'subtotal'        => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
												'serial_no'       => $item_serials[$b],
												'real_unit_price' => $real_unit_price,
												'comment'         => $item_comment,
												'suspend_item_id' => $suspend_item_id,
												'ordered' => $item_ordered,
												'ordered_by' 	  => ($ordered_by > 0?$ordered_by:$this->session->userdata("user_id")),
												'cost' => $product_details->cost,
												'return_quantity' => $return_qty,
												'item_costs' => $item_costs,
												'raw_materials' =>json_encode($raw_materials),
												'combo_product' => json_encode($combo_products),
                                                'bom_type' => $bom_type,
												'currency_rate' => $currency_rate,
												'currency_code' => $currency_code
											);
							}
						}
					}else{
								
						$products[] = array(
							'product_id'      => $item_id,
							'product_code'    => $item_code,
							'product_name'    => $item_name,
							'product_type'    => $item_type,
							'service_types'   => $service_types_opt,
							'option_id'       => $item_option,
							'net_unit_price'  => $item_net_price,
							'unit_price'      => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
							'quantity'        => $item_quantity,
							'product_unit_id' => $item_unit,
							'product_unit_code' => $unit ? $unit->code : NULL,
							'unit_quantity' => $item_unit_quantity,
							'warehouse_id'    => $warehouse_id,
							'item_tax'        => $pr_item_tax,
							'tax_rate_id'     => $pr_tax,
							'tax'             => $tax,
							'discount'        => $item_discount,
							'item_discount'   => $pr_item_discount,
							'subtotal'        => $this->cus->formatDecimalRaw($subtotal),
							'serial_no'       => $item_serial,
							'real_unit_price' => $real_unit_price,
							'comment'         => $item_comment,
							'suspend_item_id' => $suspend_item_id,
							'ordered' => $item_ordered,
							'ordered_by' 	  => ($ordered_by > 0?$ordered_by:$this->session->userdata("user_id")),
							'cost' => $product_details->cost,
							'return_quantity' => $return_qty,
							'item_costs' => $item_costs,
							'raw_materials' =>json_encode($raw_materials),
							'combo_product' => json_encode($combo_products),
                            'bom_type' => $bom_type,
							'currency_rate' => $currency_rate,
							'currency_code' => $currency_code
						);

					}


					if($this->config->item('product_promotions')){
						$product_promotions = json_decode($_POST['product_promotion'][$r]);
						if($product_promotions){
							foreach($product_promotions as $product_promotion){
								$extra_details = $this->site->getProductByID($product_promotion->product_id);
								if($extra_details){
									$extraUnit = $this->site->getProductUnit($extra_details->id,$extra_details->unit);
									$extractProductID = $extra_details->id;
									$extractQuantity = $extraUnit->unit_qty * $product_promotion->product_quantity;

									if($this->Settings->accounting_method == '0'){
										$extraCosts = $this->site->getFifoCost($extractProductID,$extractQuantity,$stockmoves);
									}else if($this->Settings->accounting_method == '1'){
										$extraCosts = $this->site->getLifoCost($extractProductID,$extractQuantity,$stockmoves);
									}else if($this->Settings->accounting_method == '3'){
										$extraCosts = $this->site->getProductMethod($extractProductID,$extractQuantity,$stockmoves);
									}

									if($extraCosts){
										$productAcc = $this->site->getProductAccByProductId($extractProductID);
										$item_cost_qty  = 0;
										$item_cost_total = 0;
										foreach($extraCosts as $extraCost){
											$item_cost_qty += $extraCost['quantity'];
											$item_cost_total += $extraCost['cost'] * $extraCost['quantity'];
											$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $extractProductID,
												'product_code' => $extra_details->code,
												'product_type' => $extra_details->type,
												'option_id' => 0,
												'quantity' => $extraCost['quantity'] * (-1),
												'unit_quantity' => $extraUnit->unit_qty,
												'unit_code' => $extraUnit->code,
												'unit_id' => $extra_details->unit,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $extraCost['cost'],
												'serial_no' => '',
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
											//========accounting=========//
												if($this->Settings->accounting == 1){
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->stock_acc,
														'amount' => -($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->cost_acc,
														'amount' => ($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
												}
											//============end accounting=======//

										}
										$extra_details->cost = $item_cost_total / $item_cost_qty;
									}else{
										$stockmoves[] = array(
											'transaction' => 'Sale',
											'product_id' => $extractProductID,
											'product_code' => $extra_details->code,
											'product_type' => $extra_details->type,
											'option_id' => 0,
											'quantity' => $extractQuantity * (-1),
											'unit_quantity' => $extraUnit->unit_qty,
											'unit_code' => $extraUnit->code,
											'unit_id' => $extra_details->unit,
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $extra_details->cost,
											'serial_no' => '',
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);

										//========accounting=========//
											if($this->Settings->accounting == 1){
												$productAcc = $this->site->getProductAccByProductId($extractProductID);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
											}
										//============end accounting=======//
									}
									
									$products[] = array(
										'product_id' => $extra_details->id,
										'product_code' => $extra_details->code,
										'product_name' => $extra_details->name,
										'product_type' => $extra_details->type,
										'net_unit_price' => 0,
										'unit_price' => 0,
										'serial_no' => '',
										'cost' => $extra_details->cost,
										'quantity' => $extractQuantity,
										'product_unit_id' => $extra_details->unit,
										'product_unit_code' => $extraUnit->code,
										'unit_quantity' => $extractQuantity,
										'warehouse_id' => $warehouse_id,
										'subtotal' => 0,
										'real_unit_price' => 0
									);
								}	
							}
						}
					}
					
					
					if($this->Settings->product_additional == 1){

						$products[$r]['pro_additionals'] = $_POST['product_additional'][$r];
						if($_POST['product_additional'][$r] != ''){
							$extraProducts = $this->pos_model->getProductAdditionalByID($_POST['product_additional'][$r], $item_unit_quantity);
						}else{
							$extraProducts = false;
						}

						if($extraProducts){
							$products[$r]['extract_product'] = json_encode($extraProducts);
							$extractCost = 0;
							foreach($extraProducts as $extraProduct){
								$extra_details = $this->site->getProductByID($extraProduct['for_product_id']);
								if($extra_details){
									$extraUnit = $this->site->getProductUnit($extra_details->id,$extraProduct['for_unit_id']);
									$extractProductID = $extra_details->id;
									$extractQuantity = $extraUnit->unit_qty * $extraProduct['for_quantity'];
									
									if($this->Settings->accounting_method == '0'){
										$extraCosts = $this->site->getFifoCost($extractProductID,$extractQuantity,$stockmoves);
									}else if($this->Settings->accounting_method == '1'){
										$extraCosts = $this->site->getLifoCost($extractProductID,$extractQuantity,$stockmoves);
									}else if($this->Settings->accounting_method == '3'){
										$extraCosts = $this->site->getProductMethod($extractProductID,$extractQuantity,$stockmoves);
									}
									
									if($extraCosts){
										$productAcc = $this->site->getProductAccByProductId($extractProductID);
										$item_cost_total = 0;
										$item_costs = '';
										foreach($extraCosts as $extraCost){
											$item_cost_total += $extraCost['cost'] * $extraCost['quantity'];
						
											$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $extractProductID,
												'product_code' => $extra_details->code,
												'product_type' => $extra_details->type,
												'option_id' => 0,
												'quantity' => $extraCost['quantity'] * (-1),
												'unit_quantity' => $extraUnit->unit_qty,
												'unit_code' => $extraUnit->code,
												'unit_id' => $extraProduct['for_unit_id'],
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $extraCost['cost'],
												'serial_no' => '',
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
											//========accounting=========//
												if($this->Settings->accounting == 1){	
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->stock_acc,
														'amount' => -($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->cost_acc,
														'amount' => ($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
												}
											//============end accounting=======//
											
										}
										$extractCost += $item_cost_total;
										
									}else{
										$extractCost += ($extractQuantity * $extra_details->cost);
										$stockmoves[] = array(
											'transaction' => 'Sale',
											'product_id' => $extractProductID,
											'product_code' => $extra_details->code,
											'product_type' => $extra_details->type,
											'option_id' => 0,
											'quantity' => $extractQuantity * (-1),
											'unit_quantity' => $extraUnit->unit_qty,
											'unit_code' => $extraUnit->code,
											'unit_id' => $extraProduct['for_unit_id'],
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $extra_details->cost,
											'serial_no' => '',
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);
										
										//========accounting=========//
											if($this->Settings->accounting == 1){		
												$productAcc = $this->site->getProductAccByProductId($extractProductID);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
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
							$products[$r]['extract_cost'] = $extractCost;
						}
					}
					
					if($this->Settings->qty_operation == 1){												
						$width = $_POST['swidth'][$r];
						$height = $_POST['sheight'][$r];
						$square = $_POST['square'][$r];
						$square_qty = $_POST['square_qty'][$r];
						
						$products[$r]['width'] = $width;
						$products[$r]['height'] = $height;
						$products[$r]['square'] = $square;
						$products[$r]['square_qty'] = $square_qty;
						
						$extraProducts = $this->cus->productFormulation($item_id,$width,$height,$square,$square_qty);
						if($extraProducts){
							$products[$r]['extract_product'] = json_encode($extraProducts);
							$extractCost = 0;
							foreach($extraProducts as $extraProduct){
								$extra_details = $this->site->getProductByID($extraProduct['for_product_id']);
								if($extra_details){
									$extraUnit = $this->site->getProductUnit($extra_details->id,$extraProduct['for_unit_id']);
									$extractProductID = $extra_details->id;
									$extractQuantity = $extraUnit->unit_qty * $extraProduct['for_quantity'];
									
									if($this->Settings->accounting_method == '0'){
										$extraCosts = $this->site->getFifoCost($extractProductID,$extractQuantity,$stockmoves);
									}else if($this->Settings->accounting_method == '1'){
										$extraCosts = $this->site->getLifoCost($extractProductID,$extractQuantity,$stockmoves);
									}else if($this->Settings->accounting_method == '3'){
										$extraCosts = $this->site->getProductMethod($extractProductID,$extractQuantity,$stockmoves);
									}
									
									if($extraCosts){
										$productAcc = $this->site->getProductAccByProductId($extractProductID);
										$item_cost_total = 0;
										$item_costs = '';
										foreach($extraCosts as $extraCost){
											$item_cost_total += $extraCost['cost'] * $extraCost['quantity'];
						
											$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $extractProductID,
												'product_code' => $extra_details->code,
												'product_type' => $extra_details->type,
												'option_id' => 0,
												'quantity' => $extraCost['quantity'] * (-1),
												'unit_quantity' => $extraUnit->unit_qty,
												'unit_code' => $extraUnit->code,
												'unit_id' => $extraProduct['for_unit_id'],
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $extraCost['cost'],
												'serial_no' => '',
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
											//========accounting=========//
												if($this->Settings->accounting == 1){	
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->stock_acc,
														'amount' => -($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->cost_acc,
														'amount' => ($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
												}
											//============end accounting=======//
											
										}
										$extractCost += $item_cost_total;
										
									}else{
										$extractCost += ($extractQuantity * $extra_details->cost);
										$stockmoves[] = array(
											'transaction' => 'Sale',
											'product_id' => $extractProductID,
											'product_code' => $extra_details->code,
											'product_type' => $extra_details->type,
											'option_id' => 0,
											'quantity' => $extractQuantity * (-1),
											'unit_quantity' => $extraUnit->unit_qty,
											'unit_code' => $extraUnit->code,
											'unit_id' => $extraProduct['for_unit_id'],
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $extra_details->cost,
											'serial_no' => '',
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);
										
										//========accounting=========//
											if($this->Settings->accounting == 1){		
												$productAcc = $this->site->getProductAccByProductId($extractProductID);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
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
							$products[$r]['extract_cost'] = $extractCost;
						}
					}
						
						//=======accounting=========//
							if($this->Settings->accounting == 1 && $product_details->type !='combo'){
								if ($item_type == 'manual') {
									$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
									$accTrans[] = array(
											'transaction' => 'Sale',
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $saleAcc->other_income_acc,
											'amount' => -($item_net_price * $item_unit_quantity),
											'narrative' => 'Sale',
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
										);
								}else{
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->sale_acc,
										'amount' => -($item_net_price * $item_unit_quantity),
										'narrative' => 'Sale',
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
									);
								}									
								
							}
						//============end accounting=======//
					
                    
					
                    $total += $this->cus->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
			
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } elseif ($this->pos_settings->item_order == 1) {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== FALSE) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->cus->formatDecimal(((($total + $product_tax) * (Float)($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->cus->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = NULL;
            }
            $total_discount = $this->cus->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->cus->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->cus->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = NULL;
            }

            $total_tax = $this->cus->formatDecimal(($product_tax + $order_tax), 4); 
            $grand_total = $this->cus->formatDecimal(($total + $total_tax + $this->cus->formatDecimal($shipping) - $order_discount), 4);
            $rounding = 0;
            if ($this->pos_settings->rounding) {
                $round_total = $this->cus->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding = $this->cus->formatMoney($round_total - $grand_total);
            }
						
			$currencies = array();
			$camounts = $this->input->post("camount");
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


            //=======acounting=========//   
                if($this->Settings->accounting == 1){           
                    $saleAcc = $this->site->getAccountSettingByBiller($biller_id);
                    $accTrans[] = array(
                        'transaction' => 'Sale',
                        'transaction_date' => $date,
                        'reference' => $reference,
                        'account' => $saleAcc->ar_acc,
                        'amount' => $grand_total,
                        'narrative' => 'Sale',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'user_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                    );
                    
                    if($order_discount > 0){
                        $accTrans[] = array(
                            'transaction' => 'Sale',
                            'transaction_date' => $date,
                            'reference' => $reference,
                            'account' => $saleAcc->sale_discount_acc,
                            'amount' => $order_discount,
                            'narrative' => 'Order Discount',
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'user_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                        );
                    }
                    if($order_tax > 0){
                        $accTrans[] = array(
                            'transaction' => 'Sale',
                            'transaction_date' => $date,
                            'reference' => $reference,
                            'account' => $saleAcc->vat_output,
                            'amount' => -$order_tax,
                            'narrative' => 'Order Tax',
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'user_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                        );
                    }
                    if($shipping > 0){
                        $accTrans[] = array(
                                'transaction' => 'Sale',
                                'transaction_date' => $date,
                                'reference' => $reference,
                                'account' => $saleAcc->shipping_acc,
                                'amount' => -$shipping,
                                'narrative' => 'Shipping',
                                'description' => $note,
                                'biller_id' => $biller_id,
                                'project_id' => $project_id,
                                'user_id' => $this->session->userdata('user_id'),
                                'customer_id' => $customer_id,
                            );
                    }
                    
                }   
            //============end accounting=======//
			
            $data = array('date'              => $date,
                          'reference_no'      => $reference,
                          'customer_id'       => $customer_id,
                          'customer'          => $customer,
                          'biller_id'         => $biller_id,
                          'biller'            => $biller,
						  'project_id'        => $project_id,
                          'warehouse_id'      => $warehouse_id,
                          'note'              => $note,
                          'staff_note'        => $staff_note,
                          'total'             => $total,
                          'product_discount'  => $product_discount,
                          'order_discount_id' => $order_discount_id,
                          'order_discount'    => $order_discount,
                          'total_discount'    => $total_discount,
                          'product_tax'       => $product_tax,
                          'order_tax_id'      => $order_tax_id,
                          'order_tax'         => $order_tax,
                          'total_tax'         => $total_tax,
                          'shipping'          => $this->cus->formatDecimal($shipping),
                          'grand_total'       => $grand_total,
                          'total_items'       => $total_items,
                          'sale_status'       => $sale_status,
                          'payment_status'    => $payment_status,
                          'payment_term'      => $payment_term,
                          'rounding'          => $rounding,
                          'suspend_note'      => $this->input->post('suspend_note'),
                          'pos'               => 1,
                          'paid'              => ($this->pos_settings->pos_payment==0 ? $grand_total : ($this->input->post('amount-paid') ? $this->input->post('amount-paid') : 0)),
                          'created_by'        => $this->session->userdata('user_id'),
						  'saleman_id'		  => (isset($saleman) && $saleman ? $saleman->id : null),
						  'saleman'      	  => (isset($saleman) && $saleman ? $saleman->username : null),
						  'currencies'		  => json_encode($currencies),
						  'delivery_status'	  => $delivery_status,
                          'ar_account'        => $saleAcc->ar_acc,
						  						  
            );			
			
			
			
            if (isset($_POST['submit-sale']) || !$suspend) {
				$kh_currency = $this->site->getCurrencyByCode("KHR");
				if($this->pos_settings->quick_pos == 1){
					$multi_payment = 0;
					$paid_amount = 0;
					$a = 1;
					$gpaying = $_POST["gpaying"];
					if($gpaying != 0){
						$cash_account = $this->site->getCashAccountByID($biller_details->default_cash);
						$paying_to = $cash_account->account_code;
						
						$currencies = false;
						$currencies[] = array(
							"amount" => $_POST["qpaying_usd"],
							"currency" => "USD",
							"rate" => "1",
						);
						$currencies[] = array(
							"amount" => $_POST["qpaying_khr"],
							"currency" => "KHR",
							"rate" => $kh_currency->rate,
						);
						
						if($gpaying > $grand_total){
							$amount = $this->cus->formatDecimal($grand_total);
						}else{
							$amount = $this->cus->formatDecimal($gpaying);
						}
						$payment[$a] = array(
									'date'         => $date,
									'amount'       => $amount,
									'paid_by'      => $biller_details->default_cash,
									'created_by'   => $this->session->userdata('user_id'),
									'type'         => 'received',
									'pos_paid'     => $gpaying,
									'pos_balance'  => ($gpaying - $grand_total),
									'currencies'   => json_encode($currencies),
									'account_code' => $paying_to,
									);
						if($this->Settings->accounting == 1){
							$accTranPayments[$a][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'account' => $saleAcc->ar_acc,
									'amount' => -$amount,
									'narrative' => 'Sale Payment '.$reference,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id,
								);
							$accTranPayments[$a][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'account' => $paying_to,
									'amount' => $amount,
									'narrative' => 'Sale Payment '.$reference,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id,
								);
						}
						$paid_amount += $amount;
						$a++;
					}
					
					if($this->pos_settings->pos_multi_payment == 1){
						$p = isset($_POST['m_paid_by']) ? sizeof($_POST['m_paid_by']) : 0;
						for ($r = 0; $r < $p; $r++) {
							$m_qpaying_usd = $_POST['m_qpaying_usd'][$r];
							$m_qpaying_khr = $_POST['m_qpaying_khr'][$r];
							$currencies = false;
							$currencies[] = array(
								"amount" => $m_qpaying_usd,
								"currency" => "USD",
								"rate" => "1",
							);
							$currencies[] = array(
								"amount" => $m_qpaying_khr,
								"currency" => "KHR",
								"rate" => $kh_currency->rate,
							);
							$multi_payment += ($m_qpaying_usd + ($m_qpaying_khr/$kh_currency->rate));
							if(($m_qpaying_usd + ($m_qpaying_khr/$kh_currency->rate)) > ($grand_total - $paid_amount)){
								$amount = $this->cus->formatDecimal($grand_total - $paid_amount);
							}else{
								$amount = $this->cus->formatDecimal(($m_qpaying_usd + ($m_qpaying_khr/$kh_currency->rate)));
							}
							
							$cash_account = $this->site->getCashAccountByID($_POST['m_paid_by'][$r]);
							$paying_to = $cash_account->account_code;
							$payment[$a] = array(
										'date'         => $date,
										'amount'       => $amount,
										'paid_by'      => $_POST['m_paid_by'][$r],
										'created_by'   => $this->session->userdata('user_id'),
										'type'         => 'received',
										'pos_paid'     => ($m_qpaying_usd + ($m_qpaying_khr/$kh_currency->rate)),
										'pos_balance'  => (($m_qpaying_usd + ($m_qpaying_khr/$kh_currency->rate)) - ($grand_total - $paid_amount)),
										'currencies'   => json_encode($currencies),
										'account_code' => $paying_to,
										);
							if($this->Settings->accounting == 1){
								$accTranPayments[$a][] = array(
										'transaction' => 'Payment',
										'transaction_date' => $date,
										'account' => $saleAcc->ar_acc,
										'amount' => -$amount,
										'narrative' => 'Sale Payment '.$reference,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
								$accTranPayments[$a][] = array(
										'transaction' => 'Payment',
										'transaction_date' => $date,
										'account' => $paying_to,
										'amount' => $amount,
										'narrative' => 'Sale Payment '.$reference,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
							}
							$paid_amount += $amount;
							$a++;
						}
					}
				} else if($this->pos_settings->pos_payment==0){
					$currencies = false;
					$currencies[] = array(
						"amount" => $grand_total,
						"currency" => "USD",
						"rate" => "1",
					);
					$currencies[] = array(
						"amount" => 0,
						"currency" => "KHR",
						"rate" => $kh_currency->rate,
					);
					$cash_account = $this->site->getCashAccountByID($biller_details->default_cash);
					$paying_to = $cash_account->account_code;
					$payment[1] = array(
						'date'         => $date,
						'amount'       => $grand_total,
						'paid_by'      => $biller_details->default_cash,
						'created_by'   => $this->session->userdata('user_id'),
						'type'         => 'received',
						'pos_paid'     => $grand_total,
						'pos_balance'  => 0,
						'currencies'   => json_encode($currencies),
						'account_code' => $paying_to,
					);
					//=====accountig=====//
						if($this->Settings->accounting == 1){
							$accTranPayments[1][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'account' => $saleAcc->ar_acc,
									'amount' => -$grand_total,
									'narrative' => 'Sale Payment '.$reference,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id,
								);
							$accTranPayments[1][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'account' => $paying_to,
									'amount' => $grand_total,
									'narrative' => 'Sale Payment '.$reference,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id,
								);
						}
						
					//=====end accountig=====//
				}else{
					$p = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
					$paid = 0;
					$a = 1;
					for ($r = 0; $r < $p; $r++) {
						if(!$_POST['paid_by'][$r]){
							$_POST['paid_by'][$r] = $biller_details->default_cash;
						}
						if (isset($_POST['amount'][$r]) && !empty($_POST['amount'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
							$amount = $this->cus->formatDecimal($_POST['balance_amount'][$r] > 0 ? $_POST['amount'][$r] - $_POST['balance_amount'][$r] : $_POST['amount'][$r]);
							if($r > 0){
								$currencies = false;
								$currencies[] = array(
									"amount" => $amount,
									"currency" => "USD",
									"rate" => "1",
								);
								$currencies[] = array(
									"amount" => 0,
									"currency" => "KHR",
									"rate" => $kh_currency->rate,
								);
							}
							
							
							if($_POST['paid_by'][$r]=='deposit' || $_POST['paid_by'][$r]=='gift_card'){
								$paying_to = $saleAcc->customer_deposit_acc;
							}else{
								$cash_account = $this->site->getCashAccountByID($_POST['paid_by'][$r]);
								$paying_to = $cash_account->account_code;
							}
							
							if ($_POST['paid_by'][$r] == 'deposit') {
								if (!$this->site->check_customer_deposit($customer_id, $amount)) {
									$this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
									redirect($_SERVER["HTTP_REFERER"]);
								}
							} 
							
							if ($_POST['paid_by'][$r] == 'gift_card') {
								$gc = $this->site->getGiftCardByNO($_POST['paying_gift_card_no'][$r]);
								$amount_paying = $_POST['amount'][$r] >= $gc->balance ? $gc->balance : $_POST['amount'][$r];
								$gc_balance = $gc->balance - $amount_paying;
								$payment[$a] = array(
									'date'         => $date,
									'amount'       => $amount,
									'paid_by'      => $_POST['paid_by'][$r],
									'cheque_no'    => $_POST['cheque_no'][$r],
									'cc_no'        => $_POST['paying_gift_card_no'][$r],
									'cc_holder'    => $_POST['cc_holder'][$r],
									'cc_month'     => $_POST['cc_month'][$r],
									'cc_year'      => $_POST['cc_year'][$r],
									'cc_type'      => $_POST['cc_type'][$r],
									'cc_cvv2'      => $_POST['cc_cvv2'][$r],
									'created_by'   => $this->session->userdata('user_id'),
									'type'         => 'received',
									'note'         => $_POST['payment_note'][$r],
									'pos_paid'     => $_POST['amount'][$r],
									'pos_balance'  => $_POST['balance_amount'][$r],
									'gc_balance'   => $gc_balance,
									'currencies'   => json_encode($currencies),
									'account_code' => $paying_to,
									);

							} else {
								$payment[$a] = array(
									'date'         => $date,
									'amount'       => $amount,
									'paid_by'      => $_POST['paid_by'][$r],
									'cheque_no'    => $_POST['cheque_no'][$r],
									'cc_no'        => $_POST['cc_no'][$r],
									'cc_holder'    => $_POST['cc_holder'][$r],
									'cc_month'     => $_POST['cc_month'][$r],
									'cc_year'      => $_POST['cc_year'][$r],
									'cc_type'      => $_POST['cc_type'][$r],
									'cc_cvv2'      => $_POST['cc_cvv2'][$r],
									'created_by'   => $this->session->userdata('user_id'),
									'type'         => 'received',
									'note'         => $_POST['payment_note'][$r],
									'pos_paid'     => $_POST['amount'][$r],
									'pos_balance'  => $_POST['balance_amount'][$r],
									'currencies'   => json_encode($currencies),
									'account_code' => $paying_to,
									);

							}
							//=====accountig=====//
								if($this->Settings->accounting == 1){
									$accTranPayments[$a][] = array(
											'transaction' => 'Payment',
											'transaction_date' => $date,
											'account' => $saleAcc->ar_acc,
											'amount' => -$amount,
											'narrative' => 'Sale Payment '.$reference,
											'description' => $_POST['payment_note'][$r],
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
									$accTranPayments[$a][] = array(
											'transaction' => 'Payment',
											'transaction_date' => $date,
											'account' => $paying_to,
											'amount' => $amount,
											'narrative' => 'Sale Payment '.$reference,
											'description' => $_POST['payment_note'][$r],
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
								}
							//=====end accountig=====//
							$a++;
						}
					}
				}
            }
            if (!isset($payment) || empty($payment)) {
                $payment = array();
            }
        }
		
        if ($this->form_validation->run() == TRUE && !empty($products) && !empty($data)) {
			
			if($this->Settings->car_operation == 1){
				$vehicle_model = $this->input->post('povehicle_model');
				$vehicle_kilometers = $this->input->post('povehicle_kilometers');
				$vehicle_vin_no = $this->input->post('povehicle_vin_no');
				$vehicle_plate = $this->input->post('povehicle_plate');
				$job_number = $this->input->post('pojob_number');
				$mechanic = $this->input->post('pomechanic');
				$data['vehicle_model'] = $vehicle_model;
				$data['vehicle_kilometers'] = $vehicle_kilometers;
				$data['vehicle_vin_no'] = $vehicle_vin_no;
				$data['vehicle_plate'] = $vehicle_plate;
				$data['job_number'] = $job_number;
				$data['mechanic'] = $mechanic;
			}
				
            if (!isset($_POST['submit-sale']) && $suspend) {
				$data['table_id'] = $this->input->post('table_id');
				$data['table_name'] = $this->input->post('table_name');				
				$bill_id = $this->input->post("bill_id");
				if($bill_id){					
					if ($this->pos_model->suspendMergeSale($data, $products, $did, $bill_id)) {
						$this->session->set_userdata('remove_posls', 1);
						$this->session->set_flashdata('message', $this->lang->line("sale_suspended"));
						redirect("pos");
					}
				}
                if ($bid = $this->pos_model->suspendSale($data, $products, $did)) {
					$this->session->set_userdata('remove_posls', 1);
                    $this->session->set_flashdata('message', $this->lang->line("sale_suspended"));
					if($this->pos_settings->table_enable == 1){
						$this->load_suspend_items($bid);
					}else{				
						redirect("pos");
					}
                }
				
            } else {
				
				$biller_id = $this->input->post('biller');
				if($this->pos_settings->table_enable == 1){
					$suspend_bill_id = $this->input->post('suspend_bill_id');
					$bill = $this->pos_model->getSuspendByID($suspend_bill_id);
					$data['table_id'] = $bill->table_id;
					$data['table_name'] = $bill->table_name;
					$data['time_in'] = $bill->date;					
				}
				
				if($this->Settings->product_expiry == '1' && $stockmoves && $products){
					$checkExpiry = $this->site->checkExpiry($stockmoves, $products,'POS');
					$stockmoves = $checkExpiry['expiry_stockmoves'];
					$products = $checkExpiry['expiry_items'];
				}
				
                if ($sale = $this->pos_model->addSale($data, $products, $payment, $did, $biller_id, $stockmoves,$accTrans,$accTranPayments)) {
					if($this->pos_settings->queue_enable == 1){
						$queueNumber = $this->pos_model->queueNumber();
					}
                    $this->session->set_userdata('remove_posls', 1);
                    $msg = $this->lang->line("sale_added");
                    if (!empty($sale['message'])) {
                        foreach ($sale['message'] as $m) {
                            $msg .= '<br>' . $m;
                        }
                    }
                    $this->session->set_flashdata('message', $msg);
                    $redirect_to = $this->pos_settings->after_sale_page == 1? "pos/?sale_id=".$sale['sale_id'] : "pos/view/" . $sale['sale_id']."?q=1";
                    if ($this->pos_settings->auto_print) {
                        if ($this->Settings->remote_printing != 1) {
                            $redirect_to .= '?print='.$sale['sale_id'];
                        }
                    }
                    redirect($redirect_to);
                }
            }
        } else {
            $this->data['suspend_sale'] = NULL;
			/*if(!$sid && !isset($_GET['p'])){
				$this->session->set_userdata('remove_posls', 1);
			}*/
            if ($sid) {
                if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {
					if($this->pos_settings->table_enable){
						$inv_items = $this->pos_model->getSuspendedSaleItemsByTableID($suspended_sale->table_id);
					}else{
						$inv_items = $this->pos_model->getSuspendedSaleItems($sid);
					}
					
					if($this->Settings->product_additional == 1){
						$additional_products = $this->pos_model->getProductAdditionals();
					}else{
						$additional_products = false;
					}
					
					$pr = array();
					if($inv_items){
						krsort($inv_items);
						$c = rand(100000, 9999999);
						foreach ($inv_items as $item) {
							$row = $this->site->getProductByID($item->product_id);
							if (!$row) {
								$row = json_decode('{}');
								$row->tax_method = 0;
								$row->quantity = 0;
							} else {
								$category = $this->site->getCategoryByID($row->category_id);
								$row->category_name = $category->name;
								$row->category_type = $category->type;
								unset($row->cost, $row->details, $row->product_details, $row->barcode_symbology, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
							}
							$pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id);
							if ($pis) {
								foreach ($pis as $pi) {
									$row->quantity += $pi->quantity_balance;
								}
							}
							$row->fup = 1;
							$row->id = $item->product_id;
							$row->suspend_item_id = $item->id;
							$row->code = $item->product_code;
							$row->name = $item->product_name;
							$row->type = $item->product_type;            
							$row->quantity += $item->quantity;
							$row->base_quantity = $item->quantity;
							$row->base_unit = isset($row->unit) ? $row->unit : $item->product_unit_id;
							$row->base_unit_price = $row->price ? $row->price : $item->real_unit_price;
							$row->discount = $item->discount ? $item->discount : '0';
							$row->price = $this->cus->formatDecimal($item->net_unit_price + $this->cus->formatDecimal($item->item_discount / $item->quantity));
							$row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimal($item->item_discount / $item->quantity) + $this->cus->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
							$row->real_unit_price = $item->real_unit_price;
							$row->unit = $item->product_unit_id;
							$row->qty = $item->unit_quantity;
							$row->tax_rate = $item->tax_rate_id;
							$row->serial = $item->serial_no;
							$row->sheight = $item->height;
							$row->square = $item->square;
							$row->square_qty = $item->square_qty;
							$row->swidth = $item->width;
							$row->option = $item->option_id;
							$row->ordered = $item->ordered;
							$row->ordered_by = $item->ordered_by;
							$row->product_additional = $item->pro_additionals;
							$order = $this->site->getUser($row->ordered_by);
							$row->order_name = $order->username;
							$row->return_qty = ($item->return_quantity)-0;
							$options = $this->pos_model->getProductOptions($row->id, $item->warehouse_id);
							
							
							$currencies = false;
							if($this->config->item('product_currency')==true){
								$currencies = $this->site->getAllCurrencies();
								foreach($currencies as $currency){
									if($currency->code == $row->currency_code){
										$currency->rate = $row->currency_rate;
									}
								}
								$row->price = $row->price * ($row->currency_rate);
								$row->real_currency_rate = $row->currency_rate;
							}
							
							if($this->Settings->product_serial == 1){
								$product_serials = $this->pos_model->getProductSerialDetailsByProductId($row->id, $item->warehouse_id);
							}else{
								$product_serials = false;
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
							$row->comment = isset($item->comment) ? $item->comment : '';
							$combo_items = false;
							if ($row->type == 'combo') {
								$combo_items = json_decode($item->combo_product);
							}
							$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
							$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
							$ri = $this->Settings->item_addition ? $row->id : $c;
							
							if(isset($enable_bom)) {
								$bom_typies = $this->pos_model->getTypeBoms($row->id);
								$row->bom_type = $bom_typies[0]->bom_type;
							}else{
								$bom_typies = false;
							}
							$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
										'row' => $row, 'product_serials'=>$product_serials, 'combo_items' => $combo_items, 'bom_typies' => $bom_typies, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options,'additional_products' => $additional_products,'currencies'=>$currencies);
						
							$c++;
						}
					}
                    $this->data['items'] = json_encode($pr);
                    $this->data['sid'] = $sid;
                    $this->data['suspend_sale'] = $suspended_sale;
                    $this->data['message'] = lang('suspended_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($suspended_sale->customer_id);
                    $this->data['reference_note'] = $suspended_sale->suspend_note;
                } else {
                    $this->session->set_flashdata('error', lang("bill_x_found"));
                    redirect("pos");
                }
				
			} else {
                $this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
                $this->data['reference_note'] = NULL;
            }
		
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['message'] = isset($this->data['message']) ? $this->data['message'] : $this->session->flashdata('message');
			$this->data['billsTable'] = $this->pos_model->getTableByBillId($sid);
            $this->data['billers'] = $this->site->getBillers();
			if($this->Owner || $this->Admin || !$this->session->userdata('biller_id')){
				$this->data['default_biller'] = $this->site->getCompanyByID($this->pos_settings->default_biller);
			}else{
				$this->data['default_biller'] = $this->site->getCompanyByID($this->session->userdata('biller_id'));
			}
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['user'] = $this->site->getUser($this->session->userdata('user_id'));
            $this->data["tcp"] = $this->pos_model->products_count($this->pos_settings->default_category);
			if($this->pos_settings->table_enable == 1){
				if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
					$this->data['categories'] = $this->pos_model->fetch_categories($this->config->item("category_rows"));
					$this->data['products'] = $this->ajaxproducts($this->pos_settings->default_category);
				} else {
					$categories = $this->pos_model->fetch_categories($this->config->item("category_rows"));
					$this->data['products'] = $this->ajaxproducts($categories[0]->id);	
					$this->data['categories'] = $categories;
				}
			}else{
				$this->data['categories'] = $this->pos_model->fetch_categories($this->config->item("category_rows"));
				$this->data['products'] = $this->ajaxproducts($this->pos_settings->default_category);
			}
			$this->data['users'] = $this->site->getAllUsers();
			$this->data['vehicles'] = $this->site->getAllVehicles();
			$this->data['tables'] = $this->site->getAllTables();
			$this->data['projects'] = $this->site->getAllProjects();
			$this->data['tags'] = $this->site->getAllTags();
			$this->data['types'] = $this->pos_model->getAllTypes();
			$this->data['sbills'] = $this->pos_model->getSuspendedBills();			
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['subcategories'] = $this->site->getSubCategories($this->pos_settings->default_category);
            $this->data['printer'] = $this->pos_model->getPrinterByID($this->pos_settings->printer);
            $order_printers = json_decode($this->pos_settings->order_printers);
            foreach ($order_printers as $printer_id) {
                $printers[] = $this->pos_model->getPrinterByID($printer_id);
            }
            $this->data['order_printers'] = $printers;
            $this->data['pos_settings'] = $this->pos_settings;
            if ($this->pos_settings->after_sale_page && $saleid = $this->input->get('print', true)) {
                if ($inv = $this->pos_model->getInvoiceByID($saleid)) {
                    $this->load->helper('pos');
                    if (!$this->session->userdata('view_right')) {
                        $this->cus->view_rights($inv->created_by, true);
                    }
                    $this->data['rows'] = $this->pos_model->getAllInvoiceItems($inv->id);
                    $this->data['biller'] = $this->pos_model->getCompanyByID($inv->biller_id);
                    $this->data['customer'] = $this->pos_model->getCompanyByID($inv->customer_id);
                    $this->data['payments'] = $this->pos_model->getInvoicePayments($inv->id);
                    $this->data['return_sale'] = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
                    $this->data['return_rows'] = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
                    $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
                    $this->data['inv'] = $inv;
                    $this->data['print'] = $inv->id;
                    $this->data['created_by'] = $this->site->getUser($inv->created_by);
                }
            }
            $this->load->view($this->theme . 'pos/add', $this->data);
        }
    }
	
    public function view_bill()
    {
        $this->cus->checkPermissions('index');
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $this->load->view($this->theme . 'pos/view_bill', $this->data);
    }

    public function stripe_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->model('stripe_payments');

        return $this->stripe_payments->get_balance();
    }

    public function paypal_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->model('paypal_payments');

        return $this->paypal_payments->get_balance();
    }

    public function registers()
    {
        $this->cus->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['registers'] = $this->pos_model->getOpenRegisters();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('open_registers')));
        $meta = array('page_title' => lang('open_registers'), 'bc' => $bc);
        $this->core_page('pos/registers', $meta, $this->data);
    }

    public function open_register()
    {
        $this->cus->checkPermissions('index');
		$open_user = $this->pos_model->getOpenRegisterByUser($this->session->userdata('user_id'));
		if($open_user){
			if($this->pos_settings->table_enable == 1){
				redirect("pos/add_table");
			}else{					
				redirect("pos");
			}
		}
		$this->form_validation->set_rules('cash_in_hand', lang("cash_in_hand"), 'trim|required|numeric');
        if ($this->form_validation->run() == TRUE) {
			$register_open_time = date("Y-m-d H:i:s");
            $data = array(
                'date' => $register_open_time,
                'cash_in_hand' => $this->input->post('cash_in_hand'),
                'user_id'      => $this->session->userdata('user_id'),
                'status'       => 'open',
                );
        }
        if ($this->form_validation->run() == TRUE && $this->pos_model->openRegister($data)) {
            $this->session->set_flashdata('message', lang("welcome_to_pos"));
			if($this->pos_settings->table_enable == 1){
				redirect("pos/add_table");
			}else{					
				redirect("pos");
			}
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('open_register')));
            $meta = array('page_title' => lang('open_register'), 'bc' => $bc);
            $this->core_page('pos/open_register', $meta, $this->data);
        }
    }
	
	public function count_money()
    {
        $this->cus->checkPermissions('index');
        $user_id = $this->session->userdata('user_id');
        $this->form_validation->set_rules('total_money', lang("total_money"), 'required');

        if ($this->form_validation->run() == TRUE) {

            $data = array(
                'counted_at'       	=> date('Y-m-d H:i:s'),
				'user_id'       	=> $user_id,
				'total_amount'      => $this->input->post('total_money'),
				'total_money_kh'    => $this->input->post('rate_kh'),
				'total_money_us'    => $this->input->post('rate_use'),
                '100_riel'        	=> $this->input->post('100_riel'),
                '1_use'           	=> $this->input->post('1_use'),
                '500_riel'         	=> $this->input->post('500_riel'),
                '2_use'     		=> $this->input->post('2_use'),
                '1k_riel'  			=> $this->input->post('1k_riel'),
                '5_use' 			=> $this->input->post('5_use'),
                '2k_riel'           => $this->input->post('2k_riel'),
                '10_use'   			=> $this->input->post('10_use'),
                '5k_riel'           => $this->input->post('5k_riel'),
				'20_use'            => $this->input->post('20_use'),
				'10k_riel'          => $this->input->post('10k_riel'),
				'50_use'            => $this->input->post('50_use'),
				'20k_riel'          => $this->input->post('20k_riel'),
				'100_use'           => $this->input->post('100_use'),
				'50k_riel'          => $this->input->post('50k_riel'),
				'500_use'           => $this->input->post('500_use'),
				'100k_riel'         => $this->input->post('100k_riel'),
				'1k_use'            => $this->input->post('1k_use'),
                );
				
        } elseif ($this->input->post('count_money')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            redirect("pos");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->countMoney($data)) {
            $this->session->set_flashdata('message', lang("money_counted"));
            redirect("pos");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['user_id'] = $user_id;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currency'] = $this->site->getCurrencyByCode('KHR');
            $this->load->view($this->theme . 'pos/count_money', $this->data);
        }
    }

    public function close_register($user_id = NULL)
    {
        $this->cus->checkPermissions('index');
		if(!$user_id){
			$user_id = $this->session->userdata('user_id');
		}
        $this->form_validation->set_rules('total_cash', lang("total_cash"), 'trim|required|numeric');
        if ($this->form_validation->run() == TRUE) {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
				$register_open_time = $user_register ? $user_register->date : NULL;
                $rid = $user_register ? $user_register->id : $this->session->userdata('register_id');
                $user_id = $user_register ? $user_register->user_id : $this->session->userdata('user_id');
            } else {
				$register_open_time = $this->session->userdata('register_open_time');
                $rid = $this->session->userdata('register_id');
                $user_id = $this->session->userdata('user_id');
            }
			$products = false;
			$register_items = $this->pos_model->getRegisterSaleItems($register_open_time,$user_id);
			if($register_items){
				foreach($register_items as $register_item){
					$products[] = array(
										"register_id" => $rid,
										"product_id" => $register_item->product_id,
										"product_code" => $register_item->product_code,
										"product_name" => $register_item->product_name,
										"quantity" => $register_item->quantity
									);
				}
			}
            $data = array(
                'closed_at'                => date('Y-m-d H:i:s'),
                'total_cash'               => $this->input->post('total_cash'),
                'total_cash_submitted'     => $this->input->post('total_cash_submitted'),
                'note'                     => $this->input->post('note'),
                'status'                   => 'close',
                'transfer_opened_bills'    => $this->input->post('transfer_opened_bills'),
                'closed_by'                => $this->session->userdata('user_id'),
                );
        } elseif ($this->input->post('close_register')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            redirect("pos");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->closeRegister($rid, $user_id, $data, $products)) {
            $this->session->set_flashdata('message', lang("register_closed"));
			if($this->config->item('server_local') && site_url() != $this->config->item('server_url')){
				redirect("synchronize/push_pos");
			}else{
				redirect("welcome");
			}
        } else {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
                $register_open_time = $user_register ? $user_register->date : NULL;
                $this->data['cash_in_hand'] = $user_register ? $user_register->cash_in_hand : NULL;
                $this->data['register_open_time'] = $user_register ? $register_open_time : NULL;
            } else {
                $register_open_time = $this->session->userdata('register_open_time');
                $this->data['cash_in_hand'] = NULL;
                $this->data['register_open_time'] = NULL;
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['total_cash'] = $this->pos_model->getTotalCash($register_open_time, $user_id);
			$this->data['payments'] = $this->pos_model->getRegisterPayments($register_open_time, $user_id);
            $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time, $user_id);
			$this->data['totaldiscount'] = $this->pos_model->getRegisterSaleDiscounts($register_open_time, $user_id);
            $this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time, $user_id);
            $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time, $user_id);
            $this->data['users'] = $this->pos_model->getUserByID($user_id);
			$this->data['open_users'] = $this->pos_model->getOpenRegisters();
            $this->data['suspended_bills'] = $this->pos_model->getSuspendedsales($user_id);
			$this->data['refund_item'] = $this->pos_model->getSaleReturnItems($register_open_time);
			$this->data['count_money'] = $this->pos_model->getCountMoney($register_open_time, $user_id);
            $this->data['user_id'] = $user_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/close_register', $this->data);
        }
    }

    public function getProductDataByCode($code = NULL, $warehouse_id = NULL)
    {
        $this->cus->checkPermissions('index');
        if ($this->input->get('code')) {
            $code = $this->input->get('code', TRUE);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', TRUE);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', TRUE);
        }
        if (!$code) {
            echo NULL;
            die();
        }
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row = $this->pos_model->getWHProduct($code, $warehouse_id);
        $option = false;
        if(in_array('bom',$this->config->item('product_types'))) {
            $enable_bom = true;
        }else{
            $enable_bom  = false;
        }
		
		if($this->Settings->product_additional == 1){
			$additional_products = $this->pos_model->getProductAdditionals();
		}else{
			$additional_products = false;
		}
		
        if ($row) {
            unset($row->cost, $row->details, $row->product_details, $row->barcode_symbology, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->item_tax_method = $row->tax_method;
            $row->qty = 1;
            $row->discount = '0';
            if(isset($row->serial)){
				if($row->serial_price > 0){
					$row->price = $row->serial_price;
				}
			}else{
				$row->serial = '';
			}
            $options = $this->pos_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option = $opt->id;
                }
            } else {
                $opt = json_decode('{}');
                $opt->price = 0;
            }
            $row->option = $option;
            $row->quantity = 0;
            $pis = $this->site->getStockmoves($row->id, $warehouse_id, $row->option);
            if ($pis) {
                foreach ($pis as $pi) {
                    $row->quantity += $pi->quantity_balance;
                }
            }
            if ($row->type == 'standard' && (!$this->Settings->overselling && $row->quantity < 1)) {
                echo NULL; die();
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
  
                }
            }
			// Product Currency
			$currencies = false;
			if($this->config->item('product_currency')==true){
				$currencies = $this->site->getAllCurrencies();
				foreach($currencies as $currency){
					if($currency->code == $row->currency_code){
						$currency->rate = $row->currency_rate;
					}
				}
				$row->price = $row->price * ($row->currency_rate);
				$row->real_currency_rate = $row->currency_rate;
			}
			
			$sale_order = $this->pos_model->getSaleOrderByApproval($row->id);
			$row->quantity -= $sale_order->quantity;
            $row->base_quantity = 1;
            $row->base_unit = $row->unit;
			$row->base_unit_price = $row->price;
            $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
			$row->comment = '';
			
			
			$product_fress = false;
			if($this->config->item('product_promotions')==true){
				$product_promotions = $this->pos_model->getProductPromotions($row->id,$customer_id);
				if($product_promotions){
					$product_pro_qty = 1;
					if($row->base_unit <> $row->unit){
						$product_pro_unit = $this->site->getProductUnit($row->id,$row->unit);
						$product_pro_qty = $product_pro_unit->unit_qty;
					}
					foreach($product_promotions as $product_promotion){
						if($product_pro_qty >= $product_promotion->min_qty && $product_pro_qty <= $product_promotion->max_qty){
							$product_fress[] = array(
								'product_id' => $product_promotion->product_id,
								'product_name' => $product_promotion->product_name .' ('.$product_promotion->product_code.')',
								'product_quantity' => $product_promotion->free_qty,
							);
						}
					}
				}
			}else{
				$product_promotions = false;
			}
			$row->product_frees = $product_fress;
			
            $combo_items = false;
			if ($row->type == 'combo') {
                $combo_items = $this->pos_model->getComboProducts($row->id);
            }
			if ($row->promotion && date('Y-m-d') >= $row->start_date && date('Y-m-d') <= $row->end_date) {
					$row->discount = (100-(($row->promo_price / $row->price) * 100)).'%';
			}else if($this->Settings->customer_price == 1){
				$customer_price = $this->pos_model->getCustomerPrice($row->id,$customer_id);
				if (isset($customer_price) && $customer_price != false) {
					if($customer_price->price > 0){
						$row->price = $customer_price->price;
					}
				}
			}else if ($customer->price_group_id) {
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
			if(in_array('bom',$this->config->item('product_types'))) {
				$bom_typies = $this->pos_model->getTypeBoms($row->id);
				if (isset($bom_typies) && $bom_typies != false) {
					$row->bom_type = $bom_typies[0]->bom_type;
				}else{
					$row->bom_type = '';
				}
			}else{
				$bom_typies = false;
			}
			$row->real_unit_price = $row->price;
			$row->unit_price = $row->price;
            $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
			$product_serials = $this->pos_model->getProductSerialDetailsByProductId($row->id, $warehouse_id);
            $pr = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 'row' => $row, 'product_serials'=>$product_serials, 
                'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'currencies' => $currencies, 'options' => $options, 'enable_bom' => $enable_bom, 'bom_typies' => $bom_typies, 'additional_products' =>$additional_products,
				'product_promotions' => $product_promotions,
			);
            $this->cus->send_json($pr);
        } else {
            echo NULL;
        }
    }

    public function ajaxproducts($category_id = NULL, $brand_id = NULL, $code = NULL, $name = NULL, $favorite = NULL)
    {
        $this->cus->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }
		if($this->input->get('sp_code')){
			$code = $this->input->get('sp_code');
		}
		if($this->input->get('sp_name')){
			$name = $this->input->get('sp_name');
		}
		if($this->input->get('sp_favorite')){
			$favorite = $this->input->get('sp_favorite');
		}
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
			/*=======ADDITION========*/
			if($this->pos_settings->table_enable == 1){
				if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
					$category_id = $this->pos_settings->default_category;
				} else {				
					$warehouse_id = $this->session->userdata("warehouse_id");
					$this->data['categories'] = $this->site->getAllCategoriesByWarehouseId($warehouse_id);				
					$category_id = $this->data['categories'][0]->id;				
				}			
			}else{
				$category_id = $this->pos_settings->default_category;
			}
        }
		
        if ($this->input->get('subcategory_id')) {
            $subcategory_id = $this->input->get('subcategory_id');
        } else {
            $subcategory_id = NULL;
        }
        if ($this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }
        $this->load->library("pagination");
        $config = array();
        $config["base_url"] = base_url() . "pos/ajaxproducts";
        $config["total_rows"] = $this->pos_model->products_count($category_id, $subcategory_id, $brand_id, $code, $name, $favorite);
        $config["per_page"] = $this->pos_settings->pro_limit;
        $config['prev_link'] = FALSE;
        $config['next_link'] = FALSE;
        $config['display_pages'] = FALSE;
        $config['first_link'] = FALSE;
        $config['last_link'] = FALSE;
        $this->pagination->initialize($config);
        $products = $this->pos_model->fetch_products($category_id, $config["per_page"], $page, $subcategory_id, $brand_id, $code, $name, $favorite);
        $pro = 1;
        $prods = '<div>';
        if (!empty($products)) {
            foreach ($products as $product) {
                $count = $product->id;
                if ($count < 10) {
                    $count = "0" . ($count / 100) * 100;
                }
                if ($category_id < 10) {
                    $category_id = "0" . ($category_id / 100) * 100;
                }
				$pfav = "";
				if($this->pos_settings->pos_favorite_items==1 && $product->rate==1){
					$pfav = "bborder";
				}


                $prods .= "<button id=\"product-" . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->code.' '.$product->name . "\" class=\"btn-prni ".$pfav." btn-" . $this->pos_settings->product_button_color . " product pos-tip\" data-container=\"body\">
                	<div class='product_price_tag'>" .$this->cus->formatMoney($product->price).''. "</div>
                	<img src=\"" . base_url() . "assets/uploads/thumbs/" . $product->image . "\" alt=\"" . $product->name . "\" style='width:" . $this->Settings->twidth . "px;height:" . $this->Settings->theight . "px;' class='img-rounded' /><span>" . character_limiter($product->name, 40) . "</span></button>";
                $pro++;



                // $prods .= "<button id=\"product-" . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->code.' '.$product->name . "\" class=\"btn-prni ".$pfav." btn-" . $this->pos_settings->product_button_color . " product pos-tip\" data-container=\"body\">
                
                // <div style='font-weight:bold;background-color: #193654; color:#ffffff;float:right; padding:3px;border-radius: 5px;'>" . number_format($product->price,2).''. "</div>
                // <div style='font-weight:bold;background-color: #193654; color:#ffffff;float:left; padding:3px;border-radius: 5px;'>" . number_format($product->quantity,2).''. "</div>
               
                // <div><img src=\"" . base_url() . "assets/uploads/thumbs/" . $product->image . "\" alt=\"" . $product->name . "\" style='width:" . $this->Settings->twidth . "px;height:" . $this->Settings->theight . "px;' class='img-rounded' /></div><span style='background-color: #193653;color:#ffffff;margin:0px;height: 35px;line-height:15px;'>" . character_limiter($product->name, 15) . "</span></button>";
                // $pro++;
            }
        }
        $prods .= "</div>";

        if ($this->input->get('per_page')) {
            echo $prods;
        } else {
            return $prods;
        }
    }

    public function ajaxcategorydata($category_id = NULL)
    {
        $this->cus->checkPermissions('index');
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
			$code = $this->input->get('sp_code');
			$name = $this->input->get('sp_name');
			$favorite = $this->input->get('sp_favorite');
        } else {
            $category_id = $this->pos_settings->default_category;
        }
        $subcategories = $this->site->getSubCategories($category_id);
        $scats = '';
        if ($subcategories) {
            foreach ($subcategories as $category) {
                $scats .= "<button id=\"subcategory-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" style='width:" . $this->Settings->twidth . "px;height:" . $this->Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
            }
        }
		
		if($this->pos_settings->pos_category_fix==1){
			$scats = '';
			if ($subcategories) {
				foreach ($subcategories as $category) {
					$scats .= "<button type='button' disabled-open-category='true' value='{$category->id}' class='ccategory btn cl-primary subcategory' style='width:16.6666666667%; margin:0px; height:50px; font-weight:bold;'>{$category->name}</button>";
				}
			}
			$scats .= '<div class="clearfix"></div>';
		}

        $products = $this->ajaxproducts($category_id, NULL, $code, $name, $favorite);
        if (!($tcp = $this->pos_model->products_count($category_id, null, null, $code, $name, $favorite))) {
            $tcp = 0;
        }
        $this->cus->send_json(array('products' => $products, 'subcategories' => $scats, 'tcp' => $tcp));
    }

    public function ajaxbranddata($brand_id = NULL)
    {
        $this->cus->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }

        $products = $this->ajaxproducts(FALSE, $brand_id);

        if (!($tcp = $this->pos_model->products_count(FALSE, FALSE, $brand_id))) {
            $tcp = 0;
        }

        $this->cus->send_json(array('products' => $products, 'tcp' => $tcp));
    }
	
	public function close_register2($register_id = false)
	{
        $this->db->select("*")->from("pos_register")->where('pos_register.id', $register_id);
        $q = $this->db->get();
        if($q->num_rows()>0){
            $this->register_report($register_id);
        }
        return false;
    }
	
	public function register_report($register_id = NULL)
    {
		$user_register = $this->pos_model->OpenRegisterData($register_id);
		$register_open_time = $user_register->date;
		$register_close_time = $user_register->closed_at;
		$user_id = $user_register->user_id;
		
        $this->data['cash_in_hand']     = $user_register->cash_in_hand;
		$this->data['total_cash']       = $user_register->total_cash;
		$this->data['total_cash_submitted']     = $user_register->total_cash_submitted;
        $this->data['register_open_time']   = $user_register->date;
        $this->data['register_close_time']  = $register_close_time;
		$this->data['payments'] = $this->pos_model->getRegisterPayments($register_open_time, $user_id);
        $this->data['totalsales']       = $this->pos_model->getRegisterSales($register_open_time, $user_id, $register_close_time);
        $this->data['refunds']          = $this->pos_model->getRegisterRefunds($register_open_time,$user_id, $register_close_time);
        $this->data['expenses']         = $this->pos_model->getRegisterExpenses($register_open_time, $user_id, $register_close_time);		
        $this->data['count_money'] 		= $this->pos_model->getCountMoney($register_open_time, $user_id, $register_close_time);
		$this->data['users']            = $this->pos_model->getUsers($user_id);        
        $this->data['modal_js']         = $this->site->modal_js();        
        $this->load->view($this->theme . 'pos/register_report', $this->data);
    }
	
    public function view($sale_id = NULL, $modal = NULL)
    {
        $this->cus->checkPermissions('index'); 
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->pos_model->getAllInvoiceItemsGroup($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();		
        //$this->data['return_sale'] = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
        //$this->data['return_rows'] = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
        //$this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
        $this->data['return_sale'] = false;
		$this->data['return_rows'] = false;
		$this->data['return_payments'] = false;
		$this->data['inv'] = $inv;
		if($this->pos_settings->table_enable == 1){
			$this->data['table'] = $this->pos_model->getTableById($inv->table_id);
		}else{
			$this->data['table'] = false;
		}
        $this->data['sid'] = $sale_id;
        $this->data['modal'] = $modal;
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['printer'] = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title'] = $this->lang->line("invoice");
        $this->load->view($this->theme . 'pos/view', $this->data);
    }
	
	public function register_items()
    {
        $this->cus->checkPermissions('index');
        $register_open_time = $this->session->userdata('register_open_time');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time);
		$this->data['totalsale_items'] = $this->pos_model->getRegisterSaleItems($register_open_time);
		$this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time);
		$this->data['refund_item'] = $this->pos_model->getSaleReturnItems($register_open_time);
        $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time);
        $this->load->view($this->theme . 'pos/register_items', $this->data);
    }
	
	public function register_items_report($register_id = false)
    {
        $this->cus->checkPermissions('index');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['register'] = $this->pos_model->getResiterByID($register_id);
		$this->data['register_items'] = $this->pos_model->getResiterItem($register_id);
        $this->load->view($this->theme . 'pos/register_items_report', $this->data);
    }

    public function register_details()
    {
        $this->cus->checkPermissions('index');
        $register_open_time = $this->session->userdata('register_open_time');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time);
		$this->data['totaldiscount'] = $this->pos_model->getRegisterSaleDiscounts($register_open_time);
		$this->data['refund_item'] = $this->pos_model->getSaleReturnItems($register_open_time);
		$this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time);
        $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time);
		$this->data['payments'] = $this->pos_model->getRegisterPayments($register_open_time);
		$this->data['total_cash'] = $this->pos_model->getTotalCash($register_open_time);
        $this->load->view($this->theme . 'pos/register_details', $this->data);
    }

    public function today_sale()
    {
        if (!$this->Owner && !$this->Admin) {
			$access_biller = $this->session->userdata['biller_id'];
        }else{
			$access_biller = 0;
		}

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['totalsales'] = $this->pos_model->getTodaySales($access_biller);
		$this->data['refund_item'] = $this->pos_model->getTodaySaleReturnItems($access_biller);
        $this->data['refunds'] = $this->pos_model->getTodayRefunds($access_biller);
        $this->data['expenses'] = $this->pos_model->getTodayExpenses($access_biller);
		$this->data['payments'] = $this->pos_model->getTodayPayments($access_biller);
		$this->data['total_cash'] = $this->pos_model->getTodayCash($access_biller);
        $this->load->view($this->theme . 'pos/today_sale', $this->data);
    }

    public function check_pin()
    {
        $pin = $this->input->post('pw', TRUE);
        if ($pin == $this->pos_pin) {
            $this->cus->send_json(array('res' => 1));
        }
        $this->cus->send_json(array('res' => 0));
    }

    public function barcode($text = NULL, $bcs = 'code128', $height = 50)
    {
        return site_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }

    public function settings()
    {
        if (!$this->Owner && !$this->GP['system_settings']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('pro_limit', $this->lang->line('pro_limit'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('category', $this->lang->line('default_category'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('customer', $this->lang->line('default_customer'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('biller', $this->lang->line('default_biller'), 'required|is_natural_no_zero');

        if ($this->form_validation->run() == TRUE) {

            $data = array(
                'pro_limit'                 => $this->input->post('pro_limit'),
                'pin_code'                  => $this->input->post('pin_code') ? $this->input->post('pin_code') : NULL,
                'default_category'          => $this->input->post('category'),
                'default_customer'          => $this->input->post('customer'),
                'default_biller'            => $this->input->post('biller'),
                'display_time'              => $this->input->post('display_time'),
                'receipt_printer'           => $this->input->post('receipt_printer'),
                'cash_drawer_codes'         => $this->input->post('cash_drawer_codes'),
                'cf_title1'                 => $this->input->post('cf_title1'),
                'cf_title2'                 => $this->input->post('cf_title2'),
                'cf_value1'                 => $this->input->post('cf_value1'),
                'cf_value2'                 => $this->input->post('cf_value2'),
                'focus_add_item'            => $this->input->post('focus_add_item'),
				'edit_last_item'            => $this->input->post('edit_last_item'),
                'add_manual_product'        => $this->input->post('add_manual_product'),
                'customer_selection'        => $this->input->post('customer_selection'),
                'add_customer'              => $this->input->post('add_customer'),
                'toggle_category_slider'    => $this->input->post('toggle_category_slider'),
                'toggle_subcategory_slider' => $this->input->post('toggle_subcategory_slider'),
                'toggle_brands_slider'      => $this->input->post('toggle_brands_slider'),
                'cancel_sale'               => $this->input->post('cancel_sale'),
                'suspend_sale'              => $this->input->post('suspend_sale'),
                'print_items_list'          => $this->input->post('print_items_list'),
                'finalize_sale'             => $this->input->post('finalize_sale'),
                'today_sale'                => $this->input->post('today_sale'),
                'open_hold_bills'           => $this->input->post('open_hold_bills'),
                'close_register'            => $this->input->post('close_register'),
                'tooltips'                  => $this->input->post('tooltips'),
                'keyboard'                  => $this->input->post('keyboard'),
                'pos_printers'              => $this->input->post('pos_printers'),
                'java_applet'               => $this->input->post('enable_java_applet'),
                'product_button_color'      => $this->input->post('product_button_color'),
                'paypal_pro'                => $this->input->post('paypal_pro'),
                'stripe'                    => $this->input->post('stripe'),
                'authorize'                 => $this->input->post('authorize'),
                'rounding'                  => $this->input->post('rounding'),
                'item_order'                => $this->input->post('item_order'),
                'after_sale_page'           => $this->input->post('after_sale_page'),
                'printer'                   => $this->input->post('receipt_printer'),
				'table_enable'              => $this->input->post('table_enable'),
				'queue_enable'              => $this->input->post('queue_enable'),
				'queue_expiry'              => $this->input->post('queue_enable')==1?$this->input->post('queue_expiry'):0,
                'order_printers'            => json_encode($this->input->post('order_printers')),
                'auto_print'                => $this->input->post('auto_print'),
                'remote_printing'           => DEMO ? 1 : $this->input->post('remote_printing'),
                'customer_details'          => $this->input->post('customer_details'),
				'pos_redirect_order'        => $this->input->post('pos_redirect_order'),
				'pos_payment_sale_note'     => $this->input->post('pos_payment_sale_note'),
				'pos_delivery'          	=> $this->input->post('pos_delivery'),
				'pos_multi_payment'			=> $this->input->post('pos_multi_payment'),
				'pos_layout_fix'			=> $this->input->post('pos_layout_fix'),
				'pos_category_fix'			=> $this->input->post('pos_category_fix'),
				'pos_order_display'			=> $this->input->post('pos_order_display'),
				'pos_favorite_items'		=> $this->input->post('pos_favorite_items'),
				'allow_min_price'           => $this->input->post('allow_min_price'),
				'default_floor'          	=> $this->input->post('floor'),
				'screen_display'          	=> $this->input->post('screen_display'),
				'quick_payable'				=> $this->input->post('quick_payable'),
				'pos_payment'				=> $this->input->post('pos_payment'),
				'quick_pos'					=> $this->input->post('quick_pos'),
            );
			
            $payment_config = array(
                'APIUsername'            => $this->input->post('APIUsername'),
                'APIPassword'            => $this->input->post('APIPassword'),
                'APISignature'           => $this->input->post('APISignature'),
                'stripe_secret_key'      => $this->input->post('stripe_secret_key'),
                'stripe_publishable_key' => $this->input->post('stripe_publishable_key'),
                'api_login_id'           => $this->input->post('api_login_id'),
                'api_transaction_key'    => $this->input->post('api_transaction_key'),
            );
        } elseif ($this->input->post('update_settings')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("pos/settings");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->updateSetting($data)) {
            if (DEMO) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                redirect("pos/settings");
            }
            if ($this->write_payments_config($payment_config)) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                redirect("pos/settings");
            } else {
                $this->session->set_flashdata('error', $this->lang->line('pos_setting_updated_payment_failed'));
                redirect("pos/settings");
            }
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
			$this->data['floors'] = $this->site->getAllFloors();
            $this->data['pos'] = $this->pos_model->getSetting();
            $this->data['categories'] = $this->site->getAllCategories();
            //$this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
            $this->data['billers'] = $this->pos_model->getAllBillerCompanies();
            $this->config->load('payment_gateways');
            $this->data['stripe_secret_key'] = $this->config->item('stripe_secret_key');
            $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
            $authorize = $this->config->item('authorize');
            $this->data['api_login_id'] = $authorize['api_login_id'];
            $this->data['api_transaction_key'] = $authorize['api_transaction_key'];
            $this->data['APIUsername'] = $this->config->item('APIUsername');
            $this->data['APIPassword'] = $this->config->item('APIPassword');
            $this->data['APISignature'] = $this->config->item('APISignature');
            $this->data['printers'] = $this->pos_model->getAllPrinters();
            $this->data['paypal_balance'] = NULL; // $this->pos_settings->paypal_pro ? $this->paypal_balance() : NULL;
            $this->data['stripe_balance'] = NULL; // $this->pos_settings->stripe ? $this->stripe_balance() : NULL;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('pos_settings')));
            $meta = array('page_title' => lang('pos_settings'), 'bc' => $bc);
            $this->core_page('pos/settings', $meta, $this->data);
        }
    }

    public function write_payments_config($config)
    {
        if (!$this->Owner && !$this->GP['pos_settings']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        if (DEMO) {
            return TRUE;
        }
        $file_contents = file_get_contents('./assets/config_dumps/payment_gateways.php');
        $output_path = APPPATH . 'config/payment_gateways.php';
        $this->load->library('parser');
        $parse_data = array(
            'APIUsername'            => $config['APIUsername'],
            'APIPassword'            => $config['APIPassword'],
            'APISignature'           => $config['APISignature'],
            'stripe_secret_key'      => $config['stripe_secret_key'],
            'stripe_publishable_key' => $config['stripe_publishable_key'],
            'api_login_id'           => $config['api_login_id'],
            'api_transaction_key'    => $config['api_transaction_key'],
        );
        $new_config = $this->parser->parse_string($file_contents, $parse_data);

        $handle = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new_config)) {
                @chmod($output_path, 0644);

                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public function opened_bills($per_page = 0)
    {
        $this->load->library('pagination');

        //$this->table->set_heading('Id', 'The Title', 'The Content');
        if ($this->input->get('per_page')) {
            $per_page = $this->input->get('per_page');
        }

        $config['base_url'] = site_url('pos/opened_bills');
        $config['total_rows'] = $this->pos_model->bills_count();
        $config['per_page'] = 6;
        $config['num_links'] = 3;

        $config['full_tag_open'] = '<ul class="pagination pagination-sm">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';

        $this->pagination->initialize($config);
        $data['r'] = TRUE;
        $bills = $this->pos_model->fetch_bills($config['per_page'], $per_page);
        if (!empty($bills)) {
            $html = "";
            $html .= '<ul class="ob">';
            foreach ($bills as $bill) {
                $html .= '<li><button type="button" class="btn cl-info sus_sale" id="' . $bill->id . '"><p>' . $bill->suspend_note . '</p><strong>' . $bill->customer . '</strong><br>'.lang('date').': ' . $this->cus->hrld($bill->date) . '<br>'.lang('items').': ' . $bill->count . '<br>'.lang('total').': ' . $this->cus->formatMoney($bill->total) . '</button></li>';
            }
            $html .= '</ul>';
        } else {
            $html = "<h3>" . lang('no_opeded_bill') . "</h3><p>&nbsp;</p>";
            $data['r'] = FALSE;
        }

        $data['html'] = $html;
        $data['page'] = $this->pagination->create_links();
        echo $this->load->view($this->theme . 'pos/opened', $data, TRUE);
    }

    public function delete($id = NULL)
    {
        $this->cus->checkPermissions('index');

        if ($this->pos_model->deleteBill($id)) {
            echo lang("suspended_sale_deleted");
        }
    }

    public function email_receipt($sale_id = NULL)
    {
        $this->cus->checkPermissions('index');
        if ($this->input->post('id')) {
            $sale_id = $this->input->post('id');
        } 
        if ( ! $sale_id) {
            die('No sale selected.');
        }
        if ($this->input->post('email')) {
            $to = $this->input->post('email');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');

        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);

        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['page_title'] = $this->lang->line("invoice");

        if (!$to) {
            $to = $this->data['customer']->email;
        }
        if (!$to) {
            $this->cus->send_json(array('msg' => $this->lang->line("no_meil_provided")));
        }
        $receipt = $this->load->view($this->theme . 'pos/email_receipt', $this->data, TRUE);

        if ($this->cus->send_email($to, 'Receipt from ' . $this->data['biller']->company, $receipt)) {
            $this->cus->send_json(array('msg' => $this->lang->line("email_sent")));
        } else {
            $this->cus->send_json(array('msg' => $this->lang->line("email_failed")));
        }

    }

    public function active()
    {
        $this->session->set_userdata('last_activity', now());
        if ((now() - $this->session->userdata('last_activity')) <= 20) {
            die('Successfully updated the last activity.');
        } else {
            die('Failed to update last activity.');
        }
    }

    public function updates()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner && !$this->GP['pos_settings']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->form_validation->set_rules('purchase_code', lang("purchase_code"), 'required');
        $this->form_validation->set_rules('envato_username', lang("envato_username"), 'required');
        if ($this->form_validation->run() == TRUE) {
            $this->db->update('pos_settings', array('purchase_code' => $this->input->post('purchase_code', TRUE), 'envato_username' => $this->input->post('envato_username', TRUE)), array('pos_id' => 1));
            redirect('pos/updates');
        } else {
            $fields = array('version' => $this->pos_settings->version, 'code' => $this->pos_settings->purchase_code, 'username' => $this->pos_settings->envato_username, 'site' => base_url());
            $this->load->helper('update');
            $protocol = is_https() ? 'https://' : 'http://';
            $updates = get_remote_contents($protocol . 'sunfixconsulting.com/api/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('updates')));
            $meta = array('page_title' => lang('updates'), 'bc' => $bc);
            $this->core_page('pos/updates', $meta, $this->data);
        }
    }

    public function install_update($file, $m_version, $version)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
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
                redirect("pos/updates");
            }
        }
        $this->db->update('pos_settings', array('version' => $version, 'update' => 0), array('pos_id' => 1));
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        redirect("pos/updates");
    }

    public function open_drawer() 
	{
        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->open_drawer();
    }

    public function p() 
	{
		$this->load->library('escpos');
        $data = json_decode($this->input->get('data'));
		
		$types = $this->pos_model->getAllTypes();
		foreach($types as $type){
			$cat_types[strtolower($type->name)] = $type->printer_id;
		}
		
		$packages = array();		
		if($data->text->attributes && $this->pos_settings->table_enable == 1){	
			
			$attributes = $data->text->attributes;
			foreach($attributes as $attribute){
				$code[] = $attribute->code;
				$packages[$attribute->category_type][] = array(
																"cf1"  => $attribute->cf1,
																"code" => $attribute->code,
																"name" => $attribute->name,
																"qty" => $attribute->qty,
																"category_type" => $attribute->category_type,
																"item_comment" => $attribute->item_comment,
																);
			}
			$groupTypes = $this->pos_model->getAllGroupTypes($code);
			foreach($groupTypes as $type){
				$printer_id = 0;
				if(array_key_exists(strtolower($type->name), $cat_types)){
					$printer_id = $cat_types[strtolower($type->name)];
				}
				$printer = $this->pos_model->getPrinterByID($printer_id);
				if($printer){					
					unset($data->text->attributes);
					$data->printer->id = $printer->id;
					$data->printer->title = $printer->title;
					$data->printer->path = $printer->path;
					$data->printer->ip_address = $printer->ip_address;	
					$data->text->header = $data->text->header;
					$data->text->store_name = $data->text->store_name;
					$data->text->info = $data->text->info;
					$html = "";
					if($packages[$type->type]){
						/*foreach($packages[$type->type] as $i => $item){
							$code = $item['code'];
							if($item['category_type']=='Drink'){
								$code = $item['code']." - ".$item['name'];
							}
							$html .= str_pad($i+1, 3) . str_pad($code, 35) . str_pad($this->cus->formatQuantity($item['qty']), 10, ' ', STR_PAD_LEFT) . "\n";							
						}*/
						$html .= "<table>";
							$html .="<tr><th style='font-size:40px; text-align:center;'>".$data->text->store_name."</th></tr>";
							$html .="<tr><th style='text-align:center;'>".$data->text->header."</th></tr>";
							$html .="<tr><th>&nbsp;</td></tr>";
						$html .= "</table>";
						
						$html .= "<table>";
							$html .="<tr><th style='font-size:20px;text-align:left;'>".lang("customer")." : ".$data->text->customer."</th></tr>";
							$html .="<tr><th style='font-size:20px;text-align:left;'>".lang("user")." : ".ucfirst($data->text->user)."</th></tr>";
							$html .="<tr><th style='font-size:20px;text-align:left;'>".lang("date")." : ".$data->text->date."</th></tr>";
							$html .="<tr><th style='font-size:20px;text-align:left;'>".lang("table")." : ".$data->text->table."</th></tr>";
							$html .="<tr><th>&nbsp;</td></tr>";
						$html .= "</table>";
						
						$html .= "<table class='table table-striped table-condensed'>";
							$html .= "<thead>";
								$html .= "<tr style='background:#000; color:#FFF;'>";
									$html .= "<th>#</th>";
									$html .= "<th>".lang("code")."</th>";
									$html .= "<th>".lang("name")."</th>";
									$html .= "<th>".lang("quantity")."</th>";
								$html .= "</tr>";
							$html .= "</thead>";
						$i = 0;
						foreach($packages[$type->type] as $item){
							$item_comment = "";
							if(!empty($item['item_comment']) || $item['item_comment'] != ""){
								$item_comment = "<br/><small style='font-size:16px;'>(".$item['item_comment'].")</small>";
							}
							if($item['qty'] > 0){
								$html .= "<tr>";
									$html .= "<th style='text-align:center;'>".($i+1).". </th>";
									$html .= "<th style='text-align:center;'>".$item['code']."</th>";
									$html .= "<th style='text-align:left;'>".$item['name'].$item_comment."</th>";
									$html .= "<th style='text-align:center;'>".$this->cus->formatQuantity($item['qty'])."</th>";
								$html .= "</tr>";
								$i++;
							}
						}
						$html .= "<tr><th colspan=4 style='text-align:center;font-size:16px;'><br/><br/><br/> ~ www.sunfixconsulting.com ~ <br/> 093 471 106 / 017 991 938</th></tr>";
						$html .= "</table>";
					}
					
					$data->text->items = $html;
					$this->escpos->load($data->printer);
					$this->escpos->print_order_html($data);
				}
			}
			echo json_encode($data);			
		}else {		
			echo json_encode($data);			
			$this->escpos->load($data->printer);		
			$this->escpos->print_receipt($data);
		}
    }
	
	public function p_bill()
	{
		$this->load->library('escpos');
        $data = json_decode($this->input->get('data'));
		$sid = $this->input->get("sid");
		
		if($this->pos_settings->table_enable == 1){
			$printer_id = $this->pos_settings->printer;
			$printer = $this->pos_model->getPrinterByID($printer_id);
			if($printer){
				$suspend = $this->pos_model->getSuspendByID($sid);
				$reference_no = $this->site->getReference("bl", $suspend->biller_id);
				$items = array();
				$key = 0;
				$attributes = $data->text->attributes_bill;
				foreach ($attributes as $item) {
					$key = $item->code;
					if (!array_key_exists($key, $items)) {
						$items[$key] = array(
										"cf1"  => $item->cf1,
										"code" => $item->code,
										"name" => $item->name,
										"qty" => $item->qty,
										"item_price" => $item->item_price,
										"item_discount" => $item->item_discount,
										"category_type" => $item->category_type,
										"item_comment" => $item->item_comment,
									);
					} else {
						$items[$key]['qty'] = $items[$key]['qty'] + $item->qty;
						$items[$key]['item_discount'] = $items[$key]['item_discount'] + $item->item_discount;
					}
					$key++;
				}
			
				$html .= "<table>";
					$html .="<tr><th style='font-size:30px; text-align:center;'>".$data->text->bill_company."</th></tr>";
					$html .="<tr><th style='text-align:center;'>".$data->text->bill_address."</th></tr>";
					$html .="<tr><th style='text-align:center;'>".$data->text->bill_phone."</th></tr>";
					$html .="<tr><th style='text-align:center;'>".$data->text->header."</th></tr>";
					$html .="<tr><th>&nbsp;</td></tr>";
				$html .= "</table>";
				
				$html .= "<table>";
					$html .="<tr><th style='font-size:20px;text-align:left;'>".lang("customer")." : ".$data->text->customer."</th></tr>";
					$html .="<tr><th style='font-size:20px;text-align:left;'>".lang("reference_no")." : ".$reference_no."</th></tr>";
					$html .="<tr><th style='font-size:20px;text-align:left;'>".lang("user")." : ".ucfirst($data->text->user)."</th></tr>";
					$html .="<tr><th style='font-size:20px;text-align:left;'>".lang("date")." : ".$data->text->date."</th></tr>";
					$html .="<tr><th style='font-size:20px;text-align:left;'>".lang("table")." : ".$data->text->table."</th></tr>";
					$html .="<tr><th>&nbsp;</td></tr>";
				$html .= "</table>";
				
				$html .= "<table class='table table-striped table-condensed'>";
					$html .= "<thead>";
						$html .= "<tr style='background:#000; color:#FFF;'>";
							$html .= "<th>#</th>";
							$html .= "<th>".lang("description")."</th>";
							$html .= "<th>".lang("quantity")."</th>";
							$html .= "<th>".lang("unit_price")."</th>";
							$html .= "<th>".lang("total")."</th>";
						$html .= "</tr>";
					$html .= "</thead>";
				
				$i = 0;
				foreach($items as $item){
					$item_comment = "";
					if(!empty($item['item_comment']) || $item['item_comment'] != ""){
						$item_comment = "<br/><small style='font-size:16px;'>(".$item['item_comment'].")</small>";
					}
					if($item['qty'] > 0){
						$subtotal = ($item['item_price'] * $item['qty']);
						$html .= "<tr>";
							$html .= "<th style='text-align:center;'>".($i+1).". </th>";
							$html .= "<th style='text-align:left;'>".$item['name'].$item_comment."</th>";
							$html .= "<th style='text-align:center;'>".$this->cus->formatQuantity($item['qty'])."</th>";
							$html .= "<th style='text-align:right;'>".$this->cus->formatMoney($item['item_price'])."</th>";
							$html .= "<th style='text-align:right;'>".$this->cus->formatMoney($subtotal)."</th>";
						$html .= "</tr>";
						$i++;
					}
				}
				$currency = $this->site->getCurrencyByCode("KHR");
				$total = $data->text->total;
				$grand_total = $data->text->grand_total;
				$order_discount = $data->text->order_discount;
				$invoice_tax = $data->text->invoice_tax;
				
				$html .= "<tr>
							<th style='text-align:right;' colspan=4>".lang("total")."</th>
							<th style='text-align:right;'>".$this->cus->formatMoney($total)."</th>
						  </tr>";
						  
				if($order_discount > 0){
					$html .= "<tr>
								<th style='text-align:right;' colspan=4>".lang("discount")."</th>
								<th style='text-align:right;'>".$this->cus->formatMoney($order_discount)."</th>
							  </tr>";
				}
				if($invoice_tax > 0){
					$html .= "<tr>
								<th style='text-align:right;' colspan=4>".lang("order_tax")."</th>
								<th style='text-align:right;'>".$this->cus->formatMoney($invoice_tax)."</th>
							  </tr>";
				}	
				$html .= "<tr>
							<th style='text-align:right;' colspan=4>".lang("grand_total")."</th>
							<th style='text-align:right;'>".$this->cus->formatMoney($grand_total)."</th>
						  </tr>";
				
				$html .= "<tr>
							<th style='text-align:right;' colspan=4>".lang("grand_total")." ~ ".$currency->name."</th>
							<th style='text-align:right;'>".$this->cus->formatMoneyKH($grand_total * $currency->rate)."</th>
						  </tr>";
						  
				$html .= "<tr><th colspan=5 style='text-align:center;font-size:16px;'><br/><br/><br/> ~ www.sunfixconsulting.com ~ <br/> 093 471 106 / 017 991 938</th></tr>";
				$html .= "</table>";
				
				$bill_data = array(
								"print" 		=> 1, 
								"print_by" 		=> $this->session->userdata("user_id"),
								"reference_no"	=> $reference_no,
							);
				$this->pos_model->updatePrintBill($sid, $bill_data);
				$data->text->items = $html;
				$this->escpos->load($data->printer);					
				$this->escpos->print_bill_html($data);
			}
		}else {
			echo json_encode($data);
		}
	}
	
	public function p_invoice()
	{
		$this->load->library('escpos');
        $data = json_decode($this->input->get('data'));
		$sale_id = $this->input->get('sale_id');
		if ($this->pos_settings->after_sale_page) {
			$printer_id = $this->pos_settings->printer;
			$printer = $this->pos_model->getPrinterByID($printer_id);
			$sale = $this->pos_model->getSaleByID($sale_id);
			if($printer && $sale->print==0){
				$this->escpos->load($data->printer);
				$items = $this->p_view($sale_id);
				$this->escpos->print_receipt_html($items);
				$this->pos_model->updatePrint($sale_id, array("print"=>1));
				return false;
			}else{
				echo lang('printer_not_found');
			}
		}else{
			echo lang('please_set_auto_printing');
		}
	}
	
	public function p_view($sale_id = NULL, $modal = NULL)
    {
        $this->cus->checkPermissions('index');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->pos_model->getAllInvoiceItemsGroup($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();		
        $this->data['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale'] = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
        $this->data['inv'] = $inv;
		if($this->pos_settings->table_enable == 1){
			$this->data['table'] = $this->pos_model->getTableById($inv->table_id);
		}
        $this->data['sid'] = $sale_id;
        $this->data['modal'] = $modal;
		$this->data['delivery'] = $this->pos_model->getDeliveryBySaleID($sale_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
		$this->data['saleman'] = $this->site->getUser($inv->saleman_id);
		$this->data['currency_kh'] = $this->site->getCurrencyByCode('KHR');
        $this->data['printer'] = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title'] = $this->lang->line("invoice");
        return $this->load->view($this->theme . 'pos/view_auto', $this->data, true);
    }
	
    public function printers()
    {
        if (!$this->Owner && !$this->GP['list_printers-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("pos");
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('printers');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('printers')));
        $meta = array('page_title' => lang('list_printers'), 'bc' => $bc);
        $this->core_page('pos/printers', $meta, $this->data);
    }

    public function get_printers()
    {
        if (!$this->Owner && !$this->GP['list_printers-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->cus->md();
        }

        $this->load->library('datatables');
        $this->datatables
        ->select("id, title, type, profile, path, ip_address, port")
        ->from("printers")
        ->add_column("Actions", "<div class='text-center'> <a href='" . site_url('pos/edit_printer/$1') . "' class='btn-warning btn-xs tip' title='".lang("edit_printer")."'><i class='fa fa-edit'></i></a> <a href='#' class='btn-danger btn-xs tip po' title='<b>" . lang("delete_printer") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('pos/delete_printer/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
        ->unset_column('id');
        echo $this->datatables->generate();

    }

    public function add_printer()
    {

        if (!$this->Owner && !$this->GP['list_printers-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("pos");
        }

        $this->form_validation->set_rules('title', $this->lang->line("title"), 'required');
        $this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line("profile"), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line("char_per_line"), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'required|is_unique[printers.ip_address]');
            $this->form_validation->set_rules('port', $this->lang->line("port"), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line("path"), 'required|is_unique[printers.path]');
        }

        if ($this->form_validation->run() == true) {

            $data = array('title' => $this->input->post('title'),
                'type' => $this->input->post('type'),
                'profile' => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path' => $this->input->post('path'),
                'ip_address' => $this->input->post('ip_address'),
                'port' => ($this->input->post('type') == 'network') ? $this->input->post('port') : NULL,
            );

        }

        if ( $this->form_validation->run() == true && $cid = $this->pos_model->addPrinter($data)) {

            $this->session->set_flashdata('message', $this->lang->line("printer_added"));
            redirect("pos/printers");

        } else {
            if($this->input->is_ajax_request()) {
                echo json_encode(array('status' => 'failed', 'msg' => validation_errors())); die();
            }

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_printer');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => site_url('pos/printers'), 'page' => lang('printers')), array('link' => '#', 'page' => lang('add_printer')));
            $meta = array('page_title' => lang('add_printer'), 'bc' => $bc);
            $this->core_page('pos/add_printer', $meta, $this->data);
        }
    }

    public function edit_printer($id = NULL)
    {

        if (!$this->Owner && !$this->GP['list_printers-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("pos");
        }
        if($this->input->get('id')) { $id = $this->input->get('id', TRUE); }

        $printer = $this->pos_model->getPrinterByID($id);
        $this->form_validation->set_rules('title', $this->lang->line("title"), 'required');
        $this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line("profile"), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line("char_per_line"), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'required');
            if ($this->input->post('ip_address') != $printer->ip_address) {
                $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'is_unique[printers.ip_address]');
            }
            $this->form_validation->set_rules('port', $this->lang->line("port"), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line("path"), 'required');
            if ($this->input->post('path') != $printer->path) {
                $this->form_validation->set_rules('path', $this->lang->line("path"), 'is_unique[printers.path]');
            }
        }

        if ($this->form_validation->run() == true) {

            $data = array('title' => $this->input->post('title'),
                'type' => $this->input->post('type'),
                'profile' => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path' => $this->input->post('path'),
                'ip_address' => $this->input->post('ip_address'),
                'port' => ($this->input->post('type') == 'network') ? $this->input->post('port') : NULL,
            );

        }

        if ( $this->form_validation->run() == true && $this->pos_model->updatePrinter($id, $data)) {

            $this->session->set_flashdata('message', $this->lang->line("printer_updated"));
            redirect("pos/printers");

        } else {

            $this->data['printer'] = $printer;
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('edit_printer');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => site_url('pos/printers'), 'page' => lang('printers')), array('link' => '#', 'page' => lang('edit_printer')));
            $meta = array('page_title' => lang('edit_printer'), 'bc' => $bc);
            $this->core_page('pos/edit_printer', $meta, $this->data);

        }
    }

    public function delete_printer($id = NULL)
    {
        if(DEMO) {
            $this->session->set_flashdata('error', $this->lang->line("disabled_in_demo"));
            $this->cus->md();
        }
        if (!$this->Owner && !$this->GP['list_printers-index']) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->cus->md();
        }

        if ($this->input->get('id')) { $id = $this->input->get('id', TRUE); }

        if ($this->pos_model->deletePrinter($id)) {
            echo lang("printer_deleted");
        }

    }
	
	/**============Suspend table============**/
	
	public function delete_suspend($id = NULL)
    {    
		$this->cus->checkPermissions('delete_table');
		if($this->pos_settings->table_enable <= 0){
			if ($this->pos_model->deleteSuspendedById($id)) {
				$this->session->set_userdata('remove_posls', 1);
				$this->session->set_flashdata('message', lang("suspend_cleared"));
				redirect("pos");
			}
		}else{
			if ($this->pos_model->deleteSuspendedBillByTableId($id)) {
				$this->session->set_userdata('remove_posls', 1);
				$this->session->set_flashdata('message', lang("table_cleared"));
				redirect("pos/add_table");
			}
		}
    }
	
	public function update_bill()
	{		
		$suspend_id = $this->input->get("suspend_id");
		if($suspend_id){			
			$this->pos_model->updateBillPrint($suspend_id);
			$this->load_suspend_items($suspend_id);
		}
	}
	
	public function add_table()
	{	
		$warehouse = $this->input->get("warehouse");
		$floor = $this->input->get("floor");		
		$delete_id = $this->input->post("delete_id");
		$warehouse_id = $this->input->post("warehouse_id");
		$customer_id = $this->input->post("customer_id");
		$table_id = $this->input->post('table_id');			
		$bill_id = $this->input->post('bill_id');
		if (!$this->pos_settings->default_biller || !$this->pos_settings->default_customer || !$this->pos_settings->default_category) {
            $this->session->set_flashdata('warning', lang('please_update_settings'));
            redirect('pos/settings');
        }
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = array('register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date);
            $this->session->set_userdata($register_data);
        } else {
            $this->session->set_flashdata('error', lang('register_not_open'));
            redirect('pos/open_register');
        }
		$this->form_validation->set_rules('customer_id', $this->lang->line("customer"), 'trim|required');
        $this->form_validation->set_rules('warehouse_id', $this->lang->line("warehouse"), 'required');
        if ($this->form_validation->run() == TRUE){
			$customer_details = $this->site->getCompanyByID($customer_id);
			$table_details = $this->pos_model->getTableById($table_id);
			$data = array(
						"date" => date("Y-m-d H:i"),
						"created_by" => $this->session->userdata("user_id"),
						"warehouse_id" => $warehouse_id,
						"customer_id" => $customer_id,
						"customer" 	=> $customer_details->name,
						"table_id" => $table_id,
						"table_name" => $table_details->name,
					);
			if($delete_id && $bill_id > 0){
				$item = $this->pos_model->getSuspendByID($delete_id);				
				$data_merge = array(
						'count' => $item->count,
						"table_id" => $table_id,
						"table_name" => $table_details->name,
						'total' => $item->total,
						"warehouse_id"	=> $warehouse_id,
					);

			}else if($delete_id){
				$data_split = array(
						"table_id" => $table_id,
						"table_name" => $table_details->name,
					);
					
				if(!isset($_POST['val'])){
					$this->session->set_flashdata("error", lang("no_items_selected"));
					redirect("pos/add_table");
				} 
			}
		}
		if($delete_id && $bill_id > 0){			
			if($this->form_validation->run() == TRUE && $this->pos_model->addMergeSaleSubspend($data_merge, $delete_id, $bill_id)){
				$this->session->set_flashdata("message", lang("suspend_items_moved"));
				redirect("pos/add_table");
			}
		}else if($delete_id){
			if($this->form_validation->run() == TRUE && $this->pos_model->addSplitSaleSubspend($data_split, $delete_id)){
				$this->session->set_flashdata("message", lang("suspend_items_splited"));
				redirect("pos/add_table");
			}
		}else {
			if($this->form_validation->run() == TRUE && $bill_id = $this->pos_model->addSaleSubspend($data)){
				$this->session->set_flashdata("message", lang("suspend_items_added"));
				redirect("pos/index/".$bill_id);
			}
		}
        $floor_id = json_decode($this->session->userdata('floor_ids'));		
        if(!$this->Owner && !$this->Admin && $floor_id){
            $floors = $this->pos_model->getFloorsByID($floor_id);
        }else{
            $floors = $this->pos_model->getAllFloors();
        }
		$this->data['floors'] = $floors;
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['sbills'] = $this->pos_model->getSuspendedBills();
		$this->data['tables'] = $this->pos_model->getAllTablesBy($warehouse, $floor);
		$this->load->view($this->theme . 'pos/add_table', $this->data);
	}
	
	public function ajax_tables()
    {
		if (!$this->input->is_ajax_request()){
			redirect("pos");
		}
		$sbills_total = array();
		$sbills_date = array();
		$sbills_id = array();
		$sbills_count = array();	
		$sbills_user = array();
		$sbills_print = array();
		$sbills_status = array();
		$shtml = "";
		$warehouse = $this->input->get("warehouse_id");
		$floor = $this->input->get("floor_id");	
		$bill_id = $this->input->get("bill_id");
		$sbills = $this->pos_model->getSuspendedBills();
        $floors = json_decode($this->session->userdata('floor_ids'));
        if(!$this->Owner && !$this->Admin && $floors){
            $tables = $this->pos_model->getAllTablesByFloor($warehouse, $floors);
        }else{
            $tables = $this->pos_model->getAllTablesBy($warehouse, $floor);
        }
		if($sbills){
			foreach($sbills as $sbill){
				$row = $this->pos_model->getSuspendItemsByTableID($sbill->table_id);
				$sbills_total[$sbill->table_id] = $row->total;
				$sbills_count[$sbill->table_id] = $row->count;
				$sbills_date[$sbill->table_id] = $sbill->date;
				$sbills_id[$sbill->table_id] = $sbill->id;
				$sbills_user[$sbill->table_id] = $sbill->created_by;
				$sbills_print[$sbill->table_id] = $sbill->print;
				$sbills_status[$sbill->table_id] = $sbill->suspend_status;
			}
		}

		if($tables){
			foreach ($tables as $table) {
				if (array_key_exists($table->id, $sbills_date)) {
					$time = date("h:i A", strtotime($sbills_date[$table->id]));
				}else{
					$time = "";
				}
				if (array_key_exists($table->id, $sbills_count)) {
					$count = $sbills_count[$table->id];
				}else{
					$count = 0;
				}
				if (array_key_exists($table->id, $sbills_total)) {
					$amount = $this->cus->formatMoney($sbills_total[$table->id]);
				}else{
					$amount = 0;		
				}
				if (array_key_exists($table->id, $sbills_id)) {
					$sbill_id = $sbills_id[$table->id];	
				}else{
					$sbill_id = 0;		
				}
				if (array_key_exists($table->id, $sbills_status)) {
					$suspend_stauts = $sbills_status[$table->id];
				}else{
					$suspend_stauts = '';		
				}
				$class_suspend  = $bill_id ? "move_suspend" : "add_suspend";
				$class_disabled = $bill_id == $sbill_id ? "disabled" : "";
				if($sbill_id > 0){
					if($count > 0){
						$style_bill = "cl-warning";
					}else{
						$style_bill = "cl-danger";
					}
					if($sbills_print[$table->id]){
						$style_bill = "cl-warning";
					}
					$shtml .= "<button table_id=".$table->id." table_name=".$table->name." bill_id=".$sbill_id." ".$class_disabled." type=\"button\" class=\"btn btn-lg table-pos btn-danger table-board".$style_bill." ".$class_suspend."\">";
						$shtml .= "<ul>";
							$shtml .="<li class='table_date'><span class='fa fa-clock-o'></span> {$time}</li>";
							$shtml .="<li><span class='table_name'><span class='table_icons'><i class='fa fa-cutlery'></i></span>{$table->name}</span></li>";
							$shtml .="<li><span class='amount table_price'>{$amount}</span></li>";
						$shtml .= "</ul>";
					$shtml .= "</button>";
				// }else{
				// 	$shtml .= "<button table_id=".$table->id." table_name=".$table->name." type=\"button\" class=\"btn btn-lg table-pos btn-primary cl-primary".$class_suspend."\">";
				// 		$shtml .= "<ul>";
				// 			$shtml .="<div style='margin-top: -20px;'>&nbsp;</div>";
				// 			$shtml .="<li><span class='table_name'><span class='table_icons_free'><i class='fa fa-cutlery'></i></span></li>";
				// 			$shtml .="<li><span class='table_name'>{$table->name}</span></li>";
				// 			$shtml .="<li>&nbsp;</li>";
							
				// 		$shtml .= "</ul>";
				// 	$shtml .= "</button>";	
				// }
				}else{
					$shtml .= "<button table_id=".$table->id." table_name=".$table->name." type=\"button\" class=\"btn btn-lg table-pos btn-primary cl  ".$class_suspend."\">";
						$shtml .= "<ul>";
							$shtml .="<div style='margin-top: -20px;'>&nbsp;</div>";
							$shtml .="<li><span class='table_name'><span class='table_icons_free'><i class='fa fa-cutlery'></i></span></li>";
							$shtml .="<li><span class='table_name'>{$table->name}</span></li>";
							$shtml .="<li>&nbsp;</li>";
						$shtml .= "</ul>";
					$shtml .= "</button>";	
				}
			}
		}
        $this->cus->send_json(array('tables' => $shtml));
    }
	
	public function delete_suspend_item($id = false)
	{
		$this->cus->checkPermissions("delete_order");
		if(!$id){
			$id = $this->input->get("id");
		}
		$suspend_id = $this->input->get("suspend_id");
		$item = $this->pos_model->getSuspendItemByID($id);
		if ($this->pos_model->deleteSuspendItemById($id, $suspend_id)){	
			echo json_encode(true);
        }
	}
	
	public function load_suspend_items($sid = false)
	{			
		$this->data['suspend_sale'] = NULL;
        if ($sid) {
			if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {
				$inv_items = $this->pos_model->getSuspendedSaleItems($sid);
				krsort($inv_items);
				$c = rand(100000, 9999999);
				foreach ($inv_items as $item) {
					$row = $this->site->getProductByID($item->product_id);
					if (!$row) {
						$row = json_decode('{}');
						$row->tax_method = 0;
						$row->quantity = 0;
					} else {
						$category = $this->site->getCategoryByID($row->category_id);
						$row->category_name = $category->name;
						$row->category_type = $category->type;
						unset($row->cost, $row->details, $row->product_details, $row->barcode_symbology, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
					}
					$pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id);
					if ($pis) {
						foreach ($pis as $pi) {
							$row->quantity += $pi->quantity_balance;
						}
					}
					$row->id = $item->product_id;
					$row->suspend_item_id = $item->id;
					$row->code = $item->product_code;
					$row->name = $item->product_name;
					$row->type = $item->product_type;            
					$row->quantity += $item->quantity;
					$row->discount = $item->discount ? $item->discount : '0';
					$row->price = $this->cus->formatDecimal($item->net_unit_price + $this->cus->formatDecimal($item->item_discount / $item->quantity));
					$row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimal($item->item_discount / $item->quantity) + $this->cus->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
					$row->real_unit_price = $item->real_unit_price;
					$row->base_quantity = $item->quantity;
					$row->base_unit = isset($row->unit) ? $row->unit : $item->product_unit_id;
					$row->base_unit_price = $row->price ? $row->price : $item->unit_price;
					$row->unit = $item->product_unit_id;
					$row->qty = $item->unit_quantity;
					$row->tax_rate = $item->tax_rate_id;
					$row->serial = $item->serial_no;
					$row->option = $item->option_id;
					$row->ordered = $item->ordered;
					$row->ordered_by = $item->ordered_by;
					$order = $this->site->getUser($row->ordered_by);
					$row->order_name = $order->username;					
					$count = $this->pos_model->getSuspendItemBySuspendID($sid);
					$options = $this->pos_model->getProductOptions($row->id, $item->warehouse_id);
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
					$row->comment = isset($item->comment) ? $item->comment : '';					
					$combo_items = false;
					if ($row->type == 'combo') {
						$combo_items = $this->pos_model->getProductComboItems($row->id, $item->warehouse_id);
					}				   
					$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
					$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
					$ri = $this->Settings->item_addition ? $row->id : $c;
					
					$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
							'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
					$c++;
				}
				echo json_encode(array('pr'=>$pr, 'count'=> $count));
			}
		}
	}
	
	public function opened_bills_items($bill_id = 0)
    {
		$html = "";
		$bills  = $this->pos_model->getAllSuspendItemsByID($bill_id);
		if($bills){
			foreach($bills as $i => $bill){				
				$product_name = $bill->product_name;
				$quantity = $bill->quantity;
				$unit_price = $bill->unit_price;
				$product_name = $bill->product_name;
				if($bill->ordered > 0){
					$html .= "<tr>";
						$html .= "<td class='center'>".($i+1)."<input type='hidden' value='".$bill->id."' class='sid' /></td>";
						$html .= "<td>{$product_name}</td>";
						$html .= "<td class='center'>{$quantity}</td>";
						$html .= "<td class='center'>{$unit_price}</td>";
						$html .= "<td class='center'><a href='#' class='cancel_print'><i class='fa fa-print'></i></a></td>";
					$html .= "</td>";
				}
			}
		}
        $data['result'] = $html;		
        echo $this->load->view($this->theme . 'pos/opened_bills_items', $data, TRUE);
    }
		
	public function cancel_print($sid = false)
	{
		if(!$sid){$sid = $this->input->get("sid");}
		if($this->pos_model->updateSuspendItemById($sid, array("ordered"=>0))){
			echo json_encode(true);
		}
	}
	
	public function update_discount()
	{		
		$bill_id = $this->input->get("bill_id");
		$order_discount_id = $this->input->get("order_discount_id");
		if($this->pos_model->updateSuspendById($bill_id, array("order_discount_id"=>$order_discount_id))){
			return true;
		}
	}
	
	public function opened_salemans($suspend_id = false, $per_page = 0)
    {
        $this->load->library('pagination');
        if ($this->input->get('per_page')) {
            $per_page = $this->input->get('per_page');
        }
        $config['base_url'] = site_url('pos/opened_salemans/'.$suspend_id."/");
        $config['total_rows'] = $this->pos_model->salemans_count();
        $config['per_page'] = 6;
        $config['num_links'] = 3;
		$config['uri_segment'] = 4;
        $config['full_tag_open'] = '<ul class="pagination pagination-sm">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';
        $this->pagination->initialize($config);
        $data['r'] = TRUE;
        $salemans = $this->pos_model->fetch_salemans($config['per_page'], $per_page);
		$bills = $this->pos_model->getOpenBillByID($suspend_id);
		
		$salemanbill_details = array();
		$salemanbills = $this->pos_model->getAllSalemanBills();
		foreach($salemanbills as $detail){
			$salemanbill_details[] = $detail->saleman_id;
		}
		
        if (!empty($salemans)) {
            $html = "";
            $html .= '<ul class="ob">';
            foreach ($salemans as $saleman) {
				$name = !empty($saleman->last_name) ? $saleman->last_name.' '.$saleman->first_name : ucfirst($saleman->username);
				if($saleman->id == $bills->saleman_id){
					$html .= '<li style="width:105px; margin:6px 6px 6px 0;"><button class="btn btn-danger"><i class="fa fa-user fa-2x"></i><br/><small>'.$name.'</small></button></li>';
				}else{
					if(in_array($saleman->id, $salemanbill_details)){
						$html .= '<li style="width:105px; margin:6px 6px 6px 0;"><button class="btn btn-warning"><i class="fa fa-user fa-2x"></i><br/><small>'.$name.'</small></button></li>';
					}else{
						$html .= '<li style="width:105px; margin:6px 6px 6px 0;"><button class="sus_saleman btn btn-info"  id='.$saleman->id.' suspend='.$suspend_id.'><i class="fa fa-user fa-2x"></i><br/><small>'.$name.'</small></button></li>';
					}
				}
            }
            $html .= '</ul>';
        } else {
            $html = "<h3>" . lang('no_found') . "</h3><p>&nbsp;</p>";
            $data['r'] = FALSE;
        }

        $data['html'] = $html;
        $data['page'] = $this->pagination->create_links();
        echo $this->load->view($this->theme . 'pos/opened_salemans', $data, TRUE);
    }
	
	public function saleman($suspend_id = false, $saleman_id = false)
	{		
		$saleman = $this->site->getUser($saleman_id);
		$name = !empty($saleman->last_name) ? $saleman->last_name.' '.$saleman->first_name : ucfirst($saleman->username);
		$data = array("saleman" => $name, "saleman_id"=>$saleman->id);
		if($this->pos_model->updateSuspendById($suspend_id,$data)){
			$this->session->set_flashdata('message', lang("saleman_added"));	
			redirect($_SERVER['HTTP_REFERER']);
		}
		$this->session->set_flashdata('error', lang("cannot_add"));	
		redirect($_SERVER['HTTP_REFERER']);
	}
	
	public function screen_display()
	{
		$total_usd = $this->input->get("total_usd");
		$total_khr = $this->input->get("total_khr");
		if($total_usd || $total_khr){
			system("cmd /c cls>COM5");
			system("cmd /c echo USD:{$total_usd}>COM5");
			system("cmd /c echo KHR:{$total_khr}>COM5");
		}
		echo "n/a"; exit;
	}
	
	public function split_suspend($delete_id = NULL, $table_id = NULL, $bill_id = NULL)
    {
		if(!$delete_id){
			$delete_id = $this->input->get("delete_id");
		}
		if(!$bill_id){
			$bill_id = $this->input->get("bill_id");
		}
		if(!$table_id){
			$table_id = $this->input->get("table_id");
		}
		
		$suspend = $this->pos_model->getSuspendByID($delete_id);
		$split_to = $this->pos_model->getTableById($table_id);
		$this->data['suspend']  = $suspend;
		$this->data['bill_id']  = $bill_id;
		$this->data['split_to'] = $split_to;
		$this->data['suspend_items'] = $this->pos_model->getSuspendedSaleItemsByTableID($suspend->table_id);
        $this->load->view($this->theme . 'pos/split_suspend', $this->data);
    }
		
	//============Customer stock================//

	public function customer_stocks()
	{
		$this->cus->checkPermissions("customer_stock");
		$this->pos_model->addCustomerStockExpired();
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('customer_stocks')));
        $meta = array('page_title' => lang('customer_stocks'), 'bc' => $bc);
        $this->core_page('pos/customer_stocks', $meta, $this->data);
	}
	
	public function getCustomerStocks()
    {
        $this->cus->checkPermissions("customer_stock");
        $this->load->library('datatables');
		$detail_link = anchor('pos/view_customer_stock/$1/1', '<i class="fa fa-file-text-o"></i> ' . lang('view_customer_stock'), ' class="cs-view" data-toggle="modal" data-target="#myModal"');
		$edit_link = anchor('pos/edit_customer_stock/$1', '<i class="fa fa-edit"></i> ' . lang('edit_customer_stock'), ' class="cs-edit"');
		$transfer_link = anchor('pos/transfer_customer_stock/$1', '<i class="fa fa-share"></i> ' . lang('transfer_customer_stock'), ' class="cs-transfer" data-toggle="modal" data-target="#myModal"');
		
		$delete_link = "<a href='#' class='po cs-delete' title='<b>" . lang("delete_customer_stock") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('pos/delete_customer_stock/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_customer_stock') . "</a>";
		
		$return_link = "<a href='#' class='po cs-return' title='<b>" . lang("return_customer_stock") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('pos/return_customer_stock/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-reply\"></i> "
        . lang('return_customer_stock') . "</a>";

		$cancel_link = "<a href='#' class='po cs-cancel' title='<b>" . lang("cancel_customer_stock") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('pos/cancel_customer_stock/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('cancel_customer_stock') . "</a>";
		
		$action = '<div class="text-center"><div class="btn-group text-left">'
					. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
					. lang('actions') . ' <span class="caret"></span></button>
						<ul class="dropdown-menu pull-right" role="menu">
							<li>' . $detail_link . '</li>
							<li>' . $return_link . '</li>
							<li>' . $transfer_link . '</li>
							<li>' . $cancel_link . '</li>
							<li>' . $edit_link . '</li>
							<li>' . $delete_link . '</li>
						</ul>
					</div></div>';
		
        $this->datatables
            ->select("
					customer_stocks.id as id, 
					date, 
					reference_no, 
					customer, 
					companies.phone,
					GROUP_CONCAT(cus_products.name) as description,
					customer_stocks.expiry,
					CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by,
					status", false)
            ->from("customer_stocks")
			->join('customer_stock_items','customer_stock_items.customer_stock_id=customer_stocks.id','left')
            ->join('products','products.id=product_id','left')
			->join('users', 'users.id=customer_stocks.created_by', 'left')
			->join('companies', 'companies.id=customer_stocks.customer_id', 'left')
			->group_by("customer_stocks.id")
            ->add_column("Actions", $action, "id");
			
			if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('customer_stocks.created_by', $this->session->userdata('user_id'));
			} elseif ($this->Customer) {
				$this->datatables->where('customer_stocks.customer_id', $this->session->userdata('user_id'));
			}
		
        echo $this->datatables->generate();
    }
	
	public function add_customer_stock()
	{
		$this->cus->checkPermissions("customer_stock");
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            if ($this->Owner || $this->Admin || $GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cs',$biller_id);
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->cus->clear_tags($this->input->post('note'));
			$expiry = isset($_POST['expiry_date']) ? $this->cus->fsd($_POST['expiry_date']) : NULL;
			$customer_id = $this->input->post("customer");
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = ($customer_details->name?$customer_details->name:$customer_details->company);
			
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : NULL;
				$item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$serial = $_POST['serial'][$r];
				$unit = $this->site->getProductUnit($product_id,$item_unit);
				$product_details = $this->site->getProductByID($product_id);
				
				$products[] = array(
					'product_id' => $product_id,
					'quantity' => $item_quantity,
					'option_id' => $variant,
					'product_unit_id' => $item_unit,
					'warehouse_id' => $warehouse_id,
					'product_unit_code' => $unit->code,
					'unit_quantity' => $quantity,
					'serial_no' => $serial,
					);
				
				if($serial != ''){
					$serial_detail = $this->products_model->getProductSerial($serial,$product_details->id,$warehouse_id);
					if($serial_detail){
						$product_details->cost = $serial_detail->cost;
					}
					if($item_quantity > 0){
						$reactive = 0;
						if($serial_detail){
							if($serial_detail->inactive==0){
								$this->session->set_flashdata('error', lang("serial_is_existed").' ('.$serial.') ');
								redirect($_SERVER["HTTP_REFERER"]);
							}else {
								$reactive = 1;
							}
						}else{
							$product_serials[] = array(
									'product_id' 	=> $product_details->id,
									'cost' 			=> $product_details->cost,
									'price' 		=> $product_details->price,
									'warehouse_id' 	=> $warehouse_id,
									'date' 			=> $date,
									'serial' 		=> $serial,
								);
						}
					}
				}
				
				$stockmoves[] = array(
						'transaction' => 'CustomerStock',
                        'product_id' => $product_id,
						'product_code' => $product_details->code,
						'product_type' => $product_details->type,
                        'option_id' => $variant,
                        'quantity' => $item_quantity,
                        'unit_quantity' => $unit->unit_qty,
						'unit_code' => $unit->code,
						'unit_id' => $item_unit,
                        'warehouse_id' => $warehouse_id,
                        'date' => $date,
						'real_unit_cost' => $product_details->cost,
						'serial_no' => $serial,
						'reference_no' => $reference_no,
						'user_id' => $this->session->userdata('user_id')
                    );	
					
				if($this->Settings->accounting == 1){
					
					$productAcc = $this->site->getProductAccByProductId($product_details->id);
					$billerAcc  = $this->site->getAccountSettingByBiller($biller_id);
					
					$accTrans[] = array(
						'transaction' => 'CustomerStock',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->stock_acc,
						'amount' => ($product_details->cost * $item_quantity),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					
					$accTrans[] = array(
						'transaction' => 'CustomerStock',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $billerAcc->customer_stock_acc,
						'amount' => ($product_details->cost * $item_quantity) * (-1),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
				}
			}
			
			$data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
				'biller_id' => $biller_id,
				'expiry' => $expiry,
				'customer_id' => $customer_details->id,
				'customer' => $customer,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                );
        }
        if ($this->form_validation->run() == true && $this->pos_model->addCustomerStock($data, $products, $stockmoves, $accTrans, $product_serials)) {
			$this->session->set_userdata('remove_csls', 1);
            $this->session->set_flashdata('message', lang("customer_stock_added")." - ".$data['reference_no']);
            redirect('pos/customer_stocks');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('add_customer_stock')));
			$meta = array('page_title' => lang('add_customer_stock'), 'bc' => $bc);
			$this->core_page('pos/add_customer_stock', $meta, $this->data);
        }
	}
	
	public function edit_customer_stock($id = NULL)
	{
		$this->cus->checkPermissions("customer_stock");
		$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
		$customer_stock = $this->pos_model->getCustomerStockByID($id);
		
        if ($this->form_validation->run() == true) {
		
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            if ($this->Owner || $this->Admin || $GP['sales-date']) {
                $date = $this->cus->fld($this->input->post('date'),1);
            } else {
                $date = $customer_stock->date;
            }
			
			$reference_no = $this->input->post('reference_no');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->cus->clear_tags($this->input->post('note'));
			$expiry = isset($_POST['expiry_date']) ? $this->cus->fsd($_POST['expiry_date']) : NULL;
			$customer_id = $this->input->post("customer");
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = ($customer_details->name?$customer_details->name:$customer_details->company);
			
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : NULL;
				$item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$serial = $_POST['serial'][$r];
				$unit = $this->site->getProductUnit($product_id,$item_unit);
				$product_details = $this->site->getProductByID($product_id);
				
				$products[] = array(
					'product_id' => $product_id,
					'quantity' => $item_quantity,
					'option_id' => $variant,
					'product_unit_id' => $item_unit,
					'warehouse_id' => $warehouse_id,
					'product_unit_code' => $unit->code,
					'unit_quantity' => $quantity,
					'serial_no' => $serial,
					);
				
				if($serial!=''){
					$serial_detail = $this->products_model->getProductSerial($serial,$product_details->id,$warehouse_id,$id);
					if($serial_detail){
						$product_details->cost = $serial_detail->cost;
					}
					if($item_quantity > 0){	
						$reactive = 0;
						if($serial_detail){
							if($serial_detail->inactive==0){
								if($this->products_model->getAdjustmentItemSerial($product_details->id,$id,$serial)){
									$reactive = 1;
								}else{
									$this->session->set_flashdata('error', lang("serial_is_existed").' ('.$serial.') ');
									redirect($_SERVER["HTTP_REFERER"]);
								}
							}else {
								$reactive = 1;
							}
						}else{
							$product_serials[] = array(
										'product_id' 	=> $product_details->id,
										'cost' 			=> $product_details->cost,
										'price' 		=> $product_details->price,
										'warehouse_id' 	=> $warehouse_id,
										'date' 			=> $date,
										'serial' 		=> $serial,
									);
						}
					}
				}
				
				$stockmoves[] = array(
						'transaction' => 'CustomerStock',
                        'product_id' => $product_id,
						'product_code' => $product_details->code,
						'product_type' => $product_details->type,
                        'option_id' => $variant,
                        'quantity' => $item_quantity,
                        'unit_quantity' => $unit->unit_qty,
						'unit_code' => $unit->code,
						'unit_id' => $item_unit,
                        'warehouse_id' => $warehouse_id,
                        'date' => $date,
						'real_unit_cost' => $product_details->cost,
						'serial_no' => $serial,
						'reference_no' => $reference_no,
						'user_id' => $this->session->userdata('user_id')
                    );		
					
				if($this->Settings->accounting == 1){
					$productAcc = $this->site->getProductAccByProductId($product_details->id);
					$billerAcc  = $this->site->getAccountSettingByBiller($biller_id);
					$accTrans[] = array(
						'transaction' => 'CustomerStock',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->stock_acc,
						'amount' => ($product_details->cost * $item_quantity),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					$accTrans[] = array(
						'transaction' => 'CustomerStock',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $billerAcc->customer_stock_acc,
						'amount' => ($product_details->cost * $item_quantity) * (-1),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
				}
			}
			
			$data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
				'biller_id' => $biller_id,
				'expiry' => $expiry,
				'customer_id' => $customer_details->id,
				'customer' => $customer,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                );
        }
        if ($this->form_validation->run() == true && $this->pos_model->updateCustomerStock($id, $data, $products, $stockmoves, $accTrans, $product_serials)) {
			$this->session->set_userdata('remove_csls', 1);
            $this->session->set_flashdata('message', lang("customer_stock_updated")." - ".$data['reference_no']);
            redirect('pos/customer_stocks');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$inv_items = $this->pos_model->getCustomerStockItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $product = $this->site->getProductByID($item->product_id);
                $row = json_decode('{}');
                $row->id = $item->product_id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->qty = $item->unit_quantity;
				$options = $this->site->getProductOptions($product->id);
                $row->option = $item->option_id ? $item->option_id : 0;
                $row->serial = $item->serial_no ? $item->serial_no : '';
                $ri = $this->Settings->item_addition ? $product->id : $c;
				$item->quantity = abs($item->quantity);
				$row->base_quantity = $item->quantity;
				$row->base_unit_cost = $product->cost;
                $row->base_unit = $product->unit ? $product->unit : $item->product_unit_id;
				$row->unit = $item->product_unit_id;
				$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options, 'units'=> $units);
                $c++;
            }
			$this->data['id'] = $id;
            $this->data['customer_stock'] = $customer_stock;
            $this->data['customer_stock_items'] = json_encode($pr);
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('edit_customer_stock')));
			$meta = array('page_title' => lang('edit_customer_stock'), 'bc' => $bc);
			$this->core_page('pos/edit_customer_stock', $meta, $this->data);
        }
	}
	
	public function delete_customer_stock($id)
    {
		$this->cus->checkPermissions("customer_stock");
        if ($this->pos_model->deleteCustomerStock($id)) {
            echo lang("customer_stock_deleted"); exit;
        }
		$this->session->set_flashdata('message', lang("customer_stock_deleted"));
        redirect('pos/customer_stocks');
    }
	
	public function view_customer_stock($id, $modal)
	{
		$this->cus->checkPermissions("customer_stock");
		if(!$id){
			$id = $this->input->get("id");
		}
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('POS',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('POS',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
		$inv = $this->pos_model->getCustomerStockByID($id);
		$this->data['inv'] = $inv;
		$this->data['modal'] = $modal;
		$this->data['pos'] = $this->pos_model->getSetting();
		$this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
		$this->data['printer'] = $this->pos_model->getPrinterByID($this->pos_settings->printer);
		$this->data['rows'] = $this->pos_model->getCustomerStockItems($id);
		$this->data['created_by'] = $this->site->getUser($inv->created_by);
		$this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
		$this->data['page_title'] = $this->lang->line("view_customer_stock");
		$this->load->view($this->theme . 'pos/view_customer_stock', $this->data);
	}
	
	public function add_customer()
	{
		$this->cus->checkPermissions("customer_stock");
		$this->load->helper('security');		
        $this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('phone', lang("phone"), 'required');
        if ($this->form_validation->run() == true) {
			$cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
			$data = array(
						'code'		=> time(),
						'name' 		=> $this->input->post('name'),
						'company' 	=> $this->input->post('name'),
						'phone' 	=> $this->input->post('phone'),
						'group_id' 	=> '3',
						'group_name' => 'customer',
						'customer_group_id' => $this->input->post('customer_group'),
						'customer_group_name' => $cg->name,
					);
        }
        if ($this->form_validation->run() == true && $this->pos_model->addCustomer($data)) {
            $this->session->set_flashdata('message', lang("customer_added"));
            redirect($_SERVER['HTTP_REFERER']); 
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['customer_groups'] = $this->pos_model->getAllCustomerGroups();
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/add_customer', $this->data);
        }
	}
	
	public function customer_stock_actions()
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
						$customer_stock = $this->pos_model->getCustomerStockByID($id);
						if($customer_stock->status=='pending'){
							$this->pos_model->deleteCustomerStock($id);
						}else{
							$this->session->set_flashdata('error', $this->lang->line("customer_stocks_cannot_delete"));
							redirect($_SERVER["HTTP_REFERER"]);
						}
                    }
                    $this->session->set_flashdata('message', $this->lang->line("customer_stocks_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
				
				if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer_stocks'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('description'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('expiry'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('created_by'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->pos_model->getCustomerStockByID($id);
						$cs = $this->site->getCompanyByID($sc->customer_id);
                        $user = $this->site->getUser($sc->created_by);
						
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($sc->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->customer);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $cs->phone);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->description);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->hrsd($sc->expiry));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $user->last_name .' '.$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($sc->status));
						
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    
					$filename = 'customer_stocks_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
			}else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
		}else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function transfer_customer_stock($id)
    {
		$this->cus->checkPermissions("customer_stock");
		if(!$id){
			$id = $this->input->get("id");
		}
		$customer_stock = $this->pos_model->getCustomerStockByID($id);
		$this->form_validation->set_rules('table', lang("table"), 'required');
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$data = array(
						"transfered_by" => $this->session->userdata("user_id"),
						"transfered_at" => $date,
						"table_id" 		=> $this->input->post("table"),
						"status" 		=> "completed",
					);
			
			$items = $this->site->getAllStockmoves("CustomerStock", $id);
			foreach($items as $item){
				$prod = $this->site->getProductByID($item->product_id);
				$unit = $this->site->getProductUnit($item->product_id,$item->unit_id);
				$products[] = array(
						'product_id'        => $item->product_id,
						'product_code'      => $item->product_code,
						'product_name'      => $prod->name,
						'product_type'      => $item->product_type,
						'option_id'         => $item->option_id,
						'quantity'          => $item->quantity,
						'product_unit_id'   => $item->unit_id,
						'product_unit_code' => $unit ? $unit->code : NULL,
						'unit_quantity'     => $item->quantity,
						'warehouse_id'      => $item->warehouse_id,
						'unit_price'        => 0,
						'net_unit_price'    => 0,
						'real_unit_price'   => 0,
						'subtotal'          => 0,
						'ordered'			=> 1,
						'serial_no'         => $item->serial_no,
						'cost' 			    => $item->real_unit_cost,
					);
			}

		}
		if ($this->form_validation->run() == true && $this->pos_model->transferCustomerStock($id, $data, $products)) {
			$this->session->set_flashdata('message', lang("customer_stock_transfered").' '.$customer_stock->reference_no);
            redirect($_SERVER['HTTP_REFERER']); 
		}else{
			$row = $this->pos_model->getCustomerStockByID($id);
			$this->data['id'] = $id;
			$this->data['customer_stock'] = $row;
			$this->data['suspend_bills'] = $this->pos_model->getAllSuspendBills();
			$this->data['biller'] = $this->site->getCompanyByID($row->biller_id);
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['page_title'] = $this->lang->line("transfer_customer_stock");
			$this->load->view($this->theme . 'pos/transfer_customer_stock', $this->data);
		}
    }
	
	public function return_customer_stock($id)
    {
		$this->cus->checkPermissions("customer_stock");
		$customer_stock = $this->pos_model->getCustomerStockByID($id);
		$items = $this->site->getAllStockmoves("CustomerStock", $id);
		
		foreach($items as $item){
			
			if($this->Settings->accounting_method == '0'){
				$costs = $this->site->getFifoCost($item->product_id,$item->quantity,$stockmoves);
			}else if($this->Settings->accounting_method == '1'){
				$costs = $this->site->getLifoCost($item->product_id,$item->quantity,$stockmoves);
			}else if($this->Settings->accounting_method == '3'){
				$costs = $this->site->getProductMethod($item->product_id,$item->quantity,$stockmoves);
			}else{
				$costs = false;
			}
			
			if($costs && $item->serial_no==''){
				
				foreach($costs as $cost_item){
					
					$stockmoves[] = array(
								'transaction' 	 => 'CustomerStockReturn',
								'product_id' 	 => $item->product_id,
								'product_code' 	 => $item->product_code,
								'product_type' 	 => $item->product_type,
								'option_id' 	 => $item->option_id,
								'quantity' 		 => $cost_item['quantity'] * (-1),
								'unit_quantity'  => $item->unit_quantity,
								'unit_code' 	 => $item->unit_code,
								'unit_id' 		 => $item->unit_id,
								'warehouse_id' 	 => $item->warehouse_id,
								'date' 			 => $item->date,
								'real_unit_cost' => $cost_item['cost'],
								'serial_no' 	 => $item->serial_no,
								'reference_no' 	 => $item->reference_no,
								'user_id' 		 => $item->user_id
							);
							
					if($this->Settings->accounting == 1){
						$productAcc = $this->site->getProductAccByProductId($item->product_id);
						$billerAcc  = $this->site->getAccountSettingByBiller($customer_stock->biller_id);
						$accTrans[] = array(
							'transaction' => 'CustomerStockReturn',
							'transaction_date' => date("Y-m-d H:i"),
							'reference' => $customer_stock->reference_no,
							'account' => $productAcc->stock_acc,
							'amount' => ($cost_item['cost'] * $item->quantity) * (-1),
							'narrative' => 'Product Code: '.$item->product_code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$cost_item['cost'],
							'description' => $note,
							'biller_id' => $biller_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'transaction' => 'CustomerStockReturn',
							'transaction_date' => date("Y-m-d H:i"),
							'reference' => $customer_stock->reference_no,
							'account' => $billerAcc->customer_stock_acc,
							'amount' => ($cost_item['cost'] * $item->quantity),
							'narrative' => 'Product Code: '.$item->product_code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$cost_item['cost'],
							'description' => $note,
							'biller_id' => $biller_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
				}
				
			}else{
				
				$stockmoves[] = array(
							'transaction' 	 => 'CustomerStockReturn',
							'product_id' 	 => $item->product_id,
							'product_code' 	 => $item->product_code,
							'product_type' 	 => $item->product_type,
							'option_id' 	 => $item->option_id,
							'quantity' 		 => $item->quantity * (-1),
							'unit_quantity'  => $item->unit_quantity,
							'unit_code' 	 => $item->unit_code,
							'unit_id' 		 => $item->unit_id,
							'warehouse_id' 	 => $item->warehouse_id,
							'date' 			 => $item->date,
							'real_unit_cost' => $item->real_unit_cost,
							'serial_no' 	 => $item->serial_no,
							'reference_no' 	 => $item->reference_no,
							'user_id' 		 => $item->user_id
						);
						
				if($this->Settings->accounting == 1){
					$productAcc = $this->site->getProductAccByProductId($item->product_id);
					$billerAcc  = $this->site->getAccountSettingByBiller($customer_stock->biller_id);
					$accTrans[] = array(
						'transaction' => 'CustomerStockReturn',
						'transaction_date' => date("Y-m-d H:i"),
						'reference' => $customer_stock->reference_no,
						'account' => $productAcc->stock_acc,
						'amount' => ($item->real_unit_cost * $item->quantity) * (-1),
						'narrative' => 'Product Code: '.$item->product_code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$item->real_unit_cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					$accTrans[] = array(
						'transaction' => 'CustomerStockReturn',
						'transaction_date' => date("Y-m-d H:i"),
						'reference' => $customer_stock->reference_no,
						'account' => $billerAcc->customer_stock_acc,
						'amount' => ($item->real_unit_cost * $item->quantity),
						'narrative' => 'Product Code: '.$item->product_code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$item->real_unit_cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
				}
			}
		}
		
        if ($this->pos_model->returnCustomerStock($id, $stockmoves, $accTrans)) {
            echo lang("customer_stock_returned")." ". $item->reference_no;
        }
    }
	
	public function cancel_customer_stock($id)
	{
		$this->cus->checkPermissions("customer_stock");
		if ($this->pos_model->cancelCustomerStock($id)) {
            echo lang("customer_stock_canceled");
        }
	}
	
	public function ajax_categories($per_page = 0, $active = 0)
    {
        $this->load->library('pagination');
        if ($this->input->get('per_page')) {
            $per_page = $this->input->get('per_page');
        }
		if ($this->input->get('active')) {
            $active = $this->input->get('active');
        }
		$total_rows 			 = $this->pos_model->categories_count();
        $config['base_url'] 	 = site_url('pos/ajax_categories');
        $config['total_rows'] 	 = $total_rows;
        $config['per_page'] 	 = $this->config->item("category_rows");
		$config['prev_link'] 	 = FALSE;
        $config['next_link'] 	 = FALSE;
        $config['display_pages'] = FALSE;
        $config['first_link'] 	 = FALSE;
        $config['last_link'] 	 = FALSE;
        $this->pagination->initialize($config);
        $categories = $this->pos_model->fetch_categories($config['per_page'], $per_page);
		if (!empty($categories)) {
            $html = ""; $subhtml = "";
            foreach ($categories as $category) {
				if($category->id == $active){
					$html .= "<button type='button' disabled-open-category='true' value='{$category->id}' class='animated ccategory btn cl-danger cl-primary category'>{$category->name}</button>";
				}else{
					$html .= "<button type='button' disabled-open-category='true' value='{$category->id}' class='animated ccategory btn cl-primary category'>{$category->name}</button>";
				}
			}
        }  else {
            $html = "<button type='button' disabled-open-category='true' id='previous_c' class='btn cl-primary' style='width:16.20%; height:55px; margin:1.7px;'>".lang("N/A")."</button>";
        } 
		echo $this->cus->send_json(array('html' => $html, 'total_rows' => $total_rows));
    }
	
	public function add_bill($bill_id = null)
	{
		$this->cus->checkPermissions('pos-print_bill');
		if(!$bill_id){
			$bill_id = $this->input->get("bill_id");
		}
		$bill = $this->pos_model->getSuspendByID($bill_id);
		$reference_no = $this->site->getReference('bl',$bill->biller_id);
		$print = ($bill->print > 0 ? ($bill->print + 1) : 1);
		$data = array(
						"print" 		=> $print, 
						"print_by" 		=> $this->session->userdata("user_id"),
						"reference_no"	=> $reference_no,
					);
		if($this->pos_model->updatePrintBill($bill_id, $data)){
			$this->pos_model->addBill($bill_id);
			echo $print;
		}
	}
	
	public function packaging($id = false)
    {
		$this->cus->checkPermissions("sales-edit");
        if ($this->pos_model->updatePackaging($id, 'packaging')) {
            echo lang("package_sale_updated"); exit;
        }
		$this->session->set_flashdata('message', lang("package_sale_updated"));
        redirect('pos/customer_stocks');
    }
	
	public function undo_packaging($id = false)
    {
		$this->cus->checkPermissions("sales-edit");
        if ($this->pos_model->updatePackaging($id, 'pending')) {
            echo lang("undo_package_sale_updated"); exit;
        }
		$this->session->set_flashdata('message', lang("undo_package_sale_updated"));
        redirect('pos/customer_stocks');
    }
	
	public function p_sticker()
	{
		$this->load->library('escpos');
		$data = json_decode($this->input->get('data'));
		$rows = $this->pos_model->getAllInvoiceItemsGroupStickers($data->sale_id);
		$html = '';
		$printer_id = $this->pos_settings->printer;
		$printer = $this->pos_model->getPrinterByID($printer_id);
		if($printer){
			$i = 1;
			$n = count($rows);
			foreach($rows as $row){
				$html_parent = '';
				if($row->pro_additionals){
					$pro_additionals = json_decode($row->extract_product);
					if($pro_additionals){
						$html_parent .= '<table style="width:100%;">';
						foreach($pro_additionals as $pro_pro_additional){
							$product = $this->site->getProductByID($pro_pro_additional->for_product_id);
							$pro_quantity = $pro_pro_additional->for_quantity;
							$html_parent .= '<tr>
													 <td>+ '.$product->name.'</td>
													 <td style="text-align:right">'.$this->cus->formatQuantity($pro_quantity).'</td>
												  </tr>';
						}
						$html_parent .= '</table>';
					}
				}
				if($row->raw_materials){
					$materials = json_decode($row->raw_materials);
					if($materials){
						$html_parent .= '<table width="100%"">';
						foreach($materials as $material){
							$product = $this->site->getProductByID($material->product_id);
							$quantity = $material->quantity;
							$html_parent .= '<tr>
												<td>+ '.$product->name.' '.$row->bom_type.'</td>
												<td style="text-align:right">'.$this->cus->formatQuantity($quantity).'</td>
											 </tr>';
						}
						$html_parent .= '</table>';
					}
				}
				$html .='<div class="pos-sticker-item" style="margin:10px 0px; page-break-after: always;">
							<table width="100%">
								<tr>
									<td style="width:33.33%; text-align:center;"></td>
									<td style="width:33.33%; text-align:center;">'.$i.'/'.$n.'</td>
									<td style="width:33.33%; text-align:right;">#'.$data->number.'</td>
								</tr>
							</table>
							<table width="100%">
								<tr>
									<td><b>'.$row->product_name.'</b></td>
								</tr>
							</table>
							'.$html_parent.'
						 </div>
						<style type="text/css">
							html, table td, label, button, span, a { 
								font-family:"Khmer", sans-serif; 
								font-size:10px !important;
							}
							.pos-sticker-item{
								width:100%;
								min-height:80px;
							}
						</style>';
				$i++;
			}
			echo $html;
		}
	}
	
	public function get_membership_code($id = false)
	{
		$membership_code = $this->input->get("membership_code",true);
		$member_card = $this->pos_model->getMemberCardCode($membership_code);
		if($member_card && (!$member_card->expiry || $member_card->expiry > date('Y-m-d'))){
			$customer = $this->site->getCompanyByID($member_card->customer_id);
			$data = array(
							"customer_id" => $customer->id,
							"phone" => $customer->phone,
							"status" => "success",
							"message" => lang("the_membership_code_you_enter_is_success"),
						);
			
		}else if($member_card && ($member_card->expiry < date('Y-m-d'))){
			$data = array(
							"customer_id" => null,
							"phone" => null,
							"status" => "expired",
							"message" => lang("the_membership_code_you_enter_is_expired"),
						);
		}else{
			$data = array(
							"customer_id" => null,
							"phone" => null,
							"status" => "error",
							"message" => lang("the_membership_code_you_enter_is_not_valid"),
						);
		}
		echo json_encode($data);
	}
	
	public function get_project()
	{
		$id = $this->input->get("biller");
		$project_id = $this->input->get("project");
		$rows = $this->site->getAllProjectByBillerID($id);
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$project = json_decode($user->project_ids);
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
	
}
