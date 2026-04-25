<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-dark">Data Santri</h4>
    <div class="small-text">Data santri pondok berdasarkan data mahasiswa yang sudah ada di Universitas Alma Ata.</div>
  </div>
  <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row align-self-stretch">
    <a href="<?php echo site_url('admin/dashboard'); ?>" class="btn btn-outline-primary btn-sm text-center">Kembali</a>
  </div>
</div>

<?php
$hasActiveFilter = !empty($filters['prodi']) || !empty($filters['lantai']) || !empty($filters['angkatan']) || !empty($filters['q']);
?>

<div class="card card-outline card-primary mb-4">
  <div class="card-header d-flex justify-content-start align-items-center">
    <strong>Tambah Santri</strong>
    <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseTambahSantri" aria-expanded="false" aria-controls="collapseTambahSantri">
      <i class="fas fa-chevron-down"></i>
    </button>
  </div>
  <div id="collapseTambahSantri" class="collapse">
    <div class="card-body">
    <form method="post" action="<?php echo site_url('admin/santri/store'); ?>" class="form-row">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <div class="form-group col-md-5">
        <label>NIM</label>
        <input type="text" name="nim" class="form-control" maxlength="15" required>
      </div>
      <div class="form-group col-md-5">
        <label>Kamar</label>
        <input type="text" name="kamar" class="form-control" maxlength="20" required>
      </div>
      <div class="form-group col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary btn-block">Tambah</button>
      </div>
    </form>
    </div>
  </div>
</div>

<div class="card card-outline card-secondary mb-4">
  <div class="card-header d-flex justify-content-start align-items-center">
    <strong>Filter dan Pencarian</strong>
    <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseFilterSantri" aria-expanded="<?php echo $hasActiveFilter ? 'true' : 'false'; ?>" aria-controls="collapseFilterSantri">
      <i class="fas fa-chevron-down"></i>
    </button>
  </div>
  <div id="collapseFilterSantri" class="collapse<?php echo $hasActiveFilter ? ' show' : ''; ?>">
    <div class="card-body">
    <form method="get" action="<?php echo site_url('admin/santri'); ?>" class="form-row">
      <div class="form-group col-md-3">
        <label>Prodi</label>
        <select name="prodi" class="form-control">
          <option value="">Semua Prodi</option>
          <?php foreach (($filter_options['prodi'] ?? array()) as $prodi): ?>
            <option value="<?php echo html_escape($prodi); ?>" <?php echo (($filters['prodi'] ?? '') === $prodi) ? 'selected' : ''; ?>><?php echo html_escape($prodi); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group col-md-2">
        <label>Lantai</label>
        <select name="lantai" class="form-control">
          <option value="">Semua Lantai</option>
          <option value="1" <?php echo (($filters['lantai'] ?? '') === '1') ? 'selected' : ''; ?>>Lantai 1</option>
          <option value="2" <?php echo (($filters['lantai'] ?? '') === '2') ? 'selected' : ''; ?>>Lantai 2</option>
          <option value="3" <?php echo (($filters['lantai'] ?? '') === '3') ? 'selected' : ''; ?>>Lantai 3</option>
        </select>
      </div>
      <div class="form-group col-md-2">
        <label>Angkatan</label>
        <select name="angkatan" class="form-control">
          <option value="">Semua Angkatan</option>
          <?php foreach (($filter_options['angkatan'] ?? array()) as $angkatan): ?>
            <option value="<?php echo html_escape($angkatan); ?>" <?php echo (($filters['angkatan'] ?? '') === $angkatan) ? 'selected' : ''; ?>><?php echo html_escape($angkatan); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group col-md-3">
        <label>Search (NIM/Nama/Alamat)</label>
        <input type="text" name="q" class="form-control" value="<?php echo html_escape($filters['q'] ?? ''); ?>" placeholder="Ketik kata kunci...">
      </div>
      <div class="form-group col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary btn-block mr-2">Filter</button>
        <a href="<?php echo site_url('admin/santri'); ?>" class="btn btn-light btn-block">Reset</a>
      </div>
    </form>
    </div>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-hover align-middle mb-0">
    <thead>
      <tr>
        <th style="width: 60px;">No</th>
        <th>NIM</th>
        <th>Nama</th>
        <th>Kamar</th>
        <th>Status</th>
        <th>Prodi</th>
        <th>Alamat</th>
        <th style="width: 220px;">Opsi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data santri.</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $idx => $row): ?>
          <?php $no = (int) ($pagination['offset'] ?? 0) + (int) $idx + 1; ?>
          <tr>
            <td><?php echo $no; ?></td>
            <td class="font-weight-bold"><?php echo html_escape($row['nim']); ?></td>
            <td><?php echo html_escape($row['nama']); ?></td>
            <td>
              <form method="post" action="<?php echo site_url('admin/santri/update/' . (int) $row['id']); ?>" class="form-inline">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <input type="text" name="kamar" class="form-control form-control-sm mr-2" value="<?php echo html_escape($row['kamar']); ?>" maxlength="20" required>
            </td>
            <td>
              <strong><?php echo html_escape($row['status'] ?? 'Aktif'); ?></strong>
            </td>
            <td><?php echo html_escape($row['prodi']); ?></td>
            <td class="small text-muted"><?php echo html_escape($row['alamat']); ?></td>
            <td>
                <button type="submit" class="btn btn-primary btn-sm mr-1">Simpan</button>
              </form>
              <form method="post" action="<?php echo site_url('admin/santri/delete/' . (int) $row['id']); ?>" class="d-inline" onsubmit="return confirm('Hapus santri ini?');">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if (($pagination['total_pages'] ?? 1) > 1): ?>
  <?php
    $queryBase = $pagination['query_base'] ?? array();
    $currentPage = (int) ($pagination['page'] ?? 1);
    $totalPages = (int) ($pagination['total_pages'] ?? 1);
    $buildUrl = function($p) use ($queryBase) {
      $query = array_merge($queryBase, array('page' => $p));
      return site_url('admin/santri') . '?' . http_build_query($query);
    };
  ?>
  <nav class="mt-4">
    <ul class="pagination pagination-sm m-0">
      <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
        <a class="page-link" href="<?php echo ($currentPage <= 1) ? '#' : $buildUrl($currentPage - 1); ?>">&laquo;</a>
      </li>
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <li class="page-item <?php echo ($p === $currentPage) ? 'active' : ''; ?>">
          <a class="page-link" href="<?php echo $buildUrl($p); ?>"><?php echo $p; ?></a>
        </li>
      <?php endfor; ?>
      <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
        <a class="page-link" href="<?php echo ($currentPage >= $totalPages) ? '#' : $buildUrl($currentPage + 1); ?>">&raquo;</a>
      </li>
    </ul>
  </nav>
<?php endif; ?>
