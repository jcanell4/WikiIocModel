<?php
if (!defined('DOKU_INC')) die();

require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/WikiPageSystemManager.php'); //CAL Canviar de ruta quan es WikiPagerSystemmanager passi al plugin de persistència

/**
 * Class DraftManager
 *
 * Gestiona la creació, eliminació, actualització i recuperació d'esborranys
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class DraftManager{
    public static function saveDraft($draft){

        $type = $draft['type'];

        switch ($type) {
            case 'structured':
                self::generateStructuredDraft($draft['content'], $draft['id']);
                break;

            case 'full': // TODO[Xavi] Processar el esborrany normal també a través d'aquesta classe
                self::saveFullDraft($draft['content'], $draft['id']);
                break;

            default:
                // error o no draft


                break;
        }
    }

    private static function generateStructuredDraft($draft, $id){

        $time = time();
        $newDraft = [];

        $draftFile = self::getStructuredDraftFilename($id);

        if (@file_exists($draftFile)) {
            // Obrim el draft actual si existeix
            $oldDraft = self::getStructuredDraft($id);
        } else {
            $oldDraft = [];
        }

        // Recorrem la llista de headers de old drafts

        foreach ($oldDraft as $header => $chunk) {
            $content1 = $draft[$header]['content'];
            $content2 = $chunk['content'];
            $iguals = $content1 == $content2;


//            if (!$draft[$header]['content']) {
            //TODO[Xavi] Encara que no es passi una secció en particular no vol dir que s'hagi d'esborrar, si no solament es guarda el chunk seleccionat
            //
//                continue;

//            } else
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

        } else {
            // No hi ha res, l'esborrem
            @unlink($draftFile);
        }

    }

    public static function getStructuredDraftForHeader($id, $header){
        $draftFile = self::getStructuredDraftFilename($id);


        if (@file_exists($draftFile)) {
            $draft = unserialize(io_readFile($draftFile, FALSE));

            if ($draft[$header]) {
                return $draft[$header];
            }

        }

        return null;
    }

    public static function getStructuredDraft($id)
    {
        $draftFile = self::getStructuredDraftFilename($id);
        $draft = [];

        if (@file_exists($draftFile)) {
            $draft = unserialize(io_readFile($draftFile, FALSE));
        }

        return $draft;
    }

    public static function getStructuredDraftFilename($id)
    {
        return self::getDraftFilename($id).'.structured';
    }

    public static function removeStructuredDraft($id, $header_id)
    {
        $draftFile = self::getStructuredDraftFilename($id);

        if (@file_exists($draftFile)) {
            $oldDraft = self::getStructuredDraft($id);

            if (array_key_exists($header_id, $oldDraft)) {
                unset($oldDraft[$header_id]);
            }

            if (count($oldDraft) > 0) {
                io_saveFile($draftFile, serialize($oldDraft));

            } else {
                // No hi ha res, l'esborrem
                @unlink($draftFile);
            }
        }

    }

    public static function removeStructuredDraftAll($id)
    {
        $draftFile = self::getStructuredDraftFilename($id);
        if (@file_exists($draftFile)) {
            @unlink($draftFile);
        }

    }

    public static function existsPartialDraft($id)
    {
        $draftFile = self::getStructuredDraftFilename($id);
        return self::existsDraft($draftFile, $id);

    }

    public static function existsFullDraft($id)
    {
        $draftFile = self::getDraftFilename($id);
        return self::existsDraft($draftFile, $id);
    }

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
     * Retorna el nom del fitxer de esborran corresponent al document i usuari actual
     *
     * @param string $id - id del document
     *
     * @return string
     */
    public static function getDraftFilename($id)
    {
        $id = WikiPageSystemManager::cleanIDForFiles($id);
        return getCacheName(WikiIocInfoManager::getInfo("client") . $id, '.draft');
    }

    private static function getFullDraft($id)
    {

        $draftFile = self::getDraftFilename($id);
        $cleanedDraft = NULL;

        // Si el draft es més antic que el document actual esborrem el draft
        if (@file_exists($draftFile)) {
            if (@filemtime($draftFile) < @filemtime(wikiFN($id))) {
                @unlink($draftFile);
            } else {
                $draft = unserialize(io_readFile($draftFile, FALSE));
                $cleanedDraft = self::cleanDraft(con($draft['prefix'], $draft['text'], $draft['suffix']));
            }
        }

        $draftDate = WikiPageSystemManager::extractDateFromRevision(@filemtime($draftFile));

        return ['content' => $cleanedDraft, 'date' => $draftDate];
    }

    public static function generateFullDraft($id)
    {
        $draft = null;

        // Existe el draft completo?
        if (self::existsFullDraft($id)) {
            // Retornamos el draft completo
            $draft = self::getFullDraft($id);

            // Si no, Existe el draft parcial?
        } else if (self::existsPartialDraft($id)) {
            // Construimos el draft
            $draft = self::getFullDraftFromPartials($id);
        }


        return $draft;
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

    private static  function getFullDraftFromPartials($id)
    {
        $draftContent = '';

        $structuredDraft = self::getStructuredDraft($id);
        $chunks = $this->modelWrapper->getAllChunksWithText($id)['chunks'];

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
        $draft['date'] = $this->modelWrapper->extractDateFromRevision(@filemtime($this->getStructuredDraftFilename($id)));

        return $draft;
    }

    /**
     * Retorna cert si existeix un esborrany o no. En cas de que es trobi un esborrany més antic que el document es
     * esborrat.
     *
     * @param $id - id del document
     *
     * @return bool - cert si hi ha un esborrany vàlid o fals en cas contrari.
     */
    public static function hasDraft($id)
    {
        $id = WikiPageSystemManager::cleanIDForFiles($id);

        $draftFilename = self::getDraftFilename($id);

        if (@file_exists($draftFilename)) {
            if (@filemtime($draftFilename) < @filemtime(wikiFN($id))) {
                @unlink($draftFilename);
            } else {
                return TRUE;
            }
        }

        // Comprovem si existeix un draft parcial
        if (self::existsPartialDraft($id)) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Guarda l'esborrany complet del document i s'eliminen els esborranys parcials
     * @param $draft
     * @param $id
     */
    public static function saveFullDraft($draft, $id)
    {
        $info = basicinfo($id);

        $aux = ['id' => $id,
                'prefix' => '',
                'text' => $draft,
                'suffix' => '',
                'date' => time(), // TODO[Xavi] Posar la data
                'client' => $info['client']
            ];

        $filename = self::getDraftFilename($id);

        if (io_saveFile($filename, serialize($aux))) {
            $INFO['draft'] = $filename;
        }

        self::removeStructuredDraftAll($id);

    }
}