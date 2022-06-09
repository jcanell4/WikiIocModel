<?php
if (!defined('DOKU_INC')) die();

class SetProjectAction extends BasicSetProjectAction{
    public function init($modelManager = NULL) {
        parent::init($modelManager);
        $this->addExcludeKeys("saveAllData");        
    }
    
     protected function responseProcess(){         
         // TODO: obtenir el model i la vista, comprovar si és allData i si és així no reemplaçar res.
         // PROBLEMA: no s'està seleccionant cap vista encara que la d'origen sigui allData.

         $model = $this->getModel();
         // TODO: Solució temporal, desar els canvis sense default només per l'admin
         // fer servir el mateix sistema de grups del ViewProject per admetre només
         // determinats grups

         if (!isset($this->params["saveAllData"])) {
             $oldValues = $this->getModel()->getCurrentDataProject();
             $oldArraytaula = (is_array($oldValues["arraytaula"]))?$oldValues["arraytaula"]:json_decode($oldValues["arraytaula"], TRUE);
             $newArraytaula = (is_array($this->params["arraytaula"]))?$this->params["arraytaula"]:json_decode($this->params["arraytaula"], TRUE);
             for($i=0; $i<count($newArraytaula); $i++) {
                 foreach ($newArraytaula[$i] as $key => $value){
                     if($key!="value"){
                         $newArraytaula[$i][$key] = $oldArraytaula [$i][$key];
                     }
                 }
             }             
         }else{
             $newArraytaula = (is_array($this->params["arraytaula"]))?$this->params["arraytaula"]:json_decode($this->params["arraytaula"], TRUE);
         }

         $this->params["arraytaula"] = $model->updateConditions($newArraytaula);

         $response = parent::responseProcess();        
         return $response;
     }
}