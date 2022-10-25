<?php
/**
 * class MetaDataRender: project 'prgfplogse'
 *      inclou la referència als subsets
 */
if (!defined("DOKU_INC")) die();

class MetaDataRender {
    public function __construct($subSet=NULL) {
        if ($subSet) {
            $class = "{$subSet}MetaDataRender";
            return new $class();
        }else {
            return new BasicMetaDataRender();
        }
    }
}

class mainMetaDataRender extends BasicMetaDataRender {}

class managementMetaDataRender extends BasicMetaDataRender {}
