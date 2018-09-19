<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . 'wikiiocmodel/actions/ProjectMetadataAction.php');

class BasicViewProjectMetaDataAction extends ProjectMetadataAction {

    protected function setParams($params) {
        parent::setParams($params);
        $this->projectModel->init($this->params[ProjectKeys::KEY_ID],
                                  $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                  $this->params[ProjectKeys::KEY_REV]);

        if (!$this->params[ProjectKeys::KEY_DATE]) {
            $draft_date = $this->projectModel->getDraft('date');
            if ($draft_date) {
                $this->params[ProjectKeys::KEY_DATE] = $draft_date;
            }
        }
    }

    public function responseProcess() {
        $this->initAction();
        $response = $this->runAction();
        $this->postAction($response);
        return $response;
    }

    protected function runAction() {
        $response = $this->projectModel->getData();

        //afegir les revisions a la resposta
        $response[ProjectKeys::KEY_REV] = $this->projectModel->getProjectRevisionList(0);

        //en un futuro, añadir pestaña de notificaciones en la ZONA META
        //$this->projectModel->addNotificationsMetaToResponse($response);

        $drafts = $this->projectModel->getAllDrafts();
        if (count($drafts) > 0) {
            $response['drafts'] = $drafts;
        }
        //Pot existir un draft local i sense draft remot
        $response['originalLastmod'] = $this->projectModel->getLastModFileDate();

        $response[ProjectKeys::KEY_ID] = $this->idToRequestId($this->params[ProjectKeys::KEY_ID]);

        if ($this->params[ProjectKeys::KEY_REV]) {
            $response[ProjectKeys::KEY_ID] .= ProjectKeys::REVISION_SUFFIX;
            if ($response['meta']) {
                // Corregim els ids de les metas per indicar que és una revisió
                $this->addRevisionSuffixIdToArray($response['meta']);
            }
        }

        //Añadir propiedades/restricciones del configMain para la creación de elementos dentro del proyecto
        parent::addResponseProperties($response);
        $response['generated'] = $this->getModel()->isProjectGenerated();
        return $response;
    }

    protected function initAction() {
        //sólo se ejecuta si existe el proyecto
        if (!$this->projectModel->existProject()) {
            throw new ProjectNotExistException($this->params[ProjectKeys::KEY_ID]);
        }
    }

    protected function postAction(&$response) {
        if ($this->params[ProjectKeys::KEY_REV]) {
            $new_message = $this->generateInfo("warning", WikiIocLangManager::getLang('project_revision'), $response[ProjectKeys::KEY_ID]);
            $response['info'] = $this->addInfoToInfo($response['info'], $new_message);
        }else {
            $new_message = $this->generateInfo("info", WikiIocLangManager::getLang('project_loaded'), $response[ProjectKeys::KEY_ID]);
            $response['info'] = $this->addInfoToInfo($response['info'], $new_message);
            $new_message = $this->generateInfo("info", WikiIocLangManager::getLang('project_view'), $response[ProjectKeys::KEY_ID]);
            $response['info'] = $this->addInfoToInfo($response['info'], $new_message);
        }
    }

    /**
     * Añade sufijo de revisión al id de cada una de las pestañas de la Zona META
     * @param type $elements de la Zona META
     */
    protected function addRevisionSuffixIdToArray(&$elements) {
        for ($i=0, $len=count($elements); $i<$len; $i++) {
            if ($elements[$i]['id'] && substr($elements[$i]['id'], -5) != ProjectKeys::REVISION_SUFFIX) {
                $elements[$i]['id'] .= ProjectKeys::REVISION_SUFFIX;
            }
        }
    }

   /**
     * Mètode que s'ha d'executar en iniciar el desbloqueig o també quan l'usuari cancel·la la petició de bloqueig.
     * Per defecte no es desbloqueja el recurs, perquè actualment el desbloqueig es realitza internament
     * a les funcions natives de la wiki.
     *
     * @param bool $unlock : si TRUE, llavors es força aquí del desbloqueig
     * @return int : és una constant amb el resultat de la petició
     */
    public function leaveResource($unlock = FALSE) {
        $this->resourceLocker->init($this->params);
        return $this->resourceLocker->leaveResource($unlock);
    }

}
