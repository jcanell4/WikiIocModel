<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_PLUGIN . "wikiiocmodel/AbstractActionManager.php";

//namespace ioc_dokuwiki;

/**
 * Description: define el array de parámetros de la acción
 * @author culpable Rafa
 */
abstract class DokuActionManager extends AbstractActionManager{

    abstract public static function getActionParams();
    
 }
