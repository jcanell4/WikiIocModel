<?php
/**
 * Description of AbstractModelAdapter
 *
 * @author Rafael Claver
 */

//[TO DO josep]: Reanomenar a AbstractModelAdapter
abstract class AbstractModelAdapter {

    public function __construct() {}
    
    abstract public function isDenied();

}