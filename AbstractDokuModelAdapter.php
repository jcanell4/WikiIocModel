<?php
/**
 * Description of AbstractDokuModelAdapter
 *
 * @author Rafael Claver
 */

abstract class AbstractDokuModelAdapter {

    public function __construct() {}
    
    abstract public function isDenied();

}