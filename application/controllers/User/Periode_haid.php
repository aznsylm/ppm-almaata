<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Periode_haid extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Periode_haid_model');
    }

    /**
     * View data periode haid santri (hanya data mereka sendiri)
     */
    public function index()
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        if (!empty($auth['must_reset_password'])) {
            redirect('auth/reset-password');
            return;
        }

        $data_haid = $this->Periode_haid_model->get_by_nim($auth['nim']);

        $data = array(
            'page_title' => 'Periode Saya',
            'content_view' => 'user/periode_haid_content',
            'content_data' => array(
                'data_haid' => $data_haid,
                'nim' => $auth['nim'],
            ),
        );

        $this->load->view('layouts/admin', $data);
    }
}
