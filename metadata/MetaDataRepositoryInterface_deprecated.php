<?php

/** 
 * Component: Project / MetaData
 * Status: @@Test
 * Purposes:
 * - Interface to be implemented by any repository
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */

interface MetaDataRepositoryInterface
{
    public function getMeta($MetaDataRequestMessage);
    public function setMeta($MetaDataEntity, $MetaDataRequestMessage);
}



