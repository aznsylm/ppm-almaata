<div class="container-fluid">
  <!-- Quick Presensi Section -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Kehadiran Hari Ini</h5>
          <small class="text-muted">
            Waktu: <?php echo date('H:i'); ?>
            <?php if ($this->session->userdata('role') === 'admin'): ?>
              <span class="badge badge-info ml-2">Mode Testing Admin</span>
            <?php endif; ?>
          </small>
        </div>
        <div class="card-body">
          <?php if (!empty($jadwal_aktif_sekarang)): ?>
            <div class="row">
              <?php foreach ($jadwal_aktif_sekarang as $jadwal): ?>
                <?php
                  $sudah_presensi = FALSE;
                  foreach ($presensi_hari_ini as $p) {
                    if ($p['kegiatan'] === $jadwal['kegiatan']) {
                      $sudah_presensi = TRUE;
                      break;
                    }
                  }
                ?>
                <div class="col-6 col-md-4 col-lg-2 mb-3">
                  <?php if ($sudah_presensi): ?>
                    <button class="btn btn-success btn-block py-3" disabled>
                      <div><?php echo html_escape(isset($kegiatan_list[$jadwal['kegiatan']]) ? $kegiatan_list[$jadwal['kegiatan']] : $jadwal['kegiatan']); ?></div>
                      <small>Sudah Hadir</small>
                    </button>
                  <?php else: ?>
                    <form method="post" action="<?php echo site_url('user/presensi/checkin'); ?>">
                      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                      <input type="hidden" name="kegiatan" value="<?php echo html_escape($jadwal['kegiatan']); ?>">
                      <button type="submit" class="btn btn-primary btn-block py-3">
                        <div><?php echo html_escape(isset($kegiatan_list[$jadwal['kegiatan']]) ? $kegiatan_list[$jadwal['kegiatan']] : $jadwal['kegiatan']); ?></div>
                        <small>
                          <?php echo html_escape($jadwal['jam_mulai']); ?> - <?php echo html_escape($jadwal['jam_selesai']); ?>
                          <?php if ($this->session->userdata('role') === 'admin'): ?>
                            <?php 
                              $now = date('H:i:s');
                              if ($now < $jadwal['jam_mulai'] || $now > $jadwal['jam_selesai']) {
                                echo ' <span class="text-warning">(Di luar periode)</span>';
                              } else {
                                echo ' <span class="text-success">(Periode aktif)</span>';
                              }
                            ?>
                          <?php endif; ?>
                        </small>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-center py-4">
              <p class="mb-0">Tidak ada kegiatan aktif sekarang</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
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

  <!-- Kehadiran Hari Ini -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Kehadiran Hari Ini</h6>
          <a href="<?php echo site_url('user/presensi/history'); ?>" class="btn btn-sm btn-outline-primary">Riwayat</a>
        </div>
        <div class="card-body">
          <?php if (!empty($presensi_hari_ini)): ?>
            <div class="table-responsive">
              <table class="table table-sm mb-0">
                <tbody>
                  <?php foreach ($presensi_hari_ini as $p): ?>
                  <tr>
                    <td><?php echo html_escape(isset($kegiatan_list[$p['kegiatan']]) ? $kegiatan_list[$p['kegiatan']] : $p['kegiatan']); ?></td>
                    <td class="text-right">
                      <?php if ($p['status'] === 'hadir'): ?>
                        Hadir
                      <?php else: ?>
                        Izin
                      <?php endif; ?>
                      <small class="text-muted d-block"><?php echo date('H:i', strtotime($p['presensi_at'])); ?></small>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-center py-3">
              <p class="mb-0 text-muted">Belum ada kehadiran hari ini</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
