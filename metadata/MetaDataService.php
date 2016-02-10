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
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/metadataconfig/MetaDataRepositoryConfig.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRepository.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRenderFactory.php');

abstract class MetaDataService {

    protected $metaDataRepositoryConfig;
    protected $metaDataRepository;
    protected $metaDataElements;
    protected $render = null;
    protected $metaDataEntityWrapper = array();

    function getMetaDataRepositoryConfig() {
        return $this->metaDataRepositoryConfig;
    }

    function setMetaDataRepositoryConfig($metaDataRepositoryConfig) {
        $this->metaDataRepositoryConfig = $metaDataRepositoryConfig;
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
     * - init $metaDataRepositoryConfig and $metaDataRepository properties
     * @param N/A
     * Restrictions:
     * - N/A
     * @return N/A
     */
    public function __construct() {
        $this->setMetaDataRepositoryConfig(new MetaDataRepositoryConfig());
        $this->setMetaDataRepository(new MetaDataRepository());
    }

    /**
     * Purpose:
     * - Call Repository to obtain metadata (one o several elements) and build a set of entities
     * @param Array $MetaDataRequestMessage
     * Restrictions:
     * - mandatory ns,projectType,metaDataSubSet in param array $MetaDataRequestMessage
     * - filter exists, then idResource is not null
     * - other exceptions are delegate
     * @return a MetaDataEntity object
     */
    public function getMeta($MetaDataRequestMessage) {
        //Check parameters mandatories and filter
        $checkParameters = false;
        if (isset($MetaDataRequestMessage['ns'])) {
            if ($MetaDataRequestMessage['ns'] != '') {
                if (isset($MetaDataRequestMessage['projectType'])) {
                    if ($MetaDataRequestMessage['projectType'] != '') {
                        if (isset($MetaDataRequestMessage['metaDataSubSet'])) {
                            if ($MetaDataRequestMessage['metaDataSubSet'] != '') {
                                //Check filter exists, then idResource not null
                                if (isset($MetaDataRequestMessage['filter']) && ($MetaDataRequestMessage['filter'] != '')) {
                                    if (isset($MetaDataRequestMessage['idResource']) && ($MetaDataRequestMessage['idResource'] != '')) {
                                        $checkParameters = true;
                                    }
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

        //Init metaDataElements property (elements set to get metadata)
        try {
            if (isset($MetaDataRequestMessage['filter']) && ($MetaDataRequestMessage['filter'] != '')) {
                $this->setMetaDataElements($this->getMetaDataRepositoryConfig()->getMetaDataElementsKey($MetaDataRequestMessage['idResource']));
            } else {
                $toEncode = array();
                $toEncode[$MetaDataRequestMessage['idResource']] = $MetaDataRequestMessage['projectType'];
                $encoder = new JSON();
                $this->setMetaDataElements($encoder->encode($toEncode));
            }
        } catch (Exception $ex) {
            throw $ex;
        }

        //'{"fp:dam:m03":"materials","fp:daw:m07":"materials"}'
        $encoder = new JSON();
        $arrayElements = $encoder->decode($this->getMetaDataElements());
        asort($arrayElements);
        $this->render = null;
        $this->metaDataEntityWrapper = array();
        $indexWrapper = 0;
        $indexResponse = 0;
        $projectTypeActual = null;
        $metaDataResponseGet = null;
        try {
            foreach ($arrayElements as $idResource => $projectType) {

                //ojo que s'ha de cridar al render
                if ($projectType != $projectTypeActual) {
                    if ($projectTypeActual != null) {
                        $metaDataResponseGet[$indexResponse]=$this->render->render($this->metaDataEntityWrapper);
                        $indexResponse++;
                    }
                    $projectTypeActual = $projectType;
                    $this->metaDataEntityWrapper = array();
                    $indexWrapper = 0;
                    $this->render = MetaDataRenderFactory::getObject($projectType, $MetaDataRequestMessage['metaDataSubSet']);
                }
                $MetaDataRequestMessageActual = $MetaDataRequestMessage;
                $MetaDataRequestMessageActual['projectType'] = $projectType;
                $MetaDataRequestMessageActual['idResource'] = $idResource;
                $metaDataEntity = $this->metaDataRepository->getMeta($MetaDataRequestMessageActual);
                $filterChecked = true;
                if (isset($MetaDataRequestMessage['filter']) && ($MetaDataRequestMessage['filter'] != '')) {
                    $filterChecked = $metaDataEntity->checkFilter($filter);
                }
                if ($filterChecked) {
                    $this->metaDataEntityWrapper[$indexWrapper] = $metaDataEntity;
                    $indexWrapper++;
                }
            }
            $metaDataResponseGet[$indexResponse]=$this->render->render($this->metaDataEntityWrapper);
            if($metaDataResponseGet == null){
                throw new ClassProjectsNotFound();
            }else{
                return $metaDataResponseGet;
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
