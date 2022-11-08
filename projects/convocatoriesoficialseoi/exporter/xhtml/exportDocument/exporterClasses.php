<?php
/**
 * projecte: eoi
 * exportDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();

class exportDocument extends renderHtmlDocument {

    public function __construct($factory, $typedef, $renderdef, $params=NULL) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->initParams($params);
    }

    public function initParams($params=NULL){
        @set_time_limit(240);
        $this->time_start = microtime(TRUE);
        $this->ioclangcontinue = array('ca'=>'continuació', 'de'=>'fortsetzung', 'en'=>'continued','es'=>'continuación','fr'=>'suite','it'=>'continua');
        $this->cfgExport->langDir = dirname(__FILE__)."/lang/";
        if($params){
            $this->cfgExport->id = $params['id'];
            $this->cfgExport->lang = (!isset($params['ioclanguage']))?'ca':strtolower($params['ioclanguage']);
            $this->cfgExport->lang = preg_replace('/\n/', '', $this->cfgExport->lang);
            $this->log = isset($params['log']);
        }
        parent::initParams();
    }

    public function cocinandoLaPlantillaConDatos($data) {
        $result = array();
        $result["tmp_dir"] = $this->cfgExport->tmp_dir;
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }

        $result["files"] = array();
        $result["fileNames"] = array();
        $result["error"] = false;

        $result = $this->createZipFiles('a2', $data, $result);
        if (!$result['error']){
            $result = $this->createZipFiles('b1', $data, $result);
        }
        if (!$result['error']){
            $result = $this->createZipFiles('b2', $data, $result);
        }
        if (!$result['error']){
            $result["info"] = "fitxers {$result['fileNames'][0]}, {$result["fileNames"][1]} i {$result["fileNames"][2]} creats correctement";
        }
        $this->setResultFileList($result);

        return $data;
    }

    private function createZipFiles($block, $data, $result) {
        $output_filename = str_replace(':', '_', $this->cfgExport->id). "_" . $block;
        $pathTemplate = "xhtml/exportDocument/templates";
        $zip = new ZipArchive;
        $zipFile = $this->cfgExport->tmp_dir . "/" . $output_filename . ".zip";
        $res = $zip->open($zipFile, ZipArchive::CREATE);

        if ($res === TRUE) {
            $document = $this->replaceInTemplate($data, "$pathTemplate/index_$block.html");

            if ($zip->addFromString('index.html', $document)) {
                $allPathTemplate = $this->cfgExport->rendererPath . "/$pathTemplate";
                $this->addFilesToZip($zip, $allPathTemplate, "", "img");
                $zip->addFile($allPathTemplate . "/main.css", "main.css");
                $this->addDefaultCssFilesToZip($zip, "c_sencer/");
                $this->addFilesToZip($zip, $allPathTemplate, "", "c_sencer", TRUE);
                $this->addFilesToZip($zip, $this->cfgExport->rendererPath, "c_sencer/", "resources");
                $cSencer = $this->replaceInTemplate($data, "$pathTemplate/c_sencer/c" . $block . ".tpl");
                $zip->addFromString('/c_sencer/c'. $block . '.html', $cSencer);

                $params = array(
                    "id" => $this->cfgExport->id,
                    "path_templates" => $this->cfgExport->rendererPath . "/pdf/exportDocument/templates",  // directori on es troben les plantilles latex usades per crear el pdf
                    "tmp_dir" => $this->cfgExport->tmp_dir,    //directori temporal on crear el pdf
                    "lang" => strtoupper($this->cfgExport->lang),  // idioma usat (CA, EN, ES, ...)
                    "mode" => isset($this->mode) ? $this->mode : $this->filetype,
                    "max_img_size" => ($data['max_img_size']) ? $data['max_img_size'] : WikiGlobalConfig::getConf('max_img_size', 'wikiiocmodel'),
                    "style" => $this->cfgExport->rendererPath."/xhtml/exportDocument/pdf/main.stypdf",
                    "data" => array(
                        "header_page_logo" => $this->cfgExport->rendererPath . "/resources/escutGene.jpg",
                        "header_page_wlogo" => 16,
                        "header_page_hlogo" => 18,
                        "header_ltext" => "Generalitat de Catalunya\nDepartament d'Educació\nEscola Oficial d'Idiomes\nInstitut Obert de Catalunya"
                    )
                );

                $params["data"]["titol"] = $data['title_' . $block];
                $params["data"]["contingut"] = json_decode($data["pdfconvocatoria_" . $block], TRUE);   //contingut latex ja rendaritzat

                $pdfFilename = "c-" . $block . ".pdf";
                $pdfRenderer = new PdfRenderer();
                $pdfRenderer->renderDocument($params, $pdfFilename);
                $zip->addFile($this->cfgExport->tmp_dir ."/". $pdfFilename, $pdfFilename);
                $pdfRenderer->resetDataRender();

                $this->attachMediaFiles($zip);

                $result["files"] [] = $zipFile;
                $result["fileNames"][] = "$output_filename.zip";
            }else{
                $result['error'] = true;
                $result['info'] = $this->cfgExport->aLang['nozipfile'];
                throw new Exception ("Error en la creació del fitxer $output_filename.zip");
            }
            if (!$zip->close()) {
                $result['error'] = true;
                $result['info'] = $this->cfgExport->aLang['nozipfile'];
            }
        }else{
            $result['error'] = true;
            $result['info'] = $this->cfgExport->aLang['nozipfile'];
        }
        return $result;
    }

    private function replaceInTemplate($data, $file) {
        $tmplt = $this->loadTemplateFile($file);
        $document = WiocclParser::getValue($tmplt, [], $data);
        foreach ($this->cfgExport->toc as $tocKey => $tocItem) {
            $toc ="";
            if($tocItem){
                foreach ($tocItem as $elem) {
                    if($elem['level']==1){
                        $toc .= "<a href='{$elem['link']}'>".htmlentities($elem['title'])."</a>\n";
                    }
                }
            }
            $document = str_replace("@@TOC($tocKey)@@", $toc, $document);
        }
        return $document;
    }

}
