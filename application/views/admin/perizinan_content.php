<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-gray-800">Validasi Perizinan</h4>
    <div class="small-text">Validasi pengajuan izin, termasuk izin tanpa surat seperti Haid.</div>
  </div>
  <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row align-self-stretch">
    <a href="<?php echo site_url('admin/dashboard'); ?>" class="btn btn-outline-primary btn-sm text-center">Kembali</a>
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small-text">Santri Aktif Saat Ini</div>
        <div class="h4 mb-0 font-weight-bold"><?php echo (int) (($status_summary['total_aktif'] ?? 0)); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-6 mb-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small-text">Santri Sedang Izin Saat Ini</div>
        <div class="h4 mb-0 font-weight-bold"><?php echo (int) (($status_summary['total_izin'] ?? 0)); ?></div>
      </div>
    </div>
  </div>
</div>

<form method="get" action="<?php echo site_url('admin/perizinan'); ?>" class="card mb-3 shadow-sm">
  <div class="card-body">
    <div class="form-row align-items-end">
      <div class="col-md-5 mb-2">
        <label class="small mb-1">Search NIM / Nama</label>
        <input type="text" name="q" class="form-control" value="<?php echo html_escape($filters['q'] ?? ''); ?>" placeholder="Cari NIM atau nama">
      </div>
      <div class="col-md-4 mb-2">
        <label class="small mb-1">Filter Status</label>
        <select name="status" class="form-control">
          <option value="">Semua Status</option>
          <?php foreach ($status_map as $key => $label): ?>
            <option value="<?php echo html_escape($key); ?>" <?php echo ((string) ($selected_status ?? '') === (string) $key) ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 mb-2 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary mr-2">Cari</button>
        <a href="<?php echo site_url('admin/perizinan'); ?>" class="btn btn-outline-secondary mr-2">Reset</a>
        <button type="button" id="toggle_delete_mode" class="btn btn-danger">Hapus Riwayat</button>
      </div>
    </div>
  </div>
</form>

<form id="bulk_delete_form" method="post" action="<?php echo site_url('admin/perizinan/delete-selected'); ?>" onsubmit="return confirm('Hapus data yang dipilih?');">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <input type="hidden" name="q" value="<?php echo html_escape($filters['q'] ?? ''); ?>">
  <input type="hidden" name="status" value="<?php echo html_escape($selected_status ?? ''); ?>">
  <input type="hidden" name="page" value="<?php echo html_escape($_GET['page'] ?? ''); ?>">
</form>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width:40px;" class="delete-column d-none"><input type="checkbox" id="check_all"></th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Periode</th>
            <th>Alasan</th>
            <th>Status</th>
            <th>Upload</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada pengajuan yang perlu divalidasi.</td></tr>
          <?php else: ?>
            <?php foreach ($rows as $row): ?>
              <tr>
                <?php $is_haid = $this->Perizinan_model->is_haid_alasan($row['alasan']); ?>
                <?php $today = date('Y-m-d'); ?>
                <td class="delete-column d-none"><input type="checkbox" form="bulk_delete_form" name="selected_ids[]" value="<?php echo html_escape($row['id']); ?>" class="row-check"></td>
                <td class="font-weight-bold"><?php echo html_escape($row['nim']); ?></td>
                <td><?php echo html_escape($row['nama']); ?></td>
                <td><?php echo html_escape($row['tgl_mulai']); ?> s/d <?php echo html_escape($row['tgl_selesai']); ?></td>
                <td><?php echo html_escape($row['alasan']); ?></td>
                <td><strong><?php echo html_escape($status_map[$row['status']] ?? $row['status']); ?></strong></td>
                <td>
                  <?php if ($row['file_upload']): ?>
                    <small class="text-muted">
                      <?php echo html_escape($row['file_upload']); ?>
                      <br>
                      <small class="text-muted"><?php echo !empty($row['file_upload_at']) ? date('d/m/Y H:i', strtotime($row['file_upload_at'])) : '-'; ?></small>
                    </small>
                  <?php elseif ($is_haid): ?>
                    <span class="text-muted small">Tanpa surat (Haid)</span>
                  <?php else: ?>
                    <span class="text-muted small">Belum ada</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($row['status'] === '2'): ?>
                    <div class="btn-group btn-group-sm" role="group">
                      <?php if (!empty($row['file_upload'])): ?>
                        <a href="<?php echo base_url('uploads/perizinan/' . rawurlencode($row['file_upload'])); ?>" target="_blank" class="btn btn-info btn-sm" title="Lihat PDF"><i class="fas fa-file-pdf"></i></a>
                      <?php endif; ?>
                      <form method="post" action="<?php echo site_url('admin/perizinan/validate/' . rawurlencode($row['id'])); ?>" style="display:inline;">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <input type="hidden" name="decision" value="3">
                        <button type="submit" class="btn btn-success btn-sm" title="Setujui"><i class="fas fa-check"></i></button>
                      </form>
                      <form method="post" action="<?php echo site_url('admin/perizinan/validate/' . rawurlencode($row['id'])); ?>" style="display:inline;">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <input type="hidden" name="decision" value="4">
                        <button type="submit" class="btn btn-danger btn-sm" title="Tolak"><i class="fas fa-times"></i></button>
                      </form>
                    </div>
                  <?php elseif ((string) $row['acc'] === '1' && $today <= $row['tgl_selesai']): ?>
                    <?php if (!empty($row['is_suspended'])): ?>
                      <form method="post" action="<?php echo site_url('admin/perizinan/lanjutkan-izin/' . rawurlencode($row['id'])); ?>" class="d-inline" onsubmit="return confirm('Lanjutkan izin santri ini?');">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <button type="submit" class="btn btn-warning btn-sm">Lanjutkan Izin</button>
                      </form>
                    <?php else: ?>
                      <form method="post" action="<?php echo site_url('admin/perizinan/selesaikan-izin/' . rawurlencode($row['id'])); ?>" class="d-inline" onsubmit="return confirm('Selesaikan izin santri ini sementara?');">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <button type="submit" class="btn btn-secondary btn-sm">Selesaikan Izin</button>
                      </form>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted small">Sudah diproses</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-between align-items-center">
    <button type="submit" form="bulk_delete_form" class="btn btn-danger btn-sm d-none" id="bulk_delete_submit">Hapus Data Terpilih</button>
    <div><?php echo $pagination ?? ''; ?></div>
  </div>
</div>
<script>
(function () {
  var checkAll = document.getElementById('check_all');
  var rowChecks = document.querySelectorAll('.row-check');
  var deleteColumns = document.querySelectorAll('.delete-column');
  var toggleDeleteMode = document.getElementById('toggle_delete_mode');
  var bulkDeleteSubmit = document.getElementById('bulk_delete_submit');
  var deleteMode = false;

  function setDeleteMode(enabled) {
    deleteMode = enabled;
    deleteColumns.forEach(function (column) {
      if (enabled) {
        column.classList.remove('d-none');
      } else {
        column.classList.add('d-none');
      }
    });
    if (bulkDeleteSubmit) {
      if (enabled) {
        bulkDeleteSubmit.classList.remove('d-none');
      } else {
        bulkDeleteSubmit.classList.add('d-none');
      }
    }
    if (!enabled && checkAll) {
      checkAll.checked = false;
      rowChecks.forEach(function (checkbox) {
        checkbox.checked = false;
      });
    }
  }

  if (checkAll) {
    checkAll.addEventListener('change', function () {
      rowChecks.forEach(function (checkbox) {
        checkbox.checked = checkAll.checked;
      });
    });
  }

  if (toggleDeleteMode) {
    toggleDeleteMode.addEventListener('click', function () {
      setDeleteMode(!deleteMode);
      toggleDeleteMode.textContent = deleteMode ? 'Batal Hapus' : 'Hapus Riwayat';
      if (deleteMode) {
        toggleDeleteMode.classList.remove('btn-danger');
        toggleDeleteMode.classList.add('btn-outline-danger');
      } else {
        toggleDeleteMode.classList.add('btn-danger');
        toggleDeleteMode.classList.remove('btn-outline-danger');
      }
    });
  }

  setDeleteMode(false);
})();
</script>
