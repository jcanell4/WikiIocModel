<?php
/**
 * ptfplogseProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');
require_once (WIKI_IOC_MODEL . "authorization/PagePermissionManager.php");
require_once (WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class ptfplogseProjectModel extends AbstractProjectModel {

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
     * Calcula el valor de los campos calculables
     * @param JSON $data
     */
    public function updateCalculatedFields($data) {

        $values = json_decode($data, true);

        $taulaDadesUnitats = (is_array($values["taulaDadesUD"])) ? $values["taulaDadesUD"] : json_decode($values["taulaDadesUD"], true);
        $taulaCalendari = (is_array($values["calendari"])) ? $values["calendari"] : json_decode($values["calendari"], true);
        $taulaJT = (is_array($values["datesJT"])) ? $values["datesJT"] : json_decode($values["datesJT"], true);

        if ($taulaJT!=NULL){
            $hiHaRecuperacio = FALSE;
            for ($i=0; !$hiHaRecuperacio && $i<count($taulaJT); $i++){
                $hiHaRecuperacio = $taulaJT[$i]["hiHaRecuperacio"];
            }
            $values["hiHaRecuperacioPerJT"] = $hiHaRecuperacio;
        }

        $taulaEAF = (is_array($values["datesEAF"])) ? $values["datesEAF"] : json_decode($values["datesEAF"], true);

        if ($taulaEAF!=NULL){
            $hiHaSolucio = FALSE;
            $hiHaEnunciatRecuperacio = FALSE;
            for ($i=0; $i<count($taulaEAF); $i++){
                $hiHaSolucio |= $taulaEAF[$i]["hiHaSolucio"];
                $hiHaEnunciatRecuperacio |= $taulaEAF[$i]["hiHaEnunciatRecuperacio"];
            }

            $values["hiHaSolucioPerEAF"] = $hiHaSolucio === 0 ? FALSE : TRUE ;
            $values["hiHaEnunciatRecuperacioPerEAF"] = $hiHaEnunciatRecuperacio === 0 ? FALSE : TRUE ;
        }

        $taulaAC = (is_array($values["datesAC"])) ? $values["datesAC"] : json_decode($values["datesAC"], true);

        if ($taulaAC!=NULL){
            $hiHaSolucio = FALSE;
            for ($i=0; !$hiHaSolucio && $i<count($taulaAC); $i++){
                $hiHaSolucio = $taulaAC[$i]["hiHaSolucio"];
            }
            $values["hiHaSolucioPerAC"] = $hiHaSolucio;
        }

        if ($taulaCalendari!=NULL && $taulaDadesUnitats!=NULL){
            $hores = array();
            $hores[0] = 0;
            for ($i=0; $i<count($taulaCalendari);$i++){
                $idU = intval($taulaCalendari[$i]["unitat didàctica"]);
                if (!isset($hores[$idU])){
                    $hores[$idU]=0;
                }
                $hores[$idU]+= $taulaCalendari[$i]["hores"];
                $hores[0] += $taulaCalendari[$i]["hores"];
            }

            for ($i=0; $i<count($taulaDadesUnitats);$i++){
                $idU = intval($taulaDadesUnitats[$i]["unitat didàctica"]);
                if (isset($hores[$idU])){
                    $taulaDadesUnitats[$i]["hores"]=$hores[$idU];
                }
            }
            $values["durada"] = $hores[0];
            $values["taulaDadesUD"] = $taulaDadesUnitats;
        }

        $data = json_encode($values);
        return parent::updateCalculatedFields($data);
    }


}
