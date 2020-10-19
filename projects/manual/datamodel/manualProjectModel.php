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

}
