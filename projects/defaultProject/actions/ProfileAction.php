<?php
/**
 * Description of ProfileAction
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once (DOKU_INC . 'inc/pluginutils.php');
require_once (DOKU_INC . 'inc/actions.php');
require_once (DOKU_PLUGIN.'ajaxcommand/defkeys/AjaxKeys.php');
require_once (DOKU_PLUGIN.'ajaxcommand/defkeys/AdminKeys.php');
require_once (DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuAction.php");

class ProfileAction extends DokuAction{

    private $usrdata = array();

    public function init($modelManager) {
        parent::init($modelManager);
        $this->params[AjaxKeys::KEY_DO] = AjaxKeys::KEY_PROFILE; //"admin";
        $this->defaultDo = AjaxKeys::KEY_PROFILE;
    }

    protected function startProcess(){
        global $ACT, $ID;

        $this->params[AjaxKeys::KEY_ID] = "start";
        $ID = $this->params[AjaxKeys::KEY_ID];
        $ACT = AjaxKeys::KEY_PROFILE;
    }

    protected function runProcess(){
        global $ACT;
        $ACT = act_permcheck( $ACT );

        if ($this->params[AdminKeys::KEY_PAGE]) {
            if ($plugin =& plugin_load('admin', $this->params[AdminKeys::KEY_PAGE]) !== NULL) {
                $plugin->handle();
            }
            $ACT = act_permcheck($ACT);
        }
    }

    protected function responseProcess(){
        $response = array();
        $id = "user_profile";
        $info_time_visible = 5;
        $fn = $_REQUEST['fn'];

        if (isset($fn)) {
            $cmd = is_array($fn) ? key($fn) : $fn;

            if ($cmd === "modify") {
                $this->usrdata = ['userid'  => $this->params['userid'],
                                  'username'=> $this->params['username'],
                                  'usermail'=> $this->params['usermail'],
                                  'oldpass' => $this->params['oldpass']
                                 ];
            }else {
                $this->usrdata = ['userid'  => WikiIocInfoManager::getInfo("client"),
                                  'username'=> WikiIocInfoManager::getInfo("userinfo")['name'],
                                  'usermail'=> WikiIocInfoManager::getInfo("userinfo")['mail'],
                                  'oldpass' => WikiIocInfoManager::getInfo("userinfo")['pass']
                                 ];
            }
            $pageToSend = $this->getHtmlEditProfile();
            $response   = $this->getCommonPage( $id, "El meu perfil", $pageToSend );

            switch ( $cmd ) {
                case "edit"   :
                    $param = WikiIocLangManager::getLang('menu','usermanager');
                    break;
                case "modify":
                    $param = $fn[ key( $fn ) ];
                    break;
            }
            $response['title']  = "El meu perfil";
            $response['info']   = self::generateInfo("info", $param, $id, $info_time_visible );
            $response['iframe'] = TRUE;
        }
        else {
            throw new IncorrectParamsException();
        }

        return $response;
    }

    private function getHtmlEditProfile() {
        global $ACT;
        ob_start();
        trigger_event( 'TPL_ACT_RENDER', $ACT, [$this, "htmlModifyProfile"] );
        $html_output = ob_get_clean();
        return $html_output;
    }

    public function htmlModifyProfile(){
        print p_locale_xhtml('updateprofile');
        ptln("<div class='edit_user'>");
        ptln("<div class='level2'>");
        ptln("<form id='dw__register' action=''>");
        ptln("<div class='no'>");
        ptln("<input name='do' type='hidden' value='profile'>");
        ptln("<input name='save' type='hidden' value='1'>");
        ptln("<input name='page' type='hidden' value='usermanager'>");
        ptln("<input name='userid' type='hidden' value='{$this->usrdata['userid']}'>");
        ptln("<input name='userid_old' type='hidden' value='{$this->usrdata['userid']}'>");
        ptln("</div>");
        ptln("<div class='table'>");
        ptln("<table class='inline'>");
        ptln("<thead><tr><th>Camp</th><th>Valor</th></tr></thead>");
        ptln("<tbody>");
        ptln("<tr>");
        ptln("<td><label for='modify_userid'>".WikiIocLangManager::getLang('user_id','usermanager').": </label></td>");
        ptln("<td><input id='modify_userid' name='user_id' class='edit' type='text' size='50' value='{$this->usrdata['userid']}' disabled='disabled'></td>");
        ptln("</tr><tr>");
        ptln("<td><label for='modify_username'>".WikiIocLangManager::getLang('fullname').": </label></td>");
        ptln("<td><input id='modify_username' name='username' class='edit' type='text' size='50' value='{$this->usrdata['username']}'></td>");
        ptln("</tr><tr>");
        ptln("<td><label for='modify_usermail'>".WikiIocLangManager::getLang('email').": </label></td>");
        ptln("<td><input id='modify_usermail' name='usermail' class='edit' type='text' size='50' value='{$this->usrdata['usermail']}'></td>");
        ptln("</tr><tr><td colspan=2><br /></td></tr><tr>");
        ptln("<thead><tr><th colspan=2>Canvi de contrasenya</th></tr></thead>");
        ptln("</tr><tr>");
        ptln("<td><label for='modify_oldpass'>".WikiIocLangManager::getLang('oldpass').": </label></td>");
        ptln("<td><input id='modify_oldpass' name='oldpass' class='edit' type='password' size='30' value=''></td>");
        ptln("</tr><tr>");
        ptln("<td><label for='modify_pass'>".WikiIocLangManager::getLang('pass').": </label></td>");
        ptln("<td><input id='modify_pass' name='pass' class='edit' type='password' size='30' value=''></td>");
        ptln("</tr><tr>");
        ptln("<td><label for='modify_newpass'>".WikiIocLangManager::getLang('newpass').": </label></td>");
        ptln("<td><input id='modify_newpass' name='newpass' class='edit' type='password' size='30' value=''></td>");
        ptln("</tr></tbody>");
        ptln("<thead><tr><th colspan=2></th></tr></thead>");
        ptln("<tr><td></td><td>");
        ptln("<input name='fn[modify]' class='button' type='submit' value='".WikiIocLangManager::getLang('btn_save')."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
        ptln("<input name='fn[edit][{$this->usrdata['userid']}]' class='button' type='submit' value='Desfés els canvis'>");
        ptln("</td></tr>");
        ptln("</table>");
        ptln("</form>");
        ptln("</div>");
        ptln("</div>");

//        global $INPUT;
//        global $INFO;
//        global $lang;
//        global $conf;
//        global $auth;
//
//        print p_locale_xhtml('updateprofile');
//        $username = $INPUT->post->str('username', $INFO['userinfo']['name'], true);
//        $usermail = $INPUT->post->str('usermail', $INFO['userinfo']['mail'], true);
//
//        $form = new Doku_Form(array('id' => 'dw__register'));
//        $form->startFieldset($lang['profile']);
//        $form->addHidden('do', 'profile');
//        $form->addHidden('save', '1');
//        $form->addElement(form_makeTextField('login', $_SERVER['REMOTE_USER'], $lang['user'], '', 'block', array('size'=>'50', 'disabled'=>'disabled')));
//        $attr = array('size'=>'50');
//        if (!$auth->canDo('modName')) $attr['disabled'] = 'disabled';
//        $form->addElement(form_makeTextField('username', $username, $lang['fullname'], '', 'block', $attr));
//        $attr = array('size'=>'50', 'class'=>'edit');
//        if (!$auth->canDo('modMail')) $attr['disabled'] = 'disabled';
//        $form->addElement(form_makeField('usermail','usermail', $usermail, $lang['email'], '', 'block', $attr));
//        $form->addElement(form_makeTag('br'));
//        if ($conf['profileconfirm']) {
//            $form->addElement(form_makeTag('br'));
//            $form->addElement(form_makePasswordField('oldpass', $lang['oldpass'], '', 'block', array('size'=>'50', 'required' => 'required')));
//        }
//        if ($auth->canDo('modPass')) {
//            $form->addElement(form_makePasswordField('newpass', $lang['newpass'], '', 'block', array('size'=>'50')));
//            $form->addElement(form_makePasswordField('passchk', $lang['passchk'], '', 'block', array('size'=>'50')));
//        }
//        $form->addElement(form_makeButton('submit', '', $lang['btn_save']));
//        $form->addElement(form_makeButton('reset', '', $lang['btn_reset']));
//
//        $form->endFieldset();
//        html_form('updateprofile', $form);
//
//        if ($auth->canDo('delUser') && actionOK('profile_delete')) {
//            $form_profiledelete = new Doku_Form(array('id' => 'dw__profiledelete'));
//            $form_profiledelete->startFieldset($lang['profdeleteuser']);
//            $form_profiledelete->addHidden('do', 'profile_delete');
//            $form_profiledelete->addHidden('delete', '1');
//            $form_profiledelete->addElement(form_makeCheckboxField('confirm_delete', '1', $lang['profconfdelete'],'dw__confirmdelete','', array('required' => 'required')));
//            if ($conf['profileconfirm']) {
//                $form_profiledelete->addElement(form_makeTag('br'));
//                $form_profiledelete->addElement(form_makePasswordField('oldpass', $lang['oldpass'], '', 'block', array('size'=>'50', 'required' => 'required')));
//            }
//            $form_profiledelete->addElement(form_makeButton('submit', '', $lang['btn_deleteuser']));
//            $form_profiledelete->endFieldset();
//
//            html_form('profiledelete', $form_profiledelete);
//        }
//
//        print '</div>'.NL;
    }

}

