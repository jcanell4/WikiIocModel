<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once DOKU_PLUGIN."wikiiocmodel/projects/default/DokuAction.php";

/**
 * Description of PageAction
 *
 * @author josep
 */
abstract class PageAction extends DokuAction {
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet fer assignacions a les variables globals de la 
     * wiki a partir dels valors de DokuAction#params.
     */
    protected function startProcess(){        
		global $ID;
		global $ACT;
		global $REV;
		global $RANGE;
		global $DATE;
		global $PRE;
		global $TEXT;
		global $SUF;
		global $SUM;

		$ACT = $this->params['do']=  $this->defaultDo;
		$ACT = act_clean( $ACT );

		if ( ! $this->params['id'] ) {
			$this->params['id'] = WikiGlobalConfig::getConf(DW_DEFAULT_PAGE);
		}
		$ID = $this->params['id'];
		if ( $this->params['rev'] ) {
			$REV = $this->params['rev'];
		}
		if ($this->params['range']) {
			$RANGE = $this->params['range'];
		}
		if ( $this->params['date'] ) {
			$DATE = $this->params['date'];
		}
		if ( $this->params['pre'] ) {
			$PRE = $this->params['pre'] = cleanText( substr( $this->params['pre'], 0, - 1 ) );
		}
		if ( $this->params['text'] ) {
			$TEXT = $this->params['text'] = cleanText( $this->params['text']  );
		}
		if ( $this->params['suf'] ) {
			$SUF = $this->params['suf'] = cleanText( $this->params['suf']  );
		}
		if ( $this->params['sum'] ) {
			$SUM = $this->params['sum'] = $this->params['sum'] ;
		}                
    }

//put your code here
}
