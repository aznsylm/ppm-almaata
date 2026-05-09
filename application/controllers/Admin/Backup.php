<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Backup extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Perizinan_model');
    }

    public function index()
    {
        $this->require_role(array('admin'));

        $tgl_dari   = trim((string) $this->input->get('tgl_dari', TRUE));
        $tgl_sampai = trim((string) $this->input->get('tgl_sampai', TRUE));
        $jumlah     = null;

        if ($tgl_dari !== '' && $tgl_sampai !== '' && $this->is_valid_date($tgl_dari) && $this->is_valid_date($tgl_sampai)) {
            $jumlah = $this->Perizinan_model->count_for_backup($tgl_dari, $tgl_sampai);
        }

        $data = array(
            'page_title'   => 'Backup Data Perizinan',
            'content_view' => 'admin/backup_content',
            'content_data' => array(
                'tgl_dari'   => $tgl_dari,
                'tgl_sampai' => $tgl_sampai,
                'jumlah'     => $jumlah,
            ),
        );

        $this->load->view('layouts/admin', $data);
    }

    public function download()
    {
        $this->require_role(array('admin'));

        $tgl_dari   = trim((string) $this->input->get('tgl_dari', TRUE));
        $tgl_sampai = trim((string) $this->input->get('tgl_sampai', TRUE));

        if (!$this->is_valid_date($tgl_dari) || !$this->is_valid_date($tgl_sampai)) {
            $this->session->set_flashdata('error', 'Rentang tanggal tidak valid.');
            redirect('admin/backup');
            return;
        }

        if ($tgl_dari > $tgl_sampai) {
            $this->session->set_flashdata('error', 'Tanggal dari tidak boleh lebih besar dari tanggal sampai.');
            redirect('admin/backup');
            return;
        }

        // Batasi maksimal 3 bulan
        $diff = (strtotime($tgl_sampai) - strtotime($tgl_dari)) / 86400;
        if ($diff > 92) {
            $this->session->set_flashdata('error', 'Rentang backup maksimal 3 bulan sekaligus.');
            redirect('admin/backup');
            return;
        }

        $rows = $this->Perizinan_model->get_for_backup($tgl_dari, $tgl_sampai);

        if (empty($rows)) {
            $this->session->set_flashdata('error', 'Tidak ada data pada periode tersebut.');
            redirect('admin/backup');
            return;
        }

        // Set memory & time limit untuk data besar
        @ini_set('memory_limit', '256M');
        @set_time_limit(120);

        $filename = 'backup_izin_' . $tgl_dari . '_sd_' . $tgl_sampai . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM agar Excel bisa baca karakter Indonesia
        fputs($output, "\xEF\xBB\xBF");

        // Header kolom
        fputcsv($output, array_keys($rows[0]), ';');

        // Data rows
        foreach ($rows as $row) {
            fputcsv($output, array_values($row), ';');
        }

        fclose($output);
        exit;
    }

    public function hapus()
    {
        $this->require_role(array('admin'));

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $tgl_dari   = trim((string) $this->input->post('tgl_dari', TRUE));
        $tgl_sampai = trim((string) $this->input->post('tgl_sampai', TRUE));
        $konfirmasi = trim((string) $this->input->post('konfirmasi', TRUE));

        if (!$this->is_valid_date($tgl_dari) || !$this->is_valid_date($tgl_sampai)) {
            $this->session->set_flashdata('error', 'Rentang tanggal tidak valid.');
            redirect('admin/backup');
            return;
        }

        if ($konfirmasi !== 'HAPUS') {
            $this->session->set_flashdata('error', 'Ketik HAPUS untuk konfirmasi penghapusan.');
            redirect('admin/backup?tgl_dari=' . urlencode($tgl_dari) . '&tgl_sampai=' . urlencode($tgl_sampai));
            return;
        }

        $result = $this->Perizinan_model->delete_by_periode($tgl_dari, $tgl_sampai);

        if ($result['ok']) {
            $this->session->set_flashdata('success', 'Berhasil menghapus ' . $result['deleted'] . ' data izin periode ' . $tgl_dari . ' s/d ' . $tgl_sampai . '.');
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('admin/backup');
    }

    private function is_valid_date($date)
    {
        if (empty($date)) return FALSE;
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
