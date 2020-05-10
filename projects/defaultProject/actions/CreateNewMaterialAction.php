<?php
/**
 * CreateNewMaterialAction en el projecte 'defaultProject'
 */
if (!defined("DOKU_INC")) die();

class CreateNewMaterialAction extends PageAction {

    protected function responseProcess() {

        $w_template = "plantilles:sensecommon:cicle:m99";
        $template_path = WikiGlobalConfig::getConf('datadir')."/".str_replace(":", "/", $w_template);
        $destination_path = WikiGlobalConfig::getConf('datadir')."/".str_replace(":", "/", $this->params[AjaxKeys::KEY_ID]);

        //Sólo se ejecuta si no existe previamente el directorio
        if (!is_file($destination_path)) {
            $unitats = json_decode($this->params['unitats'], true);

            foreach ($unitats as $unitat => $apartats) {
                //Copia los archivos de la raíz del directorio de plantillas al directorio de destino
                mkdir("$destination_path");
                $ok = $this->copyDirFiles($template_path, $destination_path);

                //Copia de la unidad correspondiente
                mkdir("$destination_path/$unitat");
                $ok &= $this->copyDirFiles("$template_path/u1", "$destination_path/$unitat");

                for ($a=1; $a<=$apartats; $a++) {
                    //Copia de los apartados correspondientes a la unidad
                    mkdir("$destination_path/$unitat/a$a");
                    $ok = $this->copyDirFiles("$template_path/u1/a1", "$destination_path/$unitat/a$a");
                }
            }

            $idIndex = $this->params[PageKeys::KEY_ID] . ":htmlindex";
            $params[PageKeys::KEY_ID] = $idIndex;
            $params[PageKeys::KEY_DO] = $this->params[PageKeys::KEY_DO];
            $params[PageKeys::KEY_TEMPLATE] = "$w_template:htmlindex";

            $action = $this->modelManager->getActionInstance("CreatePageAction");
            $response = $action->get($params);

            $id = str_replace(":", "_", $idIndex);
            $info = self::generateInfo("info", "Els materials s'han creat correctament a {$this->params[PageKeys::KEY_ID]}", $id);
            $response['info'] = self::addInfoToInfo($info, $response['info']);
            if (!$ok) {
                $info = self::generateInfo("info", "S'ha produit algún error en la còpia de fitxers", $id);
                $response['info'] = self::addInfoToInfo($response['info'], $info);
            }
        }else {
            throw new Exception;
        }

        return $response;
    }

    /** @override */
    public function get($paramsArr=array()) {
        $this->params = $paramsArr;
        return $this->responseProcess();
    }

    /**
     * Copia los archivos del directorio origen al directorio de destino
     */
    private function copyDirFiles($src, $dest) {
        $ret = true;
        $files = scandir($src);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $ret &= copy("$src/$file", "$dest/$file");
            }
        }
        return $ret;
    }

    protected function runProcess() {}

}
