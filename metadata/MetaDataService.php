<?php
/**
 * MetaDataService: Facade to use metadata component
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");

require_once( DOKU_INC . 'inc/JSON.php' );
require_once (WIKI_IOC_MODEL . 'metadata/MetaDataExceptions.php');
require_once (WIKI_IOC_MODEL . 'metadata/metadataconfig/MetaDataDaoConfig.php');
require_once (WIKI_IOC_MODEL . 'metadata/MetaDataRepository.php');
require_once (WIKI_IOC_MODEL . 'metadata/MetaDataRenderFactory.php');
require_once (DOKU_PLUGIN . 'ajaxcommand/defkeys/ProjectKeys.php');

class MetaDataService {
    const K_METADATASUBSET  = ProjectKeys::KEY_METADATA_SUBSET;
    const K_METADATAVALUE   = ProjectKeys::KEY_METADATA_VALUE;
    const K_PROJECTTYPE     = ProjectKeys::KEY_PROJECT_TYPE;
    const K_IDRESOURCE      = ProjectKeys::KEY_ID_RESOURCE;
    const K_PERSISTENCE     = ProjectKeys::KEY_PERSISTENCE;
    const K_FILTER          = ProjectKeys::KEY_FILTER;

    protected $metaDataDaoConfig;
    protected $metaDataRepository;
    protected $metaDataElements;
    protected $render = null;
    protected $metaDataEntityWrapper = array();
    protected $revision = FALSE;

    public function __construct() {
        $this->setMetaDataDaoConfig(new MetaDataDaoConfig());
        $this->setMetaDataRepository(new MetaDataRepository());
    }

    function getRevision() {
        return $this->revision;
    }
    function isRevision() {
        return $this->revision!=FALSE;
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
    public function getMeta($MetaDataRequest, $multiproject=TRUE) {
        if ($multiproject)
            return $this->getMetaMultiProject($MetaDataRequest);
        else
            return $this->getMetaOne($MetaDataRequest);
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
               $MetaDataRequest[self::K_METADATASUBSET] != '' )) {
            throw new WrongParams();
        }

        try {
            $idResource     = $MetaDataRequest[self::K_IDRESOURCE];
            $projectType    = $MetaDataRequest[self::K_PROJECTTYPE];
            $mdSubSet       = $MetaDataRequest[self::K_METADATASUBSET];
            $persistence    = $MetaDataRequest[self::K_PERSISTENCE];
            $filter         = $MetaDataRequest[self::K_FILTER];

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
     * @param Array $MetaDataRequest
     * @return a MetaDataEntity object
     */
    public function getMetaMultiProject($MetaDataRequest) {
        //Check parameters mandatories
        if (! (isset($MetaDataRequest[self::K_PERSISTENCE]) &&
               isset($MetaDataRequest[self::K_IDRESOURCE]) &&
               $MetaDataRequest[self::K_IDRESOURCE] != '' &&
               isset($MetaDataRequest[self::K_METADATASUBSET]) &&
               $MetaDataRequest[self::K_METADATASUBSET] != '')) {
            throw new WrongParams();
        }
        //Control projectType in parameter
        $projectTypeParameter = null;
        if (isset($MetaDataRequest[self::K_PROJECTTYPE]) && $MetaDataRequest[self::K_PROJECTTYPE] != '') {
            $projectTypeParameter = $MetaDataRequest[self::K_PROJECTTYPE];
        }

        //Init metaDataElements property (elements set to get metadata)
        try {
            $metaDataElements = $this->getMetaDataDaoConfig()->getMetaDataElementsKey($MetaDataRequest[self::K_IDRESOURCE], $MetaDataRequest[self::K_PERSISTENCE]);
            if ($metaDataElements !== NULL) {
                $metaDataElements = get_object_vars(json_decode($metaDataElements));
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
                     * Note: getMetaDataElementsKey returns all path/filename under $MetaDataRequest[self::K_IDRESOURCE] but
                     *       this return set would contain filenames not matching with $MetaDataRequest[self::K_METADATASUBSET], because,
                     *       for instance, that this mataDataSubSet has metada in separate files
                     */
                    if ($projectTypeParameter == null || $projectTypeParameter == $projectType) {
                        if ($projectType != $projectTypeActual) {
                            if ($projectTypeActual != null) {
                                $metaDataResponseGet[$indexResponse] = $this->render->render($this->metaDataEntityWrapper);
                                $indexResponse++;
                            }
                            $projectTypeActual = $projectType;
                            $this->metaDataEntityWrapper = array();
                            $indexWrapper = 0;
                            $this->render = MetaDataRenderFactory::getObject($projectType, $MetaDataRequest[self::K_METADATASUBSET], $MetaDataRequest[self::K_PERSISTENCE]);
                            $rc = new ReflectionClass(get_class($this->render));
                        }
                        $MetaDataRequestMessageActual = $MetaDataRequest;
                        $MetaDataRequestMessageActual[self::K_PROJECTTYPE] = $projectType;
                        $MetaDataRequestMessageActual[self::K_IDRESOURCE] = $idResource;
                        $metaDataEntity = $this->metaDataRepository->getMeta($MetaDataRequestMessageActual);
                        $filterChecked = true;
                        if (isset($MetaDataRequest[self::K_FILTER]) && ($MetaDataRequest[self::K_FILTER] != '')) {
                            $filterChecked = $metaDataEntity->checkFilter($MetaDataRequest[self::K_FILTER]);
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
     * @param Array $MetaDataRequest
     * Restrictions:
     * - mandatory idResource,persistence,metaDataSubSet,metaDataValue,projctTypeDir
     * - other exceptions are delegate
     * @return success true
     */
    public function setMeta($MetaDataRequest) {
        //Check parameters mandatories
        if (! (isset($MetaDataRequest[self::K_PERSISTENCE]) &&
               isset($MetaDataRequest[self::K_IDRESOURCE]) &&
               $MetaDataRequest[self::K_IDRESOURCE] != '' &&
               isset($MetaDataRequest[self::K_METADATASUBSET]) &&
               $MetaDataRequest[self::K_METADATASUBSET] != '' &&
               isset($MetaDataRequest[self::K_METADATAVALUE]) &&
               $MetaDataRequest[self::K_METADATAVALUE] != '')) {
            throw new WrongParams();
        }

        //Control projectType in parameter
        $projectTypeParameter = null;
        if (isset($MetaDataRequest[self::K_PROJECTTYPE]) && $MetaDataRequest[self::K_PROJECTTYPE] != '') {
            $projectTypeParameter = $MetaDataRequest[self::K_PROJECTTYPE];
        }

        $indexResponse = 0;
        //Init metaDataElements property (elements set to get metadata)
        try {
            $MetaDataElementsKey = $this->getMetaDataDaoConfig()->getMetaDataElementsKey($MetaDataRequest[self::K_IDRESOURCE], $MetaDataRequest[self::K_PERSISTENCE]);
            $this->setMetaDataElements($MetaDataElementsKey);
            $mDEKey = $this->getMetaDataElements();
            if (is_object($mDEKey)) {
                $arrayElements = get_object_vars($this->getMetaDataElements());
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
                     * Note: getMetaDataElementsKey returns all path/filename under $MetaDataRequest[self::K_IDRESOURCE] but
                     *       this return set would contain filenames not matching with $MetaDataRequest[self::K_METADATASUBSET], because,
                     *       for instance, that this mataDataSubSet has metada in separate files
                     */
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
                        $MetaDataRequestMessageActual = $MetaDataRequest;
                        $MetaDataRequestMessageActual[self::K_PROJECTTYPE] = $projectType;
                        $MetaDataRequestMessageActual[self::K_IDRESOURCE] = $idResource;
                        $metaDataEntity = $this->metaDataRepository->getMeta($MetaDataRequestMessageActual);
                        $filterChecked = true;
                        if (isset($MetaDataRequest[self::K_FILTER]) && ($MetaDataRequest[self::K_FILTER] != '')) {
                            $filterChecked = $metaDataEntity->checkFilter($MetaDataRequest[self::K_FILTER]);
                        }
                        if ($filterChecked) {
                            $metaDataEntity->updateMetaDataValue($MetaDataRequest[self::K_METADATAVALUE]);
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
                    $metaDataEntity = $this->metaDataRepository->getMeta($MetaDataRequest);
                    $metaDataEntity->updateMetaDataValue($MetaDataRequest[self::K_METADATAVALUE]);
                    $this->metaDataEntityWrapper = array();
                    //creación de la estructura y los ficheros del proyecto en ./data/mdprojects/
                    $returnSet = $this->metaDataRepository->setMeta($metaDataEntity, $MetaDataRequest);
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
