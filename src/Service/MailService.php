<?php

namespace App\Service;

use Aws\Exception\AwsException;
use Aws\Result;
use Aws\SesV2\SesV2Client;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Mime\Header\UnstructuredHeader;

class MailService
{
    private const BATCH_SIZE = 50;
    private SesV2Client $client;

    public function __construct(
        string $sesRegion,
        string $sesAccessKeyId,
        string $sesSecretAccessKey,
        private readonly LoggerInterface $logger
    ) {
        $this->client = new SesV2Client([
            'version' => 'latest',
            'region' => $sesRegion,
            'credentials' => [
                'key' => $sesAccessKeyId,
                'secret' => $sesSecretAccessKey,
            ],
        ]);
    }

    public function sendMail(
        array $emailEntries,
        string $fromName,
        string $subject,
        string $body
    ): array {
        $emails = [];
        $failed = [];
        $errors = [];
        $batches = array_chunk($emailEntries, self::BATCH_SIZE);
        foreach ($batches as $batch) {
            try {
                $res = $this->sendMailBatch($batch, $fromName, $subject, $body);
                $results = $res->get('BulkEmailEntryResults');
                $this->logger->debug(json_encode($results), [JSON_PRETTY_PRINT]);
                for ($i = 0; $i < sizeof($batch); $i++) {
                    $currentEmails = $batch[$i]['Destination']['ToAddresses'];
                    $result = $results[$i];
                    if ($result['Status'] === 'SUCCESS') {
                        $emails = array_merge($emails, $currentEmails);
                    } else {
                        $failed = array_merge($failed, $currentEmails);
                        $errors[] = $result['Error'] ?? $result['Status'];
                    }
                }
            } catch (AwsException $e) {
                foreach ($batch as $entry) {
                    $failed = array_merge($failed, $entry['Destination']['ToAddresses']);
                }
                return [
                    "emails" => $emails,
                    "failed" => $failed,
                    "done" => false,
                    "error" => $e->getMessage(),
                    "code" => $e->getCode()
                ];
            }
        }
        return [
            'emails' => $emails,
            'failed' => $failed,
            'done' => $failed === [],
            'error' => $errors === [] ? null : implode('; ', array_unique($errors)),
            'code' => $failed === [] ? 200 : 500,
        ];
    }

    /** Send a transactional message without applying newsletter template substitutions. */
    public function sendMessage(
        string $recipient,
        string $fromName,
        string $subject,
        string $body,
        array $attachments = []
    ): void {
        $message = [
            'Subject' => ['Data' => $subject, 'Charset' => 'UTF-8'],
            'Body' => ['Html' => ['Data' => $body, 'Charset' => 'UTF-8']],
        ];
        foreach ($attachments as $attachment) {
            $message['Attachments'][] = [
                'RawContent' => $attachment['content'],
                'FileName' => $attachment['name'],
                'ContentType' => $attachment['type'],
                'ContentDisposition' => 'ATTACHMENT',
                'ContentTransferEncoding' => 'BASE64',
            ];
        }

        try {
            $this->client->sendEmail([
                'FromEmailAddress' => $this->senderAddress($fromName),
                'Destination' => ['ToAddresses' => [$recipient]],
                'Content' => ['Simple' => $message],
            ]);
        } catch (AwsException $e) {
            throw new RuntimeException('Failed to send email: ' . $e->getMessage(), 0, $e);
        }
    }

    private function senderAddress(string $fromName): string
    {
        $name = (new UnstructuredHeader('From', str_replace(["\r", "\n"], '', $fromName)))->getBodyAsString();
        if (!str_contains($name, '=?')) {
            $name = '"' . addcslashes($name, '\\"') . '"';
        }

        return $name . ' <contact@nimescricketclub.fr>';
    }

    /** @throws AwsException */
    private function sendMailBatch(
        array $emailEntries,
        string $fromName,
        string $subject,
        string $body
    ): Result {
        return $this->client->sendBulkEmail([
            'BulkEmailEntries' => $emailEntries,
            'DefaultContent' => [
                'Template' => [
                    'TemplateContent' => [
                        'Html' => $body,
                        'Subject' => $subject
                    ],
                    'TemplateData' => json_encode(["UNSUBSCRIBE_URL" => "https://example.com"])
                ]
            ],
            'FromEmailAddress' => $this->senderAddress($fromName)
        ]);
    }
}
