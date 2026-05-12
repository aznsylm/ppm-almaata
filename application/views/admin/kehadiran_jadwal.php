<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-dark">Kelola Jadwal Kehadiran</h4>
    <div class="text-muted small">Atur waktu mulai dan selesai untuk setiap kegiatan.</div>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="<?php echo site_url('admin/kehadiran'); ?>" class="btn btn-sm btn-outline-secondary">Kembali</a>
  </div>
</div>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Jadwal Kegiatan</h5>
        </div>
        <div class="card-body">
          <form method="post" action="<?php echo site_url('admin/kehadiran/update_jadwal'); ?>">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Kegiatan</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($jadwal_list as $row): ?>
                  <tr>
                    <td>
                      <strong><?php echo html_escape(isset(Presensi_model::KEGIATAN_LIST[$row['kegiatan']]) ? Presensi_model::KEGIATAN_LIST[$row['kegiatan']] : $row['kegiatan']); ?></strong>
                      <input type="hidden" name="jadwal[<?php echo (int)$row['id']; ?>][kegiatan]" value="<?php echo html_escape($row['kegiatan']); ?>">
                    </td>
                    <td>
                      <input type="time" class="form-control form-control-sm" name="jadwal[<?php echo (int)$row['id']; ?>][jam_mulai]" value="<?php echo html_escape($row['jam_mulai']); ?>" required>
                    </td>
                    <td>
                      <input type="time" class="form-control form-control-sm" name="jadwal[<?php echo (int)$row['id']; ?>][jam_selesai]" value="<?php echo html_escape($row['jam_selesai']); ?>" required>
                    </td>
                    <td>
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="jadwal[<?php echo (int)$row['id']; ?>][is_active]" value="1"<?php echo ((int)$row['is_active'] === 1) ? ' checked' : ''; ?> id="active_<?php echo $row['id']; ?>">
                        <label class="form-check-label" for="active_<?php echo $row['id']; ?>">
                          <?php echo ((int)$row['is_active'] === 1) ? 'Aktif' : 'Nonaktif'; ?>
                        </label>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
              <a href="<?php echo site_url('admin/kehadiran'); ?>" class="btn btn-secondary">Batal</a>
            </div>
          </form>

          <div class="alert alert-info mt-4">
            <strong>Catatan:</strong>
            <ul class="mb-0">
              <li>Waktu harus dalam format HH:MM (contoh: 17:45, 04:30)</li>
              <li>Jika kehadiran diluar waktu yang ditentukan, santri akan mendapat pesan error</li>
              <li>Hanya kegiatan yang aktif yang bisa dilakukan kehadiran</li>
              <li>Perubahan jadwal berlaku langsung tanpa perlu restart</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
