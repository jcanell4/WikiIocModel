<?php
/* 
 * WikiIocInfoManager: S'encarrega de carregar la variable global $INFO
 */
if (!defined('DOKU_INC')) die();
require_once (DOKU_INC . 'inc/common.php');

class WikiIocInfoManager {

    private static $infoLoaded = FALSE;
    
    public static function loadInfo() {
        if (!self::$infoLoaded) {
            self::fillInfo();
        }
    }

    public static function loadMediaInfo() {
	global $INFO;
        self::loadInfo();
    	$INFO = array_merge( $INFO, mediainfo() );
    }

    protected static function fillInfo() {
	global $JSINFO;
	global $INFO;

	$INFO = pageinfo();
	//export minimal infos to JS, plugins can add more
	$JSINFO['isadmin']   = $INFO['isadmin'];
	$JSINFO['ismanager'] = $INFO['ismanager'];

	self::$infoLoaded = TRUE;
    }

}