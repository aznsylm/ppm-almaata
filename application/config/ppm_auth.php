<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| PPM AUTH CONFIG
| -------------------------------------------------------------------
| Set these in web server environment variables for local/dev:
| PPM_ADMIN_NIM
| PPM_ADMIN_PASSWORD_HASH (bcrypt from password_hash)
| CI_ENCRYPTION_KEY
|
| Temporary local fallback (for easier setup) is enabled below.
| Environment variables will override these values.
| Jalankan perintah ini di terminal:
| php -r "echo password_hash('PasswordBaruKuat123!', PASSWORD_DEFAULT), PHP_EOL;"
| Salin hasil hash ke PPM_ADMIN_PASSWORD_HASH (atau ke fallback config kalau pakai cara cepat)
*/
$config['ppm_admin_nim'] = getenv('PPM_ADMIN_NIM') ?: 'AdminPPM';
$config['ppm_admin_password_hash'] = getenv('PPM_ADMIN_PASSWORD_HASH') ?: '$2y$10$yxkcySP.dbsgzx7x.O4FnOrg6ITsiDYj58TRP3jds7mjTU/IVz.nq';

$config['ppm_role_admin'] = 'admin';
$config['ppm_role_user'] = 'user';

$config['ppm_status_active'] = 'active';
$config['ppm_status_inactive'] = 'inactive';

$config['ppm_session_timeout_minutes'] = 30;
