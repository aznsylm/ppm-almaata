<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Riwayat Kehadiran</h5>
        </div>
        <div class="card-body">
          <!-- Filter Periode -->
          <form method="get" class="mb-4">
            <div class="row">
              <div class="col-md-3">
                <label>Periode</label>
                <input type="date" class="form-control" name="minggu" value="<?php echo html_escape($minggu_param); ?>">
              </div>
              <div class="col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">Tampilkan</button>
              </div>
            </div>
          </form>

          <!-- Info Periode -->
          <div class="mb-4">
            <strong>Periode:</strong> <?php echo date('d M Y', strtotime($minggu['mulai'])); ?> - <?php echo date('d M Y', strtotime($minggu['selesai'])); ?>
          </div>

          <!-- Summary Cards -->
          <div class="row mb-4">
            <div class="col-6 col-md-3">
              <div class="card text-center">
                <div class="card-body py-3">
                  <h4 class="mb-1"><?php echo (int)$alpha['alpha_jamaah']; ?></h4>
                  <small>Alpha Jamaah</small>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card text-center">
                <div class="card-body py-3">
                  <h4 class="mb-1"><?php echo (int)$alpha['alpha_ngaji']; ?></h4>
                  <small>Alpha Ngaji</small>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card text-center">
                <div class="card-body py-3">
                  <div class="mb-1"><?php echo Presensi_model::render_kartu_badge($kartu['kartu_jamaah']); ?></div>
                  <small>Kartu Jamaah</small>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card text-center">
                <div class="card-body py-3">
                  <div class="mb-1"><?php echo Presensi_model::render_kartu_badge($kartu['kartu_ngaji']); ?></div>
                  <small>Kartu Ngaji</small>
                </div>
              </div>
            </div>
          </div>

          <!-- Kehadiran Harian -->
          <h6>Kehadiran Harian</h6>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Jamaah Maghrib</th>
                  <th>Jamaah Isya</th>
                  <th>Jamaah Subuh</th>
                  <th>Ngaji Maghrib</th>
                  <th>Ngaji Subuh</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($detail as $day): ?>
                <tr>
                  <td><strong><?php echo date('d M Y (l)', strtotime($day['tanggal'])); ?></strong></td>
                  <td class="text-center">
                    <?php 
                      $status = isset($day['jamaah_maghrib']) ? $day['jamaah_maghrib'] : '-';
                      echo ($status === 'hadir') ? 'Hadir' : (($status === 'izin') ? 'Izin' : (($status === 'alpha') ? 'Alpha' : '-'));
                    ?>
                  </td>
                  <td class="text-center">
                    <?php 
                      $status = isset($day['jamaah_isya']) ? $day['jamaah_isya'] : '-';
                      echo ($status === 'hadir') ? 'Hadir' : (($status === 'izin') ? 'Izin' : (($status === 'alpha') ? 'Alpha' : '-'));
                    ?>
                  </td>
                  <td class="text-center">
                    <?php 
                      $status = isset($day['jamaah_subuh']) ? $day['jamaah_subuh'] : '-';
                      echo ($status === 'hadir') ? 'Hadir' : (($status === 'izin') ? 'Izin' : (($status === 'alpha') ? 'Alpha' : '-'));
                    ?>
                  </td>
                  <td class="text-center">
                    <?php 
                      $status = isset($day['ngaji_maghrib']) ? $day['ngaji_maghrib'] : '-';
                      echo ($status === 'hadir') ? 'Hadir' : (($status === 'izin') ? 'Izin' : (($status === 'alpha') ? 'Alpha' : '-'));
                    ?>
                  </td>
                  <td class="text-center">
                    <?php 
                      $status = isset($day['ngaji_subuh']) ? $day['ngaji_subuh'] : '-';
                      echo ($status === 'hadir') ? 'Hadir' : (($status === 'izin') ? 'Izin' : (($status === 'alpha') ? 'Alpha' : '-'));
                    ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Riwayat Kartu -->
          <h6 class="mt-4">Riwayat Kartu (10 Minggu Terakhir)</h6>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Periode</th>
                  <th>Alpha Jamaah</th>
                  <th>Kartu Jamaah</th>
                  <th>Alpha Ngaji</th>
                  <th>Kartu Ngaji</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($riwayat_kartu as $row): ?>
                <tr>
                  <td><?php echo date('d M Y', strtotime($row['minggu_mulai'])); ?> - <?php echo date('d M Y', strtotime($row['minggu_selesai'])); ?></td>
                  <td class="text-center"><?php echo (int)$row['alpha_jamaah']; ?></td>
                  <td class="text-center"><?php echo Presensi_model::render_kartu_badge($row['kartu_jamaah'], 'small'); ?></td>
                  <td class="text-center"><?php echo (int)$row['alpha_ngaji']; ?></td>
                  <td class="text-center"><?php echo Presensi_model::render_kartu_badge($row['kartu_ngaji'], 'small'); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
