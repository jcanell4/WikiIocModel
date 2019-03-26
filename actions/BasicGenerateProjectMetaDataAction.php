<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
include_once (WIKI_IOC_MODEL . "actions/ProjectMetadataAction.php");

class BasicGenerateProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Crea los archivos necesarios definidos en la estructura del proyecto
     */
    public function responseProcess() {

        $this->projectModel->init([ProjectKeys::KEY_ID              => $this->params[ProjectKeys::KEY_ID],
                                   ProjectKeys::KEY_PROJECT_TYPE    => $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                   ProjectKeys::KEY_METADATA_SUBSET => $this->params[ProjectKeys::KEY_METADATA_SUBSET]
                                ]);

        //sólo se ejecuta si existe el proyecto
        if ($this->projectModel->existProject()) {

            $responseId = $this->idToRequestId($this->params[ProjectKeys::KEY_ID]);
            if ($this->projectModel->isProjectGenerated()) {
                $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('projectAlreadyGenerated'), $responseId );  //añade info para la zona de mensajes
                throw new ProjectExistException($this->params[ProjectKeys::KEY_ID], 'projectAlreadyGenerated');
            } else {
                $ret = $this->projectModel->generateProject();  //crea el contenido del proyecto en 'pages/'
                if (!isset($ret[ProjectKeys::KEY_GENERATED])) {
                    $ret[ProjectKeys::KEY_GENERATED] = $this->projectModel->isProjectGenerated();
                }
                if ($ret[ProjectKeys::KEY_GENERATED]) {
                    $ret[ProjectKeys::KEY_ACTIVA_UPDATE_BTN] = 1;
                    $msg = "project_generated";
                }else {
                    $msg = "project_not_generated";
                }
                $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang($msg), $responseId);  //añade info para la zona de mensajes
                $ret[ProjectKeys::KEY_ID] = $responseId;
            }
        }

        if (!$ret)
            throw new ProjectNotExistException($this->params[ProjectKeys::KEY_ID]);
        else
            return $ret;
    }
}