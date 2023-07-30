<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->lang->load('auth', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->load->model('auth_model');
		$this->load->model('pos_model');
        $this->load->library('ion_auth');
    }

    function index()
    {

        if (!$this->loggedIn) {
            redirect('login');
        } else {
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    function users()
    {
        if ( ! $this->loggedIn) {
            redirect('login');
        }
        
		$this->cus->checkPermissions('index');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('users')));
        $meta = array('page_title' => lang('users'), 'bc' => $bc);
        $this->core_page('auth/index', $meta, $this->data);
    }

    function getUsers()
    {
        $this->cus->checkPermissions('index');
        $this->load->library('datatables');
		
		$redeem_link = ''; $view_redeems_link = '';
		if (($this->Owner || $this->Admin) && $this->config->item("member_card")==true && $this->Settings->each_sale > 0 && $this->Settings->sa_point > 0) {
			$redeem_link = " <a href='" . site_url('auth/add_redeem_point/$1') . "' class='tip' title='" . lang("add_redeem_point") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-plus-circle\"></i></a> ";
			$view_redeems_link = " <a href='" . site_url('auth/view_redeem_points/$1') . "' class='tip' title='" . lang("view_redeem_points") . "'><i class=\"fa fa-list\"></i></a> ";
		}
		
        $this->datatables
            ->select($this->db->dbprefix('users').".id as id, first_name, last_name, email, company, award_points, " . $this->db->dbprefix('groups') . ".name, active")
            ->from("users")
            ->join('groups', 'users.group_id=groups.id', 'left')
            ->group_by('users.id')
            ->where('IFNULL(company_id,0)', 0)
			->where('saleman',0)
			->where('agency',0)
            ->edit_column('active', '$1__$2', 'active, id')
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('auth/profile/$1') . "' class='tip' title='" . lang("edit_user") . "'><i class=\"fa fa-edit\"></i></a> ".$redeem_link.' '.$view_redeems_link."</div>", "id");
        echo $this->datatables->generate();
    }

    function getUserLogins($id = NULL)
    {
        if (!$this->ion_auth->in_group(array('super-admin', 'admin'))) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect('welcome');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("login, ip_address, time")
            ->from("user_logins")
            ->where('user_id', $id);

        echo $this->datatables->generate();
    }

    function delete_avatar($id = NULL, $avatar = NULL)
    {

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('owner') && $id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . $_SERVER["HTTP_REFERER"] . "'; }, 0);</script>");
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            unlink('assets/uploads/avatars/' . $avatar);
            unlink('assets/uploads/avatars/thumbs/' . $avatar);
            if ($id == $this->session->userdata('user_id')) {
                $this->session->unset_userdata('avatar');
            }
            $this->db->update('users', array('avatar' => NULL), array('id' => $id));
            $this->session->set_flashdata('message', lang("avatar_deleted"));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . $_SERVER["HTTP_REFERER"] . "'; }, 0);</script>");
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function profile($id = NULL)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('owner') && $id != $this->session->userdata('user_id') && !$this->GP['auth-edit']) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$id || empty($id)) {
            redirect('auth');
        }

        $this->data['title'] = lang('profile');

        $user = $this->ion_auth->user($id)->row();
        $groups = $this->ion_auth->groups()->result_array();
        $this->data['user_info'] = $this->site->getUser($user->id);
        $this->data['csrf'] = $this->_get_csrf_nonce();
        $this->data['user'] = $user;
        $this->data['groups'] = $groups;
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['password'] = array(
            'name' => 'password',
            'id' => 'password',
            'class' => 'form-control',
            'type' => 'password',
            'value' => ''
        );
        $this->data['password_confirm'] = array(
            'name' => 'password_confirm',
            'id' => 'password_confirm',
            'class' => 'form-control',
            'type' => 'password',
            'value' => ''
        );
        $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
        $this->data['old_password'] = array(
            'name' => 'old',
            'id' => 'old',
            'class' => 'form-control',
            'type' => 'password',
        );
        $this->data['new_password'] = array(
            'name' => 'new',
            'id' => 'new',
            'type' => 'password',
            'class' => 'form-control',
            'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
        );
        $this->data['new_password_confirm'] = array(
            'name' => 'new_confirm',
            'id' => 'new_confirm',
            'type' => 'password',
            'class' => 'form-control',
            'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
        );
        $this->data['user_id'] = array(
            'name' => 'user_id',
            'id' => 'user_id',
            'type' => 'hidden',
            'value' => $user->id,
        );

        $this->data['id'] = $id;
		$this->data['projects'] = $this->site->getAllProjects();
        if($this->pos_settings->table_enable == 1){ 
            $this->data['floors'] = $this->site->getFloor();
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('auth/users'), 'page' => lang('users')), array('link' => '#', 'page' => lang('profile')));
        $meta = array('page_title' => lang('profile'), 'bc' => $bc);
        $this->core_page('auth/profile', $meta, $this->data);
    }

    public function captcha_check($cap)
    {
        $expiration = time() - 300; // 5 minutes limit
        $this->db->delete('captcha', array('captcha_time <' => $expiration));

        $this->db->select('COUNT(*) AS count')
            ->where('word', $cap)
            ->where('ip_address', $this->input->ip_address())
            ->where('captcha_time >', $expiration);

        if ($this->db->count_all_results('captcha')) {
            return true;
        } else {
            $this->form_validation->set_message('captcha_check', lang('captcha_wrong'));
            return FALSE;
        }
    }


    function login($m = NULL)
    {
        if ($this->loggedIn) {
            $this->session->set_flashdata('error', $this->session->flashdata('error'));
            redirect('welcome');
        }
        $this->data['title'] = lang('login');

        if ($this->Settings->captcha) {
            $this->form_validation->set_rules('captcha', lang('captcha'), 'required|callback_captcha_check');
        }

        if ($this->form_validation->run() == true) {

            $remember = (bool)$this->input->post('remember');

            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                if ($this->Settings->mmode) {
                    if (!$this->ion_auth->in_group('owner')) {
                        $this->session->set_flashdata('error', lang('site_is_offline_plz_try_later'));
                        redirect('auth/logout');
                    }
                }
                if ($this->ion_auth->in_group('customer') || $this->ion_auth->in_group('supplier')) {
                    redirect('auth/logout/1');
                }
                $this->session->set_flashdata('message', $this->ion_auth->messages());
				if($this->pos_model->getSetting()->table_enable == 1){
					$referrer = $this->session->userdata('requested_page') ? $this->session->userdata('requested_page') : 'pos/add_table';
				}else{
					$referrer = $this->session->userdata('requested_page') ? $this->session->userdata('requested_page') : 'welcome';                
				}
				redirect($referrer);
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                redirect('login');
            }
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['message'] = $this->session->flashdata('message');
            if ($this->Settings->captcha) {
                $this->load->helper('captcha');
                $vals = array(
                    'img_path' => './assets/captcha/',
                    'img_url' => site_url() . 'assets/captcha/',
                    'img_width' => 150,
                    'img_height' => 34,
                    'word_length' => 5,
                    'colors' => array('background' => array(255, 255, 255), 'border' => array(204, 204, 204), 'text' => array(102, 102, 102), 'grid' => array(204, 204, 204))
                );
                $cap = create_captcha($vals);
                $capdata = array(
                    'captcha_time' => $cap['time'],
                    'ip_address' => $this->input->ip_address(),
                    'word' => $cap['word']
                );

                $query = $this->db->insert_string('captcha', $capdata);
                $this->db->query($query);
                $this->data['image'] = $cap['image'];
                $this->data['captcha'] = array('name' => 'captcha',
                    'id' => 'captcha',
                    'type' => 'text',
                    'class' => 'form-control',
                    'required' => 'required',
                    'placeholder' => lang('type_captcha')
                );
            }

            $this->data['identity'] = array('name' => 'identity',
                'id' => 'identity',
                'type' => 'text',
                'class' => 'form-control',
                'placeholder' => lang('email'),
                'value' => $this->form_validation->set_value('identity'),
            );
            $this->data['password'] = array('name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'class' => 'form-control',
                'required' => 'required',
                'placeholder' => lang('password'),
            );
            $this->data['allow_reg'] = $this->Settings->allow_reg;
            if ($m == 'db') {
                $this->data['message'] = lang('db_restored');
            } elseif ($m) {
                $this->data['error'] = lang('we_are_sorry_as_this_sction_is_still_under_development.');
            }
			$this->data['users'] = $this->site->getAllUsers();
            $this->load->view($this->theme . 'auth/login-style2', $this->data);
        }
    }
	
	function clogin($m = NULL)
    {
        if ($this->loggedIn) {
            $this->session->set_flashdata('error', $this->session->flashdata('error'));
            redirect('welcome');
        }
        $this->data['title'] = lang('login');
        if ($this->Settings->captcha) {
            $this->form_validation->set_rules('captcha', lang('captcha'), 'required|callback_captcha_check');
        }
		$this->form_validation->set_rules('identity', lang('identity'), 'required');
        if ($this->form_validation->run() == true) {
            $remember = (bool)$this->input->post('remember');
            if ($this->ion_auth->clogin($this->input->post('identity'), $this->input->post('password'), $remember)) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
				$referrer = $this->session->userdata('requested_page') ? $this->session->userdata('requested_page') : 'customer_orders';                
				redirect($referrer);
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                redirect('clogin');
            }
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['message'] = $this->session->flashdata('message');
            if ($this->Settings->captcha) {
                $this->load->helper('captcha');
                $vals = array(
                    'img_path' => './assets/captcha/',
                    'img_url' => site_url() . 'assets/captcha/',
                    'img_width' => 150,
                    'img_height' => 34,
                    'word_length' => 5,
                    'colors' => array('background' => array(255, 255, 255), 'border' => array(204, 204, 204), 'text' => array(102, 102, 102), 'grid' => array(204, 204, 204))
                );
                $cap = create_captcha($vals);
                $capdata = array(
                    'captcha_time' => $cap['time'],
                    'ip_address' => $this->input->ip_address(),
                    'word' => $cap['word']
                );

                $query = $this->db->insert_string('captcha', $capdata);
                $this->db->query($query);
                $this->data['image'] = $cap['image'];
                $this->data['captcha'] = array('name' => 'captcha',
                    'id' => 'captcha',
                    'type' => 'text',
                    'class' => 'form-control',
                    'required' => 'required',
                    'placeholder' => lang('type_captcha')
                );
            }

            $this->data['identity'] = array('name' => 'identity',
                'id' => 'identity',
                'type' => 'text',
                'class' => 'form-control',
                'placeholder' => lang('email'),
                'value' => $this->form_validation->set_value('identity'),
            );
            $this->data['password'] = array('name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'class' => 'form-control',
                'required' => 'required',
                'placeholder' => lang('password'),
            );
            $this->data['allow_reg'] = $this->Settings->allow_reg;
            if ($m == 'db') {
                $this->data['message'] = lang('db_restored');
            } elseif ($m) {
                $this->data['error'] = lang('we_are_sorry_as_this_sction_is_still_under_development.');
            }
            $this->load->view($this->theme . 'auth/login-style-customer', $this->data);
        }
    }

    function reload_captcha()
    {
        $this->load->helper('captcha');
        $vals = array(
            'img_path' => './assets/captcha/',
            'img_url' => site_url() . 'assets/captcha/',
            'img_width' => 150,
            'img_height' => 34,
            'word_length' => 5,
            'colors' => array('background' => array(255, 255, 255), 'border' => array(204, 204, 204), 'text' => array(102, 102, 102), 'grid' => array(204, 204, 204))
        );
        $cap = create_captcha($vals);
        $capdata = array(
            'captcha_time' => $cap['time'],
            'ip_address' => $this->input->ip_address(),
            'word' => $cap['word']
        );
        $query = $this->db->insert_string('captcha', $capdata);
        $this->db->query($query);
        //$this->data['image'] = $cap['image'];

        echo $cap['image'];
    }

    function logout($m = NULL)
    {
        $logout = $this->ion_auth->logout(); 
        $this->session->set_flashdata('message', $this->ion_auth->messages());
		if($this->session->userdata('customer_login') == true){
			redirect('clogin/' . $m);
		}else{
			redirect('login/' . $m);
		}
    }

    function change_password()
    {
        if (!$this->ion_auth->logged_in()) {
            redirect('login');
        }
        $this->form_validation->set_rules('old_password', lang('old_password'), 'required');
		$this->form_validation->set_rules('new_password', lang('new_password'), 'required');
        $this->form_validation->set_rules('new_password_confirm', lang('confirm_password'), 'required|matches[new_password]');


        $user = $this->ion_auth->user()->row();

        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('auth/profile/' . $user->id . '/#cpassword');
        } else {
            if (DEMO) {
                $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            $identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));

            $change = $this->ion_auth->change_password($identity, $this->input->post('old_password'), $this->input->post('new_password'));

            if ($change) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $this->logout();
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                redirect('auth/profile/' . $user->id . '/#cpassword');
            }
        }
    }

    function forgot_password()
    {
        $this->form_validation->set_rules('forgot_email', lang('email_address'), 'required|valid_email');

        if ($this->form_validation->run() == false) {
            $error = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->session->set_flashdata('error', $error);
            redirect("login#forgot_password");
        } else {

            $identity = $this->ion_auth->where('email', strtolower($this->input->post('forgot_email')))->users()->row();
            if (empty($identity)) {
                $this->ion_auth->set_message('forgot_password_email_not_found');
                $this->session->set_flashdata('error', $this->ion_auth->messages());
                redirect("login#forgot_password");
            }

            $forgotten = $this->ion_auth->forgotten_password($identity->email);

            if ($forgotten) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect("login#forgot_password");
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                redirect("login#forgot_password");
            }
        }
    }

    public function reset_password($code = NULL)
    {
        if (!$code) {
            show_404();
        }

        $user = $this->ion_auth->forgotten_password_check($code);

        if ($user) {

            $this->form_validation->set_rules('new', lang('password'), 'required|min_length[8]|max_length[25]|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', lang('confirm_password'), 'required');

            if ($this->form_validation->run() == false) {

                $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                $this->data['message'] = $this->session->flashdata('message');
                $this->data['title'] = lang('reset_password');
                $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
                $this->data['new_password'] = array(
                    'name' => 'new',
                    'id' => 'new',
                    'type' => 'password',
                    'class' => 'form-control',
                    'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                    'data-bv-regexp-message' => lang('pasword_hint'),
                    'placeholder' => lang('new_password')
                );
                $this->data['new_password_confirm'] = array(
                    'name' => 'new_confirm',
                    'id' => 'new_confirm',
                    'type' => 'password',
                    'class' => 'form-control',
                    'data-bv-identical' => 'true',
                    'data-bv-identical-field' => 'new',
                    'data-bv-identical-message' => lang('pw_not_same'),
                    'placeholder' => lang('confirm_password')
                );
                $this->data['user_id'] = array(
                    'name' => 'user_id',
                    'id' => 'user_id',
                    'type' => 'hidden',
                    'value' => $user->id,
                );
                $this->data['csrf'] = $this->_get_csrf_nonce();
                $this->data['code'] = $code;
                $this->data['identity_label'] = $user->email;
                //render
                $this->load->view($this->theme . 'auth/reset_password', $this->data);
            } else {
                // do we have a valid request?
                if ($this->_valid_csrf_nonce() === FALSE || $user->id != $this->input->post('user_id')) {

                    //something fishy might be up
                    $this->ion_auth->clear_forgotten_password_code($code);
                    show_error(lang('error_csrf'));

                } else {
                    // finally change the password
                    $identity = $user->email;

                    $change = $this->ion_auth->reset_password($identity, $this->input->post('new'));

                    if ($change) {
                        //if the password was successfully changed
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                        //$this->logout();
                        redirect('login');
                    } else {
                        $this->session->set_flashdata('error', $this->ion_auth->errors());
                        redirect('auth/reset_password/' . $code);
                    }
                }
            }
        } else {
            //if the code is invalid then send them back to the forgot password page
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            redirect("login#forgot_password");
        }
    }

    function activate($id, $code = false)
    {

        if ($code !== false) {
            $activation = $this->ion_auth->activate($id, $code);
        } else if ($this->Owner) {
            $activation = $this->ion_auth->activate($id);
        }

        if ($activation) {
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            if ($this->Owner) {
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                redirect("auth/login");
            }
        } else {
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            redirect("forgot_password");
        }
    }

    function deactivate($id = NULL)
    {
        $this->cus->checkPermissions('users', TRUE);
        $id = $this->config->item('use_mongodb', 'ion_auth') ? (string)$id : (int)$id;
        $this->form_validation->set_rules('confirm', lang("confirm"), 'required');

        if ($this->form_validation->run() == FALSE) {
            if ($this->input->post('deactivate')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $this->data['csrf'] = $this->_get_csrf_nonce();
                $this->data['user'] = $this->ion_auth->user($id)->row();
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'auth/deactivate_user', $this->data);
            }
        } else {

            if ($this->input->post('confirm') == 'yes') {
                if ($id != $this->input->post('id')) {
                    show_error(lang('error_csrf'));
                }

                if ($this->ion_auth->logged_in() && $this->Owner) {
                    $this->ion_auth->deactivate($id);
                    $this->session->set_flashdata('message', $this->ion_auth->messages());
                }
            }

            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function create_user()
    {
        if (!$this->Owner && !$this->GP['auth-add']) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->data['title'] = "Create User";
        $this->form_validation->set_rules('username', lang("username"), 'trim|is_unique[users.username]');
        $this->form_validation->set_rules('status', lang("status"), 'trim|required');
        $this->form_validation->set_rules('group', lang("group"), 'trim|required');

        if ($this->form_validation->run() == true) {
			$permissions = $this->auth_model->GroupPermissions($this->input->post('group') ? $this->input->post('group') : '3');
			foreach($permissions as $permission){
				$show_price = $permission['products-price'];	
				$show_cost = $permission['products-cost'];
			}
			$username = strtolower($this->input->post('username'));
            $email = strtolower($this->input->post('email'));
            $password = $this->input->post('password');
            $notify = $this->input->post('notify');

            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'company' => $this->input->post('company'),
                'phone' => $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
				'mixzer_commission' => $this->input->post('mixzer_commission'),
                'group_id' => $this->input->post('group') ? $this->input->post('group') : '3',
                'biller_id' => $this->input->post('biller'),
				'show_cost' => $show_cost,
				'show_price' => $show_price,
                'warehouse_id' => $this->input->post('warehouse') ? json_encode($this->input->post('warehouse')) : NULL,
                'view_right' => $this->input->post('view_right'),
                'edit_right' => $this->input->post('edit_right'),
                'allow_discount' => $this->input->post('allow_discount'),
				'technician' => $this->input->post('technician'),
				'project_ids' => json_encode($this->input->post('project')),
                'floor_ids' => json_encode($this->input->post('floor')),
				'days_off' => json_encode($this->input->post('days_off')),
            );
            $active = $this->input->post('status');
        }
        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {

            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect("auth/users");

        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('error')));
            $this->data['groups'] = $this->ion_auth->groups()->result_array();
			$this->data['projects'] = $this->site->getAllProjects();
            if($this->pos_settings->table_enable == 1){ 
                $this->data['floors'] = $this->site->getFloor();
            }
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc = array(array('link' => site_url('home'), 'page' => lang('home')), array('link' => site_url('auth/users'), 'page' => lang('users')), array('link' => '#', 'page' => lang('create_user')));
            $meta = array('page_title' => lang('users'), 'bc' => $bc);
            $this->core_page('auth/create_user', $meta, $this->data);
        }
    }

    function edit_user($id = NULL)
    {

        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $this->data['title'] = lang("edit_user");

        if (!$this->loggedIn || !$this->Owner && $id != $this->session->userdata('user_id') && !$this->GP['auth-edit']) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $user = $this->ion_auth->user($id)->row();
		$this->form_validation->set_rules('username', lang("username"), 'trim|required');

        if ($user->username != $this->input->post('username')) {
            $this->form_validation->set_rules('username', lang("username"), 'trim|is_unique[users.username]');
        }

        if ($this->form_validation->run() === TRUE) {
			$permissions = $this->auth_model->GroupPermissions($this->input->post('group') ? $this->input->post('group') : '3');
			foreach($permissions as $permission){
				$show_price = $permission['products-price'];	
				$show_cost = $permission['products-cost'];
			}
            if ($this->Owner) {
                if ($id == $this->session->userdata('user_id')) {
                    $data = array(
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'company' => $this->input->post('company'),
                        'phone' => $this->input->post('phone'),
                        'gender' => $this->input->post('gender'),
                    );
                } elseif ($this->ion_auth->in_group('customer', $id) || $this->ion_auth->in_group('supplier', $id)) {
                    $data = array(
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'company' => $this->input->post('company'),
                        'phone' => $this->input->post('phone'),
                        'gender' => $this->input->post('gender'),
                    );
                } else {
                    $data = array(
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'company' => $this->input->post('company'),
                        'username' => $this->input->post('username'),
                        'email' => $this->input->post('email'),
                        'phone' => $this->input->post('phone'),
                        'gender' => $this->input->post('gender'),
                        'active' => $this->input->post('status'),
                        'group_id' => $this->input->post('group'),
						'mixzer_commission' => $this->input->post('mixzer_commission'),
                        'biller_id' => $this->input->post('biller') ? $this->input->post('biller') : NULL,
						'show_cost' => $show_cost,
						'show_price' => $show_price,
                        'warehouse_id' => $this->input->post('warehouse') ? json_encode($this->input->post('warehouse')) : NULL,
                        'award_points' => $this->input->post('award_points'),
                        'view_right' => $this->input->post('view_right'),
                        'edit_right' => $this->input->post('edit_right'),
                        'allow_discount' => $this->input->post('allow_discount'),
						'technician' => $this->input->post('technician'),
                        'floor_ids' => json_encode($this->input->post('floor')),
						'days_off' => json_encode($this->input->post('days_off')),
                    );
                }
            } else {
                $data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'company' => $this->input->post('company'),
                    'phone' => $this->input->post('phone'),
                    'gender' => $this->input->post('gender'),
					'active' => $this->input->post('status'),
					'group_id' => $this->input->post('group'),
					'biller_id' => $this->input->post('biller') ? $this->input->post('biller') : NULL,
					'warehouse_id' => $this->input->post('warehouse') ? json_encode($this->input->post('warehouse')) : NULL,
					'award_points' => $this->input->post('award_points'),
					'view_right' => $this->input->post('view_right'),
					'show_cost' => $show_cost,
					'show_price' => $show_price,
					'edit_right' => $this->input->post('edit_right'),
					'allow_discount' => $this->input->post('allow_discount'),
					'technician' => $this->input->post('technician'),
					'floor_ids' => json_encode($this->input->post('floor')),
					'days_off' => json_encode($this->input->post('days_off')),
                );
            }

            if ($this->Owner) {
                if ($this->input->post('password')) {
                    if (DEMO) {
                        $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    //$this->form_validation->set_rules('password', lang('edit_user_validation_password_label'), 'required|min_length[8]|max_length[25]|matches[password_confirm]');
                    //$this->form_validation->set_rules('password_confirm', lang('edit_user_validation_password_confirm_label'), 'required');
                    $data['password'] = $this->input->post('password');
                }
            }
			
			$projects = $this->input->post('project');
			if(in_array('all', $projects)){
				$data['project_ids'] = json_encode(['all']);
			}else{
				$data['project_ids'] = json_encode($projects);
			}
            if($this->pos_settings->table_enable == 1){ 
                $this->data['floors'] = $this->site->getFloor();
            }
        }
        if ($this->form_validation->run() === TRUE && $this->ion_auth->update($user->id, $data)) {
            $this->session->set_flashdata('message', lang('user_updated'));
            redirect("auth/profile/" . $id);
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }


    function _get_csrf_nonce()
    {
        $this->load->helper('string');
        $key = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);

        return array($key => $value);
    }

    function _valid_csrf_nonce()
    {
        if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
            $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue')
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function _render_page($view, $data = null, $render = false)
    {

        $this->viewdata = (empty($data)) ? $this->data : $data;
        $view_html = $this->load->view('header', $this->viewdata, $render);
        $view_html .= $this->load->view($view, $this->viewdata, $render);
        $view_html = $this->load->view('footer', $this->viewdata, $render);

        if (!$render)
            return $view_html;
    }

    function update_avatar($id = NULL)
    {
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }

        if (!$this->ion_auth->logged_in() || !$this->Owner && $id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        //validate form input
        $this->form_validation->set_rules('avatar', lang("avatar"), 'trim');

        if ($this->form_validation->run() == true) {

            if ($_FILES['avatar']['size'] > 0) {

                $this->load->library('upload');

                $config['upload_path'] = 'assets/uploads/avatars';
                $config['allowed_types'] = 'gif|jpg|png';
                //$config['max_size'] = '500';
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('avatar')) {

                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                $photo = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = 'assets/uploads/avatars/' . $photo;
                $config['new_image'] = 'assets/uploads/avatars/thumbs/' . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 150;
                $config['height'] = 150;;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                $user = $this->ion_auth->user($id)->row();
            } else {
                $this->form_validation->set_rules('avatar', lang("avatar"), 'required');
            }
        }

        if ($this->form_validation->run() == true && $this->auth_model->updateAvatar($id, $photo)) {
            unlink('assets/uploads/avatars/' . $user->avatar);
            unlink('assets/uploads/avatars/thumbs/' . $user->avatar);
            $this->session->set_userdata('avatar', $photo);
            $this->session->set_flashdata('message', lang("avatar_updated"));
            redirect("auth/profile/" . $id);
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect("auth/profile/" . $id);
        }
    }

    function register()
    {
        $this->data['title'] = "Register";
        if (!$this->allow_reg) {
            $this->session->set_flashdata('error', lang('registration_is_disabled'));
            redirect("login");
        }

        $this->form_validation->set_message('is_unique', lang('account_exists'));
        $this->form_validation->set_rules('first_name', lang('first_name'), 'required');
        $this->form_validation->set_rules('last_name', lang('last_name'), 'required');
        $this->form_validation->set_rules('email', lang('email_address'), 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('usernam', lang('usernam'), 'required|is_unique[users.username]');
        $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[25]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');
        if ($this->Settings->captcha) {
            $this->form_validation->set_rules('captcha', lang('captcha'), 'required|callback_captcha_check');
        }

        if ($this->form_validation->run() == true) {
            $username = strtolower($this->input->post('username'));
            $email = strtolower($this->input->post('email'));
            $password = $this->input->post('password');

            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'company' => $this->input->post('company'),
                'phone' => $this->input->post('phone'),
            );
        }
        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data)) {

            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect("login");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('error')));
            $this->data['groups'] = $this->ion_auth->groups()->result_array();

            $this->load->helper('captcha');
            $vals = array(
                'img_path' => './assets/captcha/',
                'img_url' => site_url() . 'assets/captcha/',
                'img_width' => 150,
                'img_height' => 34,
            );
            $cap = create_captcha($vals);
            $capdata = array(
                'captcha_time' => $cap['time'],
                'ip_address' => $this->input->ip_address(),
                'word' => $cap['word']
            );

            $query = $this->db->insert_string('captcha', $capdata);
            $this->db->query($query);
            $this->data['image'] = $cap['image'];
            $this->data['captcha'] = array('name' => 'captcha',
                'id' => 'captcha',
                'type' => 'text',
                'class' => 'form-control',
                'placeholder' => lang('type_captcha')
            );

            $this->data['first_name'] = array(
                'name' => 'first_name',
                'id' => 'first_name',
                'type' => 'text',
                'class' => 'form-control',
                'required' => 'required',
                'value' => $this->form_validation->set_value('first_name'),
            );
            $this->data['last_name'] = array(
                'name' => 'last_name',
                'id' => 'last_name',
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('last_name'),
            );
            $this->data['email'] = array(
                'name' => 'email',
                'id' => 'email',
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('email'),
            );
            $this->data['company'] = array(
                'name' => 'company',
                'id' => 'company',
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('company'),
            );
            $this->data['phone'] = array(
                'name' => 'phone',
                'id' => 'phone',
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('phone'),
            );
            $this->data['password'] = array(
                'name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('password'),
            );
            $this->data['password_confirm'] = array(
                'name' => 'password_confirm',
                'id' => 'password_confirm',
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('password_confirm'),
            );

            $this->load->view('auth/register', $this->data);
        }
    }

    function user_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					if (!$this->Owner && !$this->GP['auth-delete']) {
						$this->session->set_flashdata('warning', lang('access_denied'));
						redirect($_SERVER["HTTP_REFERER"]);
					}
                    foreach ($_POST['val'] as $id) {
                        if ($id != $this->session->userdata('user_id')) {
                            $this->auth_model->delete_user($id);
                        }
                    }
                    $this->session->set_flashdata('message', lang("users_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('users'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('first_name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('last_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('group'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $user = $this->site->getUser($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $user->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->email);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $user->company);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $user->group);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $user->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'users_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_user_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function delete($id = NULL)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->input->get('id')) { $id = $this->input->get('id'); }

        if ( ! $this->Owner || $id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }

        if ($this->auth_model->delete_user($id)) {
            //echo lang("user_deleted");
            $this->session->set_flashdata('message', 'user_deleted');
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	function salemans()
    {
        if ( ! $this->loggedIn) {
            redirect('login');
        }
		$this->cus->checkPermissions('saleman');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('salemans')));
        $meta = array('page_title' => lang('salemans'), 'bc' => $bc);
        $this->core_page('auth/salemans', $meta, $this->data);
    }
	
	function getSalemans()
    {
		$leader_commission = "";
		if($this->config->item("saleman_commission")){
			$leader_commission = "<a href='" . site_url('auth/share_commissions/$1') . "' class='tip' title='" . lang("share_commissions") . "'><i class=\"fa fa-money\"></i></a>";
		}
        $this->cus->checkPermissions('saleman');
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users').".id as id, first_name, last_name, gender, phone, position, saleman_commission, saleman_group,areas.name, active")
            ->from("users")
			->join("areas","areas.id = users.salesman_area","LEFT")
            ->where('saleman', 1)
			->edit_column('active', '$1__$2', 'active, id')
            ->add_column("Actions", "<div class=\"text-center\">".$leader_commission.($this->Settings->product_commission == 1 ? "<a href='" . site_url('auth/salesman_product_commissions/$1') . "' class='tip' title='" . lang("product_commissions") . "'><i class=\"fa fa-eye\"></i></a> " : "")." <a class=\"tip\" title='" . lang("edit_saleman") . "' href='" . site_url('auth/edit_saleman/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_saleman") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('auth/delete_saleman/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
		echo $this->datatables->generate();
    }
	
	function add_saleman()
    {
        $this->cus->checkPermissions('saleman-add');
		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
		$this->form_validation->set_rules('last_name', lang("last_name"), 'required');
        if ($this->form_validation->run() == true) {
            $salesman_group = $this->site->getSalesmanGroupsByID($this->input->post('group'));
            if(isset($_POST['amount'])){
                $currencies = array();
                foreach($_POST['amount'] as $i=> $currency){
                    $currencies[] = array(
                                'amount' => $_POST['amount'][$i],
                                'code' => $_POST['code'][$i],
                                'rate' => $_POST['rate'][$i],
                            );
                }
            }
			
			$share_commissions = false;
			if($this->input->post('share_commissions')){
				foreach($this->input->post('share_commissions') as $share_commission){
					$share_commissions[] = array("share_id"=>$share_commission);

				}
			}

            $data = array(
				'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'phone' => $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
				'position' => $this->input->post('position'),
                'saleman_group_id' => $salesman_group->id,
                'saleman_group' => $salesman_group->name,
				'saleman_commission' => $this->input->post('commission'),
				'share_commissions' => json_encode($this->input->post('share_commissions')),
				'active' => $this->input->post('status'),
                'salesman_area' => $this->input->post('area'),
                'fuel_time_id' => $this->input->post('fuel_time'),
                'money_change' => json_encode($currencies),
				'saleman' => 1,
            );

        } elseif ($this->input->post('add_saleman')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('auth/salemans');
        }

        if ($this->form_validation->run() == true && $this->auth_model->addSaleman($data, $share_commissions)) {
			$this->session->set_flashdata('message', lang("saleman_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$this->data['modal_js'] = $this->site->modal_js();
            $this->data['areas'] = $this->site->getAreas();
            $this->data['fuel_times'] = $this->auth_model->getFuelTimes();
            $this->data['currencies'] = $this->site->getAllCurrencies();
			$this->data['groups'] = $this->site->getSalesmanGroups();
			$this->data['leaders'] = $this->site->getSalemans();
            $this->load->view($this->theme . 'auth/add_saleman', $this->data);
        }
    }
	
	function edit_saleman($id = NULL)
    {
        $this->cus->checkPermissions('saleman-edit',true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $saleman_details = $this->site->getUser($id);
		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
		$this->form_validation->set_rules('last_name', lang("last_name"), 'required');
        if ($this->form_validation->run() == true) {
            $salesman_group = $this->site->getSalesmanGroupsByID($this->input->post('group'));
            if(isset($_POST['amount'])){
                $currencies = array();
                foreach($_POST['amount'] as $i=> $currency){
                    $currencies[] = array(
                                'amount' => $_POST['amount'][$i],
                                'code' => $_POST['code'][$i],
                                'rate' => $_POST['rate'][$i],
                            );
                }
            }
			$share_commissions = false;
			if($this->input->post('share_commissions')){
				foreach($this->input->post('share_commissions') as $share_commission){
					$share_commissions[] = array("salesman_id"=>$id,"share_id"=>$share_commission);

				}
			}
			$data = array(
				'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'phone' => $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
				'position' => $this->input->post('position'),
                'saleman_group_id' => $salesman_group->id,
                'saleman_group' => $salesman_group->name,
				'saleman_commission' => $this->input->post('commission'),
				'share_commissions' => json_encode($this->input->post('share_commissions')),
				'active' => $this->input->post('status'),
                'salesman_area' => $this->input->post('area'),
                'fuel_time_id' => $this->input->post('fuel_time'),
                'money_change' => json_encode($currencies),
				'saleman' => 1,

            );
        } elseif ($this->input->post('edit_saleman')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->auth_model->updateSaleman($id, $data, $share_commissions)) {
            $this->session->set_flashdata('message', lang("saleman_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['saleman'] = $saleman_details;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['areas'] = $this->site->getAreas();
            $this->data['fuel_times'] = $this->auth_model->getFuelTimes();
            $this->data['currencies'] = $this->site->getAllCurrencies();
			$this->data['groups'] = $this->site->getSalesmanGroups();
			$this->data['leaders'] = $this->site->getSalemans();
			$this->load->view($this->theme . 'auth/edit_saleman', $this->data);
        }
    }
	
	function delete_saleman($id)
	{
		$this->cus->checkPermissions('saleman-delete',true);
		if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->auth_model->deleteSaleman($id)) {
            echo lang("saleman_deleted");
        } else {
            $this->session->set_flashdata('warning', lang('saleman_x_cannot_delete'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
	}
	
	function saleman_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					if (!$this->Owner && !$this->GP['saleman-delete']) {
						$this->session->set_flashdata('warning', lang('access_denied'));
						redirect($_SERVER["HTTP_REFERER"]);
					}
                    foreach ($_POST['val'] as $id) {
                        if ($id != $this->session->userdata('user_id')) {
                            $this->auth_model->deleteSaleman($id);
                        }
                    }
                    $this->session->set_flashdata('message', lang("saleman_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('saleman'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('first_name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('last_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('gender'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('commission'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $saleman = $this->site->getUser($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $saleman->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $saleman->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $saleman->gender);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $saleman->phone);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $saleman->position);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $saleman->saleman_commission);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $saleman->saleman_group);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, ($saleman->active == 1 ? lang('active') : lang('inactive')));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'salemans_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_user_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	function share_commissions($salesman_id = false){
		if (!$salesman_id) {
            $this->session->set_flashdata('error', lang('no_salesman_selected'));
            redirect('auth/salemans');
        }
		
		$this->data['id'] = $salesman_id;
		$this->data['salesman'] = $this->site->getUserByID($salesman_id);
		$this->data['commissions'] = $this->auth_model->getShareCommissions($salesman_id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('auth/salemans'), 'page' => lang('salemans')),  array('link' => '#', 'page' => lang('share_commissions')));
        $meta = array('page_title' => lang('share_commissions'), 'bc' => $bc);
        $this->core_page('auth/share_commissions', $meta, $this->data);
	}
	
	function share_commission_actions($salesman_id)
    {
        if (!$salesman_id) {
            $this->session->set_flashdata('error', lang('no_salesman_selected'));
            redirect('auth/salemans');
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
			$data = false;
			$i = isset($_POST['commission_type']) ? sizeof($_POST['commission_type']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$commission_type = $_POST['commission_type'][$r];
				$description = $_POST['description'][$r];
				$min_amount = $_POST['min_amount'][$r];
				$max_amount = $_POST['max_amount'][$r];
				$commission = $_POST['commission'][$r];
				if($commission || $commission_type=="Normal"){
					$data[] = array(
									"salesman_id" => $salesman_id,
									"commission_type" => $commission_type,
									"description" => $description,
									"min_amount" => $min_amount,
									"max_amount" => $max_amount,
									"commission" => $commission,
								);
				}
			}
			if (!$data) {
				$this->form_validation->set_rules('commission', lang("order_items"), 'required');
			}
			if ($this->form_validation->run() == true && $this->auth_model->addShareCommission($salesman_id,$data)) {	
				$this->session->set_flashdata('message', $this->lang->line('share_commission_edited'));          
				redirect('auth/share_commissions/'.$salesman_id);
			}
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	function agencies()
    {
		$this->cus->checkPermissions('agency-index');
        if ( ! $this->loggedIn) {
            redirect('login');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('agencies')));
        $meta = array('page_title' => lang('agencies'), 'bc' => $bc);
        $this->core_page('auth/agencies', $meta, $this->data);
    }
	
	function getAgencies()
    {
        $this->cus->checkPermissions('agency-index');
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users').".id as id, first_name, last_name, gender, phone,agency_commission, agency_limit_percent, active")
            ->from("users")
            ->where('agency', 1)
			->edit_column('active', '$1__$2', 'active, id')
            ->add_column("Actions", "<div class=\"text-center\"> <a class=\"tip\" title='" . lang("edit_agency") . "' href='" . site_url('auth/edit_agency/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_agency") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('auth/delete_agency/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	
	function add_agency()
    {
        $this->cus->checkPermissions('agency-add');
		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
		$this->form_validation->set_rules('last_name', lang("last_name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
				'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'phone' => $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
                'agency_commission' => $this->input->post('agency_commission'),
                'agency_limit_percent' => $this->input->post('agency_limit_percent'),
                'agency_value_commission' => $this->input->post('agency_value_commission'),
				'active' => $this->input->post('status'),
				'agency' => 1,

            );
        } elseif ($this->input->post('add_agency')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('auth/agencies');
        }

        if ($this->form_validation->run() == true && $this->auth_model->addSaleman($data)) {
			$this->session->set_flashdata('message', lang("agency_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'auth/add_agency', $this->data);
        }
    }
	
	function edit_agency($id = NULL)
    {
        $this->cus->checkPermissions('agency-edit',true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $agency_details = $this->site->getUser($id);
		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
		$this->form_validation->set_rules('last_name', lang("last_name"), 'required');
        if ($this->form_validation->run() == true) {

			$data = array(
				'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'phone' => $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
                'agency_commission' => $this->input->post('agency_commission'),
                'agency_limit_percent' => $this->input->post('agency_limit_percent'),
                'agency_value_commission' => $this->input->post('agency_value_commission'),
				'active' => $this->input->post('status'),
				'agency' => 1,

            );
        } elseif ($this->input->post('edit_agency')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->auth_model->updateSaleman($id, $data)) {
            $this->session->set_flashdata('message', lang("agency_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['agency'] = $agency_details;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'auth/edit_agency', $this->data);
        }
    }
	
	function delete_agency($id)
	{
		$this->cus->checkPermissions('agency-delete',true);
		if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->auth_model->deleteSaleman($id)) {
            echo lang("agency_deleted");
        } else {
            $this->session->set_flashdata('warning', lang('agency_x_cannot_delete'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
	}
	
	function agency_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					if (!$this->Owner && !$this->GP['agency-delete']) {
						$this->session->set_flashdata('warning', lang('access_denied'));
						redirect($_SERVER["HTTP_REFERER"]);
					}
                    foreach ($_POST['val'] as $id) {
                        if ($id != $this->session->userdata('user_id')) {
                            $this->auth_model->deleteSaleman($id);
                        }
                    }
                    $this->session->set_flashdata('message', lang("agency_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('agency'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('first_name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('last_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('gender'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('commission'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('group'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $agency = $this->site->getUser($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $agency->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $agency->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $agency->gender);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $agency->phone);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $agency->agency_commission);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $agency->agency_group);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, ($agency->active == 1 ? lang('active') : lang('inactive')));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'agencies_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_user_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	function salesman_product_commissions($salesman_id = NULL)
    {
        if (!$salesman_id) {
            $this->session->set_flashdata('error', lang('no_salesman_selected'));
            redirect('auth/salemans');
        }
		
		$this->data['id'] = $salesman_id;
		$this->data['salesman'] = $this->site->getUserByID($salesman_id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('auth/salemans'), 'page' => lang('salemans')),  array('link' => '#', 'page' => lang('salesman_product_commissions')));
        $meta = array('page_title' => lang('salesman_product_commissions'), 'bc' => $bc);
        $this->core_page('auth/salesman_product_commissions', $meta, $this->data);
    }
	
	function getSalesmanProductCommissions($salesman_id = NULL)
    {
		if (!$salesman_id) {
            $this->session->set_flashdata('error', lang('no_salesman_selected'));
            redirect('auth/salemans');
        }
		
        $pp = "( SELECT {$this->db->dbprefix('product_commissions')}.product_id as product_id, {$this->db->dbprefix('product_commissions')}.commission as commission  FROM {$this->db->dbprefix('product_commissions')} WHERE salesman_id = {$salesman_id} ) PP";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id,{$this->db->dbprefix('categories')}.`name` as category_name, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('products')}.name as product_name,  PP.commission as commission")
            ->from("products")
			->join("categories","categories.id= products.category_id")
            ->join($pp, 'PP.product_id=products.id', 'left')
            ->edit_column("commission", "$1__$2", 'id, commission')
            ->add_column("Actions", "<div class=\"text-center\"><button class=\"btn btn-primary btn-xs form-submit\" type=\"button\"><i class=\"fa fa-check\"></i></button></div>", "id");
	
        echo $this->datatables->generate();
    }
	
	function update_salesman_product_commissions($salesman_id = NULL)
    {
        if (!$salesman_id) {
            $this->cus->send_json(array('status' => 0));
        }
        $product_id = $this->input->post('product_id', TRUE);
        $commission = $this->input->post('commission', TRUE);
        if (!empty($product_id)) {
            if ($this->auth_model->setSalesmanProductCommission($product_id, $salesman_id, $commission)) {
                $this->cus->send_json(array('status' => 1));
            }
        }

        $this->cus->send_json(array('status' => 0));
    }
	
	function salesman_product_commission_actions($salesman_id)
    {
        if (!$salesman_id) {
            $this->session->set_flashdata('error', lang('no_salesman_selected'));
            redirect('auth/salemans');
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_commission') {
                    foreach ($_POST['val'] as $id) {
                        $this->auth_model->setSalesmanProductCommission($id, $salesman_id, $this->input->post('commission'.$id));
                    }
                    $this->session->set_flashdata('message', lang("salesman_products_commission_updated"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->auth_model->deleteSalesmanProductCommission($id, $salesman_id);
                    }
                    $this->session->set_flashdata('message', lang("salesman_products_commission_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('salesman_product_commissions'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('salesman'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('commission'));

                    $row = 2;
                    $salesman = $this->site->getUserByID($salesman_id);
                    foreach ($_POST['val'] as $id) {
                        $pgp = $this->auth_model->getSalesmanProductCommissionByPID($id, $salesman_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pgp->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pgp->name);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $salesman->last_name.' '.$salesman->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pgp->commission);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'salesman_product_commissions_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_customer_product_price_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	function update_award_point($id)
    {
		$this->form_validation->set_rules('each_sale', lang('each_sale'), 'required');
		$this->form_validation->set_rules('sa_point', lang('sa_point'), 'required');
        if ($this->form_validation->run() == TRUE) {
            $data = array(
					'award_points' => $this->input->post('award_points'),
					'each_sale' => $this->input->post('each_sale'),
					'sa_point' => $this->input->post('sa_point')
				);
        } 
		if ($this->form_validation->run() === TRUE && $this->auth_model->updateAwardPoints($id, $data)) {
			$this->session->set_flashdata('message', lang('user_updated'));
            redirect("users");
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect('auth/profile/' . $id . '/#award_points');
		}
    }

	public function add_redeem_point($id)
    {
        $this->cus->checkPermissions('add_member_card',true);
        $user = $this->site->getUser($id);
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = array(
				'user_id' => $id,
                'amount' => $this->input->post('amount'),
				'note' => $this->input->post('note'),
                'date' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('user_id'),
            );
        } elseif ($this->input->post('redeem')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("users");
        }
        if ($this->form_validation->run() == true && $this->auth_model->addRedeemPoint($id, $data)) {
            $this->session->set_flashdata('message', lang("redeem_points_updated"));
            redirect("users");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['user'] = $user;
            $this->data['page_title'] = lang("add_redeem_point");
            $this->load->view($this->theme . 'auth/add_redeem_point', $this->data);
        }
    }
	
	public function getRedeemPoints($user_id = false)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('user_redeem_points') . ".id as id, date, amount, note, CONCAT(" . $this->db->dbprefix('users') . ".last_name, ' ', " . $this->db->dbprefix('users') . ".first_name) as created_by", false)
            ->join('users', 'users.id=user_redeem_points.created_by', 'left')
            ->from("user_redeem_points")
			->where("user_redeem_points.user_id", $user_id)
            ->add_column("Actions", "<div class=\"text-center\"><a href='#' class='tip po' title='<b>" . lang("delete_redeem_point") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('auth/delete_redeem_point/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
			->unset_column("id");
		echo $this->datatables->generate();
    }
	
	public function view_redeem_points($id = false)
	{
		$this->data['id'] = $id;
		$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('users'), 'page' => lang('users')), array('link' => '#', 'page' => lang('view_redeem_points')));
        $meta = array('page_title' => lang('view_redeem_points'), 'bc' => $bc);
        $this->core_page('auth/view_redeem_points', $meta, $this->data);
	}
	
	public function delete_redeem_point($id = null)
    {
        if ($this->auth_model->deleteRedeemPoint($id)) {
            echo lang("redeem_point_deleted");
        }
    }
	
	public function redeem_points_actions($id = NULL, $pdf = NULL, $xls = NULL)
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($pdf || $xls) {
			$this->db
					->select($this->db->dbprefix('user_redeem_points') . ".id as id, date, amount, note, CONCAT(" . $this->db->dbprefix('users') . ".last_name, ' ', " . $this->db->dbprefix('users') . ".first_name) as created_by", false)
					->join('users', 'users.id=user_redeem_points.created_by', 'left')
					->from("user_redeem_points")
					->where("user_redeem_points.user_id", $id);
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
	
}
