<?php
/**
 * projecte: guiesges
 * exportDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
if (!defined('WIKI_LIB_IOC_MODEL')) define('WIKI_LIB_IOC_MODEL', DOKU_LIB_IOC."wikiiocmodel/");

class exportDocument extends MainRender {

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
                $this->addFilesToZip($zip, WIKI_LIB_IOC_MODEL."exporter/xhtml", "ge_sencera/", "css");
                $ptSencer = $this->replaceInTemplate($data, "$pathTemplate/ge_sencera/ge.tpl");
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

                StaticPdfRenderer::renderDocument($params, "ge.pdf");
                $zip->addFile($this->cfgExport->tmp_dir."/ge.pdf", "/ge_sencera/ge.pdf");

                StaticPdfRenderer::resetStaticDataRender();
                $params["data"]["titol"]=array("Estudis de GES","Guia docent",$modul);
                $params["data"]["contingut"]=json_decode($data["pdfgd"], TRUE);   //contingut latex ja rendaritzat
                StaticPdfRenderer::renderDocument($params, $filePdf);

                $this->attachMediaFiles($zip);

                $result["files"] = array($zipFile, $this->cfgExport->tmp_dir."/$filePdf");
                $result["fileNames"] = array_values($fileNames);
                $result["info"] = "fitxers {$result['fileNames'][0]} i {$result["fileNames"][1]} creats correctement";
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
        return $result;
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

    private function attachMediaFiles(&$zip) {
        global $conf;
        //Attach media files
        foreach($this->cfgExport->media_files as $f){
            resolve_mediaid(getNS($f), $f, $exists);
            if ($exists) {
                //eliminamos el primer nivel del ns
                $arr = explode(":", $f);
                array_shift($arr);
                $zip->addFile(mediaFN($f), 'img/'.implode("/", $arr));
            }
        }
        $this->cfgExport->media_files = array();

        //Attach latex files
        foreach($this->cfgExport->latex_images as $f){
            if (file_exists($f)) $zip->addFile($f, 'img/'.basename($f));
        }
        $this->cfgExport->latex_images = array();

        //Attach graphviz files
        foreach($this->cfgExport->graphviz_images as $f){
            if (file_exists($f)) $zip->addFile($f, 'img/'.basename($f));
        }
        $this->cfgExport->graphviz_images = array();

        //Attach gif (png, jpg, etc) files
        foreach($this->cfgExport->gif_images as $m){
            if (file_exists(mediaFN($m))) $zip->addFile(mediaFN($m), "img/". str_replace(":", "/", $m));
        }
        $this->cfgExport->gif_images = array();

        if (session_status() == PHP_SESSION_ACTIVE) session_destroy();
    }

    private function addFilesToZip(&$zip, $base, $d, $dir, $recursive=FALSE) {
        $zip->addEmptyDir("$d$dir");
        $files = $this->getDirFiles("$base/$dir");
        foreach($files as $f){
            $zip->addFile($f, "$d$dir/".basename($f));
        }
        if($recursive){
            $dirs = $this->getDirs("$base/$dir");
            foreach($dirs as $dd){
                $this->addFilesToZip($zip, "$base/$dir", "$d$dir/", basename($dd));
            }
        }
    }

    /**
     * Fill files var with all media files stored on directory var
     * @param string $directory
     * @param string $files
     */
    private function getDirs($dir){
        $files = array();
        if (file_exists($dir) && is_dir($dir) && is_readable($dir)) {
            $dh = opendir($dir);
            while ($file = readdir($dh)) {
                if ($file != '.' && $file != '..' && is_dir("$dir/$file")) {
                    array_push($files, "$dir/$file");
                }
            }
            closedir($dh);
        }
        return $files;
    }

    private function getDirFiles($dir){
        $files = array();
        if (file_exists($dir) && is_dir($dir) && is_readable($dir)) {
            $dh = opendir($dir);
            while ($file = readdir($dh)) {
                if ($file != '.' && $file != '..' && !is_dir("$dir/$file")) {
                    if (preg_match('/.*?\.pdf|.*?\.png|.*?\.jpg|.*?\.gif|.*?\.ico|.*?\.css|.*?\.js|.*?\.htm|.*?\.html|.*?\.svg/', $file)){
                        array_push($files, "$dir/$file");
                    }
                }
            }
            closedir($dh);
        }
        return $files;
    }
}

