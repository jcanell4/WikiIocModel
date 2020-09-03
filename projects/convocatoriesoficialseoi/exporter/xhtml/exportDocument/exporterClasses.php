<?php
/**
 * projecte: eoi
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
        if(!$result['error']){
            $result = $this->createZipFiles('b1', $data, $result);
        }
        if(!$result['error']){
            $result = $this->createZipFiles('b2', $data, $result);
        }
        if(!$result['error']){
            $result["info"] = "fitxers {$result['fileNames'][0]}, {$result["fileNames"][1]} i {$result["fileNames"][2]} creats correctement";
        }

        return $result;
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
                $this->addFilesToZip($zip, WIKI_LIB_IOC_MODEL . "exporter/xhtml", "c_sencer/", "css");
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
                    "data" => array(
                        "header_page_logo" => $this->cfgExport->rendererPath . "/resources/escutGene.jpg",
                        "header_page_wlogo" => 16,
                        "header_page_hlogo" => 18,
                        "header_ltext" => "Generalitat de Catalunya\nDepartament d'Educació\nEscola Oficial d'Idiomes\nInstitut Obert de Catalunya",
//                        "estil" => $estils['style'],
                    )
                );

                $params["data"]["titol"] = $data['title_' . $block];
                $params["data"]["contingut"] = json_decode($data["pdfconvocatoria_" . $block], TRUE);   //contingut latex ja rendaritzat

                $pdfFilename = "c-" . $block . ".pdf";
                StaticPdfRenderer::renderDocument($params, $pdfFilename);
                $zip->addFile($this->cfgExport->tmp_dir ."/". $pdfFilename, $pdfFilename);
                StaticPdfRenderer::resetStaticDataRender();

                $this->attachMediaFiles($zip);

                $result["files"] []= $zipFile;
                $result["fileNames"][] = "$output_filename.zip";
                //$result["info"] = "fitxers {$result['fileNames'][0]} i {$result["fileNames"][1]} creats correctement";
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

//    private function getParsedDocument($data, $document) {
//        $ret = array();
//        $tmplt = $this->loadTemplateFile($document);
//        $ret["data"] = WiocclParser::getValue($tmplt, [], $data);
//        $ret["toc"] = $this->cfgExport->toc;
//        return $ret;
//    }

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