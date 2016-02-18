<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_INC . 'inc/common.php');
require_once(DOKU_INC . 'inc/actions.php');
require_once(DOKU_INC . 'inc/template.php');
require_once DOKU_PLUGIN . "ownInit/WikiGlobalConfig.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/default/DokuAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/default/DokuModelExceptions.php";


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
    public function __construct(/*BasicPersistenceEngine*/ $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_SAVE;
    }


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
        global $ID;

        $ACT = act_permcheck($ACT);

        if ($ACT == $this->params['do']) {

            $ret = act_save($ACT);

        } else {

            $ret = $ACT;
        }

//        $ret='edit';

        switch ($ret) {
            case 'edit':
                throw new WordBlockedException($ID);

            case 'conflict':
                throw new DateConflictSavingException($ID);

            case 'denied':
                throw new InsufficientPermissionToCreatePageException($ID);
        }

        // Esborrem el draft parcial perquè aquest NO l'elimina la wiki automàticament
        $this->draftQuery->removePartialDraft($this->params['id']);

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

        $response = ['code' => $this->code, 'info' => WikiIocLangManager::getLang('saved')];

        //TODO[Josep] Cal canviar els literals per referencies dinàmiques del maincfg <-- [Xavi] el nom del formulari ara es dinamic, canvia per cada document

        $response['formId'] = 'form_' . WikiPageSystemManager::cleanIDForFiles($ID);
        $response['inputs'] = [
            'date' => @filemtime(wikiFN($ID)),
            'changecheck' => md5($TEXT)
        ];
        $type = 'success';
        $duration = 10;

        $response['info'] = $this->generateInfo($type, $response['info'], NULL, $duration);


        return $response;
    }

}
