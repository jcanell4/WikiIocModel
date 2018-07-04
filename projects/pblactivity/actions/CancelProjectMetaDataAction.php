<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
include_once (DOKU_PLUGIN . "wikiiocmodel/projects/pblactivity/actions/ViewProjectMetaDataAction.php");

class CancelProjectMetaDataAction extends ViewProjectMetaDataAction implements ResourceUnlockerInterface {

    private $resourceLocker;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->resourceLocker = new ResourceLocker($this->persistenceEngine);
    }

    protected function runAction() {
        $lockStruct = $this->leaveResource(TRUE);
        if ($lockStruct['state']) {
            $response['lockInfo'] = $lockStruct['info']['locker'];
            $response['lockInfo']['state'] = $lockStruct['state'];
        }
        //if (isset($this->params[ProjectKeys::KEY_LEAVERESOURCE])) {  }

        if (!$this->params[ProjectKeys::KEY_KEEP_DRAFT]) {
            $this->getModel()->removeDraft();
        }

        if ($this->params[ProjectKeys::KEY_NO_RESPONSE] ) {
            $response[ProjectKeys::KEY_CODETYPE] = 0;
            return $response;
        }

        $response = parent::runAction();
        return $response;
    }

    protected function postAction(&$response) {
        if ($response[ProjectKeys::KEY_CODETYPE] !== 0) {
            $new_message = $this->generateInfo("info", WikiIocLangManager::getLang('project_canceled'), $this->params[ProjectKeys::KEY_ID]);
            $response['info'] = $this->addInfoToInfo($response['info'], $new_message);
        }
    }

   /**
     * Mmètode que s'ha d'executar en iniciar el desbloqueig o també quan l'usuari cancel·la la demanda
     * de bloqueig. Per defecte no es desbloqueja el recurs, perquè actualment el desbloqueig es realitza internament
     * a les funcions natives de la wiki. Es fa el desbloqueig directament aquí, si es passa el paràmetre amb valor TRUE.
     * EL mètode retorna una constant amb el resultat obtingut de la petició.
     *
     * @param bool $unlock
     * @return int
     */
    public function leaveResource($unlock = FALSE) {
        $this->resourceLocker->init($this->params);
        return $this->resourceLocker->leaveResource($unlock);
    }

}