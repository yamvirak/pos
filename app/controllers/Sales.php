<?php defined('BASEPATH') or exit('No direct script access allowed');

class Sales extends MY_Controller
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
        $this->lang->load('sales', $this->Settings->user_language);
        $this->load->library('form_validation');
		if($this->config->item("send_telegram")){
			$this->load->library('telegrambot');
		}
		$this->load->model('pos_model');
        $this->load->model('sales_model');
		$this->load->model('sale_order_model');
		$this->load->model('deliveries_model');
		$this->load->model('companies_model');
		$this->pos_settings = $this->pos_model->getSetting();
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '10240';
        $this->data['logo'] = true;
    }

    public function index($warehouse_id = null,$biller_id = NULL,$payment_status = null)
    {
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		if($biller_id == 0){
			$biller_id = null;
		}
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;	
		$this->data['payment_status'] = $payment_status;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('sales')));
		$meta = array('page_title' => lang('sales'), 'bc' => $bc);
        $this->core_page('sales/index', $meta, $this->data);
    }

    public function getSales($warehouse_id = null ,$biller_id = NULL, $payment_status = null)
    {
        $this->cus->checkPermissions('index');
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		$simulate_link = '';
		if($this->Settings->installment && (isset($this->GP['installments-add']) || ($this->Owner || $this->Admin))){
			$simulate_link = anchor('installments/add/$1', '<i class="fa fa-plus-circle"></i> ' . lang('add_installment'),'class="add_installment"');
		}
		
		$down_payment_link = '';
		$view_down_payment_link = '';
		if($this->config->item('down_payment')){
			$down_payment_link = anchor('sales/down_payment/$1', '<i class="fa fa-plus-circle"></i> ' . lang('down_payment'), 'data-toggle="modal" data-backdrop="static" class="down_payment" data-keyboard="false" data-target="#myModal"');
			$view_down_payment_link = anchor('sales/view_down_payments/$1', '<i class="fa fa-tasks"></i> ' . lang('view_down_payments'), 'data-toggle="modal" data-backdrop="static" class="view_down_payment" data-keyboard="false" data-target="#myModal"');
		}
		$pdf_link          = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
		$duplicate_link = anchor('sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_payment" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_payment" data-target="#myModal"');
        if($this->config->item('deliveries')== true){
			$add_delivery_link = anchor('deliveries/add/0/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'class="add_delivery"');
        }else{
			$add_delivery_link = '';
		}

		
		$email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
		$return_link = '';
		if ((isset($this->GP['sales-return_sales']) && $this->GP['sales-return_sales']) || $this->Owner || $this->Admin) {
			$return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'), 'class="add_return"');
		}
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . "</a>";
	
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $simulate_link . '</li>
				<li>' . $duplicate_link . '</li>
				<li>' . $payments_link . '</li>
				<li>' . $add_payment_link . '</li>
				<li>' . $add_delivery_link . '</li>
				<li>' . $down_payment_link. '</li>
				<li>' . $view_down_payment_link. '</li>
				<li>' . $pdf_link . '</li>
				<li>' . $edit_link . '</li>
				<li>' . $email_link . '</li>
				<li>' . $return_link . '</li>
				<li>' . $delete_link . '</li>
			</ul>
		</div></div>';
		
        $this->load->library('datatables');
		$this->datatables
			->select("sales.id as id,
			DATE_FORMAT(date, '%Y-%m-%d %T') as date,
			reference_no,
			CONCAT(cus_companies.company,', [ <small style=color:#FF5454;font-weight:bold;>',cus_companies.phone,'</small> ] '),
			description,
			vehicle_model,
			vehicle_plate,
			vehicle_vin_no,
			mechanic,
			grand_total,
			IFNULL(total_return,0) as total_return,
			IFNULL(cus_payments.paid + IFNULL(total_return_paid,0),0) as paid,
			IFNULL(cus_payments.discount,0) as discount,
			ROUND((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") as balance,
			delivery_status,
			IF (
				(
					round((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") = 0
				),
				'paid',
				IF (
				(
					(grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))) = grand_total
				),
				'pending',
				'partial'
			)) AS payment_status,
			attachment,
			return_id, 
			installment,
			stock_deduction,
			sales.type
			")
			->from('sales')
			->join('companies','companies.id=sales.customer_id','left')
			->join('(SELECT
						sale_id,
						SUM(ABS(grand_total)) AS total_return,
						SUM(paid) AS total_return_paid
					FROM
						'.$this->db->dbprefix('sales').' WHERE sale_status = "returned"
					GROUP BY
						sale_id) as cus_return', 'cus_return.sale_id=sales.id', 'left')
			->join('(SELECT
						sale_id,
						IFNULL(SUM(amount),0) AS paid,
						IFNULL(SUM(discount),0) AS discount
					FROM
						'.$this->db->dbprefix('payments').'
						
					GROUP BY
						sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left')

			->group_by('sales.id')
			->order_by('sales.date DESC');
			

		$this->datatables->where('IFNULL('.$this->db->dbprefix("sales").'.type,"") !=', "concrete");
		if ($warehouse_id) {
            $this->datatables->where('sales.warehouse_id', $warehouse_id);
        }
		if ($biller_id) {
			$this->datatables->where('sales.biller_id', $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if ($warehouse_id) {
			$this->datatables->where('warehouse_id', $warehouse_id);
		}
		if ($payment_status=='due') {
			$this->datatables->join('payment_terms','payment_terms.id = sales.payment_term','inner');
			$this->datatables->where("sales.due_date <=", date('Y-m-d'));
			$this->datatables->where("IF(
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
			$this->datatables->where('sales.payment_term >', 0);
			$this->datatables->where('sales.grand_total !=', 0);
		}
        $this->datatables->where('pos !=', 1);
		$this->datatables->where('sale_status !=', 'draft');
		$this->datatables->where('sale_status !=', 'returned');

        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

	public function returns($warehouse_id = null,$biller_id = NULL,$payment_status = null)
    {
		$this->cus->checkPermissions('return_sales');
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error'); 
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$this->data['payment_status'] = $payment_status;
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')),  array('link' => '#', 'page' => lang('sale_returns')));
		$meta = array('page_title' => lang('sale_returns'), 'bc' => $bc);
        $this->core_page('sales/returns', $meta, $this->data);
    }

	public function getReturns($warehouse_id = null ,$biller_id = false, $payment_status = null)
    {
        $this->cus->checkPermissions('return_sales');
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
        $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_return_details'));
        $payments_link = anchor('sales/payment_returns/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment_return/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'class="add_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_link . '</li>
				<li>' . $payments_link . '</li>
				<li>' . $add_payment_link . '</li>
				<li>' . $email_link . '</li>
				<li>' . $delete_link . '</li>
			</ul>
		</div></div>';

        $this->load->library('datatables');
		$this->datatables
			->select("id,
			DATE_FORMAT(date, '%Y-%m-%d %T') as date,
			return_sale_ref,
			reference_no,
			customer,
			ABS(grand_total) as grand_total,
			IFNULL(invoice_interest - surcharge_interest,0) as credit_interest,
			IF((invoice_total - invoice_paid - ABS(grand_total)) < 0,ABS(invoice_total - invoice_paid - ABS(grand_total)),'0') as credit_amount,
			IFNULL(ABS(cus_payments.paid),0) as paid,
			IFNULL(ABS(cus_payments.discount),0) as discount,
			IF((invoice_total - (IFNULL(invoice_paid,0) + IFNULL(invoice_interest-surcharge_interest,0)) - ABS(grand_total)) < 0, ABS(invoice_total - (IFNULL(invoice_paid,0) + IFNULL(invoice_interest-surcharge_interest,0)) - ABS(grand_total)) - (IFNULL(ABS(cus_payments.paid),0) + IFNULL(ABS(cus_payments.discount),0)),'0') as balance,
			IF((invoice_total - (IFNULL(invoice_paid,0) + IFNULL(invoice_interest-surcharge_interest,0)) - abs(grand_total)) < 0,
			IF (
				(
					round((abs(invoice_total-(IFNULL(invoice_paid,0) + IFNULL(invoice_interest-surcharge_interest,0)) - abs(grand_total)) - (IFNULL(abs(cus_payments.paid),0) + IFNULL(abs(cus_payments.discount),0))),".$this->Settings->decimals.") = 0
				),
				'paid',
				IF (
				(
					((IFNULL(cus_payments.paid,0))+(IFNULL(cus_payments.discount,0))) = 0
				),
				'pending',
				'partial'
			)),'paid') AS payment_status,
			attachment,
			return_id")
			->from('sales')
			->join('(
						SELECT
							id AS invoice_id,
							grand_total AS invoice_total,
							paid AS invoice_paid
						FROM
							cus_sales
					) AS cus_inv','sales.sale_id = cus_inv.invoice_id','inner')
			->join('(SELECT
						sale_id,
						IFNULL(SUM(interest_paid),0) AS invoice_interest
					FROM
						'.$this->db->dbprefix('payments').'
					GROUP BY
						sale_id) as cus_payment_inv', 'cus_payment_inv.sale_id=sales.sale_id', 'left')
			->join('(SELECT
						sale_id,
						IFNULL(sum(amount),0) + IFNULL(sum(interest_paid),0) AS paid,
						IFNULL(sum(discount),0) AS discount
					FROM
						'.$this->db->dbprefix('payments').'
					GROUP BY
						sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left');

		if ($warehouse_id) {
            $this->datatables->where('sales.warehouse_id', $warehouse_id);
        }
		if ($biller_id) {
			$this->datatables->where('sales.biller_id', $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if ($payment_status=='due') {
			$this->datatables->where('sales.due_date <=', date('Y-m-d'));
			$this->datatables->where('sales.payment_status !=', 'paid');
			$this->datatables->where('sales.payment_term >', 0);
		}
		$this->datatables->where('sale_status =', 'returned');

        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	
	public function modal_view_agreement($id = null)
    {
        $this->cus->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['inv'] = $inv;
		$this->load->view($this->theme . 'sales/modal_view_agreement', $this->data);
    }

    public function modal_view($id = null)
    {
        $this->cus->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
		$customer = $this->site->getCompanyByID($inv->customer_id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id,false,"asc");
		$this->data['customer'] = $customer;
		$this->data['currency'] = $this->site->getCurrencyByCode("KHR");
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
		$this->data['saleman'] = $this->site->getUser($inv->saleman_id);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
		$this->data['payment'] = $this->sales_model->getPaymentBySaleID($inv->id);       
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Sale',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Sale',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		if($inv->type == "school"){
			$student = $this->sales_model->getStudentByID($customer->student_id);
			$this->data['study'] = $this->sales_model->getStudyInfoBySale($id);
			$this->data['siblings'] = $this->sales_model->getSiblings($student->family_id);
			$this->data['student'] = $student;
			$this->load->view($this->theme . 'sales/modal_view_school', $this->data);
		}else if($inv->rental_id > 0){
			$rental = $this->sales_model->getRentalByID($inv->rental_id);
			$this->data['rental'] = $rental;
			$this->data['refund_deposit'] = $this->sales_model->getRentalRefundPayment($inv->rental_id);
			$this->data['deposit'] = $this->sales_model->getRentalDepositPayment($inv->rental_id);
			$this->data['pay_deposit'] = $this->sales_model->getRentalPayDeposit($inv->id);
			$this->data['room'] = $this->sales_model->getRoomByID($rental->room_id);
			$this->load->view($this->theme . 'sales/modal_view_rental', $this->data);
		}else if($inv->repair_id > 0){
			$repair = $this->sales_model->getRepairByID($inv->repair_id);
			$this->data['brand'] = $this->sales_model->getBrandByID($repair->brand_id);
			$this->data['model'] = $this->sales_model->getModelByID($repair->model_id);
			$this->data['machine_type'] = $this->sales_model->getMachineTypeByID($repair->machine_type_id);
			$this->data['repair'] = $repair;
			$this->load->view($this->theme . 'sales/modal_view_repair', $this->data);
		}else{
			$this->load->view($this->theme . 'sales/modal_view', $this->data);
		}
		
    }

    public function modal_view_delivery_note($id = null)
    {
        $this->cus->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
		$this->data['currency'] = $this->site->getCurrencyByCode("KHR");
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
		$this->data['saleman'] = $this->site->getUser($inv->saleman_id);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
		$this->data['payment'] = $this->sales_model->getPaymentBySaleID($inv->id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Sale',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Sale',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		$this->load->view($this->theme . 'sales/modal_view_delivery_note', $this->data);
    }

    public function modal_view_delivery_note_tax($id = null)
    {
        $this->cus->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
		$this->data['currency'] = $this->site->getCurrencyByCode("KHR");
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
		$this->data['saleman'] = $this->site->getUser($inv->saleman_id);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
		$this->data['payment'] = $this->sales_model->getPaymentBySaleID($inv->id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Sale',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Sale',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		$this->load->view($this->theme . 'sales/modal_view_delivery_note_tax', $this->data);
    }
	

	
	public function modal_view_com($id = null)
    {
        $this->cus->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
		$this->data['currency'] = $this->site->getCurrencyByCode("KHR");
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
		$this->data['saleman'] = $this->site->getUser($inv->saleman_id);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
		$this->data['payment'] = $this->sales_model->getPaymentBySaleID($inv->id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Sale',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Sale',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		$this->load->view($this->theme . 'sales/modal_view_com', $this->data);
    }
	
	public function modal_view_tax($id = null)
    {
        $this->cus->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
		$this->data['currency'] = $this->site->getCurrencyByCode("KHR");
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
		$this->data['saleman'] = $this->site->getUser($inv->saleman_id);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
		$this->data['payment'] = $this->sales_model->getPaymentBySaleID($inv->id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Sale',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Sale',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		$this->load->view($this->theme . 'sales/modal_view_tax', $this->data);
    }
	
	public function view_return($id = null)
    {
        $this->cus->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by, true);
        }
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
		$this->data['saleman'] = $this->site->getUser($inv->saleman_id);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
		$this->data['payment'] = $this->sales_model->getPaymentBySaleID($inv->id);
		$this->data['sale_payments'] = $this->sales_model->getPaymentsBySale($inv->sale_id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Sale Return',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Sale Return',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
        $this->load->view($this->theme . 'sales/modal_view_return', $this->data);
    }

    public function view($id = null)
    {
        $this->cus->checkPermissions('index');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;
        $this->data['paypal'] = $this->sales_model->getPaypalSettings();
        $this->data['skrill'] = $this->sales_model->getSkrillSettings();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_sales_details'), 'bc' => $bc);
        $this->core_page('sales/view', $meta, $this->data);
    }


	function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image, $filename);
        }
        if ($permissions != null) {
            chmod($filename, $permissions);
        }
    }

    function image($originalImage, $outputImage, $quality)
	{
	    // jpg, png, gif or bmp?
	    $exploded = explode('.',$originalImage);
	    $ext = $exploded[count($exploded) - 1]; 

	    if (preg_match('/jpg|jpeg/i',$ext))
	        $imageTmp=imagecreatefromjpeg($originalImage);
	    else if (preg_match('/png/i',$ext))
	        $imageTmp=imagecreatefrompng($originalImage);
	    else if (preg_match('/gif/i',$ext))
	        $imageTmp=imagecreatefromgif($originalImage);
	    else if (preg_match('/bmp/i',$ext))
	        $imageTmp=imagecreatefrombmp($originalImage);
	    else
	        return 0;

	    // quality is a value from 0 (worst) to 100 (best)
	    imagejpeg($imageTmp, $outputImage, $quality);
	    imagedestroy($imageTmp);

	    return 1;
	}


    public function pdf($id = null, $view = null, $save_bufffer = null)
    {
        $this->cus->checkPermissions();
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($inv->created_by);
        }
       
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user']        = $this->site->getUser($inv->created_by);
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        //$this->data['paypal'] = $this->sales_model->getPaypalSettings();
        //$this->data['skrill'] = $this->sales_model->getSkrillSettings();

        $name = lang('sale') . '_' . str_replace('/', '_', $inv->reference_no) . '.pdf';
        $html = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        if ($view) {
            $this->load->view($this->theme . 'sales/pdf', $this->data);
        } else {
            $this->cus->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
        }
    }


    public function combine_pdf($sales_id)
    {
        $this->cus->checkPermissions('pdf');

        foreach ($sales_id as $id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->sales_model->getInvoiceByID($id);
            if (!$this->session->userdata('view_right')) {
                $this->cus->view_rights($inv->created_by);
            }
            $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;
            $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
            $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;
            $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;
            $html_data = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
            if (! $this->Settings->barcode_img) {
                $html_data = preg_replace("'\<\?xml(.*)\?\>'", '', $html_data);
            }

            $html[] = array(
                'content' => $html_data,
                'footer' => $this->data['biller']->invoice_footer,
            );
        }

        $name = lang("sales") . ".pdf";
        $file = $this->cus->generate_pdf($html, $name, "S");
		if($file){
			redirect(base_url($file));
		}

    }

    public function email($id = null)
    {
        $this->cus->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        $this->form_validation->set_rules('to', lang("to") . " " . lang("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', lang("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->cus->view_rights($inv->created_by);
            }
            $to = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $customer->name,
                'company' => $customer->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->name != '-' ? $biller->name : $biller->company) . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $paypal = $this->sales_model->getPaypalSettings();
            $skrill = $this->sales_model->getSkrillSettings();
            $btn_code = '<div id="payment_buttons" class="text-center margin010">';
            if ($paypal->active == "1" && $inv->grand_total != "0.00") {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
                } else {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
                }
                $btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->default_currency->code . '&bn=FC-BuyNow&rm=2&return=' . site_url('sales/view/' . $inv->id) . '&cancel_return=' . site_url('sales/view/' . $inv->id) . '&notify_url=' . site_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('assets/images/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';

            }
            if ($skrill->active == "1" && $inv->grand_total != "0.00") {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
                } else {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
                }
                $btn_code .= ' <a href="https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . site_url('sales/view/' . $inv->id) . '&cancel_url=' . site_url('sales/view/' . $inv->id) . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->cus->formatMoney($inv->grand_total + $skrill_fee) . '&currency=' . $this->default_currency->code . '&status_url=' . site_url('payments/skrillipn') . '"><img src="' . base_url('assets/images/btn-skrill.png') . '" alt="Pay by Skrill"></a>';
            }

            $btn_code .= '<div class="clearfix"></div>
    </div>';
            $message = $message . $btn_code;

            $attachment = $this->pdf($id, null, 'S');
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->cus->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->session->set_flashdata('message', lang("email_sent"));
            redirect("sales");
        } else {

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/sale.html')) {
                $sale_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/sale.html');
            } else {
                $sale_temp = file_get_contents('./themes/default/views/email_templates/sale.html');
            }

            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('invoice') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $sale_temp),
            );
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);

            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/email', $this->data);
        }
    }

    public function add($quote_id = null, $sale_order_id = null)
    {
        $this->cus->checkPermissions();
        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : NULL;
		$action = $this->input->get('action') ? $this->input->get('action') : NULL;
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$tax_detail = $this->site->getTaxRateByID($this->input->post('order_tax'));
			$stock_deduction = ($this->input->post('stock_deduction') == 0 ? 0 : 1);
			if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			
			$fuel_customers = $this->input->post('fuel_customers');
			$sale_order_id = $this->input->post('sale_order_id');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $total_items = $this->input->post('total_items');
            $sale_status = $this->input->post('sale_status');
            $payment_status = $this->input->post('payment_status');
            $payment_term = $this->input->post('payment_term');
			$vehicle_model = $this->input->post('vehicle_model');
			$vehicle_kilometers = $this->input->post('vehicle_kilometers');
			$vehicle_vin_no = $this->input->post('vehicle_vin_no');
			$vehicle_plate = $this->input->post('vehicle_plate');
			$job_number = $this->input->post('job_number');
			$mechanic = $this->input->post('mechanic');
			$project_id = $this->input->post('project');
			$delivery_id = $this->input->post('delivery_id');
			$consignment_id = $this->input->post('consignment_id');
			$payment_term_info = $this->sales_model->getPaymentTermsByID($payment_term);
            if($payment_term_info){
				if($payment_term_info->term_type=='end_month'){
					$due_date = date("Y-m-t", strtotime($date));
				}else{
					$due_date =  date('Y-m-d', strtotime('+' . $payment_term_info->due_day . ' days', strtotime($date)));
				}
			}else{
				$due_date = null;
			}
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
			
			
			if($customer_details->credit_day > 0 || $customer_details->credit_amount > 0){
				if($customer_details->credit_day > 0){
					$credit_balance = $this->sales_model->getCreditLimit($customer_id,$customer_details->credit_day);
					if($credit_balance->balance > 0){
						$this->session->set_flashdata('error', lang('customer_have_over_credit_day'));
						$this->cus->md();
					}
				}
				if($customer_details->credit_amount > 0){
					$credit_balance = $this->sales_model->getCreditLimit($customer_id);
					if($credit_balance->balance >= $customer_details->credit_amount){
						$this->session->set_flashdata('error', lang('customer_have_over_credit_amount'));
						$this->cus->md();
					}
				}	
			}
			
			if($tax_detail && $tax_detail->rate > 0){
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tax_so',$biller_id);
			}else{
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so',$biller_id);
			}
			if($this->sales_model->getSaleByReference($reference,'sale', false, $date)){
				$this->session->set_flashdata('error',lang('reference').' "'.$reference.'" '.lang('is_already_existed'));
				$this->cus->md();
			}
            $customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->cus->clear_tags($this->input->post('note'));
            $staff_note = $this->cus->clear_tags($this->input->post('staff_note'));
            $quote_id = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;
			$agency_id = $this->input->post('agency_id') ? $this->input->post('agency_id') : null;
			$saleman = $this->site->getUser($this->input->post('saleman_id'));
			$saleman_commission = trim($this->input->post('commission'));
			$groups_delivery = $this->input->post('groups_delivery');
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $digital = FALSE;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
            	$item_id = $_POST['product_id'][$r];
            	$item_unit_quantity = $_POST['quantity'][$r];               
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
				$item_comment = $_POST['product_comment'][$r];
				$cost = $_POST['cost'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$parent_id = $_POST['parent_id'][$r];
				$currency_code = isset($_POST['currency_code'][$r]) ? $_POST['currency_code'][$r] : null;
				$currency_rate = isset($_POST['currency_rate'][$r]) ? $_POST['currency_rate'][$r] : null;
				$electricity = $_POST['electricity'][$r];
				$old_number = $_POST['old_number'][$r];
				$new_number = $_POST['new_number'][$r];
				$consignment_item_id = $_POST['consignment_item_id'][$r];
				$item_note = $_POST['item_note'][$r];

				if($_POST['expired_data'][$r] && $_POST['expired_data'][$r] != '00/00/0000'){
					$expired_data = $this->cus->fsd($_POST['expired_data'][$r]);
				}else{
					$expired_data = null;
				}
				
				
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
					if ($item_type == 'manual') {
						$add_product = $_POST['add_product'][$r];
						if($add_product==1){
							if($this->site->getProductByCode($item_code)){
								$item_code = rand(10000000,99999999);
							};
							$addProduct = array(
									'code' => $item_code,
									'barcode_symbology' => 'code128',
									'name' => $item_name,
									'type' => 'standard',
									'category_id' => $this->Settings->manual_category,
									'cost' => $cost,
									'price' => $unit_price,
									'unit' => $this->Settings->manual_unit,
									'sale_unit' => $this->Settings->manual_unit,
									'purchase_unit' => $this->Settings->manual_unit,
									'alert_quantity' => 0,
									'manual_product' => 1
								);
							$item_id = $this->sales_model->addProduct($addProduct);	
							$item_type = 'standard';
							$item_unit = $this->Settings->manual_unit;
						}
                        $item_quantity = $item_unit_quantity;
                    }
					$product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    $pr_discount = 0;
					if($product_details){
						$cost = $product_details->cost;
					}
                    if ($item_type == 'digital') {
                        $digital = TRUE;
                    }

					
					if($this->Settings->product_serial == 1 && $item_serial==''){
						$qty_warehouse = $this->sales_model->getProductQuantity($item_id,$warehouse_id);
						$qty_serial = $this->sales_model->getProductSerialQuantity($item_id,$warehouse_id);
						$avalible_qty = $qty_warehouse['quantity'] - $qty_serial['serial_qty'];
						if($avalible_qty < $item_quantity && $qty_serial['serial_qty'] > 0){
							$this->form_validation->set_rules('product', lang("serial"), 'required');
						}
					}
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
					// Product Currency
					if($this->config->item('product_currency')==true){
						if($currency_code && $currency_rate){
							$real_unit_price = $real_unit_price / $currency_rate;
							$unit_price = $unit_price / $currency_rate;
							$pr_discount = $pr_discount / $currency_rate;
							if ($dpos !== false) {
								 $item_discount = $item_discount;
							}else{
								 $item_discount = $pr_discount;
							}
						}
					}

					if($product_details = $item_type == 'service_rental'){
						$description .= $item_name.",";
					}

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
					$unit = $this->site->getProductUnit($item_id,$item_unit);
					$raw_materials = array();
					if($this->Settings->customer_price == 1){
						$cust_prices[] = array(
										'product_id' => $item_id,
										'customer_id' => $customer_id,
										'price' => ($unit->unit_qty > 0 ? (($unit_price+$pr_discount) / $unit->unit_qty) : ($unit_price+$pr_discount))
									);
					}else{
						$cust_prices = false;
					}
					
					$combo_products = json_decode($_POST['product_combo'][$r]);
					if($product_details->type=='combo' && $combo_products){
						$price_combo = 0;
						$qty_combo = count($combo_products);
						$dicount = 0;
						foreach($combo_products as $combo_product){
							$price_combo += $combo_product->price * $combo_product->qty;
						}
						if($this->cus->formatDecimal($price_combo) <> $this->cus->formatDecimal($item_net_price)){
							$dicount = (($price_combo - $item_net_price) * 100) / $price_combo;
						}
						$product_combo_cost = 0;
						foreach($combo_products as $combo_product){
							$combo_id = $combo_product->id;
							$combo_code = $combo_product->code;
							$combo_name = $combo_product->name;
							$combo_qty = $combo_product->qty;
							$combo_price = $combo_product->price;
							
							if($dicount > 0){
								$combo_price = $combo_price - (($combo_price * $dicount) / 100);
							}else if($dicount < 0){
								$combo_price = $combo_price + (($combo_price * abs($dicount)) / 100);
							}
							
							if($price_combo==0 && $item_net_price > 0){
								$combo_price = $item_net_price / $qty_combo;
							}
							
							$combo_detail = $this->site->getProductByID($combo_id);
							if($combo_detail){
								$combo_unit = $this->site->getProductUnit($combo_id, $combo_detail->unit);
								if($this->Settings->accounting_method == '0'){
									$costs = $this->site->getFifoCost($combo_id,($item_quantity * $combo_qty),$stockmoves);
								}else if($this->Settings->accounting_method == '1'){
									$costs = $this->site->getLifoCost($combo_id,($item_quantity * $combo_qty),$stockmoves);
								}else if($this->Settings->accounting_method == '3'){
									$costs = $this->site->getProductMethod($combo_id($item_quantity * $combo_qty),$stockmoves);
								}

								if($costs){
									$productAcc = $this->site->getProductAccByProductId($combo_id);
									$item_cost_qty  = 0;
									$item_cost_total = 0;
									$item_costs = '';
									foreach($costs as $cost_item){
										$item_cost_qty += $cost_item['quantity'];
										$item_cost_total += $cost_item['cost'] * $cost_item['quantity'];

										$stockmoves[] = array(
											'transaction' => 'Sale',
											'product_id' => $combo_detail->id,
											'product_type'    => $combo_detail->type,
											'product_code' => $combo_detail->code,
											'quantity' => $cost_item['quantity'] * (-1),
											'expiry' => $expired_data,
											'unit_quantity' => $combo_unit->unit_qty,
											'unit_code' => $combo_unit->code,
											'unit_id' => $combo_detail->unit,
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $cost_item['cost'],
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);
										//========accounting=========//
											if($this->Settings->accounting == 1 &&  $sale_status=='completed'){
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,

												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
											}
										//============end accounting=======//
										$item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
									}
									
									$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->sale_acc,
												'amount' => -($combo_price * $combo_qty),
												'narrative' => 'Sale',
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
									
									$product_combo_cost += ($item_cost_total / $item_cost_qty);
								}else{
									$product_combo_cost += ($combo_qty * $combo_detail->cost);
									$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $combo_detail->id,
												'product_type'    => $combo_detail->type,
												'product_code' => $combo_detail->code,
												'quantity' => ($item_quantity * $combo_qty) * -1,
												'unit_quantity' => $combo_unit->unit_qty,
												'expiry' => $expired_data,
												'unit_code' => $combo_unit->code,
												'unit_id' => $combo_detail->unit,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $combo_detail->cost,
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
									//=======accounting=========//
										$productAcc = $this->site->getProductAccByProductId($combo_detail->id);
										if($this->Settings->accounting == 1 &&  $sale_status=='completed'){
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->stock_acc,
												'amount' => -($combo_detail->cost * ($item_quantity * $combo_qty)),
												'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.($item_quantity * $combo_qty).'#'.'Cost: '.$combo_detail->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->cost_acc,
												'amount' => ($combo_detail->cost * ($item_quantity * $combo_qty)),
												'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.($item_quantity * $combo_qty).'#'.'Cost: '.$combo_detail->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											

											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->sale_acc,
												'amount' => -($combo_price * $combo_qty),
												'narrative' => 'Sale',
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											
										}
									//============end accounting=======//
								}
								
								
								
								$raw_materials[] = array(
									"product_id" => $combo_detail->id,
									"quantity" => ($item_quantity * $combo_qty)
								);
							}
							
						}
						$cost  = $product_combo_cost;	
					}else if($product_details->type=='bom'){
						$bom_type = $_POST['bom_type'][$r];
						$product_boms = $this->sales_model->getBomProductByStandProduct($item_id,$bom_type);
						if($product_boms){
							$product_bom_cost = 0;
							foreach($product_boms as $product_bom){
								if($this->Settings->accounting_method == '0'){
									$costs = $this->site->getFifoCost($product_bom->product_id,($item_quantity * $product_bom->quantity),$stockmoves);
								}else if($this->Settings->accounting_method == '1'){
									$costs = $this->site->getLifoCost($product_bom->product_id,($item_quantity * $product_bom->quantity),$stockmoves);
								}else if($this->Settings->accounting_method == '3'){
									$costs = $this->site->getProductMethod($product_bom->product_id,($item_quantity * $product_bom->quantity),$stockmoves);
								}
								
								if($costs){
									$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
									$item_cost_qty  = 0;
									$item_cost_total = 0;
									$item_costs = '';
									foreach($costs as $cost_item){
										$item_cost_qty += $cost_item['quantity'];
										$item_cost_total += $cost_item['cost'] * $cost_item['quantity'];

										$stockmoves[] = array(
											'transaction' => 'Sale',
											'product_id' => $product_bom->product_id,
											'product_type'    => $product_bom->product_type,
											'product_code' => $product_bom->product_code,
											'quantity' => $cost_item['quantity'] * (-1),
											'expiry' => $expired_data,
											'unit_quantity' => $product_bom->unit_qty,
											'unit_code' => $product_bom->code,
											'unit_id' => $product_bom->unit_id,
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $cost_item['cost'],
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);
										//========accounting=========//
											if($this->Settings->accounting == 1 && $product_bom->product_type && $sale_status=='completed'){
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,

												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
											}
										//============end accounting=======//
										$item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
									}
									$product_bom_cost += ($item_cost_total / $item_cost_qty);
								}else{
									$product_bom_cost += ($product_bom->quantity * $product_bom->cost);
									$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $product_bom->product_id,
												'product_type'    => $product_bom->product_type,
												'product_code' => $product_bom->product_code,
												'quantity' => ($item_quantity * $product_bom->quantity) * -1,
												'unit_quantity' => $product_bom->unit_qty,
												'expiry' => $expired_data,
												'unit_code' => $product_bom->code,
												'unit_id' => $product_bom->unit_id,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $product_bom->cost,
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
									//=======accounting=========//
										$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
										if($this->Settings->accounting == 1 && $product_bom->product_type != 'manual' && $sale_status=='completed'){
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->stock_acc,
												'amount' => -($product_bom->cost * ($item_quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.($item_quantity * $product_bom->quantity).'#'.'Cost: '.$product_bom->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->cost_acc,
												'amount' => ($product_bom->cost * ($item_quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.($item_quantity * $product_bom->quantity).'#'.'Cost: '.$product_bom->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
										}
									//============end accounting=======//
								}
								$raw_materials[] = array(
									"product_id" => $product_bom->product_id,
									"quantity" => ($item_quantity * $product_bom->quantity)
								);
							}
							$cost  = $product_bom_cost;
						}else{
							$error = lang('please_check_product').' '.$item_code;
							$this->session->set_flashdata('error', $error);
							redirect($_SERVER["HTTP_REFERER"]);
						}
					}else{
						if($this->Settings->accounting_method == '0'){
							$costs = $this->site->getFifoCost($item_id,$item_quantity,$stockmoves);
						}else if($this->Settings->accounting_method == '1'){
							$costs = $this->site->getLifoCost($item_id,$item_quantity,$stockmoves);
						}else if($this->Settings->accounting_method == '3'){
							$costs = $this->site->getProductMethod($item_id,$item_quantity,$stockmoves);
						}

						if(isset($costs) && $costs && $item_serial=='' && $item_quantity > 0){
							$productAcc = $this->site->getProductAccByProductId($item_id);
							$item_cost_qty  = 0;
							$item_cost_total = 0;
							$item_costs = '';
							foreach($costs as $cost_item){
								$item_cost_qty += $cost_item['quantity'];
								$item_cost_total += $cost_item['cost'] * $cost_item['quantity'];

								$stockmoves[] = array(
									'transaction' => 'Sale',
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'quantity' => $cost_item['quantity'] * (-1),
									'unit_quantity' => $unit->unit_qty,
									'expiry' => $expired_data,
									'unit_code' => $unit->code,
									'unit_id' => $item_unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => $cost_item['cost'],
									'serial_no' => $item_serial,
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);
								//========accounting=========//
									if($this->Settings->accounting == 1 && $item_type != 'manual' && $sale_status=='completed'){
										$accTrans[] = array(
											'transaction' => 'Sale',
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->stock_acc,
											'amount' => -($cost_item['cost'] * $cost_item['quantity']),
											'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
										$accTrans[] = array(
											'transaction' => 'Sale',
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->cost_acc,
											'amount' => ($cost_item['cost'] * $cost_item['quantity']),
											'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
									}
								//============end accounting=======//
								$item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
							}
							$cost = $item_cost_total / $item_cost_qty;

						}else{
							if($item_serial!=""){
								$item_serials = explode("#",$item_serial);
								if(count($item_serials) > 0){
									for($b = 0; $b<= count($item_serials); $b++){
										if($item_serials[$b]!=''){
											if($product_serial_detail = $this->sales_model->getProductSerial($item_serials[$b],$item_id,$warehouse_id)){
												$product_details->cost = $product_serial_detail->cost;
											}
											$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $item_id,
												'product_code' => $item_code,
												'product_type' => $item_type,
												'option_id' => $item_option,
												'quantity' => (-1),
												'unit_quantity' => $unit->unit_qty,
												'unit_code' => $unit->code,
												'expiry' => $expired_data,
												'unit_id' => $item_unit,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $product_details->cost,
												'serial_no' => $item_serials[$b],
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
										}
									}
								}else{
									if($product_serial_detail = $this->sales_model->getProductSerial($item_serial,$item_id,$warehouse_id)){
										$product_details->cost = $product_serial_detail->cost;
									}
									$stockmoves[] = array(
										'transaction' => 'Sale',
										'product_id' => $item_id,
										'product_code' => $item_code,
										'product_type' => $item_type,
										'option_id' => $item_option,
										'quantity' => $item_quantity * (-1),
										'unit_quantity' => $unit->unit_qty,
										'unit_code' => $unit->code,
										'expiry' => $expired_data,
										'unit_id' => $item_unit,
										'warehouse_id' => $warehouse_id,
										'date' => $date,
										'real_unit_cost' => ($item_quantity < 0 ? (($item_net_price + $item_tax) / ($unit->unit_qty > 0 ? $unit->unit_qty : 1)) : $product_details->cost),
										'serial_no' => $item_serial,
										'reference_no' => $reference,
										'user_id' => $this->session->userdata('user_id'),
									);
								}
								
							}else{
								$stockmoves[] = array(
									'transaction' => 'Sale',
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'quantity' => $item_quantity * (-1),
									'unit_quantity' => $unit->unit_qty,
									'unit_code' => $unit->code,
									'expiry' => $expired_data,
									'unit_id' => $item_unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => ($item_quantity < 0 ? (($item_net_price + $item_tax) / ($unit->unit_qty > 0 ? $unit->unit_qty : 1)) : $cost),
									'serial_no' => $item_serial,
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);
							}
							

							//========accounting=========//
								$productAcc = $this->site->getProductAccByProductId($item_id);
								if($this->Settings->accounting == 1 && $item_type != 'manual' && $sale_status=='completed'){
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->stock_acc,
										'amount' => ($cost * $item_quantity) * (-1),
										'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
								
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->cost_acc,
										'amount' => ($cost * $item_quantity),
										'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
									
									
								}
							//============end accounting=======//
						}
					}
					//========accounting=========//
						if($this->Settings->accounting == 1){
							if ($item_type == 'manual') {
								$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
								$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $saleAcc->other_income_acc,
										'amount' => -($item_net_price * $item_unit_quantity),
										'narrative' => 'Sale',
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
							}else{
								if($product_details->type !='combo'){
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->sale_acc,
										'amount' => -(($item_net_price + $item_tax) * $item_unit_quantity),
										'narrative' => 'Sale',
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
								}
							}

						}
					//============end accounting=======//
					
					$item_serials = ($item_serial != "" ? explode("#",$item_serial) : false);
					if(count($item_serials) > 0 && $item_serial!=""){
						for($b = 0; $b<= count($item_serials); $b++){
							if($item_serials[$b]!=''){
								if($product_serial_detail = $this->sales_model->getProductSerial($item_serials[$b],$item_id,$warehouse_id)){
									$product_details->cost = $product_serial_detail->cost;
								}
								$products[] = array(
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_name' => $item_name,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'net_unit_price' => $item_net_price,
									'unit_price' => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
									'cost' => $product_details->cost,
									'quantity' => 1,
									'product_unit_id' => $item_unit,
									'product_unit_code' => $unit ? $unit->code : NULL,
									'unit_quantity' => 1,
									'warehouse_id' => $warehouse_id,
									'item_tax' => $pr_item_tax,
									'tax_rate_id' => $pr_tax,
									'tax' => $tax,
									'discount' => $item_discount,
									'comment'         => $item_comment,
									'item_discount' => $pr_item_discount,
									'subtotal' => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
									'serial_no' => $item_serials[$b],
									'real_unit_price' => $real_unit_price,
									'parent_id' => $parent_id,
									'item_costs' => isset($item_costs) ? $item_costs : '',
									'raw_materials' =>json_encode($raw_materials),
									'combo_product' => json_encode($combo_products),
									'expiry' => $expired_data,
									'bom_type' => isset($bom_type) ? $bom_type : '',
									'electricity' => $electricity,
									'old_number' => $old_number,
									'new_number' => $new_number,
									'currency_rate' => $currency_rate,
									'currency_code' => $currency_code,
									'consignment_item_id' => $consignment_item_id,
								);
								
								if($this->config->item('consignments') && $consignment_id > 0 && $consignment_item_id > 0){
									$consignment_item = $this->sales_model->getConsignmentItemByID($consignment_item_id,$item_id,$expired_data,$item_serials[$b]);
									$return_consign = $consignment_item->quantity;
									if($consignment_item->quantity >= 1){
										$return_consign = 1;
									}
									if($return_consign > 0){
										$consign_unit = $this->site->getProductUnit($item_id, $product_details->unit);
										$stockmoves[] = array(
											'transaction' => 'Sale',
											'product_id' => $item_id,
											'product_code' => $item_code,
											'product_type' => $item_type,
											'option_id' => $item_option,
											'quantity' => $return_consign,
											'unit_quantity' => $consign_unit->unit_qty,
											'unit_code' => $consign_unit->code,
											'expiry' => $expired_data,
											'unit_id' => $product_details->unit,
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $product_details->cost,
											'serial_no' => $item_serials[$b],
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);
										if($this->Settings->accounting == 1 && $item_type != 'manual'){
											$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->stock_acc,
												'amount' => ($product_details->cost * $return_consign),
												'narrative' => 'Consignment Product Code: '.$item_code.'#'.'Qty: '.$return_consign.'#'.'Cost: '.$product_details->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $saleAcc->consignment_acc,
												'amount' => -($product_details->cost * $return_consign),
												'narrative' => 'Consignment Product Code: '.$item_code.'#'.'Qty: '.$return_consign.'#'.'Cost: '.$product_details->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
										}
									}
								}
								
							}
						}
					}else{
						$products[] = array(
							'product_id' => $item_id,
							'product_code' => $item_code,
							'product_name' => $item_name,
							'product_type' => $item_type,
							'option_id' => $item_option,
							'net_unit_price' => $item_net_price,
							'unit_price' => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
							'cost' => $cost,
							'quantity' => $item_quantity,
							'product_unit_id' => $item_unit,
							'product_unit_code' => $unit ? $unit->code : NULL,
							'unit_quantity' => $item_unit_quantity,
							'warehouse_id' => $warehouse_id,
							'item_tax' => $pr_item_tax,
							'tax_rate_id' => $pr_tax,
							'tax' => $tax,
							'discount' => $item_discount,
							'comment' => $item_comment,
							'item_note' => $item_note,
							'item_discount' => $pr_item_discount,
							'subtotal' => $this->cus->formatDecimalRaw($subtotal),
							'serial_no' => $item_serial,
							'real_unit_price' => $real_unit_price,
							'parent_id' => $parent_id,
							'item_costs' => isset($item_costs) ? $item_costs : '',
							'raw_materials' =>json_encode($raw_materials),
							'combo_product' => json_encode($combo_products),
							'expiry' => $expired_data,
							'bom_type' => isset($bom_type) ? $bom_type : '',
							'electricity' => $electricity,
							'old_number' => $old_number,
							'new_number' => $new_number,
							'currency_rate' => $currency_rate,
							'currency_code' => $currency_code
						);
						
						if($this->config->item('consignments') && $consignment_id > 0 && $consignment_item_id > 0){
							$products[$r]['consignment_item_id'] = $consignment_item_id;
							$return_consign = 0;
							$consignment_item = $this->sales_model->getConsignmentItemByID($consignment_item_id,$item_id,$expired_data,$item_serial);
							if($consignment_item->quantity >= $item_quantity){
								$return_consign = $item_quantity;
							}else if($item_quantity > 0){
								$return_consign = $consignment_item->quantity;
							}
							if($return_consign > 0){
								$consign_unit = $this->site->getProductUnit($item_id, $product_details->unit);
								$stockmoves[] = array(
									'transaction' => 'Sale',
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'quantity' => $return_consign,
									'unit_quantity' => $consign_unit->unit_qty,
									'unit_code' => $consign_unit->code,
									'expiry' => $expired_data,
									'unit_id' => $product_details->unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => $cost,
									'serial_no' => $item_serial,
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);
								if($this->Settings->accounting == 1 && $item_type != 'manual'){
									$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->stock_acc,
										'amount' => ($cost * $return_consign),
										'narrative' => 'Consignment Product Code: '.$item_code.'#'.'Qty: '.$return_consign.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $saleAcc->consignment_acc,
										'amount' => -($cost * $return_consign),
										'narrative' => 'Consignment Product Code: '.$item_code.'#'.'Qty: '.$return_consign.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
								}
							}
						}
					}

					if($this->config->item('product_promotions')){
						$product_promotions = json_decode($_POST['product_promotion'][$r]);
						if($product_promotions){
							foreach($product_promotions as $product_promotion){
								$extra_details = $this->site->getProductByID($product_promotion->product_id);
								if($extra_details){
									$extraUnit = $this->site->getProductUnit($extra_details->id,$extra_details->unit);
									$extractProductID = $extra_details->id;
									$extractQuantity = $extraUnit->unit_qty * $product_promotion->product_quantity;

									if($this->Settings->accounting_method == '0'){
										$extraCosts = $this->site->getFifoCost($extractProductID,$extractQuantity,$stockmoves);
									}else if($this->Settings->accounting_method == '1'){
										$extraCosts = $this->site->getLifoCost($extractProductID,$extractQuantity,$stockmoves);
									}else if($this->Settings->accounting_method == '3'){
										$extraCosts = $this->site->getProductMethod($extractProductID,$extractQuantity,$stockmoves);
									}

									if($extraCosts){
										$productAcc = $this->site->getProductAccByProductId($extractProductID);
										$item_cost_qty  = 0;
										$item_cost_total = 0;
										foreach($extraCosts as $extraCost){
											$item_cost_qty += $extraCost['quantity'];
											$item_cost_total += $extraCost['cost'] * $extraCost['quantity'];
											$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $extractProductID,
												'product_code' => $extra_details->code,
												'product_type' => $extra_details->type,
												'option_id' => 0,
												'quantity' => $extraCost['quantity'] * (-1),
												'unit_quantity' => $extraUnit->unit_qty,
												'unit_code' => $extraUnit->code,
												'unit_id' => $extra_details->unit,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $extraCost['cost'],
												'serial_no' => '',
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
											//========accounting=========//
												if($this->Settings->accounting == 1){
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->stock_acc,
														'amount' => -($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->cost_acc,
														'amount' => ($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
												}
											//============end accounting=======//

										}
										$extra_details->cost = $item_cost_total / $item_cost_qty;
									}else{
										$stockmoves[] = array(
											'transaction' => 'Sale',
											'product_id' => $extractProductID,
											'product_code' => $extra_details->code,
											'product_type' => $extra_details->type,
											'option_id' => 0,
											'quantity' => $extractQuantity * (-1),
											'unit_quantity' => $extraUnit->unit_qty,
											'unit_code' => $extraUnit->code,
											'unit_id' => $extra_details->unit,
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $extra_details->cost,
											'serial_no' => '',
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);

										//========accounting=========//
											if($this->Settings->accounting == 1){
												$productAcc = $this->site->getProductAccByProductId($extractProductID);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
											}
										//============end accounting=======//
									}
									
									$products[] = array(
										'product_id' => $extra_details->id,
										'product_code' => $extra_details->code,
										'product_name' => $extra_details->name,
										'product_type' => $extra_details->type,
										'net_unit_price' => 0,
										'unit_price' => 0,
										'serial_no' => '',
										'cost' => $extra_details->cost,
										'quantity' => $extractQuantity,
										'product_unit_id' => $extra_details->unit,
										'product_unit_code' => $extraUnit->code,
										'unit_quantity' => $extractQuantity,
										'warehouse_id' => $warehouse_id,
										'subtotal' => 0,
										'real_unit_price' => 0
									);
								}	
							}
						}
					}

					
					if($this->Settings->foc == 1 && $_POST['foc'][$r] > 0){
						$foc_cost = 0;
						$foc = $_POST['foc'][$r];
						$products[$r]['foc'] = $foc;
						if($this->Settings->accounting_method == '0'){
							$focCosts = $this->site->getFifoCost($item_id,$foc,$stockmoves);
						}else if($this->Settings->accounting_method == '1'){
							$focCosts = $this->site->getLifoCost($item_id,$foc,$stockmoves);
						}else if($this->Settings->accounting_method == '3'){
							$focCosts = $this->site->getProductMethod($item_id,$foc,$stockmoves);
						}
						
						$focUnit = $this->site->getProductUnit($item_id,$product_details->unit);
						if($focCosts){
							$productAcc = $this->site->getProductAccByProductId($item_id);
							$item_cost_total = 0;
							foreach($focCosts as $focCost){
								$item_cost_total += $focCost['cost'] * $focCost['quantity'];
								$stockmoves[] = array(
									'transaction' => 'Sale',
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'quantity' => $focCost['quantity'] * (-1),
									'unit_quantity' => $focUnit->unit_qty,
									'unit_code' => $focUnit->code,
									'unit_id' => $product_details->unit,
									'warehouse_id' => $warehouse_id,
									'expiry' => $expired_data,
									'date' => $date,
									'real_unit_cost' => $focCost['cost'],
									'serial_no' => '',
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);

								//========accounting=========//
									if($this->Settings->accounting == 1){
										$accTrans[] = array(
											'transaction' => 'Sale',
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->stock_acc,
											'amount' => -($focCost['cost'] * $focCost['quantity']),
											'narrative' => 'FOC Product Code: '.$item_code.'#'.'Qty: '.$focCost['quantity'].'#'.'Cost: '.$focCost['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
										$accTrans[] = array(
											'transaction' => 'Sale',
											'transaction_id' => $id,
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->cost_acc,
											'amount' => ($focCost['cost'] * $focCost['quantity']),
											'narrative' => 'FOC Product Code: '.$item_code.'#'.'Qty: '.$focCost['quantity'].'#'.'Cost: '.$focCost['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
									}
								//============end accounting=======//

							}
							$foc_cost += $item_cost_total;

						}else{
							$foc_cost = ($foc * $product_details->cost);
							$stockmoves[] = array(
									'transaction' => 'Sale',
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'quantity' => $foc * (-1),
									'unit_quantity' => $focUnit->unit_qty,
									'unit_code' => $focUnit->code,
									'unit_id' => $product_details->unit,
									'warehouse_id' => $warehouse_id,
									'expiry' => $expired_data,
									'date' => $date,
									'real_unit_cost' => $product_details->cost,
									'serial_no' => '',
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);

							//========accounting=========//
								if($this->Settings->accounting == 1){
									$productAcc = $this->site->getProductAccByProductId($item_id);
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->stock_acc,
										'amount' => -($product_details->cost * $foc),
										'narrative' => 'FOC Product Code: '.$item_code.'#'.'Qty: '.$foc.'#'.'Cost: '.$product_details->cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->cost_acc,
										'amount' => ($product_details->cost * $foc),
										'narrative' => 'FOC Product Code: '.$item_code.'#'.'Qty: '.$foc.'#'.'Cost: '.$product_details->cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
								}
							//============end accounting=======//
						}
						$products[$r]['foc_cost'] = $foc_cost;
					}
					
					
					
					if($this->config->item('saleman_commission') && $this->Settings->product_commission == 1){
						$product_salesmans = $_POST['product_salesmans'][$r];
						$salesman = $this->site->getUserByID($product_salesmans);
						$products[$r]['salesman_id'] = $salesman->id;
						$products[$r]['salesman'] = $salesman->last_name.' '.$salesman->first_name;
						$p_commission = $this->sales_model->getProductCommission($salesman->id,$item_id);
						if($p_commission && $p_commission->commission != '' && $p_commission->commission != 0){
							$products[$r]['salesman_commission'] = $p_commission->commission;
						}else{
							$products[$r]['salesman_commission'] = $salesman->saleman_commission;
						}
					}

					
					if($this->Settings->product_additional == 1){

						$products[$r]['pro_additionals'] = $_POST['product_additional'][$r];
						if($_POST['product_additional'][$r] != ''){
							$extraProducts = $this->sales_model->getProductAdditionalByID($_POST['product_additional'][$r], $item_unit_quantity);
						}else{
							$extraProducts = false;
						}

						if($extraProducts){
							$products[$r]['extract_product'] = json_encode($extraProducts);
							$extractCost = 0;
							foreach($extraProducts as $extraProduct){
								$extra_details = $this->site->getProductByID($extraProduct['for_product_id']);
								if($extra_details){
									$extraUnit = $this->site->getProductUnit($extra_details->id,$extraProduct['for_unit_id']);
									$extractProductID = $extra_details->id;
									$extractQuantity = $extraUnit->unit_qty * $extraProduct['for_quantity'];

									if($this->Settings->accounting_method == '0'){
										$extraCosts = $this->site->getFifoCost($extractProductID,$extractQuantity,$stockmoves);
									}else if($this->Settings->accounting_method == '1'){
										$extraCosts = $this->site->getLifoCost($extractProductID,$extractQuantity,$stockmoves);
									}else if($this->Settings->accounting_method == '3'){
										$extraCosts = $this->site->getProductMethod($extractProductID,$extractQuantity,$stockmoves);
									}

									if($extraCosts){
										$productAcc = $this->site->getProductAccByProductId($extractProductID);
										$item_cost_total = 0;
										$item_costs = '';
										foreach($extraCosts as $extraCost){
											$item_cost_total += $extraCost['cost'] * $extraCost['quantity'];

											$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $extractProductID,
												'product_code' => $extra_details->code,
												'product_type' => $extra_details->type,
												'option_id' => 0,
												'quantity' => $extraCost['quantity'] * (-1),
												'unit_quantity' => $extraUnit->unit_qty,
												'unit_code' => $extraUnit->code,
												'unit_id' => $extraProduct['for_unit_id'],
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $extraCost['cost'],
												'serial_no' => '',
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
											//========accounting=========//
												if($this->Settings->accounting == 1){
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->stock_acc,
														'amount' => -($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->cost_acc,
														'amount' => ($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
												}
											//============end accounting=======//
										}
										$extractCost += $item_cost_total;

									}else{
										$extractCost += ($extractQuantity * $extra_details->cost);
										$stockmoves[] = array(
											'transaction' => 'Sale',
											'product_id' => $extractProductID,
											'product_code' => $extra_details->code,
											'product_type' => $extra_details->type,
											'option_id' => 0,
											'quantity' => $extractQuantity * (-1),
											'unit_quantity' => $extraUnit->unit_qty,
											'unit_code' => $extraUnit->code,
											'unit_id' => $extraProduct['for_unit_id'],
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $extra_details->cost,
											'serial_no' => '',
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);

										//========accounting=========//
											if($this->Settings->accounting == 1){
												$productAcc = $this->site->getProductAccByProductId($extractProductID);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
											}
										//============end accounting=======//
									}
								}
							}
							$products[$r]['extract_cost'] = $extractCost;
						}
					}
					if($this->Settings->qty_operation == 1){
						$width = $_POST['swidth'][$r];
						$height = $_POST['sheight'][$r];
						$square = $_POST['square'][$r];
						$square_qty = $_POST['square_qty'][$r];
						$products[$r]['width'] = $width;
						$products[$r]['height'] = $height;
						$products[$r]['square'] = $square;
						$products[$r]['square_qty'] = $square_qty;
						if($this->Settings->product_formulation == 1){
							$products[$r]['pro_formulations'] = $_POST['product_formulation'][$r];
							if($_POST['product_formulation'][$r] != ''){
								$extraProducts = $this->cus->productFormulation($_POST['product_formulation'][$r],$width,$height,$square,$square_qty);
							}else{
								$extraProducts = false;
							}
							if($extraProducts){
								$products[$r]['extract_product'] = json_encode($extraProducts);
								$extractCost = 0;
								foreach($extraProducts as $extraProduct){
									$extra_details = $this->site->getProductByID($extraProduct['for_product_id']);
									if($extra_details){
										$extraUnit = $this->site->getProductUnit($extra_details->id,$extraProduct['for_unit_id']);
										$extractProductID = $extra_details->id;
										$extractQuantity = $extraUnit->unit_qty * $extraProduct['for_quantity'];
										if($this->Settings->accounting_method == '0'){
											$extraCosts = $this->site->getFifoCost($extractProductID,$extractQuantity,$stockmoves);
										}else if($this->Settings->accounting_method == '1'){
											$extraCosts = $this->site->getLifoCost($extractProductID,$extractQuantity,$stockmoves);
										}else if($this->Settings->accounting_method == '3'){
											$extraCosts = $this->site->getProductMethod($extractProductID,$extractQuantity,$stockmoves);
										}

										if($extraCosts){
											$productAcc = $this->site->getProductAccByProductId($extractProductID);
											$item_cost_total = 0;
											$item_costs = '';
											foreach($extraCosts as $extraCost){
												$item_cost_total += $extraCost['cost'] * $extraCost['quantity'];

												$stockmoves[] = array(
													'transaction' => 'Sale',
													'product_id' => $extractProductID,
													'product_code' => $extra_details->code,
													'product_type' => $extra_details->type,
													'option_id' => 0,
													'quantity' => $extraCost['quantity'] * (-1),
													'unit_quantity' => $extraUnit->unit_qty,
													'unit_code' => $extraUnit->code,
													'unit_id' => $extraProduct['for_unit_id'],
													'warehouse_id' => $warehouse_id,
													'date' => $date,
													'real_unit_cost' => $extraCost['cost'],
													'serial_no' => '',
													'reference_no' => $reference,
													'user_id' => $this->session->userdata('user_id'),
												);
												//========accounting=========//
													if($this->Settings->accounting == 1){
														$accTrans[] = array(
															'transaction' => 'Sale',
															'transaction_date' => $date,
															'reference' => $reference,
															'account' => $productAcc->stock_acc,
															'amount' => -($extraCost['cost'] * $extraCost['quantity']),
															'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
															'description' => $note,
															'biller_id' => $biller_id,
															'project_id' => $project_id,
															'user_id' => $this->session->userdata('user_id'),
															'customer_id' => $customer_id,
														);
														$accTrans[] = array(
															'transaction' => 'Sale',
															'transaction_date' => $date,
															'reference' => $reference,
															'account' => $productAcc->cost_acc,
															'amount' => ($extraCost['cost'] * $extraCost['quantity']),
															'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
															'description' => $note,
															'biller_id' => $biller_id,
															'project_id' => $project_id,
															'user_id' => $this->session->userdata('user_id'),
															'customer_id' => $customer_id,
														);
													}
												//============end accounting=======//

											}
											$extractCost += $item_cost_total;

										}else{
											$extractCost += ($extractQuantity * $extra_details->cost);
											$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $extractProductID,
												'product_code' => $extra_details->code,
												'product_type' => $extra_details->type,
												'option_id' => 0,
												'quantity' => $extractQuantity * (-1),
												'unit_quantity' => $extraUnit->unit_qty,
												'unit_code' => $extraUnit->code,
												'unit_id' => $extraProduct['for_unit_id'],
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $extra_details->cost,
												'serial_no' => '',
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);

											//========accounting=========//
												if($this->Settings->accounting == 1){
													$productAcc = $this->site->getProductAccByProductId($extractProductID);
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->stock_acc,
														'amount' => -($extra_details->cost * $extractQuantity),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->cost_acc,
														'amount' => ($extra_details->cost * $extractQuantity),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
												}
											//============end accounting=======//
										}
									}
								}
								$products[$r]['extract_cost'] = $extractCost;
							}
						}
					}
					if($this->config->item('fuel') && $fuel_customers){
						$products[$r]['fuel_customer_date'] = $_POST['fuel_customer_date'][$r];
						$products[$r]['fuel_customer_reference'] = $_POST['fuel_customer_reference'][$r];
					}
                    $total += $this->cus->formatDecimalRaw(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
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
            $total_discount = $this->cus->formatDecimalRaw($order_discount + $product_discount);
            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->cus->formatDecimalRaw($order_tax_details->rate);
                    } elseif ($order_tax_details->type == 1) {
                        $order_tax = $this->cus->formatDecimalRaw(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }
			
            $total_tax = $this->cus->formatDecimalRaw(($product_tax + $order_tax), 4);
			$grand_total = $this->cus->formatDecimalRaw(($total + $total_tax + $this->cus->formatDecimalRaw($shipping) - $order_discount), 4);
            $currencies =  $this->site->getAllCurrencies();
			if(!empty($currencies)){
				foreach($currencies as $currency_row){
					if($currency_row->code=='USD'){
						if ($this->input->post('paid_by') == 'gift_card') {
							$gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
							$current_amount = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
						}else{
							$current_amount = $this->input->post('amount-paid');
						}
					}else{
						$current_amount = 0;
					}
					$currency[] = array(
								"amount" => $current_amount,
								"currency" => $currency_row->code,
								"rate" => ($this->input->post("exchange_rate_".$currency_row->code) ? $this->input->post("exchange_rate_".$currency_row->code) : $currency_row->rate),
							);
				}
			}
			
			//=======accounting=========//
			if($this->Settings->accounting == 1){
				$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
				$accTrans[] = array(
					'transaction' => 'Sale',
					'transaction_date' => $date,
					'reference' => $reference,
					'account' => ($this->Settings->default_receivable_account==0 ? $saleAcc->ar_acc : $this->input->post('receivable_account')),
					'amount' => $grand_total,
					'narrative' => 'Sale',
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'user_id' => $this->session->userdata('user_id'),
					'customer_id' => $customer_id,
				);

				if($order_discount != 0){
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->sale_discount_acc,
						'amount' => $order_discount,
						'narrative' => 'Order Discount',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				if($order_tax != 0){
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->vat_output,
						'amount' => -$order_tax,
						'narrative' => 'Order Tax',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				if($shipping != 0){
					$accTrans[] = array(
							'transaction' => 'Sale',
							'transaction_date' => $date,
							'reference' => $reference,
							'account' => $saleAcc->shipping_acc,
							'amount' => -$shipping,
							'narrative' => 'Shipping',
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $customer_id,
						);
				}
			}

			$agency_commission = array();
			$agency_limit_percent = array();
			$agency_value_commission = array();
			if(isset($agency_id)){
				foreach($agency_id as $agency){
					$agency_details = $this->sales_model->getAgencyByID($agency);
					$agency_commission[] = $agency_details->agency_commission;
					$agency_limit_percent[] = $agency_details->agency_limit_percent;
					$agency_value_commission[] = $agency_details->agency_value_commission;
				}
			}

			//============end accounting=======//


			$data = array(
				'date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                "description" => rtrim($description,","),
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->cus->formatDecimalRaw($shipping),
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'sale_status' => $sale_status,
                'payment_status' => $payment_status,
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'paid' => 0,
				'delivery_id' => $delivery_id,
				'agency_id' => json_encode($agency_id),
				'agency_commission' => json_encode($agency_commission),
				'agency_limit_percent' => json_encode($agency_limit_percent),
				'agency_value_commission' => json_encode($agency_value_commission),
                'saleman_id' => $saleman->id,
				'saleman' => $saleman->last_name.' '.$saleman->first_name,
				'saleman_commission' => $saleman_commission,
				'created_by' => $this->session->userdata('user_id'),
				'currencies' => json_encode($currency),
				'groups_delivery' => json_encode($groups_delivery),
				'stock_deduction' => $stock_deduction,
				'consignment_id' => $consignment_id,
				'ar_account' => ($this->Settings->default_receivable_account==0 ? $saleAcc->ar_acc : $this->input->post('receivable_account'))
            );
            

			if($fuel_customers){
				$data['fuel_customers'] = $fuel_customers;
			}
			if($sale_status!='completed'){
				$stockmoves = array();
			}
			if($this->Settings->project == 1){
				$data['project_id'] = $project_id;
			}
			if($this->Settings->car_operation == 1){
				$data['vehicle_model'] = $vehicle_model;
                $data['vehicle_kilometers'] = $vehicle_kilometers;
                $data['vehicle_vin_no'] = $vehicle_vin_no;
				$data['vehicle_plate'] = $vehicle_plate;
                $data['job_number'] = $job_number;
				$data['mechanic'] = $mechanic;
			}
			$rental_id = $this->input->post('rental_id');
			$rental_deposit = $this->input->post('rental_deposit');
			$rental_status = $this->input->post('rental_status');
			if($this->config->item("room_rent") && $rental_id > 0){
				$data['rental_id'] = $rental_id;
				$data['rental_deposit'] = $rental_deposit?$rental_deposit:null;
				$data['rental_status'] = $rental_status?$rental_status:null;
				$data['from_date'] = $this->cus->fld($this->input->post('from_date'));
				$data['to_date'] = $this->cus->fld($this->input->post('to_date'));
			}
            if ($payment_status == 'partial' || $payment_status == 'paid') {
				if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
					$paying_to = $paymentAcc->customer_deposit_acc;
				}else{
					$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
					$paying_to = $cash_account->account_code;
				}
				
                if ($this->input->post('paid_by') == 'deposit') {
                    if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                        $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
				}
                if ($this->input->post('paid_by') == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance = $gc->balance - $amount_paying;
                    $payment = array(
                        'date' => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount' => $this->cus->formatDecimalRaw($amount_paying),
						'discount' => $this->cus->formatDecimalRaw($this->input->post("payment_discount")),
                        'paid_by' => $this->input->post('paid_by'),
                        'cheque_no' => $this->input->post('cheque_no'),
                        'cc_no' => $this->input->post('gift_card_no'),
                        'cc_holder' => $this->input->post('pcc_holder'),
                        'cc_month' => $this->input->post('pcc_month'),
                        'cc_year' => $this->input->post('pcc_year'),
                        'cc_type' => $this->input->post('pcc_type'),
                        'created_by' => $this->session->userdata('user_id'),
                        'note' => $this->input->post('payment_note'),
                        'type' => 'received',
                        'gc_balance' => $gc_balance,
						'currencies' => json_encode($currency),
						'account_code' => $paying_to,
                    );
                } else {
					$amount_paying = $this->input->post('amount-paid');
                    $payment = array(
                        'date' => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount' => $this->cus->formatDecimalRaw($this->input->post('amount-paid')),
						'discount' => $this->cus->formatDecimalRaw($this->input->post("payment_discount")),
                        'paid_by' => $this->input->post('paid_by'),
                        'created_by' => $this->session->userdata('user_id'),
                        'note' => $this->input->post('payment_note'),
                        'type' => 'received',
						'currencies' => json_encode($currency),
						'account_code' => $paying_to,
                    );
                }
				if($this->Settings->accounting == 1){
					$accTranPayments[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $this->input->post('payment_reference_no'),
							'account' => ($this->Settings->default_receivable_account==0 ? $saleAcc->ar_acc : $this->input->post('receivable_account')),
							'amount' => -($amount_paying+$this->input->post("payment_discount")),
							'narrative' => 'Sale Payment '.$reference,
							'description' => $this->input->post('payment_note'),
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $customer_id,
						);
					
					$accTranPayments[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $this->input->post('payment_reference_no'),
							'account' => $paying_to,
							'amount' => $amount_paying,
							'narrative' => 'Sale Payment '.$reference,
							'description' => $this->input->post('payment_note'),
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $customer_id,
						);
					if($this->input->post("payment_discount")>0){
						$accTranPayments[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $this->input->post('payment_reference_no'),
							'account' => $saleAcc->sale_discount_acc,
							'amount' => $this->input->post("payment_discount"),
							'narrative' => 'Sale Payment Discount '.$reference,
							'description' => $this->input->post('payment_note'),
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $customer_id,
						);
					}
				}
            } else {
                $payment = array();
            }
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
			$fuel_sale_id = $this->input->post('fuel_sale_id');
			$repair_id = $this->input->post('repair_id');
			if($quote_id && $fuel_sale_id && !$fuel_customers){
				$data['fuel_sale_id'] = $fuel_sale_id;
			}else if($quote_id && $repair_id && !$fuel_customers){
				$data['repair_id'] = $repair_id;
			}else if ($quote_id && $sale_order_id != 1 && !$fuel_customers) {
				$qo_reference_no = $this->sales_model->getQuoteByID($quote_id)->reference_no;
				$data['qo_reference_no'] = $qo_reference_no;
			}else if($quote_id && $sale_order_id == 1 && !$fuel_customers){
				$so_reference_no = $this->sale_order_model->getSaleOrderByID($quote_id);
				$data['so_reference_no'] = $so_reference_no->reference_no;
				$data['qo_reference_no'] = $so_reference_no->qo_reference_no;
				$data['sale_order_id'] = $quote_id;
				$so_deposit = $this->input->post("so_deposit");
				if($so_deposit > 0){
					$data['so_deposit'] = $so_deposit;
					if($this->Settings->accounting == 1){
						$accTrans[] = array(
									'transaction' => 'Sale',
									'transaction_date' => $date,
									'reference' => $reference,
									'account' => $saleAcc->customer_deposit_acc,
									'amount' => $so_deposit,
									'narrative' => 'Deposit From Sale Order '.$so_reference_no->reference_no,
									'description' => $note,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id,
								);
						$accTrans[] = array(
									'transaction' => 'Sale',
									'transaction_date' => $date,
									'reference' => $reference,
									'account' => ($this->Settings->default_receivable_account==0 ? $saleAcc->ar_acc : $this->input->post('receivable_account')),
									'amount' => - $so_deposit,
									'narrative' => 'Deposit From Sale Order '.$so_reference_no->reference_no,
									'description' => $note,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id,
								);		
					}
				}
			}
			if($this->Settings->product_expiry == '1' && $stockmoves && $products){
				$checkExpiry = $this->site->checkExpiry($stockmoves, $products,'Sale', ($delivery_id > 0 ? 'Delivery' : false), ($delivery_id > 0 ? $delivery_id : false));
				$stockmoves = $checkExpiry['expiry_stockmoves'];
				$products = $checkExpiry['expiry_items'];
			}
			
        }
        
        if ($this->form_validation->run() == true && $id = $this->sales_model->addSale($data, $products, $payment, array(), $biller_id, $stockmoves, $accTrans, $accTranPayments, $cust_prices)) {
            $this->session->set_userdata('remove_slls', 1);
			$this->sales_model->synSaleStatus($quote_id, $sale_order_id);
            $this->session->set_flashdata('message', lang("sale_added")." ".$reference);
			if($this->config->item("send_telegram")){
				$this->telegrambot->sendmsg("Sale ".$data["reference_no"]." (".$data["customer"].") (".$this->cus->formatMoney($data["grand_total"]).") has been added by ".$this->session->userdata("username"));
			}
		    if($this->input->post('add_sale_next')){
				redirect('sales/add');
			}else if(isset($rental_id) && $rental_id > 0){
				redirect('rentals/?sale_id='.$id);
			}else{
				redirect("sales");
			}
        } else {
        	if(in_array('bom',$this->config->item('product_types'))) {
	        	$enable_bom = true;
	        }else{
	        	$enable_bom  = false;
	        }
			$groups_delivery = $this->input->get('groups_delivery');
			$fuel_customers = $this->input->get('fuel_customer');
			$fuel_sale_id = $this->input->get('fuel_sale_id');
			$rental_id = $this->input->get('rental_id');
			$checked_out_date = $this->input->get('checked_out_date');
			$consignment_id = $this->input->get('consignment_id');
			$repair_id = $this->input->get('repair_id');
            if ($fuel_customers || $quote_id || $sale_id || $groups_delivery || $fuel_sale_id || $rental_id || $consignment_id || $repair_id) {
                if($this->config->item('fuel') && $fuel_customers){
					$this->data['fuel_customers'] = json_decode($fuel_customers);
					$quote_id = ($this->data['fuel_customers'][0]?$this->data['fuel_customers'][0]:0);
					$this->data['fuel_customer_id'] = $quote_id;
					$this->data['quote'] = $this->sales_model->getFuelCustomerByID($quote_id);
                    $items = $this->sales_model->getFuelCustomerItemsForSale($this->data['fuel_customers']);
				}else if($consignment_id > 0){
					$quote_id = $consignment_id;
					$this->data['consignment_id'] = $consignment_id;
					$consignment = $this->sales_model->getConsignmentByID($consignment_id);
					if($consignment->status=="completed"){
						$this->session->set_flashdata('error', lang("consignment_is_already_completed"));
						redirect('products/consignments');
					}
					$this->data['quote'] = $consignment;
                    $items = $this->sales_model->getConsigmentItems($consignment_id);
				}else if($repair_id){
					$quote_id = $repair_id;
					$this->data['repair_id'] = $quote_id;
					$repair = $this->sales_model->getRepairByID($quote_id);
					if($repair->status=="completed"){
						$this->session->set_flashdata('error', lang("repair_is_already_completed"));
						redirect('repairs');
					}
                    $this->data['quote'] = $repair;
					$items = $this->sales_model->getAllRepairItems($quote_id);
				}else if($fuel_sale_id){
					$quote_id = $fuel_sale_id;
					$customer_id = ($this->pos_settings->default_customer?$this->pos_settings->default_customer:0);
					$fuel = $this->sales_model->getFuelSaleByID($quote_id);
					$fuel->customer_id = $customer_id;
					$fuel->payment_term = 0;
					$fuel->order_discount_id = 0;
					$fuel->order_tax_id = 0;
					$fuel->shipping = 0;
					$this->data['fuel_sale_id'] = $quote_id;
					$this->data['quote'] = $fuel;
                    $items = $this->sales_model->getAllFuelSaleItems($quote_id);
				}else if($rental_id){
					$quote_id = $rental_id;
					$rental = $this->sales_model->getRentalByID($quote_id);
					$rental_deposit = $this->sales_model->getRentalDepositPayments($quote_id);
					$rental->payment_term = 0;
					$rental->order_discount_id = 0;
					$rental->order_tax_id = 0;
					$rental->shipping = 0;
					$rental->saleman_id = 0;
					$rental->checked_out = 0;
					$rental->deposit = ($rental_deposit->amount?$rental_deposit->amount:0);
					$this->data['rental_id'] = $quote_id;
					$this->data['checked_out_date'] = $checked_out_date;
					$this->data['quote'] = $rental;
					if(isset($checked_out_date) && $checked_out_date){
						$to_date = strtotime($this->cus->fld($checked_out_date));
					}else{
						$to_date = strtotime($rental->to_date);
					}
					$from_date = strtotime($rental->from_date);
					$day_of_month = date('t',$from_date);
					$period = round(($to_date - $from_date) / (60 * 60 * 24));
					$days = ($period > 0 ? $period :$period);
					$room = $this->sales_model->getRoomByID($rental->room_id);
					$items = $this->sales_model->getAllRentalItems($quote_id);
					foreach($items as $item){
						if($item->product_id==$room->product_id){
							//$unit_price = ($item->unit_price / $day_of_month) * $days;
							$unit_price = $item->unit_price;
							$item->unit_price = $unit_price;
							$item->real_unit_price = $unit_price;
							$item->net_unit_price = $unit_price;
							
						}
						

					}
				}else if ($quote_id && $sale_order_id == NULL) {
					$this->data['qa_id'] = $quote_id;
                    $this->data['quote'] = $this->sales_model->getQuoteByID($quote_id);
                    $items = $this->sales_model->getAllQuoteItems($quote_id);
                }else if($quote_id && $sale_order_id == 1){
					$this->data['so_id'] = $quote_id;
					$this->data['quote'] = $this->sale_order_model->getSaleOrderByID($quote_id);
                    $items = $this->sale_order_model->getAllSaleOrderItems($quote_id);
					$this->data['so_deposit'] = $this->sale_order_model->getTotalDeposit($quote_id)->amount;
				}else if($quote_id && $sale_order_id == 2){
					$this->data['dn_id'] = $quote_id;
					$this->data['delivery_id'] = $quote_id;
					$delivery_info = $this->deliveries_model->getDeliveryByID($quote_id);
					$sale_order_info = $this->sale_order_model->getSaleOrderByID($delivery_info->sale_order_id);
					if($sale_order_info){
						$delivery_info->order_discount = $sale_order_info->order_discount;
						$delivery_info->saleman_id = $sale_order_info->saleman_id;
						$delivery_info->payment_term = $sale_order_info->payment_term;
						$delivery_info->shipping = $sale_order_info->shipping;
						$delivery_info->order_tax_id = $sale_order_info->order_tax_id;
						$delivery_info->order_discount_id = $sale_order_info->order_discount_id;
					}
					$this->data['quote'] = $delivery_info;
                    $items = $this->deliveries_model->getAllDeliveryItems($quote_id);
				}else if ($sale_id) {
                    $this->data['quote'] = $this->sales_model->getInvoiceByID($sale_id);
                    $items = $this->sales_model->getAllInvoiceItems($sale_id);
                } else if($groups_delivery){
					$this->data['groups_delivery'] = json_decode($groups_delivery);
					$quote_id = ($this->data['groups_delivery'][0]?$this->data['groups_delivery'][0]:0);
					$this->data['delivery_id'] = $quote_id;
					$delivery_info = $this->deliveries_model->getDeliveryByID($quote_id);
					$sale_order_info = $this->sale_order_model->getSaleOrderByID($delivery_info->sale_order_id);
					if($sale_order_info){
						$delivery_info->order_discount = $sale_order_info->order_discount;
						$delivery_info->saleman_id = $sale_order_info->saleman_id;
						$delivery_info->payment_term = $sale_order_info->payment_term;
						$delivery_info->shipping = $sale_order_info->shipping;
						$delivery_info->order_tax_id = $sale_order_info->order_tax_id;
						$delivery_info->order_discount_id = $sale_order_info->order_discount_id;
					}
					$this->data['quote'] = $delivery_info;
                    $items = $this->deliveries_model->getAllGroupsDeliveryItems($this->data['groups_delivery']);
				}
				if($this->config->item('saleman_commission') && isset($this->data['quote']->saleman_id)){	
					$saleman_info = $this->site->getUser($this->data['quote']->saleman_id);
					$this->data['saleman_info'] = $saleman_info;
				}
				if($this->Settings->product_additional == 1){
					$additional_products = $this->sales_model->getProductAdditionals();
				}else{
					$additional_products = false;
				}
				if($this->Settings->product_formulation == 1){
                    $product_formulations = $this->sales_model->getProductFormulation();
                }else{
                    $product_formulations = false;
                }
				if($this->config->item('saleman_commission') && $this->Settings->product_commission == 1){
					$salesmans = $this->site->getSalemans();
					$product_commission = true;
				}else{
					$salesmans = false;
					$product_commission = false;
				}
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);

                    if (!$row) {
                        $row = json_decode('{}');
                        $row->tax_method = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $row->quantity = 0;
                    $pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
                    }
					
					// SO Approved Condition
					//$sale_order = $this->sales_model->getSaleOrderByApproval($row->id, $quote_id);
					//$row->quantity -= $sale_order->quantity;

					// Delivery Condition
					if($quote_id && $sale_order_id == 2){
						$item->quantity -= $item->foc_qty;
						$item->foc = $item->foc_qty;
						$delivery_unit = $this->site->getProductUnit($item->product_id,$item->product_unit_id);
						if($delivery_unit){
							$item->unit_quantity = $item->quantity / $delivery_unit->unit_qty;
						}else{
							$item->unit_quantity = $item->quantity;
						}
						$row->quantity += ($item->quantity);
					}
					// Groups Delivery
					if($groups_delivery){
						$row->quantity += ($item->quantity);
					}
					
					if($this->config->item('consignments') && $consignment_id > 0){
						$row->quantity += $item->quantity;
						$item->unit_quantity = $item->quantity;
						$item->unit_price = $item->real_unit_price;
						$item->net_unit_price = $item->real_unit_price;
						$item->product_unit_id = $row->unit;
						$item->item_discount = 0;
						$row->consignment_item_id = $item->id;
					}
					
					if($this->config->item('fuel') && $fuel_customers){
						$row->fuel_customer_date = $item->fuel_customer_date;
						$row->fuel_customer_reference = $item->fuel_customer_reference;
					}
				
					$row->fup = 1;
                    $row->id = $item->product_id;
                    $row->code = $item->product_code;
                    $row->name = $item->product_name;
                    $row->type = $item->product_type;
                    $row->qty = $item->quantity;
                    $row->base_quantity = $item->quantity;
                    $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                    $row->base_unit_price = $row->price ? $row->price : $item->real_unit_price;
                    $row->unit = $item->product_unit_id;
                    $row->qty = $item->unit_quantity;
                    $row->discount = (isset($item->discount) && ($this->Owner || $this->Admin || $this->session->userdata('allow_discount'))) ? $item->discount : '0';
                    $row->price = $this->cus->formatDecimalRaw($item->net_unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity));
                    $row->cost = isset($row->cost)? $row->cost: 0;
					$row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity) + $this->cus->formatDecimalRaw($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                    $row->real_unit_price = $item->real_unit_price;
                    $row->tax_rate = isset($item->tax_rate_id)?$item->tax_rate_id:NULL;
                    $row->serial = isset($item->serial_no)?$item->serial_no: '';
                    $row->option = isset($item->option_id)?$item->option_id:0;
					$row->swidth = isset($item->width)?$item->width:0;
					$row->sheight = isset($item->height)?$item->height:0;
					$row->square = isset($item->square)?$item->square:0;
					$row->square_qty = isset($item->square_qty)?$item->square_qty:0;
					$row->product_formulation = isset($item->pro_formulations)?$item->pro_formulations:NULL;
					$row->comment = isset($item->comment)?$item->comment:"";
					$row->foc = isset($item->foc)?$item->foc:0;
					$row->item_note = isset($item->warranty) ?$item->warranty:'';
					$product_fress = false;
					if($this->config->item('product_promotions')==true){
						$product_promotions = $this->sales_model->getProductPromotions($row->id,$this->data['quote']->customer_id);
						if($product_promotions){
							$product_pro_qty = $item->quantity;
							foreach($product_promotions as $product_promotion){
								if($product_pro_qty >= $product_promotion->min_qty && $product_pro_qty <= $product_promotion->max_qty){
									$product_fress[] = array(
										'product_id' => $product_promotion->product_id,
										'product_name' => $product_promotion->product_name .' ('.$product_promotion->product_code.')',
										'product_quantity' => $product_promotion->free_qty,
									);
								}
							}
						}
					}else{
						$product_promotions = false;
					}
					$row->product_frees = $product_fress;
					$currencies = false;
					if($this->config->item('product_currency')==true){
						$currencies = $this->site->getAllCurrencies();
						foreach($currencies as $currency){
							if($currency->code == $row->currency_code){
								$currency->rate = $row->currency_rate;
							}
						}
						$row->price = $row->price * ($row->currency_rate);
						$row->real_currency_rate = $row->currency_rate;
					}
					$room_rent = false;
					if($this->config->item('room_rent') && $rental_id > 0){
						$row->old_number = (double)$item->old_number;
						$room_rent = true;
					}
					if($this->Settings->product_expiry == '1'){
						if($this->config->item('consignments') && $consignment_id > 0){
							$product_expiries = $this->sales_model->getProductExpiredWithSub($row->id, $item->warehouse_id, 'Consignment' , $consignment_id);
						}else{
							$product_expiries = $this->site->getProductExpiredByProductID($row->id, $item->warehouse_id, ((isset($this->data['delivery_id']) && $this->data['delivery_id'] > 0) ? 'Delivery' : false), ((isset($this->data['delivery_id']) && $this->data['delivery_id'] > 0) ? $this->data['delivery_id'] : false));
						}
						foreach($product_expiries as $product_expirie){
							if(isset($item->expiry) && $item->expiry !='' && $item->expiry != '0000-00-00'){
								$row->expired = $this->cus->hrsd($item->expiry);
								break;
							}else if($product_expirie->quantity > 0){
								$row->expired = $product_expirie->expiry;
								break; 
							}
						}
					}else{
						$product_expiries = false;
					}
					if($this->Settings->search_by_category==1){
						$category = $this->site->getCategoryByID($row->category_id);
					}else{
						$category = false;
					}
                    $options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                    if ($options) {
                        $option_quantity = 0;
                        foreach ($options as $option) {
                            $pis = $this->site->getStockmoves($row->id, $item->warehouse_id, $item->option_id);
                            if ($pis) {
                                foreach ($pis as $pi) {
                                    $option_quantity += $pi->quantity_balance;
                                }
                            }
                        }
                    }
                    $combo_items = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->sales_model->getComboProducts($row->id);
                    }
					if($enable_bom) {
						$bom_typies = $this->sales_model->getTypeBoms($row->id);
						if ($bom_typies != false) {
							$row->bom_type = $bom_typies[0]->bom_type;
						}
					}else{
						$bom_typies = false;
					}
					if($this->Settings->product_serial == 1){
						$product_serials = $this->sales_model->getProductSerialDetailsByProductId($row->id, $item->warehouse_id, $row->serial);
					}else{
						$product_serials = false;
					}
                    $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
					$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    if($fuel_customers){
						$ri = $c;
					}else{
						$ri = $this->Settings->item_addition ? $row->id : $c;
					}
                    $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                            'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 
                            'product_formulations' => $product_formulations, 'additional_products' => $additional_products, 'product_expiries' => $product_expiries, 
							'enable_bom'=> $enable_bom, 'product_serials' => $product_serials,'bom_typies' => $bom_typies,'salesmans' => $salesmans,'product_commission' => $product_commission,
							'currencies' => $currencies, 'product_promotions' => $product_promotions, 'room_rent' => $room_rent);

                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
            }else{
				$this->data['quote'] = false;
			}
			if($this->config->item('quotation')){
				$this->data['quotations'] = $this->site->getRefQuotations();	
			}
			if($this->config->item('saleorder')){
				$this->data['saleorders'] = $this->site->getRefSaleOrders('approved');	
			}
			if($this->config->item('deliveries')){
				$this->data['deliveries'] = $this->site->getRefDelivery('completed');	
			}
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['salemans'] = $this->site->getSalemans();
			$this->data['agencies'] = $this->site->getAgencies();
			$this->data['user'] = $this->site->getUser($this->session->userdata("user_id"));
            $this->data['quote_id'] = $quote_id ? $quote_id : $sale_id;
			$this->data['sale_order_id'] = $sale_order_id ? $sale_order_id : 0;
			$this->data['projects'] = $this->site->getAllProjects();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$this->data['currencies'] = $this->site->getCurrencies();
            $this->data['slnumber'] = '';
            $this->data['payment_ref'] = '';
			if($this->Settings->accounting == 1){
				if($this->Settings->default_receivable_account != 0){
					$this->data['receivable_account'] = $this->site->getAccount('AS',$this->Settings->default_receivable_account);
				}else{
					$this->data['receivable_account'] = false;
				}
			}else{
				$this->data['receivable_account'] = false;
			}
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('add_sale')));
			$meta = array('page_title' => lang('add_sale'), 'bc' => $bc);
            $this->core_page('sales/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->cus->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->cus->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

        if ($this->form_validation->run() == true) {
            $biller_id = $this->input->post('biller');
			if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
			if($inv->pos){
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pos',$biller_id);
			}else{
				$tax_detail = $this->site->getTaxRateByID($this->input->post('order_tax'));
				if($tax_detail && $tax_detail->rate > 0){
					$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tax_so',$biller_id);
				}else{
					$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so',$biller_id);
				}
				if($this->sales_model->getSaleByReference($reference,'sale',$id,$date)){
					$this->session->set_flashdata('error',lang('reference').' "'.$reference.'" '.lang('is_already_existed'));
					$this->cus->md();
				}
			}


			$stock_deduction = ($this->input->post('stock_deduction') == 0 ? 0 : 1);
			$consignment_id = $inv->consignment_id;
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $total_items = $this->input->post('total_items');
            $sale_status = $this->input->post('sale_status');
            $payment_status = $this->input->post('payment_status');
            $payment_term = $this->input->post('payment_term');
			$vehicle_model = $this->input->post('vehicle_model');
			$vehicle_kilometers = $this->input->post('vehicle_kilometers');
			$vehicle_vin_no = $this->input->post('vehicle_vin_no');
			$vehicle_plate = $this->input->post('vehicle_plate');
			$job_number = $this->input->post('job_number');
			$mechanic = $this->input->post('mechanic');
			$project_id = $this->input->post('project');
			$payment_term_info = $this->sales_model->getPaymentTermsByID($payment_term);
			if($payment_term_info){
				if($payment_term_info->term_type=='end_month'){
					$due_date = date("Y-m-t", strtotime($date));
				}else{
					$due_date =  date('Y-m-d', strtotime('+' . $payment_term_info->due_day . ' days', strtotime($date)));
				}
			}else{
				$due_date = null;
			}
			$shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
			$agency_id = $this->input->post('agency_id') ? $this->input->post('agency_id') : NULL;
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->cus->clear_tags($this->input->post('note'));
            $staff_note = $this->cus->clear_tags($this->input->post('staff_note'));
			$saleman = $this->site->getUser($this->input->post('saleman_id'));
			$saleman_commission = trim($this->input->post('commission'));
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_service_types = $_POST['service_types'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
				$item_comment = $_POST['product_comment'][$r];
				$cost = $_POST['cost'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$parent_id = $_POST['parent_id'][$r];
				$currency_code = $_POST['currency_code'][$r];
				$currency_rate = $_POST['currency_rate'][$r];
				$electricity = $_POST['electricity'][$r];
				$old_number = $_POST['old_number'][$r];
				$new_number = $_POST['new_number'][$r];
				$consignment_item_id = $_POST['consignment_item_id'][$r];
				$item_note = $_POST['item_note'][$r];

				if($_POST['expired_data'][$r] && $_POST['expired_data'][$r] != '00/00/0000'){
					$expired_data = $this->cus->fsd($_POST['expired_data'][$r]);
				}else{
					$expired_data = null;
				}
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
					
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
					if(!$cost && $product_details){
						$cost = $product_details->cost;
					}
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
					if ($item_type == 'manual') {
                        $item_quantity = $item_unit_quantity;
                    }

                    $unit_price = $this->cus->formatDecimalRaw($unit_price - $pr_discount);
					// Product Currency
					if($this->config->item('product_currency')==true){
						if($currency_code && $currency_rate){
							$real_unit_price = $real_unit_price / $currency_rate;
							$unit_price = $unit_price / $currency_rate;
							$pr_discount = $pr_discount / $currency_rate;
							if ($dpos !== false) {
								 $item_discount = $item_discount;
							}else{
								 $item_discount = $pr_discount;
							}
						}
					}

					
					if($product_details = $item_type == 'service_rental'){
						$description .= $item_name.",";
						$abc .= $item_service_type;
					}

                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->cus->formatDecimalRaw($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxValidationByID($pr_tax);
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
					$unit = $this->site->getProductUnit($item_id,$item_unit);
					$service_types = $this->site->getServiceTypesByID($electricity);
					$raw_materials = array();
					$combo_products = json_decode($_POST['product_combo'][$r]);
					if($product_details->type=='combo' && $combo_products){
						$price_combo = 0;
						$qty_combo = count($combo_products);
						$dicount = 0;
						foreach($combo_products as $combo_product){
							$price_combo += $combo_product->price * $combo_product->qty;
						}
						if($this->cus->formatDecimal($price_combo) <> $this->cus->formatDecimal($item_net_price)){
							$dicount = (($price_combo - $item_net_price) * 100) / $price_combo;
						}
						$product_combo_cost = 0;
						foreach($combo_products as $combo_product){
							$combo_id = $combo_product->id;
							$combo_code = $combo_product->code;
							$combo_name = $combo_product->name;
							$combo_qty = $combo_product->qty;
							$combo_price = $combo_product->price;
							
							if($dicount > 0){
								$combo_price = $combo_price - (($combo_price * $dicount) / 100);
							}else if($dicount < 0){
								$combo_price = $combo_price + (($combo_price * abs($dicount)) / 100);
							}
							
							if($price_combo==0 && $item_net_price > 0){
								$combo_price = $item_net_price / $qty_combo;
							}
							
							
							$combo_detail = $this->site->getProductByID($combo_id);
							if($combo_detail){
								$combo_unit = $this->site->getProductUnit($combo_id, $combo_detail->unit);
								if($this->Settings->accounting_method == '0'){
									$costs = $this->site->getFifoCost($combo_id,($item_quantity * $combo_qty),$stockmoves);
								}else if($this->Settings->accounting_method == '1'){
									$costs = $this->site->getLifoCost($combo_id,($item_quantity * $combo_qty),$stockmoves);
								}else if($this->Settings->accounting_method == '3'){
									$costs = $this->site->getProductMethod($combo_id($item_quantity * $combo_qty),$stockmoves);
								}

								if($costs){
									$productAcc = $this->site->getProductAccByProductId($combo_id);
									$item_cost_qty  = 0;
									$item_cost_total = 0;
									$item_costs = '';
									foreach($costs as $cost_item){
										$item_cost_qty += $cost_item['quantity'];
										$item_cost_total += $cost_item['cost'] * $cost_item['quantity'];

										$stockmoves[] = array(
											'transaction' => 'Sale',
											'product_id' => $combo_detail->id,
											'product_type'    => $combo_detail->type,
											'product_code' => $combo_detail->code,
											'quantity' => $cost_item['quantity'] * (-1),
											'expiry' => $expired_data,
											'unit_quantity' => $combo_unit->unit_qty,
											'unit_code' => $combo_unit->code,
											'unit_id' => $combo_detail->unit,
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $cost_item['cost'],
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);
										//========accounting=========//
											if($this->Settings->accounting == 1 &&  $sale_status=='completed'){
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_id' => $id,
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,

												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_id' => $id,
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
											}
										//============end accounting=======//
										$item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
									}
									
									$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_id' => $id,
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->sale_acc,
												'amount' => -($combo_price * $combo_qty),
												'narrative' => 'Sale',
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
									
									$product_combo_cost += ($item_cost_total / $item_cost_qty);
								}else{
									$product_combo_cost += ($combo_qty * $combo_detail->cost);
									$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $combo_detail->id,
												'product_type'    => $combo_detail->type,
												'product_code' => $combo_detail->code,
												'quantity' => ($item_quantity * $combo_qty) * -1,
												'unit_quantity' => $combo_unit->unit_qty,
												'expiry' => $expired_data,
												'unit_code' => $combo_unit->code,
												'unit_id' => $combo_detail->unit,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $combo_detail->cost,
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
									//=======accounting=========//
										$productAcc = $this->site->getProductAccByProductId($combo_detail->id);
										if($this->Settings->accounting == 1 &&  $sale_status=='completed'){
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_id' => $id,
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->stock_acc,
												'amount' => -($combo_detail->cost * ($item_quantity * $combo_qty)),
												'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.($item_quantity * $combo_qty).'#'.'Cost: '.$combo_detail->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_id' => $id,
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->cost_acc,
												'amount' => ($combo_detail->cost * ($item_quantity * $combo_qty)),
												'narrative' => 'Product Code: '.$combo_detail->code.'#'.'Qty: '.($item_quantity * $combo_qty).'#'.'Cost: '.$combo_detail->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											

											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_id' => $id,
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->sale_acc,
												'amount' => -($combo_price * $combo_qty),
												'narrative' => 'Sale',
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											
										}
									//============end accounting=======//
								}
								$raw_materials[] = array(
									"product_id" => $combo_detail->id,
									"quantity" => ($item_quantity * $combo_qty)
								);
							}
							
						}
						$cost  = $product_combo_cost;	
					}else if($product_details->type=='bom'){
						$bom_type = $_POST['bom_type'][$r];
						$product_boms = $this->sales_model->getBomProductByStandProduct($item_id,$bom_type);
						if($product_boms){
							$product_bom_cost = 0;
							foreach($product_boms as $product_bom){
								if($this->Settings->accounting_method == '0'){
									$costs = $this->site->getFifoCost($product_bom->product_id,($item_quantity * $product_bom->quantity),$stockmoves,'Sale',$id);
								}else if($this->Settings->accounting_method == '1'){
									$costs = $this->site->getLifoCost($product_bom->product_id,($item_quantity * $product_bom->quantity),$stockmoves,'Sale',$id);
								}else if($this->Settings->accounting_method == '3'){
									$costs = $this->site->getProductMethod($product_bom->product_id,($item_quantity * $product_bom->quantity),$stockmoves,'Sale',$id);
								}
								if($costs){
									$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
									$item_cost_qty  = 0;
									$item_cost_total = 0;
									$item_costs = '';
									foreach($costs as $cost_item){
										$item_cost_qty += $cost_item['quantity'];
										$item_cost_total += $cost_item['cost'] * $cost_item['quantity'];

										$stockmoves[] = array(
											'transaction' => 'Sale',
											'product_id' => $product_bom->product_id,
											'product_type'    => $product_bom->product_type,
											'product_code' => $product_bom->product_code,
											'quantity' => $cost_item['quantity'] * (-1),
											'unit_quantity' => $product_bom->unit_qty,
											'unit_code' => $product_bom->code,
											'expiry' => $expired_data,
											'unit_id' => $product_bom->unit_id,
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $cost_item['cost'],
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);
										//========accounting=========//
											if($this->Settings->accounting == 1 && $product_bom->product_type && $sale_status=='completed'){
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_id' => $id,
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),

												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_id' => $id,
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
												);
											}
										//============end accounting=======//
										$item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
									}
									$product_bom_cost += ($item_cost_total / $item_cost_qty);
								}else{
									$product_bom_cost += ($product_bom->quantity * $product_bom->cost);
									$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $product_bom->product_id,
												'product_type'    => $product_bom->product_type,
												'product_code' => $product_bom->product_code,
												'quantity' => ($item_quantity * $product_bom->quantity) * -1,
												'unit_quantity' => $product_bom->unit_qty,
												'unit_code' => $product_bom->code,
												'expiry' => $expired_data,
												'unit_id' => $product_bom->unit_id,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $product_bom->cost,
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
									//=======accounting=========//
										$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
										if($this->Settings->accounting == 1 && $product_bom->product_type != 'manual' && $sale_status=='completed'){
											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_id' => $id,
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->stock_acc,
												'amount' => -($product_bom->cost * ($item_quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.($item_quantity * $product_bom->quantity).'#'.'Cost: '.$product_bom->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);

											$accTrans[] = array(
												'transaction' => 'Sale',
												'transaction_id' => $id,
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->cost_acc,
												'amount' => ($product_bom->cost * ($item_quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.($item_quantity * $product_bom->quantity).'#'.'Cost: '.$product_bom->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
										}
									//============end accounting=======//
								}
								$raw_materials[] = array(
									"product_id" => $product_bom->product_id,
									"quantity" => ($item_quantity * $product_bom->quantity)
								);
							}
							$cost  = $product_bom_cost;
						}else{
							$error = lang('please_check_product').' '.$item_code;
							$this->session->set_flashdata('error', $error);
							redirect($_SERVER["HTTP_REFERER"]);
						}
					}else{

						if($this->Settings->accounting_method == '0'){
							$costs = $this->site->getFifoCost($item_id,$item_quantity,$stockmoves,'Sale',$id);
						}else if($this->Settings->accounting_method == '1'){
							$costs = $this->site->getLifoCost($item_id,$item_quantity,$stockmoves,'Sale',$id);;
						}else if($this->Settings->accounting_method == '3'){
							$costs = $this->site->getProductMethod($item_id,$item_quantity,$stockmoves,'Sale',$id);;
						}


						if($costs && $item_serial=='' && $item_quantity > 0){
							$productAcc = $this->site->getProductAccByProductId($item_id);
							$item_cost_qty  = 0;
							$item_cost_total = 0;
							$item_costs = '';
							foreach($costs as $cost_item){
								$item_cost_qty += $cost_item['quantity'];
								$item_cost_total += $cost_item['cost'] * $cost_item['quantity'];

								$stockmoves[] = array(
									'transaction' => 'Sale',
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'expiry' => $expired_data,
									'quantity' => $cost_item['quantity'] * (-1),
									'unit_quantity' => $unit->unit_qty,
									'unit_code' => $unit->code,
									'unit_id' => $item_unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => $cost_item['cost'],
									'serial_no' => $item_serial,
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);
								//========accounting=========//
									if($this->Settings->accounting == 1 && $item_type != 'manual' && $sale_status=='completed'){
										$accTrans[] = array(
											'transaction' => 'Sale',
											'transaction_id' => $id,
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->stock_acc,
											'amount' => -($cost_item['cost'] * $cost_item['quantity']),
											'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
										$accTrans[] = array(
											'transaction' => 'Sale',
											'transaction_id' => $id,
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->cost_acc,
											'amount' => ($cost_item['cost'] * $cost_item['quantity']),
											'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
									}
								//============end accounting=======//
								$item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
							}
							$cost = $item_cost_total / $item_cost_qty;
						}else{
							if($item_serial!=""){
								$item_serials = explode("#",$item_serial);
								if(count($item_serials) > 0){
									for($b = 0; $b<= count($item_serials); $b++){
										if($item_serials[$b]!=''){
											if($product_serial_detail = $this->sales_model->getProductSerial($item_serials[$b],$item_id,$warehouse_id)){
												$product_details->cost = $product_serial_detail->cost;
											}
											$stockmoves[] = array(
												'transaction' => 'Sale',
												'product_id' => $item_id,
												'product_code' => $item_code,
												'product_type' => $item_type,
												'option_id' => $item_option,
												'quantity' => (-1),
												'unit_quantity' => $unit->unit_qty,
												'unit_code' => $unit->code,
												'expiry' => $expired_data,
												'unit_id' => $item_unit,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $product_details->cost,
												'serial_no' => $item_serials[$b],
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
										}
									}
								}else{
									if($product_serial_detail = $this->sales_model->getProductSerial($item_serial,$item_id,$warehouse_id)){
										$product_details->cost = $product_serial_detail->cost;
									}
									$stockmoves[] = array(
										'transaction' => 'Sale',
										'product_id' => $item_id,
										'product_code' => $item_code,
										'product_type' => $item_type,
										'option_id' => $item_option,
										'quantity' => $item_quantity * (-1),
										'unit_quantity' => $unit->unit_qty,
										'unit_code' => $unit->code,
										'expiry' => $expired_data,
										'unit_id' => $item_unit,
										'warehouse_id' => $warehouse_id,
										'date' => $date,
										'real_unit_cost' => ($item_quantity < 0 ? (($item_net_price + $item_tax) / ($unit->unit_qty > 0 ? $unit->unit_qty : 1)) : $product_details->cost), 
										'serial_no' => $item_serial,
										'reference_no' => $reference,
										'user_id' => $this->session->userdata('user_id'),
									);
								}
								
							}else{
								$stockmoves[] = array(
									'transaction' => 'Sale',
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'quantity' => $item_quantity * (-1),
									'unit_quantity' => $unit->unit_qty,
									'unit_code' => $unit->code,
									'expiry' => $expired_data,
									'unit_id' => $item_unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => ($item_quantity < 0 ? (($item_net_price + $item_tax) / ($unit->unit_qty > 0 ? $unit->unit_qty : 1)) : $cost),
									'serial_no' => $item_serial,
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);
							}
							

							//========accounting=========//
								$productAcc = $this->site->getProductAccByProductId($item_id);
								if($this->Settings->accounting == 1 && $item_type != 'manual'  && $sale_status=='completed'){
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_id' => $id,
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->stock_acc,
										'amount' => ($cost * $item_quantity) * (-1),
										'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_id' => $id,
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->cost_acc,
										'amount' => ($cost * $item_quantity),
										'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
								}
							//============end accounting=======//
						}
					}

					//========accounting=========//
						if($this->Settings->accounting == 1){
							if($item_type == 'manual'){
								$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
								$accTrans[] = array(
									'transaction' => 'Sale',
									'transaction_id' => $id,
									'transaction_date' => $date,
									'reference' => $reference,
									'account' => $saleAcc->other_income_acc,
									'amount' => -($item_net_price * $item_unit_quantity),
									'narrative' => 'Sale',
									'description' => $note,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id,
								);
							}else{
								if($product_details->type!='combo'){
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_id' => $id,
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->sale_acc,
										'amount' => -(($item_net_price + $item_tax) * $item_unit_quantity),
										'narrative' => 'Sale',
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
								}
								/*if($pr_item_discount > 0){
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_id' => $id,
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->discount_acc,
										'amount' => $pr_item_discount,
										'narrative' => 'Sale Product Discount',
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
								}*/
							}

						}
					//============end accounting=======//
					$item_serials = ($item_serial != "" ? explode("#",$item_serial) : false);
					if(count($item_serials) > 0 && $item_serial!=""){
						for($b = 0; $b<= count($item_serials); $b++){
							if($item_serials[$b]!=''){
								if($product_serial_detail = $this->sales_model->getProductSerial($item_serials[$b],$item_id,$warehouse_id)){
									$product_details->cost = $product_serial_detail->cost;
								}
								$products[] = array(
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_name' => $item_name,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'net_unit_price' => $item_net_price,
									'unit_price' => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
									'cost' => $product_details->cost,
									'quantity' => 1,
									'product_unit_id' => $item_unit,
									'product_unit_code' => $unit ? $unit->code : NULL,
									'unit_quantity' => 1,
									'warehouse_id' => $warehouse_id,
									'item_tax' => $pr_item_tax,
									'tax_rate_id' => $pr_tax,
									'tax' => $tax,
									'discount' => $item_discount,
									'comment'         => $item_comment,
									'item_discount' => $pr_item_discount,
									'subtotal' => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
									'serial_no' => $item_serials[$b],
									'real_unit_price' => $real_unit_price,
									'parent_id' => $parent_id,
									'item_costs' => $item_costs,
									'raw_materials' =>json_encode($raw_materials),
									'combo_product' =>json_encode($combo_products),
									'expiry' => $expired_data,
									'bom_type' => $bom_type,
									'electricity' => $electricity,
									'old_number' => $old_number,
									'new_number' => $new_number,
									'currency_rate' => $currency_rate,
									'currency_code' => $currency_code,
									'consignment_item_id' => $consignment_item_id
								);

								
								if($this->config->item('consignments') && $consignment_id > 0 && $consignment_item_id > 0){
									$consignment_item = $this->sales_model->getConsignmentItemByID($consignment_item_id,$item_id,$expired_data,$item_serials[$b]);
									$old_c_sale_qty = $this->sales_model->getSaleItemByConsigmentID($consignment_item_id);
									if($old_c_sale_qty){
										$consignment_item->quantity += $old_c_sale_qty->quantity;
									}
									$return_consign = $consignment_item->quantity;
									if($consignment_item->quantity >= 1){
										$return_consign = 1;
									}
									if($return_consign > 0){
										$consign_unit = $this->site->getProductUnit($item_id, $product_details->unit);
										$stockmoves[] = array(
											'transaction_id' => $id,
											'transaction' => 'Sale',
											'product_id' => $item_id,
											'product_code' => $item_code,
											'product_type' => $item_type,
											'option_id' => $item_option,
											'quantity' => $return_consign,
											'unit_quantity' => $consign_unit->unit_qty,
											'unit_code' => $consign_unit->code,
											'expiry' => $expired_data,
											'unit_id' => $product_details->unit,
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $product_details->cost,
											'serial_no' => $item_serials[$b],
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);
										if($this->Settings->accounting == 1 && $item_type != 'manual'){
											$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
											$accTrans[] = array(
												'transaction_id' => $id,
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $productAcc->stock_acc,
												'amount' => ($product_details->cost * $return_consign),
												'narrative' => 'Consignment Product Code: '.$item_code.'#'.'Qty: '.$return_consign.'#'.'Cost: '.$product_details->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											$accTrans[] = array(
												'transaction_id' => $id,
												'transaction' => 'Sale',
												'transaction_date' => $date,
												'reference' => $reference,
												'account' => $saleAcc->consignment_acc,
												'amount' => -($product_details->cost * $return_consign),
												'narrative' => 'Consignment Product Code: '.$item_code.'#'.'Qty: '.$return_consign.'#'.'Cost: '.$product_details->cost,
												'description' => $note,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
										}
									}
								}
								
							}
						}
					} else {
						$products[] = array(
							'product_id' => $item_id,
							'product_code' => $item_code,
							'product_name' => $item_name,
							'product_type' => $item_type,
							'service_types'   => $item_service_types,
							'option_id' => $item_option,
							'net_unit_price' => $item_net_price,
							'unit_price' => $this->cus->formatDecimalRaw($item_net_price + $item_tax),
							'cost' => $cost,
							'quantity' => $item_quantity,
							'product_unit_id' => $item_unit,
							'product_unit_code' => $unit ? $unit->code : NULL,
							'unit_quantity' => $item_unit_quantity,
							'warehouse_id' => $warehouse_id,
							'item_tax' => $pr_item_tax,
							'tax_rate_id' => $pr_tax,
							'tax' => $tax,
							'discount' => $item_discount,
							'comment' => $item_comment,
							'item_note' => $item_note,
							'item_discount' => $pr_item_discount,
							'subtotal' => $this->cus->formatDecimalRaw($subtotal),
							'serial_no' => $item_serial,
							'real_unit_price' => $real_unit_price,
							'parent_id' => $parent_id,
							'item_costs' => $item_costs,
							'raw_materials' =>json_encode($raw_materials),
							'combo_product' =>json_encode($combo_products),
							'expiry' => $expired_data,
							'bom_type' => $bom_type,
							'electricity' => $electricity,
							'old_number' => $old_number,
							'new_number' => $new_number,
							'currency_rate' => $currency_rate,
							'currency_code' => $currency_code

						);

						//print_r($products);die();

						if($this->config->item('consignments') && $consignment_id > 0 && $consignment_item_id > 0){
							$products[$r]['consignment_item_id'] = $consignment_item_id;
							$return_consign = 0;
							$consignment_item = $this->sales_model->getConsignmentItemByID($consignment_item_id,$item_id,$expired_data,$item_serial);
							$old_c_sale_qty = $this->sales_model->getSaleItemByConsigmentID($consignment_item_id);
							if($old_c_sale_qty){
								$consignment_item->quantity += $old_c_sale_qty->quantity;
							}
							if($consignment_item->quantity >= $item_quantity){
								$return_consign = $item_quantity;
							}else if($item_quantity > 0){
								$return_consign = $consignment_item->quantity;
							}
							if($return_consign > 0){
								$consign_unit = $this->site->getProductUnit($item_id, $product_details->unit);
								$stockmoves[] = array(
									'transaction_id' => $id,
									'transaction' => 'Sale',
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'quantity' => $return_consign,
									'unit_quantity' => $consign_unit->unit_qty,
									'unit_code' => $consign_unit->code,
									'expiry' => $expired_data,
									'unit_id' => $product_details->unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => $cost,
									'serial_no' => $item_serial,
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);
								if($this->Settings->accounting == 1 && $item_type != 'manual'){
									$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
									$accTrans[] = array(
										'transaction_id' => $id,
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->stock_acc,
										'amount' => ($cost * $return_consign),
										'narrative' => 'Consignment Product Code: '.$item_code.'#'.'Qty: '.$return_consign.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
									$accTrans[] = array(
										'transaction_id' => $id,
										'transaction' => 'Sale',
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $saleAcc->consignment_acc,
										'amount' => -($cost * $return_consign),
										'narrative' => 'Consignment Product Code: '.$item_code.'#'.'Qty: '.$return_consign.'#'.'Cost: '.$cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
								}
							}
						}
						
						
					}
					
					if($this->Settings->foc == 1 && $_POST['foc'][$r] > 0){
						$foc_cost = 0;
						$foc = $_POST['foc'][$r];
						$products[$r]['foc'] = $foc;
						if($this->Settings->accounting_method == '0'){
							$focCosts = $this->site->getFifoCost($item_id,$foc,$stockmoves,'Sale',$id);
						}else if($this->Settings->accounting_method == '1'){
							$focCosts = $this->site->getLifoCost($item_id,$foc,$stockmoves,'Sale',$id);
						}else if($this->Settings->accounting_method == '3'){
							$focCosts = $this->site->getProductMethod($item_id,$foc,$stockmoves,'Sale',$id);
						}
						
						$focUnit = $this->site->getProductUnit($item_id,$product_details->unit);
						if($focCosts){
							$productAcc = $this->site->getProductAccByProductId($item_id);
							$item_cost_total = 0;
							foreach($focCosts as $focCost){
								$item_cost_total += $focCost['cost'] * $focCost['quantity'];
								$stockmoves[] = array(
									'transaction' => 'Sale',
									'transaction_id' => $id,
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'quantity' => $focCost['quantity'] * (-1),
									'unit_quantity' => $focUnit->unit_qty,
									'unit_code' => $focUnit->code,
									'unit_id' => $product_details->unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'expiry' => $expired_data,
									'real_unit_cost' => $focCost['cost'],
									'serial_no' => '',
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);

								//========accounting=========//
									if($this->Settings->accounting == 1){
										$accTrans[] = array(
											'transaction' => 'Sale',
											'transaction_id' => $id,
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->stock_acc,
											'amount' => -($focCost['cost'] * $focCost['quantity']),
											'narrative' => 'FOC Product Code: '.$item_code.'#'.'Qty: '.$focCost['quantity'].'#'.'Cost: '.$focCost['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
										$accTrans[] = array(
											'transaction' => 'Sale',
											'transaction_id' => $id,
											'transaction_date' => $date,
											'reference' => $reference,
											'account' => $productAcc->cost_acc,
											'amount' => ($focCost['cost'] * $focCost['quantity']),
											'narrative' => 'FOC Product Code: '.$item_code.'#'.'Qty: '.$focCost['quantity'].'#'.'Cost: '.$focCost['cost'],
											'description' => $note,
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $customer_id,
										);
									}
								//============end accounting=======//

							}
							$foc_cost += $item_cost_total;

						}else{
							$foc_cost = ($foc * $product_details->cost);
							$stockmoves[] = array(
									'transaction' => 'Sale',
									'transaction_id' => $id,
									'product_id' => $item_id,
									'product_code' => $item_code,
									'product_type' => $item_type,
									'option_id' => $item_option,
									'quantity' => $foc * (-1),
									'unit_quantity' => $focUnit->unit_qty,
									'unit_code' => $focUnit->code,
									'unit_id' => $product_details->unit,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'expiry' => $expired_data,
									'real_unit_cost' => $product_details->cost,
									'serial_no' => '',
									'reference_no' => $reference,
									'user_id' => $this->session->userdata('user_id'),
								);

							//========accounting=========//
								if($this->Settings->accounting == 1){
									$productAcc = $this->site->getProductAccByProductId($item_id);
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_id' => $id,
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->stock_acc,
										'amount' => -($product_details->cost * $foc),
										'narrative' => 'FOC Product Code: '.$item_code.'#'.'Qty: '.$foc.'#'.'Cost: '.$product_details->cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
									$accTrans[] = array(
										'transaction' => 'Sale',
										'transaction_id' => $id,
										'transaction_date' => $date,
										'reference' => $reference,
										'account' => $productAcc->cost_acc,
										'amount' => ($product_details->cost * $foc),
										'narrative' => 'FOC Product Code: '.$item_code.'#'.'Qty: '.$foc.'#'.'Cost: '.$product_details->cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id,
									);
								}
							//============end accounting=======//
						}
						$products[$r]['foc_cost'] = $foc_cost;
					}
					if($this->config->item('saleman_commission') && $this->Settings->product_commission == 1){
						$product_salesmans = $_POST['product_salesmans'][$r];
						$salesman = $this->site->getUserByID($product_salesmans);
						$products[$r]['salesman_id'] = $salesman->id;
						$products[$r]['salesman'] = $salesman->last_name.' '.$salesman->first_name;
						$p_commission = $this->sales_model->getProductCommission($salesman->id,$item_id);
						if($p_commission && $p_commission->commission != '' && $p_commission->commission != 0){
							$products[$r]['salesman_commission'] = $p_commission->commission;
						}else{
							$products[$r]['salesman_commission'] = $salesman->saleman_commission;
						}
					}
					
					if($this->Settings->product_additional == 1){
						$products[$r]['pro_additionals'] = $_POST['product_additional'][$r];
						if($_POST['product_additional'][$r] != ''){
							$extraProducts = $this->sales_model->getProductAdditionalByID($_POST['product_additional'][$r], $item_unit_quantity);
						}else{
							$extraProducts = false;
						}
						if($extraProducts){
							$products[$r]['extract_product'] = json_encode($extraProducts);
							$extractCost = 0;
							foreach($extraProducts as $extraProduct){
								$extra_details = $this->site->getProductByID($extraProduct['for_product_id']);
								if($extra_details){
									$extraUnit = $this->site->getProductUnit($extra_details->id,$extraProduct['for_unit_id']);
									$extractProductID = $extra_details->id;
									$extractQuantity = $extraUnit->unit_qty * $extraProduct['for_quantity'];
									if($this->Settings->accounting_method == '0'){
										$extraCosts = $this->site->getFifoCost($extractProductID,$extractQuantity,$stockmoves,'Sale',$id);
									}else if($this->Settings->accounting_method == '1'){
										$extraCosts = $this->site->getLifoCost($extractProductID,$extractQuantity,$stockmoves,'Sale',$id);
									}else if($this->Settings->accounting_method == '3'){
										$extraCosts = $this->site->getProductMethod($extractProductID,$extractQuantity,$stockmoves,'Sale',$id);
									}
									if($extraCosts){
										$productAcc = $this->site->getProductAccByProductId($extractProductID);
										$item_cost_total = 0;
										$item_costs = '';
										foreach($extraCosts as $extraCost){
											$item_cost_total += $extraCost['cost'] * $extraCost['quantity'];

											$stockmoves[] = array(
												'transaction' => 'Sale',
												'transaction_id' => $id,
												'product_id' => $extractProductID,
												'product_code' => $extra_details->code,
												'product_type' => $extra_details->type,
												'option_id' => 0,
												'quantity' => $extraCost['quantity'] * (-1),
												'unit_quantity' => $extraUnit->unit_qty,
												'unit_code' => $extraUnit->code,
												'unit_id' => $extraProduct['for_unit_id'],
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $extraCost['cost'],
												'serial_no' => '',
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
											//========accounting=========//
												if($this->Settings->accounting == 1){
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_id' => $id,
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->stock_acc,
														'amount' => -($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_id' => $id,
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->cost_acc,
														'amount' => ($extraCost['cost'] * $extraCost['quantity']),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
												}
											//============end accounting=======//

										}
										$extractCost += $item_cost_total;

									}else{
										$extractCost += ($extractQuantity * $extra_details->cost);
										$stockmoves[] = array(
											'transaction' => 'Sale',
											'transaction_id' => $id,
											'product_id' => $extractProductID,
											'product_code' => $extra_details->code,
											'product_type' => $extra_details->type,
											'option_id' => 0,
											'quantity' => $extractQuantity * (-1),
											'unit_quantity' => $extraUnit->unit_qty,
											'unit_code' => $extraUnit->code,
											'unit_id' => $extraProduct['for_unit_id'],
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $extra_details->cost,
											'serial_no' => '',
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);

										//========accounting=========//
											if($this->Settings->accounting == 1){
												$productAcc = $this->site->getProductAccByProductId($extractProductID);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_id' => $id,
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->stock_acc,
													'amount' => -($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
												$accTrans[] = array(
													'transaction' => 'Sale',
													'transaction_id' => $id,
													'transaction_date' => $date,
													'reference' => $reference,
													'account' => $productAcc->cost_acc,
													'amount' => ($extra_details->cost * $extractQuantity),
													'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
													'description' => $note,
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'user_id' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
											}
										//============end accounting=======//
									}
								}
							}
							$products[$r]['extract_cost'] = $extractCost;
						}
					}
					
                    

					if($this->Settings->qty_operation == 1){
						$width = $_POST['swidth'][$r];
						$height = $_POST['sheight'][$r];
						$square = $_POST['square'][$r];
						$square_qty = $_POST['square_qty'][$r];
						$products[$r]['width'] = $width;
						$products[$r]['height'] = $height;
						$products[$r]['square'] = $square;
						$products[$r]['square_qty'] = $square_qty;
						if($this->Settings->product_formulation == 1){
							$products[$r]['pro_formulations'] = $_POST['product_formulation'][$r];
							if($_POST['product_formulation'][$r] != ''){
								$extraProducts = $this->cus->productFormulation($_POST['product_formulation'][$r],$width,$height,$square,$square_qty);
							}else{
								$extraProducts = false;
							}
							
							if($extraProducts){
								$products[$r]['extract_product'] = json_encode($extraProducts);
								$extractCost = 0;
								foreach($extraProducts as $extraProduct){
									$extra_details = $this->site->getProductByID($extraProduct['for_product_id']);
									if($extra_details){
										$extraUnit = $this->site->getProductUnit($extra_details->id,$extraProduct['for_unit_id']);
										$extractProductID = $extra_details->id;
										$extractQuantity = $extraUnit->unit_qty * $extraProduct['for_quantity'];

										if($this->Settings->accounting_method == '0'){
											$extraCosts = $this->site->getFifoCost($extractProductID,$extractQuantity,$stockmoves,'Sale',$id);
										}else if($this->Settings->accounting_method == '1'){
											$extraCosts = $this->site->getLifoCost($extractProductID,$extractQuantity,$stockmoves,'Sale',$id);
										}else if($this->Settings->accounting_method == '3'){
											$extraCosts = $this->site->getProductMethod($extractProductID,$extractQuantity,$stockmoves,'Sale',$id);
										}

										if($extraCosts){
											$productAcc = $this->site->getProductAccByProductId($extractProductID);
											$item_cost_total = 0;
											$item_costs = '';
											foreach($extraCosts as $extraCost){
												$item_cost_total += $extraCost['cost'] * $extraCost['quantity'];

												$stockmoves[] = array(
													'transaction' => 'Sale',
													'transaction_id' => $id,
													'product_id' => $extractProductID,
													'product_code' => $extra_details->code,
													'product_type' => $extra_details->type,
													'option_id' => 0,
													'quantity' => $extraCost['quantity'] * (-1),
													'unit_quantity' => $extraUnit->unit_qty,
													'unit_code' => $extraUnit->code,
													'unit_id' => $extraProduct['for_unit_id'],
													'warehouse_id' => $warehouse_id,
													'date' => $date,
													'real_unit_cost' => $extraCost['cost'],
													'serial_no' => '',
													'reference_no' => $reference,
													'user_id' => $this->session->userdata('user_id'),
												);
												//========accounting=========//
													if($this->Settings->accounting == 1){
														$accTrans[] = array(
															'transaction' => 'Sale',
															'transaction_id' => $id,
															'transaction_date' => $date,
															'reference' => $reference,
															'account' => $productAcc->stock_acc,
															'amount' => -($extraCost['cost'] * $extraCost['quantity']),
															'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
															'description' => $note,
															'biller_id' => $biller_id,
															'project_id' => $project_id,
															'user_id' => $this->session->userdata('user_id'),
															'customer_id' => $customer_id,
														);
														$accTrans[] = array(
															'transaction' => 'Sale',
															'transaction_id' => $id,
															'transaction_date' => $date,
															'reference' => $reference,
															'account' => $productAcc->cost_acc,
															'amount' => ($extraCost['cost'] * $extraCost['quantity']),
															'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
															'description' => $note,
															'biller_id' => $biller_id,
															'project_id' => $project_id,
															'user_id' => $this->session->userdata('user_id'),
															'customer_id' => $customer_id,
														);
													}
												//============end accounting=======//

											}
											$extractCost += $item_cost_total;

										}else{
											$extractCost += ($extractQuantity * $extra_details->cost);
											$stockmoves[] = array(
												'transaction' => 'Sale',
												'transaction_id' => $id,
												'product_id' => $extractProductID,
												'product_code' => $extra_details->code,
												'product_type' => $extra_details->type,
												'option_id' => 0,
												'quantity' => $extractQuantity * (-1),
												'unit_quantity' => $extraUnit->unit_qty,
												'unit_code' => $extraUnit->code,
												'unit_id' => $extraProduct['for_unit_id'],
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $extra_details->cost,
												'serial_no' => '',
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);

											//========accounting=========//
												if($this->Settings->accounting == 1){
													$productAcc = $this->site->getProductAccByProductId($extractProductID);
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_id' => $id,
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->stock_acc,
														'amount' => -($extra_details->cost * $extractQuantity),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
													$accTrans[] = array(
														'transaction' => 'Sale',
														'transaction_id' => $id,
														'transaction_date' => $date,
														'reference' => $reference,
														'account' => $productAcc->cost_acc,
														'amount' => ($extra_details->cost * $extractQuantity),
														'narrative' => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
														'description' => $note,
														'biller_id' => $biller_id,
														'project_id' => $project_id,
														'user_id' => $this->session->userdata('user_id'),
														'customer_id' => $customer_id,
													);
												}
											//============end accounting=======//
										}
									}
								}
								$products[$r]['extract_cost'] = $extractCost;
							}
						}
					}
					if($this->config->item('fuel') && $_POST['fuel_customer_date'][$r]){
						$products[$r]['fuel_customer_date'] = $_POST['fuel_customer_date'][$r];
						$products[$r]['fuel_customer_reference'] = $_POST['fuel_customer_reference'][$r];
					}
                    $total += $this->cus->formatDecimalRaw(($item_net_price * $item_unit_quantity), 4);
					if($this->Settings->product_serial == 1 && $item_serial==''){
						$this->site->deleteStockmoves('Sale',$id);
						$qty_warehouse = $this->sales_model->getProductQuantity($item_id,$warehouse_id);
						$qty_serial = $this->sales_model->getProductSerialQuantity($item_id,$warehouse_id);
						$avalible_qty = $qty_warehouse['quantity'] - $qty_serial['serial_qty'];
						if($avalible_qty < $item_quantity && $qty_serial['serial_qty'] > 0){
							$this->form_validation->set_rules('product', lang("serial"), 'required');
						}
					}
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
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
            $total_discount = $this->cus->formatDecimalRaw($order_discount + $product_discount);

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->cus->formatDecimalRaw($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->cus->formatDecimalRaw(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }
			
            $total_tax = $this->cus->formatDecimalRaw(($product_tax + $order_tax), 4);
            $grand_total = $this->cus->formatDecimalRaw(($total + $total_tax + $this->cus->formatDecimalRaw($shipping) - $order_discount), 4);
            
			$currencies =  $this->site->getAllCurrencies();
			if(!empty($currencies)){
				foreach($currencies as $currency_row){
					$current_amount = 0;
					$currency[] = array(
								"amount" => $current_amount,
								"currency" => $currency_row->code,
								"rate" => ($this->input->post("exchange_rate_".$currency_row->code) ? $this->input->post("exchange_rate_".$currency_row->code) : $currency_row->rate),
							);
				}
			}
            //=======accounting=========//
			if($this->Settings->accounting == 1){
				$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
				$accTrans[] = array(
					'transaction_id' => $id,
					'transaction' => 'Sale',
					'transaction_date' => $date,
					'reference' => $reference,
					'account' => ($this->Settings->default_receivable_account==0 ? $saleAcc->ar_acc : $this->input->post('receivable_account')),
					'amount' => $grand_total,
					'narrative' => 'Sale',
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'user_id' => $this->session->userdata('user_id'),
					'customer_id' => $customer_id,
				);

				if($order_discount != 0){
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->sale_discount_acc,
						'amount' => $order_discount,
						'narrative' => 'Order Discount',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				
				if($order_tax != 0){
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->vat_output,
						'amount' => -$order_tax,
						'narrative' => 'Order Tax',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				
				if($shipping != 0){
					$accTrans[] = array(
							'transaction_id' => $id,
							'transaction' => 'Sale',
							'transaction_date' => $date,
							'reference' => $reference,
							'account' => $saleAcc->shipping_acc,
							'amount' => -$shipping,
							'narrative' => 'Shipping',
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $customer_id,
						);
				}
			}

			$agency_commission = array();
			$agency_limit_percent = array();
			$agency_value_commission = array();
			if(isset($agency_id)){
				foreach($agency_id as $agency){
					$agency_details = $this->sales_model->getAgencyByID($agency);
					$agency_commission[] = $agency_details->agency_commission;
					$agency_limit_percent[] = $agency_details->agency_limit_percent;
					$agency_value_commission[] = $agency_details->agency_value_commission;
				}
			}

			//============end accounting=======//

			$rental = $this->sales_model->getRentalByID($quote_id);
			$room = $this->sales_model->getRoomByID($rental->room_id);
			$items = $this->sales_model->getAllRentalItems($quote_id);
			$rental_service_id = $this->site->getRentalsByID($this->input->post('rental_service_id'));

            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                "description" => rtrim($description,","),
                'total' => $total,
                'rental_service_id' => $this->input->post('rental_service_id'),
                'rental_name' => $rental_service_id->room_name,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->cus->formatDecimalRaw($shipping),
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'sale_status' => $sale_status,
                'payment_status' => $payment_status,
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
				'agency_id' => json_encode($agency_id),
				'agency_commission' => json_encode($agency_commission),
				'agency_limit_percent' => json_encode($agency_limit_percent),
				'agency_value_commission' => json_encode($agency_value_commission),
				'currencies' => json_encode($currency),
				'saleman_id' => $saleman->id,
				'saleman' => $saleman->last_name.' '.$saleman->first_name,
				'saleman_commission' => $saleman_commission,
				'stock_deduction' => $stock_deduction,
				'consignment_id' => $consignment_id,
				'ar_account' => ($this->Settings->default_receivable_account==0 ? $saleAcc->ar_acc : $this->input->post('receivable_account'))
            );

           // print_r($data);die();


			if($sale_status!='completed'){
				$stockmoves = array();
			}
			if($this->Settings->project == 1){
				$data['project_id'] = $project_id;
			}
			if($this->Settings->car_operation == 1){
				$data['vehicle_model'] = $vehicle_model;
                $data['vehicle_kilometers'] = $vehicle_kilometers;
                $data['vehicle_vin_no'] = $vehicle_vin_no;
				$data['vehicle_plate'] = $vehicle_plate;
				$data['job_number'] = $job_number;
				$data['mechanic'] = $mechanic;
			}

			$rental_id = $this->input->post('rental_id');
			$rental_deposit = $this->input->post('rental_deposit');
			$rental_status = $this->input->post('rental_status');
			if($this->config->item("room_rent") && (isset($inv->rental_id) && $inv->rental_id > 0)){
				$data['rental_id'] = $rental_id;
				$data['from_date'] = $this->cus->fld(trim($this->input->post("from_date")));
				$data['to_date'] = $this->cus->fld(trim($this->input->post("to_date")));
				
			}


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

        if ($this->form_validation->run() == true && $this->sales_model->updateSale($id, $data, $payment, $products, $biller_id, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang("sale_updated")." ".$reference);
			if($this->config->item("send_telegram")){
				$this->telegrambot->sendmsg("Sale ".$data["reference_no"]." (".$data["customer"].") (".$this->cus->formatMoney($data["grand_total"]).") has been edited by ".$this->session->userdata("username"));
			}
			redirect($inv->pos ? 'pos/sales' : 'sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-'.$this->Settings->disable_editing.' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang("sale_x_edited_older_than_x_days"), $this->Settings->disable_editing));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
            if(in_array('bom',$this->config->item('product_types'))) {
	        	$enable_bom = true;
	        }else{
	        	$enable_bom  = false;
			}
			if($this->Settings->product_additional == 1){
                $additional_products = $this->sales_model->getProductAdditionals();
            }else{
                $additional_products = false;
            }
			if($this->Settings->product_formulation == 1){
				$product_formulations = $this->sales_model->getProductFormulation();
			}else{
				$product_formulations = false;
			}
			
			if($this->config->item('saleman_commission') && $this->Settings->product_commission == 1){
				$salesmans = $this->site->getSalemans();
				$product_commission = true;
			}else{
				$salesmans = false;
				$product_commission = false;
			}
			
            $inv_items = $this->sales_model->getAllInvoiceItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {

                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no, $row->service_types);
                }
				
				$row->quantity = 0;
                $pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id, 'Sale' , $id);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
				$sale_order = $this->sales_model->getSaleOrderByApproval($row->id);
				$row->quantity -= $sale_order->quantity;
				if($this->Settings->product_expiry == '1'){
					$product_expiries = $this->site->getProductExpiredByProductID($row->id, $item->warehouse_id, 'Sale' , $id);
				}else{
					$product_expiries = false;
				}
				if($this->Settings->product_additional == 1 && $addtion_price = $this->sales_model->getTotalPriceAddProductByID($item->pro_additionals)){
					$item->unit_price = $item->unit_price - $addtion_price->addition_price;
				}else{
					$addtion_price = 0;
				}
				$row->fup = 1;
				$row->consignment_item_id = $item->consignment_item_id;
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->service_types = $item->service_types;
				$row->expired = $this->cus->hrsd($item->expiry);
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
				$row->base_unit_price = $row->price ? $row->price : $item->real_unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $this->cus->formatDecimalRaw($item->net_unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity));
				$row->cost = $item->cost;
				$row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity) + $this->cus->formatDecimalRaw($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate = $item->tax_rate_id;
                $row->serial = $item->serial_no;
                $row->option = $item->option_id;
				$row->parent_id = $item->parent_id;
				$row->swidth = $item->width;
				$row->sheight = $item->height;
				$row->square = $item->square;
				$row->square_qty = $item->square_qty;
				$row->product_formulation = $item->pro_formulations;
				$row->product_additional = $item->pro_additionals;
				$row->salesman_id = $item->salesman_id;
				$row->fuel_customer_date = $item->fuel_customer_date;
				$row->fuel_customer_reference = $item->fuel_customer_reference;
				$row->foc = $item->foc;
				$row->item_note = $item->item_note;
                $options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
				// Product Currency
				$currencies = false;
				if($this->config->item('product_currency')==true){
					$currencies = $this->site->getAllCurrencies();
					foreach($currencies as $currency){
						if($currency->code == $row->currency_code){
							$currency->rate = $row->currency_rate;
						}
					}
					$row->currency_rate = $item->currency_rate;
					$row->currency_code = $item->currency_code;
					$row->price = $row->price * ($item->currency_rate);
					$row->real_currency_rate = $row->currency_rate;
					$row->real_unit_price = $row->price;
					$row->unit_price = $row->unit_price * ($item->currency_rate);
					$row->base_unit_price = $row->base_unit_price * ($item->currency_rate);
					$dpos = strpos($row->discount, "%");
					if (!$dpos) { 
						$row->discount = strval($row->discount * ($row->currency_rate));
					}
				}
				$room_rent = false;
				if($this->config->item('room_rent') == true){
					$room_rent= true;
					$row->old_number = $item->old_number;
					$row->new_number = $item->new_number;
				}
				if($this->Settings->search_by_category==1){
					$category = $this->site->getCategoryByID($row->category_id);
				}else{
					$category = false;
				}
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
				$row->comment = isset($item->comment) ? $item->comment : '';

                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = json_decode($item->combo_product);
                }
				if($enable_bom) {
					$bom_typies = $this->sales_model->getTypeBoms($row->id);
					$row->bom_type = $item->bom_type;
				}else{
					$bom_typies = false;
				}
				if($this->Settings->product_serial == 1){
					$product_serials = $this->sales_model->getProductSerialDetailsByProductId($row->id,$item->warehouse_id, $row->serial);
				}
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $item->id : $c;
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'product_serials'=>isset($product_serials)? $product_serials: "", 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options,
                    'product_expiries'=> $product_expiries, 'product_formulations'=> $product_formulations, 'additional_products'=> $additional_products,
                 	'enable_bom' => $enable_bom, 'bom_typies'=> $bom_typies, 'currencies' => $currencies, 'category'=>isset($category->name)? $category->name: "", 'room_rent' => $room_rent,
					'salesmans' => $salesmans, 'product_commission'=>$product_commission, 'service_types' =>$service_types);
				$c++;
            }
            if($this->Settings->accounting == 1 && $this->Settings->default_receivable_account != 0){
				$this->data['receivable_account'] = $this->site->getAccount('AS',$this->data['inv']->ar_account);
			}else{
				$this->data['receivable_account'] = false;
			}
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
			$this->data['salemans'] = $this->site->getSalemans();
			$this->data['rooms_checked_in'] = $this->site->getRoomCheckedIN();
			$this->data['agencies'] = $this->site->getAgencies();
			$this->data['projects'] = $this->site->getAllProjects();
			$this->data['user'] = $this->site->getUser($this->session->userdata("user_id"));
            $this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['rooms'] = $this->site->getAllTables();
			$this->data['currencies'] = $this->site->getCurrencies();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('edit_sale')));
			$meta = array('page_title' => lang('edit_sale'), 'bc' => $bc);
			$this->session->set_userdata('remove_slls', 1);
            $this->core_page('sales/edit', $meta, $this->data);
        }
    }

    public function return_sale($id = null)
    {
        $this->cus->checkPermissions('return_sales');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
		$return = $this->sales_model->getReturnBySaleId($id);
        $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');
        if ($this->form_validation->run() == true) {
			
			if (!$sale) {
				$this->session->set_flashdata('error', lang("sale_is_required"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
			
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re',$sale->biller_id);
            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
			$return_surcharge_interest = $this->input->post('return_surcharge_interest') ? $this->input->post('return_surcharge_interest') : 0;
			$shipping = $this->input->post('shipping') ? ($this->input->post('shipping') * (-1)) : 0;
			$note = $this->cus->clear_tags($this->input->post('note'));
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
			$product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
			if($this->Settings->accounting_method != '2'){
				$tmp_stockmoves = $this->sales_model->getReturnItemStockmoveBySale($id);
			}
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
				$return_stock = $_POST['return_stock'][$r];
				$cost = $_POST['cost'][$r];
                $sale_item_id = $_POST['sale_item_id'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = (0-$_POST['quantity'][$r]);
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = (0-$_POST['product_base_quantity'][$r]);
				$currency_code = isset($_POST['currency_code'][$r]) ? $_POST['currency_code'][$r] : '';
				$currency_rate = isset($_POST['currency_rate'][$r]) ? $_POST['currency_rate'][$r] : '';
				$expiry = $this->cus->fsd($_POST['expiry'][$r]);
				$foc = isset($_POST['foc'][$r]) ? $_POST['foc'][$r] : '';
				$salesman_id = $_POST['salesman_id'][$r];
				$salesman = $_POST['salesman'][$r];
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && ($item_quantity < 0)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->cus->formatDecimalRaw(((($this->cus->formatDecimalRaw($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->cus->formatDecimalRaw($discount, 4);
                        }
                    }

                    $unit_price = $this->cus->formatDecimalRaw(($unit_price - $pr_discount), 4);
					// Product Currency
					if($this->config->item('product_currency')==true){
						if($currency_code && $currency_rate){
							$real_unit_price = $real_unit_price / $currency_rate;
							$unit_price = $unit_price / $currency_rate;
							$pr_discount = $pr_discount / $currency_rate;
							if ($dpos !== false) {
								 $item_discount = $item_discount;
							}else{
								 $item_discount = $pr_discount;
							}
						}
					}
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->cus->formatDecimalRaw($pr_discount * $item_unit_quantity, 4);
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
                        $pr_item_tax = $this->cus->formatDecimalRaw(($item_tax * $item_unit_quantity), 4);

                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = $this->cus->formatDecimalRaw((($item_net_price * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getProductUnit($item_id,$item_unit);
					if($this->Settings->accounting_method == '0'){
						$costs = $this->sales_model->getFifoItems('Sale',$id,$item_id,$item_quantity,$tmp_stockmoves);
					}else if($this->Settings->accounting_method == '1'){
						$costs = $this->sales_model->getFifoItems('Sale',$id,$item_id,$item_quantity,$tmp_stockmoves);
					}else if($this->Settings->accounting_method == '3'){
						$costs = $this->sales_model->getProductMethod('Sale',$id,$item_id,$item_quantity,$tmp_stockmoves);
					}
					if($this->Settings->accounting == 1){
						$productAcc = $this->site->getProductAccByProductId($item_id);
					}
					if($costs && $item_serial=='' && $return_stock=='0'){
						$item_cost_qty  = 0;
						$item_cost_total = 0;
						$item_costs = '';
						foreach($costs as $cost_item){
							$item_cost_qty += $cost_item['quantity'];
							$item_cost_total += $cost_item['cost'] * $cost_item['quantity'];

							$stockmoves[] = array(
								'transaction' => 'Sale',
								'product_id' => $item_id,
								'product_code' => $item_code,
								'option_id' => $item_option,
								'quantity' => $cost_item['quantity'],
								'unit_quantity' => $unit->unit_qty,
								'unit_code' => $unit->code,
								'unit_id' => $item_unit,
								'warehouse_id' => $sale->warehouse_id,
								'date' => $date,
								'real_unit_cost' => $cost_item['cost'],
								'serial_no'=>$item_serial,
								'reference_no' => $reference,
								'user_id' => $this->session->userdata('user_id'),
								'expiry' => $expiry
							);
						//=======accounting=========//
							if($this->Settings->accounting == 1){
								$accTrans[] = array(
									'transaction' => 'SaleReturn',
									'transaction_date' => $date,
									'reference' => $reference,
									'account' => $productAcc->stock_acc,
									'amount' => ($cost_item['cost'] * $cost_item['quantity']),
									'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
									'description' => $note,
									'biller_id' => $sale->biller_id,
									'project_id' => $sale->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $sale->customer_id,
								);
								$accTrans[] = array(
									'transaction' => 'SaleReturn',
									'transaction_date' => $date,
									'reference' => $reference,
									'account' => $productAcc->cost_acc,
									'amount' => -($cost_item['cost'] * $cost_item['quantity']),
									'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
									'description' => $note,
									'biller_id' => $sale->biller_id,
									'project_id' => $sale->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $sale->customer_id,
								);
							}
						//============end accounting=======//
							$item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
						}
						$cost = $item_cost_total / $item_cost_qty;
						if($tmp_stockmoves){
							$tmp_stockmoves = array_merge($tmp_stockmoves,$stockmoves);
						}else{
							$tmp_stockmoves = $stockmoves;
						}
					}else if($return_stock=='0'){

						$stockmoves[] = array(
							'transaction' => 'Sale',
							'product_id' => $item_id,
							'product_code' => $item_code,
							'option_id' => $item_option,
							'quantity' => $item_quantity * (-1),
							'unit_quantity' => $unit->unit_qty,
							'unit_code' => $unit->code,
							'unit_id' => $item_unit,
							'warehouse_id' => $sale->warehouse_id,
							'date' => $date,
							'real_unit_cost' => $product_details->cost,
							'serial_no'=>$item_serial,
							'reference_no' => $reference,
							'user_id' => $this->session->userdata('user_id'),
							'expiry' => $expiry
						);
					

					//=======accounting=========//
						if($this->Settings->accounting == 1){
							$accTrans[] = array(
								'transaction' => 'SaleReturn',
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $productAcc->stock_acc,
								'amount' => ($product_details->cost * abs($item_quantity)),
								'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $sale->biller_id,
								'project_id' => $sale->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale->customer_id,
							);
							$accTrans[] = array(
								'transaction' => 'SaleReturn',
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $productAcc->cost_acc,
								'amount' => -($product_details->cost * abs($item_quantity)),
								'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $sale->biller_id,
								'project_id' => $sale->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale->customer_id,
							);
						}
					//============end accounting=======//
					}

					if($return_stock=='0' && $foc > 0){
						$focUnit = $this->site->getProductUnit($item_id,$product_details->unit);
						$stockmoves[] = array(
							'transaction' => 'Sale',
							'product_id' => $item_id,
							'product_code' => $item_code,
							'option_id' => $item_option,
							'quantity' => $foc,
							'unit_quantity' => $focUnit->unit_qty,
							'unit_code' => $focUnit->code,
							'unit_id' => $product_details->unit,
							'warehouse_id' => $sale->warehouse_id,
							'date' => $date,
							'real_unit_cost' => $product_details->cost,
							'serial_no'=> '',
							'reference_no' => $reference,
							'user_id' => $this->session->userdata('user_id'),
							'expiry' => ''
						);
						
						if($this->Settings->accounting == 1){
							$accTrans[] = array(
								'transaction' => 'SaleReturn',
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $productAcc->stock_acc,
								'amount' => ($product_details->cost * abs($foc)),
								'narrative' => 'FOC Product Code: '.$item_code.'#'.'Qty: '.$foc.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $sale->biller_id,
								'project_id' => $sale->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale->customer_id,
							);
							$accTrans[] = array(
								'transaction' => 'SaleReturn',
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $productAcc->cost_acc,
								'amount' => -($product_details->cost * abs($foc)),
								'narrative' => 'FOC Product Code: '.$item_code.'#'.'Qty: '.$foc.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $sale->biller_id,
								'project_id' => $sale->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale->customer_id,
							);
						}
					}

					if($this->Settings->accounting == 1){
						$accTrans[] = array(
								'transaction' => 'SaleReturn',
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $productAcc->sale_acc,
								'amount' => abs($item_net_price * $item_unit_quantity),
								'narrative' => 'Sale Return '.$sale->reference_no,
								'description' => $note,
								'biller_id' => $sale->biller_id,
								'project_id' => $sale->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale->customer_id,
							);
					}


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
                        'product_unit_code' => $unit ? $unit->code : NULL,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $sale->warehouse_id,
                        'item_tax' => $pr_item_tax,
						'cost' => $cost,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->cus->formatDecimalRaw($subtotal),
                        'serial_no' => $item_serial,
                        'real_unit_price' => $real_unit_price,
                        'sale_item_id' => $sale_item_id,
						'item_costs' => $item_costs,
						'return_stock' => $return_stock,
						'expiry' => $expiry,
						'currency_rate' => $currency_rate,
						'currency_code' => $currency_code,
						'salesman_id' => $salesman_id,
						'salesman' => $salesman,
						'foc' => ($foc * (-1)),
						'foc_cost' => ($foc * $product_details->cost * (-1))
                    );
					

					
					$si_return[] = array(
						'id' => $sale_item_id,
						'sale_id' => $id,
						'product_id' => $item_id,
						'option_id' => $item_option,
						'quantity' => (0-$item_quantity),
						'warehouse_id' => $sale->warehouse_id
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
                $order_discount_id = ($this->input->post('discount'))*-1;
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->cus->formatDecimalRaw(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->cus->formatDecimalRaw($order_discount_id, 4);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->cus->formatDecimalRaw($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->cus->formatDecimalRaw(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }
            $total_tax = $this->cus->formatDecimalRaw($product_tax + $order_tax, 4);
            $grand_total = $this->cus->formatDecimalRaw(($total + $shipping + $total_tax + $this->cus->formatDecimalRaw($return_surcharge) - $order_discount), 4);
            
			
			//=======accounting=========//
			if($this->Settings->accounting == 1){
				$saleAcc = $this->site->getAccountSettingByBiller($sale->biller_id);

				$accTrans[] = array(
					'transaction' => 'SaleReturn',
					'transaction_date' => $date,
					'reference' => $reference,
					'account' => (($this->Settings->default_receivable_account==0 || !$sale->ar_account)  ? $saleAcc->ar_acc : $sale->ar_account),
					'amount' => -(abs($grand_total)),
					'narrative' => 'Sale Return '.$sale->reference_no,
					'description' => $note,
					'biller_id' => $sale->biller_id,
					'project_id' => $sale->project_id,
					'user_id' => $this->session->userdata('user_id'),
					'customer_id' => $sale->customer_id,
				);

				if(abs($order_discount) != 0){
					$accTrans[] = array(
						'transaction' => 'SaleReturn',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->sale_discount_acc,
						'amount' => -abs($order_discount),
						'narrative' => 'Order Discount Return '.$sale->reference_no,
						'description' => $note,
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				}
				if(abs($order_tax) != 0){
					$accTrans[] = array(
						'transaction' => 'SaleReturn',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->vat_output,
						'amount' => abs($order_tax),
						'narrative' => 'Order Tax Return '.$sale->reference_no,
						'description' => $note,
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				}
				if($return_surcharge != 0){
					$accTrans[] = array(
						'transaction' => 'SaleReturn',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->sale_return_acc,
						'amount' => -$return_surcharge,
						'narrative' => 'Surcharge Return '.$sale->reference_no,
						'description' => $note,
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				}
				if($return_surcharge_interest != 0){
					$accTrans[] = array(
						'transaction' => 'SaleReturn',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->sale_return_acc,
						'amount' => -$return_surcharge_interest,
						'narrative' => 'Surcharge Return '.$sale->reference_no,
						'description' => $note,
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				}
				if(abs($shipping) != 0){
					$accTrans[] = array(
						'transaction' => 'SaleReturn',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->shipping_acc,
						'amount' => abs($shipping),
						'narrative' => 'Return Shipping',
						'description' => $note,
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				}
			}
			
			
			
			$data = array('date' => $date,
                'sale_id' => $id,
                'reference_no' => $sale->reference_no,
                'customer_id' => $sale->customer_id,
                'customer' => $sale->customer,
                'biller_id' => $sale->biller_id,
				'project_id' => $sale->project_id,
                'biller' => $sale->biller,
                'warehouse_id' => $sale->warehouse_id,
				'saleman_id' => $sale->saleman_id,
                'saleman' => $sale->saleman,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'surcharge' => $this->cus->formatDecimalRaw($return_surcharge),
				'surcharge_interest' => $this->cus->formatDecimalRaw($return_surcharge_interest),
				'grand_total' => $grand_total,
				'shipping' => $shipping,
                'created_by' => $this->session->userdata('user_id'),
                'return_sale_ref' => $reference,
                'sale_status' => 'returned',
                'pos' => $sale->pos,
                'payment_status' => $sale->payment_status == 'paid' ? 'due' : 'pending',
				'stock_deduction' => 1,
				'ar_account' => (($this->Settings->default_receivable_account==0 || !$sale->ar_account)  ? $saleAcc->ar_acc : $sale->ar_account),
            );
			if($return){
				$data['id'] = $return->id;
			}
			if($this->config->item('agency')){
				$data['agency_id'] = $sale->agency_id;
			}
            $payment = array();
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

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, $si_return, $sale->biller_id, $stockmoves, $accTrans, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("return_sale_added").' '.$sale->reference_no);
            redirect("sales/returns");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $sale;
			$pr = false;
			if($id && $id > 0){
				if ($this->data['inv']->sale_status != 'completed') {
					$this->session->set_flashdata('error', lang("sale_status_x_competed"));
					redirect($_SERVER["HTTP_REFERER"]);
				}
				if ($this->data['inv']->date <= date('Y-m-d', strtotime('-3 months'))) {
					$this->session->set_flashdata('error', lang("sale_x_edited_older_than_3_months"));
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$inv_items = $this->sales_model->getAllInvoiceItemsWithReturn($id);
				krsort($inv_items);
				$c = rand(100000, 9999999);
				foreach ($inv_items as $item) {
					$row = $this->site->getProductByID($item->product_id);
					if (!$row) {
						$row = json_decode('{}');
						$row->tax_method = 0;
						$row->quantity = 0;
					} else {
						unset($row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
					}
					$row->unit_name = $item->unit_name;
					$row->id = $item->product_id;
					$row->sale_item_id = $item->id;
					$row->code = $item->product_code;
					$row->name = $item->product_name;
					$row->type = $item->product_type;
					$row->base_quantity = abs($item->return_qty);
					$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
					$row->base_unit_price = $row->price ? $row->price : $item->real_unit_price;
					$row->unit = $item->product_unit_id;
					$row->qty = abs($item->return_qty);
					$row->return_unit = $item->return_unit_id ? $item->return_unit_id : $item->product_unit_id;
					$row->unit_qty = $item->unit_quantity;
					$row->quantity = $item->quantity;
					$row->expiry = $this->cus->hrsd($item->expiry);
					$row->cost = $item->cost;
					$row->return_stock = $item->return_stock;
					$row->item_discount = $item->item_discount ? ($item->item_discount / $item->unit_quantity) : 0;
					$row->discount = $item->discount ? $item->discount : 0;
					$row->foc = $item->foc;
					// Product Currency
					if($this->config->item('product_currency')==true){
						$dpos = strpos($row->discount, "%");
						if (!$dpos) { 
							$row->item_discount = strval($row->item_discount * ($item->currency_rate));
							$row->discount = strval($item->discount * ($item->currency_rate));
						}
					}
					$row->price = $this->cus->formatDecimalRaw($item->net_unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity));
					$row->unit_price = $row->tax_method ? $item->unit_price + $this->cus->formatDecimalRaw($item->item_discount / $item->quantity) + $this->cus->formatDecimalRaw($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
					$row->real_unit_price = $item->real_unit_price;
					$row->tax_rate = $item->tax_rate_id;
					$row->serial = $item->serial_no;
					$row->salesman_id = $item->salesman_id;
					$row->salesman = $item->salesman;
					$row->option = $item->option_id;
					$options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id, true);
					$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
					$unit = $this->site->getProductUnit($row->id,$item->product_unit_id);
					if($unit && $item->unit_price > 0){
						$row->base_price = ($row->unit_price / $unit->unit_qty);
						if($row->item_discount > 0){
							$row->item_discount = ($row->item_discount / $unit->unit_qty);
						}
					}else{
						$row->base_price = $row->unit_price;
					}
					// Product Currency
					$currencies = false;
					if($this->config->item('product_currency')==true){
						$currencies = $this->site->getAllCurrencies();
						foreach($currencies as $currency){
							if($currency->code == $item->currency_code){
								$currency->rate = $item->currency_rate;
							}
						}
						$row->currency_rate = $item->currency_rate;
						$row->currency_code = $item->currency_code;
						$row->unit_price = $row->unit_price * ($item->currency_rate);
						$row->base_price = $row->base_price * ($item->currency_rate);
					}
					$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
					$ri = $this->Settings->item_addition ? $row->id : $c;
					$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options, 'currencies' => $currencies);
					$c++;
				}
			}
			$this->data['sales'] = $this->site->getRefSales(false, 'completed');
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['payment_ref'] = '';
			$this->data['reference'] = '';
            $this->data['return'] = $return;
			$this->data['installment'] = $this->sales_model->getInstallmentBySaleID($id);
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('sales/returns'), 'page' => lang('sale_returns')), array('link' => '#', 'page' => lang('return_sale')));
			$meta = array('page_title' => lang('return_sale'), 'bc' => $bc);
            $this->core_page('sales/return_sale', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->cus->checkPermissions(null, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$inv = $this->sales_model->getInvoiceByID($id);
		if ($this->Settings->installment==1 && $inv->installment > 0) {
            $this->session->set_flashdata('error', lang('sale_xinstallment_action')." ".$inv->reference_no);
            $this->cus->md();
        }
         /*
		 if ($inv->sale_status == 'returned') {
            $this->session->set_flashdata('error', lang('sale_x_action')." ".$inv->reference_no);
            $this->cus->md();
        }*/
        if ($this->sales_model->deleteSale($id)) {
			$this->deliveries_model->updateDeliveryStatus($inv->delivery_id);
			if($this->config->item("send_telegram")){
				$this->telegrambot->sendmsg("Sale ".$inv->reference_no." (".$inv->customer.") (".$this->cus->formatMoney($inv->grand_total).") has been deleted by ".$this->session->userdata("username"));
			}
            if ($this->input->is_ajax_request()) {
                echo lang("sale_deleted")." ".$inv->reference_no;
				die();
            }
            $this->session->set_flashdata('message', lang('sale_deleted')." ".$inv->reference_no);
            redirect('welcome');
        }
    }

    public function delete_return($id = null)
    {
        $this->cus->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deleteReturn($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("return_sale_deleted");die();
            }
            $this->session->set_flashdata('message', lang('return_sale_deleted'));
            redirect('welcome');
        }
    }
	
	public function return_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');


        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    $this->cus->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteSale($id);
                    }
                    $this->session->set_flashdata('message', lang("sale_returns_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
					$sale_ids = '';
					$s = 1;
					foreach ($_POST['val'] as $id) {
						if($s == 1){
							$sale_ids .= "'".$id."'";
							$s = 2;
						}else{
							$sale_ids .= ",'".$id."'";
						}
					}
					$this->db
							->select("id,
										DATE_FORMAT(date, '%Y-%m-%d %T') as date,
										return_sale_ref,
										reference_no,
										sales.biller,
										customer,
										ABS(grand_total) as grand_total,
										IFNULL(invoice_interest - surcharge_interest,0) as credit_interest,
										IF((invoice_total - invoice_paid - ABS(grand_total)) < 0,ABS(invoice_total - invoice_paid - ABS(grand_total)),'0') as credit_amount,
										IFNULL(ABS(cus_payments.paid),0) as paid,
										IFNULL(ABS(cus_payments.discount),0) as discount,
										IF((invoice_total - (IFNULL(invoice_paid,0) + IFNULL(invoice_interest-surcharge_interest,0)) - ABS(grand_total)) < 0, ABS(invoice_total - (IFNULL(invoice_paid,0) + IFNULL(invoice_interest-surcharge_interest,0)) - ABS(grand_total)) - (IFNULL(ABS(cus_payments.paid),0) + IFNULL(ABS(cus_payments.discount),0)),'0') as balance,
										IF((invoice_total - (IFNULL(invoice_paid,0) + IFNULL(invoice_interest-surcharge_interest,0)) - abs(grand_total)) < 0,
										IF (
											(
												round((abs(invoice_total-(IFNULL(invoice_paid,0) + IFNULL(invoice_interest-surcharge_interest,0)) - abs(grand_total)) - (IFNULL(abs(cus_payments.paid),0) + IFNULL(abs(cus_payments.discount),0))),".$this->Settings->decimals.") = 0
											),
											'paid',
											IF (
											(
												((IFNULL(cus_payments.paid,0))+(IFNULL(cus_payments.discount,0))) = 0
											),
											'pending',
											'partial'
										)),'paid') AS payment_status")
										->from('sales')
										->join('(
													SELECT
														id AS invoice_id,
														grand_total AS invoice_total,
														paid + interest_paid AS invoice_paid
													FROM
														cus_sales
													LEFT JOIN (
														SELECT
															sale_id,
															IFNULL(SUM(interest_paid),0) AS interest_paid
														FROM
															cus_payments GROUP BY sale_id) as cus_payment_inv ON cus_sales.id = cus_payment_inv.sale_id
												) AS cus_inv','sales.sale_id = cus_inv.invoice_id','inner')
										->join('(SELECT
													sale_id,
													IFNULL(SUM(interest_paid),0) AS invoice_interest
												FROM
													'.$this->db->dbprefix('payments').'
												GROUP BY
													sale_id) as cus_payment_inv', 'cus_payment_inv.sale_id=sales.sale_id', 'left')
										->join('(SELECT
													sale_id,
													IFNULL(sum(amount),0)+IFNULL(sum(interest_paid),0) AS paid,
													IFNULL(sum(discount),0) AS discount
												FROM
													'.$this->db->dbprefix('payments').'
												GROUP BY
													sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left');
					$this->db->where("sales.id IN (".$sale_ids.")");
					$q = $this->db->get();
					if ($q->num_rows() > 0) {
						foreach (($q->result()) as $row) {
							$data[] = $row;
						}
					} else {
						$data = NULL;
					}
					
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sale_returns'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no_to'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_return'));
					if($this->Settings->installment==1){
						$this->excel->getActiveSheet()->SetCellValue('G1', lang('credit_interest'));
						$this->excel->getActiveSheet()->SetCellValue('H1', lang('credit_amount'));
						$this->excel->getActiveSheet()->SetCellValue('I1', lang('paid'));
						$this->excel->getActiveSheet()->SetCellValue('J1', lang('discount'));
						$this->excel->getActiveSheet()->SetCellValue('K1', lang('balance'));
						$this->excel->getActiveSheet()->SetCellValue('L1', lang('payment_status'));
					}else{
						$this->excel->getActiveSheet()->SetCellValue('G1', lang('credit_amount'));
						$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
						$this->excel->getActiveSheet()->SetCellValue('I1', lang('discount'));
						$this->excel->getActiveSheet()->SetCellValue('J1', lang('balance'));
						$this->excel->getActiveSheet()->SetCellValue('K1', lang('payment_status'));
					}
                    $row = 2;
					if($data){
						foreach ($data as $sale) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($sale->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $sale->return_sale_ref);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->biller);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->customer);
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->formatDecimal($sale->grand_total));
							if($this->Settings->installment==1){
								$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->formatDecimal($sale->credit_interest));
								$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->formatDecimal($sale->credit_amount));
								$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->cus->formatDecimal($sale->paid));
								$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->cus->formatDecimal($sale->discount));
								$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->cus->formatDecimal($sale->balance));
								$this->excel->getActiveSheet()->SetCellValue('L' . $row, lang($sale->payment_status));
							}else{
								$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->formatDecimal($sale->credit_amount));
								$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->formatDecimal($sale->paid));
								$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->cus->formatDecimal($sale->discount));
								$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->cus->formatDecimal($sale->balance));
								$this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($sale->payment_status));
							}
							$row++;
						}
					}
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sale_returns' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_sale_return_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    
	public function sale_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete');
					$deleted = 0;
                    foreach ($_POST['val'] as $id) {
						$inv = $this->sales_model->getInvoiceByID($id);
						if ($this->Settings->installment==1 && $inv->installment > 0) {
							$this->session->set_flashdata('error', lang('sale_xinstallment_action')." ".$inv->reference_no);
							$this->cus->md();
						}
                        if($this->sales_model->deleteSale($id)){
							$deleted = 1;
							if($this->config->item("send_telegram")){
								$this->telegrambot->sendmsg("Sale ".$inv->reference_no." (".$inv->customer.") (".$this->cus->formatMoney($inv->grand_total).") has been deleted by ".$this->session->userdata("username"));
							}
						};
                    }
					if($deleted > 0){
						$this->session->set_flashdata('message', lang("sales_deleted"));
					}else{
						$this->session->set_flashdata('error', lang("sales_cannot_deleted"));
					}
                    
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel') {
					
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('returned'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('discount'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('delivery_status'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('payment_status'));
					
					$this->db->select("sales.id as id,
										DATE_FORMAT(date, '%Y-%m-%d %T') as date,
										reference_no,
										sales.biller,
										customer,
										grand_total,
										IFNULL(total_return,0) as total_return,
										IFNULL(cus_payments.paid + IFNULL(total_return_paid,0),0) as paid,
										IFNULL(cus_payments.discount,0) as discount,
										ROUND((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") as balance,
										delivery_status,
										IF (
											(
												round((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") = 0
											),
											'paid',
											IF (
											(
												(grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))) = grand_total
											),
											'pending',
											'partial'
										)) AS payment_status")
										->from('sales')
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
					$this->db->where_in("sales.id",$_POST['val']);
					$q = $this->db->get();
					$row = 2;
					if ($q->num_rows() > 0) {
						foreach (($q->result()) as $sale) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($sale->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $sale->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale->biller);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->customer);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->cus->formatDecimal($sale->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->formatDecimal($sale->total_return));
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->formatDecimal($sale->paid));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->formatDecimal($sale->discount));
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->cus->formatDecimal($sale->balance));
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($sale->delivery_status));
							$this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($sale->payment_status));
							$row++;
						}
					}
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sales_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_sale_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function deliveries()
    {
        $this->cus->checkPermissions();
		
        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('deliveries')));
        $meta = array('page_title' => lang('deliveries'), 'bc' => $bc);
        $this->core_page('sales/deliveries', $meta, $this->data);

    }

    public function getDeliveries()
    {
        $this->cus->checkPermissions('deliveries');

        $detail_link = anchor('sales/view_delivery/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_details'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $email_link = anchor('sales/email_delivery/$1', '<i class="fa fa-envelope"></i> ' . lang('email_delivery'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $edit_link = anchor('sales/edit_delivery/$1', '<i class="fa fa-edit"></i> ' . lang('edit_delivery'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_delivery") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_delivery/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_delivery') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_link . '</li>
				<li>' . $edit_link . '</li>
				<li>' . $delete_link . '</li>
			</ul>
		</div></div>';

        $this->load->library('datatables');
        //GROUP_CONCAT(CONCAT('Name: ', sale_items.product_name, ' Qty: ', sale_items.quantity ) SEPARATOR '<br>')
        $this->datatables
            ->select("deliveries.id as id, deliveries.date, do_reference_no, sale_reference_no, sales.biller, deliveries.customer, deliveries.address, deliveries.status, deliveries.attachment")
            ->from('deliveries')
            ->join('sale_items', 'sale_items.sale_id=deliveries.sale_id', 'left')
			->join('sales', 'sales.id=deliveries.sale_id', 'left')
            ->group_by('deliveries.id');
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('sales.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->datatables->add_column("Actions", $action, "id");

        echo $this->datatables->generate();
    }

    public function pdf_delivery($id = null, $view = null, $save_bufffer = null)
    {
        $this->cus->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->sales_model->getDeliveryByID($id);

        $this->data['delivery'] = $deli;
        $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
        $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
        $this->data['user'] = $this->site->getUser($deli->created_by);

        $name = lang("delivery") . "_" . str_replace('/', '_', $deli->do_reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'sales/pdf_delivery', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'sales/pdf_delivery', $this->data);
        } elseif ($save_bufffer) {
            return $this->cus->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->cus->generate_pdf($html, $name);
        }
    }

    public function view_delivery($id = null)
    {
        $this->cus->checkPermissions('deliveries');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->sales_model->getDeliveryByID($id);
        $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
        if (!$sale) {
            $this->session->set_flashdata('error', lang('sale_not_found'));
            $this->cus->md();
        }
        $this->data['delivery'] = $deli;
        $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
        $this->data['user'] = $this->site->getUser($deli->created_by);
        $this->data['page_title'] = lang("delivery_order");

        $this->load->view($this->theme . 'sales/view_delivery', $this->data);
    }

    public function add_delivery($id = null)
    {
        $this->cus->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->sale_status != 'completed') {
            $this->session->set_flashdata('error', lang('status_is_x_completed'));
            $this->cus->md();
        }

        if ($delivery = $this->sales_model->getDeliveryBySaleID($id)) {
            $this->edit_delivery($delivery->id);
        } else {

            $this->form_validation->set_rules('sale_reference_no', lang("sale_reference_no"), 'required');
            $this->form_validation->set_rules('customer', lang("customer"), 'required');
            $this->form_validation->set_rules('address', lang("address"), 'required');

            if ($this->form_validation->run() == true) {
                if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date_delivery']) {
                    $date = $this->cus->fld(trim($this->input->post('date')));
                } else {
                    $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
                }
                $dlDetails = array(
                    'date' => $date,
                    'sale_id' => $this->input->post('sale_id'),
                    'do_reference_no' => $this->input->post('do_reference_no') ? $this->input->post('do_reference_no') : $this->site->getReference('do',$sale->biller_id),
                    'sale_reference_no' => $this->input->post('sale_reference_no'),
                    'customer' => $this->input->post('customer'),
                    'address' => $this->input->post('address'),
                    'status' => $this->input->post('status'),
                    'delivered_by' => $this->input->post('delivered_by'),
                    'received_by' => $this->input->post('received_by'),
                    'note' => $this->cus->clear_tags($this->input->post('note')),
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
            } elseif ($this->input->post('add_delivery')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if ($this->form_validation->run() == true && $this->sales_model->addDelivery($dlDetails)) {
                $this->session->set_flashdata('message', lang("delivery_added"));
                redirect("sales/deliveries");
            } else {

                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['customer'] = $this->site->getCompanyByID($sale->customer_id);
                $this->data['inv'] = $sale;
                $this->data['do_reference_no'] = '';
                $this->data['modal_js'] = $this->site->modal_js();

                $this->load->view($this->theme . 'sales/add_delivery', $this->data);
            }
        }
    }

    public function edit_delivery($id = null)
    {
        $this->cus->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('do_reference_no', lang("do_reference_no"), 'required');
        $this->form_validation->set_rules('sale_reference_no', lang("sale_reference_no"), 'required');
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('address', lang("address"), 'required');

        if ($this->form_validation->run() == true) {

            $dlDetails = array(
                'sale_id' => $this->input->post('sale_id'),
                'do_reference_no' => $this->input->post('do_reference_no'),
                'sale_reference_no' => $this->input->post('sale_reference_no'),
                'customer' => $this->input->post('customer'),
                'address' => $this->input->post('address'),
                'status' => $this->input->post('status'),
                'delivered_by' => $this->input->post('delivered_by'),
                'received_by' => $this->input->post('received_by'),
                'note' => $this->cus->clear_tags($this->input->post('note')),
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date("Y-m-d H:i"),
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

            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date_delivery']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
                $dlDetails['date'] = $date;
            }
        } elseif ($this->input->post('edit_delivery')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateDelivery($id, $dlDetails)) {
            $this->session->set_flashdata('message', lang("delivery_updated"));
            redirect("sales/deliveries");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['delivery'] = $this->sales_model->getDeliveryByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_delivery', $this->data);
        }
    }

    public function delete_delivery($id = null)
    {
        $this->cus->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deleteDelivery($id)) {
            echo lang("delivery_deleted");
        }

    }

    public function delivery_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete_delivery');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteDelivery($id);
                    }
                    $this->session->set_flashdata('message', lang("deliveries_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('deliveries'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('do_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $delivery = $this->sales_model->getDeliveryByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($delivery->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $delivery->do_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $delivery->sale_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $delivery->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $delivery->address);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($delivery->status));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);

                    $filename = 'deliveries_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_delivery_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

	public function payment_returns($id = null)
    {
        $this->cus->checkPermissions("payments", true);
        $this->data['payments'] = $this->sales_model->getInvoicePayments($id);
        $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
        $this->load->view($this->theme . 'sales/payment_returns', $this->data);
    }

    public function payments($id = null, $down_payment_detail_id = null)
    {
        $this->cus->checkPermissions(false, true);
        $this->data['payments'] = $this->sales_model->getInvoicePayments($id, $down_payment_detail_id);
        $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
        $this->load->view($this->theme . 'sales/payments', $this->data);
    }

    public function payment_note($id = null)
    {
        $this->cus->checkPermissions('payments', true);
        $payment = $this->sales_model->getPaymentByID($id);
		$inv = $this->sales_model->getInvoiceByID($payment->sale_id);
		$inv_payments = $this->sales_model->getPaymentsByRef($payment->reference_no,$payment->date);
		$customer = $this->site->getCompanyByID($inv->customer_id);
		$this->data['saleman'] = $this->site->getUser($inv->saleman_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['customer'] = $customer;
        $this->data['inv'] = $inv;
		$this->data['inv_payments'] = $inv_payments;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = lang("payment_note");
		
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Sale Payment',$payment->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Sale Payment',$payment->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		if($inv->type == "school"){
			$student = $this->sales_model->getStudentByID($customer->student_id);
			$this->data['rows'] = $this->sales_model->getAllInvoiceItems($inv->id,false,"asc");
			$this->data['study'] = $this->sales_model->getStudyInfoBySale($inv->id);
			$this->data['siblings'] = $this->sales_model->getSiblings($student->family_id);
			$this->data['student'] = $student;
			$this->load->view($this->theme . 'sales/payment_note_school', $this->data);
		}else{
			$this->load->view($this->theme . 'sales/payment_note', $this->data);
		}
		
    }

    public function email_payment($id = null)
    {
        $this->cus->checkPermissions('payments', true);
        $payment = $this->sales_model->getPaymentByID($id);
        $inv = $this->sales_model->getInvoiceByID($payment->sale_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $customer = $this->site->getCompanyByID($inv->customer_id);
        if ( ! $customer->email) {
            $this->cus->send_json(array('msg' => lang("update_customer_email")));
        }
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['customer'] =$customer;
        $this->data['page_title'] = lang("payment_note");
        $html = $this->load->view($this->theme . 'sales/payment_note', $this->data, TRUE);

        $html = str_replace(array('<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>'.lang("stamp_sign").'</p>'), '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);
        // $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">'.$html.'</div>';

        $this->load->library('parser');
        $parse_data = array(
            'stylesheet' => '<link href="'.$this->data['assets'].'styles/helpers/bootstrap.min.css" rel="stylesheet"/>',
            'name' => $customer->company && $customer->company != '-' ? $customer->company :  $customer->name,
            'email' => $customer->email,
            'heading' => lang('payment_note').'<hr>',
            'msg' => $html,
            'site_link' => base_url(),
            'site_name' => $this->Settings->site_name,
            'logo' => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>'
        );
        $msg = file_get_contents('./themes/' . $this->Settings->theme . '/views/email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->Settings->site_name;

        if ($this->cus->send_email($customer->email, $subject, $message)) {
            $this->cus->send_json(array('msg' => lang("email_sent")));
        } else {
            $this->cus->send_json(array('msg' => lang("email_failed")));
        }
    }
	
	public function add_payment_return($id = null)
    {
        $this->cus->checkPermissions('payments', true);
		$this->cus->checkPermissions('add',true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getSaleReturnByID($id);
        if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
            $this->session->set_flashdata('error', lang("sale_already_paid"));
            $this->cus->md();
        }
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $customer_id = null;
            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
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
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$sale->biller_id);
            $amount_paid = $this->input->post('amount-paid');
			$principal_paid = $this->input->post('principal-paid');
			$interest_paid = $this->input->post('interest-paid');
			$amount_discount = $this->input->post('discount');

			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			
			$payment = array(
                'date' => $date,
                'sale_id' => $this->input->post('sale_id'),
                'reference_no' => $reference_no,
                'amount' => -$amount_paid,
				'discount' => -$amount_discount,
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'returned',
				'currencies' => json_encode($currencies),
				'account_code' => $paying_from,
            );
			if($this->Settings->installment==1){
				$payment['installment_customer_id'] = $sale->customer_id;
				$payment['installment_id'] = $this->input->post('installment_id');
				$payment['amount'] = -$principal_paid;
				$payment['interest_paid'] = -$interest_paid;
			}
			//=====accountig=====//
			if($this->Settings->accounting == 1){
				$paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
				$accTranPayments[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => (($this->Settings->default_receivable_account==0 || !$sale->ar_account)  ? $paymentAcc->ar_acc : $sale->ar_account),
						'amount' => ($amount_paid+$amount_discount),
						'narrative' => 'Sale Return Payment '.$sale->return_sale_ref,
						'description' => $this->input->post('note'),
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);

				$accTranPayments[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $paying_from,
						'amount' => -$amount_paid,
						'narrative' => 'Sale Return Payment '.$sale->return_sale_ref,
						'description' => $this->input->post('note'),
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				if($amount_discount>0){
					$accTranPayments[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $paymentAcc->sale_discount_acc,
						'amount' => -$amount_discount,
						'narrative' => 'Sale Return Payment Discount '.$sale->return_sale_ref,
						'description' => $this->input->post('note'),
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				}
			}
			//=====end accountig=====//

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

        } elseif ($this->input->post('add_payment_return')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addPayment($payment, $customer_id, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("payment_added"));
			if($this->config->item("send_telegram")){
				$this->telegrambot->sendmsg("Payment ".$payment['reference_no']." (".$sale->customer.") (".$this->cus->formatMoney($payment['amount']).") has been added by ".$this->session->userdata("username"));
			}
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($sale->sale_status == 'returned' && abs($sale->paid) == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->cus->md();
            }
            $this->data['inv'] = $sale;
			$this->data['return_payments'] = $this->sales_model->getPaymentsBySale($sale->id);
			$this->data['sale_payments'] = $this->sales_model->getPaymentsBySale($sale->sale_id);
			$this->data['payment_term'] = $this->sales_model->getPaymentTermsByID($sale->payment_term);
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales/add_payment_return', $this->data);
        }
    }

	public function edit_payment_returns($id = null)
    {
		$this->cus->checkPermissions('payments', true);
        $this->cus->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->sales_model->getPaymentByID($id);
        if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') {
            $this->session->set_flashdata('error', lang('x_edit_payment'));
            $this->cus->md();
        }
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			$sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale->customer_id;
                $amount = $this->input->post('amount-paid')-$payment->amount;
                if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
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
			$payment_amount = $this->input->post('amount-paid');
			$principal_paid = $this->input->post('principal-paid');
			$interest_paid = $this->input->post('interest-paid');
			$discount_amount = $this->input->post('discount');

			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			
			$payment = array(
                'date' => $date,
                'sale_id' => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount' => -$payment_amount,
				'discount' => -$discount_amount,
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
				'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
				'currencies' => json_encode($currencies),
				'account_code' => $paying_from,
            );
			if($this->Settings->installment==1){
				$payment['amount'] = -$principal_paid;
				$payment['interest_paid'] = -$interest_paid;
			}
			//=====accountig=====//
					if($this->Settings->accounting == 1){
						$paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
						$accTranPayments[] = array(
								'transaction_id' => $id,
								'transaction' => 'Payment',
								'transaction_date' => $date,
								'reference' => $this->input->post('reference_no'),
								'account' => (($this->Settings->default_receivable_account==0 || !$sale->ar_account)  ? $paymentAcc->ar_acc : $sale->ar_account),
								'amount' => ($payment_amount+$discount_amount),
								'narrative' => 'Sale Return Payment '.$sale->return_sale_ref,
								'description' => $this->input->post('note'),
								'biller_id' => $sale->biller_id,
								'project_id' => $sale->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale->customer_id,
							);

						

						$accTranPayments[] = array(
								'transaction_id' => $id,
								'transaction' => 'Payment',
								'transaction_date' => $date,
								'reference' => $this->input->post('reference_no'),
								'account' => $paying_from,
								'amount' => -$payment_amount,
								'narrative' => 'Sale Return Payment '.$sale->return_sale_ref,
								'description' => $this->input->post('note'),
								'biller_id' => $sale->biller_id,
								'project_id' => $sale->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale->customer_id,
							);
						if($this->input->post('discount')>0){
							$accTranPayments[] = array(
								'transaction_id' => $id,
								'transaction' => 'Payment',
								'transaction_date' => $date,
								'reference' => $this->input->post('reference_no'),
								'account' => $paymentAcc->sale_discount_acc,
								'amount' => -$discount_amount,
								'narrative' => 'Sale Return Payment Discount '.$sale->return_sale_ref,
								'description' => $this->input->post('note'),
								'biller_id' => $sale->biller_id,
								'project_id' => $sale->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale->customer_id,
							);
						}
					}
				//=====end accountig=====//

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

            //$this->cus->print_arrays($payment);

        } elseif ($this->input->post('edit_payment_return')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }


        if ($this->form_validation->run() == true && $this->sales_model->updatePayment($id, $payment, $customer_id, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("payment_updated"));
			if($this->config->item("send_telegram")){
				$this->telegrambot->sendmsg("Payment ".$payment['reference_no']." (".$sale->customer.") (".$this->cus->formatMoney($payment['amount']).") has been edited by ".$this->session->userdata("username"));
			}
            redirect("sales/returns");
        } else {
			$sale = $this->sales_model->getInvoiceByID($payment->sale_id);
			$this->data['inv'] = $sale;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
			$this->data['sale_payments'] = $this->sales_model->getPaymentsBySale($sale->sale_id);
			$this->data['payment_term'] = $this->sales_model->getPaymentTermsByID($sale->payment_term);
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales/edit_payment_return', $this->data);
        }
    }

		public function add_multi_payment($id = null)
    {
        $this->cus->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$ids = explode('SaleID',$id);
		$sale = $this->sales_model->getMultiInvoiceByID($ids);
		$multiple = $this->sales_model->getSalesByBillers($ids);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$total_amount = $this->input->post('amount-paid');
			$total_discount = $this->input->post('discount');
			$camounts = $this->input->post("c_amount");
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
            }

			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$multiple->row()->biller_id);
			$paid_currencies = array();
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$paid_currencies[$currency[$key]] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$cur_def = $this->site->getCurrencyByCode($this->Settings->default_currency);
			for($i=0; $i<count($ids); $i++){
				if($total_amount > 0){
					$saleInfo = $this->sales_model->getInvoiceBalanceByID($ids[$i]);
					if($saleInfo){
						$invoice_date = strtotime($saleInfo->date);
						$datediff = strtotime(date('Y-m-d'))- $invoice_date;
						$payment_date = round($datediff / (60 * 60 * 24));
						$discount = 0;
						$total = ($saleInfo->grand_total-$saleInfo->total_return) - ($saleInfo->paid+$saleInfo->discount-$saleInfo->paid_return);
						if($payment_date < $saleInfo->due_day_discount){
							if($saleInfo->discount_type == "Percentage"){
								$discount = ($saleInfo->payment_discount * $total) / 100;
							}else{
								$discount = $saleInfo->payment_discount;
							}
						}
						$grand_total = $total - $discount;
						if($total_amount > $grand_total){
							$pay_amount = $grand_total;
							$total_amount = $total_amount - $grand_total;
						}else{
							$pay_amount = $total_amount;
							$total_amount = 0;
						}
						if($total_discount > $discount){
							$discount_amount = $discount;
							$total_discount = $total_discount - $discount;
						}else{
							$discount_amount = $total_discount;
							$total_discount = 0;
						}
						
						$currencies = array();
						if(!empty($camounts)){
							$total_paid = $pay_amount;
						
							foreach($paid_currencies as $cur_code => $paid_currencie){
								$paid_cur = $paid_currencie['amount'];
								if($paid_cur > 0){
									if($cur_code != $cur_def->code){
										if($paid_currencie['rate'] > $cur_def->rate){
											$paid_cur = $paid_cur / $paid_currencie['rate'];
										}else{
											$paid_cur = $paid_cur * $cur_def->rate;
										}
									}
					
									if($paid_cur >= $total_paid && $total_paid > 0){
										$paid_currencie['amount'] = $total_paid;
										if($cur_code != $cur_def->code){
											if($paid_currencie['rate'] > $cur_def->rate){
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) * $paid_currencie['rate'];
											}else{
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) / $cur_def->rate;
											}
										}else{
											$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid);
										}
										$total_paid = 0;
									}else{
										if($total_paid > 0){
											$paid_currencie['amount'] = $paid_cur;
											$paid_currencies[$cur_code]['amount'] = 0;
											$total_paid = $total_paid - $paid_cur;
										}else{
											$paid_currencie['amount'] = 0;
										}
									}
								}								
								if($cur_code != $cur_def->code){
									if($paid_currencie['rate'] > $cur_def->rate){
										$paid_currencie['amount'] = $paid_currencie['amount'] * $paid_currencie['rate'];
									}else{
										$paid_currencie['amount'] = $paid_currencie['amount'] / $cur_def->rate;
									}
								}
								$currencies[] = $paid_currencie;
							}
						}
						
						$payment[] = array(
							'date' => $date,
							'sale_id' => $saleInfo->id,
							'reference_no' => $reference_no,
							'amount' => $pay_amount,
							'discount' => $discount_amount,
							'paid_by' => $this->input->post('paid_by'),
							'cheque_no' => $this->input->post('cheque_no'),
							'cc_no' => $this->input->post('pcc_no'),
							'cc_holder' => $this->input->post('pcc_holder'),
							'cc_month' => $this->input->post('pcc_month'),
							'cc_year' => $this->input->post('pcc_year'),
							'cc_type' => $this->input->post('pcc_type'),
							'note' => $this->input->post('note'),
							'created_by' => $this->session->userdata('user_id'),
							'type' => 'received',
							'currencies' => json_encode($currencies),
							'account_code' => $this->input->post('paying_to'),
							'attachment' => $photo,
						);
						
						if($this->Settings->accounting == 1){
							$paymentAcc = $this->site->getAccountSettingByBiller($saleInfo->biller_id);
							$accTranPayments[$saleInfo->id][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => (($this->Settings->default_receivable_account==0 || !$sale->ar_account)  ? $paymentAcc->ar_acc : $sale->ar_account),
									'amount' => -($pay_amount+$discount_amount),
									'narrative' => 'Sale Payment '.$saleInfo->reference_no,
									'description' => $this->input->post('note'),
									'biller_id' => $saleInfo->biller_id,
									'project_id' => $saleInfo->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $saleInfo->customer_id,
								);
							$accTranPayments[$saleInfo->id][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $this->input->post('paying_to'),
									'amount' => $pay_amount,
									'narrative' => 'Sale Payment '.$saleInfo->reference_no,
									'description' => $this->input->post('note'),
									'biller_id' => $saleInfo->biller_id,
									'project_id' => $saleInfo->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $saleInfo->customer_id,
								);
							if($this->input->post('discount')>0){
								$accTranPayments[$saleInfo->id][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $paymentAcc->sale_discount_acc,
									'amount' => $discount_amount,
									'narrative' => 'Sale Payment Discount '.$saleInfo->reference_no,
									'description' => $this->input->post('note'),
									'biller_id' => $saleInfo->biller_id,
									'project_id' => $saleInfo->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $saleInfo->customer_id,
								);
							}
						}
					}
				}

			}
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addMultiPayment($payment, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if (!$sale) {
                $this->session->set_flashdata('warning', lang('sales_already_paid'));
                $this->cus->md();
            }
			if($multiple->num_rows() > 1){
				$this->session->set_flashdata('error', lang("biller_multi_cannot_add"));
				$this->cus->md();
			}
			if($this->Settings->accounting == 1){
				$saleAcc = $this->site->getAccountSettingByBiller($sale[0]->biller_id);
				$this->data['cash_account'] = $this->site->getAccount('',$saleAcc->cash_acc,'1');
			}
            $this->data['inv'] = $sale;
            $this->data['payment_ref'] = '';
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales/add_multi_payment', $this->data);
        }
    }
	
    public function add_payment($id = null, $down_payment_detail_id = null)
    {
        $this->cus->checkPermissions('payments', true);
		$this->cus->checkPermissions('add', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
                $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                $customer_id = $sale->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
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
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$sale->biller_id);
            $paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
			
			$bank_name="";
			$account_name="";
			$account_number="";
			$cheque_number="";
			$cheque_date="";
			if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
				$paying_to = $paymentAcc->customer_deposit_acc;
			}else{
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_to = $cash_account->account_code;
				if($cash_account->type=="bank"){
					$bank_name = $cash_account->name;
					$account_name = $this->input->post('account_name');
					$account_number = $this->input->post('account_name');
				}else if($cash_account->type=="cheque"){
					$bank_name = $this->input->post('bank_name');
					$cheque_number = $this->input->post('cheque_number');
					$cheque_date = $this->cus->fsd($this->input->post('cheque_date'));
				}
			}
			
			$payment = array(
                'date' => $date,
                'sale_id' => $this->input->post('sale_id'),
                'reference_no' => $reference_no,
                'amount' => $this->input->post('amount-paid'),
				'discount' => $this->input->post('discount'),
                'paid_by' => $this->input->post('paid_by'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : '',
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'received',
				'currencies' => json_encode($currencies),
				'account_code' => $paying_to,
				'bank_name' => $bank_name,
				'account_name' => $account_name,
				'account_number' => $account_number,
				'cheque_number' => $cheque_number,
				'cheque_date' => $cheque_date,
				'down_payment_detail_id' => $this->input->post('down_payment_detail_id'),
            );
			
			if($this->Settings->installment==1){
				$is_deposit = $this->input->post('is_deposit');
				if($is_deposit || $is_deposit != NULL){
					$payment['is_deposit'] = 1;
				}
			}
			
			if($this->Settings->accounting == 1){	
				$accTranPayments[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => (($this->Settings->default_receivable_account==0 || !$sale->ar_account)  ? $paymentAcc->ar_acc : $sale->ar_account),
						'amount' => -($this->input->post('amount-paid')+$this->input->post('discount')),
						'narrative' => 'Sale Payment '.$sale->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				
				$accTranPayments[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $paying_to,
						'amount' => $this->input->post('amount-paid'),
						'narrative' => 'Sale Payment '.$sale->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				if($this->input->post('discount')>0){
					$accTranPayments[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $paymentAcc->sale_discount_acc,
						'amount' => $this->input->post('discount'),
						'narrative' => 'Sale Payment Discount '.$sale->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				}
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
			
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->sales_model->addPayment($payment, $customer_id, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("payment_added"));
			if($this->config->item("send_telegram")){
				$this->telegrambot->sendmsg("[New Payment]-".$payment['reference_no']." \r\n- Customer Name: ".$sale->customer."\r\n- Amount:".$this->cus->formatMoney($payment['amount'])." $ \r\n- It has been added by: ".$this->session->userdata("username"));
			}
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			if ($sale->sale_status == 'returned' && $sale->paid == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->cus->md();
            }
			$return = $this->sales_model->getReturnBySaleId($id);
			$balance_amount = $sale->grand_total - $sale->paid ;
			if($return){
				if($return->paid){
					$balance_amount += abs($return->paid);
				}
				if($return->grand_total){
					$balance_amount -= abs($return->grand_total);
				}
			}
			if($balance_amount == 0){
				$this->session->set_flashdata('error', lang('sale_already_paid'));
                $this->cus->md();
			}
			if($this->config->item('down_payment')){
				$this->data['down_payment_detail_id'] = $down_payment_detail_id;
				$this->data['down_payment'] = $this->sales_model->getSeparatePaymentDetailByID($down_payment_detail_id);
			}
			
			if($this->config->item('schools') && $sale->type=="school"){
				$study_info = $this->sales_model->getStudyInfoBySale($sale->id);
				$student_info = $this->sales_model->getStudentByID($study_info->student_id);
				$bank_info = $this->sales_model->getFamilyBank($student_info->family_id);
				$this->data['bank_info'] = $bank_info;
			}
            $this->data['inv'] = $sale;
			$this->data['return'] = $return;
            $this->data['payment_ref'] = '';
			$this->data['payment_term'] = $this->sales_model->getPaymentTermsByID($sale->payment_term);
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales/add_payment', $this->data);
        }
    }

    public function edit_payment($id = null)
    {
		$this->cus->checkPermissions('payments', true);
		$this->cus->checkPermissions('edit', true);
        $this->load->helper('security');
		
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->sales_model->getPaymentByID($id);
		$opay = $payment;
        if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') {
            $this->session->set_flashdata('error', lang('x_edit_payment'));
            $this->cus->md();
        }
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			$sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale->customer_id;
                $amount = $this->input->post('amount-paid')-$payment->amount;
                if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
				$date = $this->cus->fld(trim($this->input->post('date')));
				if($payment->pos_paid > 0){
					if($this->Settings->date_with_time == 1){
						$date = $date.':'.(explode(':',$payment->date)[2]);
					}else{
						$date = $date.' '.(explode(' ',$payment->date)[1]);
					}
				}
            } else {
                $date = $payment->date;
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
			$paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
			$bank_name="";
			$account_name="";
			$account_number="";
			$cheque_number="";
			$cheque_date="";
			if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
				$paying_to = $paymentAcc->customer_deposit_acc;
			}else{
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_to = $cash_account->account_code;
				if($cash_account->type=="bank"){
					$bank_name = $cash_account->name;
					$account_name = $this->input->post('account_name');
					$account_number = $this->input->post('account_name');
				}else if($cash_account->type=="cheque"){
					$bank_name = $this->input->post('bank_name');
					$cheque_number = $this->input->post('cheque_number');
					$cheque_date = $this->cus->fsd($this->input->post('cheque_date'));
				}
			}

            $payment = array(
                'date' => $date,
                'sale_id' => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
				'discount' => $this->input->post('discount'),
                'paid_by' => $this->input->post('paid_by'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : '',
                'note' => $this->input->post('note'),
				'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
				'currencies' => json_encode($currencies),
				'account_code' => $paying_to,
				'bank_name' => $bank_name,
				'account_name' => $account_name,
				'account_number' => $account_number,
				'cheque_number' => $cheque_number,
				'cheque_date' => $cheque_date,
            );
			
			if($this->Settings->installment==1 && $sale->installment == 1){
				$payment['amount'] = $this->input->post('principal-paid');
				$payment['interest_paid'] = $this->input->post('interest-paid');
				$payment['penalty_paid'] = $this->input->post('penalty-paid');
			}
			
			if($this->Settings->installment==1){
				$is_deposit = $this->input->post('is_deposit');
				if($is_deposit || $is_deposit != NULL){
					$payment['is_deposit'] = 1;
				}
			}
			
			if($this->Settings->accounting == 1){
				$paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
				if($opay->transaction=="SO Deposit"){
					$paying_to = $paymentAcc->customer_deposit_acc;
				}
				$accTranPayments[] = array(
						'transaction_id' => $id,
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $this->input->post('reference_no'),
						'account' => (($this->Settings->default_receivable_account==0 || !$sale->ar_account)  ? $paymentAcc->ar_acc : $sale->ar_account),
						'amount' => -($this->input->post('amount-paid')+$this->input->post('discount')),
						'narrative' => 'Sale Payment '.$sale->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				$accTranPayments[] = array(
						'transaction_id' => $id,
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $this->input->post('reference_no'),
						'account' => $paying_to,
						'amount' => $this->input->post('amount-paid'),
						'narrative' => 'Sale Payment '.$sale->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				if($this->input->post('discount')>0){
					$accTranPayments[] = array(
						'transaction_id' => $id,
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $this->input->post('reference_no'),
						'account' => $paymentAcc->sale_discount_acc,
						'amount' => $this->input->post('discount'),
						'narrative' => 'Sale Payment Discount '.$sale->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $sale->biller_id,
						'project_id' => $sale->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $sale->customer_id,
					);
				}
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

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updatePayment($id, $payment, $customer_id, $accTranPayments)) {
			if($this->Settings->installment==1 && $opay->installment_id > 0){
				$this->load->model("installments_model");
				$this->installments_model->syncInstallmentByID($opay->installment_id);
				$this->installments_model->syncInstallmentPayments($opay->installment_item_id);
			}
			$this->session->set_flashdata('message', lang("payment_updated"));
			if($this->config->item("send_telegram")){
				$this->telegrambot->sendmsg("[Edit Payment] -".$payment['reference_no']."\r\n- Customer Name: ".$sale->customer." \r\n- Amount: ".$this->cus->formatMoney($payment['amount'])." $ \r\n- it has been edited by ".$this->session->userdata("username"));
			}
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$sale = $this->sales_model->getInvoiceByID($payment->sale_id);
			$this->data['inv'] = $sale;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
			$this->data['payment_term'] = $this->sales_model->getPaymentTermsByID($sale->payment_term);
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales/edit_payment', $this->data);
        }
    }

    public function delete_payment($id = null)
    {
		$this->cus->checkPermissions('payments');
		$this->cus->checkPermissions('delete');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$opay = $this->sales_model->getPaymentByID($id);
		$inv = $this->sales_model->getSaleByID($opay->sale_id);
        if ($this->sales_model->deletePayment($id)) {
			if($this->Settings->installment==1 && $opay->installment_id > 0 && $inv->sale_status != 'returned'){
				$this->load->model("installments_model");
				$this->installments_model->syncInstallmentByID($opay->installment_id);
				$this->installments_model->syncInstallmentPayments($opay->installment_item_id);
			}
            $this->session->set_flashdata('message', lang("payment_deleted"));
			if($this->config->item("send_telegram")){
				$this->telegrambot->sendmsg("[Delete Payment] -".$opay->reference_no."\r\n- Customer Name: ".$inv->customer.") \r\n- Amount: ".$this->cus->formatMoney($opay->amount)." $ \r\n- It has been deleted by ".$this->session->userdata("username"));
			}
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function category_suggestions()
	{
		$term = $this->input->get('term', true);
		$warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);
		$analyzed = $this->cus->analyze_term($term);
		$warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
		$customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
		if(in_array('bom',$this->config->item('product_types'))) {
        	$enable_bom = true;
        }else{
        	$enable_bom  = false;
        }
		
		if($this->config->item('saleman_commission') && $this->Settings->product_commission == 1){
			$salesmans = $this->site->getSalemans();
			$product_commission = true;
		}else{
			$salesmans = false;
			$product_commission = false;
		}
		
        $sr = $analyzed['term'];
		if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
		}
		$categories = $this->sales_model->getCategoryNames($sr);
		if($categories){
			if($this->Settings->product_formulation == 1){
                $product_formulations = $this->sales_model->getProductFormulation();
            }else{
                $product_formulations = false;
			}
			$r = 1;
			$b = 0;
			$c = str_replace(".", "", microtime(true));
			foreach($categories as $category){
				$products = $this->sales_model->getProductByCategory($category->id);
				$pr = array();
				if($products){
					foreach ($products as $row) {
						unset($row->details, $row->product_details, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
						$option = false;
						$row->quantity = 0;
						$row->item_tax_method = $row->tax_method;
						$row->qty = 1;
						$row->discount = '0';
						if($row->serial){
							if($row->serial_price > 0){
								$row->price = $row->serial_price;
							}
						}else{
							$row->serial = '';
						}
						$options = $this->sales_model->getProductOptions($row->id, $warehouse_id);
						if ($options) {
							$opt = $option_id && $r == 0 ? $this->sales_model->getProductOptionByID($option_id) : $options[0];
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
							}
						}

						$sale_order = $this->sales_model->getSaleOrderByApproval($row->id);
						$row->quantity -= $sale_order->quantity;
						$row->cost = $row->cost;
						$row->base_quantity = 1;
						$row->base_unit = $row->unit;
						$row->base_unit_price = $row->price;
						$row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
						$row->comment = '';
						$combo_items = false;
						if ($row->type == 'combo') {
							$combo_items = $this->sales_model->getComboProducts($row->id);
						}

						if ($row->promotion && date('Y-m-d') >= $row->start_date && date('Y-m-d') <= $row->end_date) {
							$row->discount = (100-(($row->promo_price / $row->price) * 100)).'%';
						}else if($this->Settings->customer_price == 1){
							$customer_price = $this->sales_model->getCustomerPrice($row->id,$customer_id);
							if($customer_price->price > 0){
								$row->price = $customer_price->price;
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
						if($this->Settings->product_expiry == '1'){
							$product_expiries = $this->site->getProductExpiredByProductID($row->id, $warehouse_id);
						}else{
							$product_expiries = false;
						}
						if($enable_bom) {
							$bom_typies = $this->sales_model->getTypeBoms($row->id);
							$row->bom_type = $bom_typies[0]->bom_type;
						}else{
							$bom_typies = false;
						}
						$row->real_unit_price = $row->price;
						$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
						if($this->Settings->product_serial == 1){
							$product_serials = $this->sales_model->getProductSerialDetailsByProductId($row->id, $warehouse_id);
						}
						$pr[] = array('id' => ($c + $b), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id,
								'row' => $row, 'product_serials' => $product_serials, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options,
								'product_expiries' => $product_expiries, 'product_formulations' => $product_formulations,
								'enable_bom' => $enable_bom,'bom_typies' => $bom_typies, 'category'=> $category->name,'salesmans' => $salesmans,'product_commission'=>$product_commission);
						$b++;
					}
					$ct[] = array('id' => $r, 'item_id' => $category->id, 'label' => $category->name . " (" . $category->code . ")", 'products' =>$pr);
                	$r++;
				}				
			}
			if($ct){
				$this->cus->send_json($ct);
			}else{
				$this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
			}
			
		}else{
			$this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
		}
	}

    public function suggestions()
    {
		$term = $this->input->get('term', true);
		$warehouse_id = $this->input->get('warehouse_id', true);
		$customer_id = $this->input->get('customer_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->sales_model->getProductNames($sr, $warehouse_id, 20);
        if(in_array('bom',$this->config->item('product_types'))) {
        	$enable_bom = true;
        }else{
        	$enable_bom  = false;
        }
		if($this->config->item('saleman_commission') && $this->Settings->product_commission == 1){
			$salesmans = $this->site->getSalemans();
			$product_commission = true;
		}else{
			$salesmans = false;
			$product_commission = false;
		}
        if ($rows) {
			if($this->Settings->product_additional == 1){
                $additional_products = $this->sales_model->getProductAdditionals();
            }else{
                $additional_products = false;
            }
			if($this->Settings->product_formulation == 1){
                $product_formulations = $this->sales_model->getProductFormulation();
            }else{
                $product_formulations = false;
            }
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                unset($row->details, $row->product_details, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option = false;
                $row->quantity = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty = 1;
                $row->discount = '0';
				if(isset($row->serial)){
					if($row->serial_price > 0){
						$row->price = $row->serial_price;
					}
				}else{
					$row->serial = '';
				}
                $options = $this->sales_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->sales_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = FALSE;
                }
                $row->option = $option_id;
				$pis = $this->site->getProductBalanceQuantity($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    $row->quantity += $pis->quantity_balance;
                }
				// Product Currency
				$currencies = false;
				if($this->config->item('product_currency')==true){
					$currencies = $this->site->getAllCurrencies();
					foreach($currencies as $currency){
						if($currency->code == $row->currency_code){
							$currency->rate = $row->currency_rate;
						}
					}
					$row->price = $row->price * ($row->currency_rate);
					$row->real_currency_rate = $row->currency_rate;
				}
				
				$rental_id = $this->input->get("rental_id", true);
				$room_rent = false;
				if($this->config->item('room_rent') && $rental_id > 0){
					$rental_item = $this->sales_model->getRentalItem($rental_id, $row->id);
					$row->old_number = (double)$rental_item->old_number;
					$room_rent = true;
				}
				
				$sale_order = $this->sales_model->getSaleOrderByApproval($row->id);
				$row->quantity -= $sale_order->quantity;
                $row->cost = $row->cost;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
				$row->comment = '';
				$product_fress = false;
				if($this->config->item('product_promotions')==true){
					$product_promotions = $this->sales_model->getProductPromotions($row->id,$customer_id);
					if($product_promotions){
						$product_pro_qty = 1;
						if($row->base_unit <> $row->unit){
							$product_pro_unit = $this->site->getProductUnit($row->id,$row->unit);
							$product_pro_qty = $product_pro_unit->unit_qty;
						}
						foreach($product_promotions as $product_promotion){
							if($product_pro_qty >= $product_promotion->min_qty && $product_pro_qty <= $product_promotion->max_qty){
								$product_fress[] = array(
									'product_id' => $product_promotion->product_id,
									'product_name' => $product_promotion->product_name .' ('.$product_promotion->product_code.')',
									'product_quantity' => $product_promotion->free_qty,
								);
							}
						}
					}
				}else{
					$product_promotions = false;
				}
                $row->product_frees = $product_fress;
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getComboProducts($row->id);
                }
				if ($row->promotion && date('Y-m-d') >= $row->start_date && date('Y-m-d') <= $row->end_date) {
					$row->discount = (100-(($row->promo_price / $row->price) * 100)).'%';
                }else if($this->Settings->customer_price == 1 && $customer_price = $this->sales_model->getCustomerPrice($row->id,$customer_id)){
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
				if($customer_group){
					$row->price = $row->price + (($row->price * $customer_group->percent) / 100);
				}
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);				
				if($this->Settings->product_expiry == '1'){
					$product_expiries = $this->site->getProductExpiredByProductID($row->id, $warehouse_id);
					if($product_expiries){
						foreach($product_expiries as $product_expirie){
							if($product_expirie->quantity > 0){
								$row->expired = $product_expirie->expiry;
								break; 
							}
						}
					}
					
				}else{
					$product_expiries = false;
				}
				if($enable_bom) {
					$bom_typies = $this->sales_model->getTypeBoms($row->id);
					if (isset($bom_typies) && $bom_typies != false) {
						$row->bom_type = $bom_typies[0]->bom_type;
					}else{
						$row->bom_type = '';
					}
				}else{
					$bom_typies = false;
				}
				$row->real_unit_price = $row->price;
				$row->unit_price = $row->price;
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				if($this->Settings->product_serial == 1){
					$product_serials = $this->sales_model->getProductSerialDetailsByProductId($row->id, $warehouse_id);
				}else{
					$product_serials = false;
				}
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id,
						'row' => $row, 'product_serials' => $product_serials, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options,
						'product_expiries' => $product_expiries, 'product_formulations' => $product_formulations, 'additional_products' => $additional_products,
						'enable_bom' => $enable_bom, 'bom_typies' => $bom_typies, 'currencies' => $currencies, 'product_promotions' => $product_promotions, 'room_rent' => $room_rent,
						'salesmans' => $salesmans,'product_commission'=> $product_commission
					);
                $r++;
            }
			$this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

	public function suggestionsDigital()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->sales_model->getProductDigitalItems($sr, $warehouse_id);

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
                $row->serial = '';
                $options = $this->sales_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->sales_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = FALSE;
                }
                $row->option = $row->option_id;
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
                    }
                }
				// SO Qty
				$sale_order = $this->sales_model->getSaleOrderByApproval($row->id);
				$row->quantity -= $sale_order->quantity;
                $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
                $row->real_unit_price = $row->price;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment = '';
				$row->parent_id = $term;
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getComboProducts($row->id);
                }
                if ($row->promotion && date('Y-m-d') >= $row->start_date && date('Y-m-d') <= $row->end_date) {
					$row->discount = (100-(($row->promo_price / $row->price) * 100)).'%';
                } elseif ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                } elseif ($warehouse->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                }
				$row->real_unit_price = $row->price;
				$row->unit_price = $row->price;
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id,
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $r++;
            }
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function gift_cards()
    {
        $this->cus->checkPermissions();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('gift_cards')));
        $meta = array('page_title' => lang('gift_cards'), 'bc' => $bc);
        $this->core_page('sales/gift_cards', $meta, $this->data);
    }

    public function getGiftCards()
    {

        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('gift_cards') . ".id as id, card_no, value, balance, CONCAT(" . $this->db->dbprefix('users') . ".last_name, ' ', " . $this->db->dbprefix('users') . ".first_name) as created_by, customer, expiry", false)
            ->join('users', 'users.id=gift_cards.created_by', 'left')
            ->from("gift_cards")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('sales/view_gift_card/$1') . "' class='tip' title='" . lang("view_gift_card") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> <a href='" . site_url('sales/topup_gift_card/$1') . "' class='tip' title='" . lang("topup_gift_card") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-dollar\"></i></a> <a href='" . site_url('sales/edit_gift_card/$1') . "' class='tip' title='" . lang("edit_gift_card") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_gift_card") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_gift_card/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function view_gift_card($id = null)
    {
        $this->data['page_title'] = lang('gift_card');
        $gift_card = $this->site->getGiftCardByID($id);
        $this->data['gift_card'] = $this->site->getGiftCardByID($id);
        $this->data['customer'] = $this->site->getCompanyByID($gift_card->customer_id);
        $this->data['topups'] = $this->sales_model->getAllGCTopups($id);
        $this->load->view($this->theme . 'sales/view_gift_card', $this->data);
    }

    public function topup_gift_card($card_id)
    {
        $this->cus->checkPermissions('add_gift_card', true);
        $card = $this->site->getGiftCardByID($card_id);
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|integer|required');

        if ($this->form_validation->run() == true) {
            $data = array('card_id' => $card_id,
                'amount' => $this->input->post('amount'),
                'date' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('user_id'),
            );
            $card_data['balance'] = ($this->input->post('amount')+$card->balance);
            // $card_data['value'] = ($this->input->post('amount')+$card->value);
            if ($this->input->post('expiry')) {
                $card_data['expiry'] = $this->cus->fld(trim($this->input->post('expiry')));
            }
        } elseif ($this->input->post('topup')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/gift_cards");
        }

        if ($this->form_validation->run() == true && $this->sales_model->topupGiftCard($data, $card_data)) {
            $this->session->set_flashdata('message', lang("topup_added"));
            redirect("sales/gift_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['card'] = $card;
            $this->data['page_title'] = lang("topup_gift_card");
            $this->load->view($this->theme . 'sales/topup_gift_card', $this->data);
        }
    }

    public function validate_gift_card($no)
    {
        //$this->cus->checkPermissions();
        if ($gc = $this->site->getGiftCardByNO($no)) {
            if ($gc->expiry) {
                if ($gc->expiry >= date('Y-m-d')) {
                    $this->cus->send_json($gc);
                } else {
                    $this->cus->send_json(false);
                }
            } else {
                $this->cus->send_json($gc);
            }
        } else {
            $this->cus->send_json(false);
        }
    }

    public function add_gift_card()
    {
        $this->cus->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|is_unique[gift_cards.card_no]|required');
        $this->form_validation->set_rules('value', lang("value"), 'required');

        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer = $customer_details ? $customer_details->company : null;
            $data = array('card_no' => $this->input->post('card_no'),
				'date' => ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s')),
				'value' => $this->input->post('value'),
                'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer' => $customer,
                'balance' => $this->input->post('value'),
                'expiry' => $this->input->post('expiry') ? $this->cus->fsd($this->input->post('expiry')) : null,
                'created_by' => $this->session->userdata('user_id'),
            );
            $sa_data = array();
            $ca_data = array();
            if ($this->input->post('staff_points')) {
                $sa_points = $this->input->post('sa_points');
                $user = $this->site->getUser($this->input->post('user'));
                if ($user->award_points < $sa_points) {
                    $this->session->set_flashdata('error', lang("award_points_wrong"));
                    redirect("sales/gift_cards");
                }
                $sa_data = array('user' => $user->id, 'points' => ($user->award_points - $sa_points));
            } elseif ($customer_details && $this->input->post('use_points')) {
                $ca_points = $this->input->post('ca_points');
                if ($customer_details->award_points < $ca_points) {
                    $this->session->set_flashdata('error', lang("award_points_wrong"));
                    redirect("sales/gift_cards");
                }
                $ca_data = array('customer' => $this->input->post('customer'), 'points' => ($customer_details->award_points - $ca_points));
            }
            // $this->cus->print_arrays($data, $ca_data, $sa_data);
        } elseif ($this->input->post('add_gift_card')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/gift_cards");
        }

        if ($this->form_validation->run() == true && $this->sales_model->addGiftCard($data, $ca_data, $sa_data)) {
            $this->session->set_flashdata('message', lang("gift_card_added"));
            redirect("sales/gift_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['users'] = $this->sales_model->getStaff();
            $this->data['page_title'] = lang("new_gift_card");
            $this->load->view($this->theme . 'sales/add_gift_card', $this->data);
        }
    }

    public function edit_gift_card($id = null)
    {
        $this->cus->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|required');
        $gc_details = $this->site->getGiftCardByID($id);
        if ($this->input->post('card_no') != $gc_details->card_no) {
            $this->form_validation->set_rules('card_no', lang("card_no"), 'is_unique[gift_cards.card_no]');
        }
        $this->form_validation->set_rules('value', lang("value"), 'required');
        //$this->form_validation->set_rules('customer', lang("customer"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $gift_card = $this->site->getGiftCardByID($id);
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer = $customer_details ? $customer_details->company : null;
            $data = array('card_no' => $this->input->post('card_no'),
                'value' => $this->input->post('value'),
                'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer' => $customer,
                'balance' => ($this->input->post('value') - $gift_card->value) + $gift_card->balance,
                'expiry' => $this->input->post('expiry') ? $this->cus->fsd($this->input->post('expiry')) : null,
            );
        } elseif ($this->input->post('edit_gift_card')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/gift_cards");
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateGiftCard($id, $data)) {
            $this->session->set_flashdata('message', lang("gift_card_updated"));
            redirect("sales/gift_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['gift_card'] = $this->site->getGiftCardByID($id);
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_gift_card', $this->data);
        }
    }

    public function sell_gift_card()
    {
        $this->cus->checkPermissions('gift_cards', true);
        $error = null;
        $gcData = $this->input->get('gcdata');
        if (empty($gcData[0])) {
            $error = lang("value") . " " . lang("is_required");
        }
        if (empty($gcData[1])) {
            $error = lang("card_no") . " " . lang("is_required");
        }

        $customer_details = (!empty($gcData[2])) ? $this->site->getCompanyByID($gcData[2]) : null;
        $customer = $customer_details ? $customer_details->company : null;
        $data = array('card_no' => $gcData[0],
            'value' => $gcData[1],
            'customer_id' => (!empty($gcData[2])) ? $gcData[2] : null,
            'customer' => $customer,
            'balance' => $gcData[1],
            'expiry' => (!empty($gcData[3])) ? $this->cus->fsd($gcData[3]) : null,
            'created_by' => $this->session->userdata('user_id'),
        );

        if (!$error) {
            if ($this->sales_model->addGiftCard($data)) {
                $this->cus->send_json(array('result' => 'success', 'message' => lang("gift_card_added")));
            }
        } else {
            $this->cus->send_json(array('result' => 'failed', 'message' => $error));
        }

    }

    public function get_promotion_product()
    {
        $product_id = $this->input->get('product_id');
		$customer_id = $this->input->get('customer_id');
		if($product_id && $customer_id){
			$products = $this->sales_model->getPromotionProductByProId($product_id,$customer_id);
			echo json_encode($products);
		}
		return false;
    }

    public function delete_gift_card($id = null)
    {
        $this->cus->checkPermissions();

        if ($this->sales_model->deleteGiftCard($id)) {
            echo lang("gift_card_deleted");
        }
    }

    public function gift_card_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    $this->cus->checkPermissions('delete_gift_card');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteGiftCard($id);
                    }
                    $this->session->set_flashdata('message', lang("gift_cards_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('gift_cards'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('card_no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('value'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->site->getGiftCardByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->card_no);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->value);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->customer);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'gift_cards_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_gift_card_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function get_award_points($id = null)
    {
        $this->cus->checkPermissions('index');

        $row = $this->site->getUser($id);
        $this->cus->send_json(array('sa_points' => $row->award_points));
    }

    public function sale_by_csv()
    {
        $this->cus->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so',$biller_id);
            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');

            $total_items = $this->input->post('total_items');
            $sale_status = $this->input->post('sale_status');
            $payment_status = $this->input->post('payment_status');
            $payment_term = $this->input->post('payment_term');
			$payment_term_info = $this->sales_model->getPaymentTermsByID($payment_term);
			if($payment_term_info){
				if($payment_term_info->term_type=='end_month'){
					$due_date = date("Y-m-t", strtotime($date));
				}else{
					$due_date =  date('Y-m-d', strtotime('+' . $payment_term_info->due_day . ' days', strtotime($date)));
				}
			}else{
				$due_date = null;
			}
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->cus->clear_tags($this->input->post('note'));
            $staff_note = $this->cus->clear_tags($this->input->post('staff_note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("sales/sale_by_csv");
                }

                $csv = $this->upload->file_name;
                $data['attachment'] = $csv;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'net_unit_price', 'quantity', 'variant', 'item_tax_rate', 'discount', 'serial');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {

                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_price']) && isset($csv_pr['quantity'])) {

                        if ($product_details = $this->sales_model->getProductByCode($csv_pr['code'])) {

                            if ($csv_pr['variant']) {
                                $item_option = $this->sales_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $product_details->name . " - " . $csv_pr['variant'] . " ). " . lang("line_no") . " " . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            } else {
                                $item_option = json_decode('{}');
                                $item_option->id = null;
                            }

                            $item_id = $product_details->id;
                            $item_type = $product_details->type;
                            $item_code = $product_details->code;
                            $item_name = $product_details->name;
                            $item_net_price = $this->cus->formatDecimalRaw($csv_pr['net_unit_price']);
                            $item_quantity = $csv_pr['quantity'];
                            $item_tax_rate = $csv_pr['item_tax_rate'];
                            $item_discount = $csv_pr['discount'];
                            $item_serial = $csv_pr['serial'];

                            if (isset($item_code) && isset($item_net_price) && isset($item_quantity)) {
                                $product_details = $this->sales_model->getProductByCode($item_code);

                                if (isset($item_discount)) {
                                    $discount = $item_discount;
                                    $dpos = strpos($discount, $percentage);
                                    if ($dpos !== false) {
                                        $pds = explode("%", $discount);
                                        $pr_discount = $this->cus->formatDecimalRaw(((($this->cus->formatDecimalRaw($item_net_price)) * (Float) ($pds[0])) / 100), 4);
                                    } else {
                                        $pr_discount = $this->cus->formatDecimalRaw($discount);
                                    }
                                } else {
                                    $pr_discount = 0;
                                }
                                $item_net_price = $this->cus->formatDecimalRaw(($item_net_price - $pr_discount), 4);
                                $pr_item_discount = $this->cus->formatDecimalRaw(($pr_discount * $item_quantity), 4);
                                $product_discount += $pr_item_discount;

                                if (isset($item_tax_rate) && $item_tax_rate != 0) {

                                    if ($tax_details = $this->sales_model->getTaxRateByName($item_tax_rate)) {
                                        $pr_tax = $tax_details->id;
                                        if ($tax_details->type == 1) {

                                            $item_tax = $this->cus->formatDecimalRaw((($item_net_price) * $tax_details->rate) / 100, 4);
                                            $tax = $tax_details->rate . "%";

                                        } elseif ($tax_details->type == 2) {
                                            $item_tax = $this->cus->formatDecimalRaw($tax_details->rate);
                                            $tax = $tax_details->rate;
                                        }
                                        $pr_item_tax = $this->cus->formatDecimalRaw(($item_tax * $item_quantity), 4);
                                    } else {
                                        $this->session->set_flashdata('error', lang("tax_not_found") . " ( " . $item_tax_rate . " ). " . lang("line_no") . " " . $rw);
                                        redirect($_SERVER["HTTP_REFERER"]);
                                    }

                                } elseif ($product_details->tax_rate) {

                                    $pr_tax = $product_details->tax_rate;
                                    $tax_details = $this->site->getTaxRateByID($pr_tax);
                                    if ($tax_details->type == 1) {

                                        $item_tax = $this->cus->formatDecimalRaw((($item_net_price) * $tax_details->rate) / 100, 4);
                                        $tax = $tax_details->rate . "%";

                                    } elseif ($tax_details->type == 2) {

                                        $item_tax = $this->cus->formatDecimalRaw($tax_details->rate);
                                        $tax = $tax_details->rate;

                                    }
                                    $pr_item_tax = $this->cus->formatDecimalRaw(($item_tax * $item_quantity), 4);

                                } else {
                                    $item_tax = 0;
                                    $pr_tax = 0;
                                    $pr_item_tax = 0;
                                    $tax = "";
                                }
                                $product_tax += $pr_item_tax;
                                $subtotal = $this->cus->formatDecimalRaw((($item_net_price * $item_quantity) + $pr_item_tax), 4);
                                $unit = $this->site->getUnitByID($product_details->unit);

                                $products[] = array(
                                    'product_id' => $product_details->id,
                                    'product_code' => $item_code,
                                    'product_name' => $item_name,
                                    'product_type' => $item_type,
                                    'option_id' => $item_option->id,
                                    'net_unit_price' => $item_net_price,
                                    'quantity' => $item_quantity,
                                    'product_unit_id' => $product_details->unit,
                                    'product_unit_code' => $unit->code,
                                    'unit_quantity' => $item_quantity,
                                    'warehouse_id' => $warehouse_id,
                                    'item_tax' => $pr_item_tax,
                                    'tax_rate_id' => $pr_tax,
                                    'tax' => $tax,
                                    'discount' => $item_discount,
                                    'item_discount' => $pr_item_discount,
                                    'subtotal' => $subtotal,
                                    'serial_no' => $item_serial,
                                    'unit_price' => $this->cus->formatDecimalRaw(($item_net_price + $item_tax), 4),
                                    'real_unit_price' => $this->cus->formatDecimalRaw(($item_net_price + $item_tax + $pr_discount), 4),
                                );

                                $total += $this->cus->formatDecimalRaw(($item_net_price * $item_quantity), 4);
                            }

                        } else {
                            $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $csv_pr['code'] . " ). " . lang("line_no") . " " . $rw);
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                        $rw++;
                    }

                }
            }

            if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
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
            $total_discount = $this->cus->formatDecimalRaw(($order_discount + $product_discount), 4);

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->cus->formatDecimalRaw($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->cus->formatDecimalRaw(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->cus->formatDecimalRaw(($product_tax + $order_tax), 4);
            $grand_total = $this->cus->formatDecimalRaw(($total + $total_tax + $this->cus->formatDecimalRaw($shipping) - $order_discount), 4);
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->cus->formatDecimalRaw($shipping),
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'sale_status' => $sale_status,
                'payment_status' => $payment_status,
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'paid' => 0,
                'created_by' => $this->session->userdata('user_id'),
            );

            if ($payment_status == 'paid') {

                $payment = array(
                    'date' => $date,
                    'reference_no' => $this->site->getReference('pay',$biller_id),
                    'amount' => $grand_total,
                    'paid_by' => 'cash',
                    'cheque_no' => '',
                    'cc_no' => '',
                    'cc_holder' => '',
                    'cc_month' => '',
                    'cc_year' => '',
                    'cc_type' => '',
                    'created_by' => $this->session->userdata('user_id'),
                    'note' => lang('auto_added_for_sale_by_csv') . ' (' . lang('sale_reference_no') . ' ' . $reference . ')',
                    'type' => 'received',
                );

            } else {
                $payment = array();
            }

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

            //$this->cus->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang("sale_added"));
            redirect("sales");
        } else {

            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['slnumber'] = $this->site->getReference('so',$biller_id);

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('add_sale_by_csv')));
            $meta = array('page_title' => lang('add_sale_by_csv'), 'bc' => $bc);
            $this->core_page('sales/sale_by_csv', $meta, $this->data);

        }
    }

    public function update_status($id)
    {

        $this->form_validation->set_rules('status', lang("sale_status"), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note = $this->cus->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {

            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            $this->data['returned'] = FALSE;
            if ($this->data['inv']->sale_status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned'] = TRUE;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'sales/update_status', $this->data);

        }
    }

	public function assign_to()
    {
		if($this->input->post("assign_to")){
			$biller_id = $this->input->post('biller');
			$reference_no = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('assign',$biller_id);
			$data = array(
						"reference_no" => $reference_no,
						"date" => $this->cus->fld($this->input->post('date')),
						"assign_to"=> $this->input->post('assign_to'),
						"note" => $this->input->post('note'),
						"status" => 'assigned',
						"biller_id" => $biller_id,
						"created_by" => $this->session->userdata('user_id'),
					);
			$result = $this->db->insert("assign_sales",$data);
			$assign_id = $this->db->insert_id();

			if($result){
				$data_items = array();
				foreach($this->input->post("sale_id") as $sale){
					$data_items[] = array(
									"assign_id" => $assign_id,
									"sale_id" => $sale,
								);
				}
				$result = $this->db->insert_batch("assign_sale_items",$data_items);
			}
			if($result){
				$this->session->set_flashdata('message', lang("sales_already_assigned")." ".$reference_no);
				redirect($_SERVER["HTTP_REFERER"]);
			}
		}else{

			$sales = array();
			$assign_to = $this->input->get("assign_to");
			foreach($assign_to as $sale){
				$row = $this->sales_model->getSaleByID($sale);
				$assign_row = $this->sales_model->getAssignItemBySaleID($sale);
				if($assign_row){
					header('HTTP/1.0 400 Bad error');
					echo lang("sale_already_assigned");
					die();
				}
				if($row->paid > 0){
					header('HTTP/1.0 400 Bad error');
					echo lang("sale_already_paid");
					die();
				}
				$sales[] = $sale;
			}
			$this->data['id'] = $id;
			$this->data['sales'] = $sales;
			$this->data['salemans'] = $this->site->getSalemans();
			$this->data['billers'] = $this->site->getAllCompanies('biller');

			$this->data['allUsers'] = $this->site->getAllUsers();
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			echo json_encode($this->load->view($this->theme . 'sales/assign_to', $this->data, true));
		}
    }

    public function edit_assign_sale($id = null)
    {
		if($this->input->post("edit_assign_sale")){
			$biller_id = $this->input->post('biller');
			$reference_no = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('assign',$biller_id);
			$data = array(
						"reference_no" => $reference_no,
						"date" => $this->cus->fld($this->input->post('date')),
						"assign_to"=> $this->input->post('assign_to'),
						"note" => $this->input->post('note'),
						"biller_id" => $biller_id,
					);
			$result = $this->db->where("id",$id)->update("assign_sales",$data);
			if($result){
				$this->session->set_flashdata('message', lang("assign_sale_updated")." ". $reference_no);
				redirect($_SERVER["HTTP_REFERER"]);
			}
		}else{

			$this->data['id'] = $id;
			$this->data['assign_sale'] = $this->sales_model->getAssignSaleById($id);
			$this->data['salemans'] = $this->site->getSalemans();
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'sales/edit_assign_sale', $this->data);
		}
    }

	public function assign_sales()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('assign_sales')));
        $meta = array('page_title' => lang('assign_sales'), 'bc' => $bc);
        $this->core_page('sales/assign_sales', $meta, $this->data);
	}

	public function getAssigns($warehouse_id = null)
    {

		$delete_link = "<a href='#' class='po' title='<b>" . lang("delete_assign_sale") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_assign_sale/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_assign_sale') . "</a>";

		$add_cash_link = anchor('sales/add_cash/$1', '<i class="fa fa-money"></i> ' . lang('add_cash'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_assign_sale = anchor('sales/edit_assign_sale/$1', '<i class="fa fa-money"></i> ' . lang('edit_assign_sale'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');

        $action = '<div class="text-center">
			<div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
				<ul class="dropdown-menu pull-right" role="menu">
					<li>'.$add_cash_link.'</li>
					<li>'.$edit_assign_sale.'</li>
					<li>'.$delete_link.'</li>
				</ul>
			</div></div>';

        $this->load->library('datatables');

        $this->datatables->select("assign_sales.id as id,
				DATE_FORMAT(date, '%Y-%m-%d %T') as date,
				upper(creates.username) as created_by,
				upper(cus_users.username) as assign_to,
				reference_no,
				companies.company,
				note,
				status")
                ->from('assign_sales')
				->join('companies','companies.id = assign_sales.biller_id','left')
				->join('users','assign_sales.assign_to = users.id','left')
				->join('users as creates','assign_sales.created_by = creates.id','left');
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('assign_sales.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('assign_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

	public function delete_assign_sale($id = null)
    {
        $this->cus->checkPermissions('delete');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

		$sale_assign = $this->sales_model->getAssignSaleById($id);

        if ($this->db->where('id',$id)->delete("assign_sales")) {
			if ($this->input->is_ajax_request()) {
                echo lang("assign_sale_deleted")." ".$sale_assign->reference_no;
                die();
            }
            $this->session->set_flashdata('message', lang("assign_sale_deleted")." ".$sale_assign->reference_no);
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

	public function add_cash($id = null)
    {
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');

        if ($this->input->post("sale_id") == true) {

			$sales = $this->input->post("sale_id");
			$amount = $this->input->post('amount');
			$paid_by = $this->input->post('paid_by');
			$date = $this->input->post('date');

			$total_amount = 0;
			$payment = array();
			foreach($sales as $i => $sale){
				$row = $this->sales_model->getSaleByID($sale);
				$total_amount += $amount[$i];

				if($amount[$i] > 0){
					if($amount[$i] + $row->paid > $row->grand_total){
						$this->session->set_flashdata('error', lang("amount_greater_than_grand_total"));
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$row->biller_id);
					$payment[] = array(
						'date' => $this->cus->fld($date),
						'sale_id' => $sale,
						'reference_no' => $reference_no,
						'amount' => $amount[$i],
						'paid_by' => $paid_by[$i],
						'note' => $this->input->post('note'),
						'created_by' => $this->session->userdata('user_id'),
						'type' => 'received',
					);
				}
			}

			if($total_amount <= 0){
				$this->session->set_flashdata('error', lang("amount_zero_cannot_add"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
			$payments = $this->db->where("id",$id)->update("assign_sales", array("status"=>"cleared"));
			$payments = $this->sales_model->addPaymentMulti($payment);

			if($payments){
				$this->session->set_flashdata('message', lang("assign_sale_add_cash")." ".$reference_no);
				redirect($_SERVER["HTTP_REFERER"]);
			}

        } else {
			$this->data['id'] = $id;
			$this->data['allAssigns'] = $this->sales_model->getAllAssignItemsByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/add_cash', $this->data);
        }
    }

	public function get_company()
	{
		$customer_id = $this->input->get('customer');
		$customer = $this->site->getCompanyByID($customer_id);
		$saleman = $this->site->getUser($customer->saleman_id);
		$installment = false;
		if($this->Settings->installment==1){
			$sale_id = $this->input->get('sale_id');
			$installment = $this->sales_model->getInstallmentByCustomerID($customer_id,$sale_id);
		}
		$data = array('saleman_id'=>$customer->saleman_id, 'saleman_commission'=>($saleman ? $saleman->saleman_commission : ''), 'installment'=> json_encode($installment));
		echo json_encode($data);
	}
	
	public function get_credit()
	{
		$customer_id = $this->input->get('customer');
		$customer = $this->site->getCompanyByID($customer_id);
		$credit = '0';
		if($customer->credit_day > 0 || $customer->credit_amount > 0){
			if($customer->credit_day > 0){
				$credit_balance = $this->sales_model->getCreditLimit($customer_id,$customer->credit_day);
				if($credit_balance->balance > 0){
					$credit = $credit_balance->balance;
				}
			}
			if($customer->credit_amount > 0){
				$credit_balance = $this->sales_model->getCreditLimit($customer_id);
				if($credit_balance->balance >= $customer->credit_amount){
					$credit = $credit_balance->balance;
				}
			}
		}
		echo json_encode($credit);

	}

	public function assign_saleman()
    {
		if($this->input->post("submit"))
		{
			$sales = $this->input->post("sale_id");
			$saleman = $this->site->getUser($this->input->post('saleman_id'));
			foreach($sales as $i => $sale){
				$data[] = array(
								"id" => $sales[$i],
								"saleman_id" => $saleman->id,
								"saleman" => $saleman->last_name.' '.$saleman->first_name,
							);
			}
			$result = $this->db->update_batch("sales", $data, "id");
			if($result){
				$this->session->set_flashdata('message', lang("assign_saleman"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		}else{
			$sales = array();
			$assign_saleman = $this->input->get("assign_saleman");
			foreach($assign_saleman as $sale){
				$sales[] = $sale;
			}
			$this->data['id'] = $id;
			$this->data['sales'] = $sales;
			$this->data['salemans'] = $this->site->getSalemans();
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			echo json_encode($this->load->view($this->theme . 'sales/assign_saleman', $this->data, true));
		}
    }

	public function payment_term()
	{
		$term_id = $this->input->get("term_id");
		if($term_id){
			$payment_term = $this->sales_model->getPaymentTermsByID($term_id);
			echo json_encode(
								array(
									'discount_type'=>$payment_term->discount_type,
									'discount'=>$payment_term->discount)
								);
		}
	}

	public function get_deposit($cid = false)
	{
		$cid = $this->input->get("customer");
		if($cid){
			$row = $this->sales_model->getDepositByCId($cid);
			echo json_encode($row);
		}
	}

	public function get_project()
	{
		$id = $this->input->get("biller");
		$project_id = $this->input->get("project");
		$rows = $this->site->getAllProjectByBillerID($id);
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$project = json_decode($user->project_ids);
		$pl = array(lang('select')." ".lang('project'));
		if ($this->Owner || $this->Admin || $project[0] === 'all') {
			foreach($rows as $row){
				$pl[$row->id] = $row->name;
			}
		}else{
			foreach($rows as $row){
				if(in_array($row->id, $project)){
					$pl[$row->id] = $row->name;
				}
			}
		}
		$opt = form_dropdown('project', $pl, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="project" class="form-control"');
		echo json_encode(array("result" => $opt));
	}
	
	public function add_print()
	{
		$transaction = $this->input->get('transaction');
		$transaction_id = $this->input->get('transaction_id');
		$reference_no = $this->input->get('reference_no');
		if($transaction && $transaction_id){
			$data = array(
					'transaction' => $transaction,
					'transaction_id' => $transaction_id,
					'reference_no' => $reference_no,
					'print_by' => $this->session->userdata('user_id'),
					'print_date' => date('Y-m-d H:i:s'),	
		
			);
			if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print'] || $this->Settings->limit_print=='2' || $this->Settings->limit_print=='0' || ($this->Settings->limit_print=='1' && !$this->site->checkPrint($transaction,$transaction_id))){
				$this->site->addPrint($data);
			}
		}
	}
	
	//========Fuel Sales===========//
	
	public function fuel_sales($warehouse_id = null,$biller_id = NULL)
	{
		$this->cus->checkPermissions("fuel_sale-index");
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		if($biller_id == 0){
			$biller_id = null;
		}
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('fuel_sales')));
        $meta = array('page_title' => lang('fuel_sales'), 'bc' => $bc);
        $this->core_page('sales/fuel_sales', $meta, $this->data);
	}
	
	public function add_fuel_sale()
	{
		$this->cus->checkPermissions('fuel_sale-add', true);
		$this->form_validation->set_rules('saleman_id', lang("saleman"), 'required');
		$this->form_validation->set_rules('time_id', lang("time"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post("biller");
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = ($biller_details->name?$biller_details->name:$biller_details->company);
			$warehouse_id = $this->input->post("warehouse");
			$note = $this->input->post('note');
			$saleman_id = $this->input->post("saleman_id");
			$project_id = $this->input->post("project");
			$time_id = $this->input->post('time_id');
			$saleman_details = $this->site->getUser($saleman_id);
			$saleman = $saleman_details->last_name.' '.$saleman_details->first_name;
			$kh_rate = $this->input->post("kh_rate");
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('fuel',$biller_id);
			if ($this->Owner || $this->Admin || $this->GP['sales-fuel_sale-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$total = 0;
			$tanks = isset($_POST['tank_id']) ? sizeof($_POST['tank_id']) : 0;
			for ($r = 0; $r < $tanks; $r++) {
				$tank_id = $_POST['tank_id'][$r];
				$nozzle_id = $_POST['nozzle_id'][$r];
				$nozzle_no = $_POST['nozzle_no'][$r];
				$product_id = $_POST['product_id'][$r];
				$start_no = $_POST['start_no'][$r];
				$end_no = $_POST['end_no'][$r];
				$unit_price = $_POST['unit_price'][$r];
				$using_qty = $_POST['using_qty'][$r];
				$quantity = $_POST['quantity'][$r];
				$subtotal = $_POST['subtotal'][$r];
				$customer_qty = $_POST['customer_qty'][$r];
				$customer_amount = $_POST['customer_amount'][$r];
				if(($quantity > 0 || $using_qty > 0 || $customer_qty > 0) && $nozzle_id > 0){
					$items[] = array(
						'tank_id' => $tank_id,
						'nozzle_id' => $nozzle_id,
						'nozzle_no' => $nozzle_no,
						'product_id' => $product_id,
						'nozzle_start_no' => $start_no,
						'nozzle_end_no' => $end_no,
						'unit_price' => $unit_price,
						'quantity' => $quantity,
						'using_qty' => $using_qty,
						'customer_qty' => $customer_qty,
						'customer_amount' => $customer_amount,
						'subtotal' => $subtotal
					);
					$total += $subtotal;
				}
				
				if($using_qty > 0){
					$product_details = $this->site->getProductByID($product_id);
					$stockmoves[] = array(
						'transaction' => 'FuelSale',
						'product_id' => $product_details->id,
						'product_code' => $product_details->code,
						'quantity' => (-1)*$using_qty,
						'unit_quantity' => 1,
						'unit_id' => $product_details->unit,
						'warehouse_id' => $warehouse_id,
						'date' => $date,
						'real_unit_cost' => $product_details->cost,
						'reference_no' => $reference_no,
						'user_id' => $this->session->userdata('user_id'),
					);
					if($this->Settings->accounting == 1){		
						$productAcc = $this->site->getProductAccByProductId($product_details->id);
						$accTrans[] = array(
							'transaction' => 'FuelSale',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->stock_acc,
							'amount' => -($product_details->cost * $using_qty),
							'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$using_qty.'#'.'Cost: '.$product_details->cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'transaction' => 'FuelSale',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->usage_acc,
							'amount' => ($product_details->cost * $using_qty),
							'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$using_qty.'#'.'Cost: '.$product_details->cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
				}
			}
			$data = array(
				'date' => $date,
				'reference_no'=> $reference_no,
				'biller_id'=> $biller_id,
				'biller'=> $biller,
				'saleman_id'=> $saleman_id,
				'saleman'=> $saleman,
				'warehouse_id'=> $warehouse_id,
				'project_id'=> $project_id,
				'total'=> $total,
				'note' => $note,
				'kh_rate' => $kh_rate,
				'created_by'=> $this->session->userdata('user_id'),
				'time_id' => $time_id
			);
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
                $data['attachment'] = $photo;
            }
			
			if (empty($items)) {
                $this->form_validation->set_rules('tank_order', lang("tank_order"), 'required');
            }
			
			$default_currency = $this->site->getCurrencyByCode($this->Settings->default_currency);
			$currency = $this->site->getCurrencyByCode("USD");
			$currency_khr = $this->site->getCurrencyByCode("KHR");
			
			$credit_amount = 0;
			if(isset($_POST['credit_amount_usd']) || isset($_POST['credit_amount_khr'])){
				$credit_amount_usd = $this->input->post('credit_amount_usd',true);
				$credit_amount += (($credit_amount_usd * $default_currency->rate) / $currency->rate);
				$json_credit_amount[$currency->code] = array(
					"code"=>$currency->code,
					"rate"=> $currency->rate, 
					"amount"=>$credit_amount_usd
				);
				
				$credit_amount_khr = $this->input->post('credit_amount_khr', true);
				$credit_amount += (($credit_amount_khr * $default_currency->rate) / $kh_rate);
				$json_credit_amount[$currency_khr->code] = array(
					"code"=>$currency_khr->code,
					"rate"=> $kh_rate, 
					"amount"=>$credit_amount_khr
				);
				
				
				$data["credit_amount"] = $credit_amount;
				$data["json_credit_amount"] = json_encode($json_credit_amount);
			}
			
			if(isset($_POST['count-money-kh']) || isset($_POST['count-money-usd'])){
				$change_kh = $this->input->post('change_kh',true);
				$change_usd = $this->input->post('change_usd', true);
				$total_kh = $this->input->post('total_KHR',true);
				$total_usd = $this->input->post('total_USD', true);
				$total_cash_open = 0;
								
				if(isset($_POST['count-money-usd'])){
					/*foreach($_POST['count-money-usd'] as $money => $number){
						$total_usd += ($money * $number);
					}*/
					$total_cash += (($total_usd * $default_currency->rate) / $currency->rate);
					$json_total_cash_count[$currency->code] = json_encode($_POST['count-money-usd']);
					$total_cash_open += (($change_usd* $default_currency->rate) / $currency->rate);
					$json_total_cash[$currency->code] = array(
						"code"=>$currency->code,
						"rate"=> $currency->rate, 
						"amount"=>$total_usd
					);
					$json_total_cash_open[$currency->code] = array(
						"code"=>$currency->code,
						"rate"=> $currency->rate, 
						"amount"=>$change_usd
					);
				}
				
				if(isset($_POST['count-money-kh'])){
					
					/*foreach($_POST['count-money-kh'] as $money => $number){
						$total_kh += ($money * $number);
					}*/
					$total_cash += (($total_kh * $default_currency->rate) / $kh_rate);
					$total_cash_open += (($change_kh* $default_currency->rate) / $kh_rate);
					$json_total_cash_count[$currency_khr->code] = json_encode($_POST['count-money-kh']);
					$json_total_cash[$currency_khr->code] = array(
						"code" => $currency_khr->code,
						"rate" => $kh_rate, 
						"amount" => $total_kh
					);
					$json_total_cash_open[$currency_khr->code] = array(
						"code" => $currency_khr->code,
						"rate" => $kh_rate, 
						"amount" => $change_kh
					);
				}

				$data["total_cash"] = $total_cash;
				$data["total_cash_open"] = $total_cash_open;
				$data["json_total_cash_count"] = json_encode($json_total_cash_count);
				$data["json_total_cash"] = json_encode($json_total_cash);
				$data["json_total_cash_open"] = json_encode($json_total_cash_open);
			}
			
		}else if($this->input->post('add_fuel_sale')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
		if ($this->form_validation->run() == true && $this->sales_model->addFuelSale($data, $items, $stockmoves, $accTrans)) {
			$this->session->set_userdata('remove_shls', 1);
			$this->session->set_flashdata('message', lang("fuel_sale_added"));
			redirect('sales/fuel_sales');
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$saleman = $this->input->get("saleman_id", true);
			$saleman_details = $this->site->getUser($saleman);
			$fuel_items = $this->sales_model->getFuelItemsBySaleman($saleman);
			if($fuel_items){
				$c = rand(100000, 9999999);
				foreach ($fuel_items as $fuel_item) {
					$product = $this->site->getProductByID($fuel_item->product_id);
					$row = json_decode('{}');
					$nozzles = $this->sales_model->getTankNozzlesByTankID($fuel_item->tank_id);
					if($nozzles){
						foreach($nozzles as $nozzle){
							$fuel_sale = $this->sales_model->getFuelSaleQuantityItem($fuel_item->tank_id, $nozzle->id);
							if($fuel_sale->quantity > 0){
								$quantity = $fuel_sale->quantity;
							}else{
								$quantity = $nozzle->nozzle_start_no;
							}
							$nozzle->nozzle_start_no = $quantity;
							$nozzle->customer_qty = 0;
							$nozzle->customer_amount = 0;
							$fuel_customer = $this->sales_model->getFuelCustomerNozzleQuantity($saleman,$nozzle->id);
							if($fuel_customer){
								$nozzle->customer_qty = $fuel_customer->quantity;
								$nozzle->customer_amount = $fuel_customer->amount;
							}
						}
					}
					$fuel_old = $this->sales_model->getFuelSaleQuantityItem($fuel_item->tank_id, $fuel_item->id);
					$fuel_cus = $this->sales_model->getFuelCustomerNozzleQuantity($saleman,$fuel_item->id);
					if($fuel_old->quantity <= 0){
						$fuel_old->quantity = $fuel_item->nozzle_start_no;
					}
					$row->id = $fuel_item->tank_id;
					$row->code = $fuel_item->code;
					$row->name = $fuel_item->name;
					$row->nozzle_id = $fuel_item->id;
					$row->product_id = $fuel_item->product_id;
					$row->nozzle_no = $fuel_item->nozzle_no;
					$row->start_no = $fuel_old->quantity;
					$row->customer_qty = ($fuel_cus ? $fuel_cus->quantity : 0);
					$row->customer_amount = ($fuel_cus ? $fuel_cus->amount : 0);
					$row->end_no = 0;
					$row->quantity = 0;
					$row->unit_price = (double)$product->price;
					$row->subtotal = 0;
					$ri = $c;
					$pr[$ri] = array('id' => ($c), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'nozzles' => $nozzles);
					$c++;
				}
				$this->data['fuel_items'] = json_encode($pr);
			}else{
				$this->data['fuel_items'] = false;
			}
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['projects'] = $this->site->getAllProjects();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['salemans'] = $this->site->getSalemans();
			$this->data['times'] = $this->sales_model->getFuelTimes();
			$this->data['tanks'] = $this->site->getTanks();
			$this->data['kh_rate'] = $this->site->getCurrencyByCode("KHR")->rate;
			$this->data['money_changes'] = $saleman_details && $saleman_details->money_change?json_decode($saleman_details->money_change):null;
			$this->data['fuel_time'] = $saleman_details && $saleman_details->fuel_time_id?$saleman_details->fuel_time_id:0;
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => site_url('sales/fuel_sales'), 'page' => lang('fuels')), array('link' => '#', 'page' => lang('add_fuel_sale')));
			$meta = array('page_title' => lang('add_fuel_sale'), 'bc' => $bc);
			$this->core_page('sales/add_fuel_sale', $meta, $this->data);
		}
	}
	
	public function edit_fuel_sale($id = NULL)
	{
		$this->cus->checkPermissions('fuel_sale-edit', true);
		$this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
		$this->form_validation->set_rules('time_id', lang("time"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post("biller");
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = ($biller_details->name?$biller_details->name:$biller_details->company);
			$warehouse_id = $this->input->post("warehouse");
			$saleman_id = $this->input->post("saleman_id");
			$project_id = $this->input->post("project");
			$time_id = $this->input->post('time_id');
			$note = $this->input->post('note');
			$kh_rate = $this->input->post("kh_rate");
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('fuel',$biller_id);
			if ($this->Owner || $this->Admin || $GP['sales-fuel_sale-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$total = 0;
			$tanks = isset($_POST['tank_id']) ? sizeof($_POST['tank_id']) : 0;
			for ($r = 0; $r < $tanks; $r++) {
				$tank_id = $_POST['tank_id'][$r];
				$nozzle_id = $_POST['nozzle_id'][$r];
				$nozzle_no = $_POST['nozzle_no'][$r];
				$product_id = $_POST['product_id'][$r];
				$start_no = $_POST['start_no'][$r];
				$end_no = $_POST['end_no'][$r];
				$unit_price = $_POST['unit_price'][$r];
				$quantity = $_POST['quantity'][$r];
				$using_qty = $_POST['using_qty'][$r];
				$subtotal = $_POST['subtotal'][$r];
				$customer_qty = $_POST['customer_qty'][$r];
				$customer_amount = $_POST['customer_amount'][$r];
				if(($quantity > 0 || $using_qty > 0 || $customer_qty > 0) && $nozzle_id > 0){
					$items[] = array(
						'tank_id' => $tank_id,
						'nozzle_id' => $nozzle_id,
						'nozzle_no' => $nozzle_no,
						'product_id' => $product_id,
						'nozzle_start_no' => $start_no,
						'nozzle_end_no' => $end_no,
						'unit_price' => $unit_price,
						'quantity' => $quantity,
						'using_qty' => $using_qty,
						'customer_qty' => $customer_qty,
						'customer_amount' => $customer_amount,
						'subtotal' => $subtotal
					);
					$total += $subtotal;
				}
				if($using_qty > 0){
					$product_details = $this->site->getProductByID($product_id);
					$stockmoves[] = array(
						'transaction' => 'FuelSale',
						'transaction_id' => $id,
						'product_id' => $product_details->id,
						'product_code' => $product_details->code,
						'quantity' => (-1)*$using_qty,
						'unit_quantity' => 1,
						'unit_id' => $product_details->unit,
						'warehouse_id' => $warehouse_id,
						'date' => $date,
						'real_unit_cost' => $product_details->cost,
						'reference_no' => $reference_no,
						'user_id' => $this->session->userdata('user_id'),
					);
					if($this->Settings->accounting == 1){		
						$productAcc = $this->site->getProductAccByProductId($product_details->id);
						$accTrans[] = array(
							'transaction' => 'FuelSale',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->stock_acc,
							'amount' => -($product_details->cost * $using_qty),
							'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$using_qty.'#'.'Cost: '.$product_details->cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'transaction' => 'FuelSale',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->usage_acc,
							'amount' => ($product_details->cost * $using_qty),
							'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$using_qty.'#'.'Cost: '.$product_details->cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
				}
			}
			$data = array(
						'date' => $date,
						'reference_no'=> $reference_no,
						'biller_id'=> $biller_id,
						'biller'=> $biller,
						'warehouse_id'=> $warehouse_id,
						'project_id'=> $project_id,
						'total'=> $total,
						'kh_rate' => $kh_rate,
						'updated_by'=> $this->session->userdata('user_id'),
						'updated_at'=> date("Y-m-d H:i"),
						'note' => $note,
						'time_id' => $time_id
					);
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
                $data['attachment'] = $photo;
            }
			if (empty($items)) {
                $this->form_validation->set_rules('tank_order', lang("tank_order"), 'required');
            }
			$currency = $this->site->getCurrencyByCode("USD");
			$currency_khr = $this->site->getCurrencyByCode("KHR");
			$default_currency = $this->site->getCurrencyByCode($this->Settings->default_currency);
			
			$credit_amount = 0;
			if(isset($_POST['credit_amount_usd']) || isset($_POST['credit_amount_khr'])){
				$credit_amount_usd = $this->input->post('credit_amount_usd',true);
				$credit_amount += (($credit_amount_usd * $default_currency->rate) / $currency->rate);
				$json_credit_amount[$currency->code] = array(
					"code"=>$currency->code,
					"rate"=> $currency->rate, 
					"amount"=>$credit_amount_usd
				);
				
				$credit_amount_khr = $this->input->post('credit_amount_khr', true);
				$credit_amount += (($credit_amount_khr * $default_currency->rate) / $kh_rate);
				$json_credit_amount[$currency_khr->code] = array(
					"code"=>$currency_khr->code,
					"rate"=> $kh_rate, 
					"amount"=>$credit_amount_khr
				);
				$data["credit_amount"] = $credit_amount;
				$data["json_credit_amount"] = json_encode($json_credit_amount);
			}
			
			if(isset($_POST['count-money-kh']) || isset($_POST['count-money-usd'])){
				$change_kh = $this->input->post('change_kh',true);
				$change_usd = $this->input->post('change_usd', true);
				$total_kh = $this->input->post('total_KHR',true);
				$total_usd = $this->input->post('total_USD', true);
				$total_cash_open = 0;
				
				if(isset($_POST['count-money-usd'])){
					
					/*foreach($_POST['count-money-usd'] as $money => $number){
						$total_usd += ($money * $number);
					}*/
					$total_cash += (($total_usd * $default_currency->rate) / $currency->rate);
					$total_cash_open += (($change_usd* $default_currency->rate) / $currency->rate);
					$json_total_cash_count[$currency->code] = json_encode($_POST['count-money-usd']);
					$json_total_cash[$currency->code] = array(
						"code"=>$currency->code,
						"rate"=> $currency->rate, 
						"amount"=>$total_usd
					);
					$json_total_cash_open[$currency->code] = array(
						"code"=>$currency->code,
						"rate"=> $currency->rate, 
						"amount"=>$change_usd
					);
				}

				if(isset($_POST['count-money-kh'])){
					
					/*foreach($_POST['count-money-kh'] as $money => $number){
						$total_kh += ($money * $number);
					}*/
					$total_cash += (($total_kh * $default_currency->rate) / $kh_rate);
					$total_cash_open += (($change_kh* $default_currency->rate) / $kh_rate);
					$json_total_cash_count[$currency_khr->code] = json_encode($_POST['count-money-kh']);
					$json_total_cash[$currency_khr->code] = array(
						"code" => $currency_khr->code,
						"rate" => $kh_rate, 
						"amount" => $total_kh
					);
					$json_total_cash_open[$currency_khr->code] = array(
						"code" => $currency_khr->code,
						"rate" => $kh_rate, 
						"amount" => $change_kh
					);
				}
				$data["total_cash"] = $total_cash;
				$data["total_cash_open"] = $total_cash_open;
				$data["json_total_cash_count"] = json_encode($json_total_cash_count);
				$data["json_total_cash"] = json_encode($json_total_cash);
				$data["json_total_cash_open"] = json_encode($json_total_cash_open);
			}
			
		}else if($this->input->post('edit_fuel_sale')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
		if ($this->form_validation->run() == true && $this->sales_model->updateFuelSale($id, $data, $items, $stockmoves, $accTrans)) {
			$this->session->set_userdata('remove_shls', 1);
			$this->session->set_flashdata('message', lang("fuel_sale_updated"));
			redirect('sales/fuel_sales');
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$fuel_sale = $this->sales_model->getFuelSaleByID($id);
			$fuel_sale_items = $this->sales_model->getFuelSaleItemsByFuelSaleID($id);
            $c = rand(100000, 9999999);
			foreach ($fuel_sale_items as $fuel_sale_item) {
				$row = json_decode('{}');
				$ri = $c;
				$nozzles = $this->sales_model->getTankNozzlesByTankID($fuel_sale_item->tank_id);
				if($nozzles){
					foreach($nozzles as $nozzle){
						$shift = $this->sales_model->getFuelSaleQuantityItem($fuel_sale_item->tank_id, $nozzle->id);
						$quantity = ($shift->quantity + $nozzle->nozzle_start_no);
						$nozzle->nozzle_start_no = ($quantity - $fuel_sale_item->quantity);
					}
				}
				$row->id = $fuel_sale_item->tank_id;
				$row->code = $fuel_sale_item->tank_code;
				$row->name = $fuel_sale_item->tank_name;
				$row->nozzle_id = $fuel_sale_item->nozzle_id;
				$row->product_id = $fuel_sale_item->product_id;
				$row->nozzle_no = $fuel_sale_item->nozzle_no;
				$row->start_no = $fuel_sale_item->nozzle_start_no;
				$row->end_no = $fuel_sale_item->nozzle_end_no;
				$row->quantity = $fuel_sale_item->quantity;
				$row->using_qty = $fuel_sale_item->using_qty;
				$row->customer_qty = $fuel_sale_item->customer_qty;
				$row->customer_amount = $fuel_sale_item->customer_amount;
				$row->unit_price = $fuel_sale_item->unit_price;
				$row->subtotal = $fuel_sale_item->subtotal;
				$pr[$ri] = array('id' => ($c), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'nozzles' => $nozzles);
                $c++;
			}
			$this->data['id'] = $id;
			$this->data['fuel_sale'] = $fuel_sale;
			$this->data['fuel_sale_items'] = json_encode($pr);
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['projects'] = $this->site->getAllProjects();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['times'] = $this->sales_model->getFuelTimes();
			$this->data['salemans'] = $this->site->getSalemans();
			$this->data['tanks'] = $this->site->getTanks();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => site_url('sales/fuel_sales'), 'page' => lang('fuels')), array('link' => '#', 'page' => lang('edit_fuel_sale')));
			$meta = array('page_title' => lang('edit_fuel_sale'), 'bc' => $bc);
			$this->core_page('sales/edit_fuel_sale', $meta, $this->data);
		}
	}

	public function suggesion_fuel_sale()
	{
		$term = $this->input->get('term', true);
		$salesman_id = $this->input->get('salesman_id', true);
		$customer_id = $this->input->get('customer_id', true);
		$warehouse_id = $this->input->get('warehouse_id', true);
		$warehouse = $this->site->getWarehouseByID($warehouse_id);
		$customer_trucks = false;
		if($customer_id){
			$customer = $this->site->getCompanyByID($customer_id);
			$customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
			if($this->config->item('customer_truck')){
				$customer_trucks = $this->sales_model->getCustomerTrucks($customer_id);
			}
		}
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $rows = $this->sales_model->getTankNames($sr,$warehouse_id);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $nozzles = $this->sales_model->getTankNozzlesByTankID($row->id);
				 if($nozzles){
					foreach($nozzles as $nozzle){
						$fuel_sale = $this->sales_model->getFuelSaleQuantityItem($row->id, $nozzle->id);
						if($fuel_sale->quantity > 0){
							$quantity = $fuel_sale->quantity;
						}else{
							$quantity = $nozzle->nozzle_start_no;
						}
						$nozzle->nozzle_start_no = $quantity;
						$nozzle->customer_qty = 0;
						$nozzle->customer_amount = 0;
						$fuel_customer = $this->sales_model->getFuelCustomerNozzleQuantity($salesman_id,$nozzle->id);
						if($fuel_customer){
							$nozzle->customer_qty = $fuel_customer->quantity;
							$nozzle->customer_amount = $fuel_customer->amount;
						}
	
						if($customer_id){
							if($this->Settings->customer_price == 1 && $customer_price = $this->sales_model->getCustomerPrice($nozzle->product_id,$customer_id)){
								if (isset($customer_price) && $customer_price != false) {
									if($customer_price->price > 0){
										$nozzle->unit_price = $customer_price->price;
									}
								}
							} else if ($customer->price_group_id) {
								if ($pr_group_price = $this->site->getProductGroupPrice($nozzle->product_id, $customer->price_group_id)) {
									$nozzle->unit_price = $pr_group_price->price;
								}
							} else if ($warehouse->price_group_id) {
								if ($pr_group_price = $this->site->getProductGroupPrice($nozzle->product_id, $warehouse->price_group_id)) {
									$nozzle->unit_price = $pr_group_price->price;
								}
							}
							$nozzle->unit_price = $nozzle->unit_price + (($nozzle->unit_price * $customer_group->percent) / 100);
						}
					}
				}
				
				$row->start_no = 0;
				$row->end_no = 0;
				$row->unit_price = 0;
				$row->quantity = 0;
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'nozzles' => $nozzles, 'customer_trucks' => $customer_trucks);
                $r++;
            }
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
	}
	
	public function getFuelSales($warehouse_id = null, $biller_id = null)
    {
        $this->cus->checkPermissions("fuel_sale-index");
        $this->load->library('datatables');
		$add_sale_link = '';
		if(($this->Admin || $this->Owner) || $this->GP['sales-add']){
			$add_sale_link = anchor('sales/add?fuel_sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('add_sale'), ' class="fuel-add_sale" ');
		}
		$submit_cash_link = '';
		if(($this->Admin || $this->Owner) || $this->GP['sales-fuel_sale-add']){
			$submit_cash_link = anchor('sales/submit_cash/$1', '<i class="fa fa-usd"></i> ' . lang('submit_cash'), ' data-backdrop="static" data-keyboard="false" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal"');
		}
		$detail_link = anchor('sales/view_fuel_sale/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_fuel_sale'), ' data-toggle="modal" data-target="#myModal"');
		$edit_link = '';
		if(($this->Admin || $this->Owner) || $this->GP['sales-fuel_sale-edit']){
			$edit_link = anchor('sales/edit_fuel_sale/$1', '<i class="fa fa-edit"></i> ' . lang('edit_fuel_sale'), ' class="fuel-edit" ');
		}
		$delete_link = '';
		if(($this->Admin || $this->Owner) || $this->GP['sales-fuel_sale-delete']){
			$delete_link = "<a href='#' class='po fuel-delete' title='<b>" . lang("delete_fuel_sale") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_fuel_sale/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_fuel_sale') . "</a>";
		}
		$action = '<div class="text-center"><div class="btn-group text-left">'
					. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
					. lang('actions') . ' <span class="caret"></span></button>
						<ul class="dropdown-menu pull-right" role="menu">
							<li>' . $detail_link. '</li>
							<li>' . $add_sale_link . '</li>
							<li>' . $edit_link . '</li>
							<li>' . $delete_link . '</li>
						</ul>
					</div></div>';
		
        $this->datatables->select("
					fuel_sales.id as id,
					fuel_sales.date,
					fuel_sales.reference_no,
					fuel_sales.saleman,
					CONCAT(".$this->db->dbprefix("fuel_times").".open_time,' - ',".$this->db->dbprefix("fuel_times").".close_time) as time,
					IFNULL(cus_fuel_sale_items.customer_qty,0) as customer_qty,
					IFNULL(cus_fuel_sale_items.using_qty,0) as using_qty,
					IFNULL(cus_fuel_sale_items.quantity,0) as quantity,
					IFNULL(".$this->db->dbprefix("fuel_sales").".total,0) as total_sales,
					IFNULL(".$this->db->dbprefix("fuel_sales").".total_cash_open,0) as cash_change,
					IFNULL(".$this->db->dbprefix("fuel_sales").".total_cash,0) as cash_submit,
					IFNULL(".$this->db->dbprefix("fuel_sales").".credit_amount,0) as credit_amount,
					(IFNULL(".$this->db->dbprefix("fuel_sales").".credit_amount,0) + IFNULL(".$this->db->dbprefix("fuel_sales").".total_cash,0) - IFNULL(".$this->db->dbprefix("fuel_sales").".total_cash_open,0)) - IFNULL(".$this->db->dbprefix("fuel_sales").".total,0) as different,
					IF(IFNULL(".$this->db->dbprefix("fuel_sales").".total,0) = 0, 'completed', IF(ROUND(cus_sales.quantity,".$this->Settings->decimals.")>=ROUND(".$this->db->dbprefix("fuel_sale_items").".quantity,".$this->Settings->decimals."),'completed',IF(cus_sales.quantity > 0,'partial','pending'))) as status,
					fuel_sales.attachment
					", false)
            ->from("fuel_sales")
			->join('fuel_times', 'fuel_times.id=fuel_sales.time_id', 'left')
			->join('(SELECT 
							fuel_sale_id,
							SUM(subtotal) as subtotal,
							SUM(quantity) as quantity
						FROM '.$this->db->dbprefix("sales").'
						LEFT JOIN '.$this->db->dbprefix("sale_items").' ON '.$this->db->dbprefix("sale_items").'.sale_id = '.$this->db->dbprefix("sales").'.id
						GROUP BY fuel_sale_id) as cus_sales','cus_sales.fuel_sale_id=fuel_sales.id','left')
			->join('(SELECT 
							fuel_sale_id,
							SUM(quantity) as quantity,
							SUM(customer_qty) as customer_qty,
							SUM(using_qty) as using_qty
						FROM '.$this->db->dbprefix("fuel_sale_items").' 
						GROUP BY fuel_sale_id) as cus_fuel_sale_items','cus_fuel_sale_items.fuel_sale_id=fuel_sales.id','left')
			->join('users', 'users.id=fuel_sales.created_by', 'left')
            ->add_column("Actions", $action, "id");
		
		if ($biller_id) {
             $this->datatables->where('fuel_sales.biller_id', $biller_id);
        }
		if ($warehouse_id) {
            $this->datatables->where('fuel_sales.warehouse_id', $warehouse_id);
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('fuel_sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('fuel_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('fuel_sales.created_by', $this->session->userdata('user_id'));
		}
        echo $this->datatables->generate();
    }
	
	public function delete_fuel_sale($id = null)
    {
        $this->cus->checkPermissions("fuel_sale-delete");
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$sale = $this->sales_model->getSaleByFuelID($id);
		if($sale){
			echo lang("fuel_sale_cannot_delete");
			http_response_code(500);
			die();
		}
        if ($this->sales_model->deleteFuelSale($id)) {
			if ($this->input->is_ajax_request()) {
                echo lang("fuel_sale_deleted");
				die();
            }
            $this->session->set_flashdata('message', lang("fuel_sale_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function fuel_sale_actions()
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])){
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
						$sale = $this->sales_model->getSaleByFuelID($id);
						if($sale){
							$this->session->set_flashdata('error', lang("fuel_sale_cannot_delete"));
							redirect($_SERVER["HTTP_REFERER"]);
						}
                        $this->sales_model->deleteFuelSale($id);
                    }
                    $this->session->set_flashdata('message', lang("fuel_sales_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('saleman'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('time'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('total_sales'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('cash_change'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('cash_submit'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('credit_amount'));
					$this->excel->getActiveSheet()->SetCellValue('L1', lang('different'));
					$this->excel->getActiveSheet()->SetCellValue('M1', lang('created_by'));
					$row = 2;
                    foreach ($_POST['val'] as $id){
						$fuel_sale = $this->sales_model->getFuelSaleByID($id);
						$user = $this->site->getUser($fuel_sale->created_by);
						$warehouse = $this->site->getWarehouseByID($fuel_sale->warehouse_id);
						$time = $this->sales_model->getFuelTimeByID($fuel_sale->time_id);
						$different = ($fuel_sale->credit_amount + $fuel_sale->total_cash - $fuel_sale->total_cash_open)-$fuel_sale->total;
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($fuel_sale->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $fuel_sale->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $fuel_sale->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $warehouse->name);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $fuel_sale->saleman);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $time->open_time.' - '.$time->close_time);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->formatQuantity($fuel_sale->quantity));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->formatDecimal($fuel_sale->total));
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->cus->formatDecimal($fuel_sale->total_cash_open));
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->cus->formatDecimal($fuel_sale->total_cash));
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->cus->formatDecimal($fuel_sale->credit_amount));
						$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->cus->formatDecimal($different));
						$this->excel->getActiveSheet()->SetCellValue('M' . $row, $user->last_name.' '.$user->first_name);
                        $row++;
                    }
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
					$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'fuel_sales_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_fuel_sale_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function view_fuel_sale($id = null)
    {
		$this->cus->checkPermissions("fuel_sale-index");
		$fuel_sale = $this->sales_model->getFuelSaleByID($id);
		$this->data['fuel_sale'] = $fuel_sale;
		$this->data['time'] = $this->sales_model->getFuelTimeByID($fuel_sale->time_id);
		$this->data['rows'] = $this->sales_model->getFuelSaleItemsByFuelSaleID($id);
		$this->data['saleman'] = $this->site->getUser($fuel_sale->saleman_id);
		$this->data['biller'] = $this->site->getCompanyByID($fuel_sale->biller_id);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('FuelSale',$fuel_sale->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('FuelSale',$fuel_sale->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		$this->data['created_by'] = $this->site->getUser($fuel_sale->created_by);
		$this->data['sales'] = $this->sales_model->getSaleByFuelID($id);
        $this->load->view($this->theme . 'sales/view_fuel_sale', $this->data);
    }
	
	public function submit_cash($id = null)
	{
		$this->cus->checkPermissions("fuel_sale-index");
		$this->form_validation->set_rules('submit_cash', lang("submit_cash"), 'required');
		if ($this->form_validation->run() == true) {
			$change_kh = $this->input->post('change_kh',true);
			$change_usd = $this->input->post('change_usd', true);
			$total_usd = 0; 
			$total_cash = 0; 
			$total_cash_open = 0;
			$default_currency = $this->site->getCurrencyByCode($this->Settings->default_currency);
			if(isset($_POST['count-money-usd'])){
				$currency = $this->site->getCurrencyByCode("USD");
				foreach($_POST['count-money-usd'] as $money => $number){
					$total_usd += ($money * $number);
				}
				$total_cash += (($total_usd * $default_currency->rate) / $currency->rate);
				$total_cash_open += (($change_usd* $default_currency->rate) / $currency->rate);
				$json_total_cash_count[$currency->code] = json_encode($_POST['count-money-usd']);
				$json_total_cash_open[$currency->code] = array(
								"code"=>$currency->code,
								"rate"=> $currency->rate, 
								"amount"=>$change_usd
							);
			}
			$total_kh = 0;
			if(isset($_POST['count-money-kh'])){
				$currency = $this->site->getCurrencyByCode("KHR");
				foreach($_POST['count-money-kh'] as $money => $number){
					$total_kh += ($money * $number);
				}
				$total_cash += (($total_kh * $default_currency->rate) / $currency->rate);
				$total_cash_open += (($change_kh* $default_currency->rate) / $currency->rate);
				$json_total_cash_count[$currency->code] = json_encode($_POST['count-money-kh']);
				$json_total_cash_open[$currency->code] = array(
								"code"=>$currency->code,
								"rate"=>$currency->rate, 
								"amount"=>$change_kh
							);
			}
			$data = array(
				"total_cash" => $total_cash,
				"total_cash_open" => $total_cash_open,
				"json_total_cash_count" => json_encode($json_total_cash_count),
				"json_total_cash_open" => json_encode($json_total_cash_open)
			);
		}
		if ($this->form_validation->run() == true && $this->sales_model->addFuelSaleCash($id, $data)) {
			$this->session->set_flashdata('message', lang("cash_submitted"));
			redirect('sales/fuel_sales');
		}else{
			$fuel_sale = $this->sales_model->getFuelSaleByID($id);
			$this->data['time'] = $this->sales_model->getFuelTimeByID($fuel_sale->time_id);
			$this->data['fuel_sale'] = $fuel_sale;
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->data['time'] = $this->sales_model->getFuelTimeByID($fuel_sale->time_id);
			$this->data['rows'] = $this->sales_model->getFuelSaleItemsByFuelSaleID($id);
			$this->load->view($this->theme . 'sales/submit_cash', $this->data);
		}
	}
	
	
	public function fuel_customers($warehouse_id = null,$biller_id = NULL)
	{
		$this->cus->checkPermissions("fuel_sale-index");
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		if($biller_id == 0){
			$biller_id = null;
		}
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('fuel_customers')));
        $meta = array('page_title' => lang('fuel_customers'), 'bc' => $bc);
        $this->core_page('sales/fuel_customers', $meta, $this->data);
	}
	
	public function getFuelCustomers($warehouse_id = null, $biller_id = null)
    {
        $this->cus->checkPermissions("fuel_sale-index");
        $this->load->library('datatables');
		
		$detail_link = anchor('sales/view_fuel_customer/$1', '<i class="fa fa-file-text-o"></i> ' . lang('fuel_customer_details'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		
		$edit_link = '';
		if(($this->Admin || $this->Owner) || $this->GP['sales-fuel_sale-edit']){
			$edit_link = anchor('sales/edit_fuel_customer/$1', '<i class="fa fa-edit"></i> ' . lang('edit_fuel_customer'), ' class="edit_fuel_customer" ');
		}
		$delete_link = '';
		if(($this->Admin || $this->Owner) || $this->GP['sales-fuel_sale-delete']){
			$delete_link = "<a href='#' class='po fuel-delete delete_fuel_customer' title='<b>" . lang("delete_fuel_customer") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_fuel_customer/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_fuel_customer') . "</a>";
		}
		$action = '<div class="text-center"><div class="btn-group text-left">'
					. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
					. lang('actions') . ' <span class="caret"></span></button>
						<ul class="dropdown-menu pull-right" role="menu">
							<li>' . $detail_link . '</li>
							<li>' . $edit_link . '</li>
							<li>' . $delete_link . '</li>
						</ul>
					</div></div>';
		
        $this->datatables->select("
						fuel_customers.id as id,
						fuel_customers.date,
						fuel_customers.reference,
						companies.company as customer,
						CONCAT(".$this->db->dbprefix("users").".last_name,' ',".$this->db->dbprefix("users").".first_name) as salesman,
						IFNULL(cus_fuel_customer_items.quantity,0) as quantity,
						IFNULL(".$this->db->dbprefix("fuel_customers").".grand_total,0) as grand_total,
						IF (".$this->db->dbprefix("fuel_customers").".status = 'completed','completed', IF (cus_fuel_customer_items.fuel_sale_id > 0,'cleared','pending')) AS status,
						fuel_customers.attachment
					", false)
            ->from("fuel_customers")
			->join("(SELECT 
							fuel_customer_id,
							SUM(quantity) as quantity,
							SUM(fuel_sale_id) as fuel_sale_id
						FROM ".$this->db->dbprefix("fuel_customer_items")."
						GROUP BY fuel_customer_id) as cus_fuel_customer_items","cus_fuel_customer_items.fuel_customer_id=fuel_customers.id","left")
			->join("users", "users.id=fuel_customers.saleman_id", "left")
			->join("companies", "companies.id=fuel_customers.customer_id", "left")
            ->add_column("Actions", $action, "id");
		
		if ($biller_id) {
            $this->datatables->where('fuel_customers.biller_id', $biller_id);
        }
		if ($warehouse_id) {
            $this->datatables->where('fuel_customers.warehouse_id', $warehouse_id);
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('fuel_customers.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('fuel_customers.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('fuel_customers.created_by', $this->session->userdata('user_id'));
		}
        echo $this->datatables->generate();
    }
	
	
	public function add_fuel_customer()
	{
		$this->cus->checkPermissions('fuel_sale-add', true);
		$this->form_validation->set_rules('customer', lang("customer"), 'required');	
		$this->form_validation->set_rules('saleman_id', lang("saleman"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post("biller");
			$warehouse_id = $this->input->post("warehouse");
			$note = $this->input->post('note');
			$saleman_id = $this->input->post("saleman_id");
			$customer_id = $this->input->post("customer");
			$time_id = $this->input->post('time_id');
			$reference = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('cfuel',$biller_id);
			if ($this->Owner || $this->Admin || $this->GP['sales-fuel_sale-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$accTrans = false;
			$stockmoves = false;
			$grand_total = 0;
			$tanks = isset($_POST['tank_id']) ? sizeof($_POST['tank_id']) : 0;
			for ($r = 0; $r < $tanks; $r++) {
				$tank_id = $_POST['tank_id'][$r];
				$nozzle_id = $_POST['nozzle_id'][$r];
				$nozzle_no = $_POST['nozzle_no'][$r];
				$product_id = $_POST['product_id'][$r];
				$unit_price = $_POST['unit_price'][$r];
				$quantity = $_POST['quantity'][$r];
				$subtotal = $_POST['subtotal'][$r];
				$truck_id = $_POST['truck_id'][$r];
				if($quantity > 0 && $nozzle_id > 0){
					$items[] = array(
						'tank_id' => $tank_id,
						'nozzle_id' => $nozzle_id,
						'nozzle_no' => $nozzle_no,
						'product_id' => $product_id,
						'unit_price' => $unit_price,
						'quantity' => $quantity,
						'subtotal' => $subtotal,
						'truck_id' => $truck_id
					);
					$grand_total += $subtotal;
					
					$product_details = $this->site->getProductByID($product_id);
					$unit = $this->site->getProductUnit($product_details->id, $product_details->unit);
					$stockmoves[] = array(
						'transaction' => 'FuelCustomer',
						'reference_no' => $reference,
						'product_id' => $product_id,
						'product_code' => $product_details->code,
						'product_type' => $product_details->type,
						'quantity' => $quantity * (-1),
						'unit_quantity' => $unit->unit_qty,
						'unit_code' => $unit->code,
						'unit_id' => $product_details->unit,
						'warehouse_id' => $warehouse_id,
						'date' => $date,
						'real_unit_cost' => $product_details->cost,
						'user_id' => $this->session->userdata('user_id'),
					);
					//========accounting=========//
						$productAcc = $this->site->getProductAccByProductId($product_id);
						if($this->Settings->accounting == 1){		
							$accTrans[] = array(
								'transaction' => 'FuelCustomer',
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $productAcc->stock_acc,
								'amount' => -($product_details->cost * $quantity),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
							$accTrans[] = array(
								'transaction' => 'FuelCustomer',
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $productAcc->cost_acc,
								'amount' => ($product_details->cost * $quantity),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
						}
					//============end accounting=======//
				}
			}
			if (empty($items)) {
                $this->form_validation->set_rules('tank_order', lang("tank_order"), 'required');
            }
			$data = array(
				'date' => $date,
				'reference' => $reference,
				'biller_id'=> $biller_id,
				'customer_id'=> $customer_id,
				'saleman_id'=> $saleman_id,
				'warehouse_id'=> $warehouse_id,
				'grand_total'=> $grand_total,
				'note' => $note,
				'created_by'=> $this->session->userdata('user_id'),
				'time_id' => $time_id
			);
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
                $data['attachment'] = $photo;
            }
			
			if (empty($tanks)) {
                $this->form_validation->set_rules('tank_order', lang("tank_order"), 'required');
            }
		}else if($this->input->post('add_fuel_customer')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
		if ($this->form_validation->run() == true && $this->sales_model->addFuelCustomer($data, $items, $stockmoves, $accTrans)) {
			$this->session->set_userdata('remove_fcls', 1);
			$this->session->set_flashdata('message', lang("fuel_customer_added"));
			redirect('sales/fuel_customers');
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['salemans'] = $this->site->getSalemans();
			$this->data['tanks'] = $this->site->getTanks();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => site_url('sales/fuel_customers'), 'page' => lang('fuel_customers')), array('link' => '#', 'page' => lang('add_fuel_customer')));
			$meta = array('page_title' => lang('add_fuel_customer'), 'bc' => $bc);
			$this->core_page('sales/add_fuel_customer', $meta, $this->data);
		}
	}
	
	public function edit_fuel_customer($id = NULL)
	{
		$this->cus->checkPermissions('fuel_sale-edit', true);
		$this->form_validation->set_rules('customer', lang("customer"), 'required');	
		$this->form_validation->set_rules('saleman_id', lang("saleman"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post("biller");
			$warehouse_id = $this->input->post("warehouse");
			$note = $this->input->post('note');
			$saleman_id = $this->input->post("saleman_id");
			$customer_id = $this->input->post("customer");
			$time_id = $this->input->post('time_id');
			$reference = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('cfuel',$biller_id);
			if ($this->Owner || $this->Admin || $this->GP['sales-fuel_sale-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$accTrans = false;
			$stockmoves = false;
			$grand_total = 0;
			$tanks = isset($_POST['tank_id']) ? sizeof($_POST['tank_id']) : 0;
			for ($r = 0; $r < $tanks; $r++) {
				$tank_id = $_POST['tank_id'][$r];
				$nozzle_id = $_POST['nozzle_id'][$r];
				$nozzle_no = $_POST['nozzle_no'][$r];
				$product_id = $_POST['product_id'][$r];
				$unit_price = $_POST['unit_price'][$r];
				$quantity = $_POST['quantity'][$r];
				$subtotal = $_POST['subtotal'][$r];
				$truck_id = $_POST['truck_id'][$r];
				$fuel_sale_id = $_POST['fuel_sale_id'][$r];
				if($quantity > 0 && $nozzle_id > 0){
					$items[] = array(
						'fuel_customer_id' => $id,
						'tank_id' => $tank_id,
						'nozzle_id' => $nozzle_id,
						'nozzle_no' => $nozzle_no,
						'product_id' => $product_id,
						'unit_price' => $unit_price,
						'quantity' => $quantity,
						'subtotal' => $subtotal,
						'truck_id' => $truck_id,
						'fuel_sale_id' => $fuel_sale_id
					);
					$grand_total += $subtotal;
					
					$product_details = $this->site->getProductByID($product_id);
					$unit = $this->site->getProductUnit($product_details->id, $product_details->unit);
					$stockmoves[] = array(
						'transaction' => 'FuelCustomer',
						'transaction_id' => $id,
						'reference_no' => $reference,
						'product_id' => $product_id,
						'product_code' => $product_details->code,
						'product_type' => $product_details->type,
						'quantity' => $quantity * (-1),
						'unit_quantity' => $unit->unit_qty,
						'unit_code' => $unit->code,
						'unit_id' => $product_details->unit,
						'warehouse_id' => $warehouse_id,
						'date' => $date,
						'real_unit_cost' => $product_details->cost,
						'user_id' => $this->session->userdata('user_id'),
					);
					//========accounting=========//
						$productAcc = $this->site->getProductAccByProductId($product_id);
						if($this->Settings->accounting == 1){		
							$accTrans[] = array(
								'transaction' => 'FuelCustomer',
								'transaction_id' => $id,
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $productAcc->stock_acc,
								'amount' => -($product_details->cost * $quantity),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
							$accTrans[] = array(
								'transaction' => 'FuelCustomer',
								'transaction_id' => $id,
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $productAcc->cost_acc,
								'amount' => ($product_details->cost * $quantity),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
						}
					//============end accounting=======//
				}
			}
			if (empty($items)) {
                $this->form_validation->set_rules('tank_order', lang("tank_order"), 'required');
            }
			$data = array(
				'date' => $date,
				'reference' => $reference,
				'biller_id'=> $biller_id,
				'customer_id'=> $customer_id,
				'saleman_id'=> $saleman_id,
				'warehouse_id'=> $warehouse_id,
				'grand_total'=> $grand_total,
				'note' => $note,
				'updated_by'=> $this->session->userdata('user_id'),
				'updated_at'=> date("Y-m-d H:i"),
				'time_id' => $time_id
			);
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
                $data['attachment'] = $photo;
            }
			if (empty($tanks)) {
                $this->form_validation->set_rules('tank_order', lang("tank_order"), 'required');
            }
		}else if($this->input->post('edit_fuel_customer')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
		if ($this->form_validation->run() == true && $this->sales_model->updateFuelCustomer($id, $data, $items, $stockmoves, $accTrans)) {
			$this->session->set_userdata('remove_fcls', 1);
			$this->session->set_flashdata('message', lang("fuel_customer_updated"));
			redirect('sales/fuel_customers');
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$fuel_customer = $this->sales_model->getFuelCustomerByID($id);
			if($fuel_customer->status == "completed"){
				$this->session->set_flashdata('error', lang("cannot_edit_fuel_customer"));
                redirect($_SERVER["HTTP_REFERER"]);
			}
			$customer_id = $fuel_customer->customer_id;
			$customer_trucks = false;
			if($customer_id){
				$customer = $this->site->getCompanyByID($customer_id);
				$customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
				if($this->config->item('customer_truck')){
					$customer_trucks = $this->sales_model->getCustomerTrucks($customer_id);
				}
			}
			
			$fuel_customer_items = $this->sales_model->getFuelCustomerItems($id);
            $c = rand(100000, 9999999);
			foreach ($fuel_customer_items as $fuel_customer_item) {
				$row = json_decode('{}');
				$ri = $c;
				$nozzles = $this->sales_model->getTankNozzlesByTankID($fuel_customer_item->tank_id);
				if($nozzles){
					foreach($nozzles as $nozzle){
						if($customer_id){
							if($this->Settings->customer_price == 1 && $customer_price = $this->sales_model->getCustomerPrice($nozzle->product_id,$customer_id)){
								if (isset($customer_price) && $customer_price != false) {
									if($customer_price->price > 0){
										$nozzle->unit_price = $customer_price->price;
									}
								}
							} else if ($customer->price_group_id) {
								if ($pr_group_price = $this->site->getProductGroupPrice($nozzle->product_id, $customer->price_group_id)) {
									$nozzle->unit_price = $pr_group_price->price;
								}
							}
							$nozzle->unit_price = $nozzle->unit_price + (($nozzle->unit_price * $customer_group->percent) / 100);
						}
					}
				}
				$row->fuel_sale_id = $fuel_customer_item->fuel_sale_id;
				$row->id = $fuel_customer_item->tank_id;
				$row->code = $fuel_customer_item->tank_code;
				$row->name = $fuel_customer_item->tank_name;
				$row->nozzle_id = $fuel_customer_item->nozzle_id;
				$row->product_id = $fuel_customer_item->product_id;
				$row->nozzle_no = $fuel_customer_item->nozzle_no;
				$row->quantity = $fuel_customer_item->quantity;
				$row->unit_price = $fuel_customer_item->unit_price;
				$row->truck_id = $fuel_customer_item->truck_id;
				$row->subtotal = $fuel_customer_item->subtotal;
				$pr[$ri] = array('id' => ($c), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'nozzles' => $nozzles, 'customer_trucks' => $customer_trucks);
                $c++;
			}
			$this->data['id'] = $id;
			$this->data['fuel_customer'] = $fuel_customer;
			$this->data['fuel_customer_items'] = json_encode($pr);
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['projects'] = $this->site->getAllProjects();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['times'] = $this->sales_model->getFuelTimes();
			$this->data['salemans'] = $this->site->getSalemans();
			$this->data['tanks'] = $this->site->getTanks();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => site_url('sales/fuel_customers'), 'page' => lang('fuel_customers')), array('link' => '#', 'page' => lang('edit_fuel_customer')));
			$meta = array('page_title' => lang('edit_fuel_customer'), 'bc' => $bc);
			$this->core_page('sales/edit_fuel_customer', $meta, $this->data);
		}
	}
	
	public function delete_fuel_customer($id = null)
    {
        $this->cus->checkPermissions("fuel_sale-delete");
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$fuel_sale_id = 0;
		$fuel_customer = $this->sales_model->getFuelCustomerByID($id);
		$fuel_customer_items = $this->sales_model->getFuelCustomerItems($id);
		if($fuel_customer_items){
			foreach($fuel_customer_items as $fuel_customer_item){
				$fuel_sale_id += $fuel_customer_item->fuel_sale_id;
			}
		}
		if($fuel_customer->status!="pending" || $fuel_sale_id > 0){
			$this->session->set_flashdata('error', lang("cannot_delete_fuel_customer"));
            redirect('sales/fuel_customers');
		}else if ($this->sales_model->deleteFuelCustomer($id)) {
			if ($this->input->is_ajax_request()) {
                echo lang("fuel_customer_deleted");
				die();
            }
            $this->session->set_flashdata('message', lang("fuel_customer_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function view_fuel_customer($id = null)
    {
        $this->cus->checkPermissions('fuel_sale-index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$fuel_customer = $this->sales_model->getFuelCustomerByID($id);
		$this->data['fuel_customer'] = $fuel_customer;
		$this->data['fuel_customer_items'] = $this->sales_model->getFuelCustomerItems($id);
        $this->data['customer'] = $this->site->getCompanyByID($fuel_customer->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($fuel_customer->biller_id);
        $this->data['salesman'] = $this->site->getUser($fuel_customer->saleman_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($fuel_customer->warehouse_id);
        $this->load->view($this->theme . 'sales/view_fuel_customer', $this->data);
    }
	
	
	public function fuel_customer_actions()
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])){
				if ($this->input->post('form_action') == 'create_sale') {
					$ids = false; 
					$customer_id = "";
                    foreach ($_POST['val'] as $id) {
						$row = $this->sales_model->getFuelCustomerByID($id);
						if(($customer_id == "" || $customer_id == $row->customer_id) && $row->status != "completed"){
							$customer_id = $row->customer_id;
							$ids[] = $id;
						}
						if(!$ids){
							$this->session->set_flashdata('error', lang("cannot_add_sale"));
							redirect($_SERVER["HTTP_REFERER"]);
						}
                    }
					redirect('sales/add?fuel_customer='.json_encode($ids));
                }else if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
						$fuel_sale = $this->sales_model->getFuelCustomerByID($id);
						if($fuel_sale->status != "pending"){
							$this->session->set_flashdata('error', lang("fuel_customer_cannot_delete"));
							redirect($_SERVER["HTTP_REFERER"]);
						}
                        $this->sales_model->deleteFuelCustomer($id);
                    }
                    $this->session->set_flashdata('message', lang("fuel_customer_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('fuel_customer'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('saleman'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));
					$row = 2;
                    foreach ($_POST['val'] as $id){
						$fuel_customer = $this->sales_model->getFuelCustomerByID($id);
						$salesman = $this->site->getUser($fuel_customer->saleman_id);
						$warehouse = $this->site->getWarehouseByID($fuel_customer->warehouse_id);
						$biller = $this->site->getCompanyByID($fuel_customer->biller_id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($fuel_customer->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $fuel_customer->reference);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $biller->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $warehouse->name);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $salesman->last_name." ".$salesman->first_name);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->formatDecimal($fuel_customer->grand_total));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($fuel_customer->status));
						$row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'fuel_customers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_fuel_sale_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	// ========Down Payment=============//
	
	public function down_payment($id = null)
    {
        $this->cus->checkPermissions('payments', true);
		$inv = $this->sales_model->getInvoiceByID($id);
		$this->form_validation->set_rules('payment_term', lang("term"), 'required');
		
		if($count_down_payments = $this->sales_model->getAllCountSeparatePaymentBySaleID($id)){
			$this->session->set_flashdata('error', lang('cannot_down_payment'));
			$this->cus->md();
		}
		if($inv->installment==1){
			$this->session->set_flashdata('error', lang('cannot_down_payment_with_installment'));
			$this->cus->md();
		}else if($inv->payment_status == 'paid'){
			$this->session->set_flashdata('error', lang('cannot_down_payment_with_sale_paid'));
			$this->cus->md();
		}
		
		if ($this->form_validation->run() == true) {
			$payment_amount = $this->input->post('payment_amount', true);
			$payment_period = $this->input->post('payment_period', true);
			$payment_term = $this->input->post('payment_term', true);
			$payment_date = $this->input->post('payment_date');
			$details = array();
			if(isset($_POST['payment'])){
				for($i = 0; $i <= count($_POST['payment'])-1; $i++){
					$deadline = $_POST['deadline'][$i];
					$payment = $_POST['payment'][$i];
					$percent = $_POST['percent'][$i];
					$details[] = array(
						'payment' => $payment,
						'percent' => $percent,
						'deadline' => $this->cus->fld($deadline),
						'payment_status' => 'pending'
					);
				}
			}
			$data = array(
				'sale_id' => $id,
				'amount' => $payment_amount,
				'period' => $payment_period,
				'term' => $payment_term,
				'payment_date' => $this->cus->fld($payment_date),
				'status' => 'completed',
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i'),
			);
		}else if($this->input->post('down_payment')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
		if ($this->form_validation->run() == true && $this->sales_model->addSeparatePayment($data, $details)) {
			$this->session->set_flashdata('message', lang("down_payment_added").' '.$inv->reference_no);
            redirect($_SERVER["HTTP_REFERER"]);
        }else{
			$this->data['payments'] = $this->sales_model->getInvoicePayments($id);
			$this->data['inv'] = $inv;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'sales/down_payment', $this->data);
		}
    }
	
	public function view_down_payments($sale_id = false)
	{
		$this->cus->checkPermissions("payments");
		$this->data['inv'] = $this->sales_model->getInvoiceByID($sale_id);
		$this->data['down_payments'] = $this->sales_model->getAllSeparatePaymentBySaleID($sale_id);
		$this->data['modal_js'] = $this->site->modal_js();
		$this->load->view($this->theme . 'sales/view_down_payments', $this->data);
	}
	
	public function inactive_down_payment($id = false)
	{
		$this->cus->checkPermissions("payments");
        if ($this->sales_model->inactiveSeparatePaymentByID($id)) {
            echo lang("down_payment_inactive");
        }
		$this->session->set_flashdata('message', lang("down_payment_inactive"));
        redirect('sales');
	}
	
	public function down_payment_details($id = false)
	{
		$this->cus->checkPermissions("payments");
		if($down_payment = $this->sales_model->getSeparatePaymentByID($id)){
			$this->data['id'] = $id;
			$this->data['down_payment'] = $down_payment;
			$this->data['inv'] = $this->sales_model->getInvoiceByID($down_payment->sale_id);
		}else{
			$this->data['id'] = 0;
		}
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('down_payment_details')));
        $meta = array('page_title' => lang('down_payment_details'), 'bc' => $bc);
        $this->core_page('sales/down_payment_details', $meta, $this->data);
	}
	
	public function getSeparatePaymentDetails($id = false)
    {
        $this->cus->checkPermissions("payments");
        $this->load->library('datatables');
		$payments_link = anchor('sales/payments/$2/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$2/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_payment" data-target="#myModal"');
		$action = '<div class="text-center"><div class="btn-group text-left">'
					. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
					. lang('actions') . ' <span class="caret"></span></button>
						<ul class="dropdown-menu pull-right" role="menu">
							<li>'.$payments_link.'</li>
							<li>'.$add_payment_link.'</li>
						</ul>
					</div></div>';
		
			$this->datatables->select("
				down_payment_details.id as id,
				sales.id as sale_id,
				down_payment_details.deadline,
				sales.reference_no,
				sales.customer,
				down_payment_details.payment,
				IFNULL(cus_down_payment_details.paid,0) AS paid,
				(cus_down_payment_details.payment - IFNULL(cus_down_payment_details.paid,0)) as balance,
				down_payment_details.payment_status", false)
            ->from("down_payments")
			->join("down_payment_details","down_payments.id=down_payment_id","left")
			->join("sales","sales.id=down_payments.sale_id","left");
        
		if($id){
			$this->datatables->where("down_payments.id",$id);
		}else{
			$remind = ($this->Settings->installment_alert_days?$this->Settings->installment_alert_days:0);
			$this->datatables->where('DATE_SUB('.$this->db->dbprefix('down_payment_details').'.`deadline`, INTERVAL '.$remind.' DAY) <=', date("Y-m-d"));
			$this->datatables->where('down_payment_details.payment_status !=','paid');
			$this->datatables->where('down_payments.status', 'completed');
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('down_payments.created_by', $this->session->userdata('user_id'));
		}
		$this->datatables->add_column("Actions", $action, "id,sale_id");
		$this->datatables->unset_column("sale_id");
        echo $this->datatables->generate();
    }
	
	public function getUser()
	{
		$saleman_id = $this->input->get('saleman_id');
		$saleman = $this->site->getUser($saleman_id);
		$data = array('saleman_commission'=>$saleman->saleman_commission);
		echo json_encode($data);
	}
	
		
	public function member_cards()
	{
		$this->cus->checkPermissions('member_cards');
		$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('member_cards')));
        $meta = array('page_title' => lang('member_cards'), 'bc' => $bc);
        $this->core_page('sales/member_cards', $meta, $this->data);
	}
	
	public function getMemberCards()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('member_cards') . ".id as id, card_no, companies.award_points, CONCAT(" . $this->db->dbprefix('users') . ".last_name, ' ', " . $this->db->dbprefix('users') . ".first_name) as created_by, customer, expiry", false)
            ->join('users', 'users.id=member_cards.created_by', 'left')
			->join('companies', 'companies.id=member_cards.customer_id', 'left')
            ->from("member_cards")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('sales/view_member_card/$1') . "' class='tip' title='" . lang("view_member_card") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> <a href='" . site_url('sales/add_redeem_point/$1') . "' class='tip' title='" . lang("add_redeem_point") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-dollar\"></i></a> <a href='" . site_url('sales/edit_member_card/$1') . "' class='tip' title='" . lang("edit_member_card") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_member_card") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_member_card/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	
	public function view_member_card($id = null)
    {
        $this->data['page_title'] = lang('member_card');
        $member_card = $this->site->getMemberCardByID($id);
        $this->data['member_card'] = $member_card;
        $this->data['customer'] = $this->site->getCompanyByID($member_card->customer_id);
        $this->load->view($this->theme . 'sales/view_member_card', $this->data);
    }
	
	public function add_member_card()
    {
       $this->cus->checkPermissions('add_member_card',true);
        $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|is_unique[member_cards.card_no]|required');
		$this->form_validation->set_rules('customer', lang("customer"), 'trim|is_unique[member_cards.customer_id]|required');
        $this->form_validation->set_rules('award_points', lang("award_points"), 'required');
        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer = $customer_details ? $customer_details->company : null;
            $data = array('card_no' => $this->input->post('card_no'),
				'date' => ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s')),
				'award_points' => $this->input->post('award_points'),
				'each_spent' => $this->input->post('each_spent'),
				'ca_point' => $this->input->post('ca_point'),
                'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer' => $customer,
                'expiry' => $this->input->post('expiry') ? $this->cus->fsd($this->input->post('expiry')) : null,
                'created_by' => $this->session->userdata('user_id'),
            );
        } elseif ($this->input->post('add_member_card')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/member_cards");
        }
        if ($this->form_validation->run() == true && $this->sales_model->addMemberCard($data)) {
            $this->session->set_flashdata('message', lang("member_card_added"));
            redirect("sales/member_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("new_member_card");
            $this->load->view($this->theme . 'sales/add_member_card', $this->data);
        }
    }
	
	public function edit_member_card($id = null)
    {
		$this->cus->checkPermissions('edit_member_card',true);
        $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|required');
        $mb_details = $this->site->getMemberCardByID($id);
        if ($this->input->post('card_no') != $mb_details->card_no) {
            $this->form_validation->set_rules('card_no', lang("card_no"), 'is_unique[member_cards.card_no]');
        }
		if ($this->input->post('customer') != $mb_details->customer_id) {
            $this->form_validation->set_rules('customer', lang("customer"), 'is_unique[member_cards.customer_id]');
        }
        $this->form_validation->set_rules('award_points', lang("award_points"), 'required');
        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer = $customer_details ? $customer_details->company : null;
            $data = array(
				'card_no' => $this->input->post('card_no'),
                'award_points' => $this->input->post('award_points'),
				'each_spent' => $this->input->post('each_spent'),
				'ca_point' => $this->input->post('ca_point'),
                'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer' => $customer,
                'expiry' => $this->input->post('expiry') ? $this->cus->fsd($this->input->post('expiry')) : null,
            );
        } elseif ($this->input->post('edit_member_card')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/member_cards");
        }
        if ($this->form_validation->run() == true && $this->sales_model->updateMemberCard($id, $data)) {
            $this->session->set_flashdata('message', lang("member_card_updated"));
            redirect("sales/member_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['member_card'] = $mb_details;
			$this->data['customer'] = $this->site->getCompanyByID($mb_details->customer_id);
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_member_card', $this->data);
        }
    }
	
	public function delete_member_card($id = null)
    {
        $this->cus->checkPermissions('delete_member_card',true);
        if ($this->sales_model->deleteMemberCard($id)) {
            echo lang("member_card_deleted");
        }
    }
	
	public function member_card_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete_member_card');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteMemberCard($id);
                    }
                    $this->session->set_flashdata('message', lang("member_cards_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('member_cards'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('card_no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('credit'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->site->getMemberCardByID($id);
						$cp = $this->site->getCompanyByID($sc->customer_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->card_no);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $cp->award_points);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->customer);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'member_cards_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_member_card_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function add_redeem_point($card_id)
    {
        $this->cus->checkPermissions('add_member_card',true);
        $card = $this->site->getMemberCardByID($card_id);
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = array(
				'card_id' => $card_id,
                'amount' => $this->input->post('amount'),
				'note' => $this->input->post('note'),
                'date' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('user_id'),
            );
        } elseif ($this->input->post('redeem')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/member_cards");
        }
        if ($this->form_validation->run() == true && $this->sales_model->redeemMemberCard($data)) {
            $this->session->set_flashdata('message', lang("redeem_points_updated"));
            redirect("sales/member_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['card'] = $card;
			$this->data['customer'] = $this->site->getCompanyByID($card->customer_id);
            $this->data['page_title'] = lang("add_redeem_point");
            $this->load->view($this->theme . 'sales/add_redeem_point', $this->data);
        }
    }
	
	public function redeem_points($id = false)
	{
		$this->data['id'] = $id;
		$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('redeem_points')));
        $meta = array('page_title' => lang('redeem_points'), 'bc' => $bc);
        $this->core_page('sales/redeem_points', $meta, $this->data);
	}
	
	public function getRedeemPoints($id = false)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('member_card_redeems') . ".id as id, date, amount, note, CONCAT(" . $this->db->dbprefix('users') . ".last_name, ' ', " . $this->db->dbprefix('users') . ".first_name) as created_by", false)
            ->join('users', 'users.id=member_card_redeems.created_by', 'left')
            ->from("member_card_redeems")
			->where("member_card_redeems.card_id", $id)
            ->add_column("Actions", "<div class=\"text-center\"><a href='#' class='tip po' title='<b>" . lang("delete_redeem_point") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_redeem_point/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
			->unset_column("id");
		echo $this->datatables->generate();
    }
	
	public function redeem_points_actions($id = NULL, $pdf = NULL, $xls = NULL)
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($pdf || $xls) {
			$this->db
					->select($this->db->dbprefix('member_card_redeems') . ".id as id, date, amount, note, CONCAT(" . $this->db->dbprefix('users') . ".last_name, ' ', " . $this->db->dbprefix('users') . ".first_name) as created_by", false)
					->join('users', 'users.id=member_card_redeems.created_by', 'left')
					->from("member_card_redeems")
					->where("member_card_redeems.card_id", $id);
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
				$this->excel->getActiveSheet()->setTitle(lang('redeem_points'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('amount'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('note'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrsd($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->amount);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->cus->remove_tag($data_row->note));
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->created_by);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$filename = 'redeem_points_'.date("Y_m_d_H_i_s");
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
                create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	
	public function delete_redeem_point($id = null)
    {
        $this->cus->checkPermissions('delete_member_card',true);
        if ($this->sales_model->deleteRedeemPoint($id)) {
            echo lang("redeem_point_deleted");
        }
    }
	
	
	function import_sale()
    {
        $this->cus->checkPermissions();
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
				$sales = false;
				$sale_items = false;
				$products = false;
				$stockmoves = false;
				$accTrans = false;

				$biller_id = $this->input->post("biller");
				$biller_details = $this->site->getCompanyByID($biller_id);
				$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
				$warehouse_id = $this->input->post("warehouse");
				$project_id = $this->input->post("project");
				$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				$o_date = "";
				$o_customer_code = "";
				$o_reference = "";
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++){
						$date = trim($worksheet->getCellByColumnAndRow(0, $row)->getValue());
						$reference = trim($worksheet->getCellByColumnAndRow(1, $row)->getFormattedValue());
						$customer_code = trim($worksheet->getCellByColumnAndRow(2, $row)->getValue());
						$product_code = trim($worksheet->getCellByColumnAndRow(4, $row)->getValue());
						$quantity = trim($worksheet->getCellByColumnAndRow(6, $row)->getValue());
						$unit = trim($worksheet->getCellByColumnAndRow(7, $row)->getValue());
						$price = trim($worksheet->getCellByColumnAndRow(8, $row)->getValue());
						$discount = trim($worksheet->getCellByColumnAndRow(9, $row)->getFormattedValue());
						$order_discount = trim($worksheet->getCellByColumnAndRow(10, $row)->getFormattedValue());
						$order_tax = trim($worksheet->getCellByColumnAndRow(11, $row)->getFormattedValue());
						$shipping = trim($worksheet->getCellByColumnAndRow(12, $row)->getValue());
						if (strpos($date, '/') == false) {
							$date = PHPExcel_Shared_Date::ExcelToPHP($date);
							$date = date('d/m/Y',$date);
						}
						if($date=="" || $reference=="" || $customer_code==""){
							$date = $o_date;
							$reference = $o_reference;
							$customer_code = $o_customer_code;
							$order_discount = $o_order_discount;
							$order_tax = $o_order_tax;
							$shipping = $o_shipping;
						}
						if($date!='' && $reference!='' && $customer_code!='' && $product_code!=''){
							$finals[] = array(
								'date'			=> $date,
								'reference'		=> $reference,
								'customer_code' => $customer_code,
								'product_code'  => $product_code,
								'quantity'  	=> $quantity,
								'unit'   		=> $unit,
								'price'  		=> $price,
								'discount'   	=> $discount,
								'order_discount'=> $order_discount,
								'order_tax'   	=> $order_tax,
								'shipping'   	=> $shipping,
							);	
							$o_date = $date;
							$o_reference = $reference;
							$o_customer_code = $customer_code;
							$o_order_discount = $order_discount;
							$o_order_tax = $order_tax;
							$o_shipping = $shipping;
						}
					}
				}
				
				if($finals){
					foreach($finals as $final){
						$index = $final['date']."_".$final['reference']."_".$final['customer_code'];
						$customer = $this->site->getCustomerByCode($final['customer_code']);
						if(!$customer){
							$this->session->set_flashdata('error', lang("customer_code") . " (" . $final['customer_code'] . "). " . lang("code__exist"));
							redirect("sales/import_sale");
						}
						$product = $this->site->getProductByCode($final['product_code']);
						if(!$product){
							$this->session->set_flashdata('error', lang("product_code") . " (" . $final['product_code'] . "). " . lang("code__exist"));
							redirect("sales/import_sale");
						}
						if (isset($final['unit']) && $final['unit']) {
							$unit = $this->site->getProductUnitByCodeName($product->id,$final['unit']);
							if(!$unit){
								$this->session->set_flashdata('error', lang("unit_code") . " (" . $final['product_code']." - ".$final['unit'] . "). " . lang("code__exist"));
								redirect("sales/import_sale");
							}
						}else{
							$unit = $this->site->getProductUnitByCodeName($product->id,$product->unit);
						}
						if (isset($final['order_tax']) && $final['order_tax']) {
							$order_tax = $this->site->getTaxRateByCode($final['order_tax']);
							if(!$order_tax){
								$this->session->set_flashdata('error', lang("order_tax") . " (" . $final['order_tax'] . "). " . lang("code__exist"));
								redirect("sales/import_sale");
							}
						}else{
							$order_tax = "";
						}

						$sale = array(	'date' 			  	=> $this->cus->fsd($final['date']),
										'reference_no'      => $final['reference'],
										'customer_id'       => $customer->id,
										'customer'          => $customer->company,
										'biller_id'         => $biller_id,
										'biller'            => $biller,
										'project_id'        => $project_id,
										'warehouse_id'      => $warehouse_id,
										'order_discount_id' => $final['order_discount'],
										'shipping'          => $final['shipping'],
										'sale_status'       => "completed",
										'payment_status'    => "pending",
										'created_by'        => $this->session->userdata('user_id'),
										'delivery_status'	=> "pending",
										'order_tax'			=> $order_tax,
										'ar_account'        => $saleAcc->ar_acc,		  
									);	
						$pr_discount = 0;			
						if (isset($final['discount']) && $final['discount']) {
							$discount = $final['discount'];
							$dpos = strpos($discount, '%');
							if ($dpos !== FALSE) {
								$pds = explode("%", $discount);
								$pr_discount = $this->cus->formatDecimalRaw(((($this->cus->formatDecimalRaw($final['price'])) * (Float)($pds[0])) / 100), 11);
							} else {
								$pr_discount = $this->cus->formatDecimalRaw($discount,11);
							}
						}
						$unit_price = $final['price'] - $pr_discount;
						$subtotal = $unit_price * $final['quantity'];
							
						$sale_item = array(
									'product_id'      	=> $product->id,
									'product_code'    	=> $product->code,
									'product_name'    	=> $product->name,
									'product_type'    	=> $product->type,
									'real_unit_price' 	=> $final['price'],
									'net_unit_price'  	=> $unit_price,
									'unit_price'      	=> $unit_price,
									'quantity'        	=> $final['quantity'] * ($unit->unit_qty > 1 ? $unit->unit_qty : 1),
									'product_unit_id' 	=> $unit->unit_id,
									'product_unit_code' => $unit->code,
									'unit_quantity'   	=> $final['quantity'],
									'warehouse_id'    	=> $warehouse_id,
									'discount'        	=> $final['discount'],
									'item_discount'   	=> $pr_discount * $final['quantity'],
									'subtotal'        	=> $subtotal,
									'cost' 				=> $product->cost,
									'unit_qty'			=> $unit->unit_qty
								);			
									

						$sales[$index] = $sale;
						$sale_items[$index][] = $sale_item;
					}
					if($sales && $sale_items){
						foreach($sales as $index => $sale){
							$total = 0;
							$product_discount = 0;
							if($sale_items[$index]){
								$total_items = 0;
								foreach($sale_items[$index] as $sale_item){
									$total += $sale_item['subtotal'];
									$product_discount += $sale_item['item_discount'];
									$total_items += $sale_item['quantity'];
									$stockmoves[$index][] = array(
										'transaction' => 'Sale',
										'product_id' => $sale_item["product_id"],
										'product_type' => $sale_item["product_type"],
										'product_code' => $sale_item["product_code"],
										'quantity' => $sale_item["quantity"] * (-1),
										'unit_quantity' => $sale_item["unit_qty"],
										'unit_code' => $sale_item["product_unit_code"],
										'unit_id' => $sale_item["product_unit_id"],
										'warehouse_id' => $warehouse_id,
										'date' => $sale["date"],
										'real_unit_cost' => $sale_item["cost"],
										'reference_no' => $sale["reference_no"],
										'user_id' => $this->session->userdata('user_id'),
									);
									if ($this->Settings->overselling != 1) {
										if(isset($qty_stockmoves[$sale_item["product_id"]]) && $qty_stockmoves[$sale_item["product_id"]]){
											$qty_stockmoves[$sale_item["product_id"]] = $qty_stockmoves[$sale_item["product_id"]] + $sale_item["quantity"];
										}else{
											$qty_stockmoves[$sale_item["product_id"]] =  $sale_item["quantity"];
										}
									}
									
									if($this->Settings->accounting == 1){		
										$productAcc = $this->site->getProductAccByProductId($sale_item["product_id"]);
										$accTrans[$index][] = array(
											'transaction' => 'Sale',
											'transaction_date' => $sale["date"],
											'reference' => $sale["reference_no"],
											'account' => $productAcc->stock_acc,
											'amount' => -($sale_item["cost"] * $sale_item["quantity"]),
											'narrative' => 'Product Code: '.$sale_item["product_code"].'#'.'Qty: '.$sale_item["quantity"].'#'.'Cost: '.$sale_item["cost"],
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
										);
										$accTrans[$index][] = array(
											'transaction' => 'Sale',
											'transaction_date' => $sale["date"],
											'reference' => $sale["reference_no"],
											'account' => $productAcc->cost_acc,
											'amount' => ($sale_item["cost"] * $sale_item["quantity"]),
											'narrative' => 'Product Code: '.$sale_item["product_code"].'#'.'Qty: '.$sale_item["quantity"].'#'.'Cost: '.$sale_item["cost"],
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
										);
										$accTrans[$index][] = array(
											'transaction' => 'Sale',
											'transaction_date' => $sale["date"],
											'reference' => $sale["reference_no"],
											'account' => $productAcc->sale_acc,
											'amount' => -($sale_item["unit_quantity"] * $sale_item["unit_price"]),
											'narrative' => 'Sale',
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
										);
									}
									
								}
								
								$order_discount = 0;
								$order_tax = 0;
								if (isset($sale['order_discount_id']) && $sale['order_discount_id']) {
									$ordiscount = $sale['order_discount_id'];
									$dpos = strpos($ordiscount, '%');
									if ($dpos !== FALSE) {
										$rds = explode("%", $ordiscount);
										$order_discount = $this->cus->formatDecimalRaw(((($this->cus->formatDecimalRaw($total)) * (Float)($rds[0])) / 100), 11);
									} else {
										$order_discount = $this->cus->formatDecimalRaw($ordiscount,11);
									}
								}
								$sale["total_items"] = $total_items;
								$sale["total"] = $total;
								$sale["product_discount"] = $product_discount;
								$sale["order_discount"] = $order_discount;
								$sale["total_discount"] = $product_discount + $order_discount;
								if($sale["order_tax"]){
									$order_tax_id = $sale["order_tax"]->id;
									if ($sale["order_tax"]->type == 2) {
										$order_tax = $this->cus->formatDecimal($sale["order_tax"]->rate);
									}
									if ($sale["order_tax"]->type == 1) {
										$order_tax = $this->cus->formatDecimal(((($total - $order_discount) * $sale["order_tax"]->rate) / 100), 4);
									}
									$sale["order_tax_id"] = $order_tax_id;
									$sale["order_tax"] = $order_tax;
									$sale["total_tax"] = $order_tax;
								}
								$grand_total = $this->cus->formatDecimal(($total + $order_tax + $sale["shipping"] - $order_discount), 4);
								$sale["grand_total"] = $grand_total;
								
								
								if($this->Settings->accounting == 1){           
									$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
									$accTrans[$index][] = array(
										'transaction' => 'Sale',
										'transaction_date' => $sale["date"],
										'reference' => $sale["reference_no"],
										'account' => $saleAcc->ar_acc,
										'amount' => $grand_total,
										'narrative' => 'Sale',
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $sale["customer_id"],
									);
									
									if($order_discount > 0){
										$accTrans[$index][] = array(
											'transaction' => 'Sale',
											'transaction_date' => $sale["date"],
											'reference' => $sale["reference_no"],
											'account' => $saleAcc->sale_discount_acc,
											'amount' => $order_discount,
											'narrative' => 'Order Discount',
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $sale["customer_id"],
										);
									}
									if($order_tax > 0){
										$accTrans[$index][] = array(
											'transaction' => 'Sale',
											'transaction_date' => $sale["date"],
											'reference' => $sale["reference_no"],
											'account' => $saleAcc->vat_output,
											'amount' => -$order_tax,
											'narrative' => 'Order Tax',
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $sale["customer_id"],
										);
									}
									if($sale["shipping"] > 0){
										$accTrans[$index][] = array(
												'transaction' => 'Sale',
												'transaction_date' => $sale["date"],
												'reference' => $sale["reference_no"],
												'account' => $saleAcc->shipping_acc,
												'amount' => -$sale["shipping"],
												'narrative' => 'Shipping',
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $sale["customer_id"],
											);
									}
									
								}
								$sales[$index] = $sale;
							}
						}
					}
					
				}
			}
			if (empty($sales) || empty($sale_items)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            }else if ($this->Settings->overselling != 1) {
				if($qty_stockmoves){
					foreach($qty_stockmoves as $product_id => $qty){
						$p_balance = $this->site->getProductQty($product_id,$warehouse_id);
						if($p_balance->quantity < $qty){
							$product = $this->site->getProductByID($product_id);
							$this->session->set_flashdata('error', lang("product_code") . " (" . $final['product_code'] . "). " . lang("out_of_stock"));
							redirect("sales/import_sale");
						}
					}
				}
			}
		}
        if ($this->form_validation->run() == true && $this->sales_model->importSale($sales,$sale_items,$stockmoves,$accTrans)) {
            $this->session->set_flashdata('message', lang("sale_imported"));
            redirect(site_url('sales'));
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('import_sale')));
			$meta = array('page_title' => lang('import_sale'), 'bc' => $bc);
            $this->core_page('sales/import_sale', $meta, $this->data);
        }
    }
	
	
	public function agency_commission($biller_id = null)
	{
		$this->cus->checkPermissions('agency_commission-index');
		$this->load->library('pagination');
		$post = ($this->input->post()) ? $this->input->post() : $this->input->get();
		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$config = array();
        $config["base_url"] = base_url() . "sales/agency_commission";
        $config["total_rows"] = $this->sales_model->getSaleAgencyCommissionsRecordCounts();
        $config["per_page"] = isset($post['pagination']) && $post['pagination'] == 1?0:30;
		$config["uri_segment"] = 3;
		$config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = 'First';
        $config['last_link'] = 'Last';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$this->pagination->initialize($config);
		$this->data['sales'] = $this->sales_model->getAllSaleAgencyCommissions($config["per_page"], $page);
		$this->data['links'] = $this->pagination->create_links();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['users'] = $this->site->getAllUsers();
		$this->data['agencies'] = $this->sales_model->getAllAgencies();
        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('agency_commission')));
        $meta = array('page_title' => lang('agency_commission'), 'bc' => $bc);
		$this->core_page('sales/agency_commission', $meta, $this->data);
	}

	public function add_agency_commission_payment($id = false, $agency_id = false)
    {
		$this->cus->checkPermissions('agency_commission-add');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
		}
		$start_date = $this->input->get('start_date')?$this->input->get('start_date'):$this->input->post('start_date');
		$end_date = $this->input->get('end_date')?$this->input->get('end_date'):$this->input->post('end_date');
		$ids = explode('SaleID',$id);
		$agency_ids = explode('AgencyID',$agency_id);
		$sales = $this->sales_model->getSaleAgencyCommission($ids, false, $start_date, $end_date);
		$multiple = $this->sales_model->getSalesByBillers($ids);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->cus->GP['sales-agency_commission-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$total_amount = $this->input->post('amount-paid');
			$total_discount = $this->input->post('discount');
			$camounts = $this->input->post("c_amount");
			$photo = null;
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
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$multiple->row()->biller_id);
			$paid_currencies = array();
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$paid_currencies[$currency[$key]] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$cur_def = $this->site->getCurrencyByCode($this->Settings->default_currency);
			$c = isset($_POST['sale_id']) ? sizeof($_POST['sale_id']) : 0;
			for ($i = 0; $i < $c; $i++) {
				if($total_amount > 0){
					$index = $_POST['index'][$i];
					$agency_id = $_POST['agency_id'][$i];
					$sale_id = $_POST['sale_id'][$i];
					$sale = $this->sales_model->getSaleAgencyCommissionBySaleID($sale_id, $start_date, $end_date);
					$agency_details = $this->sales_model->getAgencyByID($agency_id);
					$agency_name = $agency_details->first_name.' '.$agency_details->last_name;
					$agency_commission = json_decode($sale->agency_commission);
					$agency_percent = json_decode($sale->agency_limit_percent);
					$agency_value_commission =  json_decode($sale->agency_value_commission);
					$agency_rate = $agency_commission[$index]?$agency_commission[$index]:'';
					$amount = $agency_value_commission[$index] == 1? $sale->grand_total : $sale->real_unit_price;
					$commission_invoice = ($agency_rate * $amount) / 100;
					
					// Last commission
					$last_commission = 0;
					$lpaid_percentage = ($sale->last_paid * 100) / $amount;
					if($lpaid_percentage > $agency_percent[$index]){
						$last_commission = $commission_invoice;
					}else{
						$last_commission = ($sale->last_paid / (($agency_percent[$index] * $amount)/100)) * $commission_invoice;
					}

					// Commission amount
					$commission_amount = 0;
                    $paid_percentage = ($sale->paid * 100) / $amount;
                    if($paid_percentage > $agency_percent[$index]){
						$commission_amount = $commission_invoice;
                    }else{
						$commission_amount = ($sale->paid / (($agency_percent[$index] * $amount)/100))* $commission_invoice;
					}

					$total_commission = $commission_amount + $last_commission;
					if($total_commission > $commission_invoice){
						$commission_amount = $commission_invoice - $last_commission;
					}

					// Commission paid
					$ppayments = $this->sales_model->getSaleAgencyPayments($sale_id, $agency_id, true);
					$commission_paid = 0;
					if($ppayments){
						foreach($ppayments as $ppayment){
							$commission_paid += (float)($ppayment->amount + $ppayment->discount);
						}
					}
					$commission_balance = $this->cus->formatDecimal($commission_amount - $commission_paid);
					if($commission_balance){
						$discount = $total_discount;
						$total = $commission_balance;
						$grand_total = $total - $discount;
						if($total_amount > $grand_total){
							$pay_amount = $grand_total;
							$total_amount = $total_amount - $grand_total;
						}else{
							$pay_amount = $total_amount;
							$total_amount = 0;
						}
						if($total_discount > $discount){
							$discount_amount = $discount;
							$total_discount = $total_discount - $discount;
						}else{
							$discount_amount = $total_discount;
							$total_discount = 0;
						}
						$currencies = array();
						if(!empty($camounts)){
							$total_paid = $pay_amount;
							foreach($paid_currencies as $cur_code => $paid_currencie){
								$paid_cur = $paid_currencie['amount'];
								if($paid_cur > 0){
									if($cur_code != $cur_def->code){
										if($paid_currencie['rate'] > $cur_def->rate){
											$paid_cur = $paid_cur / $paid_currencie['rate'];
										}else{
											$paid_cur = $paid_cur * $cur_def->rate;
										}
									}
									if($paid_cur >= $total_paid && $total_paid > 0){
										$paid_currencie['amount'] = $total_paid;
										if($cur_code != $cur_def->code){
											if($paid_currencie['rate'] > $cur_def->rate){
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) * $paid_currencie['rate'];
											}else{
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) / $cur_def->rate;
											}
										}else{
											$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid);
										}
										$total_paid = 0;
									}else{
										if($total_paid > 0){
											$paid_currencie['amount'] = $paid_cur;
											$paid_currencies[$cur_code]['amount'] = 0;
											$total_paid = $total_paid - $paid_cur;
										}else{
											$paid_currencie['amount'] = 0;
										}
									}
								}								
								if($cur_code != $cur_def->code){
									if($paid_currencie['rate'] > $cur_def->rate){
										$paid_currencie['amount'] = $paid_currencie['amount'] * $paid_currencie['rate'];
									}else{
										$paid_currencie['amount'] = $paid_currencie['amount'] / $cur_def->rate;
									}
								}
								$currencies[] = $paid_currencie;
							}
						}
						$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
						$paying_from = $cash_account->account_code;
						if($pay_amount > 0){
							$payment[] = array(
								'date' => $date,
								'transaction_id' => $sale_id,
								'transaction' => 'AgencyPayment',
								'agency_id'=> $agency_id,
								'agency_paid_date'=> $this->cus->fld($end_date),
								'reference_no' => $reference_no,
								'amount' => $pay_amount,
								'discount' => $discount_amount,
								'paid_by' => $this->input->post('paid_by'),
								'note' => $this->input->post('note'),
								'created_by' => $this->session->userdata('user_id'),
								'type' => 'sent',
								'currencies' => json_encode($currencies),
								'account_code' => $paying_from,
								'attachment' => $photo,
							);
						}

						//=====accounting=====//
							if($this->Settings->accounting == 1){
								$paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
								$accTrans[$sale->id.$agency_id][] = array(
										'transaction' => 'Payment',
										'transaction_date' => $date,
										'reference' => $reference_no,
										'account' => $paymentAcc->agency_commission_acc,
										'amount' => $pay_amount,
										'narrative' => 'Agency Commission Payment '.$agency_name.' - '.$sale->reference_no,
										'description' => $this->input->post('note'),
										'biller_id' => $sale->biller_id,
										'project_id' => $sale->project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $sale->customer_id,
										'agency_id' => $agency_id,
									);
								$accTrans[$sale->id.$agency_id][] = array(
										'transaction' => 'Payment',
										'transaction_date' => $date,
										'reference' => $reference_no,
										'account' => $paying_from,
										'amount' => $pay_amount * (-1),
										'narrative' => 'Agency Commission Payment '.$agency_name.' - '.$sale->reference_no,
										'description' => $this->input->post('note'),
										'biller_id' => $sale->biller_id,
										'project_id' => $sale->project_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $sale->customer_id,
										'agency_id' => $agency_id,
									);
							}
						//=====end accounting=====//

					}
				}
			}
            
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->sales_model->addSaleAgencyMultiPayment($payment, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			if($multiple->num_rows() > 1){
				$this->session->set_flashdata('error', lang("biller_different_cannot_select"));
				$this->cus->md();
			}
			$this->data['sales'] = $sales;
			$this->data['agency_ids'] = $agency_ids;
			$this->data['payment_ref'] = '';
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales/add_agency_commission_payment', $this->data);
        }
    }

	public function agency_payments($id = null, $agency_id = null)
    {
		$this->cus->checkPermissions('agency_commission-index');
		$this->data['payments'] = $this->sales_model->getSaleAgencyPayments($id, $agency_id);
        $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
        $this->load->view($this->theme . 'sales/agency_payments', $this->data);
    }

	public function delete_agency_payment($id = null)
    {
		$this->cus->checkPermissions('agency_commission-delete');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$opay = $this->sales_model->getPaymentByID($id);
        if ($this->sales_model->deletePayment($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}

	public function edit_agency_payment($id = null)
    {
        $this->cus->checkPermissions('agency_commission-edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->sales_model->getPaymentByID($id);
		$opay = $payment;
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			$sale_id = $this->input->post('sale_id');
			$sale = $this->sales_model->getInvoiceByID($sale_id);
            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
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
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
            $payment = array(
                'date' => $date,
                'transaction_id' => $sale_id,
				'transaction' => 'AgencyPayment',
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
				'discount' => $this->input->post('discount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
				'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
				'currencies' => json_encode($currencies),
				'account_code' => $paying_from,
			);
			
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
			
			//=====accounting=====//
				if($this->Settings->accounting == 1){
					$paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
					$accTrans[] = array(
							'transaction' => 'Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $this->input->post('reference_no'),
							'account' => $paymentAcc->agency_commission_acc,
							'amount' => $this->input->post('amount-paid'),
							'narrative' => 'Agency Commission Payment - '.$sale->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $sale->biller_id,
							'project_id' => $sale->project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $sale->customer_id,
						);
					$accTrans[] = array(
							'transaction' => 'Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $this->input->post('reference_no'),
							'account' => $paying_from,
							'amount' => $this->input->post('amount-paid') * (-1),
							'narrative' => 'Agency Commission Payment - '.$sale->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $sale->biller_id,
							'project_id' => $sale->project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $sale->customer_id,
						);
				}
			//=====end accounting=====//

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->sales_model->updatePayment($id, $payment, false, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$sale = $this->sales_model->getInvoiceByID($payment->sale_id);
			$this->data['inv'] = $sale;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$payment->account_code,'1');
			}
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales/edit_agency_payment', $this->data);
        }
    }
	
	public function sale_concretes($warehouse_id = null, $biller_id = NULL)
    {
		$this->cus->checkPermissions('index');
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		if($biller_id == 0){
			$biller_id = null;
		}
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;	
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('sale_concretes')));
		$meta = array('page_title' => lang('sale_concretes'), 'bc' => $bc);
        $this->core_page('sales/sale_concretes', $meta, $this->data);
    }
	
	public function getSaleConcretes($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions('index');
		$payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_payment" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_payment" data-target="#myModal"');
		$edit_link = anchor('sales/edit_sale_concrete/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale_concrete'), ' class="edit_sale_concrete" ');
		$delete_link = "<a href='#' class='po delete_sale' title='<b>" . $this->lang->line("delete_sale_concrete") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('sales/delete_sale_concrete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale_concrete') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $payments_link . '</li>
						<li>' . $add_payment_link . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		
		$this->datatables->select("
									sales.id as id,
									DATE_FORMAT(date, '%Y-%m-%d %T') as date,
									reference_no,
									customer,
									grand_total,
									IFNULL(total_return,0) as total_return,
									IFNULL(cus_payments.paid + IFNULL(total_return_paid,0),0) as paid,
									IFNULL(cus_payments.discount,0) as discount,
									ROUND((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") as balance,
									IF (
										(
											round((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") = 0
										),
										'paid',
										IF (
										(
											(grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))) = grand_total
										),
										'pending',
										'partial'
									)) AS payment_status,
									attachment
								")
							->from('sales')
							->join('(SELECT
										sale_id,
										SUM(ABS(grand_total)) AS total_return,
										SUM(paid) AS total_return_paid
									FROM
										'.$this->db->dbprefix('sales').' WHERE sale_status = "returned"
									GROUP BY
										sale_id) as cus_return', 'cus_return.sale_id=sales.id', 'left')
							->join('(SELECT
										sale_id,
										IFNULL(SUM(amount),0) AS paid,
										IFNULL(SUM(discount),0) AS discount
									FROM
										'.$this->db->dbprefix('payments').'
										
									GROUP BY
										sale_id) as cus_payments', 'cus_payments.sale_id=sales.id', 'left');
		$this->datatables->where('sales.type', "concrete");
	    if ($warehouse_id) {
			$this->datatables->where('sales.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
			$this->datatables->where('sales.biller_id', $biller_id);
		}	
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('sales.created_by', $this->session->userdata('user_id'));
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
			$this->datatables->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function add_sale_concrete(){
		$this->cus->checkPermissions('add');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('location', $this->lang->line("location"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller');
            $project_id = $this->input->post('project');
            $warehouse_id = $this->input->post('warehouse');
			$customer_id = $this->input->post('customer');
			$note = $this->input->post('note');
			$staff_note = $this->input->post('staff_note');
			$from_date = $this->cus->fld(trim($this->input->post('from_date')));
			$to_date = $this->cus->fld(trim($this->input->post('to_date')));
			$location_id = $this->input->post('location');
			$location = $this->sales_model->getCustomerLocationByID($location_id);
            $payment_term = $this->input->post('payment_term');
			$payment_term_info = $this->sales_model->getPaymentTermsByID($payment_term);
            if($payment_term_info){
				if($payment_term_info->term_type=='end_month'){
					$due_date = date("Y-m-t", strtotime($date));
				}else{
					$due_date =  date('Y-m-d', strtotime('+' . $payment_term_info->due_day . ' days', strtotime($date)));
				}
			}else{
				$due_date = null;
			}
			$biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
			$truck_charge = $this->input->post('truck_charge');
			$pump_charge = $this->input->post('pump_charge');
			$tax_detail = $this->site->getTaxRateByID($this->input->post('order_tax'));
			if($tax_detail && $tax_detail->rate > 0){
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tax_so',$biller_id);
			}else{
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so',$biller_id);
			}
			
			$i = isset($_POST['con_sale_id']) ? sizeof($_POST['con_sale_id']) : 0;
			$total = 0;
			$order_tax = 0;
			$order_discount = 0;
			$total_items = 0;
			$percentage = '%';
			$products = false;
			for ($r = 0; $r < $i; $r++) {
				$con_sale_id = $_POST['con_sale_id'][$r];
				$con_date = $this->cus->fsd($_POST['con_date'][$r]);
				$con_reference = $_POST['con_reference'][$r];
				$con_total = $_POST['con_total'][$r];
				$con_truck_charge = $_POST['con_truck_charge'][$r];
				$con_pump_charge = $_POST['con_pump_charge'][$r];
				$con_subtotal = $_POST['con_subtotal'][$r];
				$con_products[] = array(
					'con_sale_id' => $con_sale_id,
					'date' => $con_date,
					'reference' => $con_reference,
					'total' => $con_total,
					'truck_charge' => $con_truck_charge,
					'pump_charge' => $con_pump_charge,
					'subtotal' => $con_subtotal,
					
				);
				$concrete_sale_items = $this->sales_model->getConcreteSaleItemBySaleID($con_sale_id);
				if($concrete_sale_items){
					foreach($concrete_sale_items as $concrete_sale_item){
						$total_items += $concrete_sale_item->quantity;
						$products[] = array(
							'product_id' => $concrete_sale_item->product_id,
							'product_code' => $concrete_sale_item->product_code,
							'product_name' => $concrete_sale_item->product_name,
							'product_type' => $concrete_sale_item->product_type,
							'net_unit_price' => $concrete_sale_item->net_unit_price,
							'unit_price' => $concrete_sale_item->unit_price,
							'real_unit_price' => $concrete_sale_item->real_unit_price,
							'cost' => $concrete_sale_item->cost,
							'quantity' => $concrete_sale_item->quantity,
							'product_unit_id' => $concrete_sale_item->product_unit_id,
							'product_unit_code' => $concrete_sale_item->product_unit_code,
							'unit_quantity' => $concrete_sale_item->unit_quantity,
							'warehouse_id' => $concrete_sale_item->warehouse_id,
							'subtotal' => $concrete_sale_item->subtotal,
							'raw_materials' => $concrete_sale_item->raw_materials
						);
					}
				}
			
				$total += $con_subtotal;
			}
			if (!$con_products) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			} else {
				krsort($con_products);
			}
			if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = (($total) * (Float) ($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            } else {
                $order_discount_id = null;
            }
			
			if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = ((($total) - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
			$grand_total = ($total) + $order_tax - $order_discount;
			$data = array(
				'date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'project_id' => $project_id,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
				'staff_note' => $staff_note,
                'total' => $total,
				'order_tax_id' => $order_tax_id,
				'order_tax' => $order_tax,
				'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $order_discount,
                'grand_total' => $grand_total,
                'sale_status' => "completed",
				'total_items' => $total_items,
				'type' => 'concrete',
				'payment_status' => 'pending',
				'delivery_status' => 'completed',
                'payment_term' => $payment_term,
                'due_date' => $due_date,
				'paid' => 0,
				'created_by' => $this->session->userdata('user_id'),
				'from_date' => $from_date,
				'to_date' => $to_date,
				'location_id' => $location_id,
				'location_name' => $location->name,
            );
			if($this->Settings->accounting == 1){
				$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
				if($order_discount != 0){
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->sale_discount_acc,
						'amount' => $order_discount,
						'narrative' => 'Order Discount',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->ar_acc,
						'amount' => $order_discount * (-1),
						'narrative' => 'Order Discount',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				if($order_tax != 0){
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->vat_output,
						'amount' => $order_tax * (-1),
						'narrative' => 'Order Tax',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->ar_acc,
						'amount' => $order_tax ,
						'narrative' => 'Order Tax',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
			}

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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->sales_model->addSaleConcrete($data,$con_products,$products, $accTrans)) {	
            $this->session->set_flashdata('message', $this->lang->line("sale_concrete_added"));          
			redirect('sales/sale_concretes');
        } else {
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['customers'] = $this->sales_model->getCustomers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('sales/sale_concretes'), 'page' => lang('sale_concretes')), array('link' => '#', 'page' => lang('add_sale_concrete')));
			$meta = array('page_title' => lang('add_sale_concrete'), 'bc' => $bc);
            $this->core_page('sales/add_sale_concrete', $meta, $this->data);
        }
	}
	
	public function edit_sale_concrete($id = false){
		$this->cus->checkPermissions('edit');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('location', $this->lang->line("location"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->cus->GP['sales-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller');
            $project_id = $this->input->post('project');
            $warehouse_id = $this->input->post('warehouse');
			$customer_id = $this->input->post('customer');
			$note = $this->input->post('note');
			$staff_note = $this->input->post('staff_note');
			$from_date = $this->cus->fld(trim($this->input->post('from_date')));
			$to_date = $this->cus->fld(trim($this->input->post('to_date')));
			$location_id = $this->input->post('location');
			$location = $this->sales_model->getCustomerLocationByID($location_id);
			$payment_term = $this->input->post('payment_term');
			$payment_term_info = $this->sales_model->getPaymentTermsByID($payment_term);
            if($payment_term_info){
				if($payment_term_info->term_type=='end_month'){
					$due_date = date("Y-m-t", strtotime($date));
				}else{
					$due_date =  date('Y-m-d', strtotime('+' . $payment_term_info->due_day . ' days', strtotime($date)));
				}
			}else{
				$due_date = null;
			}
			$biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
			$truck_charge = $this->input->post('truck_charge');
			$pump_charge = $this->input->post('pump_charge');
			$tax_detail = $this->site->getTaxRateByID($this->input->post('order_tax'));
			if($tax_detail && $tax_detail->rate > 0){
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tax_so',$biller_id);
			}else{
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so',$biller_id);
			}
			
			$i = isset($_POST['con_sale_id']) ? sizeof($_POST['con_sale_id']) : 0;
			$total = 0;
			$order_tax = 0;
			$order_discount = 0;
			$total_items = 0;
			$percentage = '%';
			$products = false;
			for ($r = 0; $r < $i; $r++) {
				$con_sale_id = $_POST['con_sale_id'][$r];
				$con_date = $this->cus->fsd($_POST['con_date'][$r]);
				$con_reference = $_POST['con_reference'][$r];
				$con_total = $_POST['con_total'][$r];
				$con_truck_charge = $_POST['con_truck_charge'][$r];
				$con_pump_charge = $_POST['con_pump_charge'][$r];
				$con_subtotal = $_POST['con_subtotal'][$r];
				$con_products[] = array(
					'sale_id' => $id,
					'con_sale_id' => $con_sale_id,
					'date' => $con_date,
					'reference' => $con_reference,
					'total' => $con_total,
					'truck_charge' => $con_truck_charge,
					'pump_charge' => $con_pump_charge,
					'subtotal' => $con_subtotal,
					
				);
				$concrete_sale_items = $this->sales_model->getConcreteSaleItemBySaleID($con_sale_id);
				if($concrete_sale_items){
					foreach($concrete_sale_items as $concrete_sale_item){
						$total_items += $concrete_sale_item->quantity;
						$products[] = array(
							'sale_id' => $id,
							'product_id' => $concrete_sale_item->product_id,
							'product_code' => $concrete_sale_item->product_code,
							'product_name' => $concrete_sale_item->product_name,
							'product_type' => $concrete_sale_item->product_type,
							'net_unit_price' => $concrete_sale_item->net_unit_price,
							'unit_price' => $concrete_sale_item->unit_price,
							'real_unit_price' => $concrete_sale_item->real_unit_price,
							'cost' => $concrete_sale_item->cost,
							'quantity' => $concrete_sale_item->quantity,
							'product_unit_id' => $concrete_sale_item->product_unit_id,
							'product_unit_code' => $concrete_sale_item->product_unit_code,
							'unit_quantity' => $concrete_sale_item->unit_quantity,
							'warehouse_id' => $concrete_sale_item->warehouse_id,
							'subtotal' => $concrete_sale_item->subtotal,
							'raw_materials' => $concrete_sale_item->raw_materials
						);
					}
				}
			
				$total += $con_subtotal;
			}
			if (!$con_products) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			} else {
				krsort($con_products);
			}
			if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = (($total) * (Float) ($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            } else {
                $order_discount_id = null;
            }
			
			if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = ((($total) - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
			$grand_total = ($total) + $order_tax - $order_discount;
			$data = array(
				'date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
				'project_id' => $project_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
				'staff_note' => $staff_note,
                'total' => $total,
				'order_tax_id' => $order_tax_id,
				'order_tax' => $order_tax,
				'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $order_discount,
                'grand_total' => $grand_total,
                'sale_status' => "completed",
				'total_items' => $total_items,
				'type' => 'concrete',
				'delivery_status' => 'completed',
                'payment_term' => $payment_term,
                'due_date' => $due_date,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'from_date' => $from_date,
				'to_date' => $to_date,
				'location_id' => $location_id,
				'location_name' => $location->name,
            );
			
			if($this->Settings->accounting == 1){
				$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
				if($order_discount != 0){
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_id' => $id,
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->sale_discount_acc,
						'amount' => $order_discount,
						'narrative' => 'Order Discount',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_id' => $id,
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->ar_acc,
						'amount' => $order_discount * (-1),
						'narrative' => 'Order Discount',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				if($order_tax != 0){
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_id' => $id,
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->vat_output,
						'amount' => $order_tax * (-1),
						'narrative' => 'Order Tax',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_id' => $id,
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->ar_acc,
						'amount' => $order_tax ,
						'narrative' => 'Order Tax',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
			}

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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->sales_model->updateSaleConcrete($id,$data,$con_products,$products,$accTrans)) {	
            $this->session->set_flashdata('message', $this->lang->line("sale_concrete_updated"));          
			redirect('sales/sale_concretes');
        } else {
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['sale'] = $this->sales_model->getSaleByID($id);
			$this->data['sale_items'] = $this->sales_model->getSaleConcreteItemBySaleID($id);
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['customers'] = $this->sales_model->getCustomers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('sales/sale_concretes'), 'page' => lang('sale_concretes')), array('link' => '#', 'page' => lang('edit_sale_concrete')));
			$meta = array('page_title' => lang('edit_sale_concrete'), 'bc' => $bc);
            $this->core_page('sales/edit_sale_concrete', $meta, $this->data);
        }
	}

	public function delete_sale_concrete($id = null)
    {
        $this->cus->checkPermissions('delete', true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->sales_model->deletSaleConcrete($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("sale_concrete_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('sale_concrete_deleted'));
			redirect('sales/sale_concretes');
		}
		
    }
	
	public function sale_concrete_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deletSaleConcrete($id);
                    }
                    $this->session->set_flashdata('message', lang("sale_concrete_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('returned'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('discount'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));
					$this->db->select("sales.id as id,
										DATE_FORMAT(date, '%Y-%m-%d %T') as date,
										reference_no,
										sales.biller,
										customer,
										grand_total,
										IFNULL(total_return,0) as total_return,
										IFNULL(cus_payments.paid + IFNULL(total_return_paid,0),0) as paid,
										IFNULL(cus_payments.discount,0) as discount,
										ROUND((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") as balance,
										IF (
											(
												round((grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))),".$this->Settings->decimals.") = 0
											),
											'paid',
											IF (
											(
												(grand_total-(IFNULL(cus_payments.paid,0))-(IFNULL(cus_payments.discount,0))-(IFNULL(cus_return.total_return + total_return_paid,0))) = grand_total
											),
											'pending',
											'partial'
										)) AS payment_status")
										->from('sales')
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
					$this->db->where_in("sales.id",$_POST['val']);
					$q = $this->db->get();
					$row = 2;
					if ($q->num_rows() > 0) {
						foreach (($q->result()) as $sale) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($sale->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $sale->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale->biller);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->customer);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->cus->formatDecimal($sale->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->formatDecimal($sale->total_return));
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->formatDecimal($sale->paid));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->formatDecimal($sale->discount));
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->cus->formatDecimal($sale->balance));
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($sale->payment_status));
							$row++;
						}
					}
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sale_concretes_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_sale_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function get_concrete_sales(){
		$biller_id = $this->input->get('biller_id');
		$project_id = $this->input->get('project_id');
		$warehouse_id = $this->input->get('warehouse_id');
		$customer_id = $this->input->get('customer_id');
		$location_id = $this->input->get('location_id');
		$from_date = $this->cus->fld(trim($this->input->get('from_date')));
		$to_date = $this->cus->fld(trim($this->input->get('to_date')));
		$sale_id = $this->input->get('sale_id');
		$concrete_sales = $this->sales_model->getConcreteSales($biller_id,$project_id,$warehouse_id,$customer_id,$location_id,$from_date,$to_date,$sale_id);
		echo json_encode($concrete_sales);
	}
	public function get_customer_locations(){
		$customer_id = $this->input->get('customer');
		$locations = $this->sales_model->getCustomerLocations($customer_id);
		$data = array('locations'=>$locations);
		echo json_encode($data);
	}
	public function modal_view_sale_concrete($id = false, $type = false){
		$this->cus->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $sale = $this->sales_model->getSaleByID($id);
        $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
        $this->data['sale'] = $sale;
		$this->data['truck_charge'] = $this->sales_model->getTruckChargeBySaleID($id);
		$this->data['created_by'] = $this->site->getUserByID($sale->created_by);
		$this->data['customer'] = $this->site->getCompanyByID($sale->customer_id);
		if($type == 'daily'){
			$this->data['sale_items'] = $this->sales_model->getConcreteDeliveryItemDailyBySaleID($id);;
			$this->load->view($this->theme . 'sales/modal_view_sale_concrete_daily', $this->data);
		}else if($type == 'short'){
			$this->data['sale_items'] = $this->sales_model->getConcreteDeliveryItemShortBySaleID($id);;
			$this->load->view($this->theme . 'sales/modal_view_sale_concrete_short', $this->data);
		}else {
			$this->data['sale_items'] = $this->sales_model->getConcreteDeliveryItemBySaleID($id);;
			$this->load->view($this->theme . 'sales/modal_view_sale_concrete', $this->data);
		}
		
	}
	
	public function get_customer_trucks(){
		$customer_id = $this->input->get('customer');
		$truck = $this->sales_model->getCustomerTrucks($customer_id);
		$data = array('truck'=>$truck);
		echo json_encode($data);
	}
	
	
	public function salesman_commissions($biller_id = NULL)
    {
		$this->cus->checkPermissions();
		if($biller_id == 0){
			$biller_id = null;
		}
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;	
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('salesman_commissions')));
		$meta = array('page_title' => lang('salesman_commissions'), 'bc' => $bc);
        $this->core_page('sales/salesman_commissions', $meta, $this->data);
    }
	public function getSalesmanCommissions($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions('salesman_commissions');
		$payments_link = anchor('sales/commission_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_salesman_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_payment" data-target="#myModal"');
		$add_payment_link = anchor('sales/add_salesman_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_salesman_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_link = anchor('sales/edit_salesman_commission/$1', '<i class="fa fa-edit"></i> ' . lang('edit_salesman_commission'), ' class="edit_salesman_commission" ');
		$delete_link = "<a href='#' class='po delete_salesman_commission' title='<b>" . $this->lang->line("delete_salesman_commission") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('sales/delete_salesman_commission/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_salesman_commission') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $payments_link . '</li>
						<li>' . $add_payment_link . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("
						salesman_commissions.id as id,
						DATE_FORMAT(".$this->db->dbprefix('salesman_commissions').".date, '%Y-%m-%d %T') as date,
						salesman_commissions.commission_type,
						salesman_commissions.salesman_group,
						DATE_FORMAT(".$this->db->dbprefix('salesman_commissions').".from_date, '%Y-%m-%d') as from_date,
						DATE_FORMAT(".$this->db->dbprefix('salesman_commissions').".to_date, '%Y-%m-%d') as to_date,
						IFNULL(".$this->db->dbprefix('salesman_commissions').".total_commission,0) as total_commission,
						IFNULL(".$this->db->dbprefix('salesman_commissions').".paid,0) as paid,
						(".$this->db->dbprefix('salesman_commissions').".total_commission - ".$this->db->dbprefix('salesman_commissions').".paid) as balance,
						salesman_commissions.status,
						attachment
					")
			->from("salesman_commissions");	
		if ($biller_id) {
			$this->datatables->where('salesman_commissions.biller_id', $biller_id);
		}	
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('salesman_commissions.created_by', $this->session->userdata('user_id'));
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('salesman_commissions.biller_id', $this->session->userdata('biller_id'));
		}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function salesman_commission_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete_salesman_commission', true);
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteSalesmanCommission($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("salesman_commission_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('salesman_commissions'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('commission_type'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('salesman_group'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('from_date'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('to_date'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('commission'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('status'));

					$this->db->select("
								salesman_commissions.id as id,
								DATE_FORMAT(".$this->db->dbprefix('salesman_commissions').".date, '%Y-%m-%d %T') as date,
								salesman_commissions.commission_type,
								salesman_commissions.salesman_group,
								DATE_FORMAT(".$this->db->dbprefix('salesman_commissions').".from_date, '%Y-%m-%d') as from_date,
								DATE_FORMAT(".$this->db->dbprefix('salesman_commissions').".to_date, '%Y-%m-%d') as to_date,
								IFNULL(".$this->db->dbprefix('salesman_commissions').".total_commission,0) as total_commission,
								IFNULL(".$this->db->dbprefix('salesman_commissions').".paid,0) as paid,
								(".$this->db->dbprefix('salesman_commissions').".total_commission - ".$this->db->dbprefix('salesman_commissions').".paid) as balance,
								salesman_commissions.status,
								attachment
							");
					$this->db->where_in("salesman_commissions.id",$_POST['val']);
					$q = $this->db->get("salesman_commissions");
					$row = 2;
					if ($q->num_rows() > 0) {
						foreach (($q->result()) as $commission) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($commission->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $commission->commission_type);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $commission->salesman_group);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->cus->hrsd($commission->from_date));
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->cus->hrsd($commission->to_date));
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->formatDecimal($commission->total_commission));
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->formatDecimal($commission->paid));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->formatDecimal($commission->balance));
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($commission->status));
							$row++;
						}
					}

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'salesman_commissions_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	
	public function add_salesman_commission(){
		$this->cus->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('commission_type', $this->lang->line("commission_type"), 'required');
		$this->form_validation->set_rules('salesman_group', $this->lang->line("salesman_group"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->cus->GP['sales-salesman_commission-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$commission_type = $this->input->post('commission_type');
			$biller_id = $this->input->post('biller');
            $project_id = $this->input->post('project');
			$salesman_group_id = $this->input->post('salesman_group');
			$salesman_group = $this->site->getSalesmanGroupsByID($salesman_group_id);
			$from_date = $this->cus->fsd(trim($this->input->post('from_date')));
			$to_date = $this->cus->fsd(trim($this->input->post('to_date')));
			$note = $this->input->post('note');				
			$i = isset($_POST['salesman_id']) ? sizeof($_POST['salesman_id']) : 0;
			$total_commission = 0;
			$sales_target_commissions = false;
			for ($r = 0; $r < $i; $r++) {
				$salesman_id = $_POST['salesman_id'][$r];
				$sale_id = $_POST['sale_id'][$r];
				$grand_total = $_POST['grand_total'][$r];
				$amount = $_POST['amount'][$r];
				$rate = $_POST['rate'][$r];
				$commission = $_POST['commission'][$r];
				$target = $_POST['target'][$r];
				$sale_ids = $_POST['sale_ids'][$r];
				$items[] = array(
					'sale_id' => $sale_id,
					'salesman_id' => $salesman_id,
					'grand_total' => $grand_total,
					'amount' => $amount,
					'rate' => $rate,
					'commission' => $commission,
					'target' => $target,
					'sale_ids' => $sale_ids
				);
				$total_commission += $commission;
				if(isset($sale_ids) && $sale_ids){
					$tsale_ids = explode(",",$sale_ids);
					if($tsale_ids){
						foreach($tsale_ids as $tsale_id){
							$tsale_id = explode("=",$tsale_id);
							if($tsale_id[1] > 0){
								$sales_target_commissions[] = array("sale_id"=>$tsale_id[0],"salesman_id"=>$salesman_id,"amount"=>$tsale_id[1]);
							}
						}
					}
				}
			}
			if (!$items) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			}
			$accTrans = false;
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$commissionAcc = $this->site->getAccountSettingByBiller($biller_id);
					$accTrans[] = array(
							'transaction' => 'Salesman Commission',
							'transaction_date' => $date,
							'account' => $commissionAcc->saleman_commission_acc,
							'amount' => $total_commission,
							'narrative' => 'Salesman Commission for '.$salesman_group->name,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id')
						);
					$accTrans[] = array(
							'transaction' => 'Salesman Commission',
							'transaction_date' => $date,
							'account' => $commissionAcc->ap_acc,
							'amount' => $total_commission * (-1),
							'narrative' => 'Salesman Commission for '.$salesman_group->name,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id')
						);
				}
			//=====end accountig=====//
			
			
			$data = array(
				'date' => $date,
                'biller_id' => $biller_id,
                'project_id' => $project_id,
				'commission_type' => $commission_type,
				'salesman_group' => $salesman_group->name,
                'salesman_group_id' => $salesman_group_id,
				'salesman_id' => $this->input->post("salesman"),
				'total_commission' => $total_commission,
				'note' => $note,
				'from_date' => $from_date,
				'to_date' => $to_date,
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->sales_model->addSalesmanCommission($data,$items,$sales_target_commissions,$accTrans)) {	
            $this->session->set_flashdata('message', $this->lang->line('salesman_commission_added'));          
			redirect('sales/salesman_commissions');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['salesman_groups'] = $this->site->getSalesmanGroups();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => site_url('sales/salesman_commissions'), 'page' => lang('salesman_commissions')), array('link' => '#', 'page' => lang('add_salesman_commission')));
			$meta = array('page_title' => lang('add_salesman_commission'), 'bc' => $bc);
            $this->core_page('sales/add_salesman_commission', $meta, $this->data);
        }
	}
	
	
	public function edit_salesman_commission($id = false){
		$this->cus->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('commission_type', $this->lang->line("commission_type"), 'required');
		$this->form_validation->set_rules('salesman_group', $this->lang->line("salesman_group"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->cus->GP['sales-salesman_commission-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$commission_type = $this->input->post('commission_type');
			$biller_id = $this->input->post('biller');
            $project_id = $this->input->post('project');
			$salesman_group_id = $this->input->post('salesman_group');
			$salesman_group = $this->site->getSalesmanGroupsByID($salesman_group_id);
			$from_date = $this->cus->fsd(trim($this->input->post('from_date')));
			$to_date = $this->cus->fsd(trim($this->input->post('to_date')));
			$note = $this->input->post('note');				
			$i = isset($_POST['salesman_id']) ? sizeof($_POST['salesman_id']) : 0;
			$total_commission = 0;
			$sales_target_commissions = false;
			for ($r = 0; $r < $i; $r++) {
				$salesman_id = $_POST['salesman_id'][$r];
				$sale_id = $_POST['sale_id'][$r];
				$grand_total = $_POST['grand_total'][$r];
				$amount = $_POST['amount'][$r];
				$rate = $_POST['rate'][$r];
				$commission = $_POST['commission'][$r];
				$target = $_POST['target'][$r];
				$sale_ids = $_POST['sale_ids'][$r];
				$items[] = array(
					'commission_id' => $id,
					'sale_id' => $sale_id,
					'salesman_id' => $salesman_id,
					'grand_total' => $grand_total,
					'amount' => $amount,
					'rate' => $rate,
					'commission' => $commission,
					'target' => $target,
					'sale_ids' => $sale_ids
				);
				$total_commission += $commission;
				if(isset($sale_ids) && $sale_ids){
					$tsale_ids = explode(",",$sale_ids);
					if($tsale_ids){
						foreach($tsale_ids as $tsale_id){
							$tsale_id = explode("=",$tsale_id);
							if($tsale_id[1] > 0){
								$sales_target_commissions[] = array("commission_id"=>$id,"salesman_id"=>$salesman_id,"sale_id"=>$tsale_id[0],"amount"=>$tsale_id[1]);
							}
						}
					}
				}
			}
			if (!$items) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			}
			$accTrans = false;
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$commissionAcc = $this->site->getAccountSettingByBiller($biller_id);
					$accTrans[] = array(
							'transaction' => 'Salesman Commission',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'account' => $commissionAcc->saleman_commission_acc,
							'amount' => $total_commission,
							'narrative' => 'Salesman Commission for '.$salesman_group->name,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id')
						);
					$accTrans[] = array(
							'transaction' => 'Salesman Commission',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'account' => $commissionAcc->ap_acc,
							'amount' => $total_commission * (-1),
							'narrative' => 'Salesman Commission for '.$salesman_group->name,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id')
						);
				}
			//=====end accountig=====//
			
			
			$data = array(
				'date' => $date,
                'biller_id' => $biller_id,
                'project_id' => $project_id,
				'commission_type' => $commission_type,
				'salesman_group' => $salesman_group->name,
                'salesman_group_id' => $salesman_group_id,
				'salesman_id' => $this->input->post("salesman"),
				'total_commission' => $total_commission,
				'note' => $note,
				'from_date' => $from_date,
				'to_date' => $to_date,
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->sales_model->updateSalesmanCommission($id,$data,$items,$sales_target_commissions,$accTrans)) {	
            $this->session->set_flashdata('message', $this->lang->line('salesman_commission_edited'));          
			redirect('sales/salesman_commissions');
        } else {
			$commission = $this->sales_model->getSalesmanCommissionByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['salesman_groups'] = $this->site->getSalesmanGroups();
			$this->data['commission'] = $commission;
			$this->data['commission_items'] = $this->sales_model->getSalesmanCommissionItems($id);
			$this->data['salesmans'] = $this->sales_model->getSalesmanByGroup($commission->salesman_group_id);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => site_url('sales/salesman_commissions'), 'page' => lang('salesman_commissions')), array('link' => '#', 'page' => lang('edit_salesman_commission')));
			$meta = array('page_title' => lang('edit_salesman_commission'), 'bc' => $bc);
            $this->core_page('sales/edit_salesman_commission', $meta, $this->data);
        }
	}

	public function delete_salesman_commission($id = null)
    {
        $this->cus->checkPermissions(NULL, true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->sales_model->deleteSalesmanCommission($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("salesman_commission_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('salesman_commission_deleted'));
			redirect('sales/salesman_commissions');
		}
    }
	
	public function get_salesman_sales(){
		$data = false;
		$biller_id = $this->input->get('biller_id');
		$salesman_group_id = $this->input->get('salesman_group_id');
		$salesman_id = $this->input->get('salesman_id') ? $this->input->get('salesman_id') : NULL;
		$from_date = $this->input->get('from_date') ? $this->cus->fsd($this->input->get('from_date')) : NULL;
		$to_date = $this->input->get('to_date') ? $this->cus->fsd($this->input->get('to_date')) : NULL;
		$project_id = $this->input->get('project_id') ? $this->input->get('project_id') : NULL;
		$commission_id = $this->input->get('commission_id') ? $this->input->get('commission_id') : NULL;
		$sales = $this->sales_model->getSalesmanSales($biller_id,$salesman_group_id,$salesman_id,$project_id,$from_date,$to_date,$commission_id);
		$l_sales = $this->sales_model->getLSalesmanSales($biller_id,$salesman_group_id,$salesman_id,$project_id,$from_date,$to_date,$commission_id);
		if($sales && $l_sales){
			$data = array_merge($sales,$l_sales);
		}else if($sales){
			$data = $sales;
		}else if($l_sales){
			$data = $l_sales;
		}
		echo json_encode($data);
	}
	
	public function get_salesman_targets(){
		$data = false;
		$biller_id = $this->input->get('biller_id');
		$salesman_group_id = $this->input->get('salesman_group_id');
		$salesman_id = $this->input->get('salesman_id') ? $this->input->get('salesman_id') : NULL;
		$from_date = $this->input->get('from_date') ? $this->cus->fsd($this->input->get('from_date')) : NULL;
		$to_date = $this->input->get('to_date') ? $this->cus->fsd($this->input->get('to_date')) : NULL;
		$project_id = $this->input->get('project_id') ? $this->input->get('project_id') : NULL;
		$commission_id = $this->input->get('commission_id') ? $this->input->get('commission_id') : NULL;
		$sales = $this->sales_model->getSalesmanTargets($biller_id,$salesman_group_id,$salesman_id,$project_id,$from_date,$to_date,$commission_id);
		$l_sales = $this->sales_model->getLSalesmanTargets($biller_id,$salesman_group_id,$salesman_id,$project_id,$from_date,$to_date,$commission_id);
		if($sales && $l_sales){
			$data = array_merge($sales,$l_sales);
		}else if($sales){
			$data = $sales;
		}else if($l_sales){
			$data = $l_sales;
		}
		echo json_encode($data);
	}
	
	public function modal_view_salesman_commission($id = false){
		$this->cus->checkPermissions('salesman_commissions', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $commission = $this->sales_model->getSalesmanCommissionByID($id);
		$this->data['commission'] = $commission;
		$this->data['commission_items'] = $this->sales_model->getSalesmanCommissionItems($id);
		$this->data['salesman_group'] = $this->site->getSalesmanGroupsByID($commission->salesman_group_id);
		$this->data['created_by'] = $this->site->getUserByID($commission->created_by);
		$this->data['biller'] = $this->site->getCompanyByID($commission->biller_id);
        $this->load->view($this->theme . 'sales/modal_view_salesman_commission', $this->data);
	}
	
	public function add_salesman_payment($id = null)
    {
        $this->cus->checkPermissions('saleman_commission-add', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
		$commission = $this->sales_model->getSalesmanCommissionByID($id);
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->cus->GP['sales-salesman_commission-date'] ) {
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
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$commission->biller_id);
			$paymentAcc = $this->site->getAccountSettingByBiller($commission->biller_id);
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			$payment = array(
                'date' => $date,
				'transaction' => "Salesman Commission",
				'transaction_id' => $id,
                'reference_no' => $reference_no,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->cus->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
                'type' => "sent",
				'account_code' => $paying_from,
				'currencies' => json_encode($currencies),
            );
			$accTrans = false;
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$accTrans[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->ap_acc,
							'amount' => $this->input->post('amount-paid'),
							'narrative' => "Salesman Commission Payment",
							'description' => $this->input->post('note'),
							'biller_id' => $commission->biller_id,
							'project_id' => $commission->project_id,
							'user_id' => $this->session->userdata('user_id')
						);
					$accTrans[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paying_from,
							'amount' => $this->input->post('amount-paid') * (-1),
							'narrative' => "Salesman Commission Payment",
							'description' => $this->input->post('note'),
							'biller_id' => $commission->biller_id,
							'project_id' => $commission->project_id,
							'user_id' => $this->session->userdata('user_id')
						);
				}
			//=====end accountig=====//

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
        } elseif ($this->input->post('add_salesman_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->sales_model->addCommissionPayment($payment, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['commission'] = $commission;
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/add_salesman_payment', $this->data);
        }
    }
	
	public function edit_salesman_payment($id = null)
    {
        $this->cus->checkPermissions('saleman_commission-edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
		$payment = $this->sales_model->getCommissionPaymentByID($id);
		$commission = $this->sales_model->getSalesmanCommissionByID($payment->transaction_id);
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->cus->GP['sales-salesman_commission-date'] ) {
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
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$commission->biller_id);
			$paymentAcc = $this->site->getAccountSettingByBiller($commission->biller_id);
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			$payment = array(
                'date' => $date,
				'transaction' => "Salesman Commission",
				'transaction_id' => $commission->id,
                'reference_no' => $reference_no,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->cus->clear_tags($this->input->post('note')),
                'updated_by' => $this->session->userdata('user_id'),
                'type' => "sent",
				'account_code' => $paying_from,
				'currencies' => json_encode($currencies),
            );
			$accTrans = false;
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$accTrans[] = array(
							'transaction' => 'Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->ap_acc,
							'amount' => $this->input->post('amount-paid'),
							'narrative' => "Salesman Commission Payment",
							'description' => $this->input->post('note'),
							'biller_id' => $commission->biller_id,
							'project_id' => $commission->project_id,
							'user_id' => $this->session->userdata('user_id')
						);
					$accTrans[] = array(
							'transaction' => 'Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paying_from,
							'amount' => $this->input->post('amount-paid') * (-1),
							'narrative' => "Salesman Commission Payment",
							'description' => $this->input->post('note'),
							'biller_id' => $commission->biller_id,
							'project_id' => $commission->project_id,
							'user_id' => $this->session->userdata('user_id')
						);
				}
			//=====end accountig=====//

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
        } elseif ($this->input->post('add_salesman_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->sales_model->updateCommissionPayment($id, $payment, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_edited"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['commission'] = $commission;
			$this->data['payment'] = $payment;
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_salesman_payment', $this->data);
        }
    }
	
	public function delete_salesman_payment($id = null)
    {
        $this->cus->checkPermissions('delete_salesman_commission', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->sales_model->deleteCommissionPayment($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function commission_payments($id = null) {
        $this->cus->checkPermissions('salesman_commissions', true);
        $this->data['payments'] = $this->sales_model->getCommissionPaymentByCommission($id);
        $this->load->view($this->theme . 'sales/commission_payments', $this->data);
    }
	
	public function salesman_payment_note($id = null)
    {
        $this->cus->checkPermissions('salesman_commissions', true);
        $payment = $this->sales_model->getPaymentByID($id);
		$commission = $this->sales_model->getSalesmanCommissionByID($payment->transaction_id);
		$this->data['commission'] = $commission;
		$this->data['payment'] = $payment;
		$this->data['salesman_group'] = $this->site->getSalesmanGroupsByID($commission->salesman_group_id);
		$this->data['created_by'] = $this->site->getUserByID($payment->created_by);
		$this->data['biller'] = $this->site->getCompanyByID($commission->biller_id);
        $this->data['page_title'] = lang("salesman_payment_note");
        $this->load->view($this->theme . 'sales/salesman_payment_note', $this->data);
    }
	
	public function get_salesmans(){
		$salesman_group_id = $this->input->get('salesman_group_id');
		$data = $this->sales_model->getSalesmanByGroup($salesman_group_id);
		echo json_encode($data);
	}

    public function saleman_commission()
	{
		$this->cus->checkPermissions("saleman_commission-index");
        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('list_commissions')));
        $meta = array('page_title' => lang('saleman_commissions'), 'bc' => $bc);
        $this->core_page('sales/saleman_commission', $meta, $this->data);
	}
	
	public function getSalemanCommissions()
    {
        $this->cus->checkPermissions("saleman_commission-index");
        $add_payment = anchor('sales/add_commission_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_payment" data-target="#myModal"');
		$payments_link = anchor('sales/saleman_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_payment" data-target="#myModal"');
		$action = '<div class="text-center"><div class="btn-group text-left">'
				. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
				. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $add_payment . '</li>
				<li>' . $payments_link . '</li>
			</ul>
		</div></div>';

		$this->load->library('datatables');
		$this->datatables->select("
					sales.id as id,
					sales.date,
					sales.reference_no,
					sales.customer,
					users.saleman_group as group_name, 
					IFNULL(concat(first_name,' ', last_name),'N/A') as saleman, 
					(SUM(grand_total) - IFNULL(cus_returns.amount_return,0)) as grand_total,
					sales.saleman_commission,
					concat((SUM(grand_total) - IFNULL(cus_returns.amount_return,0)),'_',".$this->db->dbprefix('sales').".saleman_commission) as commission,
					IFNULL(payment.paid_commission, 0) as paid_commission,
					'0' as balance,
					'pending' as payment_status
					")
		->from('sales')
		->join("users","users.id = saleman_id AND users.saleman=1","inner")
		->join("(SELECT sum(amount) as paid_commission, transaction_id FROM ".$this->db->dbprefix('payments')." WHERE transaction='Saleman Commission' GROUP BY transaction_id) as payment","payment.transaction_id = sales.id","left")
		->join('(SELECT sum(abs(grand_total)) as amount_return, sale_id FROM '.$this->db->dbprefix("sales").' WHERE sale_status = "returned" GROUP BY sale_id) as cus_returns','cus_returns.sale_id = sales.id','left')
		->join('sale_items','sales.id=sale_items.sale_id','left')
		->where("saleman_id <>", 0)	
		->where("sales.sale_status !=","return")
		->where("IFNULL(".$this->db->dbprefix('sales').".saleman_commission,0) <>",0)
		->group_by("sales.id");

			
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('sales.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('sales.created_by', $this->session->userdata('user_id'));
		}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function add_commission_payment($id = null)
    {
        $this->cus->checkPermissions('saleman_commission-add', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$ids = explode('SaleID',$id);
		$sale = $this->sales_model->getMultiInvSaleman($ids);
		$multiple = $this->sales_model->getSalesByBillers($ids);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-saleman_commission-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$total_amount = $this->input->post('amount-paid');
			$camounts = $this->input->post("c_amount");
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
            }
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$multiple->row()->biller_id);
			$paid_currencies = array();
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$paid_currencies[$currency[$key]] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$cur_def = $this->site->getCurrencyByCode($this->Settings->default_currency);
			for($i=0; $i<count($ids); $i++){
				if($total_amount > 0){
					$saleInfo = $this->sales_model->getInvoiceByID($ids[$i]);
					if($saleInfo){
						$paid_commission = $this->sales_model->getMultiInvSaleman($ids[$i],true);
						$commission_amount = 0;
						$dpos = strpos($saleInfo->saleman_commission, '%');
						if ($dpos !== false) {
							$pds = explode("%", $saleInfo->saleman_commission);
							$commission_amount = $saleInfo->grand_total * $pds[0];
							if($commission_amount > 0){
								$commission_amount = $commission_amount / 100;
							}
						} else {
							$commission_amount = $saleInfo->saleman_commission;
						}
						$grand_total = $commission_amount - $paid_commission->paid_commission;
						if($total_amount > $grand_total){
							$pay_amount = $grand_total;
							$total_amount = $total_amount - $grand_total;
						}else{
							$pay_amount = $total_amount;
							$total_amount = 0;
						}
						$currencies = array();
						if(!empty($camounts)){
							$total_paid = $pay_amount;
							foreach($paid_currencies as $cur_code => $paid_currencie){
								$paid_cur = $paid_currencie['amount'];
								if($paid_cur > 0){
									if($cur_code != $cur_def->code){
										if($paid_currencie['rate'] > $cur_def->rate){
											$paid_cur = $paid_cur / $paid_currencie['rate'];
										}else{
											$paid_cur = $paid_cur * $cur_def->rate;
										}
									}
									if($paid_cur >= $total_paid && $total_paid > 0){
										$paid_currencie['amount'] = $total_paid;
										if($cur_code != $cur_def->code){
											if($paid_currencie['rate'] > $cur_def->rate){
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) * $paid_currencie['rate'];
											}else{
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) / $cur_def->rate;
											}
										}else{
											$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid);
										}
										$total_paid = 0;
									}else{
										if($total_paid > 0){
											$paid_currencie['amount'] = $paid_cur;
											$paid_currencies[$cur_code]['amount'] = 0;
											$total_paid = $total_paid - $paid_cur;
										}else{
											$paid_currencie['amount'] = 0;
										}
									}
								}								
								if($cur_code != $cur_def->code){
									if($paid_currencie['rate'] > $cur_def->rate){
										$paid_currencie['amount'] = $paid_currencie['amount'] * $paid_currencie['rate'];
									}else{
										$paid_currencie['amount'] = $paid_currencie['amount'] / $cur_def->rate;
									}
								}
								$currencies[] = $paid_currencie;
							}
						}

						$paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
						$bank_name="";
						$account_name="";
						$account_number="";
						$cheque_number="";
						$cheque_date="";
						if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
							$paying_to = $paymentAcc->customer_deposit_acc;
						}else{
							$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
							$paying_to = $cash_account->account_code;
							if($cash_account->type=="bank"){
								$bank_name = $cash_account->name;
								$account_name = $this->input->post('account_name');
								$account_number = $this->input->post('account_name');
							}else if($cash_account->type=="cheque"){
								$bank_name = $this->input->post('bank_name');
								$cheque_number = $this->input->post('cheque_number');
								$cheque_date = $this->cas->fsd($this->input->post('cheque_date'));
							}
						}
									
					
						if($grand_total > 0){
								$payment[] = array(
								'date' => $date,
								'sale_id' => $saleInfo->id,
								'transaction_id' => $saleInfo->id,
								'transaction' => 'Saleman Commission',
								'reference_no' => $reference_no,
								'amount' => $pay_amount,
								'paid_by' => $this->input->post('paid_by'),
								'cheque_no' => $this->input->post('cheque_no'),
								'cc_no' => $this->input->post('pcc_no'),
								'cc_holder' => $this->input->post('pcc_holder'),
								'cc_month' => $this->input->post('pcc_month'),
								'cc_year' => $this->input->post('pcc_year'),
								'cc_type' => $this->input->post('pcc_type'),
								'note' => $this->input->post('note'),
								'created_by' => $this->session->userdata('user_id'),
								'type' => 'sent',
								'currencies' => json_encode($currencies),
								'account_code' => $paying_to,
								'attachment' => $photo,
							);


							//=====accountig=====//
								if($this->Settings->accounting == 1){
									$paymentAcc = $this->site->getAccountSettingByBiller($saleInfo->biller_id);
									$accTrans[$saleInfo->id][] = array(
											'transaction' => 'Payment',
											'transaction_date' => $date,
											'reference' => $reference_no,
											'account' => $paymentAcc->saleman_commission_acc,
											'amount' => $pay_amount,
											'narrative' => 'Saleman Commission Payment '.$saleInfo->reference_no,
											'description' => $this->input->post('note'),
											'biller_id' => $saleInfo->biller_id,
											'project_id' => $saleInfo->project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $saleInfo->customer_id,
										);
									$accTrans[$saleInfo->id][] = array(
											'transaction' => 'Payment',
											'transaction_date' => $date,
											'reference' => $reference_no,
											//'account' => $this->input->post('paying_from'),
											'account' => $paying_to,
											'amount' => $pay_amount * (-1),
											'narrative' => 'Saleman Commission Payment '.$saleInfo->reference_no,
											'description' => $this->input->post('note'),
											'biller_id' => $saleInfo->biller_id,
											'project_id' => $saleInfo->project_id,
											'user_id' => $this->session->userdata('user_id'),
											'customer_id' => $saleInfo->customer_id,
										);
								}
							//=====end accountig=====//
						}
					}
				}

			}
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addCommissionPayment($payment, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			if($multiple->num_rows() > 1){
				$this->session->set_flashdata('error', lang("biller_multi_cannot_add"));
				$this->cus->md();
			}
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$this->Settings->default_cash,'1');
			}
            $this->data['inv'] = $sale;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales/add_commission_payment', $this->data);
        }
    }
	
	public function edit_commission_payment($id = null)
    {
        $this->cus->checkPermissions('saleman_commission-edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->sales_model->getPaymentByID($id);
        if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') {
            $this->session->set_flashdata('error', lang('x_edit_payment'));
            $this->cus->md();
        }
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			$sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
            if ($this->Owner || $this->Admin  || $this->cus->GP['sales-saleman_commission-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
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
			$paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
			$bank_name="";
			$account_name="";
			$account_number="";
			$cheque_number="";
			$cheque_date="";
			if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
				$paying_to = $paymentAcc->customer_deposit_acc;
			}else{
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_to = $cash_account->account_code;
				if($cash_account->type=="bank"){
					$bank_name = $cash_account->name;
					$account_name = $this->input->post('account_name');
					$account_number = $this->input->post('account_name');
				}else if($cash_account->type=="cheque"){
					$bank_name = $this->input->post('bank_name');
					$cheque_number = $this->input->post('cheque_number');
					$cheque_date = $this->cas->fsd($this->input->post('cheque_date'));
				}
			}
            $payment = array(
                'date' => $date,
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
				'currencies' => json_encode($currencies),
				'account_code' => $paying_to,
            );

			//=====accountig=====//
					if($this->Settings->accounting == 1){
						$paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
						$accTranPayments[] = array(
								'transaction_id' => $id,
								'transaction' => 'Payment',
								'transaction_date' => $date,
								'reference' => $this->input->post('reference_no'),
								'account' => $paymentAcc->saleman_commission_acc,
								'amount' => $this->input->post('amount-paid'),
								'narrative' => 'Saleman Commission Payment '.$sale->return_sale_ref,
								'description' => $this->input->post('note'),
								'biller_id' => $sale->biller_id,
								'project_id' => $sale->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale->customer_id,
							);

						$accTranPayments[] = array(
								'transaction_id' => $id,
								'transaction' => 'Payment',
								'transaction_date' => $date,
								'reference' => $this->input->post('reference_no'),
								'account' => $paying_to,
								 'amount' => -$this->input->post('amount-paid'),
								'narrative' => 'Saleman Commission Payment '.$sale->return_sale_ref,
								'description' => $this->input->post('note'),
								'biller_id' => $sale->biller_id,
								'project_id' => $sale->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $sale->customer_id,
							);

					}
				//=====end accountig=====//

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

        } elseif ($this->input->post('edit_payment_return')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }


        if ($this->form_validation->run() == true && $this->sales_model->updateCommissionPayment($id, $payment, $accTranPayments)) {

			$this->session->set_flashdata('message', lang("payment_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

			$sale = $this->sales_model->getMultiInvSaleman($payment->transaction_id,true);
			$this->data['inv'] = $sale;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$payment->account_code,'1');
			}
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales/edit_commission_payment', $this->data);
        }
    }
    public function payment_note_commission($id = null)
    {
       $this->cus->checkPermissions('payments', true);
        $payment = $this->sales_model->getPaymentByID($id);
		if($payment->transaction=='Saleman Commission'){
			$this->data['saleman'] = $this->site->getUser($inv->saleman_id);
			$inv = $this->sales_model->getInvoiceByID($payment->transaction_id);
			$inv_payments = $this->sales_model->getCommissionPaymentsByRef($payment->reference_no,$payment->date);
		}else{
			$inv = $this->sales_model->getInvoiceByID($payment->sale_id);
			$inv_payments = $this->sales_model->getPaymentsByRef($payment->reference_no,$payment->date);
		}
		
     
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['customers'] = $this->site->getCompanyByID($inv->customer_ids);
        $this->data['inv'] = $inv;
		$this->data['inv_payments'] = $inv_payments;
        $this->data['payment'] = $payment;
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($payment->created_by);
        $this->data['page_title'] = lang("payment_note");
		
		
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Sale Payment',$payment->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Sale Payment',$payment->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'sales/payment_note_commission', $this->data);
    }
	
	public function delete_Commision_payment($id = null)
    {
        $this->cus->checkPermissions('saleman_commission-delete');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->sales_model->deletePayment($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}

	public function saleman_payments($id = null)
    {
        $this->cus->checkPermissions('saleman_commission-index');
        $this->data['payments'] = $this->sales_model->getSalemanPayments($id);
        $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
        $this->load->view($this->theme . 'sales/payment_commission', $this->data);
    }
	
	public function saleman_commission_action()
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])){
                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('saleman_commissions'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('group'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('saleman'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('rate'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('amount'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
                    $row = 2;
					$grand_total = 0;
					$total_com = 0;
					$total_paid = 0;
					$total_balance = 0;
                    foreach ($_POST['val'] as $id){
						$sale = $this->sales_model->getMultiInvSaleman($id, true);
						$commission_amount = 0;
						$balance = 0;
						$dpos = strpos($sale->saleman_commission, '%');
						if ($dpos !== false) {
							$pds = explode("%", $sale->saleman_commission);
							$commission_amount = $sale->grand_total * $pds[0];
							if($commission_amount > 0){
								$commission_amount = $commission_amount / 100;
							}
						} else {
							$commission_amount = $sale->saleman_commission;
						}
						$balance = $commission_amount - $sale->paid_commission;
						
						if($this->cus->formatDecimal($sale->paid_commission) == 0 ){
							$status = lang('pending');
						}else if($this->cus->formatDecimal($sale->paid_commission) == $this->cus->formatDecimal($commission_amount)){
							$status = lang('completed');
						}else{
							$status = lang('partial');
						}
						
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($sale->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sale->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->group_name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->saleman);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->formatMoney($sale->grand_total));
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $sale->saleman_commission);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->formatMoney($commission_amount));
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->cus->formatMoney($sale->paid_commission));
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->cus->formatMoney($balance));
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, $status);
						
						$row++;
						
						$grand_total += $sale->grand_total;
						$total_com += $commission_amount;
						$total_paid += $sale->paid_commission;
						$total_balance += $balance;
                    }
					
					$this->excel->getActiveSheet()->getStyle("F" . $row . ":J" . $row)->getBorders()
							->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
					

					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->formatMoney($grand_total));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->cus->formatMoney($total_com));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->cus->formatMoney($total_paid));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->cus->formatMoney($total_balance));	
					
					
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'saleman_commission_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }
                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');
                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');
                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_maintenances_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function receive_payments($biller_id = NULL)
    {
		$this->cus->checkPermissions();
		if($biller_id == 0){
			$biller_id = null;
		}
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;	
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('receive_payments')));
		$meta = array('page_title' => lang('receive_payments'), 'bc' => $bc);
        $this->core_page('sales/receive_payments', $meta, $this->data);
    }
	
	public function getReceivePayments($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions('receive_payments');
		$edit_link = anchor('sales/edit_receive_payment/$1', '<i class="fa fa-edit"></i> ' . lang('edit_receive_payment'), ' class="edit_receive_payment" ');
		$delete_link = "<a href='#' class='po delete_sale' title='<b>" . $this->lang->line("delete_receive_payment") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('sales/delete_receive_payment/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_receive_payment') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("
									receive_payments.id as id,
									DATE_FORMAT(".$this->db->dbprefix('receive_payments').".date, '%Y-%m-%d %T') as date,
									receive_payments.reference_no,
									DATE_FORMAT(".$this->db->dbprefix('receive_payments').".from_date, '%Y-%m-%d') as from_date,
									DATE_FORMAT(".$this->db->dbprefix('receive_payments').".to_date, '%Y-%m-%d') as to_date,
									receive_payments.amount,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									receive_payments.status,
									receive_payments.attachment
								")
							->join('users','users.id = receive_payments.created_by','left')
							->from('receive_payments');
		if ($biller_id) {
			$this->datatables->where('receive_payments.biller_id', $biller_id);
		}	
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('receive_payments.created_by', $this->session->userdata('user_id'));
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('receive_payments.biller_id', $this->session->userdata('biller_id'));
		}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	
	public function add_receive_payment(){
		$this->cus->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->cus->GP['receive_payments-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller');
            $paid_by = $this->input->post('paid_by');
			$received_by = $this->input->post('received_by');
			$note = $this->input->post('note');
			$from_date = $this->cus->fld(trim($this->input->post('from_date')));
			$to_date = $this->cus->fld(trim($this->input->post('to_date')));
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rp',$biller_id);
			$i = isset($_POST['payment_id']) ? sizeof($_POST['payment_id']) : 0;
			$total = 0;
			$items = false;
			for ($r = 0; $r < $i; $r++) {
				$payment_id = $_POST['payment_id'][$r];
				$payment_date = $_POST['payment_date'][$r];
				$sale_ref = $_POST['sale_ref'][$r];
				$payment_ref = $_POST['payment_ref'][$r];
				$payment_paid_by = $_POST['payment_paid_by'][$r];
				$payment_amount = $_POST['payment_amount'][$r];
				$payment_created_by = $_POST['payment_created_by'][$r];
				$customer = $_POST['customer'][$r];
				$items[] = array(
					'payment_id' => $payment_id,
					'payment_date' => $payment_date,
					'sale_ref' => $sale_ref,
					'payment_ref' => $payment_ref,
					'payment_paid_by' => $payment_paid_by,
					'payment_amount' => $payment_amount,
					'payment_created_by' => $payment_created_by,
					'customer' => $customer,
					
				);
				$total += $payment_amount;
			}
			if (!$items) {
				$this->form_validation->set_rules('payment', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'reference_no' => $reference_no,
                'from_date' => $from_date,
				'to_date' => $to_date,
				'received_by' => $received_by,
                'paid_by' => $paid_by,
				'amount' => $total,
				'note' => $note,
				'status' => "pending",
				'created_by' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->sales_model->addReceivePyament($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("receive_payment_added"));          
			redirect('sales/receive_payments');
        } else {
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['users'] = $this->site->getAllUsers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('sales/receive_payments'), 'page' => lang('receive_payments')), array('link' => '#', 'page' => lang('add_receive_payment')));
			$meta = array('page_title' => lang('add_receive_payment'), 'bc' => $bc);
            $this->core_page('sales/add_receive_payment', $meta, $this->data);
        }
	}
	
	public function edit_receive_payment($id = false){
		$this->cus->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->cus->GP['receive_payments-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller');
            $paid_by = $this->input->post('paid_by');
			$received_by = $this->input->post('received_by');
			$note = $this->input->post('note');
			$from_date = $this->cus->fld(trim($this->input->post('from_date')));
			$to_date = $this->cus->fld(trim($this->input->post('to_date')));
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rp',$biller_id);
			$i = isset($_POST['payment_id']) ? sizeof($_POST['payment_id']) : 0;
			$total = 0;
			$items = false;
			for ($r = 0; $r < $i; $r++) {
				$payment_id = $_POST['payment_id'][$r];
				$payment_date = $_POST['payment_date'][$r];
				$sale_ref = $_POST['sale_ref'][$r];
				$payment_ref = $_POST['payment_ref'][$r];
				$payment_paid_by = $_POST['payment_paid_by'][$r];
				$payment_amount = $_POST['payment_amount'][$r];
				$payment_created_by = $_POST['payment_created_by'][$r];
				$customer = $_POST['customer'][$r];
				$items[] = array(
					'receive_id' => $id,
					'payment_id' => $payment_id,
					'payment_date' => $payment_date,
					'sale_ref' => $sale_ref,
					'payment_ref' => $payment_ref,
					'payment_paid_by' => $payment_paid_by,
					'payment_amount' => $payment_amount,
					'payment_created_by' => $payment_created_by,
					'customer' => $customer,
					
				);
				$total += $payment_amount;
			}
			if (!$items) {
				$this->form_validation->set_rules('payment', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'reference_no' => $reference_no,
                'from_date' => $from_date,
				'to_date' => $to_date,
				'received_by' => $received_by,
                'paid_by' => $paid_by,
				'amount' => $total,
				'note' => $note,
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->sales_model->updateReceivePyament($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("receive_payment_edited"));          
			redirect('sales/receive_payments');
        } else {
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['users'] = $this->site->getAllUsers();
			$this->data['receive_payment'] = $this->sales_model->getReceivePaymentByID($id);
			$this->data['receive_payment_items'] = $this->sales_model->getReceivePaymentItems($id);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('sales/receive_payments'), 'page' => lang('receive_payments')), array('link' => '#', 'page' => lang('edit_receive_payment')));
			$meta = array('page_title' => lang('edit_receive_payment'), 'bc' => $bc);
            $this->core_page('sales/edit_receive_payment', $meta, $this->data);
        }
	}
	
	public function delete_receive_payment($id = null)
    {
        $this->cus->checkPermissions('delete_receive_payment', true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->sales_model->deleteReceivePayment($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("receive_payment_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('receive_payment_deleted'));
			redirect('sales/receive_payments');
		}
		
    }
	
	
	public function receive_payment_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete_receive_payment', true);
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteReceivePayment($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("receive_payments_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('receive_payments'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('from_date'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('to_date'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('amount'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));
					$this->db->select("
									receive_payments.id as id,
									DATE_FORMAT(".$this->db->dbprefix('receive_payments').".date, '%Y-%m-%d %T') as date,
									receive_payments.reference_no,
									DATE_FORMAT(".$this->db->dbprefix('receive_payments').".from_date, '%Y-%m-%d') as from_date,
									DATE_FORMAT(".$this->db->dbprefix('receive_payments').".to_date, '%Y-%m-%d') as to_date,
									receive_payments.amount,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									receive_payments.status,
									receive_payments.attachment
								")
							->join('users','users.id = receive_payments.created_by','left');
					$this->db->where_in("receive_payments.id",$_POST['val']);
					$q = $this->db->get("receive_payments");
					$row = 2;
					if ($q->num_rows() > 0) {
						foreach (($q->result()) as $receive_payment) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($receive_payment->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $receive_payment->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->cus->hrsd($receive_payment->from_date));
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->cus->hrsd($receive_payment->to_date));
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->cus->formatDecimal($receive_payment->amount));
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $receive_payment->created_by);
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($receive_payment->status));
							$row++;
						}
					}
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'receive_payments_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function modal_view_receive_payment($id = false){
		$this->cus->checkPermissions('receive_payments', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $receive_payment = $this->sales_model->getReceivePaymentByID($id);
		$this->data['receive_payment'] = $receive_payment;
		$this->data['receive_payment_items'] = $this->sales_model->getReceivePaymentItems($id);
		$this->data['created_by'] = $this->site->getUserByID($receive_payment->created_by);
		$this->data['biller'] = $this->site->getCompanyByID($receive_payment->biller_id);
        $this->load->view($this->theme . 'sales/modal_view_receive_payment', $this->data);
	}
	
	public function get_sale_payments(){
		$biller_id = $this->input->get('biller_id');
		$received_by = $this->input->get('received_by');
		$paid_by = $this->input->get('paid_by');
		$from_date = $this->cus->fld(trim($this->input->get('from_date')));
		$to_date = $this->cus->fld(trim($this->input->get('to_date')));
		$receive_id = $this->input->get('receive_id');
		$concrete_sales = $this->sales_model->getSalePayments($biller_id,$received_by,$from_date,$to_date,$paid_by,$receive_id);
		echo json_encode($concrete_sales);
	}

}
