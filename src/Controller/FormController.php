<?php

namespace App\Controller;

use App\Entity\CustomerForm;
use App\Entity\CustomerFormEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FormController extends AbstractController
{
    #[Route('/form/{slug}', name: 'app_form')]
    public function index(CustomerForm $form): Response
    {
        return $this->render('form/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/form/{slug}/submit', name: 'app_form_submit')]
    public function submit(
        Request                $request,
        CustomerForm           $form,
        EntityManagerInterface $entityManager,
        TranslatorInterface    $translator
    ): Response
    {
        $email = (string)$request->request->get('email');
        $firstName = (string)$request->request->get('first_name');
        $lastName = (string)$request->request->get('last_name');
        $phone = (string)$request->request->get('phone');
        $data = (array)json_decode($request->request->get('data'));
        if (!$email || !$firstName || !$lastName || !$phone || !$data) throw new BadRequestHttpException('Invalid form data.');

        $paymentType = $request->request->get('payment_type', 'onsite');
        $price = $form->calculatePrice($data);

        $entry = new CustomerFormEntry();
        $entry->setForm($form)
            ->setData($data)
            ->setPrice($price)
            ->setEmail($email)
            ->setPhone($phone)
            ->setFirstName($firstName)
            ->setLastName($lastName);

        $entityManager->persist($entry);
        $entityManager->flush();
        $this->addFlash('success', $translator->trans('alert.form_submission.success'));
        if ($paymentType === 'online' && $price) return $this->redirectToRoute('app_form_checkout', ['id' => $entry->getId()]);
        else return $this->redirectToRoute('app_home');
    }
}
