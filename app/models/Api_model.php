<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api_model extends CI_Model
{
	protected $client_service = "frontend-client";
	protected $auth_key       = "simplerestapi";
    function __construct()
    {
        parent::__construct();
		$this->load->config('ion_auth', TRUE);
		$this->hash_method = $this->config->item('hash_method', 'ion_auth');
		$this->store_salt = $this->config->item('store_salt', 'ion_auth');
		$this->salt_length = $this->config->item('salt_length', 'ion_auth');
    }
	function getLicense($code = false){
		$q = $this->db->get_where("api_licenses",array("code"=>$code));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
    function check_auth_client(){
        $client_service = $this->input->get_request_header('Client-Service', TRUE);
        $auth_key  = $this->input->get_request_header('Auth-Key', TRUE);
        if($client_service == $this->client_service && $auth_key == $this->auth_key){
            return true;
        } else {
            return false;
        }
    }
	function login($username = false, $password = false){
		if (empty($username) || empty($password)) {
            return FALSE;
        }
        $query = $this->db->select('id,username,active,first_name,last_name,email,phone,gender')
            ->where('username', $this->db->escape_str($username))
            ->limit(1)
            ->get('users');

        if ($query->num_rows() === 1) {
            $user = $query->row();
            $password = $this->hash_password_db($user->id, $password);
            if ($password === TRUE) {
                if ($user->active != 1) {
                    return FALSE;
                }
                return $user;
            }
        }
        return FALSE;
	}
	function hash_password_db($id = false, $password = false, $use_sha1_override = FALSE)
    {
        if (empty($id) || empty($password)) {
            return FALSE;
        }
        $query = $this->db->select('password, salt')
            ->where('id', $id)
            ->limit(1)
            ->get('users');
        $hash_password_db = $query->row();
        if ($query->num_rows() !== 1) {
            return FALSE;
        }
        if ($use_sha1_override === FALSE && $this->hash_method == 'bcrypt') {
            if ($this->bcrypt->verify($password, $hash_password_db->password)) {
                return TRUE;
            }
            return FALSE;
        }
        if ($this->store_salt) {
            $db_password = sha1($password . $hash_password_db->salt);
        } else {
            $salt = substr($hash_password_db->password, 0, $this->salt_length);
            $db_password = $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
        }
        if ($db_password == $hash_password_db->password) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

	function getProducts(){
		$this->db->select("id,code,name,quantity,cost,price,image")
					->where("inactive !=",1);
		$q = $this->db->get("products");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			
		}
		return $data;
	}

	function getProductBalance(){
		$this->db->select("id,code,name,quantity,cost,price,image,convert_qty(".$this->db->dbprefix('products') . ".id,IF({$this->db->dbprefix('products')}.type='service' OR {$this->db->dbprefix('products')}.type='bom' OR {$this->db->dbprefix('products')}.type='combo', '0', COALESCE(quantity, 0))) as quantity")
					->where("inactive !=",1)
					->where("track_quantity",1)
					->where("quantity !=",0);
		$q = $this->db->get("products");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$quantity = $row->quantity;
				$row->quantity = strip_tags($quantity);
				$data[] = $row;
			}
			
		}
		return $data;
	}

	function getCategories(){
		$this->db->select("id,code,name,image");
		$q = $this->db->get("categories");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
		}
		return $data;
	}
 

	function getCustomers(){
		$this->db->select("id, code, company, name, phone");
		$this->db->where('group_name', 'customer');
		$q = $this->db->get("companies");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
		}
		return $data;
	}

	function getSuppliers(){
		$this->db->select("id, code, company, name, phone");
		$this->db->where('group_name', 'supplier');
		$q = $this->db->get("companies");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
		}
		return $data;
	}

	function getExpenseCategories(){
		$this->db->select("id,code,name");
		$q = $this->db->get("expense_categories");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
		}
		return $data;
	}

	function getSales($start_date = false, $end_date = false, $customer = false){
		if($start_date){
			$this->db->where("date(date) >=",$start_date);
		}else{
			$this->db->where("date(date) >=",date("Y-m-d"));
		}
		if($end_date){
			$this->db->where("date(date) <=",$end_date);
		}else{
			$this->db->where("date(date) <=",date("Y-m-d"));
		}
		if($customer){
			$this->db->where("customer_id",$customer);
		}
		$this->db->select("id,date,reference_no,customer,grand_total,paid,sale_status");
		$this->db->order_by("sales.date", "DESC");
		$q = $this->db->get("sales");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
		}
		return $data;
	}

	function getOpenInvoice($start_date = false, $end_date = false, $customer = false){
        if($end_date){
            $end_date = $end_date;
        }else{
            $end_date = date("Y-m-d");
        }
        $and_where = "";
        if($customer){
            $and_where = "AND customer_id = $customer";
        }
        $q = $this->db->query("SELECT
            `cus_sales`.`id`,
            `cus_sales`.`date`,
            `cus_sales`.`reference_no`,
            `cus_sales`.`customer`,
            `cus_sales`.`grand_total`,
            `cus_sales`.`paid`,
            `cus_sales`.`sale_status`,
            `pay`.total as total_paid,
            `pay`.balance
            FROM
            `cus_sales`
            JOIN (
            SELECT cus_payments.sale_id, sum(amount) AS total, (grand_total- sum(amount)) as balance
            FROM cus_payments
            INNER JOIN `cus_sales` ON `cus_payments`.`sale_id` = `cus_sales`.`id`
            WHERE date( cus_payments.date ) <= '$end_date'
            GROUP BY cus_payments.sale_id ) pay ON pay.sale_id = cus_sales.id

            WHERE
            balance > 0
            ORDER BY
            `cus_sales`.`date` DESC");
        
        $data = array();
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
        }
        return $data;
    }

	function getSaleByCustomer($customer = false){
        $si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name, '__', {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";
        $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
		$this->db->select("sales.id as id, DATE_FORMAT(date, '%Y-%m-%d %T') as date,sale_status,
						reference_no,
						biller,
						customer,
						FSI.item_nane as iname,
						grand_total,
						IFNULL(total_return,0) as total_return,
						IFNULL(cus_payments.paid + IFNULL(total_return_paid,0),0) as paid,
						IFNULL(cus_payments.discount,0) as discount,
						ROUND((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") as balance,
						IF (
							(
								round((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") = 0
							),
							'paid',
							IF (
							(
								(grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))) = grand_total
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
					sale_id) as cus_return', 'cus_return.sale_id=sales.id', 'left')
			->join('(SELECT
						sale_id,
						IFNULL(sum(amount),0) AS paid,
						IFNULL(sum(discount),0) AS discount
					FROM
						'.$this->db->dbprefix('payments').'
					GROUP BY
						sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left')
			->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');

		if ($customer) {
			$this->db->where('sales.customer_id', $customer);
		}
		$this->db->where('sale_status !=', 'returned');
		$q = $this->db->get();
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
		}
		return $data;
    }

	function searchSales($start_date = false, $end_date = false, $customer = false){
		if($start_date){
			$this->db->where("date(date) >=",$this->sma->fsd($start_date));
		}else{
			$this->db->where("date(date) >=",date("Y-m-d"));
		}
		if($end_date){
			$this->db->where("date(date) <=",$this->sma->fsd($end_date));
		}else{
			$this->db->where("date(date) <=",date("Y-m-d"));
		}
		if($customer){
			$this->db->where("customer_id",$customer);
		}
		$this->db->select("id,date,reference_no,customer,grand_total,paid")
				->where("sale_status !=", "draft")
				->where("sale_status !=", "returned");
		$q = $this->db->get("sales");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			
		}
		return $data;
	}

	function getPurchases($start_date = false, $end_date = false, $supplier = false){
		if($start_date){
			$this->db->where("date(date) >=",$start_date);
		}else{
			$this->db->where("date(date) >=",date("Y-m-d"));
		}
		if($end_date){
			$this->db->where("date(date) <=",$end_date);
		}else{
			$this->db->where("date(date) <=",date("Y-m-d"));
		}
		if($supplier){
			$this->db->where("supplier_id",$supplier);
		}
        $this->db->where('status !=', 'draft');
		$this->db->where('status !=', 'freight');
		$this->db->select("id,date,reference_no,supplier,grand_total,paid,status");
		$this->db->order_by("purchases.date", "DESC");
		$q = $this->db->get("purchases");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
		}
		return $data;
	}

	function getOpenPurchases($start_date = false, $end_date = false, $supplier = false){
		if($end_date){
            $end_date = $end_date;
        }else{
            $end_date = date("Y-m-d");
        }
        $and_where = "";
        if($supplier){
            $and_where = "AND supplier_id = $supplier";
        }
        $q = $this->db->query("SELECT
              `cus_purchases`.`id`,
              `cus_purchases`.`date`,
              `cus_purchases`.`reference_no`,
              `cus_purchases`.`supplier`,
              `cus_purchases`.`grand_total`,
              `cus_purchases`.`paid`,
              `cus_purchases`.`status`,
              `pay`.total_amount as payment_amount,
              `pay`.balance
			FROM
              `cus_purchases`
              JOIN (
                  SELECT cus_payments.purchase_id, sum(amount) AS total_amount, (grand_total- sum(amount)) as balance
                  FROM cus_payments
                  INNER JOIN `cus_purchases` ON `cus_payments`.`purchase_id` = `cus_purchases`.`id`
                  WHERE date( cus_payments.date ) <= '$end_date'
                  GROUP BY cus_payments.purchase_id ) pay ON pay.purchase_id = cus_purchases.id
			WHERE
              `status` != 'draft'
              AND `status` != 'freight' AND balance > 0 $and_where
			ORDER BY
              `cus_purchases`.`date` DESC");
			$data = array();
			if($q->num_rows() > 0){
				foreach($q->result() as $row){
					$data[] = $row;
				}
			}
			return $data;
	}

	function getPurchaseBySupplier($supplier = false){
		if($supplier){
			$this->db->where("supplier_id",$supplier);
		}
		$this->db->select("id,date,reference_no,supplier,grand_total,paid,status");
		$q = $this->db->get("purchases");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
		}
		return $data;
	}

	function getPurchaseBySupplierBalance($supplier = false){
        $pi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.quantity) SEPARATOR '___')) as item_nane from {$this->db->dbprefix('purchase_items')} ";
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
                    abs(IFNULL(cus_purchases.return_purchase_total,0)) as return_purchase_total,
                    (IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0)) as paid,
                    round((cus_purchases.grand_total-(IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))-abs(cus_purchases.return_purchase_total)),".$this->Settings->decimals.") as balance,
                    purchases.status,
                    IF(
                        (round((cus_purchases.grand_total-(IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))-abs(cus_purchases.return_purchase_total)),".$this->Settings->decimals."))=0,'paid',
                        IF(
                            (abs(IFNULL(cus_purchases.return_purchase_total,0)) + IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))<>0,'partial',
                            'pending'
                        )
                    ) as payment_status,
                    purchases.attachment,
                    purchases.id as id")
                    ->join('(select purchase_id,abs(paid) as return_paid from cus_purchases WHERE purchase_id > 0 AND status <> "draft" AND status <> "freight") as pur_return','pur_return.purchase_id = purchases.id','left')
                    ->join($pi, 'FPI.purchase_id=purchases.id', 'left')
                    ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                    ->join("projects","projects.id=purchases.project_id","left")
                    ->from('purchases');
        $this->db->where('purchases.status !=', 'returned');
        $this->db->where('purchases.status !=', 'freight');
        if ($supplier) {
            $this->db->where('purchases.supplier_id', $supplier);
        }
        $q = $this->db->get();
        $data = array();
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
        }
        return $data;
    }

	function getExpenseBySupplierBalance($supplier = false){
        $this->db->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference, biller, supplier,  grand_total, paid , (grand_total- ifnull(paid,0)) as balance, expenses.note as enote, CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as user, attachment, payment_status,{$this->db->dbprefix('expenses')}.id as id", false)
			->from('expenses')
			->join('users', 'users.id=expenses.created_by', 'left')
			->group_by('expenses.id');
		if ($supplier) {
		  $this->db->where('expenses.supplier_id', $supplier);
		}
		$q = $this->db->get();
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
		}
		return $data;
    }

	function getFreightBySupplierBalance($supplier = false){
        $this->db->select("
                        DATE_FORMAT(".$this->db->dbprefix('purchases').".date, '%Y-%m-%d %T') as date,
                        purchases.reference_no,
                        companies.company,
                        projects.name,
                        {$this->db->dbprefix('warehouses')}.name as wname,
                        purchases.supplier,
                        purchases.grand_total,
                        purchases.paid,
                        cus_purchases.grand_total - IFNULL({$this->db->dbprefix('purchases')}.paid,0) as balance,
                        IF(
                            cus_purchases.grand_total = cus_purchases.paid,'paid',
                            IF(cus_purchases.paid > 0,'partial','pending')
                        ) as payment_status,
                        purchases.id as id")
                        ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                        ->join("projects","projects.id=purchases.project_id","left")
                        ->join('companies', 'companies.id=purchases.biller_id', 'left')
                        ->from('purchases');
        $this->db->where('purchases.grand_total >', 0);
        $this->db->where('purchases.status', 'freight');
        if ($supplier) {
            $this->db->where('purchases.supplier_id', $supplier);
        }  
        $q = $this->db->get();
        $data = array();
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
        }
        return $data;
    }

	function searchPurchases($start_date = false, $end_date = false, $supplier = false){
		if($start_date){
			$this->db->where("date(date) >=",$this->sma->fsd($start_date));
		}else{
			$this->db->where("date(date) >=",date("Y-m-d"));
		}
		if($end_date){
			$this->db->where("date(date) <=",$this->sma->fsd($end_date));
		}else{
			$this->db->where("date(date) <=",date("Y-m-d"));
		}
		if($supplier){
			$this->db->where("supplier_id",$supplier);
		}
		$this->db->select("id,date,reference_no,supplier,grand_total,paid");
		$q = $this->db->get("purchases");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
		}
		return $data;
	}

	function getExpenses($start_date = false, $end_date = false, $customer = false){
		if($start_date){
			$this->db->where("date(date) >=",$start_date);
		}else{
			$this->db->where("date(date) >=",date("Y-m-d"));
		}
		if($end_date){
			$this->db->where("date(date) <=",$end_date);
		}else{
			$this->db->where("date(date) <=",date("Y-m-d"));
		}
		if($customer){
			$this->db->where("supplier_id",$customer);
		}
		$this->db->select("id,date,reference,supplier,grand_total");
		$q = $this->db->get("expenses");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			
		}
		return $data;
	}

	function searchExpenses($start_date = false, $end_date = false, $category_id = false){
		if($start_date){
			$this->db->where("date(date) >=", $start_date);
		}else{
			$this->db->where("date(date) >=",date("Y-m-d"));
		}
		if($end_date){
			$this->db->where("date(date) <=", $end_date);
		}else{
			$this->db->where("date(date) <=",date("Y-m-d"));
		}
		if($category_id){
			$this->db->where("category_id",$category_id);
		}
		$this->db->select("id,date,reference,supplier,grand_total");
		$q = $this->db->get("expenses");
		$data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			
		}
		return $data;
	}


	function getSaleItemsBySale($sale_id = false){
		if($sale_id){
			$this->db->select("product_code,product_name,unit_quantity,unit_price,subtotal")
					->where("sale_id",$sale_id);
			$q = $this->db->get("sale_items");		
			if($q->num_rows() > 0){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}
			return false;
		}
		return false;
	}
	
	function getPurchaseItemsByPurchase($purchase_id = false){
		if($purchase_id){
			$this->db->select("product_code, product_name,unit_quantity,unit_cost,subtotal")
					->where("purchase_id",$purchase_id);
			$q = $this->db->get("purchase_items");		
			if($q->num_rows() > 0){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}
			return false;
		}
		return false;
	}
	
	function getExpenseItemsByExpense($expense_id = false){
		if($expense_id){
			$this->db->select("*")
					->where("expense_id",$expense_id);
			$q = $this->db->get("expense_items");		
			if($q->num_rows() > 0){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}
			return false;
		}
		return false;
	}

	function getPayments($start_date = false, $end_date = false, $customer = false, $supplier = false){
        $this->db
    	->select("DATE_FORMAT({$this->db->dbprefix('payments')}.date, '%Y-%m-%d %T') as date,
			".$this->db->dbprefix('payments').".reference_no as payment_ref,
			" . $this->db->dbprefix('sales') . ".reference_no as sale_ref,
			IFNULL(".$this->db->dbprefix('purchases').".reference_no,".$this->db->dbprefix('expenses').".reference) as purchase_ref,
			DATE_FORMAT(IFNULL({$this->db->dbprefix('expenses')}.date,IFNULL({$this->db->dbprefix('sales')}.date,{$this->db->dbprefix('purchases')}.date)),'%Y-%m-%d %T') AS reference_date,
			paid_by, IFNULL({$this->db->dbprefix('payments')}.amount,0) as amount , 
			IFNULL(discount,0) as discount, 
			payments.type, 
			{$this->db->dbprefix('payments')}.id as id,
			{$this->db->dbprefix('payments')}.transaction")
        ->from('payments')
        ->join('sales', 'payments.sale_id=sales.id', 'left')
        ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
		->join('expenses', 'payments.expense_id=expenses.id', 'left')
		->where('payments.installment_id IS NULL')
        ->group_by('payments.id');

        if ($customer) {
            $this->db->where('sales.customer_id', $customer);
        }
        if ($supplier) {
			$this->db->where('purchases.supplier_id="'.$supplier.'" OR expenses.supplier_id="'.$supplier.'"');
        }
        if($start_date && $end_date) {
            $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }else{
        	$this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . date("Y-m-d") . '" and "' . date("Y-m-d") . '"');
        }
        $this->db->order_by($this->db->dbprefix('payments').'.date', 'DESC');
        $q = $this->db->get();
        $data = array();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			
		}
		return $data;
	}
    
    function update_profile($data = false, $user_id = false){
		if ($this->db->update('users', $data, array('id' => $user_id))) {
            return true;
        }else{
        	return false;
        }
    }

    public function getAllWarehousesWithPQ($product_id = false){
        $this->db->select('' . $this->db->dbprefix('warehouses') . '.*, ' . $this->db->dbprefix('warehouses_products') . '.quantity, ' . $this->db->dbprefix('warehouses_products') . '.rack')
            ->join('warehouses_products', 'warehouses_products.warehouse_id=warehouses.id', 'left')
            ->where('warehouses_products.product_id', $product_id)
            ->group_by('warehouses.id');
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
            	$row->product_unit = strip_tags($this->cus->convertQty($product_id,$row->quantity));
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductOptionsWithWH($pid = false){
        $this->db->select($this->db->dbprefix('product_variants') . '.*, ' . $this->db->dbprefix('warehouses') . '.name as wh_name, ' . $this->db->dbprefix('warehouses') . '.id as warehouse_id, ' . $this->db->dbprefix('warehouses_products_variants') . '.quantity as wh_qty')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->join('warehouses', 'warehouses.id=warehouses_products_variants.warehouse_id', 'left')
            ->group_by(array('' . $this->db->dbprefix('product_variants') . '.id', '' . $this->db->dbprefix('warehouses_products_variants') . '.warehouse_id'))
            ->order_by('product_variants.id');
        $q = $this->db->get_where('product_variants', array('product_variants.product_id' => $pid, 'warehouses_products_variants.quantity !=' => NULL));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getAllPurchaseItems($purchase_id = false)
    {
        $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant,units.name as unit_name,products.image as image')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
			->join('units', 'units.id=purchase_items.product_unit_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('id', 'desc');
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

}
