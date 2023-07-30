<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Companies_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getAllBillerCompanies()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'biller'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllCustomerCompanies()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'customer'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllSupplierCompanies()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'supplier'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getCustomerGroupByName($name = false) 
	{
        $q = $this->db->get_where('customer_groups', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
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

    public function getAllCardTypes()
    {
        $q = $this->db->get('rental_card_types');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllNationalityTypes()
    {
        $q = $this->db->get('nationality_type');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyUsers($company_id = false)
    {
        $q = $this->db->get_where('users', array('company_id' => $company_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyByID($id = false)
    {
        $q = $this->db->get_where('companies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCompanyByEmail($email = false)
    {
        $q = $this->db->get_where('companies', array('email' => $email), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	//=================prefix==============//
	public function addPrefix($bill_id = false,$prefix = false)
    {
		$curDate = date('Y-m-d');
        $query = $this->db->get('order_ref');
		$fields  = $query->list_fields();
		$list_field = 'date= "'.$curDate.'", bill_id='.$bill_id.',bill_prefix = "'.$prefix.'"';
		$i=0;
		foreach ($fields as $f)
		{
			if($i > 3){
				$list_field .= ',`'.$f.'`="1"';
			}else{
				$i++;
			}
		}
		if($this->db->query("INSERT INTO ".$this->db->dbprefix('order_ref')." SET ".$list_field."")){
			return true;
		}
		return FALSE;
    }
	
	public function updatePrefix($bill_id = false,$prefix = false)
	{
		$this->db->where('bill_id', $bill_id);
        if ($this->db->update('order_ref', $prefix)) {
            return true;
        }
        return false;
	}
	
	public function getPrefixByBill($bill_id = false)
	{
		$q = $this->db->get_where('order_ref', array('bill_id' => $bill_id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	//=================end prefix==============//
	
    public function addCompany($data = array(),$accounting_settings= array())
    {
		
        if ($this->db->insert('companies', $data)) {
            $cid = $this->db->insert_id();
			if ($accounting_settings) {
				$accounting_settings['biller_id'] = $cid;
				$this->db->insert('acc_setting', $accounting_settings);
            }
            return $cid;
        }
        return false;
    }
	
	public function getAccountSettingByBiller($biller_id = false)
	{
		$q = $this->db->get_where('acc_setting', array('biller_id' => $biller_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

    public function updateCompany($id = false, $data = array(),$accounting_settings= array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('companies', $data)) {
			if($accounting_settings){
				$this->db->delete('acc_setting',array('biller_id'=>$id));
				$accounting_settings['biller_id'] = $id;
				$this->db->insert('acc_setting',$accounting_settings);
			}
            return true;
        }
        return false;
    }

    public function addCompanies($data = array())
    {
        if ($this->db->insert_batch('companies', $data)) {
            return true;
        }
        return false;
    }

    public function deleteCustomer($id = false)
    {
        if ($this->getCustomerSales($id)) {
            return false;
        }
        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'customer')) && $this->db->delete('users', array('company_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteSupplier($id = false)
    {
        if ($this->getSupplierPurchases($id)) {
            return false;
        }
        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'supplier')) && $this->db->delete('users', array('company_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteBiller($id = false,$check = false)
    {
        if ($this->getBillerSales($id) && !$check) {
            return false;
        }
        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'biller'))) {
			$this->db->delete('order_ref', array('bill_id' => $id));
			$this->db->delete('acc_setting', array('biller_id' => $id));
            return true;
        }
        return FALSE;
    }

    public function getBillerSuggestions($term = false, $limit = 10)
    {
        $this->db->select("id, company as text");
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', array('group_name' => 'biller'), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCustomerSuggestions($term = false, $limit = 10)
    {
        $this->db->select("id, (CASE WHEN name = '-' THEN name ELSE CONCAT(name, ' (', phone, ')') END) as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', array('group_name' => 'customer'), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getSupplierSuggestions($term = false, $limit = 10)
    {
        $this->db->select("id, (CASE WHEN company = '-' THEN name ELSE CONCAT(company, ' (', code, ')') END) as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', array('group_name' => 'supplier'), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCustomerSales($id = false)
    {
        $this->db->where('customer_id', $id)->from('sales');
        return $this->db->count_all_results();
    }

    public function getBillerSales($id = false)
    {
        $this->db->where('biller_id', $id)->from('sales');
        return $this->db->count_all_results();
    }

    public function getSupplierPurchases($id = false)
    {
        $this->db->where('supplier_id', $id)->from('purchases');
        return $this->db->count_all_results();
    }

    public function addDeposit($data = false, $cdata = false, $accTranDeposit = false)
    {
        if ($this->db->insert('deposits', $data)) {
			$deposit_id = $this->db->insert_id();
			$this->db->update('companies', $cdata, array('id' => $data['company_id']));
			if($accTranDeposit){
				foreach($accTranDeposit as $accTran){
					$accTran['transaction_id'] = $deposit_id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
			return true;
        }
        return false;
    }

    public function updateDeposit($id = false, $data = false, $cdata = false, $accTranDeposit = false)
    {
        if ($this->db->update('deposits', $data, array('id' => $id)) && 
            $this->db->update('companies', $cdata, array('id' => $data['company_id']))) {
			$this->site->deleteAccTran('CustomerDeposit',$id);
			if($accTranDeposit){
				$this->db->insert_batch('acc_tran', $accTranDeposit);	
			}
			
            return true;
        }
        return false;
    }
	public function updateSupplierDeposit($id = false, $data = false, $cdata = false, $accTranDeposit = false)
    {
        if ($this->db->update('deposits', $data, array('id' => $id)) && 
            $this->db->update('companies', $cdata, array('id' => $data['company_id']))) {
			$this->site->deleteAccTran('SupplierDeposit',$id);
			if($accTranDeposit){
				$this->db->insert_batch('acc_tran', $accTranDeposit);	
			}
			
            return true;
        }
        return false;
    }

    public function getDepositByID($id = false)
    {
        $q = $this->db->get_where('deposits', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteDeposit($id = false)
    {
        $deposit = $this->getDepositByID($id);
        $company = $this->getCompanyByID($deposit->company_id);
        $cdata = array(
                'deposit_amount' => ($company->deposit_amount-$deposit->amount)
            );
        if ($this->db->update('companies', $cdata, array('id' => $deposit->company_id)) &&
            $this->db->delete('deposits', array('id' => $id))) {
            return true;
        }
        return false;
    }
	
	public function getPriceGroupByName($name = false)
    {
        $q = $this->db->get_where('price_groups', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
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

    public function getCompanyAddresses($company_id = false)
    {
        $q = $this->db->get_where('addresses', array('company_id' => $company_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addAddress($data = false)
    {
        if ($this->db->insert('addresses', $data)) {
            return true;
        }
        return false;
    }

    public function updateAddress($id = false, $data = false)
    {
        if ($this->db->update('addresses', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteAddress($id = false)
    {
        if ($this->db->delete('addresses', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getAddressByID($id = false)
    {
        $q = $this->db->get_where('addresses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function addMessage($data = false)
	{
		if($this->db->insert("messages", $data)){
			return true;
		}
		return false;
	}
	
	public function deleteMessage($id = false)
	{
		if($this->db->delete("messages", array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function getAddresses($id = false, $company_id = false, $color_marker = false){
		if($id && $id != "false"){
			$this->db->where("addresses.id",$id);
		}
		if($company_id && $company_id != "false"){
			$this->db->where("company_id",$company_id);
		}
		if($color_marker && $color_marker != "false"){
			$this->db->where("color_marker",$color_marker);
		}
		$this->db->select("addresses.*,companies.name,companies.company");
		$this->db->join("companies","companies.id = addresses.company_id","inner");
		$q = $this->db->get("addresses");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
	}
	public function addCustomerTruck($data = false){
		if($data && $this->db->insert("customer_trucks",$data)){
			return true;
		}
		return false;
	}
	public function updateCustomerTruck($id = false, $data = false){
		if($id && $data && $this->db->update("customer_trucks",$data,array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function deleteCustomerTruck($id = false){
		if($id && $this->db->delete("customer_trucks",array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function getCustomerTruckByID($id = false)
    {
        $q = $this->db->get_where('customer_trucks', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getCompanyByCode($code = false)
    {
        $q = $this->db->get_where('companies', array('code' => $code,'group_name'=> "customer"), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getCompanyByCodeGroupName($code = false,$group_name = false)
    {
        $q = $this->db->get_where('companies', array('code' => $code,'group_name'=> $group_name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getCompanyByPhone($phone = false,$group_name = false)
    {
        $q = $this->db->get_where('companies', array('phone' => $phone,'group_name'=> $group_name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	
	
}
