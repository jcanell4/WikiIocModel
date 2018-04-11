<?php
/**
 * DiffProjectMetaDataAction: Costrueix les dades dels 2 projecte-revisió que es volen comparar
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . "wikiiocmodel/projects/documentation/actions/ProjectMetadataAction.php");

class DiffProjectMetaDataAction extends ProjectMetadataAction {

    private static $infoDuration = -1;

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
        //$this->params['rev2'] contiene un array de fechas correspondientes a las revisiones a comparar
        if ($this->params['rev2']) {
            $revTrev = true;
            //array de datos de la primera revisión
            $rev1 = $this->projectModel->getDataRevisionProject($this->params[ProjectKeys::KEY_ID], $this->params['rev2'][0]);
            $date_rev1 = $this->params['rev2'][0];
            //array de datos de la segunda revisión
            $rev2 = $this->projectModel->getDataRevisionProject($this->params[ProjectKeys::KEY_ID], $this->params['rev2'][1]);
            $date_rev2 = $this->params['rev2'][1];
        }
        else {
            $revTrev = false;
            //array de datos del proyecto actual
            $rev1 = $this->projectModel->getDataProject($this->params[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_PROJECT_TYPE]);
            $date_rev1 = $this->projectModel->getLastModFileDate($this->params[ProjectKeys::KEY_ID]);
            //array de datos de la revisión
            $rev2 = $this->projectModel->getDataRevisionProject($this->params[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_REV]);
            $date_rev2 = $this->params[ProjectKeys::KEY_REV];
        }

        $rdata = [
            'id' =>  "{$id}_diff",
            'ns' => $this->params[ProjectKeys::KEY_ID],
            'title' => $this->params[ProjectKeys::KEY_ID],
            'type' => "project_diff",
            'content' => $rev1,
            'date' => $date_rev1,
            'rev1' => $rev2,
            'date_rev1' => $date_rev2,
            'revTrev' => $revTrev
        ];

        $response['rdata'] = $rdata;
        $response[ProjectKeys::KEY_ID] = $id;
        $response[ProjectKeys::KEY_PROJECT_TYPE] = $this->params[ProjectKeys::KEY_PROJECT_TYPE];
        $m = $revTrev ? "form_compare_rev" : "form_compare";
        $d = "%d.%m.%Y %H:%M";
        $response['info'] = self::generateInfo("warning", WikiIocLangManager::getLang($m).' '.strftime($d, $date_rev1).' - '.strftime($d, $date_rev2), $rdata['id'], self::$infoDuration);
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
