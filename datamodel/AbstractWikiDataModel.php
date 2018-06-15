<?php
/**
 * Description of AbstractWikiDataModel
 * @author josep
 */
abstract class AbstractWikiDataModel extends AbstractWikiModel{

    protected $pageDataQuery;
    protected $projectMetaDataQuery;

    public function __construct($persistenceEngine) {
        parent::__construct($persistenceEngine);
        $this->projectMetaDataQuery = $persistenceEngine->createProjectMetaDataQuery();
        $this->pageDataQuery = $persistenceEngine->createPageDataQuery();
    }

    public function getPersistenceEngine(){
        return $this->persistenceEngine;
    }

    public function getProjectMetaDataQuery() {
        return $this->projectMetaDataQuery;
    }

    public function getPageDataQuery() {
        return $this->pageDataQuery;
    }

    public function getThisProject($id) {
        return $this->getPageDataQuery()->getThisProject($id);
    }

    public function haveADirProject($id) {
        return $this->getPageDataQuery()->haveADirProject($id);
    }

    /**
     * Valida que exista el nombre de usuario que se desea utilizar
     */
    public function validaNom($nom) {
        global $auth;
        return ($auth->getUserCount(['user' => $nom]) > 0);
    }

    public function createDataDir($id) {
        $this->getProjectMetaDataQuery()->createDataDir($id);
    }

    public function createFolder($new_folder){
        return $this->getProjectMetaDataQuery()->createFolder(str_replace(":", "/", $new_folder));
    }

    public function folderExists($ns) {
        $id = str_replace(":", "/", $ns);
        return file_exists($id) && is_dir($id);
    }

    public function getListProjectTypes($projectType=NULL) {
        return $this->getProjectMetaDataQuery()->getListProjectTypes($projectType);
    }

    public function getListMetaDataComponentTypes($projectType, $component) {
        return $this->getProjectMetaDataQuery()->getListMetaDataComponentTypes($projectType, $component);
    }

}
