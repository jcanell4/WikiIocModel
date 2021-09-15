<?php
/**
 * activityutilProjectModel
 * @culpable rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();

class activityutilProjectModel extends MultiContentFilesProjectModel {

    public function __construct($persistenceEngine) {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction = false;
    }

    public function generateProject(){}

    public function hasTemplates(){
        return false;
    }

    public function directGenerateProject() {
        return $this->projectMetaDataQuery->setProjectGenerated();
    }

    public function filesToExportList() {
        // obtenemos del configMain un array con los parámetros de envío para un fichero
        $data_list = current(parent::filesToExportList());
        $id = preg_replace('/:/', '_', $this->getId());
        $remoteDir = empty($data_list['remoteDir']) ? $id : $data_list['remoteDir'];

        //obtenemos la lista de ficheros que incluye la propiedad booleana 'sendftp'
        $dataProject = $this->getCurrentDataProject();

        if ($dataProject['documents']) {
            $documents = json_decode($dataProject['documents'], true);
            //construimos la lista de ficheros a enviar con sus propiedaddes
            foreach ($documents as $doc) {
                if ($doc['sendftp'] && ((is_bool($doc['sendftp']) && $doc['sendftp']===TRUE) ||
                                        (is_string($doc['sendftp']) && !in_array($doc['sendftp'], ["false","no","0"])) )) {
                    $filesToSend[] = ['file' => "{$id}_{$doc['nom']}.zip",
                                      'local' => $data_list['local'],
                                      'action' => $data_list['action'],
                                      'remoteBase' => $data_list['remoteBase'],
                                      'remoteDir' => $remoteDir
                                     ];
                }
            }
        }
        return $filesToSend;
    }

    /**
     * overwrite
     * Guarda, en el fitxer _wikiIocSystem_.mdpr (chivato), la data del fitxer 'HTML export' que s'ha d'enviar per FTP
     * (només s'utilitza el primer fitxer de la llista)
     */
    public function set_ftpsend_metadata() {
        $dir = WikiGlobalConfig::getConf('mediadir')."/". preg_replace('/:/', '/', $this->getId());
        foreach (scandir($dir) as $f) {
            if (is_file("$dir/$f")) {
                $file = "$dir/$f";
                break;
            }
        }
        if ($file) {
            $this->projectMetaDataQuery->setProjectSystemStateAttr("ftpsend_timestamp", filemtime($file));
        }
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
                if (!empty($vellsDocuments))
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

    /**
     * Obtiene la lista de ficheros creados, por el autor, en el proyecto
     * @return array de ficheros
     */
    public function llistaDeEspaiDeNomsDeDocumentsDelProjecte() {
        //datos del proyecto almacenados
        $dataProject = $this->getCurrentDataProject();
        $id = str_replace(":", "_", $this->getId());
        //lista de campos susceptibles de ser tablas que contienen listas de ficheros
        $exportFields = $this->getMetaDataExport("fields", "main");
        if (is_array($exportFields)) {
            foreach ($exportFields as $f) {
                //obtención de los nombres de cada fichero
                $arrField = json_decode($dataProject[$f['field']], true);
                if (is_array($arrField)) {
                    foreach ($arrField as $n) {
                        $files[] = $id."_".$n['nom'].$f['ext'];
                    }
                }
            }
        }
        if (is_array($files)) {
            $files = array_unique($files);
        }
        $exportFields = $this->getMetaDataExport();
        return $files;
    }

}
