<?php
/**
 * ptfctProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class ptfctProjectModel extends MoodleUniqueContentFilesProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;
    }

    public function getProjectDocumentName() {
        $ret = $this->getCurrentDataProject();
        return $ret['fitxercontinguts'];
    }

//    public function generateProject() {
//        $ret = array();
//        //0. Obtiene los datos del proyecto
//        $ret = $this->getData();   //obtiene la estructura y el contenido del proyecto
//
//        //2. Establece la marca de 'proyecto generado'
//        $ret[ProjectKeys::KEY_GENERATED] = $this->getProjectMetaDataQuery()->setProjectGenerated();
//
//        if ($ret[ProjectKeys::KEY_GENERATED]) {
//            try {
//                //3. Otorga, a las Persons, permisos sobre el directorio de proyecto y añade enlace a dreceres
//                $params = $this->buildParamsToPersons($ret['projectMetaData'], NULL);
//                $this->modifyACLPageAndShortcutToPerson($params);
//            }
//            catch (Exception $e) {
//                $ret[ProjectKeys::KEY_GENERATED] = FALSE;
//                $this->getProjectMetaDataQuery()->setProjectSystemStateAttr("generated", FALSE);
//            }
//        }
//
//        return $ret;
//    }

    public function getTemplateContentDocumentId($responseData=NULL){
        return "continguts";
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
