<?php
/**
 * qdocProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');
require_once (WIKI_IOC_MODEL . "authorization/PagePermissionManager.php");
require_once (WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class qdocProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function getProjectDocumentName() {
        $ret = $this->getMetaDataProject();
        return $ret['fitxercontinguts'];
    }

    public function generateProject() {} //abstract obligatorio

    public function directGenerateProject($data) {
        //1. $data
        $plantilla = $data['projectMetaData']["plantilla"]['value'];
        $destino = $this->id.":".end(explode(":", $plantilla));
        $this->createPageFromTemplate($destino, $plantilla, NULL, "generate project");

        //2a. Otorga, al Autor, permisos sobre el directorio de proyecto
        PagePermissionManager::updatePagePermission($this->id.":*", $data['projectMetaData']["autor"]['value'], AUTH_UPLOAD);

        //2b. Otorga, al Responsable, permisos sobre el directorio de proyecto
        if ($data['projectMetaData']["autor"]['value'] !== $data['projectMetaData']["responsable"]['value'])
            PagePermissionManager::updatePagePermission($this->id.":*", $data['projectMetaData']["responsable"]['value'], AUTH_UPLOAD);

        //3a. Otorga permisos al autor sobre su propio directorio (en el caso de que no los tenga)
        $ns = WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel').$data['projectMetaData']["autor"]['value'].":";
        PagePermissionManager::updatePagePermission($ns."*", $data['projectMetaData']["autor"]['value'], AUTH_DELETE, TRUE);
        //3b. Incluye la página del proyecto en el archivo de atajos del Autor
        $params = [
             'id' => $this->id
            ,'autor' => $data['projectMetaData']["autor"]['value']
            ,'link_page' => $this->id
            ,'user_shortcut' => $ns.WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
        ];
        $this->includePageProjectToUserShortcut($params);

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
        $project_ns = $parArr['id'].":*";

        //Se ha modificado el Autor del proyecto
        if ($parArr['old_autor'] !== $parArr['new_autor']) {
            if ($parArr['old_autor'] !== $parArr['old_responsable']) {
                //Elimina ACL de old_autor sobre la página del proyecto
                $ret = PagePermissionManager::deletePermissionPageForUser($project_ns, $parArr['old_autor']);
                if (!$ret) $retError[] = "Error en eliminar permissos a '${parArr['old_autor']}' sobre '$project_ns'";
            }
            //Elimina el acceso a la página del proyecto en el archivo dreceres de de old_autor
            if ($parArr['old_autor']!=="") {
                $old_usershortcut = $parArr['userpage_ns'].$parArr['old_autor'].":".$parArr['shortcut_name'];
                $this->removeProjectPageFromUserShortcut($old_usershortcut, $parArr['link_page']);
            }

            //Crea ACL para new_autor sobre la página del proyecto
            $ret = PagePermissionManager::updatePagePermission($project_ns, $parArr['new_autor'], AUTH_UPLOAD, TRUE);
            if (!$ret) $retError[] = "Error en assignar permissos a '${parArr['new_autor']}' sobre '$project_ns'";

            //Otorga permisos al autor sobre su propio directorio (en el caso de que no los tenga)
            $ns = $parArr['userpage_ns'].$parArr['new_autor'].":";
            PagePermissionManager::updatePagePermission($ns."*", $parArr['new_autor'], AUTH_DELETE, TRUE);
            //Escribe un acceso a la página del proyecto en el archivo de atajos de de new_autor
            $link_page = ($parArr['old_autor']!=="") ? $parArr['link_page'] : $parArr['id'];
            $params = [
                 'id' => $parArr['id']
                ,'autor' => $parArr['new_autor']
                ,'link_page' => $link_page
                ,'user_shortcut' => $ns.$parArr['shortcut_name']
            ];
            $this->includePageProjectToUserShortcut($params);
        }

        //Se ha modificado el Responsable del proyecto
        if ($parArr['old_responsable'] !== $parArr['new_responsable']) {
            if ($parArr['old_autor'] !== $parArr['old_responsable']) {
                //Elimina ACL de old_responsable sobre la página del proyecto
                if ($parArr['old_responsable']!=="") {
                    $ret = PagePermissionManager::deletePermissionPageForUser($project_ns, $parArr['old_responsable']);
                    if (!$ret) $retError[] = "Error en eliminar permissos a '${parArr['old_responsable']}' sobre '$project_ns'";
                }
            }
            //Crea ACL para new_responsable sobre la página del proyecto
            $ret = PagePermissionManager::updatePagePermission($project_ns, $parArr['new_responsable'], AUTH_UPLOAD, TRUE);
            if (!$ret) $retError[] = "Error en assignar permissos a '${parArr['new_responsable']}' sobre '$project_ns'";
        }

        if ($retError) {
            foreach ($retError as $e) {
                throw new UnknownProjectException($project_ns, $e);
            }
        }
    }

    /**
     * Inserta en la página de dreceres del usuario un texto con enlace al proyecto
     * Si la página dreceres.txt del usuario no existe, se crea a partir de la plantilla 'userpage_shortcuts_ns'
     * @param array $parArr ['id', 'autor', 'link_page', 'user_shortcut']
     */
    private function includePageProjectToUserShortcut($parArr) {
        $summary = "include Page Project To User Shortcut";
        $comment = ($parArr['link_page'] === $parArr['id']) ? "al" : "als continguts del";
        $shortcutText = "\n[[${parArr['link_page']}|accés $comment projecte ${parArr['id']}]]\n";
        $text = $this->getPageDataQuery()->getRaw($parArr['user_shortcut']);
        if ($text == "") {
            //La página dreceres.txt del usuario no existe
            $this->createPageFromTemplate($parArr['user_shortcut'], WikiGlobalConfig::getConf('template_shortcuts_ns', 'wikiiocmodel'), $shortcutText, $summary);
        }else {
            if (preg_match("/${parArr['link_page']}/", $text) === 1) {
                $eliminar = "/\[\[${parArr['link_page']}\|.*]]/";
                $text = preg_replace($eliminar, "", $text); //texto hallado -> eliminamos antiguo
            }
            $this->createPageFromTemplate($parArr['user_shortcut'], NULL, $text.$shortcutText, $summary);
        }
    }

    /**
     * Elimina el link al proyecto contenido en el archivo dreceres del usuario
     */
    private function removeProjectPageFromUserShortcut($usershortcut, $link_page) {
        $text = $this->getPageDataQuery()->getRaw($usershortcut);
        if ($text !== "" ) {
            if (preg_match("/$link_page/", $text) === 1) {  //subtexto hallado
                $eliminar = "/\[\[$link_page\|.*]]/";
                $text = preg_replace($eliminar, "", $text);
                $this->createPageFromTemplate($usershortcut, NULL, $text, "removeProjectPageFromUserShortcut");
            }
        }
    }

    /**
     * Crea el archivo $destino a partir de una plantilla
     */
    private function createPageFromTemplate($destino, $plantilla=NULL, $extra=NULL, $summary="generate project") {
        $text = ($plantilla) ? $this->getPageDataQuery()->getRaw($plantilla) : "";
        $this->dokuPageModel->setData([PageKeys::KEY_ID => $destino,
                                       PageKeys::KEY_WIKITEXT => $text . $extra,
                                       PageKeys::KEY_SUM => $summary]);
    }

}
