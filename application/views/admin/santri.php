<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?php echo html_escape($title); ?></title>
</head>
<body>
  <h1><?php echo html_escape($title); ?></h1>
  <p><a href="<?php echo site_url('admin/dashboard'); ?>">Kembali ke Dashboard</a></p>

  <table border="1" cellpadding="6" cellspacing="0">
    <thead>
      <tr>
        <th>NIM</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Kamar</th>
        <th>Status</th>
        <th>Reset Password</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
      <tr>
        <td><?php echo html_escape($row['nim']); ?></td>
        <td><?php echo html_escape($row['nama']); ?></td>
        <td><?php echo html_escape($row['email']); ?></td>
        <td><?php echo html_escape($row['kamar']); ?></td>
        <td><?php echo html_escape($row['status']); ?></td>
        <td><?php echo ((int) $row['must_reset_password'] === 1) ? 'Ya' : 'Tidak'; ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
