<?php
/**
 * upgrader_39: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 38 a la versión 39
 * @author rafael <rclaver@xtec.cat>
 * @adaptacio marjose
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_39 extends ProgramacionsCommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
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
                if ($filename===NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                //upg39
                /*

                 */
                $txt_v38 = $this->model->getRawProjectTemplate("continguts", 38);
                $txt_v39 = $this->model->getRawProjectTemplate("continguts", 39);
                $l76_l142_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 76, 142);
                $l76_l183_v39 = $this->substringFromLineToLine($txt_v39,76, 183);
                $l310_l338_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 310, 338);
                $l351_l387_v39 = $this->substringFromLineToLine($txt_v39, 351, 387);
                $l350_l355_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 350, 355);
                $l399_l404_v39 = $this->substringFromLineToLine($txt_v39, 399, 404);
                $l393_l397_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 393, 397);
                $l442_l451_v39 = $this->substringFromLineToLine($txt_v39, 442, 451);
                $l419_l446_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 419, 446);
                $l473_l509_v39 = $this->substringFromLineToLine($txt_v39, 473, 509);
                $l449_l449_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 449, 449);
                $l512_l512_v39 = $this->substringFromLineToLine($txt_v39, 512, 512);
                $l463_l463_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 463, 463);
                $l526_l526_v39 = $this->substringFromLineToLine($txt_v39, 526, 526);
                $l466_l486_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 466, 486);
                $l529_l558_v39 = $this->substringFromLineToLine($txt_v39, 529, 558);
                $l547_l553_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 547, 553);
                $l619_l636_v39 = $this->substringFromLineToLine($txt_v39, 619, 636);
                $l556_l556_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 556, 556);
                $l639_l639_v39 = $this->substringFromLineToLine($txt_v39, 639, 639);
                $l559_l569_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 559, 569);
                $l642_l664_v39 = $this->substringFromLineToLine($txt_v39, 642, 664);
                $l573_l601_v38 =  $this->substringFromLineToLineAsPattern($txt_v38, 573, 601);
                $l669_l727_v39 = $this->substringFromLineToLine($txt_v39, 669, 627);
                $aTokRep = [
                    [$l76_l142_v38, $l76_l183_v39],
                    [$l310_l338_v38, $l351_l387_v39],
                    [$l350_l355_v38, $l399_l404_v39],
                    [$l393_l397_v38, $l442_l451_v39],
                    [$l419_l446_v38, $l473_l509_v39],
                    [$l449_l449_v38, $l512_l512_v39],
                    [$l463_l463_v38, $l526_l526_v39],
                    [$l466_l486_v38, $l529_l558_v39],
                    [$l547_l553_v38, $l619_l636_v39],
                    [$l556_l556_v38, $l639_l639_v39],
                    [$l559_l569_v38, $l642_l664_v39],
                    [$l573_l601_v38, $l669_l727_v39]
                ];

                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument("$filename", $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}