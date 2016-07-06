<?php

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once DOKU_PLUGIN."wikiiocmodel/actions/AbstractWikiAction.php";

class ProjectMetaDataAction extends AbstractWikiAction {

    public function get(/*Array*/
        $paramsArr = array())
    {
        return 'Metadades del projecte';
    }
}