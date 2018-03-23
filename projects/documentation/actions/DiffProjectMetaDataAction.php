<?php
/**
 * DraftProjectMetaDataAction: Gestiona l'esborrany del formulari de dades d'un projecte mentre s'està modificant
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_INC . 'lib/plugins/ajaxcommand/defkeys/PageKeys.php');
include_once (DOKU_PLUGIN . "wikiiocmodel/projects/documentation/actions/ProjectMetadataAction.php");

class DiffProjectMetaDataAction extends ProjectMetadataAction {

    private static $infoDuration = 15;

    public function init($modelManager) {
        parent::init($modelManager);
    }

    protected function startProcess() {
        $this->projectModel->init($this->params[ProjectKeys::KEY_ID],
                                  $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                  $this->params[ProjectKeys::KEY_REV]);
    }

    protected function runProcess() {
        if (!$this->projectModel->existProject($this->params[ProjectKeys::KEY_ID])) {
            throw new PageNotFoundException($this->ProjectKeys[ProjectKeys::KEY_ID]);
        }

        $id = $this->idToRequestId($this->params[ProjectKeys::KEY_ID]);
        //array de datos del proyecto actual
        $data_project = $this->projectModel->getDataProject($this->params[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_PROJECT_TYPE]);
        $date = $this->projectModel->getLastModFileDate($this->params[ProjectKeys::KEY_ID]);
        //array de datos de la revisión
        $rev1 = $this->projectModel->getDataRevisionProject($this->params[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_REV]);
        $date_rev1 = $this->projectModel->getDateRevisionProject($this->params[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_REV]);

        $rdata = [
            'id' =>  "{$id}_diff",
            'ns' => $this->params[ProjectKeys::KEY_ID],
            'title' => $this->params[ProjectKeys::KEY_ID],
            'type' => "project_diff",
            'content' => $data_project,
            'date' => $date,
            'rev1' => $rev1,
            'date_rev1' => $date_rev1
        ];

        if ($rev2) {
            $rdata['rev1'] = $rev2[0];
            $rdata['rev2'] = $rev2[1];
        }
        $response['rdata'] = $rdata;
        $response[ProjectKeys::KEY_ID] = $id;
        $response[ProjectKeys::KEY_PROJECT_TYPE] = $this->params[ProjectKeys::KEY_PROJECT_TYPE];
        $response['info'] = self::generateInfo("info", WikiIocLangManager::getLang('form_compare').' '.dformat(), $id, self::$infoDuration);
        //afegir les revisions a la resposta
        $response[ProjectKeys::KEY_REV] = $this->projectModel->getProjectRevisionList($this->params[ProjectKeys::KEY_ID], 0);

        return $response;
    }

    protected function responseProcess() {
        $this->startProcess();
        $ret = $this->runProcess();
        return $ret;
    }

}
