<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN'))  define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/ProjectKeys.php";

class CreateProjectMetaDataAction extends SetProjectMetaDataAction {

    /**
     * Crea un fichero para el nuevo proyecto (tipo de proyecto) a partir del archivo de configuración configMain.json
     * @param type $paramsArr
     */
    public function get($paramsArr = array()) {
        $this->projectModel->init($paramsArr[ProjectKeys::KEY_ID], $paramsArr[ProjectKeys::KEY_PROJECT_TYPE]);

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

        return $ret;
    }
}