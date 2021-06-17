<?php
/**
 * manualProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class manualProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction = false;
    }

    public function generateProject(){} //abstract obligatorio

    public function directGenerateProject($data) {
        $ret = $this->projectMetaDataQuery->setProjectGenerated();
        return $ret;
    }

}
