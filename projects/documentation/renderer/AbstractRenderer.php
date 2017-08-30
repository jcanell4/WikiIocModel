<?php
/**
 * AbstractRenderer: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
require_once DOKU_INC."inc/parser/xhtml.php";
require_once DOKU_PLUGIN."iocexportl/lib/renderlib.php";

abstract class AbstractRenderer {

    protected $factory;
    protected $extra_data;
    protected $RUTA_RENDERER;

    public function __construct($factory) {
        $this->factory = $factory;
        $this->RUTA_RENDERER = dirname(realpath(__FILE__));
    }

    public function init($extra) {
        $this->extra_data = $extra;
    }

    public function loadTemplateFile($file) {
        $tmplt = @file_get_contents("{$this->RUTA_RENDERER}/$file");
        if ($tmplt == FALSE) throw new Exception("Error en la lectura de l'arxiu de plantilla: $file");
        return $tmplt;
    }

    public function isEmptyArray($param) {
        $vacio = TRUE;
        foreach ($param as $value) {
            if (is_array($value))
                $vacio &= $this->isEmptyArray($value);
            else
                $vacio &= empty($value);
        }
        return $vacio;
    }
}

class renderField extends AbstractRenderer {

    public function process($data) {
        $ret = "<label>$this->extra_data:</label>&nbsp;<span>$data</span>";
        return $ret;
    }
}

class renderFile extends AbstractRenderer {

    public function process($data) {
        $text = io_readFile(wikiFN($data));
        $instructions = get_latex_instructions($text);
        $html = p_latex_render('docxhtml', $instructions, $info);
        return $html;
    }
}

abstract class renderComposite extends AbstractRenderer {
    protected $typedef = array();
    protected $renderdef = array();
    /**
     * @param array $typedef : tipo (objeto en configMain.json) correspondiente al campo actual en $data
     * @param array $renderdef : tipo (objeto en configRender.json) correspondiente al campo actual en $data
     */
    public function __construct($factory, $typedef, $renderdef) {
        parent::__construct($factory);
        $this->typedef = $typedef;
        $this->renderdef = $renderdef;
    }

    public function createRender($typedef=NULL, $renderdef=NULL) {
        return $this->factory->createRender($typedef, $renderdef);
    }
    public function getTypesDefinition($key = NULL) {
        return $this->factory->getTypesDefinition($key);
    }
    public function getTypesRender($key = NULL) {
        return $this->factory->getTypesRender($key);
    }
    public function getTypeDef($key = NULL) {
        return ($key === NULL) ? $this->typedef : $this->typedef[$key];
    }
    public function getRenderDef($key = NULL) {
        return ($key === NULL) ? $this->renderdef : $this->renderdef[$key];
    }
    public function getTypedefKeyField($field) { //@return array : objeto key solicitado (del configMain.json)
        return $this->getTypeDef('keys')[$field];
    }
    public function getRenderKeyField($field) { //@return array : objeto key solicitado (del configRender.json)
        return $this->getRenderDef('keys')[$field];
    }
}

class renderObject extends renderComposite {

    protected $data = array();
    /**
     * @param array $data : array correspondiente al campo actual del archivo de datos del proyecto
     * @return datos renderizados
     */
    public function process($data) {
        $this->data = $data;
        $ret = $this->process_header();
        $ret.= $this->process_body();
        $ret.= $this->process_footer();
        return $ret;
    }

    public function process_header() {
        $ret = "<div>\n";
        return $ret;
    }
    public function process_body() {
        $campos = $this->getRenderFields();
        foreach ($campos as $keyField) {
            $typedefKeyField = $this->getTypedefKeyField($keyField);
            $renderKeyField = $this->getRenderKeyField($keyField);
            $render = $this->createRender($typedefKeyField, $renderKeyField);

            $dataField = $this->getDataField($keyField);
            $render->init($keyField);
            $arrayDeDatosParaLaPlantilla[$keyField] = $render->process($dataField);
        }
        $ret = $this->cocinandoLaPlantillaConDatos($arrayDeDatosParaLaPlantilla);
        return $ret;
    }
    public function process_footer() {
        $ret = "</div>\n";
        return $ret;
    }

    public function getRenderFields() { //devuelve el array de campos establecidos para el render
        return $this->getRenderDef('render')['fields'];
    }
    public function getDataField($key = NULL) {
        return ($key === NULL) ? $this->data : $this->data[$key];
    }
    public function cocinandoLaPlantillaConDatos($param) {
        if (is_array($param)) {
            foreach ($param as $value) {
                $ret .= (is_array($value)) ? $this->cocinandoLaPlantillaConDatos($value)."\n" : $value."\n";
            }
        }else {
            $ret = $param;
        }
        return $ret;
    }
}

class renderArray extends renderComposite {

    public function process($data) {
        $ret = "";
        $filter = $this->getFilter();
        $itemType = $this->getItemsType();
        $render = $this->createRender($this->getTypesDefinition($itemType), $this->getTypesRender($itemType));
        //cada $item es un array de tipo concreto en el archivo de datos
        foreach ($data as $key => $item) {
            if ($filter === "*" || in_array($key, $filter)) {
                $ret .= $render->process($item);
            }
        }
        return $ret;
    }

    protected function getItemsType() {
        return $this->getTypeDef('itemsType'); //tipo al que pertenecen los elementos del array
    }
    protected function getFilter() {
        return $this->getRenderDef('render')['filter'];
    }
}

class render_material extends renderObject {

    public function process_body() {
        $campos = $this->getRenderFields();
        foreach ($campos as $keyField) {
            $typedefKeyField = $this->getTypedefKeyField($keyField);
            $renderKeyField = $this->getRenderKeyField($keyField);
            $render = $this->createRender($typedefKeyField, $renderKeyField);

            $dataField = $this->getDataField($keyField);
            $render->init($keyField);
            $arrayDeDatosParaLaPlantilla[$keyField] = $render->process($dataField);
        }
        $ret = $this->cocinandoLaPlantillaConDatos($arrayDeDatosParaLaPlantilla);
        return $ret;
    }

    public function cocinandoLaPlantillaConDatos($param) {
        /*
        if (is_array($param))
            foreach ($param as $key => $value)
                $ret .= (is_array($value)) ? $this->cocinandoLaPlantillaConDatos($value)."\n" : $value."\n";
        else
            $ret = $param;
        return $ret;
        */
        $tmplt = $this->loadTemplateFile('xhtml/renderDocument.html');
        $aSearch = array('@DIV_ID@','@TITLE_VALUE@','@AUTOR_VALUE@','@RESPONSABLE_VALUE@','@CONTINGUTS_VALUE@');
        $aReplace = array_merge(array("id_div_document"), $param);
        $document = str_replace($aSearch, $aReplace, $tmplt);
        return $document;
    }
}
