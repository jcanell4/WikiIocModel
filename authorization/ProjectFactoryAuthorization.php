<?php
/**
 * ProjectFactoryAuthorization: Definició de les classes (fitxers de classe) d'autoritzacions de comandes
*         Associa un nom de classe a un fitxer d'autorització
*         Serveix pels noms de classe que no tenen un fitxer d'autorització
*         amb el seu nom (el nom de la comanda s'estableix amb el mètode setAuthorizationCfg() )
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
require_once(WIKI_IOC_MODEL . "authorization/AbstractFactoryAuthorization.php");

class ProjectFactoryAuthorization extends AbstractFactoryAuthorization {

    const DEFAULT_AUTH = WIKI_IOC_MODEL . "projects/defaultProject/authorization/";

    public function __construct($projectType=NULL) {
        parent::__construct( ($projectType) ? $projectType : self::DEFAULT_AUTH );
    }

    /* Noms de commanda que ja ténen un fitxer d'autorització amb el seu nom
     * 	'edit'        => 'edit' -> EditAuthorization.php
     *
     * Noms de comanda modificats amb el mètode setAuthorizationCfg()
     * 	'saveProject' => 'editProject'
     */
    public function setAuthorizationCfg() {
        $aCfg = ['_default'                     => "admin"  //default case
                 ,'create_projectProject'       => "createProject"
                 ,'create_subprojectProject'    => "createProject"
                 ,'saveProject'                 => "editProject"
                 ,'cancelProject'               => "editProject"
                 ,'viewProject'                 => "editProject"
                 ,'diffProject'                 => "editProject"
                 ,'save_project_draftProject'   => "editProject"
                 ,'remove_project_draftProject' => "editProject"
                 ,'new_documentProject'         => "editProject"
                 ,'new_folderProject'           => "editProject"
                 ,'_none'                       => "basicCommand"
                ];
        $this->authCfg = $aCfg;
    }
}
