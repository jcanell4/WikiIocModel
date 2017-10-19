<?php
/**
 * renderDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL."projects/documentation/");

class exportDocument extends MainRender {
    protected $tmpPath;
    public function __construct($factory, $typedef, $renderdef, $params=NULL) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->initParams($params);
    }
    
    public function initParams($params=NULL){
        @set_time_limit(240);
        $this->time_start = microtime(TRUE);
        $this->langDir = dirname(__FILE__)."/lang/";
        if($params){
            $this->id = $params['id'];
            $this->lang = (!isset($params['ioclanguage']))?'ca':strtolower($params['ioclanguage']);
            $this->lang = preg_replace('/\n/', '', $this->lang);
            $this->log = isset($params['log']);
        }
        $this->export_html = TRUE;
        $this->tmp_dir = rand();
        $this->latex_images = array();
        $this->media_files = array();
        $this->graphviz_images = array();
        $this->tmpPath = DOKU_PLUGIN.'tmp/latex/';
        
        parent::initParams();
    }

    public function cocinandoLaPlantillaConDatos($data) {
        $result=array();

        $output_filename = str_replace(':','_',$this->id);

        if (!file_exists($this->tmpPath. $this->tmp_dir)){
            mkdir($this->tmpPath. $this->tmp_dir, 0775, TRUE);
        }

        $zip = new ZipArchive;
        $zipPath = $this->tmpPath. $this->tmp_dir.'/'.$output_filename.'.zip';
        $res = $zip->open($zipPath, ZipArchive::CREATE);
        if ($res === TRUE) {
            $tmplt = $this->loadTemplateFile('xhtml/exportDocument/documentation.html');
            $aSearch = array('@DIV_ID@', '@LANG','@PAGE_TITLE@', '@TITLE_VALUE@','@AUTOR_VALUE@','@RESPONSABLE_VALUE@','@CONTINGUTS_VALUE@');
            $aReplace = array_merge(array("id_div_document", $this->lang, $data[0]), $data);
            $document = str_replace($aSearch, $aReplace, $tmplt); 
            $zip->addFromString('index.html', $document);
            $result["zipFile"] = $zipPath;
            $result["zipName"] = $output_filename.".zip";
            $result["info"] = "fitxer {$result['zipName']} creat correctement";
        }else{
            $result['error'] = true;
            $result['info'] = $this->lang['nozipfile'];
        }
        $zip->addEmptyDir("css");
        $zip->addFile("{$this->RUTA_RENDERER}/xhtml/renderDocument/documentation.css", "css/documentation.css");
        $zip->close();
        return $result;
    }
}

class render_title extends renderField {
    public function process($data) {
        $ret = "<h1 class='title'>$data</h1>";
        return $ret;
    }
}
