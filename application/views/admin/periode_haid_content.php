<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-dark">Kelola Periode Haid Santri</h4>
    <div class="text-muted small">Pencatatan data periode haid masing-masing santri.</div>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="<?php echo site_url('admin/periode-haid/create'); ?>" class="btn btn-primary btn-sm">
      Tambah Data
    </a>
    <a href="<?php echo site_url('admin/dashboard'); ?>" class="btn btn-outline-primary btn-sm ml-2">Kembali</a>
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

<!-- Filter -->
<div class="card mb-3">
  <div class="card-body py-2">
    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:6px;">
      <form method="get" action="<?php echo site_url('admin/periode-haid'); ?>" class="d-flex align-items-center" style="gap:6px;">
        <input type="text" name="q" class="form-control form-control-sm"
               style="width:200px;"
               value="<?php echo html_escape($search); ?>"
               placeholder="Cari NIM / Nama...">

        <div class="position-relative">
          <button type="button" class="btn btn-secondary btn-sm" id="btn_filter_toggle">
            Filter
          </button>

          <!-- Panel Filter -->
          <div id="filter_panel" class="shadow border bg-white rounded p-3"
               style="display:none; position:absolute; left:0; top:calc(100% + 6px); width:280px; z-index:1050;">
            <div class="form-group mb-3">
              <label class="small mb-1">Kategori Durasi</label>
              <select name="duration" class="form-control form-control-sm">
                <option value="">Semua Data</option>
                <option value="singkat" <?php echo (isset($duration_filter) && $duration_filter === 'singkat') ? 'selected' : ''; ?>>Singkat (&lt;7 hari)</option>
                <option value="normal" <?php echo (isset($duration_filter) && $duration_filter === 'normal') ? 'selected' : ''; ?>>Normal (7-14 hari)</option>
                <option value="panjang" <?php echo (isset($duration_filter) && $duration_filter === 'panjang') ? 'selected' : ''; ?>>Panjang (&gt;14 hari)</option>
              </select>
            </div>
            <div class="d-flex" style="gap:6px;">
              <button type="submit" class="btn btn-primary btn-sm flex-fill">Terapkan</button>
              <?php if (!empty($search) || !empty($duration_filter)): ?>
                <a href="<?php echo site_url('admin/periode-haid'); ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        <?php if (!empty($search) || !empty($duration_filter)): ?>
          <a href="<?php echo site_url('admin/periode-haid'); ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<!-- Tabel -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width:36px;">#</th>
            <th>Nama (NIM)</th>
            <th>Rata-rata Hari</th>
            <th>Paling Lama Hari</th>
            <th>Diupdate</th>
            <th style="width:100px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data.</td></tr>
          <?php else: ?>
            <?php $no = 1; ?>
            <?php foreach ($rows as $row): ?>
              <tr>
                <td class="small text-muted"><?php echo $no++; ?></td>
                <td class="small">
                  <strong><?php echo html_escape($row['nama']); ?></strong><br>
                  <span class="text-muted">(<?php echo html_escape($row['nim']); ?>)</span>
                </td>
                <td class="small text-center"><?php echo (int) $row['rata_rata_hari']; ?> hari</td>
                <td class="small text-center"><?php echo (int) $row['paling_lama_hari']; ?> hari</td>
                <td class="small text-muted">
                  <?php echo !empty($row['updated_at']) ? date('d/m/Y H:i', strtotime($row['updated_at'])) : date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                </td>
                <td class="small">
                  <a href="<?php echo site_url('admin/periode-haid/edit/' . $row['id']); ?>" class="btn btn-warning btn-xs">
                    Edit
                  </a>
                  <form method="post" action="<?php echo site_url('admin/periode-haid/delete/' . $row['id']); ?>" 
                        onsubmit="return confirm('Hapus data ini?');" style="display:inline;">
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    <button type="submit" class="btn btn-danger btn-xs">
                      Hapus
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end">
    <div><?php echo isset($pagination) ? $pagination : ''; ?></div>
  </div>
</div>

<script>
(function () {
  var btnFilterToggle = document.getElementById('btn_filter_toggle');
  var filterPanel = document.getElementById('filter_panel');

  if (btnFilterToggle && filterPanel) {
    btnFilterToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      filterPanel.style.display = filterPanel.style.display === 'none' ? '' : 'none';
    });

    document.addEventListener('click', function (e) {
      if (!filterPanel.contains(e.target) && e.target !== btnFilterToggle) {
        filterPanel.style.display = 'none';
      }
    });
  }
})();
</script>
