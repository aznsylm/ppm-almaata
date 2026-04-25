<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Santri extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('form_validation');
        $this->load->helper(array('form'));
    }

    public function index()
    {
        $this->require_role(array('admin'));

        $filters = array(
            'prodi' => trim((string) $this->input->get('prodi', TRUE)),
            'lantai' => trim((string) $this->input->get('lantai', TRUE)),
            'angkatan' => trim((string) $this->input->get('angkatan', TRUE)),
            'q' => trim((string) $this->input->get('q', TRUE))
        );

        $perPage = 10;
        $page = (int) $this->input->get('page', TRUE);
        if ($page < 1) {
            $page = 1;
        }

        $totalRows = $this->User_model->count_santri_for_admin($filters);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $rows = $this->User_model->get_santri_for_admin($filters, $perPage, $offset);

        $queryBase = $filters;
        unset($queryBase['page']);

        $data = array(
            'page_title' => 'Data Santri',
            'content_view' => 'admin/santri_content',
            'content_data' => array(
                'rows' => $rows,
                'filters' => $filters,
                'filter_options' => $this->User_model->get_santri_filter_options(),
                'pagination' => array(
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_rows' => $totalRows,
                    'total_pages' => $totalPages,
                    'offset' => $offset,
                    'query_base' => $queryBase
                )
            )
        );

        $this->load->view('layouts/admin', $data);
    }

    public function store()
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $this->form_validation->set_rules('nim', 'NIM', 'required|trim|min_length[5]|max_length[15]');
        $this->form_validation->set_rules('kamar', 'Kamar', 'required|trim|max_length[20]');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/santri');
            return;
        }

        $result = $this->User_model->create_santri_from_msmhs(
            $this->input->post('nim', TRUE),
            $this->input->post('kamar', TRUE)
        );

        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        redirect('admin/santri');
    }

    public function update($id)
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $this->form_validation->set_rules('kamar', 'Kamar', 'required|trim|max_length[20]');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/santri');
            return;
        }

        $result = $this->User_model->update_santri(
            (int) $id,
            $this->input->post('kamar', TRUE)
        );

        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        redirect('admin/santri');
    }

    public function delete($id)
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $result = $this->User_model->delete_santri((int) $id);
        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        redirect('admin/santri');
    }
}
