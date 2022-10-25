<?php
/**
 * class MetaDataRender: project 'configuration'
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
