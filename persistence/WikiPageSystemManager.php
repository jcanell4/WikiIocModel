<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WikiPageSystemManager 
 * 
 * @author Josep Cañellas 
 */
//[Alerta JOSEP] Cal traslladar aquesta classe al plugin de persistencia 
class WikiPageSystemManager {
    public static $DEFAULT_FORMAT = 0;
    public static $SHORT_FORMAT = 1;


    
    public static function cleanIDForFiles( $id) {
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
