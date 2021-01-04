<?php
/**
 * upgrader_16: Transforma el archivo continguts.txt de los proyectos 'ptfplogse'
 *             desde la versión 15 a la versión 16
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_16 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $ret = TRUE;
                break;
            
            case "templates":
                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                $aTokRep = [
                            ["(Aquest <WIOCCL:IF condition=\"''crèdit''!=\{##tipusBlocCredit##\}\">\{##tipusBlocCredit##\} del<\/WIOCCL:IF> crèdit )(\{##credit##\} \{##descripcio##\})",
                             "$1{##creditId##} $2"],
                            ["\{#_ARRAY_GET_VALUE\(''descripció qualificació''\,\{#_SEARCH_VALUE\(''\{##item\[id\]##\}''\,\{##dadesQualificacio##\}\,''abreviació qualificació''\)_#\}\)_#\} \(\{##item\[id\]##\}\)", 
                             "{#_ARRAY_GET_VALUE(''descripció qualificació'',{#_SEARCH_VALUE(''{##item[id]##}'',{##dadesQualificacio##},''abreviació qualificació'')_#},'' '')_#} ({##item[id]##})"],
                            ["\{#_SEARCH_VALUE\(''\{##itemAval\[id\]##\}''\,\{##dadesQualificacio##\}\,''abreviació qualificació''\)_#\}\)_#\}\*\*", 
                             "{#_SEARCH_VALUE(''{##itemAval[id]##}'',{##dadesQualificacio##},''abreviació qualificació'')_#}, '' '')_#}**"],
                            ["\{?#_ARRAY_LENGTH\(\{##datesAC##\}\)_#\}.=0\|\|\{##idItemAval##\}.\>0\"\>\:\:\:\<\/WIOCCL:IF\> \| \*\*\{##itemAval\[id\]##\}\: \{#_ARRAY_GET_VALUE\(''descripció qualificació''\,", 
                             "{#_ARRAY_LENGTH({##filteredAC##})_#}\>0||{##idItemAval##}\>0\">:::</WIOCCL:IF> | **{##itemAval[id]##}: {#_ARRAY_GET_VALUE(''descripció qualificació'',"]                             
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade version 15 to 16");
                }
                $ret = !empty($doc);
        }
        return $ret;
    }

}
