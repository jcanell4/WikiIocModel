<?php
/**
 * Permission: la clase gestiona los permisos de usuario en este proyecto
 * @author Rafael Claver
 */

namespace prgfploe;

if (!defined('DOKU_INC') ) die();

class Permission extends \ProjectPermission {
     const ROL_VALIDADOR       = "validador";
     const ROL_REVISOR       = "revisor";
}
