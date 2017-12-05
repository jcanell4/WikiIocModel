<?php
/**
 * Description of RefreshEditionAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_PLUGIN . "ajaxcommand/defkeys/PageKeys.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/actions/PageAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";
require_once DOKU_PLUGIN . "wikiiocmodel/persistence/WikiPageSystemManager.php";

if (!defined('DW_ACT_LOCK')) define('DW_ACT_LOCK', "lock");
if (!defined('DW_ACT_EDIT')) define('DW_ACT_EDIT', "edit");
if (!defined('DW_ACT_DENIED')) define('DW_ACT_DENIED', "denied");
if (!defined('DW_DEFAULT_PAGE')) define('DW_DEFAULT_PAGE', "start");

class RefreshEditionAction extends PageAction implements ResourceLockerInterface /*,ResourceUnlockerInterface*/ {
    protected $engine;
    private $lockStruct;

    public function __construct(BasicPersistenceEngine $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_LOCK;
        $this->engine = $engine;
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess()
    {
        if (!WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_EXISTS)) {
            throw new PageNotFoundException($this->params[PageKeys::KEY_ID]);
        }

        $ACT = act_permcheck(DW_ACT_EDIT);

        if ($ACT == DW_ACT_DENIED) {
            throw new InsufficientPermissionToEditPageException($this->params[PageKeys::KEY_ID]);
        }

        $this->lockStruct = $this->requireResource(TRUE);
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet generar la resposta a enviar al client. Aquest
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut
     * DokuAction#response.
     */
    protected function responseProcess()
    {
        if($this->lockStruct["state"]!== ResourceLockerInterface::LOCKED){
            //[JOSEP] AIXÒ NO HAURIA DE PASSAR MAI!
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }

        $response["codeType"]=0;
        return $response;
    }

    /**
     * Es tracta del mètode que hauran d'executar en iniciar el bloqueig. Per  defecte no bloqueja el recurs, perquè
     * actualment el bloqueig es realitza internament a les funcions natives de la wiki. Malgrat tot, per a futurs
     * projectes es contempla la possibilitat de fer el bloqueig directament aquí, si es passa el paràmetre amb valor
     * TRUE. EL mètode comprova si algú està bloquejant ja el recurs i en funció d'això, retorna una constant amb el
     * resultat obtingut de la petició.
     *
     * @param bool $lock
     * @return int
     */
    public function requireResource($lock = FALSE)
    {
        return $this->resourceLocker->requireResource($lock);
    }
}
