<?php
/**
 * upgrader_9: Transforma la estructura de datos y el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 8 a la versión 9
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_9 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto "ptfploe" desde la estructura de la versión 8 a la versión 9
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                //Cambia el nombre del campo
                $dataProject = $this->changeFieldName($dataProject, "dataPaf1", "dataPaf11");
                $dataProject = $this->changeFieldName($dataProject, "dataPaf2", "dataPaf21");

                //Añade un campo en el primer nivel de la estructura de datos
                $dataProject = $this->addNewField($dataProject, "dataPaf12", $dataProject['dataPaf11']);
                $dataProject = $this->addNewField($dataProject, "dataPaf22", $dataProject['dataPaf21']);

                $status = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver (simultànea a la actualització de 25 a 26 de templates)", '{"fields":'.$ver.'}');
                break;

            case "templates":

                /* Buscar y Sustituir en el archivo 'continguts'
                 * 1-B) ***QF{##itemUf[unitat formativa]##} = <WIOCCL:FOREACH var="item" array="{##filtered##}" counter="indFiltered">{##item[abreviació qualificació]##} * {##item[ponderació]##}% <WIOCCL:IF condition="{##indFiltered##}\<{#_SUBS({#_ARRAY_LENGTH({##filtered##})_#},1)_#}">+</WIOCCL:IF></WIOCCL:FOREACH>**
                 * 1-S) ***QUF{##itemUf[unitat formativa]##} = <WIOCCL:FOREACH var="item" array="{##filtered##}" counter="indFiltered">{##item[abreviació qualificació]##} * {##item[ponderació]##}% <WIOCCL:IF condition="{##indFiltered##}\<{#_SUBS({#_ARRAY_LENGTH({##filtered##})_#},1)_#}">+
                 * 2-B) ***QF{##itemUf[unitat formativa]##} = {#_FIRST({##filtered##}, ''FIRST[ponderació]'')_#}% de la nota de la UF{##itemUf[unitat formativa]##} obtinguda a la PAF**.
                 * 2-S) ***QUF{##itemUf[unitat formativa]##} = {#_FIRST({##filtered##}, ''FIRST[ponderació]'')_#}% de la nota de la UF{##itemUf[unitat formativa]##} obtinguda a la PAF**.
                 * 3-B) La planificació establerta per a la UF{##ind##} és la següent: (veure:table:T11-{##itemUf[unitat formativa]##}:)
                 * 3-S) La planificació establerta per a la UF{##itemUf[unitat formativa]##} és la següent: (veure:table:T11-{##itemUf[unitat formativa]##}:)
                 */
                $doc = $this->model->getRawProjectDocument($filename);
                $aTokRep = [
                    [
                        "\\*\\*\\*QF\\{##itemUf\\[unitat formativa\\]##\\} \\= \\<WIOCCL:FOREACH var\\=\"item\" array\\=\"\\{##filtered##\\}\" counter\\=\"indFiltered\"\\>\\{##item\\[abreviació qualificació\\]##\\} \\* \\{##item\\[ponderació\\]##\\}\\% \\<WIOCCL:IF condition=\"\\{##indFiltered##\\}\\\\\<\\{#_SUBS\\(\\{#_ARRAY_LENGTH\\(\\{##filtered##\\}\\)_#\\},1\\)_#\\}\"\\>\\+",
                        "***QUF{##itemUf[unitat formativa]##} = <WIOCCL:FOREACH var=\"item\" array=\"{##filtered##}\" counter=\"indFiltered\">{##item[abreviació qualificació]##} * {##item[ponderació]##}% <WIOCCL:IF condition=\"{##indFiltered##}\\<{#_SUBS({#_ARRAY_LENGTH({##filtered##})_#},1)_#}\">+ "
                    ],
                    [
                        "\\*\\*\\*QF\\{##itemUf\\[unitat formativa\\]##\\} \\= \\{#_FIRST\\(\\{##filtered##\\}, ''FIRST\\[ponderació\\]''\\)_#\\}\\% de la nota de la UF\\{##itemUf\\[unitat formativa\\]##\\} obtinguda a la PAF\\*\\*\\.",
                        "***QUF{##itemUf[unitat formativa]##} = {#_FIRST({##filtered##}, ''FIRST[ponderació]'')_#}% de la nota de la UF{##itemUf[unitat formativa]##} obtinguda a la PAF**."
                    ],
                    [
                        "La planificació establerta per a la UF\\{##ind##\\} és la següent: \\(veure ?:table:T11\\-\\{##itemUf\\[unitat formativa\\]##\\}:\\)",
                        "La planificació establerta per a la UF{##itemUf[unitat formativa]##} és la següent: (veure :table:T11-{##itemUf[unitat formativa]##}:)"
                    ]
                ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($status = !empty($doc))) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $status;
    }

}
