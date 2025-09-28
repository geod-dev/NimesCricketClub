<?php

namespace App\Controller\Admin;

use App\Entity\NewsletterEmail;
use App\Service\NewsletterService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class NewsletterEmailCrudController extends AbstractCrudController
{
    public function __construct(private readonly NewsletterService $newsletterService)
    {
    }

    public static function getEntityFqcn(): string
    {
        return NewsletterEmail::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('subject', 'Subject');
        yield TextEditorField::new('content', 'Content')
            ->setNumOfRows(18);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::EDIT, Action::DELETE, Action::BATCH_DELETE);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof NewsletterEmail) {
            parent::persistEntity($entityManager, $entityInstance);
            return;
        }

        parent::persistEntity($entityManager, $entityInstance);

        // After persisting, send the newsletter to all subscribers
        $this->newsletterService->sendCustomNewsletter($entityInstance->getSubject(), $entityInstance->getContent());

        $this->addFlash('success', 'Newsletter sent to all active subscribers.');
    }
}
