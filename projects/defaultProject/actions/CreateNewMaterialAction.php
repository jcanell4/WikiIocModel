<?php
/**
 * CreateNewMaterialAction en el projecte 'defaultProject'
 */
if (!defined("DOKU_INC")) die();

class CreateNewMaterialAction extends PageAction {

    protected $action;

    protected function responseProcess() {
        $base_template = "plantilles:sensecommon:cicle:m99";
        $unitat_template = "u9";
        $apartat_template = "a9";
        $template_path = WikiGlobalConfig::getConf('datadir')."/".str_replace(":", "/", $base_template);

        $this->action = $this->modelManager->getActionInstance("CreatePageAction");

        $unitats = json_decode($this->params['unitats'], true);

        foreach ($unitats as $unitat => $apartats) {
            //Copia los archivos de la unidad correspondiente
            $ret = $this->sendFilesToCreate("$template_path/$unitat_template", $this->params[AjaxKeys::KEY_ID].":$unitat", "$base_template:$unitat_template");
            if (isset($ret['alert'])) $response['alert'] = $ret['alert'];

            for ($a=1; $a<=$apartats; $a++) {
                //Copia los archivos de los apartados correspondientes a la unidad
                $ret = $this->sendFilesToCreate("$template_path/$unitat_template/$apartat_template", $this->params[AjaxKeys::KEY_ID].":$unitat:a$a", "$base_template:$unitat_template:$apartat_template");
                if (isset($ret['alert']) && !isset($response['alert'])) $response['alert'] = $ret['alert'];
            }
        }

        //Copia los archivos de la raíz del directorio de plantillas al directorio de destino (módulo)
        $response = $this->sendFilesToCreate($template_path, $this->params[AjaxKeys::KEY_ID], $base_template);

        $id = str_replace(":", "_", $this->params[PageKeys::KEY_ID] . ":htmlindex");
        $info = self::generateInfo("info", "Els materials s'han creat correctament a {$this->params[PageKeys::KEY_ID]}", $id);
        $response['info'] = self::addInfoToInfo($info, $response['info']);

        return $response;
    }

    /**
     * Copia los archivos de plantilla del directorio origen al directorio de destino
     * @param string $src_path : ruta completa del directorio origen de las plantillas
     * @param string $wiki_dest : wiki ruta (relativa a pages) de destino de los archivos
     * @param string $wiki_base : wiki ruta del directorio de las plantillas
     * @return array|string : blanco o, en el caso de htmlindex, array con la respuesta de la Action
     */
    private function sendFilesToCreate($src_path, $wiki_dest, $wiki_base) {
        $ret = "";
        $indice = "htmlindex.txt";

        $files = scandir($src_path);
        if (($k = array_search($indice, $files))) {
            unset($files[$k]);
            $files[] = $indice;
        }

        foreach ($files as $file) {
            if (!is_dir("$src_path/$file")) {
                $file = basename($file, ".txt");
                $params[PageKeys::KEY_ID] = "$wiki_dest:$file";
                $params[PageKeys::KEY_DO] = $this->params[PageKeys::KEY_DO];
                $params[PageKeys::KEY_TEMPLATE] = "$wiki_base:$file";

                try {
                    $response = $this->action->get($params);
                    if ($file === "htmlindex") $ret = $response;
                }catch (Exception $e) {
                    $ret['alert'] = $e->getMessage();
            }
        }
        }
        return $ret;
    }

    protected function runProcess() {
//        $destination_path = WikiGlobalConfig::getConf('datadir')."/".str_replace(":", "/", $this->params[AjaxKeys::KEY_ID]);
//
//        //Sólo se ejecuta si no existe previamente el directorio
//        if (is_dir($destination_path)) {
//            throw new DefaultProjectAlreadyExistsException($this->params[AjaxKeys::KEY_ID]);
//        }
    }

}
