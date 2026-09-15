<?php

/**
 * upgrader_45: Afegeix els camps de dates PAF del semestre 2 als projectes 'ptfploe24'
 *             des de la versió 44 a la versió 45
 * @author marjose <marjose@ioc.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_45 extends ProgramacionsCommonUpgrader
{

    public function process($type, $ver, $filename = NULL)
    {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }

                $newFields = [
                    "dataPaf11_sem2" => "01/01/2000",
                    "dataPaf21_sem2" => "01/01/2000",
                    "dataQualificacioPaf1_sem2" => "01/01/2000",
                    "dataQualificacioPaf2_sem2" => "01/01/2000",
                ];
                foreach ($newFields as $name => $value) {
                    if (!isset($dataProject[$name])) {
                        $dataProject = $this->addNewField($dataProject, $name, $value);
                    }
                }

                $ret = $this->model->setDataProject(
                    json_encode($dataProject),
                    "Upgrade fields: version " . ($ver - 1) . " to $ver",
                    '{"fields":' . $ver . '}'
                );
                break;

            case "templates":
                if (FALSE) {
                    if (!$this->upgradeDocumentVersion($ver)) return false;
                }

                //Transforma el archivo continguts.txt del proyecto desde la versión $ver a la versión $ver+1
                if ($filename === NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                //upg45
                /*
                substringFromLineToLineAsPattern donats uns continguts.txt.v? crea un patro a partir del que recupera de la linia inicial - segon parametre- a la linia final -tercer parametre- i crea un patro
                substringFromLineToLine fa el mateix, sense crear el patró
                 * Aplico les diferències que trobo. I les aplico al txt_v45. Es seleccionen blocs no editables.
                 *  */
                $txt_v44 = $this->model->getRawProjectTemplate("continguts", 44);
                $txt_v45 = $this->model->getRawProjectTemplate("continguts", 45);

                $l180_l182_v44 = $this->substringFromLineToLineAsPattern($txt_v44, 180, 182); //el que busca
                $l180_l182_v45 = $this->substringFromLineToLine($txt_v45, 180, 182); //on ho substitueix

                $l284_l292_v44 = $this->substringFromLineToLineAsPattern($txt_v44, 284, 292); //el que busca
                $l284_l300_v45 = $this->substringFromLineToLine($txt_v45, 284, 300); //on ho substitueix

                $l762_l772_v44 = $this->substringFromLineToLineAsPattern($txt_v44, 762, 772); //el que busca
                $l770_l803_v45 = $this->substringFromLineToLine($txt_v45, 770, 803); //on ho substitueix

                $aTokRep = [
                    [$l180_l182_v44, $l180_l182_v45],
                    [$l284_l292_v44, $l284_l300_v45],
                    [$l762_l772_v44, $l770_l803_v45]
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
