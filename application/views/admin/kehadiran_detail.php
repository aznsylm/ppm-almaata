<?php
// Hitung minggu sebelum dan sesudah untuk navigasi
$minggu_sebelum = date('Y-m-d', strtotime($minggu['mulai'] . ' -7 days'));
$minggu_sesudah = date('Y-m-d', strtotime($minggu['mulai'] . ' +7 days'));
$is_minggu_ini  = ($minggu['mulai'] === Presensi_model::get_minggu_aktif()['mulai']);
?>

<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-dark"><?php echo html_escape($santri['nama'] ?? $nim); ?></h4>
    <div class="text-muted small">
      NIM: <strong><?php echo html_escape($santri['nim'] ?? $nim); ?></strong>
      &nbsp;&mdash;&nbsp;
      Kamar: <strong><?php echo html_escape($santri['kamar'] ?? '-'); ?></strong>
    </div>
  </div>
  <div class="mt-3 mt-md-0 d-flex align-items-center" style="gap:6px;">
    <a href="<?php echo site_url('admin/kehadiran/export_detail/' . $nim . '?minggu=' . $minggu_param); ?>"
       class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-file-csv mr-1"></i>Export CSV
    </a>
    <a href="<?php echo site_url('admin/kehadiran?minggu=' . html_escape($minggu_param)); ?>"
       class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
  </div>
</div>

<!-- Summary Info Boxes -->
<div class="row">
  <div class="col-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-times-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Alpha Jamaah</span>
        <span class="info-box-number"><?php echo (int)$alpha['alpha_jamaah']; ?></span>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-times-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Alpha Ngaji</span>
        <span class="info-box-number"><?php echo (int)$alpha['alpha_ngaji']; ?></span>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-info elevation-1"><i class="fas fa-id-card"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Kartu Jamaah</span>
        <span class="info-box-number"><?php echo Presensi_model::render_kartu_badge($kartu['kartu_jamaah']); ?></span>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-info elevation-1"><i class="fas fa-id-card"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Kartu Ngaji</span>
        <span class="info-box-number"><?php echo Presensi_model::render_kartu_badge($kartu['kartu_ngaji']); ?></span>
      </div>
    </div>
  </div>
</div>

<!-- Kehadiran Harian -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <!-- Navigasi Minggu -->
    <div class="d-flex align-items-center" style="gap:6px;">
      <a href="<?php echo site_url('admin/kehadiran/detail/' . $nim . '?minggu=' . $minggu_sebelum); ?>"
         class="btn btn-sm btn-outline-secondary" title="Minggu sebelumnya">
        <i class="fas fa-chevron-left"></i>
      </a>
      <span class="font-weight-bold small">
        <?php echo date('d M Y', strtotime($minggu['mulai'])); ?> &ndash; <?php echo date('d M Y', strtotime($minggu['selesai'])); ?>
        <?php if ($is_minggu_ini): ?>
          <span class="badge badge-secondary ml-1">Minggu Ini</span>
        <?php endif; ?>
      </span>
      <a href="<?php echo site_url('admin/kehadiran/detail/' . $nim . '?minggu=' . $minggu_sesudah); ?>"
         class="btn btn-sm btn-outline-secondary <?php echo $is_minggu_ini ? 'disabled' : ''; ?>"
         title="Minggu berikutnya">
        <i class="fas fa-chevron-right"></i>
      </a>
    </div>
    <!-- Tombol Edit -->
    <div>
      <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleBulkEdit">
        <i class="fas fa-edit mr-1"></i>Edit
      </button>
      <button type="button" class="btn btn-sm btn-success" id="saveBulkEdit" style="display:none;">
        <i class="fas fa-check mr-1"></i>Simpan
      </button>
      <button type="button" class="btn btn-sm btn-outline-secondary" id="cancelBulkEdit" style="display:none;">
        Batal
      </button>
    </div>
  </div>
  <div class="card-body p-0">
    <form id="bulkEditForm" method="post" action="<?php echo site_url('admin/kehadiran/bulk_update'); ?>">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <input type="hidden" name="nim" value="<?php echo html_escape($nim); ?>">
      <input type="hidden" name="minggu_mulai" value="<?php echo html_escape($minggu['mulai']); ?>">
      <input type="hidden" name="minggu_selesai" value="<?php echo html_escape($minggu['selesai']); ?>">
      <input type="hidden" name="minggu_param" value="<?php echo html_escape($minggu_param); ?>">

      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0" id="detailTable">
          <thead class="thead-light">
            <tr>
              <th>Tanggal</th>
              <th class="text-center">Jamaah Maghrib</th>
              <th class="text-center">Jamaah Isya</th>
              <th class="text-center">Jamaah Subuh</th>
              <th class="text-center">Ngaji Maghrib</th>
              <th class="text-center">Ngaji Subuh</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($detail as $day): ?>
            <tr>
              <td><strong><?php echo date('d M Y (l)', strtotime($day['tanggal'])); ?></strong></td>
              <?php foreach (['jamaah_maghrib', 'jamaah_isya', 'jamaah_subuh', 'ngaji_maghrib', 'ngaji_subuh'] as $kegiatan): ?>
              <td class="text-center">
                <?php
                  $status = isset($day[$kegiatan]) ? $day[$kegiatan] : '-';
                  $label  = ($status === 'hadir') ? 'Hadir' : (($status === 'izin') ? 'Izin' : (($status === 'alpha') ? 'Alpha' : '-'));
                ?>
                <span class="normal-view"><?php echo $label; ?></span>
                <select class="form-control form-control-sm edit-view" name="status[<?php echo $day['tanggal']; ?>][<?php echo $kegiatan; ?>]" style="display:none;">
                  <option value="">-</option>
                  <option value="hadir"<?php echo ($status === 'hadir') ? ' selected' : ''; ?>>Hadir</option>
                  <option value="izin"<?php echo ($status === 'izin') ? ' selected' : ''; ?>>Izin</option>
                  <option value="alpha"<?php echo ($status === 'alpha' || $status === '-') ? ' selected' : ''; ?>>Alpha</option>
                </select>
              </td>
              <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </form>
  </div>
</div>

<!-- Riwayat Kartu -->
<div class="card mt-4">
  <div class="card-header">
    <h5 class="card-title mb-0">Riwayat Kartu (8 Minggu Terakhir)</h5>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th>Periode</th>
            <th class="text-center">Alpha Jamaah</th>
            <th class="text-center">Kartu Jamaah</th>
            <th class="text-center">Alpha Ngaji</th>
            <th class="text-center">Kartu Ngaji</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($riwayat_kartu)): ?>
            <?php foreach ($riwayat_kartu as $row): ?>
            <tr>
              <td><?php echo date('d M Y', strtotime($row['minggu_mulai'])); ?> &ndash; <?php echo date('d M Y', strtotime($row['minggu_selesai'])); ?></td>
              <td class="text-center"><?php echo (int)$row['alpha_jamaah']; ?></td>
              <td class="text-center"><?php echo Presensi_model::render_kartu_badge($row['kartu_jamaah'], 'small'); ?></td>
              <td class="text-center"><?php echo (int)$row['alpha_ngaji']; ?></td>
              <td class="text-center"><?php echo Presensi_model::render_kartu_badge($row['kartu_ngaji'], 'small'); ?></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada riwayat kartu yang tersimpan.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var toggleBtn  = document.getElementById('toggleBulkEdit');
  var saveBtn    = document.getElementById('saveBulkEdit');
  var cancelBtn  = document.getElementById('cancelBulkEdit');
  var normalViews = document.querySelectorAll('.normal-view');
  var editViews   = document.querySelectorAll('.edit-view');
  var bulkMode   = false;

  function enterEditMode() {
    bulkMode = true;
    toggleBtn.style.display = 'none';
    saveBtn.style.display   = 'inline-block';
    cancelBtn.style.display = 'inline-block';
    normalViews.forEach(function(el) { el.style.display = 'none'; });
    editViews.forEach(function(el)   { el.style.display = 'block'; });
  }

  function exitEditMode() {
    bulkMode = false;
    toggleBtn.style.display = 'inline-block';
    saveBtn.style.display   = 'none';
    cancelBtn.style.display = 'none';
    normalViews.forEach(function(el) { el.style.display = 'inline'; });
    editViews.forEach(function(el)   { el.style.display = 'none'; });
  }

  toggleBtn.addEventListener('click', function() {
    if (bulkMode) { exitEditMode(); } else { enterEditMode(); }
  });

  cancelBtn.addEventListener('click', function() {
    if (confirm('Batalkan perubahan? Semua perubahan yang belum disimpan akan hilang.')) {
      exitEditMode();
    }
  });

  saveBtn.addEventListener('click', function() {
    if (confirm('Yakin ingin menyimpan semua perubahan?')) {
      document.getElementById('bulkEditForm').submit();
    }
  });
});
</script>
