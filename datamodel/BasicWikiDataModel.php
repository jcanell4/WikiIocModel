<?php

if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
require_once DOKU_INC . "inc/media.php";
require_once DOKU_INC . "inc/pageutils.php";
require_once DOKU_INC . "inc/common.php";
require_once DOKU_PLUGIN . "wikiiocmodel/datamodel/AbstractWikiDataModel.php";

class BasicWikiDataModel extends AbstractWikiDataModel{
    protected $id;

    public function init($id) {
        $this->id = $id;
    }

    public function getData() {
        throw new UnavailableMethodExecutionException();
    }

    public function setData($toSet) {
        throw new UnavailableMethodExecutionException();
    }

}
