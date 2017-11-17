<?php
/**
 * Description of DokuPageModel
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_INC . 'inc/common.php');
require_once(DOKU_PLUGIN . "ajaxcommand/defkeys/PageKeys.php");
require_once(DOKU_PLUGIN . "wikiiocmodel/datamodel/WikiRenderizableDataModel.php");
require_once(DOKU_PLUGIN . "wikiiocmodel/ResourceLocker.php");

class DokuPageModel extends WikiRenderizableDataModel {
    //const NO_DRAFT = "none";
    //const PARTIAL_DRAFT = "partial";
    //const FULL_DRAFT = "full";

    //const LOCAL_PARTIAL_DRAFT = "local_partial";
    //const LOCAL_FULL_DRAFT = "local_full";

    protected $id;
    protected $selected;
    protected $editing;
    protected $rev;
    protected $recoverDraft;
    protected $pageDataQuery;
    protected $draftDataQuery;
    protected $lockDataQuery;
    protected $resourceLocker;  //El $resourceLocker se ha trasladado desde los Actions hasta aquí. Cal revisar los Actions

    public function __construct($persistenceEngine) {
        parent::__construct($persistenceEngine);
        $this->pageDataQuery = $persistenceEngine->createPageDataQuery();
        $this->draftDataQuery = $persistenceEngine->createDraftDataQuery();
        $this->lockDataQuery = $persistenceEngine->createLockDataQuery();
        $this->resourceLocker = new ResourceLocker($persistenceEngine);
    }

    public function init($id, $editing=NULL, $selected=NULL, $rev=NULL) {
        $this->id = $id;
        $this->editing = $editing;
        $this->selected = $selected;
        $this->rev = $rev;
    }

    public function existProject($id) {
        return $this->pageDataQuery->haveADirProject($id);
    }

    public function setData($toSet) {
        $params = (is_array($toSet)) ? $toSet : array(PageKeys::KEY_WIKITEXT => $toSet);
        $params[PageKeys::KEY_ID] = ($this->id) ? $this->id : $params[PageKeys::KEY_ID];
        $this->resourceLocker->init($params);
        //mirar si està bloquejat i si no ho està => excepció
        if ($this->resourceLocker->checklock() === LockDataQuery::UNLOCKED) {
            $this->pageDataQuery->save($params[PageKeys::KEY_ID], $params[PageKeys::KEY_WIKITEXT], $params[PageKeys::KEY_SUM], $params[PageKeys::KEY_MINOR]);
        }else {
            throw new UnexpectedLockCodeException($params[PageKeys::KEY_ID], 'ResourceLocked');
        }
    }

    public function getData($partial=FALSE) {
        $ret = ($partial) ? $this->getViewRawData() : $this->getViewData();
        return $ret;
    }

    public function getViewData() {
        $ret['structure'] = self::getStructuredDocument($this->pageDataQuery, $this->id,
            $this->editing, $this->selected,
            $this->rev);
        if ($this->draftDataQuery->hasAny($this->id)) {
            $ret['draftType'] = PageKeys::FULL_DRAFT;
            $ret['draft'] = $this->getDraftAsFull();
        }
        return $ret;
    }

    public function getViewRawData() {
        $response['structure'] = self::getStructuredDocument($this->pageDataQuery, $this->id,
            $this->editing, $this->selected,
            $this->rev);

        // El content es necessari en si hi ha un draft structurat local o remot, en aquest punt no podem saber si caldrà el local
        $response['content'] = $this->getChunkFromStructure($response['structure'], $this->selected);

        if ($this->draftDataQuery->hasFull($this->id)) {
            // Si exiteix el esborrany complet, el tipus serà FULL_DRAFT
            $response['draftType'] = PageKeys::FULL_DRAFT;

        } else if ($this->isChunkInDraft($this->id, $response['structure'], $this->selected) && $this->recoverDraft === null) {
            // Si no el chunk seleccionat es troba al draft, i no s'ha indicat que s'ha de recuperar el draft el tipus sera PARTIAL_DRAFT
            $response['draftType'] = PageKeys::PARTIAL_DRAFT;
            $response['draft'] = $this->_getChunkFromDraft($this->id, $this->selected);

            // TODO[Xavi] aquesta comprovació no hauria de ser necessaria, mai s'hauria de desar un draft igual al content, i en qualsevol cas la eliminació s'hauria de fer en un altre lloc
            if ($response['draft']['content'] === $response['content']['editing']) {
                $this->draftDataQuery->removeChunk($this->id, $this->selected);
                unset($response['draft']);
                $response['draftType'] = PageKeys::NO_DRAFT;
            }

        } else {
            $response['draftType'] = PageKeys::NO_DRAFT;
        }

        //readonly si bloquejat
        return $response;
    }

    public function getRawData() {
        $id = $this->id;
        $response['locked'] = checklock($id);
        $response['content'] = $this->pageDataQuery->getRaw($id, $this->rev);
        if ($this->draftDataQuery->hasAny($id)) {
            $response['draftType'] = PageKeys::FULL_DRAFT;
        }else{
            $response['draftType'] = PageKeys::NO_DRAFT;
        }

        return $response;
    }

    public function getMetaToc() {
        $toc = $this->pageDataQuery->getToc($this->id);
        $toc = preg_replace(
            '/(<!-- TOC START -->\s?)(.*\s?)(<div class=.*tocheader.*<\/div>|<h3 class=.*toggle.*<\/h3>)((.*\s)*)(<!-- TOC END -->)/i',
            '$1<div class="dokuwiki">$2$4</div>$6', $toc
        );
        return $toc;
    }

    public function getRevisionList($offset = -1) {
        return $this->pageDataQuery->getRevisionList($this->id, $offset);
    }

    public function getPageDataQuery() {
        return $this->pageDataQuery;
    }

    public function getDraftFilename() {
        return $this->draftDataQuery->getFileName($this->id);
    }

    public function removePartialDraft() {
        $this->draftDataQuery->removeStructured($this->id);
    }

    public function removeChunkDraft($chunkId) {
        $this->draftDataQuery->removeChunk($this->id, $chunkId);
    }

    public function getChunkFromDraft() {
        return $this->_getChunkFromDraft($this->id, $this->selected);
    }

    public function getFullDraft() {
        $respose = $this->getDraftAsFull();
        return $respose;
    }

    public function hasDraft(){
        return $this->draftDataQuery->hasAny($this->id);
    }

    private function getDraftAsFull() {
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

    private function getFullDraftFromPartials() {
        $draftContent = '';

        $structuredDraft = $this->draftDataQuery->getStructured($this->id);
        $chunks = self::getAllChunksWithText($this->id, $this->pageDataQuery)['chunks'];
        $draftContent .= $structuredDraft['pre'] /*. "\n"*/;

        for ($i = 0; $i < count($chunks); $i++) {
            if (array_key_exists($chunks[$i]['header_id'], $structuredDraft['content'])) {
                $draftContent .= $structuredDraft['content'][$chunks[$i]['header_id']];
            } else {
                $draftContent .= $chunks[$i]['text']['editing'];
            }
//            $draftContent .= "\n";
        }

        $draft['content'] = $draftContent;
        $draft['date'] = WikiPageSystemManager::extractDateFromRevision(@filemtime($this->draftDataQuery->getStructuredFilename($this->id)));

        return $draft;
    }

    private function getChunkFromStructure($structure, $selected) {
        $chunks = $structure['chunks'];
        foreach ($chunks as $chunk) {
            if ($chunk['header_id'] == $selected) {
                return $chunk['text'];
            }
        }
        return null;
    }

    private function _getChunkFromDraft($id, $selected) {
        return $this->draftDataQuery->getChunk($id, $selected);
    }

    /**
     * Hi ha un casos en que no hi ha selected, per exemple quan es cancela un document.
     */
    private static function getStructuredDocument($pageDataQuery, $id, $editing=NULL, $selected=NULL, $rev=NULL) {
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

        $headerIds = self::getHeadersFromHtml($html);
        $chunks = self::getChunks($pageDataQuery, $id, $rev);

        $editingChunks = [];
        $dictionary = [];

        self::getEditingChunks($pageDataQuery, $editingChunks, $dictionary, $chunks, $id, $headerIds, $editing);

        $lastSuf = count($editingChunks) - 1;
        $document['suf'] = $pageDataQuery->getRawSlices($id, $editingChunks[$lastSuf]['start'] . "-" . $editingChunks[$lastSuf]['end'])[2];

        self::addPreToChunks($pageDataQuery, $editingChunks, $id);

        $document['chunks'] = $chunks;
        $document['dictionary'] = $dictionary;
        $document['locked'] = checklock($id);

        return $document;
    }

    private static function getHeadersFromHtml($html) {
        $pattern = '/(?:<h[123] class="sectionedit\d+" id=")(.+?)">/s'; //aquest patró només funciona si s'aplica el scedit
        preg_match_all($pattern, $html, $match);
        return $match[1]; // Conté l'array amb els ids trobats per cada secció
    }

    private static function getEditingChunks($pageDataQuery, &$editingChunks, &$dictionary, &$chunks, $id, $headerIds, $editing) {
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

    private static function getAllChunksWithText($id, $pageDataQuery) {
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
    private function isChunkInDraft($id, $document, $selected = null) {
        if (!$selected) {
            return false;
        }

        $draft = $this->draftDataQuery->getStructured($id)['content'];

        for ($i = 0; $i < count($document['chunks']); $i++) {
            if (array_key_exists($document['chunks'][$i]['header_id'], $draft)
                	&& $document['chunks'][$i]['header_id'] == $selected) {
                // Si el contingut del draft i el propi es igual, l'eliminem
                if ($document['chunks'][$i]['text'] . ['editing'] == $draft[$selected]) {
                    $this->removeStructuredDraft($id, $selected);
                } else {
                    return true;
                }
            }
        }
        return false;
    }

    private static function addPreToChunks($pageDataQuery, &$chunks, $id) {
        $lastPos = 0;

        for ($i = 0; $i < count($chunks); $i++) {
            // El pre de cada chunk va de $lastPos fins al seu start
            $chunks[$i]['text']['pre'] = $pageDataQuery->getRawSlices($id, $lastPos . "-" . $chunks[$i]['start'])[1];

            // el text no forma part del 'pre'
            $lastPos = $chunks[$i]['end'];
        }
    }

    
    // Només son editables parcialment les seccions de nivell 1, 2 i 3
    private static function getChunks($pageDataQuery, $id, $rev = NULL) {
        $instructions = $pageDataQuery->getInstructions($id, $rev);
        $chunks = self::_getChunks($instructions);

        return $chunks;
    }

    // Només son editables parcialment les seccions de nivell 1, 2 i 3
    private static function _getChunks($instructions) {
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

    public function removeFullDraft() {
        $this->draftDataQuery->removeFull($this->id);
    }

    public function replaceContentForChunk(&$structure, $chunkId, $content) {
        $index = $structure['dictionary'][$chunkId];
        $structure['chunks'][$index]['text']['originalContent'] = $structure['chunks'][$index]['text']['editing'];
        $structure['chunks'][$index]['text']['editing'] = $content;
    }

    public function getFullDraftDate() {
        return $this->draftDataQuery->getFullDraftDate($this->id);
    }

    public function getStructuredDraftDate() {
        return $this->draftDataQuery->getStructuredDraftDate($this->id, $this->selected);
    }

    public function getLockState(){
        return $this->lockDataQuery->checklock($this->id);
    }

    public function pageExists() {
        $filename = $this->pageDataQuery->getFileName($this->id);
        return file_exists($filename);
    }

    public function getAllDrafts() {
        return $this->draftDataQuery->getAll($this->id, $this->pageDataQuery);
    }
}
