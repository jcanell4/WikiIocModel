<?php

/**
 * Component: Project / MetaData
 * Status: @@Tested
 * Purposes:
 * - Abstract class that must inherit all entities
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC"))
    die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once( DOKU_INC . 'inc/JSON.php' );
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataEntityInterface.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');

abstract class MetaDataEntityAbstract implements MetaDataEntityInterface {

    protected $projectType;
    protected $metaDataSubSet;
    protected $idResource;
    protected $MetaDataValue;  //JSON array containing metadata
    protected $metaDataStructure;
    protected static $MANDATORIES = array("projectType", "metaDataSubSet", "idResource");
    protected static $JSONTYPES = array("MetaDataValue");

    function getProjectType() {
        return $this->projectType;
    }

    function getmetaDataSubSet() {
        return $this->metaDataSubSet;
    }

    function getNsRoot() {
        return $this->idResource;
    }

    function getMetaDataValue() {
        return $this->MetaDataValue;
    }

    function setProjectType($projectType) {
        $this->projectType = $projectType;
    }

    function setmetaDataSubSet($metaDataSubSet) {
        $this->metaDataSubSet = $metaDataSubSet;
    }

    function setNsRoot($idResource) {
        $this->idResource = $idResource;
    }

    function setMetaDataValue($MetaDataValue) {
        $this->MetaDataValue = $MetaDataValue;
    }

    function getMetaDataStructure() {
        return $this->metaDataStructure;
    }

    function setMetaDataStructure($metaDataStructure) {
        $this->metaDataStructure = $metaDataStructure;
    }

    /**
     * CONSTRUCTOR
     * Purpose:
     * - Object with model set (MetaDataStructure)
     * @param any
     * @return String JSON
     */
    public function __construct($MetaDataStructure = null) {

        $this->setMetaDataStructure($MetaDataStructure);
    }

    /**
     * Purpose:
     * - To map object properties to JSON (entity status to JSON)
     * @param any
     * @return String JSON
     */
    public function getArrayFromModel() {
        $encoder = new JSON();
        $arrayStatus = get_object_vars($this);
        //return json_encode($arrayStatus);
        return $encoder->encode($arrayStatus);
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
        $encoder = new JSON();
        $arrayStatus = $encoder->decode($arrayEntry);
        //$arrayStatus = json_decode($arrayEntry);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new MalFormedJSON();
        }
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
                    $this->{$property} = $encoder->encode($value);
                }
            }
        }
        return true;
    }

    /**
     * Purpose:
     * - Check de JSON filter with JSON entity metadata (MetaDataValue:  {keymd1:valormd1,...,keymdx:valormdx})
     * @param String JSON {keyf1:valorf1,...,keyfn:valorfn}
     * Restrictions:
     * - $filter wellformed JSON
     * - All keys in $filter must exist in MetaDataValue
     * - All key:value in $filter are the same in MetaDataValue
     * @return exception || false || true 
     */
    public function checkFilter($filter) {
        $encoder = new JSON();
        //$arraymd = $encoder->decode($this->MetaDataValue);
        $arraymd = json_decode($this->MetaDataValue, true); //true to force json_decode to return an array and not an object
        //$arrayfi = $encoder->decode($filter);
        $arrayfi = json_decode($filter, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new MalFormedJSON();
        }
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
     * - From data provided (JSON param) update JSON entity metadata (MetaDataValue:  {keymd1:valormd1,...,keymdx:valormdx})
     * @param String JSON {keyp1:valorp1,...,keypn:valorpn}
     * Restrictions:
     * - $paramMetaDataValue wellformed JSON
     * - keys in $filter that do NOT exist in MetaDataValue, are added
     * - keys in $filter that exist in MetaDataValue, are updated
     * @return N/A
     */
    public function updateMetaDataValue($paramMetaDataValue) {

        $arraymd = json_decode($this->MetaDataValue, true);
        print_r("\n START arraydv arraydv");
        print_r($arraymd);
        print_r("\n END arraydv arraydv");
        $arraypi = json_decode($paramMetaDataValue, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new MalFormedJSON();
        }

        foreach ($arraypi as $keypi => $valuepi) {
            $arraymd[$keypi] = $arraypi[$keypi];
        }
        $encoder = new JSON();
        $this->setMetaDataValue($encoder->encode($arraymd));
        print_r("\n START arraydva arraydva");
        print_r($this->MetaDataValue);
        print_r("\n END arraydva arraydva");
        /*
         * CheckStructure
         */
        //$arraypi = json_decode($paramMetaDataValue, true); //no cal que vinguin totes les dades si és una actualització
        $arraypi = json_decode($this->MetaDataValue, true);
        $allValues = $this->__checkStructure($arraypi);
        print_r("\n START arraypi arraypi");
        print_r($arraypi);
        print_r("\n END arraypi arraypi");
        if (!$allValues) {
            throw new NotAllEntityValidateProperties();
        }
        $allValues = $this->__checkValues($arrayEntryKeys);
        if (!$allValues) {
            throw new NotAllEntityValidateProperties();
        }
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
        print_r("\n START arrayst arrayst \n");
        print_r($arrayst);
        print_r("\n END arrayst arrayst \n");
        print_r("\n START arrayst1 arrayst1 \n");
        print_r($arraypi);
        print_r("\n END arrayst1 arrayst1 \n");
        $validate = false;
        foreach ($arrayst as $keyst => $valuest) {
            print_r("\n START arraystin arraystin \n");
            print_r($keyst);
            print_r("\n");
            print_r($valuest);
            print_r("\n END arraystin arraystin \n");
            $validate = false;
            $found = false;
            foreach ($arraypi as $keypi => $valuepi) {
                print_r("\n START arraystin1 arraystin1 \n");
                print_r($keypi);
                print_r("\n");
                print_r($valuepi);
                print_r("\n END arraystin1 arraystin1 \n");
                if ($keyst == $keypi) {
                    print_r("\n START arraystin2 arraystin2 \n");
                    print_r($keypi);
                    print_r("\n");
                    print_r($valuepi);
                    print_r("\n END arraystin2 arraystin2 \n");
                    $found = true;
                    if (isset($valuest['tipus'])) {
                        if (gettype($valuepi) == $valuest['tipus']) {
                            $validate = true;
                        }
                    } else {
                        $validate = true;
                    }
                    break;
                }
            }
            print_r("\n START arraystin3 arraystin3 \n");
            print_r(!$found);
            print_r("\n");
            print_r(!$valuest['mandatory']);
            print_r("\n END arraystin3 arraystin3 \n");
            if (!$found && !$valuest['mandatory']) {
                $validate = true;
            }
            if (!$validate) {
                break;
            }
        }
        return $validate;
    }

}
