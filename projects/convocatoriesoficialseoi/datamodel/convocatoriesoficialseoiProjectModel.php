<?php
/**
 * eoiProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');
require_once(WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class convocatoriesoficialseoiProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine) {
        parent::__construct($persistenceEngine);
    }

    public function getProjectDocumentName() {
        $ret = $this->getMetaDataProject();
        return $ret['fitxercontinguts'];
    }

    protected function getContentDocumentIdFromResponse($responseData) {
        if ($responseData['projectMetaData']["fitxercontinguts"]['value']) {
            $contentName = $responseData['projectMetaData']["fitxercontinguts"]['value'];
        } else {
            $contentName = end(explode(":", $this->getTemplateContentDocumentId($responseData)));
        }
        return $this->id . ":" . $contentName;
    }

    public function generateProject() {
        // Considerem que el projecte està generat si les dates son correctes, això permet fer l'exportació
        $success = $this->validateProjectDates();
        $ret = ($success) ? $this->projectMetaDataQuery->setProjectGenerated() : FALSE;
        $this->projectMetaDataQuery->setProjectSystemStateAttr("generated", $ret);
        return $ret;
    }

    /**
     * Canvia el nom dels directoris del projecte indicat,
     * els noms dels fitxers generats amb la base del nom del projecte i
     * les referències a l'antic nom de projecte dins dels fitxers afectats
     * @param string $ns : ns original del projecte
     * @param string $new_name : nou nom pel projecte
     * @param string $persons : noms dels autors i els responsables separats per ","
     */
    public function renameProject($ns, $new_name, $persons) {
        $base_dir = explode(":", $ns);
        $old_name = array_pop($base_dir);
        $base_dir = implode("/", $base_dir);

        $this->renameDirNames($base_dir, $old_name, $new_name);
        $this->changeOldPathProjectInRevisionFiles($base_dir, $old_name, $new_name);
        $this->changeOldPathProjectInACLFile($old_name, $new_name);
        $this->changeOldPathProjectInShortcutFiles($old_name, $new_name, $persons);
        $this->renameRenderGeneratedFiles($base_dir, $old_name, $new_name, $this->listGeneratedFilesByRender($base_dir, $old_name) );
        $this->changeOldPathProjectInContentFiles($base_dir, $old_name, $new_name);

        $new_ns = preg_replace("/:[^:]*$/", ":$new_name", $ns);
        $this->setProjectId($new_ns);
    }

    /**
     * Canvia el nom dels directoris del projecte indicat
     * @param string $base_dir : directori wiki del projecte
     * @param string $old_name : nom actual del projecte
     * @param string $new_name : nou nom del projecte
     */
    protected function renameDirNames($base_dir, $old_name, $new_name) {
        try {
            $this->projectMetaDataQuery->renameDirNames($base_dir, $old_name, $new_name);
        }catch (Exception $e) {
            throw new Exception("renameProject: Error mentre canviava el nom del projecte. $e.");
        }
    }

    /**
     * Canvia el contingut dels arxius ".changes" i ".meta" que contenen la ruta del projecte per la ruta amb el nou nom de projecte
     * @param string $base_dir : directori wiki del projecte
     * @param string $old_name : nom actual del projecte
     * @param string $new_name : nou nom del projecte
     */
    protected function changeOldPathProjectInRevisionFiles($base_dir, $old_name, $new_name) {
        try {
            $this->projectMetaDataQuery->changeOldPathProjectInRevisionFiles($base_dir, $old_name, $new_name);
        }catch (Exception $e) {
            throw new Exception("renameProject: Error mentre canviava el contingut de: $e.");
        }
    }

    /**
     * Canvia el contingut de l'arxiu ACL que pot contenir la ruta antiga del projecte
     * @param string $old_name : nom actual del projecte
     * @param string $new_name : nou nom del projecte
     */
    protected function changeOldPathProjectInACLFile($old_name, $new_name) {
        try {
            $this->projectMetaDataQuery->changeOldPathProjectInACLFile($old_name, $new_name);
        }catch (Exception $e) {
            throw new Exception("renameProject: Error mentre canviava el contingut de: $e.");
        }
    }

    /**
     * Canvia el contingut dels arxius de dreceres d'autors i responsables amb la nova ruta del projecte
     * @param string $old_name : nom actual del projecte
     * @param string $new_name : nou nom del projecte
     * @param string $persons : noms dels autors i els responsables separats per ","
     */
    protected function changeOldPathProjectInShortcutFiles($old_name, $new_name, $persons) {
        try {
            $this->projectMetaDataQuery->changeOldPathProjectInShortcutFiles($old_name, $new_name, $persons);
        }catch (Exception $e) {
            throw new Exception("renameProject: Error mentre canviava el contingut de la drecera de: $e.");
        }
    }

    /**
     * Canvia el nom dels arxius $filetype que contenen (en el nom) l'antiga ruta del projecte
     * @param string $base_dir : directori wiki del projecte
     * @param string $old_name : nom actual del projecte
     * @param string $new_name : nou nom del projecte
     * @param array $listfiles : llista d'arxius generats pel render que cal renombrar
     */
    protected function renameRenderGeneratedFiles($base_dir, $old_name, $new_name, $listfiles=[]) {
        try {
            $this->projectMetaDataQuery->renameRenderGeneratedFiles($base_dir, $old_name, $new_name, $listfiles);
        }catch (Exception $e) {
            throw new Exception("renameProject: Error mentre canviava el nom de l'arxiu: $e.");
        }
    }

    /**
     * Canvia el contingut dels arxius que contenen l'antiga ruta del projecte (normalment la ruta absoluta a les imatges)
     * @param string $base_dir : directori wiki del projecte
     * @param string $old_name : nom actual del projecte
     * @param string $new_name : nou nom del projecte
     * @throws Exception
     */
    protected function changeOldPathProjectInContentFiles($base_dir, $old_name, $new_name) {
        try {
            $this->projectMetaDataQuery->changeOldPathProjectInContentFiles($base_dir, $old_name, $new_name);
        }catch (Exception $e) {
            throw new Exception("renameProject: Error mentre canviava el contingut d'algun axiu a: $e.");
        }
    }

    /**
     * Devuelve la lista de archivos que se generan a partir de la configuración
     * indicada en el archivo 'configRender.json'
     * Esos archivos se guardan en WikiGlobalConfig::getConf('mediadir')
     * El nombre de estos archivos se construyó, en el momento de su creación, usando el nombre del proyecto
     * @param string $base_dir : directori wiki del projecte
     * @param string $old_name : nom actual del projecte
     * @return array : lista de ficheros
     */
    protected function listGeneratedFilesByRender($base_dir, $old_name) {
        $basename = str_replace([":","/"], "_", $base_dir) . "_" . $old_name;
        return [$basename."_a2.zip", $basename."_b1.zip", $basename."_b2.zip"];
    }

    protected function validateProjectDates() {
        $projectData = $this->getData();

        $today = new DateTime();
        $dataProva1 = DateTime::createFromFormat('Y-m-d', $projectData['projectMetaData']['dataProva1']['value']);
        $dataProva2 = DateTime::createFromFormat('Y-m-d', $projectData['projectMetaData']['dataProva2']['value']);
        $dataResultats = DateTime::createFromFormat('Y-m-d', $projectData['projectMetaData']['dataResultats']['value']);
        $dataDemandaNE = DateTime::createFromFormat('Y-m-d', $projectData['projectMetaData']['dataDemandaNE']['value']);

        $validated = true;
        $validated &= $dataProva1 > $today && $dataProva2 > $today;
        $validated &= $dataResultats > $dataProva1 && $dataResultats > $dataProva2;
        $validated &= $dataDemandaNE > $today && $dataDemandaNE < $dataProva1 && $dataDemandaNE < $dataProva2;

        return $validated;
    }

    public function validateTemplates($configTemplates=NULL) {
        if ($configTemplates == NULL) {
            $data = $this->getData();
            $configTemplates = $data['projectMetaData']['plantilla']['value'];
        }
        $projectTemplatesDates = explode(',', $configTemplates);
        $projectFileDates = [];

        foreach ($projectTemplatesDates as $file) {
            $ID = $this->id . ':' . $file;
            $filepath = WikiFn($ID);
            $projectFileDates[$file] = filemtime($filepath);
        }

        $pdir = $this->getProjectMetaDataQuery()->getProjectTypeDir() . "metadata/plantilles/";

        foreach ($projectTemplatesDates as $key) {
            $file = $pdir . $key . '.txt';

            if (!isset($projectFileDates[$key])) {
                // No existeix el nom del fitxer
                return false;
            }
            $currentFileTime = filemtime($file);

            if ($currentFileTime > $projectFileDates[$key]) {
                // La plantilla del projecte ha estat modificada
                return false;
            }
        }

        return true;
    }

    public function setTemplateDocuments($files, $reason = "generate project") {
        // ALERTA[Xavi] Si no s'obté primer el $metaDataQuery de vegades falla l'actualització
        $metaDataQuery = $this->getProjectMetaDataQuery();
        $pdir = $metaDataQuery->getProjectTypeDir() . "metadata/plantilles/";
        $templates = explode(',', $files);

        foreach ($templates as $template) {
            $fullpath = $pdir . $template . ".txt";
            $plantilla = file_get_contents($fullpath);
            $destino = $this->getContentDocumentId($template);
            $this->dokuPageModel->setData([PageKeys::KEY_ID => $destino,
                PageKeys::KEY_WIKITEXT => $plantilla,
                PageKeys::KEY_SUM => $reason],
                true);
        }
    }

    public function createTemplateDocument($data) {
        $templates = $data['projectMetaData']["plantilla"]['value'];
        $this->setTemplateDocuments($templates);
    }

    /**
     * Calcula el valor de los campos calculables
     * @param JSON $data
     */
    public function updateCalculatedFields($data) {
        $values = json_decode($data, true);
        $values["dataReclamacions"] = $this->sumDate($values["dataResultats"], 3);
        $values["dataProvaNE1"] = $this->sumDate($values["dataProva1"], 5);
        $values["dataProvaNE2"] = $this->sumDate($values["dataProva2"], 5);

        $data = json_encode($values);
        return parent::updateCalculatedFields($data);
    }

    protected function sumDate($date, $days, $months = 0, $years = 0, $sep = "-") {
        if (!is_numeric($days) || !is_numeric($months) || !is_numeric($years)) {
            return "[ERROR! paràmetres incorrectes ($days, $months, $years)]"; //TODO: internacionalitzar
        }

        $newDate = $date;

        if ($days > 0) {
            $calculated = strtotime("+" . $days . " day", strtotime($date));
            $newDate = date("Y" . $sep . "m" . $sep . "d", $calculated);
        }

        if ($months > 0) {
            $calculated = strtotime("+" . $months . " month", strtotime($newDate));
            $newDate = date("Y" . $sep . "m" . $sep . "d", $calculated);
        }

        if ($years > 0) {
            $calculated = strtotime("+" . $years . " year", strtotime($newDate));
            $newDate = date("Y" . $sep . "m" . $sep . "d", $calculated);
        }

        return $newDate;
    }

}