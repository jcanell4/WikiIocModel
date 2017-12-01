<?php

if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DOKU_TPL_INCDIR')) define('DOKU_TPL_INCDIR', tpl_incdir());

require_once DOKU_INC . 'inc/pluginutils.php';
require_once DOKU_INC . 'inc/actions.php';
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/HtmlPageAction.php";
require_once DOKU_TPL_INCDIR."conf/cfgIdConstants.php";

/**
 * Description of AdminTaskListAction
 *
 * @author josep
 */
class RevisionsListAction extends HtmlPageAction {

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet generar la resposta a enviar al client. Aquest
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut
     * DokuAction#response.
     */
    protected function responseProcess(){
        return $this->getRevisionList($this->params[PageKeys::KEY_OFFSET]);;
    }
    
}
