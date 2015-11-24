<?php
/* 
 * WikiIocInfoManager: S'encarrega de carregar la variable global $INFO
 */
if (!defined('DOKU_INC')) die();
require_once (DOKU_INC . 'inc/common.php');

class WikiIocInfoManager {

    protected $infoLoaded = FALSE;
    
    private function __construct() {
        if (!$this->infoLoaded) {
            $this->fillInfo();
        }
    }

    public static function Instance(){
        static $inst = NULL;
        if ($inst === NULL) {
            $inst = new WikiIocInfoManager();
        }
        return $inst;
    }
        
    public function getInfoLoaded() {
        return $this->infoLoaded;
    }

    private function fillInfo() {
	global $JSINFO;
	global $INFO;

	$INFO = pageinfo();
	//export minimal infos to JS, plugins can add more
	$JSINFO['isadmin']   = $INFO['isadmin'];
	$JSINFO['ismanager'] = $INFO['ismanager'];

	$this->infoLoaded = TRUE;

	return $JSINFO;
    }

}