<?php
/**
 * ToReviseProjectAction: L'Autor marca el projecte apte per revisar
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ToReviseProjectAction extends ViewProjectAction {

    public function responseProcess() {
        $model = $this->getModel();
        // Obtenir les dades del projecte per omplir l'històric del control de canvis
        $projectMetaData = $model->getCurrentDataProject(FALSE, FALSE);
        // L'Autor marca el projecte apte per revisar: canvi data i signatura Autor i afegeix canvi a l'històric
        $model->updateSignature($projectMetaData, "cc_dadesAutor");
        $model->modifyLastHistoricGestioDocument($projectMetaData);
        $model->setDataProject($projectMetaData, "Projecte marcat per a ser revisat");
        $response = parent::responseProcess();
        $notifyAction = $this->getActionInstance("NotifyAction", null, FALSE);
        $notifyParams=[
            "do" => NotifyAction::DO_ADDMESS,
            "to" => $projectMetaData["revisor"],
            "message" => "La programació {$this->params['id']} està a punt per ser revisada.",
            "id" => $this->params["id"],
            "type" => NotifyAction::DEFAULT_MESSAGE_TYPE,
            "data-call" => "project&do=workflow&action=view",
            "send_email" => true,
        ];
        $responseNotify = $notifyAction->get($notifyParams);
        $this->addInfoToInfo($response["info"], $responseNotify["info"]);
        $response["notifications"] = $responseNotify["notifications"];
        return $response;
    }

}
