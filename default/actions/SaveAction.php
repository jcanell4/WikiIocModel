<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_INC . 'inc/pluginutils.php');
require_once(DOKU_INC . 'inc/actions.php');
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/default/DokuAction.php";


if (!defined('DW_ACT_SAVE')) {
    define('DW_ACT_SAVE', "save");
}

/**
 * Description of AdminTaskAction
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class SaveAction extends RawPageAction
{

    private $code = 0;

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet fer assignacions a les variables globals de la
     * wiki a partir dels valors de DokuAction#params.
     */
    protected function startProcess()
    {
        global $ACT;

        if ($this->params['wikitext']) {
            $this->params['text'] = $this->params['wikitext']; // TODO[Xavi] canviar el formulari del frontent per enviar el paràmetre text en lloc de wikitext? <-- en el save total, el partial ja ho fa amb text només
        }

        parent::startProcess();

        $ACT = $this->params['do'] = DW_ACT_SAVE;
        $ACT = act_clean($ACT);
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess()
    {
        global $ACT;

        $this->code = 0;
        $ACT = act_permcheck($ACT);

        if ($ACT == $this->params['do']) {
            $ret = act_save($ACT);
        } else {
            $ret = $ACT;
        }
        if ($ret === 'edit') {
            $this->code = 1004;
        } else if ($ret === 'conflict') {
            $this->code = 1003;
        } else if ($ret === 'denied') {
            $this->code = 1005;
        }
        if ($this->code == 0) {
            $ACT = $this->params['do'] = DW_ACT_EDIT;
//            $noEsFaServir = $this->doEditPagePreProcess();    //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
            $noEsFaServir = parent::runProcess();    //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
            // [TODO: Xavi] el codi HTML que conté? es feia servir abans? ara no retornarà res perquè es un runProcess()

            // [TODO: Xavi] que fem amb això? com que ja estem en edició no cal fer cap acció extra, i el runProcess() no retorna res, hauriem de crear una nova acció i cridar al get(), però no tinc clar amb quin objectiu



        } else {
            //S'han trobat conflictes i no s'ha pogut guardar
            //TODO[Josep] de moment tornem a la versió original, però cal
            //TODO[Xavi] Això ja no es necessari perque no ha de passar mai, el frontend et tanca automàticament la edició
            // Necessitem access:
            //      al draft (o contingut document que s'ha volgut guardar!)
            //      el document guardat

            //cercar una solució més operativa com ara emmagatzemar un esborrany
            //per tal que l'usuari pugui comparar i acceptar canvis
//            $ACT = $this->params['do'] = DW_ACT_SHOW;
            // TODO[Xavi] Aaixò no ha de passar mai, amb el codi actual no pot funcionar, si estigues bloquejada surt un info avisant
//            $this->doFormatedPagePreProcess();    //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.



        }

    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet generar la resposta a enviar al client. Aquest
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut
     * DokuAction#response.
     */
    protected function responseProcess()
    {
        global $TEXT;
        global $ID;

        $response = [];
        $duration = -1;

        if ($this->code == 1004) {
            $response["code"] = $this->code;
            $response["info"] = WikiIocLangManager::getLang('wordblock');
            $response["page"] = $this->getFormatedPageResponse();
            $type = "error";
        } elseif ($this->code == 1003) {
            $response["code"] = $this->code;
            $response["info"] = WikiIocLangManager::getLang('conflictsSaving'); //conflict
            $response["page"] = $this->getFormatedPageResponse();
            $type = "error";
        } else {
            $response = ["code" => $this->code, "info" => WikiIocLangManager::getLang('saved')];
            //TODO[Josep] Cal canviar els literals per referencies dinàmiques del maincfg
            $response["formId"] = "dw__editform";
            $response["inputs"] = [
                "date" => @filemtime(wikiFN($ID)),
                "changecheck" => md5($TEXT)
            ];
            $type = 'success';
            $duration = 10;
        }

        $response["info"] = $this->generateInfo($type, $response["info"], NULL, $duration);


        return $response;
    }

    private function getFormatedPageResponse()
    {
        $pageToSend = $this->getFormatedPage();
        $response = $this->getContentPage($pageToSend);
        return $response;
    }

    private function getFormatedPage()
    {
        global $ACT;

        ob_start();
        trigger_event('TPL_ACT_RENDER', $ACT, array($this, 'onFormatRender'));
        $html_output = ob_get_clean();
        ob_start();
        trigger_event('TPL_CONTENT_DISPLAY', $html_output, 'ptln');
        $html_output = ob_get_clean();

        return $html_output;
    }
}