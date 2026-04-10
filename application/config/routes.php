<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'Dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['leaderboard'] = 'leaderboard/index';
$route['tasks'] = 'tasks/index';
$route['transactions'] = 'transactions/index';
$route['support'] = 'support/index';

$route['notifications'] = 'notifications/index';
$route['notifications/read'] = 'notifications/markRead';
$route['notifications/read-all'] = 'notifications/markAllRead';
$route['notifications/discard'] = 'notifications/discard';
$route['notifications/discard-all'] = 'notifications/discardAll';

$route['reward'] = 'reward/index';
$route['redeem'] = 'redeem/index';
$route['redeem/processRedemption'] = 'redeem/processRedemption';

$route['admin'] = 'admin/index';
$route['admin/bulkAssignTask'] = 'admin/bulkAssignTask';
$route['admin/getEmployeeTasks'] = 'admin/getEmployeeTasks';
$route['admin/addTask'] = 'admin/addTask';
$route['admin/markTaskComplete'] = 'admin/markTaskComplete';

$route['management'] = 'management/index';
$route['management/getEmployeeTasks'] = 'management/getEmployeeTasks';
$route['management/addTask'] = 'management/addTask';
$route['management/markTaskComplete'] = 'management/markTaskComplete';
$route['management/getRemainingPoints'] = 'management/getRemainingPoints';

$route['send-mail'] = 'MailController/send';
