<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Products_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductUnits(){
        $this->db->select("product_units.product_id,product_units.unit_qty,units.name as unit_name");
        $this->db->join("units","units.id = product_units.unit_id","inner");
        $this->db->order_by('product_units.unit_qty','desc');
        $q = $this->db->get("product_units");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->product_id][] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getActiveProductSerialID($product_id = false, $warehouse_id = false, $serial = false)
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

    public function getCategoryProducts($category_id = false,$parent_id = false)
    {
		if($parent_id > 0){
			$q = $this->db->get_where('products', array('category_id' => $parent_id,'subcategory_id' => $category_id));
		}else{
			$q = $this->db->get_where('products', array('category_id' => $category_id));
		}
        
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSubCategoryProducts($subcategory_id = false)
    {
        $q = $this->db->get_where('products', array('subcategory_id' => $subcategory_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductOptions($pid = false)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	
	public function getUnitbyProduct($pid = false,$baseunit = false)
	{
		if($baseunit==''){
			$baseunit = $this->getProductByID($pid)->unit;
		}
		$q = $this->db->query("SELECT
								cus_units.id,
								cus_units.`name`,
								cus_product_units.unit_qty,
								cus_product_units.unit_price
							FROM
								`cus_units`
							LEFT JOIN cus_product_units ON cus_product_units.unit_id = cus_units.id
							AND cus_product_units.product_id = '".$pid."'
							WHERE
								base_unit = '".$baseunit."'
							OR cus_units.id = '".$baseunit."'	
					");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

    public function getProductOptionsWithWH($pid = false)
    {
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where('warehouses_products_variants.warehouse_id',$this->session->userdata('warehouse_id'));
        }
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
	
	public function getProductBomItems($pid = false)
	{
		$this->db->where('bom_products.standard_product_id',$pid);
		$this->db->select('bom_products.*,products.name,products.code')
		->join('products','products.id= bom_products.product_id','inner')
		->from('bom_products');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

    public function getProductComboItems($pid = false)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.option_id as option_id, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('combo_items') . '.unit_price as price')
				->join('products', 'products.id=combo_items.item_id', 'left')->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', array('product_id' => $pid));
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

    public function getProductWithCategory($id = false)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('categories') . '.name as category')
        ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function has_purchase($product_id = false, $warehouse_id = NULL)
    {
        if($warehouse_id) { $this->db->where('warehouse_id', $warehouse_id); }
        $q = $this->db->get_where('purchase_items', array('product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function getProductDetails($id = false)
    {
        $this->db->select($this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . '.name, ' . $this->db->dbprefix('categories') . '.code as category_code, cost, price, quantity, alert_quantity')
            ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductDetail($id = false)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('tax_rates') . '.name as tax_rate_name, '.$this->db->dbprefix('tax_rates') . '.code as tax_rate_code, c.code as category_code, sc.code as subcategory_code', FALSE)
            ->join('tax_rates', 'tax_rates.id=products.tax_rate', 'left')
            ->join('categories c', 'c.id=products.category_id', 'left')
            ->join('categories sc', 'sc.id=products.subcategory_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSubCategories($parent_id = false) 
	{
        $this->db->select('id as id, name as text')
        ->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getAllWarehousesWithPQ($product_id = false)
    {
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where('warehouses.id',$this->session->userdata('warehouse_id'));
        }
        $this->db->select('' . $this->db->dbprefix('warehouses') . '.*, ' . $this->db->dbprefix('warehouses_products') . '.quantity, ' . $this->db->dbprefix('warehouses_products') . '.rack')
            ->join('warehouses_products', 'warehouses_products.warehouse_id=warehouses.id', 'left')
            ->where('warehouses_products.product_id', $product_id)
            ->group_by('warehouses.id');
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllWarehouseProducts($product_id = false){
		$q = $this->db->get_where("warehouses_products",array("product_id"=>$product_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

    public function getProductPhotos($id = false)
    {
        $q = $this->db->get_where("product_photos", array('product_id' => $id));
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

    public function addProduct($data = false, $items = false, $warehouse_qty = false, $product_attributes = false, $photos = false, $product_units = false, $items2 = false, $product_account=array(), $bom_items = false, $formulation_items = false, $convert = false, $convert_items = false)
    {

        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
			
			if($product_account){
				$product_account['product_id'] = $product_id;
				$this->db->insert('acc_product', $product_account);
			}
			if ($bom_items) {
                foreach ($bom_items as $bom_item) {
                    $bom_item['standard_product_id'] = $product_id;
                    $this->db->insert('bom_products', $bom_item);
                }
            }
			if ($formulation_items) {
                foreach ($formulation_items as $formulation_item) {
                    $formulation_item['main_product_id'] = $product_id;
                    $formulation_item['for_product_id'] = $product_id;
                    $this->db->insert('formulation_products', $formulation_item);
                }
            }
            if ($items) {
                foreach ($items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('combo_items', $item);
                }
            }
			if ($items2) {
                foreach ($items2 as $item2) {
                    $item2['product_id'] = $product_id;
                    $this->db->insert('digital_items', $item2);
                }
            }
            $warehouses = $this->site->getAllWarehouses();
     
			foreach ($warehouses as $warehouse) {
				$this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0, 'alert_quantity' => $data['alert_quantity']));
			}
            

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);
			
			if ($product_units){
				foreach ($product_units as $pr_unit){
					$pr_unit['product_id'] = $product_id;
					$this->db->insert('product_units', $pr_unit);
				}
			}
            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {
                    $pr_attr_details = $this->getPrductVariantByPIDandName($product_id, $pr_attr['name']);

                    $pr_attr['product_id'] = $product_id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    if ($pr_attr_details) {
                        $option_id = $pr_attr_details->id;
                    } else {
                        $this->db->insert('product_variants', $pr_attr);
                        $option_id = $this->db->insert_id();
                    }
                    

                    foreach ($warehouses as $warehouse) {
                        if (!$this->getWarehouseProductVariant($warehouse->id, $product_id, $option_id)) {
                            $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0));
                        }
                    }

                }
            }
			
			if($convert && $convert_items){
				$convert['product_id'] = $product_id;
				$this->db->insert('boms', $convert);
				$convert_id = $this->db->insert_id();
				foreach ($convert_items as $convert_item) {
					$convert_item['bom_id'] = $convert_id;
					$this->db->insert('bom_items', $convert_item);
				}
				$convert_item = array(
					'bom_id'=>$convert_id,
					'product_id'=>$product_id,
					'quantity'=>1,
					'unit_id'=>$data['unit'],
					'unit_qty'=>1,
					'type'=>"finished_good",
				);
				$this->db->insert('bom_items', $convert_item);
			}
			
            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', array('product_id' => $product_id, 'photo' => $photo));
                }
            }
            return true;
        }
        return false;

    }

    public function getPrductVariantByPIDandName($product_id = false, $name = false)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id, 'name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addAjaxProduct($data = false)
    {
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
            return $this->getProductByID($product_id);
        }
        return false;
    }

    public function add_products($products = array())
    {
        if (!empty($products)) {
			$warehouses = $this->site->getAllWarehouses();
			$product_acc = array();
            foreach ($products as $product) {
                $variants = explode('|', $product['variants']);
				$unit_qtys = explode('|', $product['unit_qtys']);
				$cost = $product['cost'];
                unset($product['variants']);
				unset($product['unit_qtys']);
                if ($this->db->insert('products', $product)) {
                    $product_id = $this->db->insert_id();
                    foreach ($variants as $variant) {
                        if ($variant && trim($variant) != '') {
                            $vat = array('product_id' => $product_id, 'name' => trim($variant));
                            $this->db->insert('product_variants', $vat);
                        }
                    }
					if($this->Settings->accounting == 1){
						$catd = $this->products_model->getCategoryByID($product['category_id']);
						if($catd){
								$product_acc[] = array(
														'product_id' => $product_id,
														'type' => 'standard',
														'stock_acc' => $catd->stock_acc,
														'adjustment_acc' => $catd->adjustment_acc,
														'convert_acc' => $catd->convert_acc,
														'usage_acc' => $catd->usage_acc,
														'cost_acc' => $catd->cost_acc,
														'discount_acc' => $catd->discount_acc,
														'sale_acc' => $catd->sale_acc,
														'expense_acc' => $catd->expense_acc,
														'pawn_acc' => $catd->pawn_acc,
													);
							
						}
					}
					foreach ($unit_qtys as $unit_qty) {
						$units = explode('=', $unit_qty);
						$unit_code = trim($units[0]);
						$unit_qty = trim($units[1]);
						$unit = $this->getUnitByCode($unit_code);
						$unit_id = $unit->id;
						if ($unit_id && trim($unit_qty) != '') {
							$value = array('unit_id' => $unit_id, 'product_id' => $product_id, 'unit_qty' => trim($unit_qty));
							$this->db->insert('product_units', $value);
						}
					}
					foreach ($warehouses as $warehouse) {
						$this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0, 'avg_cost' => $cost));

					}
					
                }
            }
            $this->checkProductUnit($product_id);
			if($product_acc){
				$this->db->insert_batch('acc_product',$product_acc);
			}
            return true;
        }
        return false;
    }


    public function checkProductUnit($product_id = false){
        if($product_id){
            $product = $this->db->get_where('products',array('id'=>$product_id));
            if ($product->num_rows() > 0) {
                $product = $product->row();
                $product_unit = $this->db->get_where('product_units',array('product_id'=>$product->id,'unit_id'=>$product->unit));
                if($product_unit->num_rows() > 0){
                    $product_unit = $product_unit->row();
                    if($product_unit->unit_qty <> 1){
                        $this->db->update('product_units',array('unit_qty'=>1),array('product_id'=>$product->id,'unit_id'=>$product->unit));
                    }
                }else{
                    $data = array(
                                    'product_id' => $product->id,
                                    'unit_id' => $product->unit,
                                    'unit_qty' => 1
                    );   
                    $this->db->insert('product_units',$data);           
                }
                return true;
            }else{
                return false;
            }
        }
        return false;
    }
	
	public function importSerials($data = false){
		if($data){
			$this->db->insert_batch('product_serials',$data);
			return true;
		}
		return false;
	}
	
	
	public function addSerials($data = false,$product_id = false,$warehouse_id = false){
		if($product_id && $warehouse_id){
			$this->db->where('(inactive !="1" OR `inactive` IS NULL )');
			$this->db->delete('product_serials', array('product_id' => $product_id,'warehouse_id' => $warehouse_id));
			if($data){
				$this->db->insert_batch('product_serials',$data);
			}
			return true;
		}
		return false;
	}


    public function getProductNames($term = false, $limit = 10)
    {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
		$this->db->where('products.inactive !=',1);
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, unit,' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ' . $this->db->dbprefix('product_variants') . '.name as vname, products.cost')
            ->where("type != 'combo' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->join('product_variants', 'product_variants.product_id=products.id', 'left')
            //->where('' . $this->db->dbprefix('product_variants') . '.name', NULL)
            ->group_by('products.id')->limit($limit);
			
		if ($this->Settings->set_custom_field) {
			$this->db->or_where("({$this->db->dbprefix('products')}.cf1 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf2 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf3 LIKE '%" . $term . "%'  OR {$this->db->dbprefix('products')}.cf4 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf5 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf6 LIKE '%" . $term . "%')");
		}	
		
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
	public function getRawProductNames($term = false, $limit = 10)
    {
		$this->db->where('products.inactive !=',1);
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, unit, price, cost,' . $this->db->dbprefix('products') . '.name as name')
            ->where("type = 'raw_material' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->group_by('products.id')->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
    public function getQASuggestions($term = false, $limit = 10)
    {
		$this->db->where('products.inactive !=',1);
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name')
            ->where("type != 'combo' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductsForPrinting($term = false, $limit = 10)
    {	
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
		$this->db->where('products.inactive !=',1);
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ' . $this->db->dbprefix('products') . '.cost as cost')
            ->where("(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
	public function getCategoriesForPrinting($term = false, $limit = 10)
    {
		$this->db->where('products.inactive !=',1);
        $this->db->select('*');
		$this->db->where("category_id",$term);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function updateProduct($id = false, $data = false, $items = false, $warehouse_qty = false, $product_attributes = false, $photos = false, $update_variants = false, $product_units = false, $items2 = false,  $product_account=array(), $stockmoves = false, $bom_items = false, $formulation_items = false, $convert = false, $convert_items = false)
    {

        if ($this->db->update('products', $data, array('id' => $id))) {
			$this->db->delete('bom_products', array('standard_product_id' => $id));
			$this->db->delete('combo_items', array('product_id' => $id));
			$this->db->delete('digital_items', array('product_id' => $id));
			$this->db->delete('formulation_products', array('main_product_id' => $id));
			
			if($bom = $this->getBomByProductID($id)){
				$this->db->delete('boms', array('product_id' => $id));
				$this->db->delete('bom_items', array('bom_id' => $bom->id));
			}
			
			if($stockmoves){
				$this->db->insert('stockmoves',$stockmoves);
			}
			
			if($product_account){
				if($this->getProductAccByProductId($id)){
					$this->db->update('acc_product', $product_account, array('product_id' => $id));
				}else{
					$product_account['product_id'] = $id;
					$this->db->insert("acc_product",$product_account);
				}
			}
			
			if($product_account){
				if($this->getProductAccByProductId($id)){
					$this->db->update('acc_product', $product_account, array('product_id' => $id));
				}else{
					$product_account['product_id'] = $id;
					$this->db->insert("acc_product",$product_account);
				}
			}
			
			if ($bom_items) {
                foreach ($bom_items as $item) {
                    $item['standard_product_id'] = $id;
                    $this->db->insert('bom_products', $item);
                }
            }
			
			if ($formulation_items) {
                foreach ($formulation_items as $item) {
                    $item['main_product_id'] = $id;
                    $item['for_product_id'] = $id;
                    $this->db->insert('formulation_products', $item);
                }
            }
			
            if ($items) {
                foreach ($items as $item) {
                    $item['product_id'] = $id;
                    $this->db->insert('combo_items', $item);
                }
            }
			if ($items2) {
                foreach ($items2 as $item2) {
                    $item2['product_id'] = $id;
                    $this->db->insert('digital_items', $item2);
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    $this->db->update('warehouses_products', array('rack' => $wh_qty['rack']), array('product_id' => $id, 'warehouse_id' => $wh_qty['warehouse_id']));
                }
            }

            if ($update_variants) {
                $this->db->update_batch('product_variants', $update_variants, 'id');
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', array('product_id' => $id, 'photo' => $photo));
                }
            }
			
			if ($product_units){
				$this->db->delete('product_units', array('product_id' => $id));
				foreach ($product_units as $pr_unit){
					$pr_unit['product_id'] = $id;
					$this->db->insert('product_units', $pr_unit);
				}
			}

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {

                    $pr_attr['product_id'] = $id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    $this->db->insert('product_variants', $pr_attr);
                    
                }
            }
			
			if($convert && $convert_items){
				$this->db->insert('boms', $convert);
				$convert_id = $this->db->insert_id();
				foreach ($convert_items as $convert_item) {
					$convert_item['bom_id'] = $convert_id;
					$this->db->insert('bom_items', $convert_item);
				}
				$convert_item = array(
					'bom_id'=>$convert_id,
					'product_id'=>$id,
					'quantity'=>1,
					'unit_qty'=>1,
					'unit_id'=>$data['unit'],
					'type'=>"finished_good",
				);
				$this->db->insert('bom_items', $convert_item);
			}
            $this->checkProductUnit($id);
            return true;
        } else {
            return false;
        }
    }

    public function updateProductOptionQuantity($option_id = false, $warehouse_id = false, $quantity = false, $product_id = false)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            if ($this->db->update('warehouses_products_variants', array('quantity' => $quantity), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        }
        return FALSE;
    }

    public function updatePrice($data = array())
    {
        if ($this->db->update_batch('products', $data, 'code')) {
            return true;
        }
        return false;
    }

    public function deleteProduct($id = false)
    {
		if($id && $id > 0){
			if ($this->db->delete('products', array('id' => $id)) && $this->db->delete('warehouses_products', array('product_id' => $id))) {
				$this->db->delete('warehouses_products_variants', array('product_id' => $id));
				$this->db->delete('product_variants', array('product_id' => $id));
				$this->db->delete('product_photos', array('product_id' => $id));
				$this->db->delete('product_prices', array('product_id' => $id));
				$this->db->delete('acc_product', array('product_id' => $id));
				$this->db->delete('product_serials', array('product_id' => $id));
				$this->db->delete('product_units', array('product_id' => $id));
				$this->db->delete('bom_products', array('standard_product_id' => $id));
				$this->db->delete('formulation_products', array('main_product_id' => $id));
				$this->db->delete('combo_items', array('product_id' => $id));
				if($bom = $this->getBomByProductID($id)){
					$this->db->delete('boms', array('product_id' => $id));
					$this->db->delete('bom_items', array('bom_id' => $bom->id));
				}
				return true;
			}
		}
        return FALSE;
    }
	
	public function getBomByProductID($product_id = false){
		$q = $this->db->get_where('boms',array('product_id'=>$product_id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

    public function totalCategoryProducts($category_id = false)
    {
        $q = $this->db->get_where('products', array('category_id' => $category_id));
        return $q->num_rows();
    }

    public function getCategoryByCode($code = false)
    {
        $q = $this->db->get_where('categories', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getCategoryByID($id = false)
    {
        $q = $this->db->get_where('categories', array('id' => $id), 1);
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
	
	public function getCostAdjustmentByID($id = false)
    {
        $q = $this->db->get_where('cost_adjustments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getCostAdjustmentItems($adjustment_id = false)
    {
        $this->db->select('cost_adjustment_items.*, products.code as product_code, products.name as product_name, products.image, products.details as details')
            ->join('products', 'products.id=cost_adjustment_items.product_id', 'left')
            ->group_by('cost_adjustment_items.id')
            ->order_by('id', 'asc');

        $this->db->where('adjustment_id', $adjustment_id);

        $q = $this->db->get('cost_adjustment_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAdjustmentByID($id = false)
    {
        $q = $this->db->get_where('adjustments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    public function getAdjustmentItems($adjustment_id = false)
    {
        $this->db->select('adjustment_items.*, products.code as product_code, products.name as product_name, products.image, products.details as details, product_variants.name as variant,units.name as unit_name')
            ->join('products', 'products.id=adjustment_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=adjustment_items.option_id', 'left')
            ->join('units','units.id = adjustment_items.product_unit_id','left')
            ->group_by('adjustment_items.id')
            ->order_by('id', 'asc');

        $this->db->where('adjustment_id', $adjustment_id);

        $q = $this->db->get('adjustment_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


	public function addCostAdjustment($data = false, $products = false,$accTrans = false,$stockmoves = false)
    {
        if ($this->db->insert('cost_adjustments', $data)) {
            $adjustment_id = $this->db->insert_id();
            foreach ($products as $product) {
                $product['adjustment_id'] = $adjustment_id;
                $this->db->insert('cost_adjustment_items', $product);
				$this->db->update('products', array('cost' => $product['new_cost']), array('id' => $product['product_id']));		
            }
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $adjustment_id;
					$accTran['reference'] = $data['reference_no'];
					$this->db->insert('acc_tran', $accTran);
				}
			}
			foreach($stockmoves as $stockmove){
				$stockmove['transaction_id'] = $adjustment_id;
				$stockmove['reference_no'] = $data['reference_no'];
				$this->db->insert('stockmoves', $stockmove);
			}
			
            return true;
        }
        return false;
    }
	public function updateCostAdjustment($id = false, $data = false, $products = false, $accTrans = false, $stockmoves = false)
    {

        if ($this->db->update('cost_adjustments', $data, array('id' => $id))){
            $this->db->delete('cost_adjustment_items', array('adjustment_id' => $id));
			$this->site->deleteAccTran('CostAdjustment',$id);
			$this->site->deleteStockmoves('CostAdjustment',$id);
			foreach ($products as $product) {
                $product['adjustment_id'] = $id;
                $this->db->insert('cost_adjustment_items', $product); 
				$this->db->update('products', array('cost' => $product['new_cost']), array('id' => $product['product_id']));					
            }
			if($accTrans){
				$this->db->insert_batch('acc_tran', $accTrans);
			}
			if($stockmoves){
				$this->db->insert_batch('stockmoves', $stockmoves);
			}
            return true;
        }
        return false;
    }
	
	public function getAdjustmentItemSerial($product_id=false, $adjustment_id = false, $serial_no = false)
	{
		if($product_id){
			$this->db->where("product_id", $product_id);
		}
		if($adjustment_id){
			$this->db->where("adjustment_id", $adjustment_id);
		}
		if($serial_no){
			$this->db->where("serial_no", $serial_no);
		}
		$q = $this->db->get('adjustment_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}
	
    public function addAdjustment($data = false, $products = false, $stockmoves = false, $accTrans = false, $product_serials = false)
    {
        if ($this->db->insert('adjustments', $data)) {
            $adjustment_id = $this->db->insert_id();
			if($product_serials){
				foreach($product_serials as $product_serial){
					$product_serial['adjustment_id'] = $adjustment_id;
					$this->db->insert('product_serials', $product_serial);
					$this->db->update('stockmoves',array('serial_no'=>$product_serial['serial']),array('product_id'=>$product_serial['product_id'],'warehouse_id'=>$product_serial['warehouse_id'],'serial_no'=>$product_serial['serial']));
				}
			}
            foreach ($products as $product) {
                $product['adjustment_id'] = $adjustment_id;
                $this->db->insert('adjustment_items', $product);   
            }
			if($stockmoves){
				foreach($stockmoves as $stockmove){
					if($stockmove['reactive']!=1){
						unset($stockmove['serial_no']);
					}
					unset($stockmove['reactive']);
					$stockmove['transaction_id'] = $adjustment_id;
					$this->db->insert('stockmoves', $stockmove);
				}
			}
			
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $adjustment_id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
			
            return true;
        }
        return false;
    }

    public function updateAdjustment($id = false, $data = false, $products = false, $stockmoves = false, $accTrans = false, $product_serials = false)
    {

        if ($this->db->update('adjustments', $data, array('id' => $id)) && 
            $this->db->delete('adjustment_items', array('adjustment_id' => $id))) {
			$this->db->delete('product_serials', array('adjustment_id' => $id));
			$this->site->deleteStockmoves('QuantityAdjustment',$id);
			$this->site->deleteAccTran('QuantityAdjustment',$id);			
			if($product_serials){
				foreach($product_serials as $product_serial){
					$product_serial['adjustment_id'] = $id;
					$this->db->insert('product_serials', $product_serial);
					$this->db->update('stockmoves',array('serial_no'=>$product_serial['serial']),array('product_id'=>$product_serial['product_id'],'warehouse_id'=>$product_serial['warehouse_id'],'serial_no'=>$product_serial['serial']));
				}
			}
            foreach ($products as $product) {
                $product['adjustment_id'] = $id;
                $this->db->insert('adjustment_items', $product);
            }
			if($stockmoves){
				foreach($stockmoves as $stockmove){
					if($stockmove['reactive']!=1){
						unset($stockmove['serial_no']);
					}
					unset($stockmove['reactive']);
					$stockmove['transaction_id'] = $id;
					$this->db->insert('stockmoves', $stockmove);
				}
			}
			
			if($accTrans){
				$this->db->insert_batch('acc_tran', $accTrans);
			}
            return true;
        }
        return false;
    }
	
	
	public function approveAdjustment($id = false, $data = false, $stockmoves = false, $accTrans = false, $product_serials = false)
    {
        if ($this->db->update('adjustments', $data, array('id' => $id))) {
			if($product_serials){
				foreach($product_serials as $product_serial){
					$this->db->insert('product_serials', $product_serial);
					$this->db->update('stockmoves',array('serial_no'=>$product_serial['serial']),array('product_id'=>$product_serial['product_id'],'warehouse_id'=>$product_serial['warehouse_id'],'serial_no'=>$product_serial['serial']));
				}
			}
			if($stockmoves){
				foreach($stockmoves as $stockmove){
					if($stockmove['reactive']!=1){
						unset($stockmove['serial_no']);
					}
					unset($stockmove['reactive']);
					$this->db->insert('stockmoves', $stockmove);
				}
			}
			
			if($accTrans){
				$this->db->insert_batch('acc_tran', $accTrans);
			}
            return true;
        }
        return false;
    }
	
	

    public function deleteAdjustment($id = false)
    {
		if($id && $id > 0){
			if ( $this->db->delete('adjustments', array('id' => $id))) {
				$this->site->deleteStockmoves('QuantityAdjustment',$id);
				$this->site->deleteAccTran('QuantityAdjustment',$id);	
				$this->db->delete('product_serials',array('adjustment_id' => $id));
				if($this->Settings->accounting_method == '0'){
					$items = $this->getAdjustmentItems($id);
					foreach($items as $item){
						$this->site->updateFifoCost($item->product_id);
					}
				}else if($this->Settings->accounting_method == '1'){
					$items = $this->getAdjustmentItems($id);
					foreach($items as $item){
						$this->site->updateLifoCost($item->product_id);
					}				
				}else if($this->Settings->accounting_method == '3'){
					$items = $this->getAdjustmentItems($id);
					foreach($items as $item){
						$this->site->updateProductMethod($item->product_id);
					}				
				}
				$this->db->delete('adjustment_items', array('adjustment_id' => $id));			
						
				return true;
			}
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
	
	public function getProductQuantityExpiry($product_id = false, $warehouse = false, $expiry = false){
		$this->db->select('SUM(COALESCE(quantity, 0)) as quantity');
		$this->db->where('product_id', $product_id);
		if ($warehouse) {
            $this->db->where('stockmoves.warehouse_id', $warehouse);
        }
		if($expiry){
			if($expiry == '0000-00-00'){
				$this->db->where('(expiry = "'.$expiry.'" OR expiry IS NULL)');
			}else{
				$this->db->where('expiry',$expiry);
			}
		}
		$q = $this->db->get('stockmoves');
		if ($q->num_rows() > 0) {
            return  $q->row_array(); //$q->row();
        }
        return FALSE;
	}

    public function addQuantity($product_id = false, $warehouse_id = false, $quantity = false, $rack = NULL)
    {

        if ($this->getProductQuantity($product_id, $warehouse_id)) {
            if ($this->updateQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return TRUE;
            }
        } else {
            if ($this->insertQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function insertQuantity($product_id = false, $warehouse_id = false, $quantity = false, $rack = NULL)
    {
        $product = $this->site->getProductByID($product_id);
        if ($this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity, 'rack' => $rack, 'avg_cost' => $product->cost))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function updateQuantity($product_id = false, $warehouse_id = false, $quantity = false, $rack = NULL)
    {
        $data = $rack ? array('quantity' => $quantity, 'rack' => $rack) : $data = array('quantity' => $quantity);
        if ($this->db->update('warehouses_products', $data, array('product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function products_count($category_id = false, $subcategory_id = NULL)
    {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->from('products');
        return $this->db->count_all_results();
    }

    public function fetch_products($category_id = false, $limit = false, $start = false, $subcategory_id = NULL)
    {

        $this->db->limit($limit, $start);
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->order_by("id", "asc");
        $query = $this->db->get("products");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function syncVariantQty($option_id = false)
    {
        $wh_pr_vars = $this->getProductWarehouseOptions($option_id);
        $qty = 0;
        foreach ($wh_pr_vars as $row) {
            $qty += $row->quantity;
        }
        if ($this->db->update('product_variants', array('quantity' => $qty), array('id' => $option_id))) {
            return TRUE;
        }
        return FALSE;
    }

    public function getProductWarehouseOptions($option_id = false)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function setRack($data = false)
    {
        if ($this->db->update('warehouses_products', array('rack' => $data['rack']), array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']))) {
            return TRUE;
        }
        return FALSE;
    }

    public function getSoldQty($id = false)
    {
		$curDate = date('Y-m-d');
        $this->db->select("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('sale_items') . ".quantity ) as sold, SUM( " . $this->db->dbprefix('sale_items') . ".subtotal ) as amount")
            ->from('sales')
            ->join('sale_items', 'sales.id=sale_items.sale_id', 'left')
            ->group_by("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('sale_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD("'.$curDate.'", INTERVAL 1 MONTH)')
            ->order_by("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchasedQty($id = false)
    {
		$curDate = date('Y-m-d');
        $this->db->select("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('purchase_items') . ".quantity ) as purchased, SUM( " . $this->db->dbprefix('purchase_items') . ".subtotal ) as amount")
            ->from('purchases')
            ->join('purchase_items', 'purchases.id=purchase_items.purchase_id', 'left')
            ->group_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('purchase_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD("'.$curDate.'", INTERVAL 1 MONTH)')
            ->order_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllVariants()
    {
        $q = $this->db->get('variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseProductVariant($warehouse_id = false, $product_id = false, $option_id = NULL)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('product_id' => $product_id, 'option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchaseItems($purchase_id = false)
    {
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTransferItems($transfer_id = false)
    {
        $q = $this->db->get_where('purchase_items', array('transfer_id' => $transfer_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUnitByCode($code = false)
    {
        $q = $this->db->get_where("units", array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getBrandByName($name = false)
    {
        $q = $this->db->get_where('brands', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getBrandByCode($code = false)
    {
        $q = $this->db->get_where('brands', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getStockCountProductByEnding($ending_date = false, $warehouse_id = false, $type = false, $categories = false , $brands = false){
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
		
		$this->db->where('IFNULL('.$this->db->dbprefix("products").'.inactive,0) !=',1);
        $this->db->select("products.id, products.code, products.name, IFNULL(stockmove.quantity,0) as quantity")
		->join('(SELECT product_id, sum( quantity ) AS quantity FROM '.$this->db->dbprefix("stockmoves").' WHERE warehouse_id='.$warehouse_id.' and date(date) <= "'.$ending_date.'" GROUP BY product_id) as stockmove','stockmove.product_id = products.id','LEFT')
		->where_in('products.type', array('standard','raw_material','asset'))
		->group_by('products.id')
        ->order_by('products.code', 'asc');
        if ($categories) {
            $r = 1;
            $this->db->group_start();
            foreach ($categories as $category) {
                if ($r == 1) {
                    $this->db->where('products.category_id', $category);
                } else {
                    $this->db->or_where('products.category_id', $category);
                }
                $r++;
            }
            $this->db->group_end();
        }
        if (count($brands) > 1) {
            $r = 1;
            $this->db->group_start();
            foreach ($brands as $brand) {
                if ($r == 1) {
                    $this->db->where('products.brand', $brand);
                } else {
                    $this->db->or_where('products.brand', $brand);
                }
                $r++;
            }
            $this->db->group_end();
        }

        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

    public function getStockCountProducts($warehouse_id = false, $type = false, $categories = NULL, $brands = NULL)
    {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
		
		$this->db->where('products.inactive !=',1);
        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('warehouses_products')}.quantity as quantity")
        ->join('warehouses_products', 'warehouses_products.product_id=products.id AND warehouses_products.warehouse_id='.$warehouse_id.'', 'left')
        ->where_in('products.type', array('standard','raw_material','asset'))
        ->order_by('products.code', 'asc');
        if ($categories) {
            $r = 1;
            $this->db->group_start();
            foreach ($categories as $category) {
                if ($r == 1) {
                    $this->db->where('products.category_id', $category);
                } else {
                    $this->db->or_where('products.category_id', $category);
                }
                $r++;
            }
            $this->db->group_end();
        }
        if (count($brands) > 1) {
            $r = 1;
            $this->db->group_start();
            foreach ($brands as $brand) {
                if ($r == 1) {
                    $this->db->where('products.brand', $brand);
                } else {
                    $this->db->or_where('products.brand', $brand);
                }
                $r++;
            }
            $this->db->group_end();
        }

        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getStockCountProductVariantsByEnding($ending_date = false, $warehouse_id = false, $product_id = false)
    {
        $this->db->select("product_variants.name, IFNULL(stockmove.quantity,0) as quantity")
			->join('(SELECT option_id, sum( quantity ) AS quantity FROM '.$this->db->dbprefix("stockmoves").' WHERE product_id='.$product_id.' AND warehouse_id='.$warehouse_id.' and date(date) <= "'.$ending_date.'" GROUP BY option_id) as stockmove','stockmove.option_id = product_variants.id','LEFT');
        $q = $this->db->get_where('product_variants', array('product_variants.product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getStockCountProductVariants($warehouse_id = false, $product_id = false)
    {
        $this->db->select("{$this->db->dbprefix('product_variants')}.name, {$this->db->dbprefix('warehouses_products_variants')}.quantity as quantity")
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left');
        $q = $this->db->get_where('product_variants', array('product_variants.product_id' => $product_id, 'warehouses_products_variants.warehouse_id' => $warehouse_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function addStockCount($data = false)
    {
        if ($this->db->insert('stock_counts', $data)) {
            return TRUE;
        }
        return FALSE;
    }

    public function finalizeStockCount($id = false, $data = false, $products = false)
    {
        if ($this->db->update('stock_counts', $data, array('id' => $id))) {
            foreach ($products as $product) {
                $this->db->insert('stock_count_items', $product);
            }
            return TRUE;
        }
        return FALSE;
    }

    public function getStouckCountByID($id = false)
    {
        $q = $this->db->get_where("stock_counts", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getStockCountItems($stock_count_id = false)
    {
        
		$this->db->select("{$this->db->dbprefix('stock_count_items')}.*, {$this->db->dbprefix('products')}.name as name")
            ->join('products', 'products.id=stock_count_items.product_id', 'left');
		$q = $this->db->get_where("stock_count_items", array('stock_count_id' => $stock_count_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return NULL;
    }

    public function getAdjustmentByCountID($count_id = false)
    {
        $q = $this->db->get_where('adjustments', array('count_id' => $count_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductVariantID($product_id = false, $name = false)
    {
        $q = $this->db->get_where("product_variants", array('product_id' => $product_id, 'name' => $name), 1);
        if ($q->num_rows() > 0) {
            $variant = $q->row();
            return $variant->id;
        }
        return NULL;
    }
	
	public function getAllWarehousesByUser($warehouse_id = false) 
	{
        $wid = explode(',', $warehouse_id);
        $this->db->select('warehouses.*')
                 ->from('warehouses')
                 ->where_in("id", $wid);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
			
	public function getReference()
	{
		$this->db->select('reference_no')
				 ->order_by('date', 'desc')
				 ->limit(1);
		$q = $this->db->get('convert');
        if ($q->num_rows() > 0) {
			foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	
	public function insertConvert($data = false)
    {
        if ($this->db->insert('convert', $data)) {
            $convert_id = $this->db->insert_id();
            return $convert_id;
        }
    }
	
	/*************Using Stock*****************/
	
	public function addUsingStock($data = array(), $items = array(), $stockmoves = array(), $accTrans = array())
    {
        if ($this->db->insert('using_stocks', $data)) {
            $using_id = $this->db->insert_id();
            foreach ($items as $item) {
				$item['using_id'] = $using_id;
				$this->db->insert('using_stock_items', $item);
            }
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $using_id;
					$accTran['reference'] = $data['reference_no'];
					$this->db->insert('acc_tran', $accTran);
				}
			}

			foreach ($stockmoves as $stockmove) {
				$stockmove['transaction_id'] = $using_id;
				$stockmove['reference_no'] = $data['reference_no'];
				$this->db->insert('stockmoves', $stockmove);
			}
			
			if($data['using_id'] > 0){
				$this->syncUsingStockStatus($data['using_id']);
			}

            return true;
        }
        return false;
    }
	
	public function updateUsingStock($id = false, $data = array(), $items = array(),$stockmoves = array(), $accTrans = array())
    {        
		$status = 'completed';
		
        if ($this->db->update('using_stocks', $data, array('id' => $id))) {
            $this->db->delete('using_stock_items', array('using_id' => $id));
			$this->site->deleteStockmoves('UsingStock',$id);
			$this->site->deleteAccTran('UsingStock',$id);
            foreach ($items as $item) {
					$item['using_id'] = $id;
                    $this->db->insert('using_stock_items', $item);
            }
			
			if($accTrans){
				$this->db->insert_batch('acc_tran', $accTrans);
			}
			
			if ($status == 'sent' || $status == 'completed') {
				foreach ($stockmoves as $stockmove) {
					$stockmove['transaction_id'] = $id;
					$this->db->insert('stockmoves', $stockmove);
				}
			}
            return true;
        }
        return false;
		
		
    }
	
	public function getAllUsingStockItems($using_id = false)
    {

		$this->db->select('using_stock_items.*, product_variants.name as variant, products.unit')
			->from('using_stock_items')
			->join('products', 'products.id=using_stock_items.product_id', 'left')
			->join('product_variants', 'product_variants.id=using_stock_items.option_id', 'left')
			->group_by('using_stock_items.id')
			->where_in('using_id', $using_id);
        
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getUsingStockByID($id = false)
    {
        $q = $this->db->get_where('using_stocks', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
		
    public function deleteUsingStock($id = false)
    {
		if($id && $id > 0){
			if($this->db->delete('using_stocks', array('id' => $id))) {
				$return_using_stocks = $this->getUsingStockByUsingID($id);
				if($return_using_stocks){
					foreach($return_using_stocks as $return_using_stock){
						$return_id = $return_using_stock->id;
						if($this->db->delete('using_stocks', array('id' => $return_id))) {
							$this->site->deleteStockmoves('UsingStock',$return_id);
							$this->site->deleteAccTran('UsingStock',$return_id);
							$this->db->delete('using_stock_items', array('using_id' => $return_id));
						}
						
					}
				}
				
				$this->site->deleteStockmoves('UsingStock',$id);
				$this->site->deleteAccTran('UsingStock',$id);
				if($this->Settings->accounting_method == '0'){
					$items = $this->getUsingStockItems($id);
					foreach($items as $item){
						$this->site->updateFifoCost($item->product_id);
					}
				}else if($this->Settings->accounting_method == '1'){
					$items = $this->getUsingStockItems($id);
					foreach($items as $item){
						$this->site->updateLifoCost($item->product_id);
					}				
				}else if($this->Settings->accounting_method == '3'){
					$items = $this->getUsingStockItems($id);
					foreach($items as $item){
						$this->site->updateProductMethod($item->product_id);
					}				
				}
				$this->db->delete('using_stock_items', array('using_id' => $id));
				return true;
			}
		}
        
        return FALSE;
    }
	
	public function getUsingStockByUsingID($using_id = false){
		$q = $this->db->get_where('using_stocks',array('using_id'=>$using_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getUsingStockItems($using_id = false)
	{
		$this->db->select('using_stock_items.*, product_variants.name as variant, products.code,products.name,units.name as unit_name')
			->from('using_stock_items')
			->join('products', 'products.id=using_stock_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=using_stock_items.option_id', 'left')
            ->join('units','units.id = using_stock_items.product_unit_id','left')
			->group_by('using_stock_items.id')
			->where('using_id', $using_id);
       
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
	}

	public function getProductOptionsConverts($product_id = false, $warehouse_id = false, $zero_check = TRUE)
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
	
	public function getConvertItems($convert_id = false)
    {
		$this->db->select("products.code,products.name,convert_items.*");
		$this->db->join("products","products.id = convert_items.product_id","left");
		$q = $this->db->get_where("convert_items",array("convert_id"=>$convert_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getConvertByID($id = false)
    {

        $q = $this->db->get_where('converts', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
	public function addConvert($data = false, $raw_materials = false, $finished_goods = false, $stockmoves = false, $accTrans =false)
    {
        if ($this->db->insert('converts', $data)) {
            $convert_id = $this->db->insert_id();
            foreach ($raw_materials as $raw_material) {
				$raw_material['convert_id'] = $convert_id;
				$this->db->insert('convert_items', $raw_material);
            }
			foreach ($finished_goods as $finished_good) {
				$finished_good['convert_id'] = $convert_id;
				$this->db->insert('convert_items', $finished_good);
            }
			foreach($stockmoves as $stockmove){
				$stockmove['transaction_id'] = $convert_id;
				$this->db->insert('stockmoves', $stockmove);
				if($stockmove['quantity'] > 0){
					$cal_cost = $this->site->updateAVGCost($stockmove['product_id'],"Convert",$convert_id);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $convert_id;
					$this->db->insert('acc_tran', $accTran);
				}
			}			
            return true;
        }
        return false;
    }
	
	public function updateConvert($id = false, $data = false, $raw_materials = false, $finished_goods = false, $stockmoves = false, $accTrans =false)
    { 
        if ($this->db->update('converts', $data, array('id' => $id))) {
            $this->db->delete('convert_items', array('convert_id' => $id));
			$this->site->deleteStockmoves('Convert',$id);
			$this->site->deleteAccTran('Convert',$id);
            if($raw_materials){
				$this->db->insert_batch('convert_items', $raw_materials);
			}
			if($finished_goods){
				$this->db->insert_batch('convert_items', $finished_goods);
			}
			foreach($stockmoves as $stockmove){
				$this->db->insert('stockmoves', $stockmove);
				if($stockmove['quantity'] > 0 ){
					$cal_cost = $this->site->updateAVGCost($stockmove['product_id'],"Convert",$id);
				}
			}
			if($accTrans){
				$this->db->insert_batch('acc_tran', $accTrans);
			}
            return true;
        }
        return false;
    }
	public function deleteConvert($id = false)
    {
		if($id && $id > 0){	
			$convertItems = $this->getConvertItems($id);
			if ($this->db->delete('converts', array('id' => $id)) && 
				$this->db->delete('convert_items', array('convert_id' => $id))) {
				$this->site->deleteStockmoves('Convert',$id);
				$this->site->deleteAccTran('Convert',$id);
				if($convertItems){
					foreach($convertItems as $convertItem){
						$this->site->updateAVGCost($convertItem->product_id);
					}				
				}
				return true;
			}
		}
        return FALSE;
    }
	
	public function deleteVariant($id = false)
	{		
		if($id && $id > 0){
			if($this->db->where("id",$id)->delete("product_variants")){
				return true;
			}
		}
		return false;
	}
	
	public function getVariantByID($id = NULL)
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getVariantByProductID($id = NULL)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $id));
        if ($q->num_rows() > 0) {
			foreach (($q->result()) as $row) {
				$data[] = $row;
			}
			return $q->result();
        }
        return FALSE;
    }
	
	public function getProductDigitalItems($pid = false)
    {
		$this->db->where('products.inactive !=',1);
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('digital_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('digital_items') . '.unit_price as price, ' . $this->db->dbprefix('digital_items') . '.option_id as option_id')
			 ->join('products', 'products.code=digital_items.item_code', 'left')
			 ->group_by('digital_items.id');
			 
        $q = $this->db->get_where('digital_items', array('product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	/*=======UPDATE 10-02-2018=======*/
	
	public function getAllProductNames($term = false, $limit = 10)
    {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
		$this->db->where('products.inactive !=',1);
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate, type, unit, sale_unit, tax_method, purchase_unit')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id')
			->where("track_quantity = '1' AND type != 'combo' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getAllSupplier($term = false, $limit = 10)
    {
        $this->db->select('companies.id, companies.name,companies.company')
            ->group_by('companies.id')
			->where("group_name = 'supplier' AND "
                . "(" . $this->db->dbprefix('companies') . ".name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('companies') . ".name, ' (', company, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	
	public function addScanStock($data = array(), $items = array())
    {
        $status = $data['status'];
        if ($this->db->insert('scan_stocks', $data)) {
            $scan_stock_id = $this->db->insert_id();
            foreach ($items as $item) {
				$item['scan_stock_id'] = $scan_stock_id;
                $this->db->insert('scan_stock_items', $item);                
            }
            return true;
        }
        return false;
    }
	
	public function updateScanStock($id  = false, $data = array(), $items = array())
    {        
        $status = 'completed';
        if ($this->db->update('scan_stocks', $data, array('id' => $id))) {            
            $this->db->delete("scan_stock_items", array('scan_stock_id' => $id));
            foreach ($items as $item) {                
				$item['scan_stock_id'] = $id;
                $this->db->insert('scan_stock_items', $item);
            }
            return true;
        }
        return false;
    }
	
	public function getAllScanStockItems($id = false, $status = false)
    {
		$this->db->select('scan_stock_items.*, product_variants.name as variant, products.unit')
			->from('scan_stock_items')
			->join('products', 'products.id=scan_stock_items.product_id', 'left')
			->join('product_variants', 'product_variants.id=scan_stock_items.option_id', 'left')
			->group_by('scan_stock_items.id')
			->where('scan_stock_id', $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getScanStockByID($id = false)
    {
        $q = $this->db->get_where('scan_stocks', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function deleteScanStock($id = false)
    {        
		if($id && $id > 0){
			if ($this->db->delete('scan_stocks', array('id' => $id)) && 
				$this->db->delete("scan_stock_items", array('scan_stock_id' => $id))) {            
				return true;
			}
		}
        return FALSE;
    }
		
	public function getSaleOrderByID($id = false)
    {
        $q = $this->db->get_where('sale_orders', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getProductSerialDetailsByProductId($product_id = false, $warehouse_id = false)
	{	

		$q = $this->db->get_where('product_serials',array('product_id'=>$product_id,'warehouse_id'=>$warehouse_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getProductSerial($serial=false, $product_id = false, $warehouse_id = false, $adjustment_id = false)
	{
		if($adjustment_id){
			$this->db->where("adjustment_id !=", $adjustment_id);
		}	
		$q = $this->db->get_where('product_serials', array('product_id' => $product_id,'serial' => $serial,'warehouse_id' => $warehouse_id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}
	
	public function getProductAccByProductId($product_id = false)
    {
        $q = $this->db->get_where('acc_product', array('product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getProductGroupPrices($product_id = false)
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
	
	public function getProductFormulation($pid = false)
	{
		$this->db->where('formulation_products.main_product_id',$pid);
		$this->db->select('formulation_products.*,products.name,products.code')
		->join('products','products.id= formulation_products.for_product_id','inner')
		->from('formulation_products');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function rateProductItem($id = false)
	{
		$product = $this->getProductByID($id);
		if($product->rate > 0){
			$this->db->where("id",$id)->update("products", array("rate"=>0));
			return true;
		}else{
			$this->db->where("id",$id)->update("products", array("rate"=>1));
			return true;
		}
		return false;
	}
	
	public function updateBarcodeSetting($barcode_setting = false, $id = false)
	{
		if($barcode_setting){
			$this->db->update('barcode_settings',$barcode_setting, array('id'=>$id));
			return true;
		}
		return false;
	}
	
	public function addBarcodeStyle($date = false)
	{
		if($date){
			$this->db->insert('barcode_settings',$date);
			return true;
		}
		return false;
	}
	
	public function getBarcodeSettingByID($id = false)
	{
		$q = $this->db->get_where('barcode_settings',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getBarcodeStyle()
	{
		$q = $this->db->get('barcode_settings');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getUsingStockItemWithReturn($using_id = false)
	{
		if($using_id){
			$q = $this->db->query("SELECT
								".$this->db->dbprefix('using_stock_items').".*, 
								IFNULL(using_stock_item_returns.quantity_return,0) as quantity_return
							FROM
								".$this->db->dbprefix('using_stock_items')."
							LEFT JOIN (
								SELECT
									".$this->db->dbprefix('using_stocks').".using_id,
									".$this->db->dbprefix('using_stock_items').".product_id,
									".$this->db->dbprefix('using_stock_items').".product_cost,
									".$this->db->dbprefix('using_stock_items').".product_unit_id,
									SUM(
										abs(IFNULL(unit_quantity, 0))
									) AS quantity_return
								FROM
									".$this->db->dbprefix('using_stocks')."
								INNER JOIN ".$this->db->dbprefix('using_stock_items')." ON ".$this->db->dbprefix('using_stock_items').".using_id = ".$this->db->dbprefix('using_stocks').".id
								WHERE
									".$this->db->dbprefix('using_stocks').".using_id = ".$using_id."
								AND ".$this->db->dbprefix('using_stocks').".status = 'return'
								GROUP BY
									".$this->db->dbprefix('using_stock_items').".product_id,
									".$this->db->dbprefix('using_stock_items').".product_cost,
									".$this->db->dbprefix('using_stock_items').".product_unit_id
							) AS using_stock_item_returns ON using_stock_item_returns.product_id = ".$this->db->dbprefix('using_stock_items').".product_id
							AND using_stock_item_returns.product_cost = ".$this->db->dbprefix('using_stock_items').".product_cost
							AND using_stock_item_returns.product_unit_id = ".$this->db->dbprefix('using_stock_items').".product_unit_id
							WHERE
								".$this->db->dbprefix('using_stock_items').".using_id = ".$using_id."
						");
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
	
	public function syncUsingStockStatus($using_id = false)
	{
		$q = $this->db->query("SELECT
									sum(".$this->db->dbprefix('using_stock_items').".unit_quantity) AS quantity_using,
									IFNULL(using_stock_item_returns.quantity_return,0) as quantity_return
								FROM
									".$this->db->dbprefix('using_stock_items')."
								LEFT JOIN (
									SELECT
										".$this->db->dbprefix('using_stocks').".using_id,
										SUM(
											abs(IFNULL(unit_quantity, 0))
										) AS quantity_return
									FROM
										".$this->db->dbprefix('using_stocks')."
									INNER JOIN ".$this->db->dbprefix('using_stock_items')." ON ".$this->db->dbprefix('using_stock_items').".using_id = ".$this->db->dbprefix('using_stocks').".id
									WHERE
										".$this->db->dbprefix('using_stocks').".using_id = ".$using_id."
									GROUP BY ".$this->db->dbprefix('using_stocks').".using_id	
								) AS using_stock_item_returns ON using_stock_item_returns.using_id = ".$this->db->dbprefix('using_stock_items').".using_id
								WHERE
									".$this->db->dbprefix('using_stock_items').".using_id = ".$using_id."
								GROUP BY 
									".$this->db->dbprefix('using_stock_items').".using_id
							");
		if($q->num_rows() > 0){
			$quantity_using = $q->row()->quantity_using;
			$quantity_return = $q->row()->quantity_return;
			$status = 'pending';
			if($quantity_using==$quantity_return){
				$status = 'completed';
			}else if($quantity_return > 0 && $quantity_return < $quantity_using){
				$status = 'partial';
			}
			$this->db->update('using_stocks',array('status'=>$status),array('id'=>$using_id));
		}					
	}	
	
	public function getBomItemByProductID($product_id = false){
		$this->db->select('bom_items.product_id,products.code,products.name,products.cost,products.unit,('.$this->db->dbprefix('bom_items').'.quantity) as quantity');
		$this->db->join('bom_items','bom_items.bom_id = boms.id','inner');
		$this->db->join('products','products.id = bom_items.product_id','inner');
		$this->db->where('boms.product_id',$product_id);
		$this->db->where('bom_items.type','raw_material');
		$q = $this->db->get('boms');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getProductsByID($id = false){
		if($id){
			$this->db->where_in('id',$id);
			$q = $this->db->get('products');
			if($q->num_rows() > 0){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}
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
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('consignment_items', array('consignment_id' => $consignment_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	
	public function addConsignment($data = false, $items = false, $stockmoves = false , $accTrans = false)
    {
        if ($this->db->insert('consignments', $data)) {
            $consignment_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['consignment_id'] = $consignment_id;
                $this->db->insert('consignment_items', $item);
            }
			
			if($stockmoves){
				foreach($stockmoves as $stockmove){
					$stockmove['transaction_id'] = $consignment_id;
					$this->db->insert('stockmoves', $stockmove);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $consignment_id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
			
			if($data['consignment_id'] > 0){
				$this->site->syncConsignment($data['consignment_id']);
			}
			
            return true;
        }
        return false;
    }
	
	public function updateConsignment($id = false,$data = false, $items = false, $stockmoves = false , $accTrans = false)
    {
        if ($id && $id > 0 && $this->db->update('consignments', $data, array('id'=>$id))) {
            $this->db->delete('consignment_items',array('consignment_id'=>$id));
			$this->site->deleteAccTran('Consignment',$id);
			$this->site->deleteStockmoves('Consignment',$id);
			if($items){
				$this->db->insert_batch('consignment_items',$items);
			}
			if($stockmoves){
				$this->db->insert_batch('stockmoves',$stockmoves);
			}
			if($accTrans){
				$this->db->insert_batch('acc_tran',$accTrans);
			}
            return true;
        }
        return false;
    }
	
	public function deleteConsignment($id = false){
		$consignment = $this->getConsignmentByID($id);
		$consignment_returns = $this->getConsignmentByConsignID($id);
		if($id && $id > 0 && $this->db->delete('consignments',array('id'=>$id))){
			$this->db->delete('consignment_items',array('consignment_id'=>$id));
			$this->site->deleteAccTran('Consignment',$id);
			$this->site->deleteStockmoves('Consignment',$id);
			
			if($consignment_returns){
				foreach($consignment_returns as $consignment_returns){
					$this->db->delete('consignments',array('id'=>$consignment_returns->id));
					$this->db->delete('consignment_items',array('consignment_id'=>$consignment_returns->id));
					$this->site->deleteAccTran('Consignment',$consignment_returns->id);
					$this->site->deleteStockmoves('Consignment',$consignment_returns->id);
				}
			}
			if($consignment->consignment_id > 0){
				$consignment_id = $consignment->consignment_id;
			}else{
				$consignment_id = $id;
			}
			
			$this->site->syncConsignment($consignment_id);
			return true;
		}
		return false;
	}
	
	public function getConsignmentByConsignID($consignment_id = false){
		$q = $this->db->get_where('consignments',array('consignment_id'=>$consignment_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getDocumentByID($id = false)
	{
		$q = $this->db->get_where('product_documents', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	public function addDocument($data = array())
	{
		if($this->db->insert('product_documents',$data)){
			return true;
		}
		return false;
	}
	public function updateDocument($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('product_documents', $data)){
			return true;
		}
		return false;
	}
	public function deleteDocument($id = false)
	{
		if($this->db->where("id",$id)->delete('product_documents')){
			return true;
		}
		return false;
	}
	
	public function getLicenseByID($id = false)
	{
		$q = $this->db->get_where('product_licenses', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	public function addLicense($data = array())
	{
		if($this->db->insert('product_licenses',$data)){
			return true;
		}
		return false;
	}
	public function updateLicense($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('product_licenses', $data)){
			return true;
		}
		return false;
	}
	public function deleteLicense($id = false)
	{
		if($this->db->where("id",$id)->delete('product_licenses')){
			return true;
		}
		return false;
	}
	
	public function getBoms(){
		$q = $this->db->get("boms");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getBomByID($id = false){
		$q = $this->db->get_where("boms",array("id"=>$id));
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function getBomItems($bom_id = false){
		$this->db->select("bom_items.*, products.code as product_code,products.name as product_name,products.cost as product_cost");
		$this->db->join("products","products.id = bom_items.product_id","inner");
		$q = $this->db->get_where("bom_items",array("bom_items.bom_id"=>$bom_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getFinishGoodBomQty($bom_id = false){
		$this->db->select("sum(quantity) as quantity");
		$this->db->where("bom_id",$bom_id);
		$this->db->where("type","finished_good");
		$q = $this->db->get("bom_items");
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}

	
	
	
	
}










