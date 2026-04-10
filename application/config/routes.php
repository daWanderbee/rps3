<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['leaderboard'] = 'Leaderboard/index';
$route['tasks'] = 'Tasks/index';
$route['transactions'] = 'Transactions/index';
$route['support'] = 'Support/index';

$route['notifications'] = 'Notifications/index';
$route['notifications/read'] = 'Notifications/markRead';
$route['notifications/read-all'] = 'Notifications/markAllRead';
$route['notifications/discard'] = 'Notifications/discard';
$route['notifications/discard-all'] = 'Notifications/discardAll';

$route['reward'] = 'Reward/index';
$route['redeem'] = 'Redeem/index';
$route['redeem/processRedemption'] = 'Redeem/processRedemption';

$route['admin'] = 'Admin/index';
$route['admin/bulkAssignTask'] = 'Admin/bulkAssignTask';
$route['admin/getEmployeeTasks'] = 'Admin/getEmployeeTasks';
$route['admin/addTask'] = 'Admin/addTask';
$route['admin/markTaskComplete'] = 'Admin/markTaskComplete';

$route['management'] = 'Management/index';
$route['management/getEmployeeTasks'] = 'Management/getEmployeeTasks';
$route['management/addTask'] = 'Management/addTask';
$route['management/markTaskComplete'] = 'Management/markTaskComplete';
$route['management/getRemainingPoints'] = 'Management/getRemainingPoints';

