<?php

if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_PLUGIN . "wikiiocmodel/utility/ValidateWithPermission.php");

class ValidationByRoles extends ValidateWithPermission
{

    function validate($data)
    {
        $groups = $this->permission->getUserGroups();

        foreach ( $data as $group) {
            if (in_array($group, $groups)) {
                return false;
            }
        }

        return true;
    }
}