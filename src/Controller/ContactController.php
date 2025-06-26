<?php

namespace App\Controller;

use App\Entity\ContactSubmission;
use App\Exception\SpamDetectedException;
use App\Form\ContactType;
use App\Service\ContactFormService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(
        Request            $request,
        ContactFormService $contactFormService
    ): Response
    {
        $contactSubmission = new ContactSubmission();
        $form = $this->createForm(ContactType::class, $contactSubmission);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $contactFormService->handleFormSubmission($form, $contactSubmission);
                $this->addFlash('success', 'Votre message a été envoyé !');
            } catch (TransportExceptionInterface) {
                $this->addFlash('error', 'Erreur lors de l\'envoi de votre message !');
            } catch (SpamDetectedException) {
                $this->addFlash('error', 'Ce mail a été détecté comme spam !');
            }

            return $this->redirectToRoute('app_contact');
        }


        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
