<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perizinan_model extends CI_Model
{
    public function is_haid_alasan($alasan)
    {
        return stripos((string) $alasan, 'haid') !== FALSE;
    }

    public function is_sakit_alasan($alasan)
    {
        return stripos((string) $alasan, 'sakit') !== FALSE;
    }

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
                'PKL disekolah',
                'PKL di Masyarakat',
                'Pulang Kampung',
                'Sakit'
            );
        }

        $has_haid = FALSE;
        foreach ($options as $option) {
            if (stripos((string) $option, 'haid') !== FALSE) {
                $has_haid = TRUE;
                break;
            }
        }
        if (!$has_haid) {
            $options[] = 'Haid';
        }

        return $options;
    }

    public function list_for_admin()
    {
        $this->db->select('i.id, i.nim, i.tgl_ajuan, i.tgl_mulai, i.tgl_selesai, i.alasan, i.kamar, i.acc, i.is_suspended, i.approved_by, i.approved_at, i.approval_note, m.NMMHSMSMHS AS nama');
        $this->db->from('ijin i');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = i.nim', 'left');
        $this->db->order_by('i.tgl_ajuan', 'DESC');
        return $this->db->get()->result_array();
    }

    public function count_for_admin_history(array $filters = array())
    {
        $this->build_admin_history_query($filters);
        return (int) $this->db->count_all_results();
    }

    public function list_for_admin_history(array $filters = array(), $limit = 10, $offset = 0)
    {
        $this->build_admin_history_query($filters);
        $this->db->select('i.id, i.nim, i.tgl_ajuan, i.tgl_mulai, i.tgl_selesai, i.alasan, i.kamar, i.acc, i.status, i.is_suspended, i.suspended_at, i.suspended_by, i.suspended_note, i.resumed_at, i.resumed_by, i.resumed_note, i.file_upload, i.file_upload_at, i.approved_by, i.approved_at, i.approval_note, m.NMMHSMSMHS AS nama');
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result_array();
    }

    public function count_for_user_history($nim)
    {
        return (int) $this->db->where('nim', trim($nim))->count_all_results('ijin');
    }

    public function list_for_user_history($nim, $limit = 10, $offset = 0)
    {
        $this->db->order_by('tgl_ajuan', 'DESC');
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get_where('ijin', array('nim' => trim($nim)))->result_array();
    }

    public function list_for_user($nim)
    {
        return $this->db
            ->order_by('tgl_ajuan', 'DESC')
            ->get_where('ijin', array('nim' => trim($nim)))
            ->result_array();
    }

    public function create_pengajuan($nim, $tgl_mulai, $tgl_selesai, $alasan, $smt)
    {
        $nim = trim($nim);
        $id = $nim . date('YmdHis');
        $kamar = $this->get_kamar_santri($nim);
        $is_haid = $this->is_haid_alasan($alasan);
        $is_sakit = $this->is_sakit_alasan($alasan);

        $payload = array(
            'id' => $id,
            'nim' => $nim,
            'tgl_ajuan' => date('Y-m-d'),
            'tgl_mulai' => $tgl_mulai,
            'tgl_selesai' => $tgl_selesai,
            'ket' => '',
            'kamar' => (string) $kamar,
            'acc' => '0',
            'status' => $is_haid ? '2' : ($is_sakit ? '1' : '0'),
            'smt' => (int) $smt,
            'alasan' => $alasan
        );

        $ok = $this->db->insert('ijin', $payload);
        if (!$ok) {
            return FALSE;
        }

        $this->insert_detail_dates($id, $nim, $tgl_mulai, $tgl_selesai);
        return $id;
    }

    public function get_izin_for_print($id, $nim)
    {
        return $this->db
            ->select(
                'i.id, i.nim, i.tgl_mulai, i.tgl_selesai, i.alasan, i.smt, i.acc, i.approved_at, '
                . 'm.NMMHSMSMHS AS nama, m.ALAMATLENGKAP AS alamat, '
                . "COALESCE(NULLIF(TRIM(m.JURUSAN), ''), NULLIF(TRIM(m.KDPSTMSMHS), ''), '-') AS prodi, "
                . 'COALESCE(NULLIF(TRIM(s.kmr), \'\'), i.kamar) AS kamar',
                FALSE
            )
            ->from('ijin i')
            ->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = i.nim', 'left')
            ->join('mssantri s', 'TRIM(s.nim) = i.nim', 'left')
            ->where('i.id', $id)
            ->where('i.nim', trim($nim))
            ->get()
            ->row_array();
    }

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

    public function get_izin_by_id_and_nim($id, $nim)
    {
        return $this->db
            ->select('i.id, i.nim, i.tgl_ajuan, i.tgl_mulai, i.tgl_selesai, i.alasan, i.smt, i.status, i.file_upload, i.file_upload_at')
            ->from('ijin i')
            ->where('i.id', $id)
            ->where('i.nim', trim($nim))
            ->get()
            ->row_array();
    }

    public function mark_waiting_upload($id, $nim)
    {
        return $this->db
            ->where('id', $id)
            ->where('nim', trim($nim))
            ->where('status', '0')
            ->update('ijin', array('status' => '1'));
    }

    public function upload_surat_file($id, $nim, $filename)
    {
        $izin = $this->get_izin_by_id_and_nim($id, $nim);
        if (!$izin) {
            return array('ok' => FALSE, 'message' => 'Pengajuan tidak ditemukan.');
        }

        if (!in_array((string) $izin['status'], array('0', '1', '4'), TRUE)) {
            return array('ok' => FALSE, 'message' => 'Status pengajuan tidak memungkinkan upload pada tahap ini.');
        }

        return $this->db
            ->where('id', $id)
            ->where('nim', trim($nim))
            ->update('ijin', array(
                'file_upload' => $filename,
                'file_upload_at' => date('Y-m-d H:i:s'),
                'status' => '2'
            )) ? array('ok' => TRUE) : array('ok' => FALSE, 'message' => 'Gagal menyimpan upload file.');
    }

    public function list_for_admin_validation()
    {
        $this->db->select('i.id, i.nim, i.tgl_ajuan, i.tgl_mulai, i.tgl_selesai, i.alasan, i.kamar, i.status, i.file_upload, i.file_upload_at, m.NMMHSMSMHS AS nama');
        $this->db->from('ijin i');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = i.nim', 'left');
        $this->db->where_in('i.status', array('2', '3', '4'));
        $this->db->order_by('COALESCE(i.file_upload_at, CONCAT(i.tgl_ajuan, " 00:00:00"))', 'DESC', FALSE);
        $this->db->order_by('i.id', 'DESC');
        return $this->db->get()->result_array();
    }

    public function delete_history(array $ids)
    {
        $ids = array_values(array_filter(array_map('trim', $ids)));
        if (empty($ids)) {
            return array('ok' => FALSE, 'message' => 'Tidak ada data yang dipilih.');
        }

        $rows = $this->db
            ->select('id, nim, file_upload')
            ->from('ijin')
            ->where_in('id', $ids)
            ->get()
            ->result_array();

        if (empty($rows)) {
            return array('ok' => FALSE, 'message' => 'Data tidak ditemukan.');
        }

        $upload_dir = FCPATH . 'uploads/perizinan/';

        $this->db->trans_begin();

        $this->db->where_in('id', $ids)->delete('ijindetail');
        $this->db->where_in('id', $ids)->delete('ijin');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('ok' => FALSE, 'message' => 'Gagal menghapus data.');
        }

        $this->db->trans_commit();

        foreach ($rows as $row) {
            if (!empty($row['file_upload'])) {
                $file_path = $upload_dir . $row['file_upload'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }

        return array('ok' => TRUE, 'message' => 'Riwayat berhasil dihapus.');
    }

    public function validate_upload($id, $decision)
    {
        $decision = (string) $decision;
        if (!in_array($decision, array('3', '4'), TRUE)) {
            return FALSE;
        }

        $izin = $this->db->get_where('ijin', array('id' => $id), 1)->row_array();
        if (!$izin || (string) $izin['status'] !== '2') {
            return FALSE;
        }

        return $this->db
            ->where('id', $id)
            ->update('ijin', array(
                'status' => $decision,
                'acc' => $decision === '3' ? '1' : '2',
                'approved_at' => $decision === '3' ? date('Y-m-d H:i:s') : null
            ));
    }

    public function toggle_izin_suspension($id, $suspend, $admin_nim, $note = null)
    {
        $izin = $this->db->get_where('ijin', array('id' => $id), 1)->row_array();
        if (!$izin || (string) $izin['acc'] !== '1') {
            return array('ok' => FALSE, 'message' => 'Hanya izin yang sudah disetujui yang bisa diubah statusnya.');
        }

        $suspend = (bool) $suspend;
        $currentSuspended = !empty($izin['is_suspended']) && (int) $izin['is_suspended'] === 1;
        if ($suspend === $currentSuspended) {
            return array('ok' => TRUE, 'message' => $suspend ? 'Izin sudah dalam kondisi diselesaikan sementara.' : 'Izin sudah aktif kembali.');
        }

        $payload = array(
            'is_suspended' => $suspend ? 1 : 0
        );

        if ($suspend) {
            $payload['suspended_at'] = date('Y-m-d H:i:s');
            $payload['suspended_by'] = trim((string) $admin_nim);
            $payload['suspended_note'] = $note !== null ? trim((string) $note) : 'Izin diselesaikan sementara oleh admin.';
        } else {
            $payload['resumed_at'] = date('Y-m-d H:i:s');
            $payload['resumed_by'] = trim((string) $admin_nim);
            $payload['resumed_note'] = $note !== null ? trim((string) $note) : 'Izin dilanjutkan kembali oleh admin.';
        }

        $ok = $this->db->where('id', $id)->update('ijin', $payload);
        if (!$ok) {
            return array('ok' => FALSE, 'message' => 'Gagal memperbarui status izin.');
        }

        return array(
            'ok' => TRUE,
            'message' => $suspend ? 'Izin berhasil diselesaikan sementara.' : 'Izin berhasil dilanjutkan.'
        );
    }

    private function build_admin_history_query(array $filters = array())
    {
        $this->db->from('ijin i');
        $this->db->join('sim_akademik.msmhs m', 'TRIM(m.NIMHSMSMHS) = i.nim', 'left');

        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $this->db->group_start();
            $this->db->like('i.nim', $q);
            $this->db->or_like('m.NMMHSMSMHS', $q);
            $this->db->group_end();
        }

        if (isset($filters['status']) && $filters['status'] !== '' && in_array((string) $filters['status'], array('0', '1', '2', '3', '4'), TRUE)) {
            $this->db->where('i.status', (string) $filters['status']);
        }

        $this->db->order_by('i.tgl_ajuan', 'DESC');
        $this->db->order_by('i.id', 'DESC');
    }

    public function reapply_haid_same_record($id, $nim)
    {
        $izin = $this->get_izin_by_id_and_nim($id, $nim);
        if (!$izin || !$this->is_haid_alasan($izin['alasan']) || (string) $izin['status'] !== '4') {
            return FALSE;
        }

        return $this->db
            ->where('id', $id)
            ->where('nim', trim($nim))
            ->update('ijin', array(
                'status' => '2',
                'acc' => '0',
                'approved_at' => null,
                'approval_note' => null,
                'approved_by' => null
            ));
    }

    public function update_status($id, $status, $approved_by, $approval_note = null)
    {
        return $this->db
            ->where('id', $id)
            ->update('ijin', array(
                'acc' => $status,
                'approved_by' => $approved_by,
                'approved_at' => date('Y-m-d H:i:s'),
                'approval_note' => $approval_note
            ));
    }

    private function insert_detail_dates($id_ijin, $nim, $tgl_mulai, $tgl_selesai)
    {
        try {
            $start = new DateTime($tgl_mulai);
            $end = new DateTime($tgl_selesai);
        } catch (Exception $e) {
            return;
        }

        if ($end < $start) {
            return;
        }

        $rows = array();
        while ($start <= $end) {
            $rows[] = array(
                'id_ijin' => $id_ijin,
                'tgl_detail' => $start->format('Y-m-d'),
                'nim_detail' => $nim
            );
            $start->modify('+1 day');
        }

        if (!empty($rows)) {
            $this->db->insert_batch('ijindetail', $rows);
        }
    }
}
