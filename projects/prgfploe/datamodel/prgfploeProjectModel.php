<?php
/**
 * ptfploeProjectModel
 * @culpable Josep Cañellas
 */
if (!defined("DOKU_INC")) die();

class prgfploeProjectModel extends ProgramacioProjectModel {
    
    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;
        $this->externalCallMethods["createTableRAPonderation"]="__createTableRAPonderation";
    }
    
    public function directGenerateProject() {
        //4. Establece la marca de 'proyecto generado'
        return $this->projectMetaDataQuery->setProjectGenerated();
    }

    protected function __createTableRAPonderation($data){
        $tableRaPonderation =[];
        $resApren = $data["resultatsAprenentatge"];
        if(!is_array($resApren)){
            $resApren = json_decode($resApren, TRUE);
        }
        $insAvTable = $data["taulaInstrumentsAvaluacio"];
        if(!is_array($insAvTable)){
            $insAvTable = json_decode($insAvTable, TRUE);
        }
        foreach ($resApren as $itemResAp) {
            foreach ($insAvTable as $itemInsAv) {
                if($itemInsAv["unitat formativa"]==$itemResAp["uf"]){
                    $tableRaPonderation[]=["uf"=>$itemResAp["uf"], "ra" => $itemResAp["ra"], "instAvaluacio" => $itemInsAv["id"], "ponderacio" => $itemInsAv["ponderacio"]];
                }            
            }
        }
        return [ResponseHandlerKeys::TYPE => "array", ResponseHandlerKeys::VALUE => $tableRaPonderation];
    }
 
    public function validateFields($data=NULL, $subset=FALSE){
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::validateFields($data, $subset);
        }

        //EL responsable no pot ser buit
        if(isset($data["responsable"]) && empty(trim($data["responsable"]))){
            throw new InvalidDataProjectException(
                    $this->id,
                    "El camp responsable no pot quedar buit"
            );            
        }
    }

    public function getErrorFields($data=NULL, $subset=FALSE){
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::getErrorFields($data, $subset);
        }

        $result     = array();
        $iaTable    = $data["taulaInstrumentsAvaluacio"]['value']; if (!is_array($iaTable)) $iaTable = json_decode($iaTable, TRUE);
        $aaTable    = $data["activitatsAprenentatge"]['value'];    if (!is_array($aaTable)) $aaTable = json_decode($aaTable, TRUE);
        $nfTable    = $data["taulaDadesNuclisFormatius"]['value']; if (!is_array($nfTable)) $nfTable = json_decode($nfTable, TRUE);
        $ufTable    = $data["taulaDadesUF"]['value'];              if (!is_array($ufTable)) $ufTable = json_decode($ufTable, TRUE);
        $raTable    = $data["resultatsAprenentatge"]['value'];     if (!is_array($raTable)) $raTable = json_decode($raTable, TRUE);
        $ponRaTable = $data["taulaPonderacioRA"]['value'];         if (!is_array($ponRaTable)) $ponRaTable = json_decode($ponRaTable, TRUE);
        $conTable   = $data["continguts"]['value'];                if (!is_array($conTable)) $conTable = json_decode($conTable, TRUE);
        $caTable    = $data["criterisAvaluacio"]['value'];         if (!is_array($caTable)) $caTable = json_decode($caTable, TRUE);
        
        //Camps obligatoris
        $responseType = "SINGLE_MESSAGE";
        $message = WikiIocLangManager::getLang("El camp %s és obligatori. Cal que %s.");
        $campsAComprovar=[
             ["typeField"=>"SF","field"=>"departament", "accioNecessaria"=>"hi poseu el nom del departament"] 
            ,["typeField"=>"SF","field"=>"cicle", "accioNecessaria"=>"hi poseu el nom del cicle"]
            ,["typeField"=>"SF","field"=>"modulId", "accioNecessaria"=>"hi poseu el codi del mòdul"]
            ,["typeField"=>"SF","field"=>"modul", "accioNecessaria"=>"hi poseu el nom del mòdul"]
//            ,["typeField"=>"SF","field"=>"estrategiesMetodologiques", "accioNecessaria"=>"hi poseu les estratègies moetodològiques del mòdul"]
            ,["typeField"=>"TF","field"=>"taulaDadesUF", "accioNecessaria"=>"hi afegiu les unitats formatives del mòdul"]
            ,["typeField"=>"TF","field"=>"taulaInstrumentsAvaluacio", "accioNecessaria"=>"hi afegiu els instruments d'avaluació de cada UF"]
            ,["typeField"=>"TF","field"=>"resultatsAprenentatge", "accioNecessaria"=>"hi afegiu els resultats d'avaluació de cada UF"]
            ,["typeField"=>"TF","field"=>"taulaPonderacioRA", "accioNecessaria"=>"hi afegiu la ponderació que cada instrument d'avaluació representa sobre cada RA."]
            ,["typeField"=>"TF","field"=>"criterisAvaluacio", "accioNecessaria"=>"hi afegiu els criteris d'avaluaciḉo associats a cada RA"]
            ,["typeField"=>"TF","field"=>"continguts", "accioNecessaria"=>"hi afegiu els continguts associats a cada UF"]
            ,["typeField"=>"TF","field"=>"taulaDadesNuclisFormatius", "accioNecessaria"=>"hi afegiu els nuclis formatius associats a cada UF"]
            ,["typeField"=>"TF","field"=>"activitatsAprenentatge", "accioNecessaria"=>"hi afegiu les activitats d'aprenentatge associades a cada nucli formatiu"]
            ,["typeField"=>"SF","field"=>"cc_raonsModificacio", "accioNecessaria"=>"hi assigneu una raó per la modificació actual de la programació"]
            // ALERTA! Aquests camps no es corresponen amb els IDs que s'asignen als camps
            //,["typeField"=>"OF","field"=>"cc_dadesAutor#nomGestor", "accioNecessaria"=>"hi assigneu un autor", "fieldName" => "autor"]
            ,["typeField"=>"SF","field"=>"autor", "accioNecessaria"=>"hi assigneu un autor"]
            ,["typeField"=>"OF","field"=>"cc_dadesAutor#carrec", "accioNecessaria"=>"hi assigneu el càrrec de l'autor"]
            ,["typeField"=>"SF","field"=>"revisor", "accioNecessaria"=>"hi assigneu un revisor"]
            ,["typeField"=>"OF","field"=>"cc_dadesRevisor#carrec", "accioNecessaria"=>"hi assigneu el càrrec del revisor"]
            ,["typeField"=>"SF","field"=>"validador", "accioNecessaria"=>"hi assigneu un validador"]
            ,["typeField"=>"OF","field"=>"cc_dadesValidador#carrec", "accioNecessaria"=>"hi assigneu el càrrec del validador"]
        ];
        foreach ($campsAComprovar as $item) {
            if($item["typeField"]=="SF" && (!isset($data[$item["field"]]) || $data[$item["field"]]["value"]==$data[$item["field"]]["default"])){
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
        
        $verificadorEAC = [];
        if (!empty($iaTable)){
            foreach ($iaTable as $item) {
                if (!isset($verificadorEAC[$item["unitat formativa"]])) {
                    $verificadorEAC[$item["unitat formativa"]] = [];
                }
                if (!isset($verificadorEAC[$item["unitat formativa"]][$item["id"]])) {
                    $verificadorEAC[$item["unitat formativa"]][$item["id"]] = false;
                }
            }
        }
        
        $verificadorCA = [];
        $verificadorLlistaCodiCA = [];
        if (!empty($caTable)) {
            foreach ($caTable as $item) {
                if (!isset($verificadorCA[$item["uf"]])) {
                    $verificadorCA[$item["uf"]] = [];
                }
                if (!isset($verificadorCA[$item["uf"]][$item["ra"]])) {
                    $verificadorCA[$item["uf"]][$item["ra"]] = false;
                }
                if (!isset($verificadorCA[$item["uf"]][$item["ra"]][$item["ca"]])) {
                    $verificadorCA[$item["uf"]][$item["ra"]][$item["ca"]] = false;
                }
                $verificadorLlistaCodiCA[] = "${item["uf"]}-${item["ca"]}";
            }
        }
        
        $verificadorRA = [];
        if(!empty($raTable)){
            foreach ($raTable as $item) {
                if(!isset($verificadorRA[$item["uf"]])){
                    $verificadorRA[$item["uf"]] =[];
                }
                if(!isset($verificadorRA[$item["uf"]][$item["ra"]])){
                    $verificadorRA[$item["uf"]][$item["ra"]] = false;
                }
            }
        }

        $verificadorCAofRA = [];
        if (!empty($aaTable)){
            foreach ($aaTable as $item) {
                $itemsRA = preg_split("/[\s,.]+/", $item['ra']);
                foreach ($itemsRA as $ra) {
                    $isCAofRA = preg_match("/{$ra}\.[0-9]/", $item['ca']);
                    if (!isset($verificadorCAofRA[$item['code']]["UF {$item['unitat formativa']}"]["RA $ra"]))
                        $verificadorCAofRA[$item['code']]["UF {$item['unitat formativa']}"]["RA $ra"] = $isCAofRA;
                    else {
                        $verificadorCAofRA[$item['code']]["UF {$item['unitat formativa']}"]["RA $ra"] &= $isCAofRA;
                    }
                }
            }
        }

        $verificadorCont = [];
        $verificadorLlistaContinguts = [];
        if (!empty($conTable)) {
            foreach ($conTable as $item) {
                if (!isset($verificadorCont[$item["uf"]])) {
                    $verificadorCont[$item["uf"]] = [];
                }
                $cKeys = explode(".", $item["cont"]);
                if (count($cKeys)==1) {
                    $cKeys[] = 0;
                }
                if (!isset($verificadorCont[$item["uf"]][$cKeys[0]])) {
                    $verificadorCont[$item["uf"]][$cKeys[0]] = [];
                }
                if (!isset($verificadorCont[$item["uf"]][$cKeys[0]][$cKeys[1]])) {
                    $verificadorCont[$item["uf"]][$cKeys[0]][$cKeys[1]] = false;
                }
                $verificadorLlistaContinguts[] = "${item["uf"]}-${item["cont"]}";
            }
        }
        
        //Comprovat RAs, CAs i continguts + EACs  [TODO]
        $totalNFs = array();
        if (!empty($aaTable)) {
            foreach ($aaTable as $item){
                if (!isset($totalNFs[$item["unitat formativa"]])) {
                    $totalNFs[$item["unitat formativa"]] = array();
                }
                if (!isset($totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]])) {
                    $totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]] = 0;
                }
                $totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]] += $item["hores"];
                $raKeys = explode(",", $item["ra"]);
                foreach ($raKeys as $raKey) {
                    if(!isset($verificadorRA[$item["unitat formativa"]][trim($raKey)])){
                        //Error No existeix el RA
                        $result["ERROR"][] = [
                            'responseType' => $responseType,
                            'field' => 'activitatsAprenentatge',
                            'message' => sprintf("A la taula de les activitats d'aprenentatge (activitatsAprenentatge), hi ha el el RA %d corresponent a la UF %d i al NF %d, però aquest RA no es troba definit a la taula de resultats d'aprenentatge (resutatsAprenentatge)."
                                                ,$item["ra"]
                                                ,$item["unitat formativa"]
                                                ,$item["nucli formatiu"])
                        ];                    
                    }else{
                        $verificadorRA[$item["unitat formativa"]][trim($raKey)]=true;
                    }
                }
                $caKeys = preg_split("/[, \n] ?/", $item["ca"]);
                foreach ($caKeys as $caKey) {     
                    $selectedRa = -1;
                    $raKeys = explode(",", $item["ra"]);
                    $pos = 0;
                    foreach ($raKeys as $raKey){
                        if(isset($verificadorCA[$item["unitat formativa"]][trim($raKey)][trim($caKey)])){
                            $selectedRa = $pos;
                        }
                        $pos++;
                    }                    
                    if($selectedRa==-1){
                        //Error No existeix el CA
                        $result["ERROR"][] = [
                            'responseType' => $responseType,
                            'field' => 'activitatsAprenentatge',
                            'message' => sprintf("A la taula de les activitats d'aprenentatge (activitatsAprenentatge), hi ha el CA %s corresponent al RA %d de la UF %d i el NF %d, però aquest CA no es troba definit a la taula de criteris d'avaluació (criterisAvaluacio)."
                                                ,$caKey
                                                ,$item["ra"]
                                                ,$item["unitat formativa"]
                                                ,$item["nucli formatiu"])
                        ];                    
                    }else{
                        $verificadorCA[$item["unitat formativa"]][$raKeys[$selectedRa]][trim($caKey)]=true;
                    }
                }
                $contKeys = explode(",", $item["continguts"]);
                foreach ($contKeys as $contKey) {      
                    $akeys = explode(".", trim($contKey));
                    if(count($akeys)==1){
                        $akeys[] = 0;
                    }
                    if(!isset($verificadorCont[$item["unitat formativa"]][$akeys[0]][$akeys[1]])){
                        //Error No existeix el contingut
                        $result["ERROR"][] = [
                            'responseType' => $responseType,
                            'field' => 'activitatsAprenentatge',
                            'message' => sprintf("A la taula de les activitats d'aprenentatge (activitatsAprenentatge), hi ha el contingut %s corresponent a la UF %d i el NF %d, però aquest contingut no es troba definit a la taula de continguts (continguts)."
                                                ,$contKey
                                                ,$item["unitat formativa"]
                                                ,$item["nucli formatiu"])
                        ];                    
                    }else{
                        $verificadorCont[$item["unitat formativa"]][$akeys[0]][$akeys[1]]=true;
                    }
                }
                $iaKeys = explode(",", $item["instruments d'avaluació"]);
                foreach ($iaKeys as $iaKey) {      
                    if(!isset($verificadorEAC[$item["unitat formativa"]][trim($iaKey)])){
                        //Error No existeix l'instrument d'avaluació
                        $result["ERROR"][] = [
                            'responseType' => $responseType,
                            'field' => 'activitatsAprenentatge',
                            'message' => sprintf("A la taula de les activitats d'aprenentatge (activitatsAprenentatge), hi ha l'instrument d'avaluació %s corresponent a la UF %d i el NF %d, però aquest instrument no es troba definit a la taula d'instruments d'avaluació (taulaInstrumentsAvaluacio)."
                                                ,$iaKey
                                                ,$item["unitat formativa"]
                                                ,$item["nucli formatiu"])
                        ];                    
                    }else{
                        $verificadorEAC[$item["unitat formativa"]][trim($iaKey)]=true;
                    }
                }
            }
        }
        
        foreach ($verificadorCA as $uf => $v) {
            foreach ($verificadorCA[$uf] as $ra => $v) {
                foreach ($verificadorCA[$uf][$ra] as $ca => $assigned) {
                    if(!$assigned){
                        //Error CA NO assignat
                        $result["ERROR"][] = [
                            'responseType' => $responseType,
                            'field' => 'activitatsAprenentatge',
                            'message' => sprintf("el CA %s de la UF %d i el RA %d no es troba assignat a cap registre de la taula d'activitats d'aprenentatge (activitatsAprenentatge)."
                                                ,$ca
                                                ,$uf
                                                ,$ra)
                        ];                    
                    }
                }
            }
        }

        foreach ($verificadorEAC as $uf => $v) {
            foreach ($verificadorEAC[$uf] as $eac => $assigned) {
                if(!$assigned){
                    //Error CA NO assignat
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'activitatsAprenentatge',
                        'message' => sprintf("L'instrument d'avaluació %s de la UF %d no es troba assignat a cap registre de la taula d'activitats d'aprenentatge (activitatsAprenentatge)."
                                            ,$eac
                                            ,$uf)
                    ];                    
                }
            }
        }

        foreach ($verificadorRA as $uf => $v) {
            foreach ($verificadorRA[$uf] as $ra => $assigned) {
                if(!$assigned){
                    //Error CA NO assignat
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'activitatsAprenentatge',
                        'message' => sprintf("El RA %d de la UF %d no es troba assignat a cap registre de la taula d'activitats d'aprenentatge (activitatsAprenentatge)."
                                            ,$ra
                                            ,$uf)
                    ];                    
                }
            }
        }

        foreach ($verificadorCAofRA as $code => $ufs) {
            foreach ($ufs as $uf => $ras) {
                foreach ($ras as $ra => $assigned) {
                    if (!$assigned){
                        //Error RA sense CA
                        $result["ERROR"][] = [
                            'responseType' => $responseType,
                            'field' => 'activitatsAprenentatge',
                            'message' => sprintf("En la %s del codi %s no hi ha cap CA associat a la %s.",
                                                 $uf,
                                                 $code,
                                                 $ra)
                        ];
                    }
                }
            }
        }

        foreach ($verificadorCont as $uf => $v) {
            foreach ($verificadorCont[$uf] as $conti => $v) {
                if(!$verificadorCont[$uf][$conti][0]){
                    foreach ($verificadorCont[$uf][$conti] as $contj => $assigned) {
                        if($contj>0 && !$assigned){
                            //Error CA NO assignat
                            $result["ERROR"][] = [
                                'responseType' => $responseType,
                                'field' => 'activitatsAprenentatge',
                                'message' => sprintf("El contingut %d.%d de la UF %d no es troba assignat a cap registre de la taula d'activitats d'aprenentatge (activitatsAprenentatge)."
                                                    ,$conti
                                                    ,$contj
                                                    ,$uf)
                            ];                    
                        }
                    }
                }
            }
        }
        
        //Comprova si hi ha duplicacions de codis CA dins d'una mateixa UF
        if (!empty($verificadorLlistaCodiCA)) {
            $unic = array_unique($verificadorLlistaCodiCA);
            $dif = array_diff_key($verificadorLlistaCodiCA, $unic);
            if (!empty($dif)) {
                $dif = implode(", ", $dif);
                $result["ERROR"][] = [
                    'responseType' => $responseType,
                    'field' => 'criterisAvaluacio',
                    'message' => sprintf("A la taula Criteris d'avaluacio (criterisAvaluacio), els codis CA següents estan duplicats 'uf'-'codi CA': %s."
                                        ,$dif)
                ];
            }
        }

        //Comprova si hi ha duplicacions de 'Codi contingut' dins d'una mateixa UF
        if (!empty($verificadorLlistaContinguts)) {
            $unic = array_unique($verificadorLlistaContinguts);
            $dif = array_diff_key($verificadorLlistaContinguts, $unic);
            if (!empty($dif)) {
                $dif = implode(", ", $dif);
                $result["ERROR"][] = [
                    'responseType' => $responseType,
                    'field' => 'continguts',
                    'message' => sprintf("A la taula Continguts de la UF (continguts), els codi contingut següents estan duplicats 'uf'-'codi contingut': %s."
                                        ,$dif)
                ];
            }
        }

        //Comprovació hores
        $totalUfs = array();
        
        if (!empty($nfTable)) {
            $pk = []; // comprovació de clau primaria única: 'unitat formativa' + 'nucli formatiu'
            $nf = []; // comprovació de bijecció entre 'nucli formatiu' i 'unitat al pla de treball'

            foreach ($nfTable as $item) {
                if ($item["hores"] != $totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]]) {
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'taulaDadesNuclisFormatius',
                        'message' => sprintf("A la taula dels nuclis formatius (taulaDadesNuclisFormatius), les hores del nucli formatiu %s de la UF %d no coincideixen amb la suma de les hores de les seves activitats d'aprenentatge (hores NF=%d, però suma hoes AA=%d)."
                                            ,$item["nucli formatiu"]
                                            ,$item["unitat formativa"]
                                            ,$item["hores"]
                                            ,$totalNFs[$item["unitat formativa"]][$item["nucli formatiu"]])
                    ];
                }
                if (!isset($totalUfs[$item["unitat formativa"]])) {
                    $totalUfs[$item["unitat formativa"]] = 0;
                    if($item["nucli formatiu"]!=1){
                        //comprovació de la numeració dels NF
                        $result["ERROR"][] = [
                            'responseType' => $responseType,
                            'field' => 'taulaDadesNuclisFormatius',
                            'message' => sprintf("A la taula dels nuclis formatius (taulaDadesNuclisFormatius), el primer nucli formatiu de la UF %d és %d, però hauria de ser 1. La numeració dels nuclis formatius es reinicien en cada UF."
                                                ,$item["unitat formativa"]
                                                ,$item["nucli formatiu"])
                        ];
                    }
                }
                $totalUfs[$item["unitat formativa"]] += $item["hores"];

                // comprovació de clau primaria única: 'unitat formativa' + 'nucli formatiu'
                $pk_tmp = "${item['unitat formativa']}${item['nucli formatiu']}";
                if (in_array($pk_tmp, $pk)) {
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'taulaDadesNuclisFormatius',
                        'message' => sprintf("A la taula dels nuclis formatius no es pot repetir la mateixa 'unitat formativa' '%d' i el mateix 'nucli formatiu' '%d'."
                                            ,$item["unitat formativa"]
                                            ,$item["nucli formatiu"])
                    ];
                }else {
                    $pk[] = $pk_tmp;
                }

                // la 'unitat al pla de treball' no es pot repetir => comprovació de bijecció entre 'nucli formatiu' i 'unitat al pla de treball'
                if (in_array($item['unitat al pla de treball'], $nf)) {
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'taulaDadesNuclisFormatius',
                        'message' => sprintf("A la taula dels nuclis formatius una 'unitat al pla de treball' ('%d') només pot aparèixer una vegada."
                                            ,$item['unitat al pla de treball'])
                    ];
                }else {
                    $nf[] = $item['unitat al pla de treball'];
                }
            }
        }

        if (!empty($ufTable)) {
            $horesIgualPonderacioUF = true;
            $ponderacioUF=0;
            foreach ($ufTable as $item) {
                if ($item["hores"] != $totalUfs[$item["unitat formativa"]]){
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'taulaDadesUF',
                        'message' => sprintf("A la taula d'Unitats Formatives(taulaDadesUF), les hores de la unitat formativa %s no coincideixen amb la suma de les hores dels seus nuclis foormatius (hores UF=%d, però suma hoes NF=%d)."
                                            ,$item["unitat formativa"]
                                            ,$item["hores"]
                                            ,$totalUfs[$item["unitat formativa"]])
                    ];
                }
                $horesIgualPonderacioUF = $horesIgualPonderacioUF && $item["hores"] == $item["ponderació"];
                $ponderacioUF += $item["ponderació"];
            }
        }
        
        if(!empty($raTable)){
            $horesIgualPonderacioRA = [];
            $ponderacioRA=[];
            foreach ($raTable as $item) {
                if(!isset($horesIgualPonderacioRA[$item["uf"]])){
                    $horesIgualPonderacioRA[$item["uf"]] =true;
                    $ponderacioRA[$item["uf"]] =0;
                }
                $horesIgualPonderacioRA[$item["uf"]] = $horesIgualPonderacioRA[$item["uf"]] && $item["hores"] == $item["ponderacio"];
                $ponderacioRA[$item["uf"]] += $item["ponderacio"];
                if (!empty($item['uf'])) {
                    $verificacioTaulaPonderacioRA[] = "{$item['uf']}.{$item['ra']}"; //se usa para validar 'resultatsAprenentatge'
                }
            }
        }

        //Comporvació ponderacions
        if (!empty($iaTable)) {
            $sum = [];
            foreach ($iaTable as $item) {
                if (!isset($sum[$item["unitat formativa"]])) {
                    $sum[$item["unitat formativa"]] = 0;
                }
                $sum[$item["unitat formativa"]] += $item["ponderacio"];
            }
            
            foreach ($iaTable as $item) {
                if ($item['tipus'] == "PAF" && $item["ponderacio"]/$sum[$item["unitat formativa"]] > 0.6) {
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'taulaInstrumentsAvaluacio',
                        'message' => sprintf("A la taula dels intsruments d'avaluació (taulaInstrumentsAvaluacio), la ponderació de la PAF de la unitat formativa %d pren el valor de %d sobre %d i per tant, supera el llindar del 60%s"
                                            ,$item["unitat formativa"]
                                            ,$item["ponderacio"]
                                            ,$sum[$item["unitat formativa"]]
                                            ,"%")
                    ];
                }
            }
            
            foreach ($sum as $key => $value) {
                if(abs($value-100.0)>2.0E-14){
                    $result["WARNING"][] = [
                        'responseType' => $responseType,
                        'field' => 'taulaInstrumentsAvaluacio',
                        'message' => sprintf("A la taula dels intsruments d'avaluació (taulaInstrumentsAvaluacio), la suma de les ponderacions de la unitat formativa %d no és 100. Reviseu si es tracta d'un error."
                                            ,$key)
                    ];
                }
            }
        }

        if(!$horesIgualPonderacioUF && abs($ponderacioUF-100.0)>2.0E-14){
            $result["WARNING"][] = [
                'responseType' => $responseType,
                'field' => 'taulaDadesUF',
                'message' => sprintf("A la taula d'Unitats Formatives(taulaDadesUF), la suma de les ponderacions no és 100 i els seus valors no coincidèixen amb les hores. Reviseu si es tracta d'un error.")
            ];
        }

        if(!empty($horesIgualPonderacioRA)){
            foreach ($horesIgualPonderacioRA as $key => $value) {
                if(!$horesIgualPonderacioRA[$key] && abs($ponderacioRA[$key]-100.0)>2.0E-14){
                    $result["WARNING"][] = [
                        'responseType' => $responseType,
                        'field' => 'resultatsAprenentatge',
                        'message' => sprintf("A la taula dels resultats d'aprenentatge (resultatsAprenentatge), la suma de les ponderacions dels RA de la unitat formativa %d no és 100 i els seus valors no coincidèixen amb les hores. Reviseu si es tracta d'un error."
                                            ,$key)
                    ];
                }
            }
            
        }
        
        if(!empty($ponRaTable)){
            $ponderacioRA=[];
            foreach ($ponRaTable as $item) {
                if(!isset($ponderacioRA[$item["uf"]])){
                    $ponderacioRA[$item["uf"]] =[];
                }
                if(!isset($ponderacioRA[$item["uf"]][$item["ra"]])){
                    $ponderacioRA[$item["uf"]][$item["ra"]] =0;
                }
                $ponderacioRA[$item["uf"]][$item["ra"]] += $item["ponderacio"];
                if(!isset($verificadorEAC[$item["uf"]][$item["instAvaluacio"]])){
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'taulaPonderacioRA',
                        'message' => sprintf("A la taula de registres de les ponderacions dels RA (taulaPonderacioRA), hi ha l'instrument d'avaluació %s corresponent a la UF %d, però aquest instrument no es troba definit a la taula d'instruments d'avaluació (taulaInstrumentsAvaluacio)."
                                            ,$item["instAvaluacio"]
                                            ,$item["uf"])
                    ];                    
                }
                if (!empty($verificacioTaulaPonderacioRA) && !in_array("{$item['uf']}.{$item['ra']}", $verificacioTaulaPonderacioRA)) {
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'taulaPonderacioRA',
                        'message' => sprintf("A la taula de registres de les ponderacions dels RA (taulaPonderacioRA), la RA %d no existeix a la UF %d definida a Resultats d'aprenentatge (resultatsAprenentatge)."
                                            ,$item["ra"]
                                            ,$item["uf"])
                    ];
                }
            }
            if(!empty($ponderacioRA)){
                foreach ($ponderacioRA as $keyUf => $ponderacioRAUF) {
                    foreach ($ponderacioRAUF as $keyRa => $ponderacio) {
                        if(abs($ponderacio-100.0)>2.0E-14){
                            $result["WARNING"][] = [
                                'responseType' => $responseType,
                                'field' => 'taulaPonderacioRA',
                                'message' => sprintf("A la taula de registres de la ponderació dels RA (taulaPonderacioRA), la suma de les ponderacions dels instrumens d'aprenentatge del RA %d corresponenet a la UF %d no és 100. Reviseu si es tracta d'un error."
                                                    ,$keyRa, $keyUf)
                            ];
                        }
                    }
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
        
        $ufTable = $data["taulaDadesUF"];
        if ($ufTable && !is_array($ufTable)){
            $ufTable = json_decode($ufTable, TRUE);
        }
        $resultatsAprenentatge = $data["resultatsAprenentatge"];
        if ($resultatsAprenentatge && !is_array($resultatsAprenentatge)){
           $resultatsAprenentatge = json_decode($resultatsAprenentatge, TRUE);
        }

        if ($ufTable) {
            foreach ($ufTable as $key => $value) {
                if ($ufTable[$key]["ponderació"] == "0"){
                    $ufTable[$key]["ponderació"] = $ufTable[$key]["hores"];
                }
            }
        }
        if ($resultatsAprenentatge) {
            foreach ($resultatsAprenentatge as $key => $value) {
                if (!isset($resultatsAprenentatge[$key]["ponderacio"]) || $resultatsAprenentatge[$key]["ponderacio"] == 0){
                     $resultatsAprenentatge[$key]["ponderacio"] = $resultatsAprenentatge[$key]["hores"];
                }
            }
        }

        $data["taulaDadesUF"] = $ufTable;
        $data["resultatsAprenentatge"] = $resultatsAprenentatge;
        return $data;
    }

    public function updateCalculatedFieldsOnSave($data, $originalDataKeyValue=false, $subset=false) {
        $data = parent::updateCalculatedFieldsOnSave($data, $originalDataKeyValue);

        $ufTable = $data["taulaDadesUF"];
        if ($ufTable && !is_array($ufTable)){
            $ufTable = json_decode($ufTable, TRUE);
        }
        $activitatsAprenentatge = $data["activitatsAprenentatge"];
        if ($activitatsAprenentatge && !is_array($activitatsAprenentatge)){
            $activitatsAprenentatge = json_decode($activitatsAprenentatge, TRUE);
        }
        $resultatsAprenentatge = $data["resultatsAprenentatge"];
        if ($resultatsAprenentatge && !is_array($resultatsAprenentatge)){
            $resultatsAprenentatge = json_decode($resultatsAprenentatge, TRUE);            
        }
        $originalResultatsAprenentatge = $originalDataKeyValue["resultatsAprenentatge"];
        if ($originalResultatsAprenentatge && !is_array($originalResultatsAprenentatge)){
            $originalResultatsAprenentatge = json_decode($originalResultatsAprenentatge, TRUE);            
        }
        $insAvTable = $data["taulaInstrumentsAvaluacio"];
        if ($insAvTable && !is_array($insAvTable)){
            $insAvTable = json_decode($insAvTable, TRUE);
        }
        //Calcula les hores per bloc i si cal, la pnderació.
        $blocTable = array();
        $total = 0;
        if ($ufTable) {
            $blocTotal = 0;
            $i = 0;
            $size = count($ufTable);
            while($i < $size) {
                $ufTable[$i]["hores"] = $ufTable[$i]["horesMinimes"] + $ufTable[$i]["horesLLiureDisposicio"];
                $total += $ufTable[$i]["hores"];
                $blocTotal += $ufTable[$i]["hores"];
                $currentBloc = $ufTable[$i]["bloc"];
                if ($ufTable[$i]["ponderació"] == $ufTable[$i]["hores"]){
                    $ufTable[$i]["ponderació"] = 0;
                }
                $i++;
                if ($i==$size || $ufTable[$i]['bloc'] != $currentBloc){
                    $blocRow = array();
                    $blocRow["bloc"] = $currentBloc;
                    $blocRow["horesBloc"] = $blocTotal;
                    $blocTable[] = $blocRow;
                    $blocTotal = 0;
                 }
            }
        }

        //calcula les hores pe RA
        $horesRa = array();
        if ($activitatsAprenentatge) {
            foreach ($activitatsAprenentatge as $value) {
                if (!isset($horesRa[$value["unitat formativa"]])){
                    $horesRa[$value["unitat formativa"]] = array();
                }
                $ara = explode(",", $value["ra"]);
                foreach ($ara as $raValue) {
                    $raValue = trim($raValue);
                    if (!isset($horesRa[$value["unitat formativa"]][$raValue])){
                        $horesRa[$value["unitat formativa"]][$raValue] = 0;
                    }                    
                    $horesRa[$value["unitat formativa"]][$raValue] += $value["hores"]/ count($ara);
                }
            }
        }

        if ($resultatsAprenentatge) {
            foreach ($resultatsAprenentatge as $key => $value) {
                $resultatsAprenentatge[$key]["hores"] = $horesRa[$value["uf"]][$value["ra"]];
                if ($resultatsAprenentatge[$key]["ponderacio"] == $resultatsAprenentatge[$key]["hores"]
                        || $resultatsAprenentatge[$key]["ponderacio"] == $originalResultatsAprenentatge[$key]["ponderacio"]
                            && $originalResultatsAprenentatge[$key]["ponderacio"] == $originalResultatsAprenentatge[$key]["hores"]){
                    $resultatsAprenentatge[$key]["ponderacio"] = 0;
                }
            }
        }

        $notaMinimaAC = 11;
        $notaMinimaPAF = 11;
        $notaMinimaEAF = 11;
        $notaMinimaJT = 11;
        if ($insAvTable) {
            foreach ($insAvTable as $item) {
                if ($item["tipus"] == "AC"){
                    if ($notaMinimaAC > $item["notaMinima"]){
                        $notaMinimaAC = $item["notaMinima"];
                    }
                }
                if ($item["tipus"] == "EAF"){
                    if($notaMinimaEAF > $item["notaMinima"]){
                        $notaMinimaEAF = $item["notaMinima"];
                    }
                }
                if ($item["tipus"] == "JT"){
                    if ($notaMinimaJT > $item["notaMinima"]){
                        $notaMinimaJT = $item["notaMinima"];
                    }
                }
                if ($item["tipus"] == "PAF"){
                    if($notaMinimaPAF > $item["notaMinima"]){
                        $notaMinimaPAF = $item["notaMinima"];
                    }
                }
            }
        }
        if ($notaMinimaAC == 11){
            $notaMinimaAC = 0;
        }
        if ($notaMinimaPAF == 11){
            $notaMinimaPAF = 0;
        }
        if ($notaMinimaEAF == 11){
            $notaMinimaEAF = 0;
        }
        if ($notaMinimaJT == 11){
            $notaMinimaJT = 0;
        }

        $data["resultatsAprenentatge"] = $resultatsAprenentatge;
        $data["taulaDadesUF"] = $ufTable;
        $data["taulaDadesBlocs"] = $blocTable;
        $data["durada"] = $total;
        $data["notaMinimaAC"] = $notaMinimaAC;
        $data["notaMinimaEAF"] = $notaMinimaEAF;
        $data["notaMinimaJT"] = $notaMinimaJT;
        $data["notaMinimaPAF"] = $notaMinimaPAF;

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
        if(!is_array($data['cc_dadesAutor'])){
            $data['cc_dadesAutor'] = json_decode($data['cc_dadesAutor'], TRUE);
        }
        if(!is_array($data['cc_dadesRevisor'])){
            $data['cc_dadesRevisor'] = json_decode($data['cc_dadesRevisor'], TRUE);
        }
        if(!is_array($data['cc_dadesValidador'])){
            $data['cc_dadesValidador'] = json_decode($data['cc_dadesValidador'], TRUE);
        }
        $data['cc_dadesAutor']['dataDeLaGestio'] = "";
        $data['cc_dadesAutor']['signatura'] = "pendent";
        $data['cc_dadesRevisor']['dataDeLaGestio'] = "";
        $data['cc_dadesRevisor']['signatura'] = "pendent";
        $data['cc_dadesValidador']['dataDeLaGestio'] = "";
        $data['cc_dadesValidador']['signatura'] = "pendent";        
        $data['cc_raonsModificacio'] = "";        
    }

    public function updateSignature(&$data, $role, $date=FALSE, $value="signat") {        
        $keyConverter = ["cc_dadesAutor" =>"autor", "cc_dadesRevisor" => "revisor", "cc_dadesValidador" => "validador"];
        $data[$role]['nomGestor'] = $this->getUserName($data[$keyConverter[$role]]);;
        $data[$role]['dataDeLaGestio'] = $date?$date:date("Y-m-d");
        $data[$role]['signatura'] = $value;
    }
    
    public function modifyLastHistoricGestioDocument(&$data, $date=false) {
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
        if (!is_array($data['cc_historic'])){
            $data['cc_historic'] = json_decode($data['cc_historic'], true);
        }
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
