<?php
/**
 * Description of AbstractModelAdapter
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_PLUGIN . 'wikiiocmodel/WikiIocModel.php';


abstract class AbstractModelAdapter implements WikiIocModel {

    public function __construct() {}

}