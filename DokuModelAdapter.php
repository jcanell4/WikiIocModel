<?php

/**
 * Description of DokuModelAdapter
 *
 * @author Josep Cañellas <jcanell4@ioc.cat>
 */
if ( ! defined( 'DOKU_INC' ) ) {
	die();
}
//require common
require_once DOKU_INC . 'inc/actions.php';
require_once DOKU_INC . 'inc/pageutils.php';
require_once DOKU_INC . 'inc/common.php';
require_once DOKU_INC . 'inc/media.php';
require_once DOKU_INC . 'inc/auth.php';
require_once DOKU_INC . 'inc/confutils.php';
require_once DOKU_INC . 'inc/io.php';
require_once DOKU_INC . 'inc/template.php';
require_once DOKU_INC . 'inc/JSON.php';
require_once DOKU_INC . 'inc/JpegMeta.php';

if ( ! defined( 'DOKU_PLUGIN' ) ) {
	define( 'DOKU_PLUGIN', DOKU_INC . 'lib/plugins/' );
}
require_once( DOKU_PLUGIN . 'wikiiocmodel/WikiIocModel.php' );
require_once( DOKU_PLUGIN . 'wikiiocmodel/WikiIocModelExceptions.php' );
require_once( DOKU_PLUGIN . 'acl/admin.php' );

if ( ! defined( 'DW_DEFAULT_PAGE' ) ) {
	define( 'DW_DEFAULT_PAGE', "start" );
}
if ( ! defined( 'DW_ACT_SHOW' ) ) {
	define( 'DW_ACT_SHOW', "show" );
}
if ( ! defined( 'DW_ACT_DRAFTDEL' ) ) {
	define( 'DW_ACT_DRAFTDEL', "draftdel" );
}
if ( ! defined( 'DW_ACT_SAVE' ) ) {
	define( 'DW_ACT_SAVE', "save" );
}
if ( ! defined( 'DW_ACT_EDIT' ) ) {
	define( 'DW_ACT_EDIT', "edit" );
}
if ( ! defined( 'DW_ACT_PREVIEW' ) ) {
	define( 'DW_ACT_PREVIEW', "preview" );
}
if ( ! defined( 'DW_ACT_RECOVER' ) ) {
	define( 'DW_ACT_RECOVER', "recover" );
}
if ( ! defined( 'DW_ACT_DENIED' ) ) {
	define( 'DW_ACT_DENIED', "denied" );
}
if ( ! defined( 'DW_ACT_MEDIA_DETAIL' ) ) {
	define( 'DW_ACT_MEDIA_DETAIL', "media_detail" );
}
if ( ! defined( 'DW_ACT_MEDIA_MANAGER' ) ) {
	define( 'DW_ACT_MEDIA_MANAGER', "media" );
}
if ( ! defined( 'DW_ACT_EXPORT_ADMIN' ) ) {
	define( 'DW_ACT_EXPORT_ADMIN', "admin" );
}
if ( ! defined( 'DW_ACT_MEDIA_DETAILS' ) ) {
	define( 'DW_ACT_MEDIA_DETAILS', "mediadetails" );
}

//    const DW_ACT_BACKLINK="backlink";
//    const DW_ACT_REVISIONS="revisions";
//    const DW_ACT_DIFF="diff";
//    const DW_ACT_SUBSCRIBE="subscribe";
//    const DW_ACT_UNSUBSCRIBE="unsubscribe";
//    const DW_ACT_SUBSCRIBENS="subscribens";
//    const DW_ACT_UNSUBSCRIBENS="unsubscribens";
//    const DW_ACT_INDEX="index";
//    const DW_ACT_RECENT="recent";
//    const DW_ACT_SEARCH="search";
//    const DW_ACT_EXPORT_RAW="export_raw";
//    const DW_ACT_EXPORT_XHTML="export_xhtml";
//    const DW_ACT_EXPORT_XHTMLBODY="export_xhtmlbody";
//    const DW_ACT_CHECK="check";
//    const DW_ACT_INDEX="register";
//    const DW_ACT_LOGIN="login";
//    const DW_ACT_LOGOUT="logout";
//    const DW_ACT_EXPORT_PROFILE="profile";
//    const DW_ACT_EXPORT_RESENDPWD="resendpwd";
//    const DW_ACT_DRAFT="draft";
//    const DW_ACT_WORDBLOCK="wordblock";
//    const DW_ACT_CONFLICT="conflict";
//    const DW_ACT_CANCEL="cancel";

/**
 * Mostra una pàgina de la DokuWiki.
 * TODO[Xavi] no es fa res amb l'argument
 *
 * @param string $data
 */
function onFormatRender( $data ) {
	html_show();
}

/**
 * Segons el valor de $data activa la edició del document('edit' i 'recover'), la previsualització ('preview') o mostra
 * el missatge de denegat ('denied').
 *
 * @param string $data els valors admessos son 'edit', 'recover', 'preview' i 'denied'
 */
function onCodeRender( $data ) {
	global $TEXT;

	switch ( $data ) {
		case 'locked':
		case 'edit':
		case 'recover':
			html_edit();
			break;
		case 'preview':
			html_edit();
			html_show( $TEXT );
			break;
		case 'denied':
			print p_locale_xhtml( 'denied' );
			break;
	}
}

/**
 * Retorna la taula de continguts modificada amb la nostra cadena.
 *
 * @return string taula de continguts
 */
function wrapper_tpl_toc() {
	$toc = tpl_toc( TRUE );
	$toc = preg_replace(
		'/(<!-- TOC START -->\s?)(.*\s?)(<div class=.*tocheader.*<\/div>|<h3 class=.*toggle.*<\/h3>)((.*\s)*)(<!-- TOC END -->)/i',
		'$1<div class="dokuwiki">$2$4</div>$6', $toc
	);

	return $toc;
}

/**
 * Class DokuModelAdapter
 * Adaptador per passar les nostres comandes a la Dokuwiki.
 *
 * @author Josep Cañellas <jcanell4@ioc.cat>
 */
class DokuModelAdapter implements WikiIocModel {
	const ADMIN_PERMISSION = "admin";

	protected $params;
	protected $dataTmp;
	protected $ppEvt;
	protected $infoLoaded = FALSE;

	public static $DEFAULT_FORMAT = 0;
	public static $SHORT_FORMAT = 1;

	public function getAdminTask( $ptask, $pid = NULL ) {
		global $lang;

		$this->startAdminTaskProcess( $ptask, $pid );
		$this->doAdminTaskPreProcess();
		$response = $this->getAdminTaskResponse();
		// Informació a pantalla
		$info_time_visible = 5;
		switch ( $_REQUEST['page'] ) {
			case 'config':
				if ( ! $response['needRefresh'] ) {
					if ( isset( $_REQUEST['do'] ) ) {
						$response['info'] = $this->generateInfo( "info", $lang['admin_task_loaded'], NULL, $info_time_visible );
					} else {

						$response['info'] = $this->generateInfo( "info", $lang['button_clicked'] . '"' . $lang['button_desa'] . '"', NULL, $info_time_visible );
					}
				}
				break;
			case 'plugin':
				switch ( key( $_REQUEST['fn'] ) ) {
					case NULL:
						// call from the admin tab
						$response['info'] = $this->generateInfo( "info", $lang['admin_task_loaded'], NULL, $info_time_visible );
						break;
					default:
						// call from the user plugin tab
						$fn = $_REQUEST['fn'];
						if ( is_array( $fn[ key( $fn ) ] ) ) {
							$fn = $fn[ key( $fn ) ];
						}
						$response['info'] = $this->generateInfo( "info", $lang['button_clicked'] . '"' . $fn[ key( $fn ) ] . '"', NULL, $info_time_visible );
				}
				break;
			case 'acl':
				switch ( $_REQUEST['cmd'] ) {
					case NULL:
						$response['info'] = $this->generateInfo( "info", $lang['admin_task_loaded'] );
						break;
					case 'del':
						$response['info'] = $this->generateInfo( "info", $lang['admin_task_perm_delete'], NULL, $info_time_visible );
						break;
					case 'save':
					case 'update':
						$response['info'] = $this->generateInfo( "info", $lang['admin_task_perm_update'], NULL, $info_time_visible );
						break;
					default:
						$response['info'] = $this->generateInfo( "info", $_REQUEST['cmd'] );
				}
				break;
			case 'usermanager':
				$fn  = $_REQUEST['fn'];
				$key = key( $fn );
				if ( ! isset( $key ) ) {
					// call from the admin tab
					$response['info'] = $this->generateInfo( "info", $lang['admin_task_loaded'], NULL, $info_time_visible );
				} else {
					// call from the user plugin tab
					if ( is_array( $fn ) ) {
						$cmd = key( $fn );
					} else {
						$cmd = $fn;
					}

					switch ( $cmd ) {
						case "add":
						case "delete":
						case "export" :
						case "import" :
						case "importfails":
						case "modify":
						case "start":
						case "next":
						case "last":
						case "prev":
							$param = $fn[ key( $fn ) ];
							break;
						case "edit"   :
							$param = $lang['button_edit_user'];
							break;
						case "search" :
							$param = $lang['button_filter_user'];
							break;
					}
					$response['info']   = $this->generateInfo( "info", $lang['button_clicked'] . '"' . $param . '"', NULL, $info_time_visible );
					$response['iframe'] = TRUE;
				}
				break;
			case "revert":
				if ( isset( $_REQUEST['revert'] ) ) {
					$response['info'] = $this->generateInfo( "info", $lang['button_clicked'] . '"' . $lang['button_revert'] . '"', NULL, $info_time_visible );
				} else if ( isset( $_REQUEST['filter'] ) ) {
					$response['info'] = $this->generateInfo( "info", $lang['button_clicked'] . '"' . $lang['button_cercar'] . '"', NULL, $info_time_visible );
				} else {
					$response['info'] = $this->generateInfo( "info", $lang['admin_task_loaded'], NULL, $info_time_visible );
				}
				break;
			case "latex":
				if ( isset( $_REQUEST['latexpurge'] ) ) {
					$response['info'] = $this->generateInfo( "info", $lang['button_clicked'] . '"' . $_REQUEST['latexpurge'] . '"', NULL, $info_time_visible );
				} else if ( isset( $_REQUEST['dotest'] ) ) {
					$response['info'] = $this->generateInfo( "info", $lang['button_clicked'] . '"' . $_REQUEST['dotest'] . '"', NULL, $info_time_visible );
				} else {
					$response['info'] = $this->generateInfo( "info", $lang['admin_task_loaded'], NULL, $info_time_visible );
				}
				break;
			default:
				$response['info'] = $this->generateInfo( "info", "Emplenar a DokumodelAdapter->getAdminTask:" + $_REQUEST['page'] );
				break;
		}

		return $response;
	}

	public function getAdminTaskList() {

		$this->startAdminTaskProcess();
		$this->doAdminTaskListPreProcess();

		return $this->getAdminTaskListResponse();
	}

	public function createPage( $pid, $text = NULL ) {
		global $INFO;
		global $lang;
		global $ACT;

		$this->startUpLang();

		if ( ! $text ) {
			$text = $lang['createDefaultText'];
		}

		$this->startPageProcess(
			DW_ACT_SAVE, $pid, NULL, NULL, $lang['created'], NULL,
			"", $text, ""
		);
		if ( $INFO["exists"] ) {
			throw new PageAlreadyExistsException( $pid, $lang['pageExists'] );
		}

		$permis_actual = $this->obtenir_permis( $pid, $_SERVER['REMOTE_USER'] );
		if ( $permis_actual < AUTH_CREATE ) {
			//se pide el permiso para el directorio (no para la página)
			$permis_actual = $this->setUserPagePermission( getNS( $pid ) . ':*', $INFO['client'], AUTH_DELETE );
		}
		if ( $permis_actual >= AUTH_CREATE ) {
			$code = $this->doSavePreProcess();
		} else {
			throw new InsufficientPermissionToCreatePageException( $pid ); //TODO [Josep] cal internacionalitzar el missage per defecte
		}

		$response = $this->getFormatedPageResponse();
		// Si no s'ha especificat cap altre missatge mostrem el de carrega
		if ( ! $response['info'] ) {
			$response['info'] = $this->generateInfo( "info", $lang['document_created'] );
		}

		return $response;
	}

	public function getHtmlPage( $pid, $prev = NULL ) {
		global $INFO;
		global $lang;

		$this->startPageProcess( DW_ACT_SHOW, $pid, $prev );
		if ( ! $INFO["exists"] ) {
			throw new PageNotFoundException( $pid, $lang['pageNotFound'] );
		}
		if ( ! $INFO["perm"] ) {
			throw new InsufficientPermissionToViewPageException( $pid ); //TODO [Josep] Internacionalització missatge per defecte!
		}
		$this->doFormatedPagePreProcess();

		$response = $this->getFormatedPageResponse();

		// Si no s'ha especificat cap altre missatge mostrem el de carrega
		if ( ! $response['info'] ) {
			$response['info'] = $this->generateInfo( "info", $lang['document_loaded'] );
		}

		return $response;
	}

	public function getCodePage( $pid, $prev = NULL, $prange = NULL, $psum = NULL ) {
		global $INFO;
		global $lang;

		$this->startPageProcess( DW_ACT_EDIT, $pid, $prev, $prange, $psum );
		if ( ! $INFO["exists"] ) {
			throw new PageNotFoundException( $pid, $lang['pageNotFound'] );
		}
		if ( ! $INFO["perm"] ) {
			throw new InsufficientPermissionToEditPageException( $pid ); //TODO [Josep] Internacionalització missatge per defecte!
		}
		$this->doEditPagePreProcess();

		$response = $this->getCodePageResponse();

		// TODO[Xavi] Comprovar si aquí ja està establert el contingut i d'on prové
		return $response;
	}

	public function cancelEdition( $pid, $prev = NULL, $keep_draft = FALSE ) {
		global $lang;

		$this->startPageProcess( DW_ACT_DRAFTDEL, $pid, $prev );
		$this->doCancelEditPreprocess( $keep_draft );

		$response          = $this->getFormatedPageResponse();
		$response ['info'] = $this->generateInfo( "info", $lang['edition_cancelled'] );

		return $response;
	}

	public function saveEdition(
		$pid, $prev = NULL, $prange = NULL,
		$pdate = NULL, $ppre = NULL, $ptext = NULL, $psuf = NULL, $psum = NULL
	) {
		$this->startPageProcess(
			DW_ACT_SAVE, $pid, $prev, $prange, $psum, $pdate,
			$ppre, $ptext, $psuf
		);
		$code = $this->doSavePreProcess();

		return $this->getSaveInfoResponse( $code );
	}

	public function isAdminOrManager( $checkIsmanager = TRUE ) {
		global $INFO;
		if ( ! $this->infoLoaded ) {
			$this->fillInfo();
		}

		return $INFO['isadmin'] || $checkIsmanager && $INFO['ismanager'];
	}

	/**
	 * Si el valor de la variable global $ACT es 'denied' retorna false, en cualsevol altre cas retorna true.
	 *
	 * @return bool
	 */
	public function isDenied() {
		global $ACT;
		$this->params['do'] = $ACT;

		return $this->params['do'] == DW_ACT_DENIED;
	}

	public function getMediaFileName( $id, $rev = '' ) {
		return mediaFN( $id, $rev );
	}

	public function getIdWithoutNs( $id ) {
		return noNS( $id );
	}

	public function getMediaList( $ns ) {
		$dir      = $this->getMediaFileName( $ns );
		$arrayDir = scandir( $dir );
		if ( $arrayDir ) {
			unset( $arrayDir[0] );
			unset( $arrayDir[1] );
			$arrayDir = array_values( $arrayDir );
		} else {
			$arrayDir = array();
		}

		return $arrayDir;
	}

	public function imagePathToId( $path ) {
		global $conf;
		if ( $this->starsWith( $path, "/" ) ) { //absolute path
			$path = str_replace( $conf['mediadir'] . "/", "", $path );
		}
		$id = str_replace( '/', ':', $path );

		return $id;
	}

	// TODO[Xavi] No es cridat en lloc
	public function getPageFileName( $id, $rev = '' ) {
		return wikiFN( $id, $rev );
	}

	/**
	 * @param string $image //abans era $id. $id no s'utilitzava
	 * @param bool   $rev
	 * @param bool   $meta
	 *
	 * @return string
	 */
	public function getMediaUrl( $image, $rev = FALSE, $meta = FALSE ) {
		$size = media_image_preview_size( $image, $rev, $meta );
		if ( $size ) {
			$more = array();
			if ( $rev ) {
				$more['rev'] = $rev;
			} else {
				$t         = @filemtime( mediaFN( $image ) );
				$more['t'] = $t;
			}
			$more['w'] = $size[0];
			$more['h'] = $size[1];
			$src       = ml( $image, $more );
		} else {
			$src = ml( $image, "", TRUE );
		}

		return $src;
	}

	/**
	 * @param string $nsTarget
	 * @param string $idTarget
	 * @param string $filePathSource
	 * @param bool   $overWrite
	 *
	 * @return int
	 */
	public function uploadImage( $nsTarget, $idTarget, $filePathSource, $overWrite = FALSE ) {
		return $this->_saveImage(
			$nsTarget, $idTarget, $filePathSource
			, $overWrite, "move_uploaded_file"
		);
	}

	/**
	 * @param string $nsTarget
	 * @param string $idTarget
	 * @param string $filePathSource
	 * @param bool   $overWrite
	 *
	 * @return int
	 */
	public function saveImage( $nsTarget, $idTarget, $filePathSource, $overWrite = FALSE ) {
		return $this->_saveImage(
			$nsTarget, $idTarget, $filePathSource
			, $overWrite, "copy"
		);
	}

	public function getImageDetail( $imageId, $fromPage = NULL ) {
		global $lang;

		$error = $this->startMediaProcess( DW_ACT_MEDIA_DETAIL, $imageId, $fromPage );
		if ( $error == 401 ) {
			throw new HttpErrorCodeException( $error, "Access denied" );
		} else if ( $error == 404 ) {
			throw new HttpErrorCodeException( $error, "Resource " . $imageId . " not found." );
		}
		$title = $lang['img_detail_title'] . $imageId;
		$ret   = array(
			"content"          => $this->_getImageDetail(),
			"imageTitle"       => $title,
			"imageId"          => $imageId,
			"fromId"           => $fromPage,
			"modifyImageLabel" => $lang['img_manager'],
			"closeDialogLabel" => $lang['img_backto']
		);

		return $ret;
	}

	public function getNsTree( $currentnode, $sortBy, $onlyDirs = FALSE ) {
		global $conf;
		$base = $conf['datadir'];

		return $this->getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs );
	}

	private function getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs = FALSE ) {
		$sortOptions = array( 0 => 'name', 'date' );
		$nodeData    = array();
		$children    = array();

		if ( $currentnode == "_" ) {
			return array( 'id' => "", 'name' => "", 'type' => 'd' );
		}
		if ( $currentnode ) {
			$node  = $currentnode;
			$aname = split( ":", $currentnode );
			$level = count( $aname );
			$name  = $aname[ $level - 1 ];
		} else {
			$node  = '';
			$name  = '';
			$level = 0;
		}
		$sort = $sortOptions[ $sortBy ];

		$opts = array( 'ns' => $node );
		$dir  = str_replace( ':', '/', $node );
		search(
			$nodeData, $base, 'search_index',
			$opts, $dir, 1
		);
		foreach ( array_keys( $nodeData ) as $item ) {
			if ( $onlyDirs && $nodeData[ $item ]['type'] == 'd' || ! $onlyDirs ) {
				$children[ $item ]['id']   = $nodeData[ $item ]['id'];
				$aname                     = split( ":", $nodeData[ $item ]['id'] ); //TODO[Xavi] @deprecated substitur per explode()
				$children[ $item ]['name'] = $aname[ $level ];
				$children[ $item ]['type'] = $nodeData[ $item ]['type'];
			}
		}

		$tree = array(
			'id'       => $node,
			'name'     => $node,
			'type'     => 'd',
			'children' => $children
		);

		return $tree;
	}

	public function getGlobalMessage( $id ) {
		global $lang;

		return $lang[ $id ];
	}

	/**
	 * Crea el directori on ubicar el fitxer referenciat per $filePath després
	 * d'extreure'n el nom del fitxer. Aquesta funció no crea directoris recursivamnent.
	 *
	 * @param type $filePath
	 */
	public function makeFileDir( $filePath ) {
		io_makeFileDir( $filePath );
	}

	public function tplIncDir() {
		global $conf;
		if ( is_callable( 'tpl_incdir' ) ) {
			$ret = tpl_incdir();
		} else {
			$ret = DOKU_INC . 'lib/tpl/' . $conf['template'] . '/';
		}

		return $ret;
	}

	// configuration methods
	/**
	 * tpl_getConf($id)
	 *
	 * use this function to access template configuration variables
	 */
	public function tplConf( $id ) {
		return tpl_getConf( $id );
	}

	public function setUserPagePermission( $page, $user, $acl_level ) {
		global $INFO;
		global $conf;
		include_once( DOKU_PLUGIN . 'wikiiocmodel/conf/default.php' );
		$pageuser = ":" . substr( $page, 0, strrpos( $page, ":" ) );
		$userpage = substr( $pageuser, strrpos( $pageuser, ":" ) + 1 );
		$ret      = FALSE;
		if ( $INFO['isadmin'] || $INFO['ismanager'] || (
				$INFO['namespace'] == substr( $page, 0, strrpos( $page, ":" ) ) &&
				$userpage == $user &&
				$conf['userpage_allowed'] === 1 && (
					$pageuser == $conf['userpage_ns'] . $user ||
					$pageuser == $conf['userpage_discuss_ns'] . $user )
			)
		) {
			$INFO['perm'] = $ret = $this->establir_permis( $page, $user, $acl_level, TRUE );
		}

		return $ret;
	}

	/**
	 * administració de permisos
	 *
	 * @param $page y $user son obligatorios
	 */
	private function obtenir_permis( $page, $user ) {
		$acl_class = new admin_plugin_acl();
		$acl_class->handle();
		$acl_class->who = $user;
		$permis         = auth_quickaclcheck( $page );

		/* este bucle obtiene el mismo resultado que auth_quickaclcheck()
		$permis = NULL;
		$sub_page = $page;
		while (!$permis && $sub_page) {
			$acl_class->ns = $sub_page;
			$permis = $acl_class->_get_exact_perm();
			$sub_page = substr($sub_page,0,strrpos($sub_page,':'));
		}
		*/

		return $permis;
	}

	/**
	 * @param bool $force   : true : indica que s'ha d'establir el permís estricte
	 *                      false: si existeix algún permís, no es modifica
	 */
	private function establir_permis( $page, $user, $acl_level, $force = FALSE ) {
		$acl_class = new admin_plugin_acl();
		$acl_class->handle();
		$acl_class->who = $user;
		$permis_actual  = auth_quickaclcheck( $page );

		if ( $force || $acl_level > $permis_actual ) {
			$ret = $acl_class->_acl_add( $page, $user, $acl_level );
			if ( $ret ) {
				if ( strpos( $page, '*' ) === FALSE ) {
					if ( $acl_level > AUTH_EDIT ) {
						$permis_actual = AUTH_EDIT;
					}
				} else {
					$permis_actual = $acl_level;
				}
			}
		}

		return $permis_actual;
	}

	private function eliminar_permis( $page, $user ) {
		$acl_class = new admin_plugin_acl();
		//$acl_class->handle();
		//$acl_class->who = $user;
		if ( $page && $user ) {
			$ret = $acl_class->_acl_del( $page, $user );
		}

		return $ret;
	}

	/**
	 * Retorna si s'ha trobat la cadena que es cerca al principi de la cadena on es busca.
	 *
	 * @param string $haystack cadena on buscar
	 * @param string $needle   cadena per buscar
	 *
	 * @return bool true si la cadena comença com la cadena passada per argument o la cadena a buscar es buida, i false
	 * en cas contrari
	 */
	private function starsWith( $haystack, $needle ) {
		return $needle === "" || strpos( $haystack, $needle ) === 0;
	}

	/**
	 * Retorna si s'ha trobat la cadena que es cerca al final de la cadena on es busca.
	 *
	 * @param string $haystack cadena on buscar
	 * @param string $needle   cadena per buscar
	 *
	 * @return bool true si la cadena acaba com la cadena passada per argument o la cadena a buscar es buida, i false
	 * en cas contrari
	 */
	private function endsWith( $haystack, $needle ) {
		return $needle === "" || substr( $haystack, - strlen( $needle ) ) === $needle;
	}

	/**
	 * @param string   $nsTarget
	 * @param string   $idTarget
	 * @param string   $filePathSource
	 * @param boolean  $overWrite
	 * @param callable $copyFunction funció que es cridarà per moure el fitxer de la ruta tempora a la ruta final.
	 *                               Aquesta funciò ha de rebre com a paràmetres dos strings, el primer amb el nom del
	 *                               fitxer temporal i el segon amb el nom del fitxer final
	 *
	 * @return int enter corresponent a un dels següents codis:
	 *       0 = OK
	 *      -1 = UNAUTHORIZED
	 *      -2 = OVER_WRITING_NOT_ALLOWED
	 *      -3 = OVER_WRITING_UNAUTHORIZED
	 *      -5 = FAILS
	 *      -4 = WRONG_PARAMS
	 *      -6 = BAD_CONTENT
	 *      -7 = SPAM_CONTENT
	 *      -8 = XSS_CONTENT
	 */
	private function _saveImage(
		$nsTarget, $idTarget, $filePathSource, $overWrite
		, $copyFunction
	) {
		global $conf;
		$res = NULL; //(0=OK, -1=UNAUTHORIZED, -2=OVER_WRITING_NOT_ALLOWED,
		//-3=OVER_WRITING_UNAUTHORIZED, -5=FAILS, -4=WRONG_PARAMS
		//-6=BAD_CONTENT, -7=SPAM_CONTENT, -8=XSS_CONTENT)
		$auth = auth_quickaclcheck( getNS( $idTarget ) . ":*" );

		if ( $auth >= AUTH_UPLOAD ) {
			io_createNamespace( "$nsTarget:xxx", 'media' );
			list( $ext, $mime, $dl ) = mimetype( $idTarget );
			$res_media = media_save(
				array(
					'name' => $filePathSource,
					'mime' => $mime,
					'ext'  => $ext
				),
				$nsTarget . ':' . $idTarget,
				$overWrite,
				$auth,
				$copyFunction
			);

			if ( is_array( $res_media ) ) {
				if ( $res_media[1] == 0 ) {
					if ( $auth < ( ( $conf['mediarevisions'] ) ? AUTH_UPLOAD : AUTH_DELETE ) ) {
						$res = - 3;
					} else {
						$res = - 2;
					}
				} else if ( $res_media[1] == - 1 ) {
					$res = - 5;
					$res += media_contentcheck( $filePathSource, $mime );
				}
			} else if ( ! $res_media ) {
				$res = - 4;
			} else {
				$res = 0;
			}
		} else {
			$res = - 1; //NO AUTORITZAT
		}

		return $res;
	}

	/**
	 * Inicia tractament per obtenir la llista de gestions d'administració
	 */
	private function startAdminTaskProcess( $ptask = NULL, $pid = NULL ) {
		global $ACT;
		global $_REQUEST;
		global $ID;
		global $conf;

		// Agafem l'index de la configuració
		if ( ! isset( $pid ) ) {
			$pid = $conf['start'];
		}

		$ID = $this->params['id'] = $pid;

		$ACT = $this->params['do'] = DW_ACT_EXPORT_ADMIN;

		$this->fillInfo();
		$this->startUpLang();
		if ( $ptask ) {
			if ( ! $_REQUEST['page'] || $_REQUEST['page'] != $ptask ) {
				$_REQUEST['page'] = $ptask;
			}
			$this->params['task'] = $ptask;
		}

		$this->triggerStartEvents();
	}

	/**
	 * Inicia tractament d'una pàgina de la dokuwiki
	 */
	private function startPageProcess(
		$pdo, $pid = NULL, $prev = NULL, $prange = NULL,
		$psum = NULL, $pdate = NULL, $ppre = NULL, $ptext = NULL, $psuf = NULL
	) {
		global $ID;
		global $ACT;
		global $REV;
		global $RANGE;
		global $DATE;
		global $PRE;
		global $TEXT;
		global $SUF;
		global $SUM;

		$ACT = $this->params['do'] = $pdo;
		$ACT                       = act_clean( $ACT );

		if ( ! $pid ) {
			$pid = DW_DEFAULT_PAGE;
		}
		$ID = $this->params['id'] = $pid;
		if ( $prev ) {
			$REV = $this->params['rev'] = $prev;
		}
		if ( $prange ) {
			$RANGE = $this->params['range'] = $prange;
		}
		if ( $pdate ) {
			$DATE = $this->params['date'] = $pdate;
		}
		if ( $ppre ) {
			$PRE = $this->params['pre'] = cleanText( substr( $ppre, 0, - 1 ) );
		}
		if ( $ptext ) {
			$TEXT = $this->params['text'] = cleanText( $ptext );
		}
		if ( $psuf ) {
			$SUF = $this->params['suf'] = cleanText( $psuf );
		}
		if ( $psum ) {
			$SUM = $this->params['sum'] = $psum;
		}

		$this->fillInfo();
		$this->startUpLang();

		$this->triggerStartEvents();

	}

	/**
	 * Inicia tractament d'una pàgina de la dokuwiki
	 */
	private function startMediaProcess( $pdo, $pImageId = NULL, $pFromId = NULL ) {
		global $ID;
		global $AUTH;
		global $vector_action;
		global $vector_context;
		global $loginname;
		global $IMG;
		global $ERROR;
		global $SRC;
		global $conf;
		global $lang;
		global $INFO;

		$ret = $ERROR = 0;

		$this->params['action'] = $pdo;
		if ( $pdo === DW_ACT_MEDIA_DETAIL ) {
			$vector_action = $GET["vecdo"] = $this->params['vector_action'] = "detail";
		}

		if ( $pImageId ) {
			$IMG = $this->params['imageId'] = $pImageId;
		}
		if ( $pFromId ) {
			$ID = $this->params['id'] = $pFromId;
		}
		// check image permissions
		$AUTH = auth_quickaclcheck( $pImageId );
		if ( $AUTH >= AUTH_READ ) {
			// check if image exists
			$SRC = mediaFN( $pImageId );
			if ( ! file_exists( $SRC ) ) {
				$ret = $ERROR = 404;
			}
		} else {
			// no auth
			$ret = $ERROR = 401;
		}

		if ( $ret != 0 ) {
			return $ret;
		}

		$INFO = array_merge( pageinfo(), mediainfo() );

		/**
		 * Stores the template wide context
		 *
		 * This template offers discussion pages via common articles, which should be
		 * marked as "special". DokuWiki does not know any "special" articles, therefore
		 * we have to take care about detecting if the current page is a discussion
		 * page or not.
		 *
		 * @var string
		 * @author Andreas Haerter <development@andreas-haerter.com>
		 */
		$vector_context = $this->params['vector_context'] = "article";
		if ( $pFromId && preg_match(
				"/^" . tpl_getConf( "vector_discuss_ns" ) . "?$|^"
				. tpl_getConf( "vector_discuss_ns" ) . ".*?$/i", ":"
				                                                 . getNS( getID() )
			)
		) {
			$vector_context = $this->params['vector_context'] = "discuss";
		}

		/**
		 * Stores the name the current client used to login
		 *
		 * @var string
		 * @author Andreas Haerter <development@andreas-haerter.com>
		 */
		$loginname = $this->params['loginName'] = "";
		if ( ! empty( $conf["useacl"] ) ) {
			if ( isset( $_SERVER["REMOTE_USER"] ) && //no empty() but isset(): "0" may be a valid username...
			     $_SERVER["REMOTE_USER"] !== ""
			) {
				$loginname = $this->params['loginName'] = $_SERVER["REMOTE_USER"]; //$INFO["client"] would not work here (-> e.g. if
				//current IP differs from the one used to login)
			}
		}

		$this->startUpLang();

		//detect revision
		$rev = $this->params['rev'] = (int) $INFO["rev"]; //$INFO comes from the DokuWiki core
		if ( $rev < 1 ) {
			$rev = $this->params['rev'] = (int) $INFO["lastmod"];
		}

		$this->triggerStartEvents();

		return $ret;
	}

	private function triggerStartEvents() {
		$tmp = array(); //NO DATA
		trigger_event( 'DOKUWIKI_STARTED', $tmp );
		trigger_event( 'WIOC_AJAX_COMMAND_STARTED', $this->dataTmp );
	}

	private function triggerEndEvents() {
		$tmp = array(); //NO DATA
		trigger_event( 'DOKUWIKI_DONE', $tmp );
		//trigger_event( 'WIOC_AJAX_COMMAND_DONE', $this->dataTmp );
	}

	private function startUpLang() {
		global $conf;
		global $lang;

		//get needed language array
		include $this->tplIncDir() . "lang/en/lang.php";
		//overwrite English language values with available translations
		if ( ! empty( $conf["lang"] ) &&
		     $conf["lang"] !== "en" &&
		     file_exists( $this->tplIncDir() . "/lang/" . $conf["lang"] . "/lang.php" )
		) {
			//get language file (partially translated language files are no problem
			//cause non translated stuff is still existing as English array value)
			include $this->tplIncDir() . "/lang/" . $conf["lang"] . "/lang.php";
		}
		if ( ! empty( $conf["lang"] ) &&
		     $conf["lang"] !== "en" &&
		     file_exists( DOKU_PLUGIN . "wikiiocmodel/lang/" . $conf["lang"] . "/lang.php" )
		) {
			include DOKU_PLUGIN . "wikiiocmodel/lang/" . $conf["lang"] . "/lang.php";
		}
	}

	/**
	 * Realitza el per-procés d'una pàgina de la dokuwiki en format HTML.
	 * Permet afegir etiquetes HTML al contingut final durant la fase de
	 * preprocés
	 *
	 * @return string
	 */
	private function doFormatedPagePreProcess() {
		$content = "";
		if ( $this->runBeforePreprocess( $content ) ) {
			unlock( $this->params['id'] ); //try to unlock
		}
		$this->runAfterPreprocess( $content );

		return $content;
	}

	/**
	 * Realitza el per-procés per recuperar el detall d'una imatge de la dokuwiki.
	 * Permet afegir etiquetes HTML al contingut final durant la fase de
	 * preprocés
	 *
	 * @return string
	 */
	private function _getImageDetail() {
		global $ID;
		global $AUTH;
		global $vector_action;
		global $vector_context;
		global $loginname;
		global $IMG;
		global $ERROR;
		global $SRC;
		global $conf;
		global $lang;

		ob_start();
		include $this->tplIncDir() . "inc_detail.php";
		$content = ob_get_clean();
//        $content = preg_replace(
//            '/(<!-- TOC START -->\s?)(.*\s?)(<div class=.*tocheader.*<\/div>|<h3 class=.*toggle.*<\/h3>)((.*\s)*)(<!-- TOC END -->)/i',
//            '$1<div class="dokuwiki">$2$4</div>$6', $toc
//        );

		return $content;
	}

	private function doEditPagePreProcess() {
		global $ACT;

		$content = "";
		if ( $this->runBeforePreprocess( $content ) ) {
			$ACT = act_edit( $ACT );
			$ACT = act_permcheck( $ACT );
		}
		$this->runAfterPreprocess( $content );

		return $content;
	}

	private function doAdminTaskPreProcess() {
		global $ACT;
		global $INFO;
		global $conf;
		global $ID;

		$content = "";
		if ( $this->runBeforePreprocess( $content ) ) {
			$ACT = act_permcheck( $ACT );
			//handle admin tasks
			// retrieve admin plugin name from $_REQUEST['page']
			if ( ! empty( $_REQUEST['page'] ) ) {
				$pluginlist = plugin_list( 'admin' );
				if ( in_array( $_REQUEST['page'], $pluginlist ) ) {
					// attempt to load the plugin
					if ( $plugin =& plugin_load( 'admin', $_REQUEST['page'] ) !== NULL ) {
						if ( $plugin->forAdminOnly() && ! $INFO['isadmin'] ) {
							// a manager tried to load a plugin that's for admins only
							unset( $_REQUEST['page'] );
							msg( 'For admins only', - 1 );
						} else {
							if ( is_callable( array( $plugin, "preventRefresh" ) ) ) {
								$allowedRefresh = $plugin->preventRefresh();
							}
							$plugin->handle();
							$this->dataTmp["needRefresh"] = is_callable( array( $plugin, "isRefreshNeeded" ) );
							if ( $this->dataTmp["needRefresh"] ) {
								$this->dataTmp["needRefresh"] = $plugin->isRefreshNeeded();
							}
							$this->dataTmp["title"] = $plugin->getMenuText( $conf['lang'] );
							if ( isset( $allowedRefresh )
							     && is_callable( array( $plugin, "setAllowedRefresh" ) )
							) {
								$plugin->setAllowedRefresh( $allowedRefresh );
							}
						}
					}
				}
			}
			// check permissions again - the action may have changed
			$ACT = act_permcheck( $ACT );
		}
		$this->runAfterPreprocess( $content );

		return $content;
	}

	private function doAdminTaskListPreProcess() {
		global $ACT;

		$content = "";
		if ( $this->runBeforePreprocess( $content ) ) {
			$ACT = act_permcheck( $ACT );
		}
		$this->runAfterPreprocess( $content );

		return $content;
	}

	private function doSavePreProcess() {
		global $ACT;

		$code = 0;
		$ACT  = act_permcheck( $ACT );

		if ( $ACT == $this->params['do'] ) {
			$ret = act_save( $ACT );
		} else {
			$ret = $ACT;
		}
		if ( $ret === 'edit' ) {
			$code = 1004;
		} else if ( $ret === 'conflict' ) {
			$code = 1003;
		} else if ( $ret === 'denied' ) {
			$code = 1005;
		}
		if ( $code == 0 ) {
			$ACT = $this->params['do'] = DW_ACT_EDIT;
			$this->doEditPagePreProcess();
		} else {
			//S'han trobat conflictes i no s'ha pogut guardar
			//TODO[Josep] de moment tornem a la versió original, però cal

			//TODO[Xavi] aqui es on s'ha d'implementar el diff
			// Necessitem access:
			//      al draft (o contingut document que s'ha volgut guardar!)
			//      el document guardat

			//cercar una solució més operativa com ara emmagatzemar un esborrany
			//per tal que l'usuari pugui comparar i acceptar canvis
			$ACT = $this->params['do'] = DW_ACT_SHOW;
			$this->doFormatedPagePreProcess();
		}

		return $code;
	}

	private function doCancelEditPreProcess( $keep_draft = FALSE ) {
		global $ACT;

		// TODO[Xavi] Si es passa keep_draft = true no s'esborra

		if ( ! $keep_draft ) {
			$ACT = act_draftdel( $ACT );
		}

		$this->doFormatedPagePreProcess();
	}

	private function getFormatedPageResponse() {
		global $lang;
		$pageToSend = $this->getFormatedPage();

		$response = $this->getContentPage( $pageToSend );

		return $response;
	}

	private function getAdminTaskResponse() {
		if ( ! $this->dataTmp["needRefresh"] ) {
			$pageToSend = $this->getAdminTaskHtml();
			$id         = "admin_" . $this->params["task"];
			$ret        = $this->getAdminTaskPage( $id, $this->params["task"], $pageToSend );
		}
		$ret["needRefresh"] = $this->dataTmp["needRefresh"];

		return $ret;
	}

	private function getAdminTaskHtml() {
		global $INFO;
		global $conf;

		ob_start();
		trigger_event( 'TPL_ACT_RENDER', $ACT, "tpl_admin" );

		$html_output = ob_get_clean() . "\n";

		return $html_output;
	}

	public function getAdminTaskListResponse() {
		$pageToSend  = $this->getAdminTaskListHtml();
		$containerId = cfgIdConstants::ZONA_NAVEGACIO;

		return $this->getAdminTaskListPage( $containerId, $pageToSend );
	}

	private function getAdminTaskListHtml() {
		global $INFO;
		global $conf;

		ob_start();
		trigger_event( 'TPL_ACT_RENDER', $ACT );

		// build menu of admin functions from the plugins that handle them
		$pluginlist = plugin_list( 'admin' );
		$menu       = array();
		foreach ( $pluginlist as $p ) {
			if ( $obj =& plugin_load( 'admin', $p ) === NULL ) {
				continue;
			}

			// check permissions
			if ( $obj->forAdminOnly() && ! $INFO['isadmin'] ) {
				continue;
			}

			$menu[ $p ] = array(
				'plugin' => $p,
				'prompt' => $obj->getMenuText( $conf['lang'] ),
				'sort'   => $obj->getMenuSort()
			);
		}

		// Admin Tasks
		if ( count( $menu ) ) {
			usort( $menu, 'p_sort_modes' );
			// output the menu
			ptln( '<div class="clearer"></div>' );
			print p_locale_xhtml( 'adminplugins' );
			ptln( '<ul>' );
			foreach ( $menu as $item ) {
				if ( ! $item['prompt'] ) {
					continue;
				}
				ptln( '  <li><div class="li"><a href="' . DOKU_BASE . DOKU_SCRIPT . '?'
				      . 'do=admin&amp;page=' . $item['plugin'] . '">' . $item['prompt']
				      . '</a></div></li>' );
			}
			ptln( '</ul>' );
		}

		$html_output = ob_get_clean() . "\n";

		return $html_output;
	}

	private function getCodePageResponse() {
		global $INFO, $lang;

		$pageToSend = $this->cleanResponse( $this->_getCodePage() ); // TODO[Xavi] Pot ser que aqui calgui fer la comprovació del draft?

		$resp         = $this->getContentPage( $pageToSend["content"] );
		$resp["meta"] = $pageToSend["meta"];

		$infoType = "info";

		if ( $INFO['locked'] ) {
			$infoType = "warning";

			$pageToSend["info"][] = $lang['lockedby'] . ' ' . $INFO['locked'];
		}

		$resp["info"]   = $this->generateInfo( $infoType, $pageToSend["info"] );
		$resp['locked'] = $INFO['locked'];

		return $resp;
	}

	private function CleanResponse( $text ) {
		global $lang;

		$pattern = "/^(?:(?!<div class=\"editBox\").)*/s";// Captura tot el contingut abans del div que contindrá l'editor

		preg_match( $pattern, $text, $match );
		$info = $match[0];

		$text = preg_replace( $pattern, "", $text );

		// Eliminem les etiquetes no desitgades
		$pattern = "/<div id=\"size__ctl\".*?<\/div>\\s*/s";
		$text    = preg_replace( $pattern, "", $text );

		// Eliminem les etiquetes no desitgades
		$pattern = "/<div class=\"editButtons\".*?<\/div>\\s*/s";
		$text    = preg_replace( $pattern, "", $text );

		// Copiem el license
		$pattern = "/<div class=\"license\".*?<\/div>\\s*/s";
		preg_match( $pattern, $text, $match );
		$license = $match[0];

		// Eliminem la etiqueta
		$text = preg_replace( $pattern, "", $text );

		// Copiem el wiki__editbar
		$pattern = "/<div id=\"wiki__editbar\".*?<\/div>\\s*<\/div>\\s*/s";
		preg_match( $pattern, $text, $match );
		$meta = $match[0];

		// Eliminem la etiqueta
		$text = preg_replace( $pattern, "", $text );

		// Capturem el id del formulari
		$pattern = "/<form id=\"(.*?)\"/";
		//$form = "dw__editform";
		preg_match( $pattern, $text, $match );
		$form = $match[1];

		// Afegim el id del formulari als inputs
		$pattern = "/<input/";
		$replace = "<input form=\"" . $form . "\"";
		$meta    = preg_replace( $pattern, $replace, $meta );

		// Netegem el valor
		$pattern = "/value=\"string\"/";
		$replace = "value=\"\"";
		$meta    = preg_replace( $pattern, $replace, $meta );

		$response["content"] = $text;
		$response["info"]    = [ $info ];

		if ( $license ) {
			$response["info"][] = $license;
		}

		$metaId           = str_replace( ":", "_", $this->params['id'] ) . '_metaEditForm';
		$response["meta"] = [
			( $this->getCommonPage( $metaId,
			                        $lang['metaEditForm'],
			                        $meta ) + [ 'type' => 'summary' ] )
		];

		return $response;
	}

	/**
	 * Genera un element amb la informació correctament formatada i afegeix el timestamp. Si no s'especifica el id
	 * s'assignarà el id del document que s'estigui gestionant actualment.
	 *
	 * Per generar un info associat al esdeveniment global s'ha de passar el id com a buit, es a dir
	 *
	 * @param string          $type     - tipus de missatge
	 * @param string|string[] $message  - Missatge o missatges associats amb aquesta informació
	 * @param string          $id       - id del document al que pertany el missatge
	 * @param int             $duration - Si existeix indica la quantitat de segons que es mostrarà el missatge
	 *
	 * @return array - array amb la configuració del item de informació
	 */
	public function generateInfo( $type, $message, $id = NULL, $duration = - 1 ) {
		if ( $id === NULL ) {
			$id = str_replace( ":", "_", $this->params['id'] );
		}

		return array(
			"id"        => $id,
			"type"      => $type,
			"message"   => $message,
			"duration"  => $duration,
			"timestamp" => date( "d-m-Y H:i:s" )
		);
	}

	private function getSaveInfoResponse( $code ) {
		global $lang;
		global $TEXT;
		global $ID;

		$duration = - 1;

		if ( $code == 1004 ) {
			$ret         = array();
			$ret["code"] = $code;
			$ret["info"] = $lang['wordblock'];
			$ret["page"] = $this->getFormatedPageResponse();
			$type        = "error";
		} elseif ( $code == 1003 ) {
			$ret         = array();
			$ret["code"] = $code;
			$ret["info"] = $lang['conflictsSaving']; //conflict
			$ret["page"] = $this->getFormatedPageResponse();
			$type        = "error";
		} else {
			$ret = array( "code" => $code, "info" => $lang["saved"] );
			//TODO[Josep] Cal canviar els literals per referencies dinàmiques del maincfg
			//      dw__editform, date i changecheck
			$ret["formId"] = "dw__editform";
			$ret["inputs"] = array(
				"date"        => @filemtime( wikiFN( $ID ) ),
				"changecheck" => md5( $TEXT )
			);
			$type          = 'success';
			$duration      = 10;
		}

		// TODO[Xavi] PROVES, En cas d'exit el missatge només ha de durar 10s
		$ret["info"] = $this->generateInfo( $type, $ret["info"], NULL, $duration );

		return $ret;
	}

	/**
	 * TODO[Xavi] només genera la meta pel TOC
	 *
	 * @return array
	 */
	public function getMetaResponse() {
		global $lang;
		global $ACT;
		$act_aux = $ACT;
		$ret     = array( 'id' => \str_replace( ":", "_", $this->params['id'] ) );
		//$ret = array('docId' => \str_replace(":", "_", $this->params['id']));
		$meta = array();
		$mEvt = new Doku_Event( 'WIOC_ADD_META', $meta );
		if ( $mEvt->advise_before() ) {
			$ACT    = "show";
			$toc    = wrapper_tpl_toc();
			$ACT    = $act_aux;
			$metaId = \str_replace( ":", "_", $this->params['id'] ) . '_toc';
			$meta[] = ( $this->getCommonPage( $metaId, $lang['toc'], $toc ) + [ 'type' => 'TOC' ] );
		}
		$mEvt->advise_after();
		unset( $mEvt );
		$ret['meta'] = $meta;

		return $ret;
	}

	public function getJsInfo() {
		global $JSINFO;
		$this->fillInfo();

		return $JSINFO;
	}

	public function getToolbarIds( &$value ) {
		$value["varName"]    = "toolbar";
		$value["toolbarId"]  = "tool__bar";
		$value["wikiTextId"] = "wiki__text";
		$value["editBarId"]  = "wiki__editbar";
		$value["editFormId"] = "dw__editform";
		$value["summaryId"]  = "edit__summary";
	}

	private function runBeforePreprocess( &$content ) {
		global $ACT;

		$brun = FALSE;
		// give plugins an opportunity to process the action
		$this->ppEvt = new Doku_Event( 'ACTION_ACT_PREPROCESS', $ACT );
		ob_start();
		$brun    = ( $this->ppEvt->advise_before() );
		$content = ob_get_clean();

		return $brun;
	}

	private function runAfterPreprocess( &$content ) {
		ob_start();
		$this->ppEvt->advise_after();
		$content .= ob_get_clean();
		unset( $this->ppEvt );
	}

	private function fillInfo() {
		global $JSINFO;
		global $INFO;

		$INFO = pageinfo();
		//export minimal infos to JS, plugins can add more
		$JSINFO['isadmin']   = $INFO['isadmin'];
		$JSINFO['ismanager'] = $INFO['ismanager'];

		$this->infoLoaded = TRUE;

		return $JSINFO;
	}

	private function getContentPage( $pageToSend ) {
		global $REV;
		global $lang;

		$pageTitle = tpl_pagetitle( $this->params['id'], TRUE );

		// TODO[Xavi] extreure això a un altre mètode que només modifiqui el content i el info
		$pattern    = '/^.*Aquesta és una revisió.*<hr \/>\\n\\n/mis';
		$count      = 0;
		$info       = NULL;
		$pageToSend = preg_replace( $pattern, '', $pageToSend, - 1, $count );

		if ( $count > 0 ) {
			$info = $this->generateInfo( "warning", $lang['document_revision_loaded'] . ' <b>' . $this->extractDateFromRevision( $REV, self::$SHORT_FORMAT ) . '</b>' );
		}

		$contentData = array(
			'id'      => \str_replace( ":", "_", $this->params['id'] ),
			'ns'      => $this->params['id'],
			'title'   => $pageTitle,
			'content' => $pageToSend,
			'rev'     => $REV,
			'info'    => $info,
			'type'    => 'html',
			'draft'   => $this->GetContentDraft()
	);

		return $contentData;
	}

	private function getContentDraft() {
		GLOBAL $INFO;

		$draft = null;

		// Existeix el draft?
		if ($INFO['draft']) {

			// Carreguem el draft
			$draft = unserialize(io_readFile($INFO['draft'],false));
			$text  = $this->cleanDraft($draft['text']);
		}

		// Només ens interessa el text, la resta de informació no ens cal
		return $text;
	}

	private function cleanDraft($text) {
		$pattern = '/^^(wikitext\s*=\s*)|(date=[0-9]*)$/i';

		return  preg_replace($pattern, '', $text);

	}
//    private function getMetaPage($metaId, $metaTitle, $metaToSend) {
//        $contentData = array(
//            'id' => $metaId,
//            'title' => $metaTitle,
//            'content' => $metaToSend
//        );
//        return $contentData;
//    }

	private function getAdminTaskListPage( $id, $toSend ) {
		global $lang;

		return $this->getCommonPage( $id, $lang['btn_admin'], $toSend );
	}

	private function getAdminTaskPage( $id, $task, $toSend ) {
		//TO DO [JOSEP] Pasar el títol correcte segons idiaoma. Cal extreure'l de
		//plugin(admin)->getMenuText($language);
		return $this->getCommonPage( $id, $task, $toSend );
	}

	private function getCommonPage( $id, $title, $content ) {
		$contentData = array(
			'id'      => $id,
			'title'   => $title,
			'content' => $content
		);

		return $contentData;
	}

	private function getFormatedPage() {
		global $ACT;

		ob_start();
		trigger_event( 'TPL_ACT_RENDER', $ACT, 'onFormatRender' );
		$html_output = ob_get_clean() . "\n";

		return $html_output;
	}

	private function _getCodePage() {
		global $ACT;

		// TODO[Xavi] Pot ser que aqui calgui fer la comprovació del draft?

//		if($act == 'draft' && !file_exists($INFO['draft'])) return 'edit';

		global $INFO;
		$existsDraft = file_exists( $INFO['draft'] );
		$draft       = $INFO['draft'];

		ob_start();
		trigger_event( 'TPL_ACT_RENDER', $ACT, 'onCodeRender' );
		$html_output = ob_get_clean() . "\n";

		return $html_output;
	}

	/**
	 * Miguel Angel Lozano 12/12/2014
	 * - Obtenir el gestor de medis
	 */
	public function getMediaManager( $image = NULL, $fromPage = NULL, $prev = NULL ) {
		global $lang, $NS, $INPUT;
		/*if(!$NS){
			$NS = $fromPage;
		}
		$INPUT->access['ns'] = $NS;*/
		//   $NS = getNS($fromPage);
		//

		$error = $this->startMediaManager( DW_ACT_MEDIA_MANAGER, $image, $fromPage, $prev );
		if ( $error == 401 ) {
			throw new HttpErrorCodeException( $error, "Access denied" );
		} else if ( $error == 404 ) {
			throw new HttpErrorCodeException( $error, "Resource " . $image . " not found." );
		}
		$title = $lang['img_manager'];
		$ret   = array(
			"content"          => $this->doMediaManagerPreProcess(),
			"id"               => "media",
			"title"            => "media",
			"ns"               => $NS,
			"imageTitle"       => $title,
			"image"            => $image,
			"fromId"           => $fromPage,
			"modifyImageLabel" => $lang['img_manager'],
			"closeDialogLabel" => $lang['img_backto']
		);

		return $ret;
	}

	/**
	 * Init per a l'obtenció del MediaManager
	 * Nota: aquesta funció ha tingut com a base startMediaProcess, però la separem per les següents raons:
	 * - ha de considerar que és un altre $pdo
	 * - ha de consdierar que l'Id de la imatge pot ser null
	 * - en el futur volem partir la resposta de getMediaManager per ubicar cada component en l'àrea adient
	 *   de la nostra pàgina principal de la dokuwiki_30
	 */
	private function startMediaManager( $pdo, $pImage = NULL, $pFromId = NULL, $prev = NULL ) {
		global $ID;
		global $AUTH;
		//global $vector_action;
		//global $vector_context;
		//global $loginname;
		global $IMG;
		global $ERROR;
		global $SRC;
		global $conf;
		global $lang;
		global $INFO;
		global $REV;

		$ret = $ERROR = 0;

		$this->params['action'] = $pdo;

		if ( $pdo === DW_ACT_MEDIA_MANAGER ) {
			$vector_action = $GET["vecdo"] = $this->params['vector_action'] = "media";
		}

		if ( $pImage ) {
			$IMG = $this->params['image'] = $pImage;
		}
		if ( $pFromId ) {
			$ID = $this->params['id'] = $pFromId;
		}
		if ( $prev ) {
			$REV = $this->params['rev'] = $prev;
		}
		// check image permissions
		if ( $pImage ) {
			$AUTH = auth_quickaclcheck( $pImage );
			if ( $AUTH >= AUTH_READ ) {
				// check if image exists
				$SRC = mediaFN( $pImage );
				if ( ! file_exists( $SRC ) ) {
					$ret = $ERROR = 404;
				}
			} else {
				// no auth
				$ret = $ERROR = 401;
			}
		}

		if ( $ret != 0 ) {
			return $ret;
		}

		$INFO = array_merge( pageinfo(), mediainfo() );

		/**
		 * Stores the template wide context
		 *
		 * This template offers discussion pages via common articles, which should be
		 * marked as "special". DokuWiki does not know any "special" articles, therefore
		 * we have to take care about detecting if the current page is a discussion
		 * page or not.
		 *
		 * @var string
		 * @author Andreas Haerter <development@andreas-haerter.com>
		 */
		/* $vector_context = $this->params['vector_context'] = "article";
		  if (preg_match("/^".tpl_getConf("ioc_template_discuss_ns")."?$|^".tpl_getConf("ioc_template_discuss_ns").".*?$/i", ":".getNS(getID()))){
		  $vector_context = $this->params['vector_context'] = "discuss";
		  } */

		/**
		 * Stores the name the current client used to login
		 *
		 * @var string
		 * @author Andreas Haerter <development@andreas-haerter.com>
		 */
		/* $loginname = $this->params['loginName'] = "";
		  if (!empty($conf["useacl"])) {
		  if (isset($_SERVER["REMOTE_USER"]) && //no empty() but isset(): "0" may be a valid username...
		  $_SERVER["REMOTE_USER"] !== "") {
		  $loginname = $this->params['loginName'] = $_SERVER["REMOTE_USER"]; //$INFO["client"] would not work here (-> e.g. if
		  //current IP differs from the one used to login)
		  }
		  } */

		$this->startUpLang();

		//detect revision
		$rev = $this->params['rev'] = (int) $INFO["rev"]; //$INFO comes from the DokuWiki core
		if ( $rev < 1 ) {
			$rev = $this->params['rev'] = (int) $INFO["lastmod"];
		}

		$this->triggerStartEvents();

		return $ret;
	}

	private function doMediaManagerPreProcess() {
		global $ACT;
		global $JUMPTO;

		$content = "";
		if ( $this->runBeforePreprocess( $content ) ) {
			ob_start();
			// tpl_media(); //crida antiga total del media manager
			//crida parcial: només a la llista de fitxers del directori
			$this->mediaManagerFileList();
			$content .= ob_get_clean();
			// check permissions again - the action may have changed
			$ACT = act_permcheck( $ACT );
		}
		$this->runAfterPreprocess( $content );

		return $content;
	}

	/**
	 * Prints full-screen media manager
	 *
	 * @author Kate Arzamastseva <pshns@ukr.net>
	 */

	function mediaManagerFileList() {
		global $NS, $IMG, $JUMPTO, $REV, $lang, $fullscreen, $INPUT, $AUTH;
		$fullscreen = TRUE;
		require_once DOKU_INC . 'lib/exe/mediamanager.php';

		$rev   = '';
		$image = cleanID( $INPUT->str( 'image' ) );
		if ( isset( $IMG ) ) {
			$image = $IMG;
		}
		if ( isset( $JUMPTO ) ) {
			$image = $JUMPTO;
		}
		if ( isset( $REV ) && ! $JUMPTO ) {
			$rev = $REV;
		}

		echo '<div id="mediamanager__page">' . NL;
		if ( $NS == "" ) {
			echo '<h1>Documents de l\'arrel de documents</h3>';
		} else {
			echo '<h1>Documents de ' . $NS . '</h3>';
		}

		echo '<div class="panel filelist ui-resizable">' . NL;
		echo '<div class="panelContent">' . NL;
		$do    = $AUTH;
		$query = $_REQUEST['q'];
		if ( ! $query ) {
			$query = '';
		}
		if ( $do == 'searchlist' || $query ) {
			media_searchlist( $query, $NS, $AUTH, TRUE, $_REQUEST['sort'] );
		} else {
			media_tab_files( $NS, $AUTH, $JUMPTO );
		}
		echo '</div>' . NL;
		echo '</div>' . NL;
		echo '</div>' . NL;
	}

	public function getMediaMetaResponse() {
		global $NS, $IMG, $JUMPTO, $REV, $lang, $fullscreen, $INPUT;
		$fullscreen = TRUE;
		require_once DOKU_INC . 'lib/exe/mediamanager.php';

		$rev   = '';
		$image = cleanID( $INPUT->str( 'image' ) );
		if ( isset( $IMG ) ) {
			$image = $IMG;
		}
		if ( isset( $JUMPTO ) ) {
			$image = $JUMPTO;
		}
		if ( isset( $REV ) && ! $JUMPTO ) {
			$rev = $REV;
		}
		ob_start();

		echo '<div id="mediamanager__meta">' . NL;
		echo '<h1>' . $lang['btn_media'] . '</h1>' . NL;
		html_msgarea();

		echo '<div class="panel namespaces">' . NL;
		echo '<h2>' . $lang['namespaces'] . '</h2>' . NL;
		echo '<div class="panelHeader">';
		echo $lang['media_namespaces'];
		echo '</div>' . NL;

		echo '<div class="panelContent" id="media__tree">' . NL;
		media_nstree( $NS );
		echo '</div>' . NL;
		echo '</div>' . NL;
		echo '</div>' . NL;

		echo '</div>' . NL;
		$meta = ob_get_clean();
		$ret  = array( 'id' => $NS );
		// $mEvt = new Doku_Event('WIOC_ADD_META', $meta);
		/* if ($mEvt->advise_before()) {
				$ACT = "show";
				$toc = wrapper_tpl_toc();
				$ACT = $act_aux;
				$metaId = \str_replace(":", "_", $this->params['id']) . '_toc';
				$meta[] = $this->getMetaPage($metaId, $lang['toc'], $toc);
			}*/
		//$mEvt->advise_after();
		//unset($mEvt);
		$ret['meta'] = $meta;

		return $ret;
	}

	public function getMediaTabFileOptions() {
		global $INPUT;

		$checkThumbs = "checked";
		$checkRows   = "";
		if ( $INPUT->str( 'list' ) ) {
			if ( $INPUT->str( 'list' ) == "rows" ) {
				$checkThumbs = "";
				$checkRows   = "checked";
			}
		}
		ob_start();
		echo '<span style="font-weight: bold;">Visualització</span></br>';
		echo '<div style="margin-left:10px;">';
		echo '  <input type="radio" data-dojo-type="dijit/form/RadioButton" name="fileoptions" id="thumbs" value="thumbs" ' . $checkThumbs . '/>
                <label for="radioOne">Thumbnails</label> <br />';
		echo '  <input type="radio" data-dojo-type="dijit/form/RadioButton" name="fileoptions" id="rows" value="rows" ' . $checkRows . '/>
                <label for="radioTwo">Rows</label> <br/><br/></div>';
		$strData = ob_get_clean();
		/*$tree_ret = array(
			'id'      => 'metaMediafileoptions',
			'title'   => "Visualització",
			'content' => $strData
		);*/

		//return $tree_ret;
		return $strData;
	}

	public function getMediaTabFileSort() {
		global $INPUT;
		$checkedNom  = "checked";
		$checkedData = "";
		if ( $INPUT->str( 'sort' ) ) {
			if ( $INPUT->str( 'sort' ) == "date" ) {
				$checkedNom  = "";
				$checkedData = "checked";
			}
		}

		ob_start();
		/*echo '  <input type="radio" name="drink" id="radioOne" checked value="tea"/>
				<label for="radioOne">Tea</label> <br />';*/
		echo '<span style="font-weight: bold;">Ordenació</span></br>';
		echo '<div style="margin-left:10px;">';
		echo '  <input type="radio" data-dojo-type="dijit/form/RadioButton" name="filesort" id="nom" value="name" ' . $checkedNom . '/>
                <label for="nom">Nom</label> <br />';
		echo '  <input type="radio" data-dojo-type="dijit/form/RadioButton" name="filesort" id="data" value="date" ' . $checkedData . '/>
                <label for="data">Data</label> <br/><br/></div>';
		//echo '<div class="panelContent dokuwiki" id="metamedia__fileoptions">' . NL;
		//media_tab_files_options();
		//echo '</div>' . NL;
		$strData = ob_get_clean();
		/*$tree_ret = array(
			'id'      => 'metaMediafilesort',
			'title'   => "Ordenació",
			'content' => $strData
		);*/

		//return $tree_ret;
		return $strData;
	}

	public function getMediaTabSearch() {
		global $NS;
		ob_start();
		echo '<span style="font-weight: bold;">Cerca</span></br>';
		echo '<div class="search" style="margin-left:10px;">';
		echo '<form accept-charset="utf-8" method="post"  id="dw__mediasearch">';
		echo '<div class="no">';
		//echo '<p><label><span>Cerca pel nom de fitxer: </span>';
		echo '<p>';
		echo '<input type="text" id="mediaSearchq" placeholder = "Nom de fitxer" title="Cerca en: ' . $NS . '" class="edit" name="q">';
		echo '</label>';
		echo '<input type="submit" class="button" value="Filtrar" id="mediaSearchs">';
		echo '<input style="display:none" type="submit" class="button" value="Desfer filtre" id="mediaSearchr">';
		echo '</p>';
		echo '</div></form></div>';

		$strData = ob_get_clean();
		/*$tree_ret = array(
			'id'      => 'metaMediaSearch',
			'title'   => "Cerca",
			'content' => $strData
		);*/

		//return $tree_ret;
		return $strData;
	}

	public function getMediaFileUpload() {
		global $NS, $AUTH, $JUMPTO;
		ob_start();
		media_tab_upload( $NS, $AUTH, $JUMPTO );
		$strData  = ob_get_clean();
		$tree_ret = array(
			'id'      => 'metaMediafileupload',
			'title'   => "Càrrega de fitxers",
			'content' => $strData
		);

		return $tree_ret;
	}

	function MediaUpload() {
		global $NS, $MSG, $INPUT;

		if ( $_FILES['qqfile']['tmp_name'] ) {
			$id = $INPUT->post->str( 'mediaid', $_FILES['qqfile']['name'] );
		} elseif ( $INPUT->get->has( 'qqfile' ) ) {
			$id = $INPUT->get->str( 'qqfile' );
		}

		$id = cleanID( $id );

		$NS = $INPUT->str( 'ns' );
		$ns = $NS . ':' . getNS( $id );

		$AUTH = auth_quickaclcheck( "$ns:*" );
		if ( $AUTH >= AUTH_UPLOAD ) {
			io_createNamespace( "$ns:xxx", 'media' );
		}

		if ( $_FILES['qqfile']['error'] ) {
			unset( $_FILES['qqfile'] );
		}

		// if ($_FILES['qqfile']['tmp_name']) $res = media_upload($NS, $AUTH, $_FILES['qqfile']);
		// if ($INPUT->get->has('qqfile')) $res = media_upload_xhr($NS, $AUTH);
		media_upload( $NS, $AUTH );
		if ( $res ) {
			$result = array(
				'success' => TRUE,
				'link'    => media_managerURL( array( 'ns' => $ns, 'image' => $NS . ':' . $id ), '&' ),
				'id'      => $NS . ':' . $id,
				'ns'      => $NS
			);
		}

		if ( ! $result ) {
			$error = '';
			if ( isset( $MSG ) ) {
				foreach ( $MSG as $msg ) {
					$error .= $msg['msg'];
				}
			}
			$result = array( 'error' => $msg['msg'], 'ns' => $NS );
			//$_FILES = array();
			//unset($_FILES['upload']);
			//$_FILES['upload']['error']="No s'ha pogut pujar el fitxer";
		}
		//$json = new JSON;
		//echo htmlspecialchars($json->encode($result), ENT_NOQUOTES);
	}

	public function getNsMediaTree( $currentnode, $sortBy, $onlyDirs = FALSE ) {
		global $conf;
		$base = $conf['mediadir'];

		return $this->getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs );
	}

	/**
	 * FI Miguel Angel Lozano 12/12/2014
	 */

	public function getLoginName() {
		global $_SERVER;

		$loginname = "";
		if ( ! empty( $conf["useacl"] ) ) {
			if ( isset( $_SERVER["REMOTE_USER"] ) && //no empty() but isset(): "0" may be a valid username...
			     $_SERVER["REMOTE_USER"] !== ""
			) {
				$loginname = $_SERVER["REMOTE_USER"]; //$INFO["client"] would not work here (-> e.g. if
				//current IP differs from the one used to login)
			}
		}

		return $loginname;
	}

	public function getRevisions( $id ) {
		global $ID;
		global $ACT;

		// START
		// Només definim les variables que es passen per paràmetre, la resta les ignorem

		$ACT = 'revisions';

		$this->triggerStartEvents();
//		$tmp = [ ];
//		trigger_event( 'DOKUWIKI_START', $tmp );
		session_write_close();

//		$evt = new Doku_Event( 'ACTION_ACT_PREPROCESS', $ACT );
//		if ( $evt->advise_before() ) {
		$content = "";
		if ( $this->runBeforePreprocess( $content ) ) {
			act_permcheck( $ACT );
			unlock( $ID );
		}
//		$evt->advise_after();
//		unset( $evt );
		$this->runAfterPreprocess( $content );

		//desactivem aquesta crida perquè es tracta d'una crida AJAX i no es pot modificar la capçalera
//		$headers[] = 'Content-Type:application/json; charset=utf-8';
//
//		trigger_event( 'ACTION_HEADERS_SEND', $headers, 'act_sendheaders' );

		$this->startUpLang();

		//descativem aquesta crida perquè les revisions no es retornen
		//rederitzades sinó que es rendaritzen al client
		//trigger_event( 'TPL_ACT_RENDER', $ACT, "tpl_content_core");
		// En aquest punt es on es generaria el codi HTML

		//descativem aquesta crida perquè des del dokumodeladapter el 
		//display ja està fet i no servidria de res tornar a llançar
		//aquest esdeveniment.
//		$temp = [ ];
//		trigger_event( 'TPL_CONTENT_DISPLAY', $temp );

		// DO real

		$revisions = getRevisions( $ID, - 1, 50 );

		$ret = [ ];

		foreach ( $revisions as $revision ) {
			$ret[ $revision ]         = getRevisionInfo( $ID, $revision );
			$ret[ $revision ]['date'] = $this->extractDateFromRevision( $ret[ $revision ]['date'], self::$DEFAULT_FORMAT );
			//unset ($ret[$revision]['id']);
		}

		$this->triggerEndEvents();
//		$temp = [ ];
//		trigger_event( 'DOKUWIKI_DONE', $temp );

		return $ret;
	}

	/**
	 * Extreu la data a partir del nombre de revisió
	 *
	 * @param int $revision - nombre de la revisió
	 * @param int $mode     - format de la data
	 *
	 * @return string - Data formatada
	 *
	 */
	public function extractDateFromRevision( $revision, $mode ) {

		switch ( $mode ) {

			case self::$SHORT_FORMAT:
				$format = "d-m-Y";
				break;

			case self::$DEFAULT_FORMAT:

			default:
				$format = "d-m-Y H:i:s";

		}

		return date( $format, $revision );
	}

	public function getDiffPage( $id, $rev1, $rev2 = NULL ) {
		// START
		// Només definim les variables que es passen per paràmetre, la resta les ignorem

		global $ID;
		global $ACT;
		global $REV;
		global $lang;
		global $INPUT;

		$ID  = $id;
		$REV = $rev1;
		$ACT = 'diff';

		$this->triggerStartEvents();
//		$tmp = [ ];
//		trigger_event( 'DOKUWIKI_START', $tmp );
		session_write_close();

//		$evt = new Doku_Event( 'ACTION_ACT_PREPROCESS', $ACT );
//		if ( $evt->advise_before() ) {
//			act_permcheck( $ACT );
//			unlock( $ID );
//		}
//		$evt->advise_after();
//		unset( $evt );
		$content = "";
		if ( $this->runBeforePreprocess( $content ) ) {
			act_permcheck( $ACT );
			unlock( $ID );
		}

		//desactivem aquesta crida perquè es tracta d'una crida AJAX i no es pot modificar la capçalera
//		$headers[] = 'Content-Type:application/json; charset=utf-8';
//
//		trigger_event( 'ACTION_HEADERS_SEND', $headers, 'act_sendheaders' );		

		$this->startUpLang();

		//descativem aquesta crida perquè les revisions no es retornen 
		//rederitzades sinó que es rendaritzen al client
		//trigger_event( 'TPL_ACT_RENDER', $ACT, "tpl_content_core");

		// En aquest punt es on es generaria el codi HTML

		//descativem aquesta crida perquè des del dokumodeladapter el 
		//display ja està fet i no servidria de res tornar a llançar
		//aquest esdeveniment.
//		$temp = [ ];
//		trigger_event( 'TPL_CONTENT_DISPLAY', $temp );

		// DO real

		//side_by_side

		if ( $INPUT->ref( 'difftype' ) ) {
			$difftype = $INPUT->ref( 'difftype' );
		} else {
			$difftype = 'sidebyside';
		}

		if ( $difftype == 'sidebyside' ) {
			ob_start();
			html_diff( '', TRUE, $type = 'sidebyside' );
			$content = ob_get_clean();
		} else {
			ob_start();
			html_diff( '', TRUE, $type = 'inline' );
			$content = ob_get_clean();
		}

		$response = [
			'id'      => \str_replace( ":", "_", $ID ),
			'ns'      => $ID,
			"title"   => $ID,
			"content" => $this->clearDiff( $content ),
			"type"    => 'diff'
		];

		$response['info'] = $this->generateInfo( "info", $lang['diff_loaded'] );

		$meta = [
			( $this->getCommonPage( $response['id'] . '_switch_diff_mode ',
			                        $lang['switch_diff_mode'],
			                        $this->extractMetaContentFromDiff( $content )
				) + [ 'type' => 'switch_diff_mode' ] )
		];

		$response["meta"] = [ 'id' => $response['id'], 'meta' => $meta ];

		$this->triggerEndEvents();
//		$temp = [ ];
//		trigger_event( 'DOKUWIKI_DONE', $temp );

		return $response;
	}

	/**
	 * Extreu la informació que volem fer servir com a meta pels diff, en aquest cas només ens interessa el formulari.
	 *
	 * S'afegeix una id única pel formulari per poder seleccionar-lo al frontend.
	 *
	 * @param string $content - contingut del que volem extreure el formulari
	 *
	 * @return string - cadena amb el codi html per reconstruir el formulari.
	 */
	public function extractMetaContentFromDiff( $content ) {
		global $ID;

		$pattern = '/<form.*<\/form>/s';
		preg_match( $pattern, $content, $matches );

		$pattern = '/<form /s';
		$replace = '<form id="switch_mode_' . str_replace( ":", "_", $ID ) . '" ';

		$metaContent = preg_replace( $pattern, $replace, $matches[0] );

		return $metaContent;
	}

	public function clearDiff( $content ) {
		$pattern = '/^.+?(?=<div class="table">)/s';

		return preg_replace( $pattern, '', $content );
	}

	/**
	 * Afegeix al paràmetre $value els selectors css que es
	 * fan servir per seleccionar els forms al html del pluguin ACL
	 *
	 * @param array $value - array de paràmetres
	 *
	 */
	public function getAclSelectors( &$value ) {
		$value["saveSelector"]   = "#acl__detail form:submit";
		$value["updateSelector"] = "#acl_manager .level2 form:submit";
	}

	/**
	 * Afegeix al paràmetre $value els selectors css que es
	 * fan servir per seleccionar els forms al html del pluguin PLUGIN
	 *
	 * @param array $value - array de paràmetres
	 *
	 */
	public function getPluginSelectors( &$value ) {
		$value["commonSelector"]  = "div.common form:submit";
		$value["pluginsSelector"] = "form.plugins:submit";
	}

	/**
	 * Afegeix al paràmetre $value els selectors css que es
	 * fan servir per seleccionar els forms al html del pluguin CONFIG
	 *
	 * @param array $value - array de paràmetres
	 *
	 */
	public function getConfigSelectors( &$value ) {
		$value["configSelector"] = "#config__manager form:submit";
	}

	/**
	 * Afegeix al paràmetre $value els selectors css que es
	 * fan servir per seleccionar els forms al html del pluguin USERMANAGER
	 *
	 * @param array $value - array de paràmetres
	 *
	 */
	public function getUserManagerSelectors( &$value ) {
		$value["formsSelector"] = "#user__manager form:submit";
		$value["exportCsvName"] = "fn[export]";
	}

	/**
	 * Afegeix al paràmetre $value els selectors css que es
	 * fan servir per seleccionar els forms al html del pluguin REVERT
	 *
	 * @param array $value - array de paràmetres
	 *
	 */
	public function getRevertSelectors( &$value ) {
		$value["revertSelector"] = "#admin_revert form:submit";
	}

	/**
	 * Afegeix al paràmetre $value els selectors css que es
	 * fan servir per seleccionar els forms al html del pluguin LATEX
	 *
	 * @param array $value - array de paràmetres
	 *
	 */
	public function getLatexSelectors( &$value ) {
		$value["latexSelector"] = "div.level2 form:submit"; //form
		$value["latexpurge"]    = "latexpurge"; // input name purge
		$value["dotest"]        = "dotest"; // input name test
	}

	/**
	 * Miguel Angel Lozano 21/04/2015
	 * MEDIA DETAILS: Obtenció dels detalls de un media
	 */
	public function getMediaDetails( $image ) {
		global $lang, $NS, $JSINFO;

		$error = $this->startMediaDetails( DW_ACT_MEDIA_DETAILS, $image );
		if ( $error == 401 ) {
			throw new HttpErrorCodeException( $error, "Access denied" );
		} else if ( $error == 404 ) {
			throw new HttpErrorCodeException( $error, "Resource " . $image . " not found." );
		}
		$title  = $lang['img_manager'];
		$ret    = array(
			"content"    => $this->doMediaDetailsPreProcess(),
			"id"         => $image,
			"title"      => $image,
			"ns"         => $NS,
			"imageTitle" => $image,
			"image"      => $image
		);
		$JSINFO = array( 'id' => $image, 'namespace' => $NS );

		return $ret;
	}

	/**
	 * Init per a l'obtenció del Media Details
	 * Nota: aquesta funció ha tingut com a base startMediaProcess, però la separem per les següents raons:
	 */
	private function startMediaDetails( $pdo, $pImage ) {
		global $ID;
		global $AUTH;
		global $IMG;
		global $ERROR;
		global $SRC;
		global $conf;
		global $lang;
		global $INFO;
		global $REV;
		$ret                    = $ERROR = 0;
		$this->params['action'] = $pdo;

		if ( $pImage ) {
			$IMG = $this->params['image'] = $pImage;
		}
		$ID = $pImage;

		// check image permissions
		if ( $pImage ) {
			$AUTH = auth_quickaclcheck( $pImage );
			if ( $AUTH >= AUTH_READ ) {
				// check if image exists
				$SRC = mediaFN( $pImage );
				if ( ! file_exists( $SRC ) ) {
					$ret = $ERROR = 404;
				}
			} else {
				// no auth
				$ret = $ERROR = 401;
			}
		}

		if ( $ret != 0 ) {
			return $ret;
		}

		$INFO = array_merge( pageinfo(), mediainfo() );

		$this->startUpLang();

		//detect revision
		$rev = $this->params['rev'] = (int) $INFO["rev"]; //$INFO comes from the DokuWiki core
		if ( $rev < 1 ) {
			$rev = $this->params['rev'] = (int) $INFO["lastmod"];
		}

		$this->triggerStartEvents();

		return $ret;
	}

	private function doMediaDetailsPreProcess() {
		global $ACT;
		global $JUMPTO;

		$content = "";
		if ( $this->runBeforePreprocess( $content ) ) {
			ob_start();
			$this->mediaDetailsContent();
			$content .= ob_get_clean();
			// check permissions again - the action may have changed
			$ACT = act_permcheck( $ACT );
		}
		$this->runAfterPreprocess( $content );

		return $content;
	}

	/**
	 * Prints full-screen media details
	 */

	function mediaDetailsContent() {

		global $NS, $IMG, $JUMPTO, $REV, $lang, $conf, $fullscreen, $INPUT, $AUTH;
		$fullscreen = TRUE;
		require_once DOKU_INC . 'lib/exe/mediamanager.php';

		$rev   = '';
		$image = cleanID( $INPUT->str( 'image' ) );
		if ( isset( $IMG ) ) {
			$image = $IMG;
		}
		if ( isset( $JUMPTO ) ) {
			$image = $JUMPTO;
		}
		if ( isset( $REV ) && ! $JUMPTO ) {
			$rev = $REV;
		}

		echo '<div class="panelContent">' . NL;
		$meta = new JpegMeta( mediaFN( $image, $rev ) );
		media_preview( $image, $AUTH, $rev, $meta );
		media_details( $image, $auth, $rev, $meta );
		echo '</div>' . NL;
	}

}
