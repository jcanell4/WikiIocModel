<?php
/**
 * Options for the odt2dw plugin
 *
 * @author Greg BELLAMY <garlik.crx@gmail.com> [Gag]
 */


//$meta['debugLvl']                 = array('multichoice', '_choices' => array(0,1,2,3));
$meta['userpage_allowed']           = array('onoff');
$meta['userpage_ns']                = array('string');
$meta['userpage_discuss_ns']        = array('string');;

$meta['notifier_type'] = ['multichoice', '_choices' => ['ajax', 'websockets']];
$meta['notifier_check_timer'] = ['string'];