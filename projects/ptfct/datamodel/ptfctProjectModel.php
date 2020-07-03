<?php
/**
 * ptfctProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class ptfctProjectModel extends MoodleProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function getProjectDocumentName() {
        $ret = $this->getCurrentDataProject();
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
        $ret[ProjectKeys::KEY_GENERATED] = $this->getProjectMetaDataQuery()->setProjectGenerated();

        if ($ret[ProjectKeys::KEY_GENERATED]) {
            try {
                //3. Otorga, a las Persons, permisos sobre el directorio de proyecto y añade enlace a dreceres
                $params = $this->buildParamsToPersons($ret['projectMetaData'], NULL);
                $this->modifyACLPageAndShortcutToPerson($params);
            }
            catch (Exception $e) {
                $ret[ProjectKeys::KEY_GENERATED] = FALSE;
                $this->getProjectMetaDataQuery()->setProjectSystemStateAttr("generated", FALSE);
            }
        }

        return $ret;
    }

    public function createTemplateDocument($data=NULL){
        StaticUniqueContentFileProjectModel::createTemplateDocument($this, $data);
//        $pdir = $this->getProjectMetaDataQuery()->getProjectTypeDir()."metadata/plantilles/";
//        // TODO: $file ha de ser el nom del fitxer de la plantilla, amb extensió?
//        $file = $this->getTemplateContentDocumentId($data) . ".txt";
//
//        $plantilla = file_get_contents($pdir.$file);
//        $name = substr($file, 0, -4);
//        $destino = $this->getContentDocumentId($name);
//        $this->dokuPageModel->setData([PageKeys::KEY_ID => $destino,
//            PageKeys::KEY_WIKITEXT => $plantilla,
//            PageKeys::KEY_SUM => "generate project"]);
    }

    public function getTemplateContentDocumentId($responseData){
        return "continguts";
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
        return [$basename.".zip"];
    }

    /**
     * Llista de les dates a pujar al calendari amb el format següent:
     *  - title
     *  - date (en format yyyy-mm-dd)
     *  - description
     */
    public function getCalendarDates() {
        $ret = array();
        $data = $this->getCurrentDataProject();
        $ret[] = [
            "title"=>"FCT inici",
            "date"=>$data["dataIniciFCT"],
            "description"=>"Data d'inici de la FCT",
        ];
        $ret[] = [
            "title"=>"FCT inici (data màxima)",
            "date"=>$data["dataMaxIniciFCT"],
            "description"=>"Data màxima per iniciar la FCT",
        ];
        $ret[] = [
            "title"=>"FCT 1a convocatòria",
            "date"=>$data["dataApteFCT"],
            "description"=>"Data de la 1a convocatòria de la FCT",
        ];
        $ret[] = [
            "title"=>"FCT 2a convocatòria",
            "date"=>$data["dataMaxApteFCT"],
            "description"=>"Data de la 2a convocatòria de la FCT",
        ];
        return $ret;
    }

    public function getCourseId() {
        $data = $this->getCurrentDataProject();
        return $data["moodleCourseId"];
    }

}
