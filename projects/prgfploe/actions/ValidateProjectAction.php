<?php
/**
 * ValidateProjectAction: El Validador marca el projecte com a validat apte per modificar
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ValidateProjectAction extends ViewProjectAction {

    public function responseProcess() {
        $model = $this->getModel();
        // Obtenir les dades del projecte per omplir l'històric del control de canvis
        $projectMetaData = $model->getCurrentDataProject(FALSE, FALSE);
        // El Validador marca el projecte com a validat: canvi data i signatura Validador
        $model->updateSignature($projectMetaData, "cc_dadesValidador", $this->params['data_validacio']);
        $model->modifyLastHistoricGestioDocument($projectMetaData, $this->params['data_validacio']);
        $model->setDataProject($projectMetaData, "Projecte marcat com a validat");
        $response = parent::responseProcess();
        $notifyAction = $this->getActionInstance("NotifyAction", null, FALSE);
        $notifyParams=[
            "do" => NotifyAction::DO_ADDMESS,
            "to" => "{$projectMetaData["responsable"]},{$projectMetaData["autor"]}",
            "message" => "La programació {$this->params['id']} ha estat validada.",
            "id" => $this->params["id"],
            "type" => NotifyAction::DEFAULT_MESSAGE_TYPE,
            "data-call" => "project&do=workflow&action=view",
            "send_mail" => true,
        ];
        $responseNotify = $notifyAction->get($notifyParams);
        $this->addInfoToInfo($response["info"], $responseNotify["info"]);
        $response["notifications"] = $responseNotify["notifications"];
        return $response;
    }

}
