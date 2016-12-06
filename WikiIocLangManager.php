<?php

if (! defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN'))    define("DOKU_PLUGIN", DOKU_INC."lib/plugins/");

require_once (DOKU_INC . 'inc/pageutils.php');
require_once (DOKU_INC . 'inc/parserutils.php');
require_once (DOKU_PLUGIN . 'ownInit/WikiGlobalConfig.php');


/**
 * Description of WikiIocLangManager
 *
 * @author josep
 */
class WikiIocLangManager {
    private static $langLoaded = FALSE;
    private static $pluginLangLoaded = array();
    
    
    public static function getXhtml($key){
        return p_locale_xhtml($key);
    }
    
    public static function isTemplate($key){
        return page_exists($key);
    }
    
    public static function getXhtmlTemplate($key){
        $ret = "";
        $file = wikiFN($key);
        if(file_exists($file)){
            $ret = p_cached_output($file,'xhtml',$key);
        }
        return $ret;
    }
    
    public static function getRawTemplate($key){
        $ret = "";
        $file = wikiFN($key);
        if(file_exists($file)){
            $ret = rawWiki($key);
        }
        return $ret;
    }
    
    public static function isXhtmlKey($key){
        return file_exists(localeFN($key));
    }
    
    public static function isKey($key, $plugin=""){
        $value = self::getLang($key, $plugin);
        return $value!==$key;
    }
    
    public static function getLang($key, $plugin=""){
        global $lang;
        self::load($plugin);
        if(empty($plugin)){
            $value = $lang[$key];
        }else{
            $value = self::$pluginLangLoaded[$plugin][$key];
        }
        if(!isset($value)){
            $value = $key;
        }
        
        return $value;
    }
    
    public static function load($plugin="") {
        if (!self::$langLoaded) {
            self::startUpLang();
        }
        if(!empty($plugin)){
            if(!isset(self::$pluginLangLoaded[$plugin])){
                self::startUpPluginLang($plugin);
            }
        }
    }
    
    private static function startUpLang() {
            global $lang;

            //get needed language array
            include WikiGlobalConfig::tplIncDir() . "lang/en/lang.php";
            //overwrite English language values with available translations
            if ( ! empty( WikiGlobalConfig::getConf("lang")) &&
                 WikiGlobalConfig::getConf("lang") !== "en" &&
                 file_exists( WikiGlobalConfig::tplIncDir() . "/lang/" . WikiGlobalConfig::getConf("lang") . "/lang.php" )
            ) {
                    //get language file (partially translated language files are no problem
                    //cause non translated stuff is still existing as English array value)
                    include WikiGlobalConfig::tplIncDir() . "/lang/" . WikiGlobalConfig::getConf("lang") . "/lang.php";
            }
            include DOKU_PLUGIN . "wikiiocmodel/lang/en/lang.php";;
            if ( ! empty( WikiGlobalConfig::getConf("lang") ) &&
                 WikiGlobalConfig::getConf("lang") !== "en" &&
                 file_exists( DOKU_PLUGIN . "wikiiocmodel/lang/" . WikiGlobalConfig::getConf("lang") . "/lang.php" )
            ) {
                    include DOKU_PLUGIN . "wikiiocmodel/lang/" . WikiGlobalConfig::getConf("lang") . "/lang.php";
            }
            self::$langLoaded=true;
    }
    
    private static function startUpPluginLang($plugin) {
        $path = DOKU_PLUGIN.$plugin.'/lang/';

        $lang = array();

        // don't include once, in case several plugin components require the same language file
        @include($path.'en/lang.php');
        if ($conf['lang'] != 'en') @include($path.$conf['lang'].'/lang.php');

        self::$pluginLangLoaded[$plugin] = $lang;
    }
}
