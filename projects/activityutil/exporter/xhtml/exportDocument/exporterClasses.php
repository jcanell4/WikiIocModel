<?php
/**
 * projecte: activityutil
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
        if ($params){
            $this->cfgExport->id = $params['id'];
            $this->cfgExport->lang = (!isset($params['ioclanguage']))?'ca':strtolower($params['ioclanguage']);
            $this->cfgExport->lang = preg_replace('/\n/', '', $this->cfgExport->lang);
            $this->log = isset($params['log']);
        }
        $this->cfgExport->export_html = TRUE;
        parent::initParams();
    }
    
    public function process($data) {
        session_start();
        $_SESSION['sectionElement'] = FALSE;
        $pdf_filename = $data['pdf_filename'];
        $data = parent::process($data);
        session_write_close();
        $data['pdf_filename'] = $pdf_filename;
        $this->cocinaElPdfEntero($data);
        $this->addCSSfiles();
        return $data;
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

    public function cocinandoLaPlantillaConDatos($data) {
        $data = $this->preCocinadoIndividual($data);
        return $data;
    }

    /**
     * Tractament específic per a la generació de fitxers, individuals, resultat de cocinandoLaPlantillaConDatos
     * @param array $data : dades ja renderitzades. La renderització del contigut de cada document està individualitzada
     *                      en $data['arrayDocuments']
     * @return array
     */
    public function preCocinadoIndividual($data) {
        $id = str_replace(':', '_', $this->cfgExport->id);
        $toc_backup = $this->cfgExport->toc;

        $latexImg_backup = $this->cfgExport->latex_images;
        $mediaFiles_backup = $this->cfgExport->media_files;
        $graphvizImg_backup = $this->cfgExport->graphviz_images;
        $gifImg_backup = $this->cfgExport->gif_images;
        $figRef_backup = $this->cfgExport->figure_references;
        $tabRef_backup = $this->cfgExport->table_references;

        foreach ($data['arrayDocuments'] as $doc => $arrayDocuments) { //para cada documento
            $this->cfgExport->toc = [];
            foreach ($arrayDocuments as $name => $value) { //para cada tipo: pdf, html
                $data[$name] = $value;
                $this->cfgExport->toc[$name] = $toc_backup[$doc];
            }
            $this->cfgExport->output_filename = "{$id}_{$doc}";
            $this->cfgExport->latex_images = $latexImg_backup[$doc];
            $this->cfgExport->media_files = $mediaFiles_backup[$doc];
            $this->cfgExport->graphviz_images = $graphvizImg_backup[$doc];
            $this->cfgExport->gif_images = $gifImg_backup[$doc];
            $this->cfgExport->figure_references = $figRef_backup[$doc];
            $this->cfgExport->table_references = $tabRef_backup[$doc];

            $result[$this->cfgExport->output_filename] = $this->cocinadoIndividual($data);
        }

        $this->cfgExport->toc = $toc_backup ;
        $this->cfgExport->latex_images = $latexImg_backup ;
        $this->cfgExport->media_files = $mediaFiles_backup ;
        $this->cfgExport->graphviz_images = $graphvizImg_backup ;
        $this->cfgExport->gif_images = $gifImg_backup ;
        $this->cfgExport->figure_references = $figRef_backup ;
        $this->cfgExport->table_references = $tabRef_backup ;

        $ret['tmp_dir'] = $this->cfgExport->tmp_dir;
        foreach ($result as $value) {
            if ($value['error']) {
                $ret['error'][] = $value['error'];
            }else {
                $ret['files'][] = $value['file'];
                $ret['fileNames'][] = $value['fileName'];
            }
            $ret['info'][] = $value['info'];
        }
        $this->setResultFileList($ret);

        return $data;
    }

    /**
     * Se crea un zip a partir de la plantilla y los archivos css, img, js, relacionados
     * El nombre del fichero a generar ya está definido en: $this->cfgExport->output_filename
     * En este modulo NO se genera archivo PDF
     * @param array $data Contiene los campos del formulario y un array con los documentos
     * @return array Lista de rutas de ficheros, de nombres de ficheros y de errores
     */
    public function cocinadoIndividual($data) {
        $result = array();
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
        $data_footer = ($data["estil"] == "boostioc") ? "toTitle" : "default";
        $document = str_replace("@@FIGURE_FOOTER_TYPE@@", $data_footer, $document);
        $document = str_replace("@@TABLE_FOOTER_TYPE@@", $data_footer, $document);
        return $document;
    }

    public function cocinaElPdfEntero($data) {
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }
        $docs = json_decode($data["documents"], true);
        $titol = html_entity_decode(htmlspecialchars_decode($docs[0]["descripcio"], ENT_COMPAT|ENT_QUOTES));
        $nom_real = html_entity_decode(htmlspecialchars_decode($data["nom_real"], ENT_COMPAT|ENT_QUOTES));
        $data_fitxer = html_entity_decode(htmlspecialchars_decode($data["data_fitxercontinguts"], ENT_COMPAT|ENT_QUOTES));
        $entitat_responsable = html_entity_decode(htmlspecialchars_decode($data["entitatResponsable"], ENT_COMPAT|ENT_QUOTES));
        $pdf_part = $this->getNamePdfDocument("documentPartsPdf");

        $params = array(
            "id" => $this->cfgExport->id,
            "path_templates" => $this->cfgExport->rendererPath."/xhtml/exportDocument/templates",  // directori on es troben les plantilles latex usades per crear el pdf
            "tmp_dir" => $this->cfgExport->tmp_dir,    //directori temporal on crear el pdf
            "lang" => strtoupper($this->cfgExport->lang),
            "mode" => isset($this->mode) ? $this->mode : $this->filetype,
    	    "max_img_size" => ($data['max_img_size']) ? $data['max_img_size'] : WikiGlobalConfig::getConf('max_img_size', 'wikiiocmodel'),
            "style" => $this->cfgExport->rendererPath."/xhtml/exportDocument/pdf/main.stypdf",
            "data" => array(
                "header" => ["logo"  => $this->cfgExport->rendererPath."/resources/escutGene.jpg",
                             "wlogo" => 9.9,
                             "hlogo" => 11.1,
                             "ltext" => "Generalitat de Catalunya\nDepartament d'Educació\nInstitut Obert de Catalunya",
                             "rtext" => $titol
                            ],
                "peu" => ['titol' => $titol,
                          'autor' => $data['mostrarAutor']==="true" || $data['mostrarAutor']===true ? $nom_real : "",
                          'entitatResponsable' => $entitat_responsable,
                          'data'  => $data_fitxer
                         ],
                "contingut" => json_decode($data[$pdf_part], TRUE)   //contingut latex ja rendaritzat
            )
        );

        $pdfRenderer = new PdfRenderer();
        $pdfRenderer->renderDocument($params, $data['pdf_filename']);
        
        $ret = $this->getResultFileList();
        $ret["files"][] = "{$this->cfgExport->tmp_dir}/{$data['pdf_filename']}";
        $ret["fileNames"][] = $data['pdf_filename'];
        $ret["info"][] = "fitxer {$data['pdf_filename']} creat correctement";
        $this->setResultFileList($ret);
    }

    //Añade ficheros css, locales y básicos, a la lista de ficheros a copiar al directorio 'media'
    private function addCSSfiles() {
        $ret = $this->getResultFileList();
        
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
        $this->setResultFileList($ret);
    }

    // Nom de l'atribut on s'ha de desar el PDF renderitzat
    private function getNamePdfDocument($nom = "documentPartsPdf") {
        $extres = $this->getRenderDef('render')['extraFields'];
        if ($extres) {
            foreach ($extres as $item) {
                if ($item['type'] == "psdom") {
                    $nom = $item['name'];
                }
            }
        }
        return $nom;
    }

}
