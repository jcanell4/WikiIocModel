<?php
/*
 * systemUpgrader: Actualiza el modelo de versiones del proyecto
 */
if (!defined('DOKU_INC')) die();

class systemUpgrader {

    /**
     * Esta función se encarga de actualizar, en caso necesario, la estructura y los datos
     * del archivo de sistema "_wikiIocSystem_.mdpr" del proyecto, así como de adaptar aquellos 
     * cambios en la configuració que sean de tipo genèrico que precisen un cambio con independència 
     * temporal y sin importar la versión en la qual se encuentran. 
     * Los valores constantes de esta función son constantes porque describen la historia real de los cambios
     */
    public static function preUpgrade($model, $subSet) {
        //Averigua si el archivo de sistema está en el modelo actual de versiones:
        //  {"state":{"generated":false},"main":{"versions":{"fields":0,"templates":{"continguts":0}}}}
        $ver = $model->getProjectSystemSubSetAttr("versions", $subSet);
        $ret = ($ver);
        if (!$ver) {
            //preguntamos por el modelo antiguo de versiones que corresponde con:
            //   {"state":{"generated":true},"main":{"version":1}}
            //   Aquí el valor de "version" se refiere a la versión del template
            $ver = $model->getProjectSystemSubSetAttr("version", $subSet);

            if ($ver===NULL) {
                $ver = 0;
            }else {
                //Hay que eliminar el antiguo elemento "version"
                $tmpSubSet = $model->getSystemData($subSet);
                unset($tmpSubSet['version']);
                $model->setSystemData($tmpSubSet, $subSet);
            }

            //estructura de versiones establecida en el archivo configMain.json (subset correspondiente) del tipo de proyecto
            $v_project = array();
            $v_config = $model->getMetaDataAnyAttr("versions");
            if ($v_config) {
                foreach ($v_config as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $k => $v) {
                            $v_project[$key][$k] = $ver; //asignamos a la versión del template el valor antiguo
                        }
                    }else {
                        $v_project[$key] = 0; //asignamos a fields el valor 0 (no hay valor antiguo) empezamos desde cero
                    }
                }
            }

            if (!empty($v_project)) {
                $ret = $model->setProjectSystemSubSetAttr("versions", $v_project, $subSet);
            }
        }
        return $ret;
    }
}
