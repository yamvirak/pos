<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pos_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();		
    }
	
	function getBomProductByStandProduct($pid = false, $bom_type = false)
	{
        $where = "";
        if($bom_type){
            $where = " AND (bom_type='".$bom_type."' OR bom_type='')";
        }
		$q = $this->db->query('SELECT
							bom_products.product_id,
							bom_products.unit_id,
							bom_products.quantity * unit_qty AS quantity,
							product_units.unit_qty,
							units.`code`,
							products.cost,
							products.code as product_code,
							products.type as product_type
						FROM
							'.$this->db->dbprefix("bom_products").' as bom_products
						INNER JOIN '.$this->db->dbprefix("product_units").' as product_units  ON product_units.product_id = bom_products.product_id
						AND product_units.unit_id = bom_products.unit_id
						INNER JOIN '.$this->db->dbprefix("units").' as units ON units.id = product_units.unit_id
						INNER JOIN '.$this->db->dbprefix("products").' as products ON products.id = bom_products.product_id
						WHERE
							bom_products.standard_product_id = "'.$pid.'" '.$where.'');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;					
	}
	
    function getSetting()
    {
        $q = $this->db->get('pos_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getProductSerial($serial=false, $product_id = false, $warehouse_id = false)
	{
		$q = $this->db->get_where('product_serials', array('product_id' => $product_id,'serial' => $serial,'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}

    public function updateSetting($data = false)
    {
        $this->db->where('pos_id', '1');
        if ($this->db->update('pos_settings', $data)) {
            return true;
        }
        return false;
    }

    public function products_count($category_id = false, $subcategory_id = NULL, $brand_id = NULL, $code = NULL, $name = NULL, $favorite = NULL)
    {
		if(!$code && !$name && !$favorite){
			if ($category_id) {
				$this->db->where('category_id', $category_id);
			}
			if ($subcategory_id) {
				$this->db->where('subcategory_id', $subcategory_id);
			}
			if ($brand_id) {
				$this->db->where('brand', $brand_id);
			}
		}
		if ($code) {
            $this->db->like('code', $code);
        }
		if ($name) {
            $this->db->like('name', $name);
        }
		if ($favorite) {
            $this->db->where('rate', $favorite);
        }
        $this->db->from('products');
        return $this->db->count_all_results();
    }
	
	public function getSaleOrderByApproval($product_id = NULL, $sale_order_id = NULL)
	{
		$q = $this->db->query("SELECT
									SUM(".$this->db->dbprefix('sale_order_items').".quantity) AS quantity
								FROM
									".$this->db->dbprefix('sale_orders')."
								JOIN ".$this->db->dbprefix('sale_order_items')." ON ".$this->db->dbprefix('sale_order_items').".sale_order_id = ".$this->db->dbprefix('sale_orders').".id
								WHERE
									`product_id` = '16'
								AND ".$this->db->dbprefix('sale_orders').".`status` = 'approved'
								AND ".$this->db->dbprefix('sale_order_items').".sale_order_id IS NOT NULL");
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

    public function fetch_products($category_id = false, $limit = false, $start = false, $subcategory_id = NULL, $brand_id = NULL, $code = NULL, $name = NULL, $favorite = NULL)
    {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
		
        $this->db->limit($limit, $start);
		$this->db->where('products.inactive !=',1);
		if(!$code && !$name && !$favorite){
			if ($brand_id) {
				$this->db->where('brand', $brand_id);
			} elseif ($category_id) {
				$this->db->where('category_id', $category_id);
			}
			if ($subcategory_id) {
				$this->db->where('subcategory_id', $subcategory_id);
			}
		}
		if ($code) {
            $this->db->like('code', $code);
        }
		if ($name) {
            $this->db->like('name', $name);
        }
		if ($favorite) {
            $this->db->where('rate', $favorite);
        }
		$this->db->where('type !=','raw_material');
		$this->db->where('type !=','asset');
        $this->db->order_by("name", "asc");
        $query = $this->db->get("products");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function registerData($user_id = false)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('pos_register', array('user_id' => $user_id, 'status' => 'open'), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
		
	public function OpenRegisterData($id = false)
    {
        $q = $this->db->get_where('pos_register', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function closeRegisterData($id = false)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('pos_register', array('id' => $id, 'status' => 'close'), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function openRegister($data = false)
    {
        if ($this->db->insert('pos_register', $data)) {
            return true;
        }
        return FALSE;
    }
	
	public function getOpenRegisterByUser($user_id = false){
		$this->db->where("pos_register.status","open");
		$this->db->where("pos_register.user_id",$user_id);
		$q = $this->db->get("pos_register");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

    public function getOpenRegisters()
    {
        $this->db->select("date, user_id, cash_in_hand, CONCAT(" . $this->db->dbprefix('users') . ".last_name, ' ', " . $this->db->dbprefix('users') . ".first_name) as user", FALSE)
            ->join('users', 'users.id=pos_register.user_id', 'left');
        $q = $this->db->get_where('pos_register', array('status' => 'open'));
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;

    }

    public function closeRegister($rid = false, $user_id = false, $data = false, $products = false)
    {
        if (!$rid) {
            $rid = $this->session->userdata('register_id');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        if ($data['transfer_opened_bills'] == -1) {
            $this->db->delete('suspended_bills', array('created_by' => $user_id));
        } elseif ($data['transfer_opened_bills'] != 0) {
            $this->db->update('suspended_bills', array('created_by' => $data['transfer_opened_bills']), array('created_by' => $user_id));
        }
        if ($this->db->update('pos_register', $data, array('id' => $rid, 'user_id' => $user_id))) {
			if($products){
				$this->db->insert_batch("pos_register_items",$products);
			}
            return true;
        }
        return FALSE;
    }
	
	public function countMoney($data = array()){
		if($data){
			$this->db->insert('count_money',$data);
			return true;
		}
		return false;
	}

    public function getUsers()
    {
        $q = $this->db->get_where('users', array('company_id' => NULL));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getUserByID($user_id = false){
		$q = $this->db->get_where('users', array('id'=>$user_id));
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return false;
	}

    public function getProductsByCode($code = false)
    {
        $this->db->like('code', $code, 'both')->order_by("code");
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getWHProduct($code = false, $warehouse_id = false)
    {
        $this->db->select('products.*, warehouses_products.quantity, categories.id as category_id, categories.type as category_type, categories.name as category_name , product_serials.serial,product_serials.price as serial_price')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->join('product_serials','product_serials.product_id = products.id AND product_serials.warehouse_id = "'.$warehouse_id.'" AND IFNULL('.$this->db->dbprefix("product_serials").'.inactive,0) =0','LEFT')
			->group_by('products.id');
        $q = $this->db->get_where("products", array('products.code' => $code));
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getProductOptions($product_id = false, $warehouse_id = false, $all = NULL)
    {
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', FALSE)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if (! $this->Settings->overselling && ! $all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getComboProducts($pid = false){
		$this->db->select('products.id,products.name,products.code, combo_items.unit_price as price, combo_items.quantity as qty, combo_items.quantity as width, 1 as height');
		$this->db->join('products','products.id = combo_items.item_id','inner');
		$this->db->where('combo_items.product_id',$pid);
		$q = $this->db->get('combo_items');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

    public function getProductComboItems($pid = false, $warehouse_id = NULL)
    {
        $this->db->select('products.*, products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name,products.type as type, products.price as price, warehouses_products.quantity as quantity, combo_items.option_id')
            ->join('products', 'products.id=combo_items.item_id', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('combo_items.id');
        if($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function updateOptionQuantity($option_id = false, $quantity = false)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('product_variants', array('quantity' => $nq), array('id' => $option_id))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function addOptionQuantity($option_id = false, $quantity = false)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('product_variants', array('quantity' => $nq), array('id' => $option_id))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function getProductOptionByID($id = false)
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductWarehouseOptionQty($option_id = false, $warehouse_id = false)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateProductOptionQuantity($option_id = false, $warehouse_id = false, $quantity = false, $product_id = false)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        }
        return FALSE;
    }

    public function addSale($data = array(), $items = array(), $payments = array(), $sid = NULL, $biller_id= NULL, $stockmoves = array(), $accTrans = array(), $accTranPayments = array())
    {
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $sale_id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
            foreach ($items as $item) {
				unset($item['suspend_item_id']);
				unset($item['ordered']);
				unset($item['ordered_by']);
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
            }
            $msg = array();
            if (!empty($payments)) {
                $paid = 0;
                foreach ($payments as $index => $payment) {
                    if (!empty($payment) && isset($payment['amount']) && $payment['amount'] != 0) {
                        $payment['sale_id'] = $sale_id;
						if($this->pos_settings->pos_payment==0){
							$pay_reference_no = $data['reference_no'];
						}else{
							$pay_reference_no = $this->site->getReference('pay', $biller_id);
						}
						
                        $payment['reference_no'] = $pay_reference_no;
                        if ($payment['paid_by'] == 'ppp') {
                            $card_info = array("number" => $payment['cc_no'], "exp_month" => $payment['cc_month'], "exp_year" => $payment['cc_year'], "cvc" => $payment['cc_cvv2'], 'type' => $payment['cc_type']);
                            $result = $this->paypal($payment['amount'], $card_info);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['date'] = $this->cus->fld($result['created_at'],1);
                                $payment['amount'] = $result['amount'];
                                $payment['currency'] = $result['currency'];
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);
  
                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                if (!empty($result['message'])) {
                                    foreach ($result['message'] as $m) {
                                        $msg[] = '<p class="text-danger">' . $m['L_ERRORCODE'] . ': ' . $m['L_LONGMESSAGE'] . '</p>';
                                    }
                                } else {
                                    $msg[] = lang('paypal_empty_error');
                                }
                            }
                        } elseif ($payment['paid_by'] == 'stripe') {
                            $card_info = array("number" => $payment['cc_no'], "exp_month" => $payment['cc_month'], "exp_year" => $payment['cc_year'], "cvc" => $payment['cc_cvv2'], 'type' => $payment['cc_type']);
                            $result = $this->stripe($payment['amount'], $card_info);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['date'] = $this->cus->fld($result['created_at'],1);
                                $payment['amount'] = $result['amount'];
                                $payment['currency'] = $result['currency'];
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);

                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                $msg[] = '<p class="text-danger">' . $result['code'] . ': ' . $result['message'] . '</p>';
                            }
                        } elseif ($payment['paid_by'] == 'authorize') {
                            $authorize_arr = array("x_card_num" => $payment['cc_no'], "x_exp_date" => ($payment['cc_month'].'/'.$payment['cc_year']), "x_card_code" => $payment['cc_cvv2'], 'x_amount' => $payment['amount'], 'x_invoice_num' => $sale_id, 'x_description' => 'Sale Ref '.$data['reference_no'].' and Payment Ref '.$payment['reference_no']);
                            list($first_name, $last_name) = explode(' ', $payment['cc_holder'], 2);
                            $authorize_arr['x_first_name'] = $first_name;
                            $authorize_arr['x_last_name'] = $last_name;
                            $result = $this->authorize($authorize_arr);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['approval_code'] = $result['approval_code'];
                                $payment['date'] = $this->cus->fld($result['created_at'],1);
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);

                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                $msg[] = '<p class="text-danger">' . $result['msg'] . '</p>';
                            }
                        } else {
                            if ($payment['paid_by'] == 'gift_card') {
                                $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                                unset($payment['gc_balance']);
                            } elseif ($payment['paid_by'] == 'deposit') {
								$this->sysnceCustomerDeposit($data['customer_id']);
                            }
                            unset($payment['cc_cvv2']);
                            $this->db->insert('payments', $payment);

                            $paid += $payment['amount'];
                        }
						
						$payment_id = $this->db->insert_id();
						if(isset($accTranPayments[$index]) && $accTranPayments[$index]){
							foreach($accTranPayments[$index] as $accTranPayment){
								$accTranPayment['transaction_id'] = $payment_id;
								$accTranPayment['reference'] = $pay_reference_no;
								$this->db->insert('acc_tran', $accTranPayment);
							}
						}
                    }
                }
                $this->site->syncSalePayments($sale_id);
            }

            foreach($stockmoves as $stockmove){
				if ($stockmove['product_type'] != 'combo') {      
                    $stockmove['transaction_id'] = $sale_id;
					$this->db->insert('stockmoves', $stockmove);
                }
			}
            if ($sid) {
				if($this->pos_settings->table_enable == 1){
					$this->db->where("id",$sid)->update("suspended_bills",array("sale_id" => $sale_id));
					$this->db->where("suspend_id",$sid)->update("bills",array("sale_id" => $sale_id));
					$this->deleteBillItems($sid, $items);					
				}else{
					$this->deleteBill($sid);
				}
            }
            $this->cus->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            return array('sale_id' => $sale_id, 'message' => $msg);
        }

        return false;
    }

    public function getProductByCode($code = false)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductByName($name = false)
    {
        $q = $this->db->get_where('products', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getAllBillerCompanies()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'biller'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getAllCustomerCompanies()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'customer'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCompanyByID($id = false)
    {

        $q = $this->db->get_where('companies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getAllProducts()
    {
        $q = $this->db->query('SELECT * FROM products ORDER BY id');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getProductByID($id = false)
    {

        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getAllTaxRates()
    {
        $q = $this->db->get('tax_rates');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getTaxRateByID($id = false)
    {

        $q = $this->db->get_where('tax_rates', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function updateProductQuantity($product_id = false, $warehouse_id = false, $quantity = false)
    {

        if ($this->addQuantity($product_id, $warehouse_id, $quantity)) {
            return true;
        }

        return false;
    }

    public function addQuantity($product_id = false, $warehouse_id = false, $quantity = false)
    {
        if ($warehouse_quantity = $this->getProductQuantity($product_id, $warehouse_id)) {
            $new_quantity = $warehouse_quantity['quantity'] - $quantity;
            if ($this->updateQuantity($product_id, $warehouse_id, $new_quantity)) {
                $this->site->syncProductQty($product_id, $warehouse_id);
                return TRUE;
            }
        } else {
            if ($this->insertQuantity($product_id, $warehouse_id, -$quantity)) {
                $this->site->syncProductQty($product_id, $warehouse_id);
                return TRUE;
            }
        }
        return FALSE;
    }

    public function insertQuantity($product_id = false, $warehouse_id = false, $quantity = false)
    {
        if ($this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
            return true;
        }
        return false;
    }

    public function updateQuantity($product_id = false, $warehouse_id = false, $quantity = false)
    {
        if ($this->db->update('warehouses_products', array('quantity' => $quantity), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
            return true;
        }
        return false;
    }

    public function getProductQuantity($product_id = false, $warehouse = false)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return FALSE;
    }
	public function getProductSerialQuantity($product_id = false, $warehouse_id = false)
    {
		$this->db->select('count('.$this->db->dbprefix("product_serials").'.product_id) AS serial_qty')
		->group_by('product_serials.product_id');
		$q = $this->db->get_where('product_serials', array('product_id' => $product_id,'warehouse_id' => $warehouse_id,'inactive' => 0),1);
		if ($q->num_rows() > 0) {
			return $q->row_array();
		}
        return FALSE;
    }

    public function getItemByID($id = false)
    {
        $q = $this->db->get_where('sale_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllSales()
    {
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function sales_count()
    {
        return $this->db->count_all("sales");
    }

    public function fetch_sales($limit = false, $start = false)
    {
        $this->db->limit($limit, $start);
        $this->db->order_by("id", "desc");
        $query = $this->db->get("sales");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	public function getAllInvoiceItemsGroup($sale_id = false)
    {
        if ($this->pos_settings->item_order == 0) {
            $this->db->select('sale_items.id,
					sale_items.sale_id,
					sale_items.product_id,
					sale_items.product_code,
					sale_items.product_name,
					sale_items.product_type,
					sale_items.option_id,
					sale_items.net_unit_price,
					sale_items.unit_price,
					sale_items.cost,
					sum('.$this->db->dbprefix('sale_items').'.quantity) AS quantity,
					sale_items.warehouse_id,
					sum('.$this->db->dbprefix('sale_items').'.item_tax) AS item_tax,
					sale_items.tax_rate_id,
					sale_items.tax,
					sale_items.discount,
					sum('.$this->db->dbprefix('sale_items').'.item_discount) AS item_discount,
					sum('.$this->db->dbprefix('sale_items').'.subtotal) AS subtotal,
					sale_items.serial_no,
					sale_items.real_unit_price,
					sale_items.sale_item_id,
					sale_items.product_unit_id,
					sale_items.product_unit_code,
                    sale_items.bom_type,
					sum('.$this->db->dbprefix('sale_items').'.unit_quantity) AS unit_quantity,
					sale_items.`comment`,
					sale_items.item_note,
					sale_items.parent_id,
					sale_items.currency_rate,
					sale_items.currency_code,
					sale_items.pro_additionals,
					sale_items.extract_product,
					sale_items.raw_materials,
					units.name,
					sum('.$this->db->dbprefix('sale_items').'.return_quantity) AS return_quantity,
					tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, product_variants.name as variant, products.details as details')
			->join('units','sale_items.product_unit_id=units.id','left')		
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->group_by('sale_items.product_id,sale_items.product_unit_code,sale_items.net_unit_price,sale_items.option_id,sale_items.square,sale_items.bom_type, (IF('.$this->db->dbprefix('sale_items').'.quantity>0,"sale","return"))')
            ->order_by('id', 'asc');
        } elseif ($this->pos_settings->item_order == 1) {
            $this->db->select('sale_items.id,
					sale_items.sale_id,
					sale_items.product_id,
					sale_items.product_code,
					sale_items.product_name,
					sale_items.product_type,
					sale_items.option_id,
					sale_items.net_unit_price,
					sale_items.unit_price,
					sale_items.cost,
					sum('.$this->db->dbprefix('sale_items').'.quantity) AS quantity,
					sale_items.warehouse_id,
					sum('.$this->db->dbprefix('sale_items').'.item_tax) AS item_tax,
					sale_items.tax_rate_id,
					sale_items.tax,
					sale_items.discount,
					sum('.$this->db->dbprefix('sale_items').'.item_discount) AS item_discount,
					sum('.$this->db->dbprefix('sale_items').'.subtotal) AS subtotal,
					sale_items.serial_no,
					sale_items.real_unit_price,
					sale_items.sale_item_id,
					sale_items.product_unit_id,
					sale_items.product_unit_code,
                    sale_items.bom_type,
					sum('.$this->db->dbprefix('sale_items').'.unit_quantity) AS unit_quantity,
					sale_items.`comment`,
					sale_items.item_note,
					sale_items.parent_id,
					sale_items.currency_rate,
					sale_items.pro_additionals,
					sale_items.extract_product,
					sale_items.raw_materials,
					units.name,
					tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, product_variants.name as variant, categories.id as category_id, categories.name as category_name, products.details as details')
			->join('units','sale_items.product_unit_id=units.id','left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('sale_items.product_id,sale_items.product_unit_code,sale_items.net_unit_price,sale_items.option_id,sale_items.square,sale_items.bom_type, (IF('.$this->db->dbprefix('sale_items').'.quantity>0,"sale","return"))')
            ->order_by('categories.id', 'asc');
        }
        
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllInvoiceItems($sale_id = false)
    {
        if ($this->pos_settings->item_order == 0) {
            $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, product_variants.name as variant, products.details as details')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');
        } elseif ($this->pos_settings->item_order == 1) {
            $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, product_variants.name as variant, categories.id as category_id, categories.name as category_name, products.details as details')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('categories.id', 'asc');
        }
        
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	// Update check lost table items
	public function getSuspendedSaleItemsByTableID($table_id = false)
    {
        $q = $this->db->select('suspended_items.*')
					  ->from('suspended_bills')
					  ->join('suspended_items','suspended_items.suspend_id=suspended_bills.id','inner')
					  ->where('suspended_bills.table_id', $table_id)
		              ->order_by('product_code','asc')
					  ->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
    public function getSuspendedSaleItems($id = false)
    {
        $q = $this->db->order_by('product_code','asc')->get_where('suspended_items', array('suspend_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getSuspendedSales($user_id = NULL)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('suspended_bills', array('created_by' => $user_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getOpenBillByID($id = false)
    {

        $q = $this->db->get_where('suspended_bills', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getInvoiceByID($id = false)
    {

        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function bills_count()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        return $this->db->count_all_results("suspended_bills");
    }

    public function fetch_bills($limit = false, $start = false)
    {
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->limit($limit, $start);
        $this->db->order_by("id", "desc");
        $query = $this->db->get("suspended_bills");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
		
	public function salemans_count()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        return $this->db->count_all_results("users");
    }
	
	public function fetch_salemans($limit = false, $start = false)
    {
        if (!$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
		$this->db->select("users.id, first_name, last_name");
        $this->db->limit($limit, $start);
        $this->db->order_by("users.id", "asc");
        $query = $this->db->join('groups','users.group_id=groups.id','left')->get("users");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    

    public function getCosting()
    {
        $date = date('Y-m-d');
        $this->db->select('SUM( COALESCE( purchase_unit_cost, 0 ) * quantity ) AS cost, SUM( COALESCE( sale_unit_price, 0 ) * quantity ) AS sales, SUM( COALESCE( purchase_net_unit_cost, 0 ) * quantity ) AS net_cost, SUM( COALESCE( sale_net_unit_price, 0 ) * quantity ) AS net_sales', FALSE)
            ->where('date', $date);

        $q = $this->db->get('costing');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
	
	function getCreditLimit($customer_id = false, $credit_day = false){
        $where = "";
        if($credit_day){
            $where = " AND DATE(cus_sales.date) <= '".date('Y-m-d')."' - INTERVAL ".$credit_day." DAY";
        }
		$q = $this->db->query("SELECT
									SUM(ROUND((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.")) as balance
								FROM
									cus_sales
								LEFT JOIN (
									SELECT
										sale_id,
										IFNULL(sum(amount), 0) AS paid,
										IFNULL(sum(discount), 0) AS discount
									FROM
										cus_payments
									GROUP BY
										sale_id
								) AS cus_payments ON cus_payments.sale_id = cus_sales.id
								LEFT JOIN (
									SELECT
										sum(abs(grand_total)) AS total_return,
										sum(paid) AS total_return_paid,
										sale_id
									FROM
										cus_sales
									WHERE
										cus_sales.sale_id > 0
									AND cus_sales.sale_status = 'returned'
									GROUP BY
										cus_sales.sale_id
								) AS cus_return ON cus_return.sale_id = cus_sales.id
								WHERE
									customer_id = '".$customer_id."'
								AND (cus_sales.sale_id IS NULL OR cus_sales.sale_id = 0)
								".$where."
								");
			if($q->num_rows() > 0){
				return $q->row();
			}
			return false;

	}
	
	public function getTodaySales($access_biller = false)
    {
        $sdate = date('Y-m-d 00:00:00');
		$pdate = date('Y-m-d 23:59:59');
        $where = "";
		if($access_biller > 0){
			$where = 'AND cus_sales.biller_id = "'.$access_biller.'"';
		}
		$payment = '(SELECT sum(amount) as amount,sale_id FROM '.$this->db->dbprefix("payments").' GROUP BY sale_id)';
		$q = $this->db->query('
							SELECT
								SUM(COALESCE(grand_total, 0)) AS total,
								SUM(COALESCE(amount, 0)) AS paid,
								SUM(
									COALESCE (
										sale_items.total_quantity,
										0
									)
								) AS total_quantity,
								sum(total_discount) AS total_discount
							FROM
								`cus_sales`
							LEFT JOIN (
								SELECT
									sale_id,
									sum(quantity) AS total_quantity
								FROM
									cus_sale_items
								WHERE
									quantity > 0
								GROUP BY
									sale_id
							) AS sale_items ON `sale_items`.`sale_id` = `cus_sales`.`id`
							LEFT JOIN '.$payment.' as cus_payments ON `cus_sales`.`id` = `cus_payments`.`sale_id`
							WHERE
								cus_sales.date >= "'.$sdate.'"
							AND cus_sales.sale_id IS NULL	
							'.$where.'	
						');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }	
   
	public function getTodayCCSales($access_biller = false)
    {
		$date = date('Y-m-d 00:00:00');
		if($access_biller > 0){
			$this->db->where('sales.biller_id',$access_biller);
		}	
        
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cc_slips, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
			->join('sales','sales.id = payments.sale_id','inner')
            ->where('payments.type', 'received')->where('payments.date >=', $date)->where('paid_by', 'CC');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
	public function getTodayPPPSales($access_biller = false)
    {
		$date = date('Y-m-d 00:00:00');
		if($access_biller > 0){
			$this->db->where('sales.biller_id',$access_biller);
		}	
        
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques,SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
			->join('sales','sales.id = payments.sale_id','inner')
            ->where('payments.type', 'received')->where('payments.date >=', $date)->where('paid_by', 'ppp');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
    public function getTodayCashSales($access_biller = false)
    {
		$date = date('Y-m-d 00:00:00');
		if($access_biller > 0){
			$this->db->where('sales.biller_id',$access_biller);
		}	
        
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
			->join('sales','sales.id = payments.sale_id','inner')
            ->where('payments.type', 'received')->where('payments.date >=', $date)->where('paid_by', 'cash');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayRefunds($access_biller = false)
    {
		if($access_biller > 0){
			$this->db->where('sales.biller_id',$access_biller);
		}
		$payment = '(SELECT sum(amount) as amount,sale_id FROM '.$this->db->dbprefix("payments").' GROUP BY sale_id)';
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', FALSE)
            ->join(''.$payment.' as payments', 'sales.id=payments.sale_id', 'left')
            ->where('sales.sale_id >', 0)->where('sales.date >=', $date);

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
	public function getTodaySaleReturnItems($access_biller = false)
	{
		if($access_biller > 0){
			$this->db->where('sales.biller_id',$access_biller);
		}
		$date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( subtotal, 0 ) ) AS total', FALSE)
            ->join('(SELECT sale_id, sum(subtotal) as subtotal FROM ' . $this->db->dbprefix('sale_items') . ' WHERE quantity < 0 GROUP BY sale_id) as sale_items', 'sales.id=sale_items.sale_id', 'inner');
        $this->db->where('sales.sale_status !=', 'returned');
		$this->db->where('sales.date >=', $date);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}

    public function getTodayExpenses($access_biller = false)
    {
		if($access_biller > 0){
			$this->db->where('expenses.biller_id',$access_biller);
		}
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', FALSE)
            ->where('date >=', $date);

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayChSales($access_biller = false)
    {
		if($access_biller > 0){
			$this->db->where('sales.biller_id',$access_biller);
		}
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('payments', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('sales.date >=', $date)->where('paid_by', 'Cheque');

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayStripeSales($access_biller = NULL)
    {
		if($access_biller > 0){
			$this->db->where('sales.biller_id',$access_biller);
		}
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('payments', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('sales.date >=', $date)->where('paid_by', 'stripe');

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayAuthorizeSales($access_biller = false)
    {
		if($access_biller > 0){
			$this->db->where('sales.biller_id',$access_biller);
		}
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('payments', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('sales.date >=', $date)->where('paid_by', 'authorize');

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
	public function getRegisterSaleItems($date = NULL, $user_id = NULL, $closed_date = NULL)
    {
		
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
		if($closed_date){
			$this->db->where('sales.date <', $closed_date);
		}
        $this->db->select('sale_items.product_id,sale_items.product_code,sale_items.product_name,sum('.$this->db->dbprefix("sale_items").'.quantity) as quantity', FALSE)
            ->join('sale_items','sale_items.sale_id = sales.id','inner')
            ->where('sales.date >', $date)
			->group_by('sale_items.product_id');
        $this->db->where('sales.created_by', $user_id)->where('sales.sale_id IS NULL');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getRegisterSales($date = NULL, $user_id = NULL, $closed_date = NULL)
    {
		
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
		if($closed_date){
			$this->db->where('sales.date <', $closed_date);
		}
		
		$payment = '(SELECT sum(amount) as amount,sale_id FROM '.$this->db->dbprefix("payments").' WHERE type="received" GROUP BY sale_id)';
		
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join(''.$payment.' as payments', 'sales.id=payments.sale_id', 'left')
            ->where('sales.date >', $date);
        $this->db->where('sales.created_by', $user_id)->where('sales.sale_id IS NULL');

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
	public function getRegisterSaleDiscounts($date =  NULL, $user_id = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( total_discount, 0 ) ) AS total_discount', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date);
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
	public function getCountMoney($date = NULL, $user_id = NULL, $register_close_time = NULL){
		
		if($date){
			$this->db->where('count_money.counted_at >', $date);
		}
		if($register_close_time){
			$this->db->where('count_money.counted_at <', $register_close_time);
		}
		if($user_id){
			$this->db->where('count_money.user_id', $user_id);
		}
		
		$this->db->select('sum('.$this->db->dbprefix("count_money").'.total_amount) as total_amount,sum('.$this->db->dbprefix("count_money").'.total_money_kh) as total_amount_kh,sum('.$this->db->dbprefix("count_money").'.total_money_us) as total_amount_us');
		$this->db->group_by('count_money.user_id');
		$q = $this->db->get('count_money');
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}


    public function getRegisterCCSales($date = NULL, $user_id = NULL, $closed_date = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
		if($closed_date){
			$this->db->where('payments.date <', $closed_date);
		}
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cc_slips, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'CC');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterCashSales($date = NULL, $user_id = NULL, $closed_date = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
		if($closed_date){
			$this->db->where('payments.date <', $closed_date);
		}
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'inner')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'cash');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
	
	public function getSaleReturnItems($date = NULL, $user_id = NULL){
		if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( subtotal, 0 ) ) AS total', FALSE)
            ->join('(SELECT sale_id, sum(subtotal) as subtotal FROM ' . $this->db->dbprefix('sale_items') . ' WHERE quantity < 0 GROUP BY sale_id) as sale_items', 'sales.id=sale_items.sale_id', 'inner');
        $this->db->where('sales.sale_status !=', 'returned');
		$this->db->where('sales.date >', $date);
        $this->db->where('sales.created_by', $user_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
    public function getRegisterRefunds($date = NULL, $user_id = NULL, $register_close_time = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
		if($register_close_time){
			$this->db->where('sales.date < ', $register_close_time);
		}
		$payment = '(SELECT sum(amount) as amount,sale_id,type FROM '.$this->db->dbprefix("payments").' GROUP BY sale_id)';
        
		$this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', FALSE)
            ->join(''.$payment.' as payments', 'sales.id=payments.sale_id  AND payments.type="returned"', 'left')
            ->where('sale_status', 'returned')->where('sales.date >', $date);
        $this->db->where('sales.created_by', $user_id);

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

   

    public function getRegisterExpenses($date = NULL, $user_id = NULL, $register_close_time = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
		if($register_close_time){
			$this->db->where('expenses.date < ', $register_close_time);
		}
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', FALSE)
            ->where('date >', $date);
        $this->db->where('created_by', $user_id);

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterChSales($date = NULL, $user_id = NULL, $closed_date = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
		if($closed_date){
			$this->db->where('payments.date <', $closed_date);
		}
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'Cheque');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	

    public function getRegisterGCSales($date = NULL, $user_id = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'gift_card');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }	

    public function getRegisterPPPSales($date= NULL, $user_id = NULL, $register_close_time  = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
		if($register_close_time){
			$this->db->where('payments.date <', $register_close_time);
		}
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'ppp');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterStripeSales($date = NULL, $user_id = NULL, $register_close_time = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
		if($register_close_time){
			$this->db->where('payments.date <', $register_close_time);
		}
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'stripe');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterAuthorizeSales($date = NULL, $user_id = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'authorize');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function suspendSale($data = array(), $items = array(), $did = NULL, $merge_id = NULL)
    {
		$sitems = array();
		if($items){
			foreach($items as $item){
				$sitems[] = array(
					'suspend_item_id'	=> $item['suspend_item_id'],
					'customer_stock_id' => (isset($item['customer_stock_id']) ? $item['customer_stock_id'] : null),
					'product_id' 		=> $item['product_id'],
					'product_code' 		=> $item['product_code'],
					'product_name' 		=> $item['product_name'],
					'net_unit_price' 	=> $item['net_unit_price'],
					'unit_price' 		=> $item['unit_price'],
					'quantity' 			=> $item['quantity'],
					'warehouse_id' 		=> $item['warehouse_id'],
					'item_tax'			=> $item['item_tax'],
					'tax_rate_id' 		=> $item['tax_rate_id'],
					'tax' 				=> $item['tax'],
					'discount' 			=> $item['discount'],
					'item_discount' 	=> $item['item_discount'],
					'subtotal' 			=> $item['subtotal'],
					'serial_no' 		=> $item['serial_no'],
					'option_id' 		=> $item['option_id'],
					'product_type' 		=> $item['product_type'],
					'real_unit_price' 	=> $item['real_unit_price'],
					'product_unit_id' 	=> $item['product_unit_id'],
					'product_unit_code' => $item['product_unit_code'],
					'unit_quantity' 	=> $item['unit_quantity'],
					'comment' 			=> $item['comment'],
					'item_note' 		=> (isset($item['item_note']) ? $item['item_note'] : null),
					'ordered' 			=> $item['ordered'],
					'cost' 				=> $item['cost'],
					'ordered_by' 		=> $item['ordered_by'],
					'return_quantity' 	=> $item['return_quantity'],
					'item_costs' 		=> $item['item_costs'],
					'price_group' 		=> (isset($item['price_group']) ? $item['price_group'] : null),
					'price_group_id' 	=> (isset($item['price_group_id']) ? $item['price_group_id'] : null),
					'raw_materials' 	=> (isset($item['raw_materials']) ? $item['raw_materials'] : null),
					'height' 			=> (isset($item['height']) ? $item['height'] : null),
					'width' 			=> (isset($item['width']) ? $item['width'] : null),
					'square' 			=> (isset($item['square']) ? $item['square'] : null),
					'square_qty' 		=> (isset($item['square_qty']) ? $item['square_qty'] : null),
					'extract_product' 	=> (isset($item['extract_product']) ? $item['extract_product'] : null),
					'extract_cost' 		=> (isset($item['extract_cost']) ? $item['extract_cost'] : null),
					'expiry' 			=> (isset($item['expiry']) ? $item['expiry'] : null),
					'parent_id' 		=> (isset($item['parent_id']) ? $item['parent_id'] : null),
					'return_stock' 		=> (isset($item['return_stock']) ? $item['return_stock'] : null),
					'bom_type' 			=> (isset($item['bom_type']) ? $item['bom_type'] : null),
					'pro_additionals'   => (isset($item['pro_additionals']) ? $item['pro_additionals'] : null),
					'combo_product'   	=> (isset($item['combo_product']) ? $item['combo_product'] : null),
				);
			}
		}
		
        $sData = array(
            'count' 		=> $data['total_items'],
            'biller_id' 	=> $data['biller_id'],
            'customer_id' 	=> $data['customer_id'],
            'warehouse_id' 	=> $data['warehouse_id'],
            'customer' 		=> $data['customer'],
            'suspend_note' 	=> $data['suspend_note'],
			'table_id' 		=> $data['table_id'],
			'table_name' 	=> $data['table_name'],
            'total' 		=> $data['grand_total'],
            'order_tax_id' 	=> $data['order_tax_id'],
            'order_discount_id' => $data['order_discount_id'],
            'created_by' 	=> $this->session->userdata('user_id'),
			'vehicle_model' => (isset($data['vehicle_model']) ? $data['vehicle_model'] : null),
			'vehicle_kilometers' => (isset($data['vehicle_kilometers']) ? $data['vehicle_kilometers'] : null),
			'vehicle_vin_no' => (isset($data['vehicle_vin_no']) ? $data['vehicle_vin_no'] : null),
			'vehicle_plate' => (isset($data['vehicle_plate']) ? $data['vehicle_plate'] : null),
			'job_number' => (isset($data['job_number']) ? $data['job_number'] : null),
			'mechanic' => (isset($data['mechanic']) ? $data['mechanic'] : null),
			
        );
		if ($did) {
			if($this->pos_settings->table_enable){
				if($this->db->update('suspended_bills', $sData, array('id' => $did))){
					foreach($sitems as $item){
						$sid = $item['suspend_item_id'];
						$item['suspend_id'] = $did;
						if($sid > 0){
							unset($item['suspend_item_id']);
							$this->db->update('suspended_items', $item, array("id" => $sid));
						}else{
							unset($item['suspend_item_id']);
							$this->db->insert('suspended_items', $item);
						}
					}
					return $did;
				}
			}else{
				if ($this->db->update('suspended_bills', $sData, array('id' => $did)) && $this->db->delete('suspended_items', array('suspend_id' => $did))) {
					$addOn = array('suspend_id' => $did);
					end($addOn);
					foreach ($sitems as &$var) {
						$var = array_merge($addOn, $var);
						$var['suspend_id'] = $did;
						unset($var['suspend_item_id']);					
					}
					if ($this->db->insert_batch('suspended_items', $sitems)) {
						return $did;
					}
				} 
			}
        } else {
			$sData['date'] = $data['date'];
            if ($this->db->insert('suspended_bills', $sData)) {
                $suspend_id = $this->db->insert_id();
                $addOn = array('suspend_id' => $suspend_id);
                end($addOn);
                foreach ($sitems as &$var) {
                    $var = array_merge($addOn, $var);
					$var['suspend_id'] = $suspend_id;
					unset($var['suspend_item_id']);
                }
                if ($this->db->insert_batch('suspended_items', $sitems)) {
                    return $suspend_id;
                }
            }
        }
        return FALSE;
    }
	
	public function suspendMergeSale($data = array(), $items = array(), $did = NULL,  $bid = NULL, $merge_id = NULL)
    {
		if($items){
			foreach($items as $item){
				$sitems[] = array(
					'suspend_item_id'	=> $item['suspend_item_id'],
					//TODO Rothana
					//'suspend_id' 		=> $item['suspend_id'],
					'customer_stock_id' => $item['customer_stock_id'],
					'product_id' 		=> $item['product_id'],
					'product_code' 		=> $item['product_code'],
					'product_name' 		=> $item['product_name'],
					'net_unit_price' 	=> $item['net_unit_price'],
					'unit_price' 		=> $item['unit_price'],
					'quantity' 			=> $item['quantity'],
					'warehouse_id' 		=> $item['warehouse_id'],
					'item_tax'			=> $item['item_tax'],
					'tax_rate_id' 		=> $item['tax_rate_id'],
					'tax' 				=> $item['tax'],
					'discount' 			=> $item['discount'],
					'item_discount' 	=> $item['item_discount'],
					'subtotal' 			=> $item['subtotal'],
					'serial_no' 		=> $item['serial_no'],
					'option_id' 		=> $item['option_id'],
					'product_type' 		=> $item['product_type'],
					'real_unit_price' 	=> $item['real_unit_price'],
					'product_unit_id' 	=> $item['product_unit_id'],
					'product_unit_code' => $item['product_unit_code'],
					'unit_quantity' 	=> $item['unit_quantity'],
					'comment' 			=> $item['comment'],
					'item_note' 		=> $item['item_note'],
					'ordered' 			=> $item['ordered'],
					'cost' 				=> $item['cost'],
					'ordered_by' 		=> $item['ordered_by'],
					'return_quantity' 	=> $item['return_quantity'],
					'item_costs' 		=> $item['item_costs'],
					'price_group' 		=> $item['price_group'],
					'price_group_id' 	=> $item['price_group_id'],
					'raw_materials' 	=> $item['raw_materials'],
					'height' 			=> $item['height'],
					'width' 			=> $item['width'],
					'square' 			=> $item['square'],
					'square_qty' 		=> $item['square_qty'],
					'extract_product' 	=> $item['extract_product'],
					'extract_cost' 		=> $item['extract_cost'],
					'expiry' 			=> $item['expiry'],
					'parent_id' 		=> $item['parent_id'],
					'return_stock' 		=> $item['return_stock'],
					'bom_type' 			=> $item['bom_type'],
				);
			}
		}
		$bill = $this->getSuspendByID($bid);
		if($bill){
			$data['grand_total'] += $bill->total;
			$data['total_items'] += $bill->count;
		}
        $sData = array(
            'count' 			=> $data['total_items'],
            'biller_id' 		=> $data['biller_id'],
            'customer_id' 		=> $data['customer_id'],
            'warehouse_id' 		=> $data['warehouse_id'],
            'customer' 			=> $data['customer'],
            'date' 				=> $data['date'],
            'suspend_note' 		=> $data['suspend_note'],
			'table_id' 			=> $data['table_id'],
			'table_name' 		=> $data['table_name'],
            'total' 			=> $data['grand_total'],
            'order_tax_id' 		=> $data['order_tax_id'],
            'order_discount_id' => $data['order_discount_id'],
            'created_by' 		=> $this->session->userdata('user_id')
        );
		if ($bid) {
			if($this->db->delete('suspended_bills', array('id' => $did))){
			   $this->db->delete('suspended_items', array('suspend_id' => $did));
			}
            if ($this->db->update('suspended_bills', $sData, array('id' => $bid))) {
                $addOn = array('suspend_id' => $bid);
                end($addOn);
                foreach ($sitems as &$var) {
                    $var = array_merge($addOn, $var);
					unset($var['suspend_item_id']);					
                }
                if ($this->db->insert_batch('suspended_items', $sitems)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function deleteBill($id = false)
    {
        if ($this->db->delete('suspended_items', array('suspend_id' => $id)) && $this->db->delete('suspended_bills', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getInvoicePayments($sale_id = false)
    {
		$this->db->select('payments.*, IFNULL('.$this->db->dbprefix('cash_accounts').'.name,'.$this->db->dbprefix('payments').'.paid_by) as paid_by', FALSE);
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where("payments", array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }

        return FALSE;
    }

    public function stripe($amount = 0, $card_info = array(), $desc = '')
    {
        $this->load->model('stripe_payments');
        //$card_info = array( "number" => "4242424242424242", "exp_month" => 1, "exp_year" => 2016, "cvc" => "314" );
        //$amount = $amount ? $amount*100 : 3000;
        unset($card_info['type']);
        $amount = $amount * 100;
        if ($amount && !empty($card_info)) {
            $token_info = $this->stripe_payments->create_card_token($card_info);
            if (!isset($token_info['error'])) {
                $token = $token_info->id;
                $data = $this->stripe_payments->insert($token, $desc, $amount, $this->default_currency->code);
                if (!isset($data['error'])) {
                    $result = array('transaction_id' => $data->id,
                        'created_at' => date($this->dateFormats['php_ldate'], $data->created),
                        'amount' => ($data->amount / 100),
                        'currency' => strtoupper($data->currency)
                    );
                    return $result;
                } else {
                    return $data;
                }
            } else {
                return $token_info;
            }
        }
        return false;
    }

    public function paypal($amount = NULL, $card_info = array(), $desc = '')
    {
        $this->load->model('paypal_payments');
        //$card_info = array( "number" => "5522340006063638", "exp_month" => 2, "exp_year" => 2016, "cvc" => "456", 'type' => 'MasterCard' );
        //$amount = $amount ? $amount : 30.00;
        if ($amount && !empty($card_info)) {
            $data = $this->paypal_payments->Do_direct_payment($amount, $this->default_currency->code, $card_info, $desc);
            if (!isset($data['error'])) {
                $result = array('transaction_id' => $data['TRANSACTIONID'],
                    'created_at' => date($this->dateFormats['php_ldate'], strtotime($data['TIMESTAMP'])),
                    'amount' => $data['AMT'],
                    'currency' => strtoupper($data['CURRENCYCODE'])
                );
                return $result;
            } else {
                return $data;
            }
        }
        return false;
    }

    public function authorize($authorize_data = false)
    {
        $this->load->library('authorize_net');
        // $authorize_data = array( 'x_card_num' => '4111111111111111', 'x_exp_date' => '12/20', 'x_card_code' => '123', 'x_amount' => '25', 'x_invoice_num' => '15454', 'x_description' => 'References');
        $this->authorize_net->setData($authorize_data);

        if( $this->authorize_net->authorizeAndCapture() ) {
            $result = array(
                'transaction_id' => $this->authorize_net->getTransactionId(),
                'approval_code' => $this->authorize_net->getApprovalCode(),
                'created_at' => date($this->dateFormats['php_ldate']),
            );
            return $result;
        } else {
            return array('error' => 1, 'msg' => $this->authorize_net->getError());
        }
    }

    public function addPayment($payment = array(), $customer_id = null)
    {
        if (isset($payment['sale_id']) && isset($payment['paid_by']) && isset($payment['amount'])) {
            $payment['pos_paid'] = $payment['amount'];
            $inv = $this->getInvoiceByID($payment['sale_id']);
            $paid = $inv->paid + $payment['amount'];
            if ($payment['paid_by'] == 'ppp') {
                $card_info = array("number" => $payment['cc_no'], "exp_month" => $payment['cc_month'], "exp_year" => $payment['cc_year'], "cvc" => $payment['cc_cvv2'], 'type' => $payment['cc_type']);
                $result = $this->paypal($payment['amount'], $card_info);
                if (!isset($result['error'])) {
                    $payment['transaction_id'] = $result['transaction_id'];
                    $payment['date'] = $this->cus->fld($result['created_at'],1);
                    $payment['amount'] = $result['amount'];
                    $payment['currency'] = $result['currency'];
                    unset($payment['cc_cvv2']);
                    $this->db->insert('payments', $payment);
                    $paid += $payment['amount'];
                } else {
                    $msg[] = lang('payment_failed');
                    if (!empty($result['message'])) {
                        foreach ($result['message'] as $m) {
                            $msg[] = '<p class="text-danger">' . $m['L_ERRORCODE'] . ': ' . $m['L_LONGMESSAGE'] . '</p>';
                        }
                    } else {
                        $msg[] = lang('paypal_empty_error');
                    }
                }
            } elseif ($payment['paid_by'] == 'stripe') {
                $card_info = array("number" => $payment['cc_no'], "exp_month" => $payment['cc_month'], "exp_year" => $payment['cc_year'], "cvc" => $payment['cc_cvv2'], 'type' => $payment['cc_type']);
                $result = $this->stripe($payment['amount'], $card_info);
                if (!isset($result['error'])) {
                    $payment['transaction_id'] = $result['transaction_id'];
                    $payment['date'] = $this->cus->fld($result['created_at'],1);
                    $payment['amount'] = $result['amount'];
                    $payment['currency'] = $result['currency'];
                    unset($payment['cc_cvv2']);
                    $this->db->insert('payments', $payment);
                    $paid += $payment['amount'];
                } else {
                    $msg[] = lang('payment_failed');
                    $msg[] = '<p class="text-danger">' . $result['code'] . ': ' . $result['message'] . '</p>';
                }

            } elseif ($payment['paid_by'] == 'authorize') {
                $authorize_arr = array("x_card_num" => $payment['cc_no'], "x_exp_date" => ($payment['cc_month'].'/'.$payment['cc_year']), "x_card_code" => $payment['cc_cvv2'], 'x_amount' => $payment['amount'], 'x_invoice_num' => $inv->id, 'x_description' => 'Sale Ref '.$inv->reference_no.' and Payment Ref '.$payment['reference_no']);
                list($first_name, $last_name) = explode(' ', $payment['cc_holder'], 2);
                $authorize_arr['x_first_name'] = $first_name;
                $authorize_arr['x_last_name'] = $last_name;
                $result = $this->authorize($authorize_arr);
                if (!isset($result['error'])) {
                    $payment['transaction_id'] = $result['transaction_id'];
                    $payment['approval_code'] = $result['approval_code'];
                    $payment['date'] = $this->cus->fld($result['created_at'],1);
                    unset($payment['cc_cvv2']);
                    $this->db->insert('payments', $payment);
                    $paid += $payment['amount'];
                } else {
                    $msg[] = lang('payment_failed');
                    $msg[] = '<p class="text-danger">' . $result['msg'] . '</p>';
                }

            } else {
                if ($payment['paid_by'] == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($payment['cc_no']);
                    $this->db->update('gift_cards', array('balance' => ($gc->balance - $payment['amount'])), array('card_no' => $payment['cc_no']));
                } elseif ($customer_id && $payment['paid_by'] == 'deposit') {
					$this->sysnceCustomerDeposit($customer_id);
                }
                unset($payment['cc_cvv2']);
                $this->db->insert('payments', $payment);
                $paid += $payment['amount'];
            }
            if (!isset($msg)) {
                $this->site->syncSalePayments($payment['sale_id']);
                return array('status' => 1, 'msg' => '');
            }
            return array('status' => 0, 'msg' => $msg);

        }
        return false;
    }

    public function addPrinter($data = array()) 
	{
        if($this->db->insert('printers', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    public function updatePrinter($id = false, $data = array()) 
	{
        if($this->db->update('printers', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
    
    public function deletePrinter($id = false) 
	{
        if($this->db->delete('printers', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getPrinterByID($id = false) 
	{
        $q = $this->db->get_where('printers', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllPrinters() 
	{
        $q = $this->db->get('printers');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }	
	
	/***============SUSPEND BILLS==============***/
	
	public function deleteBillItems($id = NULL, $items = array(), $suspend_item_id = NULL)
    {	
		$row = $this->getSuspendByID($id);
		$table = $this->getTableById($row->table_id);
		if($items){
			foreach($items as $item){
				$this->db->delete('suspended_items', array('id' => $item['suspend_item_id'], 'product_id' => $item['product_id']));
			}
			$this->synSuspendBills($id, $row->table_id);
			return true;			
		}
        return FALSE;
    }
	
	public function synSuspendBills($id = NULL, $table_id = NULL)
	{	
		$suspendItems = $this->db->where("suspend_id",$id)->get("suspended_items")->result();
		$total = 0; $count = 0;
		foreach($suspendItems as $item){
			$total += $item->subtotal;
			$count += 1;
		}		
		if($suspendItems){
			$data = array("total"=>$total,"count"=>$count);
			$this->db->where("id",$id)->update("suspended_bills",$data);
			return true;
		}
		if($count == 0){			
			$this->db->delete('suspended_bills', array('id' => $id));
			// Update check lost table items add table ID
			if($table_id){
				$this->db->delete('suspended_bills', array('table_id' => $table_id));
			}
		}		
		return false;
	}
	
	public function getSuspendedBills() 
	{
        $q = $this->db->get("suspended_bills");
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getSuspendedBillByTableId($id = NULL)
	{
		$q = $this->db->get_where('suspended_bills', array('table_id' => $id), 1);
        if ($q->num_rows() > 0) {            
            return $q->row();		
        }
        return FALSE;
	}
	
	public function deleteSuspendedBillByTableId($id = NULL) 
	{
		$suspend = $this->getSuspendedBillByTableId($id);		
		if($this->db->where("suspend_id",$suspend->id)->delete('suspended_items')){
			$this->db->where("table_id",$id)->delete('suspended_bills');
			$this->db->where("suspend_id", $suspend->id)->update("bills", array("suspend_status"=>"deleted"));
			return true;
		}
        return FALSE;
    }
	
	public function deleteSuspendedById($id = NULL) 
	{
		if($this->db->where("suspend_id",$id)->delete('suspended_items')){
			$this->db->where("id",$id)->delete('suspended_bills');
			return true;
		}
        return FALSE;
    }

	public function getTableById($id = NULL)
	{
		 $q = $this->db->get_where('suspended_tables', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getTableByBillId($id = NULL)
	{
		 $q = $this->db->where(array('suspended_bills.id' => $id))->join("suspended_bills","suspended_tables.id=table_id","right")->get('suspended_tables');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function updateTableById($id = NULL, $data = NULL)
	{				
		if($this->db->update('suspended_tables', $data, array('id' => $id))) {
            return true;
        }
        return false;
	}
	
	public function queueNumber()
	{
		$queue_number = ($this->getSetting()->queue_number + 1);
		if($this->getSetting()->queue_expiry == $queue_number){
			$queue_number = 1;
		}
		if($this->db->update("pos_settings",array("queue_number"=>$queue_number))){
			return true;
		}
		return FALSE;
	}
	
	public function getAllTypes() 
	{
        $q = $this->db->get('category_types');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllGroupTypes($code = NULL)
	{	
		$results = $this->db->where_in("products.code",$code)							 
							 ->join("categories","categories.id=category_id","left")
							 ->join("category_types","category_types.id=type_id","left")
							 ->group_by("type_id")
							 ->get("products")
							 ->result();
		return $results;		
	}
	
	public function updateBillPrint($suspend_id = NULL)
	{
		if($suspend_id){
			$num_row = $this->getSuspendItemBySuspendID($suspend_id);
			$ordered = $num_row > 1 ? $num_row : 1 ;
			$this->db->where("suspend_id",$suspend_id)->where('ordered', 0)->update("suspended_items",array("ordered"=>$ordered));
			return true;
		}
		return false;
	}
	
	public function addSaleSubspend($data = false)
	{
		if($this->db->insert("suspended_bills",$data)){
			$bill_id = $this->db->insert_id();
						
			$row = $this->getTableById($data['table_id']);
			if($row->product_id >= 1){				
				$product = $this->site->getProductByID($row->product_id);
				$unit = $this->site->getUnitByID($product->unit);				
				$products = array(
					'suspend_id'	  => $bill_id,
					'product_id'      => $product->id,
					'product_code'    => $product->code,
					'product_name'    => $product->name,
					'product_type'    => $product->type,
					'option_id'       => $product->unit,
					'net_unit_price'  => $product->price,
					'unit_price'      => $product->price,
					'quantity'        => 1,
					'product_unit_id' => $product->unit,
					'product_unit_code' => $unit ? $unit->code : NULL,
					'unit_quantity' => 1,
					'warehouse_id'    => $data['warehouse_id'],
					'item_tax'        => 0,
					'tax_rate_id'     => 0,
					'tax'             => 0,
					'discount'        => 0,
					'item_discount'   => 0,
					'subtotal'        => $this->cus->formatDecimal($product->price*1),
					'serial_no'       => '',
					'real_unit_price' => $product->price,					
					'cost' => $product->cost,
					'ordered_by'  	  => $this->session->userdata("user_id"),
				);
				if($this->db->insert("suspended_items",$products)){					
					$this->db->where("id",$bill_id)->update("suspended_bills", array("total"=> $product->price, "count" => 1));
				}
			}			
			return $bill_id;
		}
		return false;
	}
	
	public function getSuspendByID($id = false)
	{
		$q = $this->db->where("id",$id)->get('suspended_bills');
        if ($q->num_rows() > 0) {            
            return $q->row();
        }
        return FALSE;
	}
	
	public function getAllSuspendItemsByID($id = false)
	{		
		$q = $this->db->where("suspend_id",$id)->get('suspended_items');
        if ($q->num_rows() > 0) {            
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function addMergeSaleSubspend($data = false, $did = false, $bid = false)
	{
		$bill = $this->getSuspendByID($bid);
		if($bill){
			$data['total'] += $bill->total;
			$data['count'] += $bill->count;
		}
        $sData = array(
            'count' 			=> $data['count'],
            'biller_id' 		=> $bill->biller_id,
            'customer_id' 		=> $bill->customer_id,
            'warehouse_id' 		=> $bill->warehouse_id,
            'customer' 			=> $bill->customer,
            'date' 				=> $bill->date,
            'suspend_note' 		=> $bill->suspend_note,
			'table_id' 			=> $data['table_id'],
			'table_name' 		=> $data['table_name'],
            'total' 			=> $data['total'],
            'order_tax_id' 		=> $bill->order_tax_id,
            'order_discount_id' => $bill->order_discount_id,
            'created_by' 		=> $this->session->userdata('user_id')
        );
		if ($bid) {
            if ($this->db->update('suspended_bills', $sData, array('id' => $bid))) {				
				$this->db->delete('suspended_bills', array('id' => $did));
				$items = $this->getAllSuspendItemsByID($did);
                foreach ($items as $var) {
					$this->db->where("suspend_id",$did)->update("suspended_items", array("suspend_id"=>$bid));
                }                
            }
			return true;
        }
        return false;		
	}
	
	public function addMoveSaleSubspend($data = false, $did = false)
	{
		if($this->db->where("id",$did)->update("suspended_bills", $data)){
			return true;
		}
		return false;
	}
	
	public function addSplitSaleSubspend($data = false, $suspend_id = false)
	{
		$ids = $this->input->post('val');
		$qbill = $this->db->where_in("suspend_id", $suspend_id)->get("suspended_items");
		if($qbill->num_rows() > 0){
			foreach($qbill->result() as $qbillrow){
				$suspend_items[] = $qbillrow;
			}
		}
		$qslit = $this->db->where_in("id", $ids)->get("suspended_items");
		if($qslit->num_rows() > 0){
			foreach($qslit->result() as $qsrow){
				$split_items[] = $qsrow;
			}
		}
		if(count($split_items) == count($suspend_items)){
			$this->db->where("id",$suspend_id)->update("suspended_bills", $data);
			return true;
		}else{
			if($split_items){
				$total = 0;
				foreach($split_items as $sitem){
					$total += $sitem->subtotal;
				}
				$item = $this->getSuspendByID($suspend_id);
				$sData = array(
					'count' 			=> count($split_items),
					'biller_id' 		=> $item->biller_id,
					'customer_id' 		=> $item->customer_id,
					'warehouse_id' 		=> $item->warehouse_id,
					'customer' 			=> $item->customer,
					'date' 				=> $item->date,
					'suspend_note' 		=> $item->suspend_note,
					'table_id' 			=> $data['table_id'],
					'table_name' 		=> $data['table_name'],
					'total' 			=> $total,
					'order_tax_id' 		=> $item->order_tax_id,
					'order_discount_id' => $item->order_discount_id,
					'created_by' 		=> $this->session->userdata('user_id')
				);
				if($this->db->insert("suspended_bills", $sData)){
					$insert_id = $this->db->insert_id();
					$this->db->where_in("id", $ids)->update("suspended_items", array("suspend_id"=>$insert_id));
				}
			}
		}
		return false;
	}
	
	public function getAllFloors()
	{
		$q = $this->db->get('suspended_floors');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

    public function getFloorsByID($id = false)
    {
        $this->db->where_in('id',$id);
        $q = $this->db->get('suspended_floors');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllTablesByFloor($warehouse = false, $floor = false) 
    {
        if($warehouse){
            $this->db->where("warehouse_id", $warehouse);
        }
        if($floor){
            $this->db->where_in("floor", $floor);
        }
		$this->db->where("status","active");
        $this->db->order_by("length(name),name", "asc");
        $q = $this->db->get('suspended_tables');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getAllTablesBy($warehouse = false, $floor = false) 
	{
		if($warehouse){
			$this->db->where("warehouse_id", $warehouse);
		}
		if($floor){
			$this->db->where("floor", $floor);
		}
		$this->db->where("status","active");
		$this->db->order_by("length(name),name", "asc");
        $q = $this->db->get('suspended_tables');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function deleteSuspendItemById($id = false, $suspend_id = false)
	{
		if($this->db->where("id",$id)->delete("suspended_items")){
			
			$sbills = $this->db->where("suspend_id",$suspend_id)->get("suspended_items")->result();
			$total = 0; $count = 0;			
			foreach($sbills as $bill){
				$total += $bill->unit_price;
				$count += 1;
			}
			$data = array(
						"total" => $total,
						"count" => $count,
					);
			$this->db->where("id",$suspend_id)->update("suspended_bills", $data);
			return true;
		}
		return false;
	}
		
	public function getSaleByID($id = false)
	{
		$q = $this->db->where("id",$id)->get('sales');
        if ($q->num_rows() > 0) {            
            return $q->row();
        }
        return FALSE;
	}
	
	public function getSuspendItemBySuspendID($suspend_id = false)
	{
		$q = $this->db->where("suspend_id",$suspend_id)->group_by('ordered')->get('suspended_items');
        if ($q->num_rows() > 0) {            
            return $q->num_rows();
        }
        return 0;
	}
	
	public function getSaleItemBySaleID($sale_id = false)
	{
		$result = $this->db->where("sale_id",$sale_id)->get("sale_items")->row();
		return $result;
	}
		
	public function updateSuspendById($id = false, $data = false)
	{
		if($this->db->where("id",$id)->update("suspended_bills", $data)){
			return true;
		}
		return false;
	}
	
	public function updateSuspendItemById($id = false, $data = false)
	{
		if($this->db->where("id",$id)->update("suspended_items", $data)){
			return true;
		}
		return false;
	}
	
	public function getDeliveryBySaleID($sale_id = false)
    {
        $q = $this->db->get_where('deliveries', array('sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getProductSerialDetailsByProductId($product_id = false, $warehouse_id = false, $serial = false)
	{		
		if($warehouse_id){
			$this->db->where("warehouse_id", $warehouse_id);
		}
		if($serial){
			$this->db->where("(serial='".$serial."' OR inactive='0' OR ISNULL(inactive))");
		}else{
			$this->db->where("(inactive='0' OR ISNULL(inactive))");
		}
		$products_detail = $this->db->where("product_id",$product_id)->get("product_serials")->result();
		return $products_detail;
	}
		
	public function getProductGroupPrices($product_id = false, $category_id = false)
	{
		$q = $this->db->where("product_id",$product_id)->join('price_groups','price_groups.id=product_prices.price_group_id','left')->get('product_prices');
        if ($q->num_rows() > 0) {
			 foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;	
	}
	
	public function updatePrint($id = false, $data = array()) 
	{
        if($this->db->update('sales', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
	
	public function updatePrintBill($id = false, $data = array()) 
	{
        if($this->db->update('suspended_bills', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
	
	public function getAllSalemanBills()
	{
		$q = $this->db->group_by('saleman_id')->get_where('suspended_bills');
        if ($q->num_rows() > 0) {
			 foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;	
	}
	
	public function getSuspendItemByID($id = false)
	{
		$q = $this->db->get_where('suspended_items', array("id" => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}
	
	public function getSuspendBillByTableID($table_id = false)
	{
		$q = $this->db->get_where('suspended_bills', array("table_id" => $table_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}
	
	public function getSuspendItemsByTableID($table_id = false)
	{
		$q = $this->db->query("SELECT
					count(cc.id) AS count,
					sum(cc.subtotal) AS total
				FROM
					cus_suspended_bills AS bb
				LEFT JOIN cus_suspended_items AS cc ON bb.id = suspend_id
				WHERE
					table_id ='{$table_id}'");
					
		if($q->num_rows() > 0){
			return $q->row();
		}			
		return false;
	}
	
	public function getCategoryByID($id = false)
    {
        $q = $this->db->get_where('categories', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function addProduct($addProduct = false)
	{
		if($this->db->insert('products', $addProduct)){
            $product_id = $this->db->insert_id();
			$product_units = array('product_id'=>$product_id,
									'unit_id'=>$addProduct['unit'],
									'unit_qty'=>1
									);
			$this->db->insert('product_units', $product_units);
			if($this->Settings->accounting == 1){
				$catd = $this->getCategoryByID($addProduct['category_id']);
				if($catd){
					$product_acc = array(
										'product_id' => $product_id,
										'type' => 'standard',
										'stock_acc' => $catd->stock_acc,
										'adjustment_acc' => $catd->adjustment_acc,
										'usage_acc' => $catd->usage_acc,
										'convert_acc' => $catd->convert_acc,
										'cost_acc' => $catd->cost_acc,
										'discount_acc' => $catd->discount_acc,
										'sale_acc' => $catd->sale_acc,
										'expense_acc' => $catd->expense_acc,
										'pawn_acc' => $catd->pawn_acc,
									);
					$this->db->insert('acc_product',$product_acc);					
				}
			}
			return $product_id;
		}
		return false;
	}
	
	public function addCustomerStock($data = false, $items = false, $stockmoves = false, $accTrans = NULL, $product_serials = NULL)
	{
		if($this->db->insert("customer_stocks", $data)){
			
			if($product_serials){
				foreach($product_serials as $product_serial){
					$product_serial['adjustment_id'] = $adjustment_id;
					$this->db->insert('product_serials', $product_serial);
				}
			}
			
			if($items){
				$customer_stock_id = $this->db->insert_id();
				foreach($items as $item){
					$item['customer_stock_id'] = $customer_stock_id;
					$this->db->insert("customer_stock_items", $item);
				}
			}
			if($stockmoves){
				foreach($stockmoves as $stockmove){
					$stockmove['transaction_id'] = $customer_stock_id;
					$this->db->insert('stockmoves', $stockmove);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $customer_stock_id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateCustomerStock($id = false, $data = false, $items = false, $stockmoves = false, $accTrans = NULL, $product_serials = NULL)
	{
		if($this->db->update("customer_stocks", $data, array("id"=>$id))){
			
			if($product_serials){
				foreach($product_serials as $product_serial){
					$product_serial['adjustment_id'] = $id;
					$this->db->insert('product_serials', $product_serial);
				}
			}
			
			if($items){
				$this->db->delete("customer_stock_items", array("customer_stock_id"=>$id));
				foreach($items as $item){
					$item['customer_stock_id'] = $id;
					$this->db->insert("customer_stock_items", $item);
				}
			}
			if($stockmoves){
				$this->db->delete("stockmoves", array("transaction_id"=>$id));
				foreach($stockmoves as $stockmove){
					$stockmove['transaction_id'] = $id;
					$this->db->insert('stockmoves', $stockmove);
				}
			}
			if($accTrans){
				$this->db->delete("acc_tran", array("transaction_id"=>$id));
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
			return true;
		}
		return false;
	}
	
	public function getAllCustomerGroups()
    {
        $q = $this->db->get('customer_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function addCustomer($data = false)
	{
		if($this->db->insert("companies", $data)){
			return true;
		}
		return false;
	}
	
	public function deleteCustomerStock($id = false)
	{
		if($this->db->delete("customer_stocks", array("id"=>$id))){
			$this->db->delete("customer_stock_items", array("customer_stock_id" => $id));
			
			$this->db->delete("stockmoves", array("transaction_id" => $id, "transaction" => "CustomerStock"));
			$this->db->delete("stockmoves", array("transaction_id" => $id, "transaction" => "CustomerStockReturn"));
			
			$this->db->delete("acc_tran", array("transaction_id" => $id, "transaction" => "CustomerStock"));
			$this->db->delete("acc_tran", array("transaction_id" => $id, "transaction" => "CustomerStockReturn"));
			
			return true;
		}
		return false;
	}
	
	public function getCustomerStockByID($id = false)
	{
		$q = $this->db->select("customer_stocks.*, GROUP_CONCAT(cus_products.name) as description")
					->join("customer_stock_items","customer_stock_id=customer_stocks.id","left")
					->join("products","product_id=products.id","left")
					->group_by("customer_stocks.id")
					->where(array("customer_stocks.id"=>$id))
					->get("customer_stocks");
					
		if($q->num_rows() >0){
			return $q->row();
		}
		return false;
	}
	
	public function getCustomerStockItems($id = false)
    {
        $this->db->select('customer_stock_items.*, products.code as product_code, products.name as product_name, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=customer_stock_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=customer_stock_items.option_id', 'left')
            ->group_by('customer_stock_items.id')
            ->order_by('id', 'asc');

        $this->db->where('customer_stock_id', $id);
        $q = $this->db->get('customer_stock_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function transferCustomerStock($id = false, $data = false, $products = false)
	{
		if($this->db->update("customer_stocks",$data,array("id"=>$id))){
			if($products){
				$suspend_bill = $this->getSuspendBillByTableID($data['table_id']);
				foreach($products as $product){
					$product['suspend_id'] = $suspend_bill->id;
					$product['customer_stock_id'] = $id;
					$this->db->insert('suspended_items', $product);
				}
			}
			return true;
		}
		return false;
	}
	
	public function returnCustomerStock($id = false, $stockmoves = NULL, $accTrans = NULL)
	{
		if($this->db->update("customer_stocks",array("status" => "returned", "returned_at" => date("Y-m-d H:i") , "returned_by"=> $this->session->userdata("user_id")),array("id"=>$id))){
			
			if($stockmoves){
				foreach($stockmoves as $stockmove){
					$stockmove['transaction_id'] = $id;
					$this->db->insert('stockmoves', $stockmove);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
			
			return true;
		}
		return false;
	}
	
	public function cancelCustomerStock($id = false)
	{
		if($this->db->update("customer_stocks",array("status" => "pending"),array("id"=>$id))){
			$this->db->delete("suspended_items", array("customer_stock_id"=>$id));
			return true;
		}
		return false;
	}

	public function getAllSuspendBills()
	{
		$q = $this->db->group_by("table_id")->get("suspended_bills");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function addCustomerStockExpired()
	{
		$date = date('Y-m-d');
        $this->db->select('id')
				 ->where('expiry !=', NULL)->where('expiry !=', '0000-00-00')
				 ->where('expiry <', $date);
        $q = $this->db->get('customer_stocks');
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
				$this->db->update("customer_stocks", array("status"=>"expired"), array("id"=>$row->id));
			}
			return true;
        }
        return FALSE;
	}
	
	public function getAllCustomerStockPendings()
	{
		$date = date('Y-m-d');
        $this->db->select('COUNT(id) as alert_num')
				 ->where('expiry !=', NULL)->where('expiry !=', '0000-00-00')
				 ->where('expiry >', $date)
				 ->where('status', 'pending');
				 
        $q = $this->db->get('customer_stocks');
        if ($q->num_rows() > 0) {
			$res = $q->row();
			return $res->alert_num;
        }
        return FALSE;
	}
	
	public function categories_count()
    {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = json_decode($this->session->userdata('warehouse_id'));
			$warehouse_ids[] = '0';
            $this->db->where_in('warehouse_id', $warehouse_ids);
        }
        return $this->db->where("parent_id", 0)->count_all_results("categories");
    }

    public function fetch_categories($limit = 0, $start = 0)
    {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("categories.id",$allow_category);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = json_decode($this->session->userdata('warehouse_id'));
			$warehouse_ids[] = '0';
            $this->db->where_in('warehouse_id', $warehouse_ids);
        }
		$this->db->where("parent_id", 0);
        $this->db->limit($limit, $start);
        $this->db->order_by("name", "asc");
        $query = $this->db->get("categories");
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

	public function getCustomerPrice($product_id = false,$customer_id = false)
	{
		$q = $this->db->get_where('customer_product_prices',array('customer_id'=>$customer_id,'product_id'=>$product_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

    public function getTypeBoms($product_id = false)
	{
        $this->db->group_by('bom_type');
        $this->db->where('bom_type <>','');
        $q = $this->db->get_where('bom_products',array('standard_product_id'=>$product_id));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
	public function getSuspendBySaleID($sale_id = false)
	{
		$q = $this->db->where("sale_id", $sale_id)->get("suspended_bills");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function addBill($bill_id = false)
	{
		if($bill = $this->getSuspendByID($bill_id)){
		   unset($bill->id);
		   $bill->suspend_id = $bill_id;
		   $bill->date = date("Y-m-d H:i");
		   if($this->db->insert("bills", $bill)){
			    $insert_id = $this->db->insert_id();
			    $bill_items = $this->pos_model->getAllSuspendItemsByID($bill_id);
			    if($bill_items){
					foreach($bill_items as $item){
						$sitems = array(
							'bill_id' 			=> $insert_id,
							'product_id' 		=> $item->product_id,
							'product_code' 		=> $item->product_code,
							'product_name' 		=> $item->product_name,
							'net_unit_price' 	=> $item->net_unit_price,
							'unit_price' 		=> $item->unit_price,
							'quantity' 			=> $item->quantity,
							'warehouse_id' 		=> $item->warehouse_id,
							'item_tax'			=> $item->item_tax,
							'tax_rate_id' 		=> $item->tax_rate_id,
							'tax' 				=> $item->tax,
							'discount' 			=> $item->discount,
							'item_discount' 	=> $item->item_discount,
							'subtotal' 			=> $item->subtotal,
							'serial_no' 		=> $item->serial_no,
							'option_id' 		=> $item->option_id,
							'product_type' 		=> $item->product_type,
							'real_unit_price' 	=> $item->real_unit_price,
							'product_unit_id' 	=> $item->product_unit_id,
							'product_unit_code' => $item->product_unit_code,
							'unit_quantity' 	=> $item->unit_quantity,
							'comment' 			=> $item->comment,
							'item_note' 		=> $item->item_note,
							'ordered' 			=> $item->ordered,
							'cost' 				=> $item->cost,
							'ordered_by' 		=> $item->ordered_by,
							'return_quantity' 	=> $item->return_quantity,
							'item_costs' 		=> $item->item_costs,
							'price_group' 		=> $item->price_group,
							'price_group_id' 	=> $item->price_group_id,
							'raw_materials' 	=> $item->raw_materials,
							'height' 			=> $item->height,
							'width' 			=> $item->width,
							'square' 			=> $item->square,
							'square_qty' 		=> $item->square_qty,
							'extract_product' 	=> $item->extract_product,
							'extract_cost' 		=> $item->extract_cost,
							'expiry' 			=> $item->expiry,
							'parent_id' 		=> $item->parent_id,
							'return_stock' 		=> $item->return_stock,
							'bom_type' 			=> $item->bom_type,
						);
						$this->db->insert("bill_items", $sitems);
					}
				}
			    return true;
		   }
		}
		return false;
	}
	
	public function getCustomerByCode($code = null)
	{
		$code = trim($code);
		$q = $this->db->where("code", $code)
		              ->where("code !=", null)
					  ->get("companies");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function updatePackaging($id = false, $delivery_status = null)
	{
		if($this->db->where("id",$id)->update("sales", array("delivery_status"=>$delivery_status))){
			return true;
		}
		return false;
	}
	
	public function getProductAdditionals()
	{
		$this->db->select("products.id,products.name,products.price, products.product_additional");
		$this->db->where("products.product_additional >",0);
		$q = $this->db->get("products");
		if($q->num_rows()){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getProductAdditionalByID($id = false,$qty = false)
	{
		$q = $this->db->query("SELECT * FROM ".$this->db->dbprefix('products')." WHERE id IN (".$id.")");
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = array( 
								'for_product_id' => $row->id,
								'for_quantity' => $row->product_additional * $qty,
								'for_unit_id' => $row->unit,
							);
			}
			return $data;
		}
		return false;
	}
	
	public function getAllInvoiceItemsGroupStickers($sale_id = false)
    {
        if ($this->pos_settings->item_order == 0) {
            $this->db->select('sale_items.id,
					sale_items.sale_id,
					sale_items.product_id,
					sale_items.product_code,
					sale_items.product_name,
					sale_items.product_type,
					sale_items.option_id,
					sale_items.net_unit_price,
					sale_items.unit_price,
					sale_items.cost,
					sum('.$this->db->dbprefix('sale_items').'.quantity) AS quantity,
					sale_items.warehouse_id,
					sum('.$this->db->dbprefix('sale_items').'.item_tax) AS item_tax,
					sale_items.tax_rate_id,
					sale_items.tax,
					sale_items.discount,
					sum('.$this->db->dbprefix('sale_items').'.item_discount) AS item_discount,
					sum('.$this->db->dbprefix('sale_items').'.subtotal) AS subtotal,
					sale_items.serial_no,
					sale_items.real_unit_price,
					sale_items.sale_item_id,
					sale_items.product_unit_id,
					sale_items.product_unit_code,
                    sale_items.bom_type,
					sum('.$this->db->dbprefix('sale_items').'.unit_quantity) AS unit_quantity,
					sale_items.`comment`,
					sale_items.item_note,
					sale_items.parent_id,
					sale_items.currency_rate,
					sale_items.currency_code,
					sale_items.pro_additionals,
					sale_items.extract_product,
					sale_items.raw_materials,
					units.name,
					sum('.$this->db->dbprefix('sale_items').'.return_quantity) AS return_quantity,
					tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, product_variants.name as variant, products.details as details')
			->join('units','sale_items.product_unit_id=units.id','left')		
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->group_by('sale_items.id, sale_items.product_id,sale_items.product_unit_code,sale_items.net_unit_price,sale_items.option_id,sale_items.square,sale_items.bom_type, (IF('.$this->db->dbprefix('sale_items').'.quantity>0,"sale","return"))')
            ->order_by('id', 'asc');
        } elseif ($this->pos_settings->item_order == 1) {
            $this->db->select('sale_items.id,
					sale_items.sale_id,
					sale_items.product_id,
					sale_items.product_code,
					sale_items.product_name,
					sale_items.product_type,
					sale_items.option_id,
					sale_items.net_unit_price,
					sale_items.unit_price,
					sale_items.cost,
					sum('.$this->db->dbprefix('sale_items').'.quantity) AS quantity,
					sale_items.warehouse_id,
					sum('.$this->db->dbprefix('sale_items').'.item_tax) AS item_tax,
					sale_items.tax_rate_id,
					sale_items.tax,
					sale_items.discount,
					sum('.$this->db->dbprefix('sale_items').'.item_discount) AS item_discount,
					sum('.$this->db->dbprefix('sale_items').'.subtotal) AS subtotal,
					sale_items.serial_no,
					sale_items.real_unit_price,
					sale_items.sale_item_id,
					sale_items.product_unit_id,
					sale_items.product_unit_code,
                    sale_items.bom_type,
					sum('.$this->db->dbprefix('sale_items').'.unit_quantity) AS unit_quantity,
					sale_items.`comment`,
					sale_items.item_note,
					sale_items.parent_id,
					sale_items.currency_rate,
					sale_items.pro_additionals,
					sale_items.extract_product,
					sale_items.raw_materials,
					units.name,
					tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, product_variants.name as variant, categories.id as category_id, categories.name as category_name, products.details as details')
			->join('units','sale_items.product_unit_id=units.id','left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('sale_items.id, sale_items.product_id,sale_items.product_unit_code,sale_items.net_unit_price,sale_items.option_id,sale_items.square,sale_items.bom_type, (IF('.$this->db->dbprefix('sale_items').'.quantity>0,"sale","return"))')
            ->order_by('categories.id', 'asc');
        }
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getMemberCardCode($card_no = false)
	{
		$q = $this->db->where("card_no",trim($card_no))->get("member_cards");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function getResiterByID($id = false){
		$q = $this->db->get_where("pos_register",array("id"=>$id));
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	public function getResiterItem($register_id = false){
		$q = $this->db->get_where("pos_register_items",array("register_id"=>$register_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getProductPromotions($product_id = false, $customer_id = false)
	{
		if($product_id && $customer_id){
			$current = date("Y-m-d");
			$q = $this->db->query("SELECT
									".$this->db->dbprefix('product_promotions').".`name` AS promotion_name,
									".$this->db->dbprefix('product_promotions').". start_date,
									".$this->db->dbprefix('product_promotions').". end_date,
									".$this->db->dbprefix('products').".`id` AS product_id,
									".$this->db->dbprefix('products').".`code` AS product_code,
									".$this->db->dbprefix('products').".`name` AS product_name,
									".$this->db->dbprefix('product_promotion_items').".for_min_quantity AS min_qty,
									".$this->db->dbprefix('product_promotion_items').".for_max_quantity AS max_qty,
									".$this->db->dbprefix('product_promotion_items').".for_free_quantity AS free_qty
								FROM
									".$this->db->dbprefix('product_promotions')."
									INNER JOIN ".$this->db->dbprefix('companies')." ON ".$this->db->dbprefix('companies').".product_promotion_id = ".$this->db->dbprefix('product_promotions').".id
									INNER JOIN ".$this->db->dbprefix('product_promotion_items')." ON ".$this->db->dbprefix('product_promotion_items').".promotion_id = ".$this->db->dbprefix('product_promotions').".id
									INNER JOIN ".$this->db->dbprefix('products')." ON ".$this->db->dbprefix('products').".id = ".$this->db->dbprefix('product_promotion_items').".for_product_id 
								WHERE
									".$this->db->dbprefix('product_promotions').".`status` = 1 
									AND ".$this->db->dbprefix('product_promotion_items').".main_product_id = ".$product_id."
									AND ".$this->db->dbprefix('companies').".id = ".$customer_id." 
									AND '".$current."' >= ".$this->db->dbprefix('product_promotions').".start_date 
									AND '".$current."' <= ".$this->db->dbprefix('product_promotions').".end_date
							");
			if($q->num_rows()){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}				
		}
		return false;					
	}
	
	public function getTotalCash($date = NULL, $user_id = NULL, $closed_date = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
		if($closed_date){
			$this->db->where('payments.date <', $closed_date);
		}
        $this->db->select('SUM(IFNULL('.$this->db->dbprefix('payments').'.amount,0)) AS total_cash', FALSE);
		$this->db->join("sales","sales.id = payments.sale_id","inner");
		$this->db->join("companies","companies.id = sales.biller_id AND companies.default_cash = payments.paid_by","inner");
        $this->db->where('payments.type', 'received')->where('payments.date >', $date);
        $this->db->where('payments.created_by', $user_id);
        $q = $this->db->get('payments');
        if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
    }
	
	
	public function getRegisterPayments($date = NULL, $user_id = NULL, $closed_date = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
		if($closed_date){
			$this->db->where('payments.date <', $closed_date);
		}
        $this->db->select('SUM(IFNULL('.$this->db->dbprefix('payments').'.amount,0)) AS paid,payments.paid_by as cash_account, IFNULL('.$this->db->dbprefix('cash_accounts').'.name,'.$this->db->dbprefix('payments').'.paid_by) as paid_by', FALSE);
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $this->db->where('payments.type', 'received')->where('payments.date >', $date);
        $this->db->where('payments.created_by', $user_id);
		$this->db->group_by('payments.paid_by');
        $q = $this->db->get('payments');
        if($q->num_rows()){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
        return false;
    }
	
	public function getTodayCash($access_biller = false)
    {
		$date = date('Y-m-d 00:00:00');
		if($access_biller > 0){
			$this->db->where('sales.biller_id',$access_biller);
		}	
		$this->db->select('SUM(IFNULL('.$this->db->dbprefix('payments').'.amount,0)) AS total_cash', FALSE);
        $this->db->join("sales","sales.id = payments.sale_id","inner");
		$this->db->join("companies","companies.id = sales.biller_id AND companies.default_cash = payments.paid_by","inner");
		$this->db->where('payments.type', 'received')->where('payments.date >=', $date);
		$this->db->group_by('payments.paid_by');
        $q = $this->db->get('payments');
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
    }
	
	public function getTodayPayments($access_biller = false)
    {
		$date = date('Y-m-d 00:00:00');
		if($access_biller > 0){
			$this->db->where('sales.biller_id',$access_biller);
		}	
		$this->db->select('SUM(IFNULL('.$this->db->dbprefix('payments').'.amount,0)) AS paid,payments.paid_by as cash_account, IFNULL('.$this->db->dbprefix('cash_accounts').'.name,'.$this->db->dbprefix('payments').'.paid_by) as paid_by', FALSE);
        $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$this->db->where('payments.type', 'received')->where('payments.date >=', $date);
		$this->db->group_by('payments.paid_by');
        $q = $this->db->get('payments');
        if($q->num_rows()){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
        return false;
    }
	
	public function getPaidDepositByCustomer($customer_id = false){
		$this->db->where("payments.paid_by","deposit");
		$this->db->where("sales.customer_id",$customer_id);
		$this->db->join("sales","sales.id = payments.sale_id","inner");
		$this->db->select("sum(".$this->db->dbprefix('payments').".amount) as amount");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	public function getTotalCustomerDeposit($customer_id = false){
		$this->db->select("sum(amount) as amount");
		$this->db->where("company_id",$customer_id);
		$q = $this->db->get("deposits");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function sysnceCustomerDeposit($customer_id = false){
		if($customer_id && $customer_id > 0){
			$total_deposit = $this->getTotalCustomerDeposit($customer_id);
			$paid_deposit = $this->getPaidDepositByCustomer($customer_id);
			$balance_deposit = ($total_deposit ? $total_deposit->amount : 0) - ($paid_deposit ? $paid_deposit->amount : 0);
			$this->db->update("companies",array("deposit_amount"=>$balance_deposit),array("id"=>$customer_id));
		}
	}
	

	
	
}
