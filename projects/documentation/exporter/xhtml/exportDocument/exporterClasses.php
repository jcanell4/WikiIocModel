<?php
/**
 * renderDocument: clase que renderiza grupos de elementos
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL."projects/documentation/");

class exportDocument extends MainRender {

    public function __construct($factory, $typedef, $renderdef, $params=NULL) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->initParams($params);
    }

    public function initParams($params=NULL){
        @set_time_limit(240);
        $this->time_start = microtime(TRUE);
        $this->cfgExport->langDir = dirname(__FILE__)."/lang/";
        if($params){
            $this->cfgExport->id = $params['id'];
            $this->cfgExport->lang = (!isset($params['ioclanguage']))?'ca':strtolower($params['ioclanguage']);
            $this->cfgExport->lang = preg_replace('/\n/', '', $this->cfgExport->lang);
            $this->log = isset($params['log']);
        }
        $this->export_html = TRUE;
        parent::initParams();
    }

    public function cocinandoLaPlantillaConDatos($data) {
        $result = array();
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }
        $output_filename = str_replace(':','_',$this->cfgExport->id);
        $pathTemplate = "xhtml/exportDocument/templates";

        $zip = new ZipArchive;
        $zipFile = $this->cfgExport->tmp_dir."/$output_filename.zip";
        $res = $zip->open($zipFile, ZipArchive::CREATE);

        if ($res === TRUE) {
            $document = $this->replaceInTemplate($data, "$pathTemplate/index.html");

            $zip->addEmptyDir("html");
            $zip->addEmptyDir("media");
            if ($zip->addFromString('html/index.html', $document)) {
                $pathTemplate = $this->rendererPath . "/$pathTemplate";
                $this->addFilesToZip($zip, $pathTemplate, "html/", "_/css");
                $this->addFilesToZip($zip, $pathTemplate, "html/", "_/js");
                $this->addFilesToZip($zip, $pathTemplate, "html/", "img");
                $this->attachMediaFiles($zip);

                $result["zipFile"] = $zipFile;
                $result["zipName"] = "$output_filename.zip";
                $result["info"] = "fitxer {$result['zipName']} creat correctement";
            }else{
                $result['error'] = true;
                $result['info'] = $this->cfgExport->aLang['nozipfile'];
                throw new Exception ("Error en la creació del fitxer zip");
            }
            if (!$zip->close()) {
                $result['error'] = true;
                $result['info'] = $this->cfgExport->aLang['nozipfile'];
            }
        }else{
            $result['error'] = true;
            $result['info'] = $this->cfgExport->aLang['nozipfile'];
        }
        return $result;
    }

    private function replaceInTemplate($data, $file) {
        $tmplt = $this->loadTemplateFile($file);
        $t = preg_replace("/<!--([^\[].*)(.*\s*)*[^<!\]]-->/U", "", $tmplt);
        if ($t) $tmplt = $t;

        $IocMetaInfo = "<ul>
              <li><strong>Responsable</strong></li>
              <li>{$data['responsable']}</li>
              <li><strong>".htmlentities("Redacció")."</strong></li>
              <li>{$data['autor']}</li>
          </ul>";
        if ($data['IocMetaBC_1']) {
            $IocMetaBC = "<ul>
                  <li>{$data['IocMetaBC_1']}</li>
                  <li><strong>{$data['IocMetaBC_2']}</strong></li>
              </ul>";
        }else {
            $IocMetaBC = "<ul>
                  <li>".htmlentities("Direcció acadèmica de Cicles Formatius")."</li>
                  <li><strong>".htmlentities("Estudis de Sanitat")."</strong></li>
              </ul>";
        }
        if ($data['IocMetaBR_1']) {
            $IocMetaBR = "{$data['IocMetaBR_1']}: <strong>{$data['IocMetaBC_2']}</strong>";
        }else {
            $IocMetaBR = htmlentities("Primera edició").": <strong>novembre 2017</strong>";
        }
        $toc = "";
        foreach ($this->cfgExport->toc as $elem) {
            $toc .= "<li><a href='{$elem['link']}'>".htmlentities($elem['title'])."</a></li>\n";
        }

        $aSearch = array('@IOCHEADDOCUMENT@',
                         '@IOCTITLEDOCUMENT@',
                         '@IOCSTART@',
                         '@IOCMETAINFO@',
                         '@IOCMETABC@',
                         '@IOCMETABR@',
                         '@IOCHEADTOC@',
                         '@IOCTOC@',
                         '@CONTINGUTS_VALUE@'
                    );
        $aReplace = array(strip_tags($data['titol']),
                          $data['titol'],
                          $this->cfgExport->aLang['start'],
                          $IocMetaInfo,
                          $IocMetaBC,
                          $IocMetaBR,
                          $this->cfgExport->aLang['index'],
                          $toc,
                          $data['fitxercontinguts']
                    );
        $document = str_replace($aSearch, $aReplace, $tmplt);
        return $document;
    }

    private function attachMediaFiles(&$zip) {
        global $conf;
        //Attach media files
        foreach($this->cfgExport->media_files as $f){
            resolve_mediaid(getNS($f), $f, $exists);
            if ($exists) {
                //eliminamos el primer nivel del ns
                $arr = explode(":", $f);
                array_shift($arr);
                $zip->addFile(mediaFN($f), 'html/img/'.implode("/", $arr));
            }
        }
        $this->cfgExport->media_files = array();

        //Attach latex files
        foreach($this->cfgExport->latex_images as $f){
            if (file_exists($f)) $zip->addFile($f, 'html/img/'.basename($f));
        }
        $this->cfgExport->latex_images = array();

        //Attach graphviz files
        foreach($this->cfgExport->graphviz_images as $f){
            if (file_exists($f)) $zip->addFile($f, 'html/img/'.basename($f));
        }
        $this->cfgExport->graphviz_images = array();

        //Attach gif (png, jpg, etc) files
        foreach($this->cfgExport->gif_images as $m){
            if (file_exists(mediaFN($m))) $zip->addFile(mediaFN($m), "media/". str_replace(":", "/", $m));
        }
        $this->cfgExport->gif_images = array();

        session_destroy();
        if (!$conf['plugin']['iocexportl']['saveWorkDir']){
            $this->removeDir($this->cfgExport->tmp_dir);
        }
    }

    private function addFilesToZip(&$zip, $base, $d, $dir) {
        $zip->addEmptyDir("$d$dir");
        $files = $this->getDirFiles("$base/$dir");
        foreach($files as $f){
            $zip->addFile($f, "$d$dir/".basename($f));
        }
    }

    /**
     * Fill files var with all media files stored on directory var
     * @param string $directory
     * @param string $files
     */
    private function getDirFiles($dir){
        $files = array();
        if (file_exists($dir) && is_dir($dir) && is_readable($dir)) {
            $dh = opendir($dir);
            while ($file = readdir($dh)) {
                if ($file != '.' && $file != '..' && !is_dir("$dir/$file")) {
                    if (preg_match('/.*?\.pdf|.*?\.png|.*?\.jpg|.*?\.gif|.*?\.ico|.*?\.css|.*?\.js/', $file)){
                        array_push($files, "$dir/$file");
                    }
                }
            }
            closedir($dh);
        }
        return $files;
    }

    /**
     * Remove specified dir
     * @param string $directory
     */
    private function removeDir($directory) {
        if (!file_exists($directory) || !is_dir($directory) || !is_readable($directory)) {
            return FALSE;
        }else {
            $dh = opendir($directory);
            while ($contents = readdir($dh)) {
                if ($contents != '.' && $contents != '..') {
                    $path = "$directory/$contents";
                    if (is_dir($path)) {
                        $this->removeDir($path);
                    }else {
                        unlink($path);
                    }
                }
            }
            closedir($dh);

            if (file_exists($directory)) {
                if (!rmdir($directory)) {
                    return FALSE;
                }
            }
            return TRUE;
        }
    }
}

class render_title extends renderField {
    public function process($data) {
        $ret = parent::process($data);
        return $ret;
    }
}
