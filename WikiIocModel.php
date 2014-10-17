<?php

/**
 * Interface WikiIocModel
 *
 * @author Josep Cañellas <jcanell4@ioc.cat>
 */
interface WikiIocModel {
    public function getHtmlPage($pid, $prev = NULL);

    public function getCodePage($pid, $prev = NULL, $prange = NULL);

    public function cancelEdition($pid, $prev = NULL);

    public function saveEdition($pid, $prev = NULL, $prange = NULL,
        $pdate = NULL, $ppre = NULL, $ptext = NULL, $psuf = NULL, $psum = NULL);

    public function isDenied();
    
    public function getMediaFileName($id, $rev = '');
    
    public function getIdWithoutNs($id);
    
    public function getMediaList($ns);
    
    public function imagePathToId($path) ;

    public function getPageFileName($id, $rev = '');
    
    public function getMediaUrl($id, $rev = FALSE, $meta = FALSE);
    
    public function uploadImage($nsTarget, $idTarget, $filePathSource, $overWrite = FALSE);
    
    public function saveImage($nsTarget, $idTarget, $filePathSource, $overWrite = FALSE);
    
    public function getNsTree($currentnode, $sortBy, $onlyDirs = FALSE);
    
    public function getGlobalMessage($id);
    
    public function makeFileDir($filePath);
    
}