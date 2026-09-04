<?php

/**
 * upgrader_44: Transforma el archivo continguts.txt de los proyectos 'ptfploe24'
 *             desde la versión 43 a la versión 44
 * @author marjose <marjose@ioc.cat>
 * @adaptacio marjose
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_44 extends ProgramacionsCommonUpgrader
{

    public function process($type, $ver, $filename = NULL)
    {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión $ver a la versión $ver+1
                $ret = true;
                break;

            case "templates":
                // Sólo se debe actualizar la versión del documento si el coordinador de calidad lo indica!!!!!!
                if (FALSE) {
                    if (!$this->upgradeDocumentVersion($ver)) return false;
                }

                //Transforma el archivo continguts.txt del proyecto desde la versión $ver a la versión $ver+1
                if ($filename === NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                //upg41
                /*
                substringFromLineToLineAsPattern donats uns continguts.txt.v? crea un patro a partir del que recupera de la linia inicial - segon parametre- a la linia final -tercer parametre- i crea un patro
                substringFromLineToLine fa el mateix, sense crear el patró
                 * Aplico les diferències que trobo. I les aplico al txt_v44. Es seleccionen blocs no editables.
                 *  */
                $txt_v43 = $this->model->getRawProjectTemplate("continguts", 43);
                $txt_v44 = $this->model->getRawProjectTemplate("continguts", 44);

                $l74_l76_v43 = $this->substringFromLineToLineAsPattern($txt_v43, 74, 76); //el que busca
                $l74_l128_v44 = $this->substringFromLineToLine($txt_v44, 74, 128); //on ho substitueix

                $aTokRep = [
                    [$l74_l76_v43, $l74_l128_v44]
                ];

                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument("$filename", $dataChanged, "Upgrade templates: version " . ($ver - 1) . " to $ver", $ver);
                }
                break;
        }
        return $ret;
    }
}
