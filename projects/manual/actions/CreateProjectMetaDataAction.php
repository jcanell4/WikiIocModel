<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN."wikiiocmodel/");
require_once WIKI_IOC_MODEL . "actions/BasicCreateProjectMetaDataAction.php";

class CreateProjectMetaDataAction extends BasicCreateProjectMetaDataAction{

    public function responseProcess() {
        $ret = parent::responseProcess();
        $ret[ProjectKeys::KEY_GENERATED] = $this->getModel()->directGenerateProject($ret);  //crea el contenido del proyecto en 'pages/'

        $mess = ($ret[ProjectKeys::KEY_GENERATED]) ? "project_generated" : "project_not_generated";
        $new_message = $this->generateInfo("info", WikiIocLangManager::getLang($mess), $ret[ProjectKeys::KEY_ID]);
        $ret['info'] = $this->addInfoToInfo($ret['info'], $new_message);  //añade info para la zona de mensajes

        return $ret;
    }

    protected function getDefaultValues(){
        //asigna valores por defecto a algunos campos definidos en configMain.json
        $metaDataValues = parent::getDefaultValues();
        $metaDataValues["creador"] = $_SERVER['REMOTE_USER'];

        return $metaDataValues;
    }

}