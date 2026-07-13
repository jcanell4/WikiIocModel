<?php
if (!defined('DOKU_INC')) die();

class ViewProjectAction extends BasicViewUpdatableProjectAction{

    protected function runAction() {

        if (!$this->getModel()->isProjectGenerated()) {
            $this->getModel()->setViewConfigKey(ProjectKeys::KEY_VIEW_FIRSTVIEW);
        }
        $response = parent::runAction();

        return $response;
    }

    public function responseProcess() {

        $response = parent::responseProcess();
        $response[AjaxKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();

        return $response;
    }

    protected function isUpdatedDate($metaDataSubSet) {
        return self::stIsUpdatedDatePtfpprj24($this, $metaDataSubSet);
    }

    public static function stIsUpdatedDatePtfpprj24($obj, $metaDataSubSet) {
        $isUpdated = self::FORA_FINESTRA;
        $projectModel = $obj->getModel();

        $updatedDate = $projectModel->getProjectSystemSubSetAttr("updatedDate", $metaDataSubSet, 0);
        if ($updatedDate !== NULL) {
            $confProjectType = $obj->modelManager->getConfigProjectType();
            $projectTypeConfigFile = $projectModel->getProjectTypeConfigFile();

            $cfgProjectModel = $confProjectType."ProjectModel";
            $configProjectModel = new $cfgProjectModel($obj->persistenceEngine);

            $configProjectModel->init([ProjectKeys::KEY_ID              => $projectTypeConfigFile,
                                       ProjectKeys::KEY_PROJECT_TYPE    => $confProjectType,
                                       ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet
                                    ]);
            $metaDataConfigProject = $configProjectModel->getCurrentDataProject($metaDataSubSet);

            if ($metaDataConfigProject['arraytaula']) {
                $arraytaula = is_string($metaDataConfigProject['arraytaula'])
                    ? json_decode($metaDataConfigProject['arraytaula'], TRUE)
                    : $metaDataConfigProject['arraytaula'];

                $anyActual = date("Y");
                $dataActual = new DateTime();
                $dataActual->setTime(0, 0, 0);

                $inici_semestre = NULL;
                $fi_semestre = NULL;
                foreach ($arraytaula as $elem) {
                    if ($elem['key']==="inici_semestre") {
                        $inici_semestre = self::stObtenirData($elem['value'], $anyActual);
                    } else if ($elem['key']==="fi_semestre") {
                        $fi_semestre = self::stObtenirData($elem['value'], $anyActual);
                    }
                }

                if ($inici_semestre && $fi_semestre) {
                    if ($inici_semestre > $fi_semestre) {
                        $inici_semestre = date_sub($inici_semestre, new DateInterval('P1Y'));
                    }
                    $finestraOberta = $dataActual >= $inici_semestre && $dataActual <= $fi_semestre;
                    if ($finestraOberta) {
                        $isUpdated = ($updatedDate && $updatedDate >= $inici_semestre->getTimestamp())
                            ? self::IS_UPDATED
                            : self::NO_IS_UPDATED;
                    }
                }
            }
        }
        return $isUpdated;
    }

}
