<?php
/*
 * systemUpgrader: Actualiza el modelo de versiones del proyecto
 */
if (!defined('DOKU_INC')) die();

class systemUpgrader {

    /**
     * Esta función se encarga de actualizar, en caso necesario, la estructura y los datos
     * del archivo de sistema "_wikiIocSystem_.mdpr" del proyecto
     */
    public static function preUpgrade($model, $subSet) {
        //De momento, no ha cambiado la clave de las versiones en el archivo de sistema
        return TRUE;
    }
}
