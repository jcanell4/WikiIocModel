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
                $aTokRep = [["^:###\n<WIOCCL:IF condition=\"\{#_ARRAY_LENGTH\(\{##datesEAF##\}\)_#\}\\\\>0\">\n===== Exercici d'avaluació final \(EAF\) =====###:",
                            ":###\n===== Eines d'avaluació final =====\n\nEn aquest apartat s'expliquen les eines d'avaluació final usades en aquest <WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">bloc</WIOCCL:IF><WIOCCL:IF condition=\"''crèdit''=={##tipusBlocCredit##}\">crèdit</WIOCCL:IF>.\n\n<WIOCCL:IF condition=\"{#_ARRAY_LENGTH({##datesEAF##})_#}\\>0\">\n==== Exercici d'avaluació final (EAF) ====###:"],
                            ["^Com que és part de l'avaluació final, l'EAF té dues convocatòries cada semestre: EAF i recuperació EAF. L'alumne pot:\n\n  \* Presentar-se a l'EAF i, en cas de no superar-lo, presentar-se a la recuperació de l'EAF del mateix semestre\. En cap cas es pot presentar a la recuperació de l'EAF per pujar nota tenint l'EAF superat\.\n  \* Presentar-se directament a la recuperació de l'EAF\.",
                             "Com que és part de l'avaluació final, l'EAF té dues convocatòries cada semestre: EAF i recuperació EAF. Només podrà presentar-se a la darrera convocatòria (recuperació EAF), l'alumnat que no s'hagi presentat a la primera (EAF) o que havent-s'hi presentat, hagi tret una qualificació inferior a {##notaMinimaEAF##}."],
                            ["^===== Jornada tècnica \(JT\) =====###:",
                             "==== Jornada tècnica (JT) ====###:"],
                            ["^:###===== Prova d'avaluació final \(PAF\) =====###:",
                             ":###==== Prova d'avaluació final (PAF) ====###:"],
                            ["^S'ofereixen dues convocatòries cada semestre: convocatòria PAF 1 i convocatòria PAF 2 \(consulteu dates clau a la taula::table:T10:\)\. L'alumne pot:\n\n  \* Presentar-se a la PAF 1 i, en cas de no superar-la, presentar-se a la PAF 2 del mateix semestre \(2 setmanes després de la PAF 1\)\. En cap cas es pot presentar a la PAF 2 per pujar nota tenint la PAF 1 superada\.\n  \* Presentar-se directament a la PAF 2\.",
                             "<WIOCCL:IF condition=\"{#_ARRAY_LENGTH({##datesEAF##})_#}\\>0||{#_ARRAY_LENGTH({##datesJT##})_#}\\>0\">\nS'ofereixen dues convocatòries cada semestre: convocatòria PAF 1 i convocatòria PAF 2 (consulteu dates clau a la taula::table:T10:). Només es podrà presentar a la PAF 2, l'alumnat que compleixi tots els requisits següents:\n<WIOCCL:IF condition=\"{#_ARRAY_LENGTH({##datesEAF##})_#}\\>0\">\n  * Disposar d'una qualificació de l'EAF superior a {##notaMinimaEAF##}.\n</WIOCCL:IF>\n<WIOCCL:IF condition=\"{#_ARRAY_LENGTH({##datesJT##})_#}\\>0\">\n  * Disposar d'una qualificació de la JT superior a {##notaMinimaJT##}.\n</WIOCCL:IF>\n  * No haver superat el <WIOCCL:IF condition=\"''crèdit''=={##tipusBlocCredit##}\">crèdit</WIOCCL:IF><WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">bloc</WIOCCL:IF> en la primera convocatòria (PAF 1).</WIOCCL:IF><WIOCCL:IF condition=\"{#_ARRAY_LENGTH({##datesEAF##})_#}==0&&{#_ARRAY_LENGTH({##datesJT##})_#}==0\">S'ofereixen dues convocatòries cada semestre: convocatòria PAF 1 i convocatòria PAF 2 (consulteu dates clau a la taula::table:T10:). Només es podrà presentar a la PAF 2, l'alumnat que no hagi superat el <WIOCCL:IF condition=\"''crèdit''=={##tipusBlocCredit##}\">crèdit</WIOCCL:IF><WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">bloc</WIOCCL:IF> en la primera convocatòria (PAF 1).\n</WIOCCL:IF>"]
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
