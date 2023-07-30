<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Purchases_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
	
	public function getPaymentTermsByID($id = NULL)
	{
        $q = $this->db->where('id', $id)->get('payment_terms');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getProductSerial($serial=false, $product_id = false, $warehouse_id = false, $purchase_id = false)
	{
		if($warehouse_id){
			$this->db->where("warehouse_id", $warehouse_id);
		}
		if($serial){
			$this->db->where("serial", $serial);
		}
		if($product_id){
			$this->db->where("product_id", $product_id);
		}
		if($purchase_id){
			$this->db->where("IFNULL(purchase_id,0) !=", $purchase_id);
		}
		
		$q = $this->db->get('product_serials');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}
	
	public function getProductReceiveSerial($serial=false, $product_id = false, $warehouse_id = false, $receive_id = false)
	{
		if($warehouse_id){
			$this->db->where("warehouse_id", $warehouse_id);
		}
		if($serial){
			$this->db->where("serial", $serial);
		}
		if($product_id){
			$this->db->where("product_id", $product_id);
		}
		if($receive_id){
			$this->db->where("IFNULL(receive_id,0) !=", $receive_id);
		}
		
		$q = $this->db->get('product_serials');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}
	

	public function getProductSerialByPurchaseID($serial = false,$purchase_id = false){
		$q = $this->db->get_where('product_serials',array('serial'=>$serial,'purchase_id'=>$purchase_id));
		if($q->num_rows() > 0 ){
			return $q->row();
		}
		return false;
	}
	public function getProductSerialByReceiveID($serial = false,$receive_id = false){
		$q = $this->db->get_where('product_serials',array('serial'=>$serial,'receive_id'=>$receive_id));
		if($q->num_rows() > 0 ){
			return $q->row();
		}
		return false;
	}
	
	public function getMultiPurchaseByID($id = false)
    {
		$this->db->select('purchases.id,purchases.date,purchases.reference_no,purchases.grand_total, IFNULL(cus_payments.paid,0) as paid, IFNULL(cus_payments.discount,0) as discount,pur_return.return_total,pur_return.return_paid,purchases.biller_id')
		->join('(select purchase_id, abs(grand_total) as return_total, abs(paid) as return_paid from '.$this->db->dbprefix('purchases').' where status="returned") as pur_return','pur_return.purchase_id=purchases.id','left')
		->join('(SELECT
					purchase_id,
					IFNULL(sum(amount),0) AS paid,
					IFNULL(sum(discount),0) AS discount
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					purchase_id) as cus_payments', 'cus_payments.purchase_id=purchases.id', 'left');		
		$this->db->where_in('purchases.id',$id);
		$this->db->where('purchases.payment_status!=','paid');
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
        }
        return FALSE;
    }
	public function getPurchaseBalanceByID($id = false)
    {
		$this->db->select('purchases.project_id,purchases.supplier_id,purchases.biller_id,purchases.id,purchases.date,purchases.reference_no,purchases.grand_total, IFNULL(cus_payments.paid,0) as paid, IFNULL(cus_payments.discount,0) as discount,pur_return.return_total,pur_return.return_paid,purchases.ap_account')
		->join('(select purchase_id, abs(grand_total) as return_total, abs(paid) as return_paid from cus_purchases) as pur_return','pur_return.purchase_id=purchases.id','left')
		->join('(SELECT
					purchase_id,
					IFNULL(sum(amount),0) AS paid,
					IFNULL(sum(discount),0) AS discount
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					purchase_id) as cus_payments', 'cus_payments.purchase_id=purchases.id', 'left');	
		$this->db->where('purchases.id',$id);
		$this->db->where('purchases.payment_status!=','paid');
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function addMultiPayment($data = false, $accTranPayments = false){
		if($data){
			foreach($data as $row){
				$this->db->insert('payments',$row);
				$payment_id = $this->db->insert_id();
				$this->site->syncPurchasePayments($row['purchase_id']);
				$accTrans = $accTranPayments[$row['purchase_id']];
				if($accTrans){
					foreach($accTrans as $accTran){
						$accTran['transaction_id'] = $payment_id;
						$this->db->insert('acc_tran',$accTran);
					}
				}
			}
			return true;
		}
		return false;
	}
	
	public function getPurhcaseByBillers($ids = array()){
        $q = $this->db->select("biller_id, count(biller_id) as counts")->where_in('id',$ids)->group_by('biller_id')->get("purchases");
        return $q;
	}
    
	public function getProductNames($term = false, $limit = 10)
    {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
		$this->db->where('products.inactive !=',1);
        $this->db->where("(type = 'standard' OR type = 'raw_material' OR type = 'asset' OR type = 'combo') AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getProductSerialDetailsByProductId($product_id = false, $warehouse_id = false, $serial = false)
	{		
		if($warehouse_id){
			$this->db->where("warehouse_id", $warehouse_id);
		}
		if($serial){
			$this->db->where("(serial='".$serial."' OR inactive='1')");
		}else{
			$this->db->where("inactive", 1);
		}
		$products_detail = $this->db->where("product_id",$product_id)->get("product_serials");
		if($products_detail->num_rows() > 0){
			foreach($products_detail->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductByID($id = false)
    {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductsByCode($code = false)
    {
        $this->db->select('*')->from('products')->like('code', $code, 'both');
        $q = $this->db->get();
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

    public function getProductByName($name = false)
    {
        $q = $this->db->get_where('products', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllPurchases()
    {
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getPurchaseItemSerial($product_id=false, $purchase_id = false, $serial_no = false)
	{
		if($product_id){
			$this->db->where("product_id", $product_id);
		}
		if($purchase_id){
			$this->db->where("purchase_id", $purchase_id);
		}
		if($serial_no){
			$this->db->where("serial_no", $serial_no);
		}
		$q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}


	public function getAllPurchaseItemsWithSerial($purchase_id = false){
		$this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant,units.name as unit_name, GROUP_CONCAT( IF(cus_purchase_items.serial_no !="" , cus_purchase_items.serial_no, NULL) SEPARATOR "<br>" ) AS serial_no, sum('.$this->db->dbprefix('purchase_items').'.unit_quantity) as unit_quantity, sum('.$this->db->dbprefix('purchase_items').'.subtotal) as subtotal')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
			->join('units', 'units.id=purchase_items.product_unit_id', 'left')
            ->group_by('purchase_items.product_id,purchase_items.expiry,purchase_items.real_unit_cost,purchase_items.product_unit_id,purchase_items.discount')
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

    public function getAllPurchaseItems($purchase_id = false)
    {
        $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant,units.name as unit_name')
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
	
	
	/*=============UPDATED 07/08/2018===============*/
	
	public function getTotalPurchaseShippingByPurchaseId($purchase_id = false)
    {
        $this->db->select('sum(total) as total_shipping,date')
            ->group_by('purchase_id');
        $q = $this->db->get_where('purchase_shippings', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {            
            return $q->row();
        }
        return FALSE;
    }
	
	public function getAllPurchaseShippingByPurchaseId($purchase_id = false)
    {
		$this->db->where("purchase_shippings.purchase_id",$purchase_id);
		$this->db->select("purchase_shippings.*,IFNULL(".$this->db->dbprefix('purchases').".paid,0) as paid");
		$this->db->join("purchases","purchases.freight_id = purchase_shippings.id","left");
        $q = $this->db->get("purchase_shippings");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllReceiveShippingByReceiveId($receive_id = false)
    {
		$this->db->where("purchase_shippings.receive_id",$receive_id);
		$this->db->select("purchase_shippings.*,IFNULL(".$this->db->dbprefix('purchases').".paid,0) as paid");
		$this->db->join("purchases","purchases.freight_id = purchase_shippings.id","left");
        $q = $this->db->get("purchase_shippings");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllTotalAmountPurchaseItems($purchase_id = false)
    {
        $this->db->select('purchase_items.*, sum(cus_purchase_items.subtotal) as subtotal, sum(IFNULL(cus_purchase_items.total_cbm,0)) as total_cbm')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->group_by('purchase_id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {            
            return $q->row();
        }
        return FALSE;
    }
	
	public function getAllTotalAmountReceiveItems($receive_id = false)
    {
        $this->db->select('receive_items.*, sum(cus_receive_items.subtotal) as subtotal, sum(IFNULL(cus_receive_items.total_cbm,0)) as total_cbm')
            ->join('products', 'products.id=receive_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=receive_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=receive_items.tax_rate_id', 'left')
            ->group_by('receive_id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('receive_items', array('receive_id' => $receive_id));
        if ($q->num_rows() > 0) {            
            return $q->row();
        }
        return FALSE;
    }
	
	public function getStockmoves($product_id = false, $transaction = false, $transaction_id = false)
    {
        $q = $this->db->get_where('stockmoves',array('product_id'=>$product_id, 'transaction' => $transaction,'transaction_id' => $transaction_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getPurchaseByFreightID($freight_id = false){
		$q = $this->db->get_where("purchases",array("freight_id"=>$freight_id));
		if ($q->num_rows() > 0) {            
            return $q->row();
        }
        return FALSE;
	}
	
	public function addPurchaseRecShipping($receive_id = false,$items = false, $items2 = false, $biller_id = false, $project_id = false, $reference_no = false, $warehouse_id = false)
	{	
		if($this->Settings->accounting == 1){
			$billAcc = $this->site->getAccountSettingByBiller($biller_id);
			$purchaseShippings = $this->getAllReceiveShippingByReceiveId($receive_id);
			if($purchaseShippings){
				foreach($purchaseShippings as $purchaseShipping){
					$this->site->deleteAccTran('Freight',$purchaseShipping->id);
				}
			}
		}
		$this->db->where("receive_id",$receive_id)->delete("purchase_shippings");
		$this->db->where("receive_id",$receive_id)->delete("purchase_shipping_items");
		$this->db->where("receive_id",$receive_id)->delete("purchases");
		if($this->db->insert_batch("purchase_shipping_items",$items2)){	
			$accTrans = false;
			foreach($items as $item){
				$old_pur_id = $item["old_id"];
				unset($item["old_id"]);
				$this->db->insert("purchase_shippings", $item);
				$shiping_id = $this->db->insert_id();
				if($this->Settings->accounting == 1){			
					$accTrans[] = array(
						'transaction' => 'Freight',
						'transaction_id' => $shiping_id,
						'transaction_date' => $item['date'],
						'reference' => $reference_no,
						'account' => $billAcc->ap_acc,
						'amount' => -($item['total']),
						'narrative' => 'Freight to '.$item['supplier'],
						'description' => $item['reference_no'],
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'supplier_id' => $item['supplier_id'],
						'user_id' => $this->session->userdata('user_id'),
					);
					if($item['order_tax'] > 0){
						$accTrans[] = array(
							'transaction' => 'Freight',
							'transaction_id' => $shiping_id,
							'transaction_date' => $item['date'],
							'reference' => $reference_no,
							'account' => $billAcc->vat_input,
							'amount' => $item['order_tax'],
							'narrative' => 'Tax Freight to '.$item['supplier'],
							'description' =>$item['reference_no'],
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'supplier_id' => $item['supplier_id'],
							'user_id' => $this->session->userdata('user_id'),
						);
					}
				}
				$item['freight_id'] = $shiping_id;
				$item['biller_id'] = $biller_id;
				$item['warehouse_id'] = $warehouse_id;
				$item['project_id'] = $project_id;
				$item['status'] = "freight";
				$item['grand_total'] = $item['total'];
				$item['total'] = $item['f_cost'];
				unset($item['f_cost']);
				
				$this->db->insert("purchases", $item);
				$new_pur_id = $this->db->insert_id();
				if($old_pur_id > 0){
					$this->db->update("payments",array("purchase_id"=>$new_pur_id),array("purchase_id"=>$old_pur_id));
					$this->site->syncPurchasePayments($new_pur_id);
				}
			}
			foreach($items2 as $item2){
				$purchase_id = $this->getReceiveByID($receive_id)->purchase_id;
				$stockmove = $this->getStockmoves($item2['product_id'],"Receives",$receive_id);	
				if($stockmove){
					$freight = $this->getPurchaseProductFreight($stockmove->product_id,$purchase_id);
					$freight_cost = ($freight ? $freight->freight_cost : 0) + $item2['unit_cost'];
					$this->db->delete('stockmoves', array('product_id' => $item2['product_id'],'transaction' => 'Receives','transaction_id' => $receive_id));
					$purchase_details = $this->getReceiveItemByID($item2['purchase_item_id']);		
					$stockmove->real_unit_cost = ($freight_cost + $purchase_details->real_unit_cost);
					
					$this->db->insert('stockmoves',$stockmove);
					if($this->Settings->accounting_method == '2'){
						$cal_cost = $this->site->updateAVGCost($item2['product_id'],"Receives",$receive_id);
					}else if($this->Settings->accounting_method == '1'){
						$cal_cost = $this->site->updateLifoCost($item2['product_id']);
					}else if($this->Settings->accounting_method == '0'){
						$cal_cost = $this->site->updateFifoCost($item2['product_id']);
					}else if($this->Settings->accounting_method == '3'){
						$cal_cost = $this->site->updateProductMethod($item2['product_id'],"Receives",$receive_id);
					}
					if($cal_cost) {
						if($stockmove->option_id){
							$this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $stockmove->option_id, 'product_id' => $item2['product_id']));
						}
					}
					
					if($this->Settings->accounting == 1){
						$productAcc = $this->site->getProductAccByProductId($item2['product_id']);
						$accTrans[] = array(
							'transaction' => 'Freight',
							'transaction_id' => $shiping_id,
							'transaction_date' => $item2['date'],
							'reference' => $reference_no,
							'account' => $productAcc->stock_acc,
							'amount' => ($item2['unit_cost'] * $item2['quantity']),
							'narrative' => 'Product Code: '.$stockmove->code.'#'.'Qty: '.$item2['quantity'].'#'.'Cost: '.$item2['unit_cost'],
							'description' =>$stockmove->reference_no,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'supplier_id' => null,
							'user_id' => $this->session->userdata('user_id'),
						);		
					}
				}

			}
			if($accTrans){
				$this->db->insert_batch("acc_tran",$accTrans);
			}
			
			return true;
		}					
		return false;
	}
	
	public function addPurchaseShipping($purchase_id = false,$items = false, $items2 = false, $biller_id = false, $project_id = false, $reference_no = false, $warehouse_id = false)
	{	
		if($this->Settings->accounting == 1){
			$billAcc = $this->site->getAccountSettingByBiller($biller_id);
			$purchase_info = $this->getPurchaseByID($purchase_id);
			$purchaseShippings = $this->getAllPurchaseShippingByPurchaseId($purchase_id);
			if($purchaseShippings){
				foreach($purchaseShippings as $purchaseShipping){
					$this->site->deleteAccTran('Freight',$purchaseShipping->id);
				}
			}
		}
		$this->db->where("purchase_id",$purchase_id)->delete("purchase_shippings");
		$this->db->where("purchase_id",$purchase_id)->delete("purchase_shipping_items");
		$this->db->where("purchase_id",$purchase_id)->delete("purchases");
		if($this->db->insert_batch("purchase_shipping_items",$items2)){	
			$accTrans = false;
			foreach($items as $item){
				$old_pur_id = $item["old_id"];
				unset($item["old_id"]);
				$this->db->insert("purchase_shippings", $item);
				$shiping_id = $this->db->insert_id();
				if($this->Settings->accounting == 1){			
					$accTrans[] = array(
						'transaction' => 'Freight',
						'transaction_id' => $shiping_id,
						'transaction_date' => $item['date'],
						'reference' => $reference_no,
						'account' => ($this->Settings->default_payable_account == 0 ? $billAcc->ap_acc : $purchase_info->ap_account),
						'amount' => -($item['total']),
						'narrative' => 'Freight to '.$item['supplier'],
						'description' => 'Freight to '.$reference_no,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'supplier_id' => $item['supplier_id'],
						'user_id' => $this->session->userdata('user_id'),
					);
					if($item['order_tax'] > 0){
						$accTrans[] = array(
							'transaction' => 'Freight',
							'transaction_id' => $shiping_id,
							'transaction_date' => $item['date'],
							'reference' => $reference_no,
							'account' => $billAcc->vat_input,
							'amount' => $item['order_tax'],
							'narrative' => 'Tax Freight to '.$item['supplier'],
							'description' => 'Freight to '.$reference_no,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'supplier_id' => $item['supplier_id'],
							'user_id' => $this->session->userdata('user_id'),
						);
					}
				}
				$item['freight_id'] = $shiping_id;
				$item['biller_id'] = $biller_id;
				$item['warehouse_id'] = $warehouse_id;
				$item['project_id'] = $project_id;
				$item['status'] = "freight";
				$item['grand_total'] = $item['total'];
				$item['total'] = $item['f_cost'];
				unset($item['f_cost']);
				$this->db->insert("purchases", $item);
				$new_pur_id = $this->db->insert_id();
				if($old_pur_id > 0){
					$this->db->update("payments",array("purchase_id"=>$new_pur_id),array("purchase_id"=>$old_pur_id));
					$this->site->syncPurchasePayments($new_pur_id);
				}
			}
			foreach($items2 as $item2){
				if($this->Settings->accounting == 1){
					$productAcc = $this->site->getProductAccByProductId($item2['product_id']);
					$accTrans[] = array(
						'transaction' => 'Freight',
						'transaction_id' => $shiping_id,
						'transaction_date' => $item2['date'],
						'reference' => $reference_no,
						'account' => $productAcc->stock_acc,
						'amount' => ($item2['unit_cost'] * $item2['quantity']),
						'narrative' => 'Product Code: '.$item2['product_code'].'#'.'Qty: '.$item2['quantity'].'#'.'Cost: '.$item2['unit_cost'],
						'description' => 'Freight to '.$reference_no,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'supplier_id' => null,
						'user_id' => $this->session->userdata('user_id'),
					);		
				}


				$stockmove = $this->getStockmoves($item2['product_id'],"Purchases",$purchase_id);
				if(!$stockmove){
					$stockmove = $this->getReceievStockmvoebyPurchase($item2['product_id'],$purchase_id);
				}
				if($stockmove){
					$freight = $this->getAllProductFreight($stockmove->product_id,$purchase_id);
					$freight_cost = $freight->freight_cost;
					
					$this->db->delete('stockmoves', array('product_id' => $item2['product_id'],'transaction' => $stockmove->transaction,'transaction_id' => $stockmove->transaction_id));
					$purchase_details = $this->getPurcahseItemByID($item2['purchase_item_id']);		
					$stockmove->real_unit_cost = ($freight_cost + $purchase_details->real_unit_cost);
					
					$this->db->insert('stockmoves',$stockmove);
					if($this->Settings->accounting_method == '2'){
						$cal_cost = $this->site->updateAVGCost($item2['product_id'],"Purchases",$purchase_id);
					}else if($this->Settings->accounting_method == '1'){
						$cal_cost = $this->site->updateLifoCost($item2['product_id']);
					}else if($this->Settings->accounting_method == '0'){
						$cal_cost = $this->site->updateFifoCost($item2['product_id']);
					}else if($this->Settings->accounting_method == '3'){
						$cal_cost = $this->site->updateProductMethod($item2['product_id'],"Purchases",$purchase_id);
					}
					if($cal_cost) {
						if($stockmove->option_id){
							$this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $stockmove->option_id, 'product_id' => $item2['product_id']));
						}
					}
				}

			}
			if($accTrans){
				$this->db->insert_batch("acc_tran",$accTrans);
			}
			
			return true;
		}					
		return false;
	}
	
	public function getReceievStockmvoebyPurchase($product_id = false,$purchase_id = false){
		if($product_id && $purchase_id){
			$this->db->where('product_id',$product_id);
			$this->db->where('transaction','Receives');
			$this->db->where('transaction_id IN (SELECT id FROM '.$this->db->dbprefix('receives').' WHERE purchase_id="'.$purchase_id.'")');
			$q = $this->db->get('stockmoves');
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
	public function getAllProductFreight($product_id = false, $purchase_id = false){
		if($product_id && $purchase_id){
			$this->db->select('sum(unit_cost) as freight_cost');
			$this->db->where('(purchase_id="'.$purchase_id.'" OR receive_id IN (SELECT id FROM '.$this->db->dbprefix('receives').' WHERE purchase_id="'.$purchase_id.'"))');
			$this->db->where('product_id',$product_id);
			$q = $this->db->get('purchase_shipping_items');
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
	
	public function getPurchaseProductFreight($product_id = false, $purchase_id = false, $receive_id= false){
		if($product_id && $purchase_id){
			$this->db->select('sum(unit_cost) as freight_cost');
			if($receive_id){
				$this->db->where('(purchase_id="'.$purchase_id.'" OR receive_id="'.$receive_id.'")');
			}else{
				$this->db->where('purchase_id',$purchase_id);
			}
			$this->db->where('product_id',$product_id);
			$q = $this->db->get('purchase_shipping_items');
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
		

	
    public function getItemByID($id = false)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTaxRateByName($name = false)
    {
        $q = $this->db->get_where('tax_rates', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    public function getPurchaseByID($id = false)
    {
		$q = $this->db->select('purchases.*,IFNULL(pur_return.return_total,0) as return_total,IFNULL(pur_return.return_paid,0) as return_paid')
		->join('(select purchase_id,abs(grand_total) as return_total,abs(paid) as return_paid FROM '.$this->db->dbprefix("purchases").' WHERE purchase_id > 0 AND status <> "draft" AND status <> "freight") as pur_return','pur_return.purchase_id = purchases.id','left');
        $q = $this->db->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function getPurchaseReturnByID($id = false)
    {
		$q = $this->db->select('purchases.*,IFNULL(pur_purchase.pur_total,0) as pur_total,IFNULL(pur_purchase.pur_paid,0) as pur_paid')
		->join('(select id,abs(grand_total) as pur_total,abs(paid) as pur_paid FROM '.$this->db->dbprefix("purchases").') as pur_purchase','pur_purchase.id = purchases.purchase_id','inner');
        $q = $this->db->get_where('purchases', array('purchases.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	
	public function getPurchaseByPurchaseId($purchase_id = false)
    {
        $q = $this->db->get_where('purchases', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getPurchaseByReceiveId($receive_id = false)
    {
        $q = $this->db->get_where('purchases', array('receive_id' => $receive_id));
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

    public function getProductWarehouseOptionQty($option_id = false, $warehouse_id = false)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addProductOptionQuantity($option_id = false, $warehouse_id = false, $quantity = false, $product_id = false)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                return TRUE;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function resetProductOptionQuantity($option_id = false, $warehouse_id = false, $quantity = false, $product_id = false)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                return TRUE;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function getOverSoldCosting($product_id = false)
    {
        $q = $this->db->get_where('costing', array('overselling' => 1));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function syncPO($pu_id = false, $po_id = false){
		if(!$po_id){
			$po = $this->getPurchaseByID($pu_id);
			$po_id = ($po ? $po->purchase_order_id : false);
		}
		if($po_id){
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
						}else if($pr_item->pu_quantity > 0 || $completed > 0){
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
	

    public function addPurchase($data = false, $items = false, $accTrans = false, $stockmoveReturns = false,$product_serials = false, $return_id = null)
    {
		$po_deposit = $data['po_deposit'];
		unset($data['po_deposit']);
		if($return_id){
			$this->db->delete('purchases', array('id' => $return_id));
			$this->db->delete('purchase_items', array('purchase_id' => $return_id));
			$this->site->deleteStockmoves('Purchases',$return_id);
			$this->site->deleteAccTran('Purchases',$return_id);
			$data['id'] = $return_id;
		}
        if ($this->db->insert('purchases', $data)) {
            $purchase_id = $this->db->insert_id();
			
			if($data['receive_ids']){
				$this->sysReceiveItem($purchase_id);
			}
			
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $purchase_id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
			if($product_serials && $data['status'] == 'received'){
				foreach($product_serials as $product_serial){
					$product_serial['purchase_id'] = $purchase_id;
					$this->db->insert('product_serials', $product_serial);
				}
			}
            foreach ($items as $item) {
				if ($data['status'] == 'received') {
					if($item['reactive']!=1){
						$serial_no = '';
					}else{
						$serial_no = $item['serial_no'];
						$this->db->update('product_serials',array('cost'=>$item['real_unit_cost']),array('product_id'=>$item['product_id'],'warehouse_id'=>$data['warehouse_id'],'serial'=>$serial_no));
					}
					$stockmove = array(
						'transaction' => 'Purchases',
						'transaction_id' => $purchase_id,
						'product_id' => $item['product_id'],
						'product_code' => $item['product_code'],
						'option_id' => $item['option_id'],
						'quantity' => $item['quantity'],
						'unit_quantity' => $item['unit_qty'],
						'unit_code' => $item['product_unit_code'],
						'unit_id' => $item['product_unit_id'],
						'warehouse_id' => $data['warehouse_id'],
						'expiry' => $item['expiry'],
						'date' => $data['date'],
						'real_unit_cost' => $item['real_unit_cost'],
						'serial_no' => $serial_no,
						'reference_no' =>  $item['reference_no'],
                        'user_id' =>  $item['user_id'],
					);
					$this->db->insert('stockmoves', $stockmove);
				}
				unset($item['unit_qty']);
				unset($item['reactive']);
				unset($item['reference_no']);
				unset($item['user_id']);
				
				$item['purchase_id'] = $purchase_id;
				$this->db->insert('purchase_items', $item);
                if ($data['status'] == 'received') {
					if($this->Settings->accounting_method == '2'){
						$cal_cost = $this->site->updateAVGCost($item['product_id'],"Purchases",$purchase_id);
					}else if($this->Settings->accounting_method == '1'){
						$cal_cost = $this->site->updateLifoCost($item['product_id']);
					}else if($this->Settings->accounting_method == '0'){
						$cal_cost = $this->site->updateFifoCost($item['product_id']);
					}else if($this->Settings->accounting_method == '3'){
						$cal_cost = $this->site->updateProductMethod($item['product_id'],"Purchases",$purchase_id);
					}
					if($cal_cost) {
						if($item['option_id']){
							$this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
						}
					}
                }
            }
			
			if($po_deposit > 0){
				$this->db->update('payments',array('purchase_id'=>$purchase_id),array('purchase_order_id'=>$data['purchase_order_id']));
				$this->site->syncPurchasePayments($purchase_id);
			}
			$this->syncPO(false,$data['purchase_order_id']);
            if ($data['status'] == 'returned') {
				if($stockmoveReturns){	
					foreach($stockmoveReturns as $stockmoveReturn){
						$purchase_item_id = $stockmoveReturn['purchase_item_id'];
						unset($stockmoveReturn['purchase_item_id']);
						$stockmoveReturn['transaction_id'] = $purchase_id;
						$this->db->insert('stockmoves', $stockmoveReturn);
						if($stockmoveReturn['unit_quantity'] > 1){
							$return_qty = abs($stockmoveReturn['quantity'] / $stockmoveReturn['unit_quantity']);
						}else{
							$return_qty = abs($stockmoveReturn['quantity']);
						}
						$this->db->update('purchase_items', array('return_qty_base' => abs($stockmoveReturn['quantity']), 'return_qty' => $return_qty,'return_unit_id' => $stockmoveReturn['unit_id']), array('id' => $purchase_item_id));
					}
				}
                $this->db->update('purchases', array('return_purchase_ref' => $data['return_purchase_ref'], 'surcharge' => $data['surcharge'],'return_purchase_total' => $data['grand_total'], 'return_id' => $purchase_id), array('id' => $data['purchase_id']));
            }
            return true;
        }
        return false;
    }

    public function updatePurchase($id = false, $data = false, $items = array(), $accTrans = false, $product_serials = array())
    {
        if ($this->db->update('purchases', $data, array('id' => $id)) && $this->db->delete('purchase_items', array('purchase_id' => $id))) {
            $purchase_id = $id;
			$this->db->delete('product_serials', array('purchase_id' => $id));
			$this->site->deleteStockmoves('Purchases',$purchase_id);
			$this->site->deleteAccTran('Purchases',$purchase_id);
			if($accTrans){
				$this->db->insert_batch('acc_tran', $accTrans);
			}
			$pur_receives = $this->getReceivByPurchaseID($id);
			$shipping = $this->getTotalPurchaseShippingByPurchaseId($id);
			if($shipping){
				$this->db->delete('purchase_shipping_items', array('purchase_id' => $purchase_id));
			}
			
			
			foreach ($items as $item) {
				$amount_shipping = 0;
				$percent = 0;
				if($shipping){
					$percent = ($item['subtotal'] * 100) / $data['total'];
					$amount_shipping = (($shipping->total_shipping * $percent)/100) / $item['quantity'];
				}
				if (!$pur_receives && ($data['status'] == 'received' || $data['status'] == 'partial')) {
					if($item['reactive']!=1){
						$serial_no = '';
					}else{
						$serial_no = $item['serial_no'];
						$this->db->update('product_serials',array('cost'=>$item['real_unit_cost']),array('product_id'=>$item['product_id'],'warehouse_id'=>$data['warehouse_id'],'serial'=>$serial_no));
					}
					$stockmove = array(
						'transaction' => 'Purchases',
						'transaction_id' => $purchase_id,
						'product_id' => $item['product_id'],
						'product_code' => $item['product_code'],
						'option_id' => $item['option_id'],
						'quantity' => $item['quantity_received'],
						'unit_quantity' => $item['unit_qty'],
						'unit_code' => $item['product_unit_code'],
						'unit_id' => $item['product_unit_id'],
						'warehouse_id' => $data['warehouse_id'],
						'expiry' => $item['expiry'],
						'serial_no' => $serial_no,
						'date' => $data['date'],
						'real_unit_cost' => ($item['real_unit_cost'] + $amount_shipping),
						'reference_no' => $item['reference_no'],
						'user_id' =>  $item['user_id'],
					);
					$this->db->insert('stockmoves', $stockmove);
					if($this->Settings->accounting_method == '2'){
						$cal_cost = $this->site->updateAVGCost($item['product_id'],"Purchases",$purchase_id);
					}else if($this->Settings->accounting_method == '1'){
						$cal_cost = $this->site->updateLifoCost($item['product_id']);
					}else if($this->Settings->accounting_method == '0'){
						$cal_cost = $this->site->updateFifoCost($item['product_id']);
					}else if($this->Settings->accounting_method == '3'){
						$cal_cost = $this->site->updateProductMethod($item['product_id'],"Purchases",$purchase_id);
					}
					
					if($cal_cost) {
						if($item['option_id']){
							$this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
						}
					}
				}
				unset($item['reference_no']);
				unset($item['user_id']);
				unset($item['reactive']);
				unset($item['unit_qty']);
				$item['purchase_id'] = $purchase_id;
				$this->db->insert('purchase_items', $item);
				if($shipping){
					$purchase_item_id = $this->db->insert_id();
					$shipping_item= array(
						'purchase_id' => $purchase_id,
						'purchase_item_id' => $purchase_item_id,
                        'product_id' => $item['product_id'],
                        'product_code' => $item['product_code'],                    
                        'unit_cost' => $amount_shipping,
						'unit_percent' => $percent,
                        'quantity' => $item['quantity'],                     
                        'date' => date('Y-m-d', strtotime($shipping->date)),
                    );
					$this->db->insert('purchase_shipping_items', $shipping_item);
				}
            }
			
			if($product_serials && ($data['status'] == 'received' || $data['status'] == 'partial')){
				foreach($product_serials as $product_serial){
					$product_serial['purchase_id'] = $purchase_id;
					$this->db->insert('product_serials', $product_serial);
					
					$serial_stockmove = $this->db->get_where('stockmoves',array('product_id'=>$product_serial['product_id'],'serial_no'=>$product_serial['serial'],'warehouse_id'=>$product_serial['warehouse_id']));
					if($serial_stockmove->num_rows() > 0){
						$this->db->delete('stockmoves',array('product_id'=>$product_serial['product_id'],'serial_no'=>$product_serial['serial'],'warehouse_id'=>$product_serial['warehouse_id']));
						foreach($serial_stockmove->result_array() as $row){
							$this->db->insert('stockmoves',$row);
						}
					}
				}
			}
			$this->syncPO($id);
            $this->site->syncPurchasePayments($id);
			if($pur_receives){
				$this->sysReceiveQuantity($id);
			}
			if($this->Settings->receive_item_vat == 1){
				$this->syncePurchaseVAT($id);
			}
			$this->synPurchaseCost($id);
            return true;
        }
        return false;
    }
	

	public function sysReceiveItem($purchase_id = false){
		$purchase = $this->getPurchaseByID($purchase_id);
		if($purchase && $purchase->receive_ids){
			$receive_ids = json_decode($purchase->receive_ids);
			if($receive_ids){
				$this->db->where_in("id",$receive_ids)->update("receives",array("purchase_id"=>$purchase_id,"status"=>"completed"));
				$this->db->where("transaction","Receives")->where_in("transaction_id",$receive_ids)->delete("stockmoves");
				$this->db->where("transaction","Receives")->where_in("transaction_id",$receive_ids)->delete("acc_tran");
			}
		}
	}

    public function updateStatus($id = false, $status = false, $note = false)
    {
        $items = $this->site->getAllPurchaseItems($id);
        if ($this->db->update('purchases', array('status' => $status, 'note' => $note), array('id' => $id))) {
            foreach ($items as $item) {
                $qb = $status == 'completed' ? ($item->quantity_balance + ($item->quantity - $item->quantity_received)) : $item->quantity_balance;
                $qr = $status == 'completed' ? $item->quantity : $item->quantity_received;
                $this->db->update('purchase_items', array('status' => $status, 'quantity_balance' => $qb, 'quantity_received' => $qr), array('id' => $item->id));
                $this->updateAVCO(array('product_id' => $item->product_id, 'warehouse_id' => $item->warehouse_id, 'quantity' => $item->quantity, 'cost' => $item->real_unit_cost));
            }
            return true;
        }
        return false;
    }

    public function deletePurchase($id = false)
    {
		if($id && $id > 0){
			$purchase = $this->getPurchaseByID($id);
			$purchase_items = $this->site->getAllPurchaseItems($id);
			if ($this->db->delete('purchase_items', array('purchase_id' => $id)) && $this->db->delete('purchases', array('id' => $id))) {
				$this->db->delete('product_serials', array('purchase_id' => $id));
				if($purchase->purchase_id){
					$this->db->update('purchase_items', array('return_qty_base' => 0, 'return_qty' => 0), array('purchase_id' => $purchase->purchase_id));
					$this->db->update('purchases', array('return_purchase_ref' => '', 'surcharge' => 0,'return_purchase_total' => 0, 'return_id' => ''), array('id' => $purchase->purchase_id));
				}
				$payments = $this->getPurchasePayments($id);
				$purchases = $this->getPurchaseByPurchaseId($id);
				if($purchases){
					foreach($purchases as $row_purchase){
						$this->db->delete('purchases', array('id' => $row_purchase->id));
						$this->db->delete('purchase_items', array('purchase_id' => $row_purchase->id));
						$this->site->deleteStockmoves('Purchases',$row_purchase->id);
						$this->site->deleteAccTran('Purchases',$row_purchase->id);
						$this->site->deleteAccTran('Freight',$row_purchase->id);
						
						$pur_payments = $this->getPurchasePayments($row_purchase->id);
						if($pur_payments){
							$this->db->delete('payments', array('purchase_id' => $row_purchase->id));
							foreach($pur_payments as $pur_payment){
								$this->site->deleteAccTran('Payment',$pur_payment->id);
							}
						}
					}
				}
				
				$pur_receives = $this->getReceivByPurchaseID($id);
				if($pur_receives){
					foreach($pur_receives as $pur_receive){
						$this->deleteReceive($pur_receive->id);
					}
				}
				if($this->Settings->accounting == 1){
					$this->site->deleteAccTran('Purchases',$id);
					$purchaseShippings = $this->getAllPurchaseShippingByPurchaseId($id);
					if($purchaseShippings){
						foreach($purchaseShippings as $purchaseShipping){
							$this->site->deleteAccTran('Freight',$purchaseShipping->id);
						}
					}
					if($payments){
						$this->db->delete('payments', array('purchase_id' => $id));
						foreach($payments as $payment){
							$this->site->deleteAccTran('Payment',$payment->id);
							if($payment->purchase_id > 0 && $payment->purchase_order_id > 0){
								$this->site->deleteAccTran('Purchase Order Deposit',$payment->id);
							}
							
						}
					}
				}
				$this->db->delete('purchase_shipping_items', array('purchase_id' => $id));
				$this->db->delete('purchase_shippings', array('purchase_id' => $id));
				$this->site->deleteStockmoves('Purchases',$id);
				
				if ($purchase->status == 'received' || $purchase->status == 'partial') {
					foreach ($purchase_items as $oitem) {
						if($this->Settings->accounting_method == '2'){
							$cal_cost = $this->site->updateAVGCost($oitem->product_id);
						}else if($this->Settings->accounting_method == '1'){
							$cal_cost = $this->site->updateLifoCost($oitem->product_id);
						}else if($this->Settings->accounting_method == '0'){
							$cal_cost = $this->site->updateFifoCost($oitem->product_id);
						}else if($this->Settings->accounting_method == '3'){
							$cal_cost = $this->site->updateProductMethod($oitem->product_id);
						}
						if($cal_cost) {
							if($oitem->option_id){
								$this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $oitem->option_id, 'product_id' => $oitem->product_id));
							}
						}
						
						$received = $oitem->quantity_received ? $oitem->quantity_received : $oitem->quantity;
						if ($oitem->quantity_balance < $received) {
							$clause = array('purchase_id' => NULL, 'transfer_id' => NULL, 'product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'option_id' => $oitem->option_id);
							if ($pi = $this->site->getPurchasedItem($clause)) {
								$quantity_balance = $pi->quantity_balance + ($oitem->quantity_balance - $received);
								$this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), $clause);
							} else {
								$clause['quantity'] = 0;
								$clause['item_tax'] = 0;
								$clause['quantity_balance'] = ($oitem->quantity_balance - $received);
								$this->db->insert('purchase_items', $clause);
							}
						}
					}
				}
				$this->db->delete('payments', array('purchase_id' => $id));
				$this->syncPO(false,$purchase->purchase_order_id);
				
				return true;
			}
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

    public function getPurchasePayments($purchase_id = false)
    {
        $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
		$this->db->order_by('id', 'desc');
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getExpensePayments($expense_id = false)
    {
        $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
		$this->db->order_by('id', 'desc');
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('expense_id' => $expense_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
    public function getPaymentByID($id = false)
    {
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as cash_account");
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('payments.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getPaymentsForPurchase($purchase_id = false)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getPaymentByPurchaseID($purchase_id = false)
    {
		$this->db->select('IFNULL(sum(amount),0) AS paid,IFNULL(sum(discount),0) AS discount');
		$this->db->group_by('purchase_id');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addPayment($data = array(), $accTranPayments = array(), $supplier_id = false)
    {

        if ($this->db->insert('payments', $data)) {
			$payment_id = $this->db->insert_id();
			if($accTranPayments){
				foreach($accTranPayments as $accTranPayment){
					$accTranPayment['transaction_id']= $payment_id;
					$this->db->insert('acc_tran', $accTranPayment);
				}
			}
			if ($supplier_id && $data['paid_by'] == 'deposit') {
				$this->sysnceSupplierDeposit($supplier_id);
            }
            $this->site->syncPurchasePayments($data['purchase_id']);
			$this->site->syncExpensePayments($data['expense_id']);
            return true;
        }
        return false;
    }

    public function updatePayment($id = false, $data = array(), $accTranPayments = array(), $supplier_id = false)
    {
		$opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
			$this->site->deleteAccTran('Payment',$id);
            $this->site->syncPurchasePayments($data['purchase_id']);
			$this->site->syncExpensePayments($data['expense_id']);
			
			if($accTranPayments){
				$this->db->insert_batch('acc_tran', $accTranPayments);
			}
			if ($opay->paid_by == 'deposit' && $opay->transaction != 'PO Deposit') {
                if (!$supplier_id) {
                    $purchase = $this->getPurchaseByID($opay->purchase_id);
                    $supplier_id = $purchase->supplier_id;
                }
				$this->sysnceSupplierDeposit($supplier_id);
            }
			if ($supplier_id && $data['paid_by'] == 'deposit' && $opay->transaction != 'PO Deposit') {
                $this->sysnceSupplierDeposit($supplier_id);
            }
            return true;
        }
        return false;
    }

    public function deletePayment($id = false)
    {
		if($id && $id > 0){
			$opay = $this->getPaymentByID($id);
			if ($this->db->delete('payments', array('id' => $id))) {
				$this->site->deleteAccTran('Payment',$id);
				$this->site->syncPurchasePayments($opay->purchase_id);
				$this->site->syncExpensePayments($opay->expense_id);
				if ($opay->paid_by == 'deposit' && $opay->transaction != 'PO Deposit') {
					if($opay->purchase_id > 0){
						$purchase = $this->getPurchaseByID($opay->purchase_id);
					}else{
						$purchase = $this->getExpenseByID($opay->expense_id);
					}
                    $supplier_id = $purchase->supplier_id;
					$this->sysnceSupplierDeposit($supplier_id);
				}
				return true;
			}
		}
        
        return FALSE;
    }

    public function getProductOptions($product_id = false)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductVariantByName($name = false, $product_id = false)
    {
        $q = $this->db->get_where('product_variants', array('name' => $name, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getExpenseByID($id = false)
    {
        $q = $this->db->get_where('expenses',array('id'=>$id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addExpense($data = array(), $items=array(), $accTrans = array(), $payment = array())
    {
		if ($this->db->insert('expenses', $data)) {
			$expense_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item['expense_id'] = $expense_id;
					$this->db->insert('expense_items', $item);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $expense_id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
			if($payment){
				$payment['expense_id'] = $expense_id;
				$this->db->insert('payments',$payment);
			}
			return true;
		}
		return false;

    }


    public function updateExpense($id = false, $data = array(),$items=array(), $accTrans = array(), $payment = array())
    {
        if ($this->db->update('expenses', $data, array('id' => $id))) {
			$this->site->deleteAccTran('Expense',$id);
			$this->db->delete('expense_items',array('expense_id' => $id));
			if($items){
				$this->db->insert_batch('expense_items', $items);
			}
			if($accTrans){
				$this->db->insert_batch('acc_tran', $accTrans);
			}
			if($payment){
				$this->db->delete('payments',array('expense_id' => $id));
				$this->db->insert('payments', $payment);
			}
			$this->site->syncExpensePayments($id);
            return true;
        }
        return false;
    }
	
	public function approveExpense($id = false, $data = false, $accTrans = false, $payment = false)
    {
        if ($this->db->update('expenses', $data, array('id' => $id))) {
			if($payment){
				$this->db->insert("payments",$payment);
			}
			if($accTrans){
				$this->db->insert_batch('acc_tran', $accTrans);
			}
            return true;
        }
        return false;
    }
	public function unapproveExpense($id = false){
		if($this->db->update("expenses",array("status"=>"pending"),array("id"=>$id))){
			$this->site->deleteAccTran('Expense',$id);
			return true;
		}
		return false;
	}

    public function deleteExpense($id = false)
    {
		if($id && $id > 0){
			if ($this->db->delete('expenses', array('id' => $id))) {
				$this->db->delete('expense_items',array('expense_id' => $id));
				$this->site->deleteAccTran('Expense',$id);
				$payments = $this->getExpensePayments($id);
				$this->db->delete('payments', array('expense_id' => $id));
				if($payments){
					foreach($payments as $payment){
						$this->site->deleteAccTran('Payment',$payment->id);
					}
				}
				return true;
			}
		}
        
        return FALSE;
    }

    public function getQuoteByID($id = false)
    {
        $q = $this->db->get_where('quotes', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllQuoteItems($quote_id = false)
    {
        $q = $this->db->get_where('quote_items', array('quote_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getReturnByID($id = false)
    {
        $q = $this->db->get_where('return_purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllReturnItems($return_id = false)
    {
        $this->db->select('return_purchase_items.*, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=return_purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=return_purchase_items.option_id', 'left')
            ->group_by('return_purchase_items.id')
            ->order_by('id', 'desc');
        $q = $this->db->get_where('return_purchase_items', array('return_id' => $return_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPurcahseItemByID($id = false)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getReceiveItemByID($id = false)
    {
        $q = $this->db->get_where('receive_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function returnPurchase($data = array(), $items = array())
    {

        $purchase_items = $this->site->getAllPurchaseItems($data['purchase_id']);

        if ($this->db->insert('return_purchases', $data)) {
            $return_id = $this->db->insert_id();

            foreach ($items as $item) {
                $item['return_id'] = $return_id;
                $this->db->insert('return_purchase_items', $item);

                if ($purchase_item = $this->getPurcahseItemByID($item['purchase_item_id'])) {
                    if ($purchase_item->quantity == $item['quantity']) {
                        $this->db->delete('purchase_items', array('id' => $item['purchase_item_id']));
                    } else {
                        $nqty = $purchase_item->quantity - $item['quantity'];
                        $bqty = $purchase_item->quantity_balance - $item['quantity'];
                        $rqty = $purchase_item->quantity_received - $item['quantity'];
                        $tax = $purchase_item->unit_cost - $purchase_item->net_unit_cost;
                        $discount = $purchase_item->item_discount / $purchase_item->quantity;
                        $item_tax = $tax * $nqty;
                        $item_discount = $discount * $nqty;
                        $subtotal = $purchase_item->unit_cost * $nqty;
                        $this->db->update('purchase_items', array('quantity' => $nqty, 'quantity_balance' => $bqty, 'quantity_received' => $rqty, 'item_tax' => $item_tax, 'item_discount' => $item_discount, 'subtotal' => $subtotal), array('id' => $item['purchase_item_id']));
                    }

                }
            }
            $this->calculatePurchaseTotals($data['purchase_id'], $return_id, $data['surcharge']);
            return true;
        }
        return false;
    }

    public function calculatePurchaseTotals($id = false, $return_id = false, $surcharge = false)
    {
        $purchase = $this->getPurchaseByID($id);
        $items = $this->getAllPurchaseItems($id);
        if (!empty($items)) {
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            foreach ($items as $item) {
                $product_tax += $item->item_tax;
                $product_discount += $item->item_discount;
                $total += $item->net_unit_cost * $item->quantity;
            }
            if ($purchase->order_discount_id) {
                $percentage = '%';
                $order_discount_id = $purchase->order_discount_id;
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = (($total + $product_tax) * (Float)($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            }
            if ($purchase->order_tax_id) {
                $order_tax_id = $purchase->order_tax_id;
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            }
            $total_discount = $order_discount + $product_discount;
            $total_tax = $product_tax + $order_tax;
            $grand_total = $total + $total_tax + $purchase->shipping - $order_discount + $surcharge;
            $data = array(
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'return_id' => $return_id,
                'surcharge' => $surcharge
            );

            if ($this->db->update('purchases', $data, array('id' => $id))) {
                return true;
            }
        } else {
            $this->db->delete('purchases', array('id' => $id));
        }
        return FALSE;
    }

    public function getExpenseCategories()
    {
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getExpenseCategoryByID($id = false)
    {
        $q = $this->db->get_where("expense_categories", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateAVCO($data = false)
    {
        if ($wp_details = $this->getWarehouseProductQuantity($data['warehouse_id'], $data['product_id'])) {
            $total_cost = (($wp_details->quantity * $wp_details->avg_cost) + ($data['quantity'] * $data['cost']));
            $total_quantity = $wp_details->quantity + $data['quantity'];
            if (!empty($total_quantity)) {
                $avg_cost = ($total_cost / $total_quantity);
                $this->db->update('warehouses_products', array('avg_cost' => $avg_cost), array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']));
            }
        } else {
            $this->db->insert('warehouses_products', array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'avg_cost' => $data['cost'], 'quantity' => 0));
        }
        
    }

	public function getPurchaseShippingItem($purchase_item_id = false, $product_id = false, $freight_type = false)
	{
		if($freight_type=='freight_receive'){
			$this->db->where('receive_id >',0);
		}else{
			$this->db->where('purchase_id >',0);
		}
		if($purchase_item_id){
			$this->db->where('purchase_item_id',$purchase_item_id);
		}
		$q = $this->db->where("product_id", $product_id)->get("purchase_shipping_items");
		if($q->num_rows() > 0){
			return $q->row();
		}				   
		return false;
	}
	
	public function getPurchaseShippingItems($purchase_id = false, $receive_id = false){
		if($purchase_id){
			$this->db->where('purchase_shipping_items.purchase_id',$purchase_id);
		}else if($receive_id){
			$this->db->where('purchase_shipping_items.receive_id',$receive_id);
		}
		$q = $this->db->get('purchase_shipping_items');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getCompaniesByName($name = NULL)
	{
		$q = $this->db->get_where("companies", array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getProjectByName($name = NULL)
	{
		$q = $this->db->get_where("projects", array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getCategoryByCode($code = NULL)
	{
		$q = $this->db->get_where("expense_categories", array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getWarehouseByCode($code = NULL)
	{
		$q = $this->db->get_where("warehouses", array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function sysReceivePOQuantity($purchase_order_id = NULL)
	{
		$items = $this->getReceiveItemsByPOID($purchase_order_id);
		$recieve_qty = 0;
		$po_qty = 0;
		foreach($items as $item){
			$product_unit = $this->site->getProductUnit($item->product_id,$item->product_unit_id);
			if($product_unit){
				$unit_qty = $product_unit->unit_qty;
			}else{
				$unit_qty = 1;
			}
			$this->db->where('purchase_order_id', $item->purchase_order_id);
			$this->db->where('product_id', $item->product_id);
			$this->db->where('product_unit_id', $item->product_unit_id);
			$this->db->where('unit_price', $item->unit_price);
			$this->db->update("purchase_order_items", array("quantity_received" => ($item->received * $unit_qty)));
			$recieve_qty += ($item->received ? $item->received : 0);
			$po_qty += ($item->unit_quantity ? $item->unit_quantity : 0);
		}
		
		$data["received"] = 1;
		if($recieve_qty== 0){
			$data["status"] = 'approved';
			$data["received"] = 0;
		}else if($po_qty > $recieve_qty){
			$data["status"] = 'partial';
		}else{
			$data["status"] = 'completed';
		}
		if($purchase_order_id){
			$this->db->update('purchase_orders',$data, array('id' => $purchase_order_id));
		}
	}
	
	public function sysReceiveQuantity($purchase_id = NULL)
	{
		$items = $this->getReceiveItemsByPurchaseID($purchase_id);
		$purchase_status = 'received';
		$recieve_qty = 0;
		$purchase_qty = 0;
		if($items){
			foreach($items as $item){
				$product_unit = $this->site->getProductUnit($item->product_id,$item->product_unit_id);
				if($product_unit){
					$unit_qty = $product_unit->unit_qty;
				}else{
					$unit_qty = 1;
				}
				$this->db->where('purchase_id', $item->purchase_id);
				$this->db->where('product_id', $item->product_id);
				$this->db->where('product_unit_id', $item->product_unit_id);
				$this->db->update("purchase_items", array("quantity_received" => ($item->received * $unit_qty)));
				$recieve_qty += ($item->received ? $item->received : 0);
				$purchase_qty += ($item->unit_quantity ? $item->unit_quantity : 0);
			}	
		}
		if($recieve_qty== 0){
			$purchase_status = 'pending';
		}else if($purchase_qty > $recieve_qty){
			$purchase_status = 'partial';
		}

		if($purchase_status && $purchase_id){
			$this->db->update('purchases',array('status' => $purchase_status), array('id' => $purchase_id));
		}
		
	}
	
	public function getReceiveByPurchaseID($purchase_id = false){
		$this->db->where("purchase_id",$purchase_id);
		$q = $this->db->get_where("receives");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function addReceive($data = array(), $items = array())
	{
		if ($this->db->insert('receives', $data)) {
			$receive_id = $this->db->insert_id();
			$accTrans = false;
			$receiveAcc = false;
			$total_prepaid = 0;
			if($this->Settings->accounting == 1){
				$receiveAcc = $this->site->getAccountSettingByBiller($data['biller_id']);
			}
			foreach($items as $item){
				$item['receive_id'] = $receive_id;
				$reference_no = $item['reference_no'];
				$user_id = $item['user_id'];
				$unit_qty = $item['unit_qty'];
				unset($item['reference_no']);
				unset($item['user_id']);
				unset($item['unit_qty']);
				$this->db->insert('receive_items', $item);
				if($data['purchase_id'] > 0){
					$purchase_id = $data['purchase_id'];
					$freight = $this->getPurchaseProductFreight($item['product_id'],$purchase_id);
					$freight_cost = ($freight ? $freight->freight_cost : 0);
				}else{
					$freight_cost = 0;
				}
				$serial_no = "";
				if($item['serial_no']!=''){
					$serial_no = $item['serial_no'];
					$product_serial = $this->getProductReceiveSerial($serial_no,$item['product_id'],$data['warehouse_id']);
					if(!$product_serial){
						$product_details = $this->getProductByCode($item['product_code']);
						$product_serials = array(
							'product_id' => $item['product_id'],
							'warehouse_id' => $data['warehouse_id'],
							'date' => $data['date'],
							'serial' => $serial_no,
							'cost' => ($item['real_unit_cost'] + $freight_cost),
							'price' => $product_details->price,
							'supplier_id' => $data["supplier_id"],
							'supplier' => $data["supplier"],
							'purchase_id' => $data["purchase_id"],
							'receive_id' => $receive_id,
						);
						$serial_no = "";
						$this->db->insert('product_serials', $product_serials);
					}
				}
				
				$stockmove = array(
					'transaction' => 'Receives',
					'transaction_id' => $receive_id,
					'product_id' => $item['product_id'],
					'product_code' => $item['product_code'],
					'option_id' => $item['option_id'],
					'quantity' => $item['quantity'],
					'unit_quantity' => $unit_qty,
					'unit_code' => $item['product_unit_code'],
					'unit_id' => $item['product_unit_id'],
					'warehouse_id' => $data['warehouse_id'],
					'expiry' => $item['expiry'],
					'date' => $data['date'],
					'serial_no' => $serial_no,
					'real_unit_cost' => ($item['real_unit_cost'] + $freight_cost),
					'reference_no' => $reference_no,
					'user_id' => $user_id
				);
				$this->db->insert('stockmoves', $stockmove);
				
				if($receiveAcc){
					$productAcc = $this->site->getProductAccByProductId($item['product_id']);
					$total_prepaid += ($item['unit_cost'] * $item['unit_quantity']);
					$accTrans[] = array(
						'transaction' => 'Receives',
						'transaction_id' => $receive_id,
						'transaction_date' => $data['date'],
						'reference' => $data['re_reference_no'],
						'account' => $productAcc->stock_acc,
						'amount' => ($item['unit_cost'] * $item['unit_quantity']),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item['unit_quantity'].'#'.'Cost: '.$item['unit_cost'],
						'description' => $data['note'],
						'biller_id' => $data['biller_id'],
						'project_id' => $data['project_id'],
						'user_id' => $user_id
					);
				}
				if($this->Settings->accounting_method == '2'){
					$cal_cost = $this->site->updateAVGCost($item['product_id'],"Receives",$receive_id);
				}else if($this->Settings->accounting_method == '1'){
					$cal_cost = $this->site->updateLifoCost($item['product_id']);
				}else if($this->Settings->accounting_method == '0'){
					$cal_cost = $this->site->updateFifoCost($item['product_id']);
				}else if($this->Settings->accounting_method == '3'){
					$cal_cost = $this->site->updateProductMethod($item['product_id'],"Receives",$receive_id);
				}
				if($cal_cost) {
					if($item['option_id']){
						$this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
					}
				}
				if($data['purchase_id'] > 0 && $data['si_reference_no'] != ""){
					$this->db->update("purchases",array("si_reference_no"=>$data['si_reference_no']),array("id"=>$purchase_id));
				}
                
			}
			if($accTrans){
				$accTrans[] = array(
						'transaction' => 'Receives',
						'transaction_id' => $receive_id,
						'transaction_date' => $data['date'],
						'reference' => $data['re_reference_no'],
						'account' => $receiveAcc->prepaid_acc,
						'amount' => -$total_prepaid,
						'narrative' => 'Purchase Prepaid',
						'description' => $data['note'],
						'biller_id' => $data['biller_id'],
						'project_id' => $data['project_id'],
						'user_id' => $user_id
					);
				$this->db->insert_batch('acc_tran', $accTrans);
			}
			if($data['purchase_id'] > 0 ){
				$this->sysReceiveQuantity($data['purchase_id']);
			}else{
				$this->sysReceivePOQuantity($data['purchase_order_id']);
			}
			return true;
		}
		return false;
	}
	
	public function getPurchaseItemCosts($purchase_id = false, $product_id = false, $unit_id = false){
		$this->db->select("purchase_items.*, 
							(sum(unit_cost * unit_quantity) / sum(unit_quantity)) as unit_cost,
							(sum(net_unit_cost * unit_quantity) / sum(unit_quantity)) as net_unit_cost,
							(sum(real_unit_cost * quantity) / sum(quantity)) as real_unit_cost
						");
		$this->db->where("purchase_id",$purchase_id);
		$this->db->where("product_id",$product_id);
		if($unit_id){
			$this->db->where("product_unit_id",$unit_id);
		}
		$q = $this->db->get("purchase_items");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function synPurchaseCost($purchase_id = false){
		$receives = $this->getReceiveByPurchaseID($purchase_id);
		$purchase = $this->getPurchaseByID($purchase_id);
		$receiveAcc = false;
		if($this->Settings->accounting == 1){
			$receiveAcc = $this->site->getAccountSettingByBiller($purchase->biller_id);
		}
		if($receives){
			foreach($receives as $receive){
				$receive_items = $this->getReceiveItemByReceiveID($receive->id);
				$accTrans = false;
				$total_prepaid = 0;
				$this->db->where("receive_id",$receive->id)->delete('receive_items');
				$this->db->delete('product_serials', array('receive_id' => $receive->id));
				$this->site->deleteStockmoves('Receives',$receive->id);
				$this->site->deleteAccTran('Receives',$receive->id);
				$products = false;
				foreach($receive_items as $receive_item){
					$product_details = $this->getProductByID($receive_item->product_id);
					$purchase_item = $this->getPurchaseItemCosts($purchase_id,$receive_item->product_id,$receive_item->product_unit_id);
					$freight = $this->getPurchaseProductFreight($receive_item->product_id,$purchase_id);
					$freight_cost = ($freight ? $freight->freight_cost : 0);
					$unit = $this->site->getProductUnit($receive_item->product_id, $receive_item->product_unit_id);
					$products = array(
						'receive_id' => $receive->id,
                        'product_id' => $receive_item->product_id,
                        'product_code' => $receive_item->product_code,
                        'product_name' => $receive_item->product_name,
                        'option_id' => $receive_item->option_id,
                        'net_unit_cost' => $purchase_item->net_unit_cost,
                        'unit_cost' => $purchase_item->unit_cost,
                        'quantity' => $receive_item->quantity,
                        'product_unit_id' => $receive_item->product_unit_id,
                        'product_unit_code' => $receive_item->product_unit_code,
                        'unit_quantity' => $receive_item->unit_quantity,
                        'warehouse_id' => $receive_item->warehouse_id,
                        'item_tax' => $receive_item->item_tax,
                        'tax_rate_id' => $receive_item->tax_rate_id,
                        'tax' => $receive_item->tax,
                        'discount' => $receive_item->discount,
                        'item_discount' => $receive_item->item_discount,
                        'subtotal' => ($purchase_item->unit_cost * $receive_item->unit_quantity),
                        'real_unit_cost' => $purchase_item->real_unit_cost,
						'product_type' => $receive_item->product_type,
						'parent_id' => $receive_item->parent_id,
						'serial_no' => $receive_item->serial_no,
						'comment' => $receive_item->comment,
						'expiry' => $receive_item->expiry,
						'sup_qty' => $receive_item->sup_qty,
                    );
					
					$this->db->insert('receive_items', $products);
					
					$serial_no = "";
					if($receive_item->serial_no!=''){
						$serial_no = $receive_item->serial_no;
						$product_serial = $this->getProductReceiveSerial($serial_no,$receive_item->product_id,$receive->warehouse_id,$receive->id);
						if(!$product_serial){
							$purchase_serial = $this->getProductSerialByReceiveID($serial_no,$receive->id);
							if($purchase_serial){
								$product_serials[] = array(
									'product_id' => $product_details->id,
									'warehouse_id' => $receive->warehouse_id,
									'date' => $receive->date,
									'serial' => $serial_no,
									'cost' => ($purchase_item->real_unit_cost + $freight_cost),
									'price' => $purchase_serial->price,
									'color' => $purchase_serial->color,
									'description' => $purchase_serial->description,
									'supplier_id' => $purchase->supplier_id,
									'supplier' => $purchase->supplier,
									'purchase_id' => $purchase->id,
									'receive_id' => $receive->id
								);
							}else{
								$product_serials = array(
									'product_id' => $product_details->id,
									'warehouse_id' => $receive->warehouse_id,
									'date' => $receive->date,
									'serial' => $serial_no,
									'cost' => ($purchase_item->real_unit_cost + $freight_cost),
									'price' => $product_details->price,
									'supplier_id' => $purchase->supplier_id,
									'supplier' => $purchase->supplier,
									'purchase_id' => $purchase->id,
									'receive_id' => $receive->id
								);
							}
							$serial_no = "";
							$this->db->insert('product_serials', $product_serials);
						}
					}
					
					$stockmove = array(
						'transaction' => 'Receives',
						'transaction_id' => $receive->id,
						'product_id' => $receive_item->product_id,
						'product_code' => $receive_item->product_code,
						'option_id' => $receive_item->option_id,
						'quantity' => $receive_item->quantity,
						'unit_quantity' => $unit->unit_qty,
						'unit_code' => $receive_item->product_unit_code,
						'unit_id' => $receive_item->product_unit_id,
						'warehouse_id' => $receive->warehouse_id,
						'expiry' => $receive_item->expiry,
						'date' => $receive->date,
						'serial_no' => $serial_no,
						'real_unit_cost' => ($purchase_item->real_unit_cost + $freight_cost),
						'reference_no' => $receive->re_reference_no,
						'user_id' => $receive->created_by
					);
					$this->db->insert('stockmoves', $stockmove);
					
					if($receiveAcc){
						$productAcc = $this->site->getProductAccByProductId($receive_item->product_id);
						$total_prepaid += ($purchase_item->unit_cost * $receive_item->unit_quantity);
						$accTrans[] = array(
							'transaction' => 'Receives',
							'transaction_id' => $receive->id,
							'transaction_date' => $receive->date,
							'reference' => $receive->re_reference_no,
							'account' => $productAcc->stock_acc,
							'amount' => ($purchase_item->unit_cost * $receive_item->unit_quantity),
							'narrative' => 'Product Code: '.$receive_item->product_code.'#'.'Qty: '.$receive_item->unit_quantity.'#'.'Cost: '.$purchase_item->unit_cost,
							'description' => $receive->note,
							'biller_id' => $purchase->biller_id,
							'project_id' => $purchase->project_id,
							'user_id' => $receive->created_by
						);
					}

					if($this->Settings->accounting_method == '2'){
						$cal_cost = $this->site->updateAVGCost($receive_item->product_id,"Receives",$receive->id);
					}else if($this->Settings->accounting_method == '1'){
						$cal_cost = $this->site->updateLifoCost($receive_item->product_id);
					}else if($this->Settings->accounting_method == '0'){
						$cal_cost = $this->site->updateFifoCost($receive_item->product_id);
					}else if($this->Settings->accounting_method == '3'){
						$cal_cost = $this->site->updateProductMethod($receive_item->product_id,"Receives",$receive->id);
					}
				}
				if($accTrans){
					$accTrans[] = array(
							'transaction' => 'Receives',
							'transaction_id' => $receive->id,
							'transaction_date' => $receive->date,
							'reference' => $receive->re_reference_no,
							'account' => $receiveAcc->prepaid_acc,
							'amount' => -$total_prepaid,
							'narrative' => 'Purchase Prepaid',
							'description' => $receive->note,
							'biller_id' => $purchase->biller_id,
							'project_id' => $purchase->project_id,
							'user_id' => $receive->created_by
						);
					$this->db->insert_batch('acc_tran', $accTrans);
				}
			}
		}
	}
	
	
	public function updateReceive($id = false, $data = array(), $items = array())
	{
		if ($this->db->where("id",$id)->update('receives', $data)) {
			$accTrans = false;
			$receiveAcc = false;
			$total_prepaid = 0;
			if($this->Settings->accounting == 1){
				$receiveAcc = $this->site->getAccountSettingByBiller($data['biller_id']);
			}
			$this->db->where("receive_id",$id)->delete('receive_items');
			$this->db->delete('product_serials', array('receive_id' => $id));
			$this->site->deleteStockmoves('Receives',$id);
			$this->site->deleteAccTran('Receives',$id);
			foreach($items as $item){
				$item['receive_id'] = $id;
				$reference_no = $item['reference_no'];
				$user_id = $item['user_id'];
				$unit_qty = $item['unit_qty'];
				unset($item['reference_no']);
				unset($item['user_id']);
				unset($item['unit_qty']);
				$this->db->insert('receive_items', $item);
				if($data['purchase_id'] > 0){
					$purchase_id = $data['purchase_id'];
					$freight = $this->getPurchaseProductFreight($item['product_id'],$purchase_id);
					$freight_cost = ($freight ? $freight->freight_cost : 0);
				}else{
					$freight_cost = 0;
				}
				
				$serial_no = "";
				if($item['serial_no']!=''){
					$serial_no = $item['serial_no'];
					$product_serial = $this->getProductReceiveSerial($serial_no,$item['product_id'],$data['warehouse_id'],$id);
					if(!$product_serial){
						$purchase_serial = $this->getProductSerialByReceiveID($serial_no,$id);
						$product_details = $this->getProductByCode($item['product_code']);
						if($purchase_serial){
							$product_serials[] = array(
								'product_id' => $product_details->id,
								'warehouse_id' => $data['warehouse_id'],
								'date' => $data['date'],
								'serial' => $serial_no,
								'cost' => ($item['real_unit_cost'] + $freight_cost),
								'price' => $purchase_serial->price,
								'color' => $purchase_serial->color,
								'description' => $purchase_serial->description,
								'supplier_id' => $data["supplier_id"],
								'supplier' => $data["supplier"],
								'purchase_id' => $data["purchase_id"],
								'receive_id' => $id,
							);
						}else{
							$product_serials = array(
								'product_id' => $item['product_id'],
								'warehouse_id' => $data['warehouse_id'],
								'date' => $data['date'],
								'serial' => $serial_no,
								'cost' => ($item['real_unit_cost'] + $freight_cost),
								'price' => $product_details->price,
								'supplier_id' => $data["supplier_id"],
								'supplier' => $data["supplier"],
								'purchase_id' => $data["purchase_id"],
								'receive_id' => $id,
							);
						}
						$serial_no = "";
						$this->db->insert('product_serials', $product_serials);
					}
				}
				
				$stockmove = array(
					'transaction' => 'Receives',
					'transaction_id' => $item['receive_id'],
					'product_id' => $item['product_id'],
					'product_code' => $item['product_code'],
					'option_id' => $item['option_id'],
					'quantity' => $item['quantity'],
					'unit_quantity' => $unit_qty,
					'unit_code' => $item['product_unit_code'],
					'unit_id' => $item['product_unit_id'],
					'warehouse_id' => $data['warehouse_id'],
					'expiry' => $item['expiry'],
					'date' => $data['date'],
					'serial_no' => $serial_no,
					'real_unit_cost' => ($item['real_unit_cost'] + $freight_cost),
					'reference_no' => $reference_no,
					'user_id' => $user_id
				);
				$this->db->insert('stockmoves', $stockmove);
				
				if($receiveAcc){
					$productAcc = $this->site->getProductAccByProductId($item['product_id']);
					$total_prepaid += ($item['unit_cost'] * $item['unit_quantity']);
					$accTrans[] = array(
						'transaction' => 'Receives',
						'transaction_id' => $id,
						'transaction_date' => $data['date'],
						'reference' => $data['re_reference_no'],
						'account' => $productAcc->stock_acc,
						'amount' => ($item['unit_cost'] * $item['unit_quantity']),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item['unit_quantity'].'#'.'Cost: '.$item['unit_cost'],
						'description' => $data['note'],
						'biller_id' => $data['biller_id'],
						'project_id' => $data['project_id'],
						'user_id' => $user_id
					);
				}

				if($this->Settings->accounting_method == '2'){
					$cal_cost = $this->site->updateAVGCost($item['product_id'],"Receives",$id);
				}else if($this->Settings->accounting_method == '1'){
					$cal_cost = $this->site->updateLifoCost($item['product_id']);
				}else if($this->Settings->accounting_method == '0'){
					$cal_cost = $this->site->updateFifoCost($item['product_id']);
				}else if($this->Settings->accounting_method == '3'){
					$cal_cost = $this->site->updateProductMethod($item['product_id'],"Receives",$id);
				}
				if($cal_cost) {
					if($item['option_id']){
						$this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
					}
				}
				if($data['purchase_id'] > 0 && $data['si_reference_no'] != ""){
					$this->db->update("purchases",array("si_reference_no"=>$data['si_reference_no']),array("id"=>$purchase_id));
				}
			}
			if($accTrans){
				$accTrans[] = array(
					'transaction' => 'Receives',
					'transaction_id' => $id,
					'transaction_date' => $data['date'],
					'reference' => $data['re_reference_no'],
					'account' => $receiveAcc->prepaid_acc,
					'amount' => -$total_prepaid,
					'narrative' => 'Purchase Prepaid',
					'description' => $data['note'],
					'biller_id' => $data['biller_id'],
					'project_id' => $data['project_id'],
					'user_id' => $user_id
				);
				$this->db->insert_batch('acc_tran', $accTrans);
			}
			if($data['purchase_id'] > 0 ){
				$this->sysReceiveQuantity($data['purchase_id']);
			}else{
				$this->sysReceivePOQuantity($data['purchase_order_id']);
			}
			return true;
		}
		return false;
	}
	public function deleteReceive($id = false)
	{
		if($id && $id > 0){
			$receive = $this->getReceiveByID($id);
			$receives = $this->getAllReceiveShippingByReceiveId($id);
			$purchases = $this->getPurchaseByReceiveId($id);
			$receive_items = $this->getReceiveItemByReceiveID($id);
			if ($this->db->where("id",$id)->delete("receives")) {
				$this->db->where('receive_id', $id)->delete("receive_items");
				$this->db->delete('product_serials', array('receive_id' => $id));
				$this->site->deleteAccTran('Receives',$id);
				if($receive->purchase_id > 0){
					$this->sysReceiveQuantity($receive->purchase_id);
				}else{
					$this->sysReceivePOQuantity($receive->purchase_order_id);
				}
				$this->site->deleteStockmoves('Receives',$id);
				$this->db->delete('purchase_shippings',array('receive_id' => $id));
				$this->db->delete('purchase_shipping_items',array('receive_id' => $id));			
				foreach ($receive_items as $oitem) {
					if($this->Settings->accounting_method == '2'){
						$cal_cost = $this->site->updateAVGCost($oitem->product_id);
					}else if($this->Settings->accounting_method == '1'){
						$cal_cost = $this->site->updateLifoCost($oitem->product_id);
					}else if($this->Settings->accounting_method == '0'){
						$cal_cost = $this->site->updateFifoCost($oitem->product_id);
					}else if($this->Settings->accounting_method == '3'){
						$cal_cost = $this->site->updateProductMethod($oitem->product_id);
					}
					if($cal_cost) {
						if($oitem->option_id){
							$this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $oitem->option_id, 'product_id' => $oitem->product_id));
						}
					}
				}
				if($purchases){
					foreach($purchases as $purchase){
						$this->db->delete('purchases', array('id' => $purchase->id));
						$pur_payments = $this->getPurchasePayments($purchase->id);
						if($pur_payments){
							$this->db->delete('payments', array('purchase_id' => $purchase->id));
							foreach($pur_payments as $pur_payment){
								$this->site->deleteAccTran('Payment',$pur_payment->id);
							}
						}
					}
				}
				if($receives){
					foreach($receives as $receive){
						$this->site->deleteAccTran('Freight',$receive->id);
					}
				}
				if($this->Settings->receive_item_vat == 1){
					$this->site->deleteAccTran('ReceivesVAT',$id);
					$this->db->delete("receive_vats",array("receive_id"=>$id));
					$this->syncePurchaseVAT($receive->purchase_id);
				}
				return true;
			}
		}
        return FALSE;
	}
	public function getReceiveItemsByPOID($purchase_order_id = NULL)
	{
		$q = $this->db->select('purchase_order_items.*,purchase_order_items.real_unit_price as real_unit_cost,purchase_order_items.unit_price as unit_cost,
								sum( '.$this->db->dbprefix('purchase_order_items').'.quantity ) AS quantity,
								sum( '.$this->db->dbprefix('purchase_order_items').'.unit_quantity ) AS unit_quantity, 
								receives.quantity AS received')
					  ->from('purchase_order_items')
					  ->join('purchase_orders','purchase_order_items.purchase_order_id = purchase_orders.id','inner')
					  ->join('(SELECT
									purchase_order_id,
									product_id,
									product_unit_id,
									unit_cost,
									sum(unit_quantity) AS quantity
								FROM
									'.$this->db->dbprefix('receives').'
								LEFT JOIN '.$this->db->dbprefix('receive_items').' ON '.$this->db->dbprefix('receives').'.id = receive_id
								WHERE purchase_order_id > 0
								GROUP BY 
									product_id,
									unit_cost,
									purchase_order_id
								) as receives','receives.purchase_order_id = purchase_orders.id
								AND receives.unit_cost = purchase_order_items.unit_price
								AND receives.product_unit_id = purchase_order_items.product_unit_id
								AND receives.product_id = purchase_order_items.product_id
								','left')
					  ->where('purchase_order_items.purchase_order_id', $purchase_order_id)
					  ->group_by('purchase_order_items.product_id,purchase_order_items.purchase_order_id,purchase_order_items.unit_price,product_unit_id')
					  ->get();
        if ($q->num_rows() > 0) {
            foreach($q->result() as $item){
				$data[] = $item;
			}
        }
        return $data;
	}
	public function getReceiveItemsByPurchaseID($purchase_id = NULL)
	{
		$q = $this->db->select('purchase_items.*,
								sum( '.$this->db->dbprefix('purchase_items').'.quantity ) AS quantity,
								sum( '.$this->db->dbprefix('purchase_items').'.unit_quantity ) AS unit_quantity, 
								receives.quantity AS received')
					  ->from('purchase_items')
					  ->join('purchases','purchase_items.purchase_id = purchases.id','left')
					  ->join('(SELECT
									purchase_id,
									product_id,
									product_unit_id,
									sum(unit_quantity) AS quantity
								FROM
									'.$this->db->dbprefix('receives').'
								LEFT JOIN '.$this->db->dbprefix('receive_items').' ON '.$this->db->dbprefix('receives').'.id = receive_id
								WHERE purchase_id > 0
								GROUP BY 
									product_id,
									product_unit_id,
									purchase_id
								) as receives','receives.purchase_id = purchases.id
								AND receives.product_unit_id = purchase_items.product_unit_id
								AND receives.product_id = purchase_items.product_id
								','left')
					  ->where('purchase_items.purchase_id', $purchase_id)
					  ->group_by('purchase_items.product_id,purchase_items.purchase_id,product_unit_id,IFNULL('.$this->db->dbprefix('purchase_items').'.expiry,"0000-00-00")')
					  ->get();
					  
        if ($q->num_rows() > 0) {
            foreach($q->result() as $item){
				$data[] = $item;
			}
			return $data;
        }
       return false;
	}
	
	public function getReceiveByID($id = NULL)
	{
		$q = $this->db->get_where("receives", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getReceivByPurchaseID($purchase_id = false)
	{
		$q = $this->db->get_where("receives", array('purchase_id' => $purchase_id));
		if ($q->num_rows() > 0) {
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
        }
        return FALSE;
	}
	
	public function getReceiveItemByReceiveID($receive_id = false){
		$q = $this->db->get_where("receive_items",array("receive_id"=>$receive_id));
		if ($q->num_rows() > 0) {
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
        }
        return FALSE;
	}
	
	public function getAllReceiveItems($receive_id = NULL)
    {
        $this->db->select('
					receive_items.*, 
					tax_rates.code as tax_code, 
					tax_rates.name as tax_name,
					tax_rates.rate as tax_rate, 
					products.unit, 
					products.details as details, 
					product_variants.name as variant,
					units.name as unit_name,
					('.$this->db->dbprefix('purchase_items').'.unit_quantity - '.$this->db->dbprefix('purchase_items').'.quantity_received) AS purchase_qty')
			->join('receives','receives.id = receive_items.receive_id','inner')
			->join('purchase_items','purchase_items.purchase_id = receives.purchase_id
			AND purchase_items.product_unit_id = receive_items.product_unit_id
			AND purchase_items.product_id = receive_items.product_id','inner')
            ->join('products', 'products.id=receive_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=receive_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=receive_items.tax_rate_id', 'left')
			->join('units', 'units.id=receive_items.product_unit_id', 'left')
			->where('receive_items.unit_quantity !=',0)
            ->group_by('receive_items.id')
            ->order_by('id', 'desc');
        $q = $this->db->get_where('receive_items', array('receive_id' => $receive_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllReceivePOItems($receive_id = NULL)
    {
        $this->db->select('
					receive_items.*, 
					tax_rates.code as tax_code, 
					tax_rates.name as tax_name,
					tax_rates.rate as tax_rate, 
					products.unit, 
					products.details as details, 
					product_variants.name as variant,
					units.name as unit_name,
					('.$this->db->dbprefix('purchase_order_items').'.unit_quantity - '.$this->db->dbprefix('purchase_order_items').'.quantity_received) AS purchase_qty')
			->join('receives','receives.id = receive_items.receive_id','inner')
			->join('purchase_order_items','purchase_order_items.purchase_order_id = receives.purchase_order_id
			AND purchase_order_items.unit_price = receive_items.unit_cost
			AND purchase_order_items.product_unit_id = receive_items.product_unit_id
			AND purchase_order_items.product_id = receive_items.product_id','inner')
            ->join('products', 'products.id=receive_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=receive_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=receive_items.tax_rate_id', 'left')
			->join('units', 'units.id=receive_items.product_unit_id', 'left')
			->where('receive_items.unit_quantity !=',0)
            ->group_by('receive_items.id')
            ->order_by('id', 'desc');
        $q = $this->db->get_where('receive_items', array('receive_id' => $receive_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	
	public function getPaymentsByRef($ref = false,$date = false)
	{
		$this->db->select('payments.*,payments.date AS payment_date,payments.reference_no AS payment_ref,payments.amount AS payment_amount,payments.discount AS payment_discount,IFNULL('.$this->db->dbprefix("purchases").'.date,'.$this->db->dbprefix("expenses").'.date) AS purchase_date,IFNULL('.$this->db->dbprefix("purchases").'.reference_no,'.$this->db->dbprefix("expenses").'.reference) AS purchase_ref')
		->join('purchases','purchases.id = payments.purchase_id','left')
		->join('expenses','expenses.id = payments.expense_id','left')
		->where('payments.reference_no',$ref)
		->where('payments.date',$date);
		$q = $this->db->get('payments');
		if($q->num_rows()){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getExpenseByRef($ref = null)
	{
		$q = $this->db->get_where("expenses", array("reference"=>$ref));
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
	
	public function getExpenseByBillers($ids = array())
	{
        $q = $this->db->select("biller_id, count(biller_id) as counts")
					  ->where_in('id',$ids)
					  ->group_by('biller_id')
					  ->get("expenses");
        return $q;
	}
	
	public function getMultiExpenseByID($id = false)
    {
		if($this->Settings->approval_expense==1){
			$this->db->where("expenses.status","approved");
		}
		$this->db->select('
					expenses.id,
					expenses.date,
					expenses.biller_id,
					expenses.reference as reference_no,
					expenses.grand_total as grand_total, 
					IFNULL(cus_payments.paid,0) as paid, 
					IFNULL(cus_payments.discount,0) as discount')
		->join('(SELECT
					expense_id,
					IFNULL(sum(amount),0) AS paid,
					IFNULL(sum(discount),0) AS discount
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					expense_id) as cus_payments', 'cus_payments.expense_id=expenses.id', 'left');		
		$this->db->where_in('expenses.id',$id);
		$this->db->where('expenses.payment_status!=','paid');
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
        }
        return FALSE;
    }
	
	public function getExpenseBalanceByID($id = false)
    {
		$this->db->select('
					expenses.project_id,
					expenses.supplier_id,
					expenses.biller_id,
					expenses.id,
					expenses.date,
					expenses.reference as reference_no,
					expenses.grand_total as grand_total, 
					IFNULL(cus_payments.paid,0) as paid, 
					IFNULL(cus_payments.discount,0) as discount,
					expenses.ap_account')
		->join('(SELECT
					expense_id,
					IFNULL(sum(amount),0) AS paid,
					IFNULL(sum(discount),0) AS discount
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					expense_id) as cus_payments', 'cus_payments.expense_id=expenses.id', 'left');	
		$this->db->where('expenses.id',$id);
		$this->db->where('expenses.payment_status!=','paid');
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function addMultiExPayment($data = false, $accTranPayments = false)
	{
		if($data){
			foreach($data as $row){
				$this->db->insert('payments',$row);
				$payment_id = $this->db->insert_id();
				$this->site->syncExpensePayments($row['expense_id']);
				$accTrans = $accTranPayments[$row['expense_id']];
				if($accTrans){
					foreach($accTrans as $accTran){
						$accTran['transaction_id'] = $payment_id;
						$this->db->insert('acc_tran',$accTran);
					}
				}
			}
			return true;
		}
		return false;
	}
	
	public function getInactiveProductSerailByPurchaseID($purchase_id = false)
	{
		$this->db->where('inactive',1);
		$this->db->where('purchase_id',$purchase_id);
		$q = $this->db->get('product_serials');
		if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
        }
        return FALSE;
	}
	
	public function getAllRooms($floor = false, $warehouse = false)
	{
		if((!$this->Owner && !$this->Admin) || !empty($this->session->userdata("warehouse_id"))){
			$warehouse_id = json_decode($this->session->userdata("warehouse_id"));
			$this->db->where_in("warehouse_id", $warehouse_id);
		}
		$q = $this->db->get_where("rental_rooms", array('status'=> 'active'));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getExpenseNames($term = false, $limit = 10)
    {
        $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getExpenseItems($id = false){
		$q = $this->db->get_where('expense_items',array('expense_id'=>$id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getPurcahseFreightByID($id = false){
		if($id){
			$this->db->where('purchases.id',$id);
		}
		$this->db->select("DATE_FORMAT(".$this->db->dbprefix('purchases').".date, '%Y-%m-%d %T') as date, 
						purchases.reference_no,
						IFNULL(purchase.reference_no,".$this->db->dbprefix('receives').".re_reference_no) as pur_reference,
						companies.company,
						{$this->db->dbprefix('warehouses')}.name as wname,
						purchases.supplier, 
						purchases.grand_total,
						purchases.paid, 
						".$this->db->dbprefix('purchases').".grand_total - IFNULL({$this->db->dbprefix('purchases')}.paid,0) as balance,
						IF(
							".$this->db->dbprefix('purchases').".grand_total = ".$this->db->dbprefix('purchases').".paid,'paid',
							IF(".$this->db->dbprefix('purchases').".paid > 0,'partial','pending')
						) as payment_status"
					)
				->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
				->join('companies', 'companies.id=purchases.biller_id', 'left')
				->join('receives', 'receives.id=purchases.receive_id', 'left')
				->join('(SELECT id, reference_no FROM '.$this->db->dbprefix('purchases').') as purchase','purchase.id = purchases.purchase_id','left');
		$q = $this->db->get('purchases');
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getSupplierByCompany($company = false){
		if($company){
			$q = $this->db->get_where('companies',array('company'=>$company,'group_id'=>4));
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
	public function getExpenseCategoryByName($name = false)
    {
        $q = $this->db->get_where("expense_categories", array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getExpenseCategoryByCode($code = false)
    {
        $q = $this->db->get_where("expense_categories", array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function importExpense($expenses = false, $expense_items = false ,$accTrans = false , $payments = false){
		if($expenses && $expense_items){
			foreach($expenses as $index => $expense){
				if($this->db->insert("expenses",$expense)){
					$expense_id = $this->db->insert_id();
					if($expense_items[$index]){
						foreach($expense_items[$index] as $expense_item){
							$expense_item["expense_id"] = $expense_id;
							$this->db->insert("expense_items",$expense_item);
						}
					}
					
					if($accTrans[$index]){
						foreach($accTrans[$index] as $accTran){
							$accTran["transaction_id"] = $expense_id;
							$this->db->insert("acc_tran",$accTran);
						}
					}
					if($payments && $payments[$index]){
						$payments[$index]["expense_id"] = $expense_id;
						$this->db->insert("payments",$payments[$index]);
					}
				}

			}
			return true;
		}
		return false;
	}
	public function getReceiveItemByReceives($receive_ids = false)
    {
        $this->db->select('receive_items.*, 
						sum('.$this->db->dbprefix("receive_items").'.quantity) as quantity,
						sum('.$this->db->dbprefix("receive_items").'.unit_quantity) as unit_quantity,
						tax_rates.code as tax_code, 
						tax_rates.name as tax_name, 
						tax_rates.rate as tax_rate, 
						products.unit, 
						products.image, 
						products.details as details, 
						product_variants.name as variant, 
						units.name as unit_name')
            ->join('products', 'products.id=receive_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=receive_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=receive_items.tax_rate_id', 'left')
            ->join('units','units.id = receive_items.product_unit_id','left')
			->group_by('receive_items.product_id,receive_items.product_unit_id');
		$this->db->where_in("receive_items.receive_id",$receive_ids);	
        $q = $this->db->get('receive_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getRefPurchaseRC(){
		$this->db->select('id,reference_no');
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchase_orders.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchase_orders.biller_id',$this->session->userdata('biller_id'));
		}
		$this->db->where_in('purchase_orders.status', array('approved','partial'));
		$this->db->where('purchase_orders.received !=', 2);
		$this->db->order_by('id','desc');
		$q = $this->db->get('purchase_orders');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getReceiveVATByReceiveID($receive_id = false){
		$q = $this->db->get_where("receive_vats",array("receive_id"=>$receive_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getTotalVATByPurchaseID($purchase_id = false){
		$this->db->where("purchase_id",$purchase_id);
		$this->db->select("SUM(amount) as amount");
		$q = $this->db->get("receive_vats");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function addReceiveVAT($receive_id = false, $purchase_id = false, $data = false, $accTrans = false){
		if($receive_id){
			$this->site->deleteAccTran('ReceivesVAT',$receive_id);
			$this->db->delete('receive_vats', array('receive_id' => $receive_id));
			if($data){
				$this->db->insert_batch('receive_vats',$data);
			}
			if($accTrans){
				$this->db->insert_batch('acc_tran',$accTrans);
			}
			$this->syncePurchaseVAT($purchase_id);
			return true;
		}
		return false;
	}
	public function syncePurchaseVAT($purchase_id = false){
		if($purchase = $this->getPurchaseByID($purchase_id)){
			$receive_vat = $this->getTotalVATByPurchaseID($purchase_id);
			if($receive_vat){
				$data = array(
								"order_tax" => ($purchase->order_tax - $purchase->receive_tax + $receive_vat->amount),
								"total_tax" => ($purchase->total_tax - $purchase->receive_tax + $receive_vat->amount),
								"grand_total" => ($purchase->grand_total - $purchase->receive_tax + $receive_vat->amount),
								"receive_tax" => $receive_vat->amount,
							);
				$this->db->update("purchases",$data,array("id"=>$purchase_id));
			}
		}
	}
	
	public function importPurchase($purchases = false, $purchase_items = false ,$stockmoves = false,$accTrans = false){
		if($purchases && $purchase_items){
			foreach($purchases as $index => $purchase){
				if($this->db->insert("purchases",$purchase)){
					$purchase_id = $this->db->insert_id();
					if($purchase_items[$index]){
						foreach($purchase_items[$index] as $purchase_item){
							unset($purchase_item['unit_qty']);
							$purchase_item["purchase_id"] = $purchase_id;
							$this->db->insert("purchase_items",$purchase_item);
						}
					}
					if($stockmoves[$index]){
						foreach($stockmoves[$index] as $stockmove){
							$stockmove["transaction_id"] = $purchase_id;
							$this->db->insert("stockmoves",$stockmove);
							if($this->Settings->accounting_method == '2'){
								$cal_cost = $this->site->updateAVGCost($stockmove['product_id'],"Purchases",$purchase_id);
							}else if($this->Settings->accounting_method == '1'){
								$cal_cost = $this->site->updateLifoCost($stockmove['product_id']);
							}else if($this->Settings->accounting_method == '0'){
								$cal_cost = $this->site->updateFifoCost($stockmove['product_id']);
							}else if($this->Settings->accounting_method == '3'){
								$cal_cost = $this->site->updateProductMethod($stockmove['product_id'],"Purchases",$purchase_id);
							}
						}
					}
					if($accTrans[$index]){
						foreach($accTrans[$index] as $accTran){
							$accTran["transaction_id"] = $purchase_id;
							$this->db->insert("acc_tran",$accTran);
						}
					}
				}

			}
			return true;
		}
		return false;
	}
	
	public function getPaidDepositBySupplier($supplier_id = false){
		$this->db->where("payments.paid_by","deposit");
		$this->db->where("(".$this->db->dbprefix('purchases').".supplier_id = ".$supplier_id." OR ".$this->db->dbprefix('expenses').".supplier_id = ".$supplier_id.")");
		$this->db->join("purchases","purchases.id = payments.purchase_id","left");
		$this->db->join("expenses","expenses.id = payments.expense_id","left");
		$this->db->select("sum(".$this->db->dbprefix('payments').".amount) as amount");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	public function getTotalSupplierDeposit($supplier_id = false){
		$this->db->select("sum(amount) as amount");
		$this->db->where("company_id",$supplier_id);
		$q = $this->db->get("deposits");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function sysnceSupplierDeposit($supplier_id = false){
		if($supplier_id && $supplier_id > 0){
			$total_deposit = $this->getTotalSupplierDeposit($supplier_id);
			$paid_deposit = $this->getPaidDepositBySupplier($supplier_id);
			$balance_deposit = ($total_deposit ? $total_deposit->amount : 0) - ($paid_deposit ? $paid_deposit->amount : 0);
			$this->db->update("companies",array("deposit_amount"=>$balance_deposit),array("id"=>$supplier_id));
		}
	}
	
}
