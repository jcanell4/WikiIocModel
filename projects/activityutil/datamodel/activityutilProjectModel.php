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

    public function filesToExportList($full=false) {
        // obtenemos del configMain un array con los parámetros de envío para un fichero
        $data_list = current(parent::filesToExportList());
        $id = preg_replace('/:/', '_', $this->getId());
        $remoteDir = empty($data_list['remoteDir']) ? "$id/" : $data_list['remoteDir'];

        //obtenemos la lista de ficheros incluyendo la propiedad booleana 'sendftp'
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
                                      'remoteDir' => "$remoteDir"
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
        $files = $this->llistaDeEspaiDeNomsDeDocumentsDelProjecte();
        $file = "$dir/{$files[0]}";
        if (file_exists($file)) {
            $this->projectMetaDataQuery->setProjectSystemStateAttr("ftpsend_timestamp", filemtime($file));
        }
    }

    /**
     * Comprova si els fitxers 'HTML export' han estat generats per ser enviats via FTP
     * (només s'utilitza el primer fitxer de la llista)
     * @return string HTML per a les metadades
     */
    public function get_ftpsend_metadata($useSavedTime=TRUE) {
        $mdFtpSender = $this->getMetaDataFtpSender();
        $fileNames = $this->_constructArrayFileNames($this->id, $mdFtpSender['files']);

        $file = WikiGlobalConfig::getConf('mediadir').'/'. preg_replace('/:/', '/', $this->id) . '/' . $fileNames[0];
        $html = '';
        $fileexists = @file_exists($file);
        if ($fileexists) {
            $savedtime = $this->projectMetaDataQuery->getProjectSystemStateAttr("ftpsend_timestamp");
            $filetime = filemtime($file);
        }

        if ($fileexists && (!$useSavedTime || ($savedtime === $filetime))) {
            foreach ($mdFtpSender['files'] as $objFile) {
                $ftpId = (empty($objFile[ProjectKeys::KEY_FTPID])) ? $mdFtpSender[ProjectKeys::KEY_FTPID] : $objFile[ProjectKeys::KEY_FTPID];
                $connData = $this->getFtpConfigData($ftpId);
                $index = (empty($objFile['remoteIndex'])) ? $mdFtpSender['remoteIndex'] : $objFile['remoteIndex'];
                $rDir = (empty($objFile['remoteDir'])) ? (empty($mdFtpSender['remoteDir'])) ? $connData["remoteDir"] : $mdFtpSender['remoteDir'] : $objFile['remoteDir'];
                $unzip = in_array(1, $objFile['action']);  //es una action del tipo unzip
                $linkRef = $objFile['linkName'];
            }

            $data = date("d/m/Y H:i:s", $filetime);
            foreach ($fileNames as $file) {
                $_index = (empty($index)) ? $file : $index;
                $_linkRef = (empty($linkRef)) ? pathinfo($file, PATHINFO_FILENAME) : $linkRef;
                $_rDir = ($unzip) ? $rDir . pathinfo($file, PATHINFO_FILENAME) . "/" : $rDir;
                $url = "{$connData['remoteUrl']}${_rDir}${_index}";
                $class = "mf_".pathinfo($_index, PATHINFO_EXTENSION);
                $html.= '<p><span id="ftpsend" style="word-wrap: break-word;">';
                $html.= '<a class="media mediafile '.$class.'" href="'.$url.'" target="_blank">'.$_linkRef.'</a> ';
                $html.= '<span style="white-space: nowrap;">'.$data.'</span>';
                $html.= '</span></p>';
            }
        }else{
            $html.= '<span id="ftpsend">';
            $html.= '<p class="media mediafile '.$class.'">No hi ha cap fitxer pujat al FTP</p>';
            $html.= '</span>';
        }

        return $html;
    }

    /**
     * Construye la lista de ficheros a partir del array recibido y la consulta a los datos del proyecto
     * @return array con los nombres de los ficheros
     */
    private function _constructArrayFileNames($id, $metaDataFtpSender=NULL) {
        if ($metaDataFtpSender) {
            $ret = array();
            $base = str_replace(":", "_", $id);
            $dataProject = $this->getCurrentDataProject(FALSE, FALSE);
            $fileNames = json_decode($dataProject['documents'], true);
            if (!empty($fileNames)) {
                foreach ($metaDataFtpSender as $f) {
                    if ($f['type']=="zip") {   //sólo si es necesario que aparezca en la lista de metadatos
                        foreach ($fileNames as $file) {
                            if ($file['sendftp'] && ((is_bool($file['sendftp']) && $file['sendftp']===TRUE) ||
                                                     (is_string($file['sendftp']) && !in_array($file['sendftp'], ["false","no","0"])) )) {
                                $ret[] = "${base}_${file['nom']}.{$f['type']}";
                            }
                        }
                    }elseif ($f['type']=="pdf") {
                        $ret[] = $f['filename'];
                    }
                }
            }
        }
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
                usort($nousDocuments, 'self::cmpForSort');  //ordenamos el array por el campo 'id'

                $dataProject = $this->getCurrentDataProject();
                $vellsDocuments = json_decode($dataProject['documents'], true);
                if (!empty($vellsDocuments)) {
                    foreach ($vellsDocuments as $k => $doc) {
                        $vellsDocuments[$k]['nom'] = trim($doc['nom']);
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
                            $this->createPageFromTemplate("$id:{$doc['nom']}", NULL, $this->getRawProjectTemplate(), "create page");
                        }elseif ($rowid !== false && $doc['nom'] !== $vellsDocuments[$rowid]['nom']) {
                            //S'ha modificat el nom d'un fitxer
                            $modificat = true;
                            $this->renamePage($id, $path_continguts, $vellsDocuments[$rowid]['nom'], $doc['nom']);
                        }
                    }else {
                        //S'ha afegit un nou fitxer, és a dir, una nova fila a la taula
                        $modificat = true;
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
                        $modificat = true;
                        $this->createPageFromTemplate("$id:{$doc['nom']}", NULL, NULL, "remove page");
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

        return $files;
    }

}
