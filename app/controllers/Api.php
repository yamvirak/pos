<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
		$this->load->library('form_validation');
        $this->load->model('api_model');
        $this->load->library('ion_auth');
    }
	
	function check_auth_client($method_type = false){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != $method_type){
			echo json_encode(array('status' => 400,'message' => 'Bad request.'));
			return false;
		} else {
			$check_auth_client = $this->api_model->check_auth_client();
			if($check_auth_client == true){
		        return true;
			}else{
				echo json_encode(array('status' => 401,'message' => 'Unauthorized.'));
				return false;
			}
		}
	}
	
	function check_license(){
		$check_auth_client = $this->check_auth_client("POST");
		if($check_auth_client == true){
			$license = $this->api_model->getLicense($this->input->post("code"));
			if($license){
				$license->message = 'Setup successfully';
				$license->success = true;
				echo json_encode($license);
			}else{
				echo json_encode(array('status' => 200,'message' => 'Incorrect code.','success' => false));
			}
		}
	}
	
	function login(){
		$check_auth_client = $this->check_auth_client("POST");
		if($check_auth_client == true){
			$user = $this->api_model->login($this->input->post("username"),$this->input->post("password"));
			if($user){
				$user->success = true;
				$user->message = 'Login successfully';
				echo json_encode($user);
			}else{
				echo json_encode(array('status' => 200,'message' => 'Incorrect username or password.','success' => false));
			}
		}
	}
	function get_products(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$products = $this->api_model->getProducts();
			echo json_encode($products);
		}
	}

	function get_product_balance(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$products = $this->api_model->getProductBalance();
			echo json_encode($products);
		}
	}

	function get_customer_detail($id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('companies_model');
			$customer = $this->companies_model->getCompanyByID($id);
			echo json_encode($customer);
		}
	}

	function get_supplier_detail($id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('companies_model');
			$customer = $this->companies_model->getCompanyByID($id);
			echo json_encode($customer);
		}
	}

	function get_product_detail($id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('site');
			$this->load->model('products_model');
			$this->load->model('api_model');
			$data = array();
			$pr_details = $this->site->getProductByID($id);
			$pr_details->product_details = str_replace("\r", '', strip_tags($pr_details->product_details));
			$data['product'] = $pr_details;
			$data['price_groups'] = $this->products_model->getProductGroupPrices($id);
	        $data['unit'] = $this->site->getUnitByID($pr_details->unit);
	        $data['brand'] = $this->site->getBrandByID($pr_details->brand);
	        $data['images'] = $this->products_model->getProductPhotos($id);
	        $data['category'] = $this->site->getCategoryByID($pr_details->category_id);
	        $data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : NULL;
	        $myWarehouses = $this->api_model->getAllWarehousesWithPQ($id);
	        $data['warehouses'] = $myWarehouses;
	        $data['options'] = $this->api_model->getProductOptionsWithWH($id);
	        if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 202,'message' => 'No product is found'));
			}
		}
		
	}
	function get_categories(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$categories = $this->api_model->getCategories();
			echo json_encode($categories);
			
		}
	}

	function get_customers(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$customers = $this->api_model->getCustomers();
			echo json_encode($customers);
		}
	}

	function get_suppliers(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$suppliers = $this->api_model->getSuppliers();
			echo json_encode($suppliers);
		}
	}

	function get_expense_categories(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$categories = $this->api_model->getExpenseCategories();
			echo json_encode($categories);
		}
	}

	function get_sales($customer_id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client == true){
			$sales = $this->api_model->getSales();
			echo json_encode($sales);
		}
	}

	function get_sale_by_customer($customer_id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client == true){
			$sales = $this->api_model->getSaleByCustomer($customer_id);
			echo json_encode($sales);
		}
	}

	function search_sales(){
		$check_auth_client = $this->check_auth_client("POST");
		if($check_auth_client==true){
			$start_date = $this->input->post("start_date");
			$end_date = $this->input->post("end_date");
			$customer_id = $this->input->post("customer_id");
			$sales = $this->api_model->getSales($start_date, $end_date, $customer_id);
			echo json_encode($sales);
		}
	}

	function get_open_invoices(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$sales = $this->api_model->getOpenInvoice();
			echo json_encode($sales);
		}
	}

	function search_open_invoices(){
		$check_auth_client = $this->check_auth_client("POST");
		if($check_auth_client==true){
			$start_date = $this->input->post("start_date");
			$end_date = $this->input->post("end_date");
			$customer_id = $this->input->post("customer_id");
			$sales = $this->api_model->getOpenInvoice($start_date, $end_date, $customer_id);
			echo json_encode($sales);
		}
	}

	function get_purchases(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$purchases = $this->api_model->getPurchases();
			echo json_encode($purchases);
		}
	}

	function get_purchase_by_supplier($supplier_id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$purchases = $this->api_model->getPurchaseBySupplier($supplier_id);
			echo json_encode($purchases);
		}
	}

	function get_supplier_purchase_balance_detail($supplier_id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$purchases = $this->api_model->getPurchaseBySupplierBalance($supplier_id);
			echo json_encode($purchases);
		}
	}

	function get_supplier_expense_balance_detail($supplier_id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$expenses = $this->api_model->getExpenseBySupplierBalance($supplier_id);
			echo json_encode($expenses);
		}
	}

	function get_supplier_freight_balance_detail($supplier_id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$freights = $this->api_model->getFreightBySupplierBalance($supplier_id);
			echo json_encode($freights);
		}
	}

	

	function search_purchases(){
		$check_auth_client = $this->check_auth_client("POST");
		if($check_auth_client==true){
			$start_date = $this->input->post("start_date");
			$end_date = $this->input->post("end_date");
			$supplier_id = $this->input->post("supplier_id");
			$purchases = $this->api_model->getPurchases($start_date, $end_date, $supplier_id);
			echo json_encode($purchases);
		}
	}

	function get_open_purchases(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$purchases = $this->api_model->getOpenPurchases();
			echo json_encode($purchases);
		}
	}

	function search_open_purchases(){
		$check_auth_client = $this->check_auth_client("POST");
		if($check_auth_client==true){
			$start_date = $this->input->post("start_date");
			$end_date = $this->input->post("end_date");
			$supplier_id = $this->input->post("supplier_id");
			$purchases = $this->api_model->getOpenPurchases($start_date, $end_date, $supplier_id);
			echo json_encode($purchases);
		}
	}

	function get_expenses(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$expenses = $this->api_model->getExpenses();
			echo json_encode($expenses);
		}
	}

	function search_expenses(){
		$check_auth_client = $this->check_auth_client("POST");
		if($check_auth_client==true){
			$start_date = $this->input->post("start_date");
			$end_date = $this->input->post("end_date");
			$supplier_id = $this->input->post("supplier_id");
			$expenses = $this->api_model->getExpenses($start_date, $end_date,$supplier_id);
			echo json_encode($expenses);
		}
	}

	function get_payments(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$expenses = $this->api_model->getPayments();
			echo json_encode($expenses);
		}
	}

	function search_payments(){
		$check_auth_client = $this->check_auth_client("POST");
		if($check_auth_client==true){
			$start_date = $this->input->post("start_date");
			$end_date = $this->input->post("end_date");
			$supplier_id = $this->input->post("supplier_id");
			$customer_id = $this->input->post("customer_id");
			$expenses = $this->api_model->getPayments($start_date, $end_date,$customer_id,$supplier_id);
			echo json_encode($expenses);
		}
	}


	function get_sale_payment_detail($payment_id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('sales_model');
			$this->load->model('site');
			$payment = $this->sales_model->getPaymentByID($payment_id);
			if($payment->transaction=='Saleman Commission'){
				$data['saleman'] = $this->site->getUser($inv->saleman_id);
				$inv = $this->sales_model->getInvoiceByID($payment->transaction_id);
				$inv_payments = $this->sales_model->getCommissionPaymentsByRef($payment->reference_no,$payment->date);
			}else{
				$inv = $this->sales_model->getInvoiceByID($payment->sale_id);
				$inv_payments = $this->sales_model->getPaymentsByRef($payment->reference_no,$payment->date);
			}
	        $data['biller'] = $this->site->getCompanyByID($inv->biller_id);
	        $data['customer'] = $this->site->getCompanyByID($inv->customer_id);
	        $data['inv'] = $inv;
			$data['inv_payments'] = $inv_payments;
	        $data['payment'] = $payment;
			if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 404,'message' => 'No sale payment is found'));
			}
		}
	}

	public function get_sale_deposit_payment_detail($id = null){    
        $check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('sale_order_model');
			$this->load->model('site');
			$deposit = $this->sale_order_model->getDepositByID($id);
			$sale_order = $this->sale_order_model->getSaleOrderByID($deposit->sale_order_id);
	        $data['biller'] = $this->site->getCompanyByID($sale_order->biller_id);
	        $data['customer'] = $this->site->getCompanyByID($sale_order->customer_id);
	        $data['sale_order'] = $sale_order;
			$data['deposit'] = $deposit;
			if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 404,'message' => 'No deposit payment is found'));
			}
		}
        
    }

    public function get_rental_deposit_payment_detail($id = null){
        $check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('rentals_model');
			$this->load->model('site');
			$deposit = $this->rentals_model->getPaymentByID($id);
	        $rental = $this->rentals_model->getRentalByID($deposit->transaction_id);
	        $data['room'] = $this->rentals_model->getRoomByID($rental->room_id);
	        $data['biller'] = $this->site->getCompanyByID($rental->biller_id);
	        $data['customer'] = $this->site->getCompanyByID($rental->customer_id);
	        $data['rental'] = $rental;
			$data['deposit'] = $deposit;
			
			if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 404,'message' => 'No deposit payment is found'));
			}
		}
        
    }

    public function get_purchase_deposit_payment_detail($id = null){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('purchase_order_model');
			$this->load->model('site');

			$payment = $this->purchase_order_model->getDepositByID($id);
	        $inv = $this->purchase_order_model->getPurchaseOrderByID($payment->purchase_order_id);
	        $data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
	        $data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
	        $data['inv'] = $inv;
			$data['biller'] = $this->site->getCompanyByID($inv->biller_id);
	        $data['payment'] = $payment;
	      	
	      	if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 404,'message' => 'No deposit payment is found'));
			}
		}	
        
    }

	function get_purchase_payment_detail($payment_id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('site');
			$this->load->model('purchases_model');
			$payment = $this->purchases_model->getPaymentByID($payment_id);
			$inv_payments = $this->purchases_model->getPaymentsByRef($payment->reference_no,$payment->date);
	        if($payment->purchase_id){
				$inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
			}else{
				$inv = $this->purchases_model->getExpenseByID($payment->expense_id);
				$inv->grand_total = $inv->amount;
			}
	        $data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
	        $data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
	        $data['inv'] = $inv;
			$data['inv_payments'] = $inv_payments;
			$data['biller'] = $this->site->getCompanyByID($inv->biller_id);
	        $data['payment'] = $payment;
			if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 404,'message' => 'No purchase payment is found'));
			}
		}
	}

	function get_pawn_payment_detail($id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('site');
			$this->load->model('pawns_model');
			$items = "";
			$payment = $this->pawns_model->getPaymentByID($id);
			$inv = $this->pawns_model->getPawnByID($payment->pawn_id);
	        $data['biller'] = $this->site->getCompanyByID($inv->biller_id);
	        $data['customer'] = $this->site->getCompanyByID($inv->customer_id);
			$pawn_rate_id = $payment->pawn_rate_id;
			if($payment->pawn_rate_id > 0){
				$payment = $this->pawns_model->getPawnPaymentByID($payment->pawn_rate_id);
				$data['pawn_items'] = $this->pawns_model->getPawnRateItems($payment->id);
			}else if($payment->pawn_return_id > 0){
				$items = $this->pawns_model->getPawnReturnByID($payment->pawn_return_id);
				$data['pawn_items'] = $this->pawns_model->getAllReturnPawnItems($payment->pawn_return_id);
			}else if($payment->pawn_purchase_id > 0){
				$items = $this->pawns_model->getPawnPurchaseByID($payment->pawn_purchase_id);
				$data['pawn_items'] = $this->pawns_model->getAllPurhcasePawnItems($payment->pawn_purchase_id);
			}else{
				
				$items = $this->pawns_model->getPawnByID($payment->pawn_id);
				$data['pawn_items'] = $this->pawns_model->getAllPawnItems($payment->pawn_id);
			}
	        $data['inv'] = $inv;
			$data['data'] = $items;
	        $data['payment'] = $payment;
			if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 404,'message' => 'No pawn payment is found'));
			}
		}
	}
		


	function get_sale_detail($sale_id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('sales_model');
			$this->load->model('site');
			$data = array();
			$inv = $this->sales_model->getInvoiceByID($sale_id);
			$data['customer'] = $this->site->getCompanyByID($inv->customer_id);
	        $data['payments'] = $this->sales_model->getPaymentsForSale($sale_id);
	        $data['biller'] = $this->site->getCompanyByID($inv->biller_id);
	        $data['inv'] = $inv;
	        $data['rows'] = $this->sales_model->getAllInvoiceItems($sale_id);
			if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 404,'message' => 'No sale is found'));
			}
		}
	}

	function get_purchase_detail($purchase_id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('purchases_model');
			$this->load->model('site');
			$inv = $this->purchases_model->getPurchaseByID($purchase_id);
	        $data = array();
	        if($inv->status=='freight'){
                if($inv->purchase_id > 0){
                    $data['rows'] = $this->purchases_model->getPurchaseShippingItems($inv->purchase_id);
                }else{
                    $data['rows'] = $this->purchases_model->getPurchaseShippingItems(false,$inv->receive_id);
                }
                
            }else{
                if($this->Settings->product_serial == 1){
                    $data['rows'] = $this->purchases_model->getAllPurchaseItemsWithSerial($purchase_id);
                }else{
                    $data['rows'] = $this->api_model->getAllPurchaseItems($purchase_id);
                }
            }
	        $data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
	        $data['inv'] = $inv;
	        $data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
			if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 404,'message' => 'No purchase is found'));
			}
		}
	}

	function get_customer_open_balance(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$where = 'WHERE 1=1';
			if ($this->input->get('start_date')) {
				$where .= ' AND date(date) >= "'.$this->sma->fld($this->input->get('start_date')).'"';
			}
			if ($this->input->get('end_date')) {
				$where .= ' AND date(date) <= "'.$this->sma->fld($this->input->get('end_date'),false,1).'"';
			}
			if($this->input->get('saleman')){
				$where .= ' AND saleman_id = '.$this->input->get('saleman');
			}
			if($this->input->get('created_by')){
				$where .= ' AND created_by = '.$this->input->get('created_by');
			}
			if($this->input->get('biller')){
				$where .= ' AND biller_id = '.$this->input->get('biller');
			}
			if($this->input->get('warehouse')){
				$where .= ' AND warehouse_id = '.$this->input->get('warehouse');
			}
			if($this->input->get('customer')){
				$where .= ' AND customer_id = '.$this->input->get('customer');
			}
			$s = "( SELECT customer_id, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid_amount), 0) as paid, COALESCE(sum(discount_amount), 0) as discount, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid_amount), 0) - COALESCE(sum(discount_amount), 0)) as balance from {$this->db->dbprefix('sales')} 
					LEFT JOIN (
						SELECT
							sale_id,
							IFNULL(sum(amount), 0) AS paid_amount,
							IFNULL(sum(discount), 0) AS discount_amount
						FROM
							".$this->db->dbprefix('payments')."
						GROUP BY
							sale_id
					) AS sma_payments ON `sma_payments`.`sale_id` = ".$this->db->dbprefix('sales').".`id`
					{$where} GROUP BY {$this->db->dbprefix('sales')}.customer_id ) FS";
            $this->db
                ->select($this->db->dbprefix('companies') . ".id as id,code, company, name, phone,  FS.total, FS.total_amount, FS.paid, FS.discount, FS.balance", FALSE)
                ->from("companies")
                ->join($s, 'FS.customer_id=companies.id')
				->where('companies.group_name', 'customer')
				->group_by('companies.id');
            $data = $this->db->get()->result();
            if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 404,'message' => 'No expense is found'));
			}
		}
	}

	function get_customer_open_balance_detail($customer_id = false){
		$product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : NULL;
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date,false,1);
        }
		$si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name, '__', {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";
        if ($product) {
            $si .= " WHERE {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
        }
        $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
        $this->db->select("sales.id as id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, 
						reference_no,
						biller,
						customer,
						FSI.item_nane as iname,
						grand_total,
						IFNULL(total_return,0) as total_return,
						IFNULL(sma_payments.paid + IFNULL(total_return_paid,0),0) as paid,
						IFNULL(sma_payments.discount,0) as discount,
						ROUND((grand_total-(IFNULL(sma_payments.paid,0))-(IFNULL(sma_payments.discount,0))-(IFNULL(sma_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") as balance,
						IF (
							(
								round((grand_total-(IFNULL(sma_payments.paid,0))-(IFNULL(sma_payments.discount,0))-(IFNULL(sma_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") = 0
							),
							'paid',
							IF (
							(
								(grand_total-(IFNULL(sma_payments.paid,0))-(IFNULL(sma_payments.discount,0))-(IFNULL(sma_return.total_return + total_return_paid,0))) = grand_total
							),
							'pending',
							'partial'
						)) AS payment_status,
						{$this->db->dbprefix('sales')}.id as id", FALSE)
            ->from('sales')
            ->join($si, 'FSI.sale_id=sales.id', 'left')
			->join('(SELECT
					sum(abs(grand_total)) AS total_return,
					sum(paid) AS total_return_paid,
					sale_id
				FROM
					'.$this->db->dbprefix('sales').'
				WHERE sale_status = "returned"
				GROUP BY
					sale_id) as sma_return', 'sma_return.sale_id=sales.id', 'left')
			->join('(SELECT
						sale_id,
						IFNULL(sum(amount),0) AS paid,
						IFNULL(sum(discount),0) AS discount
					FROM
						'.$this->db->dbprefix('payments').'
					GROUP BY
						sale_id) as sma_payments', 'sma_payments.sale_id=sales.id', 'left')
            ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
			
		$this->db->where('sales.customer_id', $customer_id);		
        if ($user) {
            $this->db->where('sales.created_by', $user);
        }
        if ($product) {
            $this->db->where('FSI.product_id', $product, FALSE);
        }
        if ($serial) {
            $this->db->like('FSI.serial_no', $serial, FALSE);
        }
        if ($biller) {
            $this->db->where('sales.biller_id', $biller);
        }
        if ($warehouse) {
            $this->db->where('sales.warehouse_id', $warehouse);
        }
        if ($reference_no) {
            $this->db->like('sales.reference_no', $reference_no, 'both');
        }
        if ($start_date) {
            $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
		$this->db->where('sale_status !=', 'returned');
        $data = $this->db->get()->result();
        if($data){
			echo json_encode($data);
		}else{
			echo json_encode(array('status' => 404,'message' => 'No expense is found'));
		}
	}

	function get_supplier_open_balance(){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$where = '';
			if ($this->input->get('start_date')) {
				$where .= ' AND date(date) >= "'.$this->sma->fld($this->input->get('start_date')).'"';
			}
			if ($this->input->get('end_date')) {
				$where .= ' AND date(date) <= "'.$this->sma->fld($this->input->get('end_date'),false,1).'"';
			}
			if($this->input->get('created_by')){
				$where .= ' AND created_by = '.$this->input->get('created_by');
			}
			if($this->input->get('biller')){
				$where .= ' AND biller_id = '.$this->input->get('biller');
			}
			if($this->input->get('warehouse')){
				$where .= ' AND warehouse_id = '.$this->input->get('warehouse');
			}
			if($this->input->get('supplier')){
				$where .= ' AND supplier_id = '.$this->input->get('supplier');
			}
            $p = "( SELECT supplier_id, count(" . $this->db->dbprefix('purchases') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance from {$this->db->dbprefix('purchases')} WHERE 1=1 {$where} GROUP BY {$this->db->dbprefix('purchases')}.supplier_id ) FP";
            $this->db
                ->select($this->db->dbprefix('companies').".id as id, code, company, name, phone,  (FP.total + IFNULL(sma_expenses.expense_total,0)) as total, (FP.total_amount + IFNULL(sma_expenses.expense_amount,0)) as total_amount, (FP.paid + IFNULL(sma_expenses.expense_paid,0)) as paid, (FP.balance + IFNULL(sma_expenses.expense_balance,0)) as balance", FALSE)
                ->from("companies")
                ->join($p, 'FP.supplier_id=companies.id')
				->join("(select supplier_id,count(id) AS expense_total,sum(grand_total) AS expense_amount,sum(IFNULL(paid,0)) AS expense_paid,sum(amount - IFNULL(paid,0)) AS expense_balance FROM ".$this->db->dbprefix('expenses')." GROUP BY supplier_id) as sma_expenses","sma_expenses.supplier_id = companies.id","left")
                ->where('companies.group_name', 'supplier')
				->group_by('companies.id');
            $data = $this->db->get()->result();
            if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 404,'message' => 'No expense is found'));
			}
		}
	}

	function get_suppplier_open_balance_detail($supplier_id = false){
		$product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$project = $this->input->get('project') ? $this->input->get('project') : NULL;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date,false,1);
        }
		$pi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.quantity) SEPARATOR '___')) as item_nane from {$this->db->dbprefix('purchase_items')} ";
        if ($product) {
            $pi .= " WHERE {$this->db->dbprefix('purchase_items')}.product_id = {$product} ";
        }
        $pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id ) FPI";
		$this->db->select("
					purchases.id,
					DATE_FORMAT(".$this->db->dbprefix('purchases').".date, '%Y-%m-%d %T') as date, 
					purchases.reference_no,
					purchases.biller,
					projects.name,
					{$this->db->dbprefix('warehouses')}.name as wname,
					purchases.supplier, 
					(FPI.item_nane) as iname,
					purchases.grand_total,
					abs(IFNULL(sma_purchases.return_purchase_total,0)) as return_purchase_total,
					(IFNULL(sma_purchases.paid,0) - IFNULL(return_paid,0)) as paid, 
					round((sma_purchases.grand_total-(IFNULL(sma_purchases.paid,0) - IFNULL(return_paid,0))-abs(sma_purchases.return_purchase_total)),".$this->Settings->decimals.") as balance, 
					purchases.status, 
					IF(
						(round((sma_purchases.grand_total-(IFNULL(sma_purchases.paid,0) - IFNULL(return_paid,0))-abs(sma_purchases.return_purchase_total)),".$this->Settings->decimals."))=0,'paid',
						IF(
							(abs(IFNULL(sma_purchases.return_purchase_total,0)) + IFNULL(sma_purchases.paid,0) - IFNULL(return_paid,0))<>0,'partial',
							'pending'
						)
					) as payment_status,
					purchases.attachment,
					purchases.id as id")
					->join('(select purchase_id,abs(paid) as return_paid from sma_purchases WHERE purchase_id > 0 AND status <> "draft" AND status <> "freight") as pur_return','pur_return.purchase_id = purchases.id','left')
					->join($pi, 'FPI.purchase_id=purchases.id', 'left')
					->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
					->join("projects","projects.id=purchases.project_id","left")
					->from('purchases');
		$this->db->where('purchases.status !=', 'returned');	
        $this->db->where('purchases.status !=', 'freight');   
		$this->db->where('purchases.supplier_id', $supplier_id);
		if ($biller) {
            $this->db->where('purchases.biller_id', $biller);
        }
		if ($project) {
            $this->db->where('purchases.project_id', $project);
        }
        if ($user) {
            $this->db->where('purchases.created_by', $user);
        }
        if ($product) {
            $this->db->where('FPI.product_id', $product, FALSE);
        }
        if ($warehouse) {
            $this->db->where('purchases.warehouse_id', $warehouse);
        }
        if ($reference_no) {
            $this->db->like('purchases.reference_no', $reference_no, 'both');
        }
        if ($start_date) {
            $this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $data = $this->db->get()->result();
        if($data){
			echo json_encode($data);
		}else{
			echo json_encode(array('status' => 404,'message' => 'No expense is found'));
		}
	}
	
	function get_expense_detail($expense_id = false){
		$check_auth_client = $this->check_auth_client("GET");
		if($check_auth_client==true){
			$this->load->model('purchases_model');
			$this->load->model('site');
			$data = array();
			$expense = $this->purchases_model->getExpenseByID($expense_id);
			$data['items'] = $this->purchases_model->getExpenseItems($expense_id);
			$data['biller'] = $this->site->getCompanyByID($expense->biller_id);
	        $data['expense'] = $expense;
			if($data){
				echo json_encode($data);
			}else{
				echo json_encode(array('status' => 404,'message' => 'No expense is found'));
			}
		}
	}

	function update_profile(){
		$check_auth_client = $this->check_auth_client("POST");
		if($check_auth_client==true){
			$company_name = $this->input->post("company_name");
			$first_name = $this->input->post("first_name");
			$last_name = $this->input->post("last_name");
			$phone = $this->input->post("phone");
			$gender = $this->input->post("gender");
			$email = $this->input->post("email");
			$userId = $this->input->get_request_header('User-ID', TRUE);
			$data = array(
				'company'=> $company_name,
				'first_name'=> $first_name,
				'last_name'=> $last_name,
				'phone'=> $phone,
				'gender'=> $gender,
				'email'=> $email
			);
			$response = $this->api_model->update_profile($data, $userId);
			if($response){
				echo json_encode(array('status' => 200,'message' => 'Your profile was update successfully.','success' => true));
			}else{
				echo json_encode(array('status' => 200,'message' => 'Your profile was update false.','success' => false));
			}
			
			
		}
	}

	function change_password(){
		$check_auth_client = $this->check_auth_client("POST");
		if($check_auth_client==true){
			$current_password = $this->input->post("current_password");
			$new_password = $this->input->post("new_password");
			$confirm_password = $this->input->post("confirm_password");
			$userId = $this->input->get_request_header('User-ID', TRUE);
			$response = $this->ion_auth->change_password($userId, $current_password, $new_password);
			if($response){
				echo json_encode(array('status' => 200,'message' => 'Your password was update successfully.','success' => true));
			}else{
				echo json_encode(array('status' => 200,'message' => 'Your password was update failed.','success' => false));
			}
		}
	}
	

}
