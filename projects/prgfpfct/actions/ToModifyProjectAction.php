<?php
/**
 * ToModifyProjectAction: Inici del procés de modificació del projecte
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ToModifyProjectAction extends GetProjectAction {
    
    public function responseProcess() {
        $model = $this->getModel();
        // Obtenir les dades del projecte per omplir l'històric del control de canvis
        $projectMetaData = $model->getCurrentDataProject(FALSE, FALSE);
        // Inici de la Modificació: eliminar signatures i dates de totes les persones
        $model->clearQualityRolesData($projectMetaData);        
        $model->addHistoricGestioDocument($projectMetaData);
        $model->setDataProject($projectMetaData, "Incialització de les dades de qualitat per demanda de modificació");
        $response = parent::responseProcess();
        return $response;
    }

}
