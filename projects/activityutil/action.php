<?php
/**
 * Define y muestra los botones de un proyecto a partir de un fichero de control y de un template
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
require_once (DOKU_INC . "inc/pageutils.php");

class action_plugin_wikiiocmodel_projects_activityutil extends WikiIocProjectPluginAction {

    public function __construct($projectType, $dirProjectType) {
        parent::__construct($projectType, $dirProjectType);
    }

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ADD_TPL_CONTROLS', "AFTER", $this, "addWikiIocButtons", array());
        $controller->register_hook('ADD_TPL_CONTROL_SCRIPTS', "AFTER", $this, "addControlScripts", array());
        $controller->register_hook('WIOC_PROCESS_RESPONSE_project', "AFTER", $this, "setExtraMeta", array());
        $controller->register_hook('WIOC_PROCESS_RESPONSE_projectUpdate', "AFTER", $this, "setExtraMeta", array());
        $controller->register_hook('WIOC_PROCESS_RESPONSE_projectExport', "AFTER", $this, "setExtraMeta", array());
        //marjose added for update file_dependences
        $controller->register_hook('WIOC_AJAX_COMMAND', "BEFORE", $this, "setViewMode", array());
        $controller->register_hook('PARSER_CACHE_USE', "BEFORE", $this, "cache_use", array());       
        $controller->register_hook('IO_WIKIPAGE_WRITE', "BEFORE", $this, "io_writeWikiPage", array());        
        //end marjose
    }

    /**
     * Rellena de información una pestaña de la zona de MetaInformación
     */
    function setExtraMeta(&$event, $param) {
        //controlar que se trata del proyecto en curso
        if ($event->data['requestParams'][ProjectKeys::KEY_PROJECT_TYPE] === $this->projectType) {

            if (!isset($event->data['responseData'][ProjectKeys::KEY_CODETYPE])) {
                $result['ns'] = getID();
                $result['id'] = str_replace(':', '_', $result['ns']);
                $result['ext'] = ".zip";
                if ($event->data['responseData']['generatedZipFiles']) {
                    $result['fileNames'] = $event->data['responseData']['generatedZipFiles'];
                    $path_dest = WikiGlobalConfig::getConf('mediadir').'/'.preg_replace('/:/', '/', $result['ns']);
                    foreach ($event->data['responseData']['generatedZipFiles'] as $file) {
                        $result['dest'][] = "$path_dest/$file";
                    }
                }
                //$result['multipleFiles'] = true; //versió antiga
                if (class_exists("ResultsWithFiles", TRUE)){
                    $html = ResultsWithFiles::get_html_metadata($result) ;
                }

                $event->data["ajaxCmdResponseGenerator"]->addExtraMetadata(
                            $result['id'],
                            $result['id']."_iocexport",
                            WikiIocLangManager::getLang("metadata_export_title"),
                            $html
                            );

                $event->data["ajaxCmdResponseGenerator"]->addExtraMetadata(
                            $result['id'],
                            $result['id']."_ftpsend",
                            WikiIocLangManager::getLang("metadata_ftpsend_title"),
                            $event->data['responseData'][AjaxKeys::KEY_FTPSEND_HTML]
                );
            }
        }
        return TRUE;
    }  
    
    //marjose: actualitzada per llegir contingut del mpdr, camps amb els noms dels arxius dels que depen
    function cache_use(&$event, $param){
        global $plugin_controller;
        //recupera la variable global $plugin_controller i recull el tipus de projecte. 
        $projectOwner =  $plugin_controller->getProjectOwner();
        $projectSourceType =  $plugin_controller->getProjectSourceType();

        //Si ens trobem obrint una pàgina ($this->viewMode i el tipus de projecte actual es correspon amb "activityutil" 
        //recupera les dades del projecte.
        if($this->viewMode &&  $projectOwner && $projectSourceType=="activityutil"){
            $datProject = $plugin_controller->getProjectDataSourceFromProjectId($projectOwner); //obté ruta física a meta.mpdr   
            // consulta els fitxers associats (camp <FILE_DEPENDENCES>) 
            // i per cada entrada de l'array, afegeix a les dades de l'esdeveniment la ruta dels fitxers.             
            $documents = IocCommon::toArrayThroughArrayOrJson($datProject['file_dependences']); //per recuperar els fitxers com un array
            //per a cada arxiu de l'array $documents
            foreach ($documents as $oneDocument) {
                foreach ($oneDocument['relDocs'] as $oneRelDoc) {
                    //Afegim un fitxer com a dependència de la cache:
                    $event->data->depends["files"] []= wikiFN($oneRelDoc); //per convertir un identificador WIKI a ruta del fitxer   
                }
            }
            $event->data->depends["files"]= array_unique($event->data->depends["files"]);
        }
    }
    
    
    function setViewMode(&$event, $param){
        switch ($event->data["call"]){ // conté dades del tipus d'event. Call conté amb quin command s'ha llençat l'esdeveniment. 
            case "page": //primera vegada que obres
            case "cancel": //torno a visualitzar tipus vista
                $this->viewMode = true;
                break;
        }
    }
    
    
    
    //Marjose. Before writing, adds to the dataProject file_dependences
      function io_writeWikiPage(&$event, $param){
        global $plugin_controller;
            
        if($plugin_controller->getProjectType() == 'activityutil'){
            //Sobre dataTempo recollim el contingut de la pàgina que volem escriure (el nou)
            $dataTempo = $event->data[0][1]; 

            //Per recuperar el contingut de l'arxiu guardat:
            //a partir del plugin_controller accedir al model
            $projectOwner = $plugin_controller->getProjectOwner();
            $projectModel = $plugin_controller->getAnotherProjectModel($projectOwner, 'activityutil');
            //a partir del model (i el id del projecte) obtenim contingut guardat (funcio definida a la classe abstractProjectModel)          
            $dataPrev = $projectModel->getRawDocument(getID());
            
            //Detectar nous documents relacionats
            preg_match_all('/(^{{section>|^{{page>)(.*?):continguts/m', $dataTempo, $matches);
            $arxiusDepNew = array_unique($matches[2]);
            preg_match_all('/(^{{section>|^{{page>)(.*?):continguts/m', $dataPrev, $matches);
            $arxiusDepPrev = array_unique($matches[2]);
            if((!empty(array_diff($arxiusDepNew, $arxiusDepPrev)))||(!empty(array_diff($arxiusDepPrev, $arxiusDepNew)))){
                // Get the actual filename
                //$pattern = '/[^:]+$/'; // Match the last group of characters after the last ":"
                preg_match('/[^:]+$/', getID(), $matches);
                $actualFileName = $matches[0]; 
                //get dataProject 
                $dataProject = $projectModel->getDataProject($projectOwner,  'activityutil');
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }

                // Loop through the $fileDependences array and access object properties
                for ($i=0; $i<count($dataProject['file_dependences']);$i++){ 
                    $valorNom =  $dataProject['file_dependences'][$i]['nomDoc'];                   
                    if($valorNom==$actualFileName){
                        $dataProject['file_dependences'][$i]['relDocs']=$arxiusDepNew;
                        //Update version for fields
                        $projectModel->setDataProject(json_encode($dataProject));
                    }
                }  
            }
        }
    }
    //end marjose
     
    
    
}
