<?php
/**
 * upgrader_1: Transforma el archivo continguts.txt de los proyectos 'convocatoriesoficialseoi'
 *             desde la versión 0 a la versión 1
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_1 extends CommonUpgrader {

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
                $dataProject["dadesEspecifiquesProvaA2"]= array();
                //title
                $dataProject["dadesEspecifiquesProvaA2"]["title"]=$dataProject["title_a2"];
                //dataProva
                $dataProject["dadesEspecifiquesProvaA2"]["dataProva"]=$dataProject["dataProva1"];
                //dataProvaNE
                $dataProject["dadesEspecifiquesProvaA2"]["dataProvaNE"]=$dataProject["dataProvaNE1"];
                //provaCertificat
                $dataProject["dadesEspecifiquesProvaA2"]["provaCertificat"]=$dataProject["provaCertificat_A2"];
                //seu
                $dataProject["dadesEspecifiquesProvaA2"]["seu"]["nom"]="Institut Obert de Catalunya";
                $dataProject["dadesEspecifiquesProvaA2"]["seu"]["via"]="Av. Paral·lel 71";
                $dataProject["dadesEspecifiquesProvaA2"]["seu"]["municipi"]="Barcelona";
                $dataProject["dadesEspecifiquesProvaA2"]["seu"]["codiPostal"]="08004";
                $dataProject["dadesEspecifiquesProvaA2"]["seu"]["metro"]="Paral·lel; línia 3 (verda)";
                $dataProject["dadesEspecifiquesProvaA2"]["seu"]["bus"]="120 (A prop: 59, 21, D20, H14, V11, V13)";
                $dataProject["dadesEspecifiquesProvaA2"]["seu"]["mapImg"]="mapa_ioc.jpg";
                $dataProject["dadesEspecifiquesProvaA2"]["seu"]["interactiveMap"]="https://goo.gl/maps/4rF3DVFTbvwRrNTg6";
           
                //B1
                $dataProject["dadesEspecifiquesProvaB1"]= array();
                //title
                $dataProject["dadesEspecifiquesProvaB1"]["title"]=$dataProject["title_b1"];
                //dataProva
                $dataProject["dadesEspecifiquesProvaB1"]["dataProva"]=$dataProject["dataProva1"];
                //dataProvaNE
                $dataProject["dadesEspecifiquesProvaB1"]["dataProvaNE"]=$dataProject["dataProvaNE1"];
                //provaCertificat
                $dataProject["dadesEspecifiquesProvaB1"]["provaCertificat"]=$dataProject["provaCertificat_B1"];
                //seu
                $dataProject["dadesEspecifiquesProvaB1"]["seu"]["nom"]="Institut Obert de Catalunya";
                $dataProject["dadesEspecifiquesProvaB1"]["seu"]["via"]="Av. Paral·lel 71";
                $dataProject["dadesEspecifiquesProvaB1"]["seu"]["municipi"]="Barcelona";
                $dataProject["dadesEspecifiquesProvaB1"]["seu"]["codiPostal"]="08004";
                $dataProject["dadesEspecifiquesProvaB1"]["seu"]["metro"]="Paral·lel; línia 3 (verda)";
                $dataProject["dadesEspecifiquesProvaB1"]["seu"]["bus"]="120 (A prop: 59, 21, D20, H14, V11, V13)";
                $dataProject["dadesEspecifiquesProvaB1"]["seu"]["mapImg"]="mapa_ioc.jpg";
                $dataProject["dadesEspecifiquesProvaB1"]["seu"]["interactiveMap"]=$dataProject["urlMap_A2B1"];;
           
                //B2
                $dataProject["dadesEspecifiquesProvaB2"]= array();
                //title
                $dataProject["dadesEspecifiquesProvaB2"]["title"]=$dataProject["title_b2"];
                //dataProva
                $dataProject["dadesEspecifiquesProvaB2"]["dataProva"]=$dataProject["dataProva2"];
                //dataProvaNE
                $dataProject["dadesEspecifiquesProvaB2"]["dataProvaNE"]=$dataProject["dataProvaNE2"];
                //provaCertificat
                $dataProject["dadesEspecifiquesProvaB2"]["provaCertificat"]=$dataProject["provaCertificat_B2"];
                //seu
                $dataProject["dadesEspecifiquesProvaB2"]["seu"]["nom"]="Institut Obert de Catalunya";
                $dataProject["dadesEspecifiquesProvaB2"]["seu"]["via"]="Av. Paral·lel 71";
                $dataProject["dadesEspecifiquesProvaB2"]["seu"]["municipi"]="Barcelona";
                $dataProject["dadesEspecifiquesProvaB2"]["seu"]["codiPostal"]="08004";
                $dataProject["dadesEspecifiquesProvaB2"]["seu"]["metro"]="Paral·lel; línia 3 (verda)";
                $dataProject["dadesEspecifiquesProvaB2"]["seu"]["bus"]="120 (A prop: 59, 21, D20, H14, V11, V13)";
                $dataProject["dadesEspecifiquesProvaB2"]["seu"]["mapImg"]="mapa_ioc.jpg";
                $dataProject["dadesEspecifiquesProvaB2"]["seu"]["interactiveMap"]=$dataProject["urlMap_B2"];
                
                //legislació Reclamacions
                $dataProject["legislacioReclamacio"]= $dataProject["14_EDU_34_2009"];
                //text legislacioReclmació
                $dataProject["textLegislacioReclamacio"]= "l’article 14 de l'Ordre EDU/34/2009";
                //modelReclamacio
                $dataProject["modelReclamacio"]= $dataProject["model_i67"];
                
                //normativa
                $dataProject["taulaNormativa"]=array();
                $dataProject["taulaNormativa"][] = ["nom" => "RESOLUCIÓ EDU/4039/2010", "de data" => "de 15 de desembre", "descripció" => "per la qual s'implanta el nivell bàsic  i el nivell intermedi d'anglès dels ensenyaments d'idiomes a l'Institut Obert de Catalunya  ( DOGC Núm. 5781 de 23/12/2010).", "url" => $dataProject["EDU_4039_2010"]];
                $dataProject["taulaNormativa"][] = ["nom" => "RESOLUCIÓ EDU/34/2009", "de data" => "", "descripció" => "per la qual s'organitzen les proves específiques de certificació dels nivells intermedi i avançat dels ensenyaments d'idiomes de règim especial que s'imparteixen a les escoles oficials d'idiomes.", "url" => $dataProject["EDU_34_2009"]];
                $dataProject["taulaNormativa"][] = ["nom" => "Decret 4/2009", "de data" => "de 13 de gener", "descripció" => "pel qual s'estableix l'ordenació i el currículum dels ensenyaments d'idiomes de règim especial (DOGC núm. 5297, de 15.1.2009).", "url" => $dataProject["Decret_4_2009"]];
                $dataProject["taulaNormativa"][] = ["nom" => "Decret 73/2014", "de data" => "de 27 de maig", "descripció" => "de modificació del Decret 4/2009, de 13 de gener, pel qual s'estableix l'ordenació i el currículum dels ensenyaments d'idiomes de règim especial.", "url" => $dataProject["Decret_73_2014"]];
                $dataProject["taulaNormativa"][] = ["nom" => "Reial Decret 1041/2017", "de data" => "de 22 de desembre", "descripció" => "por el que se fijan las exigencias mínimas del nivel básico a efectos de certificación, se establece el currículo básico de los niveles Intermedio B1, Intermedio B2, Avanzado C1, y Avanzado C2, de las Enseñanzas de idiomas de régimen especial reguladas por la Ley Orgánica 2/2006, de 3 de mayo, de Educación, y se establecen las equivalencias entre las Enseñanzas de idiomas de régimen especial reguladas en diversos planes de estudios y las de este real decreto.", "url" => $dataProject["ReialDecret_1041_2017"]];
                

//                //Añade el campo 'hiHaRecuperacio' a la tabla 'datesJT'
//                $dataProject = $this->addFieldInMultiRow($dataProject, "datesJT", "hiHaRecuperacio", TRUE);
//
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":"'.($ver-1).'"}');                
                
                $ret = TRUE;
                break;

            case "templates":
                // Força una copia del continguta al disc per tal que es desactivi l'edició parcial
                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                if (($ret = !empty($doc))) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver");
                }
                break;
        }
        return $ret;
    }

}
