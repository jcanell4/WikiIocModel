<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_PLUGIN."wikiiocmodel/datamodel/AbstractWikiDataModel.php";

/**
 * Description of WikiRenderizableDataModel
 *
 * @author professor
 */
abstract class WikiRenderizableDataModel extends AbstractWikiDataModel{
    
    public function getData() {}

//    public function getData() {
//        return $this->getViewData();
//    }
//    public abstract function getViewData();
//    public abstract function getRawData();
    
}
