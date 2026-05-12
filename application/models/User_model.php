<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    private $table = 'users';

    public function find_by_nim($nim)
    {
        return $this->db->get_where($this->table, array('nim' => trim($nim)), 1)->row_array();
    }

    public function touch_last_login($user_id)
    {
        return $this->db
            ->where('id', (int) $user_id)
            ->update($this->table, array('last_login_at' => date('Y-m-d H:i:s')));
    }

    public function update_password($user_id, $password_hash)
    {
        return $this->db
            ->where('id', (int) $user_id)
            ->update($this->table, array(
                'password_hash' => $password_hash,
                'legacy_password_md5' => NULL,
                'must_reset_password' => 0,
                'password_updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function get_santri_for_admin($filters = array(), $limit = 10, $offset = 0)
    {
        $this->build_santri_admin_query($filters);
        $this->db->select(
            'u.id, u.nim, m.NMMHSMSMHS AS nama, s.kmr AS kamar, '
            . "CASE WHEN EXISTS ("
            . "SELECT 1 FROM ijin i "
            . "WHERE TRIM(i.nim) = TRIM(u.nim) "
            . "AND i.acc = '1' "
            . "AND COALESCE(i.is_suspended, 0) = 0 "
            . "AND CURDATE() BETWEEN i.tgl_mulai AND i.tgl_selesai"
            . ") THEN 'Izin' ELSE 'Aktif' END AS status, "
            . "COALESCE(NULLIF(TRIM(p.NMPSTMSPST), ''), NULLIF(TRIM(m.JURUSAN), ''), NULLIF(TRIM(m.KDPSTMSMHS), ''), '-') AS prodi, "
            . 'm.ALAMATLENGKAP AS alamat',
            FALSE
        );
        $this->db->order_by('CAST(TRIM(u.nim) AS UNSIGNED)', 'ASC', FALSE);
        $this->db->order_by('u.nim', 'ASC');
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result_array();
    }

    public function count_santri_for_admin($filters = array())
    {
        $this->build_santri_admin_query($filters);
        return (int) $this->db->count_all_results();
    }

    public function get_santri_filter_options()
    {
        $this->db->select(
            "DISTINCT COALESCE(NULLIF(TRIM(p.NMPSTMSPST), ''), NULLIF(TRIM(m.JURUSAN), ''), NULLIF(TRIM(m.KDPSTMSMHS), ''), '-') AS prodi",
            FALSE
        );
        $this->db->from('users u');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = u.nim', 'left');
        $this->db->join('sim_akademik.mspst p', 'TRIM(p.KDPSTMSPST) = TRIM(m.KDPSTMSMHS) AND TRIM(p.KDPTIMSPST) = TRIM(m.KDPTIMSMHS)', 'left');
        $this->db->where('u.role', 'user');
        $this->db->order_by('prodi', 'ASC');
        $prodiRows = $this->db->get()->result_array();

        $prodi = array();
        foreach ($prodiRows as $row) {
            if (!empty($row['prodi']) && $row['prodi'] !== '-') {
                $prodi[] = $row['prodi'];
            }
        }

        $this->db->select("DISTINCT CONCAT('20', LEFT(u.nim, 2)) AS angkatan", FALSE);
        $this->db->from('users u');
        $this->db->where('u.role', 'user');
        $this->db->where('u.nim <>', '');
        $this->db->order_by('angkatan', 'DESC');
        $angkatanRows = $this->db->get()->result_array();

        $angkatan = array();
        foreach ($angkatanRows as $row) {
            if (!empty($row['angkatan']) && preg_match('/^20[0-9]{2}$/', $row['angkatan'])) {
                $angkatan[] = $row['angkatan'];
            }
        }

        return array(
            'prodi' => array_values(array_unique($prodi)),
            'angkatan' => array_values(array_unique($angkatan)),
            'lantai' => array('1', '2', '3')
        );
    }

    public function get_santri_status_summary_for_admin()
    {
        $this->db->select(
            'COUNT(*) AS total_santri, '
            . "SUM(CASE WHEN EXISTS ("
            . "SELECT 1 FROM ijin i "
            . "WHERE TRIM(i.nim) = TRIM(u.nim) "
            . "AND i.acc = '1' "
            . "AND COALESCE(i.is_suspended, 0) = 0 "
            . "AND CURDATE() BETWEEN i.tgl_mulai AND i.tgl_selesai"
            . ") THEN 1 ELSE 0 END) AS total_izin",
            FALSE
        );
        $this->db->from('users u');
        $this->db->where('u.role', 'user');

        $row = $this->db->get()->row_array();
        $total = isset($row['total_santri']) ? (int) $row['total_santri'] : 0;
        $izin = isset($row['total_izin']) ? (int) $row['total_izin'] : 0;

        return array(
            'total_santri' => $total,
            'total_izin' => $izin,
            'total_aktif' => max(0, $total - $izin)
        );
    }

    public function get_profile_by_nim($nim)
    {
        $this->db->select(
            'u.nim, u.email, u.status, m.NMMHSMSMHS AS nama, '
            . 'NULLIF(TRIM(m.EMAIL), "") AS email_kampus, '
            . 'NULLIF(TRIM(s.kmr), "") AS kamar, '
            . "COALESCE(NULLIF(TRIM(p.NMPSTMSPST), ''), NULLIF(TRIM(m.JURUSAN), ''), NULLIF(TRIM(m.KDPSTMSMHS), ''), '-') AS prodi, "
            . 'NULLIF(TRIM(m.TAHUNMSMHS), "") AS angkatan, '
            . 'NULLIF(TRIM(m.SMAWLMSMHS), "") AS semester_masuk, '
            . 'NULLIF(TRIM(m.STMHSMSMHS), "") AS status_mahasiswa, '
            . 'NULLIF(TRIM(m.KDJENMSMHS), "") AS jenjang_kode, '
            . 'NULLIF(TRIM(m.KDPSTMSMHS), "") AS prodi_kode, '
            . 'NULLIF(TRIM(m.TPLHRMSMHS), "") AS tempat_lahir, '
            . 'm.TGLHRMSMHS AS tanggal_lahir, '
            . 'NULLIF(TRIM(m.KDJEKMSMHS), "") AS jenis_kelamin_kode, '
            . 'NULLIF(TRIM(m.AGAMA), "") AS agama, '
            . 'NULLIF(TRIM(m.GOLDARAH), "") AS golongan_darah, '
            . 'NULLIF(TRIM(m.TINGGIBADAN), "") AS tinggi_badan, '
            . 'NULLIF(TRIM(m.BERATBADAN), "") AS berat_badan, '
            . 'NULLIF(TRIM(m.TELP), "") AS no_hp, '
            . 'NULLIF(m.ALAMATLENGKAP, "") AS alamat, '
            . 'NULLIF(TRIM(m.NAMAORTUWALI), "") AS nama_ortu_wali, '
            . 'NULLIF(TRIM(m.TELPORTUWALI), "") AS telp_ortu_wali, '
            . 'NULLIF(TRIM(m.PEKERJAANORTUWALI), "") AS pekerjaan_ortu_wali, '
            . 'NULLIF(TRIM(m.NAMASEKOLAH), "") AS nama_sekolah, '
            . 'NULLIF(TRIM(m.TAHUNLULUS), "") AS tahun_lulus, '
            . 'NULLIF(TRIM(m.PENDIDIKAN), "") AS pendidikan_sekolah, '
            . '(SELECT COUNT(1) FROM ijin i WHERE TRIM(i.nim) = TRIM(u.nim)) AS total_izin, '
            . '(SELECT i2.tgl_ajuan FROM ijin i2 WHERE TRIM(i2.nim) = TRIM(u.nim) ORDER BY i2.tgl_ajuan DESC LIMIT 1) AS tanggal_izin_terakhir, '
            . '(SELECT i3.acc FROM ijin i3 WHERE TRIM(i3.nim) = TRIM(u.nim) ORDER BY i3.tgl_ajuan DESC LIMIT 1) AS acc_izin_terakhir, '
            . '(SELECT i4.tgl_mulai FROM ijin i4 WHERE TRIM(i4.nim) = TRIM(u.nim) AND i4.acc = "1" AND COALESCE(i4.is_suspended, 0) = 0 AND CURDATE() BETWEEN i4.tgl_mulai AND i4.tgl_selesai ORDER BY i4.tgl_mulai DESC LIMIT 1) AS izin_aktif_mulai, '
            . '(SELECT i5.tgl_selesai FROM ijin i5 WHERE TRIM(i5.nim) = TRIM(u.nim) AND i5.acc = "1" AND COALESCE(i5.is_suspended, 0) = 0 AND CURDATE() BETWEEN i5.tgl_mulai AND i5.tgl_selesai ORDER BY i5.tgl_mulai DESC LIMIT 1) AS izin_aktif_selesai',
            FALSE
        );
        $this->db->from('users u');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = u.nim', 'left');
        $this->db->join('sim_akademik.mspst p', 'TRIM(p.KDPSTMSPST) = TRIM(m.KDPSTMSMHS) AND TRIM(p.KDPTIMSPST) = TRIM(m.KDPTIMSMHS)', 'left');
        $this->db->join('mssantri s', 'TRIM(s.nim) = u.nim', 'left');
        $this->db->where('u.nim', trim($nim));
        return $this->db->get()->row_array();
    }

    public function get_santri_by_id($id)
    {
        $this->db->select('u.id, u.nim, u.email, u.status, u.must_reset_password, s.kmr AS kamar, m.NMMHSMSMHS AS nama');
        $this->db->from('users u');
        $this->db->join('mssantri s', 'TRIM(s.nim) = u.nim', 'left');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = u.nim', 'left');
        $this->db->where('u.id', (int) $id);
        $this->db->where('u.role', 'user');
        return $this->db->get()->row_array();
    }

    public function find_msmhs_by_nim($nim)
    {
        return $this->db
            ->select('TRIM(NIMHSMSMHS) AS nim, NMMHSMSMHS AS nama, NULLIF(TRIM(EMAIL), "") AS email')
            ->from('sim_akademik.msmhs')
            ->where('TRIM(NIMHSMSMHS)', trim($nim))
            ->get()
            ->row_array();
    }

    public function create_santri_from_msmhs($nim, $kamar)
    {
        $nim = trim($nim);
        $kamar = trim($kamar);

        $msmhs = $this->find_msmhs_by_nim($nim);
        if (!$msmhs) {
            return array('ok' => FALSE, 'message' => 'NIM tidak ditemukan di data mahasiswa (msmhs).');
        }

        if ($this->find_by_nim($nim)) {
            return array('ok' => FALSE, 'message' => 'NIM sudah terdaftar sebagai akun santri.');
        }

        $this->db->trans_begin();

        $insertUser = $this->db->insert($this->table, array(
            'nim' => $nim,
            'email' => $msmhs['email'],
            'password_hash' => NULL,
            'legacy_password_md5' => md5('ppm' . $nim),
            'role' => 'user',
            'status' => 'active',
            'must_reset_password' => 1
        ));

        if (!$insertUser) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'message' => 'Gagal menyimpan akun santri.');
        }

        $existingSantri = $this->db->get_where('mssantri', array('nim' => $nim), 1)->row_array();
        if ($existingSantri) {
            $okSantri = $this->db->where('nim', $nim)->update('mssantri', array(
                'kmr' => $kamar,
                'pass' => md5('ppm' . $nim)
            ));
        } else {
            $okSantri = $this->db->insert('mssantri', array(
                'nim' => $nim,
                'kmr' => $kamar,
                'pass' => md5('ppm' . $nim)
            ));
        }

        if (!$okSantri) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'message' => 'Gagal menyimpan data kamar santri.');
        }

        $this->db->trans_commit();
        return array('ok' => TRUE, 'message' => 'Santri berhasil ditambahkan.');
    }

    public function update_santri($id, $kamar)
    {
        $santri = $this->get_santri_by_id($id);
        if (!$santri) {
            return array('ok' => FALSE, 'message' => 'Data santri tidak ditemukan.');
        }

        $kamar = trim($kamar);

        $this->db->trans_begin();

        $existingSantri = $this->db->get_where('mssantri', array('nim' => $santri['nim']), 1)->row_array();
        if ($existingSantri) {
            $okSantri = $this->db
                ->where('nim', $santri['nim'])
                ->update('mssantri', array('kmr' => $kamar));
        } else {
            $okSantri = $this->db->insert('mssantri', array(
                'nim' => $santri['nim'],
                'kmr' => $kamar,
                'pass' => md5('ppm' . $santri['nim'])
            ));
        }

        if (!$okSantri) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'message' => 'Gagal memperbarui data kamar.');
        }

        $this->db->trans_commit();
        return array('ok' => TRUE, 'message' => 'Data santri berhasil diperbarui.');
    }

    public function delete_santri($id)
    {
        $santri = $this->get_santri_by_id($id);
        if (!$santri) {
            return array('ok' => FALSE, 'message' => 'Data santri tidak ditemukan.');
        }

        $nim = $santri['nim'];

        $this->db->trans_begin();

        // Hapus data presensi
        $this->db->where('nim', $nim)->delete('presensi');
        $this->db->where('nim', $nim)->delete('presensi_kartu');

        // Hapus data izin (ijindetail dulu karena foreign key)
        $ijin_ids = $this->db->select('id')->where('nim', $nim)->get('ijin')->result_array();
        if (!empty($ijin_ids)) {
            $ids = array_column($ijin_ids, 'id');
            $this->db->where_in('id', $ids)->delete('ijindetail');
            $this->db->where('nim', $nim)->delete('ijin');
        }

        // Hapus dari mssantri dan users
        $this->db->where('nim', $nim)->delete('mssantri');
        $okUser = $this->db->where('id', (int) $id)->delete($this->table);

        if ($this->db->trans_status() === FALSE || !$okUser) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'message' => 'Gagal menghapus data santri.');
        }

        $this->db->trans_commit();
        return array('ok' => TRUE, 'message' => 'Santri dan seluruh data terkait berhasil dihapus.');
    }

    private function build_santri_admin_query($filters = array())
    {
        $this->db->from('users u');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = u.nim', 'left');
        $this->db->join('sim_akademik.mspst p', 'TRIM(p.KDPSTMSPST) = TRIM(m.KDPSTMSMHS) AND TRIM(p.KDPTIMSPST) = TRIM(m.KDPTIMSMHS)', 'left');
        $this->db->join('mssantri s', 'TRIM(s.nim) = u.nim', 'left');
        $this->db->where('u.role', 'user');

        if (!empty($filters['prodi'])) {
            $prodi = trim($filters['prodi']);
            $this->db->where(
                "COALESCE(NULLIF(TRIM(p.NMPSTMSPST), ''), NULLIF(TRIM(m.JURUSAN), ''), NULLIF(TRIM(m.KDPSTMSMHS), ''), '-') = " . $this->db->escape($prodi),
                NULL,
                FALSE
            );
        }

        if (!empty($filters['lantai']) && in_array($filters['lantai'], array('1', '2', '3'), TRUE)) {
            $this->db->like('TRIM(s.kmr)', $filters['lantai'], 'after', FALSE);
        }

        if (!empty($filters['angkatan']) && preg_match('/^20[0-9]{2}$/', $filters['angkatan'])) {
            $this->db->where(
                "CONCAT('20', LEFT(u.nim, 2)) = " . $this->db->escape($filters['angkatan']),
                NULL,
                FALSE
            );
        }

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $this->db->group_start();
            $this->db->like('u.nim', $q);
            $this->db->or_like('m.NMMHSMSMHS', $q);
            $this->db->or_like('m.ALAMATLENGKAP', $q);
            $this->db->group_end();
        }
    }
}
