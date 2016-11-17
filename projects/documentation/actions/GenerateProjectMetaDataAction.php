<?php
if (!defined("DOKU_INC")) die();

class GenerateProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Crea los archivos necesarios definidos en la estructura del proyecto
     * @param type $paramsArr
     */
    public function get($paramsArr = array()) {
        
        $id = str_replace("_", ":", $paramsArr[ProjectKeys::KEY_ID]);
        $projectType = $paramsArr[ProjectKeys::KEY_PROJECT_TYPE];
        
        $this->projectModel->init($id, $projectType);
        
        //sólo se ejecuta si existe el proyecto
        if ($this->projectModel->existProject($id)) {
            
            $isGenerated = $this->projectModel->isProjectGenerated($id, $projectType);
            if ($isGenerated) {
                $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_already_generated'), $id);  //añade info para la zona de mensajes
            } else {
                $ret = $this->projectModel->getData();   //obtiene la estructura y el contenido del proyecto
                $plantilla = $ret['projectMetaData']['values']["plantilla"];
                $destino = "$id:".end(explode(":", $plantilla));
                $this->projectModel->generateProject($id, $destino, $projectType, $plantilla);  //crea el contenido del proyecto en 'pages/'
                $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_generated'), $id);  //añade info para la zona de mensajes
            }
        }
        
        if (!$ret)
            throw new ProjectNotExistException($id);
        else
            return $ret;
    }
}