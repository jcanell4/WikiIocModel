<?php
if (!defined('DOKU_INC')) die();

class SetProjectMetaDataAction extends BasicSetProjectMetaDataAction {

    protected function responseProcess(){
        $response = parent::responseProcess();
        if ($this->getModel()->isProjectGenerated()) {
            $llista = $this->getModel()->llistaDePlantilles();
            foreach ($llista as $p) {
                p_set_metadata($p, array('metadataProjectChanged' => time()));
            }
        }
        return $response;
    }
}
