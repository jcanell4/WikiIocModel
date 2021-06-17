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
        $documents = json_decode($dataProject['documents'], true);

        //construimos la lista de ficheros a enviar con sus propiedaddes
        foreach ($documents as $doc) {
            if ($doc['sendftp'] && $doc['sendftp'] !== "false") {
                $filesToSend[] = ['file' => "{$id}_{$doc['nom']}.zip",
                                  'local' => $data_list['local'],
                                  'action' => $data_list['action'],
                                  'remoteBase' => $data_list['remoteBase'],
                                  'remoteDir' => $remoteDir
                                 ];
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
            $this->projectMetaDataQuery->setProjectSystemStateAttr("ftpsend_timestamp", filemtime("$dir/$file"));
        }
    }

    /**
     * Gestiona la llista de documents definits per l'usuari
     * @param array $data : dades del projecte (camps del formulari actiu)
     */
    public function validateFields($data=NULL){
        if ($data) {
            $nousDocuments = json_decode($data['documents'], true);
            
            $dataProject = $this->getCurrentDataProject();
            $vellsDocuments = json_decode($dataProject['documents'], true);

            $id = $this->getId();
            $path_continguts = WikiGlobalConfig::getConf('datadir')."/".str_replace(":", "/", $id);

            foreach ($nousDocuments as $k => $doc) {
                //S'ha afegit un nou fitxer, és a dir, una nova fila a la taula
                if (!isset($vellsDocuments[$k]['nom'])) {
                    $this->createPageFromTemplate("$id:{$doc['nom']}", NULL, $this->getRawProjectTemplate(), "create page");
                }
                //S'ha modificat el nom d'un fitxer
                else if ($doc['nom'] !== $vellsDocuments[$k]['nom']) {
                    $this->renamePage($id, $path_continguts, $vellsDocuments[$k]['nom'], $doc['nom']);
                }
            }
            
            //En el cas que s'hagin eliminat files de la taula de documents
            for ($i=$k+1; $i<count($vellsDocuments); $i++) {
                $this->createPageFromTemplate("$id:{$vellsDocuments[$i]['nom']}", NULL, NULL, "remove page");
            }
        }
        else {
            throw new Exception("Aquí passa alguna cosa rara");
        }
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
