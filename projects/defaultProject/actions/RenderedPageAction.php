<?php
/**
 * Description of RenderedPageAction
 *
 * @author josep
 */
abstract class RenderedPageAction extends PageAction{

    public function __construct(/*BasicPersistenceEngine*/$engine){
        parent::__construct($engine);
        //Indica que la resposta es renderitza i caldrà llançar l'esdeveniment quan calgui
        $this->setRenderer(TRUE);
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
