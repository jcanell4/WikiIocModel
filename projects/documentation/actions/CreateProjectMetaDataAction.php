<?php
if (!defined("DOKU_INC")) die();
include_once 'ProjectMetadataAction.php';

class CreateProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Crea una estructura de directorios para el nuevo proyecto (tipo de proyecto)
     * a partir del archivo de configuración configMain.json
     * @param array $this->params : parámetros recibidos por el ajaxCall
     */
    public function responseProcess() {
        $id = $this->params[ProjectKeys::KEY_ID];
        $projectType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];
        $new_id = $this->params['new_id'];
        $new_projectType = $this->params['new_project'];
        $projectModel = $this->getModel();

        $projectModel->init($id, $projectType);

        //sólo se ejecuta si no existe el proyecto
        if (!$projectModel->existProject($new_id)) {

            //Verifica que el proyecto solicitado sea un proyecto permitido
            $listProjectTypes = $projectModel->getListProjectTypes($projectType);
            if (!in_array($new_projectType, $listProjectTypes)) {
                throw new UnknownProjectException($new_id, "El tipus de projecte so·licitat no està permés.");
            }
            //No se permite la creación de un nuevo proyecto dentro de un proyecto hijo
            $hasProject = $projectModel->getThisProject($new_id);
            if ($hasProject['nsproject'] !== $id) {
                throw new UnknownProjectException($new_id, "No es permet la creació d'un projecte dins d'un subprojecte.");
            }

            $new_ProjectModel = new ProjectModel($this->persistenceEngine);
            $new_ProjectModel->init($new_id, $new_projectType);

            //obtiene las claves de la estructura de los metadatos del proyecto
            $metaDataKeys = $new_ProjectModel->getMetaDataDefKeys($new_projectType);
            foreach ($metaDataKeys as $key => $value) {
                if ($value['default']) $metaDataValues[$key] = $value['default'];
            }
            //asigna valores por defecto a algunos campos definidos en configMain.json
            $metaDataValues['nsproject'] = $new_id;
            $metaDataValues["responsable"] = $_SERVER['REMOTE_USER'];
            $metaDataValues['autor'] = $_SERVER['REMOTE_USER'];
            $metaDataValues['fitxercontinguts'] = $new_id.":".array_pop(explode(":", $metaDataValues['plantilla']));

            $metaData = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET,
                ProjectKeys::KEY_ID_RESOURCE => $new_id,
                ProjectKeys::KEY_PROJECT_TYPE => $new_projectType,
                ProjectKeys::KEY_FILTER => $this->params[ProjectKeys::KEY_FILTER], // opcional
                ProjectKeys::KEY_METADATA_VALUE => json_encode($metaDataValues)
            ];

            $new_ProjectModel->setData($metaData);       //crea la estructura y el contenido en 'mdprojects/'
            $new_ProjectModel->createDataDir($new_id); //crea el directori del projecte a 'data/pages/'
            $ret = $new_ProjectModel->getData();       //obtiene la estructura y el contenido del proyecto
            $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_created'), $new_id); //añade info para la zona de mensajes
            $ret[ProjectKeys::KEY_ID] = $this->idToRequestId($new_id);
        }

        if (!$ret)
            throw new ProjectExistException($id);
        else
            return $ret;
    }
}