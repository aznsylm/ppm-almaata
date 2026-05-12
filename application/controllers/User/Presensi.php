<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Presensi extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Presensi_model');
        $this->load->model('User_model');
    }

    // -------------------------------------------------------------------------
    // Dashboard Presensi Santri
    // -------------------------------------------------------------------------
    public function index()
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();
        $nim  = $auth['nim'];

        // Get profile santri
        $profile = $this->User_model->get_profile_by_nim($nim);

        // Get minggu aktif
        $minggu = Presensi_model::get_minggu_aktif();

        // Get jadwal aktif sekarang (untuk tombol presensi)
        // Jika user adalah admin, tampilkan semua jadwal aktif untuk testing
        if ($this->session->userdata('role') === 'admin') {
            $jadwal_aktif_sekarang = $this->db
                ->where('is_active', 1)
                ->get('presensi_jadwal')
                ->result_array();
        } else {
            $jadwal_aktif_sekarang = $this->Presensi_model->get_jadwal_aktif_sekarang();
        }

        // Get presensi hari ini
        $presensi_hari_ini = $this->Presensi_model->get_presensi_hari_ini($nim, date('Y-m-d'));

        // Ambil presensi minggu ini
        $presensi_minggu = $this->db
            ->where('nim', trim($nim))
            ->where('tanggal >=', $minggu['mulai'])
            ->where('tanggal <=', $minggu['selesai'])
            ->get('presensi')
            ->result_array();

        // Hitung alpha minggu ini
        $alpha = $this->Presensi_model->hitung_alpha($nim, $minggu['mulai'], $minggu['selesai']);

        // Ambil kartu minggu lalu & hitung kartu minggu ini
        $kartu_lalu = $this->Presensi_model->get_kartu_minggu_sebelumnya($nim, $minggu['mulai']);
        $kartu = $this->Presensi_model->hitung_kartu(
            $alpha['alpha_jamaah'],
            $alpha['alpha_ngaji'],
            $kartu_lalu ? $kartu_lalu['kartu_jamaah'] : 'putih',
            $kartu_lalu ? $kartu_lalu['kartu_ngaji']  : 'putih'
        );

        // Ambil riwayat kartu (5 minggu terakhir)
        $riwayat_kartu = $this->Presensi_model->get_rekap_kartu($nim, 5);

        $data = array(
            'page_title'   => 'Kehadiran Saya',
            'content_view' => 'user/presensi_content',
            'content_data' => array(
                'profile'               => $profile,
                'minggu'                => $minggu,
                'jadwal_aktif_sekarang' => $jadwal_aktif_sekarang,
                'presensi_hari_ini'     => $presensi_hari_ini,
                'presensi_minggu'       => $presensi_minggu,
                'alpha'                 => $alpha,
                'kartu'                 => $kartu,
                'kartu_lalu'            => $kartu_lalu,
                'riwayat_kartu'         => $riwayat_kartu,
                'kegiatan_list'         => Presensi_model::KEGIATAN_LIST,
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    // -------------------------------------------------------------------------
    // Self-checkin presensi hadir
    // -------------------------------------------------------------------------
    public function checkin()
    {
        $this->require_role(array('user'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth     = $this->current_user();
        $nim      = $auth['nim'];
        $kegiatan = trim((string) $this->input->post('kegiatan', TRUE));

        // Validasi kegiatan
        if (!in_array($kegiatan, array_keys(Presensi_model::KEGIATAN_LIST), TRUE)) {
            $this->session->set_flashdata('error', 'Kegiatan tidak valid.');
            redirect('user/presensi');
            return;
        }

        $result = $this->Presensi_model->presensi_hadir($nim, $kegiatan, date('Y-m-d'));

        if ($result['ok']) {
            $this->session->set_flashdata('success', 'Kehadiran ' . Presensi_model::get_label_kegiatan($kegiatan) . ' berhasil dicatat.');
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('user/presensi');
    }

    // -------------------------------------------------------------------------
    // History presensi detail santri
    // -------------------------------------------------------------------------
    public function history()
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();
        $nim  = $auth['nim'];

        // Get profile santri
        $profile = $this->User_model->get_profile_by_nim($nim);

        // Get minggu parameter
        $minggu_param = trim((string) $this->input->get('minggu', TRUE));
        $minggu       = $minggu_param !== '' && $this->is_valid_date($minggu_param)
            ? Presensi_model::get_minggu_aktif($minggu_param)
            : Presensi_model::get_minggu_aktif();

        // Get detail presensi minggu ini
        $detail = $this->Presensi_model->get_detail_presensi_minggu($nim, $minggu['mulai'], $minggu['selesai']);

        // Hitung alpha
        $alpha = $this->Presensi_model->hitung_alpha($nim, $minggu['mulai'], $minggu['selesai']);

        // Ambil kartu minggu lalu
        $kartu_lalu = $this->Presensi_model->get_kartu_minggu_sebelumnya($nim, $minggu['mulai']);

        // Hitung kartu minggu ini
        $kartu = $this->Presensi_model->hitung_kartu(
            $alpha['alpha_jamaah'],
            $alpha['alpha_ngaji'],
            $kartu_lalu ? $kartu_lalu['kartu_jamaah'] : 'putih',
            $kartu_lalu ? $kartu_lalu['kartu_ngaji']  : 'putih'
        );

        // Ambil riwayat kartu (10 minggu)
        $riwayat_kartu = $this->Presensi_model->get_rekap_kartu($nim, 10);

        $data = array(
            'page_title'   => 'Riwayat Kehadiran',
            'content_view' => 'user/presensi_history',
            'content_data' => array(
                'profile'       => $profile,
                'minggu'        => $minggu,
                'minggu_param'  => $minggu_param,
                'detail'        => $detail,
                'alpha'         => $alpha,
                'kartu'         => $kartu,
                'kartu_lalu'    => $kartu_lalu,
                'riwayat_kartu' => $riwayat_kartu,
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    // -------------------------------------------------------------------------
    // Export detail kehadiran santri ke CSV
    // -------------------------------------------------------------------------
    public function export_detail()
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();
        $nim  = $auth['nim'];

        $minggu_param = trim((string) $this->input->get('minggu', TRUE));
        $minggu       = $minggu_param !== '' && $this->is_valid_date($minggu_param)
            ? Presensi_model::get_minggu_aktif($minggu_param)
            : Presensi_model::get_minggu_aktif();

        $detail     = $this->Presensi_model->get_detail_presensi_minggu($nim, $minggu['mulai'], $minggu['selesai']);
        $alpha      = $this->Presensi_model->hitung_alpha($nim, $minggu['mulai'], $minggu['selesai']);
        $kartu_lalu = $this->Presensi_model->get_kartu_minggu_sebelumnya($nim, $minggu['mulai']);
        $kartu      = $this->Presensi_model->hitung_kartu(
            $alpha['alpha_jamaah'],
            $alpha['alpha_ngaji'],
            $kartu_lalu ? $kartu_lalu['kartu_jamaah'] : 'putih',
            $kartu_lalu ? $kartu_lalu['kartu_ngaji']  : 'putih'
        );

        $profile = $this->User_model->get_profile_by_nim($nim);

        @ini_set('memory_limit', '256M');
        @set_time_limit(60);

        $filename = 'kehadiran_' . $nim . '_' . $minggu['mulai'] . '_sd_' . $minggu['selesai'] . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, array('Laporan Kehadiran Santri'), ';');
        fputcsv($output, array('NIM',    $nim), ';');
        fputcsv($output, array('Nama',   isset($profile['nama'])  ? $profile['nama']  : '-'), ';');
        fputcsv($output, array('Kamar',  isset($profile['kamar']) ? $profile['kamar'] : '-'), ';');
        fputcsv($output, array('Periode', date('d M Y', strtotime($minggu['mulai'])) . ' - ' . date('d M Y', strtotime($minggu['selesai']))), ';');
        fputcsv($output, array(), ';');

        fputcsv($output, array('Ringkasan'), ';');
        fputcsv($output, array('Alpha Jamaah', (int) $alpha['alpha_jamaah']), ';');
        fputcsv($output, array('Alpha Ngaji',  (int) $alpha['alpha_ngaji']), ';');
        fputcsv($output, array('Kartu Jamaah', ucfirst($kartu['kartu_jamaah'])), ';');
        fputcsv($output, array('Kartu Ngaji',  ucfirst($kartu['kartu_ngaji'])), ';');
        fputcsv($output, array(), ';');

        fputcsv($output, array('Tanggal', 'Jamaah Maghrib', 'Jamaah Isya', 'Jamaah Subuh', 'Ngaji Maghrib', 'Ngaji Subuh'), ';');

        foreach ($detail as $day) {
            $cols = array(date('d M Y (l)', strtotime($day['tanggal'])));
            foreach (array('jamaah_maghrib', 'jamaah_isya', 'jamaah_subuh', 'ngaji_maghrib', 'ngaji_subuh') as $k) {
                $s = isset($day[$k]) ? $day[$k] : '-';
                $cols[] = ($s === 'hadir') ? 'Hadir' : (($s === 'izin') ? 'Izin' : (($s === 'alpha') ? 'Alpha' : '-'));
            }
            fputcsv($output, $cols, ';');
        }

        fclose($output);
        exit;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------
    private function is_valid_date($date)
    {
        if (empty($date)) return FALSE;
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
