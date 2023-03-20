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
        //marjose added
        $controller->register_hook('WIOC_AJAX_COMMAND', "BEFORE", $this, "setViewMode", array());
        $controller->register_hook('PARSER_CACHE_USE', "BEFORE", $this, "cache_use", array());
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
    
    //marjose: actualitzar-la per llegir contingut del mpdr, camps amb els noms dels arxius dels que depen
    function cache_use(&$event, $param){
        global $plugin_controller;

        $projectOwner =  $plugin_controller->getProjectOwner();
        $projectSourceType =  $plugin_controller->getProjectSourceType();

        if($this->viewMode &&  $projectOwner && $projectSourceType=="activityutil"){
            $datProjetc = $plugin_controller->getProjectDataSourceFromProjectId($projectOwner); //obté ruta física a meta.mpdr
            $event->data->depends["files"] []= $fileProjetc;
        }
    }
    
    /*
     * 3.3.1: En el cos de la funció, recupera la variable global $plugin_controller i recull el tipus de projecte. Si ens trobem obrint una pàgina i el tipus de projecte actual es correspon amb "activityutil" recupera les dades del projecte. Pots fer tot això fent:
                global $plugin_controller;
                $projectOwner =  $plugin_controller->getProjectOwner();
                if($this->viewMode &&  $projectOwner && $projectOwner=="activityutil"){
                       $datProjetc = $plugin_controller->getCurrentProjectDataSource();
     *                               $plugin_controller->getProjectDataSourceFromProjectId($projectId, $projectSourceType=FALSE, $subset=FALSE) 
     * /home/professor/Projectes/wikiDev/wikiIOC.com/dokuwiki_30/inc/inc_ioc/ioc_plugincontroller.php
                            ...
3.3.2: Si es compleix la condició anterior, consulta els fitxers associats (camp <FILE_DEPENDENCES>) i per cada entrada de l'array, afegeix a les dades de l'esdeveniment la ruta dels fitxers. Cal assegurar-te que el camp <FILE_DEPENDENCES> es recupera com un array i no com un string i cal transformar els identificadors WIKI de cada document en la ruta dels seus fitxers. Per fer això cal:
                $documents = IocCommon::toArrayThroughArrayOrJson($dataProject["<FILE_DEPENDENCES>"]); per recuperar els fitxers com un array
               
     * 
     * //per a cada arxiu, el passo a document físic
                $fileName = wikiFN($unDocument); per convertir un identificador WIKI a ruta del fitxer
              
     * //afegeixo el fitxer al depents["files"]
               $event->data->depends["files"] []= $fileName; per afegir un fitxer com a dependència de la cache.

    3.4: Assegura't que existeixi l'atribut viewMode de la classe action_plugin_wikiiocmodel_projects_activityutil. Si no existeix cal crear-lo usant una altra funció callback, però aquest cop associada a l'esdevenoment WIOC_AJAX_COMMAND. Per exemple:
       
               $controller->register_hook('WIOC_AJAX_COMMAND', "BEFORE", $this, "setViewMode", array());



//això ja s'ha fet al setViewMode
   3.5: La funció callback setViewMode ha de codificar-se així:
              function setViewMode(&$event, $param){
                   switch ($event->data["call"]){
                       case "page":
                       case "cancel":
                            $this->viewMode = true;
                            break;
                       }
                 }

        D'aquesta manera mantenim actualitzat l'atribut viewMode i evitem processament inutil!
     */
    
    function setViewMode(&$event, $param){
        switch ($event->data["call"]){ // conté dades del tipus d'event. Call conté amb quin command s'ha llençat l'esdeveniment. 
            case "page": //primera vegada que obres
            case "cancel": //torno a visualitzar tipus vista
                $this->viewMode = true;
                break;
        }
    }
}
