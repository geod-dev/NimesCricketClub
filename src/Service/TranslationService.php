<?php

namespace App\Service;

use App\Entity\Interface\TranslatableInterface;

class TranslationService
{
    const SIMPLE_TRANSLATE_PROMPT = "Translate user input from French to English.
    Do not add anything around that translation. Do not add or remove any explanations or punctuation.";
    const HTML_TRANSLATE_PROMPT = "Translate HTML user input from French to English.
    Do not add anything around that translation. Translate only inner text.
    Do not translate tags, tags attributes names or values. Do not modify tags organization and overall architecture.";

    public function __construct(
        private readonly LLMService $llmService,
    )
    {
    }

    public function translate(TranslatableInterface $entity): void
    {
        $hash = ($h = $entity->getTranslationVersionHash()) ? explode(":", $h) : [""];

        $titleHash = md5($entity->getTitleFr() ?? "");
        $contentHash = md5($entity->getContentFr() ?? "");
        $titleModified = !isset($hash[0]) || !hash_equals($hash[0], $titleHash);
        $contentModified = !isset($hash[1]) || !hash_equals($hash[1], $contentHash);

        if ($titleModified) {
            $translatedTitle = $this->llmService->query(self::SIMPLE_TRANSLATE_PROMPT, $entity->getTitleFr());
            $entity->setTitleEn($translatedTitle);
        }

        if ($contentModified) {
            $translatedContent = $this->llmService->query(self::HTML_TRANSLATE_PROMPT, $entity->getContentFr());
            $entity->setContentEn($translatedContent);
        }

        if ($titleModified || $contentModified) {
            $entity->setTranslationVersionHash($titleHash . ":" . $contentHash);
        }
    }


}
