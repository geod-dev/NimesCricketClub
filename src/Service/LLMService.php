<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LLMService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string              $aiEndpointUri,
        private readonly string              $aiEndpointApiKey,
        private readonly string              $aiEndpointModel
    )
    {
    }

    public function query(string $system_prompt, string $content): string
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
