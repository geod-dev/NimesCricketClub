<?php

namespace App\Controller\Admin;

use App\Entity\Attachment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Attribute\Route;

class AttachmentsController extends AbstractController
{
    #[Route('/admin/attachments/{path}', 'app_admin_attachments')]
    public function index(Attachment $attachment): BinaryFileResponse
    {
        $path = $this->getParameter("attachments_path") . "/" . $attachment->getPath();
        return $this->file($path, $attachment->getName());
    }
}
