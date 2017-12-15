<?php
/**
 * Description of RenderedPageAction
 * @author josep
 */
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
