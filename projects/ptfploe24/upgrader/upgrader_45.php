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
                $ret = true;
                break;
        }
        return $ret;
    }
}
