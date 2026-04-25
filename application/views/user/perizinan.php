<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?php echo html_escape($title); ?></title>
</head>
<body>
  <h1><?php echo html_escape($title); ?></h1>
  <p><a href="<?php echo site_url('user/dashboard'); ?>">Dashboard</a> | <a href="<?php echo site_url('auth/logout'); ?>">Logout</a></p>

  <?php if ($this->session->flashdata('success')): ?>
    <p><?php echo html_escape($this->session->flashdata('success')); ?></p>
  <?php endif; ?>
  <?php if ($this->session->flashdata('error')): ?>
    <p><?php echo $this->session->flashdata('error'); ?></p>
  <?php endif; ?>

  <h3>Ajukan Izin</h3>
  <form method="post" action="<?php echo site_url('user/perizinan/submit'); ?>">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
    <label>Tanggal Mulai</label><br>
    <input type="date" name="tgl_mulai" required><br><br>

    <label>Tanggal Selesai</label><br>
    <input type="date" name="tgl_selesai" required><br><br>

    <label>Alasan</label><br>
    <textarea name="alasan" required></textarea><br><br>

    <label>Kamar</label><br>
    <input type="text" name="kamar" maxlength="20" required><br><br>

    <label>Semester</label><br>
    <input type="number" name="smt" min="1" max="14" required><br><br>

    <button type="submit">Kirim</button>
  </form>

  <h3>Riwayat Izin</h3>
  <table border="1" cellpadding="6" cellspacing="0">
    <thead>
      <tr>
        <th>ID</th>
        <th>Tanggal</th>
        <th>Alasan</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
      <tr>
        <td><?php echo html_escape($row['id']); ?></td>
        <td><?php echo html_escape($row['tgl_mulai']); ?> s/d <?php echo html_escape($row['tgl_selesai']); ?></td>
        <td><?php echo html_escape($row['alasan']); ?></td>
        <td><?php echo html_escape($status_map[$row['acc']]); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
