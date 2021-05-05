<?php
/**
 * ToValidateProjectAction: El Revisor marca el projecte com a revisat apte per validar
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ToValidateProjectAction extends ViewProjectAction {

    public function responseProcess() {
        $model = $this->getModel();
        // Obtenir les dades del projecte per omplir l'històric del control de canvis
        $projectMetaData = $model->getCurrentDataProject(FALSE, FALSE);
        // El Revisor marca el projecte com a revisat: canvi data i signatura del Revisor
//        $projectMetaData['cc_dadesRevisor']['dataDeLaGestio'] = date("Y-m-d");
//        $projectMetaData['cc_dadesRevisor']['signatura'] = "signat";
        $model->updateSignature($projectMetaData, "cc_dadesRevisor");
        $model->setDataProject($projectMetaData, "Programació marcada per a ser validada");
        $response = parent::responseProcess();
        $notifyAction = $this->getActionInstance("NotifyAction", null, FALSE);
        $notifyParams=[
            "do" => NotifyAction::DO_ADDMESS,
            "to" => $projectMetaData["validador"],
            "message" => "La programació {$this->params['id']} està a punt per ser validada.",
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
