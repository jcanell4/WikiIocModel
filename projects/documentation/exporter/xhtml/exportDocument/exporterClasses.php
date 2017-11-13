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
        $result=array();
        $output_filename = str_replace(':','_',$this->cfgExport->id);
        if (!file_exists($this->cfgExport->tmp_dir)) {
            mkdir($this->cfgExport->tmp_dir, 0775, TRUE);
        }
        $zip = new ZipArchive;
        $zipPath = $this->cfgExport->tmp_dir."/$output_filename.zip";
        $res = $zip->open($zipPath, ZipArchive::CREATE);
        if ($res === TRUE) {
            $tmplt = $this->loadTemplateFile("xhtml/exportDocument/index.html");
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
                $toc .= "<li><a href=\'{$elem['link']}\'>".htmlentities($elem['title'])."</a></li>\n";
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

            if ($zip->addFromString('index.html', $document)) {
                $result["zipFile"] = $zipPath;
                $result["zipName"] = $output_filename.".zip";
                $result["info"] = "fitxer {$result['zipName']} creat correctement";
            }else{
                throw new Exception ("Error en la creació del fitxer zip");
            }
        }else{
            $result['error'] = true;
            $result['info'] = $this->cfgExport->lang['nozipfile'];
        }
        $zip->addEmptyDir("css");
        $zip->addFile("{$this->RUTA_RENDERER}/xhtml/exportDocument/documentation.css", "css/documentation.css");
        $zip->close();
        return $result;
    }
}

class render_title extends renderField {
    public function process($data) {
        $ret = parent::process($data);
        $ret = "<h1 class='title'>$ret</h1>";
        return $ret;
    }
}
