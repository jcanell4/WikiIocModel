<?php
/**
 * eoiProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');
require_once(WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class convocatoriesoficialseoiProjectModel extends AbstractProjectModel
{

    public function __construct($persistenceEngine)
    {
        parent::__construct($persistenceEngine);
    }

//    public function getId()
//    {
//        return $this->id;
//    }

    public function getProjectDocumentName()
    {
        $ret = $this->getMetaDataProject();
        return $ret['fitxercontinguts'];
    }

    protected function getContentDocumentIdFromResponse($responseData)
    {
        if ($responseData['projectMetaData']["fitxercontinguts"]['value']) {
            $contentName = $responseData['projectMetaData']["fitxercontinguts"]['value'];
        } else {
            $contentName = end(explode(":", $this->getTemplateContentDocumentId($responseData)));
        }
        return $this->id . ":" . $contentName;
    }

    public function generateProject()
    {
        // Considerem que el projecte està generat si les dates son correctes, això permet fer l'exportació
        $success = $this->validateProjectDates(); // TODO: cridar a una funció que validi les dades
        $ret = ($success) ? $this->projectMetaDataQuery->setProjectGenerated() : FALSE;
        $this->projectMetaDataQuery->setProjectSystemStateAttr("generated", $ret);
        return $ret;
    }

    protected function validateProjectDates()
    {
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

    public function validateTemplates()
    {

        $data = $this->getData();
        $configTemplates = $data['projectMetaData']['plantilla']['value'];
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

    public function setTemplateDocuments($files, $reason = "generate project")
    {

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

    public function createTemplateDocument($data)
    {
        $templates = $data['projectMetaData']["plantilla"]['value'];

        $this->setTemplateDocuments($templates);

    }


    /**
     * Calcula el valor de los campos calculables
     * @param JSON $data
     */
    public function updateCalculatedFields($data)
    {

        $values = json_decode($data, true);

        $values["dataReclamacions"] = $this->sumDate($values["dataResultats"], 3);
        $values["dataProvaNE1"] = $this->sumDate($values["dataProva1"], 5);
        $values["dataProvaNE2"] = $this->sumDate($values["dataProva2"], 5);

        $data = json_encode($values);
        return parent::updateCalculatedFields($data);
    }

    protected function sumDate($date, $days, $months = 0, $years = 0, $sep = "-")
    {
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