<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MailService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string              $unioneApiKey,
    )
    {
    }

    public function sendMail(array $message): ResponseInterface
    {
        $message['global_language'] = "fr";
        $message['from_email'] = "contact@nimescricketclub.fr";
        return $this->sendRequest('/email/send.json', ['message' => $message]);
    }

    private function sendRequest(string $endpoint, array $json): ResponseInterface
    {
        return $this->httpClient->request('POST', 'https://eu1.unione.io/en/transactional/api/v1' . $endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-API-KEY' => $this->unioneApiKey,
            ],
            'json' => $json
        ]);
    }

    public function resubscribe(string $email): ResponseInterface
    {
        return $this->sendRequest('/suppression/delete.json', ['email' => $email]);
    }
}
