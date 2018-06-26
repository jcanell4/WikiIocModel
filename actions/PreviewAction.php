<?php
/**
 * PreviewAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_INC . 'inc/pluginutils.php';
require_once DOKU_INC . 'inc/actions.php';
require_once DOKU_INC . 'inc/html.php';
require_once DOKU_INC . 'inc/parserutils.php';

class PreviewAction extends DokuAction{
    private $info;
    private $html;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->defaultDo = PageKeys::DW_ACT_PREVIEW;
        $this->setRenderer(TRUE);
    }

    protected function startProcess(){
        global $ID, $ACT, $TEXT;

        $ACT = $this->params[PageKeys::KEY_DO] = $this->defaultDo;
        $ACT = act_clean($ACT);

        if (!$this->params[PageKeys::KEY_ID]) {
            $this->params[PageKeys::KEY_ID] = "";
        }
        $ID = $this->params[PageKeys::KEY_ID];
        if ($this->params['text']) {
            $TEXT = $this->params[PageKeys::KEY_TEXT] = $this->params['text'] = cleanText($this->params['text']);
        } elseif ($this->params[PageKeys::KEY_TEXT]) {
            $TEXT = $this->params[PageKeys::KEY_TEXT] = $this->params['text'] = cleanText($this->params[PageKeys::KEY_TEXT]);
        }
    }

    protected function runProcess(){
        $info;
        $this->html = html_secedit(p_render('xhtml', p_get_instructions($this->params[PageKeys::KEY_TEXT]), $info), false);
        $this->info = $info;
    }

    protected function responseProcess(){
        $response = array();
        $response['html'] = $this->prePrint();
        $response['html'] .= $this->html;
        $response['html'] .= $this->postPrint();
        if ($this->info){
            $response['info'] = $this->generateInfo("info", $this->info, $this->params[PageKeys::KEY_ID]);
        }
        return $response;
    }

    private function prePrint(){
        ob_start();
        include DOKU_TPL_INCDIR.'pre_print.php';
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    private function postPrint(){
        ob_start();
        include DOKU_TPL_INCDIR.'post_print.php';
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
}

