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
            'q'           => trim((string) $this->input->get('q', TRUE)),
            'status'      => trim((string) $this->input->get('status', TRUE)),
            'tipe_izin'   => trim((string) $this->input->get('tipe_izin', TRUE)),
            'kamar'       => trim((string) $this->input->get('kamar', TRUE)),
            'smt'         => trim((string) $this->input->get('smt', TRUE)),
            'alasan'      => trim((string) $this->input->get('alasan', TRUE)),
            'sub_kategori'=> trim((string) $this->input->get('sub_kategori', TRUE)),
            'tgl_dari'    => trim((string) $this->input->get('tgl_dari', TRUE)),
            'tgl_sampai'  => trim((string) $this->input->get('tgl_sampai', TRUE)),
        );

        $per_page = 10;
        $page     = max(1, (int) $this->input->get('page', TRUE));
        $offset   = ($page - 1) * $per_page;

        $total = $this->Perizinan_model->count_for_admin($filters);

        $config = array(
            'base_url'             => site_url('admin/perizinan'),
            'total_rows'           => $total,
            'per_page'             => $per_page,
            'page_query_string'    => TRUE,
            'query_string_segment' => 'page',
            'reuse_query_string'   => TRUE,
            'use_page_numbers'     => TRUE,
            'full_tag_open'        => '<ul class="pagination pagination-sm mb-0">',
            'full_tag_close'       => '</ul>',
            'first_tag_open'       => '<li class="page-item">',
            'first_tag_close'      => '</li>',
            'last_tag_open'        => '<li class="page-item">',
            'last_tag_close'       => '</li>',
            'next_tag_open'        => '<li class="page-item">',
            'next_tag_close'       => '</li>',
            'prev_tag_open'        => '<li class="page-item">',
            'prev_tag_close'       => '</li>',
            'cur_tag_open'         => '<li class="page-item active"><span class="page-link">',
            'cur_tag_close'        => '</span></li>',
            'num_tag_open'         => '<li class="page-item">',
            'num_tag_close'        => '</li>',
            'attributes'           => array('class' => 'page-link'),
        );
        $this->pagination->initialize($config);

        $data = array(
            'page_title'   => 'Validasi Perizinan',
            'content_view' => 'admin/perizinan_content',
            'content_data' => array(
                'rows'            => $this->Perizinan_model->list_for_admin($filters, $per_page, $offset),
                'status_map'      => Perizinan_model::get_status_map(),
                'kategori_map'    => Perizinan_model::get_kategori_tipe3(),
                'kamar_list'      => $this->Perizinan_model->get_kamar_list(),
                'alasan_list'     => $this->Perizinan_model->get_alasan_list(),
                'pagination'      => $this->pagination->create_links(),
                'filters'         => $filters,
                'status_summary'  => $this->User_model->get_santri_status_summary_for_admin(),
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    public function backup_filtered()
    {
        $this->require_role(array('admin'));

        $filters = $this->get_filters_from_request('get');

        if ($this->is_filters_empty($filters)) {
            $this->session->set_flashdata('error', 'Terapkan minimal satu filter sebelum backup.');
            redirect('admin/perizinan');
            return;
        }

        $rows = $this->Perizinan_model->get_for_backup_filtered($filters);

        if (empty($rows)) {
            $this->session->set_flashdata('error', 'Tidak ada data yang sesuai filter.');
            redirect('admin/perizinan?' . http_build_query(array_filter($filters)));
            return;
        }

        @ini_set('memory_limit', '256M');
        @set_time_limit(120);

        $filename = 'backup_izin_filtered_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, array_keys($rows[0]), ';');
        foreach ($rows as $row) {
            fputcsv($output, array_values($row), ';');
        }
        fclose($output);
        exit;
    }

    public function hapus_filtered()
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $filters    = $this->get_filters_from_request('post');
        $konfirmasi = trim((string) $this->input->post('konfirmasi', TRUE));

        if ($this->is_filters_empty($filters)) {
            $this->session->set_flashdata('error', 'Tidak ada filter aktif.');
            redirect('admin/perizinan');
            return;
        }

        if ($konfirmasi !== 'HAPUS') {
            $this->session->set_flashdata('error', 'Ketik HAPUS untuk konfirmasi penghapusan.');
            redirect('admin/perizinan?' . http_build_query(array_filter($filters)));
            return;
        }

        $result = $this->Perizinan_model->delete_by_filters($filters);

        if ($result['ok']) {
            $this->session->set_flashdata('success', 'Berhasil menghapus ' . $result['deleted'] . ' data izin.');
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('admin/perizinan');
    }

    private function get_filters_from_request($method = 'get')
    {
        $input = $method === 'post' ? $this->input->post(NULL, TRUE) : $this->input->get(NULL, TRUE);
        $keys  = array('q','status','tipe_izin','kamar','smt','alasan','sub_kategori','tgl_dari','tgl_sampai');
        $filters = array();
        foreach ($keys as $key) {
            $filters[$key] = isset($input[$key]) ? trim((string) $input[$key]) : '';
        }
        return $filters;
    }

    private function is_filters_empty(array $filters)
    {
        foreach ($filters as $val) {
            if ($val !== '') return FALSE;
        }
        return TRUE;
    }

    public function validate_izin($id)
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth     = $this->current_user();
        $decision = (string) $this->input->post('decision', TRUE);
        $note     = trim((string) $this->input->post('approval_note', TRUE));

        if (!in_array($decision, array('3', '4'), TRUE)) {
            $this->session->set_flashdata('error', 'Keputusan tidak valid.');
            redirect('admin/perizinan');
            return;
        }

        // Cek dokumentasi wajib untuk tipe 3 dengan alasan kerkom/rapat/kuliah
        if ($decision === '3') {
            $izin = $this->Perizinan_model->get_izin_by_id($id);
            if ($izin
                && (string) $izin['tipe_izin'] === '3'
                && $this->Perizinan_model->is_perlu_dokumentasi($izin['alasan'])
                && empty($izin['dokumentasi'])
            ) {
                $this->session->set_flashdata('error', 'Dokumentasi pendukung wajib ada sebelum menyetujui pengajuan ini.');
                redirect('admin/perizinan');
                return;
            }
        }

        $ok = $this->Perizinan_model->validate_izin(
            $id,
            $decision,
            isset($auth['nim']) ? $auth['nim'] : 'admin',
            $note !== '' ? $note : null
        );

        if ($ok) {
            $this->session->set_flashdata('success', $decision === '3' ? 'Izin disetujui.' : 'Izin ditolak.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memproses validasi. Pastikan status pengajuan adalah "Menunggu Validasi".');
        }

        redirect('admin/perizinan');
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
            $this->session->set_flashdata('error', 'Pilih data yang ingin dihapus.');
            redirect('admin/perizinan');
            return;
        }

        $result = $this->Perizinan_model->delete_history($ids);
        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);

        $query = array_filter(array(
            'q'           => $this->input->post('q', TRUE),
            'status'      => $this->input->post('status', TRUE),
            'tipe_izin'   => $this->input->post('tipe_izin', TRUE),
            'kamar'       => $this->input->post('kamar', TRUE),
            'smt'         => $this->input->post('smt', TRUE),
            'alasan'      => $this->input->post('alasan', TRUE),
            'sub_kategori'=> $this->input->post('sub_kategori', TRUE),
            'tgl_dari'    => $this->input->post('tgl_dari', TRUE),
            'tgl_sampai'  => $this->input->post('tgl_sampai', TRUE),
        ));

        redirect('admin/perizinan' . (!empty($query) ? '?' . http_build_query($query) : ''));
    }

    public function selesaikan_izin($id)
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth   = $this->current_user();
        $result = $this->Perizinan_model->toggle_izin_suspension(
            (string) $id, TRUE,
            isset($auth['nim']) ? $auth['nim'] : 'admin'
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

        $auth   = $this->current_user();
        $result = $this->Perizinan_model->toggle_izin_suspension(
            (string) $id, FALSE,
            isset($auth['nim']) ? $auth['nim'] : 'admin'
        );

        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
        redirect('admin/perizinan');
    }
}
