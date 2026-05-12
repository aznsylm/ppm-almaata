<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perizinan_model extends CI_Model
{
    // -------------------------------------------------------------------------
    // Konstanta Status
    // -------------------------------------------------------------------------
    const STATUS_SIAP_CETAK          = '0';
    const STATUS_MENUNGGU_UPLOAD     = '1';
    const STATUS_MENUNGGU_VALIDASI   = '2';
    const STATUS_DISETUJUI           = '3';
    const STATUS_DITOLAK             = '4';
    const STATUS_MENUNGGU_DOKUMENTASI = '5';

    // -------------------------------------------------------------------------
    // Konstanta Tipe Izin
    // -------------------------------------------------------------------------
    const TIPE_KURANG_DUA_MINGGU = '1';
    const TIPE_LEBIH_DUA_MINGGU  = '2';
    const TIPE_JAMAAH_NGAJI      = '3';

    // -------------------------------------------------------------------------
    // Kategori untuk Tipe 3
    // -------------------------------------------------------------------------
    public static function get_kategori_tipe3()
    {
        return array(
            'jamaah_maghrib' => 'Jamaah Maghrib',
            'jamaah_isya'    => 'Jamaah Isya',
            'jamaah_subuh'   => 'Jamaah Subuh',
            'ngaji_maghrib'  => 'Ngaji Ba\'da Maghrib',
            'ngaji_subuh'    => 'Ngaji Ba\'da Subuh',
        );
    }

    // -------------------------------------------------------------------------
    // Alasan untuk Tipe 3
    // -------------------------------------------------------------------------
    public static function get_alasan_tipe3()
    {
        return array('Sakit', 'Haid', 'Kerkom', 'Rapat', 'Kuliah', 'Pulang');
    }

    // -------------------------------------------------------------------------
    // Helper: cek apakah alasan butuh dokumentasi (Kerkom/Rapat/Kuliah)
    // -------------------------------------------------------------------------
    public function is_perlu_dokumentasi($alasan)
    {
        $alasan = strtolower((string) $alasan);
        foreach (array('kerkom', 'rapat', 'kuliah') as $keyword) {
            if (strpos($alasan, $keyword) !== FALSE) {
                return TRUE;
            }
        }
        return FALSE;
    }

    // -------------------------------------------------------------------------
    // Helper: status map label
    // -------------------------------------------------------------------------
    public static function get_status_map()
    {
        return array(
            '0' => 'Siap Cetak',
            '1' => 'Menunggu Upload',
            '2' => 'Menunggu Validasi',
            '3' => 'Disetujui',
            '4' => 'Ditolak',
            '5' => 'Menunggu Dokumentasi',
        );
    }

    // -------------------------------------------------------------------------
    // Ambil alasan options untuk tipe 1 & 2 dari database
    // -------------------------------------------------------------------------
    public function get_alasan_options()
    {
        $rows = $this->db
            ->select('ket')
            ->from('msketerangan')
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();

        $options = array();
        foreach ($rows as $row) {
            $ket = trim((string) $row['ket']);
            if ($ket !== '') {
                $options[] = $ket;
            }
        }

        if (empty($options)) {
            $options = array(
                'Praktek Rumah Sakit/Puskesmas',
                'PKL di Sekolah',
                'PKL di Masyarakat',
                'Pulang Kampung',
                'Sakit',
            );
        }

        return $options;
    }

    // -------------------------------------------------------------------------
    // List untuk user (riwayat dengan pagination)
    // -------------------------------------------------------------------------
    public function count_for_user_history($nim)
    {
        return (int) $this->db->where('nim', trim($nim))->count_all_results('ijin');
    }

    public function list_for_user_history($nim, $limit = 10, $offset = 0)
    {
        return $this->db
            ->order_by('tgl_ajuan', 'DESC')
            ->order_by('id', 'DESC')
            ->limit((int) $limit, (int) $offset)
            ->get_where('ijin', array('nim' => trim($nim)))
            ->result_array();
    }

    // -------------------------------------------------------------------------
    // Filter options untuk dropdown admin
    // -------------------------------------------------------------------------
    public function get_kamar_list()
    {
        $rows = $this->db
            ->select('DISTINCT TRIM(s.kmr) AS kamar', FALSE)
            ->from('users u')
            ->join('mssantri s', 'TRIM(s.nim) = u.nim', 'inner')
            ->where('u.role', 'user')
            ->where('s.kmr IS NOT NULL', NULL, FALSE)
            ->where('TRIM(s.kmr) !=', '')
            ->order_by('kamar', 'ASC')
            ->get()
            ->result_array();

        return array_column($rows, 'kamar');
    }

    public function get_alasan_list()
    {
        $rows = $this->db
            ->select('ket')
            ->from('msketerangan')
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();

        return array_column($rows, 'ket');
    }

    // -------------------------------------------------------------------------
    // List untuk admin
    // -------------------------------------------------------------------------
    public function list_for_admin(array $filters = array(), $limit = 10, $offset = 0)
    {
        $this->db->select('i.id, i.nim, i.tipe_izin, i.sub_kategori, i.tgl_ajuan, i.tgl_mulai, i.tgl_selesai, i.alasan, i.alasan_lainnya, i.kamar, i.status, i.acc, i.is_suspended, i.file_upload, i.file_upload_at, i.dokumentasi, i.dokumentasi_at, i.approved_by, i.approved_at, i.approval_note, m.NMMHSMSMHS AS nama');
        $this->build_admin_query($filters);
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result_array();
    }

    public function count_for_admin(array $filters = array())
    {
        $this->db->select('COUNT(*) AS total');
        $this->build_admin_query($filters);
        $row = $this->db->get()->row_array();
        return isset($row['total']) ? (int) $row['total'] : 0;
    }

    private function build_admin_query(array $filters = array())
    {
        $this->db->from('ijin i');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = i.nim', 'left');
        $this->db->join('sim_akademik.mspst p', 'TRIM(p.KDPSTMSPST) = TRIM(m.KDPSTMSMHS) AND TRIM(p.KDPTIMSPST) = TRIM(m.KDPTIMSMHS)', 'left');

        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $this->db->group_start();
            $this->db->like('i.nim', $q);
            $this->db->or_like('m.NMMHSMSMHS', $q);
            $this->db->group_end();
        }

        if (isset($filters['status']) && $filters['status'] !== '' && in_array((string) $filters['status'], array('0','1','2','3','4','5'), TRUE)) {
            $this->db->where('i.status', (string) $filters['status']);
        }

        if (isset($filters['tipe_izin']) && in_array((string) $filters['tipe_izin'], array('1','2','3'), TRUE)) {
            $this->db->where('i.tipe_izin', (string) $filters['tipe_izin']);
        }

        if (!empty($filters['kamar'])) {
            $this->db->where('TRIM(i.kamar)', trim((string) $filters['kamar']));
        }

        if (!empty($filters['smt'])) {
            $this->db->where('i.smt', (int) $filters['smt']);
        }

        if (!empty($filters['alasan'])) {
            $this->db->like('i.alasan', trim((string) $filters['alasan']));
        }

        if (!empty($filters['sub_kategori'])) {
            $this->db->like('i.sub_kategori', trim((string) $filters['sub_kategori']));
        }

        if (!empty($filters['tgl_dari'])) {
            $this->db->where('DATE(i.tgl_ajuan) >=', $filters['tgl_dari']);
        }
        if (!empty($filters['tgl_sampai'])) {
            $this->db->where('DATE(i.tgl_ajuan) <=', $filters['tgl_sampai']);
        }

        $this->db->order_by('i.tgl_ajuan', 'DESC');
        $this->db->order_by('i.id', 'DESC');
    }

    // -------------------------------------------------------------------------
    // Buat pengajuan baru
    // -------------------------------------------------------------------------
    public function create_pengajuan($nim, $data)
    {
        $nim      = trim($nim);
        $id       = $nim . date('YmdHis');
        $kamar    = $this->get_kamar_santri($nim);
        $tipe     = (string) $data['tipe_izin'];
        $alasan   = $this->normalize_multi_value(isset($data['alasan']) ? $data['alasan'] : '');
        $sub_kat  = $this->normalize_multi_value(isset($data['sub_kategori']) ? $data['sub_kategori'] : '');

        // Cek bentrok dengan presensi yang sudah ada
        $this->load->model('Presensi_model');
        // Skip bentrok check untuk izin haid (akan auto-convert status presensi jadi izin)
        $is_haid = strtolower($alasan) !== '' && strpos(strtolower($alasan), 'haid') !== FALSE;

        if (!$is_haid) {
            // Hanya cek bentrok untuk izin NON-HAID
            $bentrok = $this->Presensi_model->check_bentrok_presensi(
                $nim, 
                $tipe, 
                $data['tgl_mulai'], 
                isset($data['tgl_selesai']) ? $data['tgl_selesai'] : $data['tgl_mulai'],
                $alasan
            );
            
            if (!empty($bentrok)) {
                return array(
                    'ok' => FALSE, 
                    'bentrok' => TRUE,
                    'data_bentrok' => $bentrok,
                    'message' => 'Ada bentrok dengan presensi hadir yang sudah tercatat.'
                );
            }
        }

        $payload = array(
            'id'             => $id,
            'nim'            => $nim,
            'tipe_izin'      => $tipe,
            'sub_kategori'   => $sub_kat,
            'tgl_ajuan'      => date('Y-m-d'),
            'tgl_mulai'      => $data['tgl_mulai'],
            'tgl_selesai'    => isset($data['tgl_selesai']) ? $data['tgl_selesai'] : $data['tgl_mulai'],
            'alasan'         => $alasan,
            'alasan_lainnya' => isset($data['alasan_lainnya']) ? trim((string) $data['alasan_lainnya']) : '',
            'ket'            => '',
            'kamar'          => (string) $kamar,
            'acc'            => '0',
            'status'         => self::STATUS_SIAP_CETAK,
            'smt'            => (int) $data['smt'],
        );

        $ok = $this->db->insert('ijin', $payload);
        if (!$ok) {
            return array('ok' => FALSE, 'message' => 'Gagal menyimpan pengajuan izin.');
        }

        // Insert detail dates hanya untuk tipe 1 & 2
        if ($tipe === self::TIPE_KURANG_DUA_MINGGU || $tipe === self::TIPE_LEBIH_DUA_MINGGU) {
            $this->insert_detail_dates($id, $nim, $data['tgl_mulai'], $payload['tgl_selesai']);
        }

        return array('ok' => TRUE, 'id' => $id);
    }

    // -------------------------------------------------------------------------
    // Get izin by id + nim (untuk aksi user)
    // -------------------------------------------------------------------------
    public function get_izin_by_id_and_nim($id, $nim)
    {
        return $this->db
            ->select('i.id, i.nim, i.tipe_izin, i.sub_kategori, i.tgl_ajuan, i.tgl_mulai, i.tgl_selesai, i.alasan, i.alasan_lainnya, i.smt, i.status, i.acc, i.file_upload, i.file_upload_at, i.dokumentasi, i.dokumentasi_at')
            ->from('ijin i')
            ->where('i.id', $id)
            ->where('i.nim', trim($nim))
            ->get()
            ->row_array();
    }

    // -------------------------------------------------------------------------
    // Get izin lengkap untuk cetak surat (dengan join data mahasiswa)
    // -------------------------------------------------------------------------
    public function get_izin_for_print($id, $nim)
    {
        return $this->db
            ->select(
                'i.id, i.nim, i.tipe_izin, i.sub_kategori, i.tgl_mulai, i.tgl_selesai, i.alasan, i.alasan_lainnya, i.smt, i.acc, i.status, i.approved_at, '
                . 'm.NMMHSMSMHS AS nama, m.ALAMATLENGKAP AS alamat, '
                . "COALESCE(NULLIF(TRIM(p.NMPSTMSPST), ''), NULLIF(TRIM(m.JURUSAN), ''), NULLIF(TRIM(m.KDPSTMSMHS), ''), '-') AS prodi, "
                . 'COALESCE(NULLIF(TRIM(s.kmr), \'\'), i.kamar) AS kamar',
                FALSE
            )
            ->from('ijin i')
            ->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = i.nim', 'left')
            ->join('sim_akademik.mspst p', 'TRIM(p.KDPSTMSPST) = TRIM(m.KDPSTMSMHS) AND TRIM(p.KDPTIMSPST) = TRIM(m.KDPTIMSMHS)', 'left')
            ->join('mssantri s', 'TRIM(s.nim) = i.nim', 'left')
            ->where('i.id', $id)
            ->where('i.nim', trim($nim))
            ->get()
            ->row_array();
    }

    // -------------------------------------------------------------------------
    // Upload surat → status jadi Menunggu Validasi (2) atau Menunggu Dokumentasi (5)
    // -------------------------------------------------------------------------
    public function upload_surat_file($id, $nim, $filename)
    {
        $izin = $this->get_izin_by_id_and_nim($id, $nim);
        if (!$izin) {
            return array('ok' => FALSE, 'message' => 'Pengajuan tidak ditemukan.');
        }

        if (!in_array((string) $izin['status'], array('0', '1', '4'), TRUE)) {
            return array('ok' => FALSE, 'message' => 'Status pengajuan tidak memungkinkan upload pada tahap ini.');
        }

        // Tipe 3 dengan alasan kerkom/rapat/kuliah → status 5 (Menunggu Dokumentasi)
        // Selain itu → status 2 (Menunggu Validasi)
        $next_status = self::STATUS_MENUNGGU_VALIDASI;
        if ((string) $izin['tipe_izin'] === self::TIPE_JAMAAH_NGAJI && $this->is_perlu_dokumentasi($izin['alasan'])) {
            // Jika dokumentasi sudah ada, langsung ke validasi
            $next_status = empty($izin['dokumentasi'])
                ? self::STATUS_MENUNGGU_DOKUMENTASI
                : self::STATUS_MENUNGGU_VALIDASI;
        }

        $ok = $this->db
            ->where('id', $id)
            ->where('nim', trim($nim))
            ->update('ijin', array(
                'file_upload'    => $filename,
                'file_upload_at' => date('Y-m-d H:i:s'),
                'status'         => $next_status,
            ));

        return $ok
            ? array('ok' => TRUE, 'next_status' => $next_status)
            : array('ok' => FALSE, 'message' => 'Gagal menyimpan upload file.');
    }

    // -------------------------------------------------------------------------
    // Upload dokumentasi → status jadi Menunggu Validasi (2)
    // -------------------------------------------------------------------------
    public function upload_dokumentasi_file($id, $nim, $filename)
    {
        $izin = $this->get_izin_by_id_and_nim($id, $nim);
        if (!$izin) {
            return array('ok' => FALSE, 'message' => 'Pengajuan tidak ditemukan.');
        }

        // Dokumentasi hanya untuk tipe 3 dengan alasan kerkom/rapat/kuliah
        if ((string) $izin['tipe_izin'] !== self::TIPE_JAMAAH_NGAJI || !$this->is_perlu_dokumentasi($izin['alasan'])) {
            return array('ok' => FALSE, 'message' => 'Upload dokumentasi tidak berlaku untuk pengajuan ini.');
        }

        $payload = array(
            'dokumentasi'    => $filename,
            'dokumentasi_at' => date('Y-m-d H:i:s'),
        );

        // Jika surat sudah diupload, ubah status ke Menunggu Validasi
        if (!empty($izin['file_upload']) && (string) $izin['status'] === self::STATUS_MENUNGGU_DOKUMENTASI) {
            $payload['status'] = self::STATUS_MENUNGGU_VALIDASI;
        }

        $ok = $this->db->where('id', $id)->where('nim', trim($nim))->update('ijin', $payload);

        return $ok
            ? array('ok' => TRUE)
            : array('ok' => FALSE, 'message' => 'Gagal menyimpan dokumentasi.');
    }

    // -------------------------------------------------------------------------
    // Save dokumentasi saja (tanpa ubah status, untuk upload saat pengajuan)
    // -------------------------------------------------------------------------
    public function save_dokumentasi($id, $filename)
    {
        return $this->db
            ->where('id', $id)
            ->update('ijin', array(
                'dokumentasi'    => $filename,
                'dokumentasi_at' => date('Y-m-d H:i:s'),
            ));
    }

    // -------------------------------------------------------------------------
    // Save file_upload (surat otomatis)
    // -------------------------------------------------------------------------
    public function save_file_upload($id, $filename)
    {
        return $this->db
            ->where('id', $id)
            ->update('ijin', array(
                'file_upload'    => $filename,
                'file_upload_at' => date('Y-m-d H:i:s'),
            ));
    }

    // -------------------------------------------------------------------------
    // Get izin by id saja (untuk admin)
    // -------------------------------------------------------------------------
    public function get_izin_by_id($id)
    {
        return $this->db
            ->select('i.*')
            ->from('ijin i')
            ->where('i.id', $id)
            ->get()
            ->row_array();
    }

    // -------------------------------------------------------------------------
    // Mark status ke Menunggu Upload (1) saat santri klik download
    // -------------------------------------------------------------------------
    public function mark_waiting_upload($id, $nim)
    {
        return $this->db
            ->where('id', $id)
            ->where('nim', trim($nim))
            ->where('status', self::STATUS_SIAP_CETAK)
            ->update('ijin', array('status' => self::STATUS_MENUNGGU_UPLOAD));
    }

    // -------------------------------------------------------------------------
    // Admin: approve atau reject
    // -------------------------------------------------------------------------
    public function validate_izin($id, $decision, $admin_nim, $note = null)
    {
        $decision = (string) $decision;
        if (!in_array($decision, array('3', '4'), TRUE)) {
            return FALSE;
        }

        $izin = $this->db->get_where('ijin', array('id' => $id), 1)->row_array();
        if (!$izin || (string) $izin['status'] !== self::STATUS_MENUNGGU_VALIDASI) {
            return FALSE;
        }

        $ok = $this->db
            ->where('id', $id)
            ->update('ijin', array(
                'status'        => $decision,
                'acc'           => $decision === '3' ? '1' : '2',
                'approved_by'   => trim((string) $admin_nim),
                'approved_at'   => $decision === '3' ? date('Y-m-d H:i:s') : null,
                'approval_note' => $note !== null ? trim((string) $note) : null,
            ));

        if ($ok) {
            $this->load->model('Presensi_model');
            
            if ($decision === '3') {
                // APPROVE: Auto-insert presensi izin
                $this->Presensi_model->proses_izin_approved($id);
            } else {
                // REJECT: Hapus presensi izin masa depan yang mungkin sudah ada
                $this->Presensi_model->hapus_presensi_izin($id, date('Y-m-d'));
            }
        }

        return $ok;
    }

    // -------------------------------------------------------------------------
    // Get dokumentasi info
    // -------------------------------------------------------------------------
    public function get_dokumentasi($id, $nim)
    {
        return $this->db
            ->select('id, nim, dokumentasi, dokumentasi_at')
            ->from('ijin')
            ->where('id', $id)
            ->where('nim', trim($nim))
            ->get()
            ->row_array();
    }

    // -------------------------------------------------------------------------
    // Backup: ambil data untuk export CSV (by filters)
    // -------------------------------------------------------------------------
    public function get_for_backup_filtered(array $filters = array())
    {
        $kategori_map = self::get_kategori_tipe3();
        $tipe_map     = array(
            '1' => 'Kurang dari 2 Minggu',
            '2' => 'Lebih dari 2 Minggu',
            '3' => 'Jamaah & Ngaji',
        );
        $status_map = self::get_status_map();

        $this->db->select(
            'i.id, i.nim, i.tipe_izin, i.sub_kategori, i.tgl_ajuan, i.tgl_mulai, i.tgl_selesai,
             i.alasan, i.alasan_lainnya, i.smt, i.kamar, i.status,
             m.NMMHSMSMHS AS nama,
             COALESCE(NULLIF(TRIM(p.NMPSTMSPST),\'\'), NULLIF(TRIM(m.JURUSAN),\'\'), \'-\') AS prodi',
            FALSE
        );
        $this->build_admin_query($filters);
        $rows = $this->db->get()->result_array();

        $result = array();
        foreach ($rows as $row) {
            $tipe       = isset($row['tipe_izin']) ? (string) $row['tipe_izin'] : '';
            $sub_list   = array_values(array_filter(array_map('trim', explode(',', (string) $row['sub_kategori'])), 'strlen'));
            $kat_labels = array();
            foreach ($sub_list as $s) {
                $kat_labels[] = isset($kategori_map[$s]) ? $kategori_map[$s] : ucfirst(str_replace('_', ' ', $s));
            }

            $result[] = array(
                'ID Izin'           => $row['id'],
                'NIM'               => $row['nim'],
                'Nama'              => isset($row['nama']) ? (string) $row['nama'] : '-',
                'Program Studi'     => isset($row['prodi']) ? (string) $row['prodi'] : '-',
                'No. Kamar'         => $row['kamar'],
                'Semester'          => $row['smt'],
                'Jenis Izin'        => isset($tipe_map[$tipe]) ? $tipe_map[$tipe] : '-',
                'Kategori'          => !empty($kat_labels) ? implode(', ', $kat_labels) : '-',
                'Alasan'            => $row['alasan'],
                'Alasan Lainnya'    => $row['alasan_lainnya'],
                'Status'            => isset($status_map[$row['status']]) ? $status_map[$row['status']] : '-',
                'Tanggal Pengajuan' => $row['tgl_ajuan'],
                'Tanggal Mulai'     => $row['tgl_mulai'],
                'Tanggal Selesai'   => $row['tgl_selesai'],
            );
        }

        return $result;
    }

    public function delete_by_filters(array $filters = array())
    {
        // Ambil id dulu berdasarkan filter
        $this->db->select('i.id, i.file_upload, i.dokumentasi');
        $this->build_admin_query($filters);
        $rows = $this->db->get()->result_array();

        if (empty($rows)) {
            return array('ok' => FALSE, 'message' => 'Tidak ada data yang sesuai filter.');
        }

        $id_list    = array_column($rows, 'id');
        $upload_dir = FCPATH . 'uploads/perizinan/';

        $this->db->trans_begin();
        $this->load->model('Presensi_model');
        foreach ($rows as $row) {
            $this->Presensi_model->hapus_presensi_izin($row['id'], date('Y-m-d'));
        }
        $this->db->where_in('id', $id_list)->delete('ijindetail');
        $this->db->where_in('id', $id_list)->delete('ijin');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'message' => 'Gagal menghapus data.');
        }

        $this->db->trans_commit();

        foreach ($rows as $row) {
            foreach (array('file_upload', 'dokumentasi') as $col) {
                if (!empty($row[$col]) && file_exists($upload_dir . $row[$col])) {
                    unlink($upload_dir . $row[$col]);
                }
            }
        }

        return array('ok' => TRUE, 'deleted' => count($id_list));
    }

    // -------------------------------------------------------------------------
    // Backup: ambil data untuk export CSV
    // -------------------------------------------------------------------------
    public function get_for_backup($tgl_dari, $tgl_sampai)
    {
        $kategori_map = self::get_kategori_tipe3();
        $tipe_map     = array(
            '1' => 'Kurang dari 2 Minggu',
            '2' => 'Lebih dari 2 Minggu',
            '3' => 'Jamaah & Ngaji',
        );

        $rows = $this->db
            ->select(
                'i.id, i.nim, i.tipe_izin, i.sub_kategori, i.tgl_ajuan, i.tgl_mulai, i.tgl_selesai,
                 i.alasan, i.alasan_lainnya, i.smt, i.kamar,
                 m.NMMHSMSMHS AS nama,
                 COALESCE(NULLIF(TRIM(p.NMPSTMSPST),\'\'), NULLIF(TRIM(m.JURUSAN),\'\'), \'-\') AS prodi',
                FALSE
            )
            ->from('ijin i')
            ->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = i.nim', 'left')
            ->join('sim_akademik.mspst p', 'TRIM(p.KDPSTMSPST) = TRIM(m.KDPSTMSMHS) AND TRIM(p.KDPTIMSPST) = TRIM(m.KDPTIMSMHS)', 'left')
            ->where('DATE(i.tgl_ajuan) >=', $tgl_dari)
            ->where('DATE(i.tgl_ajuan) <=', $tgl_sampai)
            ->order_by('i.tgl_ajuan', 'ASC')
            ->order_by('i.id', 'ASC')
            ->get()
            ->result_array();

        // Format data untuk CSV
        $result = array();
        foreach ($rows as $row) {
            $tipe = isset($row['tipe_izin']) ? (string) $row['tipe_izin'] : '';

            // Format sub_kategori jadi label
            $sub_list = array_values(array_filter(array_map('trim', explode(',', (string) $row['sub_kategori'])), 'strlen'));
            $kat_labels = array();
            foreach ($sub_list as $s) {
                $kat_labels[] = isset($kategori_map[$s]) ? $kategori_map[$s] : ucfirst(str_replace('_', ' ', $s));
            }

            $result[] = array(
                'ID Izin'          => $row['id'],
                'NIM'              => $row['nim'],
                'Nama'             => isset($row['nama']) ? $row['nama'] : '-',
                'Program Studi'    => isset($row['prodi']) ? $row['prodi'] : '-',
                'No. Kamar'        => $row['kamar'],
                'Semester'         => $row['smt'],
                'Jenis Izin'       => isset($tipe_map[$tipe]) ? $tipe_map[$tipe] : '-',
                'Kategori'         => !empty($kat_labels) ? implode(', ', $kat_labels) : '-',
                'Alasan'           => $row['alasan'],
                'Alasan Lainnya'   => $row['alasan_lainnya'],
                'Tanggal Pengajuan'=> $row['tgl_ajuan'],
                'Tanggal Mulai'    => $row['tgl_mulai'],
                'Tanggal Selesai'  => $row['tgl_selesai'],
            );
        }

        return $result;
    }

    public function count_for_backup($tgl_dari, $tgl_sampai)
    {
        return (int) $this->db
            ->where('DATE(tgl_ajuan) >=', $tgl_dari)
            ->where('DATE(tgl_ajuan) <=', $tgl_sampai)
            ->count_all_results('ijin');
    }

    public function delete_by_periode($tgl_dari, $tgl_sampai)
    {
        $ids = $this->db
            ->select('id, file_upload, dokumentasi')
            ->from('ijin')
            ->where('DATE(tgl_ajuan) >=', $tgl_dari)
            ->where('DATE(tgl_ajuan) <=', $tgl_sampai)
            ->get()
            ->result_array();

        if (empty($ids)) {
            return array('ok' => FALSE, 'message' => 'Tidak ada data pada periode tersebut.');
        }

        $id_list    = array_column($ids, 'id');
        $upload_dir = FCPATH . 'uploads/perizinan/';

        $this->db->trans_begin();
        $this->load->model('Presensi_model');
        foreach ($ids as $row) {
            $this->Presensi_model->hapus_presensi_izin($row['id'], date('Y-m-d'));
        }
        $this->db->where_in('id', $id_list)->delete('ijindetail');
        $this->db->where_in('id', $id_list)->delete('ijin');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'message' => 'Gagal menghapus data.');
        }

        $this->db->trans_commit();

        foreach ($ids as $row) {
            foreach (array('file_upload', 'dokumentasi') as $col) {
                if (!empty($row[$col]) && file_exists($upload_dir . $row[$col])) {
                    unlink($upload_dir . $row[$col]);
                }
            }
        }

        return array('ok' => TRUE, 'deleted' => count($id_list));
    }

    // -------------------------------------------------------------------------
    // Delete history (admin)
    // -------------------------------------------------------------------------
    public function delete_history(array $ids)
    {
        $ids = array_values(array_filter(array_map('trim', $ids)));
        if (empty($ids)) {
            return array('ok' => FALSE, 'message' => 'Tidak ada data yang dipilih.');
        }

        $rows = $this->db
            ->select('id, nim, file_upload, dokumentasi')
            ->from('ijin')
            ->where_in('id', $ids)
            ->get()
            ->result_array();

        if (empty($rows)) {
            return array('ok' => FALSE, 'message' => 'Data tidak ditemukan.');
        }

        $upload_dir = FCPATH . 'uploads/perizinan/';

        $this->db->trans_begin();
        $this->load->model('Presensi_model');
        foreach ($rows as $row) {
            $this->Presensi_model->hapus_presensi_izin($row['id'], date('Y-m-d'));
        }
        $this->db->where_in('id', $ids)->delete('ijindetail');
        $this->db->where_in('id', $ids)->delete('ijin');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'message' => 'Gagal menghapus data.');
        }

        $this->db->trans_commit();

        foreach ($rows as $row) {
            foreach (array('file_upload', 'dokumentasi') as $col) {
                if (!empty($row[$col]) && file_exists($upload_dir . $row[$col])) {
                    unlink($upload_dir . $row[$col]);
                }
            }
        }

        return array('ok' => TRUE, 'message' => 'Riwayat berhasil dihapus.');
    }

    // -------------------------------------------------------------------------
    // Toggle suspension (admin)
    // -------------------------------------------------------------------------
    public function toggle_izin_suspension($id, $suspend, $admin_nim, $note = null)
    {
        $izin = $this->db->get_where('ijin', array('id' => $id), 1)->row_array();
        if (!$izin || (string) $izin['acc'] !== '1') {
            return array('ok' => FALSE, 'message' => 'Hanya izin yang sudah disetujui yang bisa diubah statusnya.');
        }

        $suspend        = (bool) $suspend;
        $currentSuspend = !empty($izin['is_suspended']) && (int) $izin['is_suspended'] === 1;
        if ($suspend === $currentSuspend) {
            return array('ok' => TRUE, 'message' => $suspend ? 'Izin sudah diselesaikan sementara.' : 'Izin sudah aktif.');
        }

        $payload = array('is_suspended' => $suspend ? 1 : 0);
        if ($suspend) {
            $payload['suspended_at']   = date('Y-m-d H:i:s');
            $payload['suspended_by']   = trim((string) $admin_nim);
            $payload['suspended_note'] = $note !== null ? trim((string) $note) : 'Diselesaikan sementara oleh admin.';
        } else {
            $payload['resumed_at']   = date('Y-m-d H:i:s');
            $payload['resumed_by']   = trim((string) $admin_nim);
            $payload['resumed_note'] = $note !== null ? trim((string) $note) : 'Dilanjutkan kembali oleh admin.';
        }

        $ok = $this->db->where('id', $id)->update('ijin', $payload);
        
        if ($ok) {
            $this->load->model('Presensi_model');
            
            if ($suspend) {
                // SUSPEND: Hapus presensi izin masa depan, riwayat sampai hari ini tetap ada
                $this->Presensi_model->hapus_presensi_izin($id, date('Y-m-d'));
            } else {
                // RESUME: Insert kembali presensi izin
                $this->Presensi_model->proses_izin_approved($id);
            }
        }
        
        return $ok
            ? array('ok' => TRUE, 'message' => $suspend ? 'Izin berhasil diselesaikan sementara.' : 'Izin berhasil dilanjutkan.')
            : array('ok' => FALSE, 'message' => 'Gagal memperbarui status izin.');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------
    private function get_kamar_santri($nim)
    {
        $row = $this->db
            ->select('TRIM(kmr) AS kmr', FALSE)
            ->from('mssantri')
            ->where('TRIM(nim)', trim($nim))
            ->get()
            ->row_array();

        return $row ? trim((string) $row['kmr']) : '';
    }

    private function normalize_multi_value($value)
    {
        return implode(', ', $this->parse_multi_input($value));
    }

    public function parse_multi_input($value)
    {
        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value) && strpos($value, ',') !== FALSE) {
            $items = explode(',', $value);
        } else {
            $items = array((string) $value);
        }

        $normalized = array();
        foreach ($items as $item) {
            $item = trim((string) $item);
            if ($item !== '' && !in_array($item, $normalized, TRUE)) {
                $normalized[] = $item;
            }
        }

        return $normalized;
    }

    private function insert_detail_dates($id_ijin, $nim, $tgl_mulai, $tgl_selesai)
    {
        try {
            $start = new DateTime($tgl_mulai);
            $end   = new DateTime($tgl_selesai);
        } catch (Exception $e) {
            return;
        }

        if ($end < $start) return;

        $rows = array();
        while ($start <= $end) {
            $rows[] = array(
                'id_ijin'    => $id_ijin,
                'tgl_detail' => $start->format('Y-m-d'),
                'nim_detail' => $nim,
            );
            $start->modify('+1 day');
        }

        if (!empty($rows)) {
            $this->db->insert_batch('ijindetail', $rows);
        }
    }
}
