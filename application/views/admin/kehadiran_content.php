<div class="container-fluid">
  <!-- Dashboard Analytics -->
  <div class="row mb-4">
    <div class="col-6 col-md-3">
      <div class="card text-center">
        <div class="card-body py-3">
          <h4 class="mb-1"><?php echo $analytics['hadir_hari_ini']; ?></h4>
          <small>Hadir Hari Ini</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center">
        <div class="card-body py-3">
          <h4 class="mb-1"><?php echo $analytics['izin_hari_ini']; ?></h4>
          <small>Izin Hari Ini</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center">
        <div class="card-body py-3">
          <h4 class="mb-1"><?php echo $analytics['alpha_hari_ini']; ?></h4>
          <small>Alpha Hari Ini</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center">
        <div class="card-body py-3">
          <h4 class="mb-1"><?php echo $analytics['kartu_merah_hitam']; ?></h4>
          <small>Kartu Merah/Hitam</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Rekap Kehadiran Mingguan</h5>
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
              <?php if (!empty($rows)): ?>
                <span class="badge badge-success ml-2">Sudah Difinalisasi</span>
              <?php else: ?>
                <span class="badge badge-secondary ml-2">Belum Difinalisasi</span>
              <?php endif; ?>
            </div>
            <div>
              <a href="<?php echo site_url('admin/kehadiran/export_rekap?minggu=' . html_escape($minggu_param ?: $minggu['mulai'])); ?>" class="btn btn-sm btn-outline-success mr-2">Export Excel</a>
              <?php if (!empty($rows)): ?>
                <form method="post" action="<?php echo site_url('admin/kehadiran/refinalisasi'); ?>" class="d-inline">
                  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                  <input type="hidden" name="minggu_mulai" value="<?php echo html_escape($minggu['mulai']); ?>">
                  <input type="hidden" name="minggu_selesai" value="<?php echo html_escape($minggu['selesai']); ?>">
                  <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Yakin ingin re-finalisasi? Data kartu akan dihitung ulang.')">
                    Re-finalisasi
                  </button>
                </form>
              <?php else: ?>
                <form method="post" action="<?php echo site_url('admin/kehadiran/finalisasi'); ?>" class="d-inline">
                  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                  <input type="hidden" name="minggu_mulai" value="<?php echo html_escape($minggu['mulai']); ?>">
                  <input type="hidden" name="minggu_selesai" value="<?php echo html_escape($minggu['selesai']); ?>">
                  <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Finalisasi akan menyimpan snapshot resmi data kartu minggu ini. Lanjutkan?')">
                    Finalisasi Rekap
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>

          <!-- Data Table -->
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>NIM</th>
                  <th>Nama</th>
                  <th>Kamar</th>
                  <th>Alpha Jamaah</th>
                  <th>Kartu Jamaah</th>
                  <th>Alpha Ngaji</th>
                  <th>Kartu Ngaji</th>
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
                    <td colspan="8" class="text-center text-muted">Tidak ada data santri</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php if (empty($rows) && !empty($preview)): ?>
            <div class="alert alert-info mt-3 mb-0">
              <small><strong>Info:</strong> Data di atas adalah preview real-time. Finalisasi bersifat opsional untuk menyimpan snapshot resmi data kartu mingguan.</small>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="row mt-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h6 class="mb-3">Aksi Cepat</h6>
          <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo site_url('admin/kehadiran/jadwal'); ?>" class="btn btn-sm btn-outline-primary">Kelola Jadwal</a>
            <a href="<?php echo site_url('admin/kehadiran/manual'); ?>" class="btn btn-sm btn-outline-secondary">Input Manual</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
