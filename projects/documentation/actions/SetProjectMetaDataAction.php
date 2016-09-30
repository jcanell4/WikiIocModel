<?php

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/documentation/datamodel/ProjectModel.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/ProjectKeys.php";

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

        $metaDataValues = $this->reconstructTree($paramsArr);

        $metaData = [
            ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
            ProjectKeys::KEY_PROJECT_TYPE => $paramsArr[ProjectKeys::KEY_PROJECT_TYPE], // Opcional
            ProjectKeys::KEY_METADATA_SUBSET => self::defaultSubSet,
            ProjectKeys::KEY_ID_RESOURCE => $paramsArr[ProjectKeys::KEY_ID],
            ProjectKeys::KEY_FILTER => $paramsArr[ProjectKeys::KEY_FILTER], // Opcional
            ProjectKeys::KEY_METADATA_VALUE => json_encode($metaDataValues)
        ];

        return $this->projectModel->setData($metaData); // TODO[Xavi] Descomentar una vegada el format sigui correcte!
    }

    private function reconstructTree($params) {
        $blacklist = ['id', 'projectType', 'do', 'submit', 'sectok']; // Valors que no pertanyen al formulari
        $cleanParams = $this->removeKeysFromArray($blacklist, $params);
        $tree = [];

        foreach ($cleanParams as $key => $value) {
            if ($value) {
                // Si la $key conté 1 o més punts, s'han de crear branques
                $this->addLeaf($key, $value, $tree);
            }
        }
        return $tree;
    }

    private function addLeaf($key, $value, &$tree) {

        $branches = explode('_', $key);
        $branch = $branches[0];

        if (count($branches) === 1) {
            $tree[$branch] = $value;
        } else if (count($branches) > 1 && is_numeric($branches[1])) {
            // Aquest element indica el itemType d'un array i per tant  només cal avançar al proper element
            $key = substr($key, strlen($branch) + 1); // S'ha de sumar 1 per eliminar el caràcter _
            $this->addLeaf($key, $value, $tree); // Cridem a afegir fulla amb els mateixos elements d'entrada
        } else {
            // Eliminem el primer element de $key
            $key = substr($key, strlen($branch) + 1); // S'ha de sumar 1 per eliminar el caràcter _

            // Si no existeix ja la branca, la afegim
            // Alerta[Xavi] Cal comprovar si és numéric?
            if (!isset($tree[$branch])) {
                $tree[$branch] = [];
            }

            $this->addLeaf($key, $value, $tree[$branch]); // Cridem a afegir fulla amb un element menys

        }
    }

    private function removeKeysFromArray($keys, $array) {
        $cleanArray = [];
        foreach ($array as $key => $value) {
            if (!in_array($key, $keys)) {
                $cleanArray[$key] = $value;
            }
        }

        return $cleanArray;
    }
}