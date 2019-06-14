<?php
/**
 * upgrader_3: Transforma los datos del proyecto "ptfplogse"
 *             desde la estructura de la versión 2 a la estructura de la versión 3
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_4 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $filename = NULL) {
        switch ($type) {
            case "fields":
                $status = TRUE;
                break;

            case "templates":
                /* Buscar y Sustituir en el archivo 'continguts'
                    * Afegir les següenst líneas:
                 *
                 * Añadir las siguientes líneas:
                 * 4) antes de: \nS'ofereixen dues convocatòries ordinàries cada semestre: JT i recuperació JT
                 * 4) añadir: <WIOCCL:IF condition="{##hiHaRecuperacioPerJT##}==true">
                 * 5) antes de: \n\n===== Prova d'avaluació final (PAF) =====
                 * 5) añadir: </WIOCCL:IF>
                 */
                $doc = $this->model->getRawProjectDocument($filename);


                //INSERT
                $aTokIns = [
                    [
                        'regexp' => "^::table:T09$",
                        'text' => "<WIOCCL:IF condition=\"{##hiHaRecuperacioPerJT##}==true\">\n",
                        'pos' => 0,
                        'modif' => "m"],
                    [
                        'regexp' => "::table:T09.*?:::",
                        'text' => "\n</WIOCCL:IF>",
                        'pos' => 1,
                        'modif' => "ms"],
                    [
                        'regexp' => "::table:T09.*?<WIOCCL:FOREACH var=\"item\" array=\"{##datesJT##}\">.*? \|$",
                        'text' => "\n</WIOCCL:IF>",
                        'pos' => 1,
                        'modif' => "ms"]
                ];
                $doc = $this->updateTemplateByInsert($doc, $aTokIns);


                $aTokIns = [
                    ['regexp' => "::table:T09.*?<WIOCCL:FOREACH var=\"item\" array=\"{##datesJT##}\">",
                        'text' => "<WIOCCL:IF condition=\"{##item[hiHaRecuperacio]##}==true\">",
                        'pos' => 1,
                        'modif' => "ms"]
                ];

                $doc = $this->updateTemplateByInsert($doc, $aTokIns);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade: version 3 to 4");
                }
                $status = !empty($doc);
        }
        return $status;
    }

}
