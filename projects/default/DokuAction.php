<?php
if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DW_ACT_DENIED')) define('DW_ACT_DENIED', "denied" );

require_once DOKU_PLUGIN."wikiiocmodel/AbstractWikiAction.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocModelExceptions.php";

//namespace ioc_dokuwiki;

/**
 * Description of DokuAction
 *
 * @author josep
 */
abstract class DokuAction extends AbstractWikiAction{
    protected $defaultDo;
    protected $params;
    private $preResponseTmp = array(); //EL format d'aquestes dades és un hashArray on la clau indica el tipus i el valor el contingut. La clau 
                                       //pot ser qualsevol de les que es processaràn després com a resposta en el responseHandler. Per exemple 
                                       //title, content, info, meta, etc. A més hi ha la possibilitat d'afegir contingut html a la resposta
    private $postResponseTmp= array(); //EL format d'aquestes dades és un hashArray on la clau indica el tipus i el valor el contingut. La clau 
                                       //pot ser qualsevol de les que es processaràn després com a resposta en el responseHandler. Per exemple 
                                       //title, content, info, meta, etc.
    private $response;
    
    /**
     * 
     * @param Array $paramsArr
     */
    public function get(/*Array*/ $paramsArr=array()){
        global $MSG;
        
        $this->start($paramsArr);
        $this->run();
        $response = $this->getResponse();
        
        if ($this->isDenied()) {
            throw new HttpErrorCodeException('accessdenied', 403);
        }
        
        if(is_string($this->preResponseTmp)){
            $response["before.content"] = $this->preResponseTmp;
        }else{
            foreach ($this->preResponseTmp as $key => $value ){
                if($key==="before.content"){
                     $response["before.content"] = $value;
                }else if($key==="info"){
                    if(isset($response["info"])){
                        if(is_string($value)){
                            $response["info"] = $this->addInfoToInfo($response["info"], $value);
                        }else{
                            $response["info"] = $this->addInfoToInfo($response["info"], self::generateInfo($value["type"], $value["message"], $value["id"], $value["duration"]));
                        }
                    }else{
                        $response["info"] = self::generateInfo($MSG['lvl'], $MSG['msg']);
                    }                    
                }else{
                    if(isset($response[$key])){
                        if(is_string($response[$key])){
                            $response[$key] .= $value;
                        }else if(is_array($response[$key])){
                            $response[$key][] = $value;
                        }else{
                            $response[$key] = $value;
                        }
                    }else{
                        $response[$key] = $value;
                    }
                }
            }
        }
       
        if(is_string($this->postResponseTmp)){
            $response["after.content"] = $this->postResponseTmp;
        }else{
            foreach ($this->postResponseTmp as $key => $value ){
                if($key==="after.content" && $this->addContentFromPlugins){
                    $response["after.content"] = $value;
                }else if($key==="info"){
                    if(isset($response["info"])){
                        if(is_string($value)){
                            $response["info"] = $this->addInfoToInfo($response["info"], $value);
                        }else{
                            $response["info"] = $this->addInfoToInfo($response["info"], self::generateInfo($value["type"], $value["message"], $value["id"], $value["duration"]));
                        }
                    }else{
                        $response["info"] = self::generateInfo($MSG['lvl'], $MSG['msg']);
                    }                    
                }else{
                    if(isset($response[$key])){
                        if(is_string($response[$key])){
                            $response[$key] .= $value;
                        }else if(is_array($response[$key])){
                            $response[$key][] = $value;
                        }else{
                            $response[$key] = $value;
                        }
                    }else{
                        $response[$key] = $value;
                    }
                }
            }     
        }
        if(isset($MSG)){
            $shown = array();
            foreach($MSG as $msg){
                $hash = md5($msg['msg']);
                if(isset($shown[$hash])) continue; // skip double messages
                    if(isset($response["info"])){
                        $response["info"] = $this->addInfoToInfo($response["info"], $this->generateInfo($MSG['lvl'], $MSG['msg']));
                    }else{
                        $response["info"] = $this->generateInfo($MSG['lvl'], $MSG['msg']);
                    }
                $shown[$hash] = 1;
            }
            unset($GLOBALS['MSG']);
        }
        
        $this->triggerEndEvents();
        
        return $response;
    }
    
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet fer assignacions a les variables globals de la 
     * wiki a partir dels valors de DokuAction#params.
     */
    protected abstract function startProcess();
    
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles 
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected abstract function runProcess();
    
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet generar la resposta a enviar al client. Aquest 
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut 
     * DokuAction#response.
     */
    protected abstract function responseProcess();
    
    private function start($paramsArr){        
        $this->params = $paramsArr;
	$this->startProcess();
        WikiIocInfoManager::loadInfo();
        WikiIocLangManager::load();
        $this->triggerStartEvents();
    }
    
    private function triggerStartEvents() {
        $tmp= array(); //NO DATA
        trigger_event( 'WIOC_AJAX_COMMAND_STARTED', $tmp);
        if(!empty($tmp)){
            $this->preResponseTmp[] = $tmp;
        }
    }

    private function triggerEndEvents() {
        $tmp = array(); //NO DATA
        trigger_event( 'WIOC_AJAX_COMMAND_DONE', $tmp );
        if(!empty($tmp)){
            $this->postResponseTmp[] = $tmp;
        }
    }
    
    private function run() {
        if ( $this->runBeforePreprocess() ) {
            $this->runProcess();            
        }
        $this->runAfterPreprocess();
    }
    
    private function runBeforePreprocess() {
        global $ACT;
        $content = "";

        $brun = FALSE;
        // give plugins an opportunity to process the action
        $this->ppEvt = new Doku_Event( 'ACTION_ACT_PREPROCESS', $ACT );
        ob_start();
        $brun    = ( $this->ppEvt->advise_before() );
        $content = ob_get_clean();

        if(!empty($content)){
            $this->preResponseTmp[] = $content;
        }
        
        return $brun;
    }

    private function runAfterPreprocess() {
        $content = "";
        ob_start();
        $this->ppEvt->advise_after();
        $content .= ob_get_clean();
        unset( $this->ppEvt );

        if(!empty($content)){
            $this->postResponseTmp[] = $content;
        }
    }
    
    private function getResponse(){
        $response = $this->responseProcess();
        if(!$response){
            $response = $this->response;
        }
        return $response;
    }
    
    /**
     * Genera un element amb la informació correctament formatada i afegeix el timestamp. Si no s'especifica el id
     * s'assignarà el id del document que s'estigui gestionant actualment.
     *
     * Per generar un info associat al esdeveniment global s'ha de passar el id com a buit, es a dir
     *
     * @param string          $type     - tipus de missatge
     * @param string|string[] $message  - Missatge o missatges associats amb aquesta informació
     * @param string          $id       - id del document al que pertany el missatge
     * @param int             $duration - Si existeix indica la quantitat de segons que es mostrarà el missatge
     *
     * @return array - array amb la configuració del item de informació
     */
    public static function generateInfo( $type, $message, $id, $duration = - 1 ) {
            return [
                    "id"        => $id,
                    "type"      => $type,
                    "message"   => $message,
                    "duration"  => $duration,
                    "timestamp" => date( "d-m-Y H:i:s" )
            ];
    }

    // En els casos en que hi hagi discrepancies i no hi haci cap preferencia es fa servir el valor de A
    protected function addInfoToInfo( $infoA, $infoB ) {
            // Els tipus global de la info serà el de major gravetat: "debug" > "error" > "warning" > "info"
            $info = [ ];

            if ( $infoA['type'] == 'debug' || $infoB['type'] == 'debug' ) {
                    $info['type'] = 'debug';
            } else if ( $infoA['type'] == 'error' || $infoB['type'] == 'error' ) {
                    $info['type'] = 'error';
            } else if ( $infoA['type'] == 'warning' || $infoB['type'] == 'warning' ) {
                    $info['type'] = 'warning';
            } else {
                    $info['type'] = $infoA['type'];
            }

            // Si algun dels dos te duració ilimitada, aquesta perdura
            if ( $infoA['duration'] == - 1 || $infoB['duration'] == - 1 ) {
                    $info['duration'] = -1;
            } else {
                    $info['duration'] = $infoA['duration'];
            }

            // El $id i el timestamp ha de ser el mateix per a tots dos
            $info ['timestamp'] = $infoA['timestamp'];
            $info ['id']        = $infoA['id'];

            $messageStack = [ ];

            if ( is_string( $infoA ['message'] ) ) {

                    $messageStack[] = $infoA['message'];

            } else if ( is_array( $infoA['message'] ) ) {

                    $messageStack = $infoA['message'];

            }

            if ( is_string( $infoB ['message'] ) ) {

                    $messageStack[] = $infoB['message'];

            } else if ( is_array( $infoB['message'] ) ) {

                    $messageStack = array_merge($messageStack, $infoB['message']);

            }

            $info['message'] = $messageStack;

            return $info;
    }

    protected function getCommonPage( $id, $title, $content=NULL ) {
        $contentData = array(
                'id'      => $id,
                'title'   => $title
        );
        if($content){
            $contentData["content"] = $content;
        }

        return $contentData;
    }

    private function isDenied() {
	global $ACT;
	return $ACT == DW_ACT_DENIED;
    }
    
}
