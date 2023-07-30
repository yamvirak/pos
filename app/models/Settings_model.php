<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function updateLogo($photo = false)
    {
        $logo = array('logo' => $photo);
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function updateLoginLogo($photo = false)
    {
        $logo = array('logo2' => $photo);
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function getLocationsProvince($type = null)
    {
        $q = $this->db->where("type", $type)->where("province_id")->get("locations");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;             
    }

    public function getSettings()
    {
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getDateFormats()
    {
        $q = $this->db->get('date_format');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function updateSetting($data = false,$stockmoves = false)
    {
        $products =  $data['products'];
        unset($data['products']);
        $this->db->where('setting_id', '1');
        if ($this->db->update('settings', $data)) {
            if($stockmoves){
                $this->db->insert_batch('stockmoves',$stockmoves);
            }else if($products){
                foreach($products as $product){
                    $costUpdate = array('real_unit_cost' => $product->cost);
                    $this->db->where('product_id',$product->id);
                    $this->db->update('stockmoves',$costUpdate);
                }
            }
            return true;
        }
        return false;
    }

    public function addTaxRate($data = false)
    {
        if ($this->db->insert('tax_rates', $data)) {
            return true;
        }
        return false;
    }

    public function updateTaxRate($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('tax_rates', $data)) {
            return true;
        }
        return false;
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
        return FALSE;
    }

    public function getTaxRateByID($id = false)
    {
        $q = $this->db->get_where('tax_rates', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addWarehouse($data = false)
    {
        if ($this->db->insert('warehouses', $data)) {
            return true;
        }
        return false;
    }

    public function updateWarehouse($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('warehouses', $data)) {
            return true;
        }
        return false;
    }

    public function getAllWarehouses()
    {
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseByID($id = false)
    {
        $q = $this->db->get_where('warehouses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteTaxRate($id = false)
    {
        if ($this->db->delete('tax_rates', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteInvoiceType($id = false)
    {
        if ($this->db->delete('invoice_types', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteWarehouse($id = false)
    {
        if ($this->db->delete('warehouses', array('id' => $id)) && $this->db->delete('warehouses_products', array('warehouse_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function addPaymentTerm($data = false)
    {
        if ($this->db->insert('payment_terms', $data)) {
            return true;
        }
        return false;
    }

    public function deletePaymentTerm($id = false)
    {
        if ($this->db->delete('payment_terms', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function updatePaymentTerm($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('payment_terms', $data)) {
            return true;
        }
        return false;
    }

    public function getPaymentTermById($id = false)
    {
        $q = $this->db->get_where('payment_terms', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addCustomerGroup($data = false)
    {
        if ($this->db->insert('customer_groups', $data)) {
            return true;
        }
        return false;
    }


    public function updateCustomerGroup($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('customer_groups', $data)) {
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

    public function getCustomerGroupByID($id = false)
    {
        $q = $this->db->get_where('customer_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteCustomerGroup($id = false)
    {
        if ($this->db->delete('customer_groups', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getGroups()
    {
        $this->db->where('id >', 4);
        $q = $this->db->get('groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getGroupByID($id = false)
    {
        $q = $this->db->get_where('groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getGroupPermissions($id = false)
    {
        $q = $this->db->get_where('permissions', array('group_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function GroupPermissions($id = false)
    {
        $q = $this->db->get_where('permissions', array('group_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return FALSE;
    }

    public function updatePermissions($id = false, $data = array())
    {
        if ($this->db->update('permissions', $data, array('group_id' => $id)) && $this->db->update('users', array('show_price' => $data['products-price'], 'show_cost' => $data['products-cost']), array('group_id' => $id))) {
            $this->db->update('users',array('show_cost'=>$data['products-cost'],'show_price'=>$data['products-price']),array('group_id'=>$id));
            return true;
        }
        return false;
    }

    public function addGroup($data = false)
    {
        if ($this->db->insert("groups", $data)) {
            $gid = $this->db->insert_id();
            $this->db->insert('permissions', array('group_id' => $gid));
            return $gid;
        }
        return false;
    }

    public function updateGroup($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update("groups", $data)) {
            return true;
        }
        return false;
    }


    public function getAllCurrencies()
    {
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCurrencyByID($id = false)
    {
        $q = $this->db->get_where('currencies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addCurrency($data = false)
    {
        if ($this->db->insert("currencies", $data)) {
            if($this->config->item('product_currency')==true){
                $new_rate = $data['rate'];
                $new_code = $data['code'];
                $q = $this->db->where("currency_code!=",'USD')->where("currency_code", $new_code)->get("products");
                if($q->num_rows() > 0){
                    foreach($q->result() as $row){
                        $old_price = $row->price;
                        $old_currency_rate = $row->currency_rate;
                        $old_currency_price = ($row->price * $row->currency_rate);
                        $new_price = ($old_currency_price / $new_rate);
                        $this->db->where("id", $row->id)->update("products", array("price"=>$new_price, "currency_rate"=> $new_rate));
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function updateCurrency($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update("currencies", $data)) {
            if($this->config->item('product_currency')==true){
                $new_rate = $data['rate'];
                $new_code = $data['code'];
                $q = $this->db->where("currency_code!=",'USD')->where("currency_code", $new_code)->get("products");
                if($q->num_rows() > 0){
                    foreach($q->result() as $row){
                        $old_price = $row->price;
                        $old_currency_rate = $row->currency_rate;
                        $old_currency_price = ($row->price * $row->currency_rate);
                        $new_price = ($old_currency_price / $new_rate);
                        $this->db->where("id", $row->id)->update("products", array("price"=>$new_price, "currency_rate"=> $new_rate));
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function deleteCurrency($id = false)
    {
        if ($this->db->delete("currencies", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getParentProjects()
    {
        $this->db->where('parent_id', NULL)->or_where('parent_id', 0);
        $q = $this->db->get("projects");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getParentCategories()
    {
        $this->db->where('parent_id', NULL)->or_where('parent_id', 0);
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCategoryByID($id = false)
    {
        $q = $this->db->get_where("categories", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCategoryByCode($code = false)
    {
        $q = $this->db->get_where('categories', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addCategory($data = false, $category_projects = false)
    {
        if ($this->db->insert("categories", $data)) {
            $category_id = $this->db->insert_id();
            if($category_projects){
                foreach($category_projects as $category_project){
                    $category_project['category_id'] = $category_id;
                    $this->db->insert("category_projects",$category_project);
                }
            }
            return true;
        }
        return false;
    }

    public function addCategories($data = false)
    {
        if ($this->db->insert_batch('categories', $data)) {
            return true;
        }
        return false;
    }

    public function AddLocationss($data)
    {
        if ($this->db->insert_batch('locationss', $data)) {
            return true;
        }
        return false;
    }

    public function updateCategory($id =false, $data = array(), $category_projects = false)
    {
        if ($this->db->update("categories", $data, array('id' => $id))) {
            $this->db->delete("category_projects",array("category_id"=>$id));
            if($category_projects){
                foreach($category_projects as $category_project){
                    $this->db->insert("category_projects",$category_project);
                }
            }
            return true;
        }
        return false;
    }

    public function deleteCategory($id = false)
    {
        if ($this->db->delete("categories", array('id' => $id))) {
            $this->db->delete("category_projects",array("category_id"=>$id));
            return true;
        }
        return FALSE;
    }

    public function getPaypalSettings()
    {
        $q = $this->db->get('paypal');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updatePaypal($data = false)
    {
        $this->db->where('id', '1');
        if ($this->db->update('paypal', $data)) {
            return true;
        }
        return FALSE;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get('skrill');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateSkrill($data = false)
    {
        $this->db->where('id', '1');
        if ($this->db->update('skrill', $data)) {
            return true;
        }
        return FALSE;
    }

    public function checkGroupUsers($id = false)
    {
        $q = $this->db->get_where("users", array('group_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteGroup($id = false)
    {
        if ($this->db->delete('groups', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function addVariant($data = false)
    {
        if ($this->db->insert('variants', $data)) {
            return true;
        }
        return false;
    }

    public function updateVariant($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('variants', $data)) {
            return true;
        }
        return false;
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

    public function getVariantByID($id = false)
    {
        $q = $this->db->get_where('variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteVariant($id = false)
    {
        if ($this->db->delete('variants', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getAllExpenseCategories()
    {
        $q = $this->db->get_where('expense_categories', array("parent_id"=>0));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getExpenseCategoryByParentID($id = false)
    {
        $q = $this->db->get_where("expense_categories", array('parent_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getExpenseCategoryByCode($code = false)
    {
        $q = $this->db->get_where("expense_categories", array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addExpenseCategory($data = false)
    {
        if ($this->db->insert("expense_categories", $data)) {
            return true;
        }
        return false;
    }

    public function addExpenseCategories($data = false)
    {
        if ($this->db->insert_batch("expense_categories", $data)) {
            return true;
        }
        return false;
    }

    public function updateExpenseCategory($id = false, $data = array())
    {
        if ($this->db->update("expense_categories", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function hasExpenseCategoryRecord($id = false)
    {
        $this->db->where('category_id', $id);
        return $this->db->count_all_results('expense_items');
    }

    public function deleteExpenseCategory($id = false)
    {
        if ($this->db->delete("expense_categories", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function addUnit($data = false)
    {
        if ($this->db->insert("units", $data)) {
            return true;
        }
        return false;
    }

    public function updateUnit($id = false, $data = array())
    {
        if ($this->db->update("units", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteUnit($id = false)
    {
        if ($this->db->delete("units", array('id' => $id))) {
            $this->db->delete("units", array('base_unit' => $id));
            return true;
        }
        return FALSE;
    }

    public function addPriceGroup($data = false)
    {
        if ($this->db->insert('price_groups', $data)) {
            return true;
        }
        return false;
    }



    public function updatePriceGroup($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('price_groups', $data)) {
            return true;
        }
        return false;
    }

    public function getAllPriceGroups()
    {
        $q = $this->db->get('price_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPriceGroupByID($id = false)
    {
        $q = $this->db->get_where('price_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    public function deletePriceGroup($id = false)
    {
        if ($this->db->delete('price_groups', array('id' => $id)) && $this->db->delete('product_prices', array('price_group_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function setProductPriceForPriceGroup($product_id = false, $group_id = false, $price = false)
    {
        if ($this->getGroupPrice($group_id, $product_id)) {
            if ($this->db->update('product_prices', array('price' => $price), array('price_group_id' => $group_id, 'product_id' => $product_id))) {
                return true;
            }
        } else {
            if ($this->db->insert('product_prices', array('price' => $price, 'price_group_id' => $group_id, 'product_id' => $product_id))) {
                return true;
            }
        }
        return FALSE;
    }

    public function setProductPriceForCustomer($product_id = false, $customer_id = false, $price = false)
    {
        if ($this->getCustomerPrice($customer_id, $product_id)) {
            if ($this->db->update('customer_product_prices', array('price' => $price), array('customer_id' => $customer_id, 'product_id' => $product_id))) {
                return true;
            }
        } else {
            if ($this->db->insert('customer_product_prices', array('price' => $price, 'customer_id' => $customer_id, 'product_id' => $product_id))) {
                return true;
            }
        }
        return FALSE;
    }

    public function getCustomerPrice($customer_id = false, $product_id = false)
    {
        $q = $this->db->get_where('customer_product_prices', array('customer_id' => $customer_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getGroupPrice($group_id = false, $product_id = false)
    {
        $q = $this->db->get_where('product_prices', array('price_group_id' => $group_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductGroupPriceByPID($product_id = false, $group_id = false)
    {
        $pg = "(SELECT {$this->db->dbprefix('product_prices')}.price as price, {$this->db->dbprefix('product_prices')}.product_id as product_id FROM {$this->db->dbprefix('product_prices')} WHERE {$this->db->dbprefix('product_prices')}.product_id = {$product_id} AND {$this->db->dbprefix('product_prices')}.price_group_id = {$group_id}) GP";

        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, GP.price", FALSE)
        // ->join('products', 'products.id=product_prices.product_id', 'left')
        ->join($pg, 'GP.product_id=products.id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCustomerProductPriceByPID($product_id = false, $customer_id = false)
    {
        $pg = "(SELECT {$this->db->dbprefix('customer_product_prices')}.price as price, {$this->db->dbprefix('customer_product_prices')}.product_id as product_id FROM {$this->db->dbprefix('customer_product_prices')} WHERE {$this->db->dbprefix('customer_product_prices')}.product_id = {$product_id} AND {$this->db->dbprefix('customer_product_prices')}.customer_id = {$customer_id}) GP";

        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name,{$this->db->dbprefix('products')}.price as product_price, GP.price", FALSE)
        ->join($pg, 'GP.product_id=products.id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    public function updateGroupPrices($data = array())
    {
        foreach ($data as $row) {
            if ($this->getGroupPrice($row['price_group_id'], $row['product_id'])) {
                $this->db->update('product_prices', array('price' => $row['price']), array('product_id' => $row['product_id'], 'price_group_id' => $row['price_group_id']));
            } else {
                $this->db->insert('product_prices', $row);
            }
        }
        return true;
    }

    public function deleteProductGroupPrice($product_id = false, $group_id = false)
    {
        if ($this->db->delete('product_prices', array('price_group_id' => $group_id, 'product_id' => $product_id))) {
            return TRUE;
        }
        return FALSE;
    }

    public function deleteCustomerProductPrice($product_id = false, $customer_id = false)
    {
        if ($this->db->delete('customer_product_prices', array('customer_id' => $customer_id, 'product_id' => $product_id))) {
            return TRUE;
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

    public function addBrand($data = false)
    {
        if ($this->db->insert("brands", $data)) {
            return true;
        }
        return false;
    }

    public function addBrands($data = false)
    {
        if ($this->db->insert_batch('brands', $data)) {
            return true;
        }
        return false;
    }

    public function updateBrand($id = false, $data = array())
    {
        if ($this->db->update("brands", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteBrand($id = false)
    {
        if ($this->db->delete("brands", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    /****************/

    public function deleteTable($id = false)
    {
        if ($this->db->delete("suspended_tables", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function updateTable($id = false, $data = array())
    {
        if ($this->db->update("suspended_tables", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addTable($data = false)
    {
        if ($this->db->insert("suspended_tables", $data)) {
            return true;
        }
        return false;
    }

    public function getTableByID($id = false)
    {
        $q = $this->db->get_where('suspended_tables', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    /****************/

    public function getAllTypes()
    {
        $q = $this->db->get('category_types');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTypeByID($id = false)
    {
        $q = $this->db->get_where('category_types', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllFloors()
    {
        $q = $this->db->get('suspended_floors');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
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

    /*************/

    public function deleteFloor($id = false)
    {
        if ($this->db->delete("suspended_floors", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function updateFloor($id = false, $data = array())
    {
        if ($this->db->update("suspended_floors", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addFloor($data = false)
    {
        if ($this->db->insert("suspended_floors", $data)) {
            return true;
        }
        return false;
    }

    public function getFloorByID($id = false)
    {
        $q = $this->db->get_where('suspended_floors', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    //

    public function getDriverByID($id = false)
    {
        $q = $this->db->get_where('drivers', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addDriver($data = false)
    {
        if ($this->db->insert("drivers", $data)) {
            return true;
        }
        return false;
    }

    public function updateDriver($id = false, $data = array())
    {
        if ($this->db->update("drivers", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteDriver($id = false)
    {
        if ($this->db->delete("drivers", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getProjectByBillerID($id = false)
    {
        if($id){
            $this->db->where('biller_id',$id);
        }
        $q = $this->db->get('projects');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProjectByID($id = false)
    {
        $q = $this->db->get_where('projects', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addProject($data = false)
    {
        if ($this->db->insert("projects", $data)) {
            return true;
        }
        return false;
    }

    public function updateProject($id = false, $data = array())
    {
        if ($this->db->update("projects", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteProject($id = false)
    {
        if ($this->db->delete("projects", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function addLoginTime($data = array())
    {
        if ($this->db->insert_batch("login_permissions", $data)) {
            return true;
        }
        return false;
    }

    public function updateLoginTime($id = false, $data = array())
    {
        if ($this->db->where("id",$id)->update("login_permissions", $data)) {
            return true;
        }
        return false;
    }

    public function getLoginTimeById($id = false)
    {
        $q = $this->db->get_where('login_permissions', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getLoginTimeByDayGroup($day = false, $group = false)
    {
        $q = $this->db->get_where('login_permissions', array('day' => $day, "group_id" => $group), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteLoginTime($id = false)
    {
        if ($this->db->delete("login_permissions", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getInstallmentTermByID($id = false)
    {
        $q = $this->db->get_where('terms', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addInstallmentTerm($data = false)
    {
        if ($this->db->insert("terms", $data)) {
            return true;
        }
        return false;
    }

    public function updateInstallmentTerm($id = false, $data = array())
    {
        if ($this->db->update("terms", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteInstallmentTerm($id = false)
    {
        if ($this->db->delete("terms", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }


    public function getFrequencyByID($id = false)
    {
        $q = $this->db->get_where('frequency', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addFrequency($data = false)
    {
        if ($this->db->insert("frequency", $data)) {
            return true;
        }
        return false;
    }

    public function updateFrequency($id = false, $data = array())
    {
        if ($this->db->update("frequency", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteFrequency($id = false)
    {
        if ($this->db->delete("frequency", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getProductsByCategoryId($category_id = false)
    {
        $q = $this->db->get_where('products', array('category_id' => $category_id), 1);
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductsBySubCategoryId($subcategory_id = false)
    {
        $q = $this->db->get_where('products', array('subcategory_id' => $subcategory_id), 1);
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function updateCategoryProduct($id = false, $code = false, $name = false, $price = false)
    {
        if ($this->db->update('products', array('code' => $code, 'name' => $name, 'price' => $price), array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getVehicleByID($id = false)
    {
        $q = $this->db->get_where('vehicles', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addVehicle($data = false)
    {
        if ($this->db->insert("vehicles", $data)) {
            return true;
        }
        return false;
    }

    public function updateVehicle($id = false, $data = array())
    {
        if ($this->db->update("vehicles", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteVehicle($id = false)
    {
        if ($this->db->delete("vehicles", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getAccountByCode($code = false)
    {
        $q = $this->db->get_where('acc_chart', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAccountByParent($parent_code = false)
    {
        $q = $this->db->get_where('acc_chart', array('parent_code' => $parent_code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getHolidayByID($id = false)
    {
        $q = $this->db->where("id", $id)->get("holiday");
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }

    public function deleteHoliday($id = false)
    {
        if($this->db->where("id", $id)->delete("holiday")){
            return true;
        }
        return false;
    }

    public function addHoliday($data = array())
    {
        if($this->db->insert("holiday", $data)){
            return true;
        }
        return false;

    }

    public function updateHoliday($id = false, $data = array())
    {
        if($this->db->where("id",$id)->update("holiday", $data)){
            return true;
        }
        return false;
    }

    public function clearSystem($data = false)
    {
        if($data){

            if($data['list_sales']==1 || $data['pos']==1 || $data['list_returns']==1 || $data['list_gift_cards']==1){
                $this->load->model('sales_model');
            }
            if($data['list_purchases']==1 || $data['list_receives']==1 || $data['purchase_returns']==1 || $data['list_expenses']==1){
                $this->load->model('purchases_model');
            }
            if($data['list_pawns']==1 || $data['list_pawn_returns']==1 || $data['list_pawn_purchases']==1){
                $this->load->model('pawns_model');
            }

            if($data['list_billers']==1){
                $billers = $this->companies_model->getAllBillerCompanies();
                if($billers){
                    foreach($billers as $biller){
                        $this->companies_model->deleteBiller($biller->id,'1');
                    }
                }
            }


            if($data['list_projects']==1){
                $query = $this->db->get('projects');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row)
                    {
                        $this->deleteProject($row->id);
                    }
                }
            }
            if($data['warehouses']==1){
                $query = $this->db->get('warehouses');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row)
                    {
                        $this->deleteWarehouse($row->id);
                    }
                }
            }
            if($data['expense_categories']==1){
                $query = $this->db->get('expense_categories');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row)
                    {
                        $this->deleteExpenseCategory($row->id);
                    }
                }
            }
            if($data['categories']==1){
                $query = $this->db->get('categories');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deleteCategory($row->id);
                    }
                }
            }
            if($data['load_term']==1){
                $query = $this->db->get('terms');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deleteInstallmentTerm($row->id);
                    }
                }
            }
            if($data['frequencies']==1){
                $query = $this->db->get('frequency');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deleteFrequency($row->id);
                    }
                }
            }
            if($data['units']==1){
                $query = $this->db->get('units');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deleteUnit($row->id);
                    }
                }
            }

            if($data['brands']==1){
                $query = $this->db->get('brands');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deleteBrand($row->id);
                    }
                }
            }

            if($data['boms']==1){
                $query = $this->db->get('boms');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->products_model->deleteBom($row->id);
                    }
                }
            }

            if($data['customer_group']==1){
                $query = $this->db->get('customer_groups');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deleteCustomerGroup($row->id);
                    }
                }
            }

            if($data['price_group']==1){
                $query = $this->db->get('price_groups');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deletePriceGroup($row->id);
                    }
                }
            }
            if($data['payment_terms']==1){
                $query = $this->db->get('payment_terms');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deletePaymentTerm($row->id);
                    }
                }
            }
            if($data['currencies']==1){
                $query = $this->db->get('currencies');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deleteCurrency($row->id);
                    }
                }
            }
            if($data['customer_opening_balances']==1){
                $query = $this->db->get_where('sales',array('suspend_note'=>'CUSTOMER_OPENING_BALANCE'));
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deleteCustomerOpeningBalance($row->id);
                    }
                }
            }

            if($data['supplier_opening_balances']==1){
                $query = $this->db->get_where('purchases',array('status'=>'draft'));
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deleteSupplierOpeningBalance($row->id);
                    }
                }
            }
            if($data['tax_rates']==1){
                $query = $this->db->get('tax_rates');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deleteTaxRate($row->id);
                    }
                }
            }
            if($data['user_groups']==1){
                $groups = $this->getGroups();
                if($groups){
                    foreach($groups as $row){
                        $this->deleteGroup($row->id);
                    }
                }
            }
            if($data['calendar_lists']==1){
                $this->load->model('calendar_model');
                $query = $this->db->get('calendar');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->calendar_model->deleteCalendar($row->id);
                    }
                }
            }
            if($data['list_products']==1){
                $query = $this->db->get('products');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->products_model->deleteProduct($row->id);
                    }
                }
            }
            if($data['using_stocks']==1){
                $query = $this->db->get('using_stocks');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->products_model->deleteUsingStock($row->id);
                    }
                }
            }
            if($data['stock_counts']==1){
                $this->db->delete("stock_counts",array('id >' => 0));
                $this->db->delete("stock_count_items",array('id >' => 0));
            }

            if($data['quantity_adjustments']==1){
                $query = $this->db->get('adjustments');
                foreach($query->result() as $row){
                    $this->products_model->deleteAdjustment($row->id);
                }
            }
            if($data['cost_adjustments']==1){
                $this->db->delete("cost_adjustments",array('id >' => 0));
                $this->db->delete("cost_adjustment_items",array('id >' => 0));
                $this->db->delete("stockmoves",array('transaction'=>'CostAdjustment'));
                $this->db->delete("acc_tran",array('transaction'=>'CostAdjustment'));
            }

            if($data['converts']==1){
                $query = $this->db->get('converts');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->products_model->deleteConvert($row->id);
                    }
                }
            }
            if($data['list_transfers']==1){
                $this->load->model('transfers_model');
                $query = $this->db->get('transfers');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->transfers_model->deleteTransfer($row->id);
                    }
                }
            }
            if($data['list_quotations']==1){
                $this->load->model('quotes_model');
                $query = $this->db->get('quotes');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->quotes_model->deleteQuote($row->id);
                    }
                }
            }
            if($data['list_sale_orders']==1){
                $this->load->model('sale_order_model');
                $query = $this->db->get('sale_orders');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->sale_order_model->deleteSaleOrder($row->id);
                    }
                }
            }

            if($data['list_sales']==1){
                $this->db->where('pos',0);
                $query = $this->db->get('sales');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->sales_model->deleteSale($row->id);
                    }
                }
            }
            if($data['pos']==1){
                $this->db->where('pos',1);
                $query = $this->db->get('sales');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->sales_model->deleteSale($row->id);
                    }
                }
            }

            if($data['list_returns']==1){
                $this->db->select('*');
                $this->db->where('sale_status','returned');
                $query = $this->db->get('sales');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->sales_model->deleteSale($row->id);
                    }
                }
            }

            if($data['list_gift_cards']==1){
                $query = $this->db->get('gift_cards');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->sales_model->deleteGiftCard($row->id);
                    }
                }
            }

            if($data['deliveries']==1){
                $this->load->model('deliveries_model');
                $query = $this->db->get('deliveries');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->deliveries_model->deleteDelivery($row->id);
                    }
                }
            }
            if($data['list_purchase_requests']==1){
                $this->load->model('purchase_request_model');
                $query = $this->db->get('purchase_requests');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->purchase_request_model->deletePurchaseRequest($row->id);
                    }
                }
            }
            if($data['list_purchase_orders']==1){
                $this->load->model('purchase_order_model');
                $query = $this->db->get('purchase_orders');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->purchase_order_model->deletePurchaseOrder($row->id);
                    }
                }
            }

            if($data['list_purchases']==1){
                $query = $this->db->get('purchases');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->purchases_model->deletePurchase($row->id);
                    }
                }
            }
            if($data['list_receives']==1){
                $query = $this->db->get('purchase_items');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->purchases_model->deleteReceive($row->id);
                    }
                }
            }
            if($data['purchase_returns']==1){
                $this->db->select('*');
                $this->db->where('status','returned');
                $query = $this->db->get('purchases');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->purchases_model->deletePurchase($row->return_id);
                    }
                }
            }

            if($data['list_expenses']==1){
                $query = $this->db->get('expenses');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->purchases_model->deleteExpense($row->id);
                    }
                }
            }


            if($data['list_pawns']==1){
                $query = $this->db->get('pawns');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->pawns_model->deletePawn($row->id);
                    }
                }
            }
            if($data['list_pawn_returns']==1){
                $query = $this->db->get('pawn_returns');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->pawns_model->deletePawnReturn($row->id);
                    }
                }
            }
            if($data['list_pawn_purchases']==1){
                $query = $this->db->get('pawn_purchases');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->pawns_model->deletePawnPurchase($row->id);
                    }
                }
            }


            if($data['list_customers']==1){
                $query = $this->db->get('companies');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->companies_model->deleteCustomer($row->id);
                    }
                }
            }
            if($data['list_suppliers']==1){
                $query = $this->db->get('companies');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->companies_model->deleteSupplier($row->id);
                    }
                }
            }
            if($data['list_enter_journals']==1){
                $this->load->model('accountings_model');
                $query = $this->db->get('acc_enter_journals');
                if($query->num_rows () > 0){
                    foreach($query->result() as $row){
                        $this->accountings_model->deleteEnterJournal($row->id);
                    }
                }
            }
            if($data['list_chart_accounts']==1){
                $this->db->delete('acc_chart',array('id >' => 0));
                $this->db->delete('acc_tran',array('id >' => 0));
            }
            return true;
        }
        return false;
    }

    public function addCustomerOpeningBalance($data = NULL, $accTrans= array())
    {
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['transaction_id'] = $sale_id;
                    $this->db->insert('acc_tran', $accTran);
                }
            }
            return true;
        }
        return false;
    }

    public function addCustomerOpeningBalanceExcel($data = false)
    {
        if($data){
            foreach($data as $row){
                //=======acounting=========//
                if($this->Settings->accounting == 1){
                    $openAcc = $this->site->getAccountSettingByBiller($row['biller_id']);
                    $row['ar_account'] = $openAcc->ar_acc;
                    $accTrans = false;
                    $sale_id = $this->db->insert_id();
                    $accTrans[] = array(
                        'transaction' => 'CustomerOpening',
                        'transaction_id' => $sale_id,
                        'transaction_date' => $row['date'],
                        'reference' => $row['reference_no'],
                        'account' => $openAcc->ar_acc,
                        'amount' => $row['grand_total'],
                        'narrative' => 'Customer Opening Balance '.$row['customer'],
                        'biller_id' => $row['biller_id'],
                        'project_id' => $row['project_id'],
                        'user_id' => $row['created_by'],
                        'customer_id' => $row['customer_id'],
                    );

                    $accTrans[] = array(
                        'transaction' => 'CustomerOpening',
                        'transaction_id' => $sale_id,
                        'transaction_date' => $row['date'],
                        'reference' => $row['reference_no'],
                        'account' => $row['account_code'],
                        'amount' => -$row['grand_total'],
                        'narrative' => 'Customer Opening Balance '.$row['customer'],
                        'biller_id' => $row['biller_id'],
                        'project_id' => $row['project_id'],
                        'user_id' => $row['created_by'],
                        'customer_id' => $row['customer_id'],
                    );
                    $this->db->insert_batch('acc_tran', $accTrans);

                }
                $this->db->insert('sales', $row);
                //============end accounting=======//
            }
            return  true;
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

    public function deleteCustomerOpeningBalance($id = NULL)
    {
        if ($this->db->delete('sales', array('id' => $id))) {
            $payments = $this->getPaymentsBySale($id);
            $this->site->deleteAccTran('CustomerOpening',$id);
            $this->db->delete('payments', array('sale_id' => $id));
            if($payments){
                foreach($payments as $payment){
                    $this->site->deleteAccTran('Payment',$payment->id);
                }
            }
            return true;
        }
        return FALSE;
    }

    public function getCustomerOpeningBalanceByID($id = NULL)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateCustomerOpeningBalance($id = NULL, $data = array(), $accTrans = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('sales', $data)) {
            $this->site->deleteAccTran('CustomerOpening',$id);
            if($accTrans){
                $this->db->insert_batch('acc_tran', $accTrans);
            }
            return true;
        }
        return false;
    }

    public function getPurchasePayments($purchase_id = false)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function addSupplierOpeningBalance($data = NULL, $accTrans= array())
    {
        if ($this->db->insert('purchases', $data)) {
            $purchase_id = $this->db->insert_id();
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['transaction_id'] = $purchase_id;
                    $this->db->insert('acc_tran', $accTran);
                }
            }
            return true;
        }
        return false;
    }


    public function addSupplierOpeningBalanceExcel($data = false)
    {
        if($data){
            foreach($data as $row){
                //=======acounting=========//
                if($this->Settings->accounting == 1){
                    $accTrans = false;
                    $purchase_id = $this->db->insert_id();
                    $openAcc = $this->site->getAccountSettingByBiller($row['biller_id']);
                    $row['ap_account'] = $openAcc->ap_acc;
                    $accTrans[] = array(
                        'transaction' => 'SupplierOpening',
                        'transaction_id' => $purchase_id,
                        'transaction_date' => $row['date'],
                        'reference' => $row['reference_no'],
                        'account' => $openAcc->ap_acc,
                        'amount' => -$row['grand_total'],
                        'narrative' => 'Supplier Opening Balance '.$row['supplier'],
                        'biller_id' => $row['biller_id'],
                        'project_id' => $row['project_id'],
                        'user_id' => $row['created_by'],
                        'supplier_id' => $row['supplier_id'],
                    );

                    $accTrans[] = array(
                        'transaction' => 'SupplierOpening',
                        'transaction_id' => $purchase_id,
                        'transaction_date' => $row['date'],
                        'reference' => $row['reference_no'],
                        'account' => $row['account_code'],
                        'amount' => $row['grand_total'],
                        'narrative' => 'Supplier Opening Balance '.$row['supplier'],
                        'biller_id' => $row['biller_id'],
                        'project_id' => $row['project_id'],
                        'user_id' => $row['created_by'],
                        'supplier_id' => $row['supplier_id'],
                    );
                    $this->db->insert_batch('acc_tran', $accTrans);

                }
                //============end accounting=======//
                $this->db->insert('purchases', $row);
            }
            return  true;
        }
        return false;
    }

    public function deleteSupplierOpeningBalance($id = NULL)
    {
        if ($this->db->delete('purchases', array('id' => $id))) {
            $payments = $this->getPurchasePayments($id);
            $this->site->deleteAccTran('SupplierOpening',$id);
            $this->db->delete('payments', array('purchase_id' => $id));
            if($payments){
                foreach($payments as $payment){
                    $this->site->deleteAccTran('Payment',$payment->id);
                }
            }
            return true;
        }
        return FALSE;
    }

    public function getSupplierOpeningBalanceByID($id = NULL)
    {
        $q = $this->db->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateSupplierOpeningBalance($id = NULL, $data = array(), $accTrans = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('purchases', $data)) {
            $this->site->deleteAccTran('SupplierOpening',$id);
            if($accTrans){
                $this->db->insert_batch('acc_tran', $accTrans);
            }
            return true;
        }
        return false;
    }

    public function getVehicleByPlateNo($plate_no = false)
    {
        $q = $this->db->where("plate_no", $plate_no)->get("vehicles");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }

    public function addVehicles($data = false)
    {
        if($this->db->insert_batch("vehicles", $data)){
            return true;
        }
        return false;
    }

    public function getProductByID($id = false)
    {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getInstallmentTermByDescription($description = false)
    {
        $q = $this->db->where("description", $description)->get("terms");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }

    public function addInstallmentTermes($data = false)
    {
        if($this->db->insert_batch("terms", $data)){
            return true;
        }
        return false;
    }

    public function deleteTank($id = false)
    {
        if($this->db->where("id", $id)->delete("tanks")){
            $this->db->where("tank_id", $id)->delete("tank_nozzles");
            $this->db->delete("tank_nozzle_salesmans",array("tank_id"=>$id));
            return true;
        }
        return false;
    }

    public function updateTank($id = false, $data = array())
    {
        if ($this->db->update("tanks", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addTank($data = false)
    {
        if ($this->db->insert("tanks", $data)) {
            return true;
        }
        return false;
    }

    public function getTankByID($id = false)
    {
        $q = $this->db->get_where('tanks', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addTables($data = false)
    {
        if($this->db->insert_batch("suspended_tables", $data)){
            return true;
        }
        return false;
    }

    public function addFrequencies($data = false)
    {
        if($this->db->insert_batch("frequency", $data)){
            return true;
        }
        return false;
    }

    public function getTableByName($name = false)
    {
        $q = $this->db->where('name',$name)->get('suspended_tables');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getWarehouseProduct($warehouse_id = false, $product_id = false)
    {
        $q = $this->db->get_where('warehouses_products',array('warehouse_id'=>$warehouse_id,'product_id'=>$product_id));
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }

    public function updateProductWarehouseAlertQty($product_id = false, $warehouse_id = false, $alert_qty = false)
    {
        if ($this->getWarehouseProduct($warehouse_id, $product_id)) {
            if ($this->db->update('warehouses_products', array('alert_quantity' => $alert_qty), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
                return true;
            }
        } else {
            if ($this->db->insert('warehouses_products', array('alert_quantity' => $alert_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
                return true;
            }
        }
        return FALSE;
    }

    public function getProductWarehouseAlert($product_id = false, $warehouse_id = false)
    {
        $pg = "( SELECT {$this->db->dbprefix('warehouses_products')}.product_id as product_id, {$this->db->dbprefix('warehouses_products')}.alert_quantity as alert_quantity  FROM {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id} ) PP";

        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name,{$this->db->dbprefix('products')}.alert_quantity as product_alert, IFNULL(PP.alert_quantity,0) as warehouse_alert", FALSE)
        ->join($pg, 'PP.product_id=products.id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addNozzleStartNo($data = false,$salesmans = false)
    {
       if ($this->db->insert("tank_nozzles", $data)) {
            $nozzle_id = $this->db->insert_id();
            if($salesmans){
                foreach($salesmans as $salesman){
                    $salesman['nozzle_id'] = $nozzle_id;
                    $this->db->insert("tank_nozzle_salesmans",$salesman);
                }
            }
            return true;
        }
        return false;
    }

    public function updateNozzleStartNo($id = false, $data = false, $salesman = false)
    {
       if ($this->db->where("id", $id)->update("tank_nozzles", $data)) {
            $this->db->delete("tank_nozzle_salesmans",array("tank_id"=>$data["tank_id"],"nozzle_id"=>$id));
            if($salesman){
                $this->db->insert_batch("tank_nozzle_salesmans",$salesman);
            }
            return true;
        }
        return false;
    }

    public function getNozzleStartNoByID($id = false)
    {
        $q = $this->db->where("id", $id)->get("cas_tank_nozzles");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
    }

    public function deleteNozzleStartNo($id =false)
    {
        if($this->db->where("id",$id)->delete("tank_nozzles")){
            $this->db->delete("tank_nozzle_salesmans",array("nozzle_id"=>$id));
            return true;
        }
        return false;
    }

    public function addProductPromotion($data = false)
    {
        if ($this->db->insert('product_promotions', $data)) {
            return true;
        }
        return false;
    }

    public function updateProductPromotion($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('product_promotions', $data)) {
            return true;
        }
        return false;
    }

    public function deleteProductPromotion($id = false)
    {
        if ($this->db->delete('product_promotions', array('id' => $id))) {
            $this->db->delete('product_promotion_items', array('promotion_id' => $id));
            return true;
        }
        return FALSE;
    }

    public function getProductPromationByID($id = false)
    {
        $q = $this->db->get_where('product_promotions', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addProductPromotionItem($data = false, $promotion_id = false, $product_id = false)
    {
        if($data && $promotion_id && $product_id){
            $this->db->delete('product_promotion_items',array('promotion_id' => $promotion_id, 'main_product_id' => $product_id));
            $this->db->insert_batch('product_promotion_items',$data);
            return true;
        }
        return false;
    }

    public function getProductPromationItems($promotion_id = false, $product_id = false)
    {
        $this->db->select('product_promotion_items.*, products.name as product_name')
            ->join('products','products.id = product_promotion_items.for_product_id','inner')
            ->where('product_promotion_items.promotion_id',$promotion_id)
            ->where('product_promotion_items.main_product_id',$product_id);
        $q = $this->db->get('product_promotion_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllSalemanTargets()
    {
        $q = $this->db->get('saleman_targets');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSalemanTargetByID($id = false)
    {
        $q = $this->db->get_where('saleman_targets', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addSalemanTarget($data = false)
    {
        if ($this->db->insert("saleman_targets", $data)) {
            return true;
        }
        return false;
    }

    public function updateSalemanTarget($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update("saleman_targets", $data)) {
            return true;
        }
        return false;
    }

    public function deleteSalemanTarget($id = false)
    {
        if ($this->db->delete("saleman_targets", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getAllProductNames($term = false, $limit = 10)
    {
        $allow_category = $this->site->getCategoryByProject();
        if($allow_category){
            $this->db->where_in("products.category_id",$allow_category);
        }
        $this->db->select('products.id, code, name,unit, cost')
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

    public function addInventoryOpeningBlanace($data = false, $products = false, $stockmoves = false, $accTrans = false)
    {
        if($this->db->insert('inventory_opening_balances',$data)){
            $opening_id = $this->db->insert_id();
            if($products){
                foreach($products as $product){
                    $product['opening_id'] = $opening_id;
                    $this->db->insert('inventory_opening_balance_items',$product);
                }
            }
            if($stockmoves){
                foreach($stockmoves as $stockmove){
                    $stockmove['transaction_id'] = $opening_id;
                    $this->db->insert('stockmoves',$stockmove);
                    
                    if($this->Settings->accounting_method == '2'){
                        $cal_cost = $this->site->updateAVGCost($stockmove['product_id'],"OpeningBalance",$opening_id);
                    }else if($this->Settings->accounting_method == '1'){
                        $cal_cost = $this->site->updateLifoCost($stockmove['product_id']);
                    }else if($this->Settings->accounting_method == '0'){
                        $cal_cost = $this->site->updateFifoCost($stockmove['product_id']);
                    }else if($this->Settings->accounting_method == '3'){
                        $cal_cost = $this->site->updateProductMethod($stockmove['product_id'],"OpeningBalance",$opening_id);
                    }
                    if($stockmove['option_id'] && $cal_cost) {
                        $this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $stockmove['option_id'], 'product_id' => $stockmove['product_id']));
                    }
                    
                }
            }
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['transaction_id'] = $opening_id;
                    $this->db->insert('acc_tran',$accTran);
                }
            }
            return true;
        }
        return false;
    }

    public function updateInventoryOpeningBalance($id= false, $data = false, $products = false, $stockmoves = false, $accTrans = false)
    {
        if($this->db->update('inventory_opening_balances',$data,array('id'=>$id))){
            $this->site->deleteStockmoves('OpeningBalance',$id);
            $this->site->deleteAccTran('OpeningBalance',$id);
            $this->db->delete('inventory_opening_balance_items', array('opening_id' => $id));
            if($products){
                $this->db->insert_batch('inventory_opening_balance_items',$products);
            }
            if($stockmoves){
                $this->db->insert_batch('stockmoves',$stockmoves);
                foreach($stockmoves as $stockmove){
                    if($this->Settings->accounting_method == '2'){
                        $cal_cost = $this->site->updateAVGCost($stockmove['product_id'],"OpeningBalance",$id);
                    }else if($this->Settings->accounting_method == '1'){
                        $cal_cost = $this->site->updateLifoCost($stockmove['product_id']);
                    }else if($this->Settings->accounting_method == '0'){
                        $cal_cost = $this->site->updateFifoCost($stockmove['product_id']);
                    }else if($this->Settings->accounting_method == '3'){
                        $cal_cost = $this->site->updateProductMethod($stockmove['product_id'],"OpeningBalance",$id);
                    }
                    if($stockmove['option_id'] && $cal_cost) {
                        $this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $stockmove['option_id'], 'product_id' => $stockmove['product_id']));
                    }
                }
            }
            if($accTrans){
                $this->db->insert_batch('acc_tran',$accTrans);
            }
            return true;
        }
        return false;
    }

    public function deleteInventoryOpeningBalance($id = false)
    {
        if ( $this->db->delete('inventory_opening_balances', array('id' => $id))) {
            $opening_items = $this->getInvntoryOpeningBlanceitems($id);
            $this->site->deleteStockmoves('OpeningBalance',$id);
            $this->site->deleteAccTran('OpeningBalance',$id);
            $this->db->delete('inventory_opening_balance_items', array('opening_id' => $id));
            if($opening_items){
                foreach($opening_items as $opening_item){
                    if($this->Settings->accounting_method == '2'){
                        $cal_cost = $this->site->updateAVGCost($opening_item->product_id);
                    }else if($this->Settings->accounting_method == '1'){
                        $cal_cost = $this->site->updateLifoCost($opening_item->product_id);
                    }else if($this->Settings->accounting_method == '0'){
                        $cal_cost = $this->site->updateFifoCost($opening_item->product_id);
                    }else if($this->Settings->accounting_method == '3'){
                        $cal_cost = $this->site->updateProductMethod($opening_item->product_id);
                    }
                }
            }
            
            return true;
        }
        return false;
    }

    public function getInvntoryOpeningBlanceByID($id = false)
    {
        $q = $this->db->get_where('inventory_opening_balances',array('id'=>$id));
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }

    public function getInvntoryOpeningBlanceitems($id = false)
    {
        $this->db->select('inventory_opening_balance_items.*, products.code as product_code, products.name as product_name');
        $this->db->join('products','products.id = inventory_opening_balance_items.product_id','inner');
        $this->db->where('inventory_opening_balance_items.opening_id',$id);
        $q = $this->db->get('inventory_opening_balance_items');

        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function addFuelTime($data = false)
    {
         if ($this->db->insert("fuel_times", $data)) {
            return true;
        }
        return false;
    }

    public function getFuelTimesByID($id = false)
    {
        $q = $this->db->get_where('fuel_times', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteFuelTime($id = false)
    {
        if($this->db->where("id", $id)->delete("fuel_times")){
            return true;
        }
        return false;
    }

    public function updateFuelTime($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('fuel_times', $data)) {
            return true;
        }
        return false;
    }

    public function addArea($data = false)
    {
        if ($this->db->insert('areas', $data)) {
            return true;
        }
        return false;
    }

    public function updateArea($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('areas', $data)) {
            return true;
        }
        return false;
    }

    public function deleteArea($id = false){
        if($id && $this->db->delete('areas',array('id'=>$id))){
            return true;
        }
        return false;

    }

    public function getAreaByID($id = false)
    {
        $this->db->select("areas.*,cities.name as city, districts.name as district, commune.name as commune")
                ->join("(SELECT id,name FROM ".$this->db->dbprefix('areas')." WHERE IFNULL(city_id,0) = 0) as cities","cities.id = areas.city_id","left")
                ->join("(SELECT id,name FROM ".$this->db->dbprefix('areas')." WHERE city_id > 0 AND IFNULL(district_id,0) = 0) as districts","districts.id = areas.district_id","left")
                ->join("(SELECT id,name FROM ".$this->db->dbprefix('areas')." WHERE district_id > 0 AND IFNULL(commune_id,0) = 0) as commune","commune.id = areas.commune_id","left");
        $q = $this->db->get_where('areas', array('areas.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }



    public function addSalesmanGroup($data = false)
    {
        if ($this->db->insert('salesman_groups', $data)) {
            return true;
        }
        return false;
    }

    public function updateSalesmanGroup($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('salesman_groups', $data)) {
            return true;
        }
        return false;
    }

    public function deleteSalesmanGroup($id = false)
    {
        if($id && $this->db->delete('salesman_groups',array('id'=>$id))){
            return true;
        }
        return false;

    }

    public function getSalesmanGroupByID($id = false)
    {
        $q = $this->db->get_where('salesman_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateViewStyle($user_id = false, $style_view = false)
    {
        if($user_id && $style_view){
            $this->db->update('users',array('style_view'=>$style_view),array('id'=>$user_id));
            return true;
        }
        return false;
    }

    public function deleteModel($id = false)
    {
        if($this->db->where("id", $id)->delete("models")){
            return true;
        }
        return false;
    }

    public function updateModel($id = false, $data = array())
    {
        if ($this->db->update("models", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addModel($data = false)
    {
        if ($this->db->insert("models", $data)) {
            return true;
        }
        return false;
    }

    public function getModelByID($id = false)
    {
        $q = $this->db->get_where('models', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addModels($data = false)
    {
        if ($this->db->insert_batch('models', $data)) {
            return true;
        }
        return false;
    }

    public function getBrandByCode($code = false)
    {
        $q = $this->db->get_where('brands', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    // ** Zone ** ======

    public function deleteZone($id = false)
    {
        if($this->db->where("id", $id)->delete("zones")){
            return true;
        }
        return false;
    }

    public function updateZone($id = false, $data = array())
    {
        if ($this->db->update("zones", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addZone($data = false)
    {
        if ($this->db->insert("zones", $data)) {
            return true;
        }
        return false;
    }

    public function getZoneByID($id = false)
    {
        $q = $this->db->get_where('zones', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }



    public function getRoomFloors()
    {
        $q = $this->db->select("rental_floors.id, rental_floors.floor")
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

    public function getProductPromotionByID($id = false)
    {
        $q = $this->db->get_where('product_promotions',array('id'=>$id));
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }

    public function getMainProductPromotion($id = false)
    {
        $this->db->select("products.id,products.code,products.name")
                    ->join("products","products.id = product_promotion_items.main_product_id","inner")
                    ->where("product_promotion_items.promotion_id",$id)
                    ->group_by("product_promotion_items.main_product_id");
        $q = $this->db->get("product_promotion_items");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getFreeProductPromotion($id = false, $main_product = false)
    {
        $this->db->select("products.id,products.code,products.name,product_promotion_items.*")
                    ->join("products","products.id = product_promotion_items.for_product_id","inner")
                    ->where("product_promotion_items.promotion_id",$id)
                    ->where("product_promotion_items.main_product_id",$main_product);
        $q = $this->db->get("product_promotion_items");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getBrandByID($id = false)
    {
        $q = $this->db->get_where('brands', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getProjectByBillers($billers = false){
        $this->db->where_in('biller_id',$billers);
        $q = $this->db->get('projects');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    function backupData(){
        $queryTables = $this->db->query('SHOW TABLES')->result_id;
        while($row = $queryTables->fetch_row()){
            if($row[0] != $this->db->dbprefix('audit_trails') && $row[0] != $this->db->dbprefix('sessions')){
                $tables[] = $row[0];
            }
        }
        $content = false;
        foreach($tables as $table){
            $result = $this->db->query('SELECT * FROM '.$table)->result_id;
            $rows_num = $result->num_rows;
            if($rows_num > 0){
                $fields_amount = $result->field_count;
                $rows_num = $result->num_rows;
                $res = $this->db->query('SHOW CREATE TABLE '.$table)->result_id;
                $TableMLine = $res->fetch_row();
                $content ='DROP TABLE IF EXISTS '.$table.'; '.(!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";
                for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) {
                    while($row = $result->fetch_row()){
                        if ($st_counter%100 == 0 || $st_counter == 0 ){
                            $content .= "\nINSERT INTO ".$table." VALUES";
                        }
                        $content .= "\n(";
                        for($j=0; $j<$fields_amount; $j++){
                            $row[$j] = str_replace("\n","\\n", addslashes($row[$j]));
                            if (isset($row[$j]) && $row[$j] != null){
                                $content .= '"'.$row[$j].'"' ;
                            }else{
                                $content .= 'null';
                            }
                            if ($j<($fields_amount-1)){
                                    $content.= ',';
                            }
                        }
                        $content .=")";
                        if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num){
                            $content .= ";";
                        }
                        else{
                            $content .= ",";
                        }
                        $st_counter=$st_counter+1;
                    }
                } 
                $content .="\n\n\n";
            }
        }
        return $content;
    }
    
    public function addCashAccount($data = false)
    {
        if ($this->db->insert("cash_accounts", $data)) {
            return true;
        }
        return false;
    }

    public function updateCashAccount($id = false, $data = array())
    {
        if ($this->db->update("cash_accounts", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteCashAccount($id = false)
    {
        if ($this->db->delete("cash_accounts", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    
    public function getUnitQtyByCode($product_id = false, $unit_code = false){
        $this->db->select("product_units.unit_qty,units.code,product_units.unit_id");
        $this->db->where("product_units.product_id",$product_id);
        $this->db->where("units.code",$unit_code);
        $this->db->join("units","units.id = product_units.unit_id","inner");
        $q = $this->db->get("product_units");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;   
    }
    public function getBomByID($id = false){
        $q = $this->db->get_where("boms",array("id"=>$id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;   
    }
    public function getBomItems($bom_id = false){
        $q = $this->db->get_where("bom_items",array("bom_id"=>$bom_id));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addBom($data = false, $row_meterials = false, $finished_goods = false){
        if($this->db->insert("boms",$data)){
            $bom_id = $this->db->insert_id();
            if($row_meterials){
                foreach($row_meterials as $row_meterial){
                    $row_meterial["bom_id"] = $bom_id;
                    $this->db->insert("bom_items",$row_meterial);
                }
            }
            if($finished_goods){
                foreach($finished_goods as $finished_good){
                    $finished_good["bom_id"] = $bom_id;
                    $this->db->insert("bom_items",$finished_good);
                }
            }
            return true;
        }
        return false;
    }
    public function updateBom($id = false, $data = false, $row_meterials = false, $finished_goods = false){
        if($id && $this->db->update("boms",$data,array("id"=>$id))){
            $this->db->delete("bom_items",array("bom_id"=>$id));
            if($row_meterials){
                $this->db->insert_batch("bom_items",$row_meterials);
            }
            if($finished_goods){
                $this->db->insert_batch("bom_items",$finished_goods);
            }
            return true;
        }
        return false;
    }
    public function deleteBom($id = false){
        if($id && $this->db->delete("boms",array("id"=>$id))){
            $this->db->delete("bom_items",array("bom_id"=>$id));
            return true;
        }
        return false;
    }

    public function updateBranchPrefix($id, $data = array())
    {
        if ($this->db->update("order_ref", $data, array('ref_id' => $id))) {
            return true;
        }
        return false;
    }

    public function getBranchPrefixByID($id)
    {
        $q = $this->db->get_where('order_ref', array('ref_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    // ** Commission_Types **
    public function getCommissionTypesGroupLevel($id)
    {
        $this->db->where('commission_type_id', $id)->from('salesman_groups');
        return $this->db->count_all_results();
    }

    public function addCommissionTypes($data)
    {
        if ($this->db->insert("commission_types", $data)) {
            return true;
        }
        return false;
    }
    public function updateCommissionTypes($id, $data = array())
    {
        if ($this->db->update("commission_types", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
    public function deleteCommissionTypes($id)
    {
        if ($this->getCommissionTypesGroupLevel($id)) {
            return false;
        }
        if ($this->db->delete("commission_types", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }


}
