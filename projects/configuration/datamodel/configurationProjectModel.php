<?php
/**
 * configurationProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class configurationProjectModel extends AbstractProjectModel{

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function generateProject() {
        //
        // NOTA:
        //    Este proyecto no es generable
        // 

        //0. Obtiene los datos del proyecto
        $ret = $this->getData();   //obtiene la estructura y el contenido del proyecto
        $plantilla = $ret['projectMetaData']["plantilla"]['value'];
        $ret['projectMetaData']["fitxercontinguts"]['value'] = $destino = $this->id.":".end(explode(":", $plantilla));

        //1. Crea el archivo 'continguts', en la carpeta del proyecto, a partir de la plantilla especificada
        $this->createPageFromTemplate($destino, $plantilla, NULL, "generate project");

        //3. Otorga, a cada 'person', permisos adecuados sobre el directorio de proyecto y añade shortcut
        $params = $this->buildParamsToPersons($ret['projectMetaData'], NULL);
        $this->modifyACLPageAndShortcutToPerson($params);

        //4. Establece la marca de 'proyecto generado'
        $ret[ProjectKeys::KEY_GENERATED] = $this->projectMetaDataQuery->setProjectGenerated();

        return $ret;
    }

}
