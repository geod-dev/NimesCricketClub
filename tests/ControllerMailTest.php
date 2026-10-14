<?php

namespace App\Tests;

use App\Controller\SecurityController;
use App\Controller\StripeWebhookController;
use App\Entity\CustomerFormEntry;
use App\Entity\Attachment;
use App\Entity\User;
use App\Repository\CustomerFormEntryRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class ControllerMailTest extends TestCase
{
    public function testAdminAttachmentDownloadRequiresAdminEvenWithoutFirewallPathMatch(): void
    {
        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $authorization->expects(self::once())->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $container = new Container();
        $container->set('security.authorization_checker', $authorization);
        $controller = new \App\Controller\Admin\AttachmentsController();
        $controller->setContainer($container);

        $this->expectException(AccessDeniedException::class);
        $controller->index(new Attachment());
    }

    public function testPaidWebhookSendsConfirmationAndPersistsPayment(): void
    {
        $secret = 'test-secret';
        $payload = json_encode([
            'id' => 'evt_test', 'object' => 'event', 'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_test', 'object' => 'checkout.session', 'status' => 'complete', 'payment_status' => 'paid',
            ]],
        ]);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        $request = Request::create('/stripe-webhook', 'POST', server: [
            'HTTP_STRIPE_SIGNATURE' => "t=$timestamp,v1=$signature",
        ], content: $payload);
        $entry = (new CustomerFormEntry())->setEmail('member@example.org');
        $repository = $this->createMock(CustomerFormEntryRepository::class);
        $repository->method('findOneBy')->with(['checkoutSessionId' => 'cs_test'])->willReturn($entry);
        $mail = $this->createMock(MailService::class);
        $mail->expects(self::once())->method('sendMessage')->with(
            'member@example.org', 'Nîmes Cricket Club', 'Confirmation de paiement - Nîmes Cricket Club', 'Paid'
        );
        $manager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $manager->expects(self::exactly(2))->method('flush');
        $manager->method('wrapInTransaction')->willReturnCallback(function (callable $callback) use ($manager) {
            $callback($manager);
            $manager->flush();
        });
        $container = new Container();
        $container->setParameter('stripe_webhook_secret', $secret);
        $container->set('parameter_bag', new ContainerBag($container));
        $container->set('twig', new Environment(new ArrayLoader(['emails/payment_confirmation.html.twig' => 'Paid'])));
        $controller = new StripeWebhookController();
        $controller->setContainer($container);

        self::assertSame(200, $controller->index($request, $repository, $mail, $manager)->getStatusCode());
        self::assertTrue($entry->isPaid());
        self::assertNotNull($entry->getPaymentConfirmationSentAt());
        self::assertSame(200, $controller->index($request, $repository, $mail, $manager)->getStatusCode());
    }

    public function testAuthenticatedLoginRedirectsToCurrentAdminRoute(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())->method('generate')->with('admin', [])->willReturn('/admin');
        $tokens = new TokenStorage();
        $tokens->setToken(new UsernamePasswordToken(new User(), 'main', ['ROLE_ADMIN']));
        $container = new Container();
        $container->set('router', $router);
        $container->set('security.token_storage', $tokens);
        $controller = new SecurityController();
        $controller->setContainer($container);

        self::assertSame('/admin', $controller->login($this->createMock(AuthenticationUtils::class))->headers->get('Location'));
    }
}
