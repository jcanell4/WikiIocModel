<?php

if (!defined("DOKU_INC")) {
    die();
}
require_once DOKU_INC . 'inc/pageutils.php';


/**
 * Description of WikiPageSystemManager 
 * 
 * @author Josep Cañellas 
 */
class WikiPageSystemManager {
    public static $DEFAULT_FORMAT = 0;
    public static $SHORT_FORMAT = 1;


    
    public static function cleanPageID( $raw_id) {
        return cleanID($raw_id);
    }
    
    public static function getContainerIdFromPageId($id) {
            return str_replace( ':', '_', $id );
    }
    
    /**
     * Extreu la data a partir del nombre de revisió
     *
     * @param int $revision - nombre de la revisió
     * @param int $mode     - format de la data
     *
     * @return string - Data formatada
     *
     */
    public static function extractDateFromRevision( $revision, $mode = NULL ) {
        if(!$revision){
            return NULL;
        }
        
        if(!$mode){
            $mode = self::$DEFAULT_FORMAT;
        }

        switch ( $mode ) {

                case self::$SHORT_FORMAT:
                        $format = "d-m-Y";
                        break;

                case self::$DEFAULT_FORMAT:

                default:
                        $format = "d-m-Y H:i:s";

        }

        return date( $format, $revision );
    }
}
