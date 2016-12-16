<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_PLUGIN . "wikiiocmodel/datamodel/WikiRenderizableDataModel.php");
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataService.php');

/**
 * Description of ProjectModel
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class ProjectModel extends AbstractWikiDataModel /*WikiRenderizableDataModel*/ {  //JOSEP: He fet un canvi d'herencia si realment ProjectModel no ha d'implementar getRawData

    protected $id;
    protected $projectType;
    protected $metaDataService;
    protected $persistenceEngine;
    protected $projectMetaDataQuery;
    protected $pageDataQuery;

    const METADATA_CLASSES_NAMESPACES = 'metaDataClassesNameSpaces';
    const METADATA_PROJECT_STRUCTURE = 'metaDataProjectStructure';
    const defaultSubset = 'main';

    public function __construct($persistenceEngine)  {
        $this->metaDataService= new MetaDataService();
        $this->persistenceEngine = $persistenceEngine;
        $this->projectMetaDataQuery = $persistenceEngine->createProjectMetaDataQuery();
        $this->pageDataQuery = $persistenceEngine->createPageDataQuery();
    }

    public function init($id, $projectType = null) {
        $this->id = $id;
        $this->projectType = $projectType;
    }

    // Set metadata
    public function setData($toSet) {
        $ret = [];
        // En aquest cas el $toSet equival al $query, que es genera al Action corresponent
        $meta = $this->metaDataService->setMeta($toSet);
        // El retorn es un array, agrupats:
        // Primer nivell: project-type
        // Segon nivell: idResource
        // Per tant, aquí sempre voldrem el [0][0] perquè només demanem un id i un projecttype
        $metaJSON = json_decode($meta[0][0], true);
        $ret['projectMetaData']['values'] = json_decode($metaJSON['MetaDataValue'], true);
        $ret['projectMetaData']['structure'] = json_decode($metaJSON['metaDataStructure'], true);

        return $ret;
    }

    public function getData() {
        $ret = [];
        $query = [
            'persistence' => $this->persistenceEngine,
            'projectType' => $this->projectType,
            'metaDataSubSet' => self::defaultSubset,
            'idResource' => $this->id
        ];

        $meta = $this->metaDataService->getMeta($query)[0];
        $ret['projectMetaData']['values'] = $meta['values'];
        $ret['projectMetaData']['structure'] = $meta['structure']; //inclou els valors

        $ret['projectViewData'] = $this->projectMetaDataQuery->getMetaViewConfig($this->projectType, "defaultView");
        
        return $ret;
    }
    
//    public function getMetaDataDef($id, $projectType) {
//        $ret0 = $this->metaDataService->getMetaDataElements();
//        $dao = $this->metaDataService->getMetaDataDaoConfig();
//        $mdNS = $dao->getMetaDataConfig($projectType, self::defaultSubset, $this->persistenceEngine, "metaDataClassesNameSpaces");
//        $mdStruc = $this->projectMetaDataQuery->getMetaDataConfig($projectType, self::defaultSubset, $this->persistenceEngine, "metaDataProjectStructure");
//        $ret2 = $dao->getMetaDataStructure($projectType, self::defaultSubset, $this->persistenceEngine);
//        return $ret2;
//    }
    
    public function createDataDir($id) {
        $this->projectMetaDataQuery->createDataDir($id);
    }
    
    public function existProject($id) {
        return $this->projectMetaDataQuery->haveADirProject($id);
    }
    
    /**
     * Indica si el proyecto ya ha sido generado
     * @return boolean
     */
    public function isProjectGenerated($id, $projectType) {
        return $this->projectMetaDataQuery->isProjectGenerated($id, $projectType);
    }

    public function generateProject($id, $destino, $projectType, $plantilla) {
        /*
         * 1. Crea el archivo 'continguts' a partir de la plantilla especificada
         */
        $text = $this->pageDataQuery->getRaw($plantilla);
        $this->pageDataQuery->save($destino, $text, "generate project");
        /*
         * 2. Establece la marca de proyecto generado
         */
        $this->projectMetaDataQuery->setProjectGenerated($id, $projectType);
        /*
         * 3. Otorga permisos
         */
    }
}
