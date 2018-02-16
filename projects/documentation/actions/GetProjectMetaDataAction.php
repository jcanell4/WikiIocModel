<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . 'wikiiocmodel/projects/documentation/actions/ProjectMetadataAction.php');

class GetProjectMetaDataAction extends ProjectMetadataAction {

    public function responseProcess() {
        $this->projectModel->init($this->params[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_PROJECT_TYPE], $this->params[ProjectKeys::KEY_REV]);

        //sólo se ejecuta si existe el proyecto
        if ($this->projectModel->existProject($this->params[ProjectKeys::KEY_ID])) {

            $response = $this->projectModel->getData();

            //afegir les revisions a la resposta
            $response[ProjectKeys::KEY_REV] = $this->projectModel->getProjectRevisionList($this->params[ProjectKeys::KEY_ID], 0);

            //en un futuro, añadir pestaña de notificaciones en la ZONA META
            //$this->projectModel->addNotificationsMetaToResponse($response);

            $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_loaded'), $this->params[ProjectKeys::KEY_ID]);
            $response[ProjectKeys::KEY_ID] = $this->idToRequestId($this->params[ProjectKeys::KEY_ID]);

            if ($this->params[ProjectKeys::KEY_REV]) {
                $response[ProjectKeys::KEY_ID] .= ProjectKeys::REVISION_SUFFIX;

                // Si no s'ha especificat cap altre missatge mostrem el de càrrega
                $revisionInfo = WikiIocLangManager::getXhtml('showprojectrev');
                if (!$response['info']) {
                    $response['info'] = $this->generateInfo("warning", strip_tags($revisionInfo), $response[ProjectKeys::KEY_ID]);
                } else {
                    $this->addInfoToInfo($response['info'], $this->generateInfo("info", strip_tags($revisionInfo), $response[ProjectKeys::KEY_ID]));
                }
                // Corregim els ids de les metas per indicar que és una revisió
                $this->addRevisionSuffixIdToArray($response['meta']);
            }
        }

        if (!$response)
            throw new ProjectNotExistException($this->params[ProjectKeys::KEY_ID]);
        else
            return $response;
    }

    /**
     * Añade sufijo de revisión al id de cada una de las pestañas de la Zona META
     * @param type $elements de la Zona META
     */
    private function addRevisionSuffixIdToArray(&$elements) {
        for ($i=0, $len=count($elements); $i<$len; $i++) {
            if ($elements[$i]['id'] && substr($elements[$i]['id'], -5) != ProjectKeys::REVISION_SUFFIX) {
                $elements[$i]['id'] .= ProjectKeys::REVISION_SUFFIX;
            }
        }
    }

}