<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'auth/login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['auth/login'] = 'auth/login';
$route['auth/logout'] = 'auth/logout';
$route['auth/reset-password'] = 'auth/reset_password';

$route['admin/dashboard'] = 'admin/dashboard/index';
$route['admin/santri'] = 'admin/santri/index';
$route['admin/santri/store'] = 'admin/santri/store';
$route['admin/santri/update/(:num)'] = 'admin/santri/update/$1';
$route['admin/santri/delete/(:num)'] = 'admin/santri/delete/$1';
$route['admin/perizinan'] = 'admin/perizinan/index';
$route['admin/perizinan/delete-selected'] = 'admin/perizinan/delete_selected';
$route['admin/perizinan/validate/(:any)'] = 'admin/perizinan/validate_izin/$1';
$route['admin/perizinan/selesaikan-izin/(:any)'] = 'admin/perizinan/selesaikan_izin/$1';
$route['admin/perizinan/lanjutkan-izin/(:any)'] = 'admin/perizinan/lanjutkan_izin/$1';
$route['admin/perizinan/backup-filtered'] = 'admin/perizinan/backup_filtered';
$route['admin/perizinan/hapus-filtered'] = 'admin/perizinan/hapus_filtered';

$route['admin/backup'] = 'admin/backup/index';
$route['admin/backup/download'] = 'admin/backup/download';
$route['admin/backup/hapus'] = 'admin/backup/hapus';

$route['admin/kehadiran'] = 'admin/kehadiran/index';
$route['admin/kehadiran/export_rekap'] = 'admin/kehadiran/export_rekap';
$route['admin/kehadiran/export_detail/(:any)'] = 'admin/kehadiran/export_detail/$1';
$route['admin/kehadiran/bulk_update'] = 'admin/kehadiran/bulk_update';
$route['admin/kehadiran/detail/(:any)'] = 'admin/kehadiran/detail/$1';
$route['admin/kehadiran/finalisasi'] = 'admin/kehadiran/finalisasi';
$route['admin/kehadiran/jadwal'] = 'admin/kehadiran/jadwal';
$route['admin/kehadiran/update_jadwal'] = 'admin/kehadiran/update_jadwal';
$route['admin/kehadiran/manual'] = 'admin/kehadiran/manual';
$route['admin/kehadiran/manual_store'] = 'admin/kehadiran/manual_store';
$route['admin/kehadiran/manual_batch'] = 'admin/kehadiran/manual_batch';
$route['admin/kehadiran/manual_edit/(:num)'] = 'admin/kehadiran/manual_edit/$1';
$route['admin/kehadiran/manual_hapus/(:num)'] = 'admin/kehadiran/manual_hapus/$1';

$route['user/dashboard'] = 'user/dashboard/index';
$route['user/presensi'] = 'user/presensi/index';
$route['user/presensi/checkin'] = 'user/presensi/checkin';
$route['user/presensi/history'] = 'user/presensi/history';
$route['user/presensi/export_detail'] = 'user/presensi/export_detail';
$route['user/perizinan'] = 'user/perizinan/index';
$route['user/perizinan/submit'] = 'user/perizinan/submit';
$route['user/perizinan/download/(:any)'] = 'user/perizinan/download/$1';
$route['user/perizinan/upload-surat/(:any)'] = 'user/perizinan/upload_surat/$1';
$route['user/perizinan/upload-dokumentasi/(:any)'] = 'user/perizinan/upload_dokumentasi/$1';
$route['user/perizinan/dokumentasi/(:any)'] = 'user/perizinan/dokumentasi/$1';
$route['user/perizinan/cetak/(:any)'] = 'user/perizinan/cetak/$1';

// ROUTES PERIODE HAID
$route['admin/periode-haid'] = 'admin/periode_haid/index';
$route['admin/periode-haid/create'] = 'admin/periode_haid/create';
$route['admin/periode-haid/store'] = 'admin/periode_haid/store';
$route['admin/periode-haid/edit/(:num)'] = 'admin/periode_haid/edit/$1';
$route['admin/periode-haid/update/(:num)'] = 'admin/periode_haid/update/$1';
$route['admin/periode-haid/delete/(:num)'] = 'admin/periode_haid/delete/$1';

$route['user/periode-haid'] = 'user/periode_haid/index';
