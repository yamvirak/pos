<?php defined('BASEPATH') or exit('No direct script access allowed');

class Rentals extends MY_Controller
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
        //$this->load->model('reports_model');
        $this->load->model('hr_model');
        $this->digital_upload_path = 'files/';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

    public function index($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
    {
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['payment_status'] = $payment_status;
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('rentals')));
		$meta = array('page_title' => lang('rentals'), 'bc' => $bc);
        $this->core_page('rentals/index', $meta, $this->data);
    }
	
	public function getRentals($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
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
            //->where('rentals.status !=','checked_out')
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


    public function reservations($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
    {
        $this->cus->checkPermissions("index");
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $this->data['billers'] = $this->site->getBillers();
        $this->data['payment_status'] = $payment_status;
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reservation_list')));
        $meta = array('page_title' => lang('rentals'), 'bc' => $bc);
        $this->core_page('rentals/rental_reservation', $meta, $this->data);
    }
    
    public function getReservations($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
    {
        $this->load->library('datatables');
        $detail_link = anchor('rentals/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('rental_details'));
        $create_sale = '';
        $deposit_link = '';
        $return_deposit_link = '';
        $view_deposit_link = '';
        if(($this->Admin || $this->Owner) || $this->GP['rentals-add']){
            
            //$return_check_in = anchor('sales/add/?$1', '<i class="icon fa fa-sign-in tip"></i> ' . lang('return_check_in'), ' class="rentals-return_check_in"');

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
                    rental_rooms.room_type_name,
                    rental_rooms.name as room_name,
                    rentals.frequency,
                    rentals.from_date,
                    rentals.to_date,
                    companies.nationality,
                    IFNULL(cus_rentals.grand_total,0) as grand_total,
                    IFNULL(cus_payments.amount,0)-IFNULL(cus_return_payments.amount,0)-IFNULL(cus_sale_payments.amount,0) as deposit,
                    rentals.adult,
                    rentals.kid,
                    rentals.note,
                    companies.name,
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
            ->where('rentals.status =','reservation')
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

    public function cancelled_reservation_list($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
    {
        $this->cus->checkPermissions("index");
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $this->data['billers'] = $this->site->getBillers();
        $this->data['payment_status'] = $payment_status;
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('rentals')));
        $meta = array('page_title' => lang('rentals'), 'bc' => $bc);
        $this->core_page('rentals/cancelled_reservation_list', $meta, $this->data);
    }
    
    public function getCancelledReservationList($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
    {
        $this->load->library('datatables');
        $detail_link = anchor('rentals/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('rental_details'));
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
                    rentals.note,
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
            ->where('rentals.status =','cancelled')
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

     public function rental_check_out($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
    {
        $this->cus->checkPermissions("index");
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $this->data['billers'] = $this->site->getBillers();
        $this->data['payment_status'] = $payment_status;
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('checked_out_list')));
        $meta = array('page_title' => lang('rentals'), 'bc' => $bc);
        $this->core_page('rentals/rental_check_out', $meta, $this->data);
    }
    
    public function getRentalCheckOut($warehouse_id = null, $biller_id = NULL, $payment_status = NULL)
    {
        $this->load->library('datatables');
        $detail_link = anchor('rentals/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('rental_details'));
        $create_sale = '';
        $deposit_link = '';
        $return_deposit_link = '';
        $view_deposit_link = '';
        if(($this->Admin || $this->Owner) || $this->GP['rentals-add']){
            
            //$return_check_in = anchor('sales/add/?$1', '<i class="icon fa fa-sign-in tip"></i> ' . lang('return_check_in'), ' class="rentals-return_check_in"');

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
            ->where('rentals.status =','checked_out')
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

    public function checked_in($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $rental = $this->rentals_model->getRentalByID($id);
        if ($this->rentals_model->updateStatusRentalByID($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("rental_checked_in");
                die();
            }
            $this->session->set_flashdata('message', lang('rental_checked_in'));
            redirect('pos');
        }
    }
	
	public function add($room_id = false)
    {
        $this->cus->checkPermissions('add');
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
			$floor_id = $this->input->post('floor');
            $room_id = $this->input->post('room');
            $status = $this->input->post('status');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->name != '-'  ? $customer_details->name : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->name != '-' ? $biller_details->name : $biller_details->name;
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
						'new_number' => $new_number,
                        'check_time' => $check_time,
						'currency_rate' => $currency_rate,
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
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'warehouse_id' => $warehouse_id,
                'floor_id' => $room->floor,
                'room_id' => $this->input->post('room'),
                'room_type_id' => $this->input->post('room_type'),
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
				redirect('rentals/add');
			}else{
				redirect('rentals/reservations');
			}
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] =  $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['tax_validations'] = $this->site->getAllTaxValidations();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['room_types'] = $this->rentals_model->getRoomTypes();
			$this->data['rooms'] = $this->rentals_model->getAllRooms();
            $this->data['sources'] = $this->rentals_model->getAllSourceTypes();
			$this->data['floors'] = $this->rentals_model->getRoomFloors();
			$this->data['frequencies'] = $this->rentals_model->getFrequencies();
            $this->data['rtnumber'] = '';
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('rentals'), 'page' => lang('rentals')), array('link' => '#', 'page' => lang('add_rental')));
			$meta = array('page_title' => lang('add_rental'), 'bc' => $bc);
            $this->core_page('rentals/add', $meta, $this->data);
        }
    }
	
	public function edit($id = null)
    {
        $this->cus->checkPermissions('edit');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->rentals_model->getRentalByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required")); 
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('frequency', $this->lang->line("frequency"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
		
        if ($this->form_validation->run() == true) {
            $sources = $this->site->getSourcesByID($this->input->post('sources'));
            $service_type = $this->rentals_model->getServiceTypesByID($this->input->post('service_type'));
            $rooms = $this->site->getRoomsByID($this->input->post('room'));
			$biller_id = $this->input->post('biller');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rental',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['rentals-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $floor_id = $this->input->post('floor');
            $status = $this->input->post('status');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->name != '-'  ? $customer_details->name : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->name != '-' ? $biller_details->name : $biller_details->name;
			$room_id = $this->input->post('room');
			$frequency = $this->input->post('frequency');
			$from_date = $this->cus->fld($this->input->post('from_date'));
			$to_date = $this->cus->fld($this->input->post('to_date'));
            $note = $this->input->post('note');
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
                $item_service_type = $_POST['service_types'][$r];
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
                        'service_types' => $item_service_type,
						'electricity' => $electricity,
						'old_number' => $old_number,
                        'check_time' => $check_time,
						'new_number' => $new_number,
						'currency_rate' => $currency_rate,
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
                        $order_tax = $this->cus->formatDecimalRaw(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
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
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
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

        if ($this->form_validation->run() == true && $this->rentals_model->updateRental($id, $data, $products)) {
            $this->session->set_userdata('remove_rtls', 1);
            $this->session->set_flashdata('message', $this->lang->line("rental_updated"));
            redirect('rentals');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $this->rentals_model->getRentalByID($id);
            $inv_items = $this->rentals_model->getAllRentalItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                } else {
                    unset($row->details, $row->product_details, $row->cost, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
                }
                $row->quantity = 0;
                $pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
				$row->fup = 1;
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $row->price ? $row->price : $item->real_unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $this->cus->formatDecimalRaw($item->net_unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity));
                $row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity) + $this->cus->formatDecimalRaw($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate = $item->tax_rate_id;
                $row->option = $item->option_id;
				$row->parent_id = $item->parent_id;
				$row->comment = $item->comment;
                $row->service_types = $item->service_types;
                $row->check_time = $this->cus->hrsd($item->check_time);
				$room_rent = false;
				if($this->config->item('room_rent') && $inv->room_id > 0){
					$row->old_number = (double)$item->old_number;
					$row->new_number = (double)$item->new_number;
					$room_rent = true;
				}
				
				$options = $this->rentals_model->getProductOptions($row->id, $item->warehouse_id);
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getStockmoves($row->id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
						if($option->id == $item->option_id){
							 $option->quantity += $item->quantity;
						}
                    }
                }
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'room_rent' => $room_rent);
                $c++;
            }
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['sources'] = $this->rentals_model->getAllSourceTypes();
            $this->data['billers'] = $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['room_types'] = $this->rentals_model->getRoomTypes();
			$this->data['floors'] = $this->rentals_model->getRoomFloors();
			$this->data['rooms'] = $this->rentals_model->getAllRooms();
			$this->data['frequencies'] = $this->rentals_model->getFrequencies();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('rentals'), 'page' => lang('rentals')), array('link' => '#', 'page' => lang('edit_rental')));
			$meta = array('page_title' => lang('edit_rental'), 'bc' => $bc);
            $this->core_page('rentals/edit', $meta, $this->data);
        }
    }

	public function rental_actions()
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])){
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete_rental');
                    foreach ($_POST['val'] as $id) {
                        $this->rentals_model->deleteRental($id);
                    }
                    $this->session->set_flashdata('message', lang("rental_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('rentals'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('room'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('from_date'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('to_date'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('total'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('deposit'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('checked_in_date'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('created_by'));
					$this->excel->getActiveSheet()->SetCellValue('M1', lang('status'));
					$style = array(
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						)
					);
					$this->excel->getActiveSheet()->getStyle("A1:M1")->applyFromArray($style)->getFont()->setBold(true);
                    $row = 2;
                    foreach ($_POST['val'] as $id){
                        $rental = $this->rentals_model->getRentalByID($id);
						$room = $this->rentals_model->getRoomByID($rental->room_id);
						$user = $this->site->getUser($rental->created_by);
						$customer = $this->site->getCompanyByID($rental->customer_id);
                        $deposit = $this->rentals_model->getRentalDepositPayments($id, "RentalDeposit","received");
                        $deposit_sale = $this->rentals_model->getRentalDepositPayments($id, "RentalDeposit","sent");
                        $return_deposit = $this->rentals_model->getRentalDepositPayments($id, "ReturnRentalDeposit","sent");
                        $deposit_amount = $deposit->amount - (double)$deposit_sale->amount - (double)$return_deposit->amount;

						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($rental->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $rental->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $rental->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $rental->customer);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->phone);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $room->name);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->hrsd($rental->from_date));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->hrsd($rental->to_date));
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->cus->formatDecimal($rental->grand_total));
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->cus->formatDecimal($deposit_amount));
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->cus->hrsd($rental->checked_in));
						$this->excel->getActiveSheet()->SetCellValue('L' . $row, $user->last_name.' '.$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, lang($rental->status));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'rentals_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_rentals_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}

	public function delete($id = null)
    {
        $this->cus->checkPermissions("delete");
        if ($this->rentals_model->deleteRental($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("rental_deleted"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("rental_deleted"));
        redirect($_SERVER["HTTP_REFERER"]);
    }
	
	public function suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);
		$room_id = $this->input->get('room_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->rentals_model->getProductNames($sr, $warehouse_id);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option = false;
                $row->quantity = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty = 1;
                $row->discount = '0';
                $options = $this->rentals_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->rentals_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = FALSE;
                }
                $row->option = $option_id;
                $pis = $this->site->getStockmoves($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getStockmoves($row->id, $warehouse_id, $row->option);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        if ($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                    }
                }
				$currency_rate = false;
				if($this->config->item('product_currency')==true){
					$currency_rate = $row->currency_rate;
					$row->price = $row->price * $currency_rate;
				}
				$room_rent = false;
				if($this->config->item('room_rent') && $room_id > 0){
					$old_number = $this->rentals_model->getOldNumber($room_id, $row->id);
					$row->old_number = (double)$old_number;
					$room_rent = true;
				}
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                if ($row->promotion && date('Y-m-d') >= $row->start_date && date('Y-m-d') <= $row->end_date) {
					$row->discount = (100-(($row->promo_price / $row->price) * 100)).'%';
                }else if($this->Settings->customer_price == 1 && $customer_price = $this->rentals_model->getCustomerPrice($row->id,$customer_id)){
					if (isset($customer_price) && $customer_price != false) {
						if($customer_price->price > 0){
							$row->price = $customer_price->price;
						}
					}
				} else if ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                } else if ($warehouse->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                }
				
				$row->price = $row->price + (($row->price * $customer_group->percent) / 100);
				$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				$row->real_unit_price = $row->price;
				$row->unit_price = $row->price;
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'room_rent'=>$room_rent, 'currency_rate' => $currency_rate);
                $r++;
            }
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	public function get_product_room()
    {
        if ($this->input->get('room_id')) {
            $room = $this->input->get('room_id', TRUE);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', TRUE);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', TRUE);
        }
        if (!$room) {
            echo NULL;
            die();
        }
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row = $this->rentals_model->getWHProductByRoom($room, $warehouse_id);
        if ($row) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
			unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
			$option = false;
			$row->quantity = 0;
			$row->item_tax_method = $row->tax_method;
			$row->qty = 1;
			$row->discount = '0';
			$options = $this->rentals_model->getProductOptions($row->id, $warehouse_id);
			if ($options) {
				$opt = $option_id && $r == 0 ? $this->rentals_model->getProductOptionByID($option_id) : $options[0];
				if (!$option_id || $r > 0) {
					$option_id = $opt->id;
				}
			} else {
				$opt = json_decode('{}');
				$opt->price = 0;
				$option_id = FALSE;
			}
			$row->option = $option_id;
			$pis = $this->site->getStockmoves($row->id, $warehouse_id, $row->option);
			if ($pis) {
				foreach ($pis as $pi) {
					$row->quantity += $pi->quantity_balance;
				}
			}
			if ($options) {
				$option_quantity = 0;
				foreach ($options as $option) {
					$pis = $this->site->getStockmoves($row->id, $warehouse_id, $row->option);
					if ($pis) {
						foreach ($pis as $pi) {
							$option_quantity += $pi->quantity_balance;
						}
					}
					if ($option->quantity > $option_quantity) {
						$option->quantity = $option_quantity;
					}
				}
			}
			if($row->rprice > 0){
				$row->price = $row->rprice;
                
			}
			$currency_rate = false;
			if($this->config->item('product_currency')==true){
				$currency_rate = $row->currency_rate;
				$row->price = $row->price * $currency_rate;
			}
			$row->base_quantity = 1;
			$row->base_unit = $row->unit;
			$row->base_unit_price = $row->price;
			$row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
			if ($row->promotion && date('Y-m-d') >= $row->start_date && date('Y-m-d') <= $row->end_date) {
				$row->discount = (100-(($row->promo_price / $row->price) * 100)).'%';
			}else if($this->Settings->customer_price == 1 && $customer_price = $this->rentals_model->getCustomerPrice($row->id,$customer_id)){
				if (isset($customer_price) && $customer_price != false) {
					if($customer_price->price > 0){
						$row->price = $customer_price->price;
					}
				}
			} else if ($customer->price_group_id) {
				if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
					$row->price = $pr_group_price->price;
				}
			} else if ($warehouse->price_group_id) {
				if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
					$row->price = $pr_group_price->price;
				}
			}
			$row->price = $row->price + (($row->price * $customer_group->percent) / 100);
			$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
			$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
			$row->real_unit_price = $row->price;
			$row->unit_price = $row->price;
			$pr = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'currency_rate' => $currency_rate);
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	public function modal_view($id = null)
    {
		$rental = $this->rentals_model->getRentalByID($id);
		$this->data['inv'] = $rental;
		$this->data['room'] = $this->rentals_model->getRoomByID((int)$rental->room_id);
		$this->data['rows'] = $this->rentals_model->getAllRentalItems($rental->id);
		$this->data['customer'] = $this->site->getCompanyByID($rental->customer_id);
        $this->data['frequency'] = $this->rentals_model->getFrequencyByID($rental->frequency);
        $this->data['created_by'] = $this->site->getUser($rental->created_by);
        $this->data['biller'] = $this->site->getCompanyByID($rental->biller_id);
		$this->data['deposits'] = $this->rentals_model->getDeposits($rental->id);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Rental',$rental->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Rental',$rental->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
        $this->load->view($this->theme . 'rentals/modal_view', $this->data);
    }

	public function update_status($id)
    {
        $this->form_validation->set_rules('status', lang("status"), 'required');
		$rental = $this->rentals_model->getRentalByID($id);
		if($rental->status == 'checked_out'){
			$this->session->set_flashdata('error', lang('rental_already_checked_out'));
			$this->cus->md();
		}
        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $period = $this->input->post('period');
			$ckidate = $this->cus->fld($this->input->post('ckidate'));
			$ckodate = $this->cus->fld($this->input->post('ckodate'));
            $fdrdate = $this->cus->fld($this->input->post('from_date'));
            $note = $this->cus->clear_tags($this->input->post('note'));
            if($status == 'checked_out'){
                redirect('sales/add?rental_id='.$id."&checked_out_date=".$this->input->post('ckodate'));
            }
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'rentals');
        }
        if ($this->form_validation->run() == true && $this->rentals_model->updateStatus($id, $status, $fdrdate, $ckidate, $ckodate, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'rentals');
        } else {
            $this->data['rental'] = $rental;
            $this->data['rental_deposit'] = $this->rentals_model->getRentalDepositPayments($rental->id);
            $this->data['customer'] = $this->site->getCompanyByID($rental->customer_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'rentals/update_status', $this->data);
        }
    }

	public function view_rooms()
	{
		$this->cus->checkPermissions('index');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['floors'] = $this->rentals_model->getRoomFloors();
		$this->load->view($this->theme . 'rentals/view_rooms', $this->data);
	}
	
	public function ajax_rooms1()
	{
		$warehouse = $this->input->get("warehouse", true);
		$floor = $this->input->get("floor",true);
		$html = '';
		$rooms = $this->rentals_model->getAllRooms($floor, $warehouse);
		if($rooms){
			foreach($rooms as $room){
				$busy_room = $this->rentals_model->getRoomStatus($room->id);
				if($busy_room && $busy_room->status=='checked_in'){
					$html .= '<button href="'.site_url('rentals/modal_view/'.$busy_room->id).'" class="btn btn-success box-room" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal">
                                <div style="margin:-12px 0px; font-size:35px;font-weight:bold;">'.$room->name.'</div><span>'.$room->room_type_name.'</span><br>
                                    <span style="font-size:12px; font-weight:bold;"><span class="fa fa-calendar"></span> '.$this->cus->hrsd($busy_room->from_date).'</span><br/>
    								<span style="font-size:12px; font-weight:bold;"><span class="fa fa-sign-out"></span> '.$this->cus->hrsd($busy_room->to_date).'</span><br/>
                                    <div class="room_rental">'.$room->bed_number_name.'</div>
                                    <div class="box-footer" style="background:#e49800">
                                    <a class="btn tip" title="" <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                                </div>

							  </button>';
				}else if($busy_room && $busy_room->status=='reservation'){
					$html .= '<button href="'.site_url('rentals/modal_view/'.$busy_room->id).'" class="btn btn-warning box-room" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal">
                                 <div style="margin:-12px 0px; font-size:35px;font-weight:bold;">'.$room->name.'</div><span>'.$room->room_type_name.'</span><br>
								<span style="font-size:12px; font-weight:bold;"><span class="fa fa-calendar"></span> '.$this->cus->hrsd($busy_room->from_date).'</span><br/>
								<span style="font-size:12px; font-weight:bold;"><span class="fa fa-sign-out"></span> '.$this->cus->hrsd($busy_room->to_date).'</span><br/>
                                <div class="room_rental">'.$room->bed_number_name.'</div>
							  </button>';
				}else if($busy_room && $busy_room->status=='room_blocking'){
                    $html .= '<button href="'.site_url('rentals/modal_view/'.$busy_room->id).'" class="btn btn-danger box-room" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal">
                                 <div style="margin:-12px 0px; font-size:35px;font-weight:bold;">'.$room->name.'</div><span>'.$room->room_type_name.'</span><br>
                                <span style="font-size:12px; font-weight:bold;"><span class="fa fa-calendar"></span> '.$this->cus->hrsd($busy_room->from_date).'</span><br/>
                                <span style="font-size:12px; font-weight:bold;"><span class="fa fa-sign-out"></span> '.$this->cus->hrsd($busy_room->to_date).'</span><br/>
                                <div class="room_rental">'.$room->bed_number_name.'</div>
                              </button>';
                }else{
					$html .= '<a href="'.site_url('rentals/add').'" class="btn btn-primary box-room">
                                <div style="margin:-12px 0px; font-size:35px;font-weight:bold;">'.$room->name.'</div><span>'.$room->room_type_name.'</span><br>
								<span style="font-size:12px; font-weight:bold;"><span class="fa fa-calendar"></span> N/A</span><br/>
								<span style="font-size:12px; font-weight:bold;"><span class="fa fa-sign-out"></span> N/A</span><br/>
                                
                                <div class="room_rental">'.$room->bed_number_name.'</div>
							  </a>';
				}
			}
		}
		echo json_encode($html);
	}
    public function ajax_rooms()
    {
        $warehouse = $this->input->get("warehouse", true);
        $floor = $this->input->get("floor",true);
        $html = '';
        $rooms = $this->rentals_model->getAllRooms($floor, $warehouse);
        if($rooms){
            foreach($rooms as $room){
                $busy_room = $this->rentals_model->getRoomStatus($room->id);
                if($busy_room && $busy_room->status=='checked_in'){
                    $html .= '<a href="'.site_url('rentals/modal_view/'.$busy_room->id).'" class="" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal">
                                <div class="bg_green">
                                    <div class="ellipsis">
                                        <button class="ellipsis-btn">&#8942;</button>
                                    </div>
                                    <div class="house_status">
                                        Checked-In
                                    </div>
                                    <div class="select"><?=lang("Clean")?> </div>
                                    <div class="num_room_green">
                                        <h1 class="num_room_name">'.$room->name.'</h1>
                                        <h4>'.$room->room_type_name.'</h4>
                                    </div>
                                    <div class="date"><i class="fa fa-calendar-plus-o" aria-hidden="true"></i>'.$this->cus->hrsd($busy_room->from_date).'</div>
                                    <div><i class="fa fa-sign-out"></i>'.$this->cus->hrsd($busy_room->to_date).'</div>
                                    <div class="box_footer_checked_in room_rental">
                                         <span class="icon_bed_available">'.$room->bed_number_name.'</span>
                                    </div>
                                </div>
                              </a>';
                }else if($busy_room && $busy_room->status=='reservation'){
                    $html .= '<a href="'.site_url('rentals/modal_view/'.$busy_room->id).'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal">
                                 <div class="bg_orange">
                                    <div class="ellipsis">
                                        <button class="ellipsis-btn">&#8942;</button>
                                    </div>
                                    <div class="house_status">
                                        Reservation
                                    </div>
                                    <div class="select"><?=lang("Clean")?> </div>
                                    <div class="num_room_reservation">
                                        <h1 class="num_room_name">'.$room->name.'</h1>
                                        <h4>'.$room->room_type_name.'</h4>
                                    </div>
                                    <div class="date"><i class="fa fa-calendar-plus-o" aria-hidden="true"></i>'.$this->cus->hrsd($busy_room->from_date).'</div>
                                    <div><i class="fa fa-sign-out"></i>'.$this->cus->hrsd($busy_room->to_date).'</div>
                                    <div class="box_footer_reservation room_rental">
                                         <span class="icon_bed_available">'.$room->bed_number_name.'</span>
                                    </div>
                                </div>
                              </a>';
                }else if($busy_room && $busy_room->status=='room_blocking'){
                    $html .= '<a href="'.site_url('rentals/modal_view/'.$busy_room->id).'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal">
                                 <div class="bg_red">
                                    <div class="ellipsis">
                                        <button class="ellipsis-btn">&#8942;</button>
                                    </div>
                                    <div class="house_status">
                                        Maintenances
                                    </div>
                                    <div class="select"><?=lang("Clean")?> </div>
                                    <div class="num_room_red">
                                        <h1 class="num_room_name">'.$room->name.'</h1>
                                        <h4>'.$room->room_type_name.'</h4>
                                    </div>
                                    <div class="date"><i class="fa fa-calendar-plus-o" aria-hidden="true"></i>'.$this->cus->hrsd($busy_room->from_date).'</div>
                                    <div><i class="fa fa-sign-out"></i>'.$this->cus->hrsd($busy_room->to_date).'</div>
                                    <div class="box_footer_maintanance room_rental">
                                         <span class="icon_bed_available">'.$room->bed_number_name.'</span>
                                    </div>
                                </div>
                              </a>';
                }else{
                    $html .= '<a href="'.site_url('rentals/add').'">
                                <div class="bg_blue">
                                    <div class="ellipsis">
                                        <button class="ellipsis-btn">&#8942;</button>
                                    </div>
                                    <div class="house_status">
                                        Free
                                    </div>
                                    <div class="select"><?=lang("Clean")?> </div>
                                    <div class="num_room_available">
                                        <h1 class="num_room_name">'.$room->name.'</h1>
                                        <h4>'.$room->room_type_name.'</h4>
                                    </div>
                                    <div class="date"><i class="fa fa-calendar-plus-o" aria-hidden="true"></i>N/A</div>
                                    <div><i class="fa fa-sign-out"></i>N/A</div>
                                    <div class="box_footer_list room_rental">
                                         <span class="icon_bed_available">'.$room->bed_number_name.'</span>
                                    </div>
                                </div>
                              </a>';
                }
            }
        }
        echo json_encode($html);
    }
	
	public function view($id = false)
	{
		$rental = $this->rentals_model->getRentalByID($id);
		$this->data['id'] = $id;
		$this->data['rental'] = $rental;
		$this->data['room'] = $this->rentals_model->getRoomByID((int)$rental->room_id);
        $this->data['food_order_items'] = $this->rentals_model->getAllFoodOrderItems($id);
		$this->data['rental_items'] = $this->rentals_model->getAllRentalItems($id);
        $this->data['deposits'] = $this->rentals_model->getDeposits($rental->id);
		$this->data['frequency'] = $this->rentals_model->getFrequencyByID($rental->frequency);
		$this->data['customer'] = $this->site->getCompanyByID($rental->customer_id);
		$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('rentals'), 'page' => lang('rentals')), array('link' => '#', 'page' => lang('rental_details')));
        $meta = array('page_title' => lang('rental_details'), 'bc' => $bc);
        $this->core_page('rentals/view', $meta, $this->data);
	}
	
	public function getSales($id = false)
	{
		$this->load->library('datatables');
		$add_payment = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), ' class="rentals-edit" data-toggle="modal" data-target="#myModal2"');
		$payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_payment" data-target="#myModal"');
		$edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
		$delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . "</a>";
            
		$action = '<div class="text-center"><div class="btn-group text-left">'
					. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
					. lang('actions') . ' <span class="caret"></span></button>
						<ul class="dropdown-menu pull-right" role="menu">
							<li>' . $add_payment . '</li>
							<li>' . $payments_link . '</li>
							<li>' . $edit_link . '</li>
							<li>' . $delete_link . '</li>
						</ul>
                    </div></div>';
                    
		$this->datatables->select("
					sales.id as id,
					sales.date,
					sales.reference_no,
					sales.customer,
					sales.from_date,
					sales.to_date,
					sales.grand_total,
					cus_payments.paid,
					cus_sales.grand_total - (cus_payments.paid - cus_payments.discount) as balance,
					sales.payment_status
					", false)
            ->from("sales")
			->join('rentals','sales.rental_id=rentals.id','left')
			->join('users', 'users.id=rentals.created_by', 'left')
			->join('(SELECT
						sale_id,
						IFNULL(sum(amount),0) AS paid,
						IFNULL(sum(discount),0) AS discount
					FROM
						'.$this->db->dbprefix('payments').'
					GROUP BY
						sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left')
			->where("sales.rental_id", $id)
            ->or_where("sales.rental_service_id", $id)
			->group_by('sales.id')
            ->add_column("Actions", $action, "id");
			$this->datatables->unset_column("id");
			$this->datatables->add_column(false,"$1","id");
        echo $this->datatables->generate();
	}
	
	public function sales_actions($id = NULL, $pdf = NULL, $xls = NULL)
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($pdf || $xls) {
			$this->db->select("
					sales.id as id,
					sales.date,
					sales.reference_no,
					sales.customer,
					sales.from_date,
					sales.to_date,
					sales.grand_total,
					cus_payments.paid,
					cus_sales.grand_total - (cus_payments.paid - cus_payments.discount) as balance,
					sales.payment_status,
					concat(cus_users.last_name,' ',cus_users.first_name) as created_by
					", false)
            ->from("sales")
			->join('rentals','sales.rental_id=rentals.id','left')
			->join('users', 'users.id=rentals.created_by', 'left')
			->join('(SELECT
						sale_id,
						IFNULL(sum(amount),0) AS paid,
						IFNULL(sum(discount),0) AS discount
					FROM
						'.$this->db->dbprefix('payments').'
					GROUP BY
						sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left')
			->where("sales.rental_id", $id)
			->group_by('sales.id');
			
			$q = $this->db->get();
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			} else {
				$data = NULL;
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('rental_details'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('from_date'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('to_date'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('created_by'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));
				$style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
				);
				$this->excel->getActiveSheet()->getStyle("A1:J1")->applyFromArray($style)->getFont()->setBold(true);
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->cus->hrsd($data_row->from_date));
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->cus->hrsd($data_row->to_date));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->formatMoney($data_row->grand_total));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->formatMoney($data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->formatMoney($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->created_by);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($data_row->payment_status));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$filename = 'rental_details_'.date("Y_m_d_H_i_s");
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
                create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	
	public function add_deposit($id = null)
    {
        $this->cus->checkPermissions('deposits', true, 'customers');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $rental = $this->rentals_model->getRentalByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin  || $this->cus->GP['rentals-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$rental->biller_id);
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_to = $cash_account->account_code;
			
            $payment = array(
                'date' => $date,
				'reference_no' => $reference_no,
                'transaction_id' => $this->input->post('rental_id'),
				'transaction' => 'RentalDeposit',
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'received',
				'currencies' => json_encode($currencies),
				'account_code' => $paying_to,
            );
			
			if($this->Settings->accounting == 1){
				$paymentAcc = $this->site->getAccountSettingByBiller($rental->biller_id);
				$accTranPayments[] = array(
						'transaction' => 'RentalDeposit',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $paymentAcc->customer_deposit_acc,
						'amount' => -($this->input->post('amount-paid')+$this->input->post('discount')),
						'narrative' => 'RentalDeposit '.$rental->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $rental->biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $rental->customer_id,
					);
				$accTranPayments[] = array(
						'transaction' => 'RentalDeposit',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $paying_to,
						'amount' => $this->input->post('amount-paid'),
						'narrative' => 'RentalDeposit '.$rental->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $rental->biller_id,
						'project_id' => $rental->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $rental->customer_id,
					);
			}

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->rentals_model->addDeposit($payment, $customer_id, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("deposit_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['rental'] = $rental;
            $this->data['payment_ref'] = '';
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'rentals/add_deposit', $this->data);
        }
    }

	public function edit_deposit($id = null)
    {
        $this->cus->checkPermissions('deposits', true, 'customers');
        $this->load->helper('security');
		if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$deposit = $this->rentals_model->getPaymentByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
			$rental = $this->rentals_model->getRentalByID($deposit->transaction_id);
			$customer_id = null;
            if ($this->Owner || $this->Admin  || $this->cus->GP['rentals-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$rental->biller_id);
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_to = $cash_account->account_code;
			$payment = array(
                'date' => $date,
				'reference_no' => $reference_no,
                'transaction_id' => $this->input->post('rental_id'),
				'transaction' => 'RentalDeposit',
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date("Y-m-d H:i:s"),
                'type' => 'received',
				'currencies' => json_encode($currencies),
				'account_code' => $paying_to,
            );

			if($this->Settings->accounting == 1){
				$paymentAcc = $this->site->getAccountSettingByBiller($rental->biller_id);
				$accTranPayments[] = array(
						'transaction_id' => $id,
						'transaction' => 'RentalDeposit',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $paymentAcc->customer_deposit_acc,
						'amount' => -($this->input->post('amount-paid')+$this->input->post('discount')),
						'narrative' => 'RentalDeposit '.$rental->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $rental->biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $rental->customer_id,
					);
				$accTranPayments[] = array(
						'transaction_id' => $id,
						'transaction' => 'RentalDeposit',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $paying_to,
						'amount' => $this->input->post('amount-paid'),
						'narrative' => 'RentalDeposit '.$rental->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $rental->biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $rental->customer_id,
					);
			}
				
			//=====end accounting=====//

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }
			
        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->rentals_model->updateDeposit($id, $payment, $customer_id, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("deposit_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$this->data['rental'] = $this->rentals_model->getRentalByID($deposit->transaction_id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['deposit'] = $deposit;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'rentals/edit_deposit', $this->data);
        }
    }

	public function view_deposits($id = null)
    {
        $this->cus->checkPermissions('deposits', true, 'customers');
        $this->data['deposits'] = $this->rentals_model->getDepositsPayments($id);
        $this->data['rental'] = $this->rentals_model->getRentalByID($id);
        $this->load->view($this->theme . 'rentals/deposits', $this->data);
    }

	public function deposit_note($id = null)
    {
       $this->cus->checkPermissions('deposits', true, 'customers');
        $deposit = $this->rentals_model->getPaymentByID($id);
        $rental = $this->rentals_model->getRentalByID($deposit->transaction_id);
        $this->data['room'] = $this->rentals_model->getRoomByID($rental->room_id);
        $this->data['biller'] = $this->site->getCompanyByID($rental->biller_id);
        $this->data['customer'] = $this->site->getCompanyByID($rental->customer_id);
        $this->data['created_by'] = $this->site->getUser($rental->created_by);
        $this->data['rental'] = $rental;
		$this->data['deposit'] = $deposit;
        $this->data['page_title'] = lang("deposit_note");
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Deposit Note',$deposit->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Deposit Note',$deposit->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}

        $this->load->view($this->theme . 'rentals/deposit_note', $this->data);
    }

	public function delete_deposit($id = null)
    {
        $this->cus->checkPermissions('delete_deposit', true, 'customers');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$opay = $this->rentals_model->getPaymentByID($id);
        if ($this->rentals_model->deleteDeposit($id)) {
            $this->session->set_flashdata('message', lang("deposit_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }


    public function get_source_type_by_group()
    {
        $id = $this->input->get("source_type");
        $rows = $this->site->getAllSourceTypeByRoomRateID($id);
        $opt = array(lang("select")." ".lang("room_type"));
        if ($rows) {
            foreach($rows as $row){
                $opt[$row->id] = $row->room_type;
            }
        }
        $opt = form_dropdown('room_type', $opt, '', 'class="form-control select" id="room_type"');
        
        echo json_encode(array("result" => $opt));
    }
    public function get_room_group_by_source_type()
    {
        $id = $this->input->get("room_groups");
        $rows = $this->site->getAllRoomTypesByID($id);
        $opts = array();

        if ($rows) {
            foreach($rows as $room){
                $opts[$room->id] = $room->name;
            }
        }
        $opts = form_dropdown('room_group', $opts, (isset($_POST['room_group']) ? $_POST['room_group'] : ''), ' id="room_group" class="form-control" required="required"'); 

        echo json_encode(array("result" => $opts));
    }


    public function get_room_type()
    {
        $room_id = $this->input->get("room_id");
        $floor_id = $this->input->get("floor_id");
        $room_type_id = $this->input->get("room_type_id");
        $rooms = $this->rentals_model->getRoomsByTypes($room_type_id);
        if ($rooms) {
            $selection = '<select class="form-control" name="room" id="rtroom" required="required">';
                $selection .= '<option value="">'.lang('select').' '.lang('room').'</option>';
            foreach ($rooms as $room) {
                $unavailable = $this->rentals_model->getUnAvailableRoom($room->id,$fdrdate);
                if($unavailable && $room->id != $room_id){
                    $selection .= '<option value="'.$room->id.'" disabled>'.$room->name.'</option>';
                }else{
                    if($room->id == $room_id){
                        $selection .= '<option value="'.$room->id.'" selected>'.$room->name.'</option>';
                    }else{
                        $selection .= '<option value="'.$room->id.'">'.$room->name.'</option>';
                    }
                }
            }
            $selection .= '</select>';
        }
        echo json_encode(array("result" => $selection));
    }

    public function get_room_type_reservation()
    {
        $room_id = $this->input->get("room_id");
        $floor_id = $this->input->get("floor_id");
        $room_type_id = $this->input->get("room_type_id");
        $rooms = $this->rentals_model->getRoomsByTypes($room_type_id);
        if ($rooms) {
            $selection = '<select class="form-control" name="room" id="rtroom" required="required">';
                $selection .= '<option value="">'.lang('select').' '.lang('room').'</option>';
            foreach ($rooms as $room) {
                //$unavailable = $this->rentals_model->getUnAvailableRoom($room->id,$fdrdate);
                if($unavailable && $room->id != $room_id){
                    $selection .= '<option value="'.$room->id.'" disabled>'.$room->name.'</option>';
                }else{
                    if($room->id == $room_id){
                        $selection .= '<option value="'.$room->id.'" selected>'.$room->name.'</option>';
                    }else{
                        $selection .= '<option value="'.$room->id.'">'.$room->name.'</option>';
                    }
                }
            }
            $selection .= '</select>';
        }
        echo json_encode(array("result" => $selection));
    }

    public function get_room_reservation()
    {
        $room_id = $this->input->get("room_id");
        $floor_id = $this->input->get("floor_id");
        $rooms = $this->rentals_model->getRoomsByFloor($room_type_id);
        if ($rooms) {
            $selection = '<select class="form-control" name="room" id="rtroom" required="required">';
                $selection .= '<option value="">'.lang('select').' '.lang('room').'</option>';
            foreach ($rooms as $room) {
                //$unavailable = $this->rentals_model->getUnAvailableRoomReservation($room->id);
                if($unavailable && $room->id != $room_id){
                    $selection .= '<option value="'.$room->id.'" disabled>'.$room->name.'</option>';
                }else{
                    if($room->id == $room_id){
                        $selection .= '<option value="'.$room->id.'" selected>'.$room->name.'</option>';
                    }else{
                        $selection .= '<option value="'.$room->id.'">'.$room->name.'</option>';
                    }
                }
            }
            $selection .= '</select>';
        }
        echo json_encode(array("result" => $selection));
    }

    public function get_room_floor()
    {
        $room_id = $this->input->get("room_id");
        $floor_id = $this->input->get("floor_id");
        $rooms = $this->rentals_model->getRoomsByFloor($floor_id);
        if ($rooms) {
            $selection = '<select class="form-control" name="room" id="rtroom" required="required">';
                $selection .= '<option value="">'.lang('select').' '.lang('room').'</option>';
            foreach ($rooms as $room) {
                $unavailable = $this->rentals_model->getUnAvailableRoom($room->id,$fdrdate);
                if($unavailable && $room->id != $room_id){
                    $selection .= '<option value="'.$room->id.'" disabled>'.$room->name.'</option>';
                }else{
                    if($room->id == $room_id){
                        $selection .= '<option value="'.$room->id.'" selected>'.$room->name.'</option>';
                    }else{
                        $selection .= '<option value="'.$room->id.'">'.$room->name.'</option>';
                    }
                }
            }
            $selection .= '</select>';
        }
        echo json_encode(array("result" => $selection));
    }
    
    
    public function add_return_deposit($id = null)
    {
        $this->cus->checkPermissions('deposits', true, 'customers');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $rental = $this->rentals_model->getRentalByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $customer_id = null;
            if ($this->Owner || $this->Admin  || $this->cus->GP['rentals-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$rental->biller_id);
            $cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			$payment = array(
                'date' => $date,
				'reference_no' => $reference_no,
                'transaction_id' => $this->input->post('rental_id'),
				'transaction' => 'ReturnRentalDeposit',
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'sent',
				'currencies' => json_encode($currencies),
				'account_code' => $paying_from,
            );
			if($this->Settings->accounting == 1){
				$paymentAcc = $this->site->getAccountSettingByBiller($rental->biller_id);
				$accTranPayments[] = array(
						'transaction' => 'ReturnRentalDeposit',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $paymentAcc->customer_deposit_acc,
						'amount' => ($this->input->post('amount-paid')),
						'narrative' => 'ReturnRentalDeposit '.$rental->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $rental->biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $rental->customer_id,
					);
				$accTranPayments[] = array(
						'transaction' => 'ReturnRentalDeposit',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $paying_from,
						'amount' => -($this->input->post('amount-paid')),
						'narrative' => 'ReturnRentalDeposit '.$rental->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $rental->biller_id,
						'project_id' => $rental->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $rental->customer_id,
					);
			}

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

        } elseif ($this->input->post('add_return_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->rentals_model->addDeposit($payment, $customer_id, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("return_deposit_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['rental'] = $rental;
            $this->data['deposit'] = $this->rentals_model->getRentalDepositPayments($rental->id, "RentalDeposit","received");
            $this->data['deposit_sale'] = $this->rentals_model->getRentalDepositPayments($rental->id, "RentalDeposit","sent");
            $this->data['return_deposit'] = $this->rentals_model->getRentalDepositPayments($rental->id, "ReturnRentalDeposit","sent");
            $this->data['payment_ref'] = '';
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'rentals/add_return_deposit', $this->data);
        }
    }


        public function add_room_blocking($room_id = false)
    {
        $this->cus->checkPermissions('add');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        $this->form_validation->set_rules('room', $this->lang->line("room"), 'required');
        $this->form_validation->set_rules('frequency', $this->lang->line("frequency"), 'required');
        $this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
        $this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
        if ($this->form_validation->run() == true) {
            $biller_id = $this->input->post('biller');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ren',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['rentals-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $floor_id = $this->input->post('floor');
            $room_id = $this->input->post('room');
            $status = $this->input->post('status');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->name != '-'  ? $customer_details->name : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->name != '-' ? $biller_details->name : $biller_details->name;
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
                        'new_number' => $new_number,
                        'currency_rate' => $currency_rate,
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
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'floor_id' => $floor_id,
                'room_id' => $room_id,
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
                redirect('rentals/add');
            }else{
                redirect('rentals');
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] =  $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['rooms'] = $this->rentals_model->getAllRooms();
            $this->data['floors'] = $this->rentals_model->getRoomFloors();
            $this->data['frequencies'] = $this->rentals_model->getFrequencies();
            $this->data['rtnumber'] = '';
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('rentals'), 'page' => lang('rentals')), array('link' => '#', 'page' => lang('add_rental')));
            $meta = array('page_title' => lang('add_rental'), 'bc' => $bc);
            $this->core_page('rentals/add_room_blocking', $meta, $this->data);
        }
    }


    // Setting Floors

    public function floors()
	{
		$this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('rentals'), 'page' => lang('rentals')), array('link' => '#', 'page' => lang('floors')));
        $meta = array('page_title' => lang('floors'), 'bc' => $bc);
        $this->core_page('rentals/floors', $meta, $this->data);
	}

	public function getFloors()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
					rental_floors.id as id,
					rental_floors.floor")
            ->from("rental_floors")
			->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_floor") . "' href='" . site_url('rentals/edit_floor/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_floor") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('rentals/delete_floor/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

	public function add_floor()
    {
        $this->cus->checkPermissions('floors');
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'is_unique[rental_floors.floor]');
        if ($this->form_validation->run() == true) {
            $data = array('floor' => $this->input->post('name'));
        }else if($this->input->post('add_floor')){
			$this->session->set_flashdata('error', validation_errors());
			redirect("rentals/floors");
		}
        if ($this->form_validation->run() == true && $sid = $this->rentals_model->addFloor($data)) {
            $this->session->set_flashdata('message', lang("floors_added"));
            redirect("rentals/floors");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'rentals/add_floor', $this->data);
        }
    }

	public function edit_floor($id = false)
    {
		$this->cus->checkPermissions('floors');
		$rental_floor = $this->rentals_model->getFloorByID($id);
		$this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
		if ($this->input->post('name') != $rental_floor->floor) {
            $this->form_validation->set_rules('name', lang("rental_floor"), 'is_unique[rental_floors.floor]');
        }
        if ($this->form_validation->run() == true) {
            $data = array('floor' => $this->input->post('name'));
        }
		else if($this->input->post('edit_floor')){
			$this->session->set_flashdata('error', validation_errors());
			redirect("rentals/floors");
		}
        if ($this->form_validation->run() == true && $sid = $this->rentals_model->updateFloor($id, $data)) {
            $this->session->set_flashdata('message', lang("floor_updated"));
            redirect("rentals/floors");
        } else {
			$this->data['id'] = $id;
			$this->data['row'] = $this->rentals_model->getFloorByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'rentals/edit_floor', $this->data);
        }
    }

	public function delete_floor($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->rentals_model->deleteFloor($id)) {
            echo $this->lang->line("rental_floor_deleted"); exit;
        }
		$this->session->set_flashdata('message', lang("rental_floor_deleted"));
            redirect("rentals/floors");
    }

	public function floor_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->rentals_model->deleteFloor($id);
                    }
                    $this->session->set_flashdata('message', lang("floors_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('floors'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->rentals_model->getFloorByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->floor);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'floors_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    // Setting Rooms

    public function rooms($warehouse_id = null, $biller_id = NULL)
	{
		$this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('rentals'), 'page' => lang('rentals')), array('link' => '#', 'page' => lang('rooms')));
        $meta = array('page_title' => lang('rooms'), 'bc' => $bc);
        $this->core_page('rentals/rooms', $meta, $this->data);
	}

	public function getRooms($warehouse_id = null, $biller_id = NULL)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
					rental_rooms.id as id,
					rental_rooms.name,
					rental_floors.floor,
					rental_rooms.price,
					rental_rooms.status")
            ->from("rental_rooms")
			->join("rental_floors","rental_floors.id = rental_rooms.floor","left")
            ->group_by('rental_rooms.id')
            ->order_by('rental_rooms.id ASC')
			->add_column("Actions", "<center> <a class=\"tip\" title='" . $this->lang->line("edit_room") . "' href='" . site_url('rentals/edit_room/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_room") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('rentals/delete_room/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></center>", "id");

        if($biller_id){
            $this->datatables->where("rental_rooms.biller_id", $biller_id);
        }
        if ($warehouse_id) {
            $this->datatables->where('rental_rooms.warehouse_id', $warehouse_id);
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->datatables->where('rental_rooms.biller_id', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('rental_rooms.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
          
        echo $this->datatables->generate();
    }

	public function add_room()
    {
        $this->cus->checkPermissions('rooms');
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'is_unique[rental_rooms.name]');
		$this->form_validation->set_rules('floor', $this->lang->line("floor"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
				'floor' => $this->input->post('floor'),
                'name' => $this->input->post('name'),
                'room_type' => $this->input->post('room_type'),
                'price' => $this->input->post('price'),
				'product_id' => $this->input->post('product'),
                'biller_id' => $this->input->post('biller'),
				'warehouse_id' => $this->input->post('warehouse'),
                'bed_number_id' => $this->input->post('bed_number'),
				'status' => 'active'
            );
        }else if($this->input->post('add_room')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->rentals_model->addRoom($data)) {
            $this->session->set_flashdata('message', lang("room_added"));
            redirect("rentals/rooms");
        } else {
			$this->data['products'] = $this->rentals_model->getRoomItems();
			$this->data['floors'] = $this->rentals_model->getAllFloors();
            $this->data['billers'] =  $this->site->getBillers();
            $this->data['bed_numbers'] = $this->rentals_model->getAllBedNumbers();
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'rentals/add_room', $this->data);
        }
    }

	public function edit_room($id = NULL)
    {
        $this->cus->checkPermissions('rooms');
		$room = $this->rentals_model->getRoomByID($id);
		$this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
		$this->form_validation->set_rules('floor', $this->lang->line("floor"), 'required');
		if ($this->input->post('name') != $room->name) {
            $this->form_validation->set_rules('name', lang("name"), 'is_unique[rental_rooms.name]');
        }
        if ($this->form_validation->run() == true) {
            $data = array(
				'floor' => $this->input->post('floor'),
                'name' => $this->input->post('name'),
                'room_type' => $this->input->post('room_type'),
                'price' => $this->input->post('price'),
				'product_id' => $this->input->post('product'),
                'biller_id' => $this->input->post('biller'),
				'warehouse_id' => $this->input->post('warehouse'),
                'bed_number_id' => $this->input->post('bed_number'),
				'status' => $this->input->post('status')
            );
        }else if($this->input->post('edit_room')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $this->rentals_model->updateRoom($id, $data)) {
            $this->session->set_flashdata('message', lang("room_updated"));
            redirect("rentals/rooms");
        } else {
			$this->data['id'] = $id;
			$this->data['products'] = $this->rentals_model->getRoomItems();
			$this->data['floors'] = $this->rentals_model->getAllFloors();
            $this->data['billers'] =  $this->site->getBillers();
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['bed_numbers'] = $this->rentals_model->getAllBedNumbers();
			$this->data['row'] = $this->rentals_model->getRoomByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'rentals/edit_room', $this->data);
        }
    }

	public function delete_room($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->rentals_model->deleteRoom($id)) {
            echo $this->lang->line("room_deleted"); exit;
        }
		$this->session->set_flashdata('message', lang("room_deleted"));
        redirect("rentals/rooms");
    }

	public function room_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->rentals_model->deleteRoom($id);
                    }
                    $this->session->set_flashdata('message', lang("rooms_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('rooms'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('floor'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('price'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $room = $this->rentals_model->getRoomByID($id);
						$floor = $this->rentals_model->getFloorByID($room->floor);
                        $warehouse = $this->site->getWarehouseByID($room->warehouse_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $room->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $floor->floor);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->cus->formatMoney($room->price));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, lang($room->status));
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'rooms_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    
    // Setting Services

    public function services($warehouse_id = null, $biller_id = NULL)
	{
		$this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('rentals'), 'page' => lang('rentals')), array('link' => '#', 'page' => lang('services')));
        $meta = array('page_title' => lang('services'), 'bc' => $bc);
        $this->core_page('rentals/services', $meta, $this->data);
	}
    
    public function getServices($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions('services');
        $this->load->library('datatables');
        $this->datatables
            ->select("
                products.id as id,
                products.code,
                products.name,
                products.price,
                IF(inactive=1,'inactive','active') as status")
            ->from("products")
            ->where('electricity >',0)
            ->where('type','service_rental')
			->add_column("Actions", "<center> <a class=\"tip\" title='" . $this->lang->line("edit_service") . "' href='" . site_url('rentals/edit_service/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fonts fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_service") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('rentals/delete_service/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

    public function service_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->rentals_model->deleteService($id);
                    }
                    $this->session->set_flashdata('message', lang("services_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('service'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $service = $this->rentals_model->getServiceByID($id);
                        $status = lang('active');
                        if($service->inactive==1){
                            $status = lang('inacive');
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $service->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $service->name);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $service->price);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $status);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'services_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function add_service()
    {
        $this->cus->checkPermissions('services',true);
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[cus_products.code]');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('price', lang("price"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $data = array(
                'code' => $this->input->post('code'),
                'barcode_symbology' => 'code128',
                'name' => $this->input->post('name'),
                'type' => 'service_rental',
                'price' => $this->input->post('price'),
                'electricity' => $this->input->post('type'),
                'track_quantity' => 0,
            );
            if($this->Settings->accounting == 1){
				$discount_acc = $this->input->post('discount_account');
				$sale_acc = $this->input->post('sale_account');
				$account = array(
					'type' => $this->input->post('type'),
					'discount_acc' => $discount_acc,
					'sale_acc' => $sale_acc,
					'expense_acc' => $this->input->post('expense_account'),
				);	
            }
            
        } elseif ($this->input->post('add_service')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->rentals_model->addService($data, $account)) {
			$this->session->set_flashdata('message', lang("service_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if($this->Settings->accounting == 1){
                $this->data['discount_accounts'] = $this->site->getAccount(array('RE','EX','GL'));
                $this->data['sale_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'));
                $this->data['expense_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'));
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'rentals/add_service', $this->data);
        }
    }

    public function edit_service($id)
    {
        $this->cus->checkPermissions('services',true);
        $this->load->helper('security');
        $valid = '';
        $service = $this->rentals_model->getServiceByID($id);
        if($service->code != $this->input->post('code')){
            $valid .= '|is_unique[cus_products.code]';
        }
        $this->form_validation->set_rules('code', lang("code"), 'required'.$valid);
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('price', lang("price"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $data = array(
                'code' => $this->input->post('code'),
                'barcode_symbology' => 'code128',
                'name' => $this->input->post('name'),
                'type' => 'service_rental',
                'price' => $this->input->post('price'),
                'track_quantity' => 0,
                'electricity' => $this->input->post('type'),
                'inactive' => $this->input->post('inactive'),
            );
            if($this->Settings->accounting == 1){
				$discount_acc = $this->input->post('discount_account');
				$sale_acc = $this->input->post('sale_account');
				$account = array(
					'type' => $this->input->post('type'),
					'discount_acc' => $discount_acc,
					'sale_acc' => $sale_acc,
					'expense_acc' => $this->input->post('expense_account'),
				);	
            }
        } elseif ($this->input->post('edit_service')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->rentals_model->updateService($id, $data, $account)) {
			$this->session->set_flashdata('message', lang("service_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['id'] = $id;
            if($this->Settings->accounting == 1){
                $productAccount = $this->rentals_model->getServiceAccByServiceId($id);
                $this->data['discount_accounts'] = $this->site->getAccount(array('RE','EX','GL'),$productAccount->discount_acc);
                $this->data['sale_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$productAccount->sale_acc);
                $this->data['expense_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'),$productAccount->expense_acc);
            }
            $this->data['service'] = $this->rentals_model->getServiceByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'rentals/edit_service', $this->data);
        }
    }

    public function delete_service($id = NULL)
    {
        $this->cus->checkPermissions('services');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->rentals_model->deleteService($id)) {
            echo $this->lang->line("service_deleted"); exit;
        }
		$this->session->set_flashdata('message', lang("service_deleted"));
        redirect("rentals/rooms");
    }

    
    public function rental_booking_tracker()
    {
        $this->cus->checkPermissions('payments');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['rooms'] = $this->rentals_model->getAllRooms();
        $this->data['floors'] = $this->rentals_model->getRoomFloors();
        $this->data['users'] = $this->site->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('rental_deposits_report')));
        $meta = array('page_title' => lang('rental_deposits_report'), 'bc' => $bc);
        $this->core_page('rentals/rental_booking_tracker', $meta, $this->data);
    }

    function getRentalDeposits($pdf = NULL, $xls = NULL)
    {
        $this->cus->checkPermissions('payments', TRUE);
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $project = $this->input->get('project') ? $this->input->get('project') : NULL;
        $payment_ref = $this->input->get('payment_ref') ? $this->input->get('payment_ref') : NULL;
        $room = $this->input->get('room') ? $this->input->get('room') : NULL;
        $floor = $this->input->get('floor') ? $this->input->get('floor') : NULL;
        $rental_ref = $this->input->get('rental_ref') ? $this->input->get('rental_ref') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $paid_by = $this->input->get('paid_by') ? $this->input->get('paid_by') : NULL;
        
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $biller = $this->session->userdata('biller_id');
        }
        
        if ($start_date) {
            $start_date = $this->cus->fld($start_date);
            $end_date = $this->cus->fld($end_date,false,1);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($xls) {

            $this->db
                    ->select("DATE_FORMAT({$this->db->dbprefix('payments')}.date, '%Y-%m-%d %T') as date, 
                    ".$this->db->dbprefix('payments').".reference_no as payment_ref,
                    ".$this->db->dbprefix('rentals').".reference_no as rental_ref,
                    ".$this->db->dbprefix('rental_rooms').".name as room_name, 
                    ".$this->db->dbprefix('rentals').".customer as cust_name,
                    ".$this->db->dbprefix('companies').".phone,
                    ".$this->db->dbprefix('rentals').".checked_in, 
                    CONCAT({$this->db->dbprefix('users')}.last_name,' ',{$this->db->dbprefix('users')}.first_name) as created_by, 
                    paid_by, 
                    IF(cus_payments.type='sent',-(cus_payments.amount), (cus_payments.amount)) as amount, 
                    payments.type, 
                    {$this->db->dbprefix('payments')}.id as id,
                    {$this->db->dbprefix('payments')}.sale_id as sale_id,
                    {$this->db->dbprefix('payments')}.transaction")
                    ->from('payments')
                    ->join('rentals','cus_rentals.id=transaction_id AND transaction="RentalDeposit" OR cus_rentals.id=transaction_id AND transaction="ReturnRentalDeposit"','inner')
                    ->join('companies','companies.id=rentals.customer_id','left')
                    ->join('rental_rooms','rental_rooms.id=room_id','left')
                    ->join('users', 'users.id=payments.created_by', 'left')
                ->group_by('payments.id');

            if ($user) {
                $this->db->where('payments.created_by', $user);
            }
            if ($customer) {
                $this->db->where('rentals.customer_id', $customer);
            }
            if ($biller) {
                $this->db->where('rentals.biller_id', $biller);
            }
            if ($room) {
                $this->db->where('rentals.room_id', $room);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($rental_ref) {
                $this->db->like('rentals.reference_no', $rental_ref);
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
                $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
                $warehouse_ids = str_replace(']',')',$warehouse_ids);
                $this->db->where('(rentals.warehouse_id IN '.$warehouse_ids.')');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('rental_deposits_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('payment_reference'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('rental_reference'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('room'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('checked_in_date'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('created_by'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('type'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->rental_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->room_name);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->cust_name);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->hrsd($data_row->checked_in));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->created_by);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($data_row->type));
                    $total += $data_row->amount;
                    $row++;
                }
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $total);
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
               
                $filename = 'rental_deposits_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT({$this->db->dbprefix('payments')}.date, '%Y-%m-%d %T') as date, 
                ".$this->db->dbprefix('payments').".reference_no as payment_ref,
                ".$this->db->dbprefix('rentals').".reference_no as rental_ref,
                ".$this->db->dbprefix('rental_rooms').".name as room_name, 
                ".$this->db->dbprefix('rentals').".customer as cust_name,
                ".$this->db->dbprefix('companies').".phone,
                ".$this->db->dbprefix('rentals').".checked_in, 
                CONCAT({$this->db->dbprefix('users')}.last_name,' ',{$this->db->dbprefix('users')}.first_name) as created_by, 
                IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by,
                IF(cus_payments.type='sent',-(cus_payments.amount), (cus_payments.amount)) as amount,
                payments.type, 
                {$this->db->dbprefix('payments')}.id as id,
                {$this->db->dbprefix('payments')}.sale_id as sale_id,
                {$this->db->dbprefix('payments')}.transaction")
                ->from('payments')
                ->join('rentals','cus_rentals.id=transaction_id AND transaction="RentalDeposit" OR cus_rentals.id=transaction_id AND transaction="ReturnRentalDeposit"','inner')
                ->join("cash_accounts","cash_accounts.id = payments.paid_by","left")
                ->join('companies','companies.id=rentals.customer_id','left')
                ->join('rental_rooms','rental_rooms.id=rentals.room_id','left')
                ->join('users', 'users.id=payments.created_by', 'left')
                ->group_by('payments.id');

            if ($user) {
                $this->datatables->where('payments.created_by', $user);
            }
            if ($customer) {
                $this->datatables->where('rentals.customer_id', $customer);
            }
            if ($biller) {
                $this->datatables->where('rentals.biller_id', $biller);
            }
            if ($room) {
                $this->datatables->where('rentals.room_id', $room);
            }
            if ($payment_ref) {
                $this->datatables->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($rental_ref) {
                $this->datatables->like('rentals.reference_no', $rental_ref);
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
                $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
                $warehouse_ids = str_replace(']',')',$warehouse_ids);
                $this->datatables->where('(rentals.warehouse_id IN '.$warehouse_ids.')');
            }
            echo $this->datatables->generate();
        }
    }


}
