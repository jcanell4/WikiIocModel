<?php
/**
 * Nota tag Syntax Plugin
 *
 * @author     Xavier Garcia <xaviergaro.dev@gmail.com>
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_wikiiocmodel_projects_convocatoriesoficialseoi_eoimaptable extends DokuWiki_Syntax_Plugin {

    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Xavier Garcia',
            'email'  => 'xaviergaro.dev@gmail.com',
            'date'   => '2019-05-14',
            'name'   => 'EOI table with map',
            'desc'   => 'Plugin to parse table with address and map',
            'url'    => 'http://ioc.gencat.cat/',
        );
    }

    function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled', 'protected', 'paragraphs', 'container');
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'container';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 513;
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<map-table>(?=.*?</map-table>)', $mode, 'plugin_wikiiocmodel_projects_convocatoriesoficialseoi_eoimaptable');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('</map-table>', 'plugin_wikiiocmodel_projects_convocatoriesoficialseoi_eoimaptable');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
        return array($state, $match);
    }

    /**
     * Create output
     */
    function render($mode, Doku_Renderer $renderer, $data)
    {

        list($state, $text) = $data;

        if ($mode === 'wikiiocmodel_psdom') {
            switch ($state) {
                case DOKU_LEXER_ENTER:
                    $node = new EoiMapTableNodeDoc(EoiMapTableNodeDoc::MAP_TABLE);
                    $renderer->getCurrentNode()->addContent($node);
                    $renderer->setCurrentNode($node);
                    break;
                case DOKU_LEXER_UNMATCHED:
                    $instructions = get_latex_instructions($text);
                    //delete document_start and document_end instructions
                    if ($instructions[0][0] === "document_start") {
                        array_shift($instructions);
                        array_pop($instructions);
                    }
                    //delete p_open and p_close instructions
                    if ($instructions[0][0] === "p_open") {
                        array_shift($instructions);
                        array_pop($instructions);
                    }
                    foreach ( $instructions as $instruction ) {
                        call_user_func_array(array(&$renderer, $instruction[0]),$instruction[1]);
                    }
                    break;

                case DOKU_LEXER_EXIT:
                    $renderer->setCurrentNode($renderer->getCurrentNode()->getOwner());
                    break;
            }
            return TRUE;

        }elseif ($mode === 'iocxhtml' || $mode === 'wikiiocmodel_ptxhtml' || $mode === "xhtml") {
//        if ($mode === 'iocxhtml' || $mode === 'wikiiocmodel_ptxhtml') {
//        } elseif ($mode === 'xhtml') {
//            $renderer->doc .= $text;
//        }
            switch ($state) {
                case DOKU_LEXER_ENTER :
                    break;
                case DOKU_LEXER_UNMATCHED :
                    $instructions = get_latex_instructions($text);
                    //delete document_start and document_end instructions
                    if ($instructions[0][0] === "document_start") {
                        array_shift($instructions);
                        array_pop($instructions);
                    }
                    //delete p_open and p_close instructions
                    if ($instructions[0][0] === "p_open") {
                        array_shift($instructions);
                        array_pop($instructions);
                    }
                    $renderer->doc .= '<div class="map-table">';
                    $renderer->doc .= p_latex_render($mode, $instructions, $info);
                    $renderer->doc .= '</div>';
                    break;
                case DOKU_LEXER_EXIT :
                    break;
            }
            return TRUE;

        }

        return FALSE;
    }
}
