<?php defined('BASEPATH') or exit('No direct script access allowed');

class Rentals_check_in extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->cus->md('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('rentals', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('rentals_model');
         $this->load->model('hr_model');
        $this->digital_upload_path = 'files/';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

    public function index($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['payment_status'] = $payment_status;
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('checked_in_list')));
		$meta = array('page_title' => lang('rentals'), 'bc' => $bc);
        $this->core_page('rentals/rental_check_in', $meta, $this->data);
    }
	
	public function getCheckIn($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
    {
        $this->load->library('datatables');
		$detail_link = anchor('rentals/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'));
		$create_sale = '';
        $deposit_link = '';
        $return_deposit_link = '';
		$view_deposit_link = '';
		if(($this->Admin || $this->Owner) || $this->GP['rentals-add']){

            $return_check_in = "<a href='#' class='po rentals-checked_in' title='<b>" . lang("return_check_in") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('rentals/checked_in/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"icon fa fa-sign-in tip\"></i> "
            . lang('return_check_in') . "</a>";

			$create_sale = anchor('sales/add/?rental_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('create_sale'), ' class="rentals-create_sale"');
			$deposit_link = anchor('rentals/add_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_deposit'), ' class="rentals-deposit" data-toggle="modal" data-target="#myModal"');
            $return_deposit_link = anchor('rentals/add_return_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_return_deposit'), ' class="rentals-return_deposit" data-toggle="modal" data-target="#myModal"');
            $view_deposit_link = anchor('rentals/view_deposits/$1', '<i class="fa fa-money"></i> ' . lang('view_deposits'), ' class="rentals-view_deposit1" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		}
		$edit_link = '';
		if(($this->Admin || $this->Owner) || $this->GP['rentals-edit']){
			$edit_link = anchor('rentals/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_rental'), ' class="rentals-edit" ');
		}
		$delete_link = '';
		if(($this->Admin || $this->Owner) || $this->GP['rentals-delete']){
			$delete_link = "<a href='#' class='po rentals-delete' title='<b>" . lang("delete_rental") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('rentals/delete/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_rental') . "</a>";
		}
		$action = '<div class="text-center"><div class="btn-group text-left">'
					. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
					. lang('actions') . ' <span class="caret"></span></button>
						<ul class="dropdown-menu pull-right" role="menu">
							<li>' . $detail_link . '</li>
                            <li>' . $return_check_in . '</li>
							<li>' . $create_sale . '</li>
                            <li>' . $deposit_link . '</li>
                            <li>' . $return_deposit_link . '</li>
							<li>' . $view_deposit_link . '</li>
							<li>' . $edit_link . '</li>
							<li>' . $delete_link . '</li>
						</ul>
					</div></div>';
        $this->datatables->select("
					rentals.id as id,
					rentals.date,
					rentals.reference_no,
					rentals.customer,
					companies.phone as phone,
					rental_rooms.name as room_name,
					rentals.from_date,
					rentals.to_date,
					IFNULL(cus_rentals.grand_total,0) as grand_total,
					IFNULL(cus_payments.amount,0)-IFNULL(cus_return_payments.amount,0)-IFNULL(cus_sale_payments.amount,0) as deposit,
					rentals.checked_in,
					rentals.status,
					rentals.attachment,
					rentals.room_id as room_id", false)
            ->from("rentals")
			->join('rental_rooms', 'rental_rooms.id=rentals.room_id', 'left')
			->join('companies', 'companies.id=rentals.customer_id', 'left')
			->join('(SELECT 
							transaction_id,
							SUM(amount) as amount
						FROM cus_payments 
                        WHERE transaction="RentalDeposit" AND type ="received" GROUP BY transaction_id) as cus_payments','cus_payments.transaction_id=rentals.id','left')
            ->join('(SELECT 
                        transaction_id,
                        SUM(amount) as amount
                    FROM cus_payments 
                    WHERE transaction="ReturnRentalDeposit" AND type ="sent"  GROUP BY transaction_id) as cus_return_payments','cus_return_payments.transaction_id=rentals.id','left')
			->join('(SELECT 
							transaction_id,
							SUM(amount) as amount
						FROM cus_payments 
						WHERE transaction="RentalDeposit" AND type ="sent"  GROUP BY transaction_id) as cus_sale_payments','cus_sale_payments.transaction_id=rentals.id','left')
			->group_by('rentals.id')
            ->order_by('rentals.from_date DESC')
            ->where('rentals.status =','checked_in')
            //->where('rentals.from_date =',date("Y-m-d"))
            ->add_column("Actions", $action, "id,room_id");
		
		if($payment_status){
			$this->datatables->where('DATE_SUB(from_date, INTERVAL 0 DAY) <=', date("Y-m-d"));
			$this->datatables->where('rentals.status','checked_in');
		}
		if($biller_id){
			$this->datatables->where("rentals.biller_id", $biller_id);
		}
		if ($warehouse_id) {
            $this->datatables->where('rentals.warehouse_id', $warehouse_id);
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('rentals.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('rentals.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('rentals.created_by', $this->session->userdata('user_id'));
		}
			$this->datatables->unset_column("room_id,count");
        echo $this->datatables->generate();
    }
	
	public function add($room_id = false)
    {
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        $this->form_validation->set_rules('room', $this->lang->line("room"), 'required');
		$this->form_validation->set_rules('frequency', $this->lang->line("frequency"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
		if ($this->form_validation->run() == true) {
            $sources = $this->site->getSourcesByID($this->input->post('sources'));
			$biller_id = $this->input->post('biller');
            $rooms = $this->site->getRoomsByID($this->input->post('room'));
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ren',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['rentals-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $room_type = $this->input->post('room_type');
			$floor_id = $this->input->post('floor');
            $room_id = $this->input->post('room');
            $status = $this->input->post('status');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->name != '-'  ? $customer_details->name : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$frequency = $this->input->post('frequency');
			$from_date = $this->cus->fld($this->input->post('from_date'));
			$to_date = $this->cus->fld($this->input->post('to_date'));
            $note = $this->cus->clear_tags($this->input->post('note'));
			$staff_note = $this->cus->clear_tags($this->input->post('staff_note'));
			$contract_period = $this->input->post('contract_period');
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
				$item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$parent_id = $_POST['parent_id'][$r];
				$electricity = $_POST['electricity'][$r];
				$old_number = $_POST['old_number'][$r];
				$new_number = $_POST['new_number'][$r];
                $check_time = $this->cus->fld(trim($_POST['check_time'][$r]));
                $item_service_type = $_POST['service_types'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
					$currency_rate = 1;
					if($this->config->item('product_currency')==true){
						$currency_rate = $_POST['currency_rate'][$r];
						$currency_code = $_POST['currency_code'][$r];
						if($currency_rate > 1){
							$real_unit_price = $real_unit_price / $currency_rate;
							$unit_price = $unit_price / $currency_rate;
							$item_discount = $item_discount / $currency_rate;
							$item_tax_rate = $item_tax_rate / $currency_rate;
						}
					}
					$product_details = $item_type != 'manual' ? $this->rentals_model->getProductByCode($item_code) : null;
                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->cus->formatDecimalRaw(((($this->cus->formatDecimalRaw($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->cus->formatDecimalRaw($discount);
                        }
                    }

                    $unit_price = $this->cus->formatDecimalRaw($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->cus->formatDecimalRaw($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimalRaw((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                            $item_tax = $this->cus->formatDecimalRaw($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->cus->formatDecimalRaw($item_tax * $item_unit_quantity, 4);
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);
                    $service_types = $this->site->getServiceTypesByID($service_type);
                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->cus->formatDecimalRaw($subtotal),
                        'real_unit_price' => $real_unit_price,
						'parent_id' => $parent_id,
						'comment' => $item_comment,
						'electricity' => $electricity,
						'old_number' => $old_number,
                        'service_types' => $item_service_type,
						'new_number' => $new_number,
						'currency_rate' => $currency_rate,
                        'check_time' => $check_time,
						'currency_code' => $currency_code
                    );

                    $total += $this->cus->formatDecimalRaw(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->cus->formatDecimalRaw(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->cus->formatDecimalRaw($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;
            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
            $total_tax = $this->cus->formatDecimalRaw(($product_tax + $order_tax), 4); 
            $grand_total = $this->cus->formatDecimalRaw(($total + $total_tax - $order_discount), 4);
            $data = array(
                'date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'warehouse_id' => $warehouse_id,
                'floor_id' => $rooms->floor,
                'room_type_id' => $this->input->post('room_type'),
                'room_id' => $this->input->post('room'),
                'room_name' => $rooms->name,
                'source_id' => $this->input->post('sources'),
                'sources' => $sources->name,
                'adult' =>  $this->input->post('adult'),
                'kid' =>  $this->input->post('kid'),
				'frequency' => $frequency,
				'from_date' => $from_date,
				'to_date' => $to_date,
				'note' => $note,
                'staff_note' => $staff_note,
				'contract_period' => $contract_period,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
            );
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }
        if ($this->form_validation->run() == true && $this->rentals_model->addRental($data, $products)) {
            $this->session->set_userdata('remove_rtls', 1);
            $this->session->set_flashdata('message', $this->lang->line("rental_added"));
            if($this->input->post('add_rental_next')){
				redirect('rentals_check_in/add');
			}else{
				redirect('rentals_check_in');
			}
        } else {

            $service_types = $this->site->getAllServiceTypes();
            $service_type_html = '<select name="service_type[]"  class="form-control select service_type" style="width:100%;">';
            if($service_types){
                foreach($service_types as $service_type){
                    $service_type_html .='<option value="'.$service_type->id.'">'.$service_type->name.'</option>';
                }
            }
            
            $service_type_html .='</select>';
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] =  $this->site->getBillers();
            $this->data['service_type'] = $service_type_html;
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['sources'] = $this->rentals_model->getAllSourceTypes();
            $this->data['rooms_no'] = $this->rentals_model->getAllRoomNumbers();
			$this->data['rooms'] = $this->rentals_model->getAllRooms();
			$this->data['floors'] = $this->rentals_model->getRoomFloors();
            $this->data['room_types'] = $this->rentals_model->getRoomTypes();
			$this->data['frequencies'] = $this->rentals_model->getFrequencies();
            $this->data['groups'] = $this->site->getAllCommissionTypeBySalemanID($id);
            $this->data['commission_types'] = $this->site->getAllCommisionTypes();
            $this->data['rtnumber'] = '';
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('rentals'), 'page' => lang('rentals')), array('link' => '#', 'page' => lang('add_check_in')));
			$meta = array('page_title' => lang('add_check_in'), 'bc' => $bc);
            $this->core_page('rentals/add_check_in', $meta, $this->data);
        }
    }
	
	

}
