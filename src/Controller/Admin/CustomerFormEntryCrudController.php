<?php

namespace App\Controller\Admin;

use App\Entity\CustomerFormEntry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomerFormEntryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CustomerFormEntry::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Form submission')
            ->setEntityLabelInPlural('Form submissions')
            ->setPageTitle('index', 'Customer Form Submissions')
            ->showEntityActionsInlined()
            ->overrideTemplate('crud/detail', 'admin/crud/customer_form_entry/detail.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('form'),
            TextField::new('email'),
            TextField::new('first_name'),
            TextField::new('last_name'),
            TextField::new('phone')
        ];
    }
}
