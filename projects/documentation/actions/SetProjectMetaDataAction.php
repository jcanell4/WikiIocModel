<?php

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/ProjectKeys.php";
require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/documentation/datamodel/ProjectModel.php";

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

        $metaDataValues = $this->createProject($paramsArr);

        $metaData = [
            ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
            ProjectKeys::KEY_PROJECT_TYPE => $paramsArr[ProjectKeys::KEY_PROJECT_TYPE], // Opcional
            ProjectKeys::KEY_METADATA_SUBSET => self::defaultSubSet,
            ProjectKeys::KEY_ID_RESOURCE => $paramsArr[ProjectKeys::KEY_ID],
            ProjectKeys::KEY_FILTER => $paramsArr[ProjectKeys::KEY_FILTER], // Opcional
            ProjectKeys::KEY_METADATA_VALUE => json_encode($metaDataValues)
        ];

        return $this->projectModel->setData($metaData);
    }

    private function createProject($params) {
        
        $aPath = explode(':', $params['id']);
        foreach ($aPath as $value) {
            $tree[] = $value;
        }
        $tree[] = $params['projectType'];

        return $tree;
    }
}