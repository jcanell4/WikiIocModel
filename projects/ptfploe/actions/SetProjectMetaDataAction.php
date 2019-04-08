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

         $model = $this->getModel();
         $modelAttrib = $model->getModelAttributes();
         $extraProject = $this->params['extraProject']; // ALERTA[Xavi] només hi ha el old_responsable i old_autor

         // ALERTA[Xavi] Actualitzem el ACL i els shortcuts encara que el projecte no estigui generat
//         if ($response[ProjectKeys::KEY_GENERATED]) {
             $include = [
                 'id' => $modelAttrib[ProjectKeys::KEY_ID]
                 ,'link_page' => $modelAttrib[ProjectKeys::KEY_ID] //$modelAttrib[ProjectKeys::KEY_ID].":".end(explode(":", $response['projectMetaData']["plantilla"]['value']))
                 ,'old_autor' => $extraProject['old_autor']
                 ,'old_responsable' => $extraProject['old_responsable']
                 ,'old_supervisor' => $extraProject['old_supervisor']
                 ,'new_autor' => $response['projectMetaData']['autor']['value']
                 ,'new_responsable' => $response['projectMetaData']['responsable']['value']
                 ,'new_supervisor' => $response['projectMetaData']['supervisor']['value']
                 ,'userpage_ns' => WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel')
                 ,'shortcut_name' => WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
             ];
             $model->modifyACLPageToUser($include);
//         }







         return $response;
     }
}