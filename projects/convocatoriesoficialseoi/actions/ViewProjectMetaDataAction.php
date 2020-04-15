<?php
if (!defined('DOKU_INC')) die();

class ViewProjectMetaDataAction extends BasicViewUpdatableProjectMetaDataAction{

    protected function runAction() {

        $model = $this->getModel();
        if (!$model->isProjectGenerated()) {
            $model->setViewConfigName("firstView");
        }

        $response = parent::runAction();

        // Si els documents no coincideixen amb les plantilles es mostra el botó update
        $plantilla = $response['projectMetaData']['plantilla']['value'];
        $response[ProjectKeys::KEY_ACTIVA_UPDATE_BTN] = !$model->validateTemplates($plantilla) ? 1 : 0;

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
