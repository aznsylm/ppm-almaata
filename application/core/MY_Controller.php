<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $auth_user;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(array('url'));
        $this->auth_user = $this->session->userdata('auth_user');
    }

    protected function current_user()
    {
        return $this->auth_user;
    }

    protected function require_login()
    {
        if (empty($this->auth_user)) {
            redirect('auth/login');
            exit;
        }
    }

    protected function require_role($roles)
    {
        $this->require_login();
        $roles = (array) $roles;

        if (!in_array($this->auth_user['role'], $roles, TRUE)) {
            show_error('Forbidden', 403);
            exit;
        }
    }

    protected function set_login_session(array $payload)
    {
        $this->session->sess_regenerate(TRUE);
        $this->session->set_userdata('auth_user', $payload);
        $this->auth_user = $payload;
    }

    protected function destroy_login_session()
    {
        $this->session->unset_userdata('auth_user');
        $this->session->sess_regenerate(TRUE);
    }
}
