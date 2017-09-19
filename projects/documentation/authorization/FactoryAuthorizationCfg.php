<?php
/**
 * FactoryAuthorizationCfg: Definició de les classes (fitxers de classe) d'autoritzacions de comandes
 *                          Associa un nom de classe a un fitxer d'autorització
 *                          Serveix pels noms de classe que no tenen un fitxer d'autorització
 *                          amb el seu nom
 *                          ( el nom de la comanda s'estableix amb el mètode getAuthorizationType() )
 * @author Rafael Claver
 */
$_AuthorizationCfg =
    array(
        '_default'              => 'admin'      /*Default case*/
	,'saveProject'  	=> 'editProject'
        /*
	,'cancel'		=> 'read'
	,'cancel_partial'	=> 'read'
	,'diff'			=> 'read'
	,'new_page'		=> 'create'
	,'page'			=> 'read'
	,'revision'		=> 'write'
	,'save'			=> 'write'
	,'save_partial'		=> 'write'
	,'draft'		=> 'write'
	,'copy_image_to_project'=> 'upload'
	,'get_image_detail'	=> 'read'
	,'media'		=> 'read'
	,'media_delete'		=> 'delete'
	,'media_edit'		=> 'write'
	,'media_upload'		=> 'upload'
	,'mediadetails'		=> 'read'
	,'mediadetails_delete'	=> 'delete'
	,'mediadetails_edit'	=> 'write'
	,'mediadetails_upload'	=> 'upload'
	,'login'                => 'command'
	,'lock'                 => 'read'
	,'unlock'               => 'read'
        */
        ,"_none"                => "command"
    );

/* Noms de commanda que ja ténen un fitxer d'autorització amb el seu nom
 *
 * 	'edit'                          => 'edit' -> EditAuthorization.php
 * 	'edit_partial' ('edit')         => 'edit'
 *
 * Noms de comanda modificats amb el mètode getAuthorizationType()
 * 	'cancel_partial' ('cancel')	=> 'read'
 */