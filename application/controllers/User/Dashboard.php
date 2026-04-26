<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
    }

    public function index()
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        if (!empty($auth['must_reset_password'])) {
            redirect('auth/reset-password');
            return;
        }

        $profile = $this->User_model->get_profile_by_nim($auth['nim']);
        if (!empty($profile['nama']) && $profile['nama'] !== (isset($auth['display_name']) ? $auth['display_name'] : '')) {
            $auth['display_name'] = $profile['nama'];
            $this->set_login_session($auth);
        }

        $data = array(
            'page_title' => 'Dashboard Santri',
            'content_view' => 'user/dashboard_content',
            'content_data' => array(
                'profile' => $profile
            )
        );

        $this->load->view('layouts/admin', $data);
    }
}
