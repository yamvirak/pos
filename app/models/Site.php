<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Model
{

  public function __construct()
	{
		parent::__construct();
	}

    public function get_total_qty_alerts() {
        $allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in('products.category_id',$allow_category);
		}
		$this->db->where('quantity < alert_quantity', NULL, FALSE)
        ->where('track_quantity', 1)
        ->where('products.type !=','service_rental')
        ->where('alert_quantity >', 0);
        return $this->db->count_all_results('products');
    }	
	public function get_total_qty_alert_warehouses() {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in('products.category_id',$allow_category);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('warehouses.id',json_decode($this->session->userdata('warehouse_id')));
		}
		$this->db->select('count('.$this->db->dbprefix("warehouses_products").'.product_id) as alert_qty, warehouses.name as warehouse,warehouses.id')
					->join('products','products.id = warehouses_products.product_id','inner')
					->join('warehouses','warehouses.id = warehouses_products.warehouse_id','inner')
					->where('warehouses_products.alert_quantity >',0)
					->where('products.track_quantity',1)
                    ->where('products.type !=','service_rental')
					->where('warehouses_products.alert_quantity >= warehouses_products.quantity')
					->group_by('warehouses_products.warehouse_id');
        $q = $this->db->get('warehouses_products');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
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

    public function get_expiring_qty_alerts() {
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->join("products","products.id = stockmoves.product_id","inner");
			$this->db->where_in('products.category_id',$allow_category);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('stockmoves.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
        $date = date('Y-m-d', strtotime('+3 months'));
        $this->db->select('SUM('.$this->db->dbprefix("stockmoves").'.quantity) as alert_num')
        ->where('stockmoves.expiry !=', NULL)
		->where('stockmoves.expiry !=', '0000-00-00')
        ->where('stockmoves.expiry <', $date);
        $q = $this->db->get('stockmoves');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->alert_num;
        }
        return FALSE;
    }

    public function get_setting() {
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	function pos_setting()
    {
        $q = $this->db->get('pos_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getDateFormat($id = false) {
        $q = $this->db->get_where('date_format', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function getFloor(){
        $q = $this->db->get('suspended_floors');
        if($q->num_rows () > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllCompanies($group_name = false) {
		if ($group_name == 'biller' && !$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('companies.id',$this->session->userdata('biller_id'));
		}
        $q = $this->db->get_where('companies', array('group_name' => $group_name));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyByID($id = false) {
        $q = $this->db->get_where('companies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getCompanyByCode($code = false) {
        $q = $this->db->get_where('companies', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCustomerGroupByID($id = false) {
        $q = $this->db->get_where('customer_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getUser($id = NULL) {
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCardType($id = NULL) {
        $q = $this->db->get_where('rental_card_types', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getNationalityType($id = NULL) {
        $q = $this->db->get_where('nationality_type', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getUserByID($id = false){
		$q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

    public function getProductByID($id = false) {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getProducts(){
		$this->db->where("IFNULL(inactive,0)",0);
		$q = $this->db->get("products");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

    public function getAllCurrencies() {
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getCurrencies() {
        $q = $this->db->where("code !=","USD")->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCurrencyByCode($code = false) {
        $q = $this->db->get_where('currencies', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllTaxValidations() {
        $q = $this->db->get('tax_validations');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllTaxRates() {
        $q = $this->db->get('tax_rates');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTaxRateByID($id = false) {
        $q = $this->db->get_where('tax_rates', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
    public function getTaxValidationByID($id = false) {
        $q = $this->db->get_where('tax_validations', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
	public function getWarehouses() {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('warehouses.id',json_decode($this->session->userdata('warehouse_id')));
		}
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getBrands(){
		$q = $this->db->get('brands');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

    public function getAllWarehouses() {
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseByID($id = false) {
        $q = $this->db->get_where('warehouses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllCategories() {
		$allow_category = $this->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("categories.id",$allow_category);
		}
        $this->db->where('IFNULL(parent_id,0)', 0)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getAllTables() {
        $q = $this->db->where("status","active")->get("suspended_tables");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getAllProjects() {
        $q = $this->db->get("projects");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getAllTags() {
        $q = $this->db->get("suspended_tags");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSubCategories($parent_id = false) {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCategoryByID($id = false) {
        $q = $this->db->get_where('categories', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getGiftCardByID($id = false) {
        $q = $this->db->get_where('gift_cards', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getGiftCardByNO($no = false) {
        $q = $this->db->get_where('gift_cards', array('card_no' => $no), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getMemberCardByID($id = false) {
        $q = $this->db->get_where('member_cards', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getMemberCardByCustomerID($id = false) {
        $q = $this->db->get_where('member_cards', array('customer_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateInvoiceStatus() {
        $date = date('Y-m-d');
        $q = $this->db->get_where('invoices', array('status' => 'unpaid'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if ($row->due_date < $date) {
                    $this->db->update('invoices', array('status' => 'due'), array('id' => $row->id));
                }
            }
            $this->db->update('settings', array('update' => $date), array('setting_id' => '1'));
            return true;
        }
    }

    public function modal_js() {
        return '<script type="text/javascript">' . file_get_contents($this->data['assets'] . 'js/modal.js') . '</script>';
    }

    public function getReference($field = false,$bill_id = false) {
        $q = $this->db->get_where('order_ref', array('bill_id' => $bill_id), 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            switch ($field) {
                case 'so':
                    $prefix = $this->Settings->sales_prefix;
                    break;
				case 'sao':
                    $prefix = $this->Settings->sale_order_prefix;
                    break;
                case 'pos':
                    $prefix = $this->Settings->pos_prefix;
                    break;
                case 'qu':
                    $prefix = $this->Settings->quote_prefix;
                    break;
                case 'po':
                    $prefix = $this->Settings->purchase_prefix;
                    break;
				case 'puo':
                    $prefix = $this->Settings->purchase_order_prefix;
                    break;
				case 'pr':
                    $prefix = $this->Settings->purchase_request_prefix;
                    break;
                case 'to':
                    $prefix = $this->Settings->transfer_prefix;
                    break;
                case 'do':
                    $prefix = $this->Settings->delivery_prefix;
                    break;
                case 'pay':
                    $prefix = $this->Settings->payment_prefix;
                    break;
                case 'ppay':
                    $prefix = $this->Settings->ppayment_prefix;
                    break;
                case 'ex':
                    $prefix = $this->Settings->expense_prefix;
                    break;
                case 're':
                    $prefix = $this->Settings->return_prefix;
                    break;
				case 'rec':
                    $prefix = $this->Settings->receive_prefix;
                    break;
                case 'rep':
                    $prefix = $this->Settings->returnp_prefix;
                    break;
                case 'qa':
                    $prefix = $this->Settings->qa_prefix;
                    break;
				case 'con':
                    $prefix = $this->Settings->convert_prefix;
					break;
				case 'customer_opening':
                    $prefix = $this->Settings->cus_opening_prefix;
					break;
				case 'supplier_opening':
                    $prefix = $this->Settings->sup_opening_prefix;
					break;
				case 'assign':
                    $prefix = $this->Settings->assign_prefix;
					break;
				case 'us':
                    $prefix = $this->Settings->us_prefix;
					break;
				case 'cou':
                    $prefix = $this->Settings->count_stock_prefix;
					break;
				case 'ca':
                    $prefix = $this->Settings->ca_prefix;
					break;
				case 'jn':
                    $prefix = $this->Settings->jn_prefix;
					break;
				case 'inst':
                    $prefix = $this->Settings->installment_prefix;
                    break;
				case 'ln':
                    $prefix = $this->Settings->loan_prefix;
                    break;
				case 'bl':
                    $prefix = $this->Settings->bill_prefix;
                    break;
				case 'pw':
                    $prefix = $this->Settings->pawn_prefix;
                    break;	
				case 'pwr':
                    $prefix = $this->Settings->pawn_return_prefix;
                    break;	
				case 'pwp':
                    $prefix = $this->Settings->pawn_purchase_prefix;
                    break;	
				case 'pwpr':
                    $prefix = $this->Settings->pawn_receive_prefix;
                    break;
				case 'pwps':
                    $prefix = $this->Settings->pawn_send_prefix;
                    break;
				case 'cv':
                    $prefix = $this->Settings->cv_prefix;
                    break;
				case 'cs':
                    $prefix = $this->Settings->customer_stock_prefix;
                    break;
				case 'tl':
                    $prefix = $this->Settings->tl_prefix;
                    break;	
				case 'rus':
                    $prefix = $this->Settings->rus_prefix;
                    break;
                case 'tax_so':
                    $prefix = $this->Settings->sale_tax_prefix;
                    break;  
				case 'repair':
                    $prefix = $this->Settings->repair_prefix;
                    break;
				case 'br':
                    $prefix = $this->Settings->br_prefix;
                    break;	
				case 'io':
                    $prefix = $this->Settings->io_prefix;
                    break;	
				case 'fuel':
                    $prefix = $this->Settings->fuel_prefix;
                    break;
				case 'app':
                    $prefix = $this->Settings->app_prefix;
                    break;
				case 'sav':
                    $prefix = $this->Settings->sav_prefix;
                    break;
				case 'sav_tr':
                    $prefix = $this->Settings->sav_tr_prefix;
                    break;
				case 'csm':
                    $prefix = $this->Settings->csm_prefix;
                    break;
				case 'rcsm':
                    $prefix = $this->Settings->rcsm_prefix;
                    break;
				case 'ren':
                    $prefix = $this->Settings->rental_prefix;
					break;
				case 'check':
					$prefix = $this->Settings->check_prefix;
					break;
				case 'cdn':
					$prefix = $this->Settings->cdn_prefix;
					break;
				case 'csale':
					$prefix = $this->Settings->csale_prefix;
					break;	
				case 'cfuel':
                    $prefix = $this->Settings->cfuel_prefix;
                    break;	
				case 'cerror':
					$prefix = $this->Settings->cer_prefix;
					break;	
				case 'cmw':
					$prefix = $this->Settings->cmw_prefix;
					break;
				case 'cmission':
					$prefix = $this->Settings->cms_prefix;
					break;
				case 'cfe':
					$prefix = $this->Settings->cfe_prefix;
					break;
				case 'ccms':
					$prefix = $this->Settings->ccms_prefix;
					break;
				case 'cabsent':
					$prefix = $this->Settings->cabsent_prefix;
					break;
				case 'rp':
					$prefix = $this->Settings->rp_prefix;
					break;
				case 'htreament':
					$prefix = "TM";
					break;	
                default:
                    $prefix = '';
            }

			if($this->Settings->reference_reset==1){
				if(date("Y",strtotime($ref->date)) !== date("Y")){
					$order_ref = array();
					foreach($ref as $index => $value){
						$order_ref[$index] = 1;
					}
					unset($order_ref['prefix']);
					unset($order_ref['bill_id']);
					unset($order_ref['ref_id']);
					unset($order_ref['bill_prefix']);
					unset($order_ref['supplier']);
					unset($order_ref['customer']);
					$order_ref['date'] = date("Y-m-d");
					$this->db->update('order_ref', $order_ref, array('bill_id' => $bill_id));
					$ref->{$field} = 1;
					
				}
			}else if($this->Settings->reference_reset==2){
				if(date("Y-m",strtotime($ref->date)) !== date("Y-m")){
					$order_ref = array();
					foreach($ref as $index => $value){
						$order_ref[$index] = 1;
					}
					unset($order_ref['prefix']);
					unset($order_ref['bill_id']);
					unset($order_ref['ref_id']);
					unset($order_ref['bill_prefix']);
					unset($order_ref['supplier']);
					unset($order_ref['customer']);
					$order_ref['date'] = date("Y-m-d");
					$this->db->update('order_ref', $order_ref, array('bill_id' => $bill_id));
					$ref->{$field} = 1;
				}
			}
			
            $ref_no = (!empty($prefix)) ? $prefix . '/' : '';
            if ($this->Settings->reference_format == 1) {
                $ref_no .= date('Y') . "/" . sprintf("%04s", $ref->{$field});
            } elseif ($this->Settings->reference_format == 2) {
                $ref_no .= date('Y') . "/" . date('m') . "/" . sprintf("%04s", $ref->{$field});
            } elseif ($this->Settings->reference_format == 3) {
                $ref_no .= sprintf("%04s", $ref->{$field});
            } else {
                $ref_no .= $this->getRandomReference();
            }

			if($bill_id){
				$q = $this->db->get_where('order_ref', array('bill_id' => $bill_id), 1);
				if ($q->num_rows() > 0) {
					if($q->row()->bill_prefix!=''){
						$ref_no =$q->row()->bill_prefix.'/'.$ref_no;
					}
				}
				$this->updateReference($field,$bill_id);
			}
            return $ref_no;
        }
        return FALSE;
    }

	public function updateReference($field = false,$bill_id = false) {
        $q = $this->db->get_where('order_ref', array('bill_id' => $bill_id), 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
			$this->db->update('order_ref', array($field => $ref->{$field} + 1), array('bill_id' => $bill_id));
            return TRUE;
        }
        return FALSE;
    }

    public function getRandomReferenceCustomer($len = 4) {
        $ref_no = '';
        for ($i = 0; $i < $len; $i++) {
            $ref_no .= mt_rand(0, 4);
        }

         if ($this->Settings->customer_prefix) {
                $date .= date('Y') . "" . sprintf("%04s");
                $references = 'CUS'.$ref_no;
              
            }

        return $references;
    }

    public function getRandomReference($len = 12) {
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result .= mt_rand(0, 9);
        }

        if ($this->getSaleByReference($result)) {
            $this->getRandomReference();
        }

        return $result;
    }

    public function getSaleByReference($ref = false) {
        $this->db->like('reference_no', $ref, 'before');
        $q = $this->db->get('sales', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function checkPermissions() {
        $q = $this->db->get_where('permissions', array('group_id' => $this->session->userdata('group_id')), 1);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return FALSE;
    }

    public function getNotifications() {
        $date = date('Y-m-d H:i:s', time());
        $this->db->where("from_date <=", $date);
        $this->db->where("till_date >=", $date);
        if (!$this->Owner) {
            if ($this->Supplier) {
                $this->db->where('scope', 4);
            } elseif ($this->Customer) {
                $this->db->where('scope', 1)->or_where('scope', 3);
            } elseif (!$this->Customer && !$this->Supplier) {
                $this->db->where('scope', 2)->or_where('scope', 3);
            }
        }
        $q = $this->db->get("notifications");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getUpcomingEvents() {
        $dt = date('Y-m-d h:i');
        $this->db->where('start >=', $dt)->order_by('start');
        if ($this->Settings->restrict_calendar) {
            $this->db->where('user_id', $this->session->userdata('user_id'));
        }

        $q = $this->db->get('calendar');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUserGroup($user_id = false) {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $group_id = $this->getUserGroupID($user_id);
        $q = $this->db->get_where('groups', array('id' => $group_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getUserGroupID($user_id = false) {
        $user = $this->getUser($user_id);
		if($user){
			return $user->group_id;
		}
       
    }

    public function getWarehouseProductsVariants($option_id = false, $warehouse_id = NULL) {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getProductOptions($pid = false){
        $q = $this->db->get_where('product_variants', array('product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getWarehouseProductsOptionByID($product_id = false,$warehouse_id = false,$option_id = false) {
        $q = $this->db->get_where('warehouses_products_variants', array('product_id'=>$product_id, 'option_id' => $option_id,'warehouse_id' => $warehouse_id),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchasedItem($where_clause = false) {
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $q = $this->db->get_where('purchase_items', $where_clause);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function syncVariantQty($variant_id = false, $warehouse_id = false, $product_id = NULL) {
        $balance_qty = $this->getBalanceVariantQuantity($variant_id);
        $wh_balance_qty = $this->getBalanceVariantQuantity($variant_id, $warehouse_id);
        if ($this->db->update('product_variants', array('quantity' => $balance_qty), array('id' => $variant_id))) {
            if ($this->getWarehouseProductsVariants($variant_id, $warehouse_id)) {
                $this->db->update('warehouses_products_variants', array('quantity' => $wh_balance_qty), array('option_id' => $variant_id, 'warehouse_id' => $warehouse_id));
            } else {
                if($wh_balance_qty) {
                    $this->db->insert('warehouses_products_variants', array('quantity' => $wh_balance_qty, 'option_id' => $variant_id, 'warehouse_id' => $warehouse_id, 'product_id' => $product_id));
                }
            }
            return TRUE;
        }
        return FALSE;
    }

    public function getWarehouseProducts($product_id = false, $warehouse_id = NULL) {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncProductQty($product_id = false, $warehouse_id = false) {
        $balance_qty = $this->getBalanceQuantity($product_id);
        $wh_balance_qty = $this->getBalanceQuantity($product_id, $warehouse_id);
        if ($this->db->update('products', array('quantity' => $balance_qty), array('id' => $product_id))) {
            if ($this->getWarehouseProducts($product_id, $warehouse_id)) {
                $this->db->update('warehouses_products', array('quantity' => $wh_balance_qty), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id));
            } else {
                if( ! $wh_balance_qty) { $wh_balance_qty = 0; }
                $product = $this->getProductByID($product_id);
                $this->db->insert('warehouses_products', array('quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'avg_cost' => $product->cost));
            }
            return TRUE;
        }
        return FALSE;
    }

    public function getSaleByID($id = false) {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSalePayments($sale_id = false) {
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function SunfixLicense(){
		// WARNING: This system is protected by copyright(SUNFIX CONSULTING CO., LTD). Reversing code or decode this system is strictly prohibited //
		echo "Please contact to administrator <br/>";
		echo "Website : <a href='http://www.sunfixconsulting.com' target=_blank>www.sunfixconsulting.com</a><br/>";
		echo "Phone : 093 471 106<br/>";
		exit;
	}

    public function syncSalePayments($id = false) {
        $sale = $this->getSaleByID($id);
        $ar_payment = $this->getARPaymentByID($id);
        $payments = $this->getSalePayments($id);
        $paid = 0;
		$grand_total = $sale->grand_total+$sale->rounding;
		if($payments){
			foreach ($payments as $payment) {
				$paid += $payment->amount + $payment->discount;
			}
		}
        $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
        if ($this->cus->formatDecimal($grand_total) == $this->cus->formatDecimal($paid)) {
            $payment_status = 'paid';
        } elseif ($paid != 0) {
            $payment_status = 'partial';
        } else {
            $payment_status = 'pending';
        }
        if ($this->db->update('sales', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getARPaymentByID($id = false)
    {
        $q = $this->db->where('rec_id', $id)->get('ar_payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    

	public function getExpenseByID($id = false) {
        $q = $this->db->get_where('expenses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function syncExpensePayments($id = false) {
        $expense = $this->getExpenseByID($id);
        $payments = $this->getExpensePayments($id);
        $paid = 0;
        foreach ($payments as $payment) {
            $paid += $payment->amount + $payment->discount;
        }

        $payment_status = $paid <= 0 ? 'pending' : $expense->payment_status;
        if ($this->cus->formatDecimal($expense->grand_total) > $this->cus->formatDecimal($paid) && $paid > 0) {
            $payment_status = 'partial';
        } elseif ($this->cus->formatDecimal($expense->grand_total) <= $this->cus->formatDecimal($paid)) {
            $payment_status = 'paid';
        }
        if ($this->db->update('expenses', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getPurchasePayments($purchase_id = false) {
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getExpensePayments($expense_id = false) {
        $q = $this->db->get_where('payments', array('expense_id' => $expense_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncPurchasePayments($id = false) {
        $purchase = $this->getPurchaseByID($id);
        $payments = $this->getPurchasePayments($id);
        $paid = 0;
        foreach ($payments as $payment) {
            $paid += $payment->amount + $payment->discount;
        }

        $payment_status = $paid <= 0 ? 'pending' : $purchase->payment_status;
        if ($this->cus->formatDecimal($purchase->grand_total) > $this->cus->formatDecimal($paid) && $paid > 0) {
            $payment_status = 'partial';
        } elseif ($this->cus->formatDecimal($purchase->grand_total) <= $this->cus->formatDecimal($paid)) {
            $payment_status = 'paid';
        }

        if ($this->db->update('purchases', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }

        return FALSE;
    }

    private function getBalanceQuantity($product_id = false, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('product_id', $product_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }
	
	public function getProductQty($product_id = false, $warehouse_id = false){
		if($product_id){
			if($warehouse_id){
				$this->db->where("warehouse_id",$warehouse_id);
			}
			$this->db->where("product_id",$product_id);
			$this->db->select("sum(quantity) as quantity");
			$q = $this->db->get("stockmoves");
			if ($q->num_rows() > 0) {
				return $q->row();
			}
		}
		return false;
	}

    private function getBalanceVariantQuantity($variant_id = false, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('option_id', $variant_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    public function calculateAVCost($product_id = false, $warehouse_id = false, $net_unit_price = false, $unit_price = false, $quantity = false, $product_name = false, $option_id = false, $item_quantity = false) {
        $product = $this->getProductByID($product_id);
        $real_item_qty = $quantity;
        $wp_details = $this->getWarehouseProduct($warehouse_id, $product_id);
        $avg_net_unit_cost = $wp_details ? $wp_details->avg_cost : $product->cost;
        $avg_unit_cost = $wp_details ? $wp_details->avg_cost : $product->cost;
        if ($pis = $this->getStockmoves($product_id, $warehouse_id, $option_id)) {
            $cost_row = array();
            $quantity = $item_quantity;

			/// Delivery condition
			$delivery_id = $this->input->post('delivery_id');
			$groups_delivery = $this->input->post('groups_delivery');
			if($groups_delivery){
				foreach($groups_delivery as $gd){
					$delivery = $this->db->select("sum(quantity) as quantity")->where(array("delivery_id"=>$gd,"product_id"=>$product_id))->get('delivery_items')->row();
					if($delivery){
						$quantity -= ($delivery->quantity?$delivery->quantity:0);
					}
				}
			}
			else if($delivery_id){
				$delivery = $this->db->select("sum(quantity) as quantity")->where(array("delivery_id"=>$delivery_id,"product_id"=>$product_id))->get('delivery_items')->row();
				if($delivery){
					$quantity -= ($delivery->quantity?$delivery->quantity:0);
				}
			}

            $balance_qty = $quantity;
            foreach ($pis as $pi) {
				if (!empty($pi) && $balance_qty <= $quantity && $quantity > 0) {
                    if ($pi->quantity_balance >= $quantity && $quantity > 0) {
                        $balance_qty = $pi->quantity_balance - $quantity;
                        $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id);
                        $quantity = 0;
                    } elseif ($quantity > 0) {
                        $quantity = $quantity - $pi->quantity_balance;
                        $balance_qty = $quantity;
                        $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id);
                    }
                }
                if (empty($cost_row)) {
                    break;
                }
                $cost[] = $cost_row;
                if ($quantity == 0) {
                    break;
                }
            }
        }
        if ($quantity > 0 && !$this->Settings->overselling) {
            $this->session->set_flashdata('error', sprintf(lang("quantity_out_of_stock_for_%s"), ($pi->product_name ? $pi->product_name : $product_name)));
            redirect($_SERVER["HTTP_REFERER"]);
        } elseif ($quantity > 0) {
            $cost[] = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => NULL, 'overselling' => 1, 'inventory' => 1);
            $cost[] = array('pi_overselling' => 1, 'product_id' => $product_id, 'quantity_balance' => (0 - $quantity), 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
        }
        return $cost;
    }

    public function calculateCost($product_id = false, $warehouse_id = false, $net_unit_price = false, $unit_price = false, $quantity = false, $product_name = false, $option_id = false, $item_quantity = false) {
        $pis = $this->getStockmoves($product_id, $warehouse_id, $option_id);
        $real_item_qty = $quantity;
        $quantity = $item_quantity;
        $balance_qty = $quantity;
        foreach ($pis as $pi) {
            $cost_row = NULL;
            if (!empty($pi) && $balance_qty <= $quantity && $quantity > 0) {
                $purchase_unit_cost = $pi->unit_cost ? $pi->unit_cost : ($pi->net_unit_cost + ($pi->item_tax / $pi->quantity));
                if ($pi->quantity_balance >= $quantity && $quantity > 0) {
                    $balance_qty = $pi->quantity_balance - $quantity;
                    $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id);
                    $quantity = 0;
                } elseif ($quantity > 0) {
                    $quantity = $quantity - $pi->quantity_balance;
                    $balance_qty = $quantity;
                    $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id);
                }
            }
            $cost[] = $cost_row;
            if ($quantity == 0) {
                break;
            }
        }
        //if ($quantity > 0) {
		if ($quantity > 0 && !$this->Settings->overselling){
            $this->session->set_flashdata('error', sprintf(lang("quantity_out_of_stock_for_%s"), ($pi->product_name ? $pi->product_name : $product_name)));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        return $cost;
    }
	
	public function getProductBalanceQuantity($product_id = false, $warehouse_id = false, $option_id = false, $transaction = false, $transaction_id = false){
        if ($product_id) {
            $this->db->where('product_id', $product_id);
        }
		if ($product_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
		if ($option_id) {
            $this->db->where('option_id', $option_id);
        }
		if($transaction && $transaction_id){
			$this->db->where('(transaction != "'.$transaction.'" OR (transaction = "'.$transaction.'" AND transaction_id != '.$transaction_id.'))');
		}
		$this->db->select("sum(quantity) as quantity_balance");
		$q = $this->db->get('stockmoves');
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

	public function getStockmoves($product_id = false, $warehouse_id = false, $option_id = false, $transaction = false, $transaction_id = false)
    {
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->select('id, quantity, quantity as quantity_balance');
        $this->db->where('product_id', $product_id)->where('warehouse_id', $warehouse_id);
        if ($option_id) {
            $this->db->where('option_id', $option_id);
        }
		if($transaction && $transaction_id){
			$this->db->where('(transaction != "'.$transaction.'" OR (transaction = "'.$transaction.'" AND transaction_id != '.$transaction_id.'))');
		}

        $this->db->group_by('id');
        $this->db->order_by('date', $orderby);
        $q = $this->db->get('stockmoves');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getAllStockmoves($transaction = false, $transaction_id = false)
    {
        $q = $this->db->get_where('stockmoves', array('transaction' => $transaction, 'transaction_id' => $transaction_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchasedItems($product_id = false, $warehouse_id = false, $option_id = NULL)
    {
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->select('id, quantity, quantity_balance, net_unit_cost, unit_cost, item_tax');
        $this->db->where('product_id', $product_id)->where('warehouse_id', $warehouse_id)->where('quantity_balance !=', 0);
        if ($option_id) {
            $this->db->where('option_id', $option_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $this->db->group_by('id');
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $q = $this->db->get('purchase_items');
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
        $this->db->select('products.id as id, products.code as code, combo_items.quantity as qty, products.name as name, products.type as type, combo_items.unit_price as unit_price, warehouses_products.quantity as quantity')
            ->join('products', 'products.id=combo_items.item_id', 'inner')
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

    public function item_costing($item = false, $pi = NULL)
	{
        $item_quantity = $pi ? $item['aquantity'] : $item['quantity'];
        if (!isset($item['option_id']) || empty($item['option_id']) || $item['option_id'] == 'null') {
            $item['option_id'] = NULL;
        }

        if ($this->Settings->accounting_method != 2 && !$this->Settings->overselling) {

            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $unit = $this->getUnitByID($item['product_unit_id']);
                    $item['net_unit_price'] = $this->convertToBase($unit, $item['net_unit_price']);
                    $item['unit_price'] = $this->convertToBase($unit, $item['unit_price']);
                    $cost = $this->calculateCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->getTaxRateByID($pr->tax_rate);
                            if ($pr->tax_method) {
                                $item_tax = $this->cus->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / (100 + $pr_tax->rate));
                                $net_unit_price = $combo_item->unit_price - $item_tax;
                                $unit_price = $combo_item->unit_price;
                            } else {
                                $item_tax = $this->cus->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / 100);
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price = $combo_item->unit_price + $item_tax;
                            }
                        } else {
                            $net_unit_price = $combo_item->unit_price;
                            $unit_price = $combo_item->unit_price;
                        }
                        if ($pr->type == 'standard') {
                            $cost[] = $this->calculateCost($pr->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $pr->name, NULL, $item_quantity);
                        } else {
                            $cost[] = array(array('date' => date('Y-m-d'), 'product_id' => $pr->id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => ($combo_item->qty * $item['quantity']), 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $combo_item->unit_price, 'sale_unit_price' => $combo_item->unit_price, 'quantity_balance' => NULL, 'inventory' => NULL));
                        }
                    }
                } else {
                    $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
            }

        } else {

            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {

					$cost = $this->calculateAVCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->getTaxRateByID($pr->tax_rate);
                            if ($pr->tax_method) {
                                $item_tax = $this->cus->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / (100 + $pr_tax->rate));
                                $net_unit_price = $combo_item->unit_price - $item_tax;
                                $unit_price = $combo_item->unit_price;
                            } else {
                                $item_tax = $this->cus->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / 100);
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price = $combo_item->unit_price + $item_tax;
                            }
                        } else {
                            $net_unit_price = $combo_item->unit_price;
                            $unit_price = $combo_item->unit_price;
                        }
                        $cost[] = $this->calculateAVCost($combo_item->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $item['product_name'], $item['option_id'], $item_quantity);
                    }
                } else {
                    $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
            }

        }
        return $cost;
    }

    public function costing($items = NULL)
	{
        $citems = array();
        foreach ($items as $item) {
            $option = (isset($item['option_id']) && !empty($item['option_id']) && $item['option_id'] != 'null' && $item['option_id'] != 'false') ? $item['option_id'] : '';
            $pr = $this->getProductByID($item['product_id']);
            $item['option_id'] = $option;
            if ($pr->type == 'standard') {
                if (isset($citems['p' . $item['product_id'] . 'o' . $item['option_id']])) {
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] += $item['quantity'];
                } else {
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']] = $item;
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] = $item['quantity'];
                }
            } elseif ($pr->type == 'combo') {
                $wh = $this->Settings->overselling ? NULL : $item['warehouse_id'];
                $combo_items = $this->getProductComboItems($item['product_id'], $wh);

                foreach ($combo_items as $combo_item) {
                    if ($combo_item->type == 'standard') {
                        if (isset($citems['p' . $combo_item->id . 'o' . $item['option_id']])) {
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']]['aquantity'] += ($combo_item->qty*$item['quantity']);
                        } else {
                            $cpr = $this->getProductByID($combo_item->id);
                            if ($cpr->tax_rate) {
                                $cpr_tax = $this->getTaxRateByID($cpr->tax_rate);
                                if ($cpr->tax_method) {
                                    $item_tax = $this->cus->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / (100 + $cpr_tax->rate));
                                    $net_unit_price = $combo_item->unit_price - $item_tax;
                                    $unit_price = $combo_item->unit_price;
                                } else {
                                    $item_tax = $this->cus->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / 100);
                                    $net_unit_price = $combo_item->unit_price;
                                    $unit_price = $combo_item->unit_price + $item_tax;
                                }
                            } else {
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price = $combo_item->unit_price;
                            }
                            $cproduct = array('product_id' => $combo_item->id, 'product_name' => $cpr->name, 'product_type' => $combo_item->type, 'quantity' => ($combo_item->qty*$item['quantity']), 'net_unit_price' => $net_unit_price, 'unit_price' => $unit_price, 'warehouse_id' => $item['warehouse_id'], 'item_tax' => $item_tax, 'tax_rate_id' => $cpr->tax_rate, 'tax' => ($cpr_tax->type == 1 ? $cpr_tax->rate.'%' : $cpr_tax->rate), 'option_id' => NULL, 'product_unit_id' => $cpr->unit);
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']] = $cproduct;
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']]['aquantity'] = ($combo_item->qty*$item['quantity']);
                        }
                    }
                }
            }
        }
        $cost = array();
        foreach ($citems as $item) {
            $item['aquantity'] = $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'];
            $cost[] = $this->item_costing($item, TRUE);
        }
        return $cost;
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

    public function getAllSaleItems($sale_id = false) {
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllPurchaseItems($purchase_id = false) {
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncPurchaseItems($data = array()) {
        if (!empty($data)) {
            foreach ($data as $items) {
                foreach ($items as $item) {
                    if (isset($item['pi_overselling'])) {
                        unset($item['pi_overselling']);
                        $option_id = (isset($item['option_id']) && !empty($item['option_id'])) ? $item['option_id'] : NULL;
                        $clause = array('purchase_id' => NULL, 'transfer_id' => NULL, 'product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'option_id' => $option_id);
                        if ($pi = $this->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance + $item['quantity_balance'];
                            $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                        } else {
                            $clause['quantity'] = 0;
                            $clause['item_tax'] = 0;
                            $clause['quantity_balance'] = $item['quantity_balance'];
                            $clause['status'] = 'received';
                            $this->db->insert('purchase_items', $clause);
                        }
                    } else {
                        if ($item['inventory']) {
                            $this->db->update('purchase_items', array('quantity_balance' => $item['quantity_balance']), array('id' => $item['purchase_item_id']));
                        }
                    }
                }
            }
            return TRUE;
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

	public function getAllProduct(){
		$q = $this->db->get('products');
		if ($q->num_rows() > 0) {
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

    public function check_customer_deposit($customer_id = false, $amount = false)
    {
        $customer = $this->getCompanyByID($customer_id);
        return $customer->deposit_amount >= $amount;
    }

    public function getWarehouseProduct($warehouse_id = false, $product_id = false)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllBaseUnits()
    {
        $q = $this->db->get_where("units", array('base_unit' => NULL));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUnitsByBUID($base_unit = false,$unit_id = null)
    {
		if($unit_id){
			$this->db->where('base_unit', $unit_id);
		}else{
			$this->db->where('id', $base_unit)->or_where('base_unit', $base_unit);
		}

        $q = $this->db->get("units");
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
		$q = $this->db->query("SELECT
									cus_units.id,
									cus_units.`code`,
									cus_units.`name`,
									cus_units.base_unit,
									cus_units.operator,
									cus_units.unit_value,
									cus_product_units.unit_qty AS operation_value,
									cus_product_units.unit_price
								FROM
									`cus_units`
								INNER JOIN cus_product_units ON cus_product_units.unit_id = cus_units.id
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

    public function getUnitByID($id = false)
    {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getServiceTypesByID($id = false)
    {
        $q = $this->db->get_where("rental_service_types", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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
    public function getRoomTypesByID($id = false)
    {
        $q = $this->db->get_where('rental_room_types', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getRentalsByID($id = false)
    {
        $q = $this->db->get_where('rentals', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getSourcesByID($id = false)
    {
        $q = $this->db->get_where('rental_source_types', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getHousekeepingStatusByID($id = false)
    {
        $q = $this->db->get_where('rental_housekeeping_status', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getRoomsByID($id = false)
    {
        $q = $this->db->get_where('rental_rooms', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getRoomStatusByID($id = false)
    {
        $q = $this->db->get_where('rental_room_status', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductGroupPrice($product_id = false, $group_id = false)
    {
        $q = $this->db->get_where('product_prices', array('price_group_id' => $group_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllBrands()
    {
        $q = $this->db->get("brands");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getBrandByID($id = false)
    {
        $q = $this->db->get_where('brands', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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

	public function getAllProjectByBillerID($id = false)
    {
        $q = $this->db->get_where('projects', array('biller_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
    public function convertToBase($unit = false, $value = false)
    {
        switch($unit->operator) {
            case '*':
                return $value / $unit->operation_value;
                break;
            case '/':
                return $value * $unit->operation_value;
                break;
            case '+':
                return $value - $unit->operation_value;
                break;
            case '-':
                return $value + $unit->operation_value;
                break;
            default:
                return $value;
        }
    }
	
	
	public function getBillers() {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('id',$this->session->userdata('biller_id'));
		}
		$this->db->where('group_name','biller');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


	public function getAllBiller() 
	{
        $this->db->where('group_name','biller');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getProductUnitByID($unit_id = false)
	{
		$q = $this->db->get_where('product_units', array('id' => $unit_id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

    public function getProductUnit($product_id = false,$unit_id = false)
    {
        $this->db->select('product_units.unit_qty,units.code,units.name');
        $this->db->join('units','units.id=product_units.unit_id','left');
        $this->db->where('product_units.product_id', $product_id);
        $this->db->where('product_units.unit_id', $unit_id);
        $q = $this->db->get('product_units');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getProductUnitByProduct($product_id = false)
	{
		$this->db->select('product_units.unit_id,product_units.unit_qty,units.code,units.name');
		$this->db->join('units','units.id=product_units.unit_id','left');
		$this->db->where('product_units.product_id', $product_id);
		$this->db->order_by('product_units.unit_qty','desc');
        $q = $this->db->get('product_units');
		if ($q->num_rows() > 0) {
			foreach($q->result() as $row){
				$data[] = $row;
			}
            return $data;
        }
        return FALSE;
	}

	public function deleteStockmoves($transaction = false,$transaction_id = false){
		$this->db->delete('stockmoves', array('transaction' => $transaction, 'transaction_id' => $transaction_id));
	}

	public function calculateCONAVCost($product_id = false, $total_raw_cost = false, $total_fin_qty = false, $unit_qty = false) {
		$percent 		= 0;
		$qty 			= 0;
		$total_new_cost = 0;
		$total_qty		= 0;
		$total_old_cost = 0;
		$old_product	= $this->getProductAllByID($product_id);

		$total_qty		= $unit_qty;


		if($old_product->cost > 0){
			$total_qty		= $unit_qty + $old_product->quantity;
			$total_old_cost = $old_product->quantity * $old_product->cost;
		}

		$total_new_cost = ($total_raw_cost * $unit_qty)/$total_fin_qty;
		echo 'TRC '. $total_raw_cost .' UQTY '. $unit_qty .' TFQ '. $total_fin_qty .' TNC '. $total_new_cost .' TOC '. $total_old_cost .' TQTY '. $total_qty .'<br/>';
		$average_cost 	= ($total_new_cost + $total_old_cost) / $total_qty;

		//============================ End ===============================//

        return array('avg'=>$average_cost, 'cost' => $total_new_cost);
    }

	public function getProductAllByID($id = false) {
        $this->db->select('products.*');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function get_payment_alerts() {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		$this->db->select('count('.$this->db->dbprefix('sales').'.id) as alert_num')
			->join('warehouses','warehouses.id = sales.warehouse_id','left')
			->join('users','users.id = sales.created_by','left')
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
		
		$this->db->join('payment_terms','payment_terms.id = sales.payment_term','inner');
		$this->db->where("sales.due_date <=",date('Y-m-d'));
		$this->db->where("IF(
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
							)) !=","paid");
		$this->db->where('sales.payment_term >', 0);
		$this->db->where('sales.grand_total !=', 0);
		$this->db->where('pos !=', 1);
		$this->db->where('sale_status !=', 'draft');
		$this->db->where('sale_status !=', 'returned');
		$this->db->where('IFNULL('.$this->db->dbprefix("sales").'.type,"") !=', "concrete");
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->alert_num;
        }
        return FALSE;
    }

	public function get_purchase_payment_alerts() {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		$this->db->select("count(".$this->db->dbprefix('purchases').".id) as alert_num")
			->join('(select purchase_id,abs(paid) as return_paid from cus_purchases WHERE purchase_id > 0 AND status <> "draft" AND status <> "freight") as pur_return','pur_return.purchase_id = purchases.id','left');
		$this->db->join('payment_terms','payment_terms.id = purchases.payment_term','inner');
		$this->db->where('purchases.due_date <=',date('Y-m-d'));
		$this->db->where('purchases.grand_total !=', 0);
		$this->db->where('purchases.payment_term >', 0);
		$this->db->where('IF(
							(round((cus_purchases.grand_total-(IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))-abs(cus_purchases.return_purchase_total)),2))=0,"paid",
							IF(
								(abs(IFNULL(cus_purchases.return_purchase_total,0)) + IFNULL(cus_purchases.paid,0) - IFNULL(return_paid,0))<>0,"partial",
								"pending"
							)
						) !=', 'paid');
		$this->db->where('purchases.status !=', 'returned');
		$this->db->where('status !=', 'draft');
		$this->db->where('status !=', 'freight');
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->alert_num;
        }
        return FALSE;
    }
	
	public function getPawnPaymentAlerts(){
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('pawns.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('pawns.biller_id',$this->session->userdata('biller_id'));
		}
		$curDate = date('Y-m-d');
		$this->db->select('count('.$this->db->dbprefix('pawns').'.id) as part_alert')
				->join('(SELECT pawn_id FROM '.$this->db->dbprefix('pawn_items').' WHERE next_date <= "'.$curDate.'" GROUP BY pawn_id) as pawn_items','pawns.id = pawn_items.pawn_id','inner')
				->where('pawns.status !=','completed')
				->where('pawns.status !=','closed');
		$q = $this->db->get('pawns');
		if($q->num_rows()>0){
			$res = $q->row();
			return (INT) $res->part_alert;
		}
		return false;
	}

	public function getAllPaymentTerms() {
        $q = $this->db->get('payment_terms');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getAllUsers()
	{
		$this->db->where('saleman',0);
		$this->db->where('agency',0);
		$q = $this->db->get("users");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function auditLogs()
	{
		/* 
		$USER_ID = $this->session->userdata("user_id");
		$MESSAGE = !empty($this->session->userdata("message"))?$this->session->userdata("message"):$this->session->userdata("error");
		$PATH_INFO = explode("/",$_SERVER["PATH_INFO"]);
		$data = array(
						"MESSAGES" => $MESSAGE,
						"USER_ID" => $USER_ID,
						"REQUEST_DATE" => $this->cus->fld(date("d-m-Y H:i:s")),
						"REQUEST_URI" => $_SERVER["REQUEST_URI"],
						"HTTP_USER_AGENT"=> $_SERVER["HTTP_USER_AGENT"],
						"HTTP_HOST"=> $_SERVER["HTTP_HOST"],
						"PATH_INFO"=> $_SERVER["PATH_INFO"],
						"PATH_CONTROLLER"=> $PATH_INFO[1],
						"PATH_FUNCTION"=> $PATH_INFO[2],
						"REQUEST_METHOD"=> $_SERVER["REQUEST_METHOD"],
					);
					
		if(!empty($MESSAGE)){
			$this->db->insert("audit_logs",$data);
		} 
		*/
	}

	public function getBeginStockExpiry($warehouse_id = false, $product_id = false, $date = false, $expiry = false)
	{

        $this->db->select('SUM(COALESCE(quantity, 0)) as quantity');
		$this->db->where('product_id', $product_id);
        if ($warehouse_id) {
            $this->db->where('stockmoves.warehouse_id', $warehouse_id);
        }
		if($date){
			$this->db->where('date(date) < "'.$this->cus->fld($date).'"');
		}else{
			$this->db->where('date(date) < CURRENT_DATE()');
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
            $res = $q->row();
			if($res->quantity == 0){
				return "";
			}
            return  $res->quantity;
        }
        return FALSE;
	}
	
	public function getUsingStockAlert(){
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		$this->db->where('status !=','return');
		$this->db->where('status !=','completed');
		$this->db->where('IFNULL(return_date,"") !=','');
		$this->db->where('return_date <=',date('Y-m-d'));
		$q = $this->db->get('using_stocks');
		if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
        return FALSE;
	}
	
	public function getBeginStock($warehouse_id = false, $product_id = false, $date = false)
	{

        $this->db->select('SUM(COALESCE(quantity, 0)) as quantity');
		$this->db->where('product_id', $product_id);
        if ($warehouse_id) {
            $this->db->where('stockmoves.warehouse_id', $warehouse_id);
        }
		if($date){
			$this->db->where('date(date) < "'.$this->cus->fld($date).'"');
		}else{
			$this->db->where('date(date) < CURRENT_DATE()');
		}
        $q = $this->db->get('stockmoves');

        if ($q->num_rows() > 0) {
            $res = $q->row();
			if($res->quantity == 0){
				return "";
			}
            return  $res->quantity;
        }
        return FALSE;
	}
	
	public function getStockQuantityExpiry($transaction = false, $qty_type = false, $warehouse_id = false, $product_id = false, $start_date = false, $end_date = false, $expiry = false)
	{
		$this->db->select('SUM(COALESCE(quantity, 0)) as quantity');
        $this->db->where('stockmoves.transaction', $transaction);
		$this->db->where('product_id', $product_id);

		if ($qty_type) {
			if($qty_type == 'minus'){
				$this->db->where('stockmoves.quantity < 0');
			}else{
				$this->db->where('stockmoves.quantity > 0');
			}

        }
		if ($warehouse_id) {
            $this->db->where('stockmoves.warehouse_id', $warehouse_id);
        }
		if($start_date){
			$this->db->where('date(date) >= "'.$this->cus->fsd($start_date).'"');
		}
		if($end_date){
			$this->db->where('date(date) <= "'.$this->cus->fsd($end_date).'"');
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
            $res = $q->row();
			if($res->quantity == 0){
				return "";
			}
            return  abs($res->quantity);
        }
        return FALSE;
	}
	
	public function getStockQuantity($transaction = false, $qty_type = false, $warehouse_id = false, $product_id = false, $start_date = false, $end_date = false)
	{
		$this->db->select('SUM(COALESCE(quantity, 0)) as quantity');
        $this->db->where('stockmoves.transaction', $transaction);
		$this->db->where('product_id', $product_id);

		if ($qty_type) {
			if($qty_type == 'minus'){
				$this->db->where('stockmoves.quantity < 0');
			}else{
				$this->db->where('stockmoves.quantity > 0');
			}

        }
		if ($warehouse_id) {
            $this->db->where('stockmoves.warehouse_id', $warehouse_id);
        }
		if($start_date){
			$this->db->where('date(date) >= "'.$this->cus->fsd($start_date).'"');
		}
		if($end_date){
			$this->db->where('date(date) <= "'.$this->cus->fsd($end_date).'"');
		}
        $q = $this->db->get('stockmoves');

        if ($q->num_rows() > 0) {
            $res = $q->row();
			if($res->quantity == 0){
				return "";
			}
            return  abs($res->quantity);
        }
        return FALSE;
	}

	public function getAllCategoriesByWarehouseId($warehouse_id = false)
	{
        $this->db->where('warehouse_id', $warehouse_id);
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getAccount($accountType = array(), $selectAcc = '', $isCash='')
	{
        $selectedAccount = $selectAcc;
		$option = '';
		$this->db->select('acc_section.id,acc_section.name');
		if($accountType){
			$this->db->where_in('acc_section.code', $accountType);
		}
		if($isCash!=''){
			$this->db->join('acc_chart','acc_section.id=acc_chart.section_id', 'inner');
			$this->db->where('acc_chart.is_cash', 1);
			$this->db->where('acc_chart.inactive', 0);
		}
		$this->db->group_by('acc_section.id');
		$se = $this->db->get('acc_section');
		if ($se->num_rows() > 0) {
            foreach (($se->result()) as $se_row) {
                if($se_row->id!=''){
					$option .='<optgroup label="'.$se_row->name.'">';
					$this->db->select('code,name,line_age');
					$this->db->where('acc_chart.inactive', 0);
					$this->db->where('acc_chart.section_id', $se_row->id);
					if($isCash!=''){
						$this->db->where('acc_chart.is_cash', 1);
					}
					$this->db->where('acc_chart.parent_code', 0);
					$this->db->order_by('acc_chart.code','asc');
					$aa = $this->db->get('acc_chart');
					if ($aa->num_rows() > 0) {
						foreach($aa->result() as $aa_row){
							$a = $this->db->get_where("acc_chart", array("parent_code"=>$aa_row->code));
							if($a->num_rows() > 0){
								$option .='<option '.($aa_row->code==$selectedAccount?'selected':'').' value="'.$aa_row->code.'">'.$aa_row->code.' - '.$aa_row->name.'</option>';
								$option .= $this->accSub($aa_row->code,$selectedAccount,$isCash);
							}else{
								$option .='<option '.($aa_row->code==$selectedAccount?'selected':'').' value="'.$aa_row->code.'">'.$aa_row->code.' - '.$aa_row->name.'</option>';
							}
						}
					}
					$option .='</optgroup>';
				}
			}
		}
		return $option;
	}

	public function accSub($ps_id = false,$selectAcc = false,$isCash = false)
	{
		$this->db->select('code,name,line_age');
		if($isCash!=''){
			$this->db->where('acc_chart.is_cash', 1);
		}
		$option1 = '';
		$this->db->where('parent_code',$ps_id);
		$this->db->where('acc_chart.inactive', 0);
		$this->db->order_by('acc_chart.code','asc');
		$ab = $this->db->get('acc_chart');
		if ($ab->num_rows() > 0) {
			foreach($ab->result() as $ab_row){
				$space ='&nbsp';
				$split = explode('/',$ab_row->line_age);
				for($i = 0 ; $i < count($split); $i++){
					$space.= $space;
				}
				$b = $this->db->get_where("acc_chart", array("parent_code"=>$ab_row->code));
				if($b->num_rows() > 0){
					$option1 .='<option '.($ab_row->code==$selectAcc?'selected':'').' value="'.$ab_row->code.'">'.$space.$ab_row->code.' - '.$ab_row->name.'</option>';
					$option1 .= $this->accSub2($ab_row->code,$selectAcc,$isCash);
				}else{
					$option1 .='<option '.($ab_row->code==$selectAcc?'selected':'').' value="'.$ab_row->code.'">'.$space.$ab_row->code.' - '.$ab_row->name.'</option>';
				}
			}
		}

		return $option1;
	}

	public function accSub2($ps_id = false,$selectAcc = false,$isCash = false)
	{
		$this->db->select('code,name,line_age');
		if($isCash!=''){
			$this->db->where('acc_chart.is_cash', 1);
		}
		$option2 = '';
		$this->db->where('parent_code',$ps_id);
		$this->db->where('acc_chart.inactive', 0);
		$this->db->order_by('acc_chart.code','asc');
		$ac = $this->db->get('acc_chart');
		if ($ac->num_rows() > 0) {
			foreach($ac->result() as $ac_row){
				$space ='&nbsp';
				$split = explode('/',$ac_row->line_age);
				for($i = 0 ; $i < count($split); $i++){
					$space.= $space;
				}
				$c = $this->db->get_where("acc_chart", array("parent_code"=>$ac_row->code));
				if($c->num_rows() > 0){
					$option2 .='<option '.($ac_row->code==$selectAcc?'selected':'').' value="'.$ac_row->code.'">'.$space.$ac_row->code.' - '.$ac_row->name.'</option>';
					$option2 .= $this->accSub($ac_row->code,$selectAcc,$isCash);
				}else{
					$option2 .='<option '.($ac_row->code==$selectAcc?'selected':'').' value="'.$ac_row->code.'">'.$space.$ac_row->code.' - '.$ac_row->name.'</option>';
				}
			}
		}

		return $option2;
	}

	public function getProductAccByProductId($product_id = false)
    {
        $q = $this->db->get_where('acc_product', array('product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function getAccountSettingByBiller($biller_id = false)
	{
		$q = $this->db->get_where('acc_setting', array('biller_id' => $biller_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

	public function deleteAccTran($transaction = false,$transaction_id = false)
	{
		$this->db->delete('acc_tran', array('transaction' => $transaction, 'transaction_id' => $transaction_id));
	}

	public function getSaleOrderApproval()
	{
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sale_orders.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sale_orders.biller_id',$this->session->userdata('biller_id'));
		}
		$q = $this->db->get_where('sale_orders', array('status' => 'pending'));
        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
        return FALSE;
	}
    public function getAllSourceTypeByRoomRateID($id)
    {
        $q = $this->db->get_where('rental_room_rates', array('source_type_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllRoomTypesByID($id)
    {
        $q = $this->db->get_where('rental_rooms', array('room_type_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllCommisionTypes() {
        $q = $this->db->get('commission_types');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllCommissionTypeBySalemanID($id)
    {
        $q = $this->db->get_where('salesman_groups', array('commission_type_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllGroupLevelByID($id)
    {
        $q = $this->db->get_where('users', array('commission_type_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getPurchaseOrderApproval()
	{
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchase_orders.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchase_orders.biller_id',$this->session->userdata('biller_id'));
		}
		$q = $this->db->get_where('purchase_orders', array('status' => 'pending'));
        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
        return FALSE;
	}
	
	
	public function getPendingDelivery(){
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('deliveries.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('deliveries.biller_id',$this->session->userdata('biller_id'));
		}
		$q = $this->db->get_where('deliveries', array('status' => 'pending'));
        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
        return FALSE;
	}
	
	public function getProductMethod($product_id = false,$quantity = false,$tmp_stockmoves = false,$transaction = false,$transaction_id = false){
		$product = $this->getProductByID($product_id);
		if($product){
			if($product->accounting_method==0){
				return $this->getFifoCost($product_id,$quantity,$tmp_stockmoves,$transaction,$transaction_id);
			}else if($product->accounting_method==1){
				return $this->getLifoCost($product_id,$quantity,$tmp_stockmoves,$transaction,$transaction_id);
			}else{
				return false;
			}
		}
		return false;
	}

	public function getFifoCost($product_id = false,$quantity = false,$tmp_stockmoves = false,$transaction = false,$transaction_id = false)
	{
		$this->db->select('product_id,quantity,real_unit_cost,transaction');
		$this->db->order_by('date', 'asc');
		$this->db->order_by('id', 'asc');
		if($transaction && $transaction_id){
			$this->db->where("NOT (`transaction_id` = ".$transaction_id." AND `transaction` = '".$transaction."')");
		}
		$q = $this->db->get_where('stockmoves', array('product_id' => $product_id));

		if($tmp_stockmoves){
			$array_result = array_merge($q->result_array(),$tmp_stockmoves);
		}else{
			$array_result = $q->result_array();
		}

		$stock_ins = array();
		$stock_out = array();
		$total_qty = 0;
        if ($q->num_rows() > 0) {
			foreach ($array_result as $row) {
				if($row['product_id']==$product_id){
					$total_qty += $row['quantity'];
					$cost = $row['real_unit_cost'] - 0;
					if($row['quantity'] < 0 ){
						$total_deduct = $stock_out[$cost] +  $row['quantity'];
						$stock_out[$cost] = $total_deduct-0;
					}else{
						$stock_ins[] = array('cost'=>$cost,'quantity'=>($row['quantity'])-0);
					}
				}

            }
			foreach($stock_ins as $stock_in){
				if(abs($stock_out[$stock_in['cost']]-0) > $stock_in['quantity']){
					$stock_in['out_quantity'] = ($stock_in['quantity'] * -1);
					$stock_out[$stock_in['cost']] = $stock_in['quantity'] + $stock_out[$stock_in['cost']];
				}else{
					$stock_in['out_quantity'] = $stock_out[$stock_in['cost']]-0;
					$stock_out[$stock_in['cost']] = 0 ;
				}
				if($stock_in['quantity'] > abs($stock_in['out_quantity'])){
					$datas[] = $stock_in;
				}
			}

			foreach($datas as $data){
				$out_quantity = abs($data['out_quantity']) + $quantity;
				if($data['quantity'] > $out_quantity){
					if($quantity > 0){
						$item_cost[] = array('cost'=>$data['cost'],'quantity'=>$quantity);
					}
					$this->db->update('products', array('cost' => $data['cost']), array('id' => $product_id));
					break;
				}else{
					$balance_quantity = $data['quantity'] + $data['out_quantity'];
					$item_cost[] = array('cost'=>$data['cost'],'quantity'=>$balance_quantity);
					$quantity = $quantity - $balance_quantity;
				}
			}
			return $item_cost;
        }
        return FALSE;
	}

	public function getLifoCost($product_id = false,$quantity = false,$tmp_stockmoves = false,$transaction = false,$transaction_id = false)
	{
		$this->db->select('product_id,quantity,real_unit_cost,transaction');
		$this->db->order_by('date', 'desc');
		$this->db->order_by('id', 'desc');
		if($transaction && $transaction_id){
			$this->db->where("NOT (`transaction_id` = ".$transaction_id." AND `transaction` = '".$transaction."')");
		}
		$q = $this->db->get_where('stockmoves', array('product_id' => $product_id));

		if($tmp_stockmoves){
			$array_result = array_merge($q->result_array(),$tmp_stockmoves);
		}else{
			$array_result = $q->result_array();
		}

		$stock_ins = array();
		$stock_out = array();
		$total_qty = 0;
        if ($q->num_rows() > 0) {
			foreach ($array_result as $row) {
				if($row['product_id']==$product_id){
					$total_qty += $row['quantity'];
					$cost = $row['real_unit_cost'] - 0;
					if($row['quantity'] < 0 ){
						$total_deduct = $stock_out[$cost] +  $row['quantity'];
						$stock_out[$cost] = $total_deduct-0;
					}else{
						$stock_ins[] = array('cost'=>$cost,'quantity'=>($row['quantity'])-0);
					}
				}
            }
			foreach($stock_ins as $stock_in){
				if(abs($stock_out[$stock_in['cost']]-0) > $stock_in['quantity']){
					$stock_in['out_quantity'] = ($stock_in['quantity'] * -1);
					$stock_out[$stock_in['cost']] = $stock_in['quantity'] + $stock_out[$stock_in['cost']];
				}else{
					$stock_in['out_quantity'] = $stock_out[$stock_in['cost']]-0;
					$stock_out[$stock_in['cost']] = 0 ;
				}
				if($stock_in['quantity'] > abs($stock_in['out_quantity'])){
					$datas[] = $stock_in;
				}
			}

			foreach($datas as $data){
				$out_quantity = abs($data['out_quantity']) + $quantity;
				if($data['quantity'] > $out_quantity){
					if($quantity > 0){
						$item_cost[] = array('cost'=>$data['cost'],'quantity'=>$quantity);
					}
					$this->db->update('products', array('cost' => $data['cost']), array('id' => $product_id));
					break;
				}else{
					$balance_quantity = $data['quantity'] + $data['out_quantity'];
					$item_cost[] = array('cost'=>$data['cost'],'quantity'=>$balance_quantity);
					$quantity = $quantity - $balance_quantity;
				}
			}
			return $item_cost;
        }
        return FALSE;
	}
	
	public function updateFifoCost($product_id = false){
		if($this->getProductByID($product_id)){
			$this->db->select('quantity,real_unit_cost,transaction,date');
			$this->db->order_by('date', 'asc');
			$this->db->order_by('id', 'asc');
			$q = $this->db->get_where("stockmoves", array('product_id' => $product_id));
			$stock_ins = array();
			$stock_out = array();
			$total_qty = 0;
			if ($q->num_rows() > 0 && $this->Settings->update_cost) {
				foreach (($q->result_array()) as $row) {
					$total_qty += $row['quantity'];
					$cost = $row['real_unit_cost'] - 0;
					if($row['quantity'] < 0 ){
						$total_deduct = $stock_out[$cost] +  $row['quantity'];
						$stock_out[$cost] = $total_deduct-0;
					}else{
						$stock_ins[] = array('cost'=>$cost,'quantity'=>($row['quantity'])-0);
					}
				}
				foreach($stock_ins as $stock_in){
					if(abs($stock_out[$stock_in['cost']]-0) > $stock_in['quantity']){
						$stock_in['out_quantity'] = ($stock_in['quantity'] * -1);
						$stock_out[$stock_in['cost']] = $stock_in['quantity'] + $stock_out[$stock_in['cost']];
					}else{
						$stock_in['out_quantity'] = $stock_out[$stock_in['cost']]-0;
						$stock_out[$stock_in['cost']] = 0 ;
					}
					if($stock_in['quantity'] > abs($stock_in['out_quantity'])){
						$this->db->update('products', array('cost' => $stock_in['cost']), array('id' => $product_id));
						return $stock_in['cost'];
						break;
					}
				}
			}
			return FALSE;
		}
		return FALSE;
	}
	
	public function updateLifoCost($product_id = false){
		if($this->getProductByID($product_id)){
			$this->db->select('quantity,real_unit_cost,transaction,date');
			$this->db->order_by('date', 'desc');
			$this->db->order_by('id', 'desc');
			$q = $this->db->get_where("stockmoves", array('product_id' => $product_id));
			$stock_ins = array();
			$stock_out = array();
			$total_qty = 0;
			if ($q->num_rows() > 0 && $this->Settings->update_cost) {
				foreach (($q->result_array()) as $row) {
					$total_qty += $row['quantity'];
					$cost = $row['real_unit_cost'] - 0;
					if($row['quantity'] < 0 ){
						$total_deduct = $stock_out[$cost] +  $row['quantity'];
						$stock_out[$cost] = $total_deduct-0;
					}else{
						$stock_ins[] = array('cost'=>$cost,'quantity'=>($row['quantity'])-0);
					}
				}
				foreach($stock_ins as $stock_in){
					if(abs($stock_out[$stock_in['cost']]-0) > $stock_in['quantity']){
						$stock_in['out_quantity'] = ($stock_in['quantity'] * -1);
						$stock_out[$stock_in['cost']] = $stock_in['quantity'] + $stock_out[$stock_in['cost']];
					}else{
						$stock_in['out_quantity'] = $stock_out[$stock_in['cost']]-0;
						$stock_out[$stock_in['cost']] = 0 ;
					}
					if($stock_in['quantity'] > abs($stock_in['out_quantity'])){
						$this->db->update('products', array('cost' => $stock_in['cost']), array('id' => $product_id));
						return $stock_in['cost'];
						break;
					}
				}
			}
			return FALSE;
		}
		return FALSE;
	}
	
	

	public function updateProductMethod($product_id = false, $transaction = false, $transaction_id = false){
		$product = $this->getProductByID($product_id);
		if($product){
			if($product->accounting_method==0){
				return $this->updateFifoCost($product_id);
			}else if($product->accounting_method==1){
				return $this->updateLifoCost($product_id);
			}else if($product->accounting_method==2){
				return $this->updateAVGCost($product_id, $transaction, $transaction_id);
			}else{
				return false;
			}
		}
		return false;
	}
	
	public function getPurchaseByID($id = false) {
        $q = $this->db->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getReceiveItemByID($id = false){
		$this->db->select("biller_id,project_id,re_reference_no as reference_no");
		$q = $this->db->get_where('receives', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getOpeningBalanceByID($id = false){
		$q = $this->db->get_where('inventory_opening_balances', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getConvertByID($id = false){
		$q = $this->db->get_where('converts', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getPurchasePawnByID($id = false){
		$q = $this->db->get_where('pawn_purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function updateAVGCost($product_id = false, $transaction = false, $transaction_id = false){
		if($product = $this->getProductByID($product_id)){
			if($transaction=="Purchases"){
				$data = $this->getPurchaseByID($transaction_id);
			}else if($transaction=="Receives"){
				$data = $this->getReceiveItemByID($transaction_id);
			}else if($transaction=="OpeningBalance"){
				$data = $this->getOpeningBalanceByID($transaction_id);
			}else if($transaction=="Convert"){
				$data = $this->getConvertByID($transaction_id);
			}else if($transaction=="Pawns"){
				$data = $this->getPurchasePawnByID($transaction_id);
			}else{
				$data = false;
			}
			
			$this->db->select('quantity,real_unit_cost,transaction,transaction_id,date');
			$this->db->order_by('date', 'asc');
			$this->db->order_by('id', 'asc');
			$q = $this->db->get_where("stockmoves", array('product_id' => $product_id));
			if ($q->num_rows() > 0 && $this->Settings->update_cost) {
				$old_qty = 0;
				$old_cost = 0;
				$accTrans = false;
				foreach (($q->result_array()) as $row) {
					//===========Accounting Less Quantity=========//
						if($this->Settings->accounting == 1 && $transaction == $row['transaction'] && $transaction_id == $row['transaction_id'] && $old_qty < 0 && $old_cost != $row['real_unit_cost'] && $data){
							if($row['quantity'] >= abs($old_qty)){
								$sold_qty = abs($old_qty);
							}else{
								$sold_qty = $row['quantity'];
							}
							if($old_cost > 0){
								$cost_varaint = $row['real_unit_cost'] - $old_cost;
							}else{
								$cost_varaint = $row['real_unit_cost'] - $product->cost;
							}
							
							$productAcc = $this->getProductAccByProductId($product_id);
							$accTrans[] = array(
								'transaction' => $transaction,
								'transaction_id' => $transaction_id,
								'transaction_date' => $row['date'],
								'reference' => $data->reference_no,
								'account' => $productAcc->stock_acc,
								'amount' => -($cost_varaint * $sold_qty),
								'narrative' => 'Product Code: '.$product->code.'#'.'Qty: '.$sold_qty.'#'.'Cost: '.$cost_varaint,
								'description' => $data->note,
								'biller_id' => $data->biller_id,
								'project_id' => $data->project_id,
								'user_id' => $this->session->userdata('user_id'),
							);
							$accTrans[] = array(
								'transaction' => $transaction,
								'transaction_id' => $transaction_id,
								'transaction_date' => $row['date'],
								'reference' => $data->reference_no,
								'account' => $productAcc->cost_acc,
								'amount' => ($cost_varaint * $sold_qty),
								'narrative' => 'Product Code: '.$product->code.'#'.'Qty: '.$sold_qty.'#'.'Cost: '.$cost_varaint,
								'description' => $data->note,
								'biller_id' => $data->biller_id,
								'project_id' => $data->project_id,
								'user_id' => $this->session->userdata('user_id'),
							);
						}
					
					//===========End Accounting Less Quantity======//
					
					if($row['transaction']=='OpeningBalance' || $row['transaction']=='CostAdjustment' || $row['transaction']=='Pawns' || $row['transaction']=='Purchases' || $row['transaction']=='Receives' || ($row['transaction']=='QuantityAdjustment' && $row['quantity'] > 0) || ($row['transaction']=='Convert' && $row['quantity'] > 0)){
						$new_cost = $row['real_unit_cost'];
						$new_qty = $row['quantity'];
						$total_qty = $new_qty + $old_qty;
						if($old_qty >= 0){
							$total_old_cost = $old_qty * $old_cost;
							$total_new_cost = $new_qty * $new_cost; 
							$old_cost = ($total_old_cost + $total_new_cost) / $total_qty;
						}else{
							if($total_qty > 0){
								$old_cost = $new_cost;
							}else{
								$old_cost = $product->cost;
							}
						}
					}
					$old_qty += $row['quantity'];	 
				}
                if($old_cost > 0){
                    $old_cost = $old_cost;
                }else{
                    $old_cost = 0;
                }
				$this->db->update('products', array('cost' => $old_cost), array('id' => $product_id));
				if($accTrans){
					$this->db->insert_batch("acc_tran",$accTrans);
				}
				return $old_cost;
			}
			return FALSE;
		}
		return FALSE;
	}

	

	public function getAllFloors()
	{
		$q = $this->db->get("suspended_floors");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getLoginTime()
	{
		$day = date("D");
		$group_id = $this->session->userdata("group_id");
		$q = $this->db->get_where('login_permissions', array('group_id' => $group_id, "day" => $day));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

	public function getCalendarDate()
	{
		$q = $this->db->query("SELECT * FROM cus_calendar WHERE `start` <= SYSDATE() AND `end` >= SYSDATE() AND holiday=1");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}


    public function getCompanyReferenceCustomers($field = false)
    {
        $q = $this->db->get('order_ref', 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            switch ($field) {
                case 'customer':
                    $prefix = $this->Settings->customer_prefix;
                    break;
                default:
                    $prefix = '';
            }

            $ref_no = (!empty($prefix)) ? $prefix . '' : '';
            if ($this->Settings->reference_format == 1) {
                $ref_no .= sprintf("%04s", $ref->{$field});
            } else {
                $ref_no .= $this->getRandomReference();
            }

            if ($q->num_rows() > 0) {
                if($q->row()->bill_prefix!=''){
                    $ref_no =$q->row()->bill_prefixs.''.$ref_no;
                }
            }
            return $ref_no;
        }
        return FALSE;
    }
	public function getCompanyReference($field = false)
	{
        $q = $this->db->get('order_ref', 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            switch ($field) {
				case 'supplier':
                    $prefix = $this->Settings->supplier_prefix;
					break;
				case 'customer':
                    $prefix = $this->Settings->customer_prefix;
					break;
                default:
                    $prefix = '';
            }
            $ref_no = (!empty($prefix)) ? $prefix . '' : '';
            $ref_no .= sprintf("%04s", $ref->{$field});
			$q = $this->db->get_where('order_ref', 1);
			if ($q->num_rows() > 0) {
				if($q->row()->bill_prefix!=''){
					$ref_no =$q->row()->bill_prefix.''.$ref_no;
				}
			}
            return $ref_no;
        }
        return FALSE;
    }

	public function updateCompanyReference($field = false)
	{
        $q = $this->db->get('order_ref', 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            $this->db->update('order_ref', array($field => $ref->{$field} + 1));
            return TRUE;
        }
        return FALSE;
    }

	public function getProductFormulation($pid = false)
	{
		$q= $this->db->query("SELECT * FROM ".$this->db->dbprefix('formulation_products')." WHERE for_product_id IN (".$pid.")");
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getAllVehicles() 
	{
        $q = $this->db->get("vehicles");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getProductExpiredByEnding($ending_date = false, $pid = false, $warehouse_id = false)
	{
		if($warehouse_id){
			$this->db->where('warehouse_id',$warehouse_id);
		}
		if($expiry){
			$this->db->where('expiry',$expiry);
		}
		if($transaction && $transaction_id){
			$this->db->where('(transaction != "'.$transaction.'" OR (transaction = "'.$transaction.'" AND transaction_id != '.$transaction_id.'))');
		}
		
		$this->db->select('sum('.$this->db->dbprefix("stockmoves").'.quantity) as quantity,expiry')
				->where('product_id',$pid)
				->where('date('.$this->db->dbprefix('stockmoves').'.date) <=',$ending_date)
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
	
	public function getProductExpiredByProductID($pid = false, $warehouse_id = false, $transaction = false, $transaction_id = false, $expiry = false)
	{
		if($warehouse_id){
			$this->db->where('warehouse_id',$warehouse_id);
		}
		if($expiry){
			$this->db->where('expiry',$expiry);
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

	
	public function getCountRegisterLists()
	{
		$q = $this->db->get_where("pos_register", array("status"=>"open"));
		if($q->num_rows() > 0){
			return $q->num_rows();
		}
		return false;
	}
	
	public function getCountSuspends()
	{
		$q = $this->db->get_where("suspended_bills");
		if($q->num_rows() > 0){
			return $q->num_rows();
		}
		return false;
	}
	
	public function addPrint($data = false)
	{
		if($this->db->insert('print_histories',$data)){
			return true;
		}
		return false;
	}
	
	public function checkPrint($transaction = false,$transaction_id = false)
	{
		$q = $this->db->get_where('print_histories',array('transaction'=>$transaction,'transaction_id'=>$transaction_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getAccTranByTransation($transaction = false,$transation_id = false)
	{
		$this->db->select('acc_tran.*,acc_chart.name as account_name')
			->join('acc_chart','acc_chart.code = acc_tran.account','inner')
			->where('acc_tran.transaction',$transaction)
			->where('acc_tran.transaction_id',$transation_id)
			->order_by('acc_tran.amount','desc');
		$q = $this->db->get('acc_tran');	
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}	
		return false;
	}
	
	public function getSalaryTax($employee_id = false, $param_salary_tax = false)
	{
		$this->load->model("hr_model");
		$data 		  = array();
		$currency 	  = $this->getCurrencyByCode("KHR");
		$salary_tax   = $param_salary_tax * $currency->rate;
		$employee 	  = $this->hr_model->getEmployeeById($employee_id);
		$salary_taxs  = $this->hr_model->getSalaryTaxCondition();
		$spouses 	  = $this->hr_model->getSpouseMemberByEmployeeID($employee_id);
		$childs 	  = $this->hr_model->getChildrenMemberByEmployeeID($employee_id);
		foreach($salary_taxs as $tax){
			if($employee->non_resident==0){
				$allowance 		 = (($spouses?count($spouses):0) + ($childs?count($childs):0)) * 150000;
				$base_salary_tax = $salary_tax - $allowance;
				if($base_salary_tax <= $tax->max_salary && $base_salary_tax >= $tax->min_salary){
					$tax_on_salary = ($base_salary_tax * $tax->tax_percent) - $tax->reduce_tax;
					$data = array(
							"tax_percent" 	=> $tax->tax_percent,
							"reduce_tax"  	=> $tax->reduce_tax,
							"tax_on_salary" => ($tax_on_salary / $currency->rate),
						); 
				}
				
			}else{
				// mutiply with 20% non-resident
				$tax_on_salary = ($salary_tax * 0.2);
				$data = array(
						"tax_percent" 	=> 0,
						"reduce_tax"  	=> 0,
						"tax_on_salary" => ($tax_on_salary / $currency->rate),
					);
			}
		}
		return $data;
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
	
	public function getAlertDownPayments()
	{
		$remind = ($this->Settings->installment_alert_days?$this->Settings->installment_alert_days:0);
         $q = $this->db->select('COUNT(cus_down_payment_details.id) as alert_num')
				       ->where('DATE_SUB('.$this->db->dbprefix('down_payment_details').'.`deadline`, INTERVAL '.$remind.' DAY) <=', date("Y-m-d"))
				       ->where('down_payment_details.payment_status !=','paid')
				       ->where('down_payments.status', 'completed')
				       ->join('cus_down_payments','cus_down_payments.id=down_payment_id','left')
				       ->get('cus_down_payment_details');
				 
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->alert_num;
        }
        return FALSE;
	}
	
	public function getProductPromotions()
	{
		$q = $this->db->get('product_promotions');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getAlertInstallmentMissedRepayments()
	{
		$remind = ($this->Settings->installment_alert_days?$this->Settings->installment_alert_days:0);
        $q = $this->db->select('COUNT(cus_installment_items.id) as alert_num')
					   ->join('installments', 'installment_items.installment_id=installments.id', 'left')
					   ->where('DATE_SUB(cus_installment_items.`deadline`, INTERVAL '.$remind.' DAY) <=', date("Y-m-d"))
					   ->where('installment_items.status !=','paid')
					   ->where('installment_items.status !=','payoff')
					   ->where('installments.status !=','payoff')
					   ->where('installments.status !=','completed')
					   ->where('installments.status !=','inactive')
					   ->get('installment_items');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->alert_num;
        }
        return FALSE;
	}
	
	public function getSalemans()
	{
		$q = $this->db->get_where('users',array('saleman'=>1));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

    public function getRoomCheckedIN()
    {
        $q = $this->db->get_where('rentals',array('status'=>'checked_in'));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
	public function getExpiryQuantityByProduct($product_id = false)
	{
		if($product_id){
			$this->db->select('product_id,expiry, sum('.$this->db->dbprefix("stockmoves").'.quantity) as quantity, warehouses.name as warehouse_name');
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('stockmoves.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
			}
			$this->db->join('warehouses','warehouses.id = stockmoves.warehouse_id','inner');
			$this->db->where('product_id',$product_id);
			$this->db->where('expiry !=','0000-00-00');
			$this->db->where('IFNULL(expiry,"") !=',"");
			$this->db->group_by('product_id,warehouse_id,expiry');
			$this->db->order_by('warehouse_id, expiry');
			$q = $this->db->get('stockmoves');
			if($q->num_rows() > 0){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}
		}
		return false;
	}
	
	public function getAgencies()
	{
		$q = $this->db->get_where('users',array('agency'=>1));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getSalesmanGroups()
	{
		$q = $this->db->get('salesman_groups');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getSalesmanGroupsByID($id = false)
	{
		$q = $this->db->get_where('salesman_groups', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getAreas()
	{
		$q = $this->db->get('areas');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getAreaByID($id = false)
	{
		$q = $this->db->get_where('areas', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getExpenseCategories(){
		$q = $this->db->get('expense_categories');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getRefQuotations(){
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('quotes.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('quotes.biller_id',$this->session->userdata('biller_id'));
		}
		
		$this->db->select('id,reference_no');
		$this->db->where('quotes.status','pending');
		$this->db->order_by('id','desc');
		$q = $this->db->get('quotes');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getRefSaleOrders($status = false){
		$this->db->select('id,reference_no');
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sale_orders.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sale_orders.biller_id',$this->session->userdata('biller_id'));
		}
		
		if($status){
			if($status=="approved"){
				$this->db->where('sale_orders.status IN("approved","partial")');
			}else{
				$this->db->where('sale_orders.status',$status);
			}
		}
		$this->db->order_by('id','desc');
		$q = $this->db->get('sale_orders');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getRefSales($delivery_status = false, $sale_status= false){
		$this->db->select('id,reference_no');
		if($delivery_status){
			$this->db->where('sales.delivery_status !=',$delivery_status);
			$this->db->where('pos !=', 1);
		}
		if($sale_status){
			$this->db->where('sales.sale_status',$sale_status);
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
		}
		$this->db->where('sale_status !=', 'draft');
		$this->db->where('sale_status !=', 'returned');
		$this->db->order_by('id','desc');
		$q = $this->db->get('sales');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getRefDelivery($status = false){
		$this->db->select('id,do_reference_no');
		if($status){
			$this->db->where('deliveries.status !=',$status);
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('deliveries.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('deliveries.biller_id',$this->session->userdata('biller_id'));
		}
		
		$this->db->order_by('id','desc');
		$q = $this->db->get('deliveries');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getRefPurchaseRequests($status = false){
		$this->db->select('id,reference_no');
		if($status){
			$this->db->where('purchase_requests.status',$status);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchase_requests.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchase_requests.biller_id',$this->session->userdata('biller_id'));
		}
		$this->db->order_by('id','desc');
		$q = $this->db->get('purchase_requests');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getRefPurchaseOrders($status = false){
		$this->db->select('id,reference_no');
		if($status){
			$this->db->where('purchase_orders.status',$status);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchase_orders.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchase_orders.biller_id',$this->session->userdata('biller_id'));
		}
		$this->db->where("received !=",1);
		$this->db->order_by('id','desc');
		$q = $this->db->get('purchase_orders');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getRefPurchases($receive_status = false){
		$this->db->select('id,reference_no');
		if($receive_status){
			$this->db->where('purchases.status !=',$receive_status);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('purchases.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
		}
		$this->db->where('purchases.status !=', 'returned');
        $this->db->where('status !=', 'draft');
		$this->db->where('status !=', 'freight');
		$this->db->order_by('id','desc');
		$q = $this->db->get('purchases');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getCustomerByCode($code = false){
		if($code){
			$q = $this->db->get_where('companies',array('code'=>$code,'group_name'=>'customer'));
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
	public function getSupplierByCode($code = false){
		if($code){
			$q = $this->db->get_where('companies',array('code'=>$code,'group_name'=>'supplier'));
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
	
	public function checkExpiry($stockmoves = false, $items = false, $type = false, $transaction = false, $transaction_id = false){
		if($stockmoves && $items){
			$expiry_items = false;
			foreach($items as $item){
				$product_detail = $this->getProductByID($item['product_id']);
				$unit = $this->getUnitByID($product_detail->unit);
				$s_unit = $this->getProductUnit($product_detail->id,$item['product_unit_id']);
				if($type=='Sale' || $type=='Delivery' || $type=='POS'){
					$price = 0;
					$item_discount = 0;
					$discount = 0;
					if($item['subtotal'] != 0 && $item['subtotal'] != ''){
						$price = $item['subtotal'] / $item['unit_quantity'];
						if($item['item_discount'] > 0){
							$item_discount = $item['item_discount'] / $item['unit_quantity'];
						}
					}
					if($s_unit->unit_qty > 1){
						$price = $price / $s_unit->unit_qty;
					}
					if($item_discount > 0){
						if (strpos($a, '%') !== false) {
							$discount = $item_discount;
						}else{
							$discount = $item['discount'];
						}
					}
					$sale_foc = 0;
					$delivery_foc = 0;
					if(($type=='Sale' || $type=='POS') && $item['foc'] > 0){
						$sale_foc = $item['foc'];
					}else if($type=='Delivery'){
						$delivery_foc = $item['foc_qty'];
					}
				}
				
				
			
				if($item['expiry'] != ''){
					$expiry_quantity = abs($item['quantity']) + $sale_foc;
					$product_expiry = $this->getProductExpiredByProductID($item['product_id'], $item['warehouse_id'], $transaction, $transaction_id, $item['expiry']);
					if($product_expiry){
						foreach($product_expiry as $row_expiry){
							$row_expiry->expiry = $this->cus->fsd($row_expiry->expiry);
							if($expiry_items){
								foreach($expiry_items as $expiry_item){
									if($expiry_item['product_id'] == $item['product_id'] && $expiry_item['expiry'] == $row_expiry->expiry){
										$row_expiry->quantity -= abs($expiry_item['quantity']);
									}
								}
							}
							if($row_expiry->quantity < (abs($item['quantity']) + $sale_foc)){
								
								$item['quantity'] = $row_expiry->quantity;
								if($type=='Sale' || $type=='Delivery' || $type=='POS'){
									$item['unit_quantity'] = $row_expiry->quantity;
									$item['product_unit_id'] = $product_detail->unit;
									$item['product_unit_code'] = $unit->code;
									$item['subtotal'] = $row_expiry->quantity * $price;
									$item['discount'] = $discount;
									$item['item_discount'] = $row_expiry->quantity * $item_discount;
									if($type=='Delivery'){
										$item['foc_qty'] = 0;
									}
								}else{
									$item['unit_quantity'] = $row_expiry->quantity;
									$item['product_unit_id'] = $product_detail->unit;
									$item['product_unit_code'] = $unit->code;
								}
								
								$expiry_items[] = $item;
								if($type=='Delivery'){
									$item['foc_qty'] = $delivery_foc;
								}else if($type=='Sale' || $type=='POS'){
									$item['foc'] = 0;
								}
								
								$expiry_quantity = $expiry_quantity - $row_expiry->quantity;
								$product_expiries = $this->getProductExpiredByProductID($item['product_id'], $item['warehouse_id'], $transaction, $transaction_id);
								if($product_expiries){
									$con = 1;
									foreach($product_expiries as $product_expirie){
										$expiry_date = $this->cus->fsd($product_expirie->expiry);
										if($expiry_items){
											foreach($expiry_items as $expiry_item){
												if($expiry_item['product_id'] == $item['product_id'] && $expiry_item['expiry'] == $expiry_date){
													$product_expirie->quantity -= abs($expiry_item['quantity']);
												}
											}
										}

										if($con==1 && $expiry_date != $row_expiry->expiry && $expiry_date >= date('Y-m-d') && $product_expirie->quantity > 0){
											
											if($product_expirie->quantity >= $expiry_quantity){
												$item['quantity'] = $expiry_quantity;
												$item['expiry'] = $expiry_date;
												
												if($type=='Sale' || $type=='Delivery' || $type=='POS'){
													$item['unit_quantity'] = $expiry_quantity;
													$item['product_unit_id'] = $product_detail->unit;
													$item['product_unit_code'] = $unit->code;
													$item['subtotal'] = $expiry_quantity * $price;
													$item['discount'] = $discount;
													$item['item_discount'] = $expiry_quantity * $item_discount;
												}else{
													$item['unit_quantity'] = $expiry_quantity;
													$item['product_unit_id'] = $product_detail->unit;
													$item['product_unit_code'] = $unit->code;
												}
												
												$con = 0;
											}else{
												$expiry_quantity = $expiry_quantity - $product_expirie->quantity;
												$item['quantity'] = $product_expirie->quantity;
												$item['expiry'] = $expiry_date;
												if($type=='Sale' || $type=='Delivery' || $type=='POS'){
													$item['unit_quantity'] = $product_expirie->quantity;
													$item['product_unit_id'] = $product_detail->unit;
													$item['product_unit_code'] = $unit->code;
													$item['subtotal'] = $product_expirie->quantity * $price;
													$item['discount'] = $discount;
													$item['item_discount'] = $product_expirie->quantity * $item_discount;
												}else{
													$item['unit_quantity'] = $product_expirie->quantity;
													$item['product_unit_id'] = $product_detail->unit;
													$item['product_unit_code'] = $unit->code;
												}
											}	
											$expiry_items[] = $item;
											if($type=='Delivery'){
												$item['foc_qty'] = 0;
											}else if($type=='Sale' || $type=='POS'){
												$item['foc'] = 0;
											}
											
										}
									}
									if($expiry_quantity > 0 && $con==1){
										unset($item['expiry']);
										$item['quantity'] = $expiry_quantity;
										
										if($type=='Sale' || $type=='Delivery' || $type=='Delivery' || $type=='POS'){
											$item['unit_quantity'] = $expiry_quantity;
											$item['product_unit_id'] = $product_detail->unit;
											$item['product_unit_code'] = $unit->code;
											$item['subtotal'] = $expiry_quantity * $price;
											$item['discount'] = $discount;
											$item['item_discount'] = $expiry_quantity * $item_discount;
										}else{
											$item['unit_quantity'] = $expiry_quantity;
											$item['product_unit_id'] = $product_detail->unit;
											$item['product_unit_code'] = $unit->code;
										}
										
										$expiry_items[] = $item;
										if($type=='Delivery'){
											$item['foc_qty'] = 0;
										}else if($type=='Sale' || $type=='POS'){
											$item['foc'] = 0;
										}
									}
								}else{
									unset($item['expiry']);
									$item['quantity'] = $expiry_quantity;
									
									if($type=='Sale' || $type=='Delivery' || $type=='POS'){
										$item['unit_quantity'] = $expiry_quantity;
										$item['product_unit_id'] = $product_detail->unit;
										$item['product_unit_code'] = $unit->code;
										$item['subtotal'] = $expiry_quantity * $price;
										$item['discount'] = $discount;
										$item['item_discount'] = $expiry_quantity * $item_discount;
									}else{
										$item['unit_quantity'] = $expiry_quantity;
										$item['product_unit_id'] = $product_detail->unit;
										$item['product_unit_code'] = $unit->code;
									}
									
									$expiry_items[] = $item;
									if($type=='Delivery'){
										$item['foc_qty'] = 0;
									}else if($type=='Sale' || $type=='POS'){
										$item['foc'] = 0;
									}
								}
							}else{
								$expiry_items[] = $item;
								if($type=='Delivery'){
									$item['foc_qty'] = 0;
								}else if($type=='Sale' || $type=='POS'){
									$item['foc'] = 0;
								}
							}
						}						
					}else{
						$expiry_items[] = $item;
					}
					
				}else if($type=='POS'){
					$product_expiries = $this->getProductExpiredByProductID($item['product_id'], $item['warehouse_id'], $transaction, $transaction_id);
					if($product_expiries){
						$con = 1;
						$expiry_quantity = 0;
						foreach($product_expiries as $product_expirie){
							if($expiry_quantity == 0){
								$expiry_quantity = abs($item['quantity']) + $sale_foc;
							}
							$expiry_date = $this->cus->fsd($product_expirie->expiry);
							
							if($expiry_items){
								foreach($expiry_items as $expiry_item){
									if($expiry_item['product_id'] == $item['product_id'] && $expiry_item['expiry'] == $expiry_date){
										$product_expirie->quantity -= abs($expiry_item['quantity']);
									}
								}
							}
							
							if($con==1 && $expiry_date >= date('Y-m-d') && $product_expirie->quantity > 0){
								if($product_expirie->quantity >= $expiry_quantity){
									$item['quantity'] = $expiry_quantity;
									$item['expiry'] = $expiry_date;
									if($type=='Sale' || $type=='Delivery' || $type=='POS'){
										$item['unit_quantity'] = $expiry_quantity;
										$item['product_unit_id'] = $product_detail->unit;
										$item['product_unit_code'] = $unit->code;
										$item['unit_price'] =  $price;
										$item['net_unit_price'] =  $price;
										$item['subtotal'] = $expiry_quantity * $price;
										$item['discount'] = $discount;
										$item['item_discount'] = $expiry_quantity * $item_discount;
									}else{
										$item['unit_quantity'] = $expiry_quantity;
										$item['product_unit_id'] = $product_detail->unit;
										$item['product_unit_code'] = $unit->code;
									}
									
									$con = 0;
								}else{
									$expiry_quantity = $expiry_quantity - $product_expirie->quantity;
									$item['quantity'] = $product_expirie->quantity;
									$item['expiry'] = $expiry_date;
									if($type=='Sale' || $type=='Delivery' || $type=='POS'){
										$item['unit_quantity'] = $product_expirie->quantity;
										$item['product_unit_id'] = $product_detail->unit;
										$item['product_unit_code'] = $unit->code;
										$item['unit_price'] =  $price;
										$item['net_unit_price'] =  $price;
										$item['subtotal'] = $product_expirie->quantity * $price;
										$item['discount'] = $discount;
										$item['item_discount'] = $product_expirie->quantity * $item_discount;
									}else{
										$item['unit_quantity'] = $product_expirie->quantity;
										$item['product_unit_id'] = $product_detail->unit;
										$item['product_unit_code'] = $unit->code;
									}
								}	
								$expiry_items[] = $item;
							}
						}
						if($expiry_quantity > 0 && $con==1){
							unset($item['expiry']);
							$item['quantity'] = $expiry_quantity;
							if($type=='Sale' || $type=='Delivery' || $type=='POS'){
								$item['unit_quantity'] = $expiry_quantity;
								$item['product_unit_id'] = $product_detail->unit;
								$item['product_unit_code'] = $unit->code;
								$item['subtotal'] = $expiry_quantity * $price;
								$item['discount'] = $discount;
								$item['unit_price'] =  $price;
								$item['net_unit_price'] =  $price;
								$item['item_discount'] = $expiry_quantity * $item_discount;
							}else{
								$item['unit_quantity'] = $expiry_quantity;
								$item['product_unit_id'] = $product_detail->unit;
								$item['product_unit_code'] = $unit->code;
							}
							
							$expiry_items[] = $item;
						}
					}else{
						$expiry_items[] = $item;
					}
				}else{
					$expiry_items[] = $item;
				}
			}

			
			$expiry_stockmoves = false;
			foreach($stockmoves as $stockmove){
				if($stockmove['expiry'] != ''){
					$expiry_quantity = abs($stockmove['quantity']);
					$product_expiry = $this->getProductExpiredByProductID($stockmove['product_id'], $stockmove['warehouse_id'], $transaction, $transaction_id, $stockmove['expiry']);
					if($product_expiry && $stockmove['quantity'] < 0){
						foreach($product_expiry as $row_expiry){
							$row_expiry->expiry = $this->cus->fsd($row_expiry->expiry);
							if($expiry_stockmoves){
								foreach($expiry_stockmoves as $expiry_stockmove){
									if($expiry_stockmove['product_id'] == $stockmove['product_id'] && $expiry_stockmove['expiry'] == $row_expiry->expiry){
										$row_expiry->quantity -= abs($expiry_stockmove['quantity']);
									}
								}
							}
							
							if($row_expiry->quantity < abs($stockmove['quantity'])){
								$stockmove['quantity'] = $row_expiry->quantity * (-1);
								$expiry_stockmoves[] = $stockmove;
								$expiry_quantity = $expiry_quantity - $row_expiry->quantity;
								$product_expiries = $this->getProductExpiredByProductID($stockmove['product_id'], $stockmove['warehouse_id'], $transaction, $transaction_id);
								if($product_expiries){
									$con = 1;
									foreach($product_expiries as $product_expirie){
										$expiry_date = $this->cus->fsd($product_expirie->expiry);
										
										if($expiry_stockmoves){
											foreach($expiry_stockmoves as $expiry_stockmove){
												if($expiry_stockmove['product_id'] == $stockmove['product_id'] && $expiry_stockmove['expiry'] == $expiry_date){
													$product_expirie->quantity -= abs($expiry_stockmove['quantity']);
												}
											}
										}
										
										if($con==1 && $expiry_date != $row_expiry->expiry && $expiry_date >= date('Y-m-d') && $product_expirie->quantity > 0){
											if($product_expirie->quantity >= $expiry_quantity){
												$stockmove['quantity'] = $expiry_quantity * (-1);
												$stockmove['expiry'] = $expiry_date;
												$con = 0;
											}else{
												$expiry_quantity = $expiry_quantity - $product_expirie->quantity;
												$stockmove['quantity'] = $product_expirie->quantity * (-1);
												$stockmove['expiry'] = $expiry_date;
											}	
											$expiry_stockmoves[] = $stockmove;
										}
									}
									if($expiry_quantity > 0 && $con==1){
										unset($stockmove['expiry']);
										$stockmove['quantity'] = $expiry_quantity * (-1);
										$expiry_stockmoves[] = $stockmove;
									}
								}else{
									unset($stockmove['expiry']);
									$stockmove['quantity'] = $expiry_quantity * (-1);
									$expiry_stockmoves[] = $stockmove;
								}
							}else{
								$expiry_stockmoves[] = $stockmove;
							}
						}						
					}else{
						$expiry_stockmoves[] = $stockmove;
					}
					
				}else if($type=='POS'){
					$product_expiries = $this->getProductExpiredByProductID($stockmove['product_id'], $stockmove['warehouse_id'], $transaction, $transaction_id);
					if($product_expiries){
						$con = 1;
						$expiry_quantity = 0;
						foreach($product_expiries as $product_expirie){
							if($expiry_quantity == 0){
								$expiry_quantity = abs($stockmove['quantity']);
							}
							$expiry_date = $this->cus->fsd($product_expirie->expiry);

							if($expiry_stockmoves){
								foreach($expiry_stockmoves as $expiry_stockmove){
									if($expiry_stockmove['product_id'] == $stockmove['product_id'] && $expiry_stockmove['expiry'] == $expiry_date){
										$product_expirie->quantity -= abs($expiry_stockmove['quantity']);
									}
								}
							}
							
							if($con==1 && $expiry_date >= date('Y-m-d') && $product_expirie->quantity > 0){
								if($product_expirie->quantity >= $expiry_quantity){
									$stockmove['quantity'] = $expiry_quantity * (-1);
									$stockmove['expiry'] = $expiry_date;
									$con = 0;
								}else{
									$expiry_quantity = $expiry_quantity - $product_expirie->quantity;
									$stockmove['quantity'] = $product_expirie->quantity * (-1);
									$stockmove['expiry'] = $expiry_date;
								}	
								$expiry_stockmoves[] = $stockmove;
							}
						}
						if($expiry_quantity > 0 && $con==1){
							unset($stockmove['expiry']);
							$stockmove['quantity'] = $expiry_quantity * (-1);
							$expiry_stockmoves[] = $stockmove;
						}
					}else{
						$expiry_stockmoves[] = $stockmove;
					}
				}else{
					$expiry_stockmoves[] = $stockmove;
				}
			}
			return array('expiry_stockmoves'=>$expiry_stockmoves , 'expiry_items'=>$expiry_items);
		}
		return false;
	}
	
	
	public function syncConsignment($consignment_id = false){
		if($consignment_id && $consignment_id > 0){
			$this->db->query("
								UPDATE ".$this->db->dbprefix("consignment_items")."
								LEFT JOIN ( SELECT consignment_item_id, sum( quantity ) AS return_qty FROM ".$this->db->dbprefix("consignment_items")." WHERE consignment_item_id > 0 GROUP BY consignment_item_id ) AS consignment_returns ON consignment_returns.consignment_item_id = ".$this->db->dbprefix("consignment_items").".id
								LEFT JOIN ( SELECT consignment_item_id, sum( quantity ) AS sale_qty FROM ".$this->db->dbprefix("sale_items")." WHERE consignment_item_id > 0 GROUP BY consignment_item_id ) AS consignment_sales ON consignment_sales.consignment_item_id = ".$this->db->dbprefix("consignment_items").".id 
								SET ".$this->db->dbprefix("consignment_items").".return_qty = IFNULL( abs( consignment_returns.return_qty ), 0 ),
								".$this->db->dbprefix("consignment_items").".sale_qty = IFNULL( abs( consignment_sales.sale_qty ), 0 ) 
								WHERE
									consignment_id = '".$consignment_id."'
							");
			$this->db->select("sum(quantity) as quantity,
								sum(IFNULL(return_qty,0) + IFNULL(sale_qty,0)) as return_qty
							")
						->where("consignment_id",$consignment_id);
			$q = $this->db->get("consignment_items");		
			if($q->num_rows() > 0){
				$quantity = $q->row()->quantity;
				$return_qty = $q->row()->return_qty;
				if($return_qty == $quantity){
					$status = "completed";
				}else if($return_qty > 0){
					$status = "partial";
				}else{
					$status = "pending";
				}
				$this->db->update('consignments',array('status'=>$status),array('id'=>$consignment_id));
			}
			
			
		}
	}
	
	public function getAlertProductLicense(){
		$date = date('Y-m-d', strtotime('+6 months'));
		$q = $this->db->select("COUNT(".$this->db->dbprefix("products").".id) as alert_num")
				->join("(SELECT
							product_id,
							max( valid_date ) valid_date 
						FROM
							".$this->db->dbprefix('product_licenses')."
						GROUP BY
							product_id) as product_licenses","product_licenses.product_id = products.id","inner")
				->where("product_licenses.valid_date <",$date)
				->get("products");
		if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->alert_num;
        }
        return FALSE;
	}
	
	public function getAlertPaymentRentals()
	{
        $q = $this->db->select('COUNT(cus_rentals.id) as alert_num')
					   ->where('DATE_SUB(to_date, INTERVAL 0 DAY) <=', date("Y-m-d"))
					   ->where('status','checked_in')
					   ->get('cus_rentals');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->alert_num;
        }
        return FALSE;
	}

	public function getAlertRepairReceives()
	{
        $q = $this->db->select('COUNT(cus_repairs.id) as alert_num')
					   ->where('DATE_SUB(receive_date, INTERVAL 2 DAY) <=', date("Y-m-d"))
					   ->where('status !=','sent')
					   ->get('cus_repairs');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->alert_num;
        }
        return FALSE;
	}
	
	public function getProductUnitByCodeName($product_id = false, $unit = false){
		$q = $this->db->query("SELECT
								".$this->db->dbprefix("product_units").".unit_id,
								".$this->db->dbprefix("product_units").".product_id,
								".$this->db->dbprefix("product_units").".unit_qty,
								".$this->db->dbprefix("units").".`code`,
								".$this->db->dbprefix("units").".`name` 
							FROM
								".$this->db->dbprefix("product_units")."
								INNER JOIN ".$this->db->dbprefix("units")." ON ".$this->db->dbprefix("units").".id = ".$this->db->dbprefix("product_units").".unit_id 
							WHERE
								".$this->db->dbprefix("product_units").".product_id = ".$product_id."
								AND (".$this->db->dbprefix("units").".`code` = '".$unit."' OR ".$this->db->dbprefix("units").".`name` = '".$unit."')
						");
		if($q->num_rows() > 0){
			return $q->row();
		}		
		return false;
	}
	public function getTaxRateByCode($code = false) {
        $q = $this->db->get_where('tax_rates', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getCategoryByProject(){
		$data = false;
		if (!$this->config->item('one_biller') && !$this->Owner && !$this->Admin){
			$where_biller = "";
			$where_project = "";
			if($this->session->userdata('biller_id')){
				$where_biller = "(".$this->db->dbprefix('category_projects').".biller_id IN (".$this->session->userdata('biller_id').") OR IFNULL(".$this->db->dbprefix('categories').".biller,'')='' OR ".$this->db->dbprefix('categories').".biller = 'null')";
			}
			if($this->Settings->project == 1 && $this->session->userdata('project_ids') && $this->session->userdata('project_ids') != "null"){
				$project_ids = json_decode($this->session->userdata("project_ids"));
				if($project_ids[0] != "all"){
					$pro_ids = "";
					$i = 1;
					foreach($project_ids as $project_id){
						if($i==1){
							$pro_ids .= "'".$project_id."'";
							$i = 2;
						}else{
							$pro_ids .= ", '".$project_id."'";
						}
					}
					$where_project = "(".$this->db->dbprefix('category_projects').".project_id IN (".$pro_ids.") OR IFNULL(".$this->db->dbprefix('categories').".project,'')='' OR ".$this->db->dbprefix('categories').".project = 'null')";
				}
			}
			if($where_biller != "" || $where_project != "" ){
				if($where_biller !=""){
					$this->db->where($where_biller);
				}
				if($where_project !=""){
					$this->db->where($where_project);
				}
				$this->db->select("categories.id")
							->join("category_projects","category_projects.category_id = categories.id","LEFT")
							->group_by("categories.id");
				$q = $this->db->get("categories");			
				if($q->num_rows() > 0){
					foreach (($q->result()) as $row) {
						$data[] = $row->id;
					}
				}
			}
		}
		
		if($this->config->item("user_by_category") && !$this->Owner && !$this->Admin){
			$categories = false;
			if($this->cus->GP['categories'] != "null" && $this->cus->GP['categories']){
				$categories = json_decode($this->cus->GP['categories']);
			}
			if($data && $categories){
				$data=array_intersect($data,$categories);
			}else if($categories){
				$data = $categories;
			}
		}
		return $data;
		
	}
	
	public function getCashAccountByID($id = false){
		$q = $this->db->get_where("cash_accounts",array("id"=>$id));
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	public function getCashAccounts(){
		$q = $this->db->get("cash_accounts");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getAlertCustomerpayments()
	{
		$remind = ($this->Settings->installment_alert_days?$this->Settings->installment_alert_days:0);
        $q = $this->db->select('COUNT('.$this->db->dbprefix("sales").'.id) as alert_num')
					   ->where('DATE_SUB('.$this->db->dbprefix("sales").'.`due_date`, INTERVAL '.$remind.' DAY) <=', date("Y-m-d"))
					   ->where('sales.payment_status !=','paid')
					   ->where('sales.payment_status !=','return')
					   ->where('IFNULL('.$this->db->dbprefix("sales").'.type,"") !=', "concrete")
					   ->get('sales');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->alert_num;
        }
        return FALSE;
	}
	
	public function getCities(){
		$this->db->where("IFNULL(city_id,0)",0);
		$q = $this->db->get("areas");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getDistricts($city_id = false){
		$this->db->where("IFNULL(district_id,0)",0);
		$this->db->where("IFNULL(city_id,0)",$city_id);
		$q = $this->db->get("areas");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getCommunes($district_id = false){
		$this->db->where("IFNULL(commune_id,0)",0);
		$this->db->where("IFNULL(district_id,0)",$district_id);
		$q = $this->db->get("areas");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
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
	public function getPaymentTermsByID($id = NULL)
	{
        $q = $this->db->where('id', $id)->get('payment_terms');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductsPrice($id = NULL) {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCustomerByWarehouse($group_name,$warehouse_id) {

        $this->db->select("id, (CASE WHEN name = '-' THEN name ELSE CONCAT(name, ' (', saleman_name, ')') END) as text", FALSE);
        $q = $this->db->get_where('companies', array('group_name' => $group_name,'saleman_id'=>$warehouse_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function get_BillersByUserID($userID) { 
          return $this->db->query("SELECT
        cus_users.biller_id,
        cus_companies.id,
        cus_companies.company
        FROM
        cus_companies
        INNER JOIN cus_users ON cus_companies.id = cus_users.biller_id
        WHERE
        cus_users.id = {$userID}")->result();
    }

    public function getAllCustomers($group_name) {
          $this->db->select("id, (CASE WHEN name = '-' THEN name ELSE CONCAT(name, ' (', code, ')') END) as text", FALSE);
        $q = $this->db->get_where('companies', array('group_name' => $group_name));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllStaffs()
    {
        $q = $this->db->where('group_name','staff')->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUsers($id = NULL) {
   
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllPaymentByCus($cus_id,$df,$dt){
       
        $where='';
        if ($df || $dt) {
            $where.=" AND(DATE_FORMAT(`date`,'%Y-%m-%d') >= '".date('Y-m-d',strtotime(str_replace("/", "-", $df)))."' AND DATE_FORMAT(`date`,'%Y-%m-%d') <= '".date('Y-m-d',strtotime(str_replace("/","-",$dt)))."')";
        }
        
        return $this->db->query("SELECT * FROM cus_sales WHERE customer_id = $cus_id AND payment_status<>'paid' {$where}")
                        ->result();
    }

    public function get_selected_sale($cus_id,$sale_ids){
        $where =' AND(';
        for ($i=0; $i < count($sale_ids) ; $i++) { 
                $where.="id='".$sale_ids[$i]."'";
                
                if($i<count($sale_ids)-1)
                    $where.=' OR ';
        }
        $where.=')';
        return $this->db->query("SELECT * FROM cus_sales WHERE customer_id = $cus_id AND payment_status<>'paid' {$where}")
                        ->result();
    }

    public function getAllBillers($group_name) {
        $q = $this->db->get_where('companies', array('group_name' => $group_name));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllServiceTypes(){
        $q = $this->db->get('rental_service_types');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }




    public function getGroupID($id) {
        return $this->db->query( "SELECT * FROM cus_users WHERE id = {$id} " )->row()  ;
    }

    public function getRecordpaymentSale($date,$first_date,$last_date,$userID,$stock,$supplier){
     $where = "WHERE pr.delete = 0";
    if($first_date !="" || $first_date != NULL  ){
        $where .= " AND pr.date >= '$first_date' ";  }
    if($last_date !="" || $last_date != NULL){ 
        $where .= " AND pr.date <= '$last_date' "; }
    
    if($first_date == "" && $last_date  == "" ){
        //$where .= ""; 
        $where .= " AND date_format(pr.date,'%Y-%m-%d') = '$date' "; 
    }
    if($userID != "" || $userID != NULL){
        $where .= " AND  pr.created_by = {$userID} "; 
    }
    if($stock !=''){
      $where .= " AND pr.billers = {$stock} ";
   }
    if($supplier !=''){
        $where .= " AND pr.cus_id = {$supplier} "; 
    }

    $query = $this->db->query("SELECT
        pr.rec_id,
        pr.cus_id,
        pr.date,
        pr.amount_usd,
        pr.amount_r,
        pr.amount_b,
        pr.`delete`,
        pr.billers,
        pr.created_by,
        u.username as sale_man,
        cus.company as Customer_name,
        cus.name as Customer_name,
        cus.address as address,
        cus.phone as phone,
        bil.company as warehouse
        FROM
        cus_payment_record pr
        INNER JOIN cus_companies cus
        ON cus.id = pr.cus_id
        INNER JOIN cus_companies bil
        ON bil.id = pr.billers

        LEFT JOIN cus_users u
        ON u.id = pr.created_by
        {$where}
        AND
        cus.group_name = 'customer'

        ");
    return $query->result();
    }

	
}
