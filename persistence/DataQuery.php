<?php
/**
 * Description of DataQuery
 * @author josep
 */
if (! defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once (DOKU_INC . 'inc/pageutils.php');
require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/io.php');
require_once (DOKU_PLUGIN . 'ajaxcommand/defkeys/ProjectKeys.php');

abstract class DataQuery {
    const K_PROJECTTYPE  = ProjectKeys::KEY_PROJECT_TYPE;
    const K_ID           = AjaxKeys::KEY_ID;
    const K_NS           = AjaxKeys::KEY_NS;
    const K_NAME         = "name";
    const K_NSPROJECT    = "nsproject";
    const K_TYPE         = "type";

    public abstract function getFileName($id, $especParams=NULL);

    public abstract function getNsTree($currentNode, $sortBy, $onlyDirs=FALSE, $expandProject=FALSE, $hiddenProjects=FALSE, $root=FALSE);

    /**
     * Busca si la ruta (id) contiene un directorio de proyecto
     * @param string 'id'
     * @return boolean
     */
    public function haveADirProject($id) {
        $ret = $this->getNsItems($id);
        return isset($ret[self::K_PROJECTTYPE]);
    }

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

    /**
     * Retorna l'espai de noms que conté el fitxer identificat per $id
     * @param string $id és l'identificador del fitxer d'on extreu l'espai de noms
     * @return string amb l'espai de noms extret
     */
    public function getNs($id){
        return getNS($id);
    }

    /**
     * Retorna el nom simple (sense l'espais de noms) del fitxer o directori identificat per $id
     * @param string $id
     * @return string contenint el nom simple del fitxer o directori
     */
    public function getIdWithoutNs($id){
        return noNS($id);
    }

    public function resolve_id($ns,$id,$clean=true){
        resolve_id($ns, $id, $clean);
    }

    /**
    * Crea el directori on ubicar el fitxer referenciat per $filePath després
    * d'extreure'n el nom del fitxer. Aquesta funció no crea directoris recursivamnent.
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
     * @param string $base
     * @param string $currentnode (ruta en formato wiki)
     * @param integer $sortBy [0|1]
     * @param boolean $onlyDirs
     * @param boolean $expandProject
     * @param boolean $hiddenProjects
     * @param string $root
     * @return json conteniendo el nodo actual con sus propiedades y sus hijos, con sus propiedades, a 1 nivel de profundidad
     */
    protected function getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs=FALSE, $expandProject=FALSE, $hiddenProjects=FALSE, $root=FALSE) {
    	return $this->getNsTreeFromGenericSearch( $base, $currentnode, $sortBy, $onlyDirs, 'search_index', $expandProject, $hiddenProjects, $root);
    }

    protected function getNsTreeFromGenericSearch( $base, $currentnode, $sortBy, $onlyDirs=FALSE, $function='search_index', $expandProject=FALSE, $hiddenProjects=FALSE, $root=FALSE ) {
        $nodeData    = array();
        $children    = array();
        $sortOptions = array(self::K_NAME, 'date');    //no se usa

        if ( $currentnode == "_" ) {
            $path = $base.'/'.($root ? "$root/" : "");
            $path = str_replace(':', '/', $path);
            if (is_dir($path)){
                $itemsProject = $this->getNsItems($root);
                $name = ($root) ? $root : "";
                $type = $itemsProject[self::K_TYPE];
            }else{
                $name = $root;
                $type = "f";
            }
            $ret = array(
                      self::K_ID => "",
                      self::K_NAME => $name,
                      self::K_TYPE => $type
                   );
            if ($itemsProject[self::K_PROJECTTYPE])
                $ret[self::K_PROJECTTYPE] = $itemsProject[self::K_PROJECTTYPE];

            return $ret;
        }

        if ( $currentnode ) {
            $node  = $currentnode;
            $aname = split(":", $node);
            $level = count($aname);
            $name  = $aname[$level - 1]; //ns (espacio de nombres, es decir, padre)
        } else {
            $node  = ($root) ? $root : "";
            $aname = split( ":", $node );
            $level = ($root) ? count($aname) : 0;
            $name  = ($root) ? $root : "";
        }
        $sort = $sortOptions[$sortBy];  //no se usa

        $opts = array(self::K_NS => $node);
        if ($function == 'search_universal') {
            global $conf;
            $opts = array(
                self::K_NS => $node,
                'listdirs' => true,
                'listfiles' => true,
                'sneakyacl' => $conf['sneaky_index']
            );
        }
        $dir = str_replace(':', '/', $node);
        search($nodeData, $base, $function, $opts, $dir, $level);

        $itemsProject = $this->getNsItems($node, $level);

        if ($itemsProject[self::K_PROJECTTYPE]) {
            if ($expandProject) {
                $children = $this->fillProjectNode($nodeData, $level, $itemsProject, $onlyDirs);
            }
        }else {
            $children = $this->fillNode($nodeData, $level, $onlyDirs, $hiddenProjects);
        }

        $tree = array(
                   self::K_ID   => $node,
                   self::K_NAME => $name,
                   self::K_TYPE => $itemsProject[self::K_TYPE]
                );
        if ($itemsProject[self::K_PROJECTTYPE]) {
            $tree[self::K_PROJECTTYPE] = $itemsProject[self::K_PROJECTTYPE];
            $tree[self::K_NSPROJECT]   = $itemsProject[self::K_NSPROJECT];
        }
        $tree['children'] = $children;
//        Logger::debug("getNsTreeFromGenericSearch: \$params=".json_encode(array('base'=>$base,'currentnode'=>$currentnode,'sortBy'=>$sortBy,'onlyDirs'=>$onlyDirs,'function'=>$function,'expandProject'=>$expandProject,'hiddenProjects'=>$hiddenProjects,'root'=>$root))."\n".
//                      "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\$tree=".json_encode($tree)."\n".
//                      "\$tree=".print_r($tree, TRUE), 0, __LINE__, "DataQuery", -1, TRUE);
        return $tree;
    }

    /**
     * Pone atributos a los hijos incluidos en $nodeData
     * @param array $nodeData lista del primer nivel de directorios y ficheros
     *              $nodeData = [$id, $ns=ns del padre, $perm, $type=[d|f], $level>=1, open]
     * @param integer $level
     * @param array $itemsProject
     * @param boolean $onlyDirs
     * @return array con todos los hijos incluidos en $nodeData con sus propiedades
     */
    private function fillProjectNode($nodeData, $level, $itemsProject, $onlyDirs) {
        $children = array();

//        Logger::debug("fillProjectNode->nodeData: ".json_encode($nodeData), 0, __LINE__, "DataQuery", -1, TRUE);
//        Logger::debug("fillProjectNode->itmsProject: ".json_encode($itemsProject), 0, __LINE__, "DataQuery", -1, TRUE);

        foreach (array_keys($nodeData) as $item) {

            if ($onlyDirs && $nodeData[$item][self::K_TYPE] == 'd' || !$onlyDirs) {
                $children[$item][self::K_ID] = $nodeData[$item][self::K_ID];
                $children[$item][self::K_NAME] = explode(":", $nodeData[$item][self::K_ID])[$level];
                $children[$item][self::K_TYPE] = "p".$nodeData[$item][self::K_TYPE];
                $children[$item][self::K_PROJECTTYPE] = $itemsProject[self::K_PROJECTTYPE];
                $children[$item][self::K_NSPROJECT] = $itemsProject[self::K_NSPROJECT];
            }
        }
        return $children;
    }

    /**
     * Pone atributos a los hijos incluidos en $nodeData
     * @param array $nodeData lista del primer nivel de directorios y ficheros [id, ns, perm, type, level, open]
     * @param int $level
     * @param bool $onlyDirs
     * @param bool $hiddenProjects
     * @return array lista de hijos con sus atributos
     */
    //private function fillNode($nodeData, $level, $onlyDirs, $hiddenProjects, $metaDataPath, $metaDataExtension) {
    private function fillNode($nodeData, $level, $onlyDirs, $hiddenProjects) {
        $children = array();

        foreach (array_keys($nodeData) as $item) {

            if ($onlyDirs && $nodeData[$item][self::K_TYPE] == 'd' || !$onlyDirs) {

                if ($nodeData[$item][self::K_TYPE] == 'd') {
                    $itemsProject = $this->getNsItems($nodeData[$item][self::K_ID]);
                    $isProject = ($itemsProject[self::K_PROJECTTYPE] !== NULL);

                    if (!$isProject || $hiddenProjects == FALSE) {
                        $children[$item][self::K_ID] = $nodeData[$item][self::K_ID];
                        $children[$item][self::K_NAME] = explode(":", $nodeData[$item][self::K_ID])[$level];
                        $children[$item][self::K_TYPE] = $itemsProject[self::K_TYPE];
                    }

                    if ($isProject && $hiddenProjects == FALSE) {
                        $children[$item][self::K_PROJECTTYPE] = $itemsProject[self::K_PROJECTTYPE];
                        $children[$item][self::K_NSPROJECT] = $itemsProject[self::K_NSPROJECT];
                    }

                } else {
                    $children[$item][self::K_ID] = $nodeData[$item][self::K_ID];
                    $children[$item][self::K_NAME] = explode(":", $nodeData[$item][self::K_ID])[$level];
                    $children[$item][self::K_TYPE] = $nodeData[$item][self::K_TYPE];
                }
            }
        }
        array_unshift($children,"noname");  //Se usa para renumerar desde 0 las claves del array
        array_shift($children);             //que se desmelenan al excluir los directorios de proyectos
        return $children;
    }

    /**
     * Evalua que tipo de elemento es la ruta $ns y retorna las propiedades que le son propias
     * @param type $ns : ns (ruta wiki relativa a pages) que se evalúa
     * @return array : propiedades del elemento $ns
     */
    public function getNsItems($ns, $level=1) {
        $datadir = WikiGlobalConfig::getConf('datadir');
        $metaDataPath = WikiGlobalConfig::getConf('mdprojects');
        $metaDataExtension = WikiGlobalConfig::getConf('mdextension');

        $pathElement = $metaDataPath."/".str_replace(":", "/", $ns);
        $camins = ($ns) ? explode(":", $ns) : NULL;
        $page = "$datadir/";
        if ($camins)
            $page .= implode("/", $camins);

        $ret[self::K_TYPE] = is_dir($page) ? "d" : (is_file($page) ? "f" : "none");

        while ($camins) {
            $nsElement = implode(":", $camins);
            $parentDir = $metaDataPath."/".implode("/", $camins);
            if (is_dir($parentDir)) {
                $fh1 = opendir($parentDir);
                while ($current = readdir($fh1)) {
                    $currentDir = "$parentDir/$current";
                    if (is_dir($currentDir) && $current!=="." && $current!=="..") {
                        $ret = $this->getProjectProperties($pathElement, $currentDir, $nsElement, $current, $metaDataExtension);
                        if ($ret[self::K_PROJECTTYPE]) {
//                            Logger::debug("getNsItems(p): \$ns=$ns, \$ret=".json_encode($ret), 0, __LINE__, "DataQuery", -1, TRUE);
                            return $ret;
                        }
                    }
                }
            }
            array_pop($camins);
        }
//        Logger::debug("getNsItems: \$ns=$ns, \$ret=".json_encode($ret), 0, __LINE__, "DataQuery", -1, TRUE);
        return $ret;
    }

    /**
     * Busca averiguar si $currentDir es un directorio de proyecto, es decir, si contiene los ficheros de proyecto
     * @param type $pathElement : nombre original del elemento/archivo que se examina (con ruta en mdprojects)
     * @param type $currentDir : ruta absoluta al directorio que se desea explorar para averiguar si contiene el fichero de proyecto
     * @param type $nsElement : ruta absoluta al padre del directorio $currentDir
     * @param type $dirName : nombre del directorio $currentDir
     * @param type $metaDataExtension : extensión del nombre del fichero de proyecto
     * @return type array con atributos del proyecto
     */
    private function getProjectProperties($pathElement, $currentDir, $nsElement, $dirName, $metaDataExtension) {

        $ret[self::K_TYPE] = is_dir($currentDir) ? "d" : "f";
        $fh2 = opendir($currentDir);

        while ($currentOne = readdir($fh2)) {
            if (!is_dir("$currentDir/$currentOne")) {
                $fileTokens = explode(".", $currentOne);
                if ($fileTokens[sizeof($fileTokens) - 1] === $metaDataExtension) {
                    $ret[self::K_TYPE] = "p" . (("$pathElement/$dirName" === $currentDir) ? "" : $ret[self::K_TYPE]);
                    $ret[self::K_PROJECTTYPE] = $dirName;
                    $ret[self::K_NSPROJECT] = $nsElement;
                    return $ret;
                }
            }
        }
        return $ret;
    }
}
