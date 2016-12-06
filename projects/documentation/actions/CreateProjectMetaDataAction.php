<?php
if (!defined("DOKU_INC")) die();

class CreateProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Crea una estructura de directorios para el nuevo proyecto (tipo de proyecto) 
     * a partir del archivo de configuración configMain.json
     * @param type $paramsArr
     */
    public function get($paramsArr = array()) {
        $this->projectModel->init($paramsArr[ProjectKeys::KEY_ID], $paramsArr[ProjectKeys::KEY_PROJECT_TYPE]);
        
        //sólo se ejecuta si no existe el proyecto
        if (!$this->projectModel->existProject($paramsArr[ProjectKeys::KEY_ID])) {
            //obtiene la estructura y el contenido del proyecto (para pruebas)
//            $projectMetaData = $this->projectModel->getMetaDataDef($paramsArr[ProjectKeys::KEY_ID],$paramsArr[ProjectKeys::KEY_PROJECT_TYPE]);

            //asigna los valores por defecto a los campos definidos en configMain.json
            $metaDataValues = [
                "responsable" => $_SERVER['REMOTE_USER'],
                "titol" => $paramsArr[ProjectKeys::KEY_ID],
                "autor" => $_SERVER['REMOTE_USER'],
                "plantilla" => "plantilles:projects:continguts"
            ];

            $metaData = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $paramsArr[ProjectKeys::KEY_PROJECT_TYPE], // Opcional
                ProjectKeys::KEY_METADATA_SUBSET => self::defaultSubSet,
                ProjectKeys::KEY_ID_RESOURCE => $paramsArr[ProjectKeys::KEY_ID], 
                ProjectKeys::KEY_FILTER => $paramsArr[ProjectKeys::KEY_FILTER], // Opcional
                ProjectKeys::KEY_METADATA_VALUE => json_encode($metaDataValues)
            ];

            $this->projectModel->setData($metaData);            //crea la estructura y el contenido en 'mdprojects/'
            $this->projectModel->createDataDir($paramsArr[ProjectKeys::KEY_ID]);    //crea el directori del projecte a 'data/pages/'
            $ret = $this->projectModel->getData();              //obtiene la estructura y el contenido del proyecto
            $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_created'), $paramsArr[ProjectKeys::KEY_ID]);    //añade info para la zona de mensajes
        }
        if (!$ret)
            throw new ProjectExistException($paramsArr[ProjectKeys::KEY_ID]);
        else
            return $ret;
    }
}