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
     * List semua data periode haid
     */
    public function index()
    {
        $this->require_role(array('admin'));
        $this->load->library('pagination');

        $per_page = 10;
        $page = max(1, (int) $this->input->get('page', TRUE));
        $offset = ($page - 1) * $per_page;

        // Filter: Search dan Duration
        $search = trim((string) $this->input->get('q', TRUE));
        $duration_filter = trim((string) $this->input->get('duration', TRUE));
        
        if (!in_array($duration_filter, array('', 'singkat', 'normal', 'panjang'), TRUE)) {
            $duration_filter = '';
        }

        // Get filtered data
        $total = $this->Periode_haid_model->count_filtered($search, $duration_filter);
        $rows = $this->Periode_haid_model->get_filtered($search, $duration_filter, $per_page, $offset);

        $config = array(
            'base_url' => site_url('admin/periode-haid'),
            'total_rows' => $total,
            'per_page' => $per_page,
            'page_query_string' => TRUE,
            'query_string_segment' => 'page',
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
            'attributes' => array('class' => 'page-link'),
        );
        $this->pagination->initialize($config);

        $data = array(
            'page_title' => 'Kelola Periode Haid',
            'content_view' => 'admin/periode_haid_content',
            'content_data' => array(
                'rows' => $rows,
                'search' => $search,
                'duration_filter' => $duration_filter,
                'pagination' => $this->pagination->create_links(),
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    /**
     * Form create data periode haid
     */
    public function create()
    {
        $this->require_role(array('admin'));

        $data = array(
            'page_title' => 'Tambah Periode Haid',
            'content_view' => 'admin/periode_haid_form',
            'content_data' => array(
                'mode' => 'create',
                'row' => null,
                'santri_list' => $this->Periode_haid_model->get_santri_without_data(),
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    /**
     * Submit create data periode haid
     */
    public function store()
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth = $this->current_user();
        $nim = trim((string) $this->input->post('nim', TRUE));
        $rata_rata_hari = (int) $this->input->post('rata_rata_hari', TRUE);
        $paling_lama_hari = (int) $this->input->post('paling_lama_hari', TRUE);

        if ($nim === '') {
            $this->session->set_flashdata('error', 'Santri wajib dipilih.');
            redirect('admin/periode-haid/create');
            return;
        }

        $result = $this->Periode_haid_model->create($nim, $rata_rata_hari, $paling_lama_hari, $auth['nim']);

        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        redirect('admin/periode-haid' . (!$result['ok'] ? '/create' : ''));
    }

    /**
     * Form edit data periode haid
     */
    public function edit($id = null)
    {
        $this->require_role(array('admin'));

        $id = (int) $id;
        $row = $this->Periode_haid_model->get_by_id($id);

        if (!$row) {
            show_404();
            return;
        }

        // Fetch nama santri dari msmhs
        $santri_info = $this->db->select('NMMHSMSMHS AS nama')
            ->from('sim_akademik.msmhs')
            ->where('TRIM(NIMHSMSMHS)', trim($row['nim']))
            ->get()
            ->row_array();

        if ($santri_info) {
            $row['nama'] = $santri_info['nama'];
        } else {
            $row['nama'] = '';
        }

        $data = array(
            'page_title' => 'Edit Periode Haid',
            'content_view' => 'admin/periode_haid_form',
            'content_data' => array(
                'mode' => 'edit',
                'row' => $row,
                'santri_list' => array(),
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    /**
     * Submit update data periode haid
     */
    public function update($id = null)
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $id = (int) $id;
        $auth = $this->current_user();
        $rata_rata_hari = (int) $this->input->post('rata_rata_hari', TRUE);
        $paling_lama_hari = (int) $this->input->post('paling_lama_hari', TRUE);

        $result = $this->Periode_haid_model->update($id, $rata_rata_hari, $paling_lama_hari, $auth['nim']);

        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        redirect('admin/periode-haid' . (!$result['ok'] ? '/edit/' . $id : ''));
    }

    /**
     * Delete data periode haid
     */
    public function delete($id = null)
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $id = (int) $id;
        $result = $this->Periode_haid_model->delete($id);

        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        redirect('admin/periode-haid');
    }
}
