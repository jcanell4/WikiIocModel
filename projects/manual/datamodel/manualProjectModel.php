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
    public function validateFields($data=NULL, $subset=FALSE){
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::validateFields($data, $subset);
        }
        
        if ($data) {
            $modificat = false;
            $nousDocuments = json_decode($data['documents'], true);
            if (!empty($nousDocuments)) {
                foreach ($nousDocuments as $k => $doc) {
                    $nousDocuments[$k]['nom'] =  str_replace(" ", "_", trim($doc['nom']));
                }
                usort($nousDocuments, 'self::cmpForSort');  //ordenamos el array por el campo 'id'

                $dataProject = $this->getCurrentDataProject();
                $vellsDocuments = json_decode($dataProject['documents'], true);
                if (!empty($vellsDocuments)) {
                    foreach ($vellsDocuments as $k => $doc) {
                        $vellsDocuments[$k]['nom'] = str_replace(" ", "_", trim($doc['nom']));
                    }
                    usort($vellsDocuments, 'self::cmpForSort');  //ordenamos el array por el campo 'id'
                }

                $id = $this->getId();
                $path_continguts = WikiGlobalConfig::getConf('datadir')."/".str_replace(":", "/", $id);

                foreach ($nousDocuments as $k => $doc) {
                    if ($doc['id'] === $vellsDocuments[$k]['id']) {
                        if ($doc['nom'] !== $vellsDocuments[$k]['nom']) {
                            //S'ha modificat el nom d'un fitxer
                            $modificat = true;
                            $this->renamePage($id, $path_continguts, $vellsDocuments[$k]['nom'], $doc['nom']);
                        }
                    }elseif ($doc['id'] > $vellsDocuments[$k]['id'] && $vellsDocuments[$k]) {
                        $rowid = array_search($doc['id'], array_column($vellsDocuments, 'id'));
                        $rownom = array_search($doc['nom'], array_column($vellsDocuments, 'nom'));
                        if ($rowid === false && $rownom === false) {
                            //S'ha afegit un nou fitxer, és a dir, una nova fila a la taula
                            $modificat = true;
                            $this->createPageFromTemplate("$id:${doc['nom']}", NULL, $this->getRawProjectTemplate(), "create page");
                        }elseif ($rowid !== false && $doc['nom'] !== $vellsDocuments[$rowid]['nom']) {
                            //S'ha modificat el nom d'un fitxer
                            $modificat = true;
                            $this->renamePage($id, $path_continguts, $vellsDocuments[$rowid]['nom'], $doc['nom']);
                        }
                    }else {
                        //S'ha afegit un nou fitxer, és a dir, una nova fila a la taula
                        $modificat = true;
                        $this->createPageFromTemplate("$id:${doc['nom']}", NULL, $this->getRawProjectTemplate(), "create page");
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
                        $modificat = true;
                        $this->createPageFromTemplate("$id:${doc['nom']}", NULL, NULL, "remove page");
                    }
                }
            }

            if ($modificat) {
                //Actualización de _wikiIocSystem_.mdpr
                $dataSystem = $this->getSystemData();
                $dataSystem['versions']['templates'] = [];
                foreach ($nousDocuments as $doc) {
                    $dataSystem['versions']['templates'][$doc['nom']] = null;
                }
                $this->setSystemData($dataSystem);
            }
        }
        else {
            throw new Exception("Aquí passa alguna cosa rara");
        }

//        if ($data) {
//            $id = $this->getId();
//            $path_continguts = WikiGlobalConfig::getConf('datadir')."/".str_replace(":", "/", $id);
//
//            $nousDocuments = is_array($data['documents']) ? $data['documents'] : json_decode($data['documents'], true);
//            if (!empty($nousDocuments)) {
//                usort($nousDocuments, 'self::cmpForSort');  //ordenamos el array por el campo 'id'
//
//                $dataProject = $this->getCurrentDataProject();
//                $vellsDocuments = is_array($dataProject['documents']) ? $dataProject['documents'] : json_decode($dataProject['documents'], true);
//                usort($vellsDocuments, 'self::cmpForSort');  //ordenamos el array por el campo 'id'
//
//                foreach ($nousDocuments as $k => $doc) {
//                    if ($doc['id'] == $vellsDocuments[$k]['id']) {
//                        //comprovem si s'ha modificat el nom d'un fitxer
//                        if ($doc['nom'] !== $vellsDocuments[$k]['nom']) {
//                            $this->renamePage($id, $path_continguts, $vellsDocuments[$k]['nom'], $doc['nom']);
//                        }
//                    }elseif ($doc['id'] > $vellsDocuments[$k]['id'] && $vellsDocuments[$k]) {
//                        // busca el id actual en todo el array de $vellsDocuments
//                        $rowid = array_search($doc['id'], array_column($vellsDocuments, 'id'));
//                        if ($rowid !== false && $doc['nom'] !== $vellsDocuments[$rowid]['nom']) {
//                            $this->renamePage($id, $path_continguts, $vellsDocuments[$rowid]['nom'], $doc['nom']);
//                        }
//                    }else {
//                        //S'ha afegit un nou fitxer, és a dir, una nova fila a la taula
//                        if (!file_exists("$path_continguts/{$doc['nom']}.txt")) {
//                            $this->createPageFromTemplate("$id:{$doc['nom']}", NULL, $this->getRawProjectTemplate(), "create page");
//                        }
//                    }
//                }
//            }
//
//            if (!empty($vellsDocuments)) {
//                //En el cas que s'hagin eliminat files de la taula de documents
//                foreach ($vellsDocuments as $doc) {
//                    if (!empty($nousDocuments)) {
//                        $rowid = array_search($doc['id'], array_column($nousDocuments, 'id'));
//                        $rownom = array_search($doc['nom'], array_column($nousDocuments, 'nom'));
//                    }
//                    if ($rowid === false && $rownom === false) {
//                        $this->createPageFromTemplate("$id:{$doc['nom']}", NULL, NULL, "remove page");
//                    }
//                }
//            }
//        }
//        else {
//            throw new Exception("Aquí passa alguna cosa rara");
//        }
    }

    /**
     * Callback de usort. Retorna el valor de la comparación de 2 elementos. Se comparan los valores de las claves 'id'
     * @param array $a
     * @param array $b
     * @return int : 0 $a==$b; -1 $a < $b; 1 $b <= $a
     */
    static function cmpForSort($a, $b) {
        return ($a['id'] === $b['id']) ? 0 : (($a['id'] < $b['id']) ? -1 : 1);
    }
//    
//    /**
//     * Obtiene la lista de ficheros, y sus propiedades, (del configMain.json) que hay que enviar por FTP
//     * @return array
//     */
//    public function filesToExportList() {
//    }
}
