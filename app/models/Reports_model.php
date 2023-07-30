<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reports_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term = false, $limit = 10)
    {
        $this->db->select('id, code, name')
            ->like('name', $term, 'both')->or_like('code', $term, 'both');
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
	
	public function getProductById($id = false)
{
		$q = $this->db->get_where('products',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

    public function getStaff()
    {
        if ($this->Admin) {
            $this->db->where('group_id !=', 1);
        }
		$this->db->where('saleman',0);
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

    public function getSalesTotals($customer_id = false)
    {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}

        $this->db->select('SUM(COALESCE('.$this->db->dbprefix('sales').'.grand_total, 0)) as total_amount, SUM(COALESCE(cus_payments.paid, 0) + COALESCE(cus_payments.discount, 0)) as paid', FALSE)
            ->where('sales.customer_id', $customer_id)
			->join('(SELECT
						sale_id,
						IFNULL(sum(amount),0) AS paid,
						IFNULL(sum(discount),0) AS discount
					FROM
						'.$this->db->dbprefix('payments').'
					GROUP BY
						sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCustomerSales($customer_id = false)
    {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->db->from('sales')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }

    public function getCustomerQuotes($customer_id = false)
    {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('quotes.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('quotes.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->db->from('quotes')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }

    public function getCustomerReturns($customer_id = false)
    {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->db->from('sales')->where('customer_id', $customer_id)->where('sale_status', 'returned');
        return $this->db->count_all_results();
    }

    public function getStockValue()
    {
		$allow_category = $this->site->getCategoryByProject();
		$where = "";
		if($allow_category){
			$where = " AND ".$this->db->dbprefix('products').".category_id IN ('".implode("','",$allow_category)."')";
		}
		if($this->Settings->product_serial==1){
			 $q = $this->db->query("SELECT
										sum(((quantity-serial_quantity) * price) + serial_price) AS stock_by_price,
										sum(((quantity-serial_quantity) * cost) + serial_cost) AS stock_by_cost
									FROM
										(
											SELECT
												" . $this->db->dbprefix('products') . ".id,
												" . $this->db->dbprefix('products') . ".cost,
												" . $this->db->dbprefix('products') . ".price,
												COALESCE (
													(
														" . $this->db->dbprefix('warehouses_products') . ".quantity
													),
													0
												) AS quantity,
												Count(
													" . $this->db->dbprefix('product_serials') . ".product_id
												) AS serial_quantity,
												COALESCE (
													sum(" . $this->db->dbprefix('product_serials') . ".cost),
													0
												) AS serial_cost,
												COALESCE (
													sum(" . $this->db->dbprefix('product_serials') . ".price),
													0
												) AS serial_price
											FROM
												" . $this->db->dbprefix('products') . "
											JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id = " . $this->db->dbprefix('products') . ".id
											LEFT JOIN " . $this->db->dbprefix('product_serials') . " ON " . $this->db->dbprefix('product_serials') . ".product_id = " . $this->db->dbprefix('warehouses_products') . ".product_id
											AND " . $this->db->dbprefix('product_serials') . ".warehouse_id = " . $this->db->dbprefix('warehouses_products') . ".warehouse_id
											AND " . $this->db->dbprefix('product_serials') . ".inactive = 0
											AND " . $this->db->dbprefix('product_serials') . ".serial <> ''
											GROUP BY
												" . $this->db->dbprefix('products') . ".id
										) AS a");
		}else{
			 $q = $this->db->query("SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select COALESCE(sum(" . $this->db->dbprefix('warehouses_products') . ".quantity), 0)*price as by_price, COALESCE(sum(" . $this->db->dbprefix('warehouses_products') . ".quantity), 0)*cost as by_cost FROM " . $this->db->dbprefix('products') . " JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id=" . $this->db->dbprefix('products') . ".id WHERE 1=1".$where." GROUP BY " . $this->db->dbprefix('products') . ".id )a");
		}
       
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getWarehouseStockValue($id = false)
    {
		$allow_category = $this->site->getCategoryByProject();
		$where = "";
		if($allow_category){
			$where = " AND ".$this->db->dbprefix('products').".category_id IN ('".implode("','",$allow_category)."')";
		}
        $q = $this->db->query("SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select sum(COALESCE(" . $this->db->dbprefix('warehouses_products') . ".quantity, 0))*price as by_price, sum(COALESCE(" . $this->db->dbprefix('warehouses_products') . ".quantity, 0))*cost as by_cost FROM " . $this->db->dbprefix('products') . " JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id=" . $this->db->dbprefix('products') . ".id WHERE 1=1".$where. $this->db->dbprefix('warehouses_products') . ".warehouse_id = ? GROUP BY " . $this->db->dbprefix('products') . ".id )a", array($id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    // public function getmonthlyPurchases()
    // {
    //     $myQuery = "SELECT (CASE WHEN date_format( date, '%b' ) Is Null THEN 0 ELSE date_format( date, '%b' ) END) as month, SUM( COALESCE( total, 0 ) ) AS purchases FROM purchases WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH ) GROUP BY date_format( date, '%b' ) ORDER BY date_format( date, '%m' ) ASC";
    //     $q = $this->db->query($myQuery);
    //     if ($q->num_rows() > 0) {
    //         foreach (($q->result()) as $row) {
    //             $data[] = $row;
    //         }
    //         return $data;
    //     }
    //     return FALSE;
    // }

    public function getChartData()
    {
		$where = "";
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$where = " AND biller_id='".$this->session->userdata('biller_id')."'";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$where .= " AND warehouse_id IN ".$warehouse_ids;
		}
		
		$myQuery = "SELECT
						S.month,
						COALESCE ( S.sales, 0 ) AS sales,
						COALESCE ( P.purchases, 0 ) AS purchases,
						COALESCE ( S.tax1, 0 ) AS tax1,
						COALESCE ( S.tax2, 0 ) AS tax2,
						COALESCE ( P.ptax, 0 ) AS ptax 
					FROM
						(
						SELECT
							date_format( date, '%Y-%m' ) month,
							SUM(
							grand_total - IFNULL( total_return, 0 )) Sales,
							SUM( product_tax ) tax1,
							SUM( order_tax ) tax2 
						FROM
							".$this->db->dbprefix('sales')."
							LEFT JOIN ( SELECT sum( abs( grand_total )) AS total_return, sale_id FROM ".$this->db->dbprefix('sales')." WHERE sale_status = 'returned' GROUP BY sale_id ) AS sale_return ON sale_return.sale_id = ".$this->db->dbprefix('sales').".id 
						WHERE
							date >= date_sub( now( ), INTERVAL 12 month ) 
							AND sale_status != 'draft' 
							AND sale_status != 'returned' 
							".$where."
						GROUP BY
							date_format( date, '%Y-%m' ) 
						) S
						LEFT JOIN (
						SELECT
							date_format( date, '%Y-%m' ) month,
							SUM( product_tax ) ptax,
							SUM( order_tax ) otax,
							SUM(
							grand_total - IFNULL( return_purchase_total, 0 )) purchases 
						FROM
							".$this->db->dbprefix('purchases')."
						WHERE
							STATUS != 'returned' 
							AND STATUS != 'draft' 
							".$where."	
						GROUP BY
						date_format( date, '%Y-%m' )) P ON S.month = P.month 
					ORDER BY
						S.month";
        $q = $this->db->query($myQuery);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		
        return FALSE;
    }

    public function getDailySales($year = false, $month = false, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping, SUM( COALESCE( return_total, 0 ) ) AS return_total
			FROM " . $this->db->dbprefix('sales') . " 
			LEFT JOIN (
						SELECT
							sale_id,
							SUM(
								COALESCE (abs(grand_total), 0)
							) AS return_total
						FROM
							".$this->db->dbprefix('sales')."
						WHERE
							".$this->db->dbprefix('sales').".sale_status = 'returned'
						GROUP BY
							sale_id
					) AS sale_return ON sale_return.sale_id = ".$this->db->dbprefix('sales').".id
			
			WHERE ";
		$myQuery .= " sale_status !='draft' AND";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$myQuery .= " biller_id = {$this->session->userdata('biller_id')} AND ";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$myQuery .= " warehouse_id IN ".$warehouse_ids." AND ";
		}
        $myQuery .= " ".$this->db->dbprefix('sales').".sale_status <> 'returned' AND ".$this->db->dbprefix('sales').".sale_status <> 'draft' AND  DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
			GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getMonthlySales($year = false, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping, SUM( COALESCE( return_total, 0 ) ) AS return_total
			FROM " . $this->db->dbprefix('sales') . " 
			LEFT JOIN (
						SELECT
							sale_id,
							SUM(
								COALESCE (abs(grand_total), 0)
							) AS return_total
						FROM
							".$this->db->dbprefix('sales')."
						WHERE
							".$this->db->dbprefix('sales').".sale_status = 'returned'
						GROUP BY
							sale_id
					) AS sale_return ON sale_return.sale_id = ".$this->db->dbprefix('sales').".id
			WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$myQuery .= " biller_id = {$this->session->userdata('biller_id')} AND ";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$myQuery .= " warehouse_id IN ".$warehouse_ids." AND ";
		}
        $myQuery .= " ".$this->db->dbprefix('sales').".sale_status <> 'returned' AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
			GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStaffDailySales($user_id = false, $year = false, $month = false, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping, SUM( COALESCE( return_total, 0 ) ) AS return_total
            FROM " . $this->db->dbprefix('sales')." 
			LEFT JOIN (
						SELECT
							sale_id,
							SUM(
								COALESCE (abs(grand_total), 0)
							) AS return_total
						FROM
							".$this->db->dbprefix('sales')."
						WHERE
							".$this->db->dbprefix('sales').".sale_status = 'returned'
						GROUP BY
							sale_id
					) AS sale_return ON sale_return.sale_id = ".$this->db->dbprefix('sales').".id
			WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " ".$this->db->dbprefix('sales').".sale_status <> 'returned' AND ".$this->db->dbprefix('sales').".sale_status <> 'draft' AND created_by = {$user_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStaffMonthlySales($user_id = false, $year = false, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping, SUM( COALESCE( return_total, 0 ) ) AS return_total
            FROM " . $this->db->dbprefix('sales') . " 
			LEFT JOIN (
						SELECT
							sale_id,
							SUM(
								COALESCE (abs(grand_total), 0)
							) AS return_total
						FROM
							".$this->db->dbprefix('sales')."
						WHERE
							".$this->db->dbprefix('sales').".sale_status = 'returned'
						GROUP BY
							sale_id
					) AS sale_return ON sale_return.sale_id = ".$this->db->dbprefix('sales').".id
			WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " ".$this->db->dbprefix('sales').".sale_status <> 'returned' AND created_by = {$user_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchasesTotals($supplier_id = false)
    {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('supplier_id', $supplier_id);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getExpensesTotals($supplier_id = false)
    {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('expenses.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('expenses.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('supplier_id', $supplier_id);
		$this->db->where("expenses.status","approved");
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSupplierPurchases($supplier_id = false)
    {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->db->from('purchases')->where('supplier_id', $supplier_id);
        return $this->db->count_all_results();
    }

    public function getStaffPurchases($user_id = false)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('created_by', $user_id);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getStaffSales($user_id = false)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('created_by', $user_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalSales($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax', FALSE)
            ->where('date(date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
        
		if ($biller) {
			$this->db->where('biller_id', $biller);
		}

		if ($project) {
			$this->db->where('project_id', $project);
		}

		if ($warehouse) {
			$this->db->where('warehouse_id', $warehouse);
		}
		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('sales.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getTotalGrossMargin($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('sum(COALESCE(total_cost, 0)) AS total_cost,sum(COALESCE(grand_total, 0)) AS grand_total', FALSE);
		$this->db->join('(SELECT sum(COALESCE((quantity + IFNULL(foc,0)) * cost + IFNULL(extract_cost,0), 0)) AS total_cost,sale_id FROM '.$this->db->dbprefix('sale_items').' GROUP BY sale_id) as sale_items', 'sale_items.sale_id=sales.id','left')
        ->where('date(date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
        
		
		if ($warehouse) {
			$this->db->where('sales.warehouse_id', $warehouse);
		}
		
		if ($biller) {
			$this->db->where('sales.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('sales.project_id', $project);
		}


		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('sales.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('sales');
		
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalPurchases($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax', FALSE)
            ->where('date(date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
        
		if ($biller) {
            $this->db->where('biller_id', $biller);
        }
		
		if ($project) {
            $this->db->where('project_id', $project);
        }
		
		if ($warehouse) {
            $this->db->where('warehouse_id', $warehouse);
        }
		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('purchases.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		$this->db->where('status !=','freight');
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getTotalFreights($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax', FALSE)
            ->where('date(date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
        
		if ($biller) {
            $this->db->where('biller_id', $biller);
        }
		
		if ($project) {
            $this->db->where('project_id', $project);
        }
		
		if ($warehouse) {
            $this->db->where('warehouse_id', $warehouse);
        }
		$this->db->where('status','freight');
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('purchases.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalExpenses($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount', FALSE)
            ->where('date(date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
        if ($warehouse) {
			$this->db->where('warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('biller_id', $biller);
		}

		if ($project) {
			$this->db->where('project_id', $project);
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('expenses.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('expenses.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('expenses.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalPaidAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.amount, 0)) as total_amount', FALSE)
			->join('purchases','purchases.id = payments.purchase_id','inner')
            ->where('payments.type', 'sent')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
		
		if ($warehouse) {
			$this->db->where('purchases.warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('purchases.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('purchases.project_id', $project);
		}
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getTotalReceivedInstallment($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.interest_paid, 0)) as interest_paid, SUM(COALESCE('.$this->db->dbprefix("payments").'.penalty_paid, 0)) as penalty_paid', FALSE)
            ->join('installments','installments.id = payments.installment_id','inner')
			->where('payments.type', 'received')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
			
		if ($warehouse) {
			$this->db->where('installments.warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('installments.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('installments.project_id', $project);
		}
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('installments.biller_id',$this->session->userdata('biller_id'));
		}		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('installments.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	

    public function getTotalReceivedAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.amount, 0)) as total_amount', FALSE)
            ->join('sales','sales.id = payments.sale_id','inner')
			->where('payments.type', 'received')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
			
		if ($warehouse) {
			$this->db->where('sales.warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('sales.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('sales.project_id', $project);
		}
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getTotalPaymentCommission($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.amount, 0)) as total_amount', FALSE)
            ->join('sales','sales.id = payments.transaction_id','inner')
			->where('payments.type', 'sent')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
			
		if ($warehouse) {
			$this->db->where('sales.warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('sales.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('sales.project_id', $project);
		}
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalReceivedCashAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.amount, 0)) as total_amount', FALSE)
			->join('sales','sales.id = payments.sale_id','inner')	
			->where('payments.type', 'received')->where('payments.paid_by', 'cash')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
			
		if ($warehouse) {
			$this->db->where('sales.warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('sales.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('sales.project_id', $project);
		}
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getTotalReceivedCCAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.amount, 0)) as total_amount', FALSE)
			->join('sales','sales.id = payments.sale_id','inner')	
			->where('payments.type', 'received')->where('payments.paid_by', 'CC')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
			
		if ($warehouse) {
			$this->db->where('sales.warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('sales.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('sales.project_id', $project);
		}
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getTotalReceivedPPPAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.amount, 0)) as total_amount', FALSE)
			->join('sales','sales.id = payments.sale_id','inner')	
			->where('payments.type', 'received')->where('payments.paid_by', 'ppp')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
			
		if ($warehouse) {
			$this->db->where('sales.warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('sales.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('sales.project_id', $project);
		}
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getTotalReceivedStripeAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.amount, 0)) as total_amount', FALSE)
			->join('sales','sales.id = payments.sale_id','inner')	
			->where('payments.type', 'received')->where('payments.paid_by', 'stripe')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
			
		if ($warehouse) {
			$this->db->where('sales.warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('sales.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('sales.project_id', $project);
		}
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getTotalReceivedChequeAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.amount, 0)) as total_amount', FALSE)
			->join('sales','sales.id = payments.sale_id','inner')	
			->where('payments.type', 'received')->where('payments.paid_by', 'Cheque')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
			
		if ($warehouse) {
			$this->db->where('sales.warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('sales.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('sales.project_id', $project);
		}
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


	public function getTotalPurchaseReturnedAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE(abs('.$this->db->dbprefix("payments").'.amount), 0)) as total_amount', FALSE)
            ->join('purchases','purchases.id = payments.purchase_id','inner')
			->where('payments.type', 'returned')
			->where('payments.purchase_id >',0)
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
			
		if ($warehouse) {
			$this->db->where('purchases.warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('purchases.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('purchases.project_id', $project);
		}
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }	
	
	public function getTotalExpensesAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
	{
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE(abs('.$this->db->dbprefix("payments").'.amount), 0)) as total_amount', FALSE)
			->join('expenses','expenses.id = payments.expense_id','inner')
			->where(''.$this->db->dbprefix("payments").'.type', 'expense')
			->where(''.$this->db->dbprefix("payments").'.expense_id >',0)
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
		
		if ($warehouse) {
			$this->db->where('warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('biller_id', $biller);
		}

		if ($project) {
			$this->db->where('project_id', $project);
		}		
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
    public function getTotalReturnedAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE(abs('.$this->db->dbprefix("payments").'.amount), 0)) as total_amount', FALSE)
            ->join('sales','sales.id = payments.sale_id','inner')	
			->where('payments.type', 'returned')
			->where('payments.sale_id >',0)
			->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
			
		if ($warehouse) {
			$this->db->where('sales.warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('sales.biller_id', $biller);
		}

		if ($project) {
			$this->db->where('sales.project_id', $project);
		}
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getWarehouseTotals($warehouse_id = NULL)
    {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->join('products','products.id = warehouses_products.product_id','inner');
			$this->db->where_in('products.category_id',$allow_category);
		}	
        $this->db->select('sum('.$this->db->dbprefix("warehouses_products").'.quantity) as total_quantity, count('.$this->db->dbprefix("warehouses_products").'.id) as total_items', FALSE);
        $this->db->where('warehouses_products.quantity !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('warehouses_products');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getCosting($date = false, $warehouse_id = NULL, $year = NULL, $month = NULL)
	{
		$this->db->select('sum(IFNULL('.$this->db->dbprefix("sale_items").'.unit_quantity,0) * IFNULL('.$this->db->dbprefix("sale_items").'.unit_price,0)) AS sales,
							sum(IFNULL('.$this->db->dbprefix("sale_items").'.quantity,0) * IFNULL('.$this->db->dbprefix("sale_items").'.cost,0) + IFNULL('.$this->db->dbprefix("sale_items").'.extract_cost,0)) AS cost,
						');
		$this->db->join('sales', 'sales.id=sale_items.sale_id','inner');				
		if ($date) {
            $this->db->where('date('.$this->db->dbprefix("sales").'.date)', $date);
        }elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('sales.date >=', $year.'-'.$month.'-01 00:00:00');
            $this->db->where('sales.date <=', $year.'-'.$month.'-'.$last_day.' 23:59:59');
        }
		if ($warehouse_id) {
            $this->db->where('sales.warehouse_id', $warehouse_id);
        }
		$q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}

    public function getExpenses($date = false, $warehouse_id = NULL, $year = NULL, $month = NULL)
    {
        $sdate = $date.' 00:00:00';
        $edate = $date.' 23:59:59';
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', FALSE);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year.'-'.$month.'-01 00:00:00');
            $this->db->where('date <=', $year.'-'.$month.'-'.$last_day.' 23:59:59');
        }
        

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturns($date = false, $warehouse_id = NULL, $year = NULL, $month = NULL)
    {
        $sdate = $date.' 00:00:00';
        $edate = $date.' 23:59:59';
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', FALSE)
        ->where('grand_total <', 0);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year.'-'.$month.'-01 00:00:00');
            $this->db->where('date <=', $year.'-'.$month.'-'.$last_day.' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getOrderDiscount($date = false, $warehouse_id = NULL, $year = NULL, $month = NULL)
    {
        $sdate = $date.' 00:00:00';
        $edate = $date.' 23:59:59';
        $this->db->select('SUM( COALESCE( order_discount, 0 ) ) AS order_discount', FALSE);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year.'-'.$month.'-01 00:00:00');
            $this->db->where('date <=', $year.'-'.$month.'-'.$last_day.' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
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

    public function getDailyPurchases($year = false, $month = false, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping, SUM(COALESCE(return_total, 0)) AS return_total
            FROM ".$this->db->dbprefix('purchases')." 
			LEFT JOIN (
						SELECT
							purchase_id,
							SUM(
								COALESCE (abs(grand_total), 0)
							) AS return_total
						FROM
							".$this->db->dbprefix('purchases')."
						WHERE
							".$this->db->dbprefix('purchases').".status = 'returned'
						GROUP BY
							purchase_id
					) AS pur_return ON pur_return.purchase_id = ".$this->db->dbprefix('purchases').".id
			WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$myQuery .= " biller_id = {$this->session->userdata('biller_id')} AND ";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$myQuery .= " warehouse_id IN ".$warehouse_ids." AND ";
		}
        $myQuery .= "".$this->db->dbprefix('purchases').".status <> 'returned' AND  DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getMonthlyPurchases($year = false, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping, SUM( COALESCE( return_total, 0 ) ) AS return_total
            FROM " . $this->db->dbprefix('purchases') . " 
			LEFT JOIN (
						SELECT
							purchase_id,
							SUM(
								COALESCE (abs(grand_total), 0)
							) AS return_total
						FROM
							".$this->db->dbprefix('purchases')."
						WHERE
							".$this->db->dbprefix('purchases').".status = 'returned'
						GROUP BY
							purchase_id
					) AS pur_return ON pur_return.purchase_id = ".$this->db->dbprefix('purchases').".id
			WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$myQuery .= " biller_id = {$this->session->userdata('biller_id')} AND ";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$myQuery .= " warehouse_id IN ".$warehouse_ids. " AND ";
		}
        $myQuery .= " ".$this->db->dbprefix('purchases').".status <> 'returned' AND  DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStaffDailyPurchases($user_id = false, $year = false, $month = false, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping, SUM(COALESCE(return_total, 0)) AS return_total
            FROM " . $this->db->dbprefix('purchases')." 
			LEFT JOIN (
						SELECT
							purchase_id,
							SUM(
								COALESCE (abs(grand_total), 0)
							) AS return_total
						FROM
							".$this->db->dbprefix('purchases')."
						WHERE
							".$this->db->dbprefix('purchases').".status = 'returned'
						GROUP BY
							purchase_id
					) AS pur_return ON pur_return.purchase_id = ".$this->db->dbprefix('purchases').".id
			WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= "".$this->db->dbprefix('purchases').".status <> 'returned' AND created_by = {$user_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStaffMonthlyPurchases($user_id = false, $year = false, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping, SUM( COALESCE( return_total, 0 ) ) AS return_total
            FROM " . $this->db->dbprefix('purchases') . " 
			LEFT JOIN (
						SELECT
							purchase_id,
							SUM(
								COALESCE (abs(grand_total), 0)
							) AS return_total
						FROM
							".$this->db->dbprefix('purchases')."
						WHERE
							".$this->db->dbprefix('purchases').".status = 'returned'
						GROUP BY
							purchase_id
					) AS pur_return ON pur_return.purchase_id = ".$this->db->dbprefix('purchases').".id
			
			WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " ".$this->db->dbprefix('purchases').".status <> 'returned' AND created_by = {$user_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getBestSeller($start_date = false, $end_date = false, $warehouse_id = NULL)
    {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->join('products','products.id = sale_items.product_id','inner');
			$this->db->where_in('products.category_id',$allow_category);
		}
        $this->db
            ->select("sale_items.product_name, sale_items.product_code")->select_sum($this->db->dbprefix("sale_items").'.quantity')
            ->join('sales', 'sales.id = sale_items.sale_id', 'left')
            ->where('sales.date >=', $start_date)->where('sales.date <=', $end_date)
            ->group_by('sale_items.product_name, sale_items.product_code')->order_by('sum('.$this->db->dbprefix("sale_items").'.quantity)', 'desc')->limit(10);
        if ($warehouse_id) {
            $this->db->where('sale_items.warehouse_id', $warehouse_id);
        }
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('sales.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id', $this->session->userdata('biller_id'));
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

    function getPOSSetting()
    {
        $q = $this->db->get('pos_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getAllProductsByInventoryInOutExpiry($category_id = false, $product = false)
    {
		if($product){
			$this->db->where("products.id",$product);
		}		
		$this->db->where("products.type <>","service");
		$this->db->where("products.category_id", $category_id);
		$this->db->select('products.*,IFNULL('.$this->db->dbprefix("stockmoves").'.expiry,"0000-00-00") as expiry')
		->join('stockmoves','stockmoves.product_id = products.id','inner')
		->group_by('products.id,IFNULL('.$this->db->dbprefix("stockmoves").'.expiry,"0000-00-00")')
		->order_by('products.code,stockmoves.expiry');
        $q = $this->db->get('products');		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	
	public function getAllProductsByInventoryInOut($category_id = false, $product = false)
    {
		if($product){
			$this->db->where("products.id",$product);
		}		
		$this->db->where("type <>","service");
		$this->db->where("category_id", $category_id);
        $q = $this->db->get('products');		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllCategoriesByInventoryInOut($category_id = false)
	{		
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("categories.id",$allow_category);
		}
		if($category_id){
			$this->db->where("id", $category_id);
		}
		$this->db->order_by("parent_id");
		$q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getAllServiceTypesByCheckIN($service_type_id = false)
	{		
		if($service_type_id){
			$this->db->where("id", $service_type_id);
		}
		$q = $this->db->get('rental_service_types');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}



	public function getRoomByCheckINlist($category_id = false, $start_date = false, $end_date = false, $product = false, $warehouse_id = false, $saleman = false, $biller = false, $project = false, $customer = false)
	{
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$sql = "";
		
		if ($category_id) {
            $sql .= " AND ".$this->db->dbprefix('products').".category_id = {$category_id}";			
        }
		if ($biller) {
            $sql .= " AND ".$this->db->dbprefix('rentals').".biller_id = {$biller}";			
        }
		if ($customer) {
            $sql .= " AND ".$this->db->dbprefix('rentals').".customer_id = {$customer}";			
        }
		if ($project) {
            $sql .= " AND ".$this->db->dbprefix('rentals').".project_id = {$project}";			
        }
		if ($saleman) {
            $sql .= " AND ".$this->db->dbprefix('rentals').".saleman_id = {$saleman}";			
        }
		if($product){
			$sql .= " AND ".$this->db->dbprefix('rental_items').".product_id= {$product}";
		}

		if($start_date){
			$sql .= " AND ".$this->db->dbprefix('rental_items').".check_time >= '{$this->cus->fld($start_date)}'";
		}
		if($end_date){
			$sql .= " AND ".$this->db->dbprefix('rental_items').".check_time <= '{$this->cus->fld($end_date)}'";
		}
		if(!$start_date && !$end_date){
			$sql .= " AND check_time = '".date('Y-m-d')."' ";
		}
		
		if ($warehouse_id) {
            $sql .= " AND ".$this->db->dbprefix('rentals').".warehouse_id = {$warehouse_id}";			
        }
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$sql .= " AND ".$this->db->dbprefix('rentals').".created_by = {$this->session->userdata('user_id')}";
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$sql .= " AND ".$this->db->dbprefix('rentals').".biller_id = {$this->session->userdata('biller_id')}";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$sql .= " AND ".$this->db->dbprefix('rentals').".warehouse_id IN ".$warehouse_ids;
		}
		
		
		$this->db->query("SET group_concat_max_len = 10000000");
		$result = $this->db->query("SELECT
										".$this->db->dbprefix('rental_items').".product_id,
										".$this->db->dbprefix('rental_items').".product_code,
										".$this->db->dbprefix('rental_items').".product_type,
										".$this->db->dbprefix('rental_items').".unit_price,
										".$this->db->dbprefix('rental_items').".product_unit_id,
										".$this->db->dbprefix('rental_service_types').".name as service_types,
										".$this->db->dbprefix('rental_service_types').".code as service_code,
										".$this->db->dbprefix('rentals').".status,
										product_name,
										
										sum(".$this->db->dbprefix('rental_items').".net_unit_price * ".$this->db->dbprefix('rental_items').".unit_quantity) as price,
										tax,
										SUM(".$this->db->dbprefix('rental_items').".item_discount) as item_discount,
										SUM(".$this->db->dbprefix('rental_items').".quantity) as quantity,
										SUM(".$this->db->dbprefix('rental_items').".subtotal) as subtotal,
										".$this->db->dbprefix('products').".quantity as stock_quantity,
										".$this->db->dbprefix('rentals').".reference_no,
										".$this->db->dbprefix('rentals').".customer
									FROM
										".$this->db->dbprefix('rental_items')."
									LEFT JOIN ".$this->db->dbprefix('products')." ON ".$this->db->dbprefix('products').".id = product_id


									-- LEFT JOIN cus_acc_product ON cus_acc_product.product_id = cus_products.id
									-- LEFT JOIN cus_rental_service_types ON cus_rental_service_types.id = cus_rental_items.service_types

									LEFT JOIN ".$this->db->dbprefix('rental_service_types')." ON ".$this->db->dbprefix('rental_service_types').".description = ".$this->db->dbprefix('rental_items').".service_types

									LEFT JOIN ".$this->db->dbprefix('rentals')." ON ".$this->db->dbprefix('rentals').".id = ".$this->db->dbprefix('rental_items').".rental_id
									WHERE 1=1
									{$sql}
									AND cus_rentals.status = 'checked_in'
									GROUP BY
										".$this->db->dbprefix('rental_items').".service_types
									")->result();
		return $result;
	}

	
	public function getProductBySalesRental($category_id = false, $start_date = false, $end_date = false, $product = false, $warehouse_id = false, $saleman = false, $biller = false, $project = false, $customer = false)
	{
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$sql = "";
		
		if ($category_id) {
            $sql .= " AND ".$this->db->dbprefix('products').".category_id = {$category_id}";			
        }
		if ($biller) {
            $sql .= " AND ".$this->db->dbprefix('sales').".biller_id = {$biller}";			
        }
		if ($customer) {
            $sql .= " AND ".$this->db->dbprefix('sales').".customer_id = {$customer}";			
        }
		if ($project) {
            $sql .= " AND ".$this->db->dbprefix('sales').".project_id = {$project}";			
        }
		if ($saleman) {
            $sql .= " AND ".$this->db->dbprefix('sales').".saleman_id = {$saleman}";			
        }
		if($product){
			$sql .= " AND ".$this->db->dbprefix('sale_items').".product_id= {$product}";
		}
		if ($start_date) {
			$sql .= " AND date >= '{$this->cus->fld($start_date)}'";
        }
		if($end_date){
			$sql .= " AND date <= '{$this->cus->fld($end_date,false,1)}'";
		}
		if(!$start_date && !$end_date){
			$sql .= " AND date(date) = '".date('Y-m-d')."' ";
		}
		if ($warehouse_id) {
            $sql .= " AND ".$this->db->dbprefix('sales').".warehouse_id = {$warehouse_id}";			
        }
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$sql .= " AND ".$this->db->dbprefix('sales').".created_by = {$this->session->userdata('user_id')}";
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$sql .= " AND ".$this->db->dbprefix('sales').".biller_id = {$this->session->userdata('biller_id')}";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$sql .= " AND ".$this->db->dbprefix('sales').".warehouse_id IN ".$warehouse_ids;
		}
		
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = "";
			if($projects){
				foreach($projects as $pr){
					$project_details .= $pr.",";
				}
			}
			
			if(!$project && $projects[0] != 'all'){
				$rtrim = rtrim($project_details,",");
				if($rtrim){
					$sql .= " AND ".$this->db->dbprefix('sales').".project_id IN ({$rtrim})";
				}
				
			}
		}
		$this->db->query("SET group_concat_max_len = 10000000");
		$result = $this->db->query("SELECT
										".$this->db->dbprefix('sale_items').".product_id,
										".$this->db->dbprefix('sale_items').".product_code,
										".$this->db->dbprefix('sale_items').".product_type,
										".$this->db->dbprefix('sale_items').".unit_price,
										".$this->db->dbprefix('sale_items').".product_unit_id,
										".$this->db->dbprefix('sale_items').".service_types as ServiceTypesName,
										".$this->db->dbprefix('categories').".service_types as CategoryName,
										".$this->db->dbprefix('products').".service_code as ServiceCode,
										GROUP_CONCAT(".$this->db->dbprefix('sale_items').".raw_materials SEPARATOR'#') as raw_materials,
										product_name,
										sum(".$this->db->dbprefix('sale_items').".cost * ".$this->db->dbprefix('sale_items').".quantity + IFNULL(".$this->db->dbprefix('sale_items').".foc,0)) as cost,
										sum(".$this->db->dbprefix('sale_items').".unit_price)  / count(".$this->db->dbprefix('sale_items').".id) as price,
										tax,
										SUM(".$this->db->dbprefix('sale_items').".item_discount) as item_discount,
										SUM(".$this->db->dbprefix('sale_items').".quantity) as quantity,
										SUM(".$this->db->dbprefix('sale_items').".foc) as foc,
										SUM(".$this->db->dbprefix('sale_items').".subtotal) as subtotal,
										".$this->db->dbprefix('products').".quantity as stock_quantity,
										".$this->db->dbprefix('sales').".reference_no,
										".$this->db->dbprefix('sales').".customer
									FROM
										".$this->db->dbprefix('sale_items')."
									LEFT JOIN ".$this->db->dbprefix('products')." ON ".$this->db->dbprefix('products').".id = product_id
									LEFT JOIN ".$this->db->dbprefix('categories')." ON ".$this->db->dbprefix('categories').".id = ".$this->db->dbprefix('products').".category_id
									LEFT JOIN ".$this->db->dbprefix('sales')." ON ".$this->db->dbprefix('sales').".id = ".$this->db->dbprefix('sale_items').".sale_id
									WHERE 1=1
									{$sql}
									
									GROUP BY
										".$this->db->dbprefix('sale_items').".service_types

										")->result();
		return $result;
	}

	public function getProductBySales($category_id = false, $start_date = false, $end_date = false, $product = false, $warehouse_id = false, $saleman = false, $biller = false, $project = false, $customer = false)
	{
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$sql = "";
		
		if ($category_id) {
            $sql .= " AND ".$this->db->dbprefix('products').".category_id = {$category_id}";			
        }
		if ($biller) {
            $sql .= " AND ".$this->db->dbprefix('sales').".biller_id = {$biller}";			
        }
		if ($customer) {
            $sql .= " AND ".$this->db->dbprefix('sales').".customer_id = {$customer}";			
        }
		if ($project) {
            $sql .= " AND ".$this->db->dbprefix('sales').".project_id = {$project}";			
        }
		if ($saleman) {
            $sql .= " AND ".$this->db->dbprefix('sales').".saleman_id = {$saleman}";			
        }
		if($product){
			$sql .= " AND ".$this->db->dbprefix('sale_items').".product_id= {$product}";
		}
		if ($start_date) {
			$sql .= " AND date >= '{$this->cus->fld($start_date)}'";
        }
		if($end_date){
			$sql .= " AND date <= '{$this->cus->fld($end_date,false,1)}'";
		}
		if(!$start_date && !$end_date){
			$sql .= " AND date(date) = '".date('Y-m-d')."' ";
		}
		if ($warehouse_id) {
            $sql .= " AND ".$this->db->dbprefix('sales').".warehouse_id = {$warehouse_id}";			
        }
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$sql .= " AND ".$this->db->dbprefix('sales').".created_by = {$this->session->userdata('user_id')}";
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$sql .= " AND ".$this->db->dbprefix('sales').".biller_id = {$this->session->userdata('biller_id')}";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$sql .= " AND ".$this->db->dbprefix('sales').".warehouse_id IN ".$warehouse_ids;
		}
		
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = "";
			if($projects){
				foreach($projects as $pr){
					$project_details .= $pr.",";
				}
			}
			
			if(!$project && $projects[0] != 'all'){
				$rtrim = rtrim($project_details,",");
				if($rtrim){
					$sql .= " AND ".$this->db->dbprefix('sales').".project_id IN ({$rtrim})";
				}
				
			}
		}
		$this->db->query("SET group_concat_max_len = 10000000");
		$result = $this->db->query("SELECT
										".$this->db->dbprefix('sale_items').".product_id,
										".$this->db->dbprefix('sale_items').".product_code,
										".$this->db->dbprefix('sale_items').".product_type,
										".$this->db->dbprefix('sale_items').".unit_price,
										".$this->db->dbprefix('sale_items').".product_unit_id,
										GROUP_CONCAT(".$this->db->dbprefix('sale_items').".raw_materials SEPARATOR'#') as raw_materials,
										product_name,
										sum(".$this->db->dbprefix('sale_items').".cost * ".$this->db->dbprefix('sale_items').".quantity + IFNULL(".$this->db->dbprefix('sale_items').".foc,0)) as cost,
										sum(".$this->db->dbprefix('sale_items').".unit_price)  / count(".$this->db->dbprefix('sale_items').".id) as price,
										tax,
										SUM(".$this->db->dbprefix('sale_items').".item_discount) as item_discount,
										SUM(".$this->db->dbprefix('sale_items').".quantity) as quantity,
										SUM(".$this->db->dbprefix('sale_items').".foc) as foc,
										SUM(".$this->db->dbprefix('sale_items').".subtotal) as subtotal,
										".$this->db->dbprefix('products').".quantity as stock_quantity,
										".$this->db->dbprefix('sales').".reference_no,
										".$this->db->dbprefix('sales').".customer
									FROM
										".$this->db->dbprefix('sale_items')."
									LEFT JOIN ".$this->db->dbprefix('products')." ON ".$this->db->dbprefix('products').".id = product_id
									LEFT JOIN ".$this->db->dbprefix('sales')." ON ".$this->db->dbprefix('sales').".id = ".$this->db->dbprefix('sale_items').".sale_id
									WHERE 1=1
									{$sql}
									GROUP BY
										".$this->db->dbprefix('products').".id, 
										unit_price, 
										product_unit_id")->result();
		return $result;
	}
	
	public function getProductByPurchases($category_id = false, $start_date = false, $end_date = false, $product = false, $warehouse_id = false, $biller = false, $project = false, $supplier = false)
	{
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$sql = "";
		
		if ($category_id) {
            $sql .= " AND ".$this->db->dbprefix('products').".category_id = {$category_id}";			
        }
		if ($biller) {
            $sql .= " AND ".$this->db->dbprefix('purchases').".biller_id = {$biller}";			
        }
		if ($supplier) {
            $sql .= " AND ".$this->db->dbprefix('purchases').".supplier_id = {$supplier}";			
        }
		if ($project) {
            $sql .= " AND ".$this->db->dbprefix('purchases').".project_id = {$project}";			
        }
		if($product){
			$sql .= " AND ".$this->db->dbprefix('purchase_items').".product_id= {$product}";
		}
		if ($start_date) {
			$sql .= " AND ".$this->db->dbprefix('purchases').".date >= '{$this->cus->fld($start_date)}'";
        }
		if($end_date){
			$sql .= " AND ".$this->db->dbprefix('purchases').".date <= '{$this->cus->fld($end_date,false,1)}'";
		}
		if(!$start_date && !$end_date){
			$sql .= " AND date(".$this->db->dbprefix('purchases').".date) = '".date('Y-m-d')."' ";
		}
		if ($warehouse_id) {
            $sql .= " AND ".$this->db->dbprefix('purchases').".warehouse_id = {$warehouse_id}";			
        }
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$sql .= " AND ".$this->db->dbprefix('purchases').".created_by = {$this->session->userdata('user_id')}";
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$sql .= " AND ".$this->db->dbprefix('purchases').".biller_id = {$this->session->userdata('biller_id')}";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$sql .= " AND ".$this->db->dbprefix('purchases').".warehouse_id IN ".$warehouse_ids;
		}
		
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = "";
			if($projects){
				foreach($projects as $pr){
					$project_details .= $pr.",";
				}
			}
			
			if(!$project && $projects[0] != 'all'){
				$rtrim = rtrim($project_details,",");
				if($rtrim){
					$sql .= " AND ".$this->db->dbprefix('purchases').".project_id IN ({$rtrim})";
				}
				
			}
		}
		$this->db->query("SET group_concat_max_len = 10000000");
		$result = $this->db->query("SELECT
										".$this->db->dbprefix('purchase_items').".product_id,
										".$this->db->dbprefix('purchase_items').".product_code,
										".$this->db->dbprefix('purchase_items').".product_type,
										".$this->db->dbprefix('purchase_items').".unit_cost,
										product_name,
										SUM(".$this->db->dbprefix('purchase_items').".item_discount) as item_discount,
										SUM(".$this->db->dbprefix('purchase_items').".quantity) as quantity,
										SUM(".$this->db->dbprefix('purchase_items').".subtotal) as subtotal,
										".$this->db->dbprefix('purchases').".supplier
									FROM
										".$this->db->dbprefix('purchase_items')."
									LEFT JOIN ".$this->db->dbprefix('products')." ON ".$this->db->dbprefix('products').".id = product_id
									LEFT JOIN ".$this->db->dbprefix('purchases')." ON ".$this->db->dbprefix('purchases').".id = ".$this->db->dbprefix('purchase_items').".purchase_id
									WHERE 1=1
									{$sql}
									GROUP BY
										".$this->db->dbprefix('products').".id, 
										unit_cost, 
										product_unit_id")->result();
		return $result;
	}
	
	public function getAllSaleman($post = null)
    {
		if(isset($post['saleman']) && $post['saleman']){
			$this->db->where('sales.saleman_id', $post['saleman']);
		}
		if(isset($post['user']) && $post['user']){
			$this->db->where('sales.created_by', $post['user']);
		}
		if(isset($post['biller']) && $post['biller']){
			$this->db->where('sales.biller_id', $post['biller']);
		}
		if(isset($post['customer']) && $post['customer']){
			$this->db->where('sales.customer_id', $post['customer']);
		}
		if(isset($post['warehouse']) && $post['warehouse']){
			$this->db->where('sales.warehouse_id', $post['warehouse']);
		}
		if(isset($post['reference_no']) && $post['reference_no']){
			$reference_no = trim($post['reference_no']);
			$this->db->like('sales.reference_no', $reference_no);
		}
		if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->db->where('created_by='.$this->session->userdata('user_id').' OR saleman_id='.$this->session->userdata('user_id').'', NULL, FALSE); 
			
        } elseif ($this->Customer) {
            $this->db->where('customer_id', $this->session->userdata('user_id'));
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		
    	$this->db->select("
						users.id, 
						concat(first_name,' ', last_name) as username,
						saleman_id", false)
                ->from("sales")
                ->join('users', 'sales.saleman_id = users.id AND users.saleman = 1', 'left')
				->where('sales.saleman_id <>', 0)
				->group_by("users.id");				
        
		$q = $this->db->get();		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllSaleBySalemanId($saleman_id=null, $post = NULL)
    {	
		$user = $this->site->getUser($this->session->userdata("user_id"));
		if(isset($post['user']) && $post['user']){
			$this->db->where('sales.created_by', $post['user']);
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('sales.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if(isset($post['biller']) && $post['biller']){
			$this->db->where('sales.biller_id', $post['biller']);
		}
		if(isset($post['project']) && $post['project']){
			$this->db->where('sales.project_id', $post['project']);
		}
		if(isset($post['customer']) && $post['customer']){
			$this->db->where('sales.customer_id', $post['customer']);
		}
		if(isset($post['warehouse']) && $post['warehouse']){
			$this->db->where('sales.warehouse_id', $post['warehouse']);
		}
		if(isset($post['reference_no']) && $post['reference_no']){
		   $reference_no = trim($post['reference_no']);
		   $this->db->like('sales.reference_no', $reference_no);
		}
		if (isset($post['start_date']) && $post['start_date']) {
            $this->db->where('date >=', $this->cus->fld($post['start_date']));
			$this->db->where('date <=', $this->cus->fld($post['end_date'],false,1));
        }
		if(isset($post['payment_status']) && $post['payment_status']){
			$this->db->where('sales.payment_status', $post['payment_status']);
		}
		if(isset($post['tank']) && $post['tank']){
			$this->db->where('sales.tank_id', $post['tank']);
		}
		if(!$post){
			$this->db->where('date(date)', date("Y-m-d"));
		}
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = array();
			if($projects){
				foreach($projects as $pr){
					$project_details[] = $pr;
				}
			}
			
			if(!isset($post['project'])){
				if($project_details){
					$this->db->where_in('sales.project_id', $project_details);
				}
			}
		}
		$q = $this->db->where('saleman_id', $saleman_id)
					  ->order_by('id', 'desc')
					  ->get('sales');		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllSalesDetail($post = null)
    {
		$user = $this->site->getUser($this->session->userdata("user_id"));
		
		if(isset($post['user']) && $post['user']){
			$this->db->where('sales.created_by', $post['user']);
		}
		
		if(isset($post['biller']) && $post['biller']){
			$this->db->where('sales.biller_id', $post['biller']);
		}
		
		if(isset($post['project']) && $post['project']){
			$this->db->where('sales.project_id', $post['project']);
		}
		
		if(isset($post['customer']) && $post['customer']){
			$this->db->where('sales.customer_id', $post['customer']);
		}
		
		if(isset($post['product']) && $post['product']){
			$this->db->where('sale_items.product_id', $post['product']);
		}
		
		if(isset($post['serial_no']) && $post['serial_no']){
			$this->db->where('sale_items.serial_no', $post['serial_no']);
		}
		
		if(isset($post['warehouse']) && $post['warehouse']){
			$this->db->where('sales.warehouse_id', $post['warehouse']);
		}
		if(isset($post['saleman']) && $post['saleman']){
			$this->db->where('sales.saleman_id', $post['saleman']);
		}
		
		if(isset($post['vehicle_model']) && $post['vehicle_model']){
			$this->db->where('sales.vehicle_model', $post['vehicle_model']);
		}
		if(isset($post['vehicle_plate']) && $post['vehicle_plate']){
			$this->db->where('sales.vehicle_plate', $post['vehicle_plate']);
		}
		if(isset($post['vehicle_vin']) && $post['vehicle_vin']){
			$this->db->where('sales.vehicle_vin_no', $post['vehicle_vin']);
		}
		if(isset($post['mechanic']) && $post['mechanic']){
			$this->db->where('sales.mechanic', $post['mechanic']);
		}
		if(isset($post['sale_tax']) && $post['sale_tax']){
			if($post['sale_tax']=="yes"){
				$this->db->where('IFNULL('.$this->db->dbprefix("sales").'.order_tax,0)!=', 0);
			}else if($post['sale_tax']=="no"){
				$this->db->where('IFNULL('.$this->db->dbprefix("sales").'.order_tax,0)', 0);
			}
		}
		if(isset($post['sale_type']) && $post['sale_type']){
			if($post['sale_type']=='sale'){
				$this->db->where('sales.pos !=', 1);
				$this->db->where('sales.sale_status !=', 'returned');
			}else if($post['sale_type']=='pos'){
				$this->db->where('sales.pos', 1);
				$this->db->where('sales.sale_status !=', 'returned');
			}else if($post['sale_type']=='return'){
				$this->db->where('sales.sale_status', 'returned');
			}
			
		}
		
		if (isset($post['start_date']) && $post['start_date']) {
            $this->db->where('date >=', $this->cus->fld($post['start_date']));
			$this->db->where('date <=', $this->cus->fld($post['end_date'],false,1));
        }else{
			$this->db->where('date(date) >=', date('Y-m-d'));
			$this->db->where('date(date) <=', date('Y-m-d'));
		}
		
		if(isset($post['reference_no']) && $post['reference_no']){
			$reference_no = trim($post['reference_no']);
			$this->db->where('sales.reference_no', $reference_no);
		}
		
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = array();
			if($projects){
				foreach($projects as $pr){
					$project_details[] = $pr;
				}
			}
			
			if(!isset($post['project'])){
				if($project_details){
					$this->db->where_in('sales.project_id', $project_details);
				}
			}
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('sales.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		$this->db->where('sales.sale_status !=', 'draft');

    	$this->db->select("sales.*", false)
                ->from("sales")
                ->join('users', 'sales.saleman_id = users.id', 'left')
				->join('sale_items', 'sales.id = sale_items.sale_id', 'left')
				->order_by("sales.id","desc")
				->group_by("sales.id");				
        
		$q = $this->db->get();		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllPurchasetail($post = null)
    {
		$user = $this->site->getUser($this->session->userdata("user_id"));
		
		if(isset($post['user']) && $post['user']){
			$this->db->where('purchases.created_by', $post['user']);
		}
		
		if(isset($post['biller']) && $post['biller']){
			$this->db->where('purchases.biller_id', $post['biller']);
		}
		
		if(isset($post['project']) && $post['project']){
			$this->db->where('purchases.project_id', $post['project']);
		}
		
		if(isset($post['supplier']) && $post['supplier']){
			$this->db->where('purchases.supplier_id', $post['supplier']);
		}
		if(isset($post['product']) && $post['product']){
			$this->db->where('purchase_items.product_id', $post['product']);
		}
		if(isset($post['warehouse']) && $post['warehouse']){
			$this->db->where('purchases.warehouse_id', $post['warehouse']);
		}
		if(isset($post['purchase_tax']) && $post['purchase_tax']){
			if($post['purchase_tax']=="yes"){
				$this->db->where('IFNULL('.$this->db->dbprefix("purchases").'.order_tax,0)!=', 0);
			}else if($post['purchase_tax']=="no"){
				$this->db->where('IFNULL('.$this->db->dbprefix("purchases").'.order_tax,0)', 0);
			}
		}
		if(isset($post['reference_no']) && $post['reference_no']){
			$reference_no = trim($post['reference_no']);
			$this->db->where('purchases.reference_no', $reference_no);
		}
		

		if (isset($post['start_date']) && $post['start_date']) {
            $this->db->where('date('.$this->db->dbprefix("purchases").'.date) >=', $this->cus->fld($post['start_date']));
			$this->db->where('date('.$this->db->dbprefix("purchases").'.date) <=', $this->cus->fld($post['end_date'],false,1));
        }else{
			$this->db->where('date('.$this->db->dbprefix("purchases").'.date) =', date('Y-m-d'));
		}
		
		
		
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = array();
			if($projects){
				foreach($projects as $pr){
					$project_details[] = $pr;
				}
			}
			
			if(!isset($post['project'])){
				if($project_details){
					$this->db->where_in('purchases.project_id', $project_details);
				}
			}
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('purchases.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id', $this->session->userdata('biller_id'));
		}
		
		$this->db->where('purchases.status !=', 'draft');
		$this->db->where('purchases.status !=', 'freight');

    	$this->db->select("purchases.*", false)
                ->from("purchases")
				->join('purchase_items', 'purchases.id = purchase_items.purchase_id', 'left')
				->order_by("purchases.id","desc")
				->group_by("purchases.id");				
        
		$q = $this->db->get();		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllSaleItemsId($sale_id=null, $post = NULL)
    {	
		if(isset($post['serial_no']) && $post['serial_no']){
			$this->db->where('sale_items.serial_no', $post['serial_no']);
		}
		if(isset($post['product_zero']) && $post['product_zero']==1){
			$this->db->where('sale_items.unit_price', 0);
		}
		
    	$this->db->where('sale_id', $sale_id);
		$q = $this->db->select('products.electricity, sale_items.*')
					 ->from('sale_items')
					 ->join('products','products.id=sale_items.product_id','left')
					 ->get();		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllPurchaseItemsId($purchase_id = false)
    {		
    	$this->db->where('purchase_id', $purchase_id);
		$q = $this->db->get('purchase_items');		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getPaymentBySaleID($sale_id = false)
    {
		$this->db->select('IFNULL(sum(amount),0) AS paid,IFNULL(sum(discount),0) AS discount');
		$this->db->group_by('sale_id');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getPaymentByPurchaseID($sale_id = false)
    {
		$this->db->select('IFNULL(sum(amount),0) AS paid,IFNULL(sum(discount),0) AS discount');
		$this->db->group_by('purchase_id');
        $q = $this->db->get_where('payments', array('purchase_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getARAgingByCustomerID($customer_id = null, $type = null, $end_date = false, $biller_id = false)
	{
		$v = "";
		
		if($end_date != 0){
			$v .= " AND DATE(cus_sales.date) <= '".$end_date."'";
		}else{
			$end_date = date('Y-m-d');
		}
		if($type && $type == 5){
			$v .= " AND DATE(cus_sales.date) = '".$end_date."'";
		}
		if($type && $type == 1){
			$v .= " AND DATE(cus_sales.date) BETWEEN '".$end_date."' - INTERVAL 30 DAY AND '".$end_date."' - INTERVAL 1 DAY";
		}
		if($type && $type == 2){
			$v .= " AND DATE(cus_sales.date) BETWEEN '".$end_date."' - INTERVAL 60 DAY AND '".$end_date."' - INTERVAL 31 DAY";
		}
		if($type && $type == 3){
			$v .= " AND DATE(cus_sales.date) BETWEEN '".$end_date."' - INTERVAL 90 DAY AND '".$end_date."' - INTERVAL 61 DAY";
		}
		if($type && $type == 4){
			$v .= " AND DATE(cus_sales.date) BETWEEN '".$end_date."' - INTERVAL 20 YEAR AND '".$end_date."' - INTERVAL 91 DAY";
		}
		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$user = $this->session->userdata('user_id');
			$v .= " AND (cus_sales.saleman_id={$user} OR cus_sales.created_by={$user})";
        }
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$v .= " AND cus_sales.biller_id= '".$this->session->userdata('biller_id')."'";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$v .= " AND cus_sales.warehouse_id IN ".$warehouse_ids;
		}
		

		if($biller_id){
			$v .= " AND cus_sales.biller_id = '".$biller_id."'";
		}
		
		$saleman = $this->input->post("saleman");
		if($saleman){
			$v .= " AND cus_sales.saleman_id={$saleman}";
		}
		
		$sql = "SELECT
					cus_sales.*, 
					SUM(cus_payments.discount) as discount, 
					SUM(IFNULL(cus_payments.amount,0) - IFNULl(paid_total_return,0)) as paid,
					SUM(cus_return.grand_total_return) as grand_total_return
				FROM
					cus_sales
				LEFT JOIN (
					SELECT
						sale_id,
						IFNULL(sum(amount),0) AS amount,
						IFNULL(sum(discount),0) AS discount
					FROM cus_payments
					GROUP BY
						sale_id					
				) as cus_payments ON cus_payments.sale_id = cus_sales.id
				LEFT JOIN (SELECT
						    sale_id,
							IFNULL(sum(abs(grand_total)),0) AS grand_total_return,
							IFNULL(sum(abs(paid)),0) AS paid_total_return
						FROM
							cus_sales
						WHERE 
							cus_sales.sale_id > 0
						AND cus_sales.sale_status = 'returned'	
						GROUP BY
							cus_sales.sale_id) as cus_return ON cus_return.sale_id=cus_sales.id	
				WHERE
					customer_id = {$customer_id}
				AND (cus_sales.sale_id IS NULL OR cus_sales.sale_id = 0)
				{$v} GROUP BY cus_sales.id";
				
		$q = $this->db->query($sql);
		return $q->result();		
	}
	
	public function getAPAgingBySupplierID($supplier_id = null, $type = null, $end_date = false, $biller_id =false)
	{
		$v = "";
		if($end_date != 0){
			$v .= " AND DATE(cus_purchases.date) <= '".$end_date."'";
		}else{
			$end_date = date('Y-m-d');
		}
		if($type && $type == 5){
			$v .= " AND DATE(cus_purchases.date) = '".$end_date."'";
		}
		if($type && $type == 1){
			$v .= " AND DATE(cus_purchases.date) BETWEEN '".$end_date."' - INTERVAL 30 DAY AND '".$end_date."' - INTERVAL 1 DAY";
		}
		if($type && $type == 2){
			$v .= " AND DATE(cus_purchases.date) BETWEEN '".$end_date."' - INTERVAL 60 DAY AND '".$end_date."' - INTERVAL 31 DAY";
		}
		if($type && $type == 3){
			$v .= " AND DATE(cus_purchases.date) BETWEEN '".$end_date."' - INTERVAL 90 DAY AND '".$end_date."' - INTERVAL 61 DAY";
		}
		if($type && $type == 4){
			$v .= " AND DATE(cus_purchases.date) BETWEEN '".$end_date."' - INTERVAL 20 YEAR AND '".$end_date."' - INTERVAL 91 DAY";
		}
		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$user = $this->session->userdata('user_id');
			$v .= " AND cus_purchases.created_by={$user}";
        }
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$v .= " AND cus_purchases.biller_id= '".$this->session->userdata('biller_id')."'";
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$v .= " AND cus_purchases.warehouse_id IN ".$warehouse_ids;
		}
		if($biller_id){
			$v .= " AND cus_purchases.biller_id = '".$biller_id."'";
		}
		
		$sql = "SELECT
					cus_purchases.*, 
					abs(IFNULL(cus_purchases.return_purchase_total,0)) as grand_total_return,
					(IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0)) as paid, 
					round((cus_purchases.grand_total-(IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))-abs(cus_purchases.return_purchase_total)),".$this->Settings->decimals.") as balance
				FROM
					cus_purchases
				LEFT JOIN (
						SELECT 
							purchase_id,
							abs(paid) as return_paid 
						FROM 
							cus_purchases 
						WHERE 
							purchase_id > 0 
						AND status <> 'draft' 
						AND status <> 'freight'
				) as pur_return ON pur_return.purchase_id = cus_purchases.id
				WHERE
					supplier_id = {$supplier_id}
				AND	status != 'returned'
				{$v} GROUP BY cus_purchases.id";
		$q = $this->db->query($sql);
		return $q->result();		
	}
	
	public function getExpensesAPAgingBySupplierID($supplier_id = null, $type = null,$end_date = false, $biller_id = false)
	{
		$v = "";
		if($end_date != 0){
			$v .= " AND DATE(cus_expenses.date) <= '".$end_date."'";
		}else{
			$end_date = date('Y-m-d');
		}
		if($type && $type == 5){
			$v .= " AND DATE(cus_expenses.date) = '".$end_date."'";
		}
		if($type && $type == 1){
			$v .= " AND DATE(cus_expenses.date) BETWEEN '".$end_date."' - INTERVAL 30 DAY AND '".$end_date."' - INTERVAL 1 DAY";
		}
		if($type && $type == 2){
			$v .= " AND DATE(cus_expenses.date) BETWEEN '".$end_date."' - INTERVAL 60 DAY AND '".$end_date."' - INTERVAL 31 DAY";
		}
		if($type && $type == 3){
			$v .= " AND DATE(cus_expenses.date) BETWEEN '".$end_date."' - INTERVAL 90 DAY AND '".$end_date."' - INTERVAL 61 DAY";
		}
		if($type && $type == 4){
			$v .= " AND DATE(cus_expenses.date) BETWEEN '".$end_date."' - INTERVAL 20 YEAR AND '".$end_date."' - INTERVAL 91 DAY";
		}		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$user = $this->session->userdata('user_id');
			$v .= " AND cus_expenses.created_by={$user}";
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$v .= " AND cus_expenses.biller_id= '".$this->session->userdata('biller_id')."'";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$v .= " AND cus_expenses.warehouse_id IN ".$warehouse_ids;
		}
		if($biller_id){
			$v .= " AND cus_expenses.biller_id='".$biller_id."'";
		}
		
		$sql = "SELECT
					cus_expenses.*,
					IFNULL(cus_expenses.paid,0) as paid, 
					round((cus_expenses.grand_total-IFNULL(cus_expenses.paid,0)),".$this->Settings->decimals.") as balance
				FROM
					cus_expenses
				WHERE
					supplier_id = {$supplier_id}
				{$v} GROUP BY cus_expenses.id";
		$q = $this->db->query($sql);
		return $q->result();		
	}
	
	public function getAllStockQuantity($transaction = false, $qty_type = false, $warehouse_id = false, $product_id = false, $start_date = false, $end_date = false, $expiry_date = false)
	{
		$this->db->select('transaction_id,SUM(quantity) as quantity,date,product_id,reference_no,users.first_name,users.last_name, real_unit_cost');		
		if ($qty_type && $transaction != 'begin' &&  $transaction != 'balance') {
			if($qty_type == 'minus'){
				$this->db->where('stockmoves.quantity < 0');
			}else{
				$this->db->where('stockmoves.quantity > 0');
			}
        }
		if ($product_id) {
            $this->db->where('stockmoves.product_id', $product_id);
        }
		if ($transaction && $transaction != 'begin' &&  $transaction != 'balance') {
			if($transaction=='CDelivery'){
				$this->db->where_in('stockmoves.transaction', array('CDelivery','CError','CAdjustment','CFuel'));
			}else if($transaction=='Purchases'){
				$this->db->where_in('stockmoves.transaction', array('Purchases','Receives'));
			}else if($transaction=='Sale'){
				$this->db->where_in('stockmoves.transaction', array('Sale','Delivery','FuelCustomer'));
			}else{
				$this->db->where('stockmoves.transaction', $transaction);
			}
        }
		if ($warehouse_id) {
            $this->db->where('stockmoves.warehouse_id', $warehouse_id);
        }
		if($start_date){
			if($transaction == 'begin'){
				$this->db->where('date('.$this->db->dbprefix('stockmoves').'.date) < "'.$this->cus->fsd($start_date).'"');
			}else if($transaction != 'balance'){
				$this->db->where('date('.$this->db->dbprefix('stockmoves').'.date) >= "'.$this->cus->fsd($start_date).'"');
			}
		}
		if($end_date){
			$this->db->where('date('.$this->db->dbprefix('stockmoves').'.date) <= "'.$this->cus->fsd($end_date).'"');
		}
		
		if($expiry_date){
			$this->db->where('date(IFNULL('.$this->db->dbprefix('stockmoves').'.expiry,"0000-00-00")) = "'.$this->cus->fsd($expiry_date).'"');
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('stockmoves.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		
		$this->db->join('users','users.id = stockmoves.user_id','left');
		$this->db->group_by('transaction,transaction_id');
        $q = $this->db->get('stockmoves');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getCustomerAR($end_date = false, $warehouse_id = false, $biller = false, $project = false, $customer = false, $user_id = false, $reference_no = false)
	{
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$where = "";
		if ($biller) {
            $where .= " AND cus_sales.biller_id = {$biller}";			
        }
		if ($project) {
            $where .= " AND cus_sales.project_id = {$project}";			
        }
		if ($end_date) {
			$where .= " AND date(cus_sales.date) <= '{$this->cus->fsd($end_date)}'";
			$end_date = $this->cus->fsd($end_date);
        }else{
			$where .= " AND date(cus_sales.date) <= '".date('Y-m-d')."'";
			$end_date = date('Y-m-d');
		}
		
		if ($warehouse_id) {
            $where .= " AND cus_sales.warehouse_id = {$warehouse_id}";			
        }
		
		if ($customer) {
            $where .= " AND cus_sales.customer_id = {$customer}";			
        }

		if ($user_id) {
            $where .= " AND cus_sales.created_by = {$user_id}";			
        }

		if ($reference_no) {
            $where .= " AND cus_sales.reference_no = '{$reference_no}'";			
        }
		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$where .= " AND cus_sales.created_by = {$this->session->userdata('user_id')}";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$where .= " AND cus_sales.biller_id= '".$this->session->userdata('biller_id')."'";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$where .= " AND cus_sales.warehouse_id IN ".$warehouse_ids;
		}
		
		
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = "";
			if($projects){
				foreach($projects as $pr){
					$project_details .= $pr.",";
				}
			}
			
			if(!$project && $projects[0] != 'all'){
				$rtrim = rtrim($project_details,",");
				if($rtrim){
					$where .= " AND cus_sales.project_id IN ({$rtrim})";
				}
				
			}
		}
		
		$result = $this->db->query("SELECT
										cus_sales.customer,
										cus_companies.company,
										cus_sales.customer_id,
										SUM(cus_sales.grand_total) AS grand_total,
										SUM(
											IFNULL(cus_payments.amount, 0)
										) AS amount_payment,
										sum(IFNULL(return_total, 0)) AS return_total,
										sum(IFNULL(return_paid, 0)) AS return_paid
									FROM
										cus_sales
									LEFT JOIN cus_companies ON cus_companies.id = cus_sales.customer_id 
									LEFT JOIN (
										SELECT
											sale_id,
											sum(
												IFNULL(amount, 0) + IFNULL(discount, 0)
											) AS amount
										FROM
											cus_payments
										WHERE 
											date(date) <= '".$end_date."'
										AND sale_id > 0
										GROUP BY
											sale_id
									) AS cus_payments ON cus_payments.sale_id = cus_sales.id
									LEFT JOIN (
										SELECT
											sale_id,
											abs(grand_total) AS return_total,
											abs(paid) AS return_paid
										FROM
											cus_sales
										WHERE 
											date(date) <= '".$end_date."'
										AND sale_id > 0
									) AS cus_return ON cus_return.sale_id = cus_sales.id
									WHERE (cus_sales.sale_id IS NULL OR cus_sales.sale_id = 0) ".$where."
									GROUP BY
										customer_id")->result();
		return $result;
	}

	
	
	public function getARInv($end_date = false, $warehouse_id = false, $biller = false, $project = false,  $user_id = false, $reference_no = false)
	{
		
		$where = "";
		if ($biller) {
            $where .= " AND cus_sales.biller_id = {$biller}";			
        }
		if ($project) {
            $where .= " AND cus_sales.project_id = {$project}";			
        }

        if ($end_date) {
			$where .= " AND date(cus_sales.date) <= '{$this->cus->fsd($end_date)}'";
			$end_date = $this->cus->fsd($end_date);
        }else{
			$where .= " AND date(cus_sales.date) <= '".date('Y-m-d')."'";
			$end_date = date('Y-m-d');
		}		
		if ($warehouse_id) {
            $where .= " AND cus_sales.warehouse_id = {$warehouse_id}";			
        }
		if ($user_id) {
            $where .= " AND cus_sales.created_by = {$user_id}";			
        }
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$where .= " AND cus_sales.created_by = {$this->session->userdata('user_id')}";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$where .= " AND cus_sales.biller_id= '".$this->session->userdata('biller_id')."'";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$where .= " AND cus_sales.warehouse_id IN ".$warehouse_ids;
		}
		if ($reference_no) {
            $where .= " AND cus_sales.reference_no = '{$reference_no}'";			
        }
		
		
		$user = $this->site->getUser($this->session->userdata("user_id"));
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = "";
			if($projects){
				foreach($projects as $pr){
					$project_details .= $pr.",";
				}
			}
			
			if(!$project && $projects[0] != 'all'){
				$rtrim = rtrim($project_details,",");
				if($rtrim){
					$where .= " AND cus_sales.project_id IN ({$rtrim})";
				}
				
			}
		}
		
		$q = $this->db->query("SELECT
										cus_sales.id,
										cus_sales.customer_id,
										cus_sales.reference_no,
										cus_sales.date,
										cus_sales.grand_total,
										cus_sales.return_id,
										cus_sales.note,
										cus_payments.amount as paid,
										cus_return.return_paid as payment_return,
										cus_return.return_total as grand_total_return
									FROM
										cus_sales
									LEFT JOIN (
										SELECT
											sale_id,
											sum(
												IFNULL(amount, 0) + IFNULL(discount, 0)
											) AS amount
										FROM
											cus_payments
										WHERE 
											date(date) <= '".$end_date."'
										AND sale_id > 0	
										GROUP BY
											sale_id
									) AS cus_payments ON cus_payments.sale_id = cus_sales.id
									LEFT JOIN (
										SELECT
											sale_id,
											abs(grand_total) AS return_total,
											abs(paid) AS return_paid
										FROM
											cus_sales
										WHERE 
											date(date) <= '".$end_date."'
										AND sale_id > 0
									) AS cus_return ON cus_return.sale_id = cus_sales.id	
									WHERE (cus_sales.sale_id IS NULL OR cus_sales.sale_id = 0) ".$where."
									ORDER BY
										date");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->customer_id][] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getPaymentSales($end_date = false)
	{
		if($end_date){
			$this->db->where("date(date) <=",$this->cus->fsd($end_date));
		}
		$this->db->where("sale_id >",0);
		$q = $this->db->get('payments');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->sale_id][] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getSaleReturn($sale_id = false, $end_date = false)
	{
		if($end_date){
			$this->db->where("date(date) <=", $this->cus->fsd($end_date));
		}
		$q = $this->db->get_where('sales',array("sale_id"=>$sale_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getSupplierAP($end_date = false, $warehouse_id = false, $biller = false, $project = false, $supplier = false, $user_id = false, $reference_no = false)
	{
		$user = $this->site->getUser($this->session->userdata("user_id"));
		
		$where = "";
		$where_expense = "";

		if ($biller) {
            $where .= " AND cus_purchases.biller_id = {$biller}";	
            $where_expense .= " AND cus_expenses.biller_id = {$biller}";		
        }
		if ($project) {
            $where .= " AND cus_purchases.project_id = {$project}";	
            $where_expense .= " AND cus_expenses.project_id = {$project}";		
        }

		if ($end_date) {
			$where .= " AND date(cus_purchases.date) <= '{$this->cus->fsd($end_date)}'";
			$where_expense .= " AND date(cus_expenses.date) <= '{$this->cus->fsd($end_date)}'";
			$end_date = $this->cus->fsd($end_date);
        }else{
			$where .= " AND date(cus_purchases.date) <= '".date('Y-m-d')."'";
			$where_expense .= " AND date(cus_expenses.date) <= '".date('Y-m-d')."'";
			$end_date = date('Y-m-d');
		}
		
		if ($warehouse_id) {
            $where .= " AND cus_purchases.warehouse_id = {$warehouse_id}";
            $where_expense .= " AND cus_expenses.warehouse_id = {$warehouse_id}";  			
        }
		
		if ($supplier) {
            $where .= " AND cus_purchases.supplier_id = {$supplier}";
            $where_expense .= " AND cus_expenses.supplier_id = {$supplier}";  			
        }

		if ($user_id) {
            $where .= " AND cus_purchases.created_by = {$user_id}";	
            $where_expense .= " AND cus_expenses.created_by = {$user_id}";  		
        }

		if ($reference_no) {
            $where .= " AND cus_purchases.reference_no = '{$reference_no}'";
            $where_expense .= " AND cus_expenses.reference = {$reference_no}";  			
        }
		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$where .= " AND cus_purchases.created_by = {$this->session->userdata('user_id')}";
            $where_expense .= " AND cus_expenses.created_by = {$this->session->userdata('user_id')}";
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$where .= " AND cus_purchases.biller_id= '".$this->session->userdata('biller_id')."'";
			$where_expense .= " AND cus_expenses.biller_id = '".$this->session->userdata('biller_id')."'";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$where .= " AND cus_purchases.warehouse_id IN ".$warehouse_ids;
			$where_expense .= " AND cus_expenses.warehouse_id IN ".$warehouse_ids;
		}
		
		
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = "";
			if($projects){
				foreach($projects as $pr){
					$project_details .= $pr.",";
				}
			}
			if(!$project && $projects[0] != 'all'){
				$rtrim = rtrim($project_details,",");
				if($rtrim){
					$where .= " AND cus_purchases.project_id IN ({$rtrim})";
                    $where_expense .= " AND cus_expenses.project_id IN ({$rtrim})";
				}
			}
		}
		
		$where_expense .= " AND cus_expenses.status = 'approved'";
		
		$result = $this->db->query("SELECT
										cus_companies.company as supplier,
										cus_companies.id as supplier_id,
										SUM(
											IFNULL(cus_purchases.grand_total,0) + IFNULL(expense_amount,0)
										) AS grand_total,
										SUM(
											IFNULL(cus_payments.amount, 0) + IFNULL(expense_paid, 0)
										) AS amount_payment,
										sum(IFNULL(return_total, 0)) AS return_total,
										sum(IFNULL(return_paid, 0)) AS return_paid
									FROM
										cus_companies
									LEFT JOIN (SELECT * 
                                                    FROM cus_purchases 
                                                WHERE 1=1 
                                                {$where}
                                    ) as cus_purchases ON cus_purchases.supplier_id = cus_companies.id
									LEFT JOIN (
										SELECT
											purchase_id,
											sum(
												IFNULL(amount, 0) + IFNULL(discount, 0)
											) AS amount
										FROM
											cus_payments
										WHERE 
											purchase_id > 0	
										AND date(date) <= '".$end_date."'	
										GROUP BY
											purchase_id
									) AS cus_payments ON cus_payments.purchase_id = cus_purchases.id
									LEFT JOIN (
										SELECT
											purchase_id,
											abs(grand_total) AS return_total,
											abs(paid) AS return_paid
										FROM
											cus_purchases
										WHERE
											purchase_id > 0	
										AND date(date) <= '".$end_date."'
									) AS cus_return ON cus_return.purchase_id = cus_purchases.id
									LEFT JOIN (
										SELECT
											supplier_id,
											sum(grand_total) AS expense_amount,
											sum(cus_ex_payments.amount) AS expense_paid
										FROM
											cus_expenses
										LEFT JOIN (
											SELECT
												expense_id,
												sum(IFNULL(amount, 0) + IFNULL(discount, 0)) AS amount
											FROM
												cus_payments
											WHERE 
												expense_id > 0	
											AND date(date) <= '".$end_date."'		
											GROUP BY
												expense_id
										) AS cus_ex_payments ON cus_ex_payments.expense_id = cus_expenses.id
										WHERE 1=1 ".$where_expense."
										GROUP BY
											supplier_id
									) AS cus_expenses ON cus_expenses.supplier_id = cus_companies.id
									WHERE (cus_purchases.purchase_id IS NULL OR cus_purchases.purchase_id = 0 OR cus_purchases.`status` = 'freight') 
                                    AND cus_companies.group_id=4
									GROUP BY
										cus_companies.id
                                    HAVING grand_total <> 0
                                    ")->result();
		return $result;
	}
	
	public function getAPExpense($end_date = false, $warehouse_id = false, $biller = false, $project = false, $user_id = false, $reference_no = false)
	{
		$where = "";
		if ($biller) {
            $where .= " AND cus_expenses.biller_id = {$biller}";			
        }
		if ($project) {
            $where .= " AND cus_expenses.project_id = {$project}";			
        }
		if ($end_date) {
			$where .= " AND date(cus_expenses.date) <= '{$this->cus->fsd($end_date)}'";
			$end_date = $this->cus->fsd($end_date);
        }else{
			$where .= " AND date(cus_expenses.date) <= '".date('Y-m-d')."'";
			$end_date = date('Y-m-d');
		}
		if ($warehouse_id) {
            $where .= " AND cus_expenses.warehouse_id = {$warehouse_id}";			
        }
		if ($user_id) {
            $where .= " AND cus_expenses.created_by = {$user_id}";			
        }
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$where .= " AND cus_expenses.created_by = {$this->session->userdata('user_id')}";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$where .= " AND cus_expenses.biller_id= '".$this->session->userdata('biller_id')."'";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$where .= " AND cus_expenses.warehouse_id IN ".$warehouse_ids;
		}
		if ($reference_no) {
            $where .= " AND cus_expenses.reference = '{$reference_no}'";			
        }
		$where .= " AND cus_expenses.status = 'approved'";
		
		
		
		$user = $this->site->getUser($this->session->userdata("user_id"));
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = "";
			if($projects){
				foreach($projects as $pr){
					$project_details .= $pr.",";
				}
			}
			if(!$project && $projects[0] != 'all'){
				$rtrim = rtrim($project_details,",");
				if($rtrim){
					$where .= " AND cus_expenses.project_id IN ({$rtrim})";
				}
				
			}
		}
		
		$q = $this->db->query("SELECT
										cus_expenses.id,
										cus_expenses.date,
										cus_expenses.reference,
										cus_expenses.supplier_id,
										cus_expenses.amount,
										cus_expenses.grand_total,
										cus_payments.amount as paid,
										cus_expenses.note
									FROM
										cus_expenses
									LEFT JOIN (
										SELECT
											expense_id,
											sum(
												IFNULL(amount, 0) + IFNULL(discount, 0)
											) AS amount
										FROM
											cus_payments
										WHERE 
											date(date) <= '".$end_date."'
										AND expense_id > 0
										GROUP BY
											expense_id
									) AS cus_payments ON cus_payments.expense_id = cus_expenses.id	
									WHERE 1=1 ".$where."
									ORDER BY
										date");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->supplier_id][] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getAPInv($end_date = false, $warehouse_id = false, $biller = false, $project = false, $user_id = false, $reference_no = false)
	{
		
		$where = "";
		if ($biller) {
            $where .= " AND cus_purchases.biller_id = {$biller}";			
        }
		if ($project) {
            $where .= " AND cus_purchases.project_id = {$project}";			
        }
        if ($end_date) {
			$where .= " AND date(cus_purchases.date) <= '{$this->cus->fsd($end_date)}'";
			$end_date = $this->cus->fsd($end_date);
        }else{
			$where .= " AND date(cus_purchases.date) <= '".date('Y-m-d')."'";
			$end_date = date('Y-m-d');
		}
		if ($warehouse_id) {
            $where .= " AND cus_purchases.warehouse_id = {$warehouse_id}";			
        }
		if ($user_id) {
            $where .= " AND cus_purchases.created_by = {$user_id}";			
        }
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$where .= " AND cus_purchases.created_by = {$this->session->userdata('user_id')}";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$where .= " AND cus_purchases.biller_id= '".$this->session->userdata('biller_id')."'";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$where .= " AND cus_purchases.warehouse_id IN ".$warehouse_ids;
		}
		if ($reference_no) {
            $where .= " AND cus_purchases.reference_no = '{$reference_no}'";			
        }
		
		
		$user = $this->site->getUser($this->session->userdata("user_id"));
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = "";
			if($projects){
				foreach($projects as $pr){
					$project_details .= $pr.",";
				}
			}
			if(!$project && $projects[0] != 'all'){
				$rtrim = rtrim($project_details,",");
				if($rtrim){
					$where .= " AND cus_purchases.project_id IN ({$rtrim})";
				}
				
			}
		}
		
		$q = $this->db->query("SELECT
										cus_purchases.id,
										cus_purchases.supplier_id,
										cus_purchases.reference_no,
										cus_purchases.date,
										cus_purchases.grand_total,
										cus_purchases.return_id,
										cus_purchases.note,
										cus_payments.amount as paid,
										cus_return.return_paid as payment_return,
										cus_return.return_total as grand_total_return
									FROM
										cus_purchases
									LEFT JOIN (
										SELECT
											purchase_id,
											sum(
												IFNULL(amount, 0) + IFNULL(discount, 0)
											) AS amount
										FROM
											cus_payments
										WHERE 
											date(date) <= '".$end_date."'
										AND purchase_id > 0	
										GROUP BY
											purchase_id
									) AS cus_payments ON cus_payments.purchase_id = cus_purchases.id
									LEFT JOIN (
										SELECT
											purchase_id,
											abs(grand_total) AS return_total,
											abs(paid) AS return_paid
										FROM
											cus_purchases
										WHERE 
											date(date) <= '".$end_date."'
										AND purchase_id > 0
									) AS cus_return ON cus_return.purchase_id = cus_purchases.id	
									WHERE (cus_purchases.purchase_id IS NULL OR cus_purchases.purchase_id = 0 OR cus_purchases.`status` = 'freight') ".$where."
									ORDER BY
										date");
										
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->supplier_id][] = $row;
			}
			return $data;
		}
		return false;
		
	}
	
	public function getPaymentPurchases($end_date = false)
	{
		if($end_date){
			$this->db->where("date(date) <=", $this->cus->fsd($end_date));
		}
		$this->db->where("purchase_id >",0);
		$q = $this->db->get('payments');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->purchase_id][] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getPurchaseReturn($purchase_id = false, $end_date = false)
	{
		if($end_date){
			$this->db->where("date(date) <=", $this->cus->fsd($end_date));
		}
		$q = $this->db->get_where('purchases',array("purchase_id"=>$purchase_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getPaymentExpenses($end_date = false)
	{
		if($end_date){
			$this->db->where("date(date) <=", $this->cus->fsd($end_date));
		}
		$this->db->where("expense_id >",0);
		$q = $this->db->get('payments');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->expense_id][] = $row;
			}
			return $data;
		}
		return false;
	}

	
	public function getProductUnitByUnitID($unit_id = false)
	{
			 $this->db->join("units","units.id=unit_id","left");
		$q = $this->db->get_where('product_units',array("unit_id"=>$unit_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

	public function getTotalPawnsAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
	{
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.amount,0)) as total_amount', FALSE)
			->join('pawns','pawns.id = payments.pawn_id','inner')
			->where(''.$this->db->dbprefix("payments").'.type', 'pawn_sent')
			->where(''.$this->db->dbprefix("payments").'.pawn_rate_id IS NULL')
			->where(''.$this->db->dbprefix("payments").'.pawn_return_id IS NULL')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
		
		if ($warehouse) {
			$this->db->where('warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('biller_id', $biller);
		}

		if ($project) {
			$this->db->where('project_id', $project);
		}		
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getTotalPawnReturnsAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
	{
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.amount,0)) as total_amount', FALSE)
			->join('pawn_returns','pawn_returns.id = payments.pawn_return_id','inner')
			->where(''.$this->db->dbprefix("payments").'.type', 'pawn_received')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
		
		if ($warehouse) {
			$this->db->where('warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('biller_id', $biller);
		}

		if ($project) {
			$this->db->where('project_id', $project);
		}		
			
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('biller_id', $this->session->userdata('biller_id'));
		}		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getTotalPawnRatesAmount($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
	{
        $this->db->select('count('.$this->db->dbprefix("payments").'.id) as total, SUM(COALESCE('.$this->db->dbprefix("payments").'.amount,0)) as total_amount', FALSE)
			->join('pawn_rates','pawn_rates.id = payments.pawn_rate_id','inner')
			->where(''.$this->db->dbprefix("payments").'.type', 'pawn_rate')
            ->where('date('.$this->db->dbprefix("payments").'.date) BETWEEN "'.$start_date.'" and "'.$end_date.'"');
		
		if ($warehouse) {
			$this->db->where('warehouse_id', $warehouse);
		}

		if ($biller) {
			$this->db->where('biller_id', $biller);
		}

		if ($project) {
			$this->db->where('project_id', $project);
		}		
		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('payments.created_by', $this->session->userdata('user_id'));
		}	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}	
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getSalemanProducts()
	{
		$biller = $this->input->post('biller');
		$project = $this->input->post('project');
		$category  = $this->input->post('category');
		$saleman = $this->input->post('saleman');
		$warehouse = $this->input->post('warehouse');
		$start_date = $this->input->post('start_date');
		$end_date = $this->input->post('end_date');
		
		if(isset($product) && $product){
			$this->db->where("product_id", $product);
		}
		if(isset($biller) && $biller){
			$this->db->where("sales.biller_id", $biller);
		}
		if(isset($project) && $project){
			$this->db->where("project_id", $project);
		}
		if(isset($saleman) && $saleman){
			$this->db->where("saleman_id", $saleman);
		}
		if(isset($warehouse) && $warehouse){
			$this->db->where("sales.warehouse_id", $warehouse);
		}
		if(isset($start_date) && $start_date){
			$this->db->where("date >=", $this->cus->fld($start_date));
			$this->db->where("date <=", $this->cus->fld($end_date,false,1));
		}
		if(!$this->input->post()){
			$this->db->where("date(date)", date("Y-m-d"));
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		
		$this->db->select("
				concat(first_name,' ', last_name) as saleman, 
				cus_sales.saleman_id")
			 ->join("sales","sales.id=sale_items.sale_id","left")
			 ->join("users","users.id=sales.saleman_id","left")
			 ->join("products","products.id=product_id","left")
			 ->where("products.type <>","service")
			 ->where("saleman_id >",0)
			 ->group_by("saleman_id");
		$q = $this->db->get("sale_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getSalemanProductDetails($saleman_id = false)
	{
		$product = $this->input->post('product');
		$biller = $this->input->post('biller');
		$project = $this->input->post('project');
		$category  = $this->input->post('category');
		$saleman = $this->input->post('saleman');
		$warehouse = $this->input->post('warehouse');
		$start_date = $this->input->post('start_date');
		$end_date = $this->input->post('end_date');
		
		if($product){
			$this->db->where("product_id", $product);
		}
		if($biller){
			$this->db->where("biller_id", $biller);
		}
		if($project){
			$this->db->where("project_id", $project);
		}
		if($category){
			$this->db->where("category_id", $category);
		}
		if($warehouse){
			$this->db->where("warehouse_id", $warehouse);
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		
		if($start_date){
			$this->db->where("date >=", $this->cus->fld($start_date));
			$this->db->where("date <=", $this->cus->fld($end_date,false,1));
		}
		if(!$this->input->post()){
			$this->db->where("date(date)", date("Y-m-d"));
		}
		$this->db->select("
						sale_items.product_id,
						sale_items.product_code,
						sale_items.product_name,
						sale_items.product_type,
						sale_items.unit_price,
						sum(cus_sale_items.cost) as cost,
						sum(cus_sale_items.quantity) as quantity,
						sum(cus_sale_items.foc) as foc,
						sum(cus_sale_items.discount) as discount,
						sum(cus_sale_items.item_discount) as item_discount,
						sum(cus_sale_items.subtotal) as subtotal,
						sale_items.real_unit_price,
						sale_items.unit_quantity")
				 ->join("sales","sales.id=sale_items.sale_id","left")
				 ->join("products","products.id=product_id","left")
				 ->where("saleman_id", $saleman_id)
				 ->where("products.type <>","service")
				 ->group_by("product_id, unit_price, saleman_id");
		$q = $this->db->get("sale_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getChartCategoriesData()
    {
		$warehouse = $this->input->post('warehouse') ? $this->input->post('warehouse') : NULL;
        $category = $this->input->post('category') ? $this->input->post('category') : NULL;
        $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : NULL;
        $end_date = $this->input->post('end_date') ? $this->input->post('end_date') : NULL;
		
        $pp = "( SELECT pp.category_id as category, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase, p.created_by from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi ON pp.id = pi.product_id
                left join " . $this->db->dbprefix('purchases') . " p ON p.id = pi.purchase_id ";
        
		$sp = "( SELECT sp.category_id as category, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale, s.created_by from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si ON sp.id = si.product_id
                left join " . $this->db->dbprefix('sales') . " s ON s.id = si.sale_id WHERE sale_status != 'returned' ";
		
		$pp .= " WHERE 1=1";
     
		if($start_date){
			$start_date = $this->cus->fld($start_date);
			$end_date = $end_date ? $this->cus->fld($end_date, false, 1) : date('Y-m-d');
			
			$pp .= " AND p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
			$sp .= " AND s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
		}
		if ($warehouse) {
			$pp .= " AND p.warehouse_id = '{$warehouse}' ";
			$sp .= " AND s.warehouse_id = '{$warehouse}' ";
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$pp .= " AND p.created_by = '{$this->session->userdata('user_id')}' ";
			$sp .= " AND s.created_by = '{$this->session->userdata('user_id')}' ";
			
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$pp .= " AND p.biller_id = '{$this->session->userdata('biller_id')}' ";
			$sp .= " AND s.biller_id = '{$this->session->userdata('biller_id')}' ";
			
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$pp .= " AND p.warehouse_id IN ".$warehouse_ids;
			$sp .= " AND s.warehouse_id IN ".$warehouse_ids;
			
		}
        $pp .= " GROUP BY pp.category_id ) PCosts";
        $sp .= " GROUP BY sp.category_id ) PSales";
		$this->db->select($this->db->dbprefix('categories') . ".id, " . $this->db->dbprefix('categories') . ".code, " . $this->db->dbprefix('categories') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left')
                ->group_by('categories.id')
				->order_by('categories.code', 'asc');
		if ($category) {
			$this->db->where($this->db->dbprefix('categories') . ".id", $category);
		}
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		
        return FALSE;
    }
	
	public function getChartProductsDataByCategory($category_id =false)
    {
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
		
        $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase,p.created_by from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id ";
        $sp = "( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale,s.created_by from " . $this->db->dbprefix('sales') . " s JOIN " . $this->db->dbprefix('sale_items') . " si on s.id = si.sale_id WHERE sale_status != 'returned'";
		
		$pp .= " WHERE 1=1";
		if($start_date){
			$start_date = $this->cus->fld($start_date);
			$end_date = $end_date ? $this->cus->fld($end_date, false, 1) : date('Y-m-d');
			
			$pp .= " AND p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
			$sp .= " AND s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
		}
		if ($warehouse) {
			$pp .= " AND p.warehouse_id = '{$warehouse}' ";
			$sp .= " AND s.warehouse_id = '{$warehouse}' ";
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$pp .= " AND p.created_by = '{$this->session->userdata('user_id')}' ";
			$sp .= " AND s.created_by = '{$this->session->userdata('user_id')}' ";
			
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$pp .= " AND p.biller_id = '{$this->session->userdata('biller_id')}' ";
			$sp .= " AND s.biller_id = '{$this->session->userdata('biller_id')}' ";
			
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$pp .= " AND p.warehouse_id IN ".$warehouse_ids;
			$sp .= " AND s.warehouse_id IN ".$warehouse_ids;
		}
		
        $pp .= " GROUP BY pi.product_id ) PCosts";
        $sp .= " GROUP BY si.product_id ) PSales";
		$this->db->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
				COALESCE( PCosts.purchasedQty, 0 ) as PurchasedQty,
				COALESCE( PSales.soldQty, 0 ) as SoldQty,
				COALESCE( ".$this->db->dbprefix('products') . ".quantity, 0 ) as BalacneQty,
				COALESCE( PCosts.totalPurchase, 0 ) as TotalPurchase,
				COALESCE( ".$this->db->dbprefix('products') . ".quantity * ".$this->db->dbprefix('products') . ".cost, 0 ) as TotalBalance,
				COALESCE( PSales.totalSale, 0 ) as TotalSales,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
				->where("products.category_id", $category_id)
				->or_where("products.subcategory_id", $category_id)
				->order_by('products.name');
				
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAuditTrailByID($id = false)
	{
		$q = $this->db->get_where("audit_trails", array("id"=>$id));
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	
	public function getAdjustmentByID($id = false)
    {
        $q = $this->db->get_where('adjustments', array('id' => $id), 1);
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
	
	public function getUsingStockByID($id = false)
    {
        $q = $this->db->get_where('using_stocks', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getWareHouseByID($id = null)
	{
		$q = $this->db->get_where('warehouses',array('id',$id));
		if($q->num_rows() > 0){
			return $q->row();
		} return false;
	}

	public function getSaleItemsBySaleID($sale_id = false)
	{
		$post = $this->input->post();
		if($post['nozzle_no']){
			$this->db->where("nozzle_no", trim($post['nozzle_no']));
		}
		$q = $this->db->where("sale_id", $sale_id)->get("sale_items");
		if($q->num_rows() >0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getTankByID($id = false)
	{
		$q = $this->db->where("id",$id)->get("tanks");
		if($q->num_rows() >0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

	public function getTanks()
	{
		$q = $this->db->get("tanks");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getBillDetails()
	{
		$post = $this->input->post();
		if($this->input->post('reference_no')){
			$this->db->like('bills.reference_no', $post['reference_no']);
		}
		if($this->input->post('sale_reference_no')){
			$this->db->like('sales.reference_no', $post['sale_reference_no']);
		}
		if($this->input->post('user')){
			$this->db->where('bills.created_by', $post['user']);
		}
		if($this->input->post('saleman')){
			$this->db->where('bills.saleman_id', $post['saleman']);
		}
		if($this->input->post('customer')){
			$this->db->where('bills.customer_id', $post['customer']);
		}
		if($this->input->post('biller')){
			$this->db->where('bills.biller_id', $post['biller']);
		}
		if($this->input->post('warehouse')){
			$this->db->where('bills.warehouse_id', $post['warehouse']);
		}
		if($this->input->post('start_date')){
			$this->db->where('bills.date >=', $this->cus->fld($this->input->post('start_date')));
			$this->db->where('bills.date <=', $this->cus->fld($this->input->post('end_date')));
		}
		if($this->input->post('show_details')){
			$this->db->group_by("bills.reference_no");
		}else{
			$this->db->group_by("sales.reference_no");
		}
		if(!$this->input->post()){
			$this->db->where('date(cus_bills.date)', date("Y-m-d"));
		}
		$q = $this->db->select("
								bills.id,
								bills.date,
								bills.reference_no,
								bills.customer,
								IFNULL(cus_companies.name,cus_companies.company) as biller,
								bills.customer,
								sales.reference_no as sale_ref,
								CONCAT(cus_users.first_name,' ',cus_users.last_name) as user,
								(SELECT total FROM cus_bills as cus_rbills WHERE id=MAX(cus_bills.id)) as total,
								bills.order_discount_id as discount,
								bills.print,
								bills.table_name as table,
								COUNT(cus_bills.id) as count,
								CONCAT(pusers.first_name,' ',pusers.last_name) as puser,
								suspend_status
								")
						->join("sales","sales.id=bills.sale_id","left")
						->join("users","users.id=bills.created_by","left")
						->join("users as pusers","pusers.id=bills.print_by","left")
						->join("companies","companies.id=bills.biller_id","left")
						->order_by("bills.reference_no")
						->get("bills");
						
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getBillItemsDetails($bill_id = false)
	{
		$q = $this->db->select("
								bill_items.id,
								bill_items.product_id,
								bill_items.product_code,
								bill_items.product_name,
								bill_items.unit_price,
								bill_items.quantity,
								bill_items.discount,
								bill_items.subtotal,
								bill_items.product_unit_id,
								bill_items.product_unit_code,
								bill_items.unit_quantity,
								bill_items.real_unit_price,
								bill_items.item_discount,
								bill_items.bill_id,
								bill_items.tax,
								bill_items.tax_rate_id,
								bill_items.item_tax,
								bill_items.option_id,
								bill_items.cost")
					  ->where("bill_items.bill_id", $bill_id)
					  ->get("bill_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getPaymentsID($id = false)
    {
        $q = $this->db->get_where('payments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getPurchasePawnByID($id = false)
	{
		$q = $this->db->get_where('pawn_purchases',array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	
	public function getSubCategoryByCategory($category_id = false)
	{
		if($category_id){
			$q = $this->db->get_where("categories",array("parent_id"=> $category_id));
			if($q->num_rows() > 0){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}
		}
		return false;
	}

	
	public function getTotalSaleByMonth($month = false)
	{
		$biller = $this->input->post('biller');
		$warehouse = $this->input->post('warehouse');
		$project = $this->input->post('project');
		$customer = $this->input->post('customer');
		$year = $this->input->post('year');
		if($biller){
			$this->db->where("biller_id", $biller);
		}
		if($warehouse){
			$this->db->where("warehouse_id", $warehouse);
		}
		if($project){
			$this->db->where("project_id", $project);
		}
		if($customer){
			$this->db->where("customer_id", $customer);
		}
		if($year){
			$this->db->where("YEAR(date)", $year);
		}
		if(!$this->input->post()){
			$this->db->where("YEAR(date)", date("Y"));
		}
		$q = $this->db->select("SUM(grand_total) as amt_sale")
					  ->from("sales")
					  ->where("MONTH(date)", $month)
					  ->where("sale_status !=", "returned")
					  ->where("sale_status !=", "draft")
					  ->get();
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row->amt_sale;
		}
		return false;
	}
	
	public function getTotalReturnSaleByMonth($month = false)
	{
		$biller = $this->input->post('biller');
		$warehouse = $this->input->post('warehouse');
		$project = $this->input->post('project');
		$customer = $this->input->post('customer');
		$year = $this->input->post('year');
		if($biller){
			$this->db->where("biller_id", $biller);
		}
		if($warehouse){
			$this->db->where("warehouse_id", $warehouse);
		}
		if($project){
			$this->db->where("project_id", $project);
		}
		if($customer){
			$this->db->where("customer_id", $customer);
		}
		if($year){
			$this->db->where("YEAR(date)", $year);
		}
		if(!$this->input->post()){
			$this->db->where("YEAR(date)", date("Y"));
		}
		$q = $this->db->select("SUM(grand_total) as amt_sale")
					  ->from("sales")
					  ->where("MONTH(date)", $month)
					  ->where("sale_status", "returned")
					  ->get();
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row->amt_sale;
		}
		return false;
	}
	
	public function getTotalPaymentByMonth($month = false)
	{
		$biller = $this->input->post('biller');
		$warehouse = $this->input->post('warehouse');
		$project = $this->input->post('project');
		$customer = $this->input->post('customer');
		$year = $this->input->post('year');
		if($biller){
			$this->db->where("biller_id", $biller);
		}
		if($warehouse){
			$this->db->where("warehouse_id", $warehouse);
		}
		if($project){
			$this->db->where("project_id", $project);
		}
		if($customer){
			$this->db->where("customer_id", $customer);
		}
		if($year){
			$this->db->where("YEAR(cus_payments.date)", $year);
		}
		if(!$this->input->post()){
			$this->db->where("YEAR(cus_payments.date)", date("Y"));
		}
		$q = $this->db->select("SUM(amount) as amt_paid")
					  ->from("payments")
					  ->join("sales","sales.id=payments.sale_id","left")
					  ->where("MONTH(cus_payments.date)", $month)
					  ->where("payments.type", 'received')
					  ->get();
					  
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row->amt_paid;
		}
		return false;
	}
	
	public function getTotalDiscountByMonth($month = false)
	{
		$biller = $this->input->post('biller');
		$warehouse = $this->input->post('warehouse');
		$project = $this->input->post('project');
		$customer = $this->input->post('customer');
		$year = $this->input->post('year');
		if($biller){
			$this->db->where("biller_id", $biller);
		}
		if($warehouse){
			$this->db->where("warehouse_id", $warehouse);
		}
		if($project){
			$this->db->where("project_id", $project);
		}
		if($customer){
			$this->db->where("customer_id", $customer);
		}
		if($year){
			$this->db->where("YEAR(cus_payments.date)", $year);
		}
		if(!$this->input->post()){
			$this->db->where("YEAR(cus_payments.date)", date("Y"));
		}
		$q = $this->db->select("sum(discount) as amt_discount")
					  ->from("payments")
					  ->join("sales","sales.id=payments.sale_id","left")
					  ->where("MONTH(cus_payments.date)", $month)
					  ->where("payments.type", 'received')
					  ->get();
					  
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row->amt_discount;
		}
		return false;
	}
	
	public function getLoanProducts()
	{
		$q = $this->db->get("loan_products");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getSaleReportByCustomers($customer = NULL)
    {
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $this->db->select("
                    date, 
                    reference_no, 
                    biller, 
                    customer, 
                    customer_id, 
                    grand_total,
                    IFNULL(total_return,0) as total_return,
                    IFNULL(cus_sales.paid,0) - IFNULL(abs(total_return_paid),0) as paid,
                    IFNULL(cus_payments.discount,0) as discount,
                    ROUND((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return - cus_return.total_return_paid,0))),".$this->Settings->decimals.") as balance", FALSE)
                ->from('sales')
                ->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
				->join('(SELECT
							sale_id,
							IFNULL(sum(amount),0) AS paid,
							IFNULL(sum(discount),0) AS discount
						FROM
							'.$this->db->dbprefix('payments').'
						GROUP BY
							sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left')
				->join('(SELECT
							sum(abs(grand_total)) AS total_return,
							sum(abs(paid)) AS total_return_paid,
	                    	sale_id
						FROM
							'.$this->db->dbprefix('sales').'
						WHERE sale_status = "returned"
						GROUP BY
							sale_id) as cus_return', 'cus_return.sale_id=sales.id', 'left')
				->where("sales.sale_status !=","void")
                ->group_by('sales.id')
                ->order_by('sales.date asc');

            if ($user) {
                $this->db->where('sales.created_by', $user);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $this->cus->fld($start_date) . '" and "' . $this->cus->fld($end_date) . '"');
            }
            $q = $this->db->where('sale_status !=', 'returned')->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
                return $data;
            }
            return false;
    }
	
	public function getPurchaseReportBySuppliers($supplier = NULL)
	{
		$user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $this->db->select("
				purchases.date, 
				reference_no, 
				biller, 
				supplier, 
				supplier_id, 
				grand_total,
				IFNULL(total_return,0) as total_return,
				IFNULL(cus_purchases.paid,0) - IFNULL(abs(total_return_paid),0) as paid,
				IFNULL(cus_payments.discount,0) as discount,
				ROUND((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return - cus_return.total_return_paid,0))),".$this->Settings->decimals.") as balance", FALSE)
			->from('purchases')
			->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
			->join('(SELECT
						purchase_id,
						IFNULL(sum(amount),0) AS paid,
						IFNULL(sum(discount),0) AS discount
					FROM
						'.$this->db->dbprefix('payments').'
					GROUP BY
						purchase_id) as cus_payments', 'cus_payments.purchase_id=purchases.id', 'left')
			->join('(SELECT
						sum(abs(grand_total)) AS total_return,
						sum(abs(paid)) AS total_return_paid,
						purchase_id
					FROM
						'.$this->db->dbprefix('purchases').'
					WHERE status = "returned"
					GROUP BY
						purchase_id) as cus_return', 'cus_return.purchase_id=purchases.id', 'left')
			->where("purchases.status !=","returned")
			->where("purchases.status !=","freight")
			->group_by('purchases.id')
			->order_by('purchases.date asc');

		if ($user) {
			$this->db->where('purchases.created_by', $user);
		}
		if ($biller) {
			$this->db->where('purchases.biller_id', $biller);
		}
		if ($supplier) {
			$this->db->where('purchases.supplier_id', $supplier);
		}
		if ($warehouse) {
			$this->db->where('purchases.warehouse_id', $warehouse);
		}
		if ($start_date) {
			$this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $this->cus->fld($start_date) . '" and "' . $this->cus->fld($end_date) . '"');
		}
		$q = $this->db->where('purchases.status !=', 'returned')->get();
		if ($q->num_rows() > 0) {
			foreach (($q->result()) as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getArryDailyExpenses(){
		$post = $this->input->post()?$this->input->post():$this->input->get();
		if(isset($post['biller']) && !empty($post['biller'])){
			$this->db->where("biller_id", trim($post['biller']));
		}
		if(isset($post['supplier']) && !empty($post['supplier'])){
			$this->db->where("supplier_id", trim($post['supplier']));
		}
		if(isset($post['project']) && !empty($post['project'])){
			$this->db->where("project_id", trim($post['project']));
		}
		if(isset($post['user']) && !empty($post['user'])){
			$this->db->where("created_by", trim($post['user']));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where("created_by", $this->session->userdata('view_right'));
		}
		if(isset($post['month']) && !empty($post['month'])){
			$this->db->where("MONTH(date)", trim($post['month']));
		}else{
			$this->db->where("MONTH(date)", date("m"));
		}
		if(isset($post['year']) && !empty($post['year'])){
			$this->db->where("YEAR(date)", trim($post['year']));
		}else{
			$this->db->where("YEAR(date)", date("Y"));
		}
		$q = $this->db->select("SUM(subtotal) as amount,category_id,DAY(date) as day")
					  ->where("status","approved")
					  ->join("expense_items","expense_items.expense_id=expenses.id","left")
					  ->group_by("category_id,DAY(date)")
					  ->get("expenses");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->category_id][$row->day] = $row->amount;
            }
            return $data;
        }
        return FALSE;
	}

	public function getArrayDailyRentalCount(){
		$post = $this->input->post()?$this->input->post():$this->input->get();
		if(isset($post['biller']) && !empty($post['biller'])){
			$this->db->where("biller_id", trim($post['biller']));
		}
		if(isset($post['supplier']) && !empty($post['supplier'])){
			$this->db->where("supplier_id", trim($post['supplier']));
		}
		if(isset($post['project']) && !empty($post['project'])){
			$this->db->where("project_id", trim($post['project']));
		}
		if(isset($post['user']) && !empty($post['user'])){
			$this->db->where("created_by", trim($post['user']));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where("created_by", $this->session->userdata('view_right'));
		}
		if(isset($post['month']) && !empty($post['month'])){
			$this->db->where("MONTH(date)", trim($post['month']));
		}else{
			$this->db->where("MONTH(date)", date("m"));
		}
		if(isset($post['year']) && !empty($post['year'])){
			$this->db->where("YEAR(date)", trim($post['year']));
		}else{
			$this->db->where("YEAR(date)", date("Y"));
		}
		$q = $this->db->select("SUM(frequency) as quantity,room_id,DAY(date) as day")
					  ->join("rental_items","rental_items.rental_id=rentals.id","left")
					  ->group_by("room_id,DAY(date)")
					  ->get("rentals");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->room_id][$row->day] = $row->quantity;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getDailyExpenseByAmount($category_id = 0, $day = 0, $month =0, $year = 0)
	{
		$post = $this->input->post()?$this->input->post():$this->input->get();
		if(isset($post['biller']) && !empty($post['biller'])){
			$this->db->where("biller_id", trim($post['biller']));
		}
		if(isset($post['supplier']) && !empty($post['supplier'])){
			$this->db->where("supplier_id", trim($post['supplier']));
		}
		if(isset($post['project']) && !empty($post['project'])){
			$this->db->where("project_id", trim($post['project']));
		}
		if(isset($post['user']) && !empty($post['user'])){
			$this->db->where("created_by", trim($post['user']));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where("created_by", $this->session->userdata('view_right'));
		}
		$q = $this->db->select("SUM(subtotal) as amount")
					  ->where("category_id", $category_id)
					  ->where("DAY(date)", $day)
					  ->where("MONTH(date)", $month)
					  ->where("YEAR(date)", $year)
					  ->where("status","approved")
					  ->join("expense_items","expense_items.expense_id=expenses.id","left")
					  ->get("expenses");
					  
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row->amount;
		}
		return false;
	}
	
	public function getDailyExpenseByCount()
	{
		$q = $this->db->where("parent_id >",0)->get('expense_categories');
        return $q->num_rows();
	}
	
	public function getDailyExpenses($limit = 0, $start = 0)
	{
		$q = $this->db->limit($limit, $start)
					  ->select("expense_categories.id, expense_categories.name, mains.name as main_cat")
		              ->join("expense_categories as mains","mains.id=expense_categories.parent_id","left")
					  ->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getDailyRentalsCount($limit = 0, $start = 0)
	{
		$q = $this->db->limit($limit, $start)
					  ->select("rental_rooms.id, rental_rooms.name, mains.name as main_cat")
		              ->join("rental_rooms as mains","mains.id=rental_rooms.id","left")
					  ->get('rental_rooms');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getSaleFuelDetails($limit = 0, $start = 0)
	{
		$post = $this->input->post()?$this->input->post():$this->input->get();
		if(isset($post['biller']) && !empty($post['biller'])){
			$this->db->where("fuel_sales.biller_id", trim($post['biller']));
		}
		if(isset($post['saleman']) && !empty($post['saleman'])){
			$this->db->where("fuel_sales.saleman_id", trim($post['saleman']));
		}
		if(isset($post['project']) && !empty($post['project'])){
			$this->db->where("fuel_sales.project_id", trim($post['project']));
		}
		if(isset($post['user']) && !empty($post['user'])){
			$this->db->where("fuel_sales.created_by", trim($post['user']));
		}
		if(isset($post['start_date']) && !empty($post['start_date'])){
			$this->db->where('fuel_sales.date BETWEEN "' . $this->cus->fld($post['start_date']) . '" and "' . $this->cus->fld($post['end_date']) . '"');
		}
		if(isset($post['reference_no']) && !empty($post['reference_no'])){
			$this->db->like("fuel_sales.reference_no", $post['reference_no']);
		}
		if(isset($post['warehouse']) && !empty($post['warehouse'])){
			$this->db->where("fuel_sales.warehouse_id", $post['warehouse']);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('fuel_sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('fuel_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where("fuel_sales.created_by", $this->session->userdata('view_right'));
		}
		if(!$post){
			$this->db->where('fuel_sales.date', date("Y-m-d"));
		}
		$q = $this->db->limit($limit, $start)
					  ->select("
						fuel_sales.id,
						fuel_sales.date, 
						fuel_sales.reference_no, 
						fuel_sales.biller, 
						fuel_sales.saleman, 
						CONCAT(cus_fuel_times.open_time,' - ',cus_fuel_times.close_time) as time,
						IFNULL(cus_fuel_sale_items.using_qty,0) as using_qty,
						IFNULL(cus_fuel_sale_items.customer_qty,0) as customer_qty,
						IFNULL(cus_fuel_sale_items.customer_amount,0) as customer_amount,
						IFNULL(cus_fuel_sale_items.quantity,0) as quantity,
						IFNULL(".$this->db->dbprefix('fuel_sales').".total,0) as total,
						IFNULL(".$this->db->dbprefix('fuel_sales').".total_cash,0) as total_cash,
						IFNULL(".$this->db->dbprefix('fuel_sales').".credit_amount,0) as credit_amount,
						IFNULL(".$this->db->dbprefix('fuel_sales').".total_cash_open,0) as total_cash_open,
						CONCAT(last_name,' ',first_name) as username,
						IF(ROUND(cus_sales.quantity,".$this->Settings->decimals.")>=ROUND(cus_fuel_sale_items.quantity,".$this->Settings->decimals."),'completed',IF(cus_sales.quantity > 0,'partial','pending')) as status")
					  ->from("fuel_sales")
					  ->join('fuel_times', 'fuel_times.id=fuel_sales.time_id', 'left')
					  ->join('(SELECT 
										fuel_sale_id,
										SUM(subtotal) as subtotal,
										SUM(quantity) as quantity
									FROM '.$this->db->dbprefix('sales').'
									LEFT JOIN '.$this->db->dbprefix('sale_items').' ON '.$this->db->dbprefix('sale_items').'.sale_id = cus_sales.id
									GROUP BY fuel_sale_id) as cus_sales','cus_sales.fuel_sale_id=fuel_sales.id','left')
					  ->join('(SELECT 
										fuel_sale_id,
										SUM(quantity) as quantity,
										SUM(using_qty) as using_qty,
										SUM(customer_qty) as customer_qty,
										SUM(customer_amount) as customer_amount
									FROM '.$this->db->dbprefix('fuel_sale_items').'
									GROUP BY fuel_sale_id) as cus_fuel_sale_items','cus_fuel_sale_items.fuel_sale_id=fuel_sales.id','left')
					  ->join("users","users.id=fuel_sales.created_by","left")
					  ->order_by("fuel_sales.id","desc")
					  ->get();
					  
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getSaleFuelItemsDetails($fuel_sale_id = 0)
	{
		$q = $this->db->select("
							fuel_sale_items.tank_id,
							fuel_sale_items.product_id,
							fuel_sale_items.nozzle_id,
							fuel_sale_items.nozzle_no,
							fuel_sale_items.nozzle_start_no,
							fuel_sale_items.nozzle_end_no,
							fuel_sale_items.quantity,
							fuel_sale_items.customer_qty,
							fuel_sale_items.using_qty,
							fuel_sale_items.customer_amount,
							tanks.name as tank,
							products.name as item")
					  ->from("fuel_sale_items")
					  ->join("tanks","tanks.id=tank_id","left")
					  ->join("products","products.id=product_id","left")
					  ->where("fuel_sale_id", $fuel_sale_id)
					  ->order_by("fuel_sale_items.id","desc")
					  ->get();
					  
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getDailyTankItems()
	{
		$q = $this->db->select("
						product_id, 
						products.name as product_name")
					  ->join("products","products.id=tank_nozzles.product_id","left")
					  ->join('tanks','tanks.id=tank_nozzles.tank_id','left')
					  ->group_by('products.id')
					  ->order_by('tanks.id, nozzle_no','asc')
					  ->get('tank_nozzles');
					  
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getDailyTanks()
	{
		$post = $this->input->post();
		if(isset($post['tank']) && !empty($post['tank'])){
			$this->db->where("tanks.id", $post['tank']);
		}
		$q = $this->db->select("
						tank_nozzles.id,
						tank_nozzles.nozzle_no,
						tank_id,
						tanks.name as tank")
					  ->join('tanks','tanks.id=tank_nozzles.tank_id','left')
					  ->group_by('tank_nozzles.id')
					  ->order_by('tanks.id, nozzle_no','asc')
					  ->get('tank_nozzles');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getDailyTankItemsQty($tank_id = null, $nozzle_id = null, $product_id = NULL)
	{
		$post = $this->input->post();
		if(isset($post['biller']) && !empty($post['biller'])){
			$this->db->where("fuel_sales.biller_id", trim($post['biller']));
		}
		if(isset($post['saleman']) && !empty($post['saleman'])){
			$this->db->where("fuel_sales.saleman_id", trim($post['saleman']));
		}
		if(isset($post['start_date']) && !empty($post['start_date'])){
			$this->db->where('fuel_sales.date BETWEEN "' . $this->cus->fld($post['start_date']) . '" and "' . $this->cus->fld($post['end_date']) . '"');
		}
		if(isset($post['warehouse']) && !empty($post['warehouse'])){
			$this->db->where("fuel_sales.warehouse_id", $post['warehouse']);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('fuel_sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('fuel_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where("fuel_sales.created_by", $this->session->userdata('view_right'));
		}
		$q = $this->db->select('
							MIN(nozzle_start_no) as nozzle_start_no,
							MAX(nozzle_end_no) as nozzle_end_no,
							SUM(IFNULL(quantity,0)) as quantity,
							SUM(IFNULL(using_qty,0)) as using_qty,
							SUM(IFNULL(customer_qty,0)) as customer_qty')
					  ->from('fuel_sale_items')
					  ->join('fuel_sales','fuel_sales.id=fuel_sale_items.fuel_sale_id','left')
					  ->where("tank_id", $tank_id)
					  ->where("nozzle_id", $nozzle_id)
					  ->where("product_id", $product_id)
					  ->get();
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function getRoomFloors()
	{
		$q = $this->db->select("rental_floors.*")
					  ->from("rental_rooms")
					  ->join("rental_floors","rental_rooms.floor=rental_floors.id","left")
					  ->where("rental_floors.id >", 0)
					  ->group_by("rental_floors.id")
					  ->get();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getRoomTypes()
	{
		$q = $this->db->select("rental_room_types.*")
					  ->from("rental_room_types")
					  ->where("rental_room_types.id >", 0)
					  ->get();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getRoomsByFloor($floor = false)
	{
		$q = $this->db->get_where("rental_rooms", array("floor"=>$floor));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getAllRooms($floor = false, $warehouse = false)
	{
		if((!$this->Owner && !$this->Admin) || !empty($this->session->userdata("warehouse_id"))){
			$warehouse_id = json_decode($this->session->userdata("warehouse_id"));
			$this->db->where_in("warehouse_id", $warehouse_id);
		}
		if($floor){
			$this->db->where("floor", $floor);
		}
		if($warehouse){
			$this->db->where("warehouse_id", $warehouse);
		}
		$q = $this->db->select('rental_rooms.*')
					  ->from('rental_rooms')
					  ->join('products','products.id=rental_rooms.product_id','left')
					  ->where('status','active')
					  ->where('products.id >', 0)
					  ->get();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getDailyRooms()
	{
		$biller = $this->input->post('biller')?$this->input->post('biller'):null;
		$warehouse = $this->input->post('warehouse')?$this->input->post('warehouse'):null;
		$floor = $this->input->post('floor')?$this->input->post('floor'):null;
		$room_type = $this->input->post('room_type')?$this->input->post('room_type'):null;
		$month = $this->input->post('month')?$this->input->post('month'):null;
		$year = $this->input->post('year')?$this->input->post('year'):null;
		if($biller){
			$this->db->where("rental_rooms.biller_id", $biller);
		}
		if($warehouse){
			$this->db->where("rental_rooms.warehouse_id", $warehouse);
		}
		if($floor){
			$this->db->where("rental_rooms.floor", $floor);
		}
		if($room_type){
			$this->db->where("rental_rooms.room_type_id", $room_type);
		}
		if(!$biller 
			&& !$warehouse 
			&& !$floor 
			&& !$room_type 
			&& !$month
			&& !$year
			){
				$this->db->where("rental_rooms.floor", (int)$this->Settings->default_floor);
			}
		$q = $this->db->select("rental_rooms.id,
								rental_rooms.name,
								rental_rooms.room_type_name,
								rental_rooms.product_id,
								rental_rooms.price,
								rental_rooms.room_type_id,
								rental_rooms.floor,
								rental_rooms.level,
								rental_floors.floor as floor_name,
								rental_rooms.warehouse_id")
					  ->from("rental_rooms")
					  ->join("rental_floors","rental_rooms.floor=rental_floors.id","left")
					  ->order_by('length(cus_rental_rooms.name),name','asc')
					  ->get();
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getRentalByDate($room_id = false, $day='', $month='', $year='')
	{
		$range_date = $year.'-'.sprintf('%02d', $month).'-'.sprintf('%02d', $day);
		$q = $this->db->select("
								rentals.id,
								rentals.date,
								rentals.customer_id,
								rentals.customer,
								rentals.warehouse_id,
								rentals.floor_id,
								rentals.room_id,
								rentals.total,
								rentals.from_date,
								rentals.to_date,
								rentals.checked_in,
								rentals.checked_out,
								rentals.status")
					  ->from("rentals")
					  ->where("rentals.room_id", $room_id)
					  ->where("rentals.status", 'checked_in')
					  ->get();
		if($q->num_rows() > 0){
			$checked_in = $q->row()->checked_in;
			$checked_out = $q->row()->checked_out;
			$to_date = $q->row()->to_date;
			if(empty($checked_out) OR $checked_out == '0000-00-00'){
				$checked_out = $to_date;
			}
			if(($range_date >= $checked_in) && ($range_date <= $checked_out)){
				return true;
			}
			return false;
		}
		return false;
	}

	public function getReservationByDate($room_id = false, $day='', $month='', $year='')
	{
		$range_date = $year.'-'.sprintf('%02d', $month).'-'.sprintf('%02d', $day);
		$q = $this->db->select("
								rentals.id,
								rentals.date,
								rentals.customer_id,
								rentals.customer,
								rentals.warehouse_id,
								rentals.floor_id,
								rentals.room_id,
								rentals.total,
								rentals.from_date,
								rentals.to_date,
								rentals.checked_in,
								rentals.checked_out,
								rentals.status")
					  ->from("rentals")
					  ->where("rentals.room_id", $room_id)
					  ->where("rentals.status", 'reservation')
					  ->get();
		if($q->num_rows() > 0){
			$checked_in = $q->row()->from_date;
			$checked_out = $q->row()->checked_out;
			$to_date = $q->row()->to_date;
			if(empty($checked_out) OR $checked_out == '0000-00-00'){
				$checked_out = $to_date;
			}
			if(($range_date >= $checked_in) && ($range_date <= $checked_out)){
				return true;
			}
			return false;
		}
		return false;
	}

	public function getBookedByDate($room_id = false, $day='', $month='', $year='')
	{
		$range_date = $year.'-'.sprintf('%02d', $month).'-'.sprintf('%02d', $day);
		$q = $this->db->select("
								rentals.id,
								rentals.date,
								rentals.customer_id,
								rentals.customer,
								rentals.warehouse_id,
								rentals.floor_id,
								rentals.room_id,
								rentals.total,
								rentals.from_date,
								rentals.to_date,
								rentals.checked_in,
								rentals.checked_out,
								rentals.status")
					  ->from("rentals")
					  ->where("rentals.room_id", $room_id)
					  ->where("rentals.status", 'booked')
					  ->get();
		if($q->num_rows() > 0){
			$checked_in = $q->row()->from_date;
			$checked_out = $q->row()->checked_out;
			$to_date = $q->row()->to_date;
			if(empty($checked_out) OR $checked_out == '0000-00-00'){
				$checked_out = $to_date;
			}
			if(($range_date >= $checked_in) && ($range_date <= $checked_out)){
				return true;
			}
			return false;
		}
		return false;
	}
	
	public function getProductSerails()
	{
		$product = $this->input->post('product');
		$category = $this->input->post('category');
		$warehouse = $this->input->post('warehouse');
		$status = $this->input->post('status');
		if($product){
			$this->db->where("product_serials.product_id", $product);
		}
		if($category){
			$this->db->where("products.category_id", $category);
		}
		if($warehouse){
			$this->db->where("product_serials.warehouse_id", $warehouse);
		}
		if($status != ""){
			if($status == '1'){
				$this->db->where("product_serials.inactive", 1);
			}else{
				$this->db->where("product_serials.inactive != ", 1);
			}
			
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
			$this->db->where_in('product_serials.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		$this->db->select('product_serials.*,products.name as product_name, products.code as product_code, warehouses.name as warehouse_name')
					->join('products','products.id = product_serials.product_id','inner')
					->join('warehouses','warehouses.id = product_serials.warehouse_id','inner')
					->order_by('products.code,warehouses.id')
					->order_by('product_serials.inactive','desc');
					
		$q = $this->db->get('product_serials');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;	
	}


	public function getAllBrands()
	{
		$q = $this->db->get("brands");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getAllTechnicians()
	{
		$this->db->where('technician',1);
		$q = $this->db->get("users");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getReturnPurchasesTotals($supplier_id = false)
    {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('supplier_id', $supplier_id)->where('grand_total < 0');
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getSupplierExpenses($supplier_id = false)
    {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		$this->db->where("expenses.status","approved");
        $this->db->from('expenses')->where('supplier_id', $supplier_id = false);
        return $this->db->count_all_results();
    }
	
	public function getPurchasesGrandTotals($supplier_id = false)
    {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('supplier_id', $supplier_id)->where('grand_total > 0');
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	

	// agency commission

	
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

	public function getAgencyByID($id = false)
	{
		$q = $this->db->where("agency",1)->where("id",$id)->get("users");
		if($q->num_rows()>0){
			$row = $q->row();
			return $row;
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
		$q = $this->db->select("payments.*")
					->from("payments")
					->where("transaction_id",$id)
					->where("transaction","AgencyPayment")
					->where("type","sent")
					->where("agency_id", $agency_id)
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
								sales.biller_id,
								sales.biller,
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
								sale_items.unit_price
								")
						->from("sales")
						->join("sale_items","sale_items.sale_id=sales.id","left")
						->join("(select sale_id, sum(amount) as amount, sum(interest_paid) as interest_paid from cus_payments where 1=1 ".$sql." group by sale_id) as payments","payments.sale_id=sales.id","left")
						->where("sales.pos","0")
						->where('sales.sale_status',"completed")
						->where('agency_id is not null')
						->where('agency_commission is not null')
						->group_by('sale_items.sale_id')
						->get();
		return $q->num_rows();
	}
	
	public function getValuationProducts($post = false){
		if(isset($post['product']) && $post['product']){
			$this->db->where('products.id', $post['product']);
		}
		$this->db->where_in("products.type",array('standard','raw_material','asset'));
		$this->db->order_by("products.code");
		$q = $this->db->get("products");
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getArrayStockmoves($post = false){
		if(isset($post['product']) && $post['product']){
			$this->db->where('stockmoves.product_id', $post['product']);
		}
		if(isset($post['warehouse']) && $post['warehouse']){
			$this->db->where('stockmoves.warehouse_id', $post['warehouse']);
		}
		if(isset($post['end_date']) && $post['end_date']){
			$this->db->where('stockmoves.date <=', $this->cus->fld($post['end_date']));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('stockmoves.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		$this->db->select("stockmoves.*,units.name as unit_name");
		$this->db->join("units","units.id = stockmoves.unit_id","left");
		$this->db->order_by("stockmoves.date,stockmoves.id","acs");
		$q = $this->db->get("stockmoves");
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[$row->product_id][] = $row;
			}
			return $data;
		}
		return false;
	}
		public function getMonthlyLoanPayments($biller_id = false,$project_id = false,$warehouse_id = false,$customer_id = false, $year = false){
		if($biller_id){
			$this->db->where("loans.biller_id",$biller_id);
		}
		if($project_id){
			$this->db->where("loans.project_id",$project_id);
		}
		if($warehouse_id){
			$this->db->where("loans.warehouse_id",$warehouse_id);
		}
		if($customer_id){
			$this->db->where("loans.customer_id",$customer_id);
		}
		if($year){
			$this->db->where("YEAR(".$this->db->dbprefix('payments').".date)",$year);
		}
		$this->db->select("loans.biller_id,MONTH(".$this->db->dbprefix('payments').".date) as month, sum(".$this->db->dbprefix('payments').".amount + ".$this->db->dbprefix('payments').".interest_paid) as paid");
		$this->db->join("payments","payments.loan_id = loans.id","inner");
		$this->db->group_by("loans.biller_id,MONTH(".$this->db->dbprefix('payments').".date)");
		$q = $this->db->get("loans");
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[$row->biller_id][$row->month] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getCustomerSaleDetailReport($biller_id = false,$warehouse_id = false,$created_by = false,$salesman_id = false,$customer_id = false,$product_id = false,$start_date = false,$end_date = false){
		if($biller_id){
			$this->db->where("sales.biller_id",$biller_id);
		}
		if($warehouse_id){
			$this->db->where("sales.warehouse_id",$warehouse_id);
		}
		if($created_by){
			$this->db->where("sales.created_by",$created_by);
		}
		if($salesman_id){
			$this->db->where("sales.saleman_id",$salesman_id);
		}
		if($customer_id){
			$this->db->where("sales.customer_id",$customer_id);
		}
		if($product_id){
			$this->db->where("sale_items.product_id",$product_id);
		}
		if($start_date){
			$this->db->where("sales.date >=",$start_date);
		}
		if($end_date){
			$this->db->where("sales.date <=",$end_date);
		}
		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('sales.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		
		$this->db->select("
							sales.id as sale_id,
							sales.reference_no,
							sales.date,
							sales.customer_id,
							sales.customer,
							sale_items.product_code,
							sale_items.product_name,
							sale_items.unit_quantity,
							sale_items.unit_price,
							units.name as unit_name
						");
		$this->db->join("sale_items","sale_items.sale_id = sales.id","inner");
		$this->db->join("units","units.id = sale_items.product_unit_id","left");
		$q = $this->db->get("sales");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data["customer"][$row->customer_id] = $row->customer;
				$data["sale"][$row->customer_id][$row->sale_id] = $row;
				$data["sale_item"][$row->sale_id][] = $row;
			}
			return $data;
		}
		return false;
	}

}
