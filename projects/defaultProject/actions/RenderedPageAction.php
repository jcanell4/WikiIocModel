<?php
/**
 * Description of RenderedPageAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/PageAction.php");

abstract class RenderedPageAction extends PageAction{

    public function init($modelManager) {
        parent::init($modelManager);
        $this->setRenderer(TRUE);   //Indica que la resposta es renderitza i caldrà llançar l'esdeveniment quan calgui
    }

    protected function responseProcess(){
        return self::staticResponseProcess($this);
    }

    static function staticResponseProcess($obj){
        $response = $obj->getModel()->getData();

        $obj->addMetaTocResponse($response);
        $response['revs'] = $obj->getRevisionList();
        $obj->addNotificationsMetaToResponse($response);
        return $response;
    }
}
