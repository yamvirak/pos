<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Synchronize extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
		if (!$this->config->item('server_local')) {
			redirect("welcome");
        }
		$this->load->library('form_validation');
        $this->load->model('synchronize_model');
		$this->load->model('attendances_model');
    }
	
	//==========service site=========//
	function push(){
		$tables = array(
						$this->db->dbprefix("settings"),
						$this->db->dbprefix("companies"),
						$this->db->dbprefix("users"),
						$this->db->dbprefix("pos_settings"),
						$this->db->dbprefix("acc_setting"),
						$this->db->dbprefix("warehouses"),
						$this->db->dbprefix("groups"),
						$this->db->dbprefix("permissions"),
						$this->db->dbprefix("projects"),
						$this->db->dbprefix("acc_chart"),
						$this->db->dbprefix("categories"),
						$this->db->dbprefix("units"),
						$this->db->dbprefix("products"),
						$this->db->dbprefix("warehouses_products"),
						$this->db->dbprefix("product_units"),
						$this->db->dbprefix("acc_product")
					);
		$data = $this->synchronize_model->getPushData($tables);
		
		$table_stockmove = array($this->db->dbprefix("stockmoves"));
		$structure = $this->synchronize_model->getPushData($table_stockmove,true);	
		$stockmove_data = $this->synchronize_model->getStockmoves();
		
		if($data){
			echo json_encode(array("data" => $data, "stockmoves" => array("structure"=>$structure,"data"=>$stockmove_data)));
		}else{
			echo json_encode(false);
		}
		
	}
	
	function get_pos(){
		$data = json_decode($this->input->post("data"));
		$pos = (array) $data->pos;
		$pos_items = (array) $data->pos_items;
		$pos_stockmoves = (array) $data->pos_stockmoves;
		$pos_payments = (array) $data->pos_payments;
		$pos_acc_trans = (array) $data->pos_acc_trans;
		$pos_acc_pay_trans = (array) $data->pos_acc_pay_trans;
		$pos_registers = (array) $data->pos_registers;
		$pos_register_items = (array) $data->pos_register_items;
		$result = $this->synchronize_model->addPOS($pos,$pos_items,$pos_stockmoves,$pos_payments,$pos_acc_trans,$pos_acc_pay_trans,$pos_registers,$pos_register_items);
		if($result){
			echo json_encode($result);
		}else{
			echo json_encode(false);
		}
	}
	
	//===========local site===========//
	function pull(){
		if(site_url() == $this->config->item('server_url')){
			redirect("welcome");
		}
		$data = false;
		$stockmoves = false;
		$unpush_stockmoves = $this->synchronize_model->getUnPushPOSStocmkoves();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->config->item("server_url")."synchronize/push");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		if($result != 'false' && $result){
			$result = json_decode($result);
			$data = $result->data;
			$stockmoves = $result->stockmoves;
		}
		curl_close($ch);
		if($data){
			$this->synchronize_model->pullData($data, $stockmoves, $unpush_stockmoves);
		}
	}
	
	function push_pos(){
		if(site_url() == $this->config->item('server_url')){
			redirect("welcome");
		}
		$pos = $this->synchronize_model->getPOS();
		if($pos){
			$pos_acc_trans = false;
			$pos_acc_pay_trans = false;
			$pos_items = $this->synchronize_model->getPOSItems();
			$pos_stockmoves = $this->synchronize_model->getPOSStocmkoves();
			$pos_payments = $this->synchronize_model->getPOSPayments();
			if($this->Settings->accounting == 1){
				$pos_acc_trans = $this->synchronize_model->getPOSAccTrans();
				$pos_acc_pay_trans = $this->synchronize_model->getPOSPaymentAccs();
			}
			$pos_registers = $this->synchronize_model->getPOSRegisters();
			$pos_register_items = $this->synchronize_model->getPOSRegisterItems();
			$q = json_encode(array(
									'pos'=>$pos,
									'pos_items'=>$pos_items,
									'pos_stockmoves'=>$pos_stockmoves,
									'pos_payments'=>$pos_payments,
									'pos_acc_trans'=>$pos_acc_trans,
									'pos_acc_pay_trans'=>$pos_acc_pay_trans,
									'pos_registers'=>$pos_registers,
									'pos_register_items'=>$pos_register_items
									));

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->config->item("server_url")."synchronize/get_pos");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('data' => $q)));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			if($result != 'false' && $result){
				$data = json_decode($result);
				$this->synchronize_model->updatePushed($data);
			}
			curl_close ($ch);
		}
		redirect("welcome");
	}
	
	
	//========================Attendance===================//
		public function get_att_log($device_id = false){
			$devices = $this->synchronize_model->getDevices('active');
			if($devices){
				$this->load->library('Zk');
				foreach($devices as $device){
					$data = false;
					$clear = false;
					$count_attendance = 0;
					$ip_address = $device->ip_address;
					$port = $device->port;
					$zk = new ZKLib($ip_address, $port);
					if($zk->connect()){
						$attendances = $zk->getAttendance();	
						if($attendances){
							foreach($attendances as $attendance){
								$check_time_int = strtotime($attendance[3]);
								if(!$this->synchronize_model->checkRawCheckInOut($attendance[1],$check_time_int)){
									$data[] = array(
												'finger_id'=>$attendance[1],
												'check_time'=>$attendance[3],
												'check_time_int'=>$check_time_int,
												'device_id'=>$device->id
											);
								}
							}
							if($data){
								$count_attendance = count($attendances);
								if($device->clear==1 && $count_attendance > $device->max_att_log){
									$clear = array(
													'ip_address'=>$ip_address,
													'port'=>$port,
													'count_attendance'=>$count_attendance,
												);
								}
								$this->synchronize_model->addRawCheckInOut($data,$clear);
							}
						}
					}
				}
				$this->push_att();
			}
		}
		function push_att(){
			if(site_url() == $this->config->item('server_url')){
				redirect("welcome");
			}
			$raw_check_in_outs = $this->synchronize_model->getRawCheckInOut(true);
			if($raw_check_in_outs){
				$q = json_encode(array('raw_check_in_outs'=>$raw_check_in_outs));
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->config->item("server_url")."synchronize/get_att");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('data' => $q)));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);
				if($result != 'false' && $result){
					$data = json_decode($result);
					$this->synchronize_model->updateAttPushed($data);
				}
				curl_close ($ch);
			}
			redirect("welcome");
		}
		
		function get_att(){
			$data = json_decode($this->input->post("data"));
			$raw_check_in_outs = (array) $data->raw_check_in_outs;
			$result = $this->synchronize_model->addCheckInOut($raw_check_in_outs);
			if($result){
				echo json_encode($result);
			}else{
				echo json_encode(false);
			}
		}
	
	//========================END Attendance===================//
	
	
	
	
	
	


}
