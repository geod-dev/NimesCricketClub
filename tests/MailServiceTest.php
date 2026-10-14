<?php

namespace App\Tests;

use App\Service\MailService;
use Aws\Command;
use Aws\Exception\AwsException;
use Aws\MockHandler;
use Aws\Result;
use Aws\SesV2\SesV2Client;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;

class MailServiceTest extends TestCase
{
    private function service(MockHandler $handler): MailService
    {
        $client = new SesV2Client([
            'version' => 'latest',
            'region' => 'eu-west-3',
            'credentials' => ['key' => 'test', 'secret' => 'test'],
            'handler' => $handler,
        ]);
        $service = new MailService('eu-west-3', 'test', 'test', new NullLogger());
        (new \ReflectionProperty($service, 'client'))->setValue($service, $client);

        return $service;
    }

    public function testMixedDeliveryResultsAreReportedAsFailure(): void
    {
        $handler = new MockHandler();
        $handler->append(new Result(['BulkEmailEntryResults' => [
            ['Status' => 'SUCCESS'],
            ['Status' => 'MESSAGE_REJECTED', 'Error' => 'Rejected'],
        ]]));

        $result = $this->service($handler)->sendMail([
            ['Destination' => ['ToAddresses' => ['success@example.org']]],
            ['Destination' => ['ToAddresses' => ['failed@example.org']]],
        ], 'Club', 'News', '<p>News</p>');

        self::assertFalse($result['done']);
        self::assertSame(['success@example.org'], $result['emails']);
        self::assertSame(['failed@example.org'], $result['failed']);
        self::assertSame('Rejected', $result['error']);
    }

    public function testBatchExceptionPreservesPreviousSuccesses(): void
    {
        $handler = new MockHandler();
        $handler->append(new Result(['BulkEmailEntryResults' => array_fill(0, 50, ['Status' => 'SUCCESS'])]));
        $handler->append(new AwsException('Throttled', new Command('SendBulkEmail')));
        $entries = [];
        for ($i = 0; $i < 51; ++$i) {
            $entries[] = ['Destination' => ['ToAddresses' => ["member$i@example.org"]]];
        }

        $result = $this->service($handler)->sendMail($entries, 'Club', 'News', '<p>News</p>');

        self::assertFalse($result['done']);
        self::assertCount(50, $result['emails']);
        self::assertSame(['member50@example.org'], $result['failed']);
    }

    public function testTransactionalMessagePreservesBodyAndAttachments(): void
    {
        $handler = new MockHandler();
        $handler->append(function ($command) {
            self::assertSame('SendEmail', $command->getName());
            self::assertSame(['member@example.org'], $command['Destination']['ToAddresses']);
            self::assertStringContainsString('contact@nimescricketclub.fr', $command['FromEmailAddress']);
            self::assertSame('<p>{{literal}}</p>', $command['Content']['Simple']['Body']['Html']['Data']);
            self::assertSame('rules.txt', $command['Content']['Simple']['Attachments'][0]['FileName']);
            self::assertSame('Club rules', $command['Content']['Simple']['Attachments'][0]['RawContent']);

            return new Result(['MessageId' => 'accepted']);
        });

        $this->service($handler)->sendMessage('member@example.org', 'Club', 'Confirmation', '<p>{{literal}}</p>', [
            ['content' => 'Club rules', 'name' => 'rules.txt', 'type' => 'text/plain'],
        ]);
    }

    public function testTransactionalRejectionThrows(): void
    {
        $handler = new MockHandler();
        $handler->append(new AwsException('Rejected', new Command('SendEmail')));

        $this->expectException(RuntimeException::class);
        $this->service($handler)->sendMessage('member@example.org', 'Club', 'Confirmation', '<p>Payment</p>');
    }
}
