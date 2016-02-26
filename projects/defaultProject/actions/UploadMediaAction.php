<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuAction.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/datamodel/DokuMediaModel.php";

if (!defined('DW_ACT_MEDIA_UPLOAD')) {
    define('DW_ACT_MEDIA_UPLOAD', "mediadetails");
}
if (!defined('DW_ACT_MEDIA_MANAGER')) {
    define('DW_ACT_MEDIA_MANAGER', "media");
}

/**
 * Description of UploadMediaAction
 *
 * @author josep
 */
class UploadMediaAction extends DokuAction{
    /* DokuMediaModel*/
      protected $mediaModel;
      private $actionReturn;

    
      public function __construct(/* BasicPersistenceEngine */ $engine) {
        $this->defaultDo = DW_ACT_MEDIA_UPLOAD;
        $this->mediaModel = new DokuMediaModel($engine);
    }
    

    protected function responseProcess(){
        return $this->actionReturn;
    }

    protected function runProcess() {       
        $toSet = array(
            'filePathSource' => $this->params['filePathSource'], 
            'overWrite' => $this->params['overWrite']
        );
        $this->actionReturn = $this->mediaModel->upLoadData($toSet);
    }

    protected function startProcess() {
        $this->mediaModel->initWhitTarget($this->params['nsTarget'], $this->params['mediaName'], $this->params['rev'], $this->params['meta']);          
    }

//put your code here
}
