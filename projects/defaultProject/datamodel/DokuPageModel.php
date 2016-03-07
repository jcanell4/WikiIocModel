<?php
if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_INC . 'inc/common.php');
require_once DOKU_PLUGIN."wikiiocmodel/datamodel/WikiRenderizableDataModel.php";

/**
 * Description of DokuPageModel
 *
 * @author josep
 */
class DokuPageModel extends WikiRenderizableDataModel {
    const NO_DRAFT = "none";
    const PARTIAL_DRAFT = "partial";
    const FULL_DRAFT = "full";

    protected $id;
    protected $selected;
    protected $editing;
    protected $rev;
    protected $recoverDraft;
    protected $pageDataQuery;
    protected $draftDataQuery;
    
    public function __construct($persistenceEngine) {
        $this->pageDataQuery = $persistenceEngine->createPageDataQuery();
        $this->draftDataQuery = $persistenceEngine->createDraftDataQuery();
    }
    
    public function init($id, $editing=NULL, $selected=NULL, $rev = null){
        $this->id = $id;
        $this->editing = $editing;
        $this->selected = $selected;
        $this->rev = $rev;
    }
    
    public function setData($toSet) {
        if(is_array($toSet)){
            $params=$toSet;
        }else{
            $params=array('text' => $toSet);
        }
        $this->pageDataQuery->save($this->id, $params['text'], $params['summary'], $params['minor']);        
    }


    public function getData($partial = FALSE){
        if($partial){
            $ret = $this->getViewRawData();
        }else{
            $ret = $this->getViewData();
        }
        
        return $ret;
    }

    public function getViewData(){
        $ret['structure'] = self::getStructuredDocument($this->pageDataQuery, $this->id, 
                                                $this->editing, $this->selected,
                                                $this->rev);
        if($this->draftDataQuery->hasAny($this->id)){
            $ret['draftType']=  self::FULL_DRAFT;
            $ret['dratf'] = $this->getDraftAsFull();
        }
        return $ret;
    }

    public function getViewRawData(){
        $response['structure'] = self::getStructuredDocument($this->pageDataQuery, $this->id, 
                                                $this->editing, $this->selected,
                                                $this->rev);
         // TODO[Xavi] si es troba una draft per la edició, no es retornarà la resposta edit_html
        // TODO[Xavi] aquí s'haura d'afegir la comprovació de que no s'ha passat el paràmetre recover draft
        // TODO[Xavi] La diferencia en aquest if es que aquest primer bloc es pel draft parcial
        if ($this->isChunkInDraft($pid, $response['structure'], $selected) && $this->recoverDraft === null) {
            $response['draftType']= self::PARTIAL_DRAFT;
            $response['content'] = $this->getChunkFromStructure($response['structure'], $selected);
            $response['draft'] = $this->getChunkFromDraft($pid, $selected);
            if ($response['draft']['content'] === $response['content']['editing']) {
                $this->draftDataQuery->removeChunk($this->id, $this->selected);
                unset($response['draft']);
                unset($response['content']);
                $response['draftType'] = self::NO_DRAFT;
            }
        }
        return $response;
    }

    public function getRawData(){
        return $this->pageDataQuery->getRaw($this->id);
    }
    
    public function getMetaToc(){
        $toc = $this->pageDataQuery->getToc($this->id);
        $toc = preg_replace(
            '/(<!-- TOC START -->\s?)(.*\s?)(<div class=.*tocheader.*<\/div>|<h3 class=.*toggle.*<\/h3>)((.*\s)*)(<!-- TOC END -->)/i',
            '$1<div class="dokuwiki">$2$4</div>$6', $toc
        );
        return $toc;
    }
    
    public function getRevisionList(){
        return $this->pageDataQuery->getRevisionList($this->id);
    }
    
    public function getDraftFilename(){
        return $this->draftDataQuery->getFileName($this->id);
    }
    
    public function removePartialDraft(){
        $this->draftDataQuery->removeStructured($this->id);
    }

    public function removeChunkDraft($chunkId){
        $this->draftDataQuery->removeChunk($this->id, $chunkId);
    }

    public function getDraftAsFull(){
        $draft = null;

        // Existe el draft completo?
        if ($this->draftDataQuery->hasFull($this->id)) {
            // Retornamos el draft completo
            $draft = $this->draftDataQuery->getFull($this->id);

            // Si no, Existe el draft parcial?
        } else if ($this->draftDataQuery->hasStructured($this->id)) {
            // Construimos el draft
            $draft = $this->getFullDraftFromPartials();
        }

        return $draft;
    }

    private function getFullDraftFromPartials(){
        $draftContent = '';

        $structuredDraft = $this->draftDataQuery->getStructured($this->id);
        $chunks = self::getAllChunksWithText($this->id, $this->pageDataQuery)['chunks']; //TODO[Xavi] Això es força complicat de refactoritzar perquè crida una pila de mètodes al dokumodel
//        $chunks = [];

        $draftContent .= $structuredDraft['pre'] . "\n";

        for ($i = 0; $i < count($chunks); $i++) {
            if (array_key_exists($chunks[$i]['header_id'], $structuredDraft)) {
                $draftContent .= $structuredDraft[$chunks[$i]['header_id']]['content'];
            } else {
                $draftContent .= $chunks[$i]['text']['editing'];
            }
            $draftContent .= "\n";
        }

        $draft['content'] = $draftContent;

        //$draft['date'] = WikiPageSystemManager::extractDateFromRevision(@filemtime(self::getStructuredDraftFilename($this->id)));
        $draft['date'] = WikiPageSystemManager::extractDateFromRevision(@filemtime($this->draftDataQuery->getStructuredFilename($this->id)));

        return $draft;
    }


    private function getChunkFromStructure($structure, $selected){
        $chunks = $structure['chunks'];
        foreach ($chunks as $chunk) {
            if ($chunk['header_id'] == $selected) {
                return $chunk['text'];
            }
        }
        return null;
    }

    private function getChunkFromDraft($id, $selected){
        return $this->draftDataQuery->getChunk($id, $selected);
    }

//    public function getDraftAsFull($id){
//        $draft = null;
//
//        // Existe el draft completo?
//        if ($this->hasFull($id)) {
//            // Retornamos el draft completo
//            $draft = $this->getFull($id);
//
//            // Si no, Existe el draft parcial?
//        } else if ($this->hasStructured($id)) {
//            // Construimos el draft
//            $draft = self::getFullDraftFromPartials($id);
//        }
//
//        return $draft;
//    }


    
    /**
     * Hi ha un casos en que no hi ha selected, per exemple quan es cancela un document.
     *
     * @param $selected
     * @param $id
     * @param $rev
     * @param null $editing
     * @return array
     * @throws InsufficientPermissionToViewPageException
     * @throws PageNotFoundException
     * @internal param $ {string|null} $selected - Chunk seleccionat $selected - Chunk seleccionat
     */
    // TODO[Xavi] PER REFACTORITZAR QUANT TINGUEM EL PLUGIN DEL RENDER. Fer privada?
    private static function getStructuredDocument($pageDataQuery, $id, $editing = null, 
                                                $selected=NULL, $rev = null){

        if (!$editing && $selected) {
            $editing = [$selected];
        } else if (!$editing) {
            $editing = [];
        }

        $document = [];


        $document['title'] = tpl_pagetitle($id, TRUE);
        $document['ns'] = $id;
        $document['id'] = str_replace(":", "_", $id);
        $document['rev'] = $rev;
        $document['selected'] = $selected;
        $document['date'] = WikiIocInfoManager::getInfo('meta')['date']['modified'] + 1;

        $html = $pageDataQuery->getHtml($id, $rev);
        $document['html'] = $html;

        $headerIds = self::getHeadersFromHtml($html);  //TODO [Josep] caldria extreure les capçaleres directement d'un fitxer.

        //S'han unificat les dues instruccions següents a PageDataQuery sota el nom únic de getChunks
        //$instructions = self::getInstructionsForDocument($id, $rev);
        
        $chunks = self::getChunks($pageDataQuery, $id, $rev);

        $editingChunks = [];
        $dictionary = [];

        self::getEditingChunks($pageDataQuery, $editingChunks, $dictionary, $chunks, $id, $headerIds, $editing);

        // Afegim el suf
        $lastSuf = count($editingChunks) - 1;
        $document['suf'] = $pageDataQuery->getRawSlices($id, $editingChunks[$lastSuf]['start'] . "-" . $editingChunks[$lastSuf]['end'])[2];


        self::addPreToChunks($pageDataQuery, $editingChunks, $id);

        $document['chunks'] = $chunks;
        $document['dictionary'] = $dictionary;
        $document['locked'] = checklock(WikiPageSystemManager::cleanIDForFiles($id));

        return $document;
    }

    private static function getHeadersFromHtml($html){
        $pattern = '/(?:<h[123] class="sectionedit\d+" id=")(.+?)">/s'; // aquest patró només funciona si s'aplica el scedit
        preg_match_all($pattern, $html, $match);
        return $match[1]; // Conté l'array amb els ids trobats per cada secció
    }

    private static function getEditingChunks($pageDataQuery, &$editingChunks, &$dictionary , &$chunks, $id, $headerIds, $editing){
        for ($i = 0; $i < count($chunks); $i++) {
            $chunks[$i]['header_id'] = $headerIds[$i];
            // Afegim el text només al seleccionat i els textos en edició
            if (in_array($headerIds[$i], $editing)) {
                $chunks[$i]['text'] = [];
                //TODO[Xavi] compte! s'ha d'agafar sempre el editing per montar els nostres pre i suf!
                $chunks[$i]['text']['editing'] = $pageDataQuery->getRawSlices($id, $chunks[$i]['start'] . "-" . $chunks[$i]['end'])[1];
                $chunks[$i]['text']['changecheck'] = md5($chunks[$i]['text']['editing']);

                $editingChunks[] = &$chunks[$i];

            }
            $dictionary[$headerIds[$i]] = $i;
        }
    }

    private static function getAllChunksWithText($id, $pageDataQuery)
    {
        $html = $pageDataQuery->getHtml($id);
        $headerIds = self::getHeadersFromHtml($html);
        $chunks = self::getChunks($pageDataQuery, $id);
        $editing = $headerIds;
        $editingChunks = [];
        $dictionary = [];

        self::getEditingChunks($pageDataQuery, $editingChunks, $dictionary, $chunks, $id, $headerIds, $editing);

        return ['chunks' => $editingChunks, 'dictionary' => $dictionary];

    }

    // Hi ha draft pel chunk a editar?
    private function isChunkInDraft($id, $document, $selected = null)
    {
        if (!$selected) {
            return false;
        }

        $draft = $this->getStructuredDraft($id);

        for ($i = 0; $i < count($document['chunks']); $i++) {
            if (array_key_exists($document['chunks'][$i]['header_id'], $draft)
                && $document['chunks'][$i]['header_id'] == $selected
            ) {

                // Si el contingut del draft i el propi es igual, l'eliminem
                if ($document['chunks'][$i]['text'] . ['editing'] == $draft[$selected]['content']) {
                    $this->removeStructuredDraft($id, $selected);
                } else {
                    return true;
                }

            }

        }

        return false;
    }


    // TODO[Xavi] PER SUBISTIUIR PEL PLUGIN DEL RENDER
    private static function addPreToChunks($pageDataQuery, &$chunks, $id){
        //[ALERTA JOSEP] Cal passar rawWikiSlices a PageDataQuery i fer la crida des d'allà
        $lastPos = 0;

        for ($i = 0; $i < count($chunks); $i++) {
            // El pre de cada chunk va de $lastPos fins al seu start
            $chunks[$i]['text']['pre'] = $pageDataQuery->getRawSlices($id, $lastPos . "-" . $chunks[$i]['start'])[1];

            // el text no forma part del 'pre'
            $lastPos = $chunks[$i]['end'];
        }

    }
    
    
    
    // TODO[Xavi] PER SUBISTIUIR PEL PLUGIN DEL RENDER
    // Només son editables parcialment les seccions de nivell 1, 2 i 3
    private static function getChunks($pageDataQuery, $id, $rev=NULL){
        $instructions = $pageDataQuery->getInstructions($id, $rev);              
        $chunks = self::_getChunks($instructions);
        
        return $chunks;
    }
    
    // TODO[Xavi] PER SUBISTIUIR PEL PLUGIN DEL RENDER
    // Només son editables parcialment les seccions de nivell 1, 2 i 3
    private static function _getChunks($instructions){
        $sections = [];
        $currentSection = [];
        $lastClosePosition = 0;
        $lastHeaderRead = '';
        $firstSection = true;


        for ($i = 0; $i < count($instructions); $i++) {
            $currentSection['type'] = 'section';

            if ($instructions[$i][0] === 'header') {
                $lastHeaderRead = $instructions[$i][1][0];
            }

            if ($instructions[$i][0] === 'section_open' && $instructions[$i][1][0] < 4) {
                // Tanquem la secció anterior
                if ($firstSection) {
                    // Ho descartem, el primer element no conté informació
                    $firstSection = false;
                } else {
                    $currentSection['end'] = $instructions[$i][2];
                    $sections[] = $currentSection;
                }

                // Obrim la nova secció
                $currentSection = [];
                $currentSection['title'] = $lastHeaderRead;
                $currentSection['start'] = $instructions[$i][2];
                $currentSection['params']['level'] = $instructions[$i][1][0];
            }

            // Si trobem un tancament de secció actualitzem la ultima posició de tancament
            if ($instructions[$i][0] === 'section_close') {
                $lastClosePosition = $instructions[$i][2];
            }

        }
        // La última secció es tanca amb la posició final del document
        $currentSection['end'] = $lastClosePosition;
        $sections[] = $currentSection;

        return $sections;
    }
}
