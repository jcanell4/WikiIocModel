<?php
/**
 * guieseoiProjectModel
 * @culpable Rafael Claver
 * @aprenent Marjose
 */
if (!defined("DOKU_INC")) die();

class guieseoiProjectModel extends MoodleMultiContentFilesProjectModel {
    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;
    }

    /* ------------------------------
    * updateCalculatedFieldsOnRead
    * Calcula el valor de los campos calculables
    * @param JSON $data
    * $originalDataKeyValue és el que ve del client.
    * Quan usuari modifica,primer passa pel calculateonread o calculateonsave. el resultat és $data
    * $originalDataKeyValue és el que ve directament del client
    * sense haver passat per les modificacions del calculate (a confirmar que sigui així)
    * per ara això pot estar buit perquè no cal fer cap càlcul addicional
    * ------------------------------
    public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue=FALSE, $subset=FALSE){}
    */

    /* ------------------------------
     * getCalendariFieldFromMix
     * Per si més endavant agafa les dades del MIX
     * ------------------------------
    private function getCalendariFieldFromMix(&$values, $taulaCalendari){
        $dataFromMix = false;
        if(isset($values["moodleCourseId"]) && $values["moodleCourseId"]>0){
            $dataFromMix = $this->getMixDataLessons($values["moodleCourseId"]);
            if($dataFromMix){
                //de moment taula buida. Aquesta funció ara no fa res
            }
        }
        $values["dataFromMix"] =$dataFromMix;
        return $taulaCalendari;
    }
    */

    /* ------------------------------
    * validateFields
    * Validem que els camps entrats són consistents.
    * comprovem el defaultsubset:  ProjectKeys::VAL_DEFAULTSUBSET és
    * el subset per defecte, el main.
    * el responsable està al default subset (al main).
    * per això, el codi següent no entra mai al if, doncs no hi ha cap
    * més subset que el main. Però ho deixem per si més endavant hi ha.
    * $data: són les dades que m'arriben. En format key-value.
    * ------------------------------*/
    public function validateFields($data = NULL, $subset=FALSE){
        parent::validateFields($data, $subset);
        if ($subset==FALSE || $subset==ProjectKeys::VAL_DEFAULTSUBSET){
            parent::validateFields($data);
            $values = is_array($data)?$data:json_decode($data, true);//is_array em retorna el valor de les dades en format array
            //$dadesGuiesEOI = IocCommon::toArrayThroughArrayOrJson($data);//Faria el mateix

            //Valida que hi ha un responsable assignat sempre en el moment de guardar
            if (empty($values["responsable"])){
                //Pendent millora marjose:
                //Si el nom de l'autor també estar al camp responsable, aqui arriben com a camps
                //autor i responsable de values. Si no està el nom de l'autor, els camps
                //autor i responsable de vaules no existeixen.
                //revisar perquè.
                //
                //throw new InconsistentDataException("No hi ha un responsable assignat. Cap assignar-ho per poder crear aqesta guia.");
            }
        }
    }

    /* ------------------------------
     * getCalendarDates
     * Puja dades al calendari de moodle
     * Llista de les dates a pujar al calendari amb el format següent:
     *  - title
     *  - date (en format yyyy-mm-dd)
     *  - description
     * ------------------------------*/
    public function getCalendarDates() {
        $ret = array();
        $data = $this->getCurrentDataProject();
        //Per enviar dades al calendari.
        // Define the mapping of nivellcurs values to corresponding dataCert values
        $mapping = array(
            "A2.2 (2B)" => "dataCertA2",
            "B1.2 (3B)" => "dataCertB1",
            "B2.2b (5B)" => "dataCertB2",
            "C1" => "dataCertC1",
            "C2" => "dataCertC2"
        );

        if($data["isCert"]){
        //Si és certificat
        //Retornem dades de proves certificació
            $ret = array();
            $ret["title"] = sprintf("%s - Prova %s", $data["codi_modul"], $data['nivellcurs']);
           // Check if the value exists in the mapping array
            if (array_key_exists($data['nivellcurs'], $mapping)) {
                $ret['date'] = $$mapping[$data['nivellcurs']];
            }else{
                $ret['date'] = $data["dataProvaNoCert"];
            }
        }
        //Per a tots
        //Retornem dades de entradaDadesBlocs: bloc id, inici i final que és la data de lliurament
        if (is_string($data["entradaDadesBlocs"])){
            $dadesBlocs = json_decode($data["entradaDadesBlocs"], true);
        }else{
            $dadesBlocs = $data["entradaDadesBlocs"];
        }
        foreach ($dadesBlocs as $item) {
            $ret[] = [
                "title"=>sprintf("%s id%d - inici", $data["codi_modul"], $item["id"]),
                "date"=>$item["inici"]
            ];
            $ret[] = [
                "title"=>sprintf("%s id%d - fi", $data["codi_modul"], $item['id']),
                "date"=>$item["final"]
            ];

        }

        return $ret;
    }
    /* No farà res si no activem acció d'enviar calendari.
     * Però, mentrestant: enviem les dates que a nosaltres es semblen rellevants.
     * Si després es volgués, implementariem l'acció **marjose: mirar quina acció seria
     */

    /* ------------------------------
     * getCourseId
     * Necessari quan vulguem
     * actualitzar el calendari de
     * l'aula des d'aquí
     * ------------------------------*/
    public function getCourseId() {
        $data = $this->getCurrentDataProject();
        return $data["moodleCourseId"];
    }

}