<?php
/**
 * manualProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');
require_once (WIKI_IOC_MODEL . "authorization/PagePermissionManager.php");
require_once (WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class manualProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

//    // Obtiene un array de claves=>valores (datos ampliados) para unirlos a los datos del proyecto
//    public function getExtendedData($id, $autor, $fitxercontinguts) {
//        $nom_real = PagePermissionManager::getUserList($autor)['values'][0]['name'];
//        $fitxer = $this->getPageDataQuery()->getFileName("$id:$fitxercontinguts");
//        $data = date("d/m/Y", filemtime($fitxer));
//        return ['nom_real' => $nom_real, 'data_fitxercontinguts' => $data];
//    }

    public function generateProject() {} //abstract obligatorio

    public function directGenerateProject($data) {
        //1. $data
//        $plantilla = $this->getListMetaDataComponentTypes(ProjectKeys::KEY_METADATA_COMPONENT_TYPES, ProjectKeys::KEY_MD_CT_DOCUMENTS)[0];
//        $destino = $this->id.":".end(explode(":", $plantilla));
//        $this->createPageFromTemplate($destino, $plantilla, NULL, "generate project");

        $aAutors = preg_split("/[\s,]+/", $data['projectMetaData']["autor"]['value']);
        foreach ($aAutors as $autor) {
            //2a. Otorga, a cada Autor, permisos sobre el directorio de proyecto
            PagePermissionManager::updatePagePermission($this->id.":*", $autor, AUTH_UPLOAD);

            //3a. Otorga permisos a cada Autor sobre su propio directorio (en el caso de que no los tenga)
            $ns = WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel').$autor.":";
            PagePermissionManager::updatePagePermission($ns."*", $autor, AUTH_DELETE, TRUE);
            //3b. Incluye la página del proyecto en el archivo de atajos del Autor
            $params = [
                 'id' => $this->id
                ,'autor' => $autor
                ,'link_page' => $this->id
                ,'user_shortcut' => $ns.WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
            ];
            $this->includePageProjectToUserShortcut($params);
        }

        //2b. Otorga, a los Responsables, permisos sobre el directorio de proyecto
        $aResponsables = preg_split("/[\s,]+/", $data['projectMetaData']["responsable"]['value']);
        foreach ($aResponsables as $responsable) {
            if (! in_array($responsable, $aAutors)) {
                PagePermissionManager::updatePagePermission($this->id.":*", $responsable, AUTH_UPLOAD);
            }
        }

        //4. Establece la marca de 'proyecto generado'
        $ret[ProjectKeys::KEY_GENERATED] = $this->projectMetaDataQuery->setProjectGenerated();

        return $ret;
    }

    /**
     * Modifica los permisos en el fichero de ACL y la página de atajos del autor
     * cuando se modifica el autor o el responsable del proyecto
     * @param array $parArr ['id','link_page','old_autor','old_responsable','new_autor','new_responsable','userpage_ns','shortcut_name']
     */
    public function modifyACLPageToUser($parArr) {
        parent::modifyACLPageToUser($parArr);
    }

}
