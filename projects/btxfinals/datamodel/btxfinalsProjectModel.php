<?php
/**
 * btxfinalsProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class btxfinalsProjectModel extends UniqueContentFileProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction = false;
    }

    public function generateProject(){} //abstract obligatorio

    public function directGenerateProject() {
        $ret = $this->projectMetaDataQuery->setProjectGenerated();
        return $ret;
    }

    /**
     * @param array $data : dades del projecte (camps del formulari actiu)
     */
    public function validateFields($data=NULL, $subset=FALSE){}


}
