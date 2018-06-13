<?php
/**
 * Description of AbstractWikiDataModel
 *
 * @author josep
 */
abstract class AbstractWikiDataModel {

    protected $persistenceEngine;
    protected $pageDataQuery;

    public function __construct($persistenceEngine) {
        $this->persistenceEngine = $persistenceEngine;
    }

    public abstract function getData();

    public abstract function setData($toSet);

    public function getPersistenceEngine(){
        return $this->persistenceEngine;
    }

    public function getThisProject($id) {
        return $this->pageDataQuery->getThisProject($id);
    }

}
