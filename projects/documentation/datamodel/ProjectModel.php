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
class ProjectModel extends WikiRenderizableDataModel {

    protected $id;
    protected $projectType;
    protected $metaDataService;
    protected $persistenceEngine;
    protected $dataquery;

    const METADATA_CLASSES_NAMESPACES = 'metaDataClassesNameSpaces';
    const METADATA_PROJECT_STRUCTURE = 'metaDataProjectStructure';
    const defaultSubset = 'main';

    public function __construct($persistenceEngine)  {
        $this->metaDataService= new MetaDataService();
        $this->persistenceEngine = $persistenceEngine;
        $this->dataquery = $persistenceEngine->createProjectMetaDataQuery();
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

    // Get metadata
    public function getViewData() {
        return $this->getRawData();
    }

    public function getRawData() {
        $ret = [];
        $query = [
            'persistence' => $this->persistenceEngine,
            'projectType' => $this->projectType,
            'metaDataSubSet' => self::defaultSubset, // TODO[Xavi] Com es passa el paràmetre si és necessari?
            'idResource' => $this->id
        ];

        $meta = $this->metaDataService->getMeta($query)[0];

        $ret['projectMetaData']['values'] = $meta['values'];
        $ret['projectMetaData']['structure'] = $meta['structure']; // inclou els valors

        return $ret;
    }
    
    public function createDataDir($id) {
        //Esto debería pasar a ProjectMetaDataQuery
        global $conf;
        $id = str_replace(':', '/', $id);
        $dir = $conf['datadir'] . '/' . utf8_encodeFN($id) . "/dummy";
        $this->dataquery->makeFileDir($dir);
    }
    
    public function existProject($id) {
        //Este es un modelo, tal vez, demasiado complicado para averiguar si ya existe el proyecto
        $query = [
            'persistence' => $this->persistenceEngine,
            'projectType' => $this->projectType,
            'metaDataSubSet' => self::defaultSubset,
            'idResource' => $id
        ];
        $ret = $this->dataquery->isDirProject($query);
        return $ret;
    }
}
