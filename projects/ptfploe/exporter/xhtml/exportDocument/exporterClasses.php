<?php
/**
 * projecte: ptfploe
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
                $this->addDefaultCssFilesToZip($zip, "pt_sencer/");
                $this->addFilesToZip($zip, $allPathTemplate, "", "pt_sencer", TRUE);
                $ptSencer = $this->replaceInTemplate($data, "$pathTemplate/pt_sencer/pt.tpl");
                //cal modificar la ruta dels arxius gràfics
                $ptSencer = preg_replace("/(<img class=.media. src=.)(img.*?\.gif)/", "$1../$2", $ptSencer);
                $ptSencer = preg_replace("/(<img.*? src=.)(img\/)(.*?\/)*(.*?)(\.[png|jpg])/", "$1../$2$4$5", $ptSencer);
                $zip->addFromString('pt_sencer/pt.html', $ptSencer);

                $jsonDates = $this->replaceInJsonTemplate($data, "$pathTemplate/../json/templates/templateDates.tjson");
                ResultsWithFiles::putFileToMedia($jsonDates, $this->cfgExport->id, ".json");

                $semestre = ($data["semestre"]==1?"Setembre ":"Febrer ").date("Y");
                $cicle = $data["cicle"]==="Indiqueu el cicle"?"":html_entity_decode(htmlspecialchars_decode($data["cicle"], ENT_COMPAT|ENT_QUOTES));
                $modul = html_entity_decode(htmlspecialchars_decode($data["modul"], ENT_COMPAT|ENT_QUOTES));
                $modulId = html_entity_decode(htmlspecialchars_decode($data["modulId"], ENT_COMPAT|ENT_QUOTES));
                $tipusBlocModul = html_entity_decode(htmlspecialchars_decode($data["tipusBlocModul"], ENT_COMPAT|ENT_QUOTES));

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
                                     "rtext" => $cicle."\n".$modulId." ".$modul."-".$tipusBlocModul."\n".$semestre],
                        "titol" => array(
                            "Formació Professional",
                            "Pla de Treball",
                            $cicle,
                            $modulId." ".$modul . (($tipusBlocModul!="mòdul") ? " - $tipusBlocModul" : ""),
                            $semestre,
                        ),
                        "contingut" => json_decode($data["pdfDocument"], TRUE)   //contingut latex ja rendaritzat
                    )
                );
                $pdfRenderer = new PdfRenderer();
                $pdfRenderer->renderDocument($params, "pt.pdf");
                $zip->addFile($this->cfgExport->tmp_dir."/pt.pdf", "pt_sencer/pt.pdf");

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
        //Attach media files
        foreach(array_unique($this->cfgExport->media_files) as $f){
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
