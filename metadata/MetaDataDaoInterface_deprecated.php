<?php

/**
 * Component: Project / MetaData
 * Status: @@Test
 * Purposes:
 * - Interface to be implemented by any DAO
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
interface MetaDataDaoInterface {

    public function getMeta($MetaDataRequestMessage);
    public function setMeta($MetaDataEntity, $MetaDataRequestMessage);
    
}
