<?php
/**
 * projecte: activityutil
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
        $this->ioclangcontinue = array('ca'=>'continuació', 'de'=>'fortsetzung', 'en'=>'continued','es'=>'continuación','fr'=>'suite','it'=>'continua');
        $this->cfgExport->langDir = dirname(__FILE__)."/lang/";
        if ($params){
            $this->cfgExport->id = $params['id'];
            $this->cfgExport->lang = (!isset($params['ioclanguage']))?'ca':strtolower($params['ioclanguage']);
            $this->cfgExport->lang = preg_replace('/\n/', '', $this->cfgExport->lang);
            $this->log = isset($params['log']);
        }
        $this->cfgExport->export_html = TRUE;
        parent::initParams();
    }

    // En este modulo NO se genera archivo PDF
    public function cocinandoLaPlantillaConDatos($data) {
        $result = array();
        $result["tmp_dir"] = $this->cfgExport->tmp_dir;
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }
        $output_filename = $this->cfgExport->output_filename;
        $pathTemplate = "xhtml/exportDocument/templates";

        $zip = new ZipArchive;
        $zipFile = $this->cfgExport->tmp_dir."/$output_filename.zip";
        $res = $zip->open($zipFile, ZipArchive::CREATE);

        if ($res === TRUE) {
            $document = $this->replaceInTemplate($data, "$pathTemplate/activityutil.tpl");

            if ($zip->addFromString('index.html', $document)) {
                $allPathTemplate = $this->cfgExport->rendererPath . "/$pathTemplate";
                $this->addFilesToZip($zip, $allPathTemplate, "", "css");
                $this->addDefaultCssFilesToZip($zip, "");
                $this->addFilesToZip($zip, $allPathTemplate, "", "img");
                $this->addFilesToZip($zip, $allPathTemplate, "", "js");
                $this->attachMediaFiles($zip);

                $result["file"] = $zipFile;
                $result["fileName"] = "$output_filename.zip";
                $result["info"] = "fitxer {$result['fileName']} creat correctement";
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

        if($data["estil"] == "boostioc"){
            $data_footer = "toTitle";
        }else{
            $data_footer = "default";
        }
        
        $document = str_replace("@@FIGURE_FOOTER_TYPE@@", $data_footer, $document);
        $document = str_replace("@@TABLE_FOOTER_TYPE@@", "default", $document);
        
//        foreach ($this->cfgExport->toc as $tocKey => $tocItem) {
//            $toc = "";
//            $nivel_anterior = 1; //nivel anterior
//            $ntoc = 0;           //número de tocItem actual
//
//            if ($tocItem){
//                foreach ($tocItem as $elem) {
//                    if ($elem['level'] <= $data['nivells']) {
//                        if ($elem['level'] > $nivel_anterior) {
//                            $toc .= "<div class='hidden'>\n";
//                        }
//                        if ($elem['level'] <= $nivel_anterior) {
//                            $toc .= $this->_add_close(($nivel_anterior-$elem['level'])*2+1);
//                        }
//                        $toc .= "<div class='toc_level_{$elem['level']}'>\n";
//                        $toc .= "<span>\n";
//                        if ($tocItem[$ntoc+1]['level'] > $elem['level']) { //si el elemento siguiente es de nivel inferior
//                            $toc .= "<span onclick='switchopcl(this)' class='button_index cl'>&nbsp;&nbsp;&nbsp;&nbsp;</span>\n";
//                        }else {
//                            $toc .= "<span class='button_index'>&nbsp;&nbsp;&nbsp;&nbsp;</span>\n";
//                        }
//                        $toc .= "<a href='{$elem['link']}' onclick='closeNav(); return true'>".htmlentities($elem['title'])."</a>\n";
//                        $toc .= "</span>\n";
//
//                        $nivel_anterior = $elem['level'];
//                        $ntoc++;
//                    }
//                }
//                $toc .= $this->_add_close(($nivel_anterior-1)*2);
//                $toc = substr($toc, 7) . "</div>\n";
//            }
//            $document = str_replace("@@TOC($tocKey)@@", $toc, $document);
//        }
        return $document;
    }

    private function _add_close($n) {
        $toc = "";
        for ($i=1; $i<=$n; $i++) {
            $toc .= "</div>\n";
        }
        return $toc;
    }

    private function attachMediaFiles(&$zip) {
        //Attach media files
        foreach(array_unique($this->cfgExport->media_files) as $f){
            resolve_mediaid(getNS($f), $f, $exists);
            if ($exists) {
                $zip->addFile(mediaFN($f), 'img/'.str_replace(":", "/", $f));
            }
        }
        $this->cfgExport->media_files = array();

        //Attach latex files
        foreach(array_unique($this->cfgExport->latex_images) as $f){
            if (file_exists($f)) $zip->addFile($f, 'img/'.basename($f));
        }
        $this->cfgExport->latex_images = array();

        //Attach graphviz files
        foreach(array_unique($this->cfgExport->graphviz_images) as $f){
            if (file_exists($f)) $zip->addFile($f, 'img/'.basename($f));
        }
        $this->cfgExport->graphviz_images = array();

        //Attach gif (png, jpg, etc) files
        foreach(array_unique($this->cfgExport->gif_images) as $m){
            if (file_exists(mediaFN($m))) $zip->addFile(mediaFN($m), "img/". str_replace(":", "/", $m));
        }
        $this->cfgExport->gif_images = array();

        if (session_status() == PHP_SESSION_ACTIVE) session_destroy();
    }

}
