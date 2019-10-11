<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT y que el usuario sea el Responsable o del grupo "admin" o "projectmanager"
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "authorization/ProjectCommandAuthorization.php");

class ResponsableProjectAuthorization extends ProjectCommandAuthorization {

    public function canRun() {
        if (parent::canRun()) {
            if ( !($this->isUserGroup(array("projectmanager","admin")) || $this->isResponsable()) ) {
                $this->errorAuth['error'] = TRUE;
                $this->errorAuth['exception'] = 'InsufficientPermissionToEditProjectException';
                $this->errorAuth['extra_param'] = $this->permission->getIdPage();
            }
        }
        return !$this->errorAuth['error'];
    }
}
