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
require_once(DOKU_INC . 'lib/plugins/wikiiocmodel/renderer/psdom.php');

class EoiBlockNodeDoc extends StructuredNodeDoc
{
    const BLOCK = "block";

    public function __construct($type)
    {
        parent::__construct($type);
    }

}

class EoiMapTableNodeDoc extends StructuredNodeDoc
{
    const MAP_TABLE = "map-table";

    public function __construct($type)
    {
        parent::__construct($type);
    }

}

class ImgResourcePrjNodeDoc extends ImageNodeDoc
{
    const IMG_RESOURCE_PRJ = "img-resource-prj";

    // TODO[Xavi]: En lloc de fer servir una mida fixa extreurela de la etiqueta <img-resource-prj>
    public function __construct($height = NULL, $width=NULL, $align=NULL)
    {
        parent::__construct('', NULL, $align, $width, $height, NULL, NULL);

        $this->type = self::IMG_RESOURCE_PRJ;
    }

    public function setSource($src)
    {
        $this->src = $src;
    }

}

class syntax_plugin_wikiiocmodel_projects_convocatoriesoficialseoi_eoiblock extends DokuWiki_Syntax_Plugin {

    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Xavier Garcia',
            'email'  => 'xaviergaro.dev@gmail.com',
            'date'   => '2019-05-14',
            'name'   => 'EOI block',
            'desc'   => 'Plugin to parse eoi blocks',
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
        $this->Lexer->addEntryPattern('<block>(?=.*?</block>)', $mode, 'plugin_wikiiocmodel_projects_convocatoriesoficialseoi_eoiblock');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('</block>', 'plugin_wikiiocmodel_projects_convocatoriesoficialseoi_eoiblock');
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
                    $node = new EoiBlockNodeDoc(EoiBlockNodeDoc::BLOCK);
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

        }elseif ($mode === 'xhtml' || $mode === 'iocxhtml' || $mode === 'wikiiocmodel_ptxhtml') {
//        if ($mode === 'iocxhtml' || $mode === 'wikiiocmodel_ptxhtml') {
//        } elseif ($mode === 'xhtml') {
//            $renderer->doc .= $text;
//        }
            switch ($state) {
                case DOKU_LEXER_ENTER :
                    $renderer->doc .= '<div class="block-border">';
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
//                    $renderer->doc .= '<div class="block-border">';
                    if($mode==='xhtml'){
                        $renderer->doc .= p_render($mode, $instructions, $info);
                    }else{
                        $renderer->doc .= p_latex_render($mode, $instructions, $info);
                    }
//                    $renderer->doc .= '</div>';
                    break;
                case DOKU_LEXER_EXIT :
                    $renderer->doc .= '</div>';
                    break;
            }
            return TRUE;

        }

        return FALSE;
    }
}
