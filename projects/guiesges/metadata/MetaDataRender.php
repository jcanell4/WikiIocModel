<?php
/**
 * Component: Project / MetaData
 */
namespace guiesges;
require_once(DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRenderAbstract.php');

class MetaDataRender extends \MetaDataRenderAbstract {
    protected function processValues($values){
//        $dedicacio = array(0, 0, 0, 0, 0);
//        $sequenciaLliurament = array(
//            json_decode($values["lliuraments"]["lliurament1"]["sequenciaDidactica"], true),
//            json_decode($values["lliuraments"]["lliurament2"]["sequenciaDidactica"], true),
//            json_decode($values["lliuraments"]["lliurament3"]["sequenciaDidactica"], true),
//            json_decode($values["lliuraments"]["lliurament4"]["sequenciaDidactica"], true)
//        );
//        for($i=0; $i<count($sequenciaLliurament); $i++){
//            if($sequenciaLliurament[$i]!=NULL){
//                for($j=0; $j<count($sequenciaLliurament[$i]);$j++){
//                    $dedicacio[$i]+= $sequenciaLliurament[$i][$j]["hores"];
//                    $dedicacio[4]+= $sequenciaLliurament[$i][$j]["hores"];
//                }                            
//            }            
//        }
//        $values["lliuraments"]["lliurament1"]["hores"] = $dedicacio[0];
//        $values["lliuraments"]["lliurament2"]["hores"] = $dedicacio[1];
//        $values["lliuraments"]["lliurament3"]["hores"] = $dedicacio[2];
//        $values["lliuraments"]["lliurament4"]["hores"] = $dedicacio[3];
//        $values["dedicacio"] = $dedicacio[4];
        return $values;
    }
}
