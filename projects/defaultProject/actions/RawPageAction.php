<?php


if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/actions.php');
require_once (DOKU_INC . 'inc/template.php');
require_once DOKU_PLUGIN."ownInit/WikiGlobalConfig.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/PageAction.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";
require_once DOKU_PLUGIN."ajaxcommand/requestparams/PageKeys.php";

if (!defined('DW_ACT_EDIT'))     define('DW_ACT_EDIT', "edit");
if (!defined('DW_ACT_DENIED'))   define('DW_ACT_DENIED', "denied");
if (!defined('DW_DEFAULT_PAGE')) define('DW_DEFAULT_PAGE', "start");

/**
 * Description of RawPageAction
 *
 * @author josep
 */
class RawPageAction extends PageAction{
    //protected $draftQuery;
    
    public function __construct(/*BasicPersistenceEngine*/ $engine) {
        parent::__construct($engine);
        //$this->draftQuery = $engine->createDraftDataQuery();
        $this->defaultDo = DW_ACT_EDIT;
    }
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles 
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess(){
        global $ACT;
        global $ID;
        
        if (!WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_EXISTS)) {
            throw new PageNotFoundException($ID, WikiIocLangManager::getLang('pageNotFound'));
        }

        $ACT = act_edit( $ACT );
        $ACT = act_permcheck( $ACT );
        
        if($ACT == DW_ACT_DENIED){
            throw new InsufficientPermissionToEditPageException($ID); 
        }
    }
    
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet generar la resposta a enviar al client. Aquest 
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut 
     * DokuAction#response.
     */
    protected function responseProcess(){   
        $pageToSend = $this->cleanResponse( $this->_getCodePage() );

        $resp         = $this->getContentPage( $pageToSend["content"] );
        $resp['meta'] = $pageToSend['meta'];

        $infoType = 'info';

        if ( WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_LOCKED)) {
                $infoType           = 'error';
                $pageToSend['info'] = $lang['lockedby'].' '.WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_LOCKED);
        }

        $resp['info'] = self::generateInfo( $infoType, $pageToSend['info'] );
        $resp[WikiIocInfoManager::KEY_LOCKED] = WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_LOCKED);
        
         if ($this->params[PageKeys::KEY_RECOVER_DRAFT] != NULL) {
            $resp['recover_draft'] = $this->params[PageKeys::KEY_RECOVER_DRAFT];

            if ($this->params[PageKeys::KEY_RECOVER_DRAFT] == 'true') {
                $info = $this->generateInfo("warning", $lang['draft_editing']);

                if (array_key_exists('info', $resp)) {
                    $info = $this->addInfoToInfo($resp['info'], $info);
                }

                $resp["info"] = $info;
            }

        }

        return $resp;
    }
    
    protected function getContentPage( $pageToSend ) {
            global $REV;
            global $lang;

            $pageTitle = tpl_pagetitle( $this->params[PageKeys::KEY_ID], TRUE );

            $pattern    = '/^.*Aquesta és una revisió.*<hr \/>\\n\\n/mis';
            $count      = 0;
            $info       = NULL;
            $pageToSend = preg_replace( $pattern, '', $pageToSend, - 1, $count );

            if ( $count > 0 ) {
                    $info = self::generateInfo( "warning", 
                                $lang['document_revision_loaded'] . ' <b>' . WikiPageSystemManager::extractDateFromRevision( $REV, self::$SHORT_FORMAT ) . '</b>' 
                                , $this->params[PageKeys::KEY_ID]);
            }

            $id          = $this->params[PageKeys::KEY_ID];
            $contentData = array(
                    'id'      => str_replace( ":", "_", $id ),
                    'ns'      => $id,
                    'title'   => $pageTitle,
                    'content' => $pageToSend,
                    'rev'     => $REV,
                    'info'    => $info,
                    'type'    => 'html',
                    'draft'   => $this->getModel()->getDraftAsFull()
            );

            return $contentData;
    }

    private function cleanResponse( $text ) {
            global $lang;

            $pattern = "/^(?:(?!<div class=\"editBox\").)*/s";// Captura tot el contingut abans del div que contindrá l'editor

            preg_match( $pattern, $text, $match );
            $info = $match[0];

            $text = preg_replace( $pattern, "", $text );

            // Eliminem les etiquetes no desitjades
            $pattern = "/<div id=\"size__ctl\".*?<\/div>\\s*/s";
            $text    = preg_replace( $pattern, "", $text );

            // Eliminem les etiquetes no desitjades
            $pattern = "/<div class=\"editButtons\".*?<\/div>\\s*/s";
            $text    = preg_replace( $pattern, "", $text );

            // Copiem el license
            $pattern = "/<div class=\"license\".*?<\/div>\\s*/s";
            preg_match( $pattern, $text, $match );
            $license = $match[0];

            // Eliminem l'etiqueta
            $text = preg_replace( $pattern, "", $text );

            // Copiem el wiki__editbar
            $pattern = "/<div id=\"wiki__editbar\".*?<\/div>\\s*<\/div>\\s*/s";
            preg_match( $pattern, $text, $match );
            $meta = $match[0];

            // Eliminem la etiqueta
            $text = preg_replace( $pattern, "", $text );

            // Capturem el id del formulari
            $pattern = "/<form id=\"(.*?)\"/";
            //$form = "dw__editform";
            preg_match( $pattern, $text, $match );
            $form = $match[1];
            
            $pattern = "/<form id=\"".$form."\"/";
            $replace = "/<form id=\"form_".$this->params[PageKeys::KEY_ID]."\"/";
            $text = preg_replace($pattern, $replace, $text);

            // Afegim el id del formulari als inputs
            $pattern = "/<input/";
            $replace = "<input form=\"form_".$this->params[PageKeys::KEY_ID]. "\"";
            $meta    = preg_replace( $pattern, $replace, $meta );

            // Netegem el valor
            $pattern = "/value=\"string\"/";
            $replace = "value=\"\"";
            $meta    = preg_replace( $pattern, $replace, $meta );

            $response["content"] = $text;
            $response["info"]    = [ $info ];

            if ( $license ) {
                    $response["info"][] = $license;
            }

            $metaId           = str_replace( ":", "_", $this->params[PageKeys::KEY_ID] ) . '_metaEditForm';
            $response["meta"] = [
                    ( $this->getCommonPage( $metaId,
                                            $lang['metaEditForm'],
                                            $meta ) + [ 'type' => 'summary' ] )
            ];

            return $response;
    }
    
    private function _getCodePage() {
            global $ACT;
            ob_start();
            trigger_event( 'TPL_ACT_RENDER', $ACT, array($this, 'onCodeRender') );
            $html_output = ob_get_clean();
            ob_start();
            trigger_event('TPL_CONTENT_DISPLAY', $html_output, 'ptln');
            $html_output = ob_get_clean();

            return $html_output;
    }

    /**
     * Segons el valor de $data activa la edició del document('edit' i 'recover'), la previsualització ('preview') o mostra
     * el missatge de denegat ('denied').
     *
     * @param string $data els valors admessos son 'edit', 'recover', 'preview' i 'denied'
     */
    function onCodeRender( $data ) {
            global $TEXT;

            switch ( $data ) {
                    case WikiIocInfoManager::KEY_LOCKED:
                    case 'edit':
                    case 'recover':
                            html_edit();
                            break;
                    case 'preview':
                            html_edit();
                            html_show( $TEXT );
                            break;
                    case 'denied':
                            print p_locale_xhtml( 'denied' );
                            break;
            }
    }
}