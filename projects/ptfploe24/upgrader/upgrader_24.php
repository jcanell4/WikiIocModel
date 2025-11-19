<?php
/**
 * upgrader_1_a_40: Transforma l'estructura de dades i l'arxiu continguts.txt dels projectes 'ptfploe24'
 *                  des de la versió 0 a la versión 40
 * @author rafael
 *
 * Actualització fantasma per igualar l'estat de LOE a l'estat LOE24
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_24 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        // Actualització fantasma per igualar l'estat de LOE a l'estat LOE24
        return true;
    }

}
