<?php
/**
 * guiesgesProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class guiesgesProjectModel extends MultiContentFilesProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function generateProject() {
        //0. Obtiene los datos del proyecto
        $ret = $this->getData();   //obtiene la estructura y el contenido del proyecto

        //2. Establece la marca de 'proyecto generado'
        $ret[ProjectKeys::KEY_GENERATED] = $this->projectMetaDataQuery->setProjectGenerated();

        if ($ret[ProjectKeys::KEY_GENERATED]) {
            //3. Otorga, a cada 'person', permisos adecuados sobre el directorio de proyecto y añade shortcut
            $params = $this->buildParamsToPersons($ret['projectMetaData'], NULL);
            $this->modifyACLPageAndShortcutToPerson($params);
        }

        return $ret;
    }

    public function llistaDePlantilles() {
        $pdir = $this->getProjectMetaDataQuery()->getProjectTypeDir()."metadata/plantilles/";
        $scdir = scandir($pdir);
        foreach($scdir as $file){
            if ($file !== '.' && $file !== '..' && substr($file, -4)===".txt") {
                $arrTemplates[] = $this->id.":".substr($file, 0, -4);
            }
        }
        return $arrTemplates;
    }

    /**
     * Canvia el nom dels directoris del projecte, els noms dels fitxers generats amb la base del nom del projecte i
     * les referències a l'antic nom de projecte dins dels fitxers afectats
     * @param string $ns : ns original del projecte
     * @param string $new_name : nou nom pel projecte
     * @param string $persons : noms dels autors i els responsables separats per ","
     */
    public function renameProject($ns, $new_name, $persons) {
        $base_dir = explode(":", $ns);
        $old_name = array_pop($base_dir);
        $base_dir = implode("/", $base_dir);

        $this->projectMetaDataQuery->renameDirNames($base_dir, $old_name, $new_name);
        $this->projectMetaDataQuery->renameRenderGeneratedFiles($base_dir, $old_name, $new_name, [".zip",".pdf"]);
        $this->projectMetaDataQuery->changeOldPathInRevisionFiles($base_dir, $old_name, $new_name);
        $this->projectMetaDataQuery->changeOldPathInContentFiles($base_dir, $old_name, $new_name);
        $this->projectMetaDataQuery->changeOldPathInACLFile($old_name, $new_name);
        $this->projectMetaDataQuery->changeOldPathProjectInShortcutFiles($old_name, $new_name, $persons);

        $new_ns = preg_replace("/:[^:]*$/", ":$new_name", $ns);
        $this->setProjectId($new_ns);
    }

}
