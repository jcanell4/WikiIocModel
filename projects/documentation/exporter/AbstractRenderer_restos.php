<?php
/**
 * AbstractRenderer_restos: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
require_once DOKU_PLUGIN."ownInit/WikiGlobalConfig.php";

class AbstractRenderer_restos {

    /**
     * Ejecuta los renders definidos en el elemento process
     * @param object $process : array nominal con los nombres de los renders a ejecutar y sus parámetros
     * @return resultado ya renderizado
     */
    protected function render($process, $data, $type, $typesDefinition){
        foreach ($process as $render_process => $param) {
            $render = new $render_process($typesDefinition);
            return $render->process($param, $type, $data);
        }
    }

    public function process_render_field($value) {
        return "<span>$value</span>";
    }

    public function process_render_file($value) {
        return "<div>$value</div>";
    }

    protected function isEmptyArray($param) {
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

class render_Composite extends AbstractRenderer_restos {

    protected $main_type;
    protected $render_type;
    protected $typesDefinition;
    protected $flds_render = '';
    protected $prms_render = array();
    protected $flds_data = array();

    /**
     * @param array $flds_render : datos del campo en el archivo de configuración
     * @param array $flds_data : datos del campo en el archivo de datos del proyecto
     */
    public function __construct($flds_render, $flds_data, $mainType, $typesDefinition) {
        $this->flds_render = $flds_render['fields'][0];
        $this->prms_render = $flds_render['params'];
        $this->flds_data = $flds_data;
        $this->main_type = $mainType;
        $this->typesDefinition = $typesDefinition;
        $this->render_type = key($flds_render);
    }

    protected function getTypesDefinition($key = NULL) {
        return ($key === NULL) ? $this->typesDefinition : $this->typesDefinition[$key];
    }
    protected function getProjectCfgArray($key = NULL) {
        return ($key === NULL) ? $this->cfgArray : $this->cfgArray[$key];
    }
    protected function getProjectDataArray($key = NULL) {
        return ($key === NULL) ? $this->flds_data : $this->flds_data[$key];
    }
}

class render_Object extends render_Composite {

    public function __construct($flds_render, $flds_data, $mainType, $typesDefinition) {
        parent::__construct($flds_render, $flds_data, $mainType, $typesDefinition);
    }

    protected function process($tipo) {
        $thisType = $this->getTypesDefinition($tipo);
        foreach ($thisType['process'] as $name_render => $data_render) {
            $field_data = array();
            $field_cfg = array();
            if ($data_render['params']) {
                $field = $data_render['fields'][0];
                $renderItem = $data_render['params']['renderItem'];
                $field_data[$field] = $this->getProjectDataArray($field);
                $field_cfg[$field] = $thisType['keys'][$field];
                $field_cfg['params'] = $data_render['params'];
                $render = new $renderItem($this->typesDefinition);
                $ret .= $render->process($field_cfg, $field_data);
            }else {
                foreach ($data_render['fields'] as $field) {
                    $field_data[$field] = $this->getProjectDataArray($field);
                    $field_cfg[$field] = $thisType['keys'][$field];
                }
                if (!$this->isEmptyArray($field_data)) {
                    $render = new $name_render($this->typesDefinition);
                    $ret .= $render->process($field_cfg, $field_data);
                }
            }
        }
        return $ret;
    }
}

class render_apartats extends render_Array {

}

class render_item_blocs extends render_Array {

}

class render_bloc extends render_Array {
    /**
     * @param string|array $param : elementos establecidos en la definición del render
     * @param object $type : objeto definido en typesDefinition
     * @param array $data : array de datos del fichero de datos
     */
    public function process($param, $type, $data) {
        $itemType = $this->getTypesCollection($type['itemsType']); //tipo a procesar
        foreach ($data as $key => $value) {
            if ($param === "*" || in_array($key, $param)) {
                $ret .= $this->render($itemType['process'], $itemType, $value);
            }
        }
        return $ret;
    }
}

class render_apartat extends render_Object {
    /**
     * @param string $param : nombre del campo
     * @param object $type : tipo al que pertenece este render
     */
    public function process($param, $type, $data) {
        $renderItem = $this->getTypesCollection($type)['keys'][$param];  //sujeto del render
        $itemType = $this->getTypesCollection($renderItem['itemsType']); //tipo a procesar
    }
}

class render_Field extends AbstractRenderer_restos {

    protected $flds_render = array();
    protected $flds_data = array();

    public function __construct($flds_render, $flds_data) {
        $this->flds_render = $flds_render;
        $this->flds_data = $flds_data;
    }

    public function process() {
        foreach ($this->flds_data as $field => $value) {
            $ret .= $this->process_render_field("$field: $value") . "\n";
        }
        return $ret;
    }
}

class render_title extends render_Field {
}

class render_autoria extends render_Field {
}

class renderComposite extends AbstractRenderer_restos {

    protected $typesDefinition = array();

    /**
     * @param array $typesDefinition : array con todos los tipos (clave 'typesDefinition') del archivo de configuración
     */
    public function __construct($typesDefinition) {
        $this->typesDefinition = $typesDefinition;
    }

    protected function getTypesDefinition($key = NULL) {
        return ($key === NULL) ? $this->typesDefinition : $this->typesDefinition[$key];
    }
}

class renderObject extends renderComposite {

    protected $type = "";
    protected $render_fields = array();
    /**
     * @param string $type : tipo actual (obtenido del archivo de configuración)
     * @param array render_fields : array de los campos con render
     */
    public function __construct($type, $render_fields, $typesDefinition) {
        parent::__construct($typesDefinition);
        $this->type = $type;
        $this->render_fields = $render_fields;
    }

    protected function getType() {
        return $this->type;
    }
    protected function getRenderFields($key = NULL) {
        return ($key === NULL) ? $this->render_fields : $this->render_fields[$key];
    }
    protected function getCfgType($key = NULL) {
        return ($key === NULL) ? $this->getTypesDefinition($this->getType()) : $this->getTypesDefinition($this->getType())[$key];
    }

    /**
     * @param array $data : array correspondiente al campo actual del archivo de datos del proyecto
     * @return datos renderizados
     */
    protected function process($data) {
        $thisType = $this->getTypesDefinition($tipo);
        foreach ($thisType['process'] as $name_render => $data_render) {
            $field_data = array();
            $field_cfg = array();
            if ($data_render['params']) {
                $field = $data_render['fields'][0];
                $renderItem = $data_render['params']['renderItem'];
                $field_data[$field] = $this->getProjectDataArray($field);
                $field_cfg[$field] = $thisType['keys'][$field];
                $field_cfg['params'] = $data_render['params'];
                $render = new $renderItem($this->typesDefinition);
                $ret .= $render->process($field_cfg, $field_data);
            }else {
                foreach ($data_render['fields'] as $field) {
                    $field_data[$field] = $this->getProjectDataArray($field);
                    $field_cfg[$field] = $thisType['keys'][$field];
                }
                if (!$this->isEmptyArray($field_data)) {
                    $render = new $name_render($this->typesDefinition);
                    $ret .= $render->process($field_cfg, $field_data);
                }
            }
        }
        return $ret;
    }

}

class render_material extends renderObject {

    protected $data = array();

    protected function process_header() {
        $ret = "<div>";
        return $ret;
    }

    protected function process_body() {
        foreach ($this->getRenderFields() as $field) {
            $class = $this->getCfgType('keys')[$field]['render']['class'];
            switch ($this->getCfgType('keys')[$field]['tipus']) {
                case "array":
                    $render = new $class($field, $this->getCfgType('keys')[$field], $this->getTypesDefinition());
                    break;
                case "object":
                    $render = new $class($field, $this->getRenderFields(), $this->getTypesDefinition());
                    break;
                default:
                    $render = new $class($field);   //tipo string
                    break;
            }
            $arrayDeDatosParaLaPlantilla[$field] = $render->process($this->getDataField($field));
        }
        $ret = $this->cocinandoLaPlantillaConDatos($arrayDeDatosParaLaPlantilla);
        return $ret;
    }

    protected function process_footer() {
        $ret = "</div>";
        return $ret;
    }

    /**
     * @param array $data : array correspondiente al campo actual del archivo de datos del proyecto
     * @return datos renderizados
     */
    protected function process($data) {
        $this->data = $data;
        $ret = $this->process_header();
        $ret .= $this->process_body();
        $ret .= $this->process_footer();
        return $ret;
    }

    protected function getDataField($key = NULL) {
        return ($key === NULL) ? $this->data : $this->data[$key];
    }
}

class renderArray extends renderComposite {

    protected $field;
    protected $object_field;

    public function __construct($field, $object_field, $typesDefinition) {
        parent::__construct($typesDefinition);
        $this->field = $field;
        $this->object_field = $object_field;
    }
}

/**
 * Render de un array de blocs
 */
class render_blocs extends render_Array {

    public function process() {
        //cada $aBloc es un array de tipo 'blocs' en el archivo de datos
        foreach ($this->flds_data as $aBloc) {
            //cada $dataBloc es un objeto de tipo 'bloc' en el archivo de datos
            foreach ($aBloc as $dataBloc) {
                $ret .= $this->render($this->itemType['process'], $this->itemType, $dataBloc);
                //aplicamos los renders definidos en el archivo de configuración a cada objeto 'bloc' existente en el archivo de datos
//                foreach ($itemType['process'] as $pRender => $value) {
//                    $render = new $pRender($this->typesDefinition);
//                    $ret .= $render->process($cfgTypes[$cfgBloc['itemsType']], $dataBloc, $value, $n_bloc);
//                }
            }
        }
        return $ret;
    }
}
