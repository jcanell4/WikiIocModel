<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_PLUGIN . 'wikiiocmodel/DokuModelAdapter.php');

/**
 * Class DraftManager
 *
 * Gestiona la creació, eliminació, actualització i recuperació d'esborranys
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class DraftManager
{
    public function __construct(WikiIocModel $modelWrapper = NULL)
    {
        if ($modelWrapper) {
            $this->modelWrapper = $modelWrapper;
        } else {
            $this->modelWrapper = new DokuModelAdapter();
        }

    }

    public function saveDraft($draft)
    {

        $type = $draft['type'];

        switch ($type) {
            case 'structured':
                $this->generateStructuredDraft($draft['content'], $draft['id']);
                break;

            case 'full': // TODO[Xavi] Processar el esborrany normal també a través d'aquesta classe
                $this->saveFullDraft($draft['content'], $draft['id']);
                break;

            default:
                // error o no draft


                break;
        }
    }

    private function generateStructuredDraft($draft, $id)
    {

        $time = time();
        $newDraft = [];

        $draftFile = $this->getStructuredDraftFilename($id);

        if (@file_exists($draftFile)) {
            // Obrim el draft actual si existeix
            $oldDraft = $this->getStructuredDraft($id);
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

    public function getStructuredDraftForHeader($id, $header)
    {
        $draftFile = $this->getStructuredDraftFilename($id);


        if (@file_exists($draftFile)) {
            $draft = unserialize(io_readFile($draftFile, FALSE));

            if ($draft[$header]) {
                return $draft[$header];
            }

        }

        return null;
    }

    public function getStructuredDraft($id)
    {
        $draftFile = $this->getStructuredDraftFilename($id);
        $draft = [];

        if (@file_exists($draftFile)) {
            $draft = unserialize(io_readFile($draftFile, FALSE));
        }

        return $draft;
    }

    public function getStructuredDraftFilename($id)
    {
        return $this->getDraftFilename($id).'.structured';
    }

    public function removeStructuredDraft($id, $header_id)
    {
        $draftFile = $this->getStructuredDraftFilename($id);

        if (@file_exists($draftFile)) {
            $oldDraft = $this->getStructuredDraft($id);

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

    public function removeStructuredDraftAll($id)
    {
        $draftFile = $this->getStructuredDraftFilename($id);
        if (@file_exists($draftFile)) {
            @unlink($draftFile);
        }

    }

    public function existsPartialDraft($id)
    {
        $draftFile = $this->getStructuredDraftFilename($id);
        return $this->existsDraft($draftFile, $id);

    }

    public function existsFullDraft($id)
    {
        $draftFile = $this->getDraftFilename($id);
        return $this->existsDraft($draftFile, $id);
    }

    /**
     * Retorna cert si existeix un draft o fals en cas contrari. Si es troba un draft però es més antic que el document
     * corresponent aquest draft s'esborra.
     *
     * @param {string} $id id del document a comprovar
     * @return bool
     */
    private function existsDraft($draftFile, $id)
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
    public function getDraftFilename($id)
    {
        $id = $this->modelWrapper->cleanIDForFiles($id);
        $info = basicinfo($id);
        return getCacheName($info['client'] . $id, '.draft');
    }

    private function getFullDraft($id)
    {

        $draftFile = $this->getDraftFilename($id);
        $cleanedDraft = NULL;

        // Si el draft es més antic que el document actual esborrem el draft
        if (@file_exists($draftFile)) {
            if (@filemtime($draftFile) < @filemtime(wikiFN($id))) {
                @unlink($draftFile);
            } else {
                $draft = unserialize(io_readFile($draftFile, FALSE));
                $cleanedDraft = $this->cleanDraft(con($draft['prefix'], $draft['text'], $draft['suffix']));
            }
        }

        $draftDate = $this->modelWrapper->extractDateFromRevision(@filemtime($draftFile));

        return ['content' => $cleanedDraft, 'date' => $draftDate];
    }

    public function generateFullDraft($id)
    {
        $draft = null;

        // Existe el draft completo?
        if ($this->existsFullDraft($id)) {
            // Retornamos el draft completo
            $draft = $this->getFullDraft($id);

            // Si no, Existe el draft parcial?
        } else if ($this->existsPartialDraft($id)) {
            // Construimos el draft
            $draft = $this->getFullDraftFromPartials($id);
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
    private function cleanDraft($text)
    {
        $pattern = '/^(wikitext\s*=\s*)|(date=[0-9]*)$/i';
        $content = preg_replace($pattern, '', $text);
        return $content;
    }

    private function getFullDraftFromPartials($id)
    {
        $draftContent = '';

        $structuredDraft = $this->getStructuredDraft($id);
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
    public function hasDraft($id)
    {
        $id = $this->modelWrapper->cleanIDForFiles($id);

        $draftFilename = $this->getDraftFilename($id);

        if (@file_exists($draftFilename)) {
            if (@filemtime($draftFilename) < @filemtime(wikiFN($id))) {
                @unlink($draftFilename);
            } else {
                return TRUE;
            }
        }

        // Comprovem si existeix un draft parcial
        if ($this->existsPartialDraft($id)) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Guarda l'esborrany complet del document i s'eliminen els esborranys parcials
     * @param $draft
     * @param $id
     */
    public function saveFullDraft($draft, $id)
    {
        $info = basicinfo($id);

        $aux = ['id' => $id,
                'prefix' => '',
                'text' => $draft,
                'suffix' => '',
                'date' => time(), // TODO[Xavi] Posar la data
                'client' => $info['client']
            ];

        $filename = $this->getDraftFilename($id);

        if (io_saveFile($filename, serialize($aux))) {
            $INFO['draft'] = $filename;
        }

        $this->removeStructuredDraftAll($id);

    }
}