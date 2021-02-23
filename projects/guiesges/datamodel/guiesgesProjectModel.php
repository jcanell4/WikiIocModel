<?php
/**
 * guiesgesProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class guiesgesProjectModel extends MultiContentFilesProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;
    }

    /**
     * Canvia el nom dels directoris del projecte, els noms dels fitxers generats amb la base del nom del projecte i
     * les referències a l'antic nom de projecte dins dels fitxers afectats
     * @param string $ns : ns original del projecte
     * @param string $new_name : nou nom pel projecte
     * @param string $persons : noms dels autors i els responsables separats per ","
     */
    public function renameProject($ns, $new_name, $persons) {
        $base_old_dir = explode(":", $ns);
        $old_name = array_pop($base_old_dir);
        $base_old_dir = implode("/", $base_old_dir);

        $this->projectMetaDataQuery->renameDirNames($base_old_dir, $old_name, $base_old_dir, $new_name);
        $this->projectMetaDataQuery->renameRenderGeneratedFiles("$base_old_dir/$old_name", "$base_old_dir/$new_name", ["extension","\.zip","\.pdf"]);
        $this->projectMetaDataQuery->changeOldPathInRevisionFiles($base_old_dir, $old_name, $base_old_dir, $new_name);
        $this->projectMetaDataQuery->changeOldPathInContentFiles($base_old_dir, $old_name, $base_old_dir, $new_name);
        $this->projectMetaDataQuery->changeOldPathInACLFile($base_old_dir, $old_name, $base_old_dir, $new_name);
        $this->projectMetaDataQuery->changeOldPathProjectInShortcutFiles($old_name, $new_name, $persons);

        $new_ns = preg_replace("/:[^:]*$/", ":$new_name", $ns);
        $this->setProjectId($new_ns);
    }

}
