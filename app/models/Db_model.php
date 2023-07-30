<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Db_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getLatestSales()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('sales.created_by', $this->session->userdata('user_id'));
        }

        $this->db
            ->select("id,
            DATE_FORMAT(date, '%Y-%m-%d %T') as date,
            reference_no,
            customer,
            grand_total,
            IFNULL(total_return,0) as total_return,
            IFNULL(cus_payments.paid + IFNULL(total_return_paid,0),0) as paid,
            IFNULL(cus_payments.discount,0) as discount,
            ROUND((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),2) as balance,
            IF (
                (
                    round((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),2) = 0
                ),
                'paid',
                IF (
                (
                    (grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))) = grand_total
                ),
                'pending',
                'partial'
            )) AS payment_status
            ")
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
                        sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left');
        
        $this->db->order_by('sales.id', 'desc');
        $q = $this->db->get("sales", 5);
        
        
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLastestQuotes()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get("quotes", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    
    public function getLastestInstallments()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->where('status', 'active');
        $this->db->order_by('id', 'desc');
        $q = $this->db->get("installments", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestPurchases()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        
        $this->db
            ->select("purchases.id as id, 
            DATE_FORMAT(".$this->db->dbprefix('purchases').".date, '%Y-%m-%d %T') as date, 
            purchases.reference_no,
            purchases.supplier, 
            purchases.grand_total,
            abs(IFNULL(cus_purchases.return_purchase_total,0)) as return_purchase_total,
            (IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0)) as paid, 
            round((cus_purchases.grand_total-(IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))-abs(cus_purchases.return_purchase_total)),2) as balance, 
            purchases.status, 
            IF(
                (round((cus_purchases.grand_total-(IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))-abs(cus_purchases.return_purchase_total)),2))=0,'paid',
                IF(
                    (abs(IFNULL(cus_purchases.return_purchase_total,0)) + IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))<>0,'partial',
                    'pending'
                )
            ) as payment_status
            ")
            ->join('(select purchase_id,abs(paid) as return_paid from cus_purchases WHERE purchase_id > 0 AND status <> "draft" AND status <> "freight") as pur_return','pur_return.purchase_id = purchases.id','left');
        
        $this->db->order_by('purchases.id', 'desc');
        $q = $this->db->get("purchases", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestTransfers()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get("transfers", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestCustomers()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get_where("companies", array('group_name' => 'customer'), 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestSuppliers()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get_where("companies", array('group_name' => 'supplier'), 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

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

        public function getChartDataExpense()
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
                        COALESCE ( S.expenses, 0 ) AS expenses,
                        COALESCE ( P.purchases, 0 ) AS purchases
                    FROM
                        (
                        SELECT
                            date_format( date, '%Y-%m' ) month,
                            SUM(grand_total) Expenses
                        FROM
                            ".$this->db->dbprefix('expenses')."
                        WHERE
                            date >= date_sub( now( ), INTERVAL 12 month ) 
                            AND status = 'approved'
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

    public function getStockValue()
    {
        $q = $this->db->query("SELECT SUM(qty*price) as stock_by_price, SUM(qty*cost) as stock_by_cost
        FROM (
            Select sum(COALESCE(" . $this->db->dbprefix('warehouses_products') . ".quantity, 0)) as qty, price, cost
            FROM " . $this->db->dbprefix('products') . "
            JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id=" . $this->db->dbprefix('products') . ".id
            GROUP BY " . $this->db->dbprefix('warehouses_products') . ".id ) a");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getBestSeller($start_date = NULL, $end_date = NULL)
    {
        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('first day of this month')) . ' 00:00:00';
        }
        if (!$end_date) {
            $end_date = date('Y-m-d', strtotime('last day of this month')) . ' 23:59:59';
        }

        $this->db
            ->select("product_name, product_code")
            ->select_sum('quantity')
            ->from('sale_items')
            ->join('sales', 'sales.id = sale_items.sale_id', 'left')
            ->where('date >=', $start_date)
            ->where('date <', $end_date)
            ->group_by('product_name, product_code')
            ->order_by('sum(quantity)', 'desc')
            ->limit(10);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function updateViewStyle($user_id = false, $style_view = false){
        if($user_id && $style_view){
            $this->db->update('users',array('style_view'=>$style_view),array('id'=>$user_id));
            return true;
        }
        return false;
    }
    
    public function updatecasStyle($user_id = false, $cus_style = false){
        if($user_id && $cus_style){
            $this->db->update('users',array('cus_style'=>$cus_style),array('id'=>$user_id));
            return true;
        }
        return false;
    }
    
    public function getSalesProfit($today = false, $last_month = false, $this_month = false){
        $where = "";
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        if($today){
            $where .= " AND DATE(date)=CURRENT_DATE()";
        }else if($last_month){
            $where .= " AND MONTH(date) = MONTH(CURRENT_DATE())-1 AND YEAR(date) = YEAR(CURRENT_DATE())";
        }else if($this_month){
            $where .= " AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
        }
        $q = $this->db->query("SELECT 
                                    SUM(grand_total) amount,
                                    SUM(IFNULL(payments.paid, 0 )) as paid,
                                    SUM(IFNULL(total_return,0)) as total_return,
                                    SUM(IFNULL(total_return_paid, 0)) as total_return_paid
                                FROM
                                    ".$this->db->dbprefix('sales')."
                                LEFT JOIN ( SELECT sum( abs( grand_total )) AS total_return, sum( abs( paid )) as total_return_paid, sale_id FROM ".$this->db->dbprefix('sales')." WHERE sale_status = 'returned' GROUP BY sale_id ) AS sale_return ON sale_return.sale_id = ".$this->db->dbprefix('sales').".id
                                LEFT JOIN ( SELECT IFNULL(SUM(amount),0) AS paid, IFNULL(SUM(discount),0) AS discount, sale_id FROM ".$this->db->dbprefix('payments')." GROUP BY sale_id ) AS payments ON payments.sale_id = ".$this->db->dbprefix('sales').".id
                                WHERE sale_status != 'draft' 
                                AND sale_status != 'returned' ".$where."");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchaseProfit($today = false, $last_month = false, $this_month = false){
        $where = "";
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        if($today){
            $where .= " AND DATE(date)=CURRENT_DATE()";
        }else if($last_month){
             $where .= " AND MONTH(date) = MONTH(CURRENT_DATE())-1 AND YEAR(date) = YEAR(CURRENT_DATE())";
        }else if($this_month){
            $where .= " AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
        }
        $q = $this->db->query("SELECT 
                                    SUM(grand_total) amount,
                                    SUM(IFNULL(payments.paid, 0 )) as paid,
                                    SUM(IFNULL(total_return,0)) as total_return,
                                    SUM(IFNULL(total_return_paid, 0)) as total_return_paid
                                FROM
                                    ".$this->db->dbprefix('purchases')."
                                LEFT JOIN ( SELECT sum( abs( grand_total )) AS total_return, sum( abs( paid )) as total_return_paid, purchase_id FROM ".$this->db->dbprefix('purchases')." WHERE status = 'returned' GROUP BY purchase_id ) AS purchase_return ON purchase_return.purchase_id = ".$this->db->dbprefix('purchases').".id
                                LEFT JOIN ( SELECT IFNULL(SUM(amount),0) AS paid, IFNULL(SUM(discount),0) AS discount, purchase_id FROM ".$this->db->dbprefix('payments')." GROUP BY purchase_id ) AS payments ON payments.purchase_id = ".$this->db->dbprefix('purchases').".id
                                WHERE status != 'draft' 
                                AND status != 'returned' ".$where."");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getInstallmentProfit($today = false, $yesterday = false, $this_month = false){
        $where = "";
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        if($today){
            $where .= " AND DATE(created_date)=CURRENT_DATE()";
        }else if($yesterday){
             $where .= " AND MONTH(date) = MONTH(CURRENT_DATE())-1 AND YEAR(date) = YEAR(CURRENT_DATE())";
        }else if($this_month){
            $where .= " AND MONTH(created_date) = MONTH(CURRENT_DATE()) AND YEAR(created_date) = YEAR(CURRENT_DATE())";
        }
        $q = $this->db->query("SELECT 
                                    SUM(principal_amount) amount,
                                    SUM(IFNULL(payments.paid, 0 )) as paid
                                FROM
                                    ".$this->db->dbprefix('installments')."
                              
                                LEFT JOIN ( SELECT IFNULL(SUM(amount),0) AS paid, IFNULL(SUM(discount),0) AS discount, installment_id FROM ".$this->db->dbprefix('payments')." GROUP BY installment_id ) AS payments ON payments.installment_id = ".$this->db->dbprefix('installments').".id
                                WHERE status != 'inactive' ".$where."");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

        public function getLateRepayments($today = false, $yesterday = false, $this_month = false){
        $where = "";
        $installment_alert_days = ($this->Settings->installment_alert_days?$this->Settings->installment_alert_days:0);
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        if($today){
            $where .= " AND DATE(deadline)=CURRENT_DATE()";
        }else if($yesterday){
             $where .= " AND MONTH(date) = MONTH(CURRENT_DATE())-1 AND YEAR(date) = YEAR(CURRENT_DATE())";
        }else if($this_month){
            $where .= " AND MONTH(deadline) = MONTH(CURRENT_DATE()) AND YEAR(deadline) = YEAR(CURRENT_DATE())";
        }
        $q = $this->db->query("SELECT 
                                    SUM(payment) amount,
                                    SUM(IFNULL(payments.paid, 0 )) as paid
                                    
                                FROM
                                    ".$this->db->dbprefix('installment_items')."
                              
                                LEFT JOIN ( SELECT IFNULL(SUM(amount),0) AS paid, IFNULL(SUM(discount),0) AS discount, installment_item_id FROM ".$this->db->dbprefix('payments')." GROUP BY installment_item_id ) AS payments ON payments.installment_item_id = ".$this->db->dbprefix('installment_items').".id
                                WHERE status != 'paid' 
                               
                                ".$where."");

        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    public function getInstallmentPayment($today = false, $yesterday = false, $this_month = false){
        $where = "";
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        if($today){
            $where .= " AND DATE(date)=CURRENT_DATE()";
        }else if($yesterday){
            $where .= " AND MONTH(date) = MONTH(CURRENT_DATE())-1 AND YEAR(date) = YEAR(CURRENT_DATE())";
        }else if($this_month){
            $where .= " AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
        }
        $q = $this->db->query("SELECT 

                                     SUM(amount) as amount 
                                FROM
                                    ".$this->db->dbprefix('payments')."
                                WHERE installment_id ".$where."");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getExpenses($today = false, $last_month = false, $this_month = false){
        $where = "";
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        if($today){
            $where .= " AND DATE(date)=CURRENT_DATE()";
        }else if($last_month){
            $where .= " AND MONTH(date) = MONTH(CURRENT_DATE())-1 AND YEAR(date) = YEAR(CURRENT_DATE())";
        }else if($this_month){
            $where .= " AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
        }
        $q = $this->db->query("SELECT 
                                     SUM(amount) as amount 
                                FROM
                                    ".$this->db->dbprefix('expenses')."
                                WHERE payment_status = 'paid' ".$where."");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllAvailables($today = false){
        $where = "";
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        $q = $this->db->query("SELECT 
                                     count(id) as TotalAvailable 
                                FROM
                                    ".$this->db->dbprefix('rental_rooms')."
                                WHERE availability != 'checked_in'
                                AND availability != 'reservation'");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllMaintenances($today = false){
        $where = "";
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        $q = $this->db->query("SELECT 
                                     count(id) as TotalMaintenance 
                                FROM
                                    ".$this->db->dbprefix('rental_rooms')."
                                WHERE housekeeping_status = 'maintenance'");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllOccuppys($today = false, $yesterday = false, $this_month = false){
        $where = "";
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        $q = $this->db->query("SELECT 
                                     count(id) as TotalOccuppys 
                                FROM
                                    ".$this->db->dbprefix('rentals')."
                                WHERE status != 'checked_in'");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCheckIns($today = false, $last_month = false, $this_month = false){
        $where = "";
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        if($today){
            $where .= " AND DATE(from_date)=CURRENT_DATE()";
        }else if($last_month){
             $where .= " AND MONTH(from_date) = MONTH(CURRENT_DATE())-1 AND YEAR(date) = YEAR(CURRENT_DATE())";
        }else if($this_month){
            $where .= " AND MONTH(from_date) = MONTH(CURRENT_DATE()) AND YEAR(from_date) = YEAR(CURRENT_DATE())";
        }
        $q = $this->db->query("SELECT 
                                     count(id) as TotalCheckIn 
                                FROM
                                    ".$this->db->dbprefix('rentals')."
                                WHERE status = 'checked_in' ".$where."");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCheckOuts($today = false, $last_month = false, $this_month = false){
        $where = "";
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        if($today){
            $where .= " AND DATE(to_date)=CURRENT_DATE()";
        }else if($last_month){
            $where .= " AND MONTH(to_date) = MONTH(CURRENT_DATE())-1 AND YEAR(date) = YEAR(CURRENT_DATE())";
        }else if($this_month){
            $where .= " AND MONTH(to_date) = MONTH(CURRENT_DATE()) AND YEAR(to_date) = YEAR(CURRENT_DATE())";
        }
        $q = $this->db->query("SELECT 
                                     count(id) as TotalCheckOut
                                FROM
                                    ".$this->db->dbprefix('rentals')."
                                WHERE status = 'checked_out' ".$where."");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getReservations($today = false, $last_month = false, $this_month = false){
        $where = "";
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where = " AND biller_id='".$this->session->userdata('biller_id')."'";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $where .= " AND warehouse_id IN ".$warehouse_ids;
        }
        if($today){
            $where .= " AND DATE(from_date)=CURRENT_DATE()";
        }else if($last_month){
            $where .= " AND MONTH(from_date) = MONTH(CURRENT_DATE())-1 AND YEAR(date) = YEAR(CURRENT_DATE())";
        }else if($this_month){
            $where .= " AND MONTH(from_date) = MONTH(CURRENT_DATE()) AND YEAR(from_date) = YEAR(CURRENT_DATE())";
        }
        $q = $this->db->query("SELECT 
                                     count(id) as TotalReservation
                                FROM
                                    ".$this->db->dbprefix('rentals')."
                                WHERE status = 'reservation' ".$where."");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }



}
