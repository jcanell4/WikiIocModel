<?php



abstract class ValidateWithPermission
{
    protected $permission;

    function init($permission) {
        $this->permission = $permission;
    }
    abstract function validate($data);
}