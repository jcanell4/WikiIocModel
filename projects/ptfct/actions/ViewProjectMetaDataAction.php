<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
include_once WIKI_IOC_MODEL . "actions/BasicViewUpdatableProjectMetaDataAction.php";

class ViewProjectMetaDataAction extends BasicViewUpdatableProjectMetaDataAction{

    protected function runAction() {

        if (!$this->getModel()->isProjectGenerated()) {
            $this->getModel()->setViewConfigName("firstView");
        }
        $response = parent::runAction();

        $response[ProjectKeys::KEY_ACTIVA_FTPSEND_BTN] = $this->getModel()->haveFilesToExportList();

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
