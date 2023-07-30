<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Synchronize_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

	//===============================service site===================================//
	function getPushData($tables = false, $structure_only = false){
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
				if(!$structure_only){
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
				}
				
				$content .="\n\n\n";
			}
		}
		return $content;
	}
	
	function getStockmoves(){
		$this->db->select("stockmoves.*,'server' as transaction,'0' as transaction_id, sum( quantity ) AS quantity");
		$this->db->group_by("product_id","warehouse_id","option_id");
		$q = $this->db->get("stockmoves");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
				unset($row->id);
				$data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	function addPOS($pos = false,$pos_items = false, $pos_stockmoves = false,$pos_payments =false, $pos_acc_trans = false, $pos_acc_pay_trans = false, $pos_registers = false,$pos_register_items = false){
		$pushed_ids = false;
		$pushed_reg_ids = false;
		if($pos){
			$sale_items = false;
			$stockmoves = false;
			$acc_trans = false;
			foreach($pos as $row){
				if($row){
					$old_sale_id = $row->id;
					unset($row->id);
					$row = (array) $row;
					$row['pushed'] = 1;
					if($this->db->insert("sales",$row)){
						$pushed_ids[] = $old_sale_id;
						$new_sale_id = $this->db->insert_id();
						if(isset($pos_items["s_".$old_sale_id]) && $pos_items["s_".$old_sale_id]){
							foreach($pos_items["s_".$old_sale_id] as $pos_item){
								$pos_item = (array) $pos_item;
								unset($pos_item['id']);
								$pos_item['sale_id'] = $new_sale_id;
								$sale_items[] = $pos_item;
							}
						}
						
						if(isset($pos_stockmoves["s_".$old_sale_id]) && $pos_stockmoves["s_".$old_sale_id]){
							foreach($pos_stockmoves["s_".$old_sale_id] as $pos_stockmove){
								$pos_stockmove = (array) $pos_stockmove;
								unset($pos_stockmove['id']);
								$pos_stockmove['transaction_id'] = $new_sale_id;
								$stockmoves[] = $pos_stockmove;
							}
						}

						if(isset($pos_acc_trans["s_".$old_sale_id]) && $pos_acc_trans["s_".$old_sale_id]){
							foreach($pos_acc_trans["s_".$old_sale_id] as $pos_acc_tran){
								$pos_acc_tran = (array) $pos_acc_tran;
								unset($pos_acc_tran['id']);
								$pos_acc_tran['transaction_id'] = $new_sale_id;
								$acc_trans[] = $pos_acc_tran;
							}
						}
						
						if(isset($pos_payments["s_".$old_sale_id]) && $pos_payments["s_".$old_sale_id]){
							foreach($pos_payments["s_".$old_sale_id] as $pos_payment){
								$pos_payment = (array) $pos_payment;
								$old_payment_id = $pos_payment['id'];
								unset($pos_payment['id']);
								$pos_payment['sale_id'] = $new_sale_id;
								if($this->db->insert("payments",$pos_payment)){
									$new_payment_id = $this->db->insert_id();
									if(isset($pos_acc_pay_trans["p_".$old_payment_id]) && $pos_acc_pay_trans["p_".$old_payment_id]){
										foreach($pos_acc_pay_trans["p_".$old_payment_id] as $pos_acc_pay_tran){
											$pos_acc_pay_tran = (array) $pos_acc_pay_tran;
											unset($pos_acc_pay_tran['id']);
											$pos_acc_pay_tran['transaction_id'] = $new_payment_id;
											$acc_trans[] = $pos_acc_pay_tran;
										}
									}
								}
							}
						}
					}
				}
			}
			
			if($sale_items){
				$this->db->insert_batch("sale_items",$sale_items);
			}
			if($stockmoves){
				$this->db->insert_batch("stockmoves",$stockmoves);
			}
			if($acc_trans){
				$this->db->insert_batch("acc_tran",$acc_trans);
			}
		}
		
		if($pos_registers){
			foreach($pos_registers as $pos_register){
				if($pos_register){
					$old_register_id = $pos_register->id;
					unset($pos_register->id);
					$pos_register = (array) $pos_register;
					$pos_register['pushed'] = 1;
					if($this->db->insert("pos_register",$pos_register)){
						$pushed_reg_ids[] = $old_register_id;
						$new_register_id = $this->db->insert_id();
						if(isset($pos_register_items["r_".$old_register_id]) && $pos_register_items["r_".$old_register_id]){
							foreach($pos_register_items["r_".$old_register_id] as $pos_register_item){
								$pos_register_item = (array) $pos_register_item;
								unset($pos_register_item['id']);
								$pos_register_item['register_id'] = $new_register_id;
								$register_items[] = $pos_register_item;
							}
						}
					}
				}
			}
			if($register_items){
				$this->db->insert_batch("pos_register_items",$register_items);
			}
		}
		if($pushed_ids || $pushed_reg_ids){
			return array("pushed_ids"=>$pushed_ids,"pushed_reg_ids"=>$pushed_reg_ids);
		}else{
			return false;
		}
		
	}
	
	
	//===============================local site===================================//
	function pullData($data = false, $stockmoves = false, $unpush_stockmoves = false){
		if($data && $this->db->multi_query($data)){
			if($stockmoves){
				$this->db->multi_query($stockmoves->structure);
				$this->db->insert_batch("stockmoves",$stockmoves->data);
				if($unpush_stockmoves){
					$this->db->insert_batch("stockmoves",$unpush_stockmoves);
				}
				$this->triggerStructure();
			}
			return true;
		}
		return false;
	}
	
	function triggerStructure(){
		$stockmove_insert = "DROP TRIGGER IF EXISTS `insertStock`; \n\n CREATE TRIGGER `insertStock` AFTER INSERT ON ".$this->db->dbprefix('stockmoves')." FOR EACH ROW BEGIN SET @total_qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = NEW.product_id ); UPDATE ".$this->db->dbprefix('products')." SET quantity = @total_qty WHERE id = NEW.product_id; SET @qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id ); IF EXISTS ( SELECT product_id FROM ".$this->db->dbprefix('warehouses_products')." WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id ) THEN UPDATE ".$this->db->dbprefix('warehouses_products')." SET quantity = @qty WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id; ELSE INSERT INTO ".$this->db->dbprefix('warehouses_products')." ( product_id, warehouse_id, quantity ) VALUES ( NEW.product_id, NEW.warehouse_id, @qty ); END IF; IF ( NEW.option_id > 0 ) THEN SET @opt_qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND option_id = NEW.option_id ); IF ( SELECT product_id FROM ".$this->db->dbprefix('warehouses_products')."_variants WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND option_id = NEW.option_id ) THEN UPDATE ".$this->db->dbprefix('warehouses_products')."_variants SET quantity = @opt_qty WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND option_id = NEW.option_id; ELSE INSERT INTO ".$this->db->dbprefix('warehouses_products')."_variants ( product_id, warehouse_id, quantity, option_id ) VALUES ( NEW.product_id, NEW.warehouse_id, @opt_qty, NEW.option_id ); END IF; END IF; IF ( NEW.serial_no <> '' ) THEN IF EXISTS ( SELECT product_id FROM ".$this->db->dbprefix('product_serials')." WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND serial = NEW.serial_no ) THEN SET @serial_qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND serial_no = NEW.serial_no ); IF ( @serial_qty < 0 ) THEN SET @inaction = 1; ELSE SET @inaction = 0; END IF; UPDATE ".$this->db->dbprefix('product_serials')." SET inactive = @inaction WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND serial = NEW.serial_no; END IF; END IF; END;\n\n"; 
		$stockmove_delete = "DROP TRIGGER IF EXISTS `deleteStock`; \n\n CREATE TRIGGER `deleteStock` AFTER DELETE ON ".$this->db->dbprefix('stockmoves')." FOR EACH ROW BEGIN SET @total_qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = OLD.product_id ); UPDATE ".$this->db->dbprefix('products')." SET quantity = @total_qty WHERE id = OLD.product_id; SET @qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id ); UPDATE ".$this->db->dbprefix('warehouses_products')." SET quantity = @qty WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id; IF ( OLD.option_id > 0 ) THEN SET @opt_qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id AND option_id = OLD.option_id ); UPDATE ".$this->db->dbprefix('warehouses_products')."_variants SET quantity = @opt_qty WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id AND option_id = OLD.option_id; END IF; IF ( OLD.serial_no <> '' ) THEN IF EXISTS ( SELECT product_id FROM ".$this->db->dbprefix('product_serials')." WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id AND serial = OLD.serial_no ) THEN SET @old_serial_qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id AND serial_no = OLD.serial_no ); IF ( @old_serial_qty < 0 ) THEN SET @inaction = 1; ELSE SET @inaction = 0; END IF; UPDATE ".$this->db->dbprefix('product_serials')." SET inactive = @inaction WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id AND serial = OLD.serial_no; END IF; END IF; END;\n\n";
		$stockmove_update = "DROP TRIGGER IF EXISTS `updateStock`; \n\n CREATE TRIGGER `updateStock` AFTER UPDATE ON ".$this->db->dbprefix('stockmoves')." FOR EACH ROW BEGIN SET @total_qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = NEW.product_id ); UPDATE ".$this->db->dbprefix('products')." SET quantity = @total_qty WHERE id = NEW.product_id; SET @qty_new = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id ); IF EXISTS ( SELECT product_id FROM ".$this->db->dbprefix('warehouses_products')." WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id ) THEN UPDATE ".$this->db->dbprefix('warehouses_products')." SET quantity = @qty_new WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id; ELSE INSERT INTO ".$this->db->dbprefix('warehouses_products')." ( product_id, warehouse_id, quantity ) VALUES ( NEW.product_id, NEW.warehouse_id, @qty_new ); END IF; IF ( NEW.option_id > 0 ) THEN SET @opt_qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND option_id = NEW.option_id ); IF ( SELECT product_id FROM ".$this->db->dbprefix('warehouses_products')."_variants WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND option_id = NEW.option_id ) THEN UPDATE ".$this->db->dbprefix('warehouses_products')."_variants SET quantity = @opt_qty WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND option_id = NEW.option_id; ELSE INSERT INTO ".$this->db->dbprefix('warehouses_products')."_variants ( product_id, warehouse_id, quantity, option_id ) VALUES ( NEW.product_id, NEW.warehouse_id, @opt_qty, NEW.option_id ); END IF; END IF; SET @total_qty_old = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = OLD.product_id ); UPDATE ".$this->db->dbprefix('products')." SET quantity = @total_qty_old WHERE id = OLD.product_id; SET @qty_old = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id ); UPDATE ".$this->db->dbprefix('warehouses_products')." SET quantity = @qty_old WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id; IF ( OLD.option_id > 0 ) THEN SET @opt_qty_old = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id AND option_id = OLD.option_id ); UPDATE ".$this->db->dbprefix('warehouses_products')."_variants SET quantity = @opt_qty_old WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id AND option_id = OLD.option_id; END IF; IF ( NEW.serial_no <> '' ) THEN IF EXISTS ( SELECT product_id FROM ".$this->db->dbprefix('product_serials')." WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND serial = NEW.serial_no ) THEN SET @serial_qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND serial_no = NEW.serial_no ); IF ( @serial_qty < 0 ) THEN SET @inaction = 1; ELSE SET @inaction = 0; END IF; UPDATE ".$this->db->dbprefix('product_serials')." SET inactive = @inaction WHERE product_id = NEW.product_id AND warehouse_id = NEW.warehouse_id AND serial = NEW.serial_no; END IF; END IF; IF ( OLD.serial_no <> '' ) THEN IF EXISTS ( SELECT product_id FROM ".$this->db->dbprefix('product_serials')." WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id AND serial = OLD.serial_no ) THEN SET @old_serial_qty = ( SELECT SUM( quantity ) AS qoh FROM ".$this->db->dbprefix('stockmoves')." WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id AND serial_no = OLD.serial_no ); IF ( @old_serial_qty < 0 ) THEN SET @inaction = 1; ELSE SET @inaction = 0; END IF; UPDATE ".$this->db->dbprefix('product_serials')." SET inactive = @inaction WHERE product_id = OLD.product_id AND warehouse_id = OLD.warehouse_id AND serial = OLD.serial_no; END IF; END IF; END;\n\n";
		$stockmove_trigger = $stockmove_insert.$stockmove_delete.$stockmove_update;
		$this->db->multi_query($stockmove_trigger);
	}
	
	function getPOS(){
		$this->db->where("sales.pushed",0);
		$q = $this->db->get_where("sales",array("pos"=>1));
		if ($q->num_rows() > 0) {
            foreach (($q->result_array()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	function getPOSItems(){
		$this->db->select("sale_items.*");
		$this->db->join("sales","sales.id = sale_items.sale_id","INNER");
		$this->db->where("sales.pos",1);
		$this->db->where("sales.pushed",0);
		$q = $this->db->get("sale_items");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
				$data["s_".$row->sale_id][] = $row;
            }
            return $data;
        }
		return false;
	}
	function getPOSStocmkoves(){
		$this->db->select("stockmoves.*");
		$this->db->join("sales","sales.id = stockmoves.transaction_id","INNER");
		$this->db->where("stockmoves.transaction","Sale");
		$this->db->where("sales.pos",1);
		$this->db->where("sales.pushed",0);
		$q = $this->db->get("stockmoves");
		
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
				$data["s_".$row->transaction_id][] = $row;
            }
            return $data;
        }
		return false;
	}
	function getPOSAccTrans(){
		$this->db->select("acc_tran.*");
		$this->db->join("sales","sales.id = acc_tran.transaction_id","INNER");
		$this->db->where("acc_tran.transaction","Sale");
		$this->db->where("sales.pos",1);
		$this->db->where("sales.pushed",0);
		$q = $this->db->get("acc_tran");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
				$data["s_".$row->transaction_id][] = $row;
            }
            return $data;
        }
		return false;
	}
	function getPOSPayments(){
		$this->db->select("payments.*");
		$this->db->join("sales","sales.id = payments.sale_id","INNER");
		$this->db->where("sales.pos",1);
		$this->db->where("sales.pushed",0);
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
				$data["s_".$row->sale_id][] = $row;
            }
            return $data;
        }
		return false;
	}
	function getPOSPaymentAccs(){
		$this->db->select("acc_tran.*");
		$this->db->join("payments","acc_tran.transaction_id = payments.id","INNER");
		$this->db->join("sales","sales.id = payments.sale_id","INNER");
		$this->db->where("acc_tran.transaction","Payment");
		$this->db->where("sales.pos",1);
		$this->db->where("sales.pushed",0);
		$q = $this->db->get("acc_tran");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
				$data["p_".$row->transaction_id][] = $row;
            }
            return $data;
        }
		return false;
	}
	
	function getPOSRegisters(){
		$this->db->where("status","close");
		$this->db->where("pushed",0);
		$q = $this->db->get("pos_register");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
				$data[] = $row;
            }
            return $data;
        }
		return false;
	}
	function getPOSRegisterItems(){
		$this->db->select("pos_register_items.*");
		$this->db->join("pos_register","pos_register.id = pos_register_items.register_id","INNER");
		$this->db->where("pos_register.pushed",0);
		$this->db->where("pos_register.status","close");
		$q = $this->db->get("pos_register_items");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
				$data["r_".$row->register_id][] = $row;
            }
            return $data;
        }
		return false;
	}
	
	function updatePushed($data = false){
		if($data->pushed_ids || $data->pushed_reg_ids){
			if($data->pushed_ids){
				$this->db->where_in("id",$data->pushed_ids);
				$this->db->update("sales",array("pushed"=>1));
			}
			if($data->pushed_reg_ids){
				$this->db->where_in("id",$data->pushed_reg_ids);
				$this->db->update("pos_register",array("pushed"=>1));
			}
			return true;
		}
		
		return false;
	}
	
	function getUnPushPOSStocmkoves(){
		$this->db->select("stockmoves.*");
		$this->db->join("sales","sales.id = stockmoves.transaction_id","INNER");
		$this->db->where("stockmoves.transaction","Sale");
		$this->db->where("sales.pos",1);
		$this->db->where("sales.pushed",0);
		$q = $this->db->get("stockmoves");
		
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
				unset($row->id);
				$data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	
	//=======================Attendance===================//
		//==client===//
			public function getDevices($active = false){
				if($active){
					$this->db->where('att_devices.inactive',0)->or_where('att_devices.inactive IS NULL');
				}
				$q = $this->db->get('att_devices');
				if($q->num_rows() > 0){
					foreach($q->result() as $row){
						$data[] = $row;
					}
					return $data;
				}
				return false;
			}
			public function checkRawCheckInOut($finger_id = false, $check_time_int = false){
				$q = $this->db->get_where('att_check_in_out_raw',array('finger_id'=>$finger_id,'check_time_int'=>$check_time_int),1);
				if($q->num_rows() > 0){
					return true;
				}
				return false;
				
			}
			public function addRawCheckInOut($data,$clear = false){
				if($data){
					if($this->db->insert_batch('att_check_in_out_raw',$data)){
						if($clear){
							$zk = new ZKLib($clear['ip_address'], $clear['port']);
							if($zk->connect()){
								$attendances = count($zk->getAttendance());
								if($attendances==$clear['count_attendance']){
									$zk->clearAttendance();
								}
							}
						}
					}
					return true;
				}
				return false;
			}
			function getRawCheckInOut($pushed = false){
				if($pushed){
					$this->db->where("att_check_in_out_raw.pushed",0);
				}
				$q = $this->db->get("att_check_in_out_raw");
				if ($q->num_rows() > 0) {
					foreach (($q->result_array()) as $row) {
						$data[] = $row;
					}
					return $data;
				}
				return false;
			}
			function updateAttPushed($data = false){
				if($data->pushed_ids){
					if($data->pushed_ids){
						$this->db->where_in("id",$data->pushed_ids);
						$this->db->update("att_check_in_out_raw",array("pushed"=>1));
					}
					return true;
				}
				return false;
			}
		//===end client===//
		
		//==server===//
			public function getEmployeeByFingerID($finger_id = false)
			{
				$q = $this->db->get_where("hr_employees", array("finger_id" => $finger_id));
				if($q->num_rows() > 0){
					return $q->row();
				}
				return false;
			}
			
			function addCheckInOut($raw_check_in_outs = false){
				$data = false;
				$pushed_ids = false;
				if($raw_check_in_outs){
					foreach($raw_check_in_outs as $raw_check_in_out){
						$employee_info = $this->getEmployeeByFingerID($raw_check_in_out->finger_id);
						if($employee_info){
							$employee_id = (int) $employee_info->id;
							$data[] = array(
										'employee_id'=>$employee_id,
										'check_time'=>$raw_check_in_out->check_time,
										'check_time_int'=>$raw_check_in_out->check_time_int,
										'device_id'=>$raw_check_in_out->device_id,
									);
							$pushed_ids[] = $raw_check_in_out->id;		
						}
					}
					if($data && $this->db->insert_batch("att_check_in_out",$data)){
						foreach($data as $row){
							$this->attendances_model->generateAttendance($row['employee_id'],$row['check_time'],$row['check_time']);
						}
						return array("pushed_ids"=>$pushed_ids);
					}else{
						return false;
					}
				}
			}
		//===end server===//	
	//=======================End Attendance===================//

}
