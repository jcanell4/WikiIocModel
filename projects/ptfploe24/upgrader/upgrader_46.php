<?php

/**
 * upgrader_46: Transforma el archivo continguts.txt de los proyectos 'ptfploe24'
 *             desde la versión 45 a la versión 46
 * @author marjose <marjose@ioc.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_46 extends ProgramacionsCommonUpgrader
{

    public function process($type, $ver, $filename = NULL)
    {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión $ver a la versión $ver+1
                $ret = true;
                break;

            case "templates":
                // Sóly se debe actualizar la versión del documento si el coordinador de calidad lo indica!!!!!!
                if (FALSE) {
                    if (!$this->upgradeDocumentVersion($ver)) return false;
                }

                //Transforma el archivo continguts.txt del proyecto desde la versión $ver a la versión $ver+1
                if ($filename === NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                //upg46
                /*
                substringFromLineToLineAsPattern donats uns continguts.txt.v? crea un patro a partir del que recupera de la linia inicial - segon parametre- a la linia final -tercer parametre- i crea un patro
                substringFromLineToLine fa el mateix, sense crear el patró
                 * Aplico les diferències que trobo. I les aplico al txt_v46. Es seleccionen blocs no editables.
                 *  */
                $txt_v45 = $this->model->getRawProjectTemplate("continguts", 45);
                $txt_v46 = $this->model->getRawProjectTemplate("continguts", 46);

                $l423_l425_v45 = $this->substringFromLineToLineAsPattern($txt_v45, 423, 425); //el que busca
                $l423_l430_v46 = $this->substringFromLineToLine($txt_v46, 423, 430); //on ho substitueix

                $l550_l552_v45 = $this->substringFromLineToLineAsPattern($txt_v45, 550, 552); //el que busca
                $l554_l561_v46 = $this->substringFromLineToLine($txt_v46, 554, 561); //on ho substitueix

                $l651_l651_v45 = $this->substringFromLineToLineAsPattern($txt_v45, 651, 651); //el que busca
                $l659_l659_v46 = $this->substringFromLineToLine($txt_v46, 659, 659); //on ho substitueix

                $aTokRep = [
                    [$l423_l425_v45, $l423_l430_v46],
                    [$l550_l552_v45, $l554_l561_v46],
                    [$l651_l651_v45, $l659_l659_v46]
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
