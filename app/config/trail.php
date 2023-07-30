<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| Enable Audit Trail
|--------------------------------------------------------------------------
|
| Set [TRUE/FALSE] to use of audit trail
|
*/
$config['audit_enable'] = TRUE;
$config['not_allowed_tables'] = [
									'costing',
									'user_logins',
									'login_attempts',
									'order_ref',
									'sessions',
									'users',
									'acc_tran',
									'print_histories',
									'cus_sessions',
									'cus_audit_trails',
								];
$config['track_insert'] = TRUE;
$config['track_update'] = TRUE;
$config['track_delete'] = TRUE;