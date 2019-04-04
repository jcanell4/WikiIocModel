<?php
/**
 * MainRender: clases de procesos render para export
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
require_once(DOKU_PLUGIN.'iocexportl/lib/renderlib.php');
//define('WIKI_IOC_PROJECT', DOKU_PLUGIN."wikiiocmodel/projects/ptfploe/");
//require_once WIKI_IOC_PROJECT."exporter/exporterClasses.php";

class MainRender extends renderObject {

    protected $ioclangcontinue;
    protected $path_templates;

    public function __construct($factory, $typedef, $renderdef) {
        parent::__construct($factory, $typedef, $renderdef);
    }

    public function initParams(){
        $this->ioclangcontinue = array('CA'=>'continuació', 'DE'=>'fortsetzung', 'EN'=>'continued','ES'=>'continuación','FR'=>'suite','IT'=>'continua');
        $this->path_templates = WIKI_IOC_PROJECT."exporter/pdf/".$this->factory->getDocumentClass()."/templates";
    }

    /**
     * Replace all reserved symbols
     * @param string $text
     */
    public function clean_accent_chars($text){
        $source_char = array('á', 'é', 'í', 'ó', 'ú', 'à', 'è', 'ò', 'ï', 'ü', 'ñ', 'ç','Á', 'É', 'Í', 'Ó', 'Ú', 'À', 'È', 'Ò', 'Ï', 'Ü', 'Ñ', 'Ç','\\\\');
        $replace_char = array("\'{a}", "\'{e}", "\'{i}", "\'{o}", "\'{u}", "\`{a}", "\`{e}", "\`{o}", '\"{i}', '\"{u}', '\~{n}', '\c{c}', "\'{A}", "\'{E}", "\'{I}", "\'{O}", "\'{U}", "\`{A}", "\`{E}", "\`{O}", '\"{I}', '\"{U}', '\~{N}', '\c{C}','\break ');
        return str_replace($source_char, $replace_char, $text);
    }
}

class renderField extends AbstractRenderer {
    public function process($data) {
        return MainRender::clean_accent_chars($data);
    }
}

class render_title extends renderField {
    public function process($data) {
        $ret = parent::process($data);
        return $ret;
    }
}

class renderFile extends AbstractRenderer {
    public function process($data) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            $startedHere = true;
        }
        $_SESSION['tmp_dir'] = $this->cfgExport->tmp_dir;
        //las siguientes variables están pendientes de revisión para establecer la necesidad de su existencia
        $_SESSION['latex_images'] = &$this->cfgExport->latex_images;
        $_SESSION['media_files'] = &$this->cfgExport->media_files;
        $_SESSION['graphviz_images'] = &$this->cfgExport->graphviz_images;
        $_SESSION['gif_images'] = &$this->cfgExport->gif_images;

        $text = io_readFile(wikiFN($data));
        $info = array();
        $instructions = get_latex_instructions($text);
        $latex = p_latex_render('wikiiocmodel_basiclatex', $instructions, $info);

        if ($startedHere) session_destroy();
        return $latex;
    }
}
