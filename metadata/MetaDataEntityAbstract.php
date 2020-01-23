<?php

/**
 * Component: Project / MetaData
 * Status: @@Tested
 * Purposes:
 * - Abstract class that must inherit all entities
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");

require_once( DOKU_INC . "inc/JSON.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataEntityInterface.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataExceptions.php");

abstract class MetaDataEntityAbstract implements MetaDataEntityInterface {

    protected $projectType;
    protected $metaDataSubSet;
    protected $idResource;
    protected $metaDataValue;  //JSON array containing metadata
    protected $metaDataStructure;
    protected static $MANDATORIES = array("projectType", "metaDataSubSet", "idResource");
    protected static $JSONTYPES = array("metaDataValue");

    protected $metaDataTypesDefinition;

    function getProjectType() {
        return $this->projectType;
    }

    function getMetaDataSubSet() {
        return $this->metaDataSubSet;
    }

    function getNsRoot() {
        return $this->idResource;
    }

    function getMetaDataValue() {
        return $this->metaDataValue;
    }

    function getMetaDataStructure() {
        return $this->metaDataStructure;
    }

    function setProjectType($projectType) {
        $this->projectType = $projectType;
    }

    function setMetaDataSubSet($metaDataSubSet) {
        $this->metaDataSubSet = $metaDataSubSet;
    }

    function setNsRoot($idResource) {
        $this->idResource = $idResource;
    }

    function setMetaDataValue($metaDataValue) {
        $this->metaDataValue = $metaDataValue;
    }

    function setMetaDataStructure($metaDataStructure) {
        $this->metaDataStructure = $metaDataStructure;
    }

    function setMetaDataTypesDefinition($metaDataTypesDefinition) {
        $this->metaDataTypesDefinition= $metaDataTypesDefinition;
    }

    /**
     * Purpose:
     * - Object with model set (MetaDataStructure)
     * @return String JSON
     */
    public function __construct($MetaDataStructure = null, $metaDataTypesDefinition = null) {
        $this->setMetaDataStructure($MetaDataStructure);
        $this->setMetaDataTypesDefinition($metaDataTypesDefinition);
    }

    /**
     * Purpose:
     * - To map object properties to JSON (entity status to JSON)
     * @param any
     * @return String JSON
     */
    public function getArrayFromModel() {
        $arrayStatus = get_object_vars($this);
        return json_encode($arrayStatus);
    }

    /**
     * Purpose:
     * - To map JSON param to object properties (JSON to entity status)
     * @param String JSON
     * Restrictions:
     * - $arrayEntry wellformed JSON
     * - keys must be entity properties (but this is within the next restriction, mandatory)
     * - mandatory $projectType, $metaDataSubSet, $idResource;
     * @return true || exception
     */
    public function setModelFromArray($arrayEntry) {
        $arrayStatus = self::controlMalFormedJson($arrayEntry);
        $arrayEntryKeys = array();
        $i = 0;

        foreach ($arrayStatus as $key => $value) {
            $arrayEntryKeys[$i] = $key;
            $i++;
        }
        $allMandatories = $this->__checkMandatory($arrayEntryKeys);
        if (!$allMandatories) {
            throw new NotAllEntityMandatoryProperties();
        }
        $allValues = $this->__checkValues($arrayEntryKeys);
        if (!$allValues) {
            throw new NotAllEntityValidateProperties();
        }
        foreach ($arrayStatus as $property => $value) {
            if (property_exists($this, $property)) {
                $isJsonType = false;
                for ($j = 0; $j < sizeof(MetaDataEntityAbstract::$JSONTYPES); $j++) {
                    if (MetaDataEntityAbstract::$JSONTYPES[$j] == $property) {
                        $isJsonType = true;
                        break;
                    }
                }
                if (!$isJsonType) {
                    $this->{$property} = $value;
                } else {
                    $this->{$property} = json_encode($value);
                }
            }
        }
        return true;
    }

    /**
     * Purpose:
     * - Check de JSON filter with JSON entity metadata (metaDataValue:  {keymd1:valormd1,...,keymdx:valormdx})
     * @param String JSON {keyf1:valorf1,...,keyfn:valorfn}
     * Restrictions:
     * - $filter wellformed JSON
     * - All keys in $filter must exist in metaDataValue
     * - All key:value in $filter are the same in metaDataValue
     * @return exception || false || true
     */
    public function checkFilter($filter) {
        $arraymd = self::controlMalFormedJson($this->metaDataValue, "array");
        $arrayfi = self::controlMalFormedJson($filter, "array");
        $filterChecked = false;

        foreach ($arrayfi as $keyfi => $valuefi) {
            $filterChecked = false;
            if (isset($arraymd[$keyfi])) {
                if (is_array($arraymd[$keyfi])) {
                    for ($i = 0; $i < sizeof($arraymd[$keyfi]); $i++) {
                        if ($arraymd[$keyfi][$i] == $arrayfi[$keyfi]) {
                            $filterChecked = true;
                        }
                    }
                } else {
                    if ($arraymd[$keyfi] == $arrayfi[$keyfi]) {
                        $filterChecked = true;
                    }
                }
            }
            if (!$filterChecked) {
                break;
            }
        }
        return $filterChecked;
    }

    /**
     * Purpose:
     * - From data provided (JSON param) update JSON entity metadata (metaDataValue:  {keymd1:valormd1,...,keymdx:valormdx})
     * @param String JSON {keyp1:valorp1,...,keypn:valorpn}
     * Restrictions:
     * - $paramMetaDataValue wellformed JSON
     * - keys in $filter that do NOT exist in metaDataValue, are added
     * - keys in $filter that exist in metaDataValue, are updated
     * @return N/A
     */
    public function updateMetaDataValue($paramMetaDataValue) {

        $arraymd = self::controlMalFormedJson($this->metaDataValue, "array");
        $arraypi = self::controlMalFormedJson($paramMetaDataValue, "array");
        //Amplía el array de metaDataValue con las nuevas propiedades contenidas en $paramMetaDataValue
        foreach ($arraypi as $keypi => $valuepi) {
            $arraymd[$keypi] = $valuepi;
        }
        $this->setMetaDataValue(json_encode($arraymd));
    }

    /**
     * Purpose:
     * - Check if all MANDATORIES properties are in param array (to ensure that mandatory properties are filled)
     * @param zarray
     * Restrictions:
     * - is possible that $checkKeys may have more elements than MANDATORIES
     * @return true if all mandatory properties are in checkKeys
     */
    public function __checkMandatory($checkKeys) {
        $found = false;
        for ($i = 0; $i < sizeof(MetaDataEntityAbstract::$MANDATORIES); $i++) {
            //print(MetaDataEntityAbstract::$MANDATORIES[$i]);
            $found = false;
            for ($j = 0; $j < sizeof($checkKeys); $j++) {
                if ($checkKeys[$j] == MetaDataEntityAbstract::$MANDATORIES[$i]) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                break;
            }
        }
        return $found;
    }

    /**
     * Purpose:
     * - Check if all values from param are validated by the model
     * (allways true in this Abstract Class)
     * @param array
     * Restrictions:
     * -
     * @return true if all values are validated by the model (allways true in this Abstract Class)
     */
    public function __checkValues($checkValues) {
        return true;
    }

    /**
     * Purpose:
     * - Check if param array validate model $this->metaDataStructure
     * @param zarray
     * Restrictions:
     * @return true if validate
     */
    public function __checkStructure($arraypi) {
        $arrayst = json_decode($this->metaDataStructure, true);
        $validate = false;
        foreach ($arrayst as $keyst => $valuest) {
            $validate = false;
            $found = false;
            foreach ($arraypi as $keypi => $valuepi) {
                if ($keyst == $keypi) {
                    $found = true;
                    if (isset($valuest['tipus'])) {
                        $validate = (gettype($valuepi) == $valuest['tipus']);
                    } else {
                        $validate = true;
                    }
                    break;
                }
            }
            if (!$found && !$valuest['mandatory']) {
                $validate = true;
            }
            if (!$validate) {
                break;
            }
        }
        return $validate;
    }

    public static function controlMalFormedJson($jsonVar, $typeReturn="object") {
        if ($jsonVar) {
            $t = ($typeReturn==="array") ? TRUE : FALSE;
            $obj = json_decode($jsonVar, $t);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new MalFormedJSON();
            }
        }
        return $obj;
    }

}
