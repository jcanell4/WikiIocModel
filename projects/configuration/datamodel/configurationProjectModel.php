<?php
/**
 * configurationProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class configurationProjectModel extends AbstractProjectModel{

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;
    }

    public function generateProject() {
        //
        // NOTA:
        //    Este proyecto no es generable
        //
    }

}
