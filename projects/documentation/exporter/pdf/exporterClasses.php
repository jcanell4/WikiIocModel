<?php
/**
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN."wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL."projects/documentation/");
if (!defined('IOC_DOCU_LATEX_TEMPLATES')) define('IOC_DOCU_LATEX_TEMPLATES', WIKI_IOC_PROJECT."exporter/pdf/");
require_once WIKI_IOC_PROJECT."exporter/exporterClasses.php";

class MainRender extends renderObject {

    protected $id;
    protected $lang;
    protected $ioclangcontinue;
    protected $path_templates;
    protected $coverImage;

    public function __construct($factory, $typedef, $renderdef) {
        parent::__construct($factory, $typedef, $renderdef);
    }

    public function initParams(){
        $this->ioclangcontinue = array('CA'=>'continuació', 'DE'=>'fortsetzung', 'EN'=>'continued','ES'=>'continuación','FR'=>'suite','IT'=>'continua');
        $this->path_templates = IOC_DOCU_LATEX_TEMPLATES.$this->factory->getDocumentClass()."/templates";
    }
}

class renderField extends AbstractRenderer {
    public function process($data) {
        $ret = "$data";
        return $ret;
    }
}

class render_title extends renderField {
    public function process($data) {
        $ret = "$data";
        return $ret;
    }
}

class renderFile extends AbstractRenderer {
    public function process($data) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            $startedHere = true;
        }
        $_SESSION['export_latex'] = $this->export_latex;
        $_SESSION['tmp_dir'] = $this->tmp_dir;
        $_SESSION['latex_images'] = &$this->latex_images;
        $_SESSION['media_files'] = &$this->media_files;
        $_SESSION['graphviz_images'] = &$this->graphviz_images;


        $text = io_readFile(wikiFN($data));
        $instructions = p_get_instructions($text);
        $renderData = array();
        $latex = p_render('wikiiocmodel_basiclatex', $instructions, $renderData);

        if ($startedHere) session_destroy();

        return $latex;
    }
}
