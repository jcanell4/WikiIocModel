<?php
/**
 * Component: Project / MetaData
 */
namespace ptfct;
require_once(DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRenderAbstract.php');

class MetaDataRender extends \MetaDataRenderAbstract {

    protected function processValues($values){
//        $taulaDadesUF = json_decode($values["taulaDadesUF"], true);
//        $taulaDadesUnitats = json_decode($values["taulaDadesUnitats"], true);
//        $taulaCalendari = json_decode($values["calendari"], true);
//        if($taulaCalendari!=NULL && $taulaDadesUnitats!=NULL){
//            $hores = array();
//            for($i=0; $i<count($taulaCalendari);$i++){
//                $idU = intval($taulaCalendari[$i]["unitat"]);
//                if(!isset($hores[$idU])){
//                    $hores[$idU]=0;
//                }
//                $hores[$idU]+= $taulaCalendari[$i]["hores"];
//            }
//
//            $horesUF = array();
//            $horesUF[0] = 0;
//            for($i=0; $i<count($taulaDadesUnitats);$i++){
//                $idU = intval($taulaDadesUnitats[$i]["unitat"]);
//                if(isset($hores[$idU])){
//                    $taulaDadesUnitats[$i]["hores"]=$hores[$idU];
//                }
//                $idUf = intval($taulaDadesUnitats[$i]["unitat formativa"]);
//                if(!isset($horesUF[$idUf])){
//                    $horesUF[$idUf]=0;
//                }
//                $horesUF[0]+= $taulaDadesUnitats[$i]["hores"];
//                $horesUF[$idUf]+= $taulaDadesUnitats[$i]["hores"];
//            }
//
//            if($taulaDadesUF!=NULL){
//                for($i=0; $i<count($taulaDadesUF);$i++){
//                     $idUf = intval($taulaDadesUF[$i]["unitat formativa"]);
//                     if(isset($horesUF[$idUf])){
//                         $taulaDadesUF[$i]["hores"]=$horesUF[$idUf];
//                     }
//                 }
//            }
//            $values["durada"] = json_encode($horesUF[0]);
//            $values["taulaDadesUnitats"] = json_encode($taulaDadesUnitats);
//            $values["taulaDadesUF"] = json_encode($taulaDadesUF);
//        }
        return parent::processValues($values);
    }
}
