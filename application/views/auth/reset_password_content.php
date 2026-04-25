<h2 class="font-weight-bold text-gray-900 mb-2">Reset Password</h2>
<p class="text-muted mb-4">Akun kamu masih memakai password legacy. Ganti sekarang supaya aman.</p>

<?php if (validation_errors()): ?>
  <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
<?php endif; ?>

<form method="post" action="<?php echo site_url('auth/reset-password'); ?>">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <div class="form-group">
    <label for="new_password">Password Baru</label>
    <input type="password" class="form-control" name="new_password" id="new_password" minlength="8" maxlength="100" required>
  </div>
  <div class="form-group">
    <label for="confirm_password">Konfirmasi Password</label>
    <input type="password" class="form-control" name="confirm_password" id="confirm_password" minlength="8" maxlength="100" required>
  </div>
  <button type="submit" class="btn btn-primary btn-block py-2">Simpan Password Baru</button>
</form>
