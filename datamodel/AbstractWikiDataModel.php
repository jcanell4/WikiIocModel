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
     * Valida que exista el nombre de usuario que se desea utilizar (pueden ser varios nombres)
     */
    public function validaNom($nom) {
        global $auth;
        $aNoms = preg_split("/[\s,]+/", $nom);
        if (!empty($aNoms)) {
            $ret = TRUE;
            foreach ($aNoms as $n) {
                $ret &= ($auth->getUserCount(['user' => $n]) > 0);
            }
        }
        return $ret;
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

    public function fileExistsInProject($id, $file) {
        $ns = str_replace(":", "/", $id);
        $fileList = $this->getPageDataQuery()->getFileList($ns);
        if ($fileList) {
            $ret = in_array($file, $fileList);
        }
        return $ret;
    }

    public function getListProjectTypes($projectType=NULL, $metaDataSubset=NULL, $projectTypeDir=NULL) {
        return $this->getProjectMetaDataQuery()->getListProjectTypes($projectType, $metaDataSubset, $projectTypeDir);
    }

    public function getListMetaDataComponentTypes($projectType, $metaDataPrincipal, $metaDataSubSet, $component) {
        return $this->getProjectMetaDataQuery()->getListMetaDataComponentTypes($projectType, $metaDataPrincipal, $metaDataSubSet, $component);
    }

}
