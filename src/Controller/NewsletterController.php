<?php

namespace App\Controller;

use App\Entity\NewsletterSubscriber;
use App\Service\NewsletterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NewsletterController extends AbstractController
{
    #[Route('/newsletter/subscribe', name: 'app_newsletter_subscribe')]
    public function subscribe(
        Request             $request,
        NewsletterService   $newsletterService,
        TranslatorInterface $translator
    ): Response
    {
        $email = $request->request->get('email');
        if (!$email) throw new BadRequestHttpException('email is required');
        $newsletterService->subscribeEmail($email);
        $this->addFlash('success', $translator->trans('newsletter.subscribed'));
        return $this->redirectToRoute('app_home');
    }

    #[Route('/newsletter/unsubscribe/{unsubscribeToken}', name: 'app_newsletter_unsubscribe')]
    public function unsubscribe(
        TranslatorInterface    $translator,
        NewsletterSubscriber   $subscriber,
        EntityManagerInterface $entityManager
    ): Response
    {
        $subscriber->setIsSubscribed(false);
        $entityManager->flush();
        $this->addFlash('success', $translator->trans('newsletter.unsubscribed'));
        return $this->redirectToRoute('app_home');
    }
}
