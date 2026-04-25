<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perizinan extends MY_Controller
{
    private $upload_path = 'uploads/perizinan/';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Perizinan_model');
        $this->load->model('User_model');
        $this->load->helper(array('form'));
    }

    public function index()
    {
        $this->require_role(array('admin'));

        $this->load->library('pagination');

        $filters = array(
            'q' => trim((string) $this->input->get('q', TRUE)),
            'status' => trim((string) $this->input->get('status', TRUE))
        );

        $per_page = 10;
        $page = (int) $this->input->get('page', TRUE);
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $per_page;

        $config = array(
            'base_url' => site_url('admin/perizinan'),
            'total_rows' => $this->Perizinan_model->count_for_admin_history($filters),
            'per_page' => $per_page,
            'page_query_string' => TRUE,
            'query_string_segment' => 'page',
            'reuse_query_string' => TRUE,
            'use_page_numbers' => TRUE,
            'full_tag_open' => '<ul class="pagination pagination-sm mb-0">',
            'full_tag_close' => '</ul>',
            'first_tag_open' => '<li class="page-item">',
            'first_tag_close' => '</li>',
            'last_tag_open' => '<li class="page-item">',
            'last_tag_close' => '</li>',
            'next_tag_open' => '<li class="page-item">',
            'next_tag_close' => '</li>',
            'prev_tag_open' => '<li class="page-item">',
            'prev_tag_close' => '</li>',
            'cur_tag_open' => '<li class="page-item active"><span class="page-link">',
            'cur_tag_close' => '</span></li>',
            'num_tag_open' => '<li class="page-item">',
            'num_tag_close' => '</li>',
            'attributes' => array('class' => 'page-link')
        );
        $this->pagination->initialize($config);

        $data = array(
            'page_title' => 'Validasi Perizinan',
            'content_view' => 'admin/perizinan_content',
            'content_data' => array(
                'rows' => $this->Perizinan_model->list_for_admin_history($filters, $per_page, $offset),
                'status_map' => array(
                    '0' => 'Siap Cetak',
                    '1' => 'Menunggu Upload',
                    '2' => 'Menunggu Validasi',
                    '3' => 'Disetujui',
                    '4' => 'Ditolak'
                ),
                'pagination' => $this->pagination->create_links(),
                'filters' => $filters,
                'selected_status' => $filters['status'],
                'status_summary' => $this->User_model->get_santri_status_summary_for_admin()
            )
        );

        $this->load->view('layouts/admin', $data);
    }

    public function delete_selected()
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $ids = $this->input->post('selected_ids');
        if (empty($ids) || !is_array($ids)) {
            $this->session->set_flashdata('error', 'Pilih data yang ingin dihapus terlebih dahulu.');
            redirect('admin/perizinan');
            return;
        }

        $result = $this->Perizinan_model->delete_history($ids);
        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);

        $query = array();
        if ($this->input->post('q', TRUE) !== NULL && trim((string) $this->input->post('q', TRUE)) !== '') {
            $query['q'] = trim((string) $this->input->post('q', TRUE));
        }
        if ($this->input->post('status', TRUE) !== NULL && trim((string) $this->input->post('status', TRUE)) !== '') {
            $query['status'] = trim((string) $this->input->post('status', TRUE));
        }

        if (!empty($query)) {
            redirect('admin/perizinan?' . http_build_query($query));
            return;
        }

        redirect('admin/perizinan');
    }

    public function validate_upload($id)
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $decision = (string) $this->input->post('decision', TRUE);

        if (!in_array($decision, array('3', '4'), TRUE)) {
            $this->session->set_flashdata('error', 'Keputusan tidak valid.');
            redirect('admin/perizinan');
            return;
        }

        $ok = $this->Perizinan_model->validate_upload($id, $decision);

        if ($ok) {
            $msg = $decision === '3' ? 'Surat disetujui.' : 'Surat ditolak.';
            $this->session->set_flashdata('success', $msg);
        } else {
            $this->session->set_flashdata('error', 'Gagal memproses validasi.');
        }

        redirect('admin/perizinan');
    }

    public function selesaikan_izin($id)
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth = $this->current_user();
        $result = $this->Perizinan_model->toggle_izin_suspension(
            (string) $id,
            TRUE,
            isset($auth['nim']) ? $auth['nim'] : 'admin',
            'Izin diselesaikan sementara dari halaman admin perizinan.'
        );

        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        redirect('admin/perizinan');
    }

    public function lanjutkan_izin($id)
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth = $this->current_user();
        $result = $this->Perizinan_model->toggle_izin_suspension(
            (string) $id,
            FALSE,
            isset($auth['nim']) ? $auth['nim'] : 'admin',
            'Izin dilanjutkan kembali dari halaman admin perizinan.'
        );

        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        redirect('admin/perizinan');
    }
}
