<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Presensi_model extends CI_Model
{
    // -------------------------------------------------------------------------
    // Konstanta
    // -------------------------------------------------------------------------
    const KEGIATAN_LIST = array(
        'jamaah_maghrib' => 'Jamaah Maghrib',
        'jamaah_isya'    => 'Jamaah Isya',
        'jamaah_subuh'   => 'Jamaah Subuh',
        'ngaji_maghrib'  => 'Ngaji Ba\'da Maghrib',
        'ngaji_subuh'    => 'Ngaji Ba\'da Subuh',
    );

    const KEGIATAN_JAMAAH = array('jamaah_maghrib', 'jamaah_isya', 'jamaah_subuh');
    const KEGIATAN_NGAJI  = array('ngaji_maghrib', 'ngaji_subuh');

    // Kartu jamaah: putih < kuning < orange < merah < hitam
    const KARTU_JAMAAH = array('putih', 'kuning', 'orange', 'merah', 'hitam');
    // Kartu ngaji: putih < kuning < merah
    const KARTU_NGAJI  = array('putih', 'kuning', 'merah');

    const ALPHA_THRESHOLD_JAMAAH = 7;
    const ALPHA_THRESHOLD_NGAJI  = 2;
    const ALPHA_THRESHOLD_NGAJI_MERAH = 3;

    // -------------------------------------------------------------------------
    // Jadwal
    // -------------------------------------------------------------------------
    public function get_all_jadwal()
    {
        return $this->db
            ->order_by('id', 'ASC')
            ->get('presensi_jadwal')
            ->result_array();
    }

    public function get_jadwal_aktif_sekarang()
    {
        $now = date('H:i:s');
        return $this->db
            ->where('is_active', 1)
            ->where('jam_mulai <=', $now)
            ->where('jam_selesai >=', $now)
            ->get('presensi_jadwal')
            ->result_array();
    }

    public function update_jadwal($id, $jam_mulai, $jam_selesai, $is_active, $admin_nim)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update('presensi_jadwal', array(
                'jam_mulai'   => $jam_mulai,
                'jam_selesai' => $jam_selesai,
                'is_active'   => (int) $is_active,
                'updated_at'  => date('Y-m-d H:i:s'),
                'updated_by'  => trim((string) $admin_nim),
            ));
    }

    // -------------------------------------------------------------------------
    // Presensi santri
    // -------------------------------------------------------------------------

    /**
     * Cek apakah santri sudah presensi untuk kegiatan+tanggal tertentu
     */
    public function get_presensi($nim, $kegiatan, $tanggal)
    {
        return $this->db
            ->get_where('presensi', array(
                'nim'      => trim($nim),
                'kegiatan' => $kegiatan,
                'tanggal'  => $tanggal,
            ), 1)
            ->row_array();
    }

    /**
     * Ambil semua presensi santri untuk satu hari
     */
    public function get_presensi_hari_ini($nim, $tanggal = null)
    {
        $tanggal = $tanggal ?: date('Y-m-d');
        return $this->db
            ->where('nim', trim($nim))
            ->where('tanggal', $tanggal)
            ->get('presensi')
            ->result_array();
    }

    /**
     * Santri presensi hadir (self)
     */
    public function presensi_hadir($nim, $kegiatan, $tanggal = null)
    {
        $tanggal = $tanggal ?: date('Y-m-d');
        $nim     = trim($nim);

        // Cek sudah presensi belum
        if ($this->get_presensi($nim, $kegiatan, $tanggal)) {
            return array('ok' => FALSE, 'message' => 'Sudah presensi untuk kegiatan ini.');
        }

        // Cek apakah dalam periode waktu
        $jadwal = $this->db
            ->where('kegiatan', $kegiatan)
            ->where('is_active', 1)
            ->get('presensi_jadwal')
            ->row_array();

        if (!$jadwal) {
            return array('ok' => FALSE, 'message' => 'Jadwal kegiatan tidak ditemukan.');
        }

        $now = date('H:i:s');
        if ($now < $jadwal['jam_mulai'] || $now > $jadwal['jam_selesai']) {
            return array('ok' => FALSE, 'message' => 'Di luar periode presensi. Hubungi admin jika terlambat.');
        }

        $ok = $this->db->insert('presensi', array(
            'nim'         => $nim,
            'kegiatan'    => $kegiatan,
            'tanggal'     => $tanggal,
            'status'      => 'hadir',
            'presensi_at' => date('Y-m-d H:i:s'),
            'created_by'  => NULL,
        ));

        return $ok
            ? array('ok' => TRUE)
            : array('ok' => FALSE, 'message' => 'Gagal menyimpan presensi.');
    }

    /**
     * Insert presensi izin otomatis dari perizinan yang disetujui
     */
    public function insert_presensi_izin($nim, $kegiatan, $tanggal, $id_izin)
    {
        $nim = trim($nim);

        // Jika sudah ada record (misal sudah hadir duluan), skip
        if ($this->get_presensi($nim, $kegiatan, $tanggal)) {
            return TRUE;
        }

        return $this->db->insert('presensi', array(
            'nim'         => $nim,
            'kegiatan'    => $kegiatan,
            'tanggal'     => $tanggal,
            'status'      => 'izin',
            'id_izin'     => $id_izin,
            'presensi_at' => date('Y-m-d H:i:s'),
            'created_by'  => NULL,
        ));
    }

    /**
     * Proses insert presensi izin dari data ijin yang disetujui
     * Dipanggil saat admin approve izin
     */
    public function proses_izin_approved($id_izin)
    {
        $izin = $this->db->get_where('ijin', array('id' => $id_izin), 1)->row_array();
        if (!$izin) return FALSE;

        $nim       = trim($izin['nim']);
        $tipe      = (string) $izin['tipe_izin'];
        $tgl_mulai = $izin['tgl_mulai'];
        $tgl_selesai = $izin['tgl_selesai'];
        $sub_kat   = isset($izin['sub_kategori']) ? (string) $izin['sub_kategori'] : '';
        $alasan = isset($izin['alasan']) ? (string) $izin['alasan'] : '';
        $is_haid = $this->is_izin_haid($alasan);

        // Izin haid: hanya 3 kegiatan jamaah selama rentang tanggal, apa pun tipe izinnya.
        // Selain haid: tetap mengikuti tipe izin masing-masing.
        if ($is_haid) {
            $dates = $this->get_date_range($tgl_mulai, $tgl_selesai);
            $kegiatan_list = self::KEGIATAN_JAMAAH;

            foreach ($dates as $tgl) {
                // Pertama, update existing presensi yang ada di tanggal ini menjadi izin
                $this->db
                    ->where('nim', $nim)
                    ->where('tanggal', $tgl)
                    ->where_in('kegiatan', $kegiatan_list)
                    ->update('presensi', array(
                        'status' => 'izin',
                        'id_izin' => $id_izin,
                        'presensi_at' => date('Y-m-d H:i:s')
                    ));

                // Kemudian insert presensi baru untuk kegiatan yang belum ada record
                foreach ($kegiatan_list as $kegiatan) {
                    $existing = $this->get_presensi($nim, $kegiatan, $tgl);
                    if (!$existing) {
                        $this->insert_presensi_izin($nim, $kegiatan, $tgl, $id_izin);
                    }
                }
            }
            return TRUE;
        }

        if ($tipe === '1' || $tipe === '2') {
            $dates = $this->get_date_range($tgl_mulai, $tgl_selesai);
            $kegiatan_list = array_keys(self::KEGIATAN_LIST);

            foreach ($dates as $tgl) {
                $this->db
                    ->where('nim', $nim)
                    ->where('tanggal', $tgl)
                    ->where_in('kegiatan', $kegiatan_list)
                    ->update('presensi', array(
                        'status' => 'izin',
                        'id_izin' => $id_izin,
                        'presensi_at' => date('Y-m-d H:i:s')
                    ));

                foreach ($kegiatan_list as $kegiatan) {
                    $existing = $this->get_presensi($nim, $kegiatan, $tgl);
                    if (!$existing) {
                        $this->insert_presensi_izin($nim, $kegiatan, $tgl, $id_izin);
                    }
                }
            }
            return TRUE;
        }

        // Tipe 3: hanya kegiatan yang ada di sub_kategori, pada tanggal izin
        if ($tipe === '3') {
            $sub_list = array_values(array_filter(array_map('trim', explode(',', $sub_kat)), 'strlen'));
            // Update existing presensi menjadi izin
            $this->db
                ->where('nim', $nim)
                ->where('tanggal', $tgl_mulai)
                ->where_in('kegiatan', $sub_list)
                ->update('presensi', array(
                    'status' => 'izin',
                    'id_izin' => $id_izin,
                    'presensi_at' => date('Y-m-d H:i:s')
                ));

            // Insert presensi baru untuk yang belum ada
            foreach ($sub_list as $kegiatan) {
                if (isset(self::KEGIATAN_LIST[$kegiatan])) {
                    $existing = $this->get_presensi($nim, $kegiatan, $tgl_mulai);
                    if (!$existing) {
                        $this->insert_presensi_izin($nim, $kegiatan, $tgl_mulai, $id_izin);
                    }
                }
            }
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Cek bentrok presensi dengan izin yang akan diajukan
     * Return: array dengan info bentrok jika ada
     * 
     * CATATAN PENTING:
     * - Untuk tanggal hari ini dan sebelumnya (<= hari ini): Tidak dicek bentrok, akan auto-convert hadir menjadi izin
     * - Untuk tanggal setelah hari ini (> hari ini): Dicek bentrok, jika ada hadir → error
     * - Haid izin: Skip bentrok check sama sekali (di level create_pengajuan), langsung auto-convert
     */
    public function check_bentrok_presensi($nim, $tipe_izin, $tgl_mulai, $tgl_selesai, $sub_kategori = '')
    {
        $nim = trim($nim);
        $bentrok = array();
        $is_haid = $this->is_izin_haid($sub_kategori);
        $today = date('Y-m-d');
        
        if ($is_haid) {
            // Izin haid: cek hanya 3 kegiatan jamaah dalam rentang tanggal.
            // (Sebenarnya haid sudah skip di create_pengajuan, tapi jaga untuk safety)
            $dates = $this->get_date_range($tgl_mulai, $tgl_selesai);
            $kegiatan_list = self::KEGIATAN_JAMAAH;
            
            foreach ($dates as $tgl) {
                foreach ($kegiatan_list as $kegiatan) {
                    $existing = $this->get_presensi($nim, $kegiatan, $tgl);
                    if ($existing && $existing['status'] === 'hadir') {
                        $bentrok[] = array(
                            'tanggal' => $tgl,
                            'kegiatan' => $kegiatan,
                            'kegiatan_label' => self::get_label_kegiatan($kegiatan),
                            'status' => 'hadir'
                        );
                    }
                }
            }
        } elseif ($tipe_izin === '1' || $tipe_izin === '2') {
            // Tipe 1 & 2 non-haid: cek semua kegiatan, HANYA untuk tanggal SETELAH hari ini (> today)
            // Tanggal hari ini dan sebelumnya akan auto-convert hadir menjadi izin saat approval
            $dates = $this->get_date_range($tgl_mulai, $tgl_selesai);
            $kegiatan_list = array_keys(self::KEGIATAN_LIST);

            foreach ($dates as $tgl) {
                // Hanya check untuk tanggal masa depan (setelah hari ini)
                if ($tgl <= $today) {
                    continue;
                }
                
                foreach ($kegiatan_list as $kegiatan) {
                    $existing = $this->get_presensi($nim, $kegiatan, $tgl);
                    if ($existing && $existing['status'] === 'hadir') {
                        $bentrok[] = array(
                            'tanggal' => $tgl,
                            'kegiatan' => $kegiatan,
                            'kegiatan_label' => self::get_label_kegiatan($kegiatan),
                            'status' => 'hadir'
                        );
                    }
                }
            }
        } elseif ($tipe_izin === '3') {
            // Tipe 3: Cek kegiatan yang dipilih pada tanggal tertentu
            // Jika tanggal hari ini atau sebelumnya, tidak perlu cek bentrok (akan auto-convert)
            if ($tgl_mulai <= $today) {
                return $bentrok; // Empty array, tidak ada bentrok check untuk tanggal lalu/hari ini
            }
            
            $sub_list = array_values(array_filter(array_map('trim', explode(',', $sub_kategori)), 'strlen'));
            
            foreach ($sub_list as $kegiatan) {
                if (isset(self::KEGIATAN_LIST[$kegiatan])) {
                    $existing = $this->get_presensi($nim, $kegiatan, $tgl_mulai);
                    if ($existing && $existing['status'] === 'hadir') {
                        $bentrok[] = array(
                            'tanggal' => $tgl_mulai,
                            'kegiatan' => $kegiatan,
                            'kegiatan_label' => self::get_label_kegiatan($kegiatan),
                            'status' => 'hadir'
                        );
                    }
                }
            }
        }
        
        return $bentrok;
    }

    /**
     * Deteksi izin haid dari alasan izin.
     * Izin haid selalu diperlakukan sebagai skema 3 kegiatan jamaah.
     */
    private function is_izin_haid($alasan)
    {
        $alasan = strtolower(trim((string) $alasan));

        if ($alasan === '') {
            return FALSE;
        }

        return (strpos($alasan, 'haid') !== FALSE);
    }

    /**
     * Hapus presensi izin untuk tanggal setelah batas tertentu.
     * Tanggal <= $keep_until_date tetap dipertahankan sebagai riwayat.
     */
    public function hapus_presensi_izin($id_izin, $keep_until_date = null)
    {
        $keep_until_date = $keep_until_date ?: date('Y-m-d');

        return $this->db
            ->where('id_izin', $id_izin)
            ->where('status', 'izin')
            ->where('tanggal >', $keep_until_date)
            ->delete('presensi');
    }

    /**
     * Cek bentrok presensi manual dengan izin yang sudah ada
     * Return: array dengan info izin jika ada bentrok
     */
    public function check_bentrok_izin($nim, $kegiatan, $tanggal)
    {
        $nim = trim($nim);
        
        // Cek apakah ada presensi izin untuk kegiatan+tanggal ini
        $existing = $this->get_presensi($nim, $kegiatan, $tanggal);
        
        if ($existing && $existing['status'] === 'izin' && !empty($existing['id_izin'])) {
            // Ada presensi izin, ambil data izin dari tabel ijin
            $izin = $this->db
                ->select('id, tipe_izin, tgl_mulai, tgl_selesai, alasan, status, acc')
                ->from('ijin')
                ->where('id', $existing['id_izin'])
                ->get()
                ->row_array();
                
            if ($izin && (string)$izin['acc'] === '1') {
                return array(
                    'id_izin' => $izin['id'],
                    'tipe_izin' => $izin['tipe_izin'],
                    'tgl_mulai' => $izin['tgl_mulai'],
                    'tgl_selesai' => $izin['tgl_selesai'],
                    'alasan' => $izin['alasan']
                );
            }
        }
        
        return null;
    }
    public function admin_tambah_presensi($nim, $kegiatan, $tanggal, $status, $admin_nim, $id_izin = null)
    {
        $existing = $this->get_presensi($nim, $kegiatan, $tanggal);
        if ($existing) {
            return array('ok' => FALSE, 'message' => 'Sudah ada record presensi untuk slot ini. Gunakan edit.');
        }

        // Cek bentrok dengan izin yang sudah disetujui (hanya untuk status hadir)
        if ($status === 'hadir') {
            $bentrok_izin = $this->check_bentrok_izin($nim, $kegiatan, $tanggal);
            if ($bentrok_izin) {
                return array(
                    'ok' => FALSE, 
                    'bentrok' => TRUE,
                    'data_bentrok' => $bentrok_izin,
                    'message' => 'Ada bentrok dengan izin yang sudah disetujui untuk kegiatan ini.'
                );
            }
        }

        $ok = $this->db->insert('presensi', array(
            'nim'         => trim($nim),
            'kegiatan'    => $kegiatan,
            'tanggal'     => $tanggal,
            'status'      => $status,
            'id_izin'     => $id_izin,
            'presensi_at' => date('Y-m-d H:i:s'),
            'created_by'  => trim($admin_nim),
        ));

        return $ok
            ? array('ok' => TRUE)
            : array('ok' => FALSE, 'message' => 'Gagal menyimpan presensi.');
    }

    public function admin_edit_presensi($id, $status, $admin_nim)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update('presensi', array(
                'status'     => $status,
                'created_by' => trim($admin_nim),
                'updated_at' => date('Y-m-d H:i:s'),
            ));
    }

    public function admin_hapus_presensi($id)
    {
        return $this->db->where('id', (int) $id)->delete('presensi');
    }

    public function get_presensi_by_id($id)
    {
        return $this->db->get_where('presensi', array('id' => (int) $id), 1)->row_array();
    }

    // -------------------------------------------------------------------------
    // Rekap & Laporan
    // -------------------------------------------------------------------------

    /**
     * Hitung alpha santri dalam rentang tanggal per kategori
     * Alpha = slot yang tidak ada record hadir/izin DAN jadwal aktif
     * Jika jadwal nonaktif (libur), tidak dihitung alpha
     */
    public function hitung_alpha($nim, $tgl_mulai, $tgl_selesai)
    {
        $nim   = trim($nim);
        $dates = $this->get_date_range($tgl_mulai, $tgl_selesai);

        // Ambil semua record presensi dalam rentang
        $records = $this->db
            ->where('nim', $nim)
            ->where('tanggal >=', $tgl_mulai)
            ->where('tanggal <=', $tgl_selesai)
            ->get('presensi')
            ->result_array();

        // Index by kegiatan+tanggal
        $hadir_map = array();
        foreach ($records as $r) {
            $hadir_map[$r['kegiatan'] . '_' . $r['tanggal']] = TRUE;
        }

        // Ambil jadwal untuk cek status aktif per kegiatan per tanggal
        $jadwal_map = $this->get_jadwal_status_map($tgl_mulai, $tgl_selesai);

        $alpha_jamaah = 0;
        $alpha_ngaji  = 0;

        foreach ($dates as $tgl) {
            // Hanya hitung alpha untuk tanggal yang sudah lewat
            if ($tgl > date('Y-m-d')) continue;

            foreach (self::KEGIATAN_JAMAAH as $k) {
                // Jika jadwal nonaktif (libur), skip - tidak dihitung alpha
                if (isset($jadwal_map[$k . '_' . $tgl]) && $jadwal_map[$k . '_' . $tgl] == 0) {
                    continue;
                }
                if (!isset($hadir_map[$k . '_' . $tgl])) {
                    $alpha_jamaah++;
                }
            }
            foreach (self::KEGIATAN_NGAJI as $k) {
                // Jika jadwal nonaktif (libur), skip - tidak dihitung alpha
                if (isset($jadwal_map[$k . '_' . $tgl]) && $jadwal_map[$k . '_' . $tgl] == 0) {
                    continue;
                }
                if (!isset($hadir_map[$k . '_' . $tgl])) {
                    $alpha_ngaji++;
                }
            }
        }

        return array(
            'alpha_jamaah' => $alpha_jamaah,
            'alpha_ngaji'  => $alpha_ngaji,
        );
    }

    /**
     * Hitung kartu berdasarkan alpha minggu ini dan kartu minggu lalu
     */
    public function hitung_kartu($alpha_jamaah, $alpha_ngaji, $kartu_jamaah_lalu, $kartu_ngaji_lalu)
    {
        $kartu_jamaah = $this->hitung_kartu_jamaah($alpha_jamaah, $kartu_jamaah_lalu);
        $kartu_ngaji  = $this->hitung_kartu_ngaji($alpha_ngaji, $kartu_ngaji_lalu);

        return array(
            'kartu_jamaah' => $kartu_jamaah,
            'kartu_ngaji'  => $kartu_ngaji,
        );
    }

    private function hitung_kartu_jamaah($alpha, $kartu_lalu)
    {
        $tangga = self::KARTU_JAMAAH;
        $idx    = array_search($kartu_lalu ?: 'putih', $tangga);
        if ($idx === FALSE) $idx = 0;

        if ($alpha >= self::ALPHA_THRESHOLD_JAMAAH) {
            // Naik satu tingkat, tidak bisa loncat
            return $tangga[min($idx + 1, count($tangga) - 1)];
        } else {
            // Turun satu tingkat
            return $tangga[max($idx - 1, 0)];
        }
    }

    private function hitung_kartu_ngaji($alpha, $kartu_lalu)
    {
        $tangga = self::KARTU_NGAJI;
        $idx    = array_search($kartu_lalu ?: 'putih', $tangga);
        if ($idx === FALSE) $idx = 0;

        if ($alpha >= self::ALPHA_THRESHOLD_NGAJI_MERAH && $kartu_lalu === 'kuning') {
            return 'merah';
        } elseif ($alpha >= self::ALPHA_THRESHOLD_NGAJI) {
            return $tangga[min($idx + 1, count($tangga) - 1)];
        } else {
            return $tangga[max($idx - 1, 0)];
        }
    }

    /**
     * Preview rekap mingguan (belum disimpan)
     */
    public function preview_rekap_minggu($minggu_mulai, $minggu_selesai, $nim = null, array $filters = array())
    {
        // Sumber data: tabel users (role='user') sebagai master santri
        if ($nim) {
            $query = $this->db
                ->select('TRIM(u.nim) AS nim, m.NMMHSMSMHS AS nama, TRIM(s.kmr) AS kamar', FALSE)
                ->from('users u')
                ->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = u.nim', 'left')
                ->join('mssantri s', 'TRIM(s.nim) = u.nim', 'left')
                ->where('u.role', 'user')
                ->where('u.nim', trim($nim));
        } else {
            $query = $this->db
                ->select('TRIM(u.nim) AS nim, m.NMMHSMSMHS AS nama, TRIM(s.kmr) AS kamar', FALSE)
                ->from('users u')
                ->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = u.nim', 'left')
                ->join('mssantri s', 'TRIM(s.nim) = u.nim', 'left')
                ->where('u.role', 'user')
                ->where('u.nim !=', '');

            if (!empty($filters['kamar'])) {
                $query->where('TRIM(s.kmr)', trim($filters['kamar']));
            }
        }

        $santri_list = $query->get()->result_array();

        $result = array();
        foreach ($santri_list as $s) {
            $n = trim($s['nim']);

            $alpha      = $this->hitung_alpha($n, $minggu_mulai, $minggu_selesai);
            $kartu_lalu = $this->get_kartu_minggu_sebelumnya($n, $minggu_mulai);
            $kartu      = $this->hitung_kartu(
                $alpha['alpha_jamaah'],
                $alpha['alpha_ngaji'],
                $kartu_lalu ? $kartu_lalu['kartu_jamaah'] : 'putih',
                $kartu_lalu ? $kartu_lalu['kartu_ngaji']  : 'putih'
            );

            // Terapkan filter kartu setelah dihitung
            if (!empty($filters['kartu_jamaah']) && $kartu['kartu_jamaah'] !== $filters['kartu_jamaah']) {
                continue;
            }
            if (!empty($filters['kartu_ngaji']) && $kartu['kartu_ngaji'] !== $filters['kartu_ngaji']) {
                continue;
            }

            $result[] = array(
                'nim'               => $n,
                'nama'              => $s['nama'] ?? 'N/A',
                'kamar'             => $s['kamar'] ?? '-',
                'alpha_jamaah'      => $alpha['alpha_jamaah'],
                'alpha_ngaji'       => $alpha['alpha_ngaji'],
                'kartu_jamaah'      => $kartu['kartu_jamaah'],
                'kartu_ngaji'       => $kartu['kartu_ngaji'],
                'kartu_jamaah_lalu' => $kartu_lalu ? $kartu_lalu['kartu_jamaah'] : 'putih',
                'kartu_ngaji_lalu'  => $kartu_lalu ? $kartu_lalu['kartu_ngaji']  : 'putih',
            );
        }

        return $result;
    }

    /**
     * Finalisasi rekap mingguan — simpan ke presensi_kartu
     */
    public function finalisasi_rekap($minggu_mulai, $minggu_selesai, $admin_nim)
    {
        $preview = $this->preview_rekap_minggu($minggu_mulai, $minggu_selesai);

        if (empty($preview)) {
            return array('ok' => FALSE, 'message' => 'Tidak ada data santri.');
        }

        $this->db->trans_begin();

        foreach ($preview as $row) {
            $existing = $this->db
                ->get_where('presensi_kartu', array('nim' => $row['nim'], 'minggu_mulai' => $minggu_mulai), 1)
                ->row_array();

            $payload = array(
                'nim'            => $row['nim'],
                'minggu_mulai'   => $minggu_mulai,
                'minggu_selesai' => $minggu_selesai,
                'alpha_jamaah'   => $row['alpha_jamaah'],
                'alpha_ngaji'    => $row['alpha_ngaji'],
                'kartu_jamaah'   => $row['kartu_jamaah'],
                'kartu_ngaji'    => $row['kartu_ngaji'],
                'is_final'       => 1,
                'finalized_at'   => date('Y-m-d H:i:s'),
                'finalized_by'   => trim($admin_nim),
            );

            if ($existing) {
                $this->db->where('id', $existing['id'])->update('presensi_kartu', $payload);
            } else {
                $this->db->insert('presensi_kartu', $payload);
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'message' => 'Gagal menyimpan rekap.');
        }

        $this->db->trans_commit();
        return array('ok' => TRUE, 'total' => count($preview));
    }

    /**
     * Ambil kartu minggu sebelumnya untuk satu santri
     */
    public function get_kartu_minggu_sebelumnya($nim, $minggu_mulai_ini)
    {
        return $this->db
            ->where('nim', trim($nim))
            ->where('minggu_mulai <', $minggu_mulai_ini)
            ->where('is_final', 1)
            ->order_by('minggu_mulai', 'DESC')
            ->limit(1)
            ->get('presensi_kartu')
            ->row_array();
    }

    /**
     * Ambil rekap kartu santri (untuk laporan)
     */
    public function get_rekap_kartu($nim, $limit = 10)
    {
        return $this->db
            ->where('nim', trim($nim))
            ->order_by('minggu_mulai', 'DESC')
            ->limit((int) $limit)
            ->get('presensi_kartu')
            ->result_array();
    }

    /**
     * Ambil detail presensi santri dalam satu minggu
     * Jika jadwal nonaktif (libur), status otomatis izin
     */
    public function get_detail_presensi_minggu($nim, $minggu_mulai, $minggu_selesai)
    {
        $nim   = trim($nim);
        $dates = $this->get_date_range($minggu_mulai, $minggu_selesai);

        $records = $this->db
            ->where('nim', $nim)
            ->where('tanggal >=', $minggu_mulai)
            ->where('tanggal <=', $minggu_selesai)
            ->get('presensi')
            ->result_array();

        $map = array();
        foreach ($records as $r) {
            $map[$r['tanggal']][$r['kegiatan']] = $r['status'];
        }

        // Ambil jadwal untuk cek status aktif per kegiatan per tanggal
        $jadwal_map = $this->get_jadwal_status_map($minggu_mulai, $minggu_selesai);

        $result = array();
        foreach ($dates as $tgl) {
            $row = array('tanggal' => $tgl);
            foreach (array_keys(self::KEGIATAN_LIST) as $k) {
                if (isset($map[$tgl][$k])) {
                    $row[$k] = $map[$tgl][$k];
                } elseif (isset($jadwal_map[$k . '_' . $tgl]) && $jadwal_map[$k . '_' . $tgl] == 0) {
                    // Jadwal nonaktif (libur) = otomatis izin
                    $row[$k] = 'izin';
                } elseif ($tgl <= date('Y-m-d')) {
                    $row[$k] = 'alpha';
                } else {
                    $row[$k] = '-';
                }
            }
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Ambil rekap presensi untuk admin (semua santri, satu minggu)
     */
    public function get_rekap_admin($minggu_mulai, $minggu_selesai, array $filters = array())
    {
        $this->db->select('k.nim, k.alpha_jamaah, k.alpha_ngaji, k.kartu_jamaah, k.kartu_ngaji, k.is_final, m.NMMHSMSMHS AS nama, s.kmr AS kamar');
        $this->db->from('presensi_kartu k');
        $this->db->join('users u', 'u.nim = k.nim AND u.role = \'user\'', 'inner');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = k.nim', 'left');
        $this->db->join('mssantri s', 'TRIM(s.nim) = k.nim', 'left');
        $this->db->where('k.minggu_mulai', $minggu_mulai);

        if (!empty($filters['kartu_jamaah'])) {
            $this->db->where('k.kartu_jamaah', $filters['kartu_jamaah']);
        }
        if (!empty($filters['kartu_ngaji'])) {
            $this->db->where('k.kartu_ngaji', $filters['kartu_ngaji']);
        }
        if (!empty($filters['kamar'])) {
            $this->db->where('TRIM(s.kmr)', trim($filters['kamar']));
        }

        $this->db->order_by('k.kartu_jamaah', 'DESC');
        $this->db->order_by('k.alpha_jamaah', 'DESC');

        return $this->db->get()->result_array();
    }

    // -------------------------------------------------------------------------
    // Helper: get minggu aktif (Minggu subuh s/d Sabtu isya)
    // Periode dimulai dari hari Minggu jam 04:00 hingga hari Sabtu jam 23:59
    // -------------------------------------------------------------------------
    public static function get_minggu_aktif($tanggal = null)
    {
        if ($tanggal) {
            // Input dari form: pakai tanggal saja tanpa logika jam
            $ts  = strtotime($tanggal);
            $dow = (int) date('w', $ts); // 0=Minggu, 6=Sabtu
        } else {
            // Real-time: pakai jam sekarang
            $ts           = time();
            $current_hour = (int) date('H', $ts);
            $dow          = (int) date('w', $ts);
            // Jika hari Minggu dan jam < 04:00, masih periode minggu lalu
            if ($dow === 0 && $current_hour < 4) {
                $ts  = strtotime('-1 week', $ts);
                $dow = (int) date('w', $ts);
            }
        }

        $minggu = date('Y-m-d', strtotime('-' . $dow . ' days', $ts));
        $sabtu  = date('Y-m-d', strtotime('+' . (6 - $dow) . ' days', $ts));

        return array('mulai' => $minggu, 'selesai' => $sabtu);
    }

    public static function get_label_kegiatan($kegiatan)
    {
        return isset(self::KEGIATAN_LIST[$kegiatan]) ? self::KEGIATAN_LIST[$kegiatan] : $kegiatan;
    }

    public static function get_warna_kartu($kartu)
    {
        $map = array(
            'putih'  => array('label' => 'Putih',  'class' => 'light',   'text' => 'dark',  'color' => '#f8f9fa'),
            'kuning' => array('label' => 'Kuning', 'class' => 'warning', 'text' => 'dark',  'color' => '#ffc107'),
            'orange' => array('label' => 'Orange', 'class' => 'orange',  'text' => 'white', 'color' => '#fd7e14'),
            'merah'  => array('label' => 'Merah',  'class' => 'danger',  'text' => 'white', 'color' => '#dc3545'),
            'hitam'  => array('label' => 'Hitam',  'class' => 'dark',    'text' => 'white', 'color' => '#343a40'),
        );
        return isset($map[$kartu]) ? $map[$kartu] : $map['putih'];
    }

    public static function render_kartu_badge($kartu, $size = 'normal')
    {
        $info = self::get_warna_kartu($kartu);
        $padding = $size === 'small' ? '2px 6px' : '4px 8px';
        $font_size = $size === 'small' ? 'font-size: 11px;' : '';
        
        return sprintf(
            '<span style="background-color: %s; color: %s; padding: %s; border-radius: 3px; display: inline-block; %s">%s</span>',
            $info['color'],
            $info['text'] === 'white' ? '#fff' : '#000',
            $padding,
            $font_size,
            htmlspecialchars($info['label'])
        );
    }

    /**
     * Ambil status jadwal (aktif/nonaktif) per kegiatan per tanggal
     * Return: array dengan key 'kegiatan_tanggal' => is_active (1/0)
     */
    private function get_jadwal_status_map($tgl_mulai, $tgl_selesai)
    {
        $dates = $this->get_date_range($tgl_mulai, $tgl_selesai);
        
        // Ambil semua jadwal
        $jadwal_list = $this->db->get('presensi_jadwal')->result_array();
        
        $map = array();
        foreach ($jadwal_list as $jadwal) {
            $kegiatan = $jadwal['kegiatan'];
            $is_active = (int)$jadwal['is_active'];
            
            // Set status untuk semua tanggal dalam rentang
            foreach ($dates as $tgl) {
                $map[$kegiatan . '_' . $tgl] = $is_active;
            }
        }
        
        return $map;
    }

    private function get_date_range($tgl_mulai, $tgl_selesai)
    {
        $dates = array();
        try {
            $start = new DateTime($tgl_mulai);
            $end   = new DateTime($tgl_selesai);
        } catch (Exception $e) {
            return $dates;
        }

        while ($start <= $end) {
            $dates[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }

        return $dates;
    }
}
