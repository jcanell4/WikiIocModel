<?php

/** 
 * Component: Project / MetaData
 * Status: @@Test
 * Purposes:
 * - Interface to be implemented by any entity
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */

interface MetaDataEntityInterface
{
    public function getArrayFromModel();
    public function setModelFromArray($arrayEntry);
    public function checkFilter($filter);
    public function updateMetaDataValue($paramMetaDataValue);
}



