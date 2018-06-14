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

    //Revisar la ubicació d'aquest mètode
    public function getThisProject($id) {
        return $this->pageDataQuery->getThisProject($id);
    }

    /**
     * Valida que exista el nombre de usuario que se desea utilizar
     */
    public function validaNom($nom) {
        global $auth;
        return ($auth->getUserCount(['user' => $nom]) > 0);
    }

    public function createDataDir($id) {
        $this->projectMetaDataQuery->createDataDir($id);
    }    
    
    public function createFolder($new_folder){
        return $this->projectMetaDataQuery->createFolder(str_replace(":", "/", $new_folder));
    }

    public function folderExists($ns) {
        $id = str_replace(":", "/", $ns);
        return file_exists($id) && is_dir($id);
    }
}
