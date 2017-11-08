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
    protected $id;
    protected $langDir;
    protected $lang = 'ca';
    protected $aLang;
    protected $max_menu;
    protected $max_navmenu;
    protected $media_path = 'lib/exe/fetch.php?media=';
    protected $menu_html = '';
    protected $tree_names = array();
    protected $web_folder = 'WebContent';
    protected $initialized = FALSE;
    protected $export_html = TRUE;


    public function initParams(){
        if (!file_exists($this->langDir.$this->lang.'.conf')){
            $this->lang = 'ca';
        }
        $this->aLang = confToHash($this->langDir.$this->lang.'.conf');
        $this->initialized=TRUE;
    }
}

class renderField extends AbstractRenderer {

    public function process($data) {
        $ret = "<span>$data</span>";
        return $ret;
    }
}

class renderFile extends AbstractRenderer {

    public function process($data) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            $startedHere = true;
        }
        $_SESSION['export_html'] = $this->export_html;
        $_SESSION['tmp_dir'] = $this->tmp_dir;
        $_SESSION['latex_images'] = &$this->latex_images;
        $_SESSION['media_files'] = &$this->media_files;
        $_SESSION['graphviz_images'] = &$this->graphviz_images;


        $text = io_readFile(wikiFN($data));
        $instructions = p_get_instructions($text);
        $renderData = array();
        $html = p_render('wikiiocmodel_basicxhtml', $instructions, $renderData);

        if ($startedHere) {
            session_destroy();
        }

        return $html;
    }
}
