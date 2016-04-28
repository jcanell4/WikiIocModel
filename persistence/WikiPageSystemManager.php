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

    public static function generateNotification($text, $type = NotifyDataQuery::TYPE_MESSAGE, $params = [], $senderId = NULL)
    {

        $notification = [];
        $now = new DateTime(); // id
        $notification[NotifyDataQuery::NOTIFICATION_ID] = $now->getTimestamp(); // ALERTA[Xavi] Moure les constants a un altre fitxer?
        $notification[NotifyDataQuery::TYPE] = $type;
        $notification[NotifyDataQuery::TEXT] = $text;
        $notification[NotifyDataQuery::PARAMS] = $params;


        // Si no s'ha especificat el sender s'atribueix al sistema
        if ($senderId === NULL) {
            $notification[NotifyDataQuery::SENDER_ID] = NotifyDataQuery::DEFAULT_USER;
        } else {
            $notification[NotifyDataQuery::SENDER_ID] = $senderId;
        }

        return $notification;
    }
}
