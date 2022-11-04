<?php
/**
 * projecte: activityutil
 * exportDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
if (!defined('WIKI_LIB_IOC_MODEL')) define('WIKI_LIB_IOC_MODEL', DOKU_LIB_IOC."wikiiocmodel/");

class exportDocument extends renderHtmlDocument {

    private static $extendedData;

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
    
    public function process($data, $alias="") {
        session_start();
        $_SESSION['sectionElement'] = FALSE;
        $ret = parent::process($data, $alias);
        session_write_close();
        $this->cocinaElPdfEntero(self::$extendedData, $ret);
        $this->addCSSfiles($ret);
        return $ret;
    }

    /**
     * Renderiza los elementos extra
     * @param array $item Datos de la propiedad 'extraFields' del configRender.json
     * @return array Documentos renderizados
     */
    public function processExtraFields($item) {
        $arrayDeDatosExtra = [];
        if ($item["valueType"] == "arrayDocuments") {
            $typedefKeyField = $this->getTypedefKeyField($item["value"]);
            $renderKeyField = $this->getRenderKeyField($item["name"]);
            $render = $this->createRender($typedefKeyField, $renderKeyField);
            $render->init($item["name"], $renderKeyField['render']['styletype']);

            $arrayDataField = json_decode($this->getDataField($item["value"]), true);
            foreach ($arrayDataField as $key) {
                $arrDataField[] = $key['nom'];
            }

            if ($item["type"] == "psdom") {
                $this->_createSessionStyle($renderKeyField['render']);
                $jsonDoc = $render->process($arrDataField, $item["name"]);
                $this->_destroySessionStyle();
                if (!empty($jsonDoc)) {//evita procesar los documentos inexistentes
                    $arrayDeDatosExtra[$item['name']] = $jsonDoc;
                }
            }else {
                // Renderiza cada uno de los documentos
                $htmlDocument = "";
                foreach ($arrDataField as $doc) {
                    $this->_createSessionStyle($renderKeyField['render']);
                    $htmlDocument = $render->process($doc, $item["name"]);
                    $this->_destroySessionStyle();
                    if (!empty($htmlDocument)) {//evita procesar los documentos inexistentes
                        $arrayDeDatosExtra['arrayDocuments'][$doc][$item['name']] = $htmlDocument;
                        $toc[$doc] = $this->cfgExport->toc[$item["name"]];
                        $latexImg[$doc] = $this->cfgExport->latex_images;
                        $this->cfgExport->latex_images = array();
                        $mediaFiles[$doc] = $this->cfgExport->media_files;
                        $this->cfgExport->media_files = array();
                        $graphvizImg[$doc]= $this->cfgExport->graphviz_images;
                        $this->cfgExport->graphviz_images = array();
                        $gifImg[$doc] = $this->cfgExport->gif_images;
                        $this->cfgExport->gif_images = array();
                        $figRef[$doc] = $this->cfgExport->figure_references;
                        $this->cfgExport->figure_references = array();
                        $tabRef[$doc] = $this->cfgExport->table_references;
                        $this->cfgExport->table_references = array();
                    }
                }
                $this->cfgExport->toc = $toc;

                $this->cfgExport->latex_images = $latexImg;
                $this->cfgExport->media_files = $mediaFiles;
                $this->cfgExport->graphviz_images = $graphvizImg;
                $this->cfgExport->gif_images = $gifImg;
                $this->cfgExport->figure_references= $figRef;
                $this->cfgExport->table_references= $tabRef;
            }
        }
        else {
            $arrayDeDatosExtra = parent::processExtraFields($item);
        }
        return $arrayDeDatosExtra;
    }

    //Añade ficheros css, locales y básicos, a la lista de ficheros a copiar al directorio 'media'
    private function addCSSfiles(&$ret) {
        //arxius css del projecte
        $pathTemplate = "{$this->cfgExport->rendererPath}/xhtml/exportDocument/templates/css";
        $scdir = scandir($pathTemplate);
        $scdir = array_diff($scdir, [".", ".."]);
        if (!empty($scdir)) {
            foreach($scdir as $file){
                if (is_file("$pathTemplate/$file")) {
                    $ret['files'][] = "$pathTemplate/$file";
                    $ret['fileNames'][] = "css/$file";
                }
            }
        }

        //arxius css generals
        $list = $this->getDefaultCssFiles();
        if (!empty($list)) {
            foreach($list as $file){
                $ret['files'][] = $file;
                $ret['fileNames'][] = "css/".basename($file);
            }
        }
    }

    /**
     * Se crea un zip a partir de la plantilla y los archivos css, img, js, relacionados
     * El nombre del fichero a generar ya está definido en: $this->cfgExport->output_filename
     * En este modulo NO se genera archivo PDF
     * @param array $data Contiene los campos del formulario y un array con los documentos
     * @return array Lista de rutas de ficheros, de nombres de ficheros y de errores
     */
    public function cocinandoLaPlantillaConDatos($data) {
        $result = array();
        self::$extendedData = $data;
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
        $document = str_replace("@@TABLE_FOOTER_TYPE@@", $data_footer, $document);

        return $document;
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

    public function cocinaElPdfEntero($data, &$ret) {
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }
        $docs = json_decode($data["documents"], true);
        $titol = html_entity_decode(htmlspecialchars_decode($docs[0]["nom"], ENT_COMPAT|ENT_QUOTES));

        // Nom de l'atribut on s'ha desat el PDF renderitzat
        $part = "documentPartsPdf";
        $extres = $this->getRenderDef('render')['extraFields'];
        if ($extres) {
            foreach ($extres as $item) {
                if ($item['type'] == "psdom") $part = $item['name'];
            }
        }

        $params = array(
            "id" => $this->cfgExport->id,
            "tmp_dir" => $this->cfgExport->tmp_dir,    //directori temporal on crear el pdf
            "lang" => strtoupper($this->cfgExport->lang),  // idioma usat (CA, EN, ES, ...)
            "mode" => isset($this->mode) ? $this->mode : $this->filetype,
    	    "max_img_size" => ($data['max_img_size']) ? $data['max_img_size'] : WikiGlobalConfig::getConf('max_img_size', 'wikiiocmodel'),
            "style" => $this->cfgExport->rendererPath."/pdf/exportDocument/styles/main.stypdf",
            "data" => array(
                "header" => ["logo"  => $this->cfgExport->rendererPath . "/resources/escutGene.jpg",
                             "wlogo" => 9.9,
                             "hlogo" => 11.1,
                             "ltext" => "Generalitat de Catalunya\nDepartament d'Educació\nInstitut Obert de Catalunya"],
                "peu" => ["titol" => $titol],
                "contingut" => json_decode($data[$part], TRUE)   //contingut latex ja rendaritzat
            )
        );

        $filenamepdf = "activityutil.pdf";
        $pdfRenderer = new PdfRenderer();
        $pdfRenderer->renderDocument($params, $filenamepdf);
        
        $ret["files"][] = "{$this->cfgExport->tmp_dir}/$filenamepdf";
        $ret["fileNames"][] = $filenamepdf;
        $ret["info"][] = "fitxer $filenamepdf creat correctement";
    }

}
