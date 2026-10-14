<?php

namespace App\Tests;

use App\Entity\News;
use App\EventListener\NewsletterPublish;
use App\Entity\NewsletterEmail;
use App\Entity\NewsletterSubscriber;
use App\Repository\NewsletterSubscriberRepository;
use App\Service\MailService;
use App\Service\NewsletterService;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class NewsletterDeliveryTest extends KernelTestCase
{
    public function testDeliveryFlagsAndUuidReferencesArePersisted(): void
    {
        self::bootKernel();
        $manager = new EntityManager(
            DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]),
            self::getContainer()->get('doctrine')->getManager()->getConfiguration()
        );
        (new SchemaTool($manager))->createSchema($manager->getMetadataFactory()->getAllMetadata());
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($manager);
        $repository = new NewsletterSubscriberRepository($registry);
        $subscriber = (new NewsletterSubscriber())->setEmail('member@example.org');
        $email = (new NewsletterEmail())->setSubject('Club')->setContent('News');
        $manager->persist($subscriber);
        $manager->persist($email);
        $manager->flush();

        $repository->setFailed(['member@example.org']);
        $manager->refresh($subscriber);
        self::assertTrue($subscriber->sendFailed());

        $repository->updateLastEmailSent(['member@example.org'], $email);
        $manager->refresh($subscriber);
        self::assertFalse($subscriber->sendFailed());
        self::assertSame($email, $subscriber->getLastEmailSent());
        self::assertNotNull($subscriber->getLastContactedAt());
        $manager->close();
    }

    public function testPartialDeliverySurvivesPublicationFailureAndRetrySkipsSuccesses(): void
    {
        self::bootKernel();
        $manager = new EntityManager(
            DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]),
            self::getContainer()->get('doctrine')->getManager()->getConfiguration()
        );
        (new SchemaTool($manager))->createSchema($manager->getMetadataFactory()->getAllMetadata());
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($manager);
        $repository = new NewsletterSubscriberRepository($registry);
        $accepted = (new NewsletterSubscriber())->setEmail('accepted@example.org');
        $rejected = (new NewsletterSubscriber())->setEmail('rejected@example.org');
        $manager->persist($accepted);
        $manager->persist($rejected);
        $manager->flush();
        $mail = $this->createMock(MailService::class);
        $calls = 0;
        $mail->expects(self::exactly(2))->method('sendMail')->willReturnCallback(function ($entries) use (&$calls) {
            ++$calls;
            if ($calls === 1) {
                self::assertCount(2, $entries);
                return [
                    'emails' => ['accepted@example.org'], 'failed' => ['rejected@example.org'],
                    'done' => false, 'error' => 'Rejected', 'code' => 500,
                ];
            }
            self::assertCount(1, $entries);
            self::assertSame(['rejected@example.org'], $entries[0]['Destination']['ToAddresses']);
            return ['emails' => ['rejected@example.org'], 'failed' => [], 'done' => true, 'error' => null, 'code' => 200];
        });
        $service = new NewsletterService(
            $repository, $manager,
            new Environment(new ArrayLoader(['emails/newsletter.html.twig' => 'News'])),
            $mail, $this->createMock(UrlGeneratorInterface::class)
        );
        $manager->getEventManager()->addEventListener(
            ['postPersist', 'postUpdate', 'postFlush'], new NewsletterPublish($service)
        );
        $news = (new News())->setTitleFr('Club')->setTitleEn('Club')->setSlug('club')
            ->setImage('club.jpg')->setIsPublished(true);
        $manager->persist($news);

        try {
            $manager->flush();
            self::fail('Rejected delivery must fail.');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('Rejected', $e->getMessage());
        }
        self::assertTrue($manager->isOpen());
        $manager->refresh($news);
        $manager->refresh($accepted);
        $manager->refresh($rejected);
        self::assertFalse($news->isPublished());
        self::assertNull($news->getPublishedAt());
        self::assertSame($news, $accepted->getLastNewsSent());
        self::assertTrue($rejected->sendFailed());

        $service->sendNewsletter($news);
        $manager->refresh($news);
        $manager->refresh($rejected);
        self::assertTrue($news->isPublished());
        self::assertNotNull($news->getPublishedAt());
        self::assertFalse($rejected->sendFailed());
        $manager->close();
    }

    public function testExistingSubscriberCanResubscribe(): void
    {
        $subscriber = (new NewsletterSubscriber())->setIsSubscribed(false);
        $repository = $this->createMock(NewsletterSubscriberRepository::class);
        $repository->method('findOneBy')->willReturn($subscriber);
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->expects(self::once())->method('flush');
        $service = new NewsletterService(
            $repository, $manager, new Environment(new ArrayLoader()),
            $this->createMock(MailService::class), $this->createMock(UrlGeneratorInterface::class)
        );

        $service->subscribeEmail('member@example.org');
        self::assertTrue($subscriber->isSubscribed());
    }
}
