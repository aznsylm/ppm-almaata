<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kehadiran extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Presensi_model');
    }

    // -------------------------------------------------------------------------
    // Halaman utama: rekap mingguan semua santri
    // -------------------------------------------------------------------------
    public function index()
    {
        $this->require_role(array('admin'));

        $minggu_param = trim((string) $this->input->get('minggu', TRUE));
        $minggu       = $minggu_param !== '' && $this->is_valid_date($minggu_param)
            ? Presensi_model::get_minggu_aktif($minggu_param)
            : Presensi_model::get_minggu_aktif();

        $filters = array(
            'kartu_jamaah' => trim((string) $this->input->get('kartu_jamaah', TRUE)),
            'kartu_ngaji'  => trim((string) $this->input->get('kartu_ngaji', TRUE)),
            'kamar'        => trim((string) $this->input->get('kamar', TRUE)),
        );

        $per_page = 20;
        $page     = max(1, (int) $this->input->get('page', TRUE));

        $rows    = $this->Presensi_model->get_rekap_admin($minggu['mulai'], $minggu['selesai'], $filters);
        $preview = array();

        if (empty($rows)) {
            $preview = $this->Presensi_model->preview_rekap_minggu($minggu['mulai'], $minggu['selesai'], null, $filters);
        }

        // Gabung rows + preview, lalu paginate manual
        $source     = !empty($rows) ? $rows : $preview;
        $total      = count($source);
        $total_pages = max(1, (int) ceil($total / $per_page));
        $page       = min($page, $total_pages);
        $offset     = ($page - 1) * $per_page;
        $paged      = array_slice($source, $offset, $per_page);

        $analytics = $this->get_dashboard_analytics($minggu['mulai'], $minggu['selesai']);

        $data = array(
            'page_title'   => 'Kehadiran Santri',
            'content_view' => 'admin/kehadiran_content',
            'content_data' => array(
                'minggu'       => $minggu,
                'minggu_param' => $minggu_param,
                'rows'         => !empty($rows) ? $paged : array(),
                'preview'      => empty($rows)  ? $paged : array(),
                'filters'      => $filters,
                'jadwal_list'  => $this->Presensi_model->get_all_jadwal(),
                'kamar_list'   => $this->get_kamar_list(),
                'analytics'    => $analytics,
                'pagination'   => array(
                    'page'        => $page,
                    'per_page'    => $per_page,
                    'total'       => $total,
                    'total_pages' => $total_pages,
                ),
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    // -------------------------------------------------------------------------
    // Detail presensi satu santri dalam satu minggu
    // -------------------------------------------------------------------------
    public function detail($nim = null)
    {
        $this->require_role(array('admin'));

        $nim          = trim((string) $nim);
        $minggu_param = trim((string) $this->input->get('minggu', TRUE));
        $minggu       = $minggu_param !== '' && $this->is_valid_date($minggu_param)
            ? Presensi_model::get_minggu_aktif($minggu_param)
            : Presensi_model::get_minggu_aktif();

        if ($nim === '') {
            show_404();
            return;
        }

        $detail  = $this->Presensi_model->get_detail_presensi_minggu($nim, $minggu['mulai'], $minggu['selesai']);
        $alpha   = $this->Presensi_model->hitung_alpha($nim, $minggu['mulai'], $minggu['selesai']);
        $kartu_lalu = $this->Presensi_model->get_kartu_minggu_sebelumnya($nim, $minggu['mulai']);
        $kartu   = $this->Presensi_model->hitung_kartu(
            $alpha['alpha_jamaah'],
            $alpha['alpha_ngaji'],
            $kartu_lalu ? $kartu_lalu['kartu_jamaah'] : 'putih',
            $kartu_lalu ? $kartu_lalu['kartu_ngaji']  : 'putih'
        );

        // Ambil nama santri dari users (konsisten dengan master data)
        $santri = $this->db
            ->select('TRIM(u.nim) AS nim, m.NMMHSMSMHS AS nama, TRIM(s.kmr) AS kamar', FALSE)
            ->from('users u')
            ->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = u.nim', 'left')
            ->join('mssantri s', 'TRIM(s.nim) = u.nim', 'left')
            ->where('u.role', 'user')
            ->where('u.nim', $nim)
            ->get()->row_array();

        $data = array(
            'page_title'   => 'Detail Kehadiran',
            'content_view' => 'admin/kehadiran_detail',
            'content_data' => array(
                'nim'         => $nim,
                'santri'      => $santri,
                'minggu'      => $minggu,
                'minggu_param'=> $minggu_param,
                'detail'      => $detail,
                'alpha'       => $alpha,
                'kartu'       => $kartu,
                'kartu_lalu'  => $kartu_lalu,
                'riwayat_kartu' => $this->Presensi_model->get_rekap_kartu($nim, 8),
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    // -------------------------------------------------------------------------
    // Export rekap kehadiran mingguan ke Excel
    // -------------------------------------------------------------------------
    public function export_rekap()
    {
        $this->require_role(array('admin'));

        $minggu_param = trim((string) $this->input->get('minggu', TRUE));
        $minggu = $minggu_param !== '' && $this->is_valid_date($minggu_param)
            ? Presensi_model::get_minggu_aktif($minggu_param)
            : Presensi_model::get_minggu_aktif();

        $preview = $this->Presensi_model->preview_rekap_minggu($minggu['mulai'], $minggu['selesai']);

        if (empty($preview)) {
            $this->session->set_flashdata('error', 'Tidak ada data santri.');
            redirect('admin/kehadiran');
            return;
        }

        @ini_set('memory_limit', '256M');
        @set_time_limit(120);

        $filename = 'rekap_kehadiran_' . $minggu['mulai'] . '_sd_' . $minggu['selesai'] . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");

        // Header info
        fputcsv($output, array('Rekap Kehadiran Mingguan'), ';');
        fputcsv($output, array('Periode', date('d M Y', strtotime($minggu['mulai'])) . ' - ' . date('d M Y', strtotime($minggu['selesai']))), ';');
        fputcsv($output, array('Total Santri', count($preview)), ';');
        fputcsv($output, array(), ';');

        // Header kolom
        fputcsv($output, array('NIM', 'Nama', 'Kamar', 'Alpha Jamaah', 'Kartu Jamaah', 'Alpha Ngaji', 'Kartu Ngaji'), ';');

        // Data
        foreach ($preview as $row) {
            fputcsv($output, array(
                $row['nim'],
                $row['nama'] ?? 'N/A',
                $row['kamar'] ?? '-',
                (int) $row['alpha_jamaah'],
                ucfirst($row['kartu_jamaah']),
                (int) $row['alpha_ngaji'],
                ucfirst($row['kartu_ngaji']),
            ), ';');
        }

        fclose($output);
        exit;
    }

    // -------------------------------------------------------------------------
    // Export detail kehadiran santri ke Excel
    // -------------------------------------------------------------------------
    public function export_detail($nim = null)
    {
        $this->require_role(array('admin'));

        $nim = trim((string) $nim);
        $minggu_param = trim((string) $this->input->get('minggu', TRUE));
        $minggu = $minggu_param !== '' && $this->is_valid_date($minggu_param)
            ? Presensi_model::get_minggu_aktif($minggu_param)
            : Presensi_model::get_minggu_aktif();

        if ($nim === '') {
            show_404();
            return;
        }

        $detail     = $this->Presensi_model->get_detail_presensi_minggu($nim, $minggu['mulai'], $minggu['selesai']);
        $alpha      = $this->Presensi_model->hitung_alpha($nim, $minggu['mulai'], $minggu['selesai']);
        $kartu_lalu = $this->Presensi_model->get_kartu_minggu_sebelumnya($nim, $minggu['mulai']);
        $kartu      = $this->Presensi_model->hitung_kartu(
            $alpha['alpha_jamaah'],
            $alpha['alpha_ngaji'],
            $kartu_lalu ? $kartu_lalu['kartu_jamaah'] : 'putih',
            $kartu_lalu ? $kartu_lalu['kartu_ngaji']  : 'putih'
        );

        $santri = $this->db
            ->select('TRIM(m.NIMHSMSMHS) AS nim, m.NMMHSMSMHS AS nama, s.kmr AS kamar', FALSE)
            ->from('sim_akademik.msmhs m')
            ->join('mssantri s', 'TRIM(s.nim) = TRIM(m.NIMHSMSMHS)', 'left')
            ->where('TRIM(m.NIMHSMSMHS)', $nim)
            ->get()->row_array();

        @ini_set('memory_limit', '256M');
        @set_time_limit(120);

        $filename = 'kehadiran_' . $nim . '_' . $minggu['mulai'] . '_sd_' . $minggu['selesai'] . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");

        // Info santri
        fputcsv($output, array('Laporan Kehadiran Santri'), ';');
        fputcsv($output, array('NIM',    $santri['nim']  ?? $nim), ';');
        fputcsv($output, array('Nama',   $santri['nama'] ?? 'N/A'), ';');
        fputcsv($output, array('Kamar',  $santri['kamar'] ?? '-'), ';');
        fputcsv($output, array('Periode', date('d M Y', strtotime($minggu['mulai'])) . ' - ' . date('d M Y', strtotime($minggu['selesai']))), ';');
        fputcsv($output, array(), ';');

        // Ringkasan
        fputcsv($output, array('Ringkasan'), ';');
        fputcsv($output, array('Alpha Jamaah', (int) $alpha['alpha_jamaah']), ';');
        fputcsv($output, array('Alpha Ngaji',  (int) $alpha['alpha_ngaji']), ';');
        fputcsv($output, array('Kartu Jamaah', ucfirst($kartu['kartu_jamaah'])), ';');
        fputcsv($output, array('Kartu Ngaji',  ucfirst($kartu['kartu_ngaji'])), ';');
        fputcsv($output, array(), ';');

        // Header detail
        fputcsv($output, array('Tanggal', 'Jamaah Maghrib', 'Jamaah Isya', 'Jamaah Subuh', 'Ngaji Maghrib', 'Ngaji Subuh'), ';');

        // Data detail harian
        foreach ($detail as $day) {
            $cols = array(date('d M Y (l)', strtotime($day['tanggal'])));
            foreach (array('jamaah_maghrib', 'jamaah_isya', 'jamaah_subuh', 'ngaji_maghrib', 'ngaji_subuh') as $kegiatan) {
                $s = isset($day[$kegiatan]) ? $day[$kegiatan] : '-';
                $cols[] = ($s === 'hadir') ? 'Hadir' : (($s === 'izin') ? 'Izin' : (($s === 'alpha') ? 'Alpha' : '-'));
            }
            fputcsv($output, $cols, ';');
        }

        fclose($output);
        exit;
    }
    public function bulk_update()
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth = $this->current_user();
        $nim = trim((string) $this->input->post('nim', TRUE));
        $minggu_mulai = trim((string) $this->input->post('minggu_mulai', TRUE));
        $minggu_selesai = trim((string) $this->input->post('minggu_selesai', TRUE));
        $minggu_param = trim((string) $this->input->post('minggu_param', TRUE)); // Tambah ini
        $status_data = $this->input->post('status', TRUE);

        if (empty($status_data) || !is_array($status_data)) {
            $this->session->set_flashdata('error', 'Data tidak valid.');
            redirect('admin/kehadiran/detail/' . $nim . '?minggu=' . ($minggu_param ?: $minggu_mulai));
            return;
        }

        $success_count = 0;
        $error_count = 0;
        $errors = array();

        // Process all dates in status_data
        foreach ($status_data as $tanggal => $kegiatan_statuses) {
            if (!is_array($kegiatan_statuses)) continue;
            
            foreach ($kegiatan_statuses as $kegiatan => $status) {
                // Get existing record
                $existing = $this->Presensi_model->get_presensi($nim, $kegiatan, $tanggal);
                
                if (empty($status) || $status === 'alpha' || $status === '-') {
                    // Delete record if alpha, empty, or dash
                    if ($existing) {
                        $this->db
                            ->where('nim', $nim)
                            ->where('kegiatan', $kegiatan)
                            ->where('tanggal', $tanggal)
                            ->delete('presensi');
                        $success_count++;
                    }
                } else {
                    // Insert or update record for hadir/izin
                    if ($existing) {
                        // Update existing
                        $ok = $this->Presensi_model->admin_edit_presensi($existing['id'], $status, $auth['nim']);
                        if ($ok) {
                            $success_count++;
                        } else {
                            $error_count++;
                            $errors[] = "Gagal update {$kegiatan} tanggal {$tanggal}";
                        }
                    } else {
                        // Insert new
                        $result = $this->Presensi_model->admin_tambah_presensi($nim, $kegiatan, $tanggal, $status, $auth['nim']);
                        if ($result['ok']) {
                            $success_count++;
                        } else {
                            $error_count++;
                            $errors[] = "Gagal insert {$kegiatan} tanggal {$tanggal}: {$result['message']}";
                        }
                    }
                }
            }
        }

        // Set flash message
        if ($success_count > 0 && $error_count === 0) {
            $this->session->set_flashdata('success', "Berhasil mengupdate {$success_count} record kehadiran.");
        } elseif ($success_count > 0 && $error_count > 0) {
            $message = "Berhasil: {$success_count} record. Gagal: {$error_count} record.";
            if (count($errors) <= 5) {
                $message .= "<br><small>" . implode('<br>', $errors) . "</small>";
            }
            $this->session->set_flashdata('warning', $message);
        } else {
            $this->session->set_flashdata('error', 'Tidak ada perubahan atau semua update gagal.');
        }

        // Redirect dengan parameter minggu yang benar
        redirect('admin/kehadiran/detail/' . $nim . '?minggu=' . ($minggu_param ?: $minggu_mulai));
    }
    // -------------------------------------------------------------------------
    // Kelola jadwal presensi
    // -------------------------------------------------------------------------
    public function jadwal()
    {
        $this->require_role(array('admin'));

        $data = array(
            'page_title'   => 'Kelola Jadwal Presensi',
            'content_view' => 'admin/kehadiran_jadwal',
            'content_data' => array(
                'jadwal_list' => $this->Presensi_model->get_all_jadwal(),
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    public function update_jadwal()
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth    = $this->current_user();
        $jadwal  = $this->input->post('jadwal', TRUE);

        if (!is_array($jadwal)) {
            $this->session->set_flashdata('error', 'Data jadwal tidak valid.');
            redirect('admin/kehadiran/jadwal');
            return;
        }

        $errors = 0;
        foreach ($jadwal as $id => $item) {
            $jam_mulai   = trim((string) (isset($item['jam_mulai']) ? $item['jam_mulai'] : ''));
            $jam_selesai = trim((string) (isset($item['jam_selesai']) ? $item['jam_selesai'] : ''));
            $is_active   = isset($item['is_active']) ? 1 : 0;

            if (!$this->is_valid_time($jam_mulai) || !$this->is_valid_time($jam_selesai)) {
                $errors++;
                continue;
            }

            $this->Presensi_model->update_jadwal((int) $id, $jam_mulai, $jam_selesai, $is_active, $auth['nim']);
        }

        if ($errors > 0) {
            $this->session->set_flashdata('error', 'Beberapa jadwal gagal disimpan karena format waktu tidak valid.');
        } else {
            $this->session->set_flashdata('success', 'Jadwal presensi berhasil diperbarui.');
        }

        redirect('admin/kehadiran/jadwal');
    }

    // -------------------------------------------------------------------------
    // Presensi manual admin
    // -------------------------------------------------------------------------
    public function manual()
    {
        $this->require_role(array('admin'));

        // Ambil riwayat presensi manual 10 terbaru
        $riwayat = $this->db
            ->select('p.*, m.NMMHSMSMHS AS nama_santri')
            ->from('presensi p')
            ->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = p.nim', 'left')
            ->where('p.created_by IS NOT NULL', NULL, FALSE)
            ->order_by('p.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->result_array();

        $data = array(
            'page_title'   => 'Kehadiran Manual',
            'content_view' => 'admin/kehadiran_manual',
            'content_data' => array(
                'kegiatan_list' => Presensi_model::KEGIATAN_LIST,
                'santri_list'   => $this->get_santri_list(),
                'kamar_list'    => $this->get_kamar_list(),
                'riwayat'       => $riwayat,
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    public function manual_store()
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth     = $this->current_user();
        $nim      = trim((string) $this->input->post('nim', TRUE));
        $kegiatan = trim((string) $this->input->post('kegiatan', TRUE));
        $tanggal  = trim((string) $this->input->post('tanggal', TRUE));
        $status   = trim((string) $this->input->post('status', TRUE));

        if (!in_array($kegiatan, array_keys(Presensi_model::KEGIATAN_LIST), TRUE)
            || !in_array($status, array('hadir', 'izin'), TRUE)
            || !$this->is_valid_date($tanggal)
            || $nim === ''
            || $tanggal > date('Y-m-d') // Tidak boleh tanggal masa depan
        ) {
            $this->session->set_flashdata('error', 'Data tidak valid atau tanggal tidak boleh masa depan.');
            redirect('admin/kehadiran/manual');
            return;
        }

        $result = $this->Presensi_model->admin_tambah_presensi($nim, $kegiatan, $tanggal, $status, $auth['nim']);
        $this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['ok'] ? 'Presensi berhasil ditambahkan.' : $result['message']);
        redirect('admin/kehadiran/manual');
    }

    public function manual_batch()
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth       = $this->current_user();
        $nim_list   = $this->input->post('nim_list', TRUE);
        $kegiatan   = trim((string) $this->input->post('kegiatan', TRUE));
        $tanggal    = trim((string) $this->input->post('tanggal', TRUE));
        $status     = trim((string) $this->input->post('status', TRUE));

        // Validasi input
        if (!is_array($nim_list) || empty($nim_list)
            || !in_array($kegiatan, array_keys(Presensi_model::KEGIATAN_LIST), TRUE)
            || !in_array($status, array('hadir', 'izin'), TRUE)
            || !$this->is_valid_date($tanggal)
            || $tanggal > date('Y-m-d') // Tidak boleh tanggal masa depan
        ) {
            $this->session->set_flashdata('error', 'Data tidak valid, tidak ada santri yang dipilih, atau tanggal tidak boleh masa depan.');
            redirect('admin/kehadiran/manual');
            return;
        }

        $success_count = 0;
        $error_count = 0;
        $errors = array();

        foreach ($nim_list as $nim) {
            $nim = trim((string) $nim);
            if ($nim === '') continue;

            $result = $this->Presensi_model->admin_tambah_presensi($nim, $kegiatan, $tanggal, $status, $auth['nim']);
            
            if ($result['ok']) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "NIM {$nim}: {$result['message']}";
            }
        }

        // Set flash message
        if ($success_count > 0 && $error_count === 0) {
            $this->session->set_flashdata('success', "Berhasil menambahkan presensi untuk {$success_count} santri.");
        } elseif ($success_count > 0 && $error_count > 0) {
            $message = "Berhasil: {$success_count} santri. Gagal: {$error_count} santri.";
            if (count($errors) <= 5) {
                $message .= "<br><small>" . implode('<br>', $errors) . "</small>";
            }
            $this->session->set_flashdata('warning', $message);
        } else {
            $message = "Semua gagal ({$error_count} santri).";
            if (count($errors) <= 5) {
                $message .= "<br><small>" . implode('<br>', $errors) . "</small>";
            }
            $this->session->set_flashdata('error', $message);
        }

        redirect('admin/kehadiran/manual');
    }

    public function manual_edit($id = null)
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth   = $this->current_user();
        $status = trim((string) $this->input->post('status', TRUE));

        if (!in_array($status, array('hadir', 'izin'), TRUE)) {
            $this->session->set_flashdata('error', 'Status tidak valid.');
            redirect('admin/kehadiran/manual');
            return;
        }

        $ok = $this->Presensi_model->admin_edit_presensi((int) $id, $status, $auth['nim']);
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Presensi berhasil diubah.' : 'Gagal mengubah presensi.');
        redirect('admin/kehadiran/manual');
    }

    public function manual_hapus($id = null)
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $ok = $this->Presensi_model->admin_hapus_presensi((int) $id);
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Presensi berhasil dihapus.' : 'Gagal menghapus presensi.');
        redirect('admin/kehadiran/manual');
    }

    // -------------------------------------------------------------------------
    // Dashboard Analytics
    // -------------------------------------------------------------------------
    private function get_dashboard_analytics($minggu_mulai, $minggu_selesai)
    {
        $today = date('Y-m-d');
        
        // Total santri aktif
        $total_santri = $this->db
            ->select('COUNT(DISTINCT TRIM(nim)) as total', FALSE)
            ->from('mssantri')
            ->where('TRIM(nim) !=', '')
            ->get()->row_array()['total'];

        // Kehadiran hari ini
        $hadir_hari_ini = $this->db
            ->select('COUNT(DISTINCT nim) as total')
            ->from('presensi')
            ->where('tanggal', $today)
            ->where('status', 'hadir')
            ->get()->row_array()['total'];

        $izin_hari_ini = $this->db
            ->select('COUNT(DISTINCT nim) as total')
            ->from('presensi')
            ->where('tanggal', $today)
            ->where('status', 'izin')
            ->get()->row_array()['total'];

        // Santri dengan kartu merah/hitam
        $kartu_merah_hitam = $this->db
            ->select('COUNT(*) as total')
            ->from('presensi_kartu')
            ->where('minggu_mulai', $minggu_mulai)
            ->where('is_final', 1)
            ->group_start()
                ->where('kartu_jamaah', 'merah')
                ->or_where('kartu_jamaah', 'hitam')
                ->or_where('kartu_ngaji', 'merah')
            ->group_end()
            ->get()->row_array()['total'] ?? 0;

        // Progress finalisasi
        $sudah_finalisasi = $this->db
            ->select('COUNT(*) as total')
            ->from('presensi_kartu')
            ->where('minggu_mulai', $minggu_mulai)
            ->where('is_final', 1)
            ->get()->row_array()['total'] ?? 0;

        return array(
            'total_santri' => (int)$total_santri,
            'hadir_hari_ini' => (int)$hadir_hari_ini,
            'izin_hari_ini' => (int)$izin_hari_ini,
            'alpha_hari_ini' => (int)$total_santri - (int)$hadir_hari_ini - (int)$izin_hari_ini,
            'kartu_merah_hitam' => (int)$kartu_merah_hitam,
            'progress_finalisasi' => array(
                'sudah' => (int)$sudah_finalisasi,
                'total' => (int)$total_santri,
                'persen' => $total_santri > 0 ? round(($sudah_finalisasi / $total_santri) * 100) : 0
            )
        );
    }
    private function get_kamar_list()
    {
        $rows = $this->db
            ->select('DISTINCT TRIM(s.kmr) AS kamar', FALSE)
            ->from('users u')
            ->join('mssantri s', 'TRIM(s.nim) = u.nim', 'inner')
            ->where('u.role', 'user')
            ->where('s.kmr IS NOT NULL', NULL, FALSE)
            ->where('TRIM(s.kmr) !=', '')
            ->order_by('kamar', 'ASC')
            ->get()->result_array();
        return array_column($rows, 'kamar');
    }

    private function get_santri_list()
    {
        return $this->db
            ->select('TRIM(u.nim) AS nim, m.NMMHSMSMHS AS nama, TRIM(s.kmr) AS kamar', FALSE)
            ->from('users u')
            ->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = u.nim', 'left')
            ->join('mssantri s', 'TRIM(s.nim) = u.nim', 'left')
            ->where('u.role', 'user')
            ->order_by('m.NMMHSMSMHS', 'ASC')
            ->get()->result_array();
    }

    private function is_valid_date($date)
    {
        if (empty($date)) return FALSE;
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function is_valid_time($time)
    {
        return (bool) preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time);
    }
}
