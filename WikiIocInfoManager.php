<?php
/* 
 * WikiIocInfoManager: S'encarrega de carregar la variable global $INFO
 */
if (!defined('DOKU_INC')) die();
require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/actions.php');

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
    
    public static function setParams($params){
        global $ID;
        global $ACT;
        global $REV;
        global $RANGE;
        global $DATE;
        global $PRE;
        global $TEXT;
        global $SUF;
        global $SUM;

        $ACT = $params['do'];
        $ACT = act_clean( $ACT );

        if ( $params['id']  ) {
                $ID = $params['id'];
        }
        if ( $params['rev']  ) {
                $REV = $params['rev'];
        }
        if ( $params['range']  ) {
                $RANGE = $params['range'];
        }
        if ( $params['date']  ) {
                $DATE = $params['date'];
        }
        if ( $params['pre']  ) {
                $PRE = cleanText( substr( $params['pre'], 0, - 1 ) );
        }
        if ( $params['text']  ) {
                $TEXT = cleanText( $params['text']  );
        }
        if ( $params['suf']  ) {
                $SUF = cleanText( $params['suf'] );
        }
        if ( $params['sum']  ) {
                $SUM = $params['sum'];
        }
        self::$infoLoaded=FALSE;
    }
    
    public static function getInfo($key){
        global $INFO;
        self::loadInfo();
        return $INFO[$key];
    }

}