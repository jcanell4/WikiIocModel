<?php
if (!defined('DOKU_INC')) die();

class SetProjectAction extends BasicSetProjectAction
{
    protected function responseProcess()
    {
        $response = parent::responseProcess();
        $model = $this->getModel();

        $response[ProjectKeys::KEY_GENERATED] = true;
        $exportOk = $model->validateProjectDates() ? 1 : 0;
        $response[AjaxKeys::KEY_EXTRA_STATE] = [AjaxKeys::KEY_EXTRA_STATE_ID => "exportOk",
                                                AjaxKeys::KEY_EXTRA_STATE_VALUE => $exportOk];

        return $response;
    }

}
