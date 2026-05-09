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

        $rows    = $this->Presensi_model->get_rekap_admin($minggu['mulai'], $minggu['selesai'], $filters);
        $preview = array();

        // Jika belum ada rekap final untuk minggu ini, tampilkan preview
        if (empty($rows)) {
            $preview = $this->Presensi_model->preview_rekap_minggu($minggu['mulai'], $minggu['selesai']);
        }

        // Dashboard Analytics
        $analytics = $this->get_dashboard_analytics($minggu['mulai'], $minggu['selesai']);

        $data = array(
            'page_title'   => 'Kehadiran Santri',
            'content_view' => 'admin/kehadiran_content',
            'content_data' => array(
                'minggu'       => $minggu,
                'minggu_param' => $minggu_param,
                'rows'         => $rows,
                'preview'      => $preview,
                'filters'      => $filters,
                'jadwal_list'  => $this->Presensi_model->get_all_jadwal(),
                'kamar_list'   => $this->get_kamar_list(),
                'analytics'    => $analytics,
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

        // Ambil nama santri
        $santri = $this->db
            ->select('TRIM(m.NIMHSMSMHS) AS nim, m.NMMHSMSMHS AS nama, s.kmr AS kamar', FALSE)
            ->from('sim_akademik.msmhs m')
            ->join('mssantri s', 'TRIM(s.nim) = TRIM(m.NIMHSMSMHS)', 'left')
            ->where('TRIM(m.NIMHSMSMHS)', $nim)
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

        // Ambil data (finalisasi atau preview)
        $rows = $this->Presensi_model->get_rekap_admin($minggu['mulai'], $minggu['selesai'], array());
        
        if (empty($rows)) {
            // Jika belum difinalisasi, gunakan preview
            $preview = $this->Presensi_model->preview_rekap_minggu($minggu['mulai'], $minggu['selesai']);
            $dataSource = $preview;
            $status = 'Preview (Belum Difinalisasi)';
        } else {
            $dataSource = $rows;
            $status = 'Sudah Difinalisasi';
        }

        // Generate Excel
        $this->generate_excel_rekap($dataSource, $minggu, $status);
    }

    private function generate_excel_rekap($data, $minggu, $status)
    {
        $filename = 'Rekap_Kehadiran_' . date('Y-m-d', strtotime($minggu['mulai'])) . '.xls';
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "<html xmlns:x='urn:schemas-microsoft-com:office:excel'>";
        echo "<head>";
        echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
        echo "<style>";
        echo "table { border-collapse: collapse; }";
        echo "th, td { border: 1px solid #000; padding: 5px; }";
        echo "th { background-color: #4CAF50; color: white; font-weight: bold; }";
        echo ".header { font-size: 16px; font-weight: bold; }";
        echo "</style>";
        echo "</head>";
        echo "<body>";
        
        // Header
        echo "<table>";
        echo "<tr><td colspan='7' class='header'>REKAP KEHADIRAN MINGGUAN</td></tr>";
        echo "<tr><td colspan='7'>&nbsp;</td></tr>";
        echo "<tr><td><strong>Periode</strong></td><td colspan='6'>" . date('d M Y', strtotime($minggu['mulai'])) . " - " . date('d M Y', strtotime($minggu['selesai'])) . "</td></tr>";
        echo "<tr><td><strong>Status</strong></td><td colspan='6'>" . htmlspecialchars($status) . "</td></tr>";
        echo "<tr><td><strong>Total Santri</strong></td><td colspan='6'>" . count($data) . "</td></tr>";
        echo "<tr><td colspan='7'>&nbsp;</td></tr>";
        
        // Table header
        echo "<tr>";
        echo "<th>NIM</th>";
        echo "<th>Nama</th>";
        echo "<th>Kamar</th>";
        echo "<th>Alpha Jamaah</th>";
        echo "<th>Kartu Jamaah</th>";
        echo "<th>Alpha Ngaji</th>";
        echo "<th>Kartu Ngaji</th>";
        echo "</tr>";
        
        // Data rows
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['nim']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nama'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['kamar'] ?? '-') . "</td>";
            echo "<td style='text-align:center;'>" . (int)$row['alpha_jamaah'] . "</td>";
            echo "<td style='text-align:center;'>" . htmlspecialchars(ucfirst($row['kartu_jamaah'])) . "</td>";
            echo "<td style='text-align:center;'>" . (int)$row['alpha_ngaji'] . "</td>";
            echo "<td style='text-align:center;'>" . htmlspecialchars(ucfirst($row['kartu_ngaji'])) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</body>";
        echo "</html>";
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

        // Get data
        $detail = $this->Presensi_model->get_detail_presensi_minggu($nim, $minggu['mulai'], $minggu['selesai']);
        $alpha = $this->Presensi_model->hitung_alpha($nim, $minggu['mulai'], $minggu['selesai']);
        $kartu_lalu = $this->Presensi_model->get_kartu_minggu_sebelumnya($nim, $minggu['mulai']);
        $kartu = $this->Presensi_model->hitung_kartu(
            $alpha['alpha_jamaah'],
            $alpha['alpha_ngaji'],
            $kartu_lalu ? $kartu_lalu['kartu_jamaah'] : 'putih',
            $kartu_lalu ? $kartu_lalu['kartu_ngaji'] : 'putih'
        );

        // Get santri info
        $santri = $this->db
            ->select('TRIM(m.NIMHSMSMHS) AS nim, m.NMMHSMSMHS AS nama, s.kmr AS kamar', FALSE)
            ->from('sim_akademik.msmhs m')
            ->join('mssantri s', 'TRIM(s.nim) = TRIM(m.NIMHSMSMHS)', 'left')
            ->where('TRIM(m.NIMHSMSMHS)', $nim)
            ->get()->row_array();

        // Generate Excel
        $this->generate_excel_detail($santri, $detail, $alpha, $kartu, $minggu);
    }

    private function generate_excel_detail($santri, $detail, $alpha, $kartu, $minggu)
    {
        $filename = 'Kehadiran_' . $santri['nim'] . '_' . date('Y-m-d', strtotime($minggu['mulai'])) . '.xls';
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "<html xmlns:x='urn:schemas-microsoft-com:office:excel'>";
        echo "<head>";
        echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
        echo "<style>";
        echo "table { border-collapse: collapse; }";
        echo "th, td { border: 1px solid #000; padding: 5px; }";
        echo "th { background-color: #4CAF50; color: white; font-weight: bold; }";
        echo ".header { font-size: 16px; font-weight: bold; }";
        echo "</style>";
        echo "</head>";
        echo "<body>";
        
        // Header info
        echo "<table>";
        echo "<tr><td colspan='6' class='header'>LAPORAN KEHADIRAN SANTRI</td></tr>";
        echo "<tr><td colspan='6'>&nbsp;</td></tr>";
        echo "<tr><td><strong>NIM</strong></td><td colspan='5'>" . htmlspecialchars($santri['nim']) . "</td></tr>";
        echo "<tr><td><strong>Nama</strong></td><td colspan='5'>" . htmlspecialchars($santri['nama']) . "</td></tr>";
        echo "<tr><td><strong>Kamar</strong></td><td colspan='5'>" . htmlspecialchars($santri['kamar'] ?? '-') . "</td></tr>";
        echo "<tr><td><strong>Periode</strong></td><td colspan='5'>" . date('d M Y', strtotime($minggu['mulai'])) . " - " . date('d M Y', strtotime($minggu['selesai'])) . "</td></tr>";
        echo "<tr><td colspan='6'>&nbsp;</td></tr>";
        
        // Summary
        echo "<tr><td colspan='6'><strong>RINGKASAN</strong></td></tr>";
        echo "<tr><td><strong>Alpha Jamaah</strong></td><td colspan='5'>" . $alpha['alpha_jamaah'] . "</td></tr>";
        echo "<tr><td><strong>Alpha Ngaji</strong></td><td colspan='5'>" . $alpha['alpha_ngaji'] . "</td></tr>";
        echo "<tr><td><strong>Kartu Jamaah</strong></td><td colspan='5'>" . htmlspecialchars(ucfirst($kartu['kartu_jamaah'])) . "</td></tr>";
        echo "<tr><td><strong>Kartu Ngaji</strong></td><td colspan='5'>" . htmlspecialchars(ucfirst($kartu['kartu_ngaji'])) . "</td></tr>";
        echo "<tr><td colspan='6'>&nbsp;</td></tr>";
        
        // Detail header
        echo "<tr><td colspan='6'><strong>DETAIL KEHADIRAN HARIAN</strong></td></tr>";
        echo "<tr>";
        echo "<th>Tanggal</th>";
        echo "<th>Jamaah Maghrib</th>";
        echo "<th>Jamaah Isya</th>";
        echo "<th>Jamaah Subuh</th>";
        echo "<th>Ngaji Maghrib</th>";
        echo "<th>Ngaji Subuh</th>";
        echo "</tr>";
        
        // Detail data
        foreach ($detail as $day) {
            echo "<tr>";
            echo "<td>" . date('d M Y (l)', strtotime($day['tanggal'])) . "</td>";
            
            foreach (['jamaah_maghrib', 'jamaah_isya', 'jamaah_subuh', 'ngaji_maghrib', 'ngaji_subuh'] as $kegiatan) {
                $status = isset($day[$kegiatan]) ? $day[$kegiatan] : '-';
                $displayStatus = ($status === 'hadir') ? 'Hadir' : (($status === 'izin') ? 'Izin' : (($status === 'alpha') ? 'Alpha' : '-'));
                echo "<td style='text-align:center;'>" . htmlspecialchars($displayStatus) . "</td>";
            }
            
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</body>";
        echo "</html>";
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
    public function refinalisasi()
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth         = $this->current_user();
        $minggu_mulai = trim((string) $this->input->post('minggu_mulai', TRUE));
        $minggu_selesai = trim((string) $this->input->post('minggu_selesai', TRUE));

        if (!$this->is_valid_date($minggu_mulai) || !$this->is_valid_date($minggu_selesai)) {
            $this->session->set_flashdata('error', 'Periode minggu tidak valid.');
            redirect('admin/kehadiran');
            return;
        }

        // Hapus data lama terlebih dahulu
        $this->db->where('minggu_mulai', $minggu_mulai)->delete('presensi_kartu');

        // Finalisasi ulang dengan data terbaru
        $result = $this->Presensi_model->finalisasi_rekap($minggu_mulai, $minggu_selesai, $auth['nim']);

        if ($result['ok']) {
            $this->session->set_flashdata('success', 'Re-finalisasi berhasil! Rekap minggu ' . $minggu_mulai . ' s/d ' . $minggu_selesai . ' telah diperbarui untuk ' . $result['total'] . ' santri.');
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('admin/kehadiran?minggu=' . $minggu_mulai);
    }
    public function finalisasi()
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $auth         = $this->current_user();
        $minggu_mulai = trim((string) $this->input->post('minggu_mulai', TRUE));
        $minggu_selesai = trim((string) $this->input->post('minggu_selesai', TRUE));

        if (!$this->is_valid_date($minggu_mulai) || !$this->is_valid_date($minggu_selesai)) {
            $this->session->set_flashdata('error', 'Periode minggu tidak valid.');
            redirect('admin/kehadiran');
            return;
        }

        $result = $this->Presensi_model->finalisasi_rekap($minggu_mulai, $minggu_selesai, $auth['nim']);

        if ($result['ok']) {
            $this->session->set_flashdata('success', 'Rekap minggu ' . $minggu_mulai . ' s/d ' . $minggu_selesai . ' berhasil difinalisasi untuk ' . $result['total'] . ' santri.');
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('admin/kehadiran?minggu=' . $minggu_mulai);
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
            ->select('DISTINCT TRIM(kmr) AS kamar', FALSE)
            ->from('mssantri')
            ->where('kmr IS NOT NULL', NULL, FALSE)
            ->where('TRIM(kmr) !=', '')
            ->order_by('kamar', 'ASC')
            ->get()->result_array();
        return array_column($rows, 'kamar');
    }

    private function get_santri_list()
    {
        return $this->db
            ->select('TRIM(m.NIMHSMSMHS) AS nim, m.NMMHSMSMHS AS nama, TRIM(s.kmr) AS kamar', FALSE)
            ->from('sim_akademik.msmhs m')
            ->join('mssantri s', 'TRIM(s.nim) = TRIM(m.NIMHSMSMHS)', 'inner')
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
