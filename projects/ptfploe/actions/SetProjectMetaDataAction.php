<?php
if (!defined('DOKU_INC')) die();

class SetProjectMetaDataAction extends BasicSetProjectMetaDataAction{
     protected function responseProcess(){
         $response = parent::responseProcess();
         if($this->getModel()->isProjectGenerated()){
             $id = $this->getModel()->getContentDocumentId($response);
             p_set_metadata($id, array('metadataProjectChanged' => time()));
         }

         $model = $this->getModel();
         $modelAttrib = $model->getModelAttributes();
         $extraProject = $this->params['extraProject']; // ALERTA[Xavi] només hi ha el old_responsable i old_autor

         if ($response[ProjectKeys::KEY_GENERATED]) {
             $include = [
                 'id' => $modelAttrib[ProjectKeys::KEY_ID]
                 ,'old_supervisor' => $extraProject['old_supervisor']
                 ,'new_autor' => $response['projectMetaData']['autor']['value']
                 ,'new_responsable' => $response['projectMetaData']['responsable']['value']
                 ,'new_supervisor' => $response['projectMetaData']['supervisor']['value']
             ];
             $model->modifyACLPageToSupervisor($include);
         }

         return $response;
     }
}