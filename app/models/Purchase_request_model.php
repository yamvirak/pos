<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_request_model extends CI_Model
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
        $q = $this->db->get_where('purchase_request_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllPurchaseRequestItemsWithDetails($purchase_request_id = false)
    {
        $this->db->select('purchase_request_items.id, purchase_request_items.product_name, purchase_request_items.product_code, purchase_request_items.quantity, purchase_request_items.serial_no, purchase_request_items.tax, purchase_request_items.unit_price, purchase_request_items.val_tax, purchase_request_items.discount_val, purchase_request_items.gross_total, products.details');
        $this->db->join('products', 'products.id=purchase_request_items.product_id', 'left');
        $this->db->order_by('id', 'desc');
        $q = $this->db->get_where('purchase_request_items', array('purchase_request_id' => $purchase_request_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPurchaseRequestByID($id = false)
    {
        $q = $this->db->get_where('purchase_requests', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllPurchaseRequestItems($purchase_request_id = false)
    {
        $this->db->select('purchase_request_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, product_variants.name as variant, units.name as unit_name')
            ->join('products', 'products.id=purchase_request_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_request_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_request_items.tax_rate_id', 'left')
            ->join('units','units.id = purchase_request_items.product_unit_id','left')
			->group_by('purchase_request_items.id')
            ->order_by('id', 'desc');
        $q = $this->db->get_where('purchase_request_items', array('purchase_request_id' => $purchase_request_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addPurchaseRequest($data = array(), $items = array())
    {
        if ($this->db->insert('purchase_requests', $data)) {
            $purchase_request_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['purchase_request_id'] = $purchase_request_id;
                $this->db->insert('purchase_request_items', $item);
            }
            return true;
        }
        return false;
    }

    public function updatePurchaseRequest($id = false, $data = false, $items = array())
    {
        if ($this->db->update('purchase_requests', $data, array('id' => $id)) && $this->db->delete('purchase_request_items', array('purchase_request_id' => $id))) {
            foreach ($items as $item) {
                $item['purchase_request_id'] = $id;
                $this->db->insert('purchase_request_items', $item);
            }
            return true;
        }
        return false;
    }

    public function updateStatus($id = false, $status = false, $note = false)
    {
        if ($this->db->update('purchase_requests', array('status' => $status, 'note' => $note), array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deletePurchaseRequest($id = false)
    {
        if ($this->db->delete('purchase_request_items', array('purchase_request_id' => $id)) && $this->db->delete('purchase_requests', array('id' => $id))) {
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
	
	public function approvePurchaseRequest($id = false, $data = false)
    {
        if ($this->db->update('purchase_requests', $data ,array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function unApprovePurchaseRequest($id = false)
    {
        if ($this->db->update('purchase_requests', array("status" => "pending"),array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function rejectPurchaseRequest($id = false)
    {
        if ($this->db->update('purchase_requests', array("status" => "rejected"),array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	public function getProductUnitByCode($product_id = false,$unit_code = false)
	{
		$this->db->select('product_units.unit_qty,units.code,units.id');
		$this->db->join('units','units.id=product_units.unit_id','left');
		$this->db->where('product_units.product_id', $product_id);
        $this->db->where('units.code', $unit_code);
        $q = $this->db->get('product_units');
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
}
