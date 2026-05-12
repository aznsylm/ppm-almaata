<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-dark">Perizinan Santri</h4>
    <div class="text-muted small">Ajukan izin, unduh surat, upload, dan pantau status.</div>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="<?php echo site_url('user/dashboard'); ?>" class="btn btn-outline-primary btn-sm">Dashboard</a>
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

<div class="row">
  <!-- ===================== FORM PENGAJUAN ===================== -->
  <div class="col-lg-5 mb-4">
    <div class="card">
      <div class="card-header"><strong>Ajukan Izin</strong></div>
      <div class="card-body">
        <form method="post" action="<?php echo site_url('user/perizinan/submit'); ?>" enctype="multipart/form-data" id="form_pengajuan">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

          <!-- TIPE IZIN -->
          <div class="form-group">
            <label>Jenis Izin</label>
            <select name="tipe_izin" id="tipe_izin" class="form-control" required>
              <option value="">-- Pilih Jenis Izin --</option>
              <option value="1">Meninggalkan Asrama Kurang dari Dua Minggu</option>
              <option value="2">Meninggalkan Asrama Lebih dari Dua Minggu</option>
              <option value="3">Tidak Mengikuti Sholat Berjamaah / Ngaji</option>
            </select>
          </div>

          <!-- TANGGAL MULAI -->
          <div class="form-group" id="wrap_tgl_mulai" style="display:none;">
            <label>Tanggal <span id="label_tgl_mulai">Mulai</span></label>
            <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control">
          </div>

          <!-- TANGGAL SELESAI (tipe 1 & 2) -->
          <div class="form-group" id="wrap_tgl_selesai" style="display:none;">
            <label>Tanggal Selesai</label>
            <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control">
            <small class="text-danger d-block mt-1" id="info_durasi" style="display:none;"></small>
          </div>

          <!-- KATEGORI (tipe 3) -->
          <div class="form-group" id="wrap_kategori" style="display:none;">
            <label>Kategori <small class="text-muted">(boleh pilih lebih dari satu)</small></label>
            <div style="border:1px solid #dee2e6; border-radius:4px; padding:10px;">
              <?php foreach ($kategori_map as $key => $label): ?>
                <div class="form-check mb-1">
                  <input class="form-check-input kategori-cb" type="checkbox" name="sub_kategori[]" id="kat_<?php echo $key; ?>" value="<?php echo $key; ?>">
                  <label class="form-check-label" for="kat_<?php echo $key; ?>"><?php echo $label; ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- ALASAN (tipe 3) -->
          <div class="form-group" id="wrap_alasan_tipe3" style="display:none;">
            <label>Alasan <small class="text-muted">(boleh pilih lebih dari satu)</small></label>
            <div style="border:1px solid #dee2e6; border-radius:4px; padding:10px;">
              <?php foreach (Perizinan_model::get_alasan_tipe3() as $alasan): ?>
                <div class="form-check mb-1">
                  <input class="form-check-input alasan-tipe3-cb" type="checkbox" name="alasan_option[]" id="alasan_<?php echo strtolower($alasan); ?>" value="<?php echo $alasan; ?>">
                  <label class="form-check-label" for="alasan_<?php echo strtolower($alasan); ?>"><?php echo $alasan; ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- UPLOAD DOKUMENTASI OPSIONAL (tipe 3, alasan kerkom/rapat/kuliah) -->
          <div class="form-group" id="wrap_dok_awal" style="display:none;">
            <label>Upload Dokumentasi <small class="text-muted">(opsional, bisa menyusul di riwayat)</small></label>
            <input type="file" name="dokumentasi" id="dok_awal" class="form-control-file">
            <small class="text-muted">Format: PDF/JPG/PNG, maks 2MB</small>
          </div>

          <!-- ALASAN TIPE 1 & 2 (checkbox, multi-pilih + alasan lainnya di akhir) -->
          <div class="form-group" id="wrap_alasan_tipe12" style="display:none;">
            <label>Alasan <small class="text-muted">(boleh pilih lebih dari satu)</small></label>
            <div style="border:1px solid #dee2e6; border-radius:4px; padding:10px;" id="alasan_tipe12_container">
              <!-- diisi JS dari alasan_options -->
            </div>
            <div class="mt-2">
              <label class="small">Alasan Lainnya <small class="text-muted">(opsional)</small></label>
              <textarea name="alasan_lainnya" id="alasan_lainnya" class="form-control form-control-sm" rows="2" placeholder="Tuliskan alasan tambahan jika ada..."></textarea>
            </div>
          </div>

          <!-- SEMESTER -->
          <div class="form-group" id="wrap_smt" style="display:none;">
            <label>Semester</label>
            <input type="number" name="smt" id="smt" class="form-control" min="1" max="99">
          </div>

          <button type="submit" class="btn btn-primary btn-block" id="btn_submit" style="display:none;">Kirim Pengajuan</button>
        </form>
      </div>
    </div>
  </div>

  <!-- ===================== RIWAYAT IZIN ===================== -->
  <div class="col-lg-7 mb-4">
    <div class="card">
      <div class="card-header"><strong>Riwayat Izin</strong></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Tanggal/Periode</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pengajuan izin.</td></tr>
              <?php else: ?>
                <?php foreach ($rows as $row): ?>
                  <?php
                    $tipe      = isset($row['tipe_izin']) ? (string) $row['tipe_izin'] : '';
                    $status    = (string) $row['status'];
                    $alasan    = isset($row['alasan']) ? (string) $row['alasan'] : '';
                    $sub_kat   = isset($row['sub_kategori']) ? (string) $row['sub_kategori'] : '';
                    $sub_list  = array_values(array_filter(array_map('trim', explode(',', $sub_kat)), 'strlen'));
                    $perlu_dok = ($tipe === '3') && $this->Perizinan_model->is_perlu_dokumentasi($alasan);
                    $punya_dok = !empty($row['dokumentasi']);
                    $punya_surat = !empty($row['file_upload']);

                    // Periode
                    if ($tipe === '1' || $tipe === '2') {
                      $periode = html_escape($row['tgl_mulai']) . ' s/d ' . html_escape($row['tgl_selesai']);
                    } else {
                      $periode = html_escape($row['tgl_mulai']);
                    }

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

                    $status_label = isset($status_map[$status]) ? $status_map[$status] : $status;

                    // Apakah bisa aksi upload (status 0, 1, 4)
                    $bisa_upload = in_array($status, array('0', '1', '4'), TRUE);
                    $bisa_upload_dok = in_array($status, array('0', '1', '4', '5'), TRUE);
                  ?>
                  <tr>
                    <td class="small"><?php echo $periode; ?></td>
                    <td class="small"><?php echo $ket; ?></td>
                    <td><?php echo $status_label; ?></td>
                    <td>
                      <?php if ($bisa_upload): ?>
                        <!-- Tombol Download -->
                        <a href="<?php echo site_url('user/perizinan/download/' . rawurlencode($row['id'])); ?>"
                           class="btn btn-info btn-xs mb-1" title="Unduh Surat">
                          <i class="fas fa-download"></i> Unduh
                        </a>

                        <!-- Tombol Upload Surat: langsung trigger file picker -->
                        <form method="post" action="<?php echo site_url('user/perizinan/upload-surat/' . rawurlencode($row['id'])); ?>" enctype="multipart/form-data" id="form_surat_<?php echo $row['id']; ?>" style="display:inline;">
                          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                          <input type="file" name="surat_file" id="file_surat_<?php echo $row['id']; ?>" accept=".pdf" style="display:none;"
                                 onchange="submitIfSelected(this, 'form_surat_<?php echo $row['id']; ?>')">
                          <button type="button" class="btn btn-warning btn-xs mb-1"
                                  onclick="document.getElementById('file_surat_<?php echo $row['id']; ?>').click()">
                            <i class="fas fa-upload"></i> Upload Surat
                          </button>
                        </form>

                        <!-- Tombol Upload Dokumentasi: langsung trigger file picker -->
                        <?php if ($perlu_dok): ?>
                          <form method="post" action="<?php echo site_url('user/perizinan/upload-dokumentasi/' . rawurlencode($row['id'])); ?>" enctype="multipart/form-data" id="form_dok_<?php echo $row['id']; ?>" style="display:inline;">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <input type="file" name="dokumentasi_file" id="file_dok_<?php echo $row['id']; ?>" accept=".pdf,.jpg,.jpeg,.png" style="display:none;"
                                   onchange="submitIfSelected(this, 'form_dok_<?php echo $row['id']; ?>')">
                            <button type="button" class="btn btn-<?php echo $punya_dok ? 'secondary' : 'outline-secondary'; ?> btn-xs mb-1"
                                    onclick="document.getElementById('file_dok_<?php echo $row['id']; ?>').click()">
                              <i class="fas fa-file-alt"></i> <?php echo $punya_dok ? 'Ganti Dok.' : 'Upload Dok.'; ?>
                            </button>
                          </form>
                        <?php endif; ?>

                      <?php elseif ($status === '5'): ?>
                        <!-- Menunggu Dokumentasi: langsung trigger file picker -->
                        <span class="text-muted small d-block mb-1">Surat sudah diupload</span>
                        <form method="post" action="<?php echo site_url('user/perizinan/upload-dokumentasi/' . rawurlencode($row['id'])); ?>" enctype="multipart/form-data" id="form_dok_<?php echo $row['id']; ?>" style="display:inline;">
                          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                          <input type="file" name="dokumentasi_file" id="file_dok_<?php echo $row['id']; ?>" accept=".pdf,.jpg,.jpeg,.png" style="display:none;"
                                 onchange="submitIfSelected(this, 'form_dok_<?php echo $row['id']; ?>')">
                          <button type="button" class="btn btn-warning btn-xs mb-1"
                                  onclick="document.getElementById('file_dok_<?php echo $row['id']; ?>').click()">
                            <i class="fas fa-file-alt"></i> Upload Dok.
                          </button>
                        </form>

                      <?php elseif ($status === '2'): ?>
                        <span class="text-muted small">Menunggu validasi admin</span>
                        <?php if ($punya_dok): ?>
                          <a href="<?php echo site_url('user/perizinan/dokumentasi/' . rawurlencode($row['id'])); ?>"
                             class="btn btn-outline-secondary btn-xs" target="_blank">Lihat Dok.</a>
                        <?php endif; ?>

                      <?php elseif ($status === '3'): ?>
                        <a href="<?php echo site_url('user/perizinan/cetak/' . rawurlencode($row['id'])); ?>"
                           class="btn btn-success btn-xs" target="_blank">
                          <i class="fas fa-print"></i> Cetak
                        </a>
                        <?php if ($punya_dok): ?>
                          <a href="<?php echo site_url('user/perizinan/dokumentasi/' . rawurlencode($row['id'])); ?>"
                             class="btn btn-outline-secondary btn-xs" target="_blank">Lihat Dok.</a>
                        <?php endif; ?>

                      <?php else: ?>
                        <span class="text-muted small">-</span>
                      <?php endif; ?>

                      <!-- hidden form upload sudah inline di tombol masing-masing, tidak perlu div toggle lagi -->


                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-end mt-2">
  <?php echo isset($pagination) ? $pagination : ''; ?>
</div>

<script>
(function () {
  var elTipe       = document.getElementById('tipe_izin');
  var elTglMulai   = document.getElementById('tgl_mulai');
  var elTglSelesai = document.getElementById('tgl_selesai');
  var elInfoDurasi = document.getElementById('info_durasi');
  var elBtnSubmit  = document.getElementById('btn_submit');

  var ALASAN_OPTIONS = <?php echo json_encode($alasan_options); ?>;
  var PERLU_DOK_ALASAN = ['kerkom', 'rapat', 'kuliah'];

  function show(id) { var el = document.getElementById(id); if (el) el.style.display = ''; }
  function hide(id) { var el = document.getElementById(id); if (el) el.style.display = 'none'; }

  function hideAll() {
    ['wrap_tgl_mulai','wrap_tgl_selesai','wrap_kategori','wrap_alasan_tipe3',
     'wrap_dok_awal','wrap_alasan_tipe12','wrap_smt','btn_submit',
     'info_durasi'].forEach(hide);
  }

  function buildAlasanTipe12() {
    var container = document.getElementById('alasan_tipe12_container');
    if (!container) return;
    var html = '';
    ALASAN_OPTIONS.forEach(function (opt, i) {
      var id = 'alasan12_' + i;
      html += '<div class="form-check mb-1">' +
        '<input class="form-check-input" type="checkbox" name="alasan_option[]" id="' + id + '" value="' + opt + '">' +
        '<label class="form-check-label" for="' + id + '">' + opt + '</label>' +
        '</div>';
    });
    container.innerHTML = html;
  }

  elTipe.addEventListener('change', function () {
    hideAll();
    var tipe = this.value;
    if (!tipe) return;

    // Semua tipe butuh tanggal & semester
    document.getElementById('label_tgl_mulai').textContent = (tipe === '3') ? 'Izin' : 'Mulai';
    show('wrap_tgl_mulai');
    show('wrap_smt');
    show('btn_submit');

    if (tipe === '1' || tipe === '2') {
      show('wrap_tgl_selesai');
      show('wrap_alasan_tipe12');
      buildAlasanTipe12();
    } else if (tipe === '3') {
      show('wrap_kategori');
      show('wrap_alasan_tipe3');
      // wrap_dok_awal ditampilkan dinamis saat alasan dipilih
    }
  });

  // Validasi durasi tipe 1 & 2
  function cekDurasi() {
    var tipe = elTipe.value;
    if ((tipe !== '1' && tipe !== '2') || !elTglMulai.value || !elTglSelesai.value) return;

    var start  = new Date(elTglMulai.value + 'T00:00:00');
    var end    = new Date(elTglSelesai.value + 'T00:00:00');
    var durasi = Math.floor((end - start) / 86400000) + 1;

    elInfoDurasi.style.display = '';
    elBtnSubmit.disabled = false;

    if (end < start) {
      elInfoDurasi.textContent = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
      elBtnSubmit.disabled = true;
    } else if (tipe === '1' && durasi > 14) {
      elInfoDurasi.textContent = 'Maksimal 14 hari untuk izin kurang dari 2 minggu. (saat ini ' + durasi + ' hari)';
      elBtnSubmit.disabled = true;
    } else if (tipe === '2' && durasi < 15) {
      elInfoDurasi.textContent = 'Minimal 15 hari untuk izin lebih dari 2 minggu. (saat ini ' + durasi + ' hari)';
      elBtnSubmit.disabled = true;
    } else {
      elInfoDurasi.textContent = 'Durasi: ' + durasi + ' hari.';
      elBtnSubmit.disabled = false;
    }
  }

  elTglMulai.addEventListener('change', cekDurasi);
  elTglSelesai.addEventListener('change', cekDurasi);

  // Tampilkan field dokumentasi jika alasan kerkom/rapat/kuliah dipilih (tipe 3)
  document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('alasan-tipe3-cb')) return;

    var checked = Array.from(document.querySelectorAll('.alasan-tipe3-cb:checked'))
                       .map(function (cb) { return cb.value.toLowerCase(); });

    var butuhDok = checked.some(function (a) {
      return PERLU_DOK_ALASAN.indexOf(a) !== -1;
    });

    if (butuhDok) {
      show('wrap_dok_awal');
    } else {
      hide('wrap_dok_awal');
    }
  });
})();

// Auto-submit form setelah file dipilih
function submitIfSelected(input, formId) {
  if (input.files && input.files.length > 0) {
    var form = document.getElementById(formId);
    if (form) form.submit();
  }
}
</script>
