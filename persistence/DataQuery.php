<?php

if (!defined('DOKU_INC'))
    die();

require_once (DOKU_INC . 'inc/pageutils.php');
require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/io.php');

/**
 * Description of DataQuery
 *
 * @author josep
 */
abstract class DataQuery {

    /**
     * Retorna l'espai de noms que conté el fitxer identificat per $id
     * @param string $id és l'identificador del fitxer d'on extreu l'espai de noms
     * @return string amb l'espai de noms extret
     */
    public function getNs($id) {
        return getNS($id);
    }

    /**
     * Retorna el nom simple (sense els espais de noms que el contenen) del 
     * firxer o directori identificat per $id
     * @param type $id
     * @return string contenint el nom simple del fitxer o directori
     */
    public function getIdWithoutNs($id) {
        return noNS($id);
    }

    public abstract function getFileName($id, $especParams = NULL);

    public abstract function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE, $expandProjects = TRUE);

    /**
     * Retorna la llista de fitxers continguts a l'espai de noms identificat per $ns
     * @param string $ns és l'espai de noms d'on consultar la llista
     * @return array amb la llista de fitxers
     */
    public function getFileList($ns) {
        $dir = $this->getFileName($ns);
        $arrayDir = scandir($dir);
        if ($arrayDir) {
            unset($arrayDir[0]);
            unset($arrayDir[1]);
            $arrayDir = array_values($arrayDir);
        } else {
            $arrayDir = array();
        }

        return $arrayDir;
    }

    public function resolve_id($ns, $id, $clean = true) {
        resolve_id($ns, $id, $clean);
    }

    /**
     * Crea el directori on ubicar el fitxer referenciat per $filePath després
     * d'extreure'n el nom del fitxer. Aquesta funció no crea directoris recursivamnent.
     *
     * @param type $filePath
     */
    public function makeFileDir($filePath) {
        io_makeFileDir($filePath);
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
    protected function getNsTreeFromBase($base, $currentnode, $sortBy, $onlyDirs = FALSE, $function = 'search_index', $expandProjects = TRUE) {
        $sortOptions = array(0 => 'name', 'date');
        $nodeData = array();
        $children = array();

        if ($currentnode == "_") {
            return array('id' => "", 'name' => "", 'type' => 'd');
        }
        if ($currentnode) {
            $node = $currentnode;
            $aname = split(":", $currentnode);
            $level = count($aname);
            $name = $aname[$level - 1];
        } else {
            $node = '';
            $name = '';
            $level = 0;
        }
        $sort = $sortOptions[$sortBy];

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
        search(
                $nodeData, $base, $function, $opts, $dir, 1
        );
        print_r("\ngetNSTree\n");

        $levelProject = -1;
        $isProject = false;
        $projectType = "";
        $metaDataPath = DOKU_INC . WikiGlobalConfig::getConf('mdprojects');
        $metaDataExtension = WikiGlobalConfig::getConf('mdextension');
        foreach (array_keys($nodeData) as $item) {
            //print_r($nodeData[$item]);
            $type = 'd';
            //print_r("\n" . $levelProject . "\n");
            //print_r("\n" . $isProject . "\n");
            if ($onlyDirs && $nodeData[$item]['type'] == 'd' || !$onlyDirs) {
                if ($nodeData[$item]['type'] == 'd') {
                    if (!$isProject || ($isProject && $levelProject == $nodeData[$item]['level'])) {
                        //Determinar si és projecte
                        $levelProject = $nodeData[$item]['level'];
                        $pathProject = str_replace(':', '/', $nodeData[$item]['id']);
                        $pathProject = $metaDataPath . $pathProject;
                        print_r("\n PATHPROJECT PATHPROJECT PATHPROJECT PATHPROJECT PATHPROJECT \n");
                        print_r("\n" . $pathProject . "\n");
                        $isProject = false;
                        if (is_dir($pathProject)) {
                            print_r("\n ISDIR ISDIR ISDIR ISDIR ISDIR ISDIR ISDIR ISDIR ISDIR ISDIR ISDIR \n");
                            print_r("\n" . $pathProject . "\n");
                            $dirProject = opendir($pathProject);
                            while ($current = readdir($dirProject)) {
                                print_r("\n current current current current current current current current current \n");
                                print_r("\n" . $current . "\n");
                                $pathProjectOne = $pathProject . '/' . $current;
                                if (is_dir($pathProjectOne)) {
                                    print_r("\n ISDIR2 ISDIR2 ISDIR2 ISDIR2 ISDIR2 ISDIR2 ISDIR2 ISDIR2 ISDIR2\n");
                                    print_r("\n" . $pathProjectOne . "\n");
                                    $dirProjectOne = opendir($pathProjectOne);
                                    while ($currentOne = readdir($dirProjectOne)) {
                                        print_r("\n current2 current2 current2 current2 current2 current2 current2 current2 current2 \n");
                                        print_r("\n" . $currentOne . "\n");
                                        if (!is_dir($pathProjectOne . '/' . $currentOne)) {
                                            $fileTokens = explode(".", $currentOne);
                                            print_r("\n" . $fileTokens[sizeof($fileTokens) - 1] . "\n");
                                            print_r("\naaa" . $metaDataExtension . "\n");
                                            if ($fileTokens[sizeof($fileTokens) - 1] == $metaDataExtension) {
                                                print_r("\n PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE \n");
                                                print_r("\n" . $currentOne . "\n");
                                                //És projecte i escriure   p  
                                                $levelProject = $nodeData[$item]['level'];
                                                $isProject = true;
                                                $type = 'p';
                                                $projectType = $current;
                                                $children[$item]['projectType'] = $projectType;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $children[$item]['id'] = $nodeData[$item]['id'];
                        $aname = split(":", $nodeData[$item]['id']); //TODO[Xavi] @deprecated substitur per explode()
                        $children[$item]['name'] = $aname[$level];
                        $children[$item]['type'] = $type;
                    } else {
                        print_r("\n SUBNIVELL DE PROJECTE" . $nodeData[$item]['id'] . "\n");
                        //Subnivell de projecte - i només quan s'ha d'expadir el projecte
                        if ($expandProjects) {
                            $children[$item]['id'] = $nodeData[$item]['id'];
                            $aname = split(":", $nodeData[$item]['id']); //TODO[Xavi] @deprecated substitur per explode()
                            $children[$item]['name'] = $aname[$level];
                            $children[$item]['type'] = 'pd';
                            $children[$item]['projectType'] = $projectType;
                        }
                    }
                } else {
                    //fitxer de projecte o no
                    print_r("\n FITXER DE PROJECTE O NO DE PROJECTE" . $nodeData[$item]['id'] . "\n");
                    if ($isProject) {
                        if ($expandProjects) {
                            print_r("\n FITXER DE PROJECTE" . $children[$item]['id'] . "\n");
                            $children[$item]['id'] = $nodeData[$item]['id'];
                            $aname = split(":", $nodeData[$item]['id']); //TODO[Xavi] @deprecated substitur per explode()
                            $children[$item]['name'] = $aname[$level];
                            $children[$item]['type'] = 'pf';
                            $children[$item]['projectType'] = $projectType;
                        }
                    } else {
                        $children[$item]['id'] = $nodeData[$item]['id'];
                        $aname = split(":", $nodeData[$item]['id']); //TODO[Xavi] @deprecated substitur per explode()
                        $children[$item]['name'] = $aname[$level];
                        $children[$item]['type'] = $nodeData[$item]['type'];
                    }
                }
            }
            print_r($children[$item]);
        }

        $tree = array(
            'id' => $node,
            'name' => $node,
            'type' => 'd',
            'children' => $children
        );

        return $tree;
    }

}
