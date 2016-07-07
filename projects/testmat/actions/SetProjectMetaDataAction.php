<?php

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/testmat/datamodel/ProjectModel.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/ProjectKeys.php";

class SetProjectMetaDataAction extends AbstractWikiAction
{

    const  defaultSubSet = 'main';
    protected $projectModel;
    protected $persistenceEngine;


    public function __construct($persistenceEngine)
    {
        $this->persistenceEngine = $persistenceEngine;
        $this->projectModel = new ProjectModel($persistenceEngine);


    }

    public function get(/*Array*/
        $paramsArr = array())
    {
        $this->projectModel->init($paramsArr[ProjectKeys::KEY_ID], $paramsArr[ProjectKeys::KEY_PROJECT_TYPE]);

        $blacklist = ['id', 'projectType', 'do', 'submit', 'sectok']; // Valors que no pertanyen al formulari

        $metaDataValues = $this->removeKeysFromArray($blacklist, $paramsArr);

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

    private function removeKeysFromArray($keys, $array) {
        $cleanArray = [];
        foreach($array as $key=>$value) {
            if (!in_array($key, $keys)) {
                $cleanArray[$key] = $value;
            }
        }

        return $cleanArray;
    }
}