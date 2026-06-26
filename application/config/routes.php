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
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['reports/sales/custom/(:any)/(:any)'] = 'Reports/sales_custom/$1/$2';
$route['reports/sales/(:any)']               = 'Reports/sales/$1';
$route['reports/sales']                      = 'Reports/sales';

$route['reports/chats/custom/(:any)/(:any)'] = 'Reports/chats_custom/$1/$2';
$route['reports/chats/(:any)']               = 'Reports/chats/$1';
$route['reports/chats']                      = 'Reports/chats';

$route['q/(:any)/sign'] = 'Public_quote/sign/$1';
$route['q/(:any)']      = 'Public_quote/view/$1';

$route['api/companies/public']              = 'Api/companies_public';
$route['api/conversations/(:num)/messages'] = 'Api/conversation_messages/$1';
$route['api/conversations/(:num)/read']     = 'Api/conversation_read/$1';
$route['api/conversations/(:num)']          = 'Api/conversation/$1';

$route['api/users/(:num)/password']   = 'Api/user_password/$1';
$route['api/quotation/(:num)/images'] = 'Api/quotation_images/$1';
$route['api/push-token']              = 'Api/push_token';

$route['pos']                    = 'Pos/index';
$route['pos/place_order']        = 'Pos/place_order';
$route['pos/orders']             = 'Pos/orders';
$route['pos/receipt/(:num)']     = 'Pos/receipt/$1';
