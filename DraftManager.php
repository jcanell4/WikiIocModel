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

            //case 'simple': // TODO[Xavi] Processar el esborrany normal també a través d'aquesta classe
            // break

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

            if (!$draft[$header]['content']) {

                continue;

            } else if (array_key_exists($header, $draft)

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
        $id = str_replace(':', '_', $id);;
        $info = basicinfo($id);

        return getCacheName($info['client'] . $id, '.draft.structured');
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
        @unlink($draftFile);
    }

}