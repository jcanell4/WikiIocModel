<?php
/**
 * ToReviseProjectAction: L'Autor marca el projecte apte per revisar
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ToRevoqueProjectAction extends ViewProjectAction {

    public function responseProcess() {
        $model = $this->getModel();
        // Obtenir les dades del projecte per omplir l'històric del control de canvis
        $projectMetaData = $model->getCurrentDataProject(FALSE, FALSE);
        // L'Autor marca el projecte apte per revisar: canvi data i signatura Autor i afegeix canvi a l'històric
        $model->updateSignature($projectMetaData, "cc_dadesAutor", FALSE, "pendent");
        $model->setDataProject($projectMetaData, "Modificació revocada durant la revisió");
        $response = parent::responseProcess();
        $notifyAction = $this->getActionInstance("NotifyAction", null, FALSE);
        $notifyParams=[
            "do" => NotifyAction::DO_ADDMESS,
            "to" => "{$projectMetaData["responsable"]},{$projectMetaData["autor"]}",
            "message" => "S'ha revocat la modificació de la programació {$this->params['id']} degut al següent motiu:\n\n{$this->params["motiu"]}",
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
