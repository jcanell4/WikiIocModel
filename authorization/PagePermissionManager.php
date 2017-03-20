<?php
/**
 * PagePermissionManager: Gestió de permisos de pàgina
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once(WIKI_IOC_MODEL . 'WikiIocInfoManager.php');

class PagePermissionManager {

    /**
     * Obtiene la lista de usuarios, grupos o todos, que tienen, como mínimo, permiso $permis sobre la página $id
     * @param $type: 'users', 'groups', 'all'
     */
    public function getListUsersPagePermission($id, $permis=AUTH_CREATE, $type='users') {
        $acl_class = new admin_plugin_acl();
        $acl_class->handle();
        $acl = self::get_acl();

        $ret = array();
        $camins = explode(":", $id);
        for ($c = count($camins)-1; $c >= 0; $c--) {
            $camí = implode(":", $camins);
            foreach ($acl[$camí] as $k => $v) {
                switch ($type) {
                    case 'users': $inc = (substr($k,0,1)!=="@"); break;
                    case 'groups': $inc = (substr($k,0,1)==="@"); break;
                    default: $inc = true; break;
                }
                if ($inc && $v >= $permis) $ret[] = $k;
            }
            if ($c > 0) {
                if ($camins[$c] === "*") {
                    array_pop($camins);     //elimina último elemento del array
                    $camins[$c-1] = "*";    //sube un nivel de directorio
                }else {
                    $camins[$c++] = "*";    //sube un nivel de directorio
                }
            }
        }
        $ret = array_unique($ret);
        asort($ret);
        return array_values($ret);
    }

    /**
     * Obtiene un array de usuarios cuyo username o nombre contiene el texto $filter
     * @param $cadena es la parte del nombre que se busca
     * @return array[username=>[array['username'=>username, 'firstname'=>firstname, 'lastname'=>lastname]]
     */
    public function getUserList($filter, $start=0, $pagesize=100) {
        global $auth;
        $au = & $auth;
        $user_list = $au->retrieveUsers($start, $pagesize+1, array('username_name'=>$filter));
        foreach ($user_list as $k => $v) {
            $ul[] = array('username' => $k, 'name' => $v['name']);
        }
        if (count($user_list) > $pagesize) {
            $ret['info'] = ['hasmore' => TRUE];
            array_pop($ul);
        }
        $ret['values'] = $ul;
        return $ret;
    }

    /**
     * @param $page y $user son obligatorios
     */
    public static function getPermissionPageForUser( $page, $user=NULL ) {
        global $USERINFO;
        if (!$user) $user = $_SERVER['REMOTE_USER'];
        $permis = auth_aclcheck($page, $user, $USERINFO['grps']);
        return $permis;
    }

    /**
     * @param bool $force : true indica que s'ha d'establir estrictament el permís
     */
    public static function setPermissionPageForUser( $page, $user, $permis, $force=FALSE ) {
        global $USERINFO;
        $acl_class = new admin_plugin_acl();
        $acl_class->handle();
        $permis_actual  = auth_aclcheck($page, $user, $USERINFO['grps']);
        if ( $force || $permis > $permis_actual ) {
            $ret = $acl_class->_acl_add( $page, $user, $permis );
            if ( $ret ) {
                if ( strpos( $page, '*' ) === FALSE ) {
                    if ( $permis > AUTH_EDIT ) {
                        $permis_actual = AUTH_EDIT;
                    }
                } else {
                    $permis_actual = $permis;
                }
            }
        }
        return $permis_actual;
    }

    public static function deletePermissionPageForUser( $page, $user ) {
        $acl_class = new admin_plugin_acl();
        if ( $page && $user ) {
            $ret = $acl_class->_acl_del( $page, $user );
        }
        return $ret;
    }

    public static function updatePermission($permission) {
        if ($permission->getIsMyOwnNs()) {  //si es tracta de la pròpia pàgina de l'usuari ...
            $acl_class = new admin_plugin_acl();
            $acl_class->handle();
            $user = WikiIocInfoManager::getInfo('client');
            $page = $permission->getIdPage();
            $ns = substr($page, 0, strrpos($page, ":")) . ':*';
            if ( !$acl_class->acl[$ns][$user] ) {
                // La pàgina de l'usuari no existeix en el fitxer de permissos
                $ret = self::setPermissionPageForUser($ns, $user, AUTH_DELETE, TRUE);
                WikiIocInfoManager::setInfo('perm', $ret);
            }
        }
        return $ret;
    }

    /**
     * Get current ACL settings as multidim array
     * @author Andreas Gohr <andi@splitbrain.org>
     * @culpable Rafael Claver
     */
    private static function get_acl(){
        global $AUTH_ACL;
        $acl_config = array();

        foreach($AUTH_ACL as $line){
            $line = trim(preg_replace('/#.*$/','',$line));  //ignore comments
            if ($line) {
                $acl = preg_split('/[ \t]+/',$line);        //0 is pagename, 1 is user, 2 is acl
                $acl[1] = rawurldecode($acl[1]);
                $acl_config[$acl[0]][$acl[1]] = $acl[2];
            }
        }
        ksort($acl_config);
        return $acl_config;
    }

}
