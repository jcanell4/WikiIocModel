<?php
/**
 * renderDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL."projects/documentation/");
require_once WIKI_IOC_PROJECT."renderer/AbstractRenderer.php";

class renderDocument extends MainRender {
    
    public function __construct($params, $factory, $typedef, $renderdef) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->initParams($params);
    }
    
    public function initParams($params=NULL){
        if($params){
            $this->id = $params['id'];
            $this->lang = (!isset($params['ioclanguage']))?'ca':strtolower($params['ioclanguage']);
            $this->lang = preg_replace('/\n/', '', $lang);
            $this->log = isset($params['log']);
        }
        parent::initParams();
    }

    public function process($data) {
        @set_time_limit(240);

        $this->time_start = microtime(TRUE);

        $output_filename = str_replace(':','_',$this->id);

        $this->export_html = TRUE;
        $tmp_dir = rand();
        $this->tmp_dir = $tmp_dir;
        $this->latex_images = array();
        $this->media_files = array();
        $this->graphviz_images = array();
        if (!file_exists(DOKU_PLUGIN.'tmp/latex/'.$tmp_dir)){
            mkdir(DOKU_PLUGIN.'tmp/latex/'.$tmp_dir, 0775, TRUE);
        }

        /*
        //get all pages and activitites
        $data = $this->getData();
        */
         $zip = new ZipArchive;
        $res = $zip->open(DOKU_IOCEXPORTL_LATEX_TMP.$tmp_dir.'/'.$output_filename.'.zip', ZipArchive::CREATE);
        if ($res === TRUE) {
            
        }
        
        
        
        $tmplt = $this->loadTemplateFile('xhtml/renderDocument.html');
        $aSearch = array('@DIV_ID@','@TITLE_VALUE@','@AUTOR_VALUE@','@RESPONSABLE_VALUE@','@CONTINGUTS_VALUE@');
        $aReplace = array_merge(array("id_div_document"), $data);
        $document = str_replace($aSearch, $aReplace, $tmplt);
        return $document;
    }
}
