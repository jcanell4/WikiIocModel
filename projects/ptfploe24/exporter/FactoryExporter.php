<?php
/**
 * FactoryExporter (projecte 'ptfploe24')
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class FactoryExporter extends BasicFactoryExporter {

    public function __construct() {
        parent::__construct(dirname(__FILE__));
    }
}
