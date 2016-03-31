<?php

/**
 * Component: Project / MetaData
 * Status: @@Develop
 * Purposes:
 * - Facade to use metadata component
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC"))
    die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once( DOKU_INC . 'inc/JSON.php' );
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/metadataconfig/MetaDataDaoConfig.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRepository.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRenderFactory.php');

class MetaDataService {

    protected $metaDataDaoConfig;
    protected $metaDataRepository;
    protected $metaDataElements;
    protected $render = null;
    protected $metaDataEntityWrapper = array();

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
     * Constructor      
     */

    /**
     * Purpose:
     * - init $metaDataDaoConfig and $metaDataRepository properties
     * @param N/A
     * Restrictions:
     * - N/A
     * @return N/A
     */
    public function __construct() {
        $this->setMetaDataDaoConfig(new MetaDataDaoConfig());
        $this->setMetaDataRepository(new MetaDataRepository());
    }

    /**
     * Purpose:
     * - Call Repository to obtain metadata (one o several elements) and build a set of entities
     * @param Array $MetaDataRequestMessage
     * Restrictions:
     * - mandatory idResource,metaDataSubSet in param array $MetaDataRequestMessage
     * - other exceptions are delegate
     * @return a MetaDataEntity object
     */
    public function getMeta($MetaDataRequestMessage) {
        //Check parameters mandatories
        $checkParameters = false;
        if (isset($MetaDataRequestMessage['persistence'])) {
            if (isset($MetaDataRequestMessage['idResource'])) {
                if ($MetaDataRequestMessage['idResource'] != '') {
                    if (isset($MetaDataRequestMessage['metaDataSubSet'])) {
                        if ($MetaDataRequestMessage['metaDataSubSet'] != '') {
                            $checkParameters = true;
                        }
                    }
                }
            }
        }

        if (!$checkParameters) {
            throw new WrongParams();
        }
        //Control projectType in parameter
        $projectTypeParameter = null;
        if (isset($MetaDataRequestMessage['projectType'])) {
            if ($MetaDataRequestMessage['projectType'] != '') {
                $projectTypeParameter = $MetaDataRequestMessage['projectType'];
                print_r("\n setting project type parameter\n");
                print_r($projectTypeParameter);
            }
        }


        //Init metaDataElements property (elements set to get metadata)
        try {
            $this->setMetaDataElements($this->getMetaDataDaoConfig()->getMetaDataElementsKey($MetaDataRequestMessage['idResource'], $MetaDataRequestMessage['persistence']));
            $encoder = new JSON();
            $arrayElements = get_object_vars($encoder->decode($this->getMetaDataElements(), true));
            print_r("--------------TO GETDATAELELEMENTS PRE ASORT");
            print_r($arrayElements);
            asort($arrayElements);
            print_r("--------------TO GETDATAELELEMENTS AFTER ASORT");
            print_r($arrayElements);
            $this->setMetaDataElements($arrayElements);
            print_r($this->getMetaDataElements());
            $this->render = null;
            $this->metaDataEntityWrapper = array();
            $indexWrapper = 0;
            $indexResponse = 0;
            $projectTypeActual = null;
            $metaDataResponseGet = null;

            foreach ($this->getMetaDataElements() as $idResource => $projectType) {
                print_r("\nprojects types param\n");
                print_r($projectTypeParameter);
                print_r("\nprojects types actual\n");
                print_r($projectTypeActual);
                print_r("\nprojects types bucle\n");
                print_r($projectType);
                /*
                 * Check $idResource (sense path) == 
                 * == configMain filename: F($projectType, $metaDataSubset, $persistence, $configSubSet = "metaDataProjectStructure")
                 * Note: getMetaDataElementsKey returns all path/filename under $MetaDataRequestMessage['idResource'] but
                 *       this return set would contain filenames not matching with $MetaDataRequestMessage['metaDataSubSet'], because,
                 *       for instance, that this mataDataSubSet has metada in separate files
                 */
                $filename = $this->getMetaDataDaoConfig()->getMetaDataFileName($projectType, $MetaDataRequestMessage['metaDataSubSet'], $MetaDataRequestMessage['persistence']);
                print_r("\nEEEEEEEEEEEEEEEL FILE NAME\n");
                print_r($filename);
                print_r("\FIIIIIIIIIIIIIIIIIIIIIN FILE NAME\n");
                $filenameParamArray = explode(':', $idResource);
                if ($filename == $filenameParamArray[sizeof($filenameParamArray) - 1]) {

                    if ($projectTypeParameter == null || $projectTypeParameter == $projectType) {
                        if ($projectType != $projectTypeActual) {
                            if ($projectTypeActual != null) {
                                $metaDataResponseGet[$indexResponse] = $this->render->render($this->metaDataEntityWrapper);
                                $indexResponse++;
                            }
                            $projectTypeActual = $projectType;
                            $this->metaDataEntityWrapper = array();
                            $indexWrapper = 0;
                            $this->render = MetaDataRenderFactory::getObject($projectType, $MetaDataRequestMessage['metaDataSubSet'], $MetaDataRequestMessage['persistence']);
                            $rc = new ReflectionClass(get_class($this->render));
                            print_r(basename(dirname($rc->getFileName())));
                        }
                        $MetaDataRequestMessageActual = $MetaDataRequestMessage;
                        $MetaDataRequestMessageActual['projectType'] = $projectType;
                        $MetaDataRequestMessageActual['idResource'] = $idResource;
                        $metaDataEntity = $this->metaDataRepository->getMeta($MetaDataRequestMessageActual);
                        print_r("\nMESSAGE ACTUAL\n");
                        print_r($MetaDataRequestMessageActual);
                        print_r("\nENTITY GET\n");
                        print_r($metaDataEntity->getMetaDataValue());
                        print_r("\nENTITY projectType\n");
                        print_r($metaDataEntity->getProjectType());
                        $filterChecked = true;
                        if (isset($MetaDataRequestMessage['filter']) && ($MetaDataRequestMessage['filter'] != '')) {
                            $filterChecked = $metaDataEntity->checkFilter($MetaDataRequestMessage['filter']);
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
            print_r("\nmetaDataResponseGet\n");
            print_r($metaDataResponseGet);
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
     * - mandatory idResource,metaDataSubSet,MetaDataValue in param array $MetaDataRequestMessage
     * - other exceptions are delegate
     * @return success true
     */
    public function setMeta($MetaDataRequestMessage) {
        //Check parameters mandatories
        $checkParameters = false;
        if (isset($MetaDataRequestMessage['persistence'])) {
            if (isset($MetaDataRequestMessage['idResource'])) {
                if ($MetaDataRequestMessage['idResource'] != '') {
                    if (isset($MetaDataRequestMessage['metaDataSubSet'])) {
                        if ($MetaDataRequestMessage['metaDataSubSet'] != '') {
                            if (isset($MetaDataRequestMessage['metaDataValue'])) {
                                if ($MetaDataRequestMessage['metaDataValue'] != '') {
                                    $checkParameters = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$checkParameters) {
            throw new WrongParams();
        }
        //Control projectType in parameter
        $projectTypeParameter = null;
        if (isset($MetaDataRequestMessage['projectType'])) {
            if ($MetaDataRequestMessage['projectType'] != '') {
                $projectTypeParameter = $MetaDataRequestMessage['projectType'];
                print_r("\n setting project type parameter\n");
                print_r($projectTypeParameter);
            }
        }

        //Init metaDataElements property (elements set to get metadata)
        try {
            $this->setMetaDataElements($this->getMetaDataDaoConfig()->getMetaDataElementsKey($MetaDataRequestMessage['idResource'], $MetaDataRequestMessage['persistence']));
            //'{"fp:dam:m03":"materials","fp:daw:m07":"materials"}'
            $encoder = new JSON();
            $arrayElements = get_object_vars($encoder->decode($this->getMetaDataElements(), true));
            print_r("--------------TO GETDATAELELEMENTS PRE ASORT");
            print_r($arrayElements);
            asort($arrayElements);
            print_r("--------------TO GETDATAELELEMENTS AFTER ASORT");
            print_r($arrayElements);
            $this->setMetaDataElements($arrayElements);
            print_r($this->getMetaDataElements());
            $this->metaDataEntityWrapper = array();
            $metaDataResponseSet = null;
            $indexWrapper = 0;
            $indexResponse = 0;
            $projectTypeActual = null;
            foreach ($this->getMetaDataElements() as $idResource => $projectType) {
                /*
                 * Check $idResource (sense path) == 
                 * == configMain filename: F($projectType, $metaDataSubset, $persistence, $configSubSet = "metaDataProjectStructure")
                 * Note: getMetaDataElementsKey returns all path/filename under $MetaDataRequestMessage['idResource'] but
                 *       this return set would contain filenames not matching with $MetaDataRequestMessage['metaDataSubSet'], because,
                 *       for instance, that this mataDataSubSet has metada in separate files
                 */
                $filename = $this->getMetaDataDaoConfig()->getMetaDataFileName($projectType, $MetaDataRequestMessage['metaDataSubSet'], $MetaDataRequestMessage['persistence']);
                print_r("\nEEEEEEEEEEEEEEEL FILE NAME\n");
                print_r($filename);
                print_r("\FIIIIIIIIIIIIIIIIIIIIIN FILE NAME\n");
                $filenameParamArray = explode(':', $idResource);
                if ($filename == $filenameParamArray[sizeof($filenameParamArray) - 1]) {
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
                        $MetaDataRequestMessageActual['projectType'] = $projectType;
                        $MetaDataRequestMessageActual['idResource'] = $idResource;
                        print_r("\nMESSAGE ACTUAL\n");
                        print_r($MetaDataRequestMessageActual);
                        $metaDataEntity = $this->metaDataRepository->getMeta($MetaDataRequestMessageActual);
                        
                        print_r("\nENTITY GET\n");
                        print_r($metaDataEntity->getMetaDataValue());
                        print_r("\nENTITY projectType\n");
                        print_r($metaDataEntity->getProjectType());
                        $filterChecked = true;
                        if (isset($MetaDataRequestMessage['filter']) && ($MetaDataRequestMessage['filter'] != '')) {
                            $filterChecked = $metaDataEntity->checkFilter($MetaDataRequestMessage['filter']);
                        }
                        if ($filterChecked) {
                            $metaDataEntity->updateMetaDataValue($MetaDataRequestMessage['metaDataValue']);
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
            if ($metaDataResponseSet == null) {
                if ($projectTypeParameter == null) {
                    throw new ClassProjectsNotFound();
                } else {
                    /*
                     * It's a new set of metadata (only 1 element)
                     */

                    // Will returns an Entity Object with $MetaDataValue empty
                    $metaDataEntity = $this->metaDataRepository->getMeta($MetaDataRequestMessage);
                    // Must not check filter
                    // Will update (fill) $MetaDataValue
                    $metaDataEntity->updateMetaDataValue($MetaDataRequestMessage['metaDataValue']);
                    $this->metaDataEntityWrapper = array();
                    $indexWrapper = 0;
                    $returnSet = $this->metaDataRepository->setMeta($metaDataEntity, $MetaDataRequestMessageActual);
                    if ($returnSet) {
                        $this->metaDataEntityWrapper[$indexWrapper] = $metaDataEntity;
                    }
                    $metaDataResponseSet[$indexResponse] = $this->toAddResponse();
                }
            } else {
                print_r("\nREEEEEEEEEEEEEEEEEEEEEEESPONSE TOOOOOOOOOTAL\n");
                print_r($metaDataResponseSet);
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

}
