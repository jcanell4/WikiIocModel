<?php

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/ProjectKeys.php";
require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";
//require_once DOKU_PLUGIN . "wikiiocmodel/projects/documentation/datamodel/ProjectModel.php"; //autoload

class SetProjectMetaDataAction extends AbstractWikiAction {

    const  defaultSubSet = 'main';
    protected $projectModel;
    protected $persistenceEngine;

    public function __construct($persistenceEngine) {
        $this->persistenceEngine = $persistenceEngine;
        $this->projectModel = new ProjectModel($persistenceEngine);
    }

    public function get($paramsArr = array()) {
        $this->projectModel->init($paramsArr[ProjectKeys::KEY_ID], $paramsArr[ProjectKeys::KEY_PROJECT_TYPE]);

        //sólo se ejecuta si no existe el proyecto
        if (!$this->projectModel->existProject($paramsArr[ProjectKeys::KEY_ID])) {
            
            $metaDataValues = $this->recullFormulari($paramsArr);
            
            $metaData = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $paramsArr[ProjectKeys::KEY_PROJECT_TYPE], // Opcional
                ProjectKeys::KEY_METADATA_SUBSET => self::defaultSubSet,
                ProjectKeys::KEY_ID_RESOURCE => $paramsArr[ProjectKeys::KEY_NS], //[TODO Rafa] Jooools! la ruta no está en ID, está en NS
                ProjectKeys::KEY_FILTER => $paramsArr[ProjectKeys::KEY_FILTER], // Opcional
                ProjectKeys::KEY_METADATA_VALUE => json_encode($metaDataValues)
            ];

            $ret = $this->projectModel->setData($metaData);
            $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_saved'), $paramsArr[ProjectKeys::KEY_ID]);
        }
        if (!$ret)
            throw new ProjectExistException($paramsArr[ProjectKeys::KEY_NS]);
        else
            return $ret;
    }

    private function recullFormulari($params) {
        $excludeKeys = ['id', 'ns', 'projectType', 'do', 'submit', 'sectok']; //valors que no pertanyen al formulari
        $cleanParams = $this->removeKeysFromArray($excludeKeys, $params);     //[TODO Rafa] Aquestes keys haurien de ser conegudes per defecte
        return $cleanParams;                                                  //sense que calgués possar-les manualment
    }
    
    private function removeKeysFromArray($excludeKeys, $array) {
        $cleanArray = [];
        foreach ($array as $key => $value) {
            if (!in_array($key, $excludeKeys)) {
                $cleanArray[$key] = $value;
            }
        }
        return $cleanArray;
    }
}