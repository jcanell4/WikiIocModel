<?php
/**
 * Description of AbstractWikiDataModel
 *
 * @author josep
 */
abstract class AbstractWikiDataModel {
    
    protected $persistenceEngine;
    
    public function __construct($persistenceEngine) {
        $this->persistenceEngine = $persistenceEngine;
    }

    public abstract function getData();
    
    public abstract function setData($toSet);
    
    public function getPersistenceEngine(){
        return $this->persistenceEngine;
    }

}
