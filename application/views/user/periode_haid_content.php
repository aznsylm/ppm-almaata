<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-dark">Data Periode Haid Saya</h4>
    <div class="text-muted small">Informasi periode haid Anda yang telah dicatat oleh admin.</div>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="<?php echo site_url('user/dashboard'); ?>" class="btn btn-outline-primary btn-sm">Kembali</a>
  </div>
</div>

<?php if (empty($data_haid)): ?>
  <div class="alert alert-info alert-dismissible fade show">
    Data belum diinput. Hubungi admin untuk menginputkan data periode haid Anda.
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php else: ?>
  <div class="row">
    <div class="col-md-6">
      <div class="card border-left-info">
        <div class="card-body">
          <h6 class="text-uppercase text-muted small mb-3">Rata-rata Normal Haid</h6>
          <div class="display-5 font-weight-bold text-info">
            <?php echo (int) $data_haid['rata_rata_hari']; ?>
            <span class="h5 text-muted"> hari</span>
          </div>
          <div class="text-muted small mt-2">Lama periode haid Anda biasanya</div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-left-warning">
        <div class="card-body">
          <h6 class="text-uppercase text-muted small mb-3">Paling Lama Haid</h6>
          <div class="display-5 font-weight-bold text-warning">
            <?php echo (int) $data_haid['paling_lama_hari']; ?>
            <span class="h5 text-muted"> hari</span>
          </div>
          <div class="text-muted small mt-2">Durasi maksimal periode haid Anda</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-4">
    <div class="card-body">
      <p class="text-muted small mb-0">
        Data ini bersifat informasi FYI (For Your Information). 
        Jika ada perubahan atau koreksi, silakan hubungi admin.
      </p>
    </div>
  </div>
<?php endif; ?>
