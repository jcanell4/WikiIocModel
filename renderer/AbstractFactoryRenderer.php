<?php
/**
 * AbstractFactoryRenderer
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");

class AbstractFactoryRenderer {

    protected $rendererProjectPath; //ruta de los renders particulares de cada proyecto
    protected $rendererDefaultPath; //ruta de los renders por defecto
    protected $loadedRenderers = array();

    protected function __construct($rendererProjectPath=NULL) {
        $this->rendererProjectPath = $rendererProjectPath;
    }

    public static function Instance($rendererDefaultPath=NULL){
        static $inst = NULL;
        if ($inst === NULL) {
            $inst = new FactoryRenderer();
            $inst->rendererDefaultPath = $rendererDefaultPath;
        }
        return $inst;
    }

    public function getRenderer($type, $render) {
        $contentRenderer = $this->_getRendererFileName($type, $render);
        if ($contentRenderer && !$this->loadedRenderers[$type]) {
            $classe = "basic_$type";
            include_once WIKI_IOC_MODEL."renderer/$classe.php";
            $inst = new $classe;
            $inst->init($contentRenderer);
            $this->loadedRenderers[$type] = TRUE;
        }
    }

    /**
     * Devuelve el contenido del fichero de configuración específico del renderer
     * @param string $type : tipo de renderer: xhtml, latex, etc.
     * @param string $render : nombre del archivo de configuración específico del renderer
     * @return array : contenido del fichero de configuración específico del renderer
     */
    private function _getRendererFileName($type, $render) {
        $ficheros[] = $this->rendererProjectPath . $type . "/" . $render;
        $ficheros[] = $this->rendererDefaultPath . $type . "/" . $render;
        foreach ($ficheros as $file) {
            $renderMain = @file_get_contents($file);
            if ($renderMain != FALSE) {
                $renderMainArray = json_decode($renderMain, true);
                return $renderMainArray;
            }
        }
    }

}