<?php
/**
 * AbstractWikiAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();

abstract class AbstractWikiAction {

    protected $modelManager;

    public function get($paramsArr = array()){
        $this->params = $paramsArr;
        $this->triggerStartEvents();
        $ret = $this->responseProcess();
        $this->triggerEndEvents();
        return $ret;
    }

    public function init($modelManager = NULL) {
        $this->modelManager = $modelManager;
    }

    public function getModelManager() {
        return $this->modelManager;
    }

    /**
     * Genera un element amb la informació correctament formatada i afegeix el timestamp. Si no s'especifica el id
     * s'assignarà el id del document que s'estigui gestionant actualment.
     *
     * Per generar un info associat al esdeveniment global s'ha de passar el id com a buit
     *
     * @param string          $type     - tipus de missatge
     * @param string|string[] $message  - Missatge o missatges associats amb aquesta informació
     * @param string          $id       - id del document al que pertany el missatge
     * @param int             $duration - Si existeix indica la quantitat de segons que es mostrarà el missatge
     *
     * @return array - array amb la configuració del item de informació
     */
    public static function generateInfo( $type, $message, $id='', $duration = - 1 ) {
            return [
                    "id"        => str_replace(':', '_', $id),
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

    protected function triggerStartEvents() {
        $tmp= array(); //NO DATA
        trigger_event( 'WIOC_AJAX_COMMAND_STARTED', $tmp);
        if(!empty($tmp)){
            $this->preResponseTmp[] = $tmp;
        }
    }

    protected function triggerEndEvents() {
        $tmp = array(); //NO DATA
        trigger_event( 'WIOC_AJAX_COMMAND_DONE', $tmp );
        if(!empty($tmp)){
            $this->postResponseTmp[] = $tmp;
        }
    }

    protected abstract function responseProcess();
}
