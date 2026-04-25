<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-gray-800">Perizinan Santri</h4>
    <div class="small-text">Ajukan izin, cetak surat, upload, dan pantau status.</div>
  </div>
  <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row align-self-stretch">
    <a href="<?php echo site_url('user/dashboard'); ?>" class="btn btn-outline-primary btn-sm text-center">Dashboard</a>
  </div>
</div>

<div class="row">
  <div class="col-lg-5 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Ajukan Izin</strong></div>
      <div class="card-body">
        <?php if (validation_errors()): ?>
          <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
        <?php endif; ?>
        <form method="post" action="<?php echo site_url('user/perizinan/submit'); ?>">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
          <div class="form-group">
            <label>Tanggal Mulai</label>
            <input type="date" name="tgl_mulai" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Tanggal Selesai</label>
            <input type="date" name="tgl_selesai" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Alasan</label>
            <select name="alasan_option" id="alasan_option" class="form-control" required>
              <option value="">-- Pilih Alasan --</option>
              <?php foreach ($alasan_options as $option): ?>
                <option value="<?php echo html_escape($option); ?>"><?php echo html_escape($option); ?></option>
              <?php endforeach; ?>
              <option value="__lainnya__">Lainnya...</option>
            </select>
          </div>
          <div class="form-group" id="alasan_custom_wrap" style="display:none;">
            <label>Alasan Lainnya</label>
            <textarea name="alasan_custom" id="alasan_custom" class="form-control" rows="3" minlength="5"></textarea>
          </div>
          <div class="alert alert-info py-2 px-3" id="haid_info" style="display:none;">
            Izin Haid tidak memerlukan surat unduh/upload. Pengajuan akan langsung masuk ke validasi admin.
            Durasi maksimal 14 hari (termasuk tanggal mulai dan tanggal selesai).
          </div>
          <div class="form-group">
            <label>Semester</label>
            <input type="number" name="smt" class="form-control" min="1" max="14" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block" id="submit_btn">Kirim Pengajuan</button>
          <small class="text-danger d-block mt-2" id="haid_error" style="display:none;"></small>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-7 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Riwayat Izin</strong></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Periode</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pengajuan izin.</td></tr>
              <?php else: ?>
                <?php foreach ($rows as $row): ?>
                  <tr>
                    <?php $is_haid = $this->Perizinan_model->is_haid_alasan($row['alasan']); ?>
                    <?php $is_sakit = $this->Perizinan_model->is_sakit_alasan($row['alasan']); ?>
                    <td><?php echo html_escape($row['tgl_mulai']); ?> s/d <?php echo html_escape($row['tgl_selesai']); ?></td>
                    <td><?php echo html_escape($row['alasan']); ?></td>
                    <td>
                      <strong><?php echo html_escape($status_map[$row['status']] ?? $row['status']); ?></strong>
                    </td>
                    <td>
                      <div class="btn-group btn-group-sm" role="group">
                        <?php if ($is_haid): ?>
                          <?php if ($row['status'] === '4'): ?>
                            <form method="post" action="<?php echo site_url('user/perizinan/reapply-haid/' . rawurlencode($row['id'])); ?>" style="display:inline;">
                              <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                              <button type="submit" class="btn btn-warning btn-sm">Ajukan Kembali</button>
                            </form>
                          <?php elseif ($row['status'] === '3'): ?>
                            <span class="text-muted small">Tanpa surat</span>
                          <?php else: ?>
                            <span class="text-muted small">-</span>
                          <?php endif; ?>
                        <?php elseif ($is_sakit): ?>
                          <?php if ($row['status'] === '0' || $row['status'] === '1' || $row['status'] === '4'): ?>
                            <a href="<?php echo site_url('user/perizinan/upload/' . rawurlencode($row['id'])); ?>" class="btn btn-warning btn-sm" title="Upload Surat Sakit">
                              <i class="fas fa-upload"></i>
                            </a>
                          <?php else: ?>
                            <span class="text-muted small">-</span>
                          <?php endif; ?>
                        <?php elseif ($row['status'] === '0' || $row['status'] === '1' || $row['status'] === '4'): ?>
                          <a href="<?php echo site_url('user/perizinan/download/' . rawurlencode($row['id'])); ?>" class="btn btn-info btn-sm" title="Download PDF">
                            <i class="fas fa-download"></i>
                          </a>
                          <a href="<?php echo site_url('user/perizinan/upload/' . rawurlencode($row['id'])); ?>" class="btn btn-warning btn-sm" title="Upload Surat">
                            <i class="fas fa-upload"></i>
                          </a>
                        <?php elseif ($row['status'] === '3'): ?>
                          <a href="<?php echo site_url('user/perizinan/cetak/' . rawurlencode($row['id'])); ?>" target="_blank" class="btn btn-success btn-sm">
                            <i class="fas fa-print"></i> Cetak
                          </a>
                        <?php else: ?>
                          <span class="text-muted small">-</span>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-end mt-2">
  <?php echo $pagination ?? ''; ?>
</div>

<script>
(function () {
  var select = document.getElementById('alasan_option');
  var wrap = document.getElementById('alasan_custom_wrap');
  var custom = document.getElementById('alasan_custom');
  var tglMulai = document.querySelector('input[name="tgl_mulai"]');
  var tglSelesai = document.querySelector('input[name="tgl_selesai"]');
  var infoHaid = document.getElementById('haid_info');
  var submitBtn = document.getElementById('submit_btn');
  var haidError = document.getElementById('haid_error');

  function isHaidSelected() {
    if (!select || !select.value) {
      return false;
    }
    return select.value.toLowerCase().indexOf('haid') !== -1;
  }

  function hitungDurasiHari() {
    if (!tglMulai || !tglSelesai || !tglMulai.value || !tglSelesai.value) {
      return null;
    }
    var start = new Date(tglMulai.value + 'T00:00:00');
    var end = new Date(tglSelesai.value + 'T00:00:00');
    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
      return null;
    }
    var diff = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
    return diff;
  }

  function syncHaidRules() {
    var isHaid = isHaidSelected();
    var durasi = hitungDurasiHari();
    var invalidRange = durasi !== null && durasi > 14;

    if (infoHaid) {
      infoHaid.style.display = isHaid ? 'block' : 'none';
    }

    if (haidError) {
      if (isHaid && invalidRange) {
        haidError.style.display = 'block';
        haidError.textContent = 'Izin haid maksimal 14 hari. Silakan sesuaikan tanggal.';
      } else {
        haidError.style.display = 'none';
        haidError.textContent = '';
      }
    }

    if (submitBtn) {
      submitBtn.disabled = !!(isHaid && invalidRange);
    }
  }

  function syncCustomField() {
    var isCustom = select && select.value === '__lainnya__';
    if (wrap) {
      wrap.style.display = isCustom ? 'block' : 'none';
    }
    if (custom) {
      custom.required = !!isCustom;
      if (!isCustom) {
        custom.value = '';
      }
    }
  }

  if (select) {
    select.addEventListener('change', syncCustomField);
    select.addEventListener('change', syncHaidRules);
    syncCustomField();
    syncHaidRules();
  }

  if (tglMulai) {
    tglMulai.addEventListener('change', syncHaidRules);
  }

  if (tglSelesai) {
    tglSelesai.addEventListener('change', syncHaidRules);
  }
})();
</script>

