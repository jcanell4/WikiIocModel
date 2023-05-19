<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT y
 * que el usuario sea el Responsable o del grupo "admin" o "projectmanager"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

//[JOSEP] ALERTA! Aquesta classe, crec que no cal. El grup admin és del grup d'adminitradors i ja té permis. No cal donar-li aquí!
//Si s'ha posat perquè no funcionava, cal cirregir-lo en origen, no apadaçar-lo com ara.
class EditProjectAuthorization extends SupervisorProjectAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedGroups = ["admin"];
        //[RAFA] Aquesta assignació elimina els altres grups (desconec el motiu)
        // específicament el grup "manager" assignat per la classe SupervisorProjectAuthorization
    }

}
