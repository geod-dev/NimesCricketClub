<?php

namespace App\Controller;

use App\Entity\Attachment;
use App\Entity\ContactSubmission;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(
        Request $request,
        Filesystem $filesystem,
        TransportInterface $mailer,
        EntityManagerInterface $entityManager
    ): Response {
        $attachmentsPath = $this->getParameter("attachments_path");

        $contactSubmission = new ContactSubmission();
        $form = $this->createForm(ContactType::class, $contactSubmission);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->get('attachments')->getData() as $file) {
                $extension = $file->getClientOriginalExtension();
                $path = Uuid::v4()->toRfc4122() . '.' . $extension;
                $fullPath = $attachmentsPath . '/' . $path;
                $filesystem->dumpFile($fullPath, $file->getContent());

                $attachment = new Attachment();
                $attachment->setName($file->getClientOriginalName())->setPath($path)->setIsPrivate(true);
                $contactSubmission->addAttachment($attachment);
                $entityManager->persist($attachment);
            }

            $email = (new Email());

            $email
                ->from(new Address('contact@nimescricketclub.fr', $contactSubmission->getName()))
                ->subject('Message de ' . $contactSubmission->getName() . ' via le formulaire de contact')
                ->to('contact@nimescricketclub.fr')
                ->text('Email: ' . $contactSubmission->getEmail() . "\n\n" .$contactSubmission->getContent());

            foreach ($contactSubmission->getAttachments() as $attachment) {
                $path = $attachmentsPath . '/' . $attachment->getPath();
                $email->addPart(new DataPart(new File($path), $attachment->getName()));
            }

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Votre message a été envoyé !');
                $entityManager->persist($contactSubmission);
                $entityManager->flush();
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi de votre message !');
                foreach ($contactSubmission->getAttachments() as $attachment) {
                    $path = $attachmentsPath . '/' . $attachment->getPath();
                    $filesystem->remove($path);
                }
            }

            return $this->redirectToRoute('app_contact');
        }


        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
