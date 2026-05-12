<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-dark">Rekap Kehadiran</h4>
    <div class="text-muted small">Rekap kehadiran mingguan jamaah dan ngaji santri.</div>
  </div>
  <div class="mt-3 mt-md-0 d-flex" style="gap:6px;">
    <a href="<?php echo site_url('admin/kehadiran/jadwal'); ?>" class="btn btn-outline-primary btn-sm">Kelola Jadwal</a>
    <a href="<?php echo site_url('admin/kehadiran/manual'); ?>" class="btn btn-outline-secondary btn-sm">Input Manual</a>
    <a href="<?php echo site_url('admin/dashboard'); ?>" class="btn btn-outline-dark btn-sm">Kembali</a>
  </div>
</div>

<!-- Analytics Info Boxes -->
<div class="row">
  <div class="col-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Hadir Hari Ini</span>
        <span class="info-box-number"><?php echo $analytics['hadir_hari_ini']; ?></span>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-door-open"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Izin Hari Ini</span>
        <span class="info-box-number"><?php echo $analytics['izin_hari_ini']; ?></span>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-times"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Alpha Hari Ini</span>
        <span class="info-box-number"><?php echo $analytics['alpha_hari_ini']; ?></span>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-dark elevation-1"><i class="fas fa-id-card"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Kartu Merah/Hitam</span>
        <span class="info-box-number"><?php echo $analytics['kartu_merah_hitam']; ?></span>
      </div>
    </div>
  </div>
</div>

<!-- Main Card -->
<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">Rekap Kehadiran Mingguan</h5>
  </div>
  <div class="card-body">
    <!-- Filters -->
    <form method="get" class="mb-3">
      <div class="row">
        <div class="col-md-2 mb-2">
          <input type="date" class="form-control form-control-sm" name="minggu" value="<?php echo html_escape($minggu_param); ?>" placeholder="Periode">
        </div>
        <div class="col-md-2 mb-2">
          <select class="form-control form-control-sm" name="kartu_jamaah">
            <option value="">Semua Kartu Jamaah</option>
            <?php foreach (array('putih', 'kuning', 'orange', 'merah', 'hitam') as $kartu): ?>
              <option value="<?php echo html_escape($kartu); ?>"<?php echo ($filters['kartu_jamaah'] === $kartu) ? ' selected' : ''; ?>><?php echo html_escape(ucfirst($kartu)); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <select class="form-control form-control-sm" name="kartu_ngaji">
            <option value="">Semua Kartu Ngaji</option>
            <?php foreach (array('putih', 'kuning', 'merah') as $kartu): ?>
              <option value="<?php echo html_escape($kartu); ?>"<?php echo ($filters['kartu_ngaji'] === $kartu) ? ' selected' : ''; ?>><?php echo html_escape(ucfirst($kartu)); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <select class="form-control form-control-sm" name="kamar">
            <option value="">Semua Kamar</option>
            <?php foreach ($kamar_list as $kamar): ?>
              <option value="<?php echo html_escape($kamar); ?>"<?php echo ($filters['kamar'] === $kamar) ? ' selected' : ''; ?>><?php echo html_escape($kamar); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <button type="submit" class="btn btn-sm btn-primary btn-block">Filter</button>
        </div>
      </div>
    </form>

    <div class="mb-3 d-flex justify-content-between align-items-center">
      <div>
        <strong>Periode:</strong> <?php echo date('d M Y', strtotime($minggu['mulai'])); ?> - <?php echo date('d M Y', strtotime($minggu['selesai'])); ?>
        <span class="text-muted small ml-2">(<?php echo $pagination['total']; ?> santri)</span>
      </div>
      <div>
        <a href="<?php echo site_url('admin/kehadiran/export_rekap?minggu=' . html_escape($minggu_param ?: $minggu['mulai'])); ?>" class="btn btn-sm btn-outline-success">
          <i class="fas fa-file-csv mr-1"></i>Export CSV
        </a>
      </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>NIM</th>
            <th>Nama</th>
            <th>Kamar</th>
            <th class="text-center">Alpha Jamaah</th>
            <th class="text-center">Kartu Jamaah</th>
            <th class="text-center">Alpha Ngaji</th>
            <th class="text-center">Kartu Ngaji</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $dataSource = !empty($rows) ? $rows : $preview; ?>
          <?php if (!empty($dataSource)): ?>
            <?php foreach ($dataSource as $row): ?>
            <tr>
              <td><code><?php echo html_escape($row['nim']); ?></code></td>
              <td><?php echo html_escape($row['nama'] ?? 'N/A'); ?></td>
              <td><?php echo html_escape($row['kamar'] ?? '-'); ?></td>
              <td class="text-center"><?php echo (int)$row['alpha_jamaah']; ?></td>
              <td class="text-center"><?php echo Presensi_model::render_kartu_badge($row['kartu_jamaah'], 'small'); ?></td>
              <td class="text-center"><?php echo (int)$row['alpha_ngaji']; ?></td>
              <td class="text-center"><?php echo Presensi_model::render_kartu_badge($row['kartu_ngaji'], 'small'); ?></td>
              <td>
                <a href="<?php echo site_url('admin/kehadiran/detail/' . html_escape($row['nim']) . '?minggu=' . html_escape($minggu_param)); ?>" class="btn btn-sm btn-outline-primary">Detail</a>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">Tidak ada data santri</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if ($pagination['total_pages'] > 1): ?>
  <?php
    $qBase = array_filter(array(
      'minggu'       => $minggu_param,
      'kartu_jamaah' => $filters['kartu_jamaah'],
      'kartu_ngaji'  => $filters['kartu_ngaji'],
      'kamar'        => $filters['kamar'],
    ));
    $buildUrl = function($p) use ($qBase) {
      return site_url('admin/kehadiran') . '?' . http_build_query(array_merge($qBase, array('page' => $p)));
    };
  ?>
  <div class="card-footer">
    <ul class="pagination pagination-sm m-0">
      <li class="page-item <?php echo ($pagination['page'] <= 1) ? 'disabled' : ''; ?>">
        <a class="page-link" href="<?php echo $buildUrl($pagination['page'] - 1); ?>">&laquo;</a>
      </li>
      <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
        <li class="page-item <?php echo ($p === $pagination['page']) ? 'active' : ''; ?>">
          <a class="page-link" href="<?php echo $buildUrl($p); ?>"><?php echo $p; ?></a>
        </li>
      <?php endfor; ?>
      <li class="page-item <?php echo ($pagination['page'] >= $pagination['total_pages']) ? 'disabled' : ''; ?>">
        <a class="page-link" href="<?php echo $buildUrl($pagination['page'] + 1); ?>">&raquo;</a>
      </li>
    </ul>
  </div>
  <?php endif; ?>
</div>
