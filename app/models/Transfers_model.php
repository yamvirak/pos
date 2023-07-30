<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Transfers_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

	public function getActiveProductSerialID($product_id = false, $warehouse_id = false, $serial = false)
	{		
		if($warehouse_id){
			$this->db->where("warehouse_id", $warehouse_id);
		}
		if($serial){
			$this->db->where("(serial='".$serial."' OR IFNULL(inactive,0)=0)");
		}else{
			$this->db->where("IFNULL(inactive,0)",0);
		}
		$products_detail = $this->db->where("product_id",$product_id)->get("product_serials")->result();
		return $products_detail;
	}
	
	
	public function getProductSerial($serial=false, $product_id = false, $warehouse_id = false, $transfer_id = false)
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
		if($transfer_id){
			$this->db->where("IFNULL(transfer_id,0) !=", $transfer_id);
		}
		
		$q = $this->db->get('product_serials');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}
	
	public function getTransferItemSerial($product_id=false, $transfer_id = false, $serial_no = false)
	{
		if($product_id){
			$this->db->where("product_id", $product_id);
		}
		if($transfer_id){
			$this->db->where("transfer_id", $transfer_id);
		}
		if($serial_no){
			$this->db->where("serial_no", $serial_no);
		}
		$q = $this->db->get('transfer_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}

    public function getProductNames($term = false, $warehouse_id = false, $limit = 10)
    {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
        $serial_no = '';
        if($this->Settings->product_serial){
            $serial_no = ',product_serials.serial';
        }
		$this->db->where('products.inactive !=',1);
        $this->db->select('products.id, products.code, products.name, warehouses_products.quantity, products.cost, tax_rate, type, unit, sale_unit, tax_method,products.seperate_qty,products.purchase_unit '.$serial_no.'')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("(type = 'standard' OR type = 'raw_material' OR type = 'asset') AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("(type = 'standard' OR type = 'raw_material' OR type = 'asset') AND warehouses_products.warehouse_id = '" . $warehouse_id . "' AND warehouses_products.quantity > 0 AND "
                . "(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        }
        if($this->Settings->product_serial){
			$this->db->join('product_serials','product_serials.product_id = products.id AND product_serials.warehouse_id="'.$warehouse_id.'" AND IFNULL('.$this->db->dbprefix("product_serials").'.inactive,0) = 0','LEFT');
			$this->db->or_where("(product_serials.serial = '".$term."')");
		}
		
		
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
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

    public function addTransfer($data = array(), $items = array(), $stockmoves = array(), $product_serials = array(), $update_serials = array(), $accTrans = false)
    {
        $status = $data['status'];
        if ($this->db->insert('transfers', $data)) {
            $transfer_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['transfer_id'] = $transfer_id;
				$this->db->insert('transfer_items', $item);
            }
			if ($status=='completed'){
				if($product_serials){
					foreach($product_serials as $product_serial){
						$product_serial['transfer_id'] = $transfer_id;
						$this->db->insert('product_serials', $product_serial);
						$this->db->update('stockmoves',array('serial_no'=>$product_serial['serial']),array('product_id'=>$product_serial['product_id'],'warehouse_id'=>$product_serial['warehouse_id'],'serial_no'=>$product_serial['serial']));
					}
				}
				foreach($stockmoves as $stockmove){
					$reactive = $stockmove['reactive'];
					unset($stockmove['reactive']);
					$stockmove['transaction_id'] = $transfer_id;
					$stockmove['warehouse_id'] = $data['from_warehouse_id'];
					$stockmove['quantity'] = $stockmove['quantity'] * (-1);
					$this->db->insert('stockmoves', $stockmove);
					
					if($reactive!=1){
						unset($stockmove['serial_no']);
					}
					
					$stockmove['warehouse_id'] = $data['to_warehouse_id'];
					$stockmove['quantity'] = $stockmove['quantity'] * (-1);
					$this->db->insert('stockmoves', $stockmove);
				}
				
				if($update_serials){
					foreach($update_serials as $update_serial){
						$this->db->update('product_serials', $update_serial, array('product_id'=>$update_serial['product_id'],'warehouse_id'=>$update_serial['warehouse_id'],'serial'=>$update_serial['serial']));
					}
				}
			}else if($status=='sent'){
				foreach($stockmoves as $stockmove){
					unset($stockmove['reactive']);
					$stockmove['transaction_id'] = $transfer_id;
					$stockmove['warehouse_id'] = $data['from_warehouse_id'];
					$stockmove['quantity'] = $stockmove['quantity'] * (-1);
					$this->db->insert('stockmoves', $stockmove);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $transfer_id;
					$this->db->insert("acc_tran",$accTran);
				}
			}
            return true;
        }
        return false;
    }

    public function updateTransfer($id, $data = array(), $items = array(), $stockmoves= array(), $product_serials = array(), $accTrans = false)
    {
        $status = $data['status'];
        if ($this->db->update('transfers', $data, array('id' => $id))) {
            $this->db->delete('transfer_items', array('transfer_id' => $id));
			$this->db->delete('product_serials', array('transfer_id' => $id));
			$this->site->deleteStockmoves('Transfer',$id);
			$this->site->deleteAccTran('Transfer',$id);
            foreach ($items as $item) {
                $item['transfer_id'] = $id;
                $this->db->insert('transfer_items', $item);
            }
			
			if ($status=='completed'){
				if($product_serials){
					foreach($product_serials as $product_serial){
						$product_serial['transfer_id'] = $id;
						$this->db->insert('product_serials', $product_serial);
						$this->db->update('stockmoves',array('serial_no'=>$product_serial['serial']),array('product_id'=>$product_serial['product_id'],'warehouse_id'=>$product_serial['warehouse_id'],'serial_no'=>$product_serial['serial']));

					}
				}
				foreach($stockmoves as $stockmove){
					$reactive = $stockmove['reactive'];
					unset($stockmove['reactive']);
					$stockmove['transaction_id'] = $id;
					
					$stockmove['warehouse_id'] = $data['from_warehouse_id'];
					$stockmove['quantity'] = $stockmove['quantity'] * (-1);
					$this->db->insert('stockmoves', $stockmove);
					if($reactive!=1){
						unset($stockmove['serial_no']);
					}
					$stockmove['warehouse_id'] = $data['to_warehouse_id'];
					$stockmove['quantity'] = $stockmove['quantity'] * (-1);
					$this->db->insert('stockmoves', $stockmove);
				}
			}else if($status=='sent'){
				foreach($stockmoves as $stockmove){
					unset($stockmove['reactive']);
					$stockmove['transaction_id'] = $id;
					$stockmove['warehouse_id'] = $data['from_warehouse_id'];
					$stockmove['quantity'] = $stockmove['quantity'] * (-1);
					$this->db->insert('stockmoves', $stockmove);
				}
			}
			
			if($accTrans){
				$this->db->insert_batch("acc_tran",$accTrans);
			}

            return true;
        }

        return false;
    }

    public function updateStatus($id = false, $status = false, $note = false)
    {
		if($id){
			$transfer = $this->getTransferByID($id);
			$items = $this->getAllTransferItems($id);
			if ($this->db->update('transfers', array('status' => $status, 'note' => $note), array('id' => $id))) {
				$this->db->delete('product_serials', array('transfer_id' => $id));
				$stockmoves = false;
				$product_serials = false;
				$accTrans = false;
				if($items){
					foreach($items as $item){
						$unit = $this->site->getProductUnit($item->product_id,$item->product_unit_id);
						$product_detail = $this->site->getProductByID($item->product_id);
						if ($status=='completed'){
							$item_serial = $item->serial_no;
							$reactive = 0;
							if($item_serial != ''){
								$fr_product_serial = $this->getProductSerial($item_serial,$product_detail->id,$transfer->from_warehouse_id);
								if($fr_product_serial){
									$to_product_serial = $this->getProductSerial($item_serial,$product_detail->id,$transfer->to_warehouse_id, $id);
									if($to_product_serial){
										if($to_product_serial->inactive==0){
											if($this->transfers_model->getTransferItemSerial($product_detail->id,$id,$item_serial)){
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
											'product_id' => $product_detail->id,
											'warehouse_id' => $transfer->to_warehouse_id,
											'transfer_id' => $transfer->id,
											'date' => $transfer->date,
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
							$stockmoves[] = array(
								'transaction_id' => $transfer->id,
								'transaction' => 'Transfer',
								'product_id' => $item->product_id,
								'product_code' => $item->product_code,
								'option_id' => $item->option_id,
								'quantity' => $item->quantity * (-1) ,
								'unit_quantity' => $unit->unit_qty,
								'warehouse_id' => $transfer->from_warehouse_id,
								'unit_code' => $unit->code,
								'unit_id' => $item->product_unit_id,
								'date' => $transfer->date,
								'expiry' => $item->expiry,
								'serial_no' => $item->serial_no,
								'real_unit_cost' => $product_detail->cost,
								'reference_no' => $transfer->transfer_no,
								'user_id' => $this->session->userdata('user_id'),
							);
							$stockmoves[] = array(
								'transaction_id' => $transfer->id,
								'transaction' => 'Transfer',
								'product_id' => $item->product_id,
								'product_code' => $item->product_code,
								'option_id' => $item->option_id,
								'quantity' => $item->quantity,
								'unit_quantity' => $unit->unit_qty,
								'warehouse_id' => $transfer->to_warehouse_id,
								'unit_code' => $unit->code,
								'unit_id' => $item->product_unit_id,
								'date' => $transfer->date,
								'expiry' => $item->expiry,
								'serial_no' => ($reactive!=1 ? '' : $item->serial_no),
								'real_unit_cost' => $product_detail->cost,
								'reference_no' => $transfer->transfer_no,
								'user_id' => $this->session->userdata('user_id'),
							);
							if($this->Settings->accounting == 1 && ($transfer->biller_id != $transfer->to_biller_id || ($this->Settings->project && $transfer->to_project != $transfer->from_project)) && $status != "pending"){
								$productAcc = $this->site->getProductAccByProductId($item->product_id);
								$accTrans[] = array(
									'transaction_id' => $transfer->id,
									'transaction' => 'Transfer',
									'transaction_date' => $transfer->date,
									'reference' => $transfer->transfer_no,
									'account' => $productAcc->stock_acc,
									'amount' => ($product_detail->cost * $item->quantity)  * (-1),
									'narrative' => 'Product Code: '.$product_detail->code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$product_detail->cost,
									'description' => $transfer->note,
									'biller_id' => $transfer->biller_id,
									'project_id' => $transfer->from_project,
									'user_id' => $this->session->userdata('user_id'),
								);
								$accTrans[] = array(
									'transaction_id' => $transfer->id,
									'transaction' => 'Transfer',
									'transaction_date' => $transfer->date,
									'reference' => $transfer->transfer_no,
									'account' => $productAcc->adjustment_acc,
									'amount' => ($product_detail->cost * $item->quantity),
									'narrative' => 'Product Code: '.$product_detail->code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$product_detail->cost,
									'description' => $transfer->note,
									'biller_id' => $transfer->biller_id,
									'project_id' => $transfer->from_project,
									'user_id' => $this->session->userdata('user_id'),
								);
								$accTrans[] = array(
									'transaction_id' => $transfer->id,
									'transaction' => 'Transfer',
									'transaction_date' => $transfer->date,
									'reference' => $transfer->transfer_no,
									'account' => $productAcc->stock_acc,
									'amount' => ($product_detail->cost * $item->quantity),
									'narrative' => 'Product Code: '.$product_detail->code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$product_detail->cost,
									'description' => $transfer->note,
									'biller_id' => $transfer->to_biller_id,
									'project_id' => $transfer->to_project,
									'user_id' => $this->session->userdata('user_id'),
								);
								$accTrans[] = array(
									'transaction_id' => $transfer->id,
									'transaction' => 'Transfer',
									'transaction_date' => $transfer->date,
									'reference' => $transfer->transfer_no,
									'account' => $productAcc->adjustment_acc,
									'amount' => ($product_detail->cost * $item->quantity) * (-1),
									'narrative' => 'Product Code: '.$product_detail->code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$product_detail->cost,
									'description' => $transfer->note,
									'biller_id' => $transfer->to_biller_id,
									'project_id' => $transfer->to_project,
									'user_id' => $this->session->userdata('user_id'),
								);
								
							}
						}else if($status=='sent'){
							$stockmoves[] = array(
								'transaction_id' => $transfer->id,
								'transaction' => 'Transfer',
								'product_id' => $item->product_id,
								'product_code' => $item->product_code,
								'option_id' => $item->option_id,
								'quantity' => $item->quantity * (-1) ,
								'unit_quantity' => $unit->unit_qty,
								'warehouse_id' => $transfer->from_warehouse_id,
								'unit_code' => $unit->code,
								'unit_id' => $item->product_unit_id,
								'date' => $transfer->date,
								'expiry' => $item->expiry,
								'serial_no' => $item->serial_no,
								'real_unit_cost' => $product_detail->cost,
								'reference_no' => $transfer->transfer_no,
								'user_id' => $this->session->userdata('user_id'),
							);
							if($this->Settings->accounting == 1 && ($transfer->biller_id != $transfer->to_biller_id || ($this->Settings->project && $transfer->to_project != $transfer->from_project)) && $status != "pending"){
								$productAcc = $this->site->getProductAccByProductId($item->product_id);
								$accTrans[] = array(
									'transaction_id' => $transfer->id,
									'transaction' => 'Transfer',
									'transaction_date' => $transfer->date,
									'reference' => $transfer->transfer_no,
									'account' => $productAcc->stock_acc,
									'amount' => ($product_detail->cost * $item->quantity)  * (-1),
									'narrative' => 'Product Code: '.$product_detail->code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$product_detail->cost,
									'description' => $transfer->note,
									'biller_id' => $transfer->biller_id,
									'project_id' => $transfer->from_project,
									'user_id' => $this->session->userdata('user_id'),
								);
								$accTrans[] = array(
									'transaction_id' => $transfer->id,
									'transaction' => 'Transfer',
									'transaction_date' => $transfer->date,
									'reference' => $transfer->transfer_no,
									'account' => $productAcc->adjustment_acc,
									'amount' => ($product_detail->cost * $item->quantity),
									'narrative' => 'Product Code: '.$product_detail->code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$product_detail->cost,
									'description' => $transfer->note,
									'biller_id' => $transfer->biller_id,
									'project_id' => $transfer->from_project,
									'user_id' => $this->session->userdata('user_id'),
								);
							}
						}
					}
				}
				
				$this->site->deleteStockmoves('Transfer',$id);
				$this->site->deleteAccTran('Transfer',$id);
				if($stockmoves){
					$this->db->insert_batch('stockmoves',$stockmoves);
				}
				if($accTrans){
					$this->db->insert_batch('acc_tran',$accTrans);
				}
				if($product_serials){
					$this->db->insert_batch('product_serials',$product_serials);
				}
				return true;
			}
		}
        
        return false;
    }

    public function getProductWarehouseOptionQty($option_id = false, $warehouse_id = false)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    public function getProductByCategoryID($id = false)
    {

        $q = $this->db->get_where('products', array('category_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return true;
        }

        return FALSE;
    }

    public function getProductQuantity($product_id = false, $warehouse = DEFAULT_WAREHOUSE)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return FALSE;
    }

    public function insertQuantity($product_id = false, $warehouse_id = false, $quantity = false)
    {
        if ($this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function updateQuantity($product_id = false, $warehouse_id = false, $quantity = false)
    {
        if ($this->db->update('warehouses_products', array('quantity' => $quantity), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
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
	public function getProductByID($code = false)
    {

        $q = $this->db->get_where('products', array('id' => $id), 1);
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

    public function getTransferByID($id = false)
    {

        $q = $this->db->get_where('transfers', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getAllTransferItems($transfer_id = false)
    {
		$this->db->select('transfer_items.*, product_variants.name as variant, products.unit,units.name as unit_name')
			->from('transfer_items')
			->join('products', 'products.id=transfer_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=transfer_items.option_id', 'left')
            ->join('units','units.id = transfer_items.product_unit_id','left')
			->group_by('transfer_items.id')
			->where('transfer_id', $transfer_id);

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getWarehouseProduct($warehouse_id = false, $product_id = false, $variant_id = false)
    {
        if ($variant_id) {
            return $this->getProductWarehouseOptionQty($variant_id, $warehouse_id);
        } else {
            return $this->getWarehouseProductQuantity($warehouse_id, $product_id);
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


    public function deleteTransfer($id = false)
    {
        if ($this->db->delete('transfers', array('id' => $id)) && $this->db->delete('transfer_items', array('transfer_id' => $id))) {
            $this->site->deleteStockmoves('Transfer',$id);
			$this->site->deleteAccTran('Transfer',$id);
			$this->db->delete('product_serials',array('transfer_id' => $id));
            return true;
        }
        return FALSE;
    }

    public function getProductOptions($product_id = false, $warehouse_id = false, $zero_check = TRUE)
    {
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.cost as cost, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->where('warehouses_products_variants.warehouse_id', $warehouse_id)
            ->group_by('product_variants.id');
        if ($zero_check) {
            $this->db->where('warehouses_products_variants.quantity >', 0);
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

    public function getProductComboItems($pid = false, $warehouse_id = false)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, warehouses_products.quantity as quantity')
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

    public function getProductVariantByName($name = false, $product_id = false)
    {
        $q = $this->db->get_where('product_variants', array('name' => $name, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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
	
	public function getAllWarehouses() {
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

}
