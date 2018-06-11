<?php
if (!defined("DOKU_INC")) die();
include_once 'ProjectMetadataAction.php';

class CreateProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Crea una estructura de directorios para el nuevo proyecto (tipo de proyecto)
     * a partir del archivo de configuración configMain.json
     * @param array $paramsArr : parámetros recibidos por el ajaxCall
     */
    public function responseProcess() {
        $paramsArr = $this->params;
        $id = $paramsArr[ProjectKeys::KEY_ID];
        $projectType = $paramsArr[ProjectKeys::KEY_PROJECT_TYPE];

        $this->projectModel->init($id, $projectType);

        //sólo se ejecuta si no existe el proyecto
        if (!$this->projectModel->existProject($id)) {
            //obtiene las claves de la estructura de los metadatos del proyecto
            $metaDataKeys = $this->projectModel->getMetaDataDefKeys($projectType);
            foreach ($metaDataKeys as $key => $value) {
                if ($value['default']) $metaDataValues[$key] = $value['default'];
            }
            //asigna valores por defecto a algunos campos definidos en configMain.json
            $metaDataValues['nsproject'] = $id;
            $metaDataValues["responsable"] = $_SERVER['REMOTE_USER'];
            $metaDataValues['autor'] = $_SERVER['REMOTE_USER'];
            $metaDataValues['fitxercontinguts'] = $id.":".$metaDataValues['fitxercontinguts'];

            $metaData = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET,
                ProjectKeys::KEY_ID_RESOURCE => $id,
                ProjectKeys::KEY_PROJECT_TYPE => $projectType,                  // opcional
                ProjectKeys::KEY_FILTER => $paramsArr[ProjectKeys::KEY_FILTER], // opcional
                ProjectKeys::KEY_METADATA_VALUE => json_encode($metaDataValues)
            ];

            $this->projectModel->setData($metaData);    //crea la estructura y el contenido en 'mdprojects/'
            $this->projectModel->createDataDir($id);    //crea el directori del projecte a 'data/pages/'
            $ret = $this->projectModel->getData();      //obtiene la estructura y el contenido del proyecto
            $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_created'), $id);    //añade info para la zona de mensajes
            $ret[ProjectKeys::KEY_ID] = $this->idToRequestId($id);
        }
        if (!$ret)
            throw new ProjectExistException($id);
        else
            return $ret;
    }
}