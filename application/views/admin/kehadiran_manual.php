<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Kehadiran Manual</h5>
          <a href="<?php echo site_url('admin/kehadiran'); ?>" class="btn btn-sm btn-secondary">Kembali</a>
        </div>
        <div class="card-body">
          <!-- Tab Navigation -->
          <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="tab-tunggal" data-toggle="tab" href="#input-tunggal" role="tab">
                Input Tunggal
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="tab-massal" data-toggle="tab" href="#input-massal" role="tab">
                Input Massal
              </a>
            </li>
          </ul>

          <!-- Tab Content -->
          <div class="tab-content">
            <!-- Input Tunggal -->
            <div class="tab-pane fade show active" id="input-tunggal" role="tabpanel">
              <div class="row">
                <div class="col-md-8 col-lg-6">
                  <form method="post" action="<?php echo site_url('admin/kehadiran/manual_store'); ?>">
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    
                    <div class="form-group">
                      <label>Santri</label>
                      <select class="form-control select2-santri" name="nim" required style="width: 100%;">
                        <option value="">Pilih Santri</option>
                        <?php foreach ($santri_list as $santri): ?>
                          <option value="<?php echo html_escape($santri['nim']); ?>">
                            <?php echo html_escape($santri['nim']); ?> - <?php echo html_escape($santri['nama']); ?>
                            <?php if (!empty($santri['kamar'])): ?>
                              (<?php echo html_escape($santri['kamar']); ?>)
                            <?php endif; ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="form-group">
                      <label>Kegiatan</label>
                      <select class="form-control" name="kegiatan" required>
                        <option value="">Pilih Kegiatan</option>
                        <?php foreach ($kegiatan_list as $key => $label): ?>
                          <option value="<?php echo html_escape($key); ?>"><?php echo html_escape($label); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="form-group">
                      <label>Tanggal</label>
                      <input type="date" class="form-control" name="tanggal" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                      <small class="text-muted">Maksimal hari ini</small>
                    </div>

                    <div class="form-group">
                      <label>Status</label>
                      <select class="form-control" name="status" required>
                        <option value="">Pilih Status</option>
                        <option value="hadir">Hadir</option>
                        <option value="izin">Izin</option>
                      </select>
                    </div>

                    <button type="submit" class="btn btn-success">Simpan</button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Input Massal -->
            <div class="tab-pane fade" id="input-massal" role="tabpanel">
              <form method="post" action="<?php echo site_url('admin/kehadiran/manual_batch'); ?>">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                
                <!-- Parameter Input -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label>Kegiatan</label>
                    <select class="form-control" name="kegiatan" required>
                      <option value="">Pilih Kegiatan</option>
                      <?php foreach ($kegiatan_list as $key => $label): ?>
                        <option value="<?php echo html_escape($key); ?>"><?php echo html_escape($label); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label>Tanggal</label>
                    <input type="date" class="form-control" name="tanggal" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                  </div>
                  <div class="col-md-3">
                    <label>Status</label>
                    <select class="form-control" name="status" required>
                      <option value="">Pilih Status</option>
                      <option value="hadir">Hadir</option>
                      <option value="izin">Izin</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label>Filter Kamar</label>
                    <select class="form-control" id="filter_kamar">
                      <option value="">Semua Kamar</option>
                      <?php foreach ($kamar_list as $kamar): ?>
                        <option value="<?php echo html_escape($kamar); ?>"><?php echo html_escape($kamar); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <!-- Search & Quick Actions -->
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label>Cari Santri</label>
                    <input type="text" class="form-control" id="search_santri" placeholder="Ketik nama atau NIM...">
                  </div>
                  <div class="col-md-6">
                    <label>&nbsp;</label>
                    <div>
                      <button type="button" class="btn btn-sm btn-outline-primary" id="select_all">Pilih Semua</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary" id="select_none">Hapus Semua</button>
                      <button type="button" class="btn btn-sm btn-outline-info" id="select_kamar">Pilih Kamar</button>
                    </div>
                  </div>
                </div>

                <!-- Santri Selection -->
                <div class="form-group">
                  <label>Pilih Santri (<span id="selected_count" class="font-weight-bold text-primary">0</span> dipilih)</label>
                  <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                    <div class="row" id="santri_list">
                      <?php foreach ($santri_list as $index => $santri): ?>
                      <div class="col-md-6 col-lg-4 santri-item mb-2" 
                           data-kamar="<?php echo html_escape($santri['kamar'] ?? ''); ?>" 
                           data-search="<?php echo html_escape(strtolower($santri['nim'] . ' ' . $santri['nama'])); ?>">
                        <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input santri-checkbox" 
                                 name="nim_list[]" 
                                 value="<?php echo html_escape($santri['nim']); ?>" 
                                 id="santri_<?php echo $index; ?>">
                          <label class="custom-control-label" for="santri_<?php echo $index; ?>">
                            <small>
                              <strong><?php echo html_escape($santri['nim']); ?></strong><br>
                              <?php echo html_escape($santri['nama']); ?>
                              <?php if (!empty($santri['kamar'])): ?>
                                <span class="text-muted">(<?php echo html_escape($santri['kamar']); ?>)</span>
                              <?php endif; ?>
                            </small>
                          </label>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Kehadiran Massal</button>
                <span class="ml-3 text-muted"><small>Pilih minimal 1 santri</small></span>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Kehadiran Manual -->
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0">Riwayat Kehadiran Manual</h5>
        </div>
        <div class="card-body">
          <!-- Search & Filter -->
          <div class="row mb-3">
            <div class="col-md-4">
              <input type="text" class="form-control form-control-sm" id="searchRiwayat" placeholder="Cari NIM atau Nama...">
            </div>
            <div class="col-md-3">
              <select class="form-control form-control-sm" id="filterKegiatan">
                <option value="">Semua Kegiatan</option>
                <?php foreach ($kegiatan_list as $key => $label): ?>
                  <option value="<?php echo html_escape($key); ?>"><?php echo html_escape($label); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <input type="date" class="form-control form-control-sm" id="filterTanggal" placeholder="Filter Tanggal">
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-secondary btn-sm btn-block" id="clearFilter">Clear</button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>NIM</th>
                  <th>Nama</th>
                  <th>Kegiatan</th>
                  <th>Status</th>
                  <th>Input Oleh</th>
                  <th>Waktu Input</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($riwayat)): ?>
                  <?php foreach ($riwayat as $row): ?>
                  <tr class="riwayat-row" 
                      data-nim="<?php echo html_escape(strtolower($row['nim'])); ?>"
                      data-nama="<?php echo html_escape(strtolower($row['nama_santri'] ?? '')); ?>"
                      data-kegiatan="<?php echo html_escape($row['kegiatan']); ?>"
                      data-tanggal="<?php echo html_escape($row['tanggal']); ?>">
                    <td><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                    <td><code><?php echo html_escape($row['nim']); ?></code></td>
                    <td><?php echo html_escape($row['nama_santri'] ?? 'N/A'); ?></td>
                    <td><?php echo html_escape(isset(Presensi_model::KEGIATAN_LIST[$row['kegiatan']]) ? Presensi_model::KEGIATAN_LIST[$row['kegiatan']] : $row['kegiatan']); ?></td>
                    <td>
                      <?php if ($row['status'] === 'hadir'): ?>
                        Hadir
                      <?php else: ?>
                        Izin
                      <?php endif; ?>
                    </td>
                    <td><code><?php echo html_escape($row['created_by']); ?></code></td>
                    <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?php echo $row['id']; ?>">Edit</button>
                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?php echo $row['id']; ?>">Hapus</button>
                      </div>

                      <!-- Modal Edit -->
                      <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Edit Status</h5>
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <form method="post" action="<?php echo site_url('admin/kehadiran/manual_edit/' . $row['id']); ?>">
                              <div class="modal-body">
                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                                <div class="form-group">
                                  <label>Status Baru:</label>
                                  <select class="form-control form-control-sm" name="status" required>
                                    <option value="hadir"<?php echo ($row['status'] === 'hadir') ? ' selected' : ''; ?>>Hadir</option>
                                    <option value="izin"<?php echo ($row['status'] === 'izin') ? ' selected' : ''; ?>>Izin</option>
                                  </select>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-warning btn-sm">Simpan</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>

                      <!-- Modal Delete -->
                      <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Hapus Kehadiran</h5>
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                              <p>Yakin hapus kehadiran ini?</p>
                              <p><strong><?php echo html_escape($row['nama_santri'] ?? $row['nim']); ?></strong><br>
                              <?php echo date('d M Y', strtotime($row['tanggal'])); ?> - <?php echo html_escape(Presensi_model::KEGIATAN_LIST[$row['kegiatan']] ?? $row['kegiatan']); ?></p>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                              <form method="post" action="<?php echo site_url('admin/kehadiran/manual_hapus/' . $row['id']); ?>" class="d-inline">
                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr id="noDataRow">
                    <td colspan="8" class="text-center text-muted">
                      Belum ada riwayat kehadiran manual
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Load Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize Select2 for santri dropdown
  $('.select2-santri').select2({
    theme: 'bootstrap4',
    placeholder: 'Pilih Santri',
    allowClear: true,
    width: '100%'
  });

  // Smart Bulk Input JavaScript
  const searchInput = document.getElementById('search_santri');
  const filterKamar = document.getElementById('filter_kamar');
  const selectAll = document.getElementById('select_all');
  const selectNone = document.getElementById('select_none');
  const selectKamar = document.getElementById('select_kamar');
  const selectedCount = document.getElementById('selected_count');
  const santriItems = document.querySelectorAll('.santri-item');
  const santriCheckboxes = document.querySelectorAll('.santri-checkbox');

  // Update selected count
  function updateSelectedCount() {
    const checked = document.querySelectorAll('.santri-checkbox:checked').length;
    selectedCount.textContent = checked;
  }

  // Filter santri by search and kamar
  function filterSantri() {
    const searchTerm = searchInput.value.toLowerCase();
    const selectedKamar = filterKamar.value;

    santriItems.forEach(item => {
      const searchData = item.getAttribute('data-search');
      const kamarData = item.getAttribute('data-kamar');
      
      const matchSearch = searchTerm === '' || searchData.includes(searchTerm);
      const matchKamar = selectedKamar === '' || kamarData === selectedKamar;
      
      if (matchSearch && matchKamar) {
        item.style.display = 'block';
      } else {
        item.style.display = 'none';
        const checkbox = item.querySelector('.santri-checkbox');
        if (checkbox) checkbox.checked = false;
      }
    });
    updateSelectedCount();
  }

  // Event listeners
  searchInput.addEventListener('input', filterSantri);
  filterKamar.addEventListener('change', filterSantri);

  selectAll.addEventListener('click', function() {
    santriItems.forEach(item => {
      if (item.style.display !== 'none') {
        const checkbox = item.querySelector('.santri-checkbox');
        if (checkbox) checkbox.checked = true;
      }
    });
    updateSelectedCount();
  });

  selectNone.addEventListener('click', function() {
    santriCheckboxes.forEach(cb => cb.checked = false);
    updateSelectedCount();
  });

  selectKamar.addEventListener('click', function() {
    const selectedKamar = filterKamar.value;
    if (selectedKamar === '') {
      alert('Pilih kamar terlebih dahulu');
      return;
    }
    santriItems.forEach(item => {
      const kamarData = item.getAttribute('data-kamar');
      if (kamarData === selectedKamar) {
        const checkbox = item.querySelector('.santri-checkbox');
        if (checkbox) checkbox.checked = true;
      }
    });
    updateSelectedCount();
  });

  // Update count on checkbox change
  santriCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
  });

  // Initial count
  updateSelectedCount();

  // Enhanced Search & Filter for Riwayat
  const searchRiwayat = document.getElementById('searchRiwayat');
  const filterKegiatan = document.getElementById('filterKegiatan');
  const filterTanggal = document.getElementById('filterTanggal');
  const clearBtn = document.getElementById('clearFilter');
  const riwayatRows = document.querySelectorAll('.riwayat-row');
  const noDataRow = document.getElementById('noDataRow');
  
  function filterRiwayat() {
    const searchTerm = searchRiwayat.value.toLowerCase();
    const selectedKegiatan = filterKegiatan.value;
    const selectedTanggal = filterTanggal.value;
    
    let visibleCount = 0;
    
    riwayatRows.forEach(row => {
      const nim = row.getAttribute('data-nim');
      const nama = row.getAttribute('data-nama');
      const kegiatan = row.getAttribute('data-kegiatan');
      const tanggal = row.getAttribute('data-tanggal');
      
      const matchSearch = searchTerm === '' || nim.includes(searchTerm) || nama.includes(searchTerm);
      const matchKegiatan = selectedKegiatan === '' || kegiatan === selectedKegiatan;
      const matchTanggal = selectedTanggal === '' || tanggal === selectedTanggal;
      
      if (matchSearch && matchKegiatan && matchTanggal) {
        row.style.display = 'table-row';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });
    
    // Show/hide no data message
    if (noDataRow) {
      if (visibleCount === 0 && riwayatRows.length > 0) {
        noDataRow.style.display = 'table-row';
        noDataRow.querySelector('td').textContent = 'Tidak ada data yang sesuai filter';
      } else {
        noDataRow.style.display = 'none';
      }
    }
  }
  
  // Event listeners for riwayat filter
  searchRiwayat.addEventListener('input', filterRiwayat);
  filterKegiatan.addEventListener('change', filterRiwayat);
  filterTanggal.addEventListener('change', filterRiwayat);
  
  // Clear filters
  clearBtn.addEventListener('click', function() {
    searchRiwayat.value = '';
    filterKegiatan.value = '';
    filterTanggal.value = '';
    filterRiwayat();
  });
});
</script>
