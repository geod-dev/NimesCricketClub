<?php

namespace App\Tests;

use App\Entity\CalendarEvent;
use App\Entity\News;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CalendarEventTest extends KernelTestCase
{
    public function testTimestampsAndOptionalNewsArePersisted(): void
    {
        self::bootKernel();

        $manager = new EntityManager(
            DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]),
            self::getContainer()->get('doctrine')->getManager()->getConfiguration()
        );
        (new SchemaTool($manager))->createSchema($manager->getMetadataFactory()->getAllMetadata());

        $event = $this->createEvent();
        $manager->persist($event);
        $manager->flush();

        $createdAt = $event->getCreatedAt();
        $updatedAt = $event->getUpdatedAt();

        self::assertNotNull($createdAt);
        self::assertNotNull($updatedAt);
        self::assertNull($event->getRelatedNews());

        $news = (new News())
            ->setTitleFr('Club news')
            ->setTitleEn('Club news')
            ->setSlug('club-news')
            ->setImage('news.jpg');

        $manager->persist($news);
        $event->setTitle('Updated event')->setRelatedNews($news);
        $manager->flush();

        self::assertSame($createdAt, $event->getCreatedAt());
        self::assertGreaterThan($updatedAt, $event->getUpdatedAt());

        $id = $event->getId();
        $manager->clear();

        $event = $manager->find(CalendarEvent::class, $id);
        self::assertSame('club-news', $event->getRelatedNews()->getSlug());

        $event->setRelatedNews(null);
        $manager->flush();
        $manager->clear();

        self::assertNull($manager->find(CalendarEvent::class, $id)->getRelatedNews());
        $manager->close();
    }

    public function testInvalidDatesAndColorsAreRejected(): void
    {
        self::bootKernel();

        $event = $this->createEvent();
        $validator = self::getContainer()->get('validator');

        self::assertCount(0, $validator->validate($event));

        $event->setEndAt(new \DateTime('2026-10-14 09:00'));
        $event->setColor('invalid');

        $paths = [];
        foreach ($validator->validate($event) as $violation) {
            $paths[] = $violation->getPropertyPath();
        }

        self::assertContains('endAt', $paths);
        self::assertContains('color', $paths);
    }

    public function testPageRendersCalendarDataRichTextAndOptionalNewsLink(): void
    {
        self::bootKernel();

        $request = Request::create('/evenements');
        $request->attributes->set('_route', 'app_events');
        $request->setLocale('fr');
        $request->setSession(new Session(new MockArraySessionStorage()));
        self::getContainer()->get('request_stack')->push($request);

        $event = $this->createEvent()->setTitle('Match "Nîmes" <club>');
        $news = (new News())->setSlug('club-news');
        $event->setRelatedNews($news);

        $twig = self::getContainer()->get('twig');
        $html = $twig->render('events/index.html.twig', ['events' => [$event]]);
        $crawler = new Crawler($html);
        $events = json_decode($crawler->filter('.event-calendar')->attr('data-events'), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Match "Nîmes" <club>', $events[0]['title']);
        self::assertSame('2026-10-14T10:00:00', $events[0]['start']);
        self::assertSame('#007B45', $events[0]['color']);
        self::assertStringContainsString('<strong>Club event</strong>', $html);
        self::assertCount(1, $crawler->filter('template a[href="/actus/club-news"]'));
        self::assertCount(0, $crawler->filter('a[href="/joueurs"]'));

        $event->setRelatedNews(null);
        $html = $twig->render('events/index.html.twig', ['events' => [$event]]);
        self::assertStringNotContainsString('/actus/club-news', $html);

        $html = $twig->render('events/index.html.twig', ['events' => []]);
        self::assertSame([], json_decode((new Crawler($html))->filter('.event-calendar')->attr('data-events'), true));
    }

    private function createEvent(): CalendarEvent
    {
        return (new CalendarEvent())
            ->setTitle('Club event')
            ->setDescription('<p><strong>Club event</strong></p>')
            ->setStartAt(new \DateTime('2026-10-14 10:00'))
            ->setEndAt(new \DateTime('2026-10-14 12:00'));
    }
}
