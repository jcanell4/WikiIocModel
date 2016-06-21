<?php
if (! defined('DOKU_INC')) die();

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
    public function getNs($id){
        return getNS($id);
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
    public abstract function getNsTree($currentNode, $sortBy, $onlyDirs=FALSE, $expandProject=FALSE);


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
     * @param type $base
     * @param type $currentnode
     * @param type $sortBy
     * @param type $onlyDirs
     * @return string
     */
    protected function getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs=FALSE, $expandProject=FALSE ) {
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
        } else {
                $node  = '';
                $name  = '';
                $level = 0;
        }
        $sort = $sortOptions[ $sortBy ];

        $opts = array( 'ns' => $node );
        $dir  = str_replace( ':', '/', $node );
        search(
                $nodeData, $base, 'search_index',
                $opts, $dir, 1
        );
        foreach ( array_keys( $nodeData ) as $item ) {
                if ( $onlyDirs && $nodeData[ $item ]['type'] == 'd' || ! $onlyDirs ) {
                        $children[ $item ]['id']   = $nodeData[ $item ]['id'];
                        $aname                     = split( ":", $nodeData[ $item ]['id'] ); //TODO[Xavi] @deprecated substitur per explode()
                        $children[ $item ]['name'] = $aname[ $level ];
                        $children[ $item ]['type'] = $nodeData[ $item ]['type'];
                }
        }

        $tree = array(
                'id'       => $node,
                'name'     => $node,
                'type'     => 'd',
                'children' => $children
        );

        return $tree;
    }
}
