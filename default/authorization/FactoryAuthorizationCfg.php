<?php
/**
 * FactoryAuthorizationCfg: Definició de les classes (i fitxers de classe) d'autoritzacions
 *
 * @author Rafael Claver
 */

$_AuthorizationCfg = 
    array(
	'_command'		=> array('auth' => 'read', 'file' => 'read')
	,'cancel'		=> array('auth' => 'write', 'file' => 'write')
	,'edit'			=> array('auth' => 'read', 'file' => 'read')
	,'diff'			=> array('auth' => 'read', 'file' => 'read')
	,'new_page'		=> array('auth' => 'create', 'file' => 'create')
	,'page'			=> array('auth' => 'read', 'file' => 'read')
	,'revision'		=> array('auth' => 'write', 'file' => 'write')
	,'save'			=> array('auth' => 'write', 'file' => 'write')
	,'copy_image_to_project'=> array('auth' => 'upload', 'file' => 'upload')
	,'get_image_detail'	=> array('auth' => 'read', 'file' => 'read')
	,'media'		=> array('auth' => 'read', 'file' => 'read')
	,'mediadetails'		=> array('auth' => 'read', 'file' => 'read')
	,'media_delete'		=> array('auth' => 'delete', 'file' => 'delete')
	,'media_edit'		=> array('auth' => 'write', 'file' => 'write')
	,'media_upload'		=> array('auth' => 'upload', 'file' => 'upload')
    );