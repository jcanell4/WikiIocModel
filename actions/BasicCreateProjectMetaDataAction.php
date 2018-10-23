<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
include_once WIKI_IOC_MODEL."actions/ProjectMetadataAction.php";

class BasicCreateProjectMetaDataAction extends ProjectMetadataAction {

    protected function setParams($params) {
        parent::setParams($params);
        $this->getModel()->init([ProjectKeys::KEY_ID              => $this->params[ProjectKeys::KEY_ID],
                                 ProjectKeys::KEY_PROJECT_TYPE    => $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                 ProjectKeys::KEY_METADATA_SUBSET => $this->params[ProjectKeys::KEY_METADATA_SUBSET],
                                 ProjectKeys::KEY_PROJECTTYPE_DIR => $this->params[ProjectKeys::KEY_PROJECTTYPE_DIR]
                               ]);
    }

    /**
     * Crea una estructura de directorios para el nuevo proyecto (tipo de proyecto)
     * a partir del archivo de configuración configMain.json correspondiente
     */
    public function responseProcess() {
        $model = $this->getModel();
        $modelAttrib = $model->getModelAttributes();
        $id = $modelAttrib[ProjectKeys::KEY_ID];
        $projectType = $modelAttrib[ProjectKeys::KEY_PROJECT_TYPE];

        //sólo se ejecuta si no existe el proyecto
        if (!$model->existProject()) {

            $metaDataValues = $this->getDefaultValues();

            $metaData = [
                ProjectKeys::KEY_ID_RESOURCE => $id,
                ProjectKeys::KEY_PROJECT_TYPE => $projectType,
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_METADATA_SUBSET => $modelAttrib[ProjectKeys::KEY_METADATA_SUBSET],
                ProjectKeys::KEY_PROJECTTYPE_DIR => $modelAttrib[ProjectKeys::KEY_PROJECTTYPE_DIR],
                ProjectKeys::KEY_FILTER => $this->params[ProjectKeys::KEY_FILTER], // opcional
                ProjectKeys::KEY_METADATA_VALUE => json_encode($metaDataValues)
            ];

            $model->setData($metaData);    //crea la estructura y el contenido en 'mdprojects/'
            $model->createDataDir($id);    //crea el directori del projecte a 'data/pages/'

            $ret = $model->getData();      //obtiene la estructura y el contenido del proyecto

            if ($ret['projectMetaData']["plantilla"]['value']) {
                $link_page = ":".end(explode(":", $ret['projectMetaData']["plantilla"]['value']));
            }
            $include = [
                 'id' => $id
                ,'link_page' => $id.$link_page
                ,'old_autor' => ""
                ,'old_responsable' => ""
                ,'new_autor' => $ret['projectMetaData']['autor']['value']
                ,'new_responsable' => $ret['projectMetaData']['responsable']['value']
                ,'userpage_ns' => WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel')
                ,'shortcut_name' => WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
            ];
            $model->modifyACLPageToUser($include);

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
        $metaDataKeys = $this->projectModel->getMetaDataDefKeys();
        if ($metaDataKeys) {
            foreach ($metaDataKeys as $key => $value) {
                if ($value['default'])
                    $metaDataValues[$key] = $value['default'];
            }
        }
        //asigna valores por defecto a algunos campos definidos en configMain.json
        $metaDataValues["responsable"] = $_SERVER['REMOTE_USER'];
        $metaDataValues['autor'] = $_SERVER['REMOTE_USER'];

        return $metaDataValues;
    }
}