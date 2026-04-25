<h2 class="font-weight-bold text-gray-900 mb-2">Masuk ke akun</h2>
<p class="text-muted mb-4">Gunakan NIM untuk login sebagai admin atau santri.</p>

<?php if (validation_errors()): ?>
  <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
  <div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('error')); ?></div>
<?php endif; ?>

<form method="post" action="<?php echo site_url('auth/login'); ?>">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <div class="form-group">
    <label for="nim">NIM</label>
    <input type="text" class="form-control" name="nim" id="nim" maxlength="15" placeholder="Masukkan NIM" required>
  </div>
  <div class="form-group">
    <label for="password">Password</label>
    <input type="password" class="form-control" name="password" id="password" maxlength="100" placeholder="Masukkan password" required>
  </div>
  <button type="submit" class="btn btn-primary btn-block py-2">Login</button>
</form>
