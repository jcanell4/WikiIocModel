<?php
/**
 * projecte: ptfplogse
 * exportDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
if (!defined('WIKI_LIB_IOC_MODEL')) define('WIKI_LIB_IOC_MODEL', DOKU_LIB_IOC."wikiiocmodel/");

class exportDocument extends renderHtmlDocument {

    public function __construct($factory, $typedef, $renderdef, $params=NULL) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->initParams($params);
//        Logger::init(1);
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
        $this->cfgExport->export_html = TRUE;
        parent::initParams();
    }

    public function cocinandoLaPlantillaConDatos($data) {
        $result = array();
        $result["tmp_dir"] = $this->cfgExport->tmp_dir;
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }
        $output_filename = str_replace(':','_',$this->cfgExport->id);
        $pathTemplate = "xhtml/exportDocument/templates";

        $zip = new ZipArchive;
        $zipFile = $this->cfgExport->tmp_dir."/$output_filename.zip";
        $res = $zip->open($zipFile, ZipArchive::CREATE);

        if ($res === TRUE) {
            $document = $this->replaceInTemplate($data, "$pathTemplate/index.html");

            if ($zip->addFromString('index.html', $document)) {
                $allPathTemplate = $this->cfgExport->rendererPath . "/$pathTemplate";
                $this->addFilesToZip($zip, $allPathTemplate, "", "img");
                $zip->addFile($allPathTemplate."/main.css", "main.css");
//                $this->addFilesToZip($zip, WIKI_LIB_IOC_MODEL."exporter/xhtml", "pt_sencer/", "css");
                $this->addDefaultCssFilesToZip($zip, "pt_sencer/");                
                $this->addFilesToZip($zip, $allPathTemplate, "", "pt_sencer", TRUE);
                $ptSencer = $this->replaceInTemplate($data, "$pathTemplate/pt_sencer/pt.tpl");
                $zip->addFromString('/pt_sencer/pt.html', $ptSencer);

                $semestre = ($data["semestre"]==1?"Setembre ":"Febrer ").date("Y");
                $cicle = $data["cicle"]==="Indiqueu el cicle"?"":html_entity_decode(htmlspecialchars_decode($data["cicle"], ENT_COMPAT|ENT_QUOTES));
                $credit = html_entity_decode(htmlspecialchars_decode($data["credit"], ENT_COMPAT|ENT_QUOTES));
                $creditId = html_entity_decode(htmlspecialchars_decode($data["creditId"], ENT_COMPAT|ENT_QUOTES));
                $tipusBlocCredit = html_entity_decode(htmlspecialchars_decode($data["tipusBlocCredit"], ENT_COMPAT|ENT_QUOTES));
                //$durada = html_entity_decode(htmlspecialchars_decode($data["durada"], ENT_COMPAT|ENT_QUOTES));
                //$professors = html_entity_decode(htmlspecialchars_decode($data["professors"], ENT_COMPAT|ENT_QUOTES));
                //$coordinador = html_entity_decode(htmlspecialchars_decode($data["coordinador"], ENT_COMPAT|ENT_QUOTES));

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
                                     "rtext" => $cicle."\n".$creditId." ".$credit."-".$tipusBlocCredit."\n".$semestre],
                        "titol" => ["Formació Professional",
                                    "Pla de Treball",
                                    $cicle,
                                    $creditId." ".$credit . (($tipusBlocCredit!="crèdit") ? " - $tipusBlocCredit" : ""),
                                    $semestre],
                        "contingut" => json_decode($data["pdfDocument"], TRUE)   //contingut latex ja rendaritzat
                    )
                );
                $pdfRenderer = new PdfRenderer();
                $pdfRenderer->renderDocument($params, "pt.pdf");
                $zip->addFile($this->cfgExport->tmp_dir."/pt.pdf", "/pt_sencer/pt.pdf");

                $this->attachMediaFiles($zip);

                $result["zipFile"] = $zipFile;
                $result["zipName"] = "$output_filename.zip";
                $result["info"] = "fitxer {$result['zipName']} creat correctement";
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

//    private function addFilesToZip(&$zip, $base, $d, $dir, $recursive=FALSE) {
//        $zip->addEmptyDir("$d$dir");
//        $files = $this->getDirFiles("$base/$dir");
//        foreach($files as $f){
//            $zip->addFile($f, "$d$dir/".basename($f));
//        }
//        if($recursive){
//            $dirs = $this->getDirs("$base/$dir");
//            foreach($dirs as $dd){
//                $this->addFilesToZip($zip, "$base/$dir", "$d$dir/", basename($dd));
//            }
//        }
//    }

//    /**
//     * Fill files var with all media files stored on directory var
//     * @param string $directory
//     * @param string $files
//     */
//    private function getDirs($dir){
//        $files = array();
//        if (file_exists($dir) && is_dir($dir) && is_readable($dir)) {
//            $dh = opendir($dir);
//            while ($file = readdir($dh)) {
//                if ($file != '.' && $file != '..' && is_dir("$dir/$file")) {
//                    array_push($files, "$dir/$file");
//                }
//            }
//            closedir($dh);
//        }
//        return $files;
//    }

//    private function getDirFiles($dir){
//        $files = array();
//        if (file_exists($dir) && is_dir($dir) && is_readable($dir)) {
//            $dh = opendir($dir);
//            while ($file = readdir($dh)) {
//                if ($file != '.' && $file != '..' && !is_dir("$dir/$file")) {
//                    if (preg_match('/.*?\.pdf|.*?\.png|.*?\.jpg|.*?\.gif|.*?\.ico|.*?\.css|.*?\.js|.*?\.htm|.*?\.html|.*?\.svg/', $file)){
//                        array_push($files, "$dir/$file");
//                    }
//                }
//            }
//            closedir($dh);
//        }
//        return $files;
//    }
}

//class render_title extends renderField {
//    public function process($data) {
//        $ret = parent::process($data);
//        return $ret;
//    }
//}
