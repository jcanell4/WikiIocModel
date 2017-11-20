<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_PLUGIN . "wikiiocmodel/WikiIocModelExceptions.php");
//require_once(DOKU_PLUGIN . "wikiiocmodel/WikiIocInfoManager.php");
require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');

/**
 * Description of DraftDataQuery
 *
 * @author Josep Cañellas i Xavier Garcia
 */
class DraftDataQuery extends DataQuery
{
    public function getFileName($id, $extra = NULL)
    {
        return $this->getFullFileName($id);
    }

    public function getFullFileName($id)
    {
//        $id = WikiPageSystemManager::cleanIDForFiles($id);
        return getCacheName(WikiIocInfoManager::getInfo("client") . $id, '.draft');
    }

    public function getStructuredFilename($id)
    {
//        $id = WikiPageSystemManager::cleanIDForFiles($id);
        return $this->getFilename($id) . '.structured';
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE, $expandProject = FALSE, $hiddenProjects = FALSE, $root = FALSE)
    {
        throw new UnavailableMethodExecutionException("DraftDataQuery#getNsTree");
    }

    public function getFull($id)
    {
//        $id = WikiPageSystemManager::cleanIDForFiles($id);
        $draftFile = $this->getFilename($id);
        $cleanedDraft = NULL;
        $draft = [];

        // Si el draft es més antic que el document actual esborrem el draft
        if ($this->hasFull($id)) {
//        if (@file_exists($draftFile)) {
//            if (@filemtime($draftFile) < @filemtime(wikiFN($id))) {
//                @unlink($draftFile);
//            } else {
            $draft = unserialize(io_readFile($draftFile, FALSE));
            $cleanedDraft = self::cleanDraft(con($draft['prefix'], $draft['text'], $draft['suffix']));
//            }
        }

//        $draftDate = WikiPageSystemManager::extractDateFromRevision(@filemtime($draftFile));

        return ['content' => $cleanedDraft, 'date' => $draft['date']];
    }

    public function removeStructured($id)
    {
//        $id = WikiPageSystemManager::cleanIDForFiles($id);
        $draftFile = $this->getStructuredFilename($id);
        if (@file_exists($draftFile)) {
            @unlink($draftFile);
        }
    }

    public function removeChunk($id, $chunkId)
    {
//        $id = WikiPageSystemManager::cleanIDForFiles($id);
        $draftFile = $this->getStructuredFilename($id);

        if (@file_exists($draftFile)) {
            $oldDraft = $this->getStructured($id);

            if (array_key_exists($chunkId, $oldDraft['content'])) {
                unset($oldDraft['content'][$chunkId]);
            }

            if (count($oldDraft['content']) > 0) {
                io_saveFile($draftFile, serialize($oldDraft));

            } else {
                // No hi ha res, l'esborrem
                @unlink($draftFile);
            }
        }
    }

    public function getStructured($id)
    {
//        $id = WikiPageSystemManager::cleanIDForFiles($id);
        $draftFile = self::getStructuredFilename($id);
        $draft = [];

        if (@file_exists($draftFile)) {
            $draft = unserialize(io_readFile($draftFile, FALSE));
        }

        return $draft;
    }


    public function getAll($id)
    {
        $drafts = [];

        if ($this->hasStructured($id)) {
            $drafts['structured'] = $this->getStructured($id);
        }

        $hasFull = $this->hasFull($id);

        if ($hasFull) {
            $drafts['full'] = $this->getFull($id);
        }

        // Si no hi ha draft full, o la data del draft estructurat es més recent, s'envia el draft reestructurat
        if ($this->hasStructured($id) && (!$hasFull || $drafts['full']['date'] < $drafts['structured']['date'])) {
            $drafts['full'] = $this->getFullDraftFromPartials($id);
        }


        return $drafts;
    }

    public function hasFull($id)
    {
        $draftFile = $this->getFullFileName($id);
        return self::existsDraft($draftFile, $id);
    }

    public function hasStructured($id)
    {
        $draftFile = $this->getStructuredFilename($id);
        return self::existsDraft($draftFile, $id);
    }

    /**
     * Retorna cert si existeix un esborrany o no. En cas de que es trobi un esborrany més antic que el document es
     * esborrat.
     *
     * @param $id - id del document
     *
     * @return bool - cert si hi ha un esborrany vàlid o fals en cas contrari.
     */
    public function hasAny($id)
    {
        return $this->hasFull($id) || $this->hasStructured($id);
    }

    public function getChunk($id, $header)
    {
        $draftFile = $this->getStructuredFilename($id);


        if ($this->hasStructured($id)) {
            $draft = unserialize(io_readFile($draftFile, FALSE));

            if ($draft['content'][$header]) {
                return [
                    'content' => $draft['content'][$header],
                    'date' => $draft['date']
                ];

            }

        }

        return null;
    }
    
    
    private function generateStructured($draft, $id)
    {

//        $time = time();
        $time = $draft['date'];

        $newDraft = [];

        $draftFile = $this->getStructuredFilename($id);

        if (@file_exists($draftFile)) {
            // Obrim el draft actual si existeix
            $oldDraft = $this->getStructured($id);
        } else {
            $oldDraft = [];
        }

        // Recorrem la llista de headers de old drafts

        foreach ($oldDraft as $header => $chunk) {

            if (array_key_exists($header, $draft)
                && $chunk['content'] != $draft[$header]['content']
            ) {
                $chunk['date'] = $time;
                $chunk['content'] = $draft[$chunk[$header]];
                $newDraft[$header] = ['content' => $draft[$header], 'date' => $time];
                unset($draft[$header]);

            } else {
                $newDraft[$header] = $chunk;
            }
        }


        foreach ($draft as $header => $content) {
            $newDraft[$header] = ['content' => $content, 'date' => $time];
        }

        // Guardem el draft si hi ha cap chunk
        if (count($newDraft) > 0) {
            io_saveFile($draftFile, serialize($newDraft));
            $this->removeFull($id);
        } else {
            // No hi ha res, l'esborrem
            @unlink($draftFile);
        }


    }

    /**
     * Guarda l'esborrany complet del document i s'eliminen els esborranys parcials
     * @param $draft
     * @param $id
     */
    public function saveFullDraft($draft, $id)
    {
        $aux = ['id' => $id,
            'prefix' => '',
            'text' => $draft,
            'suffix' => '',
            'date' => time(), // TODO[Xavi] Posar la data
            'client' => WikiIocInfoManager::getInfo('client')
        ];

        $filename = $this->getFilename($id);

        if (io_saveFile($filename, serialize($aux))) {
            $INFO['draft'] = $filename;
        }

        $this->removeStructured($id);
//        self::removeStructuredDraftAll($id);

    }


    public function getStructuredDraft($id)
    {
        $draftFile = $this->getStructuredFilename($id);
        $draft = [];

        if (@file_exists($draftFile)) {
            $draft = unserialize(io_readFile($draftFile, FALSE));
        }

        return $draft;
    }

//    private static function removeStructuredDraft($id, $header_id){
//        $draftFile = $this->getStructuredFilename($id);
//        
//        if (@file_exists($draftFile)) {
//            $oldDraft = $this->getStructured($id);
//
//            if (array_key_exists($header_id, $oldDraft)) {
//                unset($oldDraft[$header_id]);
//            }
//
//            if (count($oldDraft) > 0) {
//                io_saveFile($draftFile, serialize($oldDraft));
//
//            } else {
//                // No hi ha res, l'esborrem
//                @unlink($draftFile);
//            }
//        }
//
//    }

    /**
     * Retorna cert si existeix un draft o fals en cas contrari. Si es troba un draft però es més antic que el document
     * corresponent aquest draft s'esborra.
     *
     * @param {string} $id id del document a comprovar
     * @return bool
     */
    private static function existsDraft($draftFile, $id)
    {

        $exists = false;

        // Si el draft es més antic que el document actual esborrem el draft
        if (@file_exists($draftFile)) {
            if (@filemtime($draftFile) < @filemtime(wikiFN($id))) {
                @unlink($draftFile);
                $exists = false;
            } else {
                $exists = true;
            }
        }
        return $exists;
    }

    /**
     * Neteja el contingut del esborrany per poder fer-lo servir directament.
     *
     * @param string $text - contingut original del fitxer de esborrany.
     *
     * @return mixed
     */
    private static function cleanDraft($text)
    {
        $pattern = '/^(wikitext\s*=\s*)|(date=[0-9]*)$/i';
        $content = preg_replace($pattern, '', $text);
        return $content;
    }

    // ALERTA[Xavi] Afegit perquè no s'ha trobat equivalent
    public function removeFull($id)
    {
//        $id = WikiPageSystemManager::cleanIDForFiles($id);
        $draftFile = $this->getFileName($id);
        if (@file_exists($draftFile)) {
            @unlink($draftFile);
        }
    }

    // ALERTA[Xavi] Afegit perquè no s'ha trobat equivalent
    public function getFullDraftDate($id)
    {
        $draftFile = $this->getFullFileName($id);
        if (@file_exists($draftFile)) {
            $draft = unserialize(io_readFile($draftFile, FALSE));
            return $draft['date'];
        } else {
            return -1;
        }

//        return @file_exists($draftFile) ? @filemtime($draftFile) : -1;
    }


    public function getStructuredDraftDate($id)
    {
        $draft = $this->getStructured($id);

        return $draft['date'];

    }

}
