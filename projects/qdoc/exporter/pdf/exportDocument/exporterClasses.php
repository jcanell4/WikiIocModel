<?php
/**
 * projecte: qdoc
 * exportDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();

class exportDocument extends MainRender {

    public function __construct($factory, $typedef, $renderdef, $params=NULL) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->initParams($params);
    }

    public function initParams($params=NULL){
        @set_time_limit(240);
        $this->time_start = microtime(TRUE);
        $this->cfgExport->langDir = dirname(__FILE__)."/lang/";
        if ($params){
            $this->cfgExport->id = $params['id'];
            $this->cfgExport->lang = (!isset($params['ioclanguage'])) ? 'ca' : strtolower(preg_replace('/\n/', '', $params['ioclanguage']));
            $this->log = isset($params['log']);
        }
        $this->cfgExport->export_html = TRUE;
        $this->cfgExport->rendererPath = dirname(realpath(__FILE__));
        parent::initParams();
    }

    public function cocinandoLaPlantillaConDatos($data) {
        $result = array();
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }

        $titol = html_entity_decode(htmlspecialchars_decode($data["titol"], ENT_COMPAT|ENT_QUOTES));
        $codi = html_entity_decode(htmlspecialchars_decode($data["codi"], ENT_COMPAT|ENT_QUOTES));
        $versio = html_entity_decode(htmlspecialchars_decode($data["versio"], ENT_COMPAT|ENT_QUOTES));

        $params = array(
            "id" => $this->cfgExport->id,
            "tmp_dir" => $this->cfgExport->tmp_dir,    //directori temporal on crear el pdf
            "lang" => strtoupper($this->cfgExport->lang),  // idioma usat (CA, EN, ES, ...)
            "mode" => isset($this->mode) ? $this->mode : $this->filetype,
            "data" => array(
                "header" => ["logo"  => $this->cfgExport->rendererPath . "/resources/escutGene.jpg",
                             "wlogo" => 9.9,
                             "hlogo" => 11.1,
                             "ltext" => "Generalitat de Catalunya\nDepartament d'Ensenyament\nInstitut Obert de Catalunya"],
                "peu" => ["titol" => $titol,
                          "codi"  => $codi,
                          "versio"=> $versio],
                "contingut" => json_decode($data["fitxercontinguts"], TRUE)   //contingut latex ja rendaritzat
            )
        );
        StaticPdfRenderer::renderDocument($params, "pt.pdf");
        $result["tmp_dir"] = $this->cfgExport->tmp_dir."/pt.pdf";
        return $result;
    }

}
