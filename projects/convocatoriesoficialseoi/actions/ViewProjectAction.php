<?php
if (!defined('DOKU_INC')) die();

class ViewProjectAction extends BasicViewUpdatableProjectAction{

    protected function runAction() {

        $model = $this->getModel();        

        $response = parent::runAction();
        
        $exportOk = $this->getModel()->validateProjectDates()? 1 : 0;
        $response[AjaxKeys::KEY_EXTRA_STATE] = [AjaxKeys::KEY_EXTRA_STATE_ID => "exportOk", AjaxKeys::KEY_EXTRA_STATE_VALUE => $exportOk];

        return $response;
    }

    /**
     * Retorna una data UNIX a partir de:
     * @param string $diames en format "01/06"
     * @param string $anyActual
     * @return object DateTime
     */
    private function _obtenirData($diames, $anyActual) {
        $mesdia = explode("/", $diames);
        return date_create($anyActual."/".$mesdia[1]."/".$mesdia[0]);
    }

}
