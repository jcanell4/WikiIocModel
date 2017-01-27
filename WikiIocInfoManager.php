<?php
/* 
 * WikiIocInfoManager: Carrega la variable global $INFO
 */
if (!defined('DOKU_INC')) die();
require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/actions.php');
require_once (DOKU_INC . 'inc/pageutils.php');

class WikiIocInfoManager {

    const KEY_EXISTS = "exists";
    const KEY_LOCKED = "locked";

    private static $isMediaAction = FALSE;
    private static $infoLoaded = FALSE;
    private static $mediaInfoLoaded = FALSE;

    public static function getInfo($key){
        global $INFO;
        self::loadInfo();        
        $ret = (isset($INFO[$key])) ? $INFO[$key] : $key;
        return $ret;
    }
    
    public static function setIsMediaAction($value){
        self::$isMediaAction = $value;
    }
    
    public static function setInfo($key, $value){
        global $INFO;
        self::loadInfo();
        $INFO[$key] = $value;
        self::updateJsInfo();
    }
    
    public static function loadInfo() {
        if (self::$isMediaAction){
            self::loadMediaInfo();
        }else if (!self::$infoLoaded) {
            self::fillInfo();
        }
    }
    
    public static function getMediaInfo($key){
        global $INFO;
        self::loadMediaInfo();
        return $INFO[$key];
    }

    public static function loadMediaInfo() {
	global $INFO, $IMG;
        if (!self::$infoLoaded) {
            self::fillInfo();
        }
        if (!self::$mediaInfoLoaded) {
            $INFO = array_merge($INFO, mediainfo());
            $INFO['mediapath'] = mediaFN($IMG);
            $INFO['mediaexists'] = @file_exists($INFO['mediapath']);
            self::$mediaInfoLoaded = TRUE;
        }
    }

    protected static function fillInfo() {
	global $INFO;

	$INFO = pageinfo();
        self::updateJsInfo();
        if ($INFO['isadmin'] && !in_array('admin', $INFO['userinfo']['grps']))
            $INFO['userinfo']['grps'][] = 'admin';
        if ($INFO['ismanager'] && in_array('manager', $INFO['userinfo']['grps']))
            $INFO['userinfo']['grps'][] = 'manager';
        
	self::$infoLoaded = TRUE;
    }
    
    private static function updateJsInfo(){
	global $JSINFO;
	global $INFO;
        
	$JSINFO['isadmin']   = $INFO['isadmin'];
	$JSINFO['ismanager'] = $INFO['ismanager'];
        if (in_array('projectmanager', $INFO['userinfo']['grps']))
            $JSINFO['isprojectmanager'] = TRUE;
    }
    
    public static function setParams($params){
        global $ID;
        global $ACT;
        global $REV;
        global $DATE;
        global $NS;
        global $IMG;

        $ACT = $params['do'];
        $ACT = act_clean( $ACT );

        if ($params['id'])
            $ID = $params['id'];

        if ($params['rev'])
            $REV = $params['rev'];

        if ($params['date'])
            $DATE = $params['date'];

        if ($params['ns'])
            $NS = $params['ns'];
        
        if ($params['do'] === 'media') {
            if ($params['id'] && !$params['ns']) {
                $NS = $params['ns'] = $params['id'];
            }
            $IMG = $params['image'];
        }
        self::$infoLoaded = FALSE;
        self::$mediaInfoLoaded = FALSE;
    }

}