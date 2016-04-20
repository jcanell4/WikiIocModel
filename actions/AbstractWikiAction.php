<?php
/**
 * Description of page_response_handler
 *
 * @author Josep Cañellas <jcanell4@ioc.cat>
 */

if (!defined("DOKU_INC")) {
    die();
}

/**
 * Description of AbstractWikiAction
 *
 * @author josep
 */
abstract class AbstractWikiAction {
    public abstract function get(/*Array*/ $paramsArr=array());
}
