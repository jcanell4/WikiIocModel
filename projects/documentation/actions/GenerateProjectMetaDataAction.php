<?php
if (!defined("DOKU_INC")) die();

class GenerateProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Crea los archivos necesarios definidos en la estructura del proyecto
     * @param type $paramsArr
     */
    public function get($paramsArr = array()) {
        $this->projectModel->init($paramsArr[ProjectKeys::KEY_ID], $paramsArr[ProjectKeys::KEY_PROJECT_TYPE]);
        
        //sólo se ejecuta si existe el proyecto
        if ($this->projectModel->existProject($paramsArr[ProjectKeys::KEY_ID])) {
            //asigna los valores por defecto a los campos definidos en configMain.json
            $metaDataValues = [
                "responsable" => $_SERVER['REMOTE_USER'],
                "titol" => $paramsArr[ProjectKeys::KEY_ID],
                "autor" => $_SERVER['REMOTE_USER']
            ];

            $metaData = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $paramsArr[ProjectKeys::KEY_PROJECT_TYPE], // Opcional
                ProjectKeys::KEY_METADATA_SUBSET => self::defaultSubSet,
                ProjectKeys::KEY_ID_RESOURCE => $paramsArr[ProjectKeys::KEY_ID], 
                ProjectKeys::KEY_FILTER => $paramsArr[ProjectKeys::KEY_FILTER], // Opcional
                ProjectKeys::KEY_METADATA_VALUE => json_encode($metaDataValues)
            ];

            //crea la estructura y el contenido en 'mdprojects/'
            $this->projectModel->setData($metaData);
            //crea el directori del projecte a 'data/pages/'
            $this->projectModel->createDataDir($paramsArr[ProjectKeys::KEY_ID]);
            //obtiene la estructura y el contenido del proyecto
            $ret = $this->projectModel->getData();
            //añade info para la zona de mensajes
            $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_created'), $paramsArr[ProjectKeys::KEY_ID]);
        }
        if (!$ret)
            throw new ProjectNotExistException($paramsArr[ProjectKeys::KEY_ID]);
        else
            return $ret;
    }
}