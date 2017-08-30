<?php
/**
 * renderDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL."projects/documentation/");
require_once WIKI_IOC_PROJECT."renderer/AbstractRenderer.php";

class renderDocument extends AbstractRenderer {

    public function process($data) {
        $tmplt = $this->loadTemplateFile('xhtml/renderDocument.html');
        $aSearch = array('@DIV_ID@','@TITLE_VALUE@','@AUTOR_VALUE@','@RESPONSABLE_VALUE@','@CONTINGUTS_VALUE@');
        $aReplace = array_merge(array("id_div_document"), $data);
        $document = str_replace($aSearch, $aReplace, $tmplt);
        return $document;
    }
}
