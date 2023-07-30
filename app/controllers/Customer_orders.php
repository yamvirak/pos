<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_orders extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
		
		if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->cus->md('clogin');
        }		
		
        $this->lang->load('customer_orders_lang', $this->Settings->user_language);
        $this->load->library('form_validation');
		$this->digital_upload_path = 'files/';
		$this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '10240';
    }
	function index(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => '#', 'page' => lang('customer_orders')), array('link' => 'customer_orders', 'page' => lang('customer_orders')));
        $meta = array('page_title' => lang('customer_orders'), 'bc' => $bc);
        $this->core_page('customer_orders/index', $meta, $this->data);
	}


}
