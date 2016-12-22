<?php
if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_INC . 'inc/common.php');
require_once DOKU_PLUGIN . "wikiiocmodel/datamodel/WikiRenderizableDataModel.php";
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataService.php');

/**
 * Description of ProjectModel
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class ProjectModel extends WikiRenderizableDataModel
{

    protected $id;
    protected $projectType;

    protected $metaDataService;
    protected $persistenceEngine;


    const defaultSubset = 'main';

    public function __construct($persistenceEngine)
    {
        $this->metaDataService= new MetaDataService();
        $this->persistenceEngine = $persistenceEngine;

//        $this->pageDataQuery = $persistenceEngine->createPageDataQuery();
//        $this->draftDataQuery = $persistenceEngine->createDraftDataQuery();
//        $this->lockDataQuery = $persistenceEngine->createLockDataQuery();
    }

    public function init($id, $projectType = null)
    {
        $this->id = $id;
        $this->projectType = $projectType;
    }

    // Set metadata
    public function setData($toSet)
    {

        // En aquest cas el $toSet equival al $query, que es genera al Action corresponent
        $meta = $this->metaDataService->setMeta($toSet);
        // El retorn es un array, agrupats:
        // Primer nivell: project-type
        // Segon nivell: idResource
        // Per tant, aquí sempre voldrem el [0][0] perquè només demanem un id i un projecttype
        $metaJSON = json_decode($meta[0][0], true);
        $ret['projectMetaData']['values'] = json_decode($metaJSON['metaDataValue'], true);
        $ret['projectMetaData']['structure'] = json_decode($metaJSON['metaDataStructure'], true);


//        $ret = [];
//
//        $ret['structure'] = json_decode($this->projectMetaDataQuery->getMetaDataConfig($this->projectType, 'main', self::METADATA_PROJECT_STRUCTURE), true); // TODO [Xavi] com obtenim el subset?
//
//        $filename = $ret['structure']['main'];  // TODO [Xavi] com obtenim el subset?
//
//        $ret['values'] = json_decode($this->projectMetaDataQuery->getMeta($this->id, $this->projectType, 'main', $filename), true);
        return $ret;


//        return $this->metaDataService->setMeta($toSet);

    }

    // Get metadata

    public function getViewData()
    {
//        $ret['structure'] = self::getStructuredDocument($this->pageDataQuery, $this->id,
//            $this->editing, $this->selected,
//            $this->rev);
//        if ($this->draftDataQuery->hasAny($this->id)) {
//            $ret['draftType'] = self::FULL_DRAFT;
//            $ret['draft'] = $this->getDraftAsFull();
//        }
//        return $ret;

        return $this->getRawData();
    }


    const METADATA_CLASSES_NAMESPACES = 'metaDataClassesNameSpaces';
    const METADATA_PROJECT_STRUCTURE = 'metaDataProjectStructure';

    public function getRawData()
    {
        $ret = [];
//        $ret['structure'] = json_decode($this->projectMetaDataQuery->getMetaDataConfig($this->projectType, 'main', self::METADATA_PROJECT_STRUCTURE), true); // TODO [Xavi] com obtenim el subset?

        $query = [
            'persistence' => $this->persistenceEngine,
            'projectType' => $this->projectType,
            'metaDataSubSet' => self::defaultSubset, // TODO[Xavi] Com es passa el paràmetre si és necessari?
            'idResource' => $this->id,
//            'filter' => '', // TODO[Xavi] Com es passa el paràmetre si és necessari?

        ];

        $meta = $this->metaDataService->getMeta($query)[0];

        $ret['projectMetaData']['values'] = $meta['values'];
        $ret['projectMetaData']['structure'] = $meta['structure']; // inclou els valors

//        $ret = [];
//
//        $ret['structure'] = json_decode($this->projectMetaDataQuery->getMetaDataConfig($this->projectType, 'main', self::METADATA_PROJECT_STRUCTURE), true); // TODO [Xavi] com obtenim el subset?
//
//        $filename = $ret['structure']['main'];  // TODO [Xavi] com obtenim el subset?
//
//        $ret['values'] = json_decode($this->projectMetaDataQuery->getMeta($this->id, $this->projectType, 'main', $filename), true);
        return $ret;

//        $id = $this->id;
//        $response['locked'] = checklock($id);
//        $response['content'] = $this->pageDataQuery->getRaw($id, $this->rev);
//        if ($this->draftDataQuery->hasAny($id)) {
//            $response['draftType'] = self::FULL_DRAFT;
////            $response['draft'] = $this->getDraftAsFull();
//        }else{
//            $response['draftType'] = self::NO_DRAFT;
//        }
//
//        return $response;
    }
}
