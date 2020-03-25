<?php
/**
 * qdocProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');
require_once (WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class qdocProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function getProjectDocumentName() {
        $ret = $this->getCurrentDataProject();
        return $ret['fitxercontinguts'];
    }

    public function generateProject() {} //abstract obligatorio

    public function directGenerateProject($data) {
        //1. $data
        $plantilla = $data['projectMetaData']["plantilla"]['value'];
        $destino = $this->id.":".end(explode(":", $plantilla));
        $this->createPageFromTemplate($destino, $plantilla, NULL, "generate project");

        //[TODO: Rafael] De momento, esto lo hace el padre durante la Creación
        //2. Otorga, a las Persons, permisos sobre el directorio de proyecto y 3. añade enlace a dreceres
//        $params = $this->buildParamsToPersons($data['projectMetaData'], NULL);
//        $this->modifyACLPageAndShortcutToPerson($params);

        //4. Establece la marca de 'proyecto generado'
        $ret = $this->projectMetaDataQuery->setProjectGenerated();

        return $ret;
    }

}
