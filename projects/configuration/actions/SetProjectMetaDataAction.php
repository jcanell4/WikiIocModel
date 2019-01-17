<?php
if (!defined('DOKU_INC')) die();

class SetProjectMetaDataAction extends BasicSetProjectMetaDataAction{
     protected function responseProcess(){
         $oldValues = $this->getModel()->getDataProject();
         $oldArraytaula = json_decode($oldValues["arraytaula"], TRUE);
         $newArraytaula = json_decode($this->params["arraytaula"], TRUE);
         for($i=0; $i<count($newArraytaula); $i++) {
             foreach ($newArraytaula[$i] as $key => $value){
                 if($key!="value"){
                    $newArraytaula[$i][$key] = $oldArraytaula [$i][$key];
                 }
             }
         }
         $this->params["arraytaula"] = json_encode($newArraytaula);
         $response = parent::responseProcess();        
         return $response;
     }
}