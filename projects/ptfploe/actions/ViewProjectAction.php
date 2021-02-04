<?php
if (!defined('DOKU_INC')) die();

class ViewProjectAction extends BasicViewUpdatableProjectAction{

    protected function runAction() {

        if (!$this->getModel()->isProjectGenerated()) {
            $this->getModel()->setViewConfigName("firstView");
        }
        $response = parent::runAction();

        return $response;
    }

    public function responseProcess() {

        $response = parent::responseProcess();
        $response[AjaxKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();

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
