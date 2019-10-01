<?php
/**
 * guiesgesProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');
require_once (WIKI_IOC_MODEL . "authorization/PagePermissionManager.php");
require_once (WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class guiesgesProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function generateProject() {
        //0. Obtiene los datos del proyecto
        $ret = $this->getData();   //obtiene la estructura y el contenido del proyecto

        //2. Establece la marca de 'proyecto generado'
        $ret[ProjectKeys::KEY_GENERATED] = $this->projectMetaDataQuery->setProjectGenerated();

        if ($ret[ProjectKeys::KEY_GENERATED]) {
            try {
                $aAutors = preg_split("/[\s,]+/", $ret['projectMetaData']["autor"]['value']);
                foreach ($aAutors as $autor) {
                    //3a. Otorga, a cada Autor, permisos sobre el directorio de proyecto
                    PagePermissionManager::updatePagePermission($this->id.":*", $autor, AUTH_UPLOAD);

                    //4a. Otorga permisos a cada Autor sobre su propio directorio (en el caso de que no los tenga)
                    $ns = WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel').$autor.":";
                    PagePermissionManager::updatePagePermission($ns."*", $autor, AUTH_DELETE, TRUE);
                    //4b. Incluye la página del proyecto en el archivo de atajos del Autor
                    $params = [
                         'id' => $this->id
                        ,'link_page' => $this->id
                        ,'user_shortcut' => $ns.WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
                    ];
                    $this->includePageProjectToUserShortcut($params);
                }

                //3b. Otorga, a los Responsables, permisos sobre el directorio de proyecto
                $aResponsables = preg_split("/[\s,]+/", $ret['projectMetaData']["responsable"]['value']);
                foreach ($aResponsables as $responsable) {
                    if (! in_array($responsable, $aAutors)) {
                        PagePermissionManager::updatePagePermission($this->id.":*", $responsable, AUTH_UPLOAD);
                    }
                }
            }
            catch (Exception $e) {
                $ret[ProjectKeys::KEY_GENERATED] = FALSE;
                $this->projectMetaDataQuery->setProjectSystemStateAttr("generated", FALSE);
            }
        }

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

    public function createTemplateDocument($data){
        $pdir = $this->getProjectMetaDataQuery()->getProjectTypeDir()."metadata/plantilles/";
        $scdir = scandir($pdir);
        foreach($scdir as $file){
            if ($file !== '.' && $file !== '..' && substr($file, -4)===".txt") {
                $plantilla = file_get_contents($pdir.$file);
                $name = substr($file, 0, -4);
                $this->dokuPageModel->setData([PageKeys::KEY_ID => $this->id.":".$name,
                                               PageKeys::KEY_WIKITEXT => $plantilla,
                                               PageKeys::KEY_SUM => "generate project"]);
            }
        }
    }

    public function llistaDePlantilles() {
        $pdir = $this->getProjectMetaDataQuery()->getProjectTypeDir()."metadata/plantilles/";
        $scdir = scandir($pdir);
        foreach($scdir as $file){
            if ($file !== '.' && $file !== '..' && substr($file, -4)===".txt") {
                $arrTemplates[] = $this->id.":".substr($file, 0, -4);
            }
        }
        return $arrTemplates;
    }
}
