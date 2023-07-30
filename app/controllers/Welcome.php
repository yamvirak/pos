<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->cus->md('login');
        }
        $this->load->library('form_validation');
        $this->load->model('db_model');
    }

    public function index()
    {
        if ($this->Settings->version == '2.3') {
            $this->session->set_flashdata('warning', 'Please complete your update by synchronizing your database.');
            redirect('sync');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['sales'] = $this->db_model->getLatestSales();
        $this->data['installments'] = $this->db_model->getLastestInstallments();
        $this->data['quotes'] = $this->db_model->getLastestQuotes();
        $this->data['purchases'] = $this->db_model->getLatestPurchases();
        $this->data['transfers'] = $this->db_model->getLatestTransfers();
        $this->data['customers'] = $this->db_model->getLatestCustomers();
        $this->data['suppliers'] = $this->db_model->getLatestSuppliers();
        $this->data['chatData'] = $this->db_model->getChartData();
        $this->data['chatDataExpense'] = $this->db_model->getChartDataExpense();
        $this->data['stock'] = $this->db_model->getStockValue();
        $this->data['bs'] = $this->db_model->getBestSeller();
        $lmsdate = date('Y-m-d', strtotime('first day of last month')) . ' 00:00:00';
        $lmedate = date('Y-m-d', strtotime('last day of last month')) . ' 23:59:59';
        $this->data['lmbs'] = $this->db_model->getBestSeller($lmsdate, $lmedate);
        $this->data['chatData'] = $this->db_model->getChartData();
        $this->data['chatDataExpense'] = $this->db_model->getChartDataExpense();
        $this->data['todayPurchase'] = $this->db_model->getPurchaseProfit(true,false,false);
        $this->data['lastMonthPurchase'] = $this->db_model->getPurchaseProfit(false,true,false);
        $this->data['thisMonthPurchase'] = $this->db_model->getPurchaseProfit(false,false,true);
        $this->data['todayExpenses'] = $this->db_model->getExpenses(true,false,false);
        $this->data['lastMonthExpenses'] = $this->db_model->getExpenses(false,true,false);
        $this->data['thisMonthExpenses'] = $this->db_model->getExpenses(false,false,true);

        $this->data['todayCheckIn'] = $this->db_model->getCheckIns(true,false,false);
        $this->data['lastMonthCheckIn'] = $this->db_model->getCheckIns(false,true,false);
        $this->data['thisMonthCheckIn'] = $this->db_model->getCheckIns(false,false,true);

        $this->data['todayCheckOut'] = $this->db_model->getCheckOuts(true,false,false);
        $this->data['lastMonthCheckOut'] = $this->db_model->getCheckOuts(false,true,false);
        $this->data['thisMonthCheckOut'] = $this->db_model->getCheckOuts(false,false,true);

        $this->data['todayReservation'] = $this->db_model->getReservations(true,false,false);
        $this->data['lastMonthReservation'] = $this->db_model->getReservations(false,true,false);
        $this->data['thisMonthReservation'] = $this->db_model->getReservations(false,false,true);

        $this->data['todayReservation'] = $this->db_model->getReservations(true,false,false);
        $this->data['lastMonthReservation'] = $this->db_model->getReservations(false,true,false);
        $this->data['thisMonthReservation'] = $this->db_model->getReservations(false,false,true);

        $this->data['todayData'] = $this->db_model->getSalesProfit(true,false,false);
        $this->data['lastMonthData'] = $this->db_model->getSalesProfit(false,true,false);
        $this->data['thisMonthData'] = $this->db_model->getSalesProfit(false,false,true);

        $lmsdate = date('Y-m-d', strtotime('first day of last month')) . ' 00:00:00';
        $lmedate = date('Y-m-d', strtotime('last day of last month')) . ' 23:59:59';
        $this->data['lmbs'] = $this->db_model->getBestSeller($lmsdate, $lmedate);
        $bc = array(array('link' => '#', 'page' => lang('home')));
        $meta = array('page_title' => lang('home'), 'bc' => $bc);
        $this->core_page('dashboard', $meta, $this->data);

    }

    function promotions()
    {
        $this->load->view($this->theme . 'promotions', $this->data);
    }

    function image_upload()
    {
        if (DEMO) {
            $error = array('error' => $this->lang->line('disabled_in_demo'));
            $this->cus->send_json($error);
            exit;
        }
        $this->security->csrf_verify();
        if (isset($_FILES['file'])) {
            $this->load->library('upload');
            $config['upload_path'] = 'assets/uploads/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = '500';
            $config['max_width'] = $this->Settings->iwidth;
            $config['max_height'] = $this->Settings->iheight;
            $config['encrypt_name'] = TRUE;
            $config['overwrite'] = FALSE;
            $config['max_filename'] = 25;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $error = $this->upload->display_errors();
                $error = array('error' => $error);
                $this->cus->send_json($error);
                exit;
            }
            $photo = $this->upload->file_name;
            $array = array(
                'filelink' => base_url() . 'assets/uploads/images/' . $photo
            );
            echo stripslashes(json_encode($array));
            exit;

        } else {
            $error = array('error' => 'No file selected to upload!');
            $this->cus->send_json($error);
            exit;
        }
    }

    function set_data($ud, $value)
    {
        $this->session->set_userdata($ud, $value);
        echo true;
    }

    function hideNotification($id = NULL)
    {
        $this->session->set_userdata('hidden' . $id, 1);
        echo true;
    }

    function language($lang = false)
    {
        if ($this->input->get('lang')) {
            $lang = $this->input->get('lang');
        }
        //$this->load->helper('cookie');
        $folder = 'app/language/';
        $languagefiles = scandir($folder);
        if (in_array($lang, $languagefiles)) {
            $cookie = array(
                'name' => 'language',
                'value' => $lang,
                'expire' => '31536000',
                'prefix' => 'cus_',
                'secure' => false
            );
            $this->input->set_cookie($cookie);
        }
        redirect($_SERVER["HTTP_REFERER"]);
    }

    function toggle_rtl()
    {
        $cookie = array(
            'name' => 'rtl_support',
            'value' => $this->Settings->user_rtl == 1 ? 0 : 1,
            'expire' => '31536000',
            'prefix' => 'cus_',
            'secure' => false
        );
        $this->input->set_cookie($cookie);
        redirect($_SERVER["HTTP_REFERER"]);
    }

    function download($file)
    {
        if (file_exists('./files/'.$file)) {
            $this->load->helper('download');
            force_download('./files/'.$file, NULL);
            exit();
        }
        $this->session->set_flashdata('error', lang('file_x_exist'));
        redirect($_SERVER["HTTP_REFERER"]);
    }
    
    function style_view($style_view){
        $this->db_model->updateViewStyle($this->session->userdata('user_id'),$style_view);
        $_SESSION['style_view'] = $style_view;
    }

}
