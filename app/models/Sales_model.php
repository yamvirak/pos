<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
	function getBomProductByStandProduct($pid = false, $bom_type = false){
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
							products.type as product_type,
							products.accounting_method
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

	public function getCategoryNames($term = false, $limit = 10){
		$this->db->where("({$this->db->dbprefix('categories')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('categories')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('categories')}.name, ' (', {$this->db->dbprefix('categories')}.code, ')') LIKE '%" . $term . "%')");
		$this->db->limit($limit);
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
	}

    public function getProductNames($term = false, $warehouse_id = false, $limit = 10)
    {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
		$this->db->where('products.inactive !=',1);
		$serial_no = '';
		if($this->Settings->product_serial){
			$serial_no = ',product_serials.serial,product_serials.price as serial_price';
		}
        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";
		
        $this->db->select('products.*, FWP.quantity as quantity, categories.id as category_id, categories.type as category_type, categories.name as category_name'.$serial_no.'', FALSE)
            ->join($wp, 'FWP.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
			->where('products.type != ','raw_material')
			->where('products.type != ','asset')
			->where('products.type != ','problem')
			//->where('products.type != ','service_rental')
            ->group_by('products.id');
		
		$where="";
		
		if ($this->Settings->set_custom_field) {
			$where = " OR ({$this->db->dbprefix('products')}.cf1 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf2 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf3 LIKE '%" . $term . "%'  OR {$this->db->dbprefix('products')}.cf4 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf5 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf6 LIKE '%" . $term . "%')"; 
		}
		
        if ($this->Settings->overselling) {
            $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%'".$where.")");
        } else {
            $this->db->where("(products.track_quantity = 0 OR FWP.quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "' AND "
                . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%'".$where.")");
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

    public function getProductByCode($code = false)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function syncQuantity($sale_id = false)
    {
        if ($sale_items = $this->getAllInvoiceItems($sale_id)) {
            foreach ($sale_items as $item) {
                $this->site->syncProductQty($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->site->syncVariantQty($item->option_id, $item->warehouse_id);
                }
            }
        }
    }

    public function getProductQuantity($product_id = false, $warehouse = false)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
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

    public function getProductVariants($product_id = false)
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

    public function getItemByID($id = false)
    {

        $q = $this->db->get_where('sale_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }


	public function getAllInvoiceItemParents($sale_id = false)
    {
        $this->db->select('sale_items.parent_id,products.name')
			->join('products', 'products.id=sale_items.product_id', 'left')
            ->group_by('sale_items.parent_id')
			->order_by('parent_id', 'desc');
			$this->db->where('sale_id', $sale_id);
			$q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result_array()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllInvoiceItems($sale_id = false, $return_id = NULL, $sort="desc")
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant,product_units.unit_qty as unitqty, units.name as unit_name, products.electricity, old_number, new_number, products.product_details as product_details, products.service_types')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
			->join('units','units.id=sale_items.product_unit_id','left')
			->join('product_units', 'product_units.product_id=sale_items.product_id AND product_units.unit_id = sale_items.product_unit_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', $sort);
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllInvoiceItemsReturn($sale_id = false)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant,product_units.unit_qty as unitqty, units.name as unit_name')
            ->join('sales', 'sales.id=sale_items.sale_id', 'inner')
			->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
			->join('units','units.id=sale_items.product_unit_id','left')
			->join('product_units', 'product_units.product_id=sale_items.product_id AND product_units.unit_id = sale_items.product_unit_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'desc');
            $this->db->where('sales.sale_id', $sale_id);
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllInvoiceItemsWithReturn($sale_id = false, $return_id = NULL)
    {
        $this->db->select('sale_items.*,
							sum('.$this->db->dbprefix('sale_items').'.unit_quantity) as unit_quantity,
							sum('.$this->db->dbprefix('sale_items').'.subtotal) as subtotal,
							sum('.$this->db->dbprefix('sale_items').'.quantity) as quantity,
							sale_return.return_stock as return_stock,
							sale_return.return_unit_id,
							units.name as unit_name,
							tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant,product_units.unit_qty as unitqty,sale_return.return_qty')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
			->join('product_units', 'product_units.product_id=sale_items.product_id AND product_units.unit_id = sale_items.product_unit_id', 'left')
			->join('units','units.id = sale_items.product_unit_id','left')
			->join('(SELECT
						'.$this->db->dbprefix('sales').'.sale_id AS return_id,
						'.$this->db->dbprefix('sale_items').'.product_id,
						'.$this->db->dbprefix('sale_items').'.sale_item_id,
						'.$this->db->dbprefix('sale_items').'.serial_no,
						'.$this->db->dbprefix('sale_items').'.expiry,
						'.$this->db->dbprefix('sale_items').'.return_stock,
						'.$this->db->dbprefix('sale_items').'.product_unit_id as return_unit_id,
						sum(
							'.$this->db->dbprefix('sale_items').'.unit_quantity
						) AS return_qty,
						unit_price
					FROM
						'.$this->db->dbprefix('sales').'
					INNER JOIN '.$this->db->dbprefix('sale_items').' ON '.$this->db->dbprefix('sale_items').'.sale_id = '.$this->db->dbprefix('sales').'.id
					WHERE
						cus_sales.sale_id <> ""
					GROUP BY
						cus_sales.sale_id,
						'.$this->db->dbprefix('sale_items').'.product_id,
						'.$this->db->dbprefix('sale_items').'.unit_price,
						'.$this->db->dbprefix('sale_items').'.option_id,
						'.$this->db->dbprefix('sale_items').'.expiry
					) as sale_return', 'sale_return.return_id = sale_items.sale_id
					AND sale_return.product_id = sale_items.product_id
					AND sale_return.sale_item_id = sale_items.id
					AND sale_return.serial_no = sale_items.serial_no
					AND sale_return.expiry = sale_items.expiry', 'left')

			->group_by('sale_items.product_id,sale_items.unit_price,sale_items.serial_no,sale_items.option_id,sale_items.expiry')
            ->order_by('id', 'desc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllInvoiceItemsWithDetails($sale_id = false)
    {
        $this->db->select('sale_items.*, products.details, product_variants.name as variant');
        $this->db->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->group_by('sale_items.id');
        $this->db->order_by('id', 'desc');
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

	public function getReturnBySaleId($sale_id = false)
	{
		$this->db->select('sales.*');
		$q = $this->db->get_where('sales', array('sale_id' => $sale_id));
		if ($q->num_rows() > 0) {
			return $q->row();
		}
	}

	public function getMultiInvoiceByID($id = false)
    {
		$this->db->select('sales.id,sales.date,sales.reference_no,sales.grand_total, IFNULL(cus_payments.paid,0) as paid, IFNULL(cus_payments.discount,0) as discount, payment_terms.due_day_discount, payment_terms.discount_type, payment_terms.discount as payment_discount,total_return,paid_return,sales.biller_id')
		->join('(select sale_id, abs(grand_total) as total_return, abs(paid) as paid_return from cus_sales) as sale_return','sale_return.sale_id=sales.id','left')
		->join('(SELECT
					sale_id,
					IFNULL(sum(amount),0) AS paid,
					IFNULL(sum(discount),0) AS discount
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left')
		->join('payment_terms','payment_terms.id = sales.payment_term','left');
		$this->db->where_in('sales.id',$id);
		$this->db->where('sales.payment_status!=','paid');
		$this->db->order_by('sales.date');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
        }
        return FALSE;
    }
	
	public function getAllMultiInvoiceByID($id = false)
    {
		$this->db->select('sales.id,sales.date,sales.reference_no,sales.grand_total, IFNULL(cus_payments.paid,0) as paid, IFNULL(cus_payments.discount,0) as discount, payment_terms.due_day_discount, payment_terms.discount_type, payment_terms.discount as payment_discount,total_return,paid_return')
		->join('(select sale_id, abs(grand_total) as total_return, abs(paid) as paid_return from cus_sales) as sale_return','sale_return.sale_id=sales.id','left')
		->join('(SELECT
					sale_id,
					IFNULL(sum(amount),0) AS paid,
					IFNULL(sum(discount),0) AS discount
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left')
		->join('payment_terms','payment_terms.id = sales.payment_term','left');
		$this->db->where_in('sales.id',$id);
		$this->db->order_by('sales.date');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
        }
        return FALSE;
    }

	public function getInvoiceBalanceByID($id = false)
    {
		$this->db->select('sales.project_id,sales.customer_id,sales.biller_id,sales.id,sales.date,sales.reference_no,sales.grand_total, IFNULL(cus_payments.paid,0) as paid, IFNULL(cus_payments.discount,0) as discount, payment_terms.due_day_discount, payment_terms.discount_type, payment_terms.discount as payment_discount,total_return,paid_return,sales.ar_account')
		->join('(select sale_id, abs(grand_total) as total_return, abs(paid) as paid_return from cus_sales) as sale_return','sale_return.sale_id=sales.id','left')
		->join('(SELECT
					sale_id,
					IFNULL(sum(amount),0) AS paid,
					IFNULL(sum(discount),0) AS discount
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left')
		->join('payment_terms','payment_terms.id = sales.payment_term','left');
		$this->db->where('sales.id',$id);
		$this->db->where('sales.payment_status!=','paid');
		$this->db->order_by('sales.date');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function getSaleReturnByID($id = false)
	{
		$this->db->select('sales.*,
					IF (
						(
							invoice_total - invoice_paid - abs(grand_total)
						) < 0,
						abs(
							invoice_total - invoice_paid - abs(grand_total)
						),
						,
						"0"
					) AS grand_total')
		->join('(
					SELECT
						id AS invoice_id,
						grand_total AS invoice_total,
						paid AS invoice_paid
					FROM
						cus_sales
				) AS cus_inv','sales.sale_id = cus_inv.invoice_id','inner')
		->where('id',$id);
		$q= $this->db->get('sales');
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

    public function getReturnByID($id = false)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getReturnBySID($sale_id = false)
    {
        $q = $this->db->get_where('sales', array('sale_id' => $sale_id), 1);
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

    public function addSale($data = array(), $items = array(), $payment = array(), $si_return = array(), $biller_id=null, $stockmoves = array(), $accTrans = array(), $accTranPayments = array(), $cust_prices = false)
    {
		if($data['id']){
			$this->db->delete('sales', array('id' => $data['id']));
			$this->db->delete('sale_items', array('sale_id' => $data['id']));
			$this->site->deleteStockmoves('Sale',$data['id']);
			$this->site->deleteAccTran('SaleReturn',$data['id']);
		}

		$so_deposit = $data['so_deposit'];
		$rental_deposit = $data['rental_deposit'];
		$rental_status = $data['rental_status'];
		unset($data['so_deposit']);
		unset($data['rental_deposit']);
		unset($data['rental_status']);
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $sale_id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
			if($cust_prices){
				foreach($cust_prices as $cust_price){
					$this->db->delete('customer_product_prices',array('product_id'=>$cust_price['product_id'],'customer_id'=>$cust_price['customer_id']));
					$this->db->insert('customer_product_prices',$cust_price);
				}
			}
			$groups_delivery = $this->input->post('groups_delivery');
			if($groups_delivery){
				foreach($groups_delivery as $gd){
					$ds = $this->site->getAllStockmoves("Delivery",$gd);
					$this->site->deleteStockmoves('Delivery',$gd);
					$this->site->deleteAccTran('Delivery',$gd);
					if($ds){
						$this->db->update("deliveries", array("status" => "completed", "sale_reference_no"=> $data['reference_no'], "sale_id" => $sale_id), array("id" => $gd));
						$this->db->update("sales", array("delivery_status" => "completed"), array("id" => $sale_id));
					}
				}
			}else if($data['delivery_id']){
				$deliveries_stock = $this->site->getAllStockmoves("Delivery",$data['delivery_id']);
				if($deliveries_stock){
					$this->site->deleteStockmoves('Delivery',$data['delivery_id']);
					$this->site->deleteAccTran('Delivery',$data['delivery_id']);
					$this->db->update("deliveries", array("status" => "completed", "sale_reference_no"=> $data['reference_no'], "sale_id" => $sale_id), array("id" => $data['delivery_id']));
					$this->db->update("sales", array("delivery_status" => "completed"), array("id" => $sale_id));
				}
			}

            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
				if($item['quantity'] < 0){
					if($this->Settings->accounting_method == '2'){
						$cal_cost = $this->site->updateAVGCost($item['product_id']);
					}else if($this->Settings->accounting_method == '1'){
						$cal_cost = $this->site->updateLifoCost($item['product_id']);
					}else if($this->Settings->accounting_method == '0'){
						$cal_cost = $this->site->updateFifoCost($item['product_id']);
					}else if($this->Settings->accounting_method == '3'){
						$cal_cost = $this->site->updateProductMethod($item['product_id']);
					}
					
					if($cal_cost) {
						if($item['option_id']){
							$this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
						}
					}
				}
            }

            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
					$payment_reference_no = $this->site->getReference('pay',$biller_id);
                    $payment['reference_no'] = $payment_reference_no;
                }
				$payment['sale_id'] = $sale_id;
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {
                    if ($payment['paid_by'] == 'deposit') {
						$this->sysnceCustomerDeposit($data['customer_id']);
					}
					$this->db->insert('payments', $payment);
                }
				$payment_id = $this->db->insert_id();
				if($accTranPayments){
					foreach($accTranPayments as $accTranPayment){
						$accTranPayment['transaction_id'] = $payment_id;
						if (empty($accTranPayment['reference'])) {
							$accTranPayment['reference'] = $payment_reference_no;
						}
						$this->db->insert('acc_tran', $accTranPayment);
					}
				}
                $this->site->syncSalePayments($sale_id);
            }
			
			if($so_deposit > 0){
				$this->db->update('payments',array('sale_id'=>$sale_id),array('sale_order_id'=>$data['sale_order_id']));
				$this->site->syncSalePayments($sale_id);
			}

            $reference_no = "";
            $date = "";

			if($data['stock_deduction'] != 0 && $stockmoves){
				foreach($stockmoves as $stockmove){
					if ($stockmove['product_type'] != 'combo') {
						$stockmove['transaction_id'] = $sale_id;
						$this->db->insert('stockmoves', $stockmove);
					}
				}
			}else if($stockmoves && $this->Settings->accounting == 1){
				foreach($stockmoves as $stockmove){
					if($productAcc = $this->site->getProductAccByProductId($stockmove['product_id'])){
						$this->db->where_in('acc_tran.account',array($productAcc->stock_acc,$productAcc->cost_acc))
						->where('acc_tran.transaction','Sale')
						->where('acc_tran.transaction_id',$sale_id)
						->delete('acc_tran');
					}
				}
				
			}
			if($data['rental_id'] > 0){
				$rental = $this->getRentalByID($data['rental_id']);
                $room = $this->getRentalRoomByID($data['product_id']);
				if($rental && $rental->frequency > 0){
					if($rental->frequency == 30){
						$to_date = date("Y-m-d", strtotime("+1 Month",strtotime($data['to_date'])));
					}else{
						$to_date = date("Y-m-d", strtotime("+".(double)$rental->frequency." days",strtotime($data['to_date'])));
					}
					if(isset($rental_status) && $rental_status=='checked_out'){
						$rdata['status'] = 'checked_out';
						$rdata['from_date'] = $data['from_date'];
						$rdata['to_date'] = $data['to_date'];
                        $rdata['checked_out'] = $data['to_date'];

					}else{
						$rdata['from_date'] = $data['to_date'];
						$rdata['to_date'] = $to_date;
					}
					$this->db->where("id", $data['rental_id'])->update("rentals", $rdata);
                    //$this->db->where("id", $data['rental_id'])->update("rental_items", $rdata);
                    $this->db->where("id", $data['rental_id'])->update("rentals", $rdata);
                    $this->db->update('rental_rooms', array('housekeeping_status' => 'dirty','availability' => 'free'),array('rental_id' => $data['rental_id']));
				}

				if ($rental_deposit>0) {
					$currencies =  $this->site->getAllCurrencies();
					$payment_reference_no = $this->site->getReference('pay',$data['biller_id']);
					// Deduct Deposit Amount
					$deduct_deposit_amount = $rental_deposit>$data['grand_total']?$data['grand_total']:$rental_deposit;
					if(!empty($currencies)){
						foreach($currencies as $currency_row){
							if($currency_row->code=='USD'){
								$current_amount = $deduct_deposit_amount;
							}else{
								$current_amount = 0;
							}
							$deposit_currency[] = array(
								"amount" => $current_amount,
								"currency" => $currency_row->code,
								"rate" => $currency_row->rate,
							);
						}
					}
					$deduct_deposit = array(
                        'date' => $data['date'],
                        'reference_no' => $payment_reference_no,
                        'amount' => $this->cus->formatDecimalRaw($deduct_deposit_amount),
						'discount' => null,
                        'paid_by' => 'deposit',
                        'cheque_no' => null,
                        'cc_no' => null,
                        'cc_holder' => null,
                        'cc_month' => null,
                        'cc_year' => null,
                        'cc_type' => null,
                        'created_by' => $this->session->userdata('user_id'),
                        'note' => $this->input->post('payment_note'),
						'transaction' => 'RentalDeposit',
						'transaction_id' => $data['rental_id'],
						'type' => 'sent',
						'sale_id' => null,
						'currencies' => json_encode($deposit_currency)
					);
					if($deduct_deposit){
						$this->db->insert('payments', $deduct_deposit);
					}
					// Paid Deposit Amount
					$deposit = array(
                        'date' => $data['date'],
                        'reference_no' => $payment_reference_no,
                        'amount' => $this->cus->formatDecimalRaw($deduct_deposit_amount),
						'discount' => null,
                        'paid_by' => 'deposit',
                        'cheque_no' => null,
                        'cc_no' => null,
                        'cc_holder' => null,
                        'cc_month' => null,
                        'cc_year' => null,
                        'cc_type' => null,
                        'created_by' => $this->session->userdata('user_id'),
                        'note' => $this->input->post('payment_note'),
						'type' => 'received',
						'sale_id' => $sale_id,
						'currencies' => json_encode($deposit_currency)
					);
					if($deposit){
						$this->db->insert('payments', $deposit);
						$this->site->syncSalePayments($sale_id);
					}
				}
				
			}
			if (!empty($si_return)) {
				$this->site->syncSalePayments($sale_id);
                foreach ($si_return as $return_item) {
					if($this->Settings->accounting_method == '0'){
						$this->site->updateFifoCost($return_item['product_id']);
					}else if($this->Settings->accounting_method == '1'){
						$this->site->updateLifoCost($return_item['product_id']);
					}
                }
				$total_return = $data['grand_total'];
                $this->db->update('sales', array('return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'],'return_sale_total' => $total_return, 'return_id' => $sale_id), array('id' => $data['sale_id']));
				
				if($this->Settings->installment==1){
					$installment = $this->getInstallmentBySaleID($data['sale_id']);
					if($installment->id > 0){
						$this->db->where("sale_id", $data['sale_id'])->update("installments", array("status"=>"returned"));
					}
				}
			}
            $this->cus->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
			if($data['consignment_id'] > 0){
				$this->site->syncConsignment($data['consignment_id']);
			}
			if($data['repair_id'] > 0){
				$this->db->where("id", (int)$data['repair_id'])->update("repairs", array("status"=>"sent"));
				$repair = $this->db->where("id", (int)$data['repair_id'])->get("repair_items");
				if($repair->num_rows()>0){
					foreach($repair->result() as $repair_row){
						$sale_margin = (double)$repair_row->unit_price - (double)$repair_row->cost;
						$this->cus->update_award_points($sale_margin, 0, (int)$repair_row->technician_id);
					}
				}
			}
			
			if($this->config->item('fuel') && $data['fuel_customers']){
				$fuel_customers = json_decode($data['fuel_customers']);
				$this->db->where_in("id",$fuel_customers)->update("fuel_customers",array("status"=>"completed"));
				foreach($fuel_customers as $fuel_customer){
					$this->site->deleteStockmoves('FuelCustomer',$fuel_customer);
					$this->site->deleteAccTran('FuelCustomer',$fuel_customer);
				}
			}
			return $sale_id;
        }
        return false;
    }

    public function updateSale($id = false, $data = false, $payment = array(), $items = array(), $biller_id =null, $stockmoves = false, $accTrans = array())
    {
		$ids = array(15);
        if ($this->db->update('sales', $data, array('id' => $id)) &&
            $this->db->delete('sale_items', array('sale_id' => $id))) {
			$this->site->deleteStockmoves('Sale',$id);
			$this->site->deleteAccTran('Sale',$id);
			if($accTrans){
				$this->db->insert_batch('acc_tran', $accTrans);
			}
            foreach ($items as $item) {
                $item['sale_id'] = $id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
            }

			if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay',$biller_id);
                }
                $payment['sale_id'] = $id;
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {
                    if ($payment['paid_by'] == 'deposit') {
						$this->sysnceCustomerDeposit($data['customer_id']);
                    }
					if($payment['amount'] > 0){
						$this->db->insert('payments', $payment);
					}
                }
                //$this->site->syncSalePayments($id);
            }

            if($data['rental_id'] > 0){
                $rental = $this->getRentalByID($data['rental_id']);
                if($rental && $rental->frequency > 0){
                    if($rental->frequency == 30){
                        $to_date = date("Y-m-d", strtotime("+1 Month",strtotime($data['to_date'])));
                    }else{
                        $to_date = date("Y-m-d", strtotime("+".(double)$rental->frequency." days",strtotime($data['to_date'])));
                    }
                    if(isset($rental_status) && $rental_status=='checked_out'){
                        $rdata['status'] = 'checked_out';
                        $rdata['from_date'] = $data['from_date'];
                        $rdata['to_date'] = $data['to_date'];
                        $rdata['checked_out'] = $data['to_date'];
                    }else{
                        $rdata['from_date'] = $data['to_date'];
                        $rdata['to_date'] = $to_date;
                    }
                    $this->db->where("id", $data['rental_id'])->update("rentals", $rdata);
                }
            }
			if($data['stock_deduction'] != 0 && $stockmoves){
				foreach($stockmoves as $stockmove){
					if ($stockmove['product_type'] != 'combo') {
						$stockmove['transaction_id'] = $id;
						$this->db->insert('stockmoves', $stockmove);
					}
				}
			}else if($stockmoves && $this->Settings->accounting == 1){
				foreach($stockmoves as $stockmove){
					if($productAcc = $this->site->getProductAccByProductId($stockmove['product_id'])){
						$this->db->where_in('acc_tran.account',array($productAcc->stock_acc,$productAcc->cost_acc))
						->where('acc_tran.transaction','Sale')
						->where('acc_tran.transaction_id',$id)
						->delete('acc_tran');
					}
				}
				
			}
            $this->site->syncSalePayments($id);
			$this->synDeliveries($id);
            $this->cus->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);	
			if($data['consignment_id'] > 0){
				$this->site->syncConsignment($data['consignment_id']);
			}
			
            return true;

        }
        return false;
    }

    public function updateStatus($id = false, $status = false, $note = false)
    {

        $sale = $this->getInvoiceByID($id);
        $items = $this->getAllInvoiceItems($id);
        $cost = array();
        if ($status == 'completed' && $status != $sale->sale_status) {
            foreach ($items as $item) {
                $items_array[] = (array) $item;
            }
            $cost = $this->site->costing($items_array);
        }

        if ($this->db->update('sales', array('sale_status' => $status, 'note' => $note), array('id' => $id))) {

            if ($status == 'completed' && $status != $sale->sale_status) {

                foreach ($items as $item) {
                    $item = (array) $item;
                    if ($this->site->getProductByID($item['product_id'])) {
                        $item_costs = $this->site->item_costing($item);
                        foreach ($item_costs as $item_cost) {
                            $item_cost['sale_item_id'] = $item['id'];
                            $item_cost['sale_id'] = $id;
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        }
                    }
                }

            } elseif ($status != 'completed' && $sale->sale_status == 'completed') {
                $this->resetSaleActions($id);
            }

            if (!empty($cost)) { $this->site->syncPurchaseItems($cost); }
            return true;
        }
        return false;
    }
	
	public function getPaymentsBySale($sale_id = false)
    {
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getSaleReturns($sale_id = false)
    {
        $q = $this->db->get_where('sales', array('sale_id' => $sale_id,'sale_status' => 'returned'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
	public function deleteSale($id = false)
    {
		if($id && $id > 0){
			$sale = $this->getInvoiceByID($id);
			if ($this->db->delete('sales', array('id' => $id))) {
				$payments = $this->getPaymentsBySale($id);
				$sales_returns = $this->getSaleReturns($id);
				$this->db->delete('sales', array('sale_id' => $id));
				$this->db->delete('payments', array('sale_id' => $id));
				$this->site->deleteStockmoves('Sale',$id);
				$this->site->deleteAccTran('Sale',$id);
				$this->site->deleteAccTran('SaleReturn',$id);
				$this->db->update('sales', array('return_sale_ref' => '', 'surcharge' => 0,'return_sale_total' => 0, 'return_id' => ''), array('return_id' => $id));

				if($this->Settings->accounting_method == '0'){
					$items = $this->getAllInvoiceItems($id);
					foreach($items as $item){
						$this->site->updateFifoCost($item->product_id);
					}
				}else if($this->Settings->accounting_method == '1'){
					$items = $this->getAllInvoiceItems($id);
					foreach($items as $item){
						$this->site->updateLifoCost($item->product_id);
					}
				}

				$this->db->delete('sale_items', array('sale_id' => $id));
				if($payments){
					foreach($payments as $payment){
						if ($payment->paid_by == 'gift_card') {
							$gc = $this->site->getGiftCardByNO($payment->cc_no);
							$this->db->update('gift_cards', array('balance' => ($gc->balance+$payment->amount)), array('card_no' => $payment->cc_no));
						} elseif ($payment->paid_by == 'deposit') {
							$this->sysnceCustomerDeposit($sale->customer_id);
						}
						$this->site->deleteAccTran('Payment',$payment->id);
						if($payment->sale_id > 0 && $payment->sale_order_id > 0){
							$this->site->deleteAccTran('Sale Order Deposit',$payment->id);
						}
					}
				}
				
				if($sales_returns){
					foreach($sales_returns as $sales_return){
						$payment_returns = $this->getPaymentsBySale($sales_return->id);
						$this->site->deleteAccTran('SaleReturn',$sales_return->id);
						$this->site->deleteStockmoves('Sale',$sales_return->id);
						$this->db->delete('payments', array('sale_id' => $sales_return->id));
						if($payment_returns){
							foreach($payment_returns as $payment_return){
								$this->site->deleteAccTran('Payment',$payment_return->id);
							}
						}
					}
				}
				
				if($this->Settings->installment==1){
					$installment = $this->getInstallmentBySaleID($sale->sale_id);
					if($installment && $installment->sale_id > 0){
						$this->db->where("sale_id", $installment->sale_id)->update("installments", array("status"=>"active"));
					}
				}
				
				if($sale->consignment_id > 0){
					$this->site->syncConsignment($sale->consignment_id);
				}
				if($this->config->item('fuel') && $sale->fuel_customers){
					$stockmoves = false;
					$accTrans = false;
					$fuel_customers = json_decode($sale->fuel_customers);
					if($fuel_customers){	
						$this->db->where_in("id",$fuel_customers)->update("fuel_customers",array("status"=>"pending"));
						$fuel_customers = $this->getFuelCustomerByIDs($fuel_customers);
						if($fuel_customers){
							foreach($fuel_customers as $fuel_customer){
								$fuel_customer_items = $this->getFuelCustomerItems($fuel_customer->id);
								if($fuel_customer_items){
									foreach($fuel_customer_items as $fuel_customer_item){
										$product_details = $this->site->getProductByID($fuel_customer_item->product_id);
										$unit = $this->site->getProductUnit($product_details->id, $product_details->unit);
										$stockmoves[] = array(
															'transaction' => 'FuelCustomer',
															'transaction_id' => $fuel_customer->id,
															'reference_no' => $fuel_customer->reference,
															'product_id' => $product_details->id,
															'product_code' => $product_details->code,
															'product_type' => $product_details->type,
															'quantity' => $fuel_customer_item->quantity * (-1),
															'unit_quantity' => $unit->unit_qty,
															'unit_code' => $unit->code,
															'unit_id' => $product_details->unit,
															'warehouse_id' => $fuel_customer->warehouse_id,
															'date' => $fuel_customer->date,
															'real_unit_cost' => $product_details->cost,
															'user_id' => $fuel_customer->created_by,
														);
										//========accounting=========//
											$productAcc = $this->site->getProductAccByProductId($product_details->id);
											if($this->Settings->accounting == 1){		
												$accTrans[] = array(
													'transaction' => 'FuelCustomer',
													'transaction_id' => $fuel_customer->id,
													'transaction_date' => $fuel_customer->date,
													'reference' => $fuel_customer->reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($product_details->cost * $fuel_customer_item->quantity),
													'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$fuel_customer_item->quantity.'#'.'Cost: '.$product_details->cost,
													'description' => $fuel_customer->note,
													'biller_id' => $fuel_customer->biller_id,
													'user_id' => $fuel_customer->created_by,
													'customer_id' => $fuel_customer->customer_id,
												);
												$accTrans[] = array(
													'transaction' => 'FuelCustomer',
													'transaction_id' => $fuel_customer->id,
													'transaction_date' => $fuel_customer->date,
													'reference' => $fuel_customer->reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($product_details->cost * $fuel_customer_item->quantity),
													'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$fuel_customer_item->quantity.'#'.'Cost: '.$product_details->cost,
													'description' => $fuel_customer->note,
													'biller_id' => $fuel_customer->biller_id,
													'user_id' => $fuel_customer->created_by,
													'customer_id' => $fuel_customer->customer_id,
												);
											}
										//============end accounting=======//
									}
								}
							}
						}
						
					}
					if($stockmoves){
						$this->db->insert_batch("stockmoves",$stockmoves);
					}
					if($accTrans){
						$this->db->insert_batch("acc_tran",$accTrans);
					}
				}
				return true;
			}
		}
        return FALSE;
    }

    public function resetSaleActions($id = false, $return_id = NULL, $check_return = NULL)
    {
        if ($sale = $this->getInvoiceByID($id)) {

			 /*if ($check_return && $sale->sale_status == 'returned') {
				$this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
            }*/

			$this->db->where("return_id",$id)->update("sales", array("return_id"=>0));

            if ($sale->sale_status == 'completed' || $sale->sale_status == 'returned') {
                $items = $this->getAllInvoiceItems($id);
                foreach ($items as $item) {
                    if ($item->product_type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if($combo_item->type == 'standard') {
                                $qty = ($item->quantity*$combo_item->qty);
                                $this->updatePurchaseItem(NULL, $qty, NULL, $combo_item->id, $item->warehouse_id);
                            }
                        }
                    } else {
                        $option_id = isset($item->option_id) && !empty($item->option_id) ? $item->option_id : NULL;
                        $this->updatePurchaseItem(NULL, $item->quantity, $item->id, $item->product_id, $item->warehouse_id, $option_id);
                    }
                }
                if ($sale->return_id || $return_id) {
                    $rid = $return_id ? $return_id : $sale->return_id;
                    $returned_items = $this->getAllInvoiceItems(FALSE, $rid);
                    foreach ($returned_items as $item) {

                        if ($item->product_type == 'combo') {
                            $combo_items = $this->site->getProductComboItems($item->product_id, $item->warehouse_id);
                            foreach ($combo_items as $combo_item) {
                                if($combo_item->type == 'standard') {
                                    $qty = ($item->quantity*$combo_item->qty);
                                    $this->updatePurchaseItem(NULL, $qty, NULL, $combo_item->id, $item->warehouse_id);
                                }
                            }
                        } else {
                            $option_id = isset($item->option_id) && !empty($item->option_id) ? $item->option_id : NULL;
                            $this->updatePurchaseItem(NULL, $item->quantity, $item->id, $item->product_id, $item->warehouse_id, $option_id);
                        }

                    }
                }
                $this->cus->update_award_points($sale->grand_total, $sale->customer_id, $sale->created_by, TRUE);
                return $items;
            }
        }
    }

    public function updatePurchaseItem($id = false, $qty = false, $sale_item_id = false, $product_id = NULL, $warehouse_id = NULL, $option_id = NULL)
    {
        if ($id) {
            if($pi = $this->getPurchaseItemByID($id)) {
                $pr = $this->site->getProductByID($pi->product_id);
                if ($pr->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($pr->id, $pi->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        if($combo_item->type == 'standard') {
                            $cpi = $this->site->getPurchasedItem(array('product_id' => $combo_item->id, 'warehouse_id' => $pi->warehouse_id, 'option_id' => NULL));
                            $bln = $pi->quantity_balance + ($qty*$combo_item->qty);
                            $this->db->update('purchase_items', array('quantity_balance' => $bln), array('id' => $combo_item->id));
                        }
                    }
                } else {
                    $bln = $pi->quantity_balance + $qty;
                    $this->db->update('purchase_items', array('quantity_balance' => $bln), array('id' => $id));
                }
            }
        } else {
            if ($sale_item_id) {
                if ($sale_item = $this->getSaleItemByID($sale_item_id)) {
                    $option_id = isset($sale_item->option_id) && !empty($sale_item->option_id) ? $sale_item->option_id : NULL;
                    $clause = array('product_id' => $sale_item->product_id, 'warehouse_id' => $sale_item->warehouse_id, 'option_id' => $option_id);
                    if ($pi = $this->site->getPurchasedItem($clause)) {
                        $quantity_balance = $pi->quantity_balance+$qty;
                        $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                    } else {
                        $clause['purchase_id'] = NULL;
                        $clause['transfer_id'] = NULL;
                        $clause['quantity'] = 0;
                        $clause['quantity_balance'] = $qty;
                        $this->db->insert('purchase_items', $clause);
                    }
                }
            } else {
                if ($product_id && $warehouse_id) {
                    $pr = $this->site->getProductByID($product_id);
                    $clause = array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
                    if ($pr->type == 'standard') {
                        if ($pi = $this->site->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance+$qty;
                            $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                        } else {
                            $clause['purchase_id'] = NULL;
                            $clause['transfer_id'] = NULL;
                            $clause['quantity'] = 0;
                            $clause['quantity_balance'] = $qty;
                            $this->db->insert('purchase_items', $clause);
                        }
                    } elseif ($pr->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($pr->id, $warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            $clause = array('product_id' => $combo_item->id, 'warehouse_id' => $warehouse_id, 'option_id' => NULL);
                            if($combo_item->type == 'standard') {
                                if ($pi = $this->site->getPurchasedItem($clause)) {
                                    $quantity_balance = $pi->quantity_balance+($qty*$combo_item->qty);
                                    $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), $clause);
                                } else {
                                    $clause['transfer_id'] = NULL;
                                    $clause['purchase_id'] = NULL;
                                    $clause['quantity'] = 0;
                                    $clause['quantity_balance'] = $qty;
                                    $this->db->insert('purchase_items', $clause);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function getPurchaseItemByID($id = false)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    public function getSaleItemByID($id = false)
    {
        $q = $this->db->get_where('sale_items', array('id' => $id), 1);
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

    public function addDelivery($data = array())
    {
        if ($this->db->insert('deliveries', $data)) {

            return true;
        }
        return false;
    }

    public function updateDelivery($id = false, $data = array())
    {
        if ($this->db->update('deliveries', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getDeliveryByID($id = false)
    {
        $q = $this->db->get_where('deliveries', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getDeliveryBySaleID($sale_id = false)
    {
        $q = $this->db->get_where('deliveries', array('sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteDelivery($id = false)
    {
		if($id && $id > 0){	
			if ($this->db->delete('deliveries', array('id' => $id))) {
				return true;
			}
		}
        return FALSE;
    }

    public function getInvoicePayments($sale_id = false, $down_payment_detail_id = false)
    {
        $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
		$this->db->order_by('id', 'desc');
		if($down_payment_detail_id){
			$this->db->where('down_payment_detail_id', $down_payment_detail_id);
		}
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

	public function getPaymentBySaleID($sale_id = false)
    {
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as cash_account");
		$this->db->select('IFNULL(sum(amount),0) AS paid, IFNULL(sum(interest_paid),0) AS interest_paid,IFNULL(sum(discount),0) AS discount');
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$this->db->group_by('sale_id');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
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

    public function getPaymentsForSale($sale_id = false)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function addMultiPayment($data = false, $accTranPayments = array())
	{
		if($data){
			foreach($data as $row){
				$sale = $this->getInvoiceByID($row['sale_id']);
				$customer_id = $sale->customer_id;
				$this->db->insert('payments',$row);
				$payment_id = $this->db->insert_id();
				$this->site->syncSalePayments($row['sale_id']);
				if ($row['paid_by'] == 'gift_card') {
					$gc = $this->site->getGiftCardByNO($row['cc_no']);
					$this->db->update('gift_cards', array('balance' => ($gc->balance - $row['amount'])), array('card_no' => $row['cc_no']));
				} elseif ($customer_id && $row['paid_by'] == 'deposit') {
					$this->sysnceCustomerDeposit($customer_id);
				}
				$accTrans = $accTranPayments[$row['sale_id']];
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

    public function addPayment($data = array(), $customer_id = null, $accTranPayments = array())
    {
        if ($this->db->insert('payments', $data)) {
			$payment_id = $this->db->insert_id();
			if($accTranPayments){
				foreach($accTranPayments as $accTranPayment){
					$accTranPayment['transaction_id']= $payment_id;
					$this->db->insert('acc_tran', $accTranPayment);
				}
			}
            $this->site->syncSalePayments($data['sale_id']);
            if($data['down_payment_detail_id'] > 0){
				$this->syncSeparatePaymentDetails($data['down_payment_detail_id']);
			}
			if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
				$this->sysnceCustomerDeposit($customer_id);
            }
            return true;
        }
        return false;
    }
	
	public function addPaymentMulti($datas = array())
    {
        if ($this->db->insert_batch('payments', $datas)) {
			foreach($datas as $data){
				$this->site->syncSalePayments($data['sale_id']);
			}
            return true;
        }
        return false;
    }

    public function updatePayment($id = false, $data = array(), $customer_id = null, $accTranPayments = array())
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $this->site->syncSalePayments($data['sale_id']);
			$this->site->deleteAccTran('Payment',$id);
			if($opay->down_payment_detail_id > 0){
				$this->syncSeparatePaymentDetails($opay->down_payment_detail_id);
			}
			if($accTranPayments){
				$this->db->insert_batch('acc_tran', $accTranPayments);
			}
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit' && $opay->transaction != 'SO Deposit') {
                if (!$customer_id) {
                    $sale = $this->getInvoiceByID($opay->sale_id);
                    $customer_id = $sale->customer_id;
                }
				$this->sysnceCustomerDeposit($customer_id);
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit' && $opay->transaction != 'SO Deposit') {
				$this->sysnceCustomerDeposit($customer_id);
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
				$this->site->syncSalePayments($opay->sale_id);
				$this->site->deleteAccTran('Payment',$id);
				if($opay->down_payment_detail_id > 0){
					$this->syncSeparatePaymentDetails($opay->down_payment_detail_id);
				}
				if ($opay->paid_by == 'gift_card') {
					$gc = $this->site->getGiftCardByNO($opay->cc_no);
					$this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
				} elseif ($opay->paid_by == 'deposit' && $opay->transaction != 'SO Deposit') {
					$sale = $this->getInvoiceByID($opay->sale_id);
					$this->sysnceCustomerDeposit($sale->customer_id);
				}
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

    /* ----------------- Gift Cards --------------------- */

    public function addGiftCard($data = array(), $ca_data = array(), $sa_data = array())
    {
        if ($this->db->insert('gift_cards', $data)) {
            if (!empty($ca_data)) {
                $this->db->update('companies', array('award_points' => $ca_data['points']), array('id' => $ca_data['customer']));
            } elseif (!empty($sa_data)) {
                $this->db->update('users', array('award_points' => $sa_data['points']), array('id' => $sa_data['user']));
            }
            return true;
        }
        return false;
    }

    public function updateGiftCard($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('gift_cards', $data)) {
            return true;
        }
        return false;
    }

    public function deleteGiftCard($id = false)
    {
		if($id && $id > 0){	
			if ($this->db->delete('gift_cards', array('id' => $id))) {
				return true;
			}
		}
        return FALSE;
    }

    public function getPaypalSettings()
    {
        $q = $this->db->get_where('paypal', array('id' => 1));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get_where('skrill', array('id' => 1));
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getStaff()
    {
        if (!$this->Owner) {
            $this->db->where('group_id !=', 1);
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
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

    public function getTaxRateByName($name = false)
    {
        $q = $this->db->get_where('tax_rates', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    public function topupGiftCard($data = array(), $card_data = NULL)
    {
        if ($this->db->insert('gift_card_topups', $data)) {
            $this->db->update('gift_cards', $card_data, array('id' => $data['card_id']));
            return true;
        }
        return false;
    }

    public function getAllGCTopups($card_id = false)
    {
        $this->db->select("{$this->db->dbprefix('gift_card_topups')}.*, {$this->db->dbprefix('users')}.first_name, {$this->db->dbprefix('users')}.last_name, {$this->db->dbprefix('users')}.email")
        ->join('users', 'users.id=gift_card_topups.created_by', 'left')
        ->order_by('id', 'desc')->limit(10);
        $q = $this->db->get_where('gift_card_topups', array('card_id' => $card_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
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

	public function getSaleByID($id = NULL)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAssignSaleById($id = NULL)
	{
		$q = $this->db->get_where('assign_sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

	public function getAllAssignItemsByID($id = NULL)
	{
		$result = $this->db->select("sales.*")
					       ->from("assign_sales")
						   ->join("assign_sale_items","assign_sales.id=assign_id","left")
						   ->join("sales","sales.id=assign_sale_items.sale_id","left")
						   ->where('assign_id',$id)
						   ->get()
						   ->result();
		return $result;
	}

	public function getAssignItemBySaleID($id = NULL)
	{
		$result = $this->db->select("assign_sales.*")
					       ->from("assign_sales")
						   ->join("assign_sale_items","assign_sales.id=assign_id","left")
						   ->where('sale_id',$id)
						   ->get()
						   ->row();
		return $result;
	}

	public function getProductDigitalItems($pid = false, $warehouse_id = NULL)
    {
        $this->db->select('products.id as id, digital_items.item_code as code, digital_items.quantity as qty, digital_items.option_id as option_id, products.name as name,products.type as type, products.price as price, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=digital_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('digital_items.id');
        if($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('digital_items', array('digital_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getPaymentTermsByID($id = NULL)
	{
        $q = $this->db->where('id', $id)->get('payment_terms');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function getPaymentTermsByDueDay($id = NULL)
	{
        $q = $this->db->where('due_day', $id)->get('payment_terms');
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
        $q = $this->db->where("product_id",$product_id)->get("product_serials");
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
	}

	public function getDepositByCId($cid = false)
	{
		$q = $this->db->get_where('companies', array('companies.id' => $cid));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

    public function getPromotionProductByProId($product_id = false, $customer_id = false)
    {
		$current = date("Y-m-d");
        $this->db->select("products.name as product_name,product_promotion_items.*");
        $this->db->from('product_promotion_items');
        $this->db->join('product_promotions','product_promotions.id = product_promotion_items.promotion_id','inner');
        $this->db->join('products','products.id = product_promotion_items.for_product_id','inner');
        $this->db->join('companies','companies.product_promotion_id = product_promotions.id','inner');
		$this->db->where('"'.$current.'" >= product_promotions.start_date');
		$this->db->where('"'.$current.'" <= product_promotions.end_date');
        $this->db->where('product_promotions.status', 1);
        $this->db->where('main_product_id', $product_id);
		$this->db->where('companies.id', $customer_id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

	public function getReturnItemStockmoveBySale($sale_id = false)
	{
		$this->db->select('product_id,sum(quantity) as quantity,real_unit_cost');
		$this->db->group_by('real_unit_cost');
		$this->db->order_by('id', 'asc');
		$this->db->where('transaction = "Sale" AND transaction_id IN (SELECT id FROM cus_sales WHERE sale_id = "'.$sale_id.'")');
		$q = $this->db->get('stockmoves');
		if ($q->num_rows() > 0) {
			foreach($q->result_array() as $row){
				$data[] = $row;
			}
			return $data;
		}else{
			return false;
		}
	}

	public function getFifoItems($transaction = false,$transaction_id = false,$product_id = false,$return_quantity = false,$tmp_stockmoves = false)
	{
		$this->db->select('product_id,sum(quantity) as quantity,real_unit_cost');
		$this->db->group_by('real_unit_cost');
		$this->db->order_by('id', 'asc');
		$q = $this->db->get_where("stockmoves", array('transaction_id' => $transaction_id,'transaction' => $transaction,'product_id' => $product_id));

		if($tmp_stockmoves){
			$tmp_quantitys = array();
			foreach($tmp_stockmoves as $tmp_stockmove){
				if($tmp_stockmove['product_id']==$product_id){
					$tmp_quantity = $tmp_quantitys[$tmp_stockmove['real_unit_cost']] + $tmp_stockmove['quantity'];
					$tmp_quantitys[$tmp_stockmove['real_unit_cost']] = $tmp_quantity;
				}
			}
		}

		$return_quantity = abs($return_quantity);
		if ($q->num_rows() > 0) {
			 $array_result = $q->result_array();
			 foreach($array_result as $data){
				if($data['product_id']==$product_id){
					if($tmp_quantitys[$data['real_unit_cost']]){
						$sale_quantity = abs($data['quantity']) - abs($tmp_quantitys[$data['real_unit_cost']]);
					}else{
						$sale_quantity = abs($data['quantity']);
					}

					if($sale_quantity >= $return_quantity){
						$item_cost[] = array('cost'=>$data['real_unit_cost'],'quantity'=>$return_quantity);
						break;
					}else{
						$item_cost[] = array('cost'=>$data['real_unit_cost'],'quantity'=>$sale_quantity);
						$return_quantity = $return_quantity - $sale_quantity;
					}
				}
			}
            return $item_cost;
        }
        return FALSE;
	}

	public function synSaleStatus($quote_id = false, $sale_order_id = false)
	{
		if ($quote_id && $sale_order_id != 1) {
			$this->db->update('quotes', array('status' => 'completed'), array('id' => $quote_id));
		}else if ($quote_id && $sale_order_id == 1){
			$this->db->update('sale_orders', array('status' => 'completed'), array('id' => $quote_id));
		}
	}

	public function getCreditLimit($customer_id = false, $credit_day = false)
	{
        $where = "";
        if($credit_day){
            $where = " AND DATE(".$this->db->dbprefix('sales').".date) <= '".date('Y-m-d')."' - INTERVAL ".$credit_day." DAY";
        }
		$q = $this->db->query("SELECT
									SUM(ROUND((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.")) as balance
								FROM
									".$this->db->dbprefix('sales')."
								LEFT JOIN (
									SELECT
										sale_id,
										IFNULL(sum(amount), 0) AS paid,
										IFNULL(sum(discount), 0) AS discount
									FROM
										cus_payments
									GROUP BY
										sale_id
								) AS cus_payments ON cus_payments.sale_id = ".$this->db->dbprefix('sales').".id
								LEFT JOIN (
									SELECT
										sum(abs(grand_total)) AS total_return,
										sum(paid) AS total_return_paid,
										sale_id
									FROM
										".$this->db->dbprefix('sales')."
									WHERE
										".$this->db->dbprefix('sales').".sale_id > 0
									AND ".$this->db->dbprefix('sales').".sale_status = 'returned'
									GROUP BY
										".$this->db->dbprefix('sales').".sale_id
								) AS cus_return ON cus_return.sale_id = ".$this->db->dbprefix('sales').".id
								WHERE
									customer_id = '".$customer_id."'
								AND (".$this->db->dbprefix('sales').".sale_id IS NULL OR ".$this->db->dbprefix('sales').".sale_id = 0)
								".$where."
								");
			if($q->num_rows() > 0){
				return $q->row();
			}
			return false;

	}

	public function getSalesByBillers($ids = array())
	{
        $q = $this->db->select("biller_id, count(biller_id) as counts")->where_in('id',$ids)->group_by('biller_id')->get("sales");
        return $q;
	}
/*======================================getsale*/
  public function getSales(){
		$this->db->select('biller_id');
		$this->db->limit(1);
		$q = $this->db->get('sales');
		if($q->num_rows()> 0){
			return $q->row();
		}
		return false;
	}

	public function getPaymentsByRef($ref = false,$date = false)
	{
		$this->db->select('payments.*,payments.date AS payment_date,payments.reference_no AS payment_ref,payments.amount AS payment_amount,payments.discount AS payment_discount,sales.date AS sale_date,sales.reference_no AS sale_ref')
		->join('sales','sales.id = payments.sale_id','inner')
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
	
	public function getCustomerPrice($product_id = false,$customer_id = false)
	{
		$q = $this->db->get_where('customer_product_prices',array('customer_id'=>$customer_id,'product_id'=>$product_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getSaleByReference($reference = false, $type= '', $id = '', $data = false)
    {
		if($type=='sale'){
			$this->db->where('pos !=', 1);
			$this->db->where('sale_status !=', 'draft');
			$this->db->where('sale_status !=', 'returned');
		}else if($type=='pos'){
			$this->db->where('pos', 1);
			$this->db->where('sale_status !=', 'draft');
			$this->db->where('sale_status !=', 'returned');
		}else{
			$this->db->where('sale_status', $type);
		}
		
		if($id){
			$this->db->where('id !=', $id);
		}
		if($reference){
			$this->db->where('reference_no',$reference);
		}
		if($date){
			$this->db->where("year(date)","year(".$date.")");
		}
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function synDeliveries($sale_id = false)
	{
		if($sale_id){
			$q = $this->db->join('delivery_items','delivery_items.delivery_id=deliveries.id','left')->where("sale_id",$sale_id)->get("deliveries");
			$delivered_quantity = 0;
			foreach($q->result() as $delivery){
				$delivered_quantity += $delivery->quantity;
			}
		
			$s = $this->db->where(array("sale_id"=>$sale_id))->get("sale_items");
			$saled_quantity = 0;
			foreach($s->result() as $sale){
				$saled_quantity += $sale->quantity;
			}
			if($delivered_quantity >= $saled_quantity){
				$this->db->update("sales", array("delivery_status" => "completed"), array("id" => $sale_id));
			}else if($delivered_quantity < $saled_quantity && $delivered_quantity > 0){
				$this->db->update("sales", array("delivery_status" => "partial"), array("id" => $sale_id));
			}else{
				$this->db->update("sales", array("delivery_status" => "pending"), array("id" => $sale_id));
			}
		}
	}
    
    public function getProductFormulation()
	{
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

	public function getProductByCategory($category_id = false)
	{
		$this->db->where('products.inactive !=',1);
		if ($this->Settings->overselling != 1) {
			$this->db->where('warehouses_products.quantity >',0);
		}

		$this->db->select('products.*,warehouses_products.quantity')
				->join('warehouses_products','warehouses_products.product_id = products.id','left')
				->group_by('products.id');
		$q = $this->db->get_where('products',array('category_id'=>$category_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	// Down Payment 
	
	public function addSeparatePayment($data = array(), $details = array())
	{
		 if($this->db->insert('down_payments', $data)){
			$insert_id = $this->db->insert_id();
			foreach($details as $detail){
				$detail['down_payment_id'] = $insert_id;
				$this->db->insert('down_payment_details', $detail);
			}
			return true;
		} 
		return false;
	}
	
	public function getSeparatePaymentByID($id = false)
	{
		$q = $this->db->where("id", $id)->get("down_payments");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function inactiveSeparatePaymentByID($id = false)
	{
		if($this->db->where("id", $id)->update("down_payments", array('status'=>'inactive'))){
			return true;
		}
		return false;
	}
	
	public function getAllSeparatePaymentBySaleID($sale_id =false)
	{
		$q = $this->db->where("sale_id", $sale_id)->get("down_payments");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getSeparatePaymentDetailByID($id = false)
	{
		$q = $this->db->where("id", $id)->get("down_payment_details");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function syncSeparatePaymentDetails($id = false) 
	{
        $spayment = $this->getSeparatePaymentDetailByID($id);
        $payments = $this->getPaymentBySeparatePaymentDetailID($id);
        $paid = 0;
        $grand_total = $spayment->payment;
        foreach ($payments as $payment) {
			$paid += $payment->amount + $payment->discount;
        }
        $status = $paid == 0 ? 'pending' : $spayment->status;
        if ($this->cus->formatDecimal($grand_total) == $this->cus->formatDecimal($paid)) {
            $status = 'paid';
        } elseif ($paid != 0) {
            $status = 'partial';
        }else{
			$status = 'pending';
		}
        if ($this->db->update('down_payment_details', array('paid' => $paid, 'payment_status' => $status), array('id' => $spayment->id))) {
            return true;
        }
        return FALSE;
    }
	
	public function getPaymentBySeparatePaymentDetailID($down_payment_detail_id = false)
	{
		$q = $this->db->where('down_payment_detail_id', $down_payment_detail_id)->get('payments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getAllCountSeparatePaymentBySaleID($sale_id = false)
	{
		$q = $this->db->where("sale_id", $sale_id)->where("status","completed")->get("down_payments");
		if($q->num_rows() > 0){
			$nums = $q->num_rows();
			return $nums;
		}
		return false;
	}

	public function getInstallmentByCustomerID($customer_id = false, $sale_id = false)
	{
		if($sale_id){
			$this->db ->where("sale_id <>",$sale_id);
		}
		$q = $this->db->where("customer_id", $customer_id)
					  ->where("status","active")
					  ->get("installments");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
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
		if($id){
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
		}
		
		return false;
	}
	
	public function getTotalPriceAddProductByID($id = false)
	{
		if($id){
			$q = $this->db->query("SELECT sum(product_additional * price) as addition_price FROM ".$this->db->dbprefix('products')." WHERE id IN (".$id.")");
			if($q->num_rows() > 0){
				$row = $q->row();
				return $row;
			}
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
	
	public function getMaxNumberofRoomRent($product_id = false)
	{
		$q = $this->db->select("MAX(end_number) as max_number")
					  ->from("sale_items")
					  ->where("product_id", $product_id)
					  ->get();
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row->max_number;
		}
	}

	
	// ========Fuel Sales=========== //
	
	public function getTankNozzlesByTankID($tank_id = false)
	{
		$q = $this->db->select("
							tank_nozzles.id,
							tank_nozzles.tank_id,
							tank_nozzles.product_id,
							tank_nozzles.nozzle_no,
							tank_nozzles.nozzle_start_no,
							products.code as product_code,
							products.name as product_name,
							products.price as unit_price
	
						")
					  ->where("tank_id", $tank_id)
					  ->join("products",'products.id=product_id','left')
					  ->get("tank_nozzles");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getTankByID($id = false)
	{
		$q = $this->db->where("id", $id)->get("tanks");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function getTankNames($term = false, $warehouse_id = false, $limit = 10)
    {
		if($warehouse_id){
			$this->db->where("tanks.warehouse_id",$warehouse_id);
		}
		$this->db->where("IFNULL(inactive,0)",0);
        $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('tanks');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getFuelSaleQuantityItem($tank_id = false, $nozzle_no = false)
	{
		$q = $this->db->select("MAX(nozzle_end_no) as quantity")
					  ->where("tank_id", $tank_id)
					  ->where("nozzle_id", $nozzle_no)
					  ->get("fuel_sale_items");
		if($q->num_rows()>0){
			$row = $q->row();
			
			return $row;
		}
		return false;
	}
	
	public function addFuelSale($data = false, $items = false, $stockmoves = false, $accTrans = false)
	{
		if($this->db->insert("fuel_sales", $data)){
			$id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item['fuel_sale_id'] = $id;
					$this->db->insert("fuel_sale_items",$item);
					$this->db->query("UPDATE ".$this->db->dbprefix('fuel_customer_items')."
									INNER JOIN ".$this->db->dbprefix('fuel_customers')." ON ".$this->db->dbprefix('fuel_customers').".id = ".$this->db->dbprefix('fuel_customer_items').".fuel_customer_id 
									SET ".$this->db->dbprefix('fuel_customer_items').".fuel_sale_id = ".$id." 
									WHERE
										".$this->db->dbprefix('fuel_customers').".saleman_id = ".$data['saleman_id']."
										AND ".$this->db->dbprefix('fuel_customer_items').".nozzle_id = ".$item['nozzle_id']."
										AND IFNULL(".$this->db->dbprefix('fuel_customer_items').".fuel_sale_id,0) = 0
								");
				}
			}
			if($stockmoves){
				foreach($stockmoves as $stockmove){
					$stockmove['transaction_id'] = $id;
					$this->db->insert("stockmoves",$stockmove);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $id;
					$this->db->insert("acc_tran",$accTran);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateFuelSale($id = false, $data = false, $items = false, $stockmoves = false, $accTrans = false)
	{
		if($this->db->where("id", $id)->update("fuel_sales", $data)){
			$this->site->deleteStockmoves('FuelSale',$id);
			$this->site->deleteAccTran('FuelSale',$id);
			if($items){
				$this->db->where("fuel_sale_id", $id)->delete("fuel_sale_items");
				foreach($items as $item){
					$item['fuel_sale_id'] = $id;
					$this->db->insert("fuel_sale_items",$item);
				}
			}
			if($stockmoves){
				$this->db->insert_batch("stockmoves",$stockmoves);
			}
			if($accTrans){
				$this->db->insert_batch("acc_tran",$accTrans);
			}
			return true;
		}
		return false;
	}
	
	public function getFuelSaleByID($id = false)
	{
		$q = $this->db->where("fuel_sales.id", $id)
					  ->select("fuel_sales.*, SUM(quantity) as quantity")
					  ->join("fuel_sale_items","fuel_sales.id=fuel_sale_items.fuel_sale_id","left")
					  ->get("fuel_sales");
		if($q->num_rows()>0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function getFuelSaleItemsByFuelSaleID($id = false)
	{
		$q = $this->db->select('
						fuel_sale_items.fuel_sale_id,
						fuel_sale_items.tank_id,
						fuel_sale_items.product_id,
						fuel_sale_items.nozzle_id,
						fuel_sale_items.nozzle_no,
						fuel_sale_items.nozzle_start_no,
						fuel_sale_items.nozzle_end_no,
						fuel_sale_items.quantity,
						fuel_sale_items.using_qty,
						fuel_sale_items.customer_qty,
						fuel_sale_items.customer_amount,
						fuel_sale_items.unit_price,
						fuel_sale_items.subtotal,
						tanks.code as tank_code,
						tanks.name as tank_name,
						tanks.code as tank_code,
						tanks.name as tank_name,
						products.code as product_code,
						products.name as product_name')
					  ->where("fuel_sale_id", $id)
					  ->join('tanks','tanks.id=tank_id','left')
					  ->join('products','products.id=product_id','left')
					  ->order_by('nozzle_no, product_name','asc')
					  ->get("fuel_sale_items");
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function deleteFuelSale($id = false)
	{
		if($id && $id > 0){	
			if($this->db->where("id", $id)->delete("fuel_sales")){
				$this->db->where("fuel_sale_id", $id)->delete("fuel_sale_items");
				$this->db->update("fuel_customer_items",array("fuel_sale_id"=>0),array("fuel_sale_id"=>$id));
				$this->site->deleteStockmoves('FuelSale',$id);
				$this->site->deleteAccTran('FuelSale',$id);
				return true;
			}
		}
		return false;
	}
	
	public function getFuelTimes()
	{
		$q = $this->db->get("fuel_times");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getFuelTimeByID($id = null)
	{
		$q = $this->db->where("id", $id)->get("fuel_times");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function addFuelSaleCash($id = null, $data)
	{
		if($this->db->where("id", $id)->update("fuel_sales", $data)){
			return true;
		}
		return false;
	}

	public function getAllFuelSaleItems($id = false)
	{
		$q = $this->db
					  ->select('
							fuel_sale_items.fuel_sale_id,
							fuel_sale_items.tank_id,
							fuel_sale_items.product_id,
							fuel_sale_items.nozzle_id,
							fuel_sale_items.nozzle_no,
							fuel_sale_items.nozzle_start_no,
							fuel_sale_items.nozzle_end_no,
							fuel_sales.warehouse_id,
							SUM(cus_fuel_sale_items.quantity) - IFNULL(cus_sale_items.quantity,0) as quantity,
							SUM(cus_fuel_sale_items.quantity) - IFNULL(cus_sale_items.quantity,0) as unit_quantity,
							products.id as product_id,
							products.code as product_code,
							products.name as product_name,
							products.type as product_type,
							products.sale_unit as product_unit_id,
							fuel_sale_items.unit_price as unit_price,
							fuel_sale_items.unit_price as net_unit_price,
							fuel_sale_items.unit_price as real_unit_price,
							0 as option_id,
							0 as item_discount
						')
					  ->from("fuel_sale_items")
					  ->join('fuel_sales','fuel_sales.id=fuel_sale_id','left')
					  ->join('tanks','tanks.id=tank_id','left')
					  ->join('products','products.id=product_id','left')
					  ->join('(SELECT 
										fuel_sale_id,
										product_id,
										SUM(quantity) as quantity
									FROM cus_sale_items
									LEFT JOIN cus_sales ON cus_sales.id= cus_sale_items.sale_id
										GROUP BY product_id,cus_sales.fuel_sale_id
									) as cus_sale_items','cus_sale_items.product_id=fuel_sale_items.product_id AND cus_sale_items.fuel_sale_id=fuel_sales.id','left')
					  ->where("fuel_sales.id", $id)
					  ->order_by('nozzle_no, product_name','asc')
					  ->group_by('products.id, unit_price')
					  ->having('unit_quantity >', 0)
					  ->get();
					  
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getSaleByFuelID($id = false)
	{
		$q = $this->db->where("fuel_sale_id", $id)->get("sales");
		if($q->num_rows() >0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getSaleItemsByFuelID($id = false)
	{
		$q = $this->db->select("SUM(quantity) as quantity, SUM(subtotal) as subtotal")
					  ->join("sale_items","sale_items.sale_id=sales.id","left")
					  ->where("fuel_sale_id", $id)
					  ->get("sales");
					  
		if($q->num_rows() >0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	
	public function getFuelCustomerByID($id = false){
		$q = $this->db->get_where("fuel_customers",array("id"=>$id));
		if($q->num_rows() >0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	public function getFuelCustomerByIDs($ids = false){
		if($ids){
			$this->db->where_in("id",$ids);
			$q = $this->db->get("fuel_customers");
			if($q->num_rows() >0){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}
		}
		return false;
	}
	
	
	public function getFuelCustomerItemsForSale($fuel_customer_id = false){
		$this->db->select("
							products.id as product_id,
							products.code as product_code,
							products.name as product_name,
							products.type as product_type,
							fuel_customer_items.unit_price as real_unit_price,
							fuel_customer_items.unit_price as net_unit_price,
							fuel_customer_items.unit_price,
							fuel_customer_items.quantity,
							fuel_customer_items.quantity as unit_quantity,
							products.unit as product_unit_id,
							fuel_customers.warehouse_id,
							fuel_customers.date as fuel_customer_date,
							fuel_customers.reference as fuel_customer_reference,
							'' as option_id,
							0 as item_discount
						");
		$this->db->where_in("fuel_customer_items.fuel_customer_id",$fuel_customer_id);
		$this->db->join("products","products.id = fuel_customer_items.product_id","inner");
		$this->db->join("fuel_customers","fuel_customers.id = fuel_customer_items.fuel_customer_id","inner");
		$q = $this->db->get("fuel_customer_items");
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getFuelCustomerItems($fuel_customer_id = false){
		$this->db->select("fuel_customer_items.*,tanks.name as tank_name,tanks.code as tank_code,products.name as product_name, products.code as product_code, customer_trucks.name as truck_name, customer_trucks.plate_number");
		$this->db->join("tanks","tanks.id = fuel_customer_items.tank_id","LEFT");
		$this->db->join("customer_trucks","customer_trucks.id = fuel_customer_items.truck_id","LEFT");
		$this->db->join("products","products.id = fuel_customer_items.product_id","LEFT");
		$this->db->where("fuel_customer_items.fuel_customer_id",$fuel_customer_id);
		$q = $this->db->get("fuel_customer_items");
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function addFuelCustomer($data = false, $items = false, $stockmoves = false, $acctrans = false)
	{
		if($this->db->insert("fuel_customers", $data)){
			$fuel_customer_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["fuel_customer_id"] = $fuel_customer_id;
					$this->db->insert("fuel_customer_items",$item);
				}
			}
			if($stockmoves){
				foreach($stockmoves as $stockmove){
					$stockmove["transaction_id"] = $fuel_customer_id;
					$this->db->insert("stockmoves",$stockmove);
				}
			}
			if($acctrans){
				foreach($acctrans as $acctran){
					$acctran["transaction_id"] = $fuel_customer_id;
					$this->db->insert("acc_tran",$acctran);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateFuelCustomer($id = false,$data = false, $items = false, $stockmoves = false, $acctrans = false){
		if($this->db->update("fuel_customers", $data, array("id"=>$id))){
			$this->db->delete("fuel_customer_items",array("fuel_customer_id"=>$id));
			$this->site->deleteStockmoves('FuelCustomer',$id);
			$this->site->deleteAccTran('FuelCustomer',$id);
			if($items){
				$this->db->insert_batch("fuel_customer_items",$items);
				foreach($items as $item){
					if($item["fuel_sale_id"] > 0){
						$this->db->query("UPDATE ".$this->db->dbprefix('fuel_sale_items')."
									INNER JOIN ".$this->db->dbprefix('fuel_sales')." ON ".$this->db->dbprefix('fuel_sales').".id = ".$this->db->dbprefix('fuel_sale_items').".fuel_sale_id 
									SET ".$this->db->dbprefix('fuel_sale_items').".customer_amount = ".$item["subtotal"]." 
									WHERE
										".$this->db->dbprefix('fuel_sales').".saleman_id = ".$data['saleman_id']."
										AND ".$this->db->dbprefix('fuel_sale_items').".nozzle_id = ".$item['nozzle_id']."
										AND ".$this->db->dbprefix('fuel_sale_items').".fuel_sale_id = ".$item['fuel_sale_id']."
								");
					}
				}
			}
			if($stockmoves){
				$this->db->insert_batch("stockmoves",$stockmoves);
			}
			if($acctrans){
				$this->db->insert_batch("acc_tran",$acctrans);
			}
			return true;
		}
		return false;
	}
	
	public function deleteFuelCustomer($id = false){
		if($id && $id > 0){
			if($this->db->delete("fuel_customers",array("id"=>$id))){
				$this->db->delete("fuel_customer_items",array("fuel_customer_id"=>$id));
				$this->site->deleteStockmoves('FuelCustomer',$id);
				$this->site->deleteAccTran('FuelCustomer',$id);
				return true;
			}
		}
		return false;
	}
	
	
	public function getFuelCustomerNozzleQuantity($salesman_id = false, $nozzle_id = false){
		if($salesman_id){
			$this->db->where("fuel_customers.saleman_id",$salesman_id);
		}
		if($nozzle_id){
			$this->db->where("fuel_customer_items.nozzle_id",$nozzle_id);
		}
		$this->db->where("IFNULL(".$this->db->dbprefix('fuel_customer_items').".fuel_sale_id,0)",0);
		$this->db->select("sum(".$this->db->dbprefix("fuel_customer_items").".quantity) as quantity,sum(".$this->db->dbprefix("fuel_customer_items").".quantity * ".$this->db->dbprefix("fuel_customer_items").".unit_price) as amount");
		$this->db->join("fuel_customers","fuel_customers.id = fuel_customer_items.fuel_customer_id","INNER");
		$q = $this->db->get("fuel_customer_items");
		if($q->num_rows() >0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function getProductCommission($salesman_id = false, $product_id = false)
	{
		if($salesman_id && $product_id){
			$q = $this->db->get_where('product_commissions',array('salesman_id'=>$salesman_id,'product_id'=>$product_id));
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
	
	public function getFuelItemsBySaleman($saleman = false)
	{
		$this->db->select("tank_nozzles.*, tanks.code, tanks.name")
				->join("tanks","tanks.id=tank_id","inner")
				->join("tank_nozzle_salesmans","tank_nozzle_salesmans.tank_id = tank_nozzles.tank_id AND tank_nozzle_salesmans.nozzle_id = tank_nozzles.id","inner")
				->where("tank_nozzle_salesmans.saleman_id",$saleman)
				->where("IFNULL(".$this->db->dbprefix('tanks').".inactive,0)",0)
				->group_by("tank_nozzles.id")
				->order_by("tanks.name,tank_nozzles.nozzle_no");
		$q = $this->db->get("tank_nozzles");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getConsignmentByConsignID($consignment_id = false)
	{
		$q = $this->db->get_where('consignments',array('consignment_id'=>$consignment_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getConsignmentByID($id = false)
    {
        $q = $this->db->get_where('consignments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getConsigmentItems($consignment_id = false)
    {
        $this->db->order_by('id', 'desc');
		$this->db->select("consignment_items.*, (IFNULL(quantity,0) - IFNULL(return_qty,0) - IFNULL(sale_qty,0)) as quantity");
		$this->db->where("(IFNULL(quantity,0) - IFNULL(return_qty,0) - IFNULL(sale_qty,0)) > ", 0);
        $q = $this->db->get_where('consignment_items', array('consignment_id' => $consignment_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getProductExpiredWithSub($pid = false, $warehouse_id = false, $transaction = false, $transaction_id = false)
	{
		if($transaction=='Consignment'){
			$consignments = $this->getConsignmentByConsignID($transaction_id);
			if($consignments){
				foreach($consignments as $consignment){
					$this->db->where('(transaction != "'.$transaction.'" OR (transaction = "'.$transaction.'" AND transaction_id != '.$consignment->id.'))');
				}
			}
		}
		
		if($warehouse_id){
			$this->db->where('warehouse_id',$warehouse_id);
		}
		if($transaction && $transaction_id){
			$this->db->where('(transaction != "'.$transaction.'" OR (transaction = "'.$transaction.'" AND transaction_id != '.$transaction_id.'))');
		}
		
		$this->db->select('sum('.$this->db->dbprefix("stockmoves").'.quantity) as quantity,expiry')
				->where('product_id',$pid)
				->order_by('expiry')
				->group_by('expiry');
		$q = $this->db->get('stockmoves');
		if($q->num_rows() > 0){
			$quantity = 0;
			foreach($q->result() as $row){
				if($row->expiry && $row->expiry!='0000-00-00'){
					$row->expiry = $this->cus->hrsd($row->expiry);
					$data[$row->expiry] = $row;
				}else{
					$quantity += $row->quantity;
					$row->quantity = $quantity;
					$row->expiry = '00/00/0000';
					$data['0000-00-00'] = $row;
				}
			}
			return $data;
			
		}
		return false;
	}
	
	public function getConsignmentItemByID($id = false, $product_id = false, $expiry = false, $serial_no = false)
	{
		if($product_id){
			$this->db->where("product_id",$product_id);
		}
		if($expiry){
			$this->db->where("IFNULL(expiry,'0000-00-00')",$expiry);
		}
		if($serial_no){
			$this->db->where("serial_no",$serial_no);
		}
		
		$this->db->select("(IFNULL(quantity,0) - IFNULL(return_qty,0) - IFNULL(sale_qty,0)) as quantity");
		$this->db->where("(IFNULL(quantity,0) - IFNULL(return_qty,0) - IFNULL(sale_qty,0)) > ", 0);
        $q = $this->db->get_where('consignment_items', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
		return false;
	}
	
	public function getSaleItemByConsigmentID($consignment_item_id = false)
	{
		$q = $this->db->get_where('sale_items',array('consignment_item_id'=>$consignment_item_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

	public function getAllRepairItems($repair_id=false)
    {
        $q = $this->db->get_where('repair_items', array('repair_id' => $repair_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getRepairByID($id = null)
	{
		$q = $this->db->where("id", $id)->get("repairs");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function addMemberCard($data = array())
    {
		$c_points = $data['award_points'];
		unset($data['award_points']);
        if ($this->db->insert('member_cards', $data)) {
			$this->db->where("id",$data['customer_id'])->update("companies",array("award_points"=>$c_points));
            return true;
        }
        return false;
    }
	
	public function updateMemberCard($id = false, $data = array())
    {
		$c_points = $data['award_points'];
		unset($data['award_points']);
        $this->db->where('id', $id);
        if ($this->db->update('member_cards', $data)) {
			$this->db->where("id",$data['customer_id'])->update("companies",array("award_points"=>$c_points));
            return true;
        }
        return false;
    }
	
	public function deleteMemberCard($id = false)
    {
		if($id && $id > 0){	
			if ($this->db->delete('member_cards', array('id' => $id))) {
				return true;
			}
		}
        return FALSE;
    }
	
	public function redeemMemberCard($data = array())
    {
		$member_card = $this->site->getMemberCardByID($data['card_id']);
		$company = $this->site->getCompanyByID($member_card->customer_id);
        if ($this->db->insert('member_card_redeems', $data)) {
			if($company){
				$remain_points = (double)$company->award_points - (double)$data['amount'];
				$this->db->update('companies', array('award_points' => $remain_points), array('id' => $company->id));
			}
            return true;
        }
        return false;
    }
	
	public function getRedeemPointByID($id = null)
	{
		$q = $this->db->where("id", $id)->get("member_card_redeems");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function deleteRedeemPoint($id = false)
    {
		if($id && $id > 0){	
			if ($this->db->delete('member_card_redeems', array('id' => $id))) {
				return true;
			}
		}
        return FALSE;
    }
	
	public function getRentalByID($id = false)
    {
        $q = $this->db->get_where('rentals', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getRentalRoomByID($id = false)
    {
        $q = $this->db->get_where('rental_rooms', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getAllRentalItems($rental_id = false)
    {
        $this->db->select('rental_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, product_variants.name as variant,units.name as unit_name')
            ->join('products', 'products.id=rental_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=rental_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=rental_items.tax_rate_id', 'left')
			->join('units','units.id = rental_items.product_unit_id','left')
            ->group_by('rental_items.id')
            ->order_by('id', 'desc');
        $q = $this->db->get_where('rental_items', array('rental_id' => $rental_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getRentalItem($rental_id = false, $product_id=false)
    {
        $this->db->select('rental_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, product_variants.name as variant,units.name as unit_name')
            ->join('products', 'products.id=rental_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=rental_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=rental_items.tax_rate_id', 'left')
			->join('units','units.id = rental_items.product_unit_id','left');
        $q = $this->db->get_where('rental_items', array('rental_items.rental_id' => $rental_id,'rental_items.product_id'=>$product_id));
        if ($q->num_rows() > 0) {
            $row = $q->row();
            return $row;
        }
        return FALSE;
	}
	
	public function getRentalDepositPayments($rental_id = false)
	{
		$q = $this->db->select("SUM(amount) as amount")
					  ->from("payments")
					  ->where("transaction","RentalDeposit")
					  ->where("type","received")
					  ->where("transaction_id", $rental_id)
					  ->get();
		if($q->num_rows()>0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function getInstallmentBySaleID($sale_id = false)
	{
		if($sale_id > 0){
			$q = $this->db->where("sale_id", $sale_id)->get("installments");
			if($q->num_rows() > 0){
				$row = $q->row();
				return $row;
			}
		}
		return false;
	}
	
	public function getRoomByID($id = false)
	{
		$q = $this->db->get_where("rental_rooms", array("id"=>$id));
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

	public function getModelByID($id = false)
	{
		$q = $this->db->get_where("models", array("id"=>$id));
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

	public function getBrandByID($id = false)
	{
		$q = $this->db->get_where("brands", array("id"=>$id));
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

	public function getMachineTypeByID($id = false)
	{
		$q = $this->db->get_where("machine_types", array("id"=>$id));
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

	public function getRentalRefundPayment($transaction_id=false){
		$q = $this->db->select("sum(amount) as amount")
					  ->where("transaction","ReturnRentalDeposit")
					  ->where("transaction_id", $transaction_id)
					  ->where("type","sent")
					  ->get("payments");
		if($q->num_rows()>0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

	public function getRentalDepositPayment($transaction_id=false){
		$q = $this->db->select("sum(amount) as amount")
					  ->where("transaction","RentalDeposit")
					  ->where("transaction_id", $transaction_id)
					  ->where("type","received")
					  ->get("payments");
		if($q->num_rows()>0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

	public function getRentalPayDeposit($sale_id=false){
		$q = $this->db->select("sum(amount) as amount")
					  ->where("sale_id", $sale_id)
					  ->where("paid_by","deposit")
					  ->where("type","received")
					  ->get("payments");
		if($q->num_rows()>0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function importSale($sales = false, $sale_items = false ,$stockmoves = false,$accTrans = false){
		if($sales && $sale_items){
			foreach($sales as $index => $sale){
				if($this->db->insert("sales",$sale)){
					$sale_id = $this->db->insert_id();
					if($sale_items[$index]){
						if($this->Settings->product_expiry == '1' && $stockmoves[$index] && $sale_items[$index]){
							$checkExpiry = $this->site->checkExpiry($stockmoves[$index], $sale_items[$index],'POS');
							$stockmoves[$index] = $checkExpiry['expiry_stockmoves'];
							$sale_items[$index] = $checkExpiry['expiry_items'];
						}
						foreach($sale_items[$index] as $sale_item){
							unset($sale_item['unit_qty']);
							$sale_item["sale_id"] = $sale_id;
							$this->db->insert("sale_items",$sale_item);
						}
					}
					if($stockmoves[$index]){
						foreach($stockmoves[$index] as $stockmove){
							$stockmove["transaction_id"] = $sale_id;
							$this->db->insert("stockmoves",$stockmove);
						}
					}
					if($accTrans[$index]){
						foreach($accTrans[$index] as $accTran){
							$accTran["transaction_id"] = $sale_id;
							$this->db->insert("acc_tran",$accTran);
						}
					}
				}

			}
			return true;
		}
		return false;
	}

	public function getAgencyByID($id = false)
	{
		$q = $this->db->where("agency",1)->where("id",$id)->get("users");
		if($q->num_rows()>0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

	// Agency commission functions 

	public function getAllAgencies()
	{
		$q = $this->db->where("agency",1)->get("users");
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getSaleAgencyCommissionBySaleID($sale_id = false, $start_date = '', $end_date = '')
	{
		$sql = ""; $ssql = "";
		if($start_date!=""){
			$sql .= " and date(date) >= '".$this->cus->fld($start_date)."'";
			$ssql .= " and date(date) < '".$this->cus->fld($start_date)."'";
		}else{
			$ssql .= " and id=0";
		}
		if($end_date!=""){
			$sql .= " and date(date) <= '".$this->cus->fld($end_date)."'";
		}
		$q = $this->db->select("
						sales.id, 
						sales.biller_id,
						sales.project_id,
						sales.reference_no,
						sales.agency_id, 
						sales.total, 
						sales.order_discount,
						sales.grand_total, 
						sales.customer, 
						sales.customer_id,
						sales.date, 
						sales.agency_commission, 
						sales.agency_limit_percent, 
						sales.agency_value_commission,
						ifnull(payments.amount,0) + ifnull(payments.interest_paid,0) as paid,
						ifnull(tpayments.amount,0) + ifnull(tpayments.interest_paid,0) as invoice_paid,
						ifnull(lpayments.amount,0) + ifnull(lpayments.interest_paid,0) as last_paid, 
						sale_items.product_name,
						sale_items.real_unit_price,
						sale_items.net_unit_price,
						sale_items.unit_price")
					->from("sales")
					->join("sale_items","sale_items.sale_id=sales.id","left")
					->join("(select sale_id, sum(amount) as amount, sum(interest_paid) as interest_paid from cus_payments where 1=1 ".$sql." group by sale_id) as payments","payments.sale_id=sales.id","left")
					->join("(select sale_id, sum(amount) as amount, sum(interest_paid) as interest_paid from cus_payments where 1=1 ".$ssql." group by sale_id) as lpayments","lpayments.sale_id=sales.id","left")
					->join("(select sale_id, sum(amount) as amount, sum(interest_paid) as interest_paid from cus_payments group by sale_id) as tpayments","tpayments.sale_id=sales.id","left")
					->where("sales.id",$sale_id)
					->get();

		if($q->num_rows()>0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

	public function getSaleAgencyCommission($sale_id = false, $agency_id = false, $start_date = '', $end_date = '')
	{
		$sql = ""; $ssql = "";
		if($start_date!=""){
			$sql .= " and date(date) >= '".$this->cus->fld($start_date)."'";
			$ssql .= " and date(date) < '".$this->cus->fld($start_date)."'";
		}else{
			$ssql .= " and id=0";
		}
		if($end_date!=""){
			$sql .= " and date(date) <= '".$this->cus->fld($end_date)."'";
		}
		$q = $this->db->select("
						sales.id, 
						sales.agency_id, 
						sales.total, 
						sales.order_discount,
						sales.grand_total, 
						sales.customer, 
						sales.customer_id,
						sales.date, 
						sales.agency_commission, 
						sales.agency_limit_percent, 
						sales.agency_value_commission,
						ifnull(payments.amount,0) + ifnull(payments.interest_paid,0) as paid, 
						ifnull(tpayments.amount,0) + ifnull(tpayments.interest_paid,0) as invoice_paid,
						ifnull(lpayments.amount,0) + ifnull(lpayments.interest_paid,0) as last_paid,
						sale_items.product_name,
						sale_items.real_unit_price,
						sale_items.net_unit_price,
						sale_items.unit_price")
					->from("sales")
					->join("sale_items","sale_items.sale_id=sales.id","left")
					->join("(select sale_id, sum(amount) as amount, sum(interest_paid) as interest_paid from cus_payments where 1=1 ".$sql." group by sale_id) as payments","payments.sale_id=sales.id","left")
					->join("(select sale_id, sum(amount) as amount, sum(interest_paid) as interest_paid from cus_payments where 1=1 ".$ssql." group by sale_id) as lpayments","lpayments.sale_id=sales.id","left")
					->join("(select sale_id, sum(amount) as amount, sum(interest_paid) as interest_paid from cus_payments group by sale_id) as tpayments","tpayments.sale_id=sales.id","left")
					->group_by('sale_items.sale_id')
					->where_in("sales.id",$sale_id)
					->get();
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function addSaleAgencyMultiPayment($data = array(), $accTranPayments = array())
	{
		if($data){
			foreach($data as $row){
				$this->db->insert("payments", $row);
				$payment_id = $this->db->insert_id();
				$accTrans = $accTranPayments[$row['transaction_id'].$row['agency_id']];
				if($accTrans){
					foreach($accTrans as $accTran){
						unset($accTran['agency_id']);
						$accTran['transaction_id'] = $payment_id;
						$this->db->insert('acc_tran',$accTran);
					}
				}

			}
			return true;
		}
		return false;
	}

	public function getSaleAgencyPayments($id = false, $agency_id=false, $search = false)
	{
		$post = $this->input->post()?$this->input->post():$this->input->get();
		if($search){
			if(isset($post['start_date']) && $post['start_date']){
				$this->db->where("date(agency_paid_date) >=", $this->cus->fld($post['start_date']));
			}
			if(isset($post['end_date']) && $post['end_date']){
				$this->db->where("date(agency_paid_date) <=", $this->cus->fld($post['end_date']));
			}
		}
		$q = $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by")
					->from("payments")
					->join("cash_accounts","cash_accounts.id = payments.paid_by","left")
					->where("payments.transaction_id",$id)
					->where("payments.transaction","AgencyPayment")
					->where("payments.type","sent")
					->where("payments.agency_id", $agency_id)
					->order_by("payments.id","desc")
					->get();
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getAllSaleAgencyCommissions($limit=0, $start=0)
	{
		$post = $this->input->post()?$this->input->post():$this->input->get();
		$sql = ''; $ssql = '';
		if(isset($post['product_name']) && $post['product_name']){
			$this->db->like("product_name", $post['product_name']);
		}
		if(isset($post['user']) && $post['user']){
			$this->db->where("created_by", $post['user']);
		}
		if(isset($post['biller']) && $post['biller']){
			$this->db->where("biller_id", $post['biller']);
		}
		if(isset($post['warehouse']) && $post['warehouse']){
			$this->db->where("warehouse_id", $post['warehouse']);
		}
		if(isset($post['customer']) && $post['customer']){
			$this->db->where("sales.customer_id", $post['customer']);
		}
		if(isset($post['start_date']) && $post['start_date']){
			$sql .= " and date(date) >= '".$this->cus->fld($post['start_date'])."'";
			$ssql .= " and date(date) < '".$this->cus->fld($post['start_date'])."'";
		}else{
			$ssql .= " and id=0";
		}
		if(isset($post['end_date']) && $post['end_date']){
			$sql .= " and date(date) <= '".$this->cus->fld($post['end_date'])."'";
		}
		if(isset($post)){
			$this->db->where("ifnull(payments.amount,0) + ifnull(payments.interest_paid,0) >", 0);
		}
		$q = $this->db->select("sales.id, 
								sales.agency_id, 
								sales.total, 
								sales.order_discount,
								sales.grand_total, 
								sales.customer, 
								sales.customer_id,
								sales.date, 
								sales.agency_commission,
								sales.agency_limit_percent, 
								sales.agency_value_commission,
								ifnull(payments.amount,0) + ifnull(payments.interest_paid,0) as paid,
								ifnull(tpayments.amount,0) + ifnull(tpayments.interest_paid,0) as invoice_paid,
								ifnull(lpayments.amount,0) + ifnull(lpayments.interest_paid,0) as last_paid,
								ifnull(deposits.amount,0) as deposit,
								sale_items.product_name,
								sale_items.real_unit_price,
								sale_items.net_unit_price,
								sale_items.unit_price")
					->from("sales")
					->join("sale_items","sale_items.sale_id=sales.id","left")
					->join("(select sale_id, sum(amount) as amount, sum(interest_paid) as interest_paid from cus_payments where 1=1 ".$sql." group by sale_id) as payments","payments.sale_id=sales.id","left")
					->join("(select sale_id, sum(amount) as amount, sum(interest_paid) as interest_paid from cus_payments where 1=1 ".$ssql." group by sale_id) as lpayments","lpayments.sale_id=sales.id","left")
					->join("(select sale_id, sum(amount) as amount, sum(interest_paid) as interest_paid from cus_payments group by sale_id) as tpayments","tpayments.sale_id=sales.id","left")
					->join("(select sale_id, sum(amount) as amount from cus_payments where is_deposit=1 group by sale_id) as deposits","deposits.sale_id=sales.id","left")
					->where("sales.pos","0")
					->where('sales.sale_status',"completed")
					->where('agency_id is not null')
					->where('agency_commission is not null')
					->where('agency_limit_percent is not null')
					->where('agency_value_commission is not null')
					->group_by('sale_items.sale_id')
					->limit($limit, $start)
					->order_by('sales.id','desc')
					->get();

		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getSaleAgencyCommissionsRecordCounts() 
	{
		$post = $this->input->post()?$this->input->post():$this->input->get();
		$sql = '';
		if(isset($post['product_name']) && $post['product_name']){
			$this->db->like("product_name", $post['product_name']);
		}
		if(isset($post['user']) && $post['user']){
			$this->db->where("created_by", $post['user']);
		}
		if(isset($post['biller']) && $post['biller']){
			$this->db->where("biller_id", $post['biller']);
		}
		if(isset($post['warehouse']) && $post['warehouse']){
			$this->db->where("warehouse_id", $post['warehouse']);
		}
		if(isset($post['customer']) && $post['customer']){
			$this->db->where("customer_id", $post['customer']);
		}
		if(isset($post['start_date']) && $post['start_date']){
			$sql .= " and date(date) >= '".$this->cus->fld($post['start_date'])."'";
		}
		if(isset($post['end_date']) && $post['end_date']){
			$sql .= " and date(date) <= '".$this->cus->fld($post['end_date'])."'";
		}
		if(isset($post)){
			$this->db->where("ifnull(payments.amount,0) + ifnull(payments.interest_paid,0) >", 0);
		}
		
		$q = $this->db->select("sales.id, 
								sales.agency_id, 
								sales.total, 
								sales.order_discount,
								sales.grand_total, 
								sales.customer, 
								sales.customer_id, 
								sales.date, 
								sales.agency_commission, 
								sales.agency_limit_percent, 
								sales.agency_value_commission,
								ifnull(payments.amount,0) + ifnull(payments.interest_paid,0) as paid,
								sale_items.product_name,
								sale_items.real_unit_price,
								sale_items.net_unit_price,
								sale_items.unit_price")
						->from("sales")
						->join("sale_items","sale_items.sale_id=sales.id","left")
						->join("(select sale_id, sum(amount) as amount, sum(interest_paid) as interest_paid from cus_payments where 1=1 ".$sql." group by sale_id) as payments","payments.sale_id=sales.id","left")
						->where("sales.pos","0")
						->where('sales.sale_status',"completed")
						->where('agency_id is not null')
						->where('agency_commission is not null')
						->where('agency_limit_percent is not null')
						->where('agency_value_commission is not null')
						->group_by('sale_items.sale_id')
						->get();
		return $q->num_rows();
	}

	public function updateAgencyPayment($id = false, $data = array(), $customer_id = null)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale = $this->getInvoiceByID($opay->sale_id);
                    $customer_id = $sale->customer_id;
                }
				$this->sysnceCustomerDeposit($customer_id);
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
				$this->sysnceCustomerDeposit($customer_id);
            }
            return true;
        }
        return false;
	}
	
	public function getCustomers(){
		$q = $this->db->get_where("companies",array("group_id"=>3));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getCustomerLocations($customer_id = false){
		$this->db->where("status",0);
		$q = $this->db->get_where("addresses",array("company_id"=>$customer_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getCustomerLocationByID($id = false){
		$q = $this->db->get_where('addresses',array('id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getConcreteSales($biller_id = false,$project_id = false,$warehouse_id = false,$customer_id = false,$location_id = false,$from_date = false,$to_date = false,$sale_id = false){
		if($biller_id){
			$this->db->where("con_sales.biller_id",$biller_id);
		}
		if($project_id){
			$this->db->where("con_sales.project_id",$project_id);
		}
		if($warehouse_id){
			$this->db->where("con_sales.warehouse_id",$warehouse_id);
		}
		if($customer_id){
			$this->db->where("con_sales.customer_id",$customer_id);
		}
		if($location_id){
			$this->db->where("con_sales.location_id",$location_id);
		}
		if($from_date){
			$this->db->where("con_sales.date >=",$from_date);
		}
		if($to_date){
			$this->db->where("con_sales.date <=",$to_date);
		}
		if($sale_id){
			$this->db->join("sale_concrete_items","con_sales.id = sale_concrete_items.con_sale_id AND sale_concrete_items.sale_id = ".$sale_id,"LEFT");
			$this->db->where("(".$this->db->dbprefix('con_sales').".sale_status = 'pending' OR ".$this->db->dbprefix('sale_concrete_items').".con_sale_id > 0)");
		}else{
			$this->db->where("con_sales.sale_status","pending");
		}
		$this->db->select("
							con_sales.id,
							con_sales.date,
							con_sales.reference_no,
							con_sales.total,
							con_sales.truck_charge,
							con_sales.pump_charge,
							con_sales.grand_total
						");
		$this->db->group_by("con_sales.id");
		$q = $this->db->get("con_sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$row->date = $this->cus->hrld($row->date);
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getSaleConcreteItemBySaleID($sale_id = false){
		$q = $this->db->get_where("sale_concrete_items",array("sale_id"=>$sale_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getConcreteSaleItemBySaleID($sale_id = false){
		$q = $this->db->get_where("con_sale_items",array("sale_id"=>$sale_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function addSaleConcrete($data = false, $con_products = false, $products = false, $accTrans = false){
		if($this->db->insert("sales",$data)){
			$sale_id = $this->db->insert_id();
			if($con_products){
				foreach($con_products as $con_product){
					$con_product['sale_id'] = $sale_id;
					$this->db->insert("sale_concrete_items",$con_product);
				}
			}
			if($products){
				$sale_items = false;
				foreach($products as $product){
					$product['sale_id'] = $sale_id;
					$sale_items[] = $product;
				}
				if($sale_items){
					$this->db->insert_batch("sale_items",$sale_items);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $sale_id;
					$this->db->insert("acc_tran",$accTran);
				}
			}
			$this->synceConcreteSales();
			return true;
		}
		return false;
	}
	
	public function updateSaleConcrete($id = false, $data = false, $con_products = false, $products = false, $accTrans = false){
		
		if($id && $this->db->update("sales",$data,array("id"=>$id))){
			$this->db->delete("sale_concrete_items",array("sale_id"=>$id));
			$this->db->delete("sale_items",array("sale_id"=>$id));
			$this->site->deleteAccTran('Sale',$id);
			if($con_products){
				$this->db->insert_batch("sale_concrete_items",$con_products);
			}
			if($products){
				$this->db->insert_batch("sale_items",$products);
			}
			if($accTrans){
				$this->db->insert_batch("acc_tran",$accTrans);
			}
			$this->synceConcreteSales();
			return true;
		}
		return false;	}
	public function deletSaleConcrete($id = false) {
		if($id && $id > 0){
			$sale = $this->getInvoiceByID($id);
			if ($this->db->delete('sales', array('id' => $id))) {
				$payments = $this->getPaymentsBySale($id);
				$sales_returns = $this->getSaleReturns($id);
				$this->db->delete('sales', array('sale_id' => $id));
				$this->db->delete('payments', array('sale_id' => $id));
				$this->db->delete('sale_concrete_items', array('sale_id' => $id));
				$this->site->deleteStockmoves('Sale',$id);
				$this->site->deleteAccTran('Sale',$id);
				$this->site->deleteAccTran('SaleReturn',$id);
				$this->db->update('sales', array('return_sale_ref' => '', 'surcharge' => 0,'return_sale_total' => 0, 'return_id' => ''), array('return_id' => $id));
				$this->db->delete('sale_items', array('sale_id' => $id));
				if($payments){
					foreach($payments as $payment){
						if ($payment->paid_by == 'gift_card') {
							$gc = $this->site->getGiftCardByNO($payment->cc_no);
							$this->db->update('gift_cards', array('balance' => ($gc->balance+$payment->amount)), array('card_no' => $payment->cc_no));
						} elseif ($payment->paid_by == 'deposit') {
							$this->sysnceCustomerDeposit($sale->customer_id);
						}
						$this->site->deleteAccTran('Payment',$payment->id);
						if($payment->sale_id > 0 && $payment->sale_order_id > 0){
							$this->site->deleteAccTran('Sale Order Deposit',$payment->id);
						}
					}
				}
				if($sales_returns){
					foreach($sales_returns as $sales_return){
						$payment_returns = $this->getPaymentsBySale($sales_return->id);
						$this->site->deleteAccTran('SaleReturn',$sales_return->id);
						$this->site->deleteStockmoves('Sale',$sales_return->id);
						$this->db->delete('payments', array('sale_id' => $sales_return->id));
						if($payment_returns){
							foreach($payment_returns as $payment_return){
								$this->site->deleteAccTran('Payment',$payment_return->id);
							}
						}
					}
				}
				$this->synceConcreteSales();
				return true;
			}
		}
        return FALSE;
    }
	
	public function synceConcreteSales(){
		$this->db->query("UPDATE ".$this->db->dbprefix('con_sales')."
						LEFT JOIN ".$this->db->dbprefix('sale_concrete_items')." ON ".$this->db->dbprefix('sale_concrete_items').".con_sale_id = ".$this->db->dbprefix('con_sales').".id 
						SET ".$this->db->dbprefix('con_sales').".sale_status = IF(IFNULL( ".$this->db->dbprefix('sale_concrete_items').".con_sale_id, '' ) = '','pending','completed')
						");
		return true;
	}
	
	public function getConcreteDeliveryItemBySaleID($sale_id = false){
		$this->db->where("sale_concrete_items.sale_id",$sale_id);
		$this->db->select("con_sale_items.*,con_deliveries.reference_no,con_deliveries.date,con_deliveries.casting_type_name");
		$this->db->join("con_sale_items","con_sale_items.sale_id = sale_concrete_items.con_sale_id","INNER");
		$this->db->join("con_deliveries","con_deliveries.id = con_sale_items.con_delivery_id","LEFT");
		$this->db->order_by("con_deliveries.date,con_deliveries.reference_no");
		$q = $this->db->get("sale_concrete_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getConcreteDeliveryItemDailyBySaleID($sale_id = false){
		$this->db->where("sale_concrete_items.sale_id",$sale_id);
		$this->db->select("sum(".$this->db->dbprefix('con_sale_items').".quantity) as quantity,
							con_sale_items.product_name,
							con_sale_items.product_code,
							con_sale_items.unit_price,
							con_deliveries.date
						");
		$this->db->join("con_sale_items","con_sale_items.sale_id = sale_concrete_items.con_sale_id","INNER");
		$this->db->join("con_deliveries","con_deliveries.id = con_sale_items.con_delivery_id","LEFT");
		$this->db->group_by("con_sale_items.product_id,con_sale_items.unit_price, con_deliveries.date");
		$this->db->order_by("con_deliveries.date");
		$q = $this->db->get("sale_concrete_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getConcreteDeliveryItemShortBySaleID($sale_id = false){
		$this->db->where("sale_concrete_items.sale_id",$sale_id);
		$this->db->select("sum(".$this->db->dbprefix('con_sale_items').".quantity) as quantity,
							con_sale_items.product_name,
							con_sale_items.product_code,
							con_sale_items.unit_price
						");
		$this->db->join("con_sale_items","con_sale_items.sale_id = sale_concrete_items.con_sale_id","INNER");
		$this->db->join("con_deliveries","con_deliveries.id = con_sale_items.con_delivery_id","LEFT");
		$this->db->group_by("con_sale_items.product_id,con_sale_items.unit_price");
		$this->db->order_by("con_deliveries.date");
		$q = $this->db->get("sale_concrete_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getTruckChargeBySaleID($sale_id = false){
		$this->db->where("sale_concrete_items.sale_id",$sale_id);
		$this->db->select("sum(truck_charge) as truck_charge, sum(pump_charge) as pump_charge");
		$q = $this->db->get("sale_concrete_items");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
		
	}
	public function getCustomerTrucks($customer_id = false){
		$q = $this->db->get_where("customer_trucks",array("customer_id"=>$customer_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getTruckByID($id = false)
    {
        $q = $this->db->get_where('customer_trucks', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getSalesmanCommissionByID($id = false){
		$q = $this->db->get_where("salesman_commissions",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getSalesmanCommissionItems($commission_id = false){
		$this->db->select("salesman_commision_items.*,users.last_name,users.first_name, sales.reference_no,sales.date as sale_date");
		$this->db->join("sales","sales.id = salesman_commision_items.sale_id","left");
		$this->db->join("users","users.id = salesman_commision_items.salesman_id","left");
		$q = $this->db->get_where("salesman_commision_items",array("commission_id"=>$commission_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	
	public function getSalesmanSales($biller_id = false,$salesman_group_id = false,$salesman_id = false,$project_id = false,$from_date = false,$to_date = false, $commission_id = false){
		if($biller_id){
			$this->db->where("sales.biller_id",$biller_id);
		}
		if($salesman_group_id){
			$this->db->where("users.saleman_group_id",$salesman_group_id);
		}
		if($salesman_id){
			$this->db->where("users.id",$salesman_id);
		}
		if($project_id){
			$this->db->where("sales.project_id",$project_id);
		}
		if($from_date){
			$this->db->where("date(".$this->db->dbprefix('sales').".date) >=",$from_date);
		}
		if($to_date){
			$this->db->where("date(".$this->db->dbprefix('sales').".date) <=",$to_date);
		}
		$where_commission = "";
		if($commission_id){
			$where_commission .=" AND ".$this->db->dbprefix('salesman_commision_items').".commission_id != ".$commission_id;
		}
		$this->db->select("
						sales.id as sale_id,
						users.id as salesman_id,
						CONCAT(".$this->db->dbprefix('users').".last_name, ' ', ".$this->db->dbprefix('users').".first_name ) AS salesman,
						sales.reference_no,
						(SUM(grand_total) - IFNULL(cus_returns.total_return,0)) as grand_total,
						IFNULL(cus_payments.paid + cus_payments.discount + IFNULL(cus_returns.total_return_paid,0),0) - IFNULL(cus_salesman_commission.amount,0) as amount,
						sales.saleman_commission as rate
					")
		->join("users","users.id = sales.saleman_id","inner")
		->join('(SELECT
					sale_id,
					SUM(ABS(grand_total)) AS total_return,
					SUM(paid) AS total_return_paid
				FROM
					'.$this->db->dbprefix('sales').' WHERE sale_status = "returned"
				GROUP BY
					sale_id) as cus_returns', 'cus_returns.sale_id=sales.id', 'left')
		->join('(SELECT
					sale_id,
					IFNULL(SUM(amount),0) AS paid,
					IFNULL(SUM(discount),0) AS discount
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left')
		->join("(SELECT
					".$this->db->dbprefix('salesman_commision_items').".sale_id,
					".$this->db->dbprefix('salesman_commision_items').".salesman_id,
					sum(".$this->db->dbprefix('salesman_commision_items').".amount ) AS amount 
				FROM
					".$this->db->dbprefix('salesman_commision_items')."
				INNER JOIN ".$this->db->dbprefix('salesman_commissions')." ON ".$this->db->dbprefix('salesman_commissions').".id = ".$this->db->dbprefix('salesman_commision_items').".commission_id
				WHERE ".$this->db->dbprefix('salesman_commissions').".commission_type='Normal' ".$where_commission."
				GROUP BY
					".$this->db->dbprefix('salesman_commision_items').".sale_id,".$this->db->dbprefix('salesman_commision_items').".salesman_id) as cus_salesman_commission","cus_salesman_commission.sale_id = sales.id AND cus_salesman_commission.salesman_id = users.id","left")
		->where("sales.sale_status !=","return")
		->where("IFNULL(".$this->db->dbprefix('sales').".saleman_commission,0) <>",0)
		->where("ROUND((IFNULL(cus_payments.paid + cus_payments.discount + IFNULL(cus_returns.total_return_paid,0),0) - IFNULL(cus_salesman_commission.amount,0)),".$this->Settings->decimals.") >",0)
		->group_by("sales.id");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getLSalesmanSales($biller_id = false,$salesman_group_id = false,$salesman_id = false,$project_id = false,$from_date = false,$to_date = false, $commission_id = false){
		if($biller_id){
			$this->db->where("sales.biller_id",$biller_id);
		}
		if($salesman_group_id){
			$this->db->where("users.saleman_group_id",$salesman_group_id);
		}
		if($salesman_id){
			$this->db->where("share_commissions.salesman_id",$salesman_id);
		}
		if($project_id){
			$this->db->where("sales.project_id",$project_id);
		}
		if($from_date){
			$this->db->where("date(".$this->db->dbprefix('sales').".date) >=",$from_date);
		}
		if($to_date){
			$this->db->where("date(".$this->db->dbprefix('sales').".date) <=",$to_date);
		}
		$where_commission = "";
		if($commission_id){
			$where_commission .=" AND ".$this->db->dbprefix('salesman_commision_items').".commission_id != ".$commission_id;
		}
		$this->db->select("
						sales.id as sale_id,
						share_commissions.share_id as salesman_id,
						CONCAT(salesman_leaders.last_name, ' ', salesman_leaders.first_name ) AS salesman,
						sales.reference_no,
						(SUM(grand_total) - IFNULL(cus_returns.total_return,0)) as grand_total,
						IFNULL(cus_payments.paid + cus_payments.discount + IFNULL(cus_returns.total_return_paid,0),0) - IFNULL(cus_salesman_commission.amount,0) as amount,
						share_commission_rates.commission as rate
					")
		->join("users","users.id = sales.saleman_id","inner")
		->join("share_commissions","share_commissions.salesman_id = users.id","inner")
		->join('(SELECT
					sale_id,
					SUM(ABS(grand_total)) AS total_return,
					SUM(paid) AS total_return_paid
				FROM
					'.$this->db->dbprefix('sales').' WHERE sale_status = "returned"
				GROUP BY
					sale_id) as cus_returns', 'cus_returns.sale_id=sales.id', 'left')
		->join('(SELECT
					sale_id,
					IFNULL(SUM(amount),0) AS paid,
					IFNULL(SUM(discount),0) AS discount
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left')
		->join("(SELECT
					".$this->db->dbprefix('salesman_commision_items').".sale_id,
					".$this->db->dbprefix('salesman_commision_items').".salesman_id,
					sum(".$this->db->dbprefix('salesman_commision_items').".amount ) AS amount
				FROM
					".$this->db->dbprefix('salesman_commision_items')."
				INNER JOIN ".$this->db->dbprefix('salesman_commissions')." ON ".$this->db->dbprefix('salesman_commissions').".id = ".$this->db->dbprefix('salesman_commision_items').".commission_id
				WHERE ".$this->db->dbprefix('salesman_commissions').".commission_type='Normal' ".$where_commission."
				GROUP BY
					".$this->db->dbprefix('salesman_commision_items').".sale_id,".$this->db->dbprefix('salesman_commision_items').".salesman_id) as cus_salesman_commission","cus_salesman_commission.sale_id = sales.id AND cus_salesman_commission.salesman_id = share_commissions.share_id","left")
		->join("share_commission_rates","share_commission_rates.salesman_id = share_commissions.share_id AND share_commission_rates.commission_type='Normal'","inner")
		->join("(SELECT id,last_name,first_name FROM ".$this->db->dbprefix("users").") as salesman_leaders","salesman_leaders.id = share_commissions.share_id","inner")
		->where("sales.sale_status !=","return")
		->where("IFNULL(".$this->db->dbprefix('sales').".saleman_commission,0) <>",0)
		->where("ROUND((IFNULL(cus_payments.paid + cus_payments.discount + IFNULL(cus_returns.total_return_paid,0),0) - IFNULL(cus_salesman_commission.amount,0)),".$this->Settings->decimals.") >",0)
		->group_by("sales.id,share_commissions.share_id");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getSalesmanTargets($biller_id = false,$salesman_group_id = false,$salesman_id = false,$project_id = false,$from_date = false,$to_date = false,$commission_id = false){
		$where = "";
		if($biller_id){
			$where .= " AND ".$this->db->dbprefix('sales').".biller_id = ".$biller_id;
		}
		if($project_id){
			$where .= " AND ".$this->db->dbprefix('sales').".project_id = ".$project_id;
		}
		if($from_date){
			$where .= " AND date(".$this->db->dbprefix('sales').".date) >= '".$from_date."'";
		}
		if($to_date){
			$where .= " AND date(".$this->db->dbprefix('sales').".date) <= '".$to_date."'";
		}
		if($salesman_group_id){
			$this->db->where("users.saleman_group_id",$salesman_group_id);
		}
		if($salesman_id){
			$this->db->where("users.id",$salesman_id);
		}
		$where_commission = "";
		if($commission_id){
			$where_commission .=" AND ".$this->db->dbprefix('salesman_commission_targets').".commission_id != ".$commission_id;
		}
		
		$this->db->select("
							sale_com.sale_ids,
							users.id as salesman_id,
							CONCAT(".$this->db->dbprefix('users').".last_name, ' ', ".$this->db->dbprefix('users').".first_name ) AS salesman,
							sale_com.grand_total,
							sale_com.amount,
							CONCAT(".$this->db->dbprefix('saleman_targets').".description, ' (', ".$this->db->dbprefix('saleman_targets').".min_amount, ' - ', ".$this->db->dbprefix('saleman_targets').".max_amount, ')') AS target,
							saleman_targets.commission as rate
						")
				->from("(SELECT
							IFNULL(".$this->db->dbprefix('sales').".saleman_id,'') as saleman_id,
							GROUP_CONCAT(CONCAT(".$this->db->dbprefix('sales').".id,'=',(IFNULL(cus_payments.paid + cus_payments.discount + IFNULL(cus_returns.total_return_paid,0),0) - IFNULL(cus_salesman_commission_targets.amount,0)))) AS sale_ids,
							sum(".$this->db->dbprefix('sales').".grand_total ) AS grand_total,
							sum(IFNULL(cus_payments.paid + cus_payments.discount + IFNULL(cus_returns.total_return_paid,0),0) - IFNULL(cus_salesman_commission_targets.amount,0)) as amount
						FROM
							".$this->db->dbprefix('sales')." 
						LEFT JOIN (SELECT sale_id,IFNULL(SUM(amount),0) AS paid,IFNULL(SUM(discount),0) AS discount FROM ".$this->db->dbprefix('payments')." GROUP BY sale_id) as cus_payments ON cus_payments.sale_id = ".$this->db->dbprefix('sales')." .id
						LEFT JOIN (SELECT sale_id, SUM(ABS(grand_total)) AS total_return, SUM(paid) AS total_return_paid FROM ".$this->db->dbprefix('sales')." WHERE sale_status = 'returned' GROUP BY sale_id) as cus_returns ON cus_returns.sale_id = ".$this->db->dbprefix('sales')." .id
						LEFT JOIN (SELECT sale_id, IFNULL(salesman_id,0) as salesman_id, sum(amount) as amount FROM ".$this->db->dbprefix('salesman_commission_targets')." WHERE 1=1 ".$where_commission." GROUP BY sale_id,salesman_id) as cus_salesman_commission_targets ON cus_salesman_commission_targets.sale_id = ".$this->db->dbprefix('sales')." .id AND cus_salesman_commission_targets.salesman_id = ".$this->db->dbprefix('sales')." .saleman_id
						WHERE
							IFNULL(".$this->db->dbprefix('sales').".saleman_id,'') > 0 
						".$where."
						GROUP BY
							IFNULL(".$this->db->dbprefix('sales').".saleman_id,'')
					) AS cus_sale_com")
		->join("users","users.id = sale_com.saleman_id","inner")
		->join("saleman_targets","IFNULL(".$this->db->dbprefix('users').".saleman_group_id,0) = IFNULL(".$this->db->dbprefix('saleman_targets').".group_id,0) AND sale_com.amount >= saleman_targets.min_amount AND sale_com.amount <= saleman_targets.max_amount","inner")
		->group_by("users.id")
		->where("users.saleman", 1);
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getLSalesmanTargets($biller_id = false,$salesman_group_id = false,$salesman_id = false,$project_id = false,$from_date = false,$to_date = false,$commission_id = false){
		$where = "";
		if($biller_id){
			$where .= " AND ".$this->db->dbprefix('sales').".biller_id = ".$biller_id;
		}
		if($project_id){
			$where .= " AND ".$this->db->dbprefix('sales').".project_id = ".$project_id;
		}
		if($from_date){
			$where .= " AND date(".$this->db->dbprefix('sales').".date) >= '".$from_date."'";
		}
		if($to_date){
			$where .= " AND date(".$this->db->dbprefix('sales').".date) <= '".$to_date."'";
		}
		if($salesman_group_id){
			$this->db->where("users.saleman_group_id",$salesman_group_id);
		}
		if($salesman_id){
			$this->db->where("sale_com.saleman_id",$salesman_id);
		}
		$where_commission = "";
		if($commission_id){
			$where_commission .=" AND ".$this->db->dbprefix('salesman_commission_targets').".commission_id != ".$commission_id;
		}
		
		$this->db->select("
							sale_com.sale_ids,
							users.id as salesman_id,
							CONCAT(".$this->db->dbprefix('users').".last_name, ' ', ".$this->db->dbprefix('users').".first_name ) AS salesman,
							sale_com.grand_total,
							sale_com.amount,
							CONCAT(".$this->db->dbprefix('share_commission_rates').".description, ' (', ".$this->db->dbprefix('share_commission_rates').".min_amount, ' - ', ".$this->db->dbprefix('share_commission_rates').".max_amount, ')') AS target,
							share_commission_rates.commission as rate
						")
				->from("(SELECT
							".$this->db->dbprefix('share_commissions').".share_id as saleman_id,
							GROUP_CONCAT(CONCAT(".$this->db->dbprefix('sales').".id,'=',(IFNULL(cus_payments.paid + cus_payments.discount + IFNULL(cus_returns.total_return_paid,0),0) - IFNULL(cus_salesman_commission_targets.amount,0)))) AS sale_ids,
							sum(".$this->db->dbprefix('sales').".grand_total ) AS grand_total,
							sum(IFNULL(cus_payments.paid + cus_payments.discount + IFNULL(cus_returns.total_return_paid,0),0) - IFNULL(cus_salesman_commission_targets.amount,0)) as amount
						FROM
							".$this->db->dbprefix('sales')." 
						INNER JOIN ".$this->db->dbprefix('users')." ON ".$this->db->dbprefix('users').".id = ".$this->db->dbprefix('sales').".saleman_id
						INNER JOIN ".$this->db->dbprefix('share_commissions')." ON ".$this->db->dbprefix('users').".id = ".$this->db->dbprefix('share_commissions').".salesman_id
						LEFT JOIN (SELECT sale_id,IFNULL(SUM(amount),0) AS paid,IFNULL(SUM(discount),0) AS discount FROM ".$this->db->dbprefix('payments')." GROUP BY sale_id) as cus_payments ON cus_payments.sale_id = ".$this->db->dbprefix('sales')." .id
						LEFT JOIN (SELECT sale_id, SUM(ABS(grand_total)) AS total_return, SUM(paid) AS total_return_paid FROM ".$this->db->dbprefix('sales')." WHERE sale_status = 'returned' GROUP BY sale_id) as cus_returns ON cus_returns.sale_id = ".$this->db->dbprefix('sales')." .id
						LEFT JOIN (SELECT sale_id, IFNULL(salesman_id,0) as salesman_id, sum(amount) as amount FROM ".$this->db->dbprefix('salesman_commission_targets')." WHERE 1=1 ".$where_commission." GROUP BY sale_id,salesman_id) as cus_salesman_commission_targets ON cus_salesman_commission_targets.sale_id = ".$this->db->dbprefix('sales')." .id AND cus_salesman_commission_targets.salesman_id = ".$this->db->dbprefix('share_commissions')." .share_id
						WHERE
							1=1 ".$where."
						GROUP BY
							".$this->db->dbprefix('share_commissions').".share_id
					) AS cus_sale_com")
		->join("users","users.id = sale_com.saleman_id","inner")
		->join("share_commission_rates","sale_com.saleman_id = share_commission_rates.salesman_id AND share_commission_rates.commission_type = 'Target' AND sale_com.amount >= share_commission_rates.min_amount AND sale_com.amount <= share_commission_rates.max_amount","inner")
		->group_by("users.id")
		->where("users.saleman", 1);
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function addSalesmanCommission($data = false, $items = false, $sales_target_commissions = false, $acc_trans = false){
		if($this->db->insert("salesman_commissions",$data)){
			$commission_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["commission_id"] = $commission_id;
					$this->db->insert("salesman_commision_items",$item);
				}
			}
			if($sales_target_commissions){
				foreach($sales_target_commissions as $sales_target_commission){
					$sales_target_commission["commission_id"] = $commission_id;
					$this->db->insert("salesman_commission_targets",$sales_target_commission);
				}
			}
			if($acc_trans){
				foreach($acc_trans as $acc_tran){
					$acc_tran["transaction_id"] = $commission_id;
					$this->db->insert("acc_tran",$acc_tran);
				}
			}
			
			return true;
		}
		return false;
	}
	
	public function updateSalesmanCommission($id = false, $data = false, $items = false, $sales_target_commissions = false, $acc_trans = false){
		if($this->db->update("salesman_commissions",$data, array("id"=>$id))){
			$this->db->delete("salesman_commision_items",array("commission_id"=>$id));
			$this->db->delete("salesman_commission_targets",array("commission_id"=>$id));
			$this->site->deleteAccTran('Salesman Commission',$id);
			if($items){
				$this->db->insert_batch("salesman_commision_items",$items);
			}
			if($sales_target_commissions){
				$this->db->insert_batch("salesman_commission_targets",$sales_target_commissions);
			}
			if($acc_trans){
				$this->db->insert_batch("acc_tran",$acc_trans);
			}
			$this->sysCommissionPayment($id);
			return true;
		}
		return false;
	}
	
	public function deleteSalesmanCommission($id = false){
		if($id && $this->db->delete("salesman_commissions",array("id"=>$id))){
			$this->db->delete("salesman_commision_items",array("commission_id"=>$id));
			$this->db->delete("salesman_commission_targets",array("commission_id"=>$id));
			$this->site->deleteAccTran('Salesman Commission',$id);
			$payments = $this->getCommissionPaymentByCommission($id);
			if($payments){
				$this->db->delete("payments",array("transaction"=>"Salesman Commission", "transaction_id"=>$id));
				foreach($payments as $payment){
					$this->site->deleteAccTran('Payment',$payment->id);
				}
			}
			return true;
		}
		return false;
	}
	
	public function sysCommissionPayment($commission_id = false){
		$commission = $this->getSalesmanCommissionByID($commission_id);
		if($commission){
			$status = "pending";
			$paid = 0;
			$payment = $this->getTotalCommissionPayment($commission_id);
			if($payment){
				$paid = $this->cus->formatDecimal($payment->amount);
				if($paid == $this->cus->formatDecimal($commission->total_commission)){
					$status = "paid";
				}else if ($paid  > 0){
					$status = "partial";
				}
			}
			$this->db->update("salesman_commissions",array("paid"=>$paid,"status"=>$status),array("id"=>$commission_id));
		}
	}
	
	public function getTotalCommissionPayment($commission_id = false){
		$this->db->select("sum(amount) as amount");
		$this->db->where("transaction_id",$commission_id);
		$this->db->where("transaction","Salesman Commission");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getCommissionPaymentByID($id = false){
		$q = $this->db->get_where("payments",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addCommissionPayment_test($data = false, $accTrans = false)
	{
		if($data && $this->db->insert("payments",$data)){
			$payment_id = $this->db->insert_id();
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $payment_id;
					$this->db->insert('acc_tran',$accTran);
				}
			}
			$this->sysCommissionPayment($data["transaction_id"]);
			return true;
		}
		return false;
	}

    public function addCommissionPayment($data, $accTranPayments)
    {
        if($data){
            foreach($data as $row){
                $this->db->insert('payments',$row);
                $payment_id = $this->db->insert_id();
                $accTrans = $accTranPayments[$row['transaction_id']];
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
    public function getMultiInvSaleman($id, $one_row = false)
    {
        $this->db->select("
                    sales.id as id,
                    sales.reference_no,
                    sales.date,
                    biller,
                    users.saleman_group as group_name, 
                    IFNULL(concat(first_name,' ', last_name),'N/A') as saleman, 
                    (".$this->db->dbprefix("sales").".grand_total - IFNULL(cus_returns.amount_return,0)) as grand_total,
                    sales.saleman_commission,
                    IFNULL(payment.paid_commission, 0) as paid_commission
                    ")
        ->join("users","users.id = saleman_id AND users.saleman=1","inner")
        ->join("(SELECT sum(amount) as paid_commission, transaction_id FROM ".$this->db->dbprefix('payments')." WHERE transaction='Saleman Commission' GROUP BY transaction_id) as payment","payment.transaction_id = sales.id","left")
        ->join('(SELECT sum(abs(grand_total)) as amount_return, sale_id FROM '.$this->db->dbprefix("sales").' WHERE sale_status = "returned" GROUP BY sale_id) as cus_returns','cus_returns.sale_id = sales.id','left')
        ->where("saleman_id <>", 0) 
        ->where_in('sales.id',$id)
        ->where("sales.sale_status !=","return")
        ->where("IFNULL(".$this->db->dbprefix('sales').".saleman_commission,0) <>",0)
        ->group_by("sales.id")
        ->order_by("sales.id");
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            if($one_row){
                return $q->row();
            }else{
                foreach($q->result() as $row){
                    $data[] = $row;
                }
                return $data;
            }
            
        }
        return FALSE;
    }
    public function getSalemanPayments($sale_id = false)
    {
        if($sale_id){
            $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
            $this->db->where('transaction_id IN ('.$sale_id.')');
            $this->db->where('transaction','Saleman Commission');
            $this->db->order_by('id', 'asc');
            $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
            $q = $this->db->get('payments');
           
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
                return $data;
            }
        }
        return false;
    }

    public function getCommissionPaymentsByRef($ref,$date)
    {
        $this->db->select('payments.*,payments.date AS payment_date,payments.reference_no AS payment_ref,payments.amount AS payment_amount,payments.discount AS payment_discount,sales.date AS sale_date,sales.reference_no AS sale_ref, payments.transaction')
        ->join('sales','sales.id = payments.transaction_id','inner')
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
	public function updateCommissionPayment($id = false, $data = false, $accTrans = false)
	{
		if($id && $this->db->update("payments",$data,array("id"=>$id))){
			$this->site->deleteAccTran('Payment',$id);
			if($accTrans){
				$this->db->insert_batch('acc_tran',$accTrans);
			}
			$this->sysCommissionPayment($data["transaction_id"]);
			return true;
		}
		return false;
	}
	
	public function deleteCommissionPayment($id = false)
    {
		$payment = $this->getCommissionPaymentByID($id);
		if($payment){
			if ($this->db->delete('payments', array('id' => $id))) {
				$this->site->deleteAccTran('Payment',$id);
				$this->sysCommissionPayment($payment->transaction_id);
				return true;
			}
		}
        return FALSE;
    }
	
	public function getCommissionPaymentByCommission($commission_id = false){
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$this->db->where("payments.transaction","Salesman Commission");
		$this->db->where("payments.transaction_id",$commission_id);
		$this->db->order_by("payments.id","desc");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getLeaderCommission($salesman_id = false, $commission_type = false){
		if($salesman_id){
			$this->db->where("leader_commissions.salesman_id",$salesman_id);
		}
		if($commission_type){
			$this->db->where("leader_commissions.commission_type",$commission_type);
		}
		$this->db->select("leader_commissions.*,users.last_name,users.first_name");
		$this->db->join("users","users.id = leader_commissions.salesman_id","inner");
		$q = $this->db->get("leader_commissions");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getSalesmanByGroup($salesman_group_id = false){
		$q = $this->db->get_where("users",array("saleman_group_id"=>$salesman_group_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
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
	
	public function getStudentByID($student_id = false){
		$q = $this->db->get_where("sh_students",array("id"=>$student_id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function getStudyInfoBySale($sale_id = false){
		$q = $this->db->get_where("sh_study_infos",array("sale_id"=>$sale_id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	public function getSiblings($family_id = false){
		$this->db->select("sh_students.*");
		$this->db->order_by("sh_family_groups.student_id");
		$this->db->join("sh_students","sh_students.id = sh_family_groups.student_id","inner");
		$q = $this->db->get_where('sh_family_groups', array('sh_family_groups.family_id' => $family_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getLastStudyInfo($student_id = false, $study_year = false){
		if($study_year){
			$this->db->where("study_year",$study_year);
		}
		$this->db->order_by("id","desc");
		$q = $this->db->get_where("sh_study_infos", array('student_id' => $student_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getFamilyBank($family_id = false){
		$this->db->where("family_id",$family_id);
		$this->db->where("IFNULL(account_name,'') !=","");
		$this->db->where("IFNULL(account_number,'') !=","");
		$q = $this->db->get("sh_student_families",1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getSalePayments($biller_id = false,$created_by = false,$from_date = false,$to_date = false, $paid_by = false, $receive_id = false){
		if($biller_id){
			$this->db->where("sales.biller_id",$biller_id);
		}
		if($created_by){
			$this->db->where("payments.created_by",$created_by);
		}
		if($paid_by){
			$this->db->where("payments.paid_by",$paid_by);
		}
		if($from_date){
			$this->db->where("date(".$this->db->dbprefix('payments').".date) >=",$from_date);
		}
		if($to_date){
			$this->db->where("date(".$this->db->dbprefix('payments').".date) <=",$to_date);
		}
		if($receive_id){
			$this->db->where($this->db->dbprefix('payments').".id NOT IN (SELECT payment_id FROM ".$this->db->dbprefix('receive_payment_items')." WHERE receive_id !=".$receive_id.")");
		}else{
			$this->db->where($this->db->dbprefix('payments').".id NOT IN (SELECT payment_id FROM ".$this->db->dbprefix('receive_payment_items').")");
		}
		$this->db->select("
							payments.id,
							payments.date,
							payments.reference_no as payment_ref,
							payments.amount,
							sales.reference_no as sale_ref,
							sales.customer,
							CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
							cash_accounts.name as paid_by
						");
		$this->db->join("sales","sales.id = payments.sale_id","inner");
		$this->db->join("users","users.id = payments.created_by","left");
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$this->db->group_by("payments.id");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getReceivePaymentByID($id = false){
		$q = $this->db->get_where("receive_payments",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getReceivePaymentItems($receive_id = false){
		$q = $this->db->get_where('receive_payment_items', array('receive_id' => $receive_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function addReceivePyament($data = false, $items = false){
		if($this->db->insert("receive_payments",$data)){
			$receive_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["receive_id"] = $receive_id;
					$this->db->insert("receive_payment_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateReceivePyament($id = false, $data = false, $items = false){
		if($this->db->update("receive_payments",$data,array("id"=>$id))){
			$this->db->delete("receive_payment_items",array("receive_id"=>$id));
			if($items){
				$this->db->insert_batch("receive_payment_items",$items);
			}
			return true;
		}
		return false;
	}
	
	public function deleteReceivePayment($id = false){
		if($id && $this->db->delete("receive_payments",array("id"=>$id))){
			$this->db->delete("receive_payment_items",array("receive_id"=>$id));
			return true;
		}
		return false;
	}
	


	
	
	
	
	
	
}
