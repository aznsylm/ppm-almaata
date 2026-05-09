<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Detail Kehadiran</h5>
          <a href="<?php echo site_url('admin/kehadiran?minggu=' . html_escape($minggu_param)); ?>" class="btn btn-sm btn-secondary">Kembali</a>
        </div>
        <div class="card-body">
          <!-- Data Santri -->
          <div class="row mb-4">
            <div class="col-md-6">
              <h6>Data Santri</h6>
              <table class="table table-sm">
                <tr>
                  <td><strong>NIM</strong></td>
                  <td><?php echo html_escape($santri['nim'] ?? $nim); ?></td>
                </tr>
                <tr>
                  <td><strong>Nama</strong></td>
                  <td><?php echo html_escape($santri['nama'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                  <td><strong>Kamar</strong></td>
                  <td><?php echo html_escape($santri['kamar'] ?? '-'); ?></td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <h6>Periode: <?php echo date('d M Y', strtotime($minggu['mulai'])); ?> - <?php echo date('d M Y', strtotime($minggu['selesai'])); ?></h6>
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

          <!-- Kehadiran Harian -->
          <h6>Kehadiran Harian</h6>
          
          <!-- Bulk Edit Controls -->
          <div class="mb-3">
            <div class="row">
              <div class="col-md-6">
                <button type="button" class="btn btn-sm btn-outline-primary" id="toggleBulkEdit">Mode Bulk Edit</button>
                <button type="button" class="btn btn-sm btn-success" id="saveBulkEdit" style="display:none;">Simpan Perubahan</button>
                <button type="button" class="btn btn-sm btn-secondary" id="cancelBulkEdit" style="display:none;">Batal</button>
              </div>
              <div class="col-md-6 text-right">
                <a href="<?php echo site_url('admin/kehadiran/export_detail/' . $nim . '?minggu=' . $minggu_param); ?>" class="btn btn-sm btn-outline-success">
                  <i class="fas fa-file-excel"></i> Export Excel
                </a>
              </div>
            </div>
          </div>

          <form id="bulkEditForm" method="post" action="<?php echo site_url('admin/kehadiran/bulk_update'); ?>">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <input type="hidden" name="nim" value="<?php echo html_escape($nim); ?>">
            <input type="hidden" name="minggu_mulai" value="<?php echo html_escape($minggu['mulai']); ?>">
            <input type="hidden" name="minggu_selesai" value="<?php echo html_escape($minggu['selesai']); ?>">
            <input type="hidden" name="minggu_param" value="<?php echo html_escape($minggu_param); ?>">
            
            <div class="table-responsive">
              <table class="table table-sm" id="detailTable">
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
                  <?php foreach ($detail as $index => $day): ?>
                  <tr>
                    <td><strong><?php echo date('d M Y (l)', strtotime($day['tanggal'])); ?></strong></td>
                    
                    <?php foreach (['jamaah_maghrib', 'jamaah_isya', 'jamaah_subuh', 'ngaji_maghrib', 'ngaji_subuh'] as $kegiatan): ?>
                    <td class="text-center">
                      <?php 
                        $status = isset($day[$kegiatan]) ? $day[$kegiatan] : '-';
                        $displayStatus = ($status === 'hadir') ? 'Hadir' : (($status === 'izin') ? 'Izin' : (($status === 'alpha') ? 'Alpha' : '-'));
                      ?>
                      
                      <!-- Normal View -->
                      <span class="normal-view"><?php echo $displayStatus; ?></span>
                      
                      <!-- Edit View -->
                      <select class="form-control form-control-sm edit-view" name="status[<?php echo $day['tanggal']; ?>][<?php echo $kegiatan; ?>]" style="display:none;">
                        <option value="">-</option>
                        <option value="hadir"<?php echo ($status === 'hadir') ? ' selected' : ''; ?>>Hadir</option>
                        <option value="izin"<?php echo ($status === 'izin') ? ' selected' : ''; ?>>Izin</option>
                        <?php if ($status === 'alpha' || $status === '-'): ?>
                        <option value="alpha" selected>Alpha</option>
                        <?php endif; ?>
                      </select>
                    </td>
                    <?php endforeach; ?>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </form>

          <!-- Riwayat Kartu -->
          <h6 class="mt-4">Riwayat Kartu (8 Minggu Terakhir)</h6>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Periode</th>
                  <th>Alpha Jamaah</th>
                  <th>Kartu Jamaah</th>
                  <th>Alpha Ngaji</th>
                  <th>Kartu Ngaji</th>
                  <th>Status</th>
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
                  <td><?php echo ((int)$row['is_final'] === 1) ? 'Finalisasi' : 'Draf'; ?></td>
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

<script>
// Bulk Edit JavaScript
document.addEventListener('DOMContentLoaded', function() {
  const toggleBtn = document.getElementById('toggleBulkEdit');
  const saveBtn = document.getElementById('saveBulkEdit');
  const cancelBtn = document.getElementById('cancelBulkEdit');
  const selectAllBtn = document.getElementById('selectAll');
  const bulkOnlyElements = document.querySelectorAll('.bulk-only');
  const normalViews = document.querySelectorAll('.normal-view');
  const editViews = document.querySelectorAll('.edit-view');
  const rowSelects = document.querySelectorAll('.row-select');
  
  let bulkMode = false;
  
  // Toggle bulk edit mode
  toggleBtn.addEventListener('click', function() {
    bulkMode = !bulkMode;
    
    if (bulkMode) {
      // Enter bulk mode
      toggleBtn.style.display = 'none';
      saveBtn.style.display = 'inline-block';
      cancelBtn.style.display = 'inline-block';
      
      normalViews.forEach(el => el.style.display = 'none');
      editViews.forEach(el => el.style.display = 'block');
    } else {
      // Exit bulk mode
      exitBulkMode();
    }
  });
  
  // Cancel bulk edit
  cancelBtn.addEventListener('click', function() {
    if (confirm('Batalkan perubahan? Semua perubahan yang belum disimpan akan hilang.')) {
      location.reload();
    }
  });
  
  // Exit bulk mode function
  function exitBulkMode() {
    bulkMode = false;
    toggleBtn.style.display = 'inline-block';
    saveBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
    
    normalViews.forEach(el => el.style.display = 'inline');
    editViews.forEach(el => el.style.display = 'none');
  }
  
  // Save bulk edit
  saveBtn.addEventListener('click', function() {
    if (confirm('Yakin ingin menyimpan semua perubahan?')) {
      document.getElementById('bulkEditForm').submit();
    }
  });
});
</script>
