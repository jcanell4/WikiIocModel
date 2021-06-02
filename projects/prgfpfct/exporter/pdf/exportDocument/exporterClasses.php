<?php
/**
 * projecte: prgfpfct
 * exportDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
if (!defined('WIKI_LIB_IOC_MODEL')) define('WIKI_LIB_IOC_MODEL', DOKU_LIB_IOC."wikiiocmodel/");

class exportDocument extends renderHtmlDocument {

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
        parent::initParams();
    }

    public function cocinandoLaPlantillaConDatos($data) {
        $result = array();
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }

        $departament = html_entity_decode(htmlspecialchars_decode($data["departament"], ENT_COMPAT|ENT_QUOTES));
        $cicle = html_entity_decode(htmlspecialchars_decode($data["cicle"], ENT_COMPAT|ENT_QUOTES));

        $params = array(
            "id" => $this->cfgExport->id,
            "tmp_dir" => $this->cfgExport->tmp_dir,
            "lang" => strtoupper($this->cfgExport->lang),
            "mode" => isset($this->mode) ? $this->mode : $this->filetype,
    	    "max_img_size" => ($data['max_img_size']) ? $data['max_img_size'] : WikiGlobalConfig::getConf('max_img_size', 'wikiiocmodel'),
            "style" => $this->cfgExport->rendererPath."/pdf/exportDocument/styles/main.stypdf",
            "data" => array(
                "header" => ["logo"  => $this->cfgExport->rendererPath . "/resources/escutGene.jpg",
                             "wlogo" => 9.9,
                             "hlogo" => 11.1,
                             "ltext" => "Generalitat de Catalunya\nDepartament d'Educació\nInstitut Obert de Catalunya",
                             "rtext" => ""],
                "titol" => ["titol" => "Programacions cicles formatius",
                            "departament" => $departament,
                            "cicle" => $cicle,
                            "modulId" => $data["modulId"],
                            "hores" => $data['durada']],
                "peu" => ["logo"  => $this->cfgExport->rendererPath . "/resources/escutIOC.jpg",
                          "titol" => "Programacions FCT",
                          "wlogo" => 10,
                          "hlogo" => 8,
                          "codi"  => "I33",
                          "versio" => $data['documentVersion']],
                "contingut" => json_decode($data['pdfDocument'], TRUE)   //contingut latex ja rendaritzat
            )
        );
        $pdfRenderer = new PdfRenderer();
        $pdfRenderer->renderDocument($params, "pt.pdf");
        $result["tmp_dir"] = $this->cfgExport->tmp_dir."/pt.pdf";
        return $result;
    }

}
