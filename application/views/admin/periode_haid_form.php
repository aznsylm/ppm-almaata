<div class="row">
  <div class="col-md-6 mx-auto">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <?php echo ($mode === 'create') ? 'Tambah Data Periode Haid' : 'Edit Data Periode Haid'; ?>
        </h5>
      </div>
      <div class="card-body">
        
        <?php if ($this->session->flashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
          </div>
        <?php endif; ?>

        <?php if ($mode === 'create'): ?>
          <form method="post" action="<?php echo site_url('admin/periode-haid/store'); ?>">
        <?php else: ?>
          <form method="post" action="<?php echo site_url('admin/periode-haid/update/' . $row['id']); ?>">
        <?php endif; ?>

          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

          <!-- SANTRI (hanya di CREATE) -->
          <?php if ($mode === 'create'): ?>
            <div class="form-group">
              <label><strong>Santri</strong> <span class="text-danger">*</span></label>
              <select name="nim" id="nim" class="form-control" required>
                <option value="">-- Pilih Santri --</option>
                <?php foreach ($santri_list as $s): ?>
                  <option value="<?php echo html_escape($s['nim']); ?>">
                    <?php echo html_escape($s['nama']); ?> (<?php echo html_escape($s['nim']); ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted">Hanya menampilkan santri yang belum memiliki data periode haid.</small>
            </div>
          <?php else: ?>
            <div class="form-group">
              <label><strong>Santri</strong></label>
              <div class="form-control" style="background-color:#f5f5f5; border:1px solid #ddd;">
                <strong><?php echo html_escape(!empty($row['nama']) ? $row['nama'] : $row['nim']); ?></strong><br>
                <small class="text-muted"><?php echo html_escape($row['nim']); ?></small>
              </div>
              <small class="text-muted d-block mt-1">NIM tidak dapat diubah</small>
              <input type="hidden" name="nim" value="<?php echo html_escape($row['nim']); ?>">
            </div>
          <?php endif; ?>

          <!-- RATA-RATA HARI -->
          <div class="form-group">
            <label><strong>Rata-rata Normal Haid</strong> <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="rata_rata_hari" id="rata_rata_hari" class="form-control" 
                     min="1" max="50" value="<?php echo ($mode === 'edit') ? (int) $row['rata_rata_hari'] : ''; ?>" required>
              <div class="input-group-append">
                <span class="input-group-text">hari</span>
              </div>
            </div>
            <small class="text-muted d-block mt-1">Rentang: 1 - 50 hari</small>
          </div>

          <!-- PALING LAMA HARI -->
          <div class="form-group">
            <label><strong>Paling Lama Haid</strong> <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="paling_lama_hari" id="paling_lama_hari" class="form-control" 
                     min="1" max="50" value="<?php echo ($mode === 'edit') ? (int) $row['paling_lama_hari'] : ''; ?>" required>
              <div class="input-group-append">
                <span class="input-group-text">hari</span>
              </div>
            </div>
            <small class="text-muted d-block mt-1">Harus lebih besar atau sama dengan rata-rata. Rentang: 1 - 50 hari</small>
          </div>

          <!-- BUTTON -->
          <div class="d-flex gap-2 mt-4" style="gap:6px;">
            <button type="submit" class="btn btn-primary flex-fill">
              Simpan
            </button>
            <a href="<?php echo site_url('admin/periode-haid'); ?>" class="btn btn-outline-secondary">
              Batal
            </a>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var rataRataInput = document.getElementById('rata_rata_hari');
  var paliingLomaInput = document.getElementById('paling_lama_hari');
  var form = document.querySelector('form');

  // Real-time validation: paling_lama >= rata_rata
  function validateForm() {
    var rr = parseInt(rataRataInput.value) || 0;
    var pm = parseInt(paliingLomaInput.value) || 0;

    if (rr > 0 && pm > 0 && pm < rr) {
      paliingLomaInput.classList.add('is-invalid');
      paliingLomaInput.title = 'Paling lama hari harus >= rata-rata hari';
    } else {
      paliingLomaInput.classList.remove('is-invalid');
      paliingLomaInput.title = '';
    }
  }

  if (rataRataInput && paliingLomaInput) {
    rataRataInput.addEventListener('change', validateForm);
    paliingLomaInput.addEventListener('change', validateForm);
    paliingLomaInput.addEventListener('blur', validateForm);
  }

  // Prevent submit jika invalid
  if (form) {
    form.addEventListener('submit', function (e) {
      validateForm();
      if (paliingLomaInput.classList.contains('is-invalid')) {
        e.preventDefault();
        alert('Paling lama hari harus lebih besar atau sama dengan rata-rata hari.');
        return false;
      }
    });
  }
})();
</script>
