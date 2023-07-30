<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Rentals_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
	
    public function getProductNames($term = false, $warehouse_id = false, $limit = 10)
    {
		$this->db->where('products.inactive !=',1);
        $this->db->select('products.*, warehouses_products.quantity')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')            
			->where('products.type != ','raw_material')
            ->where('products.type != ','asset')
            ->where('products.type','service_rental')
            ->where('products.electricity>',0)
			->group_by('products.id');
            $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
 
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
            ->where('products.type','service')
            ->where('products.electricity>',0)
            ->group_by('products.id');
        $q = $this->db->get_where('products', array('warehouses_products.product_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getItemByID($id = false)
    {
        $q = $this->db->get_where('rental_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllRentalItemsWithDetails($rental_id = false)
    {
        $this->db->select('rental_items.*, products.details');
        $this->db->join('products', 'products.id=rental_items.product_id', 'left');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('rentals_items', array('rental_id' => $rental_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    

    public function getRentalRoomsByID($id = false)
    {
        $q = $this->db->get_where('rental_rooms', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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
    public function getRentalHousekeepingByID($id = false)
    {
        $q = $this->db->get_where('rental_housekeepings', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
     public function updateStatusRentalByID($id = false)
    {
        $rentals = $this->getRentalByID($id);
        if($rentals->status == "checked_in"){
            $rental_status = "checked_out";
        }else{
            $rental_status = "checked_in";
        }

        if($this->db->where("id", $id)->update("rentals", array("status" => $rental_status))){
            return true;
        }
    }
	
	public function getAllRentalItemParents($rental_id = false)
    {
        $this->db->select('rental_items.parent_id,products.name')
			->join('products', 'products.id=rental_items.product_id', 'left')
            ->group_by('rental_items.parent_id')
			->order_by('parent_id', 'desc');
			$this->db->where('rental_id', $rental_id);
			$q = $this->db->get('rental_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result_array()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
	public function getAllRentalItems($rental_id = false)
    {
        $this->db->select('rental_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, products.product_details as product_details, product_variants.name as variant,units.name as unit_name')
            ->join('products', 'products.id=rental_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=rental_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=rental_items.tax_rate_id', 'left')
			->join('units','units.id = rental_items.product_unit_id','left')
            ->group_by('rental_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('rental_items', array('rental_id' => $rental_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllFoodOrderItems($rental_id = false)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, products.product_details as product_details, product_variants.name as variant,units.name as unit_name')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('units','units.id = sale_items.product_unit_id','left')
           // ->join('sales','sales.id = sale_items.sale_id','left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('sale_items', array('rental_id' => $rental_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addRental($data = array(), $items = array())
    {
		if($data['status'] == 'checked_in'){
		   $data['checked_in'] = $data['from_date'];
		}
        if ($this->db->insert('rentals', $data)) {
            $rental_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['rental_id'] = $rental_id;
                $this->db->insert('rental_items', $item);
            }
        if($this->db->update('rental_rooms', array('availability' => $data['status'],'housekeeping_status' => 'cleaned','availability' => $data['status'], 'rental_id' =>$rental_id),array('id' => $data['room_id']))){

            return true;
        }
    }
        return false;
    }

    public function updateRental($id = false, $data = false, $items = array())
    {
        if ($this->db->update('rentals', $data, array('id' => $id)) && $this->db->delete('rental_items', array('rental_id' => $id))) {
            if($this->db->update('rental_rooms', array('availability' => $data['status'], 'housekeeping_status' => 'cleaned'),array('id' => $data['room_id']))){
            foreach ($items as $item) {
                $item['rental_id'] = $id;
                $this->db->insert('rental_items', $item);
            }
            return true;
        }
    }
        return false;
    }

    public function updateStatus($id = false, $status = false, $fdrdate = false, $ckidate = false, $ckodate = false, $note = false)
    {
		$data = array('status' => $status, 'note' => $note);
		if($status == 'checked_in'){
		   $data['checked_in'] = $ckidate;
		}

        if($status == 'reservation'){
           $data['reservation'] = $fdrdate;
        }

		if($status == 'checked_out'){
           $data['to_date'] = $ckodate;
           $data['checked_out'] = $ckodate;
		}
        if ($this->db->update('rentals', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
    
    public function updateStatusRooms($id = false, $status = false, $fdrdate = false, $ckidate = false, $ckodate = false, $note = false, $updated_by = false,$updated_at = false)
    {
        $data = array(
            'housekeeping_status'   => $status, 
            'note'                  => $note,
            'updated_by'            => $updated_by,
            'updated_at'            => $updated_at
        ); 

        if($status == 'checked_in'){
           $data['checked_in'] = $ckidate;
        }

        if($status == 'checked_out'){
           $data['to_date'] = $ckodate;
           $data['checked_out'] = $ckodate;
        }
        if ($this->db->update('rental_rooms', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
	
    public function deleteRental($id = false)
    {
        if ($this->db->delete('rental_items', array('rental_id' => $id)) && $this->db->delete('rentals', array('id' => $id))) {
            $this->db->where("transaction_id", $id)->where("transaction","RentalDeposit")->delete("payments");
            $this->db->where("transaction_id", $id)->where("transaction","ReturnRentalDeposit")->delete("payments");
            return true;
        }
        return FALSE;
    }

    public function deleteRentalHousekeeping($id = false)
    {
        if ($this->db->delete('rentals', array('id' => $id))){
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
		$q = $this->db->select('rental_rooms.id as id, rental_rooms.*,rental_bed_number.description as bed_number_name')
					  ->from('rental_rooms')
					  ->join('products','products.id=rental_rooms.product_id','left')
                      ->join('rental_bed_number','rental_bed_number.id=rental_rooms.bed_number_id','left')
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

    public function getAllRoomsPayments($floor = false, $warehouse = false)
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

    public function getServiceTypes()
    {
        $q = $this->db->order_by('name','asc')->where_in('code', array('room_charge','room_late_checkout','house_house','complimentary'))->get('rental_service_types');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getRoomTypes()
    {
        $q = $this->db->select("rental_room_types.*")
                      ->from("rental_rooms")
                      ->join("rental_room_types","rental_rooms.room_type_id=rental_room_types.id","left")
                      ->where("rental_room_types.id >", 0)
                      ->group_by("rental_room_types.id")
                      ->get();
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
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


	
	public function getUnAvailableRoomReservation($room_id = false, $status = false, $fdrdate = false, $ckidate = false, $ckodate = false, $note = false)
	{
        $res_date = $data['from_date'];
		$q = $this->db->where("room_id", $room_id)->where_in('from_date', array($res_date))->get("rentals");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

    public function getUnAvailableRoom2($room_id = false, $status = false, $fdrdate = false, $ckidate = null, $ckodate = false, $note = false)
    {
        $q = $this->db->where("room_id", $room_id)->where_in('from_date', array(date("Y-m-d")))->get("rentals");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }

    public function getUnAvailableRoom($room_id = false)
    {
        $q = $this->db->where("room_id", $room_id)->where_in('status', array('checked_in','reservation'))->where_in('from_date', array(date("Y-m-d")))->get("rentals");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    

    public function getRoomStatus($room_id = false)
    {
        $q = $this->db->where("room_id", $room_id)->where_in('status', array('checked_in','reservation','maintenance'))->get("rentals");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }

	public function getSaleByRentalID($rental_id = false)
	{
		$q = $this->db->where("rental_id", $rental_id)->get("sales");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getFrequencies()
    {
        $q = $this->db->order_by('day','asc')->get('frequency');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getFrequencyByID($id = false)
    {
        $q = $this->db->where('day',$id)->get('frequency');
        if ($q->num_rows() > 0) {
            $row = $q->row();
            return $row;
        }
        return FALSE;
    }

	public function addDeposit($data = array(), $customer_id = null, $accTranPayments = array())
    {
        if ($this->db->insert('payments', $data)) {
			$payment_id = $this->db->insert_id();
            $customer_id = $data->customer_id;
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

	public function getDeposits3($rental_id = false)
	{
		$q = $this->db->get_where('payments', array('transaction_id' => $rental_id));
		if($q->num_rows() > 0 ){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

    public function getDepositsPayments($rental_id = false)
    {
        $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
        $this->db->order_by('id', 'desc');
      
        $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('transaction_id' => $rental_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }


    public function getDeposits($rental_id = false)
    {
        $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
        $this->db->order_by('id', 'desc');
        $this->db->where('payments.type', 'received');
      
        $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('transaction_id' => $rental_id));
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
	
	public function deleteDeposit($id = false)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
			$this->site->deleteAccTran('RentalDeposit',$id);
            return true;
        }
        return FALSE;
    }
	
	public function updateDeposit($id = false, $data = array(), $customer_id = null, $accTranPayments = array())
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
			$this->site->deleteAccTran('RentalDeposit',$id);
			if($accTranPayments){
				$this->db->insert_batch('acc_tran', $accTranPayments);
			}
            return true;
        }
        return false;
    }
	
	public function getWHProductByRoom($room_id = false)
    {
        $this->db->select('
					products.*,
					rental_rooms.price as rprice,
					warehouses_products.quantity, 
					categories.id as category_id, 
					categories.type as category_type, 
					categories.name as category_name')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
			->join('rental_rooms','rental_rooms.product_id=products.id','right')
			->group_by('products.id');
        $q = $this->db->get_where("products", array('rental_rooms.id' => $room_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getOldNumber($room_id = false, $product_id = false)
	{
		$q = $this->db->select("MAX(new_number) as old_number")
					  ->from('sales')
					  ->join('rentals','rentals.id=sales.rental_id','left')
					  ->join('sale_items', 'sales.id=sale_items.sale_id','left')
					  ->where('room_id', $room_id)
					  ->where('product_id', $product_id)
					  ->group_by('room_id, product_id')
					  ->get();
		if($q->num_rows() > 0){
			$row = $q->row();
			return (double)$row->old_number;
		}	
		return 0;
	}
	
	public function getRoomsByFloor($floor_id = false)
	{
		$q = $this->db->get_where("rental_rooms", array("floor"=>$floor_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

    public function getRoomsByTypes($room_type_id = false)
    {
        $q = $this->db->get_where("rental_rooms", array("room_type_id"=>$room_type_id));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    // Setting Floor

    public function addFloor($data = false)
    {
        if ($this->db->insert("rental_floors", $data)) {
            return true;
        }
        return false;
    }

	public function getFloorByID($id = false)
    {
        $q = $this->db->get_where('rental_floors', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function updateFloor($id = false, $data = array())
    {
        if ($this->db->update("rental_floors", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

	public function deleteFloor($id = false)
	{
		if ($this->db->delete("rental_floors", array('id' => $id))) {
            return true;
        }
        return FALSE;
	}

	public function getAllFloors()
	{
		$q = $this->db->order_by('floor','asc')->get('rental_floors');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllBedNumbers()
    {
        $q = $this->db->order_by('name','asc')->get('rental_bed_number');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllRoomTypes()
    {
        $q = $this->db->order_by('name','asc')->get('rental_room_types');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllServiceTypes()
    {
        $q = $this->db->order_by('name','asc')->get('rental_service_types');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    
    public function getAllSourceTypes()
    {
        $q = $this->db->order_by('name','asc')->get('rental_source_types');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllRoomNumbers()
    {
        $q = $this->db->order_by('name','asc')->get('rental_rooms');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllRoomStatus()
    {
        $q = $this->db->order_by('id','asc')->get('rental_room_status');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllHousekeepingTypes()
    {
        $q = $this->db->order_by('name','asc')->get('rental_housekeeping_status');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


    public function addRoomType($data = false)
    {
        if ($this->db->insert("rental_room_types", $data)) {
            return true;
        }
        return false;
    }

    public function getRoomTypeByID($id = false)
    {
        $q = $this->db->get_where('rental_room_types', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getRoomTypesByID($id = false)
    {
        $q = $this->db->get_where('rental_room_types', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
        public function deleteRoomType($id = false)
    {
        if ($this->db->delete("rental_room_types", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    public function updateRoomType($id = false, $data = array())
    {
        if ($this->db->update("rental_room_types", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
    // Room Rate

    public function addRoomRates($data = false)
    {
        if ($this->db->insert("rental_room_rates", $data)) {
            return true;
        }
        return false;
    }

    public function deleteRoomRates($id = false)
    {
        if ($this->db->delete("rental_room_rates", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    
    public function getRoomRateByID($id = false)
    {
        $q = $this->db->get_where('rental_room_rates', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function updateRoomRate($id = false, $data = array())
    {
        if ($this->db->update("rental_room_rates", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    // Service Types

    public function addServiceType($data = false)
    {
        if ($this->db->insert("rental_service_types", $data)) {
            return true;
        }
        return false;
    }
    public function deleteServiceType($id = false)
    {
        if ($this->db->delete("rental_service_types", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    public function updateServiceType($id = false, $data = array())
    {
        if ($this->db->update("rental_service_types", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
      public function getServiceTypeByID($id = false)
    {
        $q = $this->db->get_where('rental_service_types', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getServiceTypesByID($id = false)
    {
        $q = $this->db->get_where('rental_service_types', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSourceTypesByID($id = false)
    {
        $q = $this->db->get_where('rental_source_types', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    // Housekeeping Status

    public function addHousekeepingStatus($data = false)
    {
        if ($this->db->insert("rental_housekeeping_status", $data)) {
            return true;
        }
        return false;
    }
        public function getHousekeepingStatusByID($id = false)
    {
        $q = $this->db->get_where('rental_housekeeping_status', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function updateHousekeepingStatus($id = false, $data = array())
    {
        if ($this->db->update("rental_housekeeping_status", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addHousekeepings($data = false)
    {
        if ($this->db->insert("rentals", $data)) {
            $this->db->update('rental_rooms', array('housekeeping_status' => $data['status']),array('id' => $data['room_id']));
            return true;
        }
        return false;
    }
    public function updateHousekeepings($id = false, $data = array())
    {
        if ($this->db->update("rentals", $data, array('id' => $id))) {
            if($this->db->update('rental_rooms', array('housekeeping_status' => $data['status']),array('id' => $data['room_id']))){
            return true;
        }
    }
        return false;
    }
    public function deleteHousekeepings($id = false)
    {
        if ($this->db->delete("rentals", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    // Setting Room

    public function addRoom($data = false)
    {
        if ($this->db->insert("rental_rooms", $data)) {
            return true;
        }
        return false;
    }

	public function updateRoom($id = false, $data = array())
    {
        if ($this->db->update("rental_rooms", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

	public function deleteRoom($id = false)
    {
        if ($this->db->delete("rental_rooms", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

	public function getRoomItems()
	{
		$q = $this->db->where('service_types','Room Charge')->get('products');
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
        }
        return FALSE;
	}

	public function getRoomByName($name = false)
    {
        $q = $this->db->where('name',$name)->get('rental_rooms');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function getRentalDepositPayments($rental_id = false, $transaction = 'RentalDeposit', $type='received')
	{
		$q = $this->db->select("SUM(amount) as amount")
					  ->from("payments")
					  ->where("transaction",$transaction)
					  ->where("type",$type)
					  ->where("transaction_id", $rental_id)
					  ->get();
		if($q->num_rows()>0){
			$row = $q->row();
			return $row;
		}
		return false;
    }


    public function getServiceByID($id = false){
        $q = $this->db->where("id",$id)->get("products");
        if($q->num_rows()>0){
            $row = $q->row();
            return $row;
        }
        return false;
    }

    public function deleteService($id = false)
    {
        if ($this->db->delete("products", array('id' => $id))) {
            $this->db->where("product_id", $id)->delete("acc_product");
            return true;
        }
        return FALSE;
    }

    public function addService($data = false, $account=null)
    {
        if ($this->db->insert("products", $data)) {
            $service_id = $this->db->insert_id();
            if($account){
				$account['product_id'] = $service_id;
				$this->db->insert('acc_product', $account);
			}
            return true;
        }
        return false;
    }

    public function updateService($id = false, $data = false, $account=null)
    {
        if($this->db->where("id", $id)->update("products", $data)){
            if($account){
                $this->db->where("product_id", $id)->delete("acc_product");
				$account['product_id'] = $id;
				$this->db->insert('acc_product', $account);
			}
            return true;
        }
        return false;
    }

    public function getServiceAccByServiceId($service_id = false)
    {
        $q = $this->db->get_where('acc_product', array('product_id' => $service_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalRooms() 
      {
        $q = $this->db->select("count(id) as totalRoom")
                      ->from("rental_rooms")
                      ->get();
        if($q->num_rows()>0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    public function getTotalRoomsTypes() 
      {
        $q = $this->db->select("count(id) as totalRoomType")
                      ->from("rental_room_types")
                      ->get();
        if($q->num_rows()>0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    public function getTotalFloors() 
      {
        $q = $this->db->select("count(id) as TotalFloor")
                      ->from("rental_floors")
                      ->get();
        if($q->num_rows()>0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    public function getTotalServices() 
      {
        $q = $this->db->select("count(id) as TotalService")
                      ->from("products")
                       ->where("type",'service_rental')
                      ->get();
        if($q->num_rows()>0){
            $row = $q->row();
            return $row;
        }
        return false;
    }

    function getTotalRooms11($start_date = false, $end_date = false, $customer = false){
        if($start_date){
            $this->db->where("date(date) >=",$start_date);
        }else{
            $this->db->where("date(date) >=",date("Y-m-d"));
        }
        if($end_date){
            $this->db->where("date(date) <=",$end_date);
        }else{
            $this->db->where("date(date) <=",date("Y-m-d"));
        }
        if($customer){
            $this->db->where("supplier_id",$customer);
        }
        $this->db->select("id,date,reference,supplier,grand_total");
        $q = $this->db->get("expenses");
        $data = array();
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            
        }
        return $data;
    }

}
