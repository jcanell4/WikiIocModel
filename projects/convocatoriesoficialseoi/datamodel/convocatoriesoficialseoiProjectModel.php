<?php
/**
 * convocatoriesoficialseoiProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class convocatoriesoficialseoiProjectModel extends MultiContentFilesProjectModel {

    public function __construct($persistenceEngine) {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;
    }

    public function getProjectDocumentName() {
        $ret = $this->getCurrentDataProject();
        return $ret['fitxercontinguts'];
    }

    public function generateProject() {
        $ret = $this->projectMetaDataQuery->setProjectGenerated();
        return TRUE;
    }

    /**
     * Canvia el nom dels directoris del projecte indicat
     * @param string $base_old_dir : directori wiki del projecte
     * @param string $old_name : nom actual del projecte
     * @param string $new_name : nou nom del projecte
     */
    protected function renameDirNames($base_old_dir, $old_name, $new_name) {
        try {
            $this->projectMetaDataQuery->renameDirNames($base_old_dir, $old_name, $base_old_dir, $new_name);
        }catch (Exception $e) {
            throw new Exception("renameProject: Error mentre canviava el nom del projecte. $e.");
        }
    }

    /**
     * Canvia el contingut dels arxius ".changes" i ".meta" que contenen la ruta del projecte per la ruta amb el nou nom de projecte
     * @param string $base_old_dir : directori wiki del projecte
     * @param string $old_name : nom actual del projecte
     * @param string $new_name : nou nom del projecte
     */
    protected function changeOldPathInRevisionFiles($base_old_dir, $old_name, $new_name) {
        try {
            $this->projectMetaDataQuery->changeOldPathInRevisionFiles($base_old_dir, $old_name, $base_old_dir, $new_name);
        }catch (Exception $e) {
            throw new Exception("renameProject: Error mentre canviava el contingut de: $e.");
        }
    }

    /**
     * Canvia el contingut de l'arxiu ACL que pot contenir la ruta antiga del projecte
     * @param string $old_name : nom actual del projecte
     * @param string $new_name : nou nom del projecte
     */
    protected function changeOldPathInACLFile($base_old_dir, $old_name, $new_name) {
        try {
            $this->projectMetaDataQuery->changeOldPathInACLFile($base_old_dir, $old_name, $base_old_dir, $new_name);
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
     * Canvia el nom dels arxius del tipus $listfiles que contenen (en el nom) l'antiga ruta del projecte
     * @param string $base_old_dir : directori wiki del projecte
     * @param string $old_name : nom actual del projecte
     * @param string $new_name : nou nom del projecte
     * @param array $listfiles : llista d'arxius generats pel render que cal renombrar
     */
    protected function renameRenderGeneratedFiles($base_old_dir, $old_name, $new_name, $listfiles=[]) {
        try {
            $this->projectMetaDataQuery->renameRenderGeneratedFiles("$base_old_dir/$old_name", "$base_old_dir/$new_name", $listfiles);
        }catch (Exception $e) {
            throw new Exception("renameProject: Error mentre canviava el nom de l'arxiu: $e.");
        }
    }

    /**
     * Canvia el contingut dels arxius que contenen l'antiga ruta del projecte (normalment la ruta absoluta a les imatges)
     * @param string $base_old_dir : directori wiki del projecte
     * @param string $old_name : nom actual del projecte
     * @param string $new_name : nou nom del projecte
     * @throws Exception
     */
    protected function changeOldPathInContentFiles($base_old_dir, $old_name, $new_name) {
        try {
            $this->projectMetaDataQuery->changeOldPathInContentFiles($base_old_dir, $old_name, $base_old_dir, $new_name);
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
    protected function listGeneratedFilesByRender($base_dir=NULL, $old_name=NULL) {
        $basename = str_replace([":","/"], "_", $base_dir) . "_" . $old_name;
        return [$basename."_a2.zip", $basename."_b1.zip", $basename."_b2.zip"];
    }

    public function validateProjectDates() {
        $projectData = $this->getDataProject();

        $today = new DateTime();
        $dataProvaA2 = DateTime::createFromFormat('Y-m-d', $projectData["dadesEspecifiquesProvaA2"]["dataProva"]);
        $dataProvaB1 = DateTime::createFromFormat('Y-m-d', $projectData["dadesEspecifiquesProvaB1"]["dataProva"]);
        $dataProvaB2 = DateTime::createFromFormat('Y-m-d', $projectData["dadesEspecifiquesProvaB2"]["dataProva"]);
        $dataResultats = DateTime::createFromFormat('Y-m-d', $projectData['dataResultats']);
        $dataDemandaNE = DateTime::createFromFormat('Y-m-d', $projectData['dataDemandaNE']);

        $validated = true;
        $validated &= $dataProvaA2 > $today && $dataProvaB1 > $today && $dataProvaB2 > $today;
        $validated &= $dataResultats > $dataProvaA2 && $dataResultats > $dataProvaB1 && $dataResultats > $dataProvaB2;
        $validated &= $dataDemandaNE > $today && $dataDemandaNE < $dataProvaA2 && $dataDemandaNE < $dataProvaB1 && $dataDemandaNE < $dataProvaB2;

        return $validated;
    }

    public function validateTemplates($configTemplates=NULL) {
        if ($configTemplates == NULL) {
            $data = $this->getData();
            $configTemplates = $data[ProjectKeys::KEY_PROJECT_METADATA]['plantilla']['value'];
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

    public function createTemplateDocument($data=NULL) {
        $templates = $data[ProjectKeys::KEY_PROJECT_METADATA]["plantilla"]['value'];
        $this->setTemplateDocuments($templates);
    }

    /**
     * Calcula el valor de los campos calculables
     * @param JSON $data
     */
    public function updateCalculatedFieldsOnSave($values, $originalDataKeyValue=false, $subset=false) {
        $values["dataReclamacions"] = $this->sumDate($values["dataResultats"], 3);
        $values["dadesEspecifiquesProvaA2"]["dataProvaNE"] = $this->sumDate($values["dadesEspecifiquesProvaA2"]["dataProva"], 5);
        $values["dadesEspecifiquesProvaB1"]["dataProvaNE"] = $this->sumDate($values["dadesEspecifiquesProvaB1"]["dataProva"], 5);
        $values["dadesEspecifiquesProvaB2"]["dataProvaNE"] = $this->sumDate($values["dadesEspecifiquesProvaB2"]["dataProva"], 5);

        return parent::updateCalculatedFieldsOnSave($values, $originalDataKeyValue);
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