<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Periode_haid_model extends CI_Model
{
    const TABLE = 'periode_haid';
    const MIN_HARI = 1;
    const MAX_HARI = 50;

    /**
     * Get semua data periode haid dengan join ke users
     */
    public function get_all($limit = 0, $offset = 0)
    {
        $this->db->select('ph.id, ph.nim, ph.rata_rata_hari, ph.paling_lama_hari, ph.created_at, ph.updated_at, ph.created_by, ph.updated_by, m.NMMHSMSMHS AS nama');
        $this->db->from(self::TABLE . ' ph');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = ph.nim', 'left');
        $this->db->order_by('ph.created_at', 'DESC');
        
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result_array();
    }

    /**
     * Count total data periode haid
     */
    public function count_all()
    {
        return (int) $this->db->from(self::TABLE)->count_all_results();
    }

    /**
     * Get filtered data periode haid dengan search dan duration filter
     * @param string $search - Search by NIM or Nama
     * @param string $duration_filter - 'singkat' (<7), 'normal' (7-14), 'panjang' (>14)
     */
    public function get_filtered($search = '', $duration_filter = '', $limit = 0, $offset = 0)
    {
        $this->db->select('ph.id, ph.nim, ph.rata_rata_hari, ph.paling_lama_hari, ph.created_at, ph.updated_at, ph.created_by, ph.updated_by, m.NMMHSMSMHS AS nama');
        $this->db->from(self::TABLE . ' ph');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = ph.nim', 'left');

        // Filter by search (NIM or Nama)
        if (!empty($search)) {
            $search = trim($search);
            $this->db->where('(ph.nim LIKE "%' . $this->db->escape_like_str($search) . '%" OR m.NMMHSMSMHS LIKE "%' . $this->db->escape_like_str($search) . '%")');
        }

        // Filter by duration category
        if (!empty($duration_filter)) {
            if ($duration_filter === 'singkat') {
                $this->db->where('ph.rata_rata_hari < 7');
            } elseif ($duration_filter === 'normal') {
                $this->db->where('ph.rata_rata_hari >= 7 AND ph.rata_rata_hari <= 14');
            } elseif ($duration_filter === 'panjang') {
                $this->db->where('ph.rata_rata_hari > 14');
            }
        }

        $this->db->order_by('ph.created_at', 'DESC');

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Count filtered data periode haid
     */
    public function count_filtered($search = '', $duration_filter = '')
    {
        $this->db->from(self::TABLE . ' ph');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = ph.nim', 'left');

        // Filter by search
        if (!empty($search)) {
            $search = trim($search);
            $this->db->where('(ph.nim LIKE "%' . $this->db->escape_like_str($search) . '%" OR m.NMMHSMSMHS LIKE "%' . $this->db->escape_like_str($search) . '%")');
        }

        // Filter by duration
        if (!empty($duration_filter)) {
            if ($duration_filter === 'singkat') {
                $this->db->where('ph.rata_rata_hari < 7');
            } elseif ($duration_filter === 'normal') {
                $this->db->where('ph.rata_rata_hari >= 7 AND ph.rata_rata_hari <= 14');
            } elseif ($duration_filter === 'panjang') {
                $this->db->where('ph.rata_rata_hari > 14');
            }
        }

        return (int) $this->db->count_all_results();
    }

    /**
     * Get data periode haid by NIM
     */
    public function get_by_nim($nim)
    {
        return $this->db
            ->where('nim', trim($nim))
            ->get(self::TABLE)
            ->row_array();
    }

    /**
     * Get data periode haid by ID
     */
    public function get_by_id($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->get(self::TABLE)
            ->row_array();
    }

    /**
     * Get list santri yang BELUM punya data periode haid
     */
    public function get_santri_without_data()
    {
        $query = "
            SELECT u.nim, m.NMMHSMSMHS AS nama
            FROM users u
            LEFT JOIN sim_akademik.msmhs m ON TRIM(m.NIMHSMSMHS) = u.nim
            LEFT JOIN " . self::TABLE . " ph ON ph.nim = u.nim
            WHERE u.role = 'user' AND ph.id IS NULL
            ORDER BY m.NMMHSMSMHS ASC
        ";
        return $this->db->query($query)->result_array();
    }

    /**
     * Get list santri yang SUDAH punya data periode haid
     */
    public function get_santri_with_data($limit = 0, $offset = 0)
    {
        $query = "
            SELECT ph.id, ph.nim, ph.rata_rata_hari, ph.paling_lama_hari, 
                   ph.created_at, ph.updated_at, ph.created_by, ph.updated_by,
                   m.NMMHSMSMHS AS nama
            FROM " . self::TABLE . " ph
            JOIN sim_akademik.msmhs m ON TRIM(m.NIMHSMSMHS) = ph.nim
            ORDER BY ph.created_at DESC
        ";
        
        if ($limit > 0) {
            $query .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }
        
        return $this->db->query($query)->result_array();
    }

    /**
     * Count santri yang SUDAH punya data periode haid
     */
    public function count_with_data()
    {
        return (int) $this->db
            ->from(self::TABLE)
            ->count_all_results();
    }

    /**
     * Create data periode haid
     */
    public function create($nim, $rata_rata_hari, $paling_lama_hari, $created_by)
    {
        // Validasi
        $error = $this->validate_input($rata_rata_hari, $paling_lama_hari);
        if ($error) {
            return array('ok' => FALSE, 'message' => $error);
        }

        // Cek apakah sudah ada
        if ($this->get_by_nim($nim)) {
            return array('ok' => FALSE, 'message' => 'Data periode haid untuk santri ini sudah ada.');
        }

        $payload = array(
            'nim' => trim($nim),
            'rata_rata_hari' => (int) $rata_rata_hari,
            'paling_lama_hari' => (int) $paling_lama_hari,
            'created_by' => trim($created_by),
            'created_at' => date('Y-m-d H:i:s'),
        );

        $ok = $this->db->insert(self::TABLE, $payload);
        if (!$ok) {
            return array('ok' => FALSE, 'message' => 'Gagal menyimpan data periode haid.');
        }

        // Ambil nama santri untuk flash message
        $santri = $this->db->select('NMMHSMSMHS AS nama')
            ->from('sim_akademik.msmhs')
            ->where('TRIM(NIMHSMSMHS)', trim($nim))
            ->get()
            ->row_array();
        $nama = isset($santri['nama']) ? $santri['nama'] : $nim;

        return array(
            'ok' => TRUE,
            'message' => 'Data periode haid untuk ' . html_escape($nama) . ' berhasil disimpan.'
        );
    }

    /**
     * Update data periode haid
     */
    public function update($id, $rata_rata_hari, $paling_lama_hari, $updated_by)
    {
        // Validasi
        $error = $this->validate_input($rata_rata_hari, $paling_lama_hari);
        if ($error) {
            return array('ok' => FALSE, 'message' => $error);
        }

        $data = $this->get_by_id($id);
        if (!$data) {
            return array('ok' => FALSE, 'message' => 'Data periode haid tidak ditemukan.');
        }

        $payload = array(
            'rata_rata_hari' => (int) $rata_rata_hari,
            'paling_lama_hari' => (int) $paling_lama_hari,
            'updated_by' => trim($updated_by),
            'updated_at' => date('Y-m-d H:i:s'),
        );

        $ok = $this->db
            ->where('id', (int) $id)
            ->update(self::TABLE, $payload);

        if (!$ok) {
            return array('ok' => FALSE, 'message' => 'Gagal mengupdate data periode haid.');
        }

        // Ambil nama santri untuk flash message
        $santri = $this->db->select('NMMHSMSMHS AS nama')
            ->from('sim_akademik.msmhs')
            ->where('TRIM(NIMHSMSMHS)', $data['nim'])
            ->get()
            ->row_array();
        $nama = isset($santri['nama']) ? $santri['nama'] : $data['nim'];

        return array(
            'ok' => TRUE,
            'message' => 'Data periode haid untuk ' . html_escape($nama) . ' berhasil diupdate.'
        );
    }

    /**
     * Delete data periode haid
     */
    public function delete($id)
    {
        $data = $this->get_by_id($id);
        if (!$data) {
            return array('ok' => FALSE, 'message' => 'Data periode haid tidak ditemukan.');
        }

        $ok = $this->db
            ->where('id', (int) $id)
            ->delete(self::TABLE);

        if (!$ok) {
            return array('ok' => FALSE, 'message' => 'Gagal menghapus data periode haid.');
        }

        // Ambil nama santri untuk flash message
        $santri = $this->db->select('NMMHSMSMHS AS nama')
            ->from('sim_akademik.msmhs')
            ->where('TRIM(NIMHSMSMHS)', $data['nim'])
            ->get()
            ->row_array();
        $nama = isset($santri['nama']) ? $santri['nama'] : $data['nim'];

        return array(
            'ok' => TRUE,
            'message' => 'Data periode haid untuk ' . html_escape($nama) . ' berhasil dihapus.'
        );
    }

    /**
     * Validate input
     */
    private function validate_input($rata_rata_hari, $paling_lama_hari)
    {
        $rr = (int) $rata_rata_hari;
        $pm = (int) $paling_lama_hari;

        if ($rr < self::MIN_HARI || $rr > self::MAX_HARI) {
            return "Rata-rata hari harus antara " . self::MIN_HARI . " - " . self::MAX_HARI . " hari.";
        }

        if ($pm < self::MIN_HARI || $pm > self::MAX_HARI) {
            return "Paling lama hari harus antara " . self::MIN_HARI . " - " . self::MAX_HARI . " hari.";
        }

        if ($pm < $rr) {
            return "Paling lama hari harus lebih besar atau sama dengan rata-rata hari.";
        }

        return null;
    }

    /**
     * Check if santri has periode haid data
     */
    public function has_data($nim)
    {
        return (bool) $this->get_by_nim($nim);
    }
}
