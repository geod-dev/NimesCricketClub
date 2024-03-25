<?php

namespace App\Controller\Admin;

use App\Entity\ContactSubmission;
use App\Field\AttachmentsField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ContactSubmissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ContactSubmission::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Contact')
            ->setEntityLabelInPlural('Contacts')
            ->setPageTitle('index', 'Contact Form Submissions');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name'),
            EmailField::new('email'),
            TextEditorField::new('content', 'Message')->hideOnIndex(),
            AttachmentsField::new('attachments'),
            DateTimeField::new('createdAt', "Creation Date")->setFormat('dd MMMM yyyy - hh:mm')->hideOnIndex(),
//            SlugField::new('slug')->setTargetFieldName('title'),
//            PublicImageField::new(News::UPLOAD_PATH),
//            TextEditorField::new('content'),
        ];
    }
}
