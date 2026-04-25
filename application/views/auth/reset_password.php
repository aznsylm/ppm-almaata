<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f7fb; margin: 0; }
    .wrap { max-width: 440px; margin: 60px auto; background: #fff; border: 1px solid #d9e2f1; border-radius: 10px; padding: 24px; box-sizing: border-box; }
    h1 { font-size: 22px; margin: 0 0 8px; }
    .msg { margin-top: 10px; padding: 8px; border-radius: 4px; background: #ffeaea; color: #7f1d1d; }
    label { display: block; font-weight: 600; margin-top: 14px; }
    input { width: 100%; padding: 10px; margin-top: 6px; box-sizing: border-box; }
    button { width: 100%; margin-top: 16px; background: #0e5ba8; color: #fff; border: 0; padding: 10px 14px; cursor: pointer; border-radius: 4px; }
    @media (max-width: 576px) {
      .wrap { margin: 20px 12px; padding: 16px; }
      h1 { font-size: 20px; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Reset Password</h1>
    <p>Password lama (MD5) perlu diganti ke password baru yang lebih aman.</p>

    <?php if (validation_errors()): ?>
      <div class="msg"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo site_url('auth/reset-password'); ?>">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <label for="new_password">Password Baru</label>
      <input type="password" name="new_password" id="new_password" minlength="8" maxlength="100" required>

      <label for="confirm_password">Konfirmasi Password</label>
      <input type="password" name="confirm_password" id="confirm_password" minlength="8" maxlength="100" required>

      <button type="submit">Simpan Password Baru</button>
    </form>
  </div>
</body>
</html>
