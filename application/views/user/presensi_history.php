<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-dark">Riwayat Kehadiran</h4>
    <div class="text-muted small">Rekap kehadiran mingguan per kegiatan.</div>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="<?php echo site_url('user/presensi'); ?>" class="btn btn-sm btn-outline-secondary">Kembali</a>
  </div>
</div>

<!-- Filter Periode -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">Filter Periode</h5>
  </div>
  <div class="card-body">
    <form method="get">
      <div class="row align-items-end">
        <div class="col-md-3 mb-2">
          <input type="date" class="form-control form-control-sm" name="minggu" value="<?php echo html_escape($minggu_param); ?>">
        </div>
        <div class="col-md-2 mb-2">
          <button type="submit" class="btn btn-sm btn-primary btn-block">Filter</button>
        </div>
      </div>
    </form>
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
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      Kehadiran Harian
      <span class="text-muted small font-weight-normal ml-2">
        <?php echo date('d M Y', strtotime($minggu['mulai'])); ?> &ndash; <?php echo date('d M Y', strtotime($minggu['selesai'])); ?>
      </span>
    </h5>
    <a href="<?php echo site_url('user/presensi/export_detail?minggu=' . html_escape($minggu_param ?: $minggu['mulai'])); ?>"
       class="btn btn-sm btn-outline-success">
      <i class="fas fa-file-csv mr-1"></i>Export CSV
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
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
            <?php foreach (array('jamaah_maghrib','jamaah_isya','jamaah_subuh','ngaji_maghrib','ngaji_subuh') as $k): ?>
            <td class="text-center">
              <?php
                $s = isset($day[$k]) ? $day[$k] : '-';
                echo ($s === 'hadir') ? 'Hadir' : (($s === 'izin') ? 'Izin' : (($s === 'alpha') ? 'Alpha' : '-'));
              ?>
            </td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Riwayat Kartu -->
<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">Riwayat Kartu (10 Minggu Terakhir)</h5>
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
            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada riwayat kartu.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
