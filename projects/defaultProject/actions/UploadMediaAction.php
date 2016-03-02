<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN."ajaxcommand/requestparams/MediaKeys.php";
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
            'filePathSource' => $this->params[MediaKeys::KEY_FILE_PATH_SOURCE], 
            'overWrite' => $this->params[MediaKeys::KEY_OVERWRITE]
        );
        $this->actionReturn = $this->mediaModel->upLoadData($toSet);
    }

    protected function startProcess() {
        $this->mediaModel->initWhitTarget($this->params[MediaKeys::KEY_NS_TARGET], $this->params[MediaKeys::KEY_MEDIA_NAME], $this->params[MediaKeys::KEY_REV], $this->params[MediaKeys::KEY_META]);          
    }

//put your code here
}
