<?php
/**
 * ptfctProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');
require_once (WIKI_IOC_MODEL . "authorization/PagePermissionManager.php");
require_once (WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class ptfctProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function getId(){
        return $this->id;
    }

    public function getProjectDocumentName() {
        $ret = $this->getMetaDataProject();
        return $ret['fitxercontinguts'];
    }

    protected function getContentDocumentIdFromResponse($responseData){
        if ($responseData['projectMetaData']["fitxercontinguts"]['value']){
            $contentName = $responseData['projectMetaData']["fitxercontinguts"]['value'];
        }else{
            $contentName = end(explode(":", $this->getTemplateContentDocumentId($responseData)));
        }
        return $this->id.":" .$contentName;
    }

    public function generateProject() {
        $ret = array();
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
                        ,'autor' => $autor
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

                //5. Otorga, a los Supervisores, permisos de lectura sobre el directorio de proyecto
                $aSupervisors = preg_split("/[\s,]+/", $ret['projectMetaData']["supervisor"]['value']);
                foreach ($aSupervisors as $supervisor) {
                    if (! (in_array($supervisor, $aAutors) || in_array($supervisor, $aResponsables)) ) {
                        PagePermissionManager::updatePagePermission($this->id.":*", $supervisor, AUTH_READ, TRUE);
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

    public function createTemplateDocument($data){
        $pdir = $this->getProjectMetaDataQuery()->getProjectTypeDir()."metadata/plantilles/";
        // TODO: $file ha de ser el nom del fitxer de la plantilla, amb extensió?
        $file = $this->getTemplateContentDocumentId($data) . ".txt";


        $plantilla = file_get_contents($pdir.$file);
        $name = substr($file, 0, -4);
        $destino = $this->getContentDocumentId($name);
        $this->dokuPageModel->setData([PageKeys::KEY_ID => $destino,
            PageKeys::KEY_WIKITEXT => $plantilla,
            PageKeys::KEY_SUM => "generate project"]);

    }

    public function getTemplateContentDocumentId($responseData){
        return "continguts";
    }

    public function modifyACLPageToSupervisor($parArr) {
        $project_ns = $parArr['id'].":*";

        // S'ha modificat el supervisor
        if ($parArr['old_supervisor'] !== $parArr['new_supervisor']) {
            if ($parArr['old_supervisor'] !== $parArr['new_autor']
                && $parArr['old_supervisor'] !== $parArr['new_responsable']) {
                //Elimina ACL de old_responsable sobre la página del proyecto
                if ($parArr['old_supervisor'] && $parArr['old_supervisor']!=="") {
                    $ret = PagePermissionManager::deletePermissionPageForUser($project_ns, $parArr['old_supervisor']);
                    if (!$ret) $retError[] = "Error en eliminar permissos a '${parArr['old_supervisor']}' sobre '$project_ns'";
                }
            }

            // Si el supervisor es també autor o responsable te permisos superiors, no cal fer res
            //Crea ACL para new_responsable sobre la pàgina del projecte
            if ($parArr['new_supervisor'] !== $parArr['new_autor']
                && $parArr['new_supervisor'] !== $parArr['new_responsable']
                && $parArr['new_supervisor'] !== '') {
                $ret = PagePermissionManager::updatePagePermission($project_ns, $parArr['new_supervisor'], AUTH_READ, TRUE);
                if (!$ret) $retError[] = "Error en assignar permissos a '${parArr['new_supervisor']}' sobre '$project_ns'";
            }
        }

        if ($retError) {
            foreach ($retError as $e) {
                throw new UnknownProjectException($project_ns, $e);
            }
        }
    }

    /**
     * Averigua si hay fichero para enviar por FTP
     * @return boolean
     */
    public function haveFilesToExportList() {
        $ret = $this->filesToExportList();
        return (!empty($ret));
    }

    /**
     * Obtiene la lista de ficheros, y sus propiedades, (del configMain.json) que hay que enviar por FTP
     * @return array
     */
    public function filesToExportList() {
        $ret = array();
        $metadata = $this->getProjectMetaDataQuery()->getMetaDataFtpSender();
        foreach ($metadata as $n => $ofile) {
            $path = ($ofile['local']==='mediadir') ? WikiGlobalConfig::getConf('mediadir')."/". str_replace(':', '/', $this->id)."/" : $ofile['local'];
            if (($dir = @opendir($path))) {
                while ($file = readdir($dir)) {
                    if (!is_dir("$path/$file") && end(explode(".",$file))===$ofile['type']) {
                        $ret[$n]['file'] = $file;
                        $ret[$n]['local'] = $path;
                        $ret[$n]['action'] = $ofile['action'];
//                        $ret[$n]['remoteBase'] = $ofile['remoteBase'];
                        $ret[$n]['remoteDir'] = $ofile['remoteDir'];
                    }
                }
            }
        }
        return $ret;
    }
}
