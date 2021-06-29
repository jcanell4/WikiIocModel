<?php
/**
 * manualProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class manualProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction = false;
    }

    public function generateProject(){} //abstract obligatorio

    public function directGenerateProject($data) {
        $ret = $this->projectMetaDataQuery->setProjectGenerated();
        return $ret;
    }

    /**
     * Gestiona la llista de documents definits per l'usuari
     * @param array $data : dades del projecte (camps del formulari actiu)
     */
    public function validateFields($data=NULL){
        if ($data) {
            $nousDocuments = json_decode($data['documents'], true);
            if (!empty($nousDocuments)) {
                usort($nousDocuments, 'self::cmpForSort');  //ordenamos el array por el campo 'id'

                $dataProject = $this->getCurrentDataProject();
                $vellsDocuments = json_decode($dataProject['documents'], true);
                usort($vellsDocuments, 'self::cmpForSort');  //ordenamos el array por el campo 'id'

                $id = $this->getId();
                $path_continguts = WikiGlobalConfig::getConf('datadir')."/".str_replace(":", "/", $id);

                foreach ($nousDocuments as $k => $doc) {
                    if ($doc['id'] === $vellsDocuments[$k]['id']) {
                        //S'ha modificat el nom d'un fitxer
                        if ($doc['nom'] !== $vellsDocuments[$k]['nom']) {
                            $this->renamePage($id, $path_continguts, $vellsDocuments[$k]['nom'], $doc['nom']);
                        }
                    }elseif ($doc['id'] > $vellsDocuments[$k]['id'] && $vellsDocuments[$k]) {
                        $rowid = array_search($doc['id'], array_column($vellsDocuments, 'id'));
                        // busca el id actual en todo el array de $vellsDocuments
                        if ($rowid !== false && $doc['nom'] !== $vellsDocuments[$rowid]['nom']) {
                            $this->renamePage($id, $path_continguts, $vellsDocuments[$rowid]['nom'], $doc['nom']);
                        }
                    }else {
                        //S'ha afegit un nou fitxer, és a dir, una nova fila a la taula
                        $this->createPageFromTemplate("$id:{$doc['nom']}", NULL, $this->getRawProjectTemplate(), "create page");
                    }
                }
            }

            if (!empty($vellsDocuments)) {
                //En el cas que s'hagin eliminat files de la taula de documents
                foreach ($vellsDocuments as $doc) {
                    if (!empty($nousDocuments)) {
                        $rowid = array_search($doc['id'], array_column($nousDocuments, 'id'));
                        $rownom = array_search($doc['nom'], array_column($nousDocuments, 'nom'));
                    }
                    if ($rowid === false && $rownom === false) {
                        $this->createPageFromTemplate("$id:{$doc['nom']}", NULL, NULL, "remove page");
                    }
                }
            }
        }
        else {
            throw new Exception("Aquí passa alguna cosa rara");
        }
    }

    /**
     * Callback de usort. Retorna el valor de la comparación de 2 elementos. Se comparan los valores de las claves 'id'
     * @param array $a
     * @param array $b
     * @return int : 0 $a==$b; 1 $a < $b; -1 $a < $b
     */
    static function cmpForSort($a, $b) {
        return ($a['id'] === $b['id']) ? 0 : (($a['id'] < $b['id']) ? -1 : 1);
    }

}
