<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perizinan extends MY_Controller
{
    private $upload_path     = 'uploads/perizinan/';
    private $upload_max_size = 2097152; // 2MB

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Perizinan_model');
        $this->load->helper(array('form'));
        $this->load->library('form_validation');
    }

    // -------------------------------------------------------------------------
    // Halaman utama: form pengajuan + riwayat
    // -------------------------------------------------------------------------
    public function index()
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();
        $this->load->library('pagination');

        if (!empty($auth['must_reset_password'])) {
            redirect('auth/reset-password');
            return;
        }

        $per_page = 5;
        $page     = max(1, (int) $this->input->get('page', TRUE));
        $offset   = ($page - 1) * $per_page;

        $total = $this->Perizinan_model->count_for_user_history($auth['nim']);

        $config = array(
            'base_url'             => site_url('user/perizinan'),
            'total_rows'           => $total,
            'per_page'             => $per_page,
            'page_query_string'    => TRUE,
            'query_string_segment' => 'page',
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
            'page_title'   => 'Perizinan Santri',
            'content_view' => 'user/perizinan_content',
            'content_data' => array(
                'rows'           => $this->Perizinan_model->list_for_user_history($auth['nim'], $per_page, $offset),
                'status_map'     => Perizinan_model::get_status_map(),
                'kategori_map'   => Perizinan_model::get_kategori_tipe3(),
                'alasan_options' => $this->Perizinan_model->get_alasan_options(),
                'pagination'     => $this->pagination->create_links(),
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    // -------------------------------------------------------------------------
    // Submit pengajuan baru
    // -------------------------------------------------------------------------
    public function submit()
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $tipe_izin = trim((string) $this->input->post('tipe_izin', TRUE));

        if (!in_array($tipe_izin, array('1', '2', '3'), TRUE)) {
            $this->session->set_flashdata('error', 'Tipe izin tidak valid.');
            redirect('user/perizinan');
            return;
        }

        $tgl_mulai      = trim((string) $this->input->post('tgl_mulai', TRUE));
        $tgl_selesai    = trim((string) $this->input->post('tgl_selesai', TRUE));
        $smt            = (int) $this->input->post('smt', TRUE);
        $alasan_lainnya = trim((string) $this->input->post('alasan_lainnya', TRUE));

        // Validasi dasar
        if ($tgl_mulai === '') {
            $this->session->set_flashdata('error', 'Tanggal mulai wajib diisi.');
            redirect('user/perizinan');
            return;
        }

        if ($smt < 1 || $smt > 99) {
            $this->session->set_flashdata('error', 'Semester Wajib Diisi.');
            redirect('user/perizinan');
            return;
        }

        // Validasi tipe 1 & 2
        if ($tipe_izin === '1' || $tipe_izin === '2') {
            if ($tgl_selesai === '') {
                $this->session->set_flashdata('error', 'Tanggal selesai wajib diisi.');
                redirect('user/perizinan');
                return;
            }

            $error = $this->validate_durasi($tipe_izin, $tgl_mulai, $tgl_selesai);
            if ($error) {
                $this->session->set_flashdata('error', $error);
                redirect('user/perizinan');
                return;
            }
        }

        // Validasi tipe 3
        $sub_kategori_input = $this->input->post('sub_kategori', TRUE);
        $alasan_input       = $this->input->post('alasan_option', TRUE);
        $sub_kategori_list  = $this->Perizinan_model->parse_multi_input($sub_kategori_input);
        $alasan_list        = $this->Perizinan_model->parse_multi_input($alasan_input);

        // Tipe 1 & 2: minimal salah satu dari checkbox alasan ATAU alasan lainnya harus diisi
        if (($tipe_izin === '1' || $tipe_izin === '2') && empty($alasan_list) && $alasan_lainnya === '') {
            $this->session->set_flashdata('error', 'Alasan wajib diisi.');
            redirect('user/perizinan');
            return;
        }

        // Tipe 1 & 2: validasi setiap alasan yang dipilih harus ada di opsi database
        if (($tipe_izin === '1' || $tipe_izin === '2') && !empty($alasan_list)) {
            $valid_alasan_12 = $this->Perizinan_model->get_alasan_options();
            foreach ($alasan_list as $a) {
                if (!in_array($a, $valid_alasan_12, TRUE)) {
                    $this->session->set_flashdata('error', 'Alasan tidak valid.');
                    redirect('user/perizinan');
                    return;
                }
            }
        }

        if ($tipe_izin === '3') {
            $valid_kategori = array_keys(Perizinan_model::get_kategori_tipe3());
            $valid_alasan   = Perizinan_model::get_alasan_tipe3();

            if (empty($sub_kategori_list)) {
                $this->session->set_flashdata('error', 'Kategori wajib dipilih.');
                redirect('user/perizinan');
                return;
            }

            foreach ($sub_kategori_list as $k) {
                if (!in_array($k, $valid_kategori, TRUE)) {
                    $this->session->set_flashdata('error', 'Kategori tidak valid.');
                    redirect('user/perizinan');
                    return;
                }
            }

            if (empty($alasan_list)) {
                $this->session->set_flashdata('error', 'Alasan wajib dipilih.');
                redirect('user/perizinan');
                return;
            }

            foreach ($alasan_list as $a) {
                if (!in_array($a, $valid_alasan, TRUE)) {
                    $this->session->set_flashdata('error', 'Alasan tidak valid.');
                    redirect('user/perizinan');
                    return;
                }
            }
        }

        // Handle upload dokumentasi opsional (tipe 3 saat pengajuan)
        $dokumentasi_filename = null;
        if ($tipe_izin === '3' && !empty($_FILES['dokumentasi']['name']) && $_FILES['dokumentasi']['error'] === UPLOAD_ERR_OK) {
            $file    = $_FILES['dokumentasi'];
            $allowed = array('pdf', 'jpg', 'jpeg', 'png');
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed, TRUE) && (int) $file['size'] <= $this->upload_max_size) {
                $upload_dir = FCPATH . $this->upload_path;
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, TRUE);
                $dokumentasi_filename = 'dok_' . trim($auth['nim']) . '_' . time() . '.' . $ext;
                if (!move_uploaded_file($file['tmp_name'], $upload_dir . $dokumentasi_filename)) {
                    $dokumentasi_filename = null;
                }
            }
        }

        // Buat pengajuan
        $create_result = $this->Perizinan_model->create_pengajuan($auth['nim'], array(
            'tipe_izin'      => $tipe_izin,
            'sub_kategori'   => $tipe_izin === '3' ? $sub_kategori_list : array(),
            'tgl_mulai'      => $tgl_mulai,
            'tgl_selesai'    => ($tipe_izin === '1' || $tipe_izin === '2') ? $tgl_selesai : $tgl_mulai,
            'alasan'         => $alasan_list,
            'alasan_lainnya' => ($tipe_izin === '1' || $tipe_izin === '2') ? $alasan_lainnya : '',
            'smt'            => $smt,
        ));

        $id = '';
        if (is_array($create_result)) {
            if (!empty($create_result['ok']) && !empty($create_result['id'])) {
                $id = (string) $create_result['id'];
            } else {
                $message = !empty($create_result['message']) ? $create_result['message'] : 'Gagal menyimpan pengajuan izin.';
                $this->session->set_flashdata('error', $message);
                redirect('user/perizinan');
                return;
            }
        } else {
            $id = trim((string) $create_result);
        }

        if ($id === '') {
            $this->session->set_flashdata('error', 'Gagal menyimpan pengajuan izin.');
            redirect('user/perizinan');
            return;
        }

        // Simpan dokumentasi jika ada
        if ($dokumentasi_filename) {
            $this->Perizinan_model->save_dokumentasi($id, $dokumentasi_filename);
        }

        // Generate surat otomatis
        $this->generate_and_save_auto_surat($id, $auth['nim']);

        $this->session->set_flashdata('success', 'Pengajuan perizinan berhasil dikirim.');
        redirect('user/perizinan');
    }

    // -------------------------------------------------------------------------
    // Download surat (generate ulang PDF)
    // -------------------------------------------------------------------------
    public function download($id = null)
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        $id   = trim((string) $id);
        $izin = $this->Perizinan_model->get_izin_for_print($id, $auth['nim']);

        if (!$izin) {
            show_404();
            return;
        }

        if (!in_array((string) $izin['status'], array('0', '1', '4'), TRUE)) {
            $this->session->set_flashdata('error', 'Surat tidak bisa diunduh pada status saat ini.');
            redirect('user/perizinan');
            return;
        }

        $this->Perizinan_model->mark_waiting_upload($id, $auth['nim']);

        $filename = $this->generate_pdf_file($this->build_pdf_data($izin));
        if ($filename === FALSE) {
            $this->session->set_flashdata('error', 'Gagal membuat file PDF.');
            redirect('user/perizinan');
            return;
        }

        $this->Perizinan_model->save_file_upload($id, $filename);
        redirect(base_url($this->upload_path . $filename));
    }

    // -------------------------------------------------------------------------
    // Upload surat (inline, AJAX-friendly POST)
    // -------------------------------------------------------------------------
    public function upload_surat($id = null)
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $id   = trim((string) $id);
        $izin = $this->Perizinan_model->get_izin_by_id_and_nim($id, $auth['nim']);

        if (!$izin) {
            $this->session->set_flashdata('error', 'Pengajuan tidak ditemukan.');
            redirect('user/perizinan');
            return;
        }

        if (!in_array((string) $izin['status'], array('0', '1', '4'), TRUE)) {
            $this->session->set_flashdata('error', 'Pengajuan ini tidak bisa diupload pada status saat ini.');
            redirect('user/perizinan');
            return;
        }

        if (empty($_FILES['surat_file']['name'])) {
            $this->session->set_flashdata('error', 'File surat wajib dipilih.');
            redirect('user/perizinan');
            return;
        }

        // Validasi manual: hanya PDF
        $file = $_FILES['surat_file'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf' || $file['error'] !== UPLOAD_ERR_OK) {
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
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, TRUE);

        // Ambil nama dari data print agar ada nama santri
        $izin_print = $this->Perizinan_model->get_izin_for_print($id, $auth['nim']);
        $filename   = $this->get_surat_filename($izin_print ?: $izin);
        $dest       = $upload_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->session->set_flashdata('error', 'Gagal menyimpan file.');
            redirect('user/perizinan');
            return;
        }

        // Hapus file lama jika beda nama
        $old_file = $izin['file_upload'];
        if ($old_file && $old_file !== $filename && file_exists($upload_dir . $old_file)) {
            unlink($upload_dir . $old_file);
        }

        $result = $this->Perizinan_model->upload_surat_file($id, $auth['nim'], $filename);

        if ($result['ok']) {
            $next = isset($result['next_status']) ? $result['next_status'] : '2';
            if ($next === '5') {
                $this->session->set_flashdata('success', 'Surat berhasil diupload. Silakan upload dokumentasi pendukung.');
            } else {
                $this->session->set_flashdata('success', 'Surat berhasil diupload dan menunggu validasi admin.');
            }
        } else {
            if (file_exists($dest)) unlink($dest);
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('user/perizinan');
    }

    // -------------------------------------------------------------------------
    // Upload dokumentasi (inline POST)
    // -------------------------------------------------------------------------
    public function upload_dokumentasi($id = null)
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $id   = trim((string) $id);
        $izin = $this->Perizinan_model->get_izin_by_id_and_nim($id, $auth['nim']);

        if (!$izin) {
            $this->session->set_flashdata('error', 'Pengajuan tidak ditemukan.');
            redirect('user/perizinan');
            return;
        }

        if (empty($_FILES['dokumentasi_file']['name'])) {
            $this->session->set_flashdata('error', 'File dokumentasi wajib dipilih.');
            redirect('user/perizinan');
            return;
        }

        $file    = $_FILES['dokumentasi_file'];
        $allowed = array('pdf', 'jpg', 'jpeg', 'png');
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, TRUE) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'Format file tidak diperbolehkan. Gunakan PDF/JPG/PNG.');
            redirect('user/perizinan');
            return;
        }

        if ((int) $file['size'] > $this->upload_max_size) {
            $this->session->set_flashdata('error', 'File maksimal 2MB.');
            redirect('user/perizinan');
            return;
        }

        $upload_dir = FCPATH . $this->upload_path;
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, TRUE);

        $filename = 'dok_' . trim($auth['nim']) . '_' . time() . '.' . $ext;
        $dest     = $upload_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->session->set_flashdata('error', 'Gagal menyimpan file.');
            redirect('user/perizinan');
            return;
        }

        // Hapus file dokumentasi lama
        $old_dok = $izin['dokumentasi'];
        if ($old_dok && file_exists($upload_dir . $old_dok)) {
            @unlink($upload_dir . $old_dok);
        }

        $result = $this->Perizinan_model->upload_dokumentasi_file($id, $auth['nim'], $filename);

        if ($result['ok']) {
            $this->session->set_flashdata('success', 'Dokumentasi berhasil diupload dan menunggu validasi admin.');
        } else {
            if (file_exists($dest)) unlink($dest);
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('user/perizinan');
    }

    // -------------------------------------------------------------------------
    // Lihat file dokumentasi
    // -------------------------------------------------------------------------
    public function dokumentasi($id = null)
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        $id      = trim((string) $id);
        $dok     = $this->Perizinan_model->get_dokumentasi($id, $auth['nim']);

        if (!$dok || empty($dok['dokumentasi'])) {
            $this->session->set_flashdata('error', 'Dokumentasi tidak ditemukan.');
            redirect('user/perizinan');
            return;
        }

        $file_path = FCPATH . $this->upload_path . $dok['dokumentasi'];
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server.');
            redirect('user/perizinan');
            return;
        }

        $ext        = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime_types = array(
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
        );
        $mime = isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }

    // -------------------------------------------------------------------------
    // Cetak surat (setelah disetujui)
    // -------------------------------------------------------------------------
    public function cetak($id = null)
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        $id   = trim((string) $id);
        $izin = $this->Perizinan_model->get_izin_for_print($id, $auth['nim']);

        if (!$izin || (string) $izin['acc'] !== '1') {
            $this->session->set_flashdata('error', 'Surat hanya bisa dicetak jika izin sudah disetujui.');
            redirect('user/perizinan');
            return;
        }

        $filename = $this->generate_pdf_file($this->build_pdf_data($izin));
        if ($filename === FALSE) {
            $this->session->set_flashdata('error', 'Gagal membuat file PDF.');
            redirect('user/perizinan');
            return;
        }

        redirect(base_url($this->upload_path . $filename));
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function validate_durasi($tipe_izin, $tgl_mulai, $tgl_selesai)
    {
        try {
            $start  = new DateTime($tgl_mulai);
            $end    = new DateTime($tgl_selesai);
            $durasi = (int) $start->diff($end)->days + 1;
        } catch (Exception $e) {
            return 'Format tanggal tidak valid.';
        }

        if ($end < $start) {
            return 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
        }

        if ($tipe_izin === '1' && $durasi > 14) {
            return 'Izin kurang dari 2 minggu maksimal 14 hari.';
        }

        if ($tipe_izin === '2' && $durasi < 15) {
            return 'Izin lebih dari 2 minggu minimal 15 hari.';
        }

        return null;
    }

    private function build_pdf_data(array $izin)
    {
        $start = new DateTime($izin['tgl_mulai']);
        $end   = new DateTime($izin['tgl_selesai']);

        return array(
            'izin'         => $izin,
            'durasi_hari'  => (int) $start->diff($end)->days + 1,
            'surat_date'   => !empty($izin['approved_at']) ? $izin['approved_at'] : date('Y-m-d H:i:s'),
            'pdf_template' => $this->get_pdf_template_info($izin),
        );
    }

    private function get_pdf_template_info(array $izin)
    {
        $tipe        = isset($izin['tipe_izin']) ? (string) $izin['tipe_izin'] : '';
        $sub_list    = array_values(array_filter(array_map('trim', explode(',', isset($izin['sub_kategori']) ? (string) $izin['sub_kategori'] : ''))));

        $templates = array(
            '1' => array('title' => 'SURAT IZIN MENINGGALKAN ASRAMA KURANG DARI DUA MINGGU',                          'show_footnote' => TRUE),
            '2' => array('title' => 'SURAT IZIN MENINGGALKAN ASRAMA LEBIH DARI DUA MINGGU DAN BEBAS BIAYA MAKAN SEMENTARA', 'show_footnote' => TRUE),
            '3_jamaah_maghrib' => array('title' => 'SURAT IZIN TIDAK BERJAMAAH SHOLAT MAGHRIB',  'show_footnote' => FALSE),
            '3_jamaah_isya'    => array('title' => 'SURAT IZIN TIDAK BERJAMAAH SHOLAT ISYA',     'show_footnote' => FALSE),
            '3_jamaah_subuh'   => array('title' => 'SURAT IZIN TIDAK BERJAMAAH SHOLAT SUBUH',    'show_footnote' => FALSE),
            '3_ngaji_maghrib'  => array('title' => 'SURAT IZIN TIDAK MENGAJI BA\'DA MAGHRIB',    'show_footnote' => FALSE),
            '3_ngaji_subuh'    => array('title' => 'SURAT IZIN TIDAK MENGAJI BA\'DA SUBUH',      'show_footnote' => FALSE),
            '3_multi'          => array('title' => 'SURAT IZIN TIDAK MENGIKUTI KEGIATAN BERJAMAAH DAN MENGAJI', 'show_footnote' => FALSE),
        );

        $key = '';
        if ($tipe === '1' || $tipe === '2') {
            $key = $tipe;
        } elseif ($tipe === '3') {
            if (count($sub_list) === 1) {
                $key = '3_' . $sub_list[0];
            } else {
                $key = '3_multi';
            }
        }

        $template = isset($templates[$key]) ? $templates[$key] : array(
            'title'         => 'SURAT IZIN PPM ALMA ATA',
            'show_footnote' => FALSE,
        );

        $template['subtitle'] = '';
        return $template;
    }

    private function generate_and_save_auto_surat($id, $nim)
    {
        $izin = $this->Perizinan_model->get_izin_for_print($id, $nim);
        if (!$izin) return FALSE;

        $filename = $this->generate_pdf_file($this->build_pdf_data($izin));
        if ($filename !== FALSE) {
            $this->Perizinan_model->save_file_upload($id, $filename);
            return TRUE;
        }

        return FALSE;
    }

    private function get_surat_filename(array $izin)
    {
        $nama    = $this->slugify(isset($izin['nama']) ? $izin['nama'] : 'santri');
        $nim     = $this->slugify(isset($izin['nim']) ? $izin['nim'] : 'nim');
        $tanggal = isset($izin['tgl_mulai']) ? date('Ymd', strtotime($izin['tgl_mulai'])) : date('Ymd');

        return $nama . '_' . $nim . '_SuratIzin_' . $tanggal . '.pdf';
    }

    private function slugify($value)
    {
        $value = trim(strtolower((string) $value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim($value, '_') ?: 'file';
    }

    private function generate_pdf_file(array $view_data)
    {
        $autoload = FCPATH . 'vendor/autoload.php';
        if (!file_exists($autoload)) return FALSE;

        require_once $autoload;

        $old = error_reporting(error_reporting() & ~E_WARNING);
        $exists = class_exists('Dompdf\\Dompdf');
        error_reporting($old);

        if (!$exists) return FALSE;

        $upload_dir = FCPATH . $this->upload_path;
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, TRUE);

        $html   = $this->load->view('user/surat_izin_pdf', $view_data, TRUE);
        $dompdf = new Dompdf\Dompdf(array('isRemoteEnabled' => TRUE));

        if (method_exists($dompdf, 'set_option')) {
            $dompdf->set_option('isHtml5ParserEnabled', true);
        }

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');

        try {
            $dompdf->render();
        } catch (Exception $e) {
            log_message('error', 'Dompdf render error: ' . $e->getMessage());
            return FALSE;
        }

        $filename = $this->get_surat_filename($view_data['izin']);
        $saved    = file_put_contents($upload_dir . $filename, $dompdf->output());

        return $saved !== FALSE ? $filename : FALSE;
    }
}
