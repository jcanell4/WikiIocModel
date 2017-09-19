<?php
/**
 * MetaDataService: Facade to use metadata component
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once( DOKU_INC . 'inc/JSON.php' );
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/metadataconfig/MetaDataDaoConfig.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRepository.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRenderFactory.php');
require_once (DOKU_PLUGIN . 'ajaxcommand/defkeys/ProjectKeys.php');

class MetaDataService {
    const K_METADATASUBSET  = ProjectKeys::KEY_METADATA_SUBSET;
    const K_METADATAVALUE   = ProjectKeys::KEY_METADATA_VALUE;
    const K_PROJECTTYPE     = ProjectKeys::KEY_PROJECT_TYPE;
    const K_IDRESOURCE      = ProjectKeys::KEY_ID_RESOURCE;
    const K_PERSISTENCE     = ProjectKeys::KEY_PERSISTENCE;
    const K_FILTER          = ProjectKeys::KEY_FILTER;
    const K_PROJECT_FILENAME= ProjectKeys::KEY_PROJECT_FILENAME;

    protected $metaDataDaoConfig;
    protected $metaDataRepository;
    protected $metaDataElements;
    protected $render = null;
    protected $metaDataEntityWrapper = array();

    public function __construct() {
        $this->setMetaDataDaoConfig(new MetaDataDaoConfig());
        $this->setMetaDataRepository(new MetaDataRepository());
    }

    function getMetaDataDaoConfig() {
        return $this->metaDataDaoConfig;
    }
    function setMetaDataDaoConfig($metaDataDaoConfig) {
        $this->metaDataDaoConfig = $metaDataDaoConfig;
    }
    function getMetaDataRepository() {
        return $this->metaDataRepository;
    }
    function setMetaDataRepository($metaDataRepository) {
        $this->metaDataRepository = $metaDataRepository;
    }
    function getMetaDataElements() {
        return $this->metaDataElements;
    }
    function setMetaDataElements($metaDataElements) {
        $this->metaDataElements = $metaDataElements;
    }

    /**
     * Dispatcher para seleccionar una de entre 2 funciones para preservar la compatibilidad con la versión heredada
     * @param type $multiproject : indica si se desea utilizar la versión multiproyecto heredada o la nueva versión simplificada para un solo proyecto
     */
    public function getMeta($MetaDataRequestMessage, $multiproject=TRUE) {
        if ($multiproject)
            return $this->getMetaMultiProject($MetaDataRequestMessage);
        else
            return $this->getMetaOne($MetaDataRequestMessage);
    }

    /**
     * Obtiene, manda construir y retorna una estructura con los metadatos y valores del proyecto
     * @param array $MetaDataRequest : idResource, projectType, k_metadatasubset, persistencia
     * @return array(array('structure'), array('values'))
     */
    public function getMetaOne($MetaDataRequest) {
        //Check parameters mandatories
        if (! (isset($MetaDataRequest[self::K_PERSISTENCE]) &&
               isset($MetaDataRequest[self::K_IDRESOURCE]) &&
               $MetaDataRequest[self::K_IDRESOURCE] != '' &&
               isset($MetaDataRequest[self::K_METADATASUBSET]) &&
               $MetaDataRequest[self::K_METADATASUBSET] != '')) {
            throw new WrongParams();
        }

        try {
            $idResource  = $MetaDataRequest[self::K_IDRESOURCE];
            $projectType = $MetaDataRequest[self::K_PROJECTTYPE];
            $mdSubSet    = $MetaDataRequest[self::K_METADATASUBSET];
            $persistence = $MetaDataRequest[self::K_PERSISTENCE];
            $filter      = $MetaDataRequest[self::K_FILTER];

            $this->setMetaDataElements(json_encode([$idResource => $projectType]));
            $this->metaDataEntityWrapper = array();
            $metaDataResponseGet = null;
            $this->render = MetaDataRenderFactory::getObject($projectType, $mdSubSet, $persistence);

            $metaDataEntity = $this->metaDataRepository->getMeta($MetaDataRequest);

            $filterChecked = true;
            if (isset($filter) && ($filter != '')) {
                $filterChecked = $metaDataEntity->checkFilter($filter);
            }
            if ($filterChecked) {
                $this->metaDataEntityWrapper[0] = $metaDataEntity;
            }
            if ($this->render != null) {
                $metaDataResponseGet[0] = $this->render->render($this->metaDataEntityWrapper);
            }
            if ($this->isAllZero($metaDataResponseGet)) {
                $metaDataResponseGet = null;
            }

            if ($metaDataResponseGet == null) {
                throw new ClassProjectsNotFound();
            } else {
                return $metaDataResponseGet;
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Call Repository to obtain metadata (one o several elements) and build a set of entities
     * @param Array $MetaDataRequestMessage
     * @return a MetaDataEntity object
     */
    public function getMetaMultiProject($MetaDataRequestMessage) {
        //Check parameters mandatories
        $checkParameters =  isset($MetaDataRequestMessage[self::K_PERSISTENCE]) &&
                            isset($MetaDataRequestMessage[self::K_IDRESOURCE]) &&
                            $MetaDataRequestMessage[self::K_IDRESOURCE] != '' &&
                            isset($MetaDataRequestMessage[self::K_METADATASUBSET]) &&
                            $MetaDataRequestMessage[self::K_METADATASUBSET] != '';

        if (!$checkParameters) {
            throw new WrongParams();
        }
        //Control projectType in parameter
        $projectTypeParameter = null;
        if (isset($MetaDataRequestMessage[self::K_PROJECTTYPE]) && $MetaDataRequestMessage[self::K_PROJECTTYPE] != '') {
            $projectTypeParameter = $MetaDataRequestMessage[self::K_PROJECTTYPE];
        }

        //Init metaDataElements property (elements set to get metadata)
        try {
            $metaDataElements = $this->getMetaDataDaoConfig()->getMetaDataElementsKey($MetaDataRequestMessage[self::K_IDRESOURCE], $MetaDataRequestMessage[self::K_PERSISTENCE]);
            if ($metaDataElements !== NULL) {
                $encoder = new JSON();
                $metaDataElements = get_object_vars($encoder->decode($metaDataElements, true));
                asort($metaDataElements);
                $this->setMetaDataElements($metaDataElements);

                $this->render = null;
                $this->metaDataEntityWrapper = array();
                $indexWrapper = 0;
                $indexResponse = 0;
                $projectTypeActual = null;
                $metaDataResponseGet = null;

                foreach ($metaDataElements as $idResource => $projectType) {
                    /*
                     * Check $idResource (sense path) ==
                     * == configMain filename: F($projectType, $metaDataSubset, $persistence, $configSubSet = ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE)
                     * Note: getMetaDataElementsKey returns all path/filename under $MetaDataRequestMessage[self::K_IDRESOURCE] but
                     *       this return set would contain filenames not matching with $MetaDataRequestMessage[self::K_METADATASUBSET], because,
                     *       for instance, that this mataDataSubSet has metada in separate files
                     */
                    $filename = $this->getMetaDataDaoConfig()->getMetaDataFileName($projectType, $MetaDataRequestMessage[self::K_METADATASUBSET], $MetaDataRequestMessage[self::K_PERSISTENCE]);

                    if ($projectTypeParameter == null || $projectTypeParameter == $projectType) {
                        if ($projectType != $projectTypeActual) {
                            if ($projectTypeActual != null) {
                                $metaDataResponseGet[$indexResponse] = $this->render->render($this->metaDataEntityWrapper);
                                $indexResponse++;
                            }
                            $projectTypeActual = $projectType;
                            $this->metaDataEntityWrapper = array();
                            $indexWrapper = 0;
                            $this->render = MetaDataRenderFactory::getObject($projectType, $MetaDataRequestMessage[self::K_METADATASUBSET], $MetaDataRequestMessage[self::K_PERSISTENCE]);
                            $rc = new ReflectionClass(get_class($this->render));
                        }
                        $MetaDataRequestMessageActual = $MetaDataRequestMessage;
                        $MetaDataRequestMessageActual[self::K_PROJECTTYPE] = $projectType;
                        $MetaDataRequestMessageActual[self::K_IDRESOURCE] = $idResource;
                        $MetaDataRequestMessageActual[self::K_PROJECT_FILENAME] = $filename;
                        $metaDataEntity = $this->metaDataRepository->getMeta($MetaDataRequestMessageActual);
                        $filterChecked = true;
                        if (isset($MetaDataRequestMessage[self::K_FILTER]) && ($MetaDataRequestMessage[self::K_FILTER] != '')) {
                            $filterChecked = $metaDataEntity->checkFilter($MetaDataRequestMessage[self::K_FILTER]);
                        }
                        if ($filterChecked) {
                            $this->metaDataEntityWrapper[$indexWrapper] = $metaDataEntity;
                            $indexWrapper++;
                        }
                    }
                }
            }
            if ($this->render != null) {
                $metaDataResponseGet[$indexResponse] = $this->render->render($this->metaDataEntityWrapper);
            }
            if ($this->isAllZero($metaDataResponseGet)) {
                $metaDataResponseGet = null;
            }
            if ($metaDataResponseGet == null) {
                throw new ClassProjectsNotFound();
            } else {
                return $metaDataResponseGet;
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Purpose:
     * - Call Repository to set / update metadata (one o several elements)
     * @param Array $MetaDataRequestMessage
     * Restrictions:
     * - mandatory idResource,metaDataSubSet,metaDataValue in param array $MetaDataRequestMessage
     * - other exceptions are delegate
     * @return success true
     */
    public function setMeta($MetaDataRequestMessage) {
        //Check parameters mandatories
        $checkParameters =  isset($MetaDataRequestMessage[self::K_PERSISTENCE]) &&
                            isset($MetaDataRequestMessage[self::K_IDRESOURCE]) &&
                            $MetaDataRequestMessage[self::K_IDRESOURCE] != '' &&
                            isset($MetaDataRequestMessage[self::K_METADATASUBSET]) &&
                            $MetaDataRequestMessage[self::K_METADATASUBSET] != '' &&
                            isset($MetaDataRequestMessage[self::K_METADATAVALUE]) &&
                            $MetaDataRequestMessage[self::K_METADATAVALUE] != '';

        if (!$checkParameters) {
            throw new WrongParams();
        }
        //Control projectType in parameter
        $projectTypeParameter = null;
        if (isset($MetaDataRequestMessage[self::K_PROJECTTYPE]) && $MetaDataRequestMessage[self::K_PROJECTTYPE] != '') {
            $projectTypeParameter = $MetaDataRequestMessage[self::K_PROJECTTYPE];
        }

        $indexResponse = 0;
        //Init metaDataElements property (elements set to get metadata)
        try {
            $MetaDataElementsKey = $this->getMetaDataDaoConfig()->getMetaDataElementsKey($MetaDataRequestMessage[self::K_IDRESOURCE], $MetaDataRequestMessage[self::K_PERSISTENCE]);
            $this->setMetaDataElements($MetaDataElementsKey);
            if ($this->getMetaDataElements() != null && count($this->getMetaDataElements() > 0)) {
                $encoder = new JSON();
                $arrayElements = get_object_vars($encoder->decode($this->getMetaDataElements(), true));
                asort($arrayElements);
                $this->setMetaDataElements($arrayElements);
                $this->metaDataEntityWrapper = array();
                $metaDataResponseSet = null;
                $indexWrapper = 0;
                $indexResponse = 0;
                $projectTypeActual = null;
                foreach ($this->getMetaDataElements() as $idResource => $projectType) {
                    /*
                     * Check $idResource (sense path) ==
                     * == configMain filename: F($projectType, $metaDataSubset, $persistence, $configSubSet = ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE)
                     * Note: getMetaDataElementsKey returns all path/filename under $MetaDataRequestMessage[self::K_IDRESOURCE] but
                     *       this return set would contain filenames not matching with $MetaDataRequestMessage[self::K_METADATASUBSET], because,
                     *       for instance, that this mataDataSubSet has metada in separate files
                     */
                    $filename = $this->getMetaDataDaoConfig()->getMetaDataFileName($projectType, $MetaDataRequestMessage[self::K_METADATASUBSET], $MetaDataRequestMessage[self::K_PERSISTENCE]);
                    if ($projectTypeParameter == null || $projectTypeParameter == $projectType) {
                        if ($projectType != $projectTypeActual) {
                            if ($projectTypeActual != null) {
                                $metaDataResponseSet[$indexResponse] = $this->toAddResponse();
                                $indexResponse++;
                            }
                            $projectTypeActual = $projectType;
                            $this->metaDataEntityWrapper = array();
                            $indexWrapper = 0;
                        }
                        $MetaDataRequestMessageActual = $MetaDataRequestMessage;
                        $MetaDataRequestMessageActual[self::K_PROJECTTYPE] = $projectType;
                        $MetaDataRequestMessageActual[self::K_IDRESOURCE] = $idResource;
                        $MetaDataRequestMessageActual[self::K_PROJECT_FILENAME] = $filename;
                        $metaDataEntity = $this->metaDataRepository->getMeta($MetaDataRequestMessageActual);
                        $filterChecked = true;
                        if (isset($MetaDataRequestMessage[self::K_FILTER]) && ($MetaDataRequestMessage[self::K_FILTER] != '')) {
                            $filterChecked = $metaDataEntity->checkFilter($MetaDataRequestMessage[self::K_FILTER]);
                        }
                        if ($filterChecked) {
                            $metaDataEntity->updateMetaDataValue($MetaDataRequestMessage[self::K_METADATAVALUE]);
                            $returnSet = $this->metaDataRepository->setMeta($metaDataEntity, $MetaDataRequestMessageActual);
                            if ($returnSet) {
                                $this->metaDataEntityWrapper[$indexWrapper] = $metaDataEntity;
                                $indexWrapper++;
                            }
                        }
                    }
                }
            }
            $metaDataResponseSet[$indexResponse] = $this->toAddResponse();
            if ($this->isAllZero($metaDataResponseSet)) {
                $metaDataResponseSet = null;
            }
            if ($metaDataResponseSet == null) {
                if ($projectTypeParameter == null) {
                    throw new ClassProjectsNotFound();
                }else {
                    //nombre del fichero que contiene los datos del formulario del proyecto
                    $MetaDataRequestMessage[self::K_PROJECT_FILENAME] = $this->getMetaDataDaoConfig()->getMetaDataFileName($MetaDataRequestMessage[self::K_PROJECTTYPE], $MetaDataRequestMessage[self::K_METADATASUBSET], $MetaDataRequestMessage[self::K_PERSISTENCE]);
                    $metaDataEntity = $this->metaDataRepository->getMeta($MetaDataRequestMessage);
                    $metaDataEntity->updateMetaDataValue($MetaDataRequestMessage[self::K_METADATAVALUE]);
                    $this->metaDataEntityWrapper = array();
                    //creación de la estructura y los ficheros del proyecto en ./data/mdprojects/
                    $returnSet = $this->metaDataRepository->setMeta($metaDataEntity, $MetaDataRequestMessage);
                    if ($returnSet) {
                        $this->metaDataEntityWrapper[0] = $metaDataEntity;
                    }
                    $metaDataResponseSet[$indexResponse] = $this->toAddResponse();
                    return $metaDataResponseSet;
                }
            }else {
                return $metaDataResponseSet;
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    private function toAddResponse() {
        $toReturn = array();
        for ($i = 0; $i < sizeof($this->metaDataEntityWrapper); $i++) {
            $toReturn[$i] = $this->metaDataEntityWrapper[$i]->getArrayFromModel();
        }
        return $toReturn;
    }

    private function isAllZero($var) {
        $allZero = true;
        for ($i = 0; $i < sizeof($var); $i++) {
            if (sizeof($var[$i]) > 0) {
                $allZero = false;
            }
        }
        return $allZero;
    }
}
