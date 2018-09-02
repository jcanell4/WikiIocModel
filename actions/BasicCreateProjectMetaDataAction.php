<?php
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
include_once WIKI_IOC_MODEL."actions/ProjectMetadataAction.php";

class BasicCreateProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Crea una estructura de directorios para el nuevo proyecto (tipo de proyecto)
     * a partir del archivo de configuración configMain.json correspondiente
     */
    public function responseProcess() {
        $id = $this->params[ProjectKeys::KEY_ID];
        $projectType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];

        $this->projectModel->init($id, $projectType);

        //sólo se ejecuta si no existe el proyecto
        if (!$this->projectModel->existProject()) {
            
            $metaDataValues = $this->getDefaultValues();

            $metaData = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET,
                ProjectKeys::KEY_ID_RESOURCE => $id,
                ProjectKeys::KEY_PROJECT_TYPE => $projectType,
                ProjectKeys::KEY_FILTER => $this->params[ProjectKeys::KEY_FILTER], // opcional
                ProjectKeys::KEY_METADATA_VALUE => json_encode($metaDataValues)
            ];

            $this->projectModel->setData($metaData);    //crea la estructura y el contenido en 'mdprojects/'
            $this->projectModel->createDataDir($id);    //crea el directori del projecte a 'data/pages/'
            $ret = $this->projectModel->getData();      //obtiene la estructura y el contenido del proyecto
            $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_created'), $id);  //añade info para la zona de mensajes
            $ret[ProjectKeys::KEY_ID] = $this->idToRequestId($id);
            $ret[ProjectKeys::KEY_NS] = $id;
            $ret[ProjectKeys::KEY_PROJECT_TYPE] = $projectType;
        }
        if (!$ret)
            throw new ProjectExistException($id);
        else
            return $ret;
    }
    
    protected function getDefaultValues(){
        $metaDataValues = array();
        $metaDataKeys = $this->projectModel->getMetaDataDefKeys($projectType);
        foreach ($metaDataKeys as $key => $value) {
            if ($value['default']) $metaDataValues[$key] = $value['default'];
        }
        //asigna valores por defecto a algunos campos definidos en configMain.json
        //$metaDataValues['nsproject'] = $this->params[ProjectKeys::KEY_ID];
        $metaDataValues["responsable"] = $_SERVER['REMOTE_USER'];
        $metaDataValues['autor'] = $_SERVER['REMOTE_USER'];

        return $metaDataValues;
    }
}