<?php
/**
 * projecte: guiesges
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
        $this->cfgExport->langDir = dirname(__FILE__)."/lang/";
        if($params){
            $this->cfgExport->id = $params['id'];
            $this->cfgExport->lang = (!isset($params['ioclanguage']))?'ca':strtolower($params['ioclanguage']);
            $this->cfgExport->lang = preg_replace('/\n/', '', $this->cfgExport->lang);
            $this->log = isset($params['log']);
        }
        $this->cfgExport->export_html = TRUE;
        parent::initParams();
    }

    public function cocinandoLaPlantillaConDatos($data) {
        $result = array();
        $result["tmp_dir"] = $this->cfgExport->tmp_dir;
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }
        $pathTemplate = "xhtml/exportDocument/templates";

        $fileNames = $this->factory->getProjectModel()->getMetaDataFtpSenderFiles();
        //Només s'espera que arribin 2 fitxers: un zip i un pdf
        foreach ($fileNames as $value) {
            switch (pathinfo($value, PATHINFO_EXTENSION)) {
                case "zip": $fileZip = $value; break;
                case "pdf": $filePdf = $value; break;
            }
        }

        $zip = new ZipArchive;
        $zipFile = $this->cfgExport->tmp_dir."/$fileZip";
        $res = $zip->open($zipFile, ZipArchive::CREATE);

        if ($res === TRUE) {
            $document = $this->replaceInTemplate($data, "$pathTemplate/index.html");

            if ($zip->addFromString('index.html', $document)) {
                $allPathTemplate = $this->cfgExport->rendererPath . "/$pathTemplate";
                $this->addFilesToZip($zip, $allPathTemplate, "", "img");
                $zip->addFile($allPathTemplate."/main.css", "main.css");
                $this->addFilesToZip($zip, $allPathTemplate, "", "ge_sencera", TRUE);
                $this->addDefaultCssFilesToZip($zip, "ge_sencera/");
                $ptSencer = $this->replaceInTemplate($data, "$pathTemplate/ge_sencera/ge.tpl");
                //cal modificar la ruta dels arxius gràfics
                $ptSencer = preg_replace("/(<img class=.media. src=.)(img.*?\.gif)/", "$1../$2", $ptSencer);
                $ptSencer = preg_replace("/(<img.*? src=.)(img\/)(.*?\/)*(.*?)(\.[png|jpg])/", "$1../$2$4$5", $ptSencer);
                $zip->addFromString('/ge_sencera/ge.html', $ptSencer);

                $trimestre = ($data["trimestre"]==1?"Tardor ":($data["trimestre"]==2?"Hivern ":"Primavera ")).date("Y");
                $modul = html_entity_decode(htmlspecialchars_decode($data["codi_modul"], ENT_COMPAT|ENT_QUOTES));
                $modul .= "-";
                $modul .= html_entity_decode(htmlspecialchars_decode($data["modul"], ENT_COMPAT|ENT_QUOTES));
                $codi = html_entity_decode(htmlspecialchars_decode($data["codi"], ENT_COMPAT|ENT_QUOTES));
                $versio = html_entity_decode(htmlspecialchars_decode($data["versio"], ENT_COMPAT|ENT_QUOTES));
                $titol = ["Estudis de GES", "Guia d'estudi", $modul, $trimestre];

                $params = array(
                    "id" => $this->cfgExport->id,
                    "path_templates" => $this->cfgExport->rendererPath . "/pdf/exportDocument/templates",  // directori on es troben les plantilles latex usades per crear el pdf
                    "tmp_dir" => $this->cfgExport->tmp_dir,    //directori temporal on crear el pdf
                    "lang" => strtoupper($this->cfgExport->lang),  // idioma usat (CA, EN, ES, ...)
                    "mode" => isset($this->mode) ? $this->mode : $this->filetype,
                    "max_img_size" => ($data['max_img_size']) ? $data['max_img_size'] : WikiGlobalConfig::getConf('max_img_size', 'wikiiocmodel'),
                    "style" => $this->cfgExport->rendererPath."/xhtml/exportDocument/pdf/main.stypdf",
                    "data" => array(
                        "header" => ["logo" => $this->cfgExport->rendererPath . "/resources/escutGene.jpg",
                                     "wlogo" => 9.9,
                                     "hlogo" => 11.1,
                                     "ltext" => "Generalitat de Catalunya\nDepartament d'Educació\nInstitut Obert de Catalunya",
                                     "rtext" => $modul."\n".$trimestre],
                        "titol" => $titol,
                        "peu" => ["titol" => implode(" ", $titol),
                                  "codi"  => $codi,
                                  "versio"=> $versio],
                        "contingut" => json_decode($data["pdfge"], TRUE)   //contingut latex ja rendaritzat
                    )
                );
                $pdfRenderer = new PdfRenderer();
                $pdfRenderer->renderDocument($params, "ge.pdf");
                $zip->addFile($this->cfgExport->tmp_dir."/ge.pdf", "/ge_sencera/ge.pdf");

                $pdfRenderer->resetDataRender();
                $params["data"]["titol"] = array("Estudis de GES","Guia docent",$modul);
                $params["data"]["contingut"] = json_decode($data["pdfgd"], TRUE);   //contingut latex ja rendaritzat
                $pdfRenderer->renderDocument($params, $filePdf);

                $this->attachMediaFiles($zip);

                $result["files"] = array($zipFile, $this->cfgExport->tmp_dir."/$filePdf");
                $result["fileNames"] = array_values($fileNames);
                foreach ($result["fileNames"] as $name) {
                    $result["info"][] = "fitxer $name creat correctement";
                }
            }else{
                $result['error'] = true;
                $result['info'] = $this->cfgExport->aLang['nozipfile'];
                throw new Exception ("Error en la creació del fitxer zip");
            }
            if (!$zip->close()) {
                $result['error'] = true;
                $result['info'] = $this->cfgExport->aLang['nozipfile'];
            }
        }else{
            $result['error'] = true;
            $result['info'] = $this->cfgExport->aLang['nozipfile'];
        }
        $this->setResultFileList($result);

        return $data;
    }

    private function replaceInTemplate($data, $file) {
        $tmplt = $this->loadTemplateFile($file);
        $document = WiocclParser::getValue($tmplt, [], $data);
        foreach ($this->cfgExport->toc as $tocKey => $tocItem) {
            if ($tocItem) {
                $toc ="";
                foreach ($tocItem as $elem) {
                    if($elem['level']<2){
                        $toc .= "<a href='{$elem['link']}'>".htmlentities($elem['title'])."</a>\n";
                    }
                }
                $document = str_replace("@@TOC($tocKey)@@", $toc, $document);
            }
        }
        return $document;
    }

}
