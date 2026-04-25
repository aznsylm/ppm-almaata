<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login PPM Alma Ata</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f7fb; margin: 0; }
    .wrap { max-width: 440px; margin: 60px auto; background: #fff; border: 1px solid #d9e2f1; border-radius: 10px; padding: 24px; box-sizing: border-box; }
    h1 { font-size: 22px; margin: 0 0 8px; }
    p { color: #4f5b6c; }
    label { display: block; font-weight: 600; margin-top: 14px; }
    input { width: 100%; padding: 10px; margin-top: 6px; box-sizing: border-box; }
    button { width: 100%; margin-top: 16px; background: #0e5ba8; color: #fff; border: 0; padding: 10px 14px; cursor: pointer; border-radius: 4px; }
    .msg { margin-top: 10px; padding: 8px; border-radius: 4px; }
    .err { background: #ffeaea; color: #7f1d1d; }
    @media (max-width: 576px) {
      .wrap { margin: 20px 12px; padding: 16px; }
      h1 { font-size: 20px; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>PPM Alma Ata</h1>
    <p>Login menggunakan NIM. Akun admin menggunakan NIM admin dari environment.</p>

    <?php if (validation_errors()): ?>
      <div class="msg err"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
      <div class="msg err"><?php echo html_escape($this->session->flashdata('error')); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo site_url('auth/login'); ?>">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <label for="nim">NIM</label>
      <input type="text" name="nim" id="nim" maxlength="15" required>

      <label for="password">Password</label>
      <input type="password" name="password" id="password" maxlength="100" required>

      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
