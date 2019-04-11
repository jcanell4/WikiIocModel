<?php
/**
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
require_once DOKU_INC."inc/parserutils.php";

class MainRender extends renderObject {
    protected $max_menu;
    protected $max_navmenu;
    protected $media_path = 'lib/exe/fetch.php?media=';
    protected $menu_html = '';
    protected $tree_names = array();
    protected $web_folder = 'WebContent';
    protected $initialized = FALSE;

    public function initParams(){
        $langFile = $this->cfgExport->langDir.$this->cfgExport->lang.'.conf';
        if (!file_exists($langFile)){
            $this->cfgExport->lang = 'ca';
            $langFile = $this->cfgExport->langDir.$this->cfgExport->lang.'.conf';
        }
        if (file_exists($langFile)) {
            $this->cfgExport->aLang = confToHash($langFile);
        }
        $this->initialized = TRUE;
    }
}

class renderDate extends AbstractRenderer {
    private $sep;

    public function __construct($factory, $cfgExport=NULL, $sep="-") {
        parent::__construct($factory, $cfgExport);
        $this->sep = $sep;
    }

    public function process($date) {
        $dt = strtotime(str_replace('/', '-', $date));
        return date("d". $this->sep."m".$this->sep."Y", $dt);
    }

}

class renderText extends AbstractRenderer {

    public function process($data) {
        return htmlentities($data, ENT_QUOTES);
    }
}

class renderField extends AbstractRenderer {

    public function process($data) {
        return $data;
    }
}

class renderRenderizableText extends AbstractRenderer {

    public function process($data) {
        $instructions = p_get_instructions($data);
        $html = p_render('wikiiocmodel_ptxhtml', $instructions, $info);
        return $html;
    }
}

class renderFileToPsDom extends renderFile {

    protected function render($instructions, &$renderData){
        return p_latex_render('wikiiocmodel_psdom', $instructions, $renderData);
    }
}

class renderFile extends AbstractRenderer {

    public function process($data, $alias="") {
        global $plugin_controller;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            $startedHere = true;
        }
        $_SESSION['export_html'] = $this->cfgExport->export_html;
        $_SESSION['tmp_dir'] = $this->cfgExport->tmp_dir;
        $_SESSION['latex_images'] = &$this->cfgExport->latex_images;
        $_SESSION['media_files'] = &$this->cfgExport->media_files;
        $_SESSION['graphviz_images'] = &$this->cfgExport->graphviz_images;
        $_SESSION['gif_images'] = &$this->cfgExport->gif_images;
        $_SESSION['alternateAddress'] = TRUE;
        $_SESSION['dir_images'] = "img/";
        $_SESSION['styletype'] = $this->cfgExport->styletype;

        if (preg_match("/".$this->cfgExport->id."/", $data)!=1){
            $fns = $this->cfgExport->id.":".$data;
        }
        $file = wikiFN($fns);
        $text = io_readFile($file);

        $counter = 0;
        $text = preg_replace("/~~USE:WIOCCL~~\n/", "", $text, 1, $counter);
        if ($counter>0){
            $dataSource = $plugin_controller->getCurrentProjectDataSource($this->cfgExport->id, $plugin_controller->getCurrentProject());
            $text = WiocclParser::getValue($text, [], $dataSource);
        }

        $instructions = p_get_instructions($text);
        $renderData = array();
        $html = $this->render($instructions, $renderData);

        if (empty($alias)) $alias = $data;
        $this->cfgExport->toc[$alias] = $renderData["tocItems"];
        if ($startedHere) session_destroy();

        return $html;
    }

    protected function render($instructions, &$renderData){
        return p_render('wikiiocmodel_ptxhtml', $instructions, $renderData);
    }
}
