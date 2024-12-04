<?php

namespace App\Controller;

use App\Entity\Attachment;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AttachmentsController extends AbstractController
{
    #[Route('/admin/attachments', name: 'app_admin_attachments_insert', methods: ['POST'])]
    public function insert(
        Request                $request,
        ValidatorInterface     $validator,
        EntityManagerInterface $entityManager
    ): Response
    {
        $file = $request->files->get('file');
        $name = $request->request->get('name');

        if (!$file instanceof UploadedFile || empty($name)) {
            throw new BadRequestHttpException('Invalid file or name.');
        }

        $uploadsDirectory = $this->getParameter('attachments_path');
        $fileName = Uuid::v4()->toRfc4122() . '.' . $file->guessExtension();

        try {
            $file->move($uploadsDirectory, $fileName);
        } catch (Exception) {
            throw new BadRequestHttpException('Failed to save file.');
        }

        $attachment = new Attachment();
        $attachment->setName($name);
        $attachment->setPath($fileName);

        $errors = $validator->validate($attachment);
        if (count($errors) > 0) {
            throw new BadRequestHttpException($errors);
        }

        $entityManager->persist($attachment);
        $entityManager->flush();

        $accessUrl = $this->generateUrl('app_attachments_get', [
            'path' => $attachment->getPath(),
        ]);

        return new Response($accessUrl, Response::HTTP_CREATED);
    }

    #[Route('/attachments/{path}', 'app_attachments_get', methods: 'GET')]
    public function get(Attachment $attachment): BinaryFileResponse
    {
        if ($attachment->isPrivate()) $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $path = $this->getParameter("attachments_path") . "/" . $attachment->getPath();
        return $this->file($path, $attachment->getName());
    }

    #[Route('/attachments/{path}', 'app_attachments_delete', methods: 'DELETE')]
    public function delete(
        Attachment             $attachment,
        EntityManagerInterface $entityManager,
        Filesystem             $filesystem
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $path = $this->getParameter("attachments_path") . "/" . $attachment->getPath();
        $filesystem->remove($path);

        $entityManager->remove($attachment);
        return new Response("done");
    }
}
