<?php
if (!defined('DOKU_INC')) die();

class GetProjectAction extends BasicGetProjectAction{

    public function runAction()
    {
        if ($this->params[ProjectKeys::KEY_METADATA_SUBSET] === "management") {
            $this->getModel()->setViewConfigName("#blank#");
        }

        $ret = parent::runAction();

        return $ret;
    }

}
