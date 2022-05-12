<?php
/**
 * upgrader_26: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 25 a la versión 26
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_27 extends ProgramacionsCommonUpgrader {

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

                /* Este modelo no sirve dado que aplica la primera aparición de $2 ("item") a todas las sustituciones
                 * $aTokRep = [["^(<WIOCCL:FOREACH var=\")(.*?)(\" array=\"\{##)(taulaDadesUD)(##\}\")",
                 *              "$1$2$3sortedTaulaDadesUD$5"],
                 */
                $aTokRep = [["^No haver superat el <WIOCCL:IF condition=\"''crèdit''==\{##tipusBlocCredit##\}\">crèdit<\/WIOCCL:IF><WIOCCL:IF condition=\"''crèdit''!=\{##tipusBlocCredit##\}\">bloc<\/WIOCCL:IF> en la primera convocatòria \(PAF 1\)\.",
                            "No haver-se presntat a la PAF1 o en cas d'haver-s'hi presentat, no haver superat el <WIOCCL:IF condition=\"''crèdit''=={##tipusBlocCredit##}\">crèdit</WIOCCL:IF><WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">bloc</WIOCCL:IF>."],
                            ["^la PAF 2, l'alumnat que no hagi superat el <WIOCCL:IF condition=\"''crèdit''==\{##tipusBlocCredit##\}\">crèdit<\/WIOCCL:IF><WIOCCL:IF condition=\"''crèdit''!=\{##tipusBlocCredit##\}\">bloc<\/WIOCCL:IF> en la primera convocatòria \(PAF 1\)\.",
                             "la PAF 2, l'alumnat que no s'hagi presentat a la PAF1 o que havent-s'hi presentat, no hagi superat el <WIOCCL:IF condition=\"''crèdit''=={##tipusBlocCredit##}\">crèdit</WIOCCL:IF><WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">bloc</WIOCCL:IF>."]
                           ];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
