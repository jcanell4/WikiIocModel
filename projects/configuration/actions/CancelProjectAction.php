<?php
if (!defined('DOKU_INC')) die();

class CancelProjectAction extends ViewProjectAction{

    protected $view;

    protected function preResponseProcess() {
        parent::preResponseProcess();

        $userInfo = WikiIocInfoManager::getInfo("userinfo");
        $user_groups = $userInfo['grps'];

        $model = $this->getModel();
        $action = $model->getMetaDataActionViews();

        $view = $action["default"];
            if (($views_group = $action['groups'])) {
                foreach ($views_group as $g => $vista) {
                    if (in_array($g, $user_groups)) {
                        $view = $vista;
                        break;
                    }
                }
            }

        $file = $model->getProjectMetaDataQuery()->getProjectTypeDir()."metadata/config/{$view}.json";
        if ($view && is_file($file)) {
            $this->view = $view;
        }
    }

    protected function runAction() {

        if($this->getModel()->getViewConfigKey()===ProjectKeys::KEY_VIEW_DEFAULTVIEW && $this->view){
            $this->getModel()->setViewConfigKey($this->view);
        }

        return parent::runAction();
    }

}
