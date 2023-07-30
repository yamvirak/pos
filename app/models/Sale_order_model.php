<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sale_order_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term = false, $warehouse_id = false, $limit = 10)
    {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
		$this->db->where('products.inactive !=',1);
        $this->db->select('products.*, warehouses_products.quantity')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
			->where('products.type != ','raw_material')
			->where('products.type != ','asset')
            ->group_by('products.id');

            $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
 
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductByCode($code = false)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getWHProduct($id = false)
    {
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        $q = $this->db->get_where('products', array('warehouses_products.product_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getItemByID($id = false)
    {
        $q = $this->db->get_where('sale_order_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllSaleOrderItemsWithDetails($sale_order_id = false)
    {
        $this->db->select('sale_order_items.id, sale_order_items.product_name, sale_order_items.product_code, sale_order_items.quantity, sale_order_items.serial_no, sale_order_items.tax, sale_order_items.unit_price, sale_order_items.val_tax, sale_order_items.discount_val, sale_order_items.gross_total, products.details');
        $this->db->join('products', 'products.id=sale_order_items.product_id', 'left');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('sale_order_items', array('sale_order_id' => $sale_order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getSaleOrderByID($id = false)
    {
        $q = $this->db->get_where('sale_orders', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllSaleOrderItems($sale_order_id = false)
    {
        $this->db->select('sale_order_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, product_variants.name as variant,units.name as unit_name')
            ->join('products', 'products.id=sale_order_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_order_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_order_items.tax_rate_id', 'left')
            ->join('units','units.id = sale_order_items.product_unit_id','left')
			->group_by('sale_order_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('sale_order_items', array('sale_order_id' => $sale_order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addSaleOrder($data = array(), $items = array())
    {
        if ($this->db->insert('sale_orders', $data)) {
            $sale_order_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['sale_order_id'] = $sale_order_id;
                $this->db->insert('sale_order_items', $item);
            }
            return true;
        }
        return false;
    }

    public function updateQuote($id = false, $data = false, $items = array())
    {
        if ($this->db->update('sale_orders', $data, array('id' => $id)) && $this->db->delete('sale_order_items', array('sale_order_id' => $id))) {
            foreach ($items as $item) {
                $item['sale_order_id'] = $id;
                $this->db->insert('sale_order_items', $item);
            }
            return true;
        }
        return false;
    }

    public function updateStatus($id = false, $status = false, $note = false)
    {
        if ($this->db->update('sale_orders', array('status' => $status, 'note' => $note), array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteSaleOrder($id = false)
    {
        if ($this->db->delete('sale_orders', array('id' => $id,'status !='=>'completed'))) {
			$this->db->delete('sale_order_items', array('sale_order_id' => $id));
            $this->db->delete('payments',array('sale_order_id'=>$id));
			$this->db->delete('acc_tran',array('transaction'=>'Sale Order Deposit','transaction_id'=>$id));
			return true;
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

    public function getWarehouseProductQuantity($warehouse_id = false, $product_id = false)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductComboItems($pid = false, $warehouse_id = false)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
    }

    public function getProductOptions($product_id = false, $warehouse_id = false)
    {
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->where('warehouses_products_variants.warehouse_id', $warehouse_id)
            ->where('warehouses_products_variants.quantity >', 0)
            ->group_by('product_variants.id');
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

	/**************Updated Approved & Rejected*********************/
	
	public function approveSaleOrder($id = false)
    {
        if ($this->db->update('sale_orders', array("status" => "approved"),array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function unApproveSaleOrder($id = false)
    {
        if ($this->db->update('sale_orders', array("status" => "pending"),array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function rejectSaleOrder($id = false)
    {
        if ($this->db->update('sale_orders', array("status" => "rejected"),array('id' => $id))) {
            return true;
        }
        return FALSE;
    }


	public function updateRequestQuote($quote_id = false)
	{
		if ($quote_id) {
			$this->db->update('quotes', array('status' => 'completed'), array('id' => $quote_id));
		}
	}
	

	public function getCustomerPrice($product_id = false,$customer_id = false){
		$q = $this->db->get_where('customer_product_prices',array('customer_id'=>$customer_id,'product_id'=>$product_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
    }
    
    public function getProductFormulation(){
        $this->db->select('formulation_products.*, products.name');
        $this->db->group_by('for_product_id');
        $this->db->join('products','products.id = formulation_products.for_product_id','inner');
        $q = $this->db->get('formulation_products');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
	public function deleteDeposit($id = false)
    {
        $opay = $this->getDepositByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
			$this->site->deleteAccTran('Sale Order Deposit',$id);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                $sale_order = $this->getSaleOrderByID($opay->sale_order_id);
                $customer = $this->site->getCompanyByID($sale_order->customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            return true;
        }
        return FALSE;
    }
	
	
	public function getDepositByID($id = false)
    {
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as cash_account");
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('payments.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getTotalDeposit($sale_order_id = false){
		$q = $this->db->select('sum(amount) as amount')
					->where('sale_order_id',$sale_order_id)
					->get('payments');
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getSODeposits($sale_order_id = false){
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
		$this->db->order_by('id', 'desc');
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$q = $this->db->get_where('payments', array('sale_order_id' => $sale_order_id));
		if($q->num_rows() > 0 ){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function addDeposit($data = array(), $customer_id = null, $accTranPayments = array())
    {
        if ($this->db->insert('payments', $data)) {
			$payment_id = $this->db->insert_id();
			if($accTranPayments){
				foreach($accTranPayments as $accTranPayment){
					$accTranPayment['transaction_id']= $payment_id;
					$this->db->insert('acc_tran', $accTranPayment);
				}
			}
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            return true;
        }
        return false;
    }
	
	public function updateDeposit($id = false, $data = array(), $customer_id = null, $accTranPayments = array())
    {
        $opay = $this->getDepositByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
			$this->site->deleteAccTran('Sale Order Deposit',$id);
			if($accTranPayments){
				$this->db->insert_batch('acc_tran', $accTranPayments);
			}

            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale_order = $this->getInvoiceByID($opay->sale_order_id);
                    $customer_id = $sale_order->customer_id;
                }
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            return true;
        }
        return false;
    }
	
	
	
	
	
	
	
	
	
	
	
}
