<?php
/**
 * DraftProjectMetaDataAction: Gestiona l'esborrany del formulari de dades d'un projecte mentre s'està modificant
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
require_once (DOKU_INC . "lib/plugins/ajaxcommand/defkeys/PageKeys.php");
include_once (DOKU_PLUGIN . "wikiiocmodel/projects/iocdocum/actions/ProjectMetadataAction.php");

class DraftProjectMetaDataAction extends ProjectMetadataAction {

    private $resourceLocker;
    private $Do;
    private static $infoDuration = 15;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->resourceLocker = new ResourceLocker($this->persistenceEngine);
        $this->Do = PageKeys::DW_ACT_PREVIEW;
    }

    protected function startProcess() {
        $this->projectModel->init($this->params[ProjectKeys::KEY_ID],
                                  $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                  $this->params[ProjectKeys::KEY_REV]);

        $this->resourceLocker->init($this->params);

        if ($this->params[ProjectKeys::KEY_DO]===ProjectKeys::KEY_SAVE || $this->params[ProjectKeys::KEY_DO]===ProjectKeys::KEY_SAVE_PROJECT_DRAFT) {
            $this->Do = PageKeys::DW_ACT_PREVIEW;
        }else if ($this->params[ProjectKeys::KEY_DO]===PageKeys::DW_ACT_REMOVE || $this->params[ProjectKeys::KEY_DO]===ProjectKeys::KEY_REMOVE_PROJECT_DRAFT) {
            $this->Do = PageKeys::DW_ACT_DRAFTDEL;
        }
    }

    protected function runProcess() {
        if ( ! $this->projectModel->existProject($this->params[ProjectKeys::KEY_ID]) ) {
            throw new PageNotFoundException($this->ProjectKeys[ProjectKeys::KEY_ID]);
        }

        if ($this->resourceLocker->checklock() === LockDataQuery::LOCKED) {
            throw new FileIsLockedException($this->params[ProjectKeys::KEY_ID]);
        }

        $id = $this->idToRequestId($this->params[ProjectKeys::KEY_ID]);
        if ($this->Do === PageKeys::DW_ACT_PREVIEW) {
            //actualiza la información de bloqueo mientras se siguen modificando los datos del formulario del proyecto
            $response["lockInfo"] = $this->resourceLocker->updateLock()["info"];

            $draft = json_decode($this->params['draft'], true);
            $draft['date'] = $this->params['date'];
            $this->getModel()->saveDraft($draft);
            $response[ProjectKeys::KEY_ID] = $id;
            $response['info'] = self::generateInfo("info", "S'ha desat l'esborrany", $id, self::$infoDuration);
        }
        else if ($this->Do === PageKeys::DW_ACT_DRAFTDEL) {
            $this->getModel()->removeDraft();
            $response[ProjectKeys::KEY_ID] = $id;
        }
        else{
            throw new UnexpectedValueException("Unexpected value '".$this->params[ProjectKeys::KEY_DO]."', for parameter 'do'");
        }
        return $response;
    }

    protected function responseProcess() {
        $this->startProcess();
        $ret = $this->runProcess();
        return $ret;
    }
}
