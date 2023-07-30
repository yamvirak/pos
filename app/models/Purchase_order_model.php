<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_order_model extends CI_Model
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
            ->group_by('products.id');
        $this->db->where("(type = 'standard' OR type = 'raw_material' OR type = 'asset' OR type = 'combo') AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
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
        $q = $this->db->get_where('purchase_order_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllPurchaseOrderItemsWithDetails($purchase_order_id = false)
    {
        $this->db->select('purchase_order_items.id, purchase_order_items.product_name, purchase_order_items.product_code, purchase_order_items.quantity, purchase_order_items.serial_no, purchase_order_items.tax, purchase_order_items.unit_price, purchase_order_items.val_tax, purchase_order_items.discount_val, purchase_order_items.gross_total, products.details');
        $this->db->join('products', 'products.id=purchase_order_items.product_id', 'left');
        $this->db->order_by('id', 'desc');
        $q = $this->db->get_where('purchase_order_items', array('purchase_order_id' => $purchase_order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPurchaseOrderByID($id = false)
    {
        $q = $this->db->get_where('purchase_orders', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllPurchaseOrderItems($purchase_order_id = false)
    {
        $this->db->select('purchase_order_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, product_variants.name as variant, units.name as unit_name')
            ->join('products', 'products.id=purchase_order_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_order_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_order_items.tax_rate_id', 'left')
            ->join('units','units.id = purchase_order_items.product_unit_id','left')
			->group_by('purchase_order_items.id')
            ->order_by('id', 'desc');
        $q = $this->db->get_where('purchase_order_items', array('purchase_order_id' => $purchase_order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	

	public function syncPR($po_id = false, $pr_id = false){
		if(!$pr_id){
			$po = $this->getPurchaseOrderByID($po_id);
			$pr_id = $po->pr_id;
		}
		if($pr_id){
			$this->db->update("purchase_request_items",array("po_quantity"=>0),array("purchase_request_id"=>$pr_id));
			$status = "approved";
			
			
			$this->db->select("product_id, sum(quantity) as quantity");
			$this->db->join("purchase_orders","purchase_orders.id = purchase_order_items.purchase_order_id","INNER");
			$this->db->where("purchase_orders.pr_id",$pr_id);
			$this->db->group_by("product_id");
			$q = $this->db->get("purchase_order_items");
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$this->db->update("purchase_request_items",array("po_quantity"=>$row->quantity),array("purchase_request_id"=>$pr_id,"product_id"=>$row->product_id));
				}
				$approved = 0;
				$partial = 0;
				$completed = 0;
				$pr_items = $this->db->get_where("purchase_request_items",array("purchase_request_id"=>$pr_id));
				if ($pr_items->num_rows() > 0) {
					foreach (($pr_items->result()) as $pr_item) {
						if($pr_item->po_quantity >= $pr_item->quantity){
							$completed++;
						}else if($pr_item->po_quantity > 0){
							$partial++;
						}else{
							$approved++;
						}
					}
				}
				if($partial > 0){
					$status = "partial";
				}else if($approved > 0){
					$status = "approved";
				}else{
					$status = "completed";
				}
				
			}
			$this->db->update("purchase_requests",array("status"=>$status),array("id"=>$pr_id));
		}
	}

    public function addPurchaseOrder($data = array(), $items = array(), $pr_id = false)
    {
        if ($this->db->insert('purchase_orders', $data)) {
            $purchase_order_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['purchase_order_id'] = $purchase_order_id;
                $this->db->insert('purchase_order_items', $item);
            }
			$this->syncPR(false,$data['pr_id']);
            return true;
        }
        return false;
    }

    public function updatePurchaseOrder($id = false, $data = false, $items = array())
    {
        if ($this->db->update('purchase_orders', $data, array('id' => $id)) && $this->db->delete('purchase_order_items', array('purchase_order_id' => $id))) {
            foreach ($items as $item) {
                $item['purchase_order_id'] = $id;
                $this->db->insert('purchase_order_items', $item);
            }
			$this->syncPR($id);
			$this->syncPO($id);
            return true;
        }
        return false;
    }

    public function updateStatus($id = false, $status = false, $note = false)
    {
        if ($this->db->update('purchase_orders', array('status' => $status, 'note' => $note), array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deletePurchaseOrder($id = false)
    {
		$po = $this->getPurchaseOrderByID($id);
        if ($this->db->delete('purchase_orders', array('id' => $id,'status !=' => 'completed'))) {
			$this->db->delete('purchase_order_items', array('purchase_order_id' => $id));
			$this->db->delete('payments',array('purchase_order_id' =>$id));
			$this->db->delete('acc_tran',array('transaction'=>'Purchase Order Deposit','transaction_id'=>$id));
			$this->syncPR(false,$po->pr_id);
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
	
	public function approvePurchaseOrder($id = false, $data = false)
    {
        if ($this->db->update('purchase_orders', $data ,array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function unApprovePurchaseOrder($id = false)
    {
        if ($this->db->update('purchase_orders', array("status" => "pending"),array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function rejectPurchaseOrder($id = false)
    {
        if ($this->db->update('purchase_orders', array("status" => "rejected"),array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function getDeposits($purchase_order_id = false)
    {
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
		$this->db->order_by('id', 'desc');
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('payments.purchase_order_id' => $purchase_order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
    }
	
	public function getTotalDeposit($purchase_order_id = false){
		$q = $this->db->select('sum(amount) as amount')
					->where('purchase_order_id',$purchase_order_id)
					->get('payments');
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
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
	
	
	public function addDeposit($data = array(), $accTranPayments = array())
    {
        if ($this->db->insert('payments', $data)) {
			$payment_id = $this->db->insert_id();
			if($accTranPayments){
				foreach($accTranPayments as $accTranPayment){
					$accTranPayment['transaction_id']= $payment_id;
					$this->db->insert('acc_tran', $accTranPayment);
				}
			}
            return true;
        }
        return false;
    }
	
	public function updateDeposit($id = false, $data = array(), $accTranPayments = array())
    {
        if ($this->db->update('payments', $data, array('id' => $id))) {
			$this->site->deleteAccTran('Purchase Order Deposit',$id);
			if($accTranPayments){
				$this->db->insert_batch('acc_tran', $accTranPayments);
			}
			
            return true;
        }
        return false;
    }
	
	public function deleteDeposit($id = false)
    {
        if ($this->db->delete('payments', array('id' => $id))) {
			$this->site->deleteAccTran('Purchase Order Deposit',$id);
            return true;
        }
        return FALSE;
    }
	public function getPurchaseByPO($po_id = false){
		$q = $this->db->get_where("purchases",array("purchase_order_id"=>$po_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}

	
	public function syncPO($po_id = false){
		if($this->getPurchaseByPO($po_id)){
			$this->db->update("purchase_order_items",array("pu_quantity"=>0),array("purchase_order_id"=>$po_id));
			$status = "approved";
			$received = 0;
			$this->db->select("product_id, sum(quantity) as quantity");
			$this->db->join("purchases","purchases.id = purchase_items.purchase_id","INNER");
			$this->db->where("purchases.purchase_order_id",$po_id);
			$this->db->group_by("product_id");
			$q = $this->db->get("purchase_items");
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$this->db->update("purchase_order_items",array("pu_quantity"=>$row->quantity),array("purchase_order_id"=>$po_id,"product_id"=>$row->product_id));
				}
				$approved = 0;
				$partial = 0;
				$completed = 0;
				$pr_items = $this->db->get_where("purchase_order_items",array("purchase_order_id"=>$po_id));
				if ($pr_items->num_rows() > 0) {
					foreach (($pr_items->result()) as $pr_item) {
						if($pr_item->pu_quantity >= $pr_item->quantity){
							$completed++;
						}else if($pr_item->pu_quantity > 0){
							$partial++;
						}else{
							$approved++;
						}
					}
				}
				if($partial > 0){
					$status = "partial";
					$received = 2;
				}else if($approved > 0){
					$status = "approved";
					$received = 0;
				}else{
					$status = "completed";
					$received = 2;
				}
			}
			$this->db->update("purchase_orders",array("status"=>$status,"received"=>$received),array("id"=>$po_id));
		}
	}
}
