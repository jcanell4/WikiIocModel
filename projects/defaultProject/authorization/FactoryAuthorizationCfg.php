<?php
/**
 * FactoryAuthorizationCfg: Definició de les classes (fitxers de classe) d'autoritzacions de comandes
 *
 * @author Rafael Claver
 */

$_AuthorizationCfg = 
    array(
        '_command'              => 'admin'      /*Default case*/
	,'cancel'		=> 'read'
	,'cancel_partial'	=> 'read'
	,'edit_partial'		=> 'edit'
	,'diff'			=> 'read'
	,'new_page'		=> 'create'
	,'page'			=> 'read'
	,'revision'		=> 'write'
	,'save'			=> 'write'
	,'save_partial'		=> 'write'
	,'save_draft'		=> 'write'
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
        ,"_none"                => "command"
    );