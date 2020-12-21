<?php

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../../') . '/');
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_wikiiocmodel_projects_convocatoriesoficialseoi_imgresourceprj extends DokuWiki_Syntax_Plugin
{

    // Data saved to be use on the lexer unmatched stated
    protected $pendingData;


    function getInfo()
    {
        return array(
            'name' => 'IOC image resource project Plugin',
            'desc' => 'Plugin to parse project images',
            'syntax' => '<img-resource-prj>image path in the server</img-resource-prj>',
            'url' => 'http://ioc.gencat.cat/',
        );
    }

    function getType()
    {
        return 'substition';
    }

    function getPType()
    {
        return 'block';
    }

    function getSort()
    {
        return 43;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode)
    {
//        $this->Lexer->addEntryPattern('<img-resource-prj>(?=.*?</img-resource-prj>)', $mode, 'plugin_wikiiocmodel_projects_convocatoriesoficialseoi_imgresourceprj');
        $this->Lexer->addEntryPattern('<img-resource-prj .*?>(?=.*?</img-resource-prj>)', $mode, 'plugin_wikiiocmodel_projects_convocatoriesoficialseoi_imgresourceprj');
    }

    function postConnect()
    {
        $this->Lexer->addExitPattern('</img-resource-prj>', 'plugin_wikiiocmodel_projects_convocatoriesoficialseoi_imgresourceprj');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {
        preg_match('/height="(.*?)"/', $match, $matches);
        $height = $matches[1] ? $matches[1] : NULL;

        preg_match('/width="(.*?)"/', $match, $matches);
        $width = $matches[1] ? $matches[1] : NULL;

        preg_match('/align="(.*?)"/', $match, $matches);
        $align = $matches[1] ? $matches[1] : NULL;


        return [$state, $match, $height, $width, $align];
    }

    function render($mode, Doku_Renderer $renderer, $data)
    {
        global $plugin_controller;

        list($state, $text) = $data;

        if ($mode === 'wikiiocmodel_psdom') {
            switch ($state) {
                case DOKU_LEXER_ENTER:
                    $node = new ImgResourcePrjNodeDoc($data[2], $data[3], $data[4]);
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

                    $src = $plugin_controller->getProjectTypeDir($plugin_controller->getProjectType()) . 'exporter/resources/' . $instructions[0][1][0];
                    $renderer->getCurrentNode()->setSource($src);
                    break;
                case DOKU_LEXER_EXIT:
                    $renderer->setCurrentNode($renderer->getCurrentNode()->getOwner());
                    break;
            }
            return TRUE;

        } elseif ($mode === 'iocxhtml' || $mode === 'xhtml' || $mode === 'wikiiocmodel_ptxhtml') { // el ptxhtml és el de la exportació?
            switch ($state) {
                case DOKU_LEXER_ENTER :
                    $this->pendingData = $data;
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

                    $path = '';
                    if ($mode !== 'wikiiocmodel_ptxhtml') {
                        $path = str_replace(DOKU_INC, '', $plugin_controller->getProjectTypeDir($plugin_controller->getProjectType())) . 'exporter/';
                    }

                    $path .= 'resources/' . $text;
                    $height = $this->pendingData[2] != NULL ? 'height="' . $this->pendingData[2] . '"' : '';
                    $width = $this->pendingData[3] != NULL ? 'width="' . $this->pendingData[3] . '"' : '';
                    $align = $this->pendingData[4] != NULL ? 'align="' . $this->pendingData[4] . '"' : '';

                    $renderer->doc .= '<img class="media" src="' . $path . '" ' . $height . ' ' . $width . ' ' . $align . '/>';
                    break;
                case DOKU_LEXER_EXIT :
                    $this->pendingData = NULL;
                    break;
            }
            return TRUE;

        }

        return FALSE;
    }

}
