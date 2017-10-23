<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
include_once (DOKU_PLUGIN . "wikiiocmodel/projects/documentation/actions/ProjectMetadataAction.php");
require_once (DOKU_PLUGIN . "ownInit/WikiGlobalConfig.php");

class GenerateProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Crea los archivos necesarios definidos en la estructura del proyecto
     * @param type $paramsArr
     */
    public function responseProcess() {
        $paramsArr = $this->params;

        $this->projectModel->init($paramsArr[ProjectKeys::KEY_ID], $paramsArr[ProjectKeys::KEY_PROJECT_TYPE]);

        //sólo se ejecuta si existe el proyecto
        if ($this->projectModel->existProject($paramsArr[ProjectKeys::KEY_ID])) {

            if ($this->projectModel->isProjectGenerated($paramsArr[ProjectKeys::KEY_ID], $paramsArr[ProjectKeys::KEY_PROJECT_TYPE])) {
                $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('projectAlreadyGenerated'), $paramsArr[ProjectKeys::KEY_ID]);  //añade info para la zona de mensajes
                throw new ProjectExistException($paramsArr[ProjectKeys::KEY_ID], 'projectAlreadyGenerated');
            } else {
                $ret = $this->projectModel->generateProject($paramsArr[ProjectKeys::KEY_ID], $paramsArr[ProjectKeys::KEY_PROJECT_TYPE]);  //crea el contenido del proyecto en 'pages/'
                $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_generated'), $paramsArr[ProjectKeys::KEY_ID]);  //añade info para la zona de mensajes
                $ret[ProjectKeys::KEY_ID] = $this->idToRequestId($paramsArr[ProjectKeys::KEY_ID]);
            }
        }

        if (!$ret)
            throw new ProjectNotExistException($paramsArr[ProjectKeys::KEY_ID]);
        else
            return $ret;
    }
}