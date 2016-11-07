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
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/MediaAction.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/datamodel/DokuMediaModel.php";

//if (!defined('DW_ACT_MEDIA_UPLOAD')) {
//    define('DW_ACT_MEDIA_UPLOAD', "mediadetails");
//}
//if (!defined('DW_ACT_MEDIA_MANAGER')) {
//    define('DW_ACT_MEDIA_MANAGER', "media");
//}

/**
 * Description of UploadMediaAction
 *
 * @author josep
 */
class UploadMediaAction extends MediaAction{
    private $actionReturn;

    
    public function __construct(/* BasicPersistenceEngine */ $engine) {
          parent::__construct($engine);
          
    }
    

    protected function responseProcess(){
        return $this->actionReturn;
    }

    protected function runProcess() {       
        $toSet = array(
            'filePathSource' => $this->params[MediaKeys::KEY_FILE_PATH_SOURCE], 
            'overWrite' => $this->params[MediaKeys::KEY_OVERWRITE]
        );
        $this->actionReturn = $this->dokuModel->upLoadData($toSet);
        /*
         0 = OK
     *      -1 = UNAUTHORIZED
     *      -2 = OVER_WRITING_NOT_ALLOWED
     *      -3 = OVER_WRITING_UNAUTHORIZED
     *      -5 = FAILS
     *      -4 = WRONG_PARAMS
     *      -6 = BAD_CONTENT
     *      -7 = SPAM_CONTENT
     *      -8 = XSS_CONTENT
         * 
         */
        //if($this->actionReturn) Falten les excepcions!
    }

    protected function initModel() {
        $this->dokuModel->initWhitTarget($this->params[MediaKeys::KEY_NS_TARGET], $this->params[MediaKeys::KEY_MEDIA_NAME], $this->params[MediaKeys::KEY_REV], $this->params[MediaKeys::KEY_META]);          
    }

//put your code here
}
