<?php
/**
 * upgrader_2: Transforma el archivo continguts.txt de los proyectos 'prgfploe'
 *             desde la versión 1 a la versión 2
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_2 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión 1 a la versión 2
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                $dataProject['estrategiesMetodologiques'] = preg_replace("/<p>(\s*&(amp;)*lt;p&(amp;)*gt;)*\s*(.*?)(\s*&(amp;)*lt;\/p&(amp;)*gt;)*\s*<\/p>/s", "$4", $dataProject['estrategiesMetodologiques']);

                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver. Simultànea a l'actualització de 18 a 19 de templates", '{"fields":'.$ver.'}');
                break;

            case "templates":
                // Actualiza la versión del documento establecido en el sistema de calidad del IOC (Visible en el pie del documento)
                // Sólo se debe actualizar si el coordinador de calidad lo indica!!!!!!
                /*
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject))
                    $dataProject = json_decode($dataProject, TRUE);
                $dataProject['documentVersion'] = $dataProject['documentVersion']+1;
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                */
                $ret = TRUE;
                break;
        }
        return $ret;
    }

}
