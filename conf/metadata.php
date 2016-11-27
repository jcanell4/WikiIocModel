<?php
/**

 */

$meta['userpage_allowed']           = array('onoff');
$meta['userpage_ns']                = array('string');
$meta['userpage_discuss_ns']        = array('string');;

$meta['notifier_type'] = ['multichoice', '_choices' => ['ajax', 'websockets']];
$meta['notifier_ajax_timer'] = ['numeric'];

// Avisos del sistema
$meta['system_warning_user'] = ['string'];
$meta['system_warning_title'] = ['string'];
$meta['system_warning_message'] = ['string'];
$meta['system_warning_show_alert'] = ['onoff'];
$meta['system_warning_start_date'] = ['string', '_pattern' => '/\d\d-\d\d-\d\d\d\d \d\d:\d\d/'];
$meta['system_warning_end_date'] = ['string', '_pattern' => '/\d\d-\d\d-\d\d\d\d \d\d:\d\d/'];
$meta['system_warning_type'] = ['multichoice', '_choices' => ['error', 'warning', 'info', 'success']];
