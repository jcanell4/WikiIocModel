<?php
/**
 * Description of AbstractDokuModelAdapter
 *
 * @author Rafael Claver
 */

//[TO DO josep]: Reanomenar a AbstractModelAdapter
abstract class AbstractDokuModelAdapter {

    public function __construct() {}
    
    abstract public function isDenied();

}