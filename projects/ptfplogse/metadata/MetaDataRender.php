<?php
/**
 * Component: Project / MetaData
 */
namespace ptfplogse;
if (!defined("DOKU_INC")) die();

class MetaDataRender extends \MetaDataRenderAbstract {

    protected function processValues($values){
//        $taulaDadesUnitats = json_decode($values["taulaDadesUD"], true);
//        $taulaCalendari = json_decode($values["calendari"], true);
//        if($taulaCalendari!=NULL && $taulaDadesUnitats!=NULL){
//            $hores = array();
//            $hores[0] = 0;
//            for($i=0; $i<count($taulaCalendari);$i++){
//                $idU = intval($taulaCalendari[$i]["unitat didàctica"]);
//                if(!isset($hores[$idU])){
//                    $hores[$idU]=0;
//                }
//                $hores[$idU]+= $taulaCalendari[$i]["hores"];
//                $hores[0] += $taulaCalendari[$i]["hores"];
//            }
//
//            for($i=0; $i<count($taulaDadesUnitats);$i++){
//                $idU = intval($taulaDadesUnitats[$i]["unitat didàctica"]);
//                if(isset($hores[$idU])){
//                    $taulaDadesUnitats[$i]["hores"]=$hores[$idU];
//                }
//            }
//            $values["durada"] = json_encode($hores[0]);
//            $values["taulaDadesUD"] = json_encode($taulaDadesUnitats);
//        }
        return $values;
    }
}
