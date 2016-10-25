<?php
/**
 * Description of DataQuery
 *
 * @author josep
 */

if (! defined('DOKU_INC')) die();

require_once (DOKU_INC . 'inc/pageutils.php');
require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/io.php');

abstract class DataQuery {
    /**
     * Retorna l'espai de noms que conté el fitxer identificat per $id
     * @param string $id és l'identificador del fitxer d'on extreu l'espai de noms
     * @return string amb l'espai de noms extret
     */
    public function getNs($id){
        return getNS($id);
    }
    
    /**
     * Busca si la ruta (id) contiene un directorio de proyecto
     * @param type string 'id'
     * @return type boolean
     */
    public function haveADirProject($id) {
        $ret = false;
        $metaDataPath = WikiGlobalConfig::getConf('mdprojects');
        $metaDataExtension = WikiGlobalConfig::getConf('mdextension');

        $path = utf8_encodeFN(str_replace(':', '/', $id));
        $aDir = explode("/", $path);
        foreach ($aDir as $dir) {
            $metaDataPath .= "/$dir";
            $ret = ($this->isProject($metaDataPath, 1, $metaDataExtension) !== NULL);
            if ($ret) break;
        }
        return $ret;
    }
    
    /**
     * Retorna el nom simple (sense els espais de noms que el contenen) del 
     * firxer o directori identificat per $id
     * @param type $id
     * @return string contenint el nom simple del fitxer o directori
     */
    public function getIdWithoutNs($id){
        return noNS($id);
    }
    
    public abstract function getFileName($id, $especParams=NULL);

    public abstract function getNsTree($currentNode, $sortBy, $onlyDirs=FALSE, $expandProject=FALSE, $hiddenProjects=FALSE);

    /**
     * Retorna la llista de fitxers continguts a l'espai de noms identificat per $ns
     * @param string $ns és l'espai de noms d'on consultar la llista
     * @return array amb la llista de fitxers
     */
    public function getFileList($ns) {
        $dir = $this->getFileName( $ns );
        $arrayDir = scandir( $dir );
        if ( $arrayDir ) {
                unset( $arrayDir[0] );
                unset( $arrayDir[1] );
                $arrayDir = array_values( $arrayDir );
        } else {
                $arrayDir = array();
        }

        return $arrayDir;        
    }
    
    public function resolve_id($ns,$id,$clean=true){
        resolve_id($ns, $id, $clean);
    }
    
    /**
    * Crea el directori on ubicar el fitxer referenciat per $filePath després
    * d'extreure'n el nom del fitxer. Aquesta funció no crea directoris recursivamnent.
    *
    * @param type $filePath
    */
    public function makeFileDir( $filePath ) {
           io_makeFileDir( $filePath );
    }
    
    /**
     * Mètode privat que obté l'arbre de directoris a partir d'un espai de noms
     * i el sistema de dades concret d'on obtenir-lo (media, data, meta, etc)
     * mlozan54: també retorna si el directori és un projecte o el directori o fitxer és a dins d'un projecte
     *      Node                        Tipus de retorn
     *      Directori                      d
     *      Fitxer                         f
     *      Projecte                       p
     *      Directori dins de projecte     pd
     *      Fitxer dins de projecte        pf
     * @param type $base
     * @param type $currentnode
     * @param type $sortBy
     * @param type $onlyDirs
     * @return string
     */
    protected function getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs=FALSE, $expandProject=FALSE, $hiddenProjects=FALSE ) {
    	return $this->getNsTreeFromGenericSearch( $base, $currentnode, $sortBy, $onlyDirs, 'search_index', $expandProject, $hiddenProjects);
    }
    
    protected function getNsTreeFromGenericSearch( $base, $currentnode, $sortBy, $onlyDirs=FALSE, $function='search_index', $expandProject=FALSE, $hiddenProjects=FALSE ) {
    
        $sortOptions = array( 0 => 'name', 'date' );
        $nodeData    = array();
        $children    = array();
                
        if ( $currentnode == "_" ) {
            return array( 'id' => "", 'name' => "", 'type' => 'd' );
        }
        if ( $currentnode ) {
            $node  = $currentnode;
            $aname = split( ":", $currentnode );
            $level = count( $aname );
            $name  = $aname[ $level - 1 ];
        }else {
            $node  = '';
            $name  = '';
            $level = 0;
        }
        $sort = $sortOptions[ $sortBy ];

        $opts = array('ns' => $node);
        if ($function == 'search_universal') {
            global $conf;
            $opts = array(
                'ns' => $node,
                'listdirs' => true,
                'listfiles' => true,
                'sneakyacl' => $conf['sneaky_index']
            );
        }
        $dir = str_replace(':', '/', $node);
        search($nodeData, $base, $function, $opts, $dir, 1);

        $metaDataPath = WikiGlobalConfig::getConf('mdprojects');
        $metaDataExtension = WikiGlobalConfig::getConf('mdextension');
        $pathProject = $metaDataPath . '/' . $dir;
        $itemProject = $this->isProject($pathProject, 1, $metaDataExtension);
        
        if ($itemProject !== NULL) {
            $children = $this->fillProjectNode($nodeData, $level, $itemProject, $onlyDirs, $hiddenProjects, $expandProject, $metaDataPath, $metaDataExtension);
        }else {
            $children = $this->fillNode($nodeData, $level, $onlyDirs, $hiddenProjects, $metaDataPath, $metaDataExtension);
        }
        
        array_unshift($children,"noname");  //Se usa para renumerar desde 0 las claves del array
        array_shift($children);             //que se desmelenan al excluir los directorios de proyectos

        $tree = array(
                'id'       => $node,
                'name'     => $node,
                'type'     => 'd',
                'children' => $children
        );

        return $tree;
    }

    private function fillProjectNode($nodeData, $level, $itemProject, $onlyDirs, $hiddenProjects, $expandProject, $metaDataPath, $metaDataExtension) {
        $children = array();
        $projectType = $itemProject['projectType'];
        $levelProject = $itemProject['levelProject'];
        $isProject = TRUE;

        foreach (array_keys($nodeData) as $item) {

            $type = 'd';

            if ($onlyDirs && $nodeData[$item]['type'] == 'd' || !$onlyDirs) {
                if ($nodeData[$item]['type'] == 'd') {
                    if ($levelProject == $nodeData[$item]['level']) {
                        //Determinar si és projecte
                        $pathProject = $metaDataPath . '/' . str_replace(':', '/', $nodeData[$item]['id']);
                        
                        $itemProject = $this->isProject($pathProject, $nodeData[$item]['level'], $metaDataExtension);
                        $isProject = ($itemProject !== NULL);

                        if ($isProject && $hiddenProjects == FALSE) {
                            $levelProject = $itemProject['levelProject'];
                            $type = $itemProject['type'];
                            $projectType = $itemProject['projectType'];
                            $children[$item]['projectType'] = $projectType;
                        }

                        if (!$isProject || $hiddenProjects == FALSE) {
                            $children[$item]['id'] = $nodeData[$item]['id'];
                            $aname = explode(":", $nodeData[$item]['id']);
                            $children[$item]['name'] = $aname[$level];
                            $children[$item]['type'] = $type;
                        }
                            
                    }else {
                        //Subnivell de projecte - i només quan s'ha d'expadir el projecte
                        if ($expandProject) {
                            $children[$item]['id'] = $nodeData[$item]['id'];
                            $aname = explode(":", $nodeData[$item]['id']);
                            $children[$item]['name'] = $aname[$level];
                            $children[$item]['type'] = 'pd';
                            $children[$item]['projectType'] = $projectType;
                        }
                    }
                }else {
                    //fitxer de projecte o no
                    if ($isProject) {
                        if ($expandProject) {
                            $children[$item]['id'] = $nodeData[$item]['id'];
                            $aname = explode(":", $nodeData[$item]['id']);
                            $children[$item]['name'] = $aname[$level];
                            $children[$item]['type'] = 'pf';
                            $children[$item]['projectType'] = $projectType;
                        }
                    }else {
                        $children[$item]['id'] = $nodeData[$item]['id'];
                        $aname = explode(":", $nodeData[$item]['id']); 
                        $children[$item]['name'] = $aname[$level];
                        $children[$item]['type'] = $nodeData[$item]['type'];
                    }
                }
            }
        }
        return $children;
    }
    
    private function fillNode($nodeData, $level, $onlyDirs, $hiddenProjects, $metaDataPath, $metaDataExtension) {
        $children = array();
        
        foreach (array_keys($nodeData) as $item) {

            if ($onlyDirs && $nodeData[$item]['type'] == 'd' || !$onlyDirs) {

                if ($nodeData[$item]['type'] == 'd') {
                    $type = 'd';
                    $pathProject = $metaDataPath . '/' . str_replace(':', '/', $nodeData[$item]['id']);
                    $itemProject = $this->isProject($pathProject, $nodeData[$item]['level'], $metaDataExtension);
                    $isProject = ($itemProject !== NULL);

                    if ($isProject && $hiddenProjects == FALSE) {
                        $type = $itemProject['type'];
                        $children[$item]['projectType'] = $itemProject['projectType'];
                    }

                    if (!$isProject || $hiddenProjects == FALSE) {
                        $children[$item]['id'] = $nodeData[$item]['id'];
                        $aname = explode(":", $nodeData[$item]['id']);
                        $children[$item]['name'] = $aname[$level];
                        $children[$item]['type'] = $type;
                    }
                            
                } else {
                    $children[$item]['id'] = $nodeData[$item]['id'];
                    $aname = explode(":", $nodeData[$item]['id']); 
                    $children[$item]['name'] = $aname[$level];
                    $children[$item]['type'] = $nodeData[$item]['type'];
                }
            }
        }
        return $children;
    }
    
    private function isProject($pathProject, $level, $metaDataExtension) {
        if (is_dir($pathProject)) {
            $dirProject = opendir($pathProject);
            while ($current = readdir($dirProject)) {
                $pathProjectOne = $pathProject . '/' . $current;
                if (is_dir($pathProjectOne)) {
                    $dirProjectOne = opendir($pathProjectOne);
                    while ($currentOne = readdir($dirProjectOne)) {
                        if (!is_dir($pathProjectOne . '/' . $currentOne)) {
                            $fileTokens = explode(".", $currentOne);
                            if ($fileTokens[sizeof($fileTokens) - 1] == $metaDataExtension) {
                                //És projecte
                                $ret['levelProject'] = $level;
                                $ret['type'] = 'p';
                                $ret['projectType'] = $current;
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }
}
