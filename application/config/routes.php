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
$route['admin/perizinan/validate/(:any)'] = 'admin/perizinan/validate_upload/$1';
$route['admin/perizinan/selesaikan-izin/(:any)'] = 'admin/perizinan/selesaikan_izin/$1';
$route['admin/perizinan/lanjutkan-izin/(:any)'] = 'admin/perizinan/lanjutkan_izin/$1';

$route['user/dashboard'] = 'user/dashboard/index';
$route['user/perizinan'] = 'user/perizinan/index';
$route['user/perizinan/submit'] = 'user/perizinan/submit';
$route['user/perizinan/reapply-haid/(:any)'] = 'user/perizinan/reapply_haid/$1';
$route['user/perizinan/download/(:any)'] = 'user/perizinan/download/$1';
$route['user/perizinan/upload/(:any)'] = 'user/perizinan/upload/$1';
$route['user/perizinan/cetak/(:any)'] = 'user/perizinan/cetak/$1';
