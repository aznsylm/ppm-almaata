<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?php echo html_escape($title); ?></title>
</head>
<body>
  <h1><?php echo html_escape($title); ?></h1>
  <p><a href="<?php echo site_url('admin/dashboard'); ?>">Kembali ke Dashboard</a></p>

  <?php if ($this->session->flashdata('success')): ?>
    <p><?php echo html_escape($this->session->flashdata('success')); ?></p>
  <?php endif; ?>
  <?php if ($this->session->flashdata('error')): ?>
    <p><?php echo html_escape($this->session->flashdata('error')); ?></p>
  <?php endif; ?>

  <table border="1" cellpadding="6" cellspacing="0">
    <thead>
      <tr>
        <th>ID</th>
        <th>NIM</th>
        <th>Nama</th>
        <th>Tanggal</th>
        <th>Alasan</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
      <tr>
        <td><?php echo html_escape($row['id']); ?></td>
        <td><?php echo html_escape($row['nim']); ?></td>
        <td><?php echo html_escape($row['nama']); ?></td>
        <td><?php echo html_escape($row['tgl_mulai']); ?> s/d <?php echo html_escape($row['tgl_selesai']); ?></td>
        <td><?php echo html_escape($row['alasan']); ?></td>
        <td><?php echo html_escape($status_map[$row['acc']]); ?></td>
        <td>
          <?php if ($row['acc'] === '0'): ?>
            <form method="post" action="<?php echo site_url('admin/perizinan/update-status/' . rawurlencode($row['id'])); ?>">
              <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
              <select name="status" required>
                <option value="1">Setujui</option>
                <option value="2">Tolak</option>
              </select>
              <input type="text" name="approval_note" placeholder="Catatan admin">
              <button type="submit">Simpan</button>
            </form>
          <?php else: ?>
            <span>Selesai</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
