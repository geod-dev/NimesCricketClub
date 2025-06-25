<?php

namespace App\Service;

use App\Entity\Interface\TranslatableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TranslationService
{
    const SIMPLE_TRANSLATE_PROMPT = "Translate user input from French to English.
    Do not add anything around that translation.";
    const HTML_TRANSLATE_PROMPT = "Translate HTML user input from French to English.
    Do not add anything around that translation. Translate only inner text.
    Do not translate tags, tags attributes names or values. Do not modify tags organization and overall architecture.";

    public function __construct(
        private readonly HttpClientInterface                              $httpClient,
        #[Autowire(param: 'ai_endpoint.uri')] private readonly string     $aiEndpointUri,
        #[Autowire(param: 'ai_endpoint.api_key')] private readonly string $aiEndpointApiKey,
        #[Autowire(param: 'ai_endpoint.model')] private readonly string   $aiEndpointModel
    )
    {
    }

    public function translate(TranslatableInterface $entity): void
    {
        $hash = ($h = $entity->getTranslationVersionHash()) ? explode(":", $h) : [""];

        $titleHash = md5($entity->getTitleFr()??"");
        $contentHash = md5($entity->getContentFr()??"");
        $titleModified = !isset($hash[0]) || !hash_equals($hash[0], $titleHash);
        $contentModified = !isset($hash[1]) || !hash_equals($hash[1], $contentHash);

        if ($titleModified) {
            $translatedTitle = $this->query(self::SIMPLE_TRANSLATE_PROMPT, $entity->getTitleFr());
            $entity->setTitleEn($translatedTitle);
        }

        if ($contentModified) {
            $translatedContent = $this->query(self::HTML_TRANSLATE_PROMPT, $entity->getContentFr());
            $entity->setContentEn($translatedContent);
        }

        if ($titleModified || $contentModified) {
            $entity->setTranslationVersionHash($titleHash . ":" . $contentHash);
        }
    }

    private function query(string $system_prompt, string $content): string
    {
        $response = $this->httpClient->request('POST', $this->aiEndpointUri, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiEndpointApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messages' => [
                    ['role' => 'system', 'content' => $system_prompt],
                    ['role' => 'user', 'content' => $content]
                ],
                'model' => $this->aiEndpointModel
            ],
        ]);

        $response = json_decode($response->getContent(false), true);
        return $response["choices"][0]["message"]["content"];
    }
}
