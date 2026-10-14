<?php

namespace App\Tests;

use App\Entity\Attachment;
use App\Entity\CustomerForm;
use App\Entity\CustomerFormEntry;
use App\Entity\News;
use App\Entity\NewsletterEmail;
use App\Entity\NewsletterSubscriber;
use App\Entity\User;
use App\Service\MailService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationSafetyTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManager $manager;
    private string $databasePath;
    private array $previousDatabaseEnv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databasePath = tempnam(sys_get_temp_dir(), 'club-safety-');
        $this->previousDatabaseEnv = [$_SERVER['DATABASE_URL'] ?? null, $_ENV['DATABASE_URL'] ?? null];
        $_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] = 'sqlite:///' . $this->databasePath;
        $this->client = self::createClient();
        $configuredManager = self::getContainer()->get('doctrine')->getManager();
        // Seed fixtures without running translation or newsletter listeners.
        $this->manager = new EntityManager(
            \Doctrine\DBAL\DriverManager::getConnection(['driver' => 'pdo_sqlite', 'path' => $this->databasePath]),
            $configuredManager->getConfiguration()
        );
        $this->manager->getConnection()->executeStatement('PRAGMA foreign_keys = ON');
        (new SchemaTool($this->manager))->createSchema($this->manager->getMetadataFactory()->getAllMetadata());
    }

    protected function tearDown(): void
    {
        $this->manager->close();
        parent::tearDown();
        foreach (['_SERVER', '_ENV'] as $index => $name) {
            if ($this->previousDatabaseEnv[$index] === null) {
                unset($GLOBALS[$name]['DATABASE_URL']);
            } else {
                $GLOBALS[$name]['DATABASE_URL'] = $this->previousDatabaseEnv[$index];
            }
        }
        @unlink($this->databasePath);
    }

    public function testAnonymousAdminRoutesAreProtectedInBothLanguages(): void
    {
        foreach (['/admin', '/en/admin', '/admin/attachments', '/en/admin/attachments'] as $path) {
            $this->client->request(str_ends_with($path, 'attachments') ? 'POST' : 'GET', $path);
            self::assertResponseRedirects();
            self::assertStringContainsString('/login', $this->client->getResponse()->headers->get('Location'));
        }
    }

    public function testDraftNewsIsHiddenFromPagesAndSitemap(): void
    {
        $draft = $this->news('draft-review', false);
        $published = $this->news('published-review', true)->setPublishedAt(new \DateTimeImmutable());
        $this->manager->persist($draft);
        $this->manager->persist($published);
        $this->manager->flush();

        foreach (['', '/en'] as $prefix) {
            $this->client->request('GET', $prefix . '/actus');
            self::assertResponseIsSuccessful();
            self::assertStringNotContainsString('draft-review', $this->client->getResponse()->getContent());
            self::assertStringContainsString('published-review', $this->client->getResponse()->getContent());
            $this->client->request('GET', $prefix . '/actus/draft-review');
            self::assertResponseStatusCodeSame(404);
            $this->client->request('GET', $prefix . '/actus/published-review');
            self::assertResponseIsSuccessful();
            $this->client->request('GET', $prefix . '/sitemap.xml');
            self::assertResponseIsSuccessful();
            self::assertStringNotContainsString('draft-review', $this->client->getResponse()->getContent());
            self::assertStringContainsString('published-review', $this->client->getResponse()->getContent());
        }
    }

    public function testAdminPagesRejectRegularUsersAndAllowAdmins(): void
    {
        $user = (new User())->setEmail('user@example.org')->setPassword('unused');
        $admin = (new User())->setEmail('admin@example.org')->setPassword('unused')->setRoles(['ROLE_ADMIN']);
        $this->manager->persist($user);
        $this->manager->persist($admin);
        $this->manager->flush();

        $this->client->loginUser($user);
        foreach (['/admin', '/en/admin'] as $path) {
            $this->client->request('GET', $path);
            self::assertResponseStatusCodeSame(403);
        }
        $this->client->loginUser($admin);
        foreach (['/admin', '/en/admin'] as $path) {
            $this->client->request('GET', $path);
            self::assertResponseIsSuccessful();
        }
    }

    public function testDeletingSentContentKeepsSubscribers(): void
    {
        $news = $this->news('sent-news', false);
        $email = (new NewsletterEmail())->setSubject('Club')->setContent('News');
        $subscriber = (new NewsletterSubscriber())->setEmail('member@example.org')
            ->setLastNewsSent($news)->setLastEmailSent($email);
        foreach ([$news, $email, $subscriber] as $entity) {
            $this->manager->persist($entity);
        }
        $this->manager->flush();
        $id = $subscriber->getId();
        $this->manager->remove($news);
        $this->manager->remove($email);
        $this->manager->flush();
        $this->manager->clear();

        $subscriber = $this->manager->find(NewsletterSubscriber::class, $id);
        self::assertNotNull($subscriber);
        self::assertNull($subscriber->getLastNewsSent());
        self::assertNull($subscriber->getLastEmailSent());
    }

    public function testAttachmentDeletionRemovesFileAndDatabaseRecord(): void
    {
        $user = (new User())->setEmail('admin@example.org')->setPassword('unused')->setRoles(['ROLE_ADMIN']);
        $path = 'review-' . bin2hex(random_bytes(8)) . '.txt';
        $fullPath = self::getContainer()->getParameter('attachments_path') . '/' . $path;
        (new \Symfony\Component\Filesystem\Filesystem())->dumpFile($fullPath, 'Attachment');
        $attachment = (new Attachment())->setName('Review')->setPath($path);
        $this->manager->persist($user);
        $this->manager->persist($attachment);
        $this->manager->flush();
        $id = $attachment->getId();

        try {
            $this->client->loginUser($user);
            $this->client->request('DELETE', '/attachments/' . $path);
            self::assertResponseIsSuccessful();
            self::assertFileDoesNotExist($fullPath);
            $this->manager->clear();
            self::assertNull($this->manager->find(Attachment::class, $id));
        } finally {
            @unlink($fullPath);
        }
    }

    public function testPaymentRemainsPaidAfterMailFailureAndConfirmationCanBeRetried(): void
    {
        $form = (new CustomerForm())->setTitleFr('Club')->setTitleEn('Club')->setSlug('club')
            ->setPrice('10')->setIsRepeatable(false);
        $entry = (new CustomerFormEntry())->setForm($form)->setEmail('member@example.org')
            ->setFirstName('Club')->setLastName('Member')->setPhone('0102030405')->setPrice(10)
            ->setCheckoutSessionId('cs_review');
        $this->manager->persist($form);
        $this->manager->persist($entry);
        $this->manager->flush();
        $this->client->disableReboot();
        $mail = $this->createMock(MailService::class);
        $attempts = 0;
        $mail->expects(self::exactly(2))->method('sendMessage')->willReturnCallback(function () use (&$attempts) {
            if (++$attempts === 1) {
                throw new RuntimeException('SES unavailable');
            }
        });
        self::getContainer()->set(MailService::class, $mail);
        $secret = self::getContainer()->getParameter('stripe_webhook_secret');
        $payload = json_encode([
            'id' => 'evt_review', 'object' => 'event', 'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_review', 'object' => 'checkout.session', 'status' => 'complete', 'payment_status' => 'paid',
            ]],
        ]);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        $headers = ['HTTP_STRIPE_SIGNATURE' => "t=$timestamp,v1=$signature"];

        $this->client->request('POST', '/stripe-webhook', server: $headers, content: $payload);
        self::assertResponseStatusCodeSame(503);
        $this->manager->refresh($entry);
        self::assertTrue($entry->isPaid());
        self::assertNull($entry->getPaymentConfirmationSentAt());

        self::getContainer()->get('doctrine')->resetManager();
        $this->client->request('POST', '/stripe-webhook', server: $headers, content: $payload);
        self::assertResponseIsSuccessful();
        $this->manager->refresh($entry);
        self::assertNotNull($entry->getPaymentConfirmationSentAt());
        $this->client->request('POST', '/stripe-webhook', server: $headers, content: $payload);
        self::assertResponseIsSuccessful();
    }

    public function testRetiredUnioneWebhookIsGone(): void
    {
        $this->client->request('POST', '/unione-webhook');
        self::assertResponseStatusCodeSame(404);
        $this->client->request('POST', '/stripe-webhook');
        self::assertResponseStatusCodeSame(400);
    }

    private function news(string $slug, bool $published): News
    {
        return (new News())->setTitleFr($slug)->setTitleEn($slug)->setSlug($slug)
            ->setImage('review.jpg')->setIsPublished($published);
    }
}
