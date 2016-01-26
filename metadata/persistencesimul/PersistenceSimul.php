<?php

/**
 * Component: Project / MetaData
 * Status: @@Development
 * Purposes:
 * - Simulació del component PERSISTENCE mentre no es pugui fer una crida real a aquest component
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
class PersistenceSimul {

    private static $retornNsConfig = '{"metaDataClassesNameSpaces":
            {
            "MetaDataRepository": "default", 
            "MetaDataDAO": "default",
            "MetaDataEntity": "default",
            "MetaDataRender": "default"
            }
        }';
    
    private static $retornNsProject = '{"fp:dam:m03":"materials","fp:daw:m07":"materials"}';
    

    public static function getMetaDataConfig($projectType, $metaDataSubset, $configSubSet) {
        return self::$retornNsConfig;
        
    }
    
    //Retorn → JSON {ns1:projectType1, …, nsm:projectTypem}
    public static function getMetaDataElementsKey($nsRoot){
        return self::$retornNsProject;
    }
    

}
