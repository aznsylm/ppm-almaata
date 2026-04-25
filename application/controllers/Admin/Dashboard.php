<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function index()
    {
        $this->require_role(array('admin'));

        $data = array(
            'page_title' => 'Dashboard Admin',
            'content_view' => 'admin/dashboard_content',
            'content_data' => array(
                'menu' => array(
                'Dashboard' => site_url('admin/dashboard'),
                'Data Santri' => site_url('admin/santri'),
                'Data Perizinan' => site_url('admin/perizinan'),
                'Logout' => site_url('auth/logout')
                )
            )
        );

        $this->load->view('layouts/admin', $data);
    }
}
