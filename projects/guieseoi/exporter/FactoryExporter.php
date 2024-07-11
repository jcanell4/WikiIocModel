<?php
/**
 * FactoryExporter (projecte 'guieseoi')
 * @culpable Rafael Claver
 * @re-creator marjose
 */
if (!defined('DOKU_INC')) die();

class FactoryExporter extends BasicFactoryExporter {

    public function __construct() {
        parent::__construct(dirname(__FILE__));
    }
}
