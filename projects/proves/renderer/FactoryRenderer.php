<?php
/**
 * FactoryRenderer
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
require_once WIKI_IOC_MODEL."renderer/AbstractFactoryRenderer.php";

//class FactoryRenderer extends AbstractFactoryRenderer {
//
//    const PATH_RENDERER = WIKI_IOC_MODEL."projects/documentation/renderer/";
//
//    protected function __construct() {
//        parent::__construct(self::PATH_RENDERER);
//    }
//}

class FactoryRenderer {

    protected $typesDefinition = array();
    protected $typesRender = array();
    /**
     * @param array $typesDefinition : array con todos los tipos (clave 'typesDefinition') del archivo configMain.json
     * @param array $typesRender : array con todos los tipos del archivo configRender.json
     */
    public function __construct($typesDefinition, $typesRender) {
        $this->typesDefinition = $typesDefinition;
        $this->typesRender = $typesRender;
    }

    /**
     * @param array $typedef : tipo (objeto en configMain.json) correspondiente al campo actual en $data
     * @param array $renderdef : tipo (objeto en configRender.json) correspondiente al campo actual en $data
     * @return instancia del render correspondiente
     */
    public function createRender($typedef=NULL, $renderdef=NULL) {
        $clase = $this->getKeyRenderClass($renderdef);
        $class = $this->validateClass($clase, $typedef['type']);

        //creamos una instancia del render correspontiente al tipo de elemento
        switch ($typedef['type']) {
            case "array":  $render = new $class($this, $typedef, $renderdef); break;
            case "object": $render = new $class($this, $typedef, $renderdef); break;
            case "file":   $render = new $class($this); break;
            default:       $render = new $class($this); break;
        }
        return $render;
    }

    public function getTypesDefinition($key = NULL) {
        return ($key === NULL) ? $this->typesDefinition : $this->typesDefinition[$key];
    }
    public function getTypesRender($key = NULL) {
        return ($key === NULL) ? $this->typesRender : $this->typesRender[$key];
    }
    private function validateClass($class, $tipo) {
        if ($class !== NULL && !class_exists($class, false)) {
            //throw new ErrorException("La clase no existe");
        }
        if ($class === NULL || !class_exists($class, false)) {
            $itemsType = $this->getTypesDefinition('itemsType'); //busca el render por defecto del tipo en configRender.json
            $class = $this->getKeyRenderClass($itemsType);
            if ($class === NULL) {
                $class = $this->defaultRenderClass($tipo); //render por defecto del tipo definido en configMain.json
            }
        }
        return $class;
    }
    private function getKeyRenderClass($renderdef) { //devuelve el nombre de la clase render
        return $renderdef['render']['class'];
    }
    /**
     * Establece la clase por defecto para cada tipo
     * @param string $tipo : tipo de objeto (string, array, object, number, etc.)
     * @return string : nombre de la clase asignada por defecto a ese tipo
     */
    private function defaultRenderClass($tipo) {
        switch ($tipo) {
            case 'array':  $ret = "renderArray"; break;
            case 'object': $ret = "renderObject"; break;
            default:       $ret = "renderField"; break;
        }
        return $ret;
    }
}

