<?php
if (!defined('DOKU_INC')) die();

class CreateProjectMetaDataAction extends BasicCreateProjectMetaDataAction{

    public function responseProcess() {
        $ret = parent::responseProcess();
        $ret[ProjectKeys::KEY_GENERATED] = $this->getModel()->directGenerateProject($ret);  //crea el contenido del proyecto en 'pages/'

        $mess = ($ret[ProjectKeys::KEY_GENERATED]) ? "project_generated" : "project_not_generated";
        $new_message = self::generateInfo("info", WikiIocLangManager::getLang($mess), $ret[ProjectKeys::KEY_ID]);
        $ret['info'] = self::addInfoToInfo($ret['info'], $new_message);  //añade info para la zona de mensajes

        return $ret;
    }

    protected function getDefaultValues(){
        //asigna valores por defecto a algunos campos definidos en configMain.json
        $metaDataValues = parent::getDefaultValues();
        $metaDataValues["creador"] = $_SERVER['REMOTE_USER'];

        return $metaDataValues;
    }

}