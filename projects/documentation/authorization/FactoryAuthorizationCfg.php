<?php
/**
 * FactoryAuthorizationCfg: Definició de les classes (fitxers de classe) d'autoritzacions de comandes
 *                          Associa un nom de classe a un fitxer d'autorització
 *                          Serveix pels noms de classe que no tenen un fitxer d'autorització
 *                          amb el seu nom
 *                          ( el nom de la comanda s'estableix amb el mètode getAuthorizationType() )
 * @author Rafael Claver
 */
//[JOSEP] Alerta caldria pujar una versió per defecte a wikiiocmodel/
$_AuthorizationCfg =
    array(
        '_default'                     => "admin"      /*Default case*/
	,'saveProject'                 => "editProject"
	,'cancelProject'               => "editProject"
	,'viewProject'                 => "editProject"
	,'diffProject'                 => "editProject"
        ,'revertProject'               => "editProject"
	,'save_project_draftProject'   => "editProject"
	,'remove_project_draftProject' => "editProject"
	,'new_documentProject'         => "editProject"
	,'new_folderProject'           => "editProject"
        ,'_none'                       => "command"
    );

/* Noms de commanda que ja ténen un fitxer d'autorització amb el seu nom
 * 	'edit'          => 'edit' -> EditAuthorization.php
 *
 * Noms de comanda modificats amb el mètode getAuthorizationType()
 * 	'saveProject'   => 'editProject'
 */