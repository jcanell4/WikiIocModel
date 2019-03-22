<?php
/**
 * BasicExporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                       correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
if (!defined('EXPORT_TMP')) define('EXPORT_TMP', DOKU_PLUGIN."tmp/latex/");

abstract class AbstractRenderer {
    protected $factory;
    protected $cfgExport;
    protected $extra_data;
    protected $mode;
    protected $filetype;

    public function __construct($factory, $cfgExport=NULL) {
        $this->factory = $factory;
        $this->mode = $factory->getMode();
        $this->filetype = $factory->getFileType();
        if ($cfgExport) {
            $this->cfgExport = $cfgExport;
        }else {
            $this->cfgExport = new cfgExporter();
        }
    }

    public function getTocs() {
        return $this->cfgExport->tocs;
    }

    public function init($extra) {
        $this->extra_data = $extra;
    }

    public function loadTemplateFile($file) {
        $tmplt = @file_get_contents("{$this->cfgExport->rendererPath}/$file");
        if ($tmplt == FALSE) throw new Exception("Error en la lectura de l'arxiu de plantilla: $file");
        return $tmplt;
    }

    public function isEmptyArray($param) {
        $vacio = TRUE;
        foreach ($param as $value) {
            $vacio &= (is_array($value)) ? $this->isEmptyArray($value) : empty($value);
        }
        return $vacio;
    }
}

class cfgExporter {
    public $id;
    public $aLang = array(); //cadenes traduïdes
    public $lang = 'ca';     //idioma amb el que es treballa
    public $langDir;         //directori amb cadenes traduïdes
    public $gif_images = array();
    public $graphviz_images = array();
    public $latex_images = array();
    public $media_files = array();
    public $export_html = TRUE;
    public $permissionToExport = TRUE;
    public $tmp_dir;
    public $rendererPath;
    public $toc = NULL;
    public $tocs = array();

    public function __construct() {
        $this->tmp_dir = realpath(EXPORT_TMP)."/".rand();;
    }
}

abstract class renderComposite extends AbstractRenderer {
    protected $typedef = array();
    protected $renderdef = array();
    /**
     * @param array $typedef : tipo (objeto en configMain.json) correspondiente al campo actual en $data
     * @param array $renderdef : tipo (objeto en configRender.json) correspondiente al campo actual en $data
     */
    public function __construct($factory, $typedef, $renderdef, $cfgExport=NULL) {
        parent::__construct($factory, $cfgExport);
        $this->typedef = $typedef;
        $this->renderdef = $renderdef;
    }

    public function createRender($typedef=NULL, $renderdef=NULL) {
        return $this->factory->createRender($typedef, $renderdef, $this->cfgExport);
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
//        $ret = $this->getTypeDef('keys')[$field];
//        //No comprendo por qué hay que añadir el type si ya del objeto se recoge el objeto completo
//        while($this->getTypesDefinition($ret["type"])){
//            $ret = array_merge($ret, $this->getTypesDefinition($ret["type"]));
//        }
//        return $ret;
    }
    public function getRenderKeyField($field) { //@return array : objeto key solicitado (del configRender.json)
        return $this->getRenderDef('keys')[$field];
    }
}

class BasicRenderObject extends renderComposite {

    protected $data = array();

    /**
     * @param array $data : array correspondiente al campo actual del archivo de datos del proyecto
     *                      (o array con todos los campos del proyecto)
     * @return datos renderizados
     */
    public function process($data) {
        $this->data = $data;
        $campos = $this->getRenderFields();
        foreach ($campos as $keyField) {
            $typedefKeyField = $this->getTypedefKeyField($keyField);
            $renderKeyField = $this->getRenderKeyField($keyField);
            $render = $this->createRender($typedefKeyField, $renderKeyField);

            $dataField = $this->getDataField($keyField);
            $render->init($keyField);
            $arrayDeDatosParaLaPlantilla[$keyField] = $render->process($dataField);
        }
        $extres = $this->getRenderExtraFields();
        if ($extres) {
            foreach ($extres as $item) {
                if ($item["valueType"] == "field") {
                    $typedefKeyField = $this->getTypedefKeyField($item["value"]);
                    $renderKeyField = $this->getRenderKeyField($item["name"]);
                    $render = $this->createRender($typedefKeyField, $renderKeyField);

                    $dataField = $this->getDataField($item["value"]);
                    $render->init($item["name"]);

                    $arrayDeDatosParaLaPlantilla[$item["name"]] = $render->process($dataField, $item["name"]);
                }
            }
        }

        $ret = $this->cocinandoLaPlantillaConDatos($arrayDeDatosParaLaPlantilla);
        return $ret;
    }

    public function getRenderFields() { //devuelve el array de campos establecidos para el render
        $ret = $this->getRenderDef('render')['fields'];
        if (!isset($ret) && $this->factory->getDefaultValueForObjectFields()) {
            $ret = $this->factory->getDefaultValueForObjectFields();
        }
        if (is_string($ret)) {
            switch (strtoupper($ret)) {
                case "ALL":
                    if (!isset($this->typedef["keys"])) {
                        $this->typedef = $this->factory->getTypesDefinition($this->typedef["typeDef"]);
                    }
                    $ret = array_keys($this->typedef["keys"]);
                    break;
                case "MANDATORY":
                    $ret = array();
                    $allKeys = array_keys($this->typedef["keys"]);
                    foreach ($allKeys as $key) {
                        if ($this->typedef["keys"][$key]["mandatory"]) {
                            $ret [] = $key;
                        }
                    }
                    break;
            }
        }
        return $ret;
    }

    public function getRenderExtraFields() { //devuelve el array de campos establecidos para el render
        return $this->getRenderDef('render')['extraFields'];
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
