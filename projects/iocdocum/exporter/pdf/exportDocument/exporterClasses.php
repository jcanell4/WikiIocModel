<?php
/**
 * exportDocument: clase que renderiza grupos de elementos
 * @culpable Rafael Claver
*/
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN."wikiiocmodel/");
require_once WIKI_IOC_MODEL."projects/iocdocum/exporter/exporterClasses.php";

class exportDocument extends MainRender {

    public function __construct($factory, $typedef, $renderdef, $params) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->initParams($params);
    }

    public function initParams($params=NULL){
        parent::initParams();
        if ($params) {
            $this->cfgExport->id = $params['id'];
            $this->cfgExport->lang = (!isset($params['ioclanguage'])) ? "CA" : trim(strtoupper($params['ioclanguage']));
            $this->log = isset($params['log']);
        }
    }

    public function cocinandoLaPlantillaConDatos($data) {
        @set_time_limit(240);
        $this->time_start = microtime(TRUE);
        $output_filename = str_replace(":", "_", $this->cfgExport->id);

        if (!file_exists($this->cfgExport->tmp_dir)) mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        if (!file_exists($this->cfgExport->tmp_dir."/media")) mkdir($this->cfgExport->tmp_dir."/media", 0775, TRUE);

        $frontCover = "frontCoverDoc.ltx";
        $tocPage = "tocPageDoc.ltx";
        $background = "bgCoverDoc.pdf";

        $latex = $this->renderHeader($data);
        $latex.= $this->renderCoverPage($data, $frontCover, $background);
        $latex.= $this->renderTocPage($data, $tocPage);
        $latex.= $data['fitxercontinguts'];
        if (file_exists($this->path_templates."/footer.ltx")){
            $latex .= io_readFile($this->path_templates."/footer.ltx");
        }

        $result = array();
        if ($this->mode === 'zip' || $this->filetype === 'zip'){
            $this->createZip($output_filename, $this->cfgExport->tmp_dir, $latex);
        }else{
            $this->createLatex($output_filename, $this->cfgExport->tmp_dir, $latex, $result);
        }

        return $result;
    }

    /**
     * Render frontpage
     * @param array $data
     */
    private function renderTocPage($data, $tocPage) {
        $latex = "";
        if (file_exists($this->path_templates."/$tocPage")) {
            $latex = io_readFile($this->path_templates."/$tocPage");
            if ($latex) {
                $aSearch = array("@DOC_TITOL@", "@DOC_CREDITS@");
                $aReplace = array($data['titol'], "crèdits: p2 Team");
                $latex = str_replace($aSearch, $aReplace, $latex);
            }
        }
        return $latex;
    }

    private function renderCoverPage($data, $frontCover, $background=NULL){
        $latex = "";
        if (file_exists($this->path_templates."/$frontCover")) {
            $latex = io_readFile($this->path_templates."/$frontCover");
            if ($latex) {
                $autor = implode('\\\\\\\\', explode(",", $data['autor']));
                $aSearch = array("@DOC_BACKGROUND@", "@DOC_TITOL@", "@DOC_AUTOR@");
                $aReplace = array("media/$background", $data['titol'], $autor);
                $latex = str_replace($aSearch, $aReplace, $latex);
                if ($background) {
                    $this->copyToTmp("{$this->path_templates}/$background", "media/$background");
                }
            }
        }
        return $latex;
    }

    private function renderHeader($data) {
        $latex = "";
        if (file_exists("{$this->path_templates}/header.ltx")) {
            $latex = io_readFile("{$this->path_templates}/header.ltx");
            if ($latex) {
                $qrcode = ($_SESSION['qrcode']) ? '\usepackage{pst-barcode,auto-pst-pdf}' : '';
                $titol = trim(wordwrap($data['titol']), 77, '\break ');
                $aSearch = array("@IOCLANGUAGE@", "@IOCQRCODE@", "@IOCLANGCONTINUE@", "@DOC_TITOL@", "@DOC_CREDITS@");
                $aReplace = array($this->cfgExport->lang, $qrcode, $this->ioclangcontinue[$this->cfgExport->lang], $titol, "crèdits: p2 Team");
                $latex = str_replace($aSearch, $aReplace, $latex);
            }
        }
        return $latex;
    }

    private function copyToTmp($source, $dest){
        return copy($source, $this->cfgExport->tmp_dir."/$dest");
    }

    /**
     * Compile latex document to create a pdf file
     * @param string $filename, $path, $text
     */
    private function createLatex($filename, $path, &$text, &$result){
        io_saveFile("$path/$filename.tex", $text);

        $shell_escape = ($_SESSION['qrcode']) ? "-shell-escape" : "";
        @exec("cd $path && pdflatex -draftmode $shell_escape -halt-on-error $filename.tex", $sortida, $return);
        if ($return === 0){
            //One more to calculate correctly size tables
            @exec("cd $path && pdflatex -draftmode $shell_escape -halt-on-error $filename.tex" , $sortida, $return);
            if ($_SESSION['onemoreparsing']){
                @exec("cd $path && pdflatex -draftmode $shell_escape -halt-on-error $filename.tex" , $sortida, $return);
            }
            @exec("cd $path && pdflatex $shell_escape -halt-on-error $filename.tex" , $sortida, $return);
        }
        //Si pdflatex no está instalado localmente, probaremos ejecutarlo en otro servidor con una conexión ssh remota
        else {
            $destino = mediaFN(str_replace("_", ":", $this->cfgExport->id));
            $moreparsing = ($_SESSION['onemoreparsing']) ? 1 : 0;
            @exec(DOKU_INC."../sh/remoteSSHexport.sh $path $filename $destino $moreparsing $shell_escape", $sortida, $return);
        }

        if ($return !== 0){
            $this->getLogError($path, $filename, $result);
        }else{
            $this->returnData($path, "$filename.pdf", "pdf", $result);
        }
    }

    /**
     * Returns pdf/zip file info
     * @param string $path, $filename, $type
     */
    private function returnData($path, $filename, $type, &$result=NULL){

        if (file_exists("$path/$filename")){
            $error = '';
            //Return pdf number pages
            if ($type === 'pdf'){
                $num_pages = @exec("pdfinfo $path/$filename | awk '/Pages/ {print $2}'");
            }
            $filesize = filesize_h(filesize("$path/$filename"));
            $ns = str_replace("_", ":", $this->cfgExport->id);
            $mediadir = mediaFN($ns);
            if (!file_exists($mediadir)) mkdir($mediadir, 0755, TRUE);

            $filename_dest = ($this->log || $this->cfgExport->permissionToExport) ? $filename : basename($filename, ".$type")."_draft.$type";
            //Replace log extension to txt, and show where error is
            if ($type === 'log'){
                $filename_dest = preg_replace('/\.log$/', '.txt', $filename_dest, 1);
                $error = io_grep("$path/$filename", '/^!/', 1);
                $line = io_grep("$path/$filename", '/^l.\d+/', 1);
                preg_match('/\d+/', $line[0], $matches);
                $error = preg_replace('/!/', '('.$matches[0].') ', $error);
            }
            copy("$path/$filename", "$mediadir/$filename_dest");

            $time_end = microtime(TRUE);
            $time = round($time_end - $this->time_start, 2);
            setlocale(LC_TIME, 'ca_ES.utf8');
            $dateFile = strftime("%e %B %Y %T", filemtime("$path/$filename"));

            if ($this->log){
                if ($type === 'log') $num_pages = 'E';
                $result = array('time'=>$dateFile, 'path'=>"$ns:$filename_dest", 'pages'=>$num_pages, 'size'=>$filesize);
            }else{
                if ($type === 'pdf'){
                    $result = array($type, "$ns:$filename_dest&time=".gettimeofday(TRUE), $filename_dest, $filesize, $num_pages, $time, $dateFile);
                }else{
                    $result = array($type, "$ns:$filename_dest&time=".gettimeofday(TRUE), $filename_dest, $filesize, $time, $error, $dateFile);
                }
            }
        }else{
            $dateFile = strftime("%e %B %Y %T", filemtime($path.'/'.$filename));
            $fname_dest = preg_replace('/\.log$/', '.txt', $filename, 1);
            $fsize = filesize_h(filesize("$path/$filename"));
            $result = array('time' => $dateFile, 'path' => $this->cfgExport->id.":".$fname_dest, 'pages' => "E", 'size' => $fsize, 'description'=> "Error en la creació del arixu: $fname_dest");
        }
        return $result;
    }

    /**
     * Returns log file on latex compilation
     * @param string $path, $filename
     */
    private function getLogError($path, $filename, &$return=array()){
        $output = array();

        if ($this->log || auth_isadmin()){
            $result = $this->returnData($path, "$filename.log", 'log', $return);
        }else{
            @exec("tail -n 20 $path/$filename.log;", $output);
            io_saveFile("$path/filename.log", implode(DOKU_LF, $output));
            $result = $this->returnData($path, "$filename.log", "log", $return);
        }
        return $result;
    }

    /**
     * Create a zip file with tex file and all media files
     * @param string $filename, $path, $text
     */
    private function createZip($filename, $path, $text){

        $zip = new ZipArchive;
        $res = $zip->open("$path/$filename.zip", ZipArchive::CREATE);
        if ($res === TRUE) {
            $zip->addFromString("$filename.tex", $text);
            $zip->addEmptyDir('media');
            $files = array();
            if ($this->getFiles("$path/media", $files)) {
                foreach($files as $f){
                    $zip->addFile($f, 'media/'.basename($f));
                }
                $zip->close();
                $result = $this->returnData($path, "$filename.zip", 'zip');
            }else {
                $res = FALSE;
            }
        }

        if ($res !== TRUE) {
            $zip->close();
            $result = $this->getLogError($filename);
        }

        return $result;
    }

    /**
     * Fill files var with all media files stored on directory var
     * @param string $directory, $files
     */
    private function getFiles($directory, &$files){
        if (!file_exists($directory) || !is_dir($directory) || !is_readable($directory)) {
            return FALSE;
        } else {
            $dh = opendir($directory);
            while ($contents = readdir($dh)) {
                if ($contents != '.' && $contents != '..') {
                    //Extensions allowed
                    if (preg_match('/.*?\.pdf|.*?\.png|.*?\.jpg|.*?\.gif/', $contents)){
                        $path = $directory . "/" . $contents;
                        if (!is_dir($path)) {
                            array_push($files, $path);
                        }
                    }
                }
            }
            closedir($dh);
            return TRUE;
        }
    }
}
