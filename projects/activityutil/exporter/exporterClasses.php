<?php
/**
 * projecte 'activityutil'
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class renderFileToPsDom extends BasicRenderFileToPsDom {

    public function process($arrayDocs, $alias="documentPartsPdf") {
        global $plugin_controller;
        $startedHere = $this->preProcessSession();

        foreach ($arrayDocs as $doc) {
            if (preg_match("/{$this->cfgExport->id}/", $doc) != 1){
                $fns = "{$this->cfgExport->id}:$doc";
            }
            $file = wikiFN($fns);
            $text = io_readFile($file);

            $counter = 0;
            $text = preg_replace("/~~USE:WIOCCL~~\n/", "", $text, 1, $counter);
            if ($counter>0){
                $dataSource = $plugin_controller->getCurrentProjectDataSource($this->cfgExport->id, $plugin_controller->getCurrentProject());
                $text = WiocclParser::getValue($text, [], $dataSource);
            }
            $part[] = $text;
        }
        $text = implode(" \n", $part);

        $instructions = p_get_instructions($text);
        $renderData = array();
        try {
            $html = $this->render($instructions, $renderData);
        }catch (Exception $e) {
            throw new Exception($e->getMessage().". En el document: $arrayDocs[0]");
        }
        $this->cfgExport->toc[$alias] = $renderData["tocItems"];
        if ($startedHere) session_destroy();

        return $html;
    }

}
