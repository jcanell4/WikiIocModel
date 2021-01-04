<?php
/**
 * upgrader_3: Transforma los archivos convocatoria_??.txt y la estructura de datos de los proyectos 'convocatoriesoficialseoi'
 *             desde la versión 2 a la versión 3
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_3 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                //A2
                $dataProject["dadesEspecifiquesProvaA2"]["seu"]["provaVirtual"] = false;
                //B1
                $dataProject["dadesEspecifiquesProvaB1"]["seu"]["provaVirtual"] = false;
                //B2
                $dataProject["dadesEspecifiquesProvaB2"]["seu"]["provaVirtual"] = false;

                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":"'.($ver-1).'"}');
                break;

            case "templates":
                if ($filename===NULL) { //Ahora se pasan por parámetro cada uno de los ficheros (uno cada vez)
                    $filename = $this->model->getProjectDocumentName();
                }
                $conv = $this->model->getRawProjectDocument($filename);

                //Tratamiento común a todas las convocatorias
                $prova = strtoupper(explode("_", $filename)[1]);
                $provaVirtual = "dadesEspecifiquesProva$prova#seu#provaVirtual";

                $aTokDel = ["Convocatòria individual.*\s*.*horàries\.\n"];
                $conv = $this->updateTemplateByDelete($conv, $aTokDel);

                $aTokIns = [['regexp' => "^La prova tindrà lloc a.*$",
                              'text' => "<WIOCCL:IF condition=\"{##$provaVirtual##}==true\">\n".
                                        "La prova es realitzarà de forma virtual.\n".
                                        "</WIOCCL:IF>\n".
                                        "\n<WIOCCL:IF condition=\"{##$provaVirtual##}==false\">\n",
                              'pos' => 0,
                              'modif' => "m"
                            ],
                            ['regexp' => "^<\/map-table>$",
                              'text' => "\n</WIOCCL:IF>",
                              'pos' => 1,
                              'modif' => "m"
                            ]
                           ];
                $conv = $this->updateTemplateByInsert($conv, $aTokIns);

                $aTokRep = [["(====== Publicació de resultats ======)(\s*.*)*?(\n======)",
                             "$1\n".
                             "Les reclamacions són del {#_LONG_DATE(\"{##dataReclamacions##}\")_#} a les 11h fins el {#_LONG_DATE(\"{#_SUM_DATE(\"{##dataReclamacions##}\", 2)_#}\")_#} a les 11h. ".
                             "Les reclamacions es fan a través del formulari que es publicarà a Comunitat EOI el dia {#_LONG_DATE(\"{##dataReclamacions##}\")_#} a les 11h.".
                             "$3"
                           ]];
                $conv = $this->updateTemplateByReplace($conv, $aTokRep);

                $aTokRep = [["(En cas que no s\'hagi dut a terme alguna.*ada)(\.)(.*)(, en.*)",
                             "$1$3."
                           ]];
                $conv = $this->updateTemplateByReplace($conv, $aTokRep);

                //Tratamiento específico para cada convocatoria
                switch ($filename) {
                    case "convocatoria_a2":
                        $aTokRep = [["(Les persones aspirants que justifiquin documentalment)(.*\s*){2}.*inici de les proves",
                                     "$1 la seva impossibilitat de realitzar la prova el dia previst s'han de posar en contacte amb ididacademica@ioc.cat"
                                   ]];
                        $conv = $this->updateTemplateByReplace($conv, $aTokRep);
                        break;
                    case "convocatoria_b1":
                    case "convocatoria_b2":
                        $aTokRep = [["(L\'alumnat que es trobi en aquesta situació)(.*)anglès\"\/\/",
                                     "$1 s'ha d'adreçar a ididacademica@ioc.cat"
                                   ]];
                        $conv = $this->updateTemplateByReplace($conv, $aTokRep);
                        break;
                }

                if (($ret = !empty($conv))) {
                    $this->model->setRawProjectDocument($filename, $conv, "Upgrade template '$filename': version ".($ver-1)." to $ver");
                }
                break;
        }

        return $ret;
    }

}
