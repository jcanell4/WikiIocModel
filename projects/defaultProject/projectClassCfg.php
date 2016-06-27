<?php
/**
 * Valors de les classes per defecte del projecte defaultProject
 *
 * @culpable Rafael Claver
  */
if (!defined("DOKU_INC")) die();

class projectClassCfg {

    const DEF = DOKU_INC.'lib/plugins/wikiiocmodel/projects/defaultProject/';
    static $cfg = array (
                        "default" => array (
                                        "Action" => array (
                                                        projectClassCfg::DEF."actions"
                                                       ,projectClassCfg::DEF."actions/extra"
                                                    )
                                       ,"Authorization" => array (
                                                            projectClassCfg::DEF."authorization"
                                                           )
                                       ,"Model" => array (
                                                    projectClassCfg::DEF."datamodel"
                                                   )
                                     )
                       ,"class" => array (
                                        "Action" => array (
                                                        "actions"
                                                       ,"actions/extra"
                                                    )
                                       ,"Authorization" => array (
                                                            "authorization"
                                                           )
                                       ,"Model" => array (
                                                    "datamodel"
                                                   )
                                   )
    );

    public function getDefaultClassDir($name) {
        return projectClassCfg::$cfg["default"][$name];
    }

    public function getClassDir($name) {
        return projectClassCfg::$cfg["class"][$name];
    }
}