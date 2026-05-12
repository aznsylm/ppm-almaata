<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-dark">Validasi Perizinan</h4>
    <div class="text-muted small">Kelola dan validasi pengajuan izin santri.</div>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="<?php echo site_url('admin/backup'); ?>" class="btn btn-outline-success btn-sm mr-1">Backup Data</a>
    <a href="<?php echo site_url('admin/dashboard'); ?>" class="btn btn-outline-primary btn-sm">Kembali</a>
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

<!-- Summary -->
<div class="row mb-3">
  <div class="col-md-6 mb-2">
    <div class="info-box mb-0">
      <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Santri Aktif Saat Ini</span>
        <span class="info-box-number"><?php echo (int) (isset($status_summary['total_aktif']) ? $status_summary['total_aktif'] : 0); ?></span>
      </div>
    </div>
  </div>
  <div class="col-md-6 mb-2">
    <div class="info-box mb-0">
      <span class="info-box-icon bg-warning"><i class="fas fa-door-open"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Santri Sedang Izin</span>
        <span class="info-box-number"><?php echo (int) (isset($status_summary['total_izin']) ? $status_summary['total_izin'] : 0); ?></span>
      </div>
    </div>
  </div>
</div>

<!-- Filter -->
<?php
  $active_filters = array_filter(array(
    isset($filters['status'])       && $filters['status']       !== '' ? 1 : 0,
    isset($filters['tipe_izin'])    && $filters['tipe_izin']    !== '' ? 1 : 0,
    isset($filters['kamar'])        && $filters['kamar']        !== '' ? 1 : 0,
    isset($filters['smt'])          && $filters['smt']          !== '' ? 1 : 0,
    isset($filters['alasan'])       && $filters['alasan']       !== '' ? 1 : 0,
    isset($filters['sub_kategori']) && $filters['sub_kategori'] !== '' ? 1 : 0,
    isset($filters['tgl_dari'])     && $filters['tgl_dari']     !== '' ? 1 : 0,
    isset($filters['tgl_sampai'])   && $filters['tgl_sampai']   !== '' ? 1 : 0,
  ));
  $filter_count = array_sum($active_filters);
?>
<form method="get" action="<?php echo site_url('admin/perizinan'); ?>" id="form_filter">
  <div class="card mb-3">
    <div class="card-body py-2">
      <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:6px;">

        <!-- Kiri: Search + Filter toggle -->
        <div class="d-flex align-items-center" style="gap:6px;">
          <input type="text" name="q" class="form-control form-control-sm"
                 style="width:220px;"
                 value="<?php echo html_escape(isset($filters['q']) ? $filters['q'] : ''); ?>"
                 placeholder="Cari NIM / Nama...">

          <div class="position-relative">
            <button type="button" class="btn btn-secondary btn-sm" id="btn_filter_toggle">
              <i class="fas fa-sliders-h"></i> Filter
              <?php if ($filter_count > 0): ?>
                <span class="badge badge-light ml-1"><?php echo $filter_count; ?></span>
              <?php endif; ?>
            </button>

            <!-- Panel Filter -->
            <div id="filter_panel" class="shadow border bg-white rounded p-3"
                 style="display:none; position:absolute; left:0; top:calc(100% + 6px); width:320px; z-index:1050;">
              <div class="form-group mb-2">
                <label class="small mb-1">Status</label>
                <select name="status" class="form-control form-control-sm">
                  <option value="">Semua Status</option>
                  <?php foreach ($status_map as $k => $lbl): ?>
                    <option value="<?php echo $k; ?>" <?php echo (isset($filters['status']) && (string) $filters['status'] === (string) $k) ? 'selected' : ''; ?>>
                      <?php echo html_escape($lbl); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group mb-2">
                <label class="small mb-1">Jenis Izin</label>
                <select name="tipe_izin" class="form-control form-control-sm">
                  <option value="">Semua Jenis</option>
                  <option value="1" <?php echo (isset($filters['tipe_izin']) && $filters['tipe_izin'] === '1') ? 'selected' : ''; ?>>Kurang 2 Minggu</option>
                  <option value="2" <?php echo (isset($filters['tipe_izin']) && $filters['tipe_izin'] === '2') ? 'selected' : ''; ?>>Lebih 2 Minggu</option>
                  <option value="3" <?php echo (isset($filters['tipe_izin']) && $filters['tipe_izin'] === '3') ? 'selected' : ''; ?>>Jamaah / Ngaji</option>
                </select>
              </div>
              <div class="form-group mb-2">
                <label class="small mb-1">Kamar</label>
                <select name="kamar" class="form-control form-control-sm">
                  <option value="">Semua Kamar</option>
                  <?php foreach ($kamar_list as $kmr): ?>
                    <option value="<?php echo html_escape($kmr); ?>" <?php echo (isset($filters['kamar']) && $filters['kamar'] === $kmr) ? 'selected' : ''; ?>>
                      <?php echo html_escape($kmr); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group mb-2">
                <label class="small mb-1">Semester</label>
                <select name="smt" class="form-control form-control-sm">
                  <option value="">Semua Semester</option>
                  <?php for ($s = 1; $s <= 14; $s++): ?>
                    <option value="<?php echo $s; ?>" <?php echo (isset($filters['smt']) && (int) $filters['smt'] === $s) ? 'selected' : ''; ?>>
                      Semester <?php echo $s; ?>
                    </option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="form-group mb-2">
                <label class="small mb-1">Alasan</label>
                <select name="alasan" class="form-control form-control-sm">
                  <option value="">Semua Alasan</option>
                  <?php foreach ($alasan_list as $al): ?>
                    <option value="<?php echo html_escape($al); ?>" <?php echo (isset($filters['alasan']) && $filters['alasan'] === $al) ? 'selected' : ''; ?>>
                      <?php echo html_escape($al); ?>
                    </option>
                  <?php endforeach; ?>
                  <?php foreach (Perizinan_model::get_alasan_tipe3() as $al): ?>
                    <?php if (!in_array($al, $alasan_list, TRUE)): ?>
                      <option value="<?php echo html_escape($al); ?>" <?php echo (isset($filters['alasan']) && $filters['alasan'] === $al) ? 'selected' : ''; ?>>
                        <?php echo html_escape($al); ?>
                      </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group mb-2">
                <label class="small mb-1">Kategori (Jamaah/Ngaji)</label>
                <select name="sub_kategori" class="form-control form-control-sm">
                  <option value="">Semua Kategori</option>
                  <?php foreach ($kategori_map as $key => $lbl): ?>
                    <option value="<?php echo html_escape($key); ?>" <?php echo (isset($filters['sub_kategori']) && $filters['sub_kategori'] === $key) ? 'selected' : ''; ?>>
                      <?php echo html_escape($lbl); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group mb-2">
                <label class="small mb-1">Dari Tanggal</label>
                <input type="date" name="tgl_dari" class="form-control form-control-sm"
                       value="<?php echo html_escape(isset($filters['tgl_dari']) ? $filters['tgl_dari'] : ''); ?>">
              </div>
              <div class="form-group mb-3">
                <label class="small mb-1">Sampai Tanggal</label>
                <input type="date" name="tgl_sampai" class="form-control form-control-sm"
                       value="<?php echo html_escape(isset($filters['tgl_sampai']) ? $filters['tgl_sampai'] : ''); ?>">
              </div>
              <div class="d-flex" style="gap:6px;">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">Terapkan</button>
                <?php if ($filter_count > 0): ?>
                  <a href="<?php echo site_url('admin/perizinan'); ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
          <?php if ($filter_count > 0 || (isset($filters['q']) && $filters['q'] !== '')): ?>
            <a href="<?php echo site_url('admin/perizinan'); ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
          <?php endif; ?>
        </div>

        <!-- Kanan: aksi data -->
        <div class="d-flex align-items-center" style="gap:6px;">
          <button type="button" id="btn_toggle_delete" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-trash-alt"></i> Hapus Pilihan
          </button>
          <?php
            $has_filter = FALSE;
            foreach ($filters as $fval) {
              if ($fval !== '') { $has_filter = TRUE; break; }
            }
          ?>
          <?php if ($has_filter): ?>
          <div class="dropdown">
            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
              <i class="fas fa-database"></i> Data Filter
            </button>
            <div class="dropdown-menu dropdown-menu-right">
              <a class="dropdown-item"
                 href="<?php echo site_url('admin/perizinan/backup-filtered?' . http_build_query(array_filter($filters))); ?>">
                <i class="fas fa-download mr-2"></i>Backup CSV
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item text-danger" href="#"
                 data-toggle="modal" data-target="#modalHapusFiltered">
                <i class="fas fa-trash mr-2"></i>Hapus Permanen
              </a>
            </div>
          </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</form>

<!-- Bulk delete form -->
<form id="form_bulk_delete" method="post"
      action="<?php echo site_url('admin/perizinan/delete-selected'); ?>"
      onsubmit="return confirm('Hapus data yang dipilih? Tindakan ini tidak bisa dibatalkan.');">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <input type="hidden" name="q"           value="<?php echo html_escape(isset($filters['q']) ? $filters['q'] : ''); ?>">
  <input type="hidden" name="status"      value="<?php echo html_escape(isset($filters['status']) ? $filters['status'] : ''); ?>">
  <input type="hidden" name="tipe_izin"   value="<?php echo html_escape(isset($filters['tipe_izin']) ? $filters['tipe_izin'] : ''); ?>">
  <input type="hidden" name="kamar"       value="<?php echo html_escape(isset($filters['kamar']) ? $filters['kamar'] : ''); ?>">
  <input type="hidden" name="smt"         value="<?php echo html_escape(isset($filters['smt']) ? $filters['smt'] : ''); ?>">
  <input type="hidden" name="alasan"      value="<?php echo html_escape(isset($filters['alasan']) ? $filters['alasan'] : ''); ?>">
  <input type="hidden" name="sub_kategori" value="<?php echo html_escape(isset($filters['sub_kategori']) ? $filters['sub_kategori'] : ''); ?>">
  <input type="hidden" name="tgl_dari"    value="<?php echo html_escape(isset($filters['tgl_dari']) ? $filters['tgl_dari'] : ''); ?>">
  <input type="hidden" name="tgl_sampai"  value="<?php echo html_escape(isset($filters['tgl_sampai']) ? $filters['tgl_sampai'] : ''); ?>">
</form>

<!-- Tabel -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th class="delete-col d-none" style="width:36px;"><input type="checkbox" id="check_all"></th>
            <th style="width:36px;">#</th>
            <th>Santri</th>
            <th>Jenis / Keterangan</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Surat</th>
            <th>Dokumentasi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data.</td></tr>
          <?php else: ?>
            <?php $no = 1; ?>
            <?php foreach ($rows as $row): ?>
              <?php
                $tipe      = isset($row['tipe_izin']) ? (string) $row['tipe_izin'] : '';
                $status    = (string) $row['status'];
                $alasan    = isset($row['alasan']) ? (string) $row['alasan'] : '';
                $sub_kat   = isset($row['sub_kategori']) ? (string) $row['sub_kategori'] : '';
                $sub_list  = array_values(array_filter(array_map('trim', explode(',', $sub_kat)), 'strlen'));
                $perlu_dok = ($tipe === '3') && $this->Perizinan_model->is_perlu_dokumentasi($alasan);
                $punya_dok = !empty($row['dokumentasi']);

                // Label jenis izin
                $tipe_label = array('1' => 'Kurang 2 Minggu', '2' => 'Lebih 2 Minggu', '3' => 'Jamaah/Ngaji');
                $jenis = isset($tipe_label[$tipe]) ? $tipe_label[$tipe] : '-';

                // Keterangan
                if ($tipe === '3') {
                  $kat_labels = array();
                  foreach ($sub_list as $s) {
                    $kat_labels[] = isset($kategori_map[$s]) ? $kategori_map[$s] : ucfirst(str_replace('_', ' ', $s));
                  }
                  $ket = (!empty($kat_labels) ? implode(', ', $kat_labels) . ' — ' : '') . html_escape($alasan);
                } else {
                  $ket = html_escape($alasan);
                  if (!empty($row['alasan_lainnya'])) {
                    $ket .= ' <small class="text-muted">(' . html_escape($row['alasan_lainnya']) . ')</small>';
                  }
                }

                // Periode
                if ($tipe === '1' || $tipe === '2') {
                  $periode = html_escape($row['tgl_mulai']) . ' s/d ' . html_escape($row['tgl_selesai']);
                } else {
                  $periode = html_escape($row['tgl_mulai']);
                }

                $status_label = isset($status_map[$status]) ? $status_map[$status] : $status;
              ?>
              <tr>
                <td class="delete-col d-none">
                  <input type="checkbox" form="form_bulk_delete" name="selected_ids[]"
                         value="<?php echo html_escape($row['id']); ?>" class="row-check">
                </td>
                <td class="small text-muted"><?php echo $no++; ?></td>
                <td class="small">
                  <strong><?php echo html_escape($row['nim']); ?></strong><br>
                  <span class="text-muted"><?php echo html_escape(isset($row['nama']) ? $row['nama'] : '-'); ?></span>
                </td>
                <td class="small">
                  <span class="border px-1"><?php echo $jenis; ?></span><br>
                  <?php echo $ket; ?>
                </td>
                <td class="small"><?php echo $periode; ?></td>
                <td class="small"><?php echo $status_label; ?></td>

                <!-- Kolom Surat -->
                <td class="small">
                  <?php if (!empty($row['file_upload'])): ?>
                    <a href="<?php echo base_url('uploads/perizinan/' . rawurlencode($row['file_upload'])); ?>"
                       target="_blank" class="btn btn-outline-info btn-xs">
                      <i class="fas fa-file-pdf"></i> Lihat
                    </a>
                    <div class="text-muted" style="font-size:11px;">
                      <?php echo !empty($row['file_upload_at']) ? date('d/m/Y H:i', strtotime($row['file_upload_at'])) : ''; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-muted">Belum ada</span>
                  <?php endif; ?>
                </td>

                <!-- Kolom Dokumentasi -->
                <td class="small">
                  <?php if ($punya_dok): ?>
                    <?php $dok_ext = strtolower(pathinfo($row['dokumentasi'], PATHINFO_EXTENSION)); ?>
                    <?php if (in_array($dok_ext, array('jpg','jpeg','png'), TRUE)): ?>
                      <a href="<?php echo base_url('uploads/perizinan/' . rawurlencode($row['dokumentasi'])); ?>" target="_blank">
                        <img src="<?php echo base_url('uploads/perizinan/' . rawurlencode($row['dokumentasi'])); ?>"
                             style="max-width:60px; max-height:60px; object-fit:cover; border:1px solid #dee2e6; border-radius:3px;">
                      </a>
                    <?php else: ?>
                      <a href="<?php echo base_url('uploads/perizinan/' . rawurlencode($row['dokumentasi'])); ?>"
                         target="_blank" class="btn btn-outline-secondary btn-xs">
                        <i class="fas fa-file"></i> Lihat
                      </a>
                    <?php endif; ?>
                    <div class="text-muted" style="font-size:11px;">
                      <?php echo !empty($row['dokumentasi_at']) ? date('d/m/Y H:i', strtotime($row['dokumentasi_at'])) : ''; ?>
                    </div>
                  <?php elseif ($perlu_dok): ?>
                    <span class="text-warning small">Belum ada</span>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>

                <!-- Kolom Aksi -->
                <td>
                  <?php if ($status === '2'): ?>
                    <div class="d-flex flex-column" style="gap:4px;">
                      <form method="post" action="<?php echo site_url('admin/perizinan/validate/' . rawurlencode($row['id'])); ?>"
                            onsubmit="return confirm('Setujui pengajuan ini?');" style="display:inline;">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <input type="hidden" name="decision" value="3">
                        <button type="submit" class="btn btn-success btn-xs"><i class="fas fa-check"></i> Setujui</button>
                      </form>
                      <form method="post" action="<?php echo site_url('admin/perizinan/validate/' . rawurlencode($row['id'])); ?>"
                            onsubmit="return confirm('Tolak pengajuan ini?');" style="display:inline;">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <input type="hidden" name="decision" value="4">
                        <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-times"></i> Tolak</button>
                      </form>
                    </div>

                  <?php elseif ($status === '5'): ?>
                    <span class="text-warning small">Menunggu dokumentasi dari santri</span>

                  <?php elseif ((string) $row['acc'] === '1'): ?>
                    <?php $today = date('Y-m-d'); ?>
                    <?php $masih_aktif = ($tipe === '1' || $tipe === '2') && $today <= $row['tgl_selesai']; ?>
                    <?php if ($masih_aktif): ?>
                      <?php if (!empty($row['is_suspended'])): ?>
                        <form method="post" action="<?php echo site_url('admin/perizinan/lanjutkan-izin/' . rawurlencode($row['id'])); ?>"
                              onsubmit="return confirm('Lanjutkan izin ini?');" style="display:inline;">
                          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                          <button type="submit" class="btn btn-warning btn-xs">Lanjutkan</button>
                        </form>
                      <?php else: ?>
                        <form method="post" action="<?php echo site_url('admin/perizinan/selesaikan-izin/' . rawurlencode($row['id'])); ?>"
                              onsubmit="return confirm('Selesaikan izin ini?');" style="display:inline;">
                          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                          <button type="submit" class="btn btn-secondary btn-xs">Selesaikan</button>
                        </form>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-success small">Disetujui</span>
                    <?php endif; ?>

                  <?php else: ?>
                    <span class="text-muted small">-</span>
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
    <button type="submit" form="form_bulk_delete" id="btn_bulk_delete"
            class="btn btn-danger btn-sm d-none"
            onclick="return confirm('Hapus data yang dipilih?');">
      Hapus Terpilih
    </button>
    <div><?php echo isset($pagination) ? $pagination : ''; ?></div>
  </div>
</div>

<!-- Modal Konfirmasi Hapus Filter -->
<div class="modal fade" id="modalHapusFiltered" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle"></i> Hapus Data Sesuai Filter</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form method="post" action="<?php echo site_url('admin/perizinan/hapus-filtered'); ?>">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
        <?php foreach ($filters as $fkey => $fval): ?>
          <?php if ($fval !== ''): ?>
            <input type="hidden" name="<?php echo html_escape($fkey); ?>" value="<?php echo html_escape($fval); ?>">
          <?php endif; ?>
        <?php endforeach; ?>
        <div class="modal-body">
          <p class="text-danger">Tindakan ini akan <strong>menghapus permanen</strong> semua data izin yang sesuai dengan filter aktif saat ini.</p>
          <p class="small text-muted mb-3">Pastikan sudah download backup sebelum menghapus.</p>
          <div class="form-group mb-0">
            <label>Ketik <strong>HAPUS</strong> untuk konfirmasi</label>
            <input type="text" name="konfirmasi" class="form-control" placeholder="Ketik HAPUS" autocomplete="off" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Hapus Permanen</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function () {
  var checkAll      = document.getElementById('check_all');
  var rowChecks     = document.querySelectorAll('.row-check');
  var deleteCols    = document.querySelectorAll('.delete-col');
  var btnToggle     = document.getElementById('btn_toggle_delete');
  var btnBulkDelete = document.getElementById('btn_bulk_delete');
  var deleteMode    = false;

  function setDeleteMode(on) {
    deleteMode = on;
    deleteCols.forEach(function (el) { el.classList.toggle('d-none', !on); });
    if (btnBulkDelete) btnBulkDelete.classList.toggle('d-none', !on);
    if (btnToggle) {
      btnToggle.textContent = on ? 'Batal' : 'Hapus';
      btnToggle.classList.toggle('btn-danger', !on);
      btnToggle.classList.toggle('btn-outline-danger', on);
    }
    if (!on && checkAll) {
      checkAll.checked = false;
      rowChecks.forEach(function (cb) { cb.checked = false; });
    }
  }

  if (checkAll) {
    checkAll.addEventListener('change', function () {
      rowChecks.forEach(function (cb) { cb.checked = checkAll.checked; });
    });
  }

  if (btnToggle) {
    btnToggle.addEventListener('click', function () { setDeleteMode(!deleteMode); });
  }

  // Filter panel toggle
  var btnFilterToggle = document.getElementById('btn_filter_toggle');
  var filterPanel     = document.getElementById('filter_panel');

  if (btnFilterToggle && filterPanel) {
    btnFilterToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      filterPanel.style.display = filterPanel.style.display === 'none' ? '' : 'none';
    });

    // Tutup panel jika klik di luar
    document.addEventListener('click', function (e) {
      if (!filterPanel.contains(e.target) && e.target !== btnFilterToggle) {
        filterPanel.style.display = 'none';
      }
    });

    // Auto-close setelah submit (Terapkan Filter)
    var formFilter = document.getElementById('form_filter');
    if (formFilter) {
      formFilter.addEventListener('submit', function () {
        filterPanel.style.display = 'none';
      });
    }
  }

  setDeleteMode(false);
})();
</script>