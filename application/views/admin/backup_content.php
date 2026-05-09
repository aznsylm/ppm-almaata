<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-gray-800">Backup Data Perizinan</h4>
    <div class="small-text">Export data izin ke CSV dan hapus data lama.</div>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="<?php echo site_url('admin/perizinan'); ?>" class="btn btn-outline-primary btn-sm">Kembali</a>
  </div>
</div>

<?php if ($this->session->flashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?php echo $this->session->flashdata('success'); ?>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <?php echo $this->session->flashdata('error'); ?>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>

<div class="row">
  <div class="col-lg-6">

    <!-- Form Pilih Periode -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white"><strong>Pilih Periode Backup</strong></div>
      <div class="card-body">
        <form method="get" action="<?php echo site_url('admin/backup'); ?>" id="form_preview">
          <div class="form-row">
            <div class="col-md-6 mb-3">
              <label class="small mb-1">Dari Tanggal</label>
              <input type="date" name="tgl_dari" class="form-control"
                     value="<?php echo html_escape($tgl_dari); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="small mb-1">Sampai Tanggal</label>
              <input type="date" name="tgl_sampai" class="form-control"
                     value="<?php echo html_escape($tgl_sampai); ?>" required>
            </div>
          </div>
          <small class="text-muted d-block mb-3">Maksimal rentang 3 bulan per backup.</small>
          <button type="submit" class="btn btn-primary btn-sm">Cek Jumlah Data</button>
        </form>
      </div>
    </div>

    <!-- Preview & Aksi -->
    <?php if ($jumlah !== null): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Hasil Pengecekan</strong></div>
        <div class="card-body">
          <p class="mb-1">Periode: <strong><?php echo html_escape($tgl_dari); ?></strong> s/d <strong><?php echo html_escape($tgl_sampai); ?></strong></p>
          <p class="mb-3">Jumlah data: <strong><?php echo $jumlah; ?> izin</strong></p>

          <?php if ($jumlah > 0): ?>
            <!-- Tombol Download CSV -->
            <a href="<?php echo site_url('admin/backup/download?tgl_dari=' . urlencode($tgl_dari) . '&tgl_sampai=' . urlencode($tgl_sampai)); ?>"
               class="btn btn-success btn-sm mb-3">
              <i class="fas fa-download"></i> Download CSV
            </a>

            <hr>

            <!-- Form Hapus Data -->
            <p class="text-danger small mb-2">
              <i class="fas fa-exclamation-triangle"></i>
              Hapus data akan <strong>permanen</strong> dan tidak bisa dikembalikan.
              Pastikan sudah download CSV sebelum menghapus.
            </p>
            <form method="post" action="<?php echo site_url('admin/backup/hapus'); ?>"
                  onsubmit="return confirm('Yakin hapus <?php echo $jumlah; ?> data izin periode ini? Tindakan tidak bisa dibatalkan!');">
              <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
              <input type="hidden" name="tgl_dari" value="<?php echo html_escape($tgl_dari); ?>">
              <input type="hidden" name="tgl_sampai" value="<?php echo html_escape($tgl_sampai); ?>">
              <div class="form-group mb-2">
                <label class="small">Ketik <strong>HAPUS</strong> untuk konfirmasi</label>
                <input type="text" name="konfirmasi" class="form-control form-control-sm"
                       placeholder="Ketik HAPUS" autocomplete="off" required>
              </div>
              <button type="submit" class="btn btn-danger btn-sm">
                <i class="fas fa-trash"></i> Hapus Data Periode Ini
              </button>
            </form>
          <?php else: ?>
            <div class="alert alert-info mb-0">Tidak ada data pada periode tersebut.</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>

  <!-- Panduan -->
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-header bg-white"><strong>Panduan Backup</strong></div>
      <div class="card-body">
        <ol class="pl-3 small">
          <li class="mb-2">Pilih rentang tanggal periode yang ingin dibackup (maks. 3 bulan).</li>
          <li class="mb-2">Klik <strong>Cek Jumlah Data</strong> untuk melihat berapa data yang akan dibackup.</li>
          <li class="mb-2">Klik <strong>Download CSV</strong> untuk mengunduh file backup. File bisa dibuka di Excel.</li>
          <li class="mb-2">Setelah yakin data sudah tersimpan, ketik <strong>HAPUS</strong> dan klik tombol hapus untuk membersihkan data lama dari database.</li>
        </ol>
        <hr>
        <p class="small text-muted mb-1"><strong>Data yang dibackup:</strong></p>
        <ul class="pl-3 small text-muted">
          <li>ID Izin, NIM, Nama, Program Studi, No. Kamar, Semester</li>
          <li>Jenis Izin, Kategori, Alasan, Alasan Lainnya</li>
          <li>Tanggal Pengajuan, Tanggal Mulai, Tanggal Selesai</li>
        </ul>
      </div>
    </div>
  </div>
</div>
