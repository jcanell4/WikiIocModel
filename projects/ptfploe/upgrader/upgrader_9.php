<?php
/**
 * upgrader_7: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 6 a la versión 7
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_9 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $filename=NULL) {
        switch ($type) {
            case "fields":
                $status = TRUE;
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
                        "La planificació establerta per a la UF\\{##ind##\\} és la següent: \\(veure:table:T11-\\{##itemUf\\[unitat formativa\\]##\\}:\\)", 
                        "La planificació establerta per a la UF{##itemUf[unitat formativa]##} és la següent: (veure :table:T11-{##itemUf[unitat formativa]##}:)"
                    ]
                ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade: version 7 to 8");
                }
                $status = !empty($doc);
        }
        return $status;
    }

}
