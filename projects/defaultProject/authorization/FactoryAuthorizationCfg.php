<?php
/**
 * FactoryAuthorizationCfg: Definició de les classes (fitxers de classe) d'autoritzacions de comandes
 *                          Associa un nom de classe a un fitxer d'autorització
 *                          Serveix pels noms de classe que no ténen un fitxer d'autorització
 *                          amb el seu nom
 *                          ( el nom de la comanda s'estableix amb el mètode getAuthorizationType() )
 * @author Rafael Claver
 */

$_AuthorizationCfg =
    array(
        '_default'              => 'admin'      /*Default case*/
	,'cancel'		=> 'read'
	,'cancel_partial'	=> 'read'
	,'copy_image_to_project'=> 'upload'
	,'diff'			=> 'read'
	,'draft'		=> 'editing'
	,'edit'			=> 'read'
	,'get_image_detail'	=> 'read'
	,'login'                => 'command'
	,'lock'                 => 'read'
	,'media'		=> 'read'
	,'media_delete'		=> 'delete'
	,'media_edit'		=> 'write'
	,'media_upload'		=> 'upload'
	,'mediadetails'		=> 'read'
	,'mediadetails_delete'	=> 'deleteMedia'
	,'mediadetails_edit'	=> 'write'
	,'mediadetails_upload'	=> 'upload'
	,'new_page'		=> 'create'
	,'page'			=> 'read'
	,'revision'		=> 'write'
	,'save'			=> 'write'
	,'save_partial'		=> 'write'
	,'unlock'               => 'read'
	,'user_list'            => 'editing'
        ,"_none"                => 'command'
    );

/* Noms de commanda que ja ténen un fitxer d'autorització amb el seu nom
 * 	'read'  => 'read' -> ReadAuthorization.php
 *
 * Noms de comanda modificats amb el mètode getAuthorizationType()
    * 	'edit'  => 'read'
 */