<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perizinan extends MY_Controller
{
    private $upload_path = 'uploads/perizinan/';
    private $upload_max_size = 2097152; // 2MB in bytes

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Perizinan_model');
        $this->load->helper(array('form'));
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();
        $this->load->library('pagination');

        if (!empty($auth['must_reset_password'])) {
            redirect('auth/reset-password');
            return;
        }

        $per_page = 8;
        $page = (int) $this->input->get('page', TRUE);
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $per_page;

        $config = array(
            'base_url' => site_url('user/perizinan'),
            'total_rows' => $this->Perizinan_model->count_for_user_history($auth['nim']),
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
            'attributes' => array('class' => 'page-link')
        );
        $this->pagination->initialize($config);

        $data = array(
            'page_title' => 'Perizinan Santri',
            'content_view' => 'user/perizinan_content',
            'content_data' => array(
                'rows' => $this->Perizinan_model->list_for_user_history($auth['nim'], $per_page, $offset),
                'status_map' => array(
                    '0' => 'Siap Cetak',
                    '1' => 'Menunggu Upload',
                    '2' => 'Menunggu Validasi',
                    '3' => 'Disetujui',
                    '4' => 'Ditolak'
                ),
                'pagination' => $this->pagination->create_links(),
                'alasan_options' => $this->Perizinan_model->get_alasan_options()
            )
        );

        $this->load->view('layouts/admin', $data);
    }

    public function submit()
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $this->form_validation->set_rules('tgl_mulai', 'Tanggal Mulai', 'required');
        $this->form_validation->set_rules('tgl_selesai', 'Tanggal Selesai', 'required');
        $this->form_validation->set_rules('alasan_option', 'Alasan', 'required');
        $this->form_validation->set_rules('smt', 'Semester', 'required|integer');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('user/perizinan');
            return;
        }

        $alasan_option = trim((string) $this->input->post('alasan_option', TRUE));
        $alasan_custom = trim((string) $this->input->post('alasan_custom', TRUE));
        if ($alasan_option === '__lainnya__') {
            if (mb_strlen($alasan_custom) < 5) {
                $this->session->set_flashdata('error', 'Alasan lainnya minimal 5 karakter.');
                redirect('user/perizinan');
                return;
            }
            $alasan = $alasan_custom;
        } else {
            $alasan = $alasan_option;
        }

        $is_haid = $this->Perizinan_model->is_haid_alasan($alasan);
        if ($is_haid) {
            try {
                $start = new DateTime((string) $this->input->post('tgl_mulai', TRUE));
                $end = new DateTime((string) $this->input->post('tgl_selesai', TRUE));
            } catch (Exception $e) {
                $this->session->set_flashdata('error', 'Format tanggal tidak valid.');
                redirect('user/perizinan');
                return;
            }

            if ($end < $start) {
                $this->session->set_flashdata('error', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
                redirect('user/perizinan');
                return;
            }

            $durasi = (int) $start->diff($end)->days + 1;
            if ($durasi > 14) {
                $this->session->set_flashdata('error', 'Izin haid maksimal 14 hari (termasuk tanggal mulai dan selesai).');
                redirect('user/perizinan');
                return;
            }
        }

        $this->Perizinan_model->create_pengajuan(
            $auth['nim'],
            $this->input->post('tgl_mulai', TRUE),
            $this->input->post('tgl_selesai', TRUE),
            $alasan,
            (int) $this->input->post('smt', TRUE)
        );

        if ($is_haid) {
            $this->session->set_flashdata('success', 'Pengajuan izin haid berhasil dikirim dan menunggu validasi admin (tanpa surat).');
        } else {
            $this->session->set_flashdata('success', 'Pengajuan perizinan berhasil dikirim.');
        }
        redirect('user/perizinan');
    }

    public function reapply_haid($id = null)
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $id = trim((string) $id);
        if ($id === '') {
            show_404();
            return;
        }

        $izin = $this->Perizinan_model->get_izin_by_id_and_nim($id, $auth['nim']);
        if (!$izin || !$this->Perizinan_model->is_haid_alasan($izin['alasan'])) {
            show_404();
            return;
        }

        if ((string) $izin['status'] !== '4') {
            $this->session->set_flashdata('error', 'Pengajuan ulang hanya tersedia untuk izin haid yang ditolak.');
            redirect('user/perizinan');
            return;
        }

        $ok = $this->Perizinan_model->reapply_haid_same_record($id, $auth['nim']);
        if (!$ok) {
            $this->session->set_flashdata('error', 'Gagal mengajukan ulang izin haid.');
            redirect('user/perizinan');
            return;
        }

        $this->session->set_flashdata('success', 'Pengajuan ulang izin haid berhasil dikirim dan menunggu validasi admin.');
        redirect('user/perizinan');
    }

    public function download($id = null)
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        $id = trim((string) $id);
        if ($id === '') {
            show_404();
            return;
        }

        $izin = $this->Perizinan_model->get_izin_for_print($id, $auth['nim']);
        if (!$izin) {
            show_404();
            return;
        }

        if ($this->Perizinan_model->is_haid_alasan($izin['alasan']) || $this->Perizinan_model->is_sakit_alasan($izin['alasan'])) {
            $this->session->set_flashdata('error', 'Izin haid dan sakit tidak menggunakan surat unduh/cetak.');
            redirect('user/perizinan');
            return;
        }

        $this->Perizinan_model->mark_waiting_upload($id, $auth['nim']);

        $start = new DateTime($izin['tgl_mulai']);
        $end = new DateTime($izin['tgl_selesai']);

        $data = array(
            'izin' => $izin,
            'durasi_hari' => (int) $start->diff($end)->days + 1,
            'surat_date' => date('Y-m-d H:i:s')
        );

        $filename = $this->generate_pdf_file($data);
        if ($filename === FALSE) {
            $this->session->set_flashdata('error', 'Gagal membuat file PDF.');
            redirect('user/perizinan');
            return;
        }

        redirect(base_url($this->upload_path . $filename));
    }

    public function upload($id = null)
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        $id = trim((string) $id);
        if ($id === '') {
            show_404();
            return;
        }

        $izin = $this->Perizinan_model->get_izin_by_id_and_nim($id, $auth['nim']);
        if (!$izin) {
            show_404();
            return;
        }

        $izin_detail = $this->Perizinan_model->get_izin_for_print($id, $auth['nim']);
        if ($izin_detail) {
            $izin = array_merge($izin, $izin_detail);
        }

        if ($this->Perizinan_model->is_haid_alasan($izin['alasan'])) {
            $this->session->set_flashdata('error', 'Izin haid tidak memerlukan upload surat.');
            redirect('user/perizinan');
            return;
        }

        if (!in_array((string) $izin['status'], array('0', '1', '4'), TRUE)) {
            $this->session->set_flashdata('error', 'Pengajuan ini tidak bisa diupload pada status saat ini.');
            redirect('user/perizinan');
            return;
        }

        if ($this->input->method(TRUE) === 'POST') {
            if (!isset($_FILES['surat_file']) || $_FILES['surat_file']['error'] !== UPLOAD_ERR_OK) {
                $this->session->set_flashdata('error', 'File tidak ditemukan atau ada error upload.');
                redirect('user/perizinan');
                return;
            }

            $file = $_FILES['surat_file'];
            if ($file['type'] !== 'application/pdf') {
                $this->session->set_flashdata('error', 'File harus berformat PDF.');
                redirect('user/perizinan');
                return;
            }

            if ((int) $file['size'] > $this->upload_max_size) {
                $this->session->set_flashdata('error', 'File maksimal 2MB.');
                redirect('user/perizinan');
                return;
            }

            $upload_dir = FCPATH . $this->upload_path;
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, TRUE);
            }

            $filename = $this->get_surat_filename($izin);
            $dest = $upload_dir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $this->session->set_flashdata('error', 'Gagal menyimpan file.');
                redirect('user/perizinan');
                return;
            }

            $old_file = $izin['file_upload'];
            $result = $this->Perizinan_model->upload_surat_file($id, $auth['nim'], $filename);

            if ($result['ok']) {
                if ($old_file && $old_file !== $filename && file_exists($upload_dir . $old_file)) {
                    unlink($upload_dir . $old_file);
                }
                $this->session->set_flashdata('success', 'File surat berhasil diupload.');
            } else {
                if (file_exists($dest)) {
                    unlink($dest);
                }
                $this->session->set_flashdata('error', $result['message']);
            }

            redirect('user/perizinan');
            return;
        }

        $data = array(
            'page_title' => 'Upload Surat Izin',
            'content_view' => 'user/perizinan_upload',
            'content_data' => array(
                'izin' => $izin,
                'is_haid' => $this->Perizinan_model->is_haid_alasan($izin['alasan']),
                'is_sakit' => $this->Perizinan_model->is_sakit_alasan($izin['alasan']),
                'upload_title' => $this->Perizinan_model->is_sakit_alasan($izin['alasan']) ? 'Upload Surat Keterangan Sakit' : 'Upload Surat Izin',
                'upload_subtitle' => $this->Perizinan_model->is_sakit_alasan($izin['alasan']) ? 'Upload surat keterangan sakit dalam bentuk PDF.' : 'Upload surat yang sudah ditandatangani oleh pimpinan pondok.',
                'upload_notice' => $this->Perizinan_model->is_sakit_alasan($izin['alasan']) ? 'Pastikan surat keterangan sakit sudah lengkap dan jelas sebelum diupload.' : 'Pastikan surat sudah ditandatangani tangan oleh pimpinan pondok sebelum diupload.',
                'upload_file_hint' => 'Format: PDF | Ukuran maksimal: 2 MB'
            )
        );

        $this->load->view('layouts/admin', $data);
    }

    public function cetak($id = null)
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        $id = trim((string) $id);
        if ($id === '') {
            show_404();
            return;
        }

        $izin = $this->Perizinan_model->get_izin_for_print($id, $auth['nim']);
        if (!$izin || (string) $izin['acc'] !== '1') {
            $this->session->set_flashdata('error', 'Surat hanya bisa dicetak jika izin sudah disetujui.');
            redirect('user/perizinan');
            return;
        }

        if ($this->Perizinan_model->is_haid_alasan($izin['alasan']) || $this->Perizinan_model->is_sakit_alasan($izin['alasan'])) {
            $this->session->set_flashdata('error', 'Izin haid dan sakit tidak menggunakan surat unduh/cetak.');
            redirect('user/perizinan');
            return;
        }

        $start = new DateTime($izin['tgl_mulai']);
        $end = new DateTime($izin['tgl_selesai']);

        $data = array(
            'izin' => $izin,
            'durasi_hari' => (int) $start->diff($end)->days + 1,
            'surat_date' => !empty($izin['approved_at']) ? $izin['approved_at'] : date('Y-m-d H:i:s')
        );

        $filename = $this->generate_pdf_file($data);
        if ($filename === FALSE) {
            $this->session->set_flashdata('error', 'Gagal membuat file PDF.');
            redirect('user/perizinan');
            return;
        }

        redirect(base_url($this->upload_path . $filename));
    }

    private function get_surat_filename(array $izin)
    {
        $nama = $this->slugify_filename_part($izin['nama'] ?? 'nama');
        $nim = $this->slugify_filename_part($izin['nim'] ?? 'nim');
        $tanggal = $this->format_filename_date($izin['tgl_mulai'] ?? ($izin['tgl_ajuan'] ?? date('Y-m-d')));

        return $nama . '_' . $nim . '_SuratIzin_' . $tanggal . '.pdf';
    }

    private function slugify_filename_part($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 'nama';
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/i', '_', $value);
        $value = trim($value, '_');

        return $value === '' ? 'nama' : $value;
    }

    private function format_filename_date($date)
    {
        try {
            $dt = new DateTime((string) $date);
            return $dt->format('Ymd');
        } catch (Exception $e) {
            return date('Ymd');
        }
    }

    private function generate_pdf_file(array $view_data)
    {
        $autoload = FCPATH . 'vendor/autoload.php';
        if (!file_exists($autoload)) {
            return FALSE;
        }

        require_once $autoload;
        if (!class_exists('Dompdf\Dompdf')) {
            return FALSE;
        }

        $upload_dir = FCPATH . $this->upload_path;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, TRUE);
        }

        $html = $this->load->view('user/surat_izin_pdf', $view_data, TRUE);

        $dompdf = new Dompdf\Dompdf(array(
            'isRemoteEnabled' => TRUE
        ));
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = $this->get_surat_filename($view_data['izin']);
        $saved = file_put_contents($upload_dir . $filename, $dompdf->output());
        if ($saved === FALSE) {
            return FALSE;
        }

        return $filename;
    }
}
