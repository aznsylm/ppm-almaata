<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-gray-800"><?php echo html_escape(!empty($upload_title) ? $upload_title : 'Upload Surat Izin'); ?></h4>
    <div class="small-text"><?php echo html_escape(!empty($upload_subtitle) ? $upload_subtitle : 'Upload surat yang sudah ditandatangani oleh pimpinan pondok.'); ?></div>
  </div>
  <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row align-self-stretch">
    <a href="<?php echo site_url('user/perizinan'); ?>" class="btn btn-outline-primary btn-sm text-center">Kembali</a>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-header bg-white"><strong>Form Upload Surat</strong></div>
      <div class="card-body">
        <div class="alert alert-info mb-3">
          <i class="fas fa-info-circle mr-2"></i>
          <?php echo html_escape(!empty($upload_notice) ? $upload_notice : 'Pastikan surat sudah ditandatangani tangan oleh pimpinan pondok sebelum diupload.'); ?>
        </div>

        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

          <div class="form-group">
            <label>Data Izin</label>
            <div class="border p-3 bg-light rounded">
              <div class="row">
                <div class="col-md-6">
                  <small class="text-muted">Tanggal Pengajuan</small>
                  <div class="font-weight-bold"><?php echo html_escape($izin['tgl_ajuan']); ?></div>
                </div>
                <div class="col-md-6">
                  <small class="text-muted">Periode Izin</small>
                  <div class="font-weight-bold"><?php echo html_escape($izin['tgl_mulai']); ?> s/d <?php echo html_escape($izin['tgl_selesai']); ?></div>
                </div>
                <div class="col-md-6 mt-3">
                  <small class="text-muted">Alasan</small>
                  <div class="font-weight-bold"><?php echo html_escape($izin['alasan']); ?></div>
                </div>
                <div class="col-md-6 mt-3">
                  <small class="text-muted">Status</small>
                  <div>
                    <?php if ($izin['status'] === '0'): ?>
                      <span class="badge badge-info">Siap Cetak</span>
                    <?php elseif ($izin['status'] === '1'): ?>
                      <span class="badge badge-warning">Menunggu Upload</span>
                    <?php elseif ($izin['status'] === '2'): ?>
                      <span class="badge badge-secondary">Menunggu Validasi</span>
                    <?php elseif ($izin['status'] === '3'): ?>
                      <span class="badge badge-success">Disetujui</span>
                    <?php else: ?>
                      <span class="badge badge-danger">Ditolak</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>File Surat (PDF)*</label>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="surat_file" name="surat_file" accept=".pdf" required>
              <label class="custom-file-label" for="surat_file">Pilih file PDF...</label>
            </div>
            <small class="form-text text-muted d-block mt-2">
              <?php echo html_escape(!empty($upload_file_hint) ? $upload_file_hint : 'Format: PDF | Ukuran maksimal: 2 MB'); ?>
            </small>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Upload Surat</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card shadow-sm bg-light">
      <div class="card-header bg-white"><strong>Info Penting</strong></div>
      <div class="card-body">
        <div class="mb-3">
          <strong class="d-block mb-2">Langkah-langkah:</strong>
          <ol class="pl-3 mb-0">
            <?php if (!empty($is_sakit)): ?>
              <li>Siapkan surat keterangan sakit dalam format PDF</li>
              <li>Pastikan dokumen sudah lengkap dan jelas</li>
              <li>Upload file PDF di form ini</li>
            <?php else: ?>
              <li>Klik "Kembali" dan download surat</li>
              <li>Cetak surat dari file PDF</li>
              <li>Minta tanda tangan pimpinan pondok</li>
              <li>Scan atau foto surat yang sudah ditandatangani</li>
              <li>Convert kembali ke PDF jika perlu</li>
            <?php endif; ?>
          </ol>
        </div>
        <div class="alert alert-warning">
          <small>
            <strong>Catatan:</strong> Jika surat ditolak admin, kamu bisa upload ulang tanpa harus ajukan izin baru.
          </small>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('surat_file').addEventListener('change', function(e) {
  var label = document.querySelector('.custom-file-label');
  if (this.files && this.files[0]) {
    label.textContent = this.files[0].name;
  } else {
    label.textContent = 'Pilih file PDF...';
  }
});
</script>
