<?php
/**
 * manualProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');
require_once (WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");

class manualProjectModel extends AbstractProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function generateProject() {} //abstract obligatorio

    public function directGenerateProject($data) {
        //[TODO: Rafael] De momento, esto lo hace el padre durante la Creación
        //2. Otorga, a las Persons, permisos sobre el directorio de proyecto y 3. añade enlace a dreceres
//        $params = $this->buildParamsToPersons($data['projectMetaData'], NULL);
//        $this->modifyACLPageAndShortcutToPerson($params);

        //4. Establece la marca de 'proyecto generado'
        $ret = $this->projectMetaDataQuery->setProjectGenerated();

        return $ret;
    }

    /**
     * Canvia el nom dels directoris del projecte indicat,
     * els noms dels fitxers generats amb la base del nom del projecte i
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
        $this->projectMetaDataQuery->changeOldPathProjectInRevisionFiles($base_dir, $old_name, $new_name);
        $this->projectMetaDataQuery->changeOldPathProjectInACLFile($old_name, $new_name);
        $this->projectMetaDataQuery->changeOldPathProjectInShortcutFiles($old_name, $new_name, $persons);
        $this->projectMetaDataQuery->renameRenderGeneratedFiles($base_dir, $old_name, $new_name, $this->listGeneratedFilesByRender($base_dir, $old_name));
        $this->projectMetaDataQuery->changeOldPathProjectInContentFiles($base_dir, $old_name, $new_name);

        $new_ns = preg_replace("/:[^:]*$/", ":$new_name", $ns);
        $this->setProjectId($new_ns);
    }

    /**
     * Devuelve la lista de archivos que se generan a partir de la configuración
     * indicada en el archivo 'configRender.json'
     * Esos archivos se guardan en WikiGlobalConfig::getConf('mediadir')
     * El nombre de estos archivos se construyó, en el momento de su creación, usando el nombre del proyecto
     * @param string $base_dir : directori wiki del projecte
     * @param string $old_name : nom actual del projecte
     * @return array : lista de ficheros
     */
    protected function listGeneratedFilesByRender($base_dir, $old_name) {
        $basename = str_replace([":","/"], "_", $base_dir) . "_" . $old_name;
        return [$basename.".zip"];
    }

}
