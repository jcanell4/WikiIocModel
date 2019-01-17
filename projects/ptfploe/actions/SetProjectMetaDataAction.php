<?php
if (!defined('DOKU_INC')) die();

class SetProjectMetaDataAction extends BasicSetProjectMetaDataAction{
     protected function responseProcess(){
//         $this->params["duradaClicle"]=0;
//         $this->params["taulaDadesUF"]=0;
         $response = parent::responseProcess();
         if($this->getModel()->isProjectGenerated()){
             $id = $this->getModel()->getContentDocumentId($response);
             p_set_metadata($id, array('metadataProjectChanged' => time()));
         }
         return $response;
     }
}