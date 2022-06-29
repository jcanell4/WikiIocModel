<?php
/**
 * prgfpfctProjectModel
 * @culpable Josep Cañellas
 */
if (!defined("DOKU_INC")) die();

class prgfpfctProjectModel extends ProgramacioProjectModel {
    
    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction = false;
    }
    
    public function directGenerateProject() {
        //4. Establece la marca de 'proyecto generado'
        return $this->projectMetaDataQuery->setProjectGenerated();
    }

    public function validateFields($data=NULL, $subset=FALSE){
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::validateFields($data, $subset);
        }

        //EL responsable no pot ser buit
        if (isset($data["responsable"]) && empty(trim($data["responsable"]))){
            throw new InvalidDataProjectException($this->id, "El camp responsable no pot quedar buit");
        }
    }

    public function getErrorFields($data=NULL, $subset=FALSE){
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::getErrorFields($data, $subset);
        }

        $result  = array();
        
        //Camps obligatoris
        $responseType = "SINGLE_MESSAGE";
        $message = WikiIocLangManager::getLang("El camp %s és obligatori. Cal que %s.");
        $campsAComprovar = [
             ["typeField"=>"SF", "field"=>"departament", "accioNecessaria"=>"hi poseu el nom del departament"]
            ,["typeField"=>"SF", "field"=>"cicle", "accioNecessaria"=>"hi poseu el nom del cicle"]
            ,["typeField"=>"SF", "field"=>"modulId", "accioNecessaria"=>"hi poseu el codi del mòdul"]
            ,["typeField"=>"TF", "field"=>"resultatsAprenentatgeObjectiusTerminals", "accioNecessaria"=>"hi afegiu els resultats d'avaluació o Objectius Terminals"]
            ,["typeField"=>"SF", "field"=>"activitatsFormatives", "accioNecessaria"=>"hi afegiu les Activitats Formatives"]
            ,["typeField"=>"SF", "field"=>"cc_raonsModificacio", "accioNecessaria"=>"hi assigneu una raó per la modificació actual de la programació"]
            // ALERTA! Aquests camps no es corresponen amb els IDs que s'asignen als camps
            ,["typeField"=>"SF", "field"=>"autor", "accioNecessaria"=>"hi assigneu un autor"]
            ,["typeField"=>"OF", "field"=>"cc_dadesAutor#carrec", "accioNecessaria"=>"hi assigneu el càrrec de l'autor"]
            ,["typeField"=>"SF", "field"=>"revisor", "accioNecessaria"=>"hi assigneu un revisor"]
            ,["typeField"=>"OF", "field"=>"cc_dadesRevisor#carrec", "accioNecessaria"=>"hi assigneu el càrrec del revisor"]
            ,["typeField"=>"SF", "field"=>"validador", "accioNecessaria"=>"hi assigneu un validador"]
            ,["typeField"=>"OF", "field"=>"cc_dadesValidador#carrec", "accioNecessaria"=>"hi assigneu el càrrec del validador"]
        ];
        foreach ($campsAComprovar as $item) {
            if ($item["typeField"]=="SF" && (!isset($data[$item["field"]]) || $data[$item["field"]]["value"]==$data[$item["field"]]["default"])){
                $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => $item["field"],
                        'message' => sprintf($message
                                            ,$item["field"]
                                            ,$item["accioNecessaria"])
                    ];                
            }elseif($item["typeField"]=="TF" && (!isset($data[$item["field"]]) || empty ($data[$item["field"]]["value"]) || $data[$item["field"]]["value"]=="[]" || $data[$item["field"]]["value"]==$data[$item["field"]]["default"])){
                $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => $item["field"],
                        'message' => sprintf($message
                                            ,$item["field"]
                                            ,$item["accioNecessaria"])
                    ];                
            }else if($item["typeField"]=="OF"){
                $keys = explode("#", $item["field"]);
                $error=false;
                $dataf = $data;
                for($i=0; !$error && $i<count($keys); $i++){
                    if(!isset($dataf[$keys[$i]]) || $dataf[$keys[$i]]["value"]==$dataf[$keys[$i]]["default"]){
                        $error=true;
                    }else{
                        $dataf = $dataf[$keys[$i]]["value"];
                    }
                }
                if($error){
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => $item["fieldName"] ? $item["fieldName"] : $item["field"],
                        'message' => sprintf($message
                                            ,$item["field"]
                                            ,$item["accioNecessaria"])
                    ];                     
                }
            }
        }
        
        if (empty($result)) {
            $responseType = "NOERROR";
            $result[$responseType] = WikiIocLangManager::getLang("No s'han detectat errors a les dades del projecte");
        }
        return $result;
    }

    public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue=FALSE, $subset=FALSE) {
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::updateCalculatedFieldsOnRead($data, $subset);
        }
        
        $resultatsAprenentatge = $data["resultatsAprenentatgeObjectiusTerminals"];
        $data["resultatsAprenentatgeObjectiusTerminals"] = IocCommon::toArrayThroughArrayOrJson($resultatsAprenentatge);

//        if ($resultatsAprenentatge && !is_array($resultatsAprenentatge)){
//           $resultatsAprenentatge = json_decode($resultatsAprenentatge, TRUE);
//            $data["resultatsAprenentatgeObjectiusTerminals"] = $resultatsAprenentatge;
//        }
        return $data;
    }

    public function updateCalculatedFieldsOnSave($data, $originalDataKeyValue=FALSE, $subset=FALSE) {
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::updateCalculatedFieldsOnSave($data, $subset, $subset);
        }
        
        $data = parent::updateCalculatedFieldsOnSave($data, $originalDataKeyValue, $subset);

//        $resultatsAprenentatge = $data["resultatsAprenentatgeObjectiusTerminals"];
        $resultatsAprenentatge = IocCommon::toArrayThroughArrayOrJson($data["resultatsAprenentatgeObjectiusTerminals"]);

//        if ($resultatsAprenentatge && !is_array($resultatsAprenentatge)){
//            $resultatsAprenentatge = json_decode($resultatsAprenentatge, TRUE);
//        }

        if($data["tipusCicle"]=="LOE"){
            $resultatsAprenentatge = array(
                array("ra"=>"1","descripcio"=>"Identifica l'estructura, l'organització i les condicions de treball de l'empresa, centre o servei, relacionant-ho amb les activitats que realitza."),
                array("ra"=>"2","descripcio"=>"Desenvolupa actituds ètiques i laborals pròpies de l'activitat professional d'acord amb les característiques del lloc de treball i els procediments establerts pel centre de treball."),
                array("ra"=>"3","descripcio"=>"Realitza les activitats formatives de referència seguint protocols establerts pel centre de treball.")
            );
            $criterisAvaluacio = array(
                array("ra"=>"1","ca"=>"1.1","descripcio"=>"Identifica les característiques generals de l'empresa, centre o servei i l'organigrama i les funcions de cada àrea."),
                array("ra"=>"1","ca"=>"1.2","descripcio"=>"Identifica els procediments de treball en el desenvolupament de l'activitat."),
                array("ra"=>"1","ca"=>"1.3","descripcio"=>"Identifica les competències dels llocs de treball en el desenvolupament de l'activitat."),
                array("ra"=>"1","ca"=>"1.4","descripcio"=>"Identifica les característiques del mercat o entorn, tipus d'usuaris i proveïdors."),
                array("ra"=>"1","ca"=>"1.5","descripcio"=>"Identifica les activitats de responsabilitat social de l'empresa, centre o servei cap a l'entorn."),
                array("ra"=>"1","ca"=>"1.6","descripcio"=>"Identifica el flux de serveis o els canals de comercialització més freqüents en aquesta activitat."),
                array("ra"=>"1","ca"=>"1.7","descripcio"=>"Relaciona avantatges i inconvenients de l'estructura de l'empresa, centre o servei, enfront a altres tipus d'organitzacions relacionades."),
                array("ra"=>"1","ca"=>"1.8","descripcio"=>"Identifica el conveni col·lectiu o el sistema de relacions laborals al que està acollida l'empresa, centre o servei."),
                array("ra"=>"1","ca"=>"1.9","descripcio"=>"Identifica els incentius laborals, les activitats d'integració o de formació i les mesures de conciliació en relació amb l'activitat."),
                array("ra"=>"1","ca"=>"1.10","descripcio"=>"Valora les condicions de treball en el clima laboral de l'empresa, centre o servei."),
                array("ra"=>"1","ca"=>"1.11","descripcio"=>"Valora la importància de treballar en grup per aconseguir amb eficàcia els objectius establerts en l'activitat i resoldre els problemes que es plantegen."),
                array("ra"=>"2","ca"=>"2.1","descripcio"=>"Compleix l'horari establert."),
                array("ra"=>"2","ca"=>"2.2","descripcio"=>"Mostra una presentació personal adequada."),
                array("ra"=>"2","ca"=>"2.3","descripcio"=>"És responsable en l'execució de les tasques assignades."),
                array("ra"=>"2","ca"=>"2.4","descripcio"=>"S'adapta als canvis de les tasques assignades."),
                array("ra"=>"2","ca"=>"2.5","descripcio"=>"Manifesta iniciativa en la resolució de problemes."),
                array("ra"=>"2","ca"=>"2.6","descripcio"=>"Valora la importància de la seva activitat professional."),
                array("ra"=>"2","ca"=>"2.7","descripcio"=>"Manté organitzada la seva àrea de treball."),
                array("ra"=>"2","ca"=>"2.8","descripcio"=>"Té cura dels materials, equips o eines que utilitza en la seva activitat."),
                array("ra"=>"2","ca"=>"2.9","descripcio"=>"Manté una actitud clara de respecte al medi ambient."),
                array("ra"=>"2","ca"=>"2.10","descripcio"=>"Estableix una comunicació i relació eficaç amb el personal de l'empresa."),
                array("ra"=>"2","ca"=>"2.11","descripcio"=>"Es coordina amb els membres del seu equip de treball."),
                array("ra"=>"3","ca"=>"3.1","descripcio"=>"Executa les tasques segons els procediments establerts."),
                array("ra"=>"3","ca"=>"3.2","descripcio"=>"Identifica les característiques particulars dels mitjans de producció, equips i eines."),
                array("ra"=>"3","ca"=>"3.3","descripcio"=>"Aplica les normes de prevenció de riscos laborals en l'activitat professional."),
                array("ra"=>"3","ca"=>"3.4","descripcio"=>"Utilitza els equips de protecció individual segons els riscos de l'activitat professional i les normes pel centre de treball."),array("ra"=>"3","ca"=>"3.5","descripcio"=>"Aplica les normes internes i externes vinculades a l'activitat."),array("ra"=>"3","ca"=>"3.6","descripcio"=>"Obté la informació i els mitjans necessaris per realitzar l'activitat assignada."),array("ra"=>"3","ca"=>"3.7","descripcio"=>"Interpreta i expressa la informació amb la terminologia o simbologia i els mitjans propis de l'activitat."),array("ra"=>"3","ca"=>"3.8","descripcio"=>"Detecta anomalies o desviacions en l'àmbit de l'activitat assignada, n'identifica les causes i proposa possibles solucions.")
            );
            $data["criterisAvaluacio"] = $criterisAvaluacio;
        }
        $data["resultatsAprenentatgeObjectiusTerminals"] = $resultatsAprenentatge;
        
        // Dades de la gestió de la darrera modificació
        $this->dadesActualsGestio($data);

        // Històric del control de canvis
        $this->modifyLastHistoricGestioDocument($data);

        return $data;
    }

    private function dadesActualsGestio(&$data) {
        if ($data['autor']) $data['cc_dadesAutor']['nomGestor'] = $this->getUserName($data['autor']);
        if ($data['revisor']) $data['cc_dadesRevisor']['nomGestor'] = $this->getUserName($data['revisor']);
        if ($data['validador']) $data['cc_dadesValidador']['nomGestor'] = $this->getUserName($data['validador']);
    }

    public function clearQualityRolesData(&$data){
        $data['cc_dadesAutor'] = IocCommon::toArrayThroughArrayOrJson($data['cc_dadesAutor']);
        $data['cc_dadesRevisor'] = IocCommon::toArrayThroughArrayOrJson($data['cc_dadesRevisor']);
        $data['cc_dadesValidador'] = IocCommon::toArrayThroughArrayOrJson($data['cc_dadesValidador']);
        $data['cc_dadesValidador'] = IocCommon::toArrayThroughArrayOrJson($data['cc_dadesValidador']);

//        if(!is_array($data['cc_dadesAutor'])){
//            $data['cc_dadesAutor'] = json_decode($data['cc_dadesAutor'], TRUE);
//        }
//        if(!is_array($data['cc_dadesRevisor'])){
//            $data['cc_dadesRevisor'] = json_decode($data['cc_dadesRevisor'], TRUE);
//        }
//        if(!is_array($data['cc_dadesValidador'])){
//            $data['cc_dadesValidador'] = json_decode($data['cc_dadesValidador'], TRUE);
//        }
        $data['cc_dadesAutor']['dataDeLaGestio'] = "";
        $data['cc_dadesAutor']['signatura'] = "pendent";
        $data['cc_dadesRevisor']['dataDeLaGestio'] = "";
        $data['cc_dadesRevisor']['signatura'] = "pendent";
        $data['cc_dadesValidador']['dataDeLaGestio'] = "";
        $data['cc_dadesValidador']['signatura'] = "pendent";        
        $data['cc_raonsModificacio'] = "";        
    }

    public function updateSignature(&$data, $role, $date=FALSE) {        
        $keyConverter = ["cc_dadesAutor" =>"autor", "cc_dadesRevisor" => "revisor", "cc_dadesValidador" => "validador"];
        $data[$role]['nomGestor'] = $this->getUserName($data[$keyConverter[$role]]);;
        $data[$role]['dataDeLaGestio'] = $date?$date:date("Y-m-d");
        $data[$role]['signatura'] = "signat";
    }
    
    public function modifyLastHistoricGestioDocument(&$data, $date=false) {
        // ALERTA[Xavi] No he fet el canvi per IocCommon::toArrayThroughArrayOrJson perquè no tinc clar
        // si aquest càs funcionaria o no (cometes dobles i dins un array buid)
        if ($data['cc_historic'] === '"[]"') {
            $data['cc_historic'] = array();
        }elseif (!is_array($data['cc_historic'])){
            $data['cc_historic'] = json_decode($data['cc_historic'], true);
        }
        if (is_array($data['cc_historic'])) {
            $hist['data'] = $date ? $date : date("Y-m-d");
            $hist['autor'] = $this->getUserName($data['autor']);
            $hist['modificacions'] = $data['cc_raonsModificacio'] ? $data['cc_raonsModificacio'] : "";
            $c = (count($data['cc_historic']) < 1) ? 0 : count($data['cc_historic'])-1;
            $data['cc_historic'][$c] = $hist;
        }
    }
    
    public function addHistoricGestioDocument(&$data) {

        $data['cc_historic'] = IocCommon::toArrayThroughArrayOrJson($data['cc_historic']);
        $hist['data'] = date("Y-m-d");
        $hist['autor'] = $this->getUserName($data['autor']);
        $hist['modificacions'] = $data['cc_raonsModificacio'] ? $data['cc_raonsModificacio'] : "";
        $data['cc_historic'][] = $hist;
    }

    private function getUserName($users) {
        global $auth;
        $retUser = "";
        $u = explode(",", $users);
        foreach ($u as $user) {
            $retUser .= $auth->getUserData($user)['name'] . ", ";
        }
        return trim($retUser, ", ");
    }

    /**
     * @override Guarda los datos en el momento de la cración
     * @param array $toSet (s'ha generat a l'Action corresponent)
     */
    public function createData($toSet) {
        parent::createData($toSet);

        //Creació de l'arxiu de metadades corresponent al workflow
        $subSet = "management";
        $metaDataQuery = $this->getPersistenceEngine()->createProjectMetaDataQuery($this->id, $subSet, $this->projectType);
        $metaDataManagement = ['workflow'=>['currentState'=>"creating"]];
        $metaDataQuery->setMeta(json_encode($metaDataManagement), $subSet, "creació", NULL);
    }

}
