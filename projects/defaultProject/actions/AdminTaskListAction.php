<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once (DOKU_INC . 'inc/pluginutils.php');
require_once (DOKU_INC . 'inc/actions.php');
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/AdminTaskAction.php";
require_once WikiGlobalConfig::tplIncDir()."conf/cfgIdConstants.php";

/**
 * Description of AdminTaskListAction
 * @author josep
 */
class AdminTaskListAction extends AdminTaskAction {
    private $pageToSend;

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess(){
        $ACT = act_permcheck( $ACT );
        $this->pageToSend = $this->getAdminTaskListHtml();
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet generar la resposta a enviar al client. Aquest
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut
     * DokuAction#response.
     */
    protected function responseProcess(){
        $ret = $this->getCommonPage($id, WikiIocLangManager::getLang('btn_admin'), $this->pageToSend);
        return $ret;
    }

    private function getAdminTaskListHtml() {
            global $conf;

            ob_start();
            trigger_event( 'TPL_ACT_RENDER', $ACT );

            // build menu of admin functions from the plugins that handle them
            $pluginlist = plugin_list( 'admin' );
            $menu       = array();
            foreach ( $pluginlist as $p ) {
                    if ( $obj =& plugin_load( 'admin', $p ) === NULL ) {
                            continue;
                    }

                    // check permissions
                    if ( $obj->forAdminOnly() && !WikiIocInfoManager::getInfo('isadmin')) {
                            continue;
                    }

                    $menu[ $p ] = array(
                            'plugin' => $p,
                            'prompt' => $obj->getMenuText( $conf['lang'] ),
                            'sort'   => $obj->getMenuSort()
                    );
            }

            // Admin Tasks
            if ( count( $menu ) ) {
                    usort( $menu, 'p_sort_modes' );
                    // output the menu
                    ptln( '<div class="clearer"></div>' );
                    print p_locale_xhtml( 'adminplugins' );
                    ptln( '<ul>' );
                    foreach ( $menu as $item ) {
                            if ( ! $item['prompt'] ) {
                                    continue;
                            }
                            ptln( '  <li><div class="li"><a href="' . DOKU_BASE . DOKU_SCRIPT . '?'
                                  . 'do=admin&amp;page=' . $item['plugin'] . '">' . $item['prompt']
                                  . '</a></div></li>' );
                    }
                    ptln( '</ul>' );
            }

            $html_output = ob_get_clean();
            ob_start();
            trigger_event('TPL_CONTENT_DISPLAY', $html_output, 'ptln');
            $html_output = ob_get_clean();


            return $html_output;
    }
}
