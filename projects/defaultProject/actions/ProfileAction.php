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
        $this->params[AjaxKeys::KEY_DO] = AjaxKeys::KEY_PROFILE;
        //$this->defaultDo = AjaxKeys::KEY_PROFILE;
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
                                  'usermail'=> $this->params['usermail']
                                 ];
            }else {
                $this->usrdata = ['userid'  => WikiIocInfoManager::getInfo("client"),
                                  'username'=> WikiIocInfoManager::getInfo("userinfo")['name'],
                                  'usermail'=> WikiIocInfoManager::getInfo("userinfo")['mail']
                                 ];
            }
            $pageToSend = $this->getHtmlEditProfile();
            $response   = $this->getCommonPage( $id, "El meu perfil", $pageToSend );

            switch ( $cmd ) {
                case "edit"   :
                    $param = WikiIocLangManager::getLang('menu','usermanager');
                    break;
                case "modify":
                    $param = WikiIocLangManager::getLang('update_ok','usermanager');
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

    public function htmlModifyProfile() {
        print p_locale_xhtml('updateprofile');
        ptln("<div class='edit_user'>");
        ptln("<div class='level2'>");
        /*
        $form = new FormInTableTwoColumn("dw__user_profile");
        $form->addHidden( ['do' =>  "profile",
                           'page' => "usermanager",
                           'userid' => $this->usrdata['userid'],
                           'userid_old' => $this->usrdata['userid']
                          ] );
        $form->addHeadElement("descripció","valor");
        $form->addInput(
                ['user_id' =>  array('type'=>"text", 'size'=>"50", 'value'=>$this->usrdata['userid'], 'attr'=>array('disabled'=>"disabled")),
                 'username' =>  array('type'=>"text", 'size'=>"50", 'value'=>$this->usrdata['username']),
                 'usermail' =>  array('type'=>"text", 'size'=>"50", 'value'=>$this->usrdata['usermail'])
                ]);
        $form->addHeadElement("Canvi de contrasenya");
        $form->addInput(
                ['oldpass' =>  array('type'=>"password", 'size'=>"30"),
                 'userpass' =>  array('type'=>"password", 'size'=>"30"),
                 'newpass' =>  array('type'=>"password", 'size'=>"30")
                ]);
        $form->addInputButton(
                ['fn[modify]' => array('class'=>"button", 'type'=>"submit", 'value'=>WikiIocLangManager::getLang('btn_save')),
                 "fn[edit][{$this->usrdata['userid']}]" => array('class'=>"button", 'type'=>"hidden", 'value'=>"Desfés els canvis")
                ]);

        $htmlForm = $form->getFormTag();
        $htmlForm.= $form->getHiddenInputs();
        $htmlForm.= $form->getInitTable($form->getHeadElement(0));
        $htmlForm.= $form->getInputElements(0);
        $htmlForm.= $form->getTrSeparator();
        $htmlForm.= $form->getHeadElement(1);
        $htmlForm.= $form->getInputElements(1);
        $htmlForm.= $form->getEndBody();
        $htmlForm.= $form->getTrSeparator();
        $htmlForm.= $form->getTrBlock($form->getEmptyTd(), $form->getInputButtons(0));
        $htmlForm.= $form->getEndTableFormTag();
        */
        ptln("<form id='dw__register' action=''>");
        ptln("<div class='no'>");
        ptln("<input name='do' type='hidden' value='profile'>");
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
        ptln("<td><label for='modify_userpass'>".WikiIocLangManager::getLang('pass').": </label></td>");
        ptln("<td><input id='modify_userpass' name='userpass' class='edit' type='password' size='30' value=''></td>");
        ptln("</tr><tr>");
        ptln("<td><label for='modify_newpass'>".WikiIocLangManager::getLang('newpass').": </label></td>");
        ptln("<td><input id='modify_newpass' name='newpass' class='edit' type='password' size='30' value=''></td>");
        ptln("</tr></tbody>");
        ptln("<thead><tr><th colspan=2></th></tr></thead>");
        ptln("<tr><td></td><td>");
        ptln("<input name='fn[modify]' class='button' type='submit' value='".WikiIocLangManager::getLang('btn_save')."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
        //ptln("<input name='fn[edit][{$this->usrdata['userid']}]' class='button' type='submit' value='Desfés els canvis'>");
        ptln("</td></tr>");
        ptln("</table>");
        ptln("</form>");
        ptln("</div>");
        ptln("</div>");
    }

}

class FormInTableTwoColumn {
    var $f_attr = array();  //Form ID and other attributes
    var $_hidden = array();
    var $_input = array();
    var $_head = array();
    var $_button = array();

    /**
     * Constructor. Sets form parameters
     * @param mixed  $f_attr  Parameters for the HTML form element
     * @param string $action  submit URL, defaults to current page
     * @param string $method  'POST' or 'GET', default is POST
     * @param string $enctype Encoding type of the data
     */
    function FormInTable($f_attr, $action=false, $method=false, $enctype=false, $charset=false) {
        if (!is_array($f_attr)) {
            $this->f_attr = array('id' => $f_attr);
            if ($action !== false) $this->f_attr['action'] = $action;
            if ($method !== false) $this->f_attr['method'] = strtolower($method);
            if ($enctype !== false) $this->f_attr['enctype'] = $enctype;
            if ($charset !== false) $this->f_attr['accept-charset'] = $charset;
        } else {
            $this->f_attr = $f_attr;
        }
        $this->f_attr['method'] = (!isset($this->f_attr['method'])) ? 'post' : strtolower($this->f_attr['method']);
        if (!isset($this->f_attr['action'])) {$this->f_attr['action'] = '';}
        if (!isset($this->f_attr['accept-charset'])) {$this->f_attr['accept-charset'] = WikiIocLangManager::getLang('encoding');}
    }

    /**
     * Adds a name/value pair as a hidden field.
     * @param mixed   string $name  Field name | array [name, value]
     * @param string  $value  Field value
     */
    function addHidden($name, $value=null) {
        if (is_array($name)) {
            foreach ($name as $k => $v)
                $this->_hidden[$k] = $v;
        }else
            $this->_hidden[$name] = $value;
    }

    /**
     * Adds a name/value pair as a input field.
     * @param mixed   string $name  Field name | array [name, value]
     * @param string  $value  Field value
     */
    function addTagElement($name, $value=null) {
        if (is_array($name)) {
            foreach ($name as $k => $v)
                $input[$k] = $v;
        }else {
            $input[$name] = $value;
        }
        return $input;
    }

    /**
     * Adds a name/value pair as a input field.
     * @param mixed   string $name  Field name | array [name, value]
     * @param string  $value  Field value
     */
    function addInput($name, $value=null) {
        $this->_input[] = $this->addTagElement($name, $value);
    }

    /**
     * Adds a name/value pair as a input button
     * @param mixed   string $name  Field name | array [name, value]
     * @param string  $value  Field value
     */
    function addInputButton($name, $value=null) {
        $this->_button[] = $this->addTagElement($name, $value);
    }

    /**
     * Appends a content element to the form.
     * @param   string  $elem   Pseudo-tag or string to add to the form.
     */
    function addHeadElement($elem_1, $elem_2=null) {
        $ret .= "<tr><thead><tr>";
        if (is_null($elem_2))
            $ret .= "<th colspan=2>{$elem_1}";
        else
            $ret .= "<th>{$elem_1}</th><th>{$elem_2}";

        $ret .= "</th></tr></thead></tr>";
        $this->_head[] = $ret;
    }

    /** Return the FORM tag
     */
    function getFormTag() {
        $form = "<form ".buildAttributes($this->f_attr,false).">".NL;
        return $form;
    }

    /** Return the TABLE inicialization
     */
    function getInitTable($detail_header=null) {
        $ret = "<div class='table'>".NL
             . "<table class='inline'>".NL;
        if ($detail_header)
            $ret.= $detail_header;
        $ret.= "<tbody>".NL;
        return $ret;
    }

    /** Return the en TABLE FORM tag
     */
    function getEndTableFormTag() {
        return "</table></form>".NL;
    }

    /** Return a TR separator
     */
    function getTrSeparator() {
        return "<tr><td colspan=2><br /></td></tr>".NL;
    }

    /** Return the end BODY tag
     */
    function getEndBody() {
        return "</body>".NL;
    }

    /** Return a empty TD tag
     */
    function getEmptyTd() {
        return "<td></td>";
    }

    /** Return a TH detail header
     */
    function getHeadElement($i) {
        return $this->_head[$i].NL;
    }

    /** Return the DIV block with the hidden INPUT
     */
    function getHiddenInputs() {
        $ret = "";
        if (!empty($this->_hidden)) {
            $ret = "<div class='no'>".NL;
            foreach ($this->_hidden as $name => $value) {
                $ret.= "<input name='{$name}' type='hidden' value='".formText($value)."'>".NL;
            }
            $ret.= "</div>".NL;
        }
        return $ret;
    }

    /** Return the TR block with the INPUT elements
     */
    function getInputElements($i) {
        $ret = "";
        if (!empty($this->_input[$i])) {
            foreach ($this->_input[$i] as $name => $arr_value) {
                $ret.= "<tr>".NL;
                $ret.= "<td><label for='modify_{$name}'>".WikiIocLangManager::getLang($name,'usermanager').": </label></td>".NL;
                $ret.= "<td><input id='modify_{$name}' name='{$name}' ";
                foreach ($arr_value as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $k => $v) {
                            $ret.= "$k='{$v}' ";
                        }
                    }else {
                        $ret.= "$key='".formText($value)."' ";
                    }
                }
                $ret.= "></td>".NL;
                $ret.= "</tr>".NL;
            }
        }
        return $ret;
    }

    /** Return the block with the BUTTONs
     */
    function getInputButtons($i) {
        $ret = "";
        if (!empty($this->_button[$i])) {
            foreach ($this->_hidden as $name => $arr_value) {
                $ret.= "<input name='{$name}' ";
                foreach ($arr_value as $key => $value) {
                    $ret.= "$key='{$value}' ";
                }
                $ret.= "/>".NL;
            }
        }
        return $ret;
    }

    /** Return the TR block with the params
     */
    function getTrBlock($pre, $arr_elements) {
        $ret = "<tr>".NL;
        $ret.= $pre.NL;
        $ret.= "<td>".NL;
        foreach ($arr_elements as $elem) {
            $ret .= $elem.NL;
        }
        $ret.= "</td></tr>".NL;
        return $ret;
    }

}