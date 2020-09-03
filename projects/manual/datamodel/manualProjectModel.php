<?php
/**
 * manualProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

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
