<?php

namespace App\Controller\Admin;

use App\Entity\CustomerForm;
use App\Form\CustomerFormFieldType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomerFormCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CustomerForm::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $inForm = $pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW;
        yield TextField::new('titleFr', 'Title');
        yield SlugField::new('slug')->setTargetFieldName('titleFr');
        yield BooleanField::new('isRepeatable', 'Repeatable');
        yield TextField::new('entityNameFr', 'Entity french name (only repeatable)')->hideOnIndex();
        yield TextField::new('entityNameEn', 'Entity english name (only repeatable)')->hideOnIndex();
        yield TextField::new('price', 'Price');
        yield TextEditorField::new('contentFr', 'Content');
        yield CollectionField::new('fields', 'Fields' . ($pageName === 'edit' ? ' - Email field will be added automatically' : ''))
            ->setEntryType(CustomerFormFieldType::class)
            ->allowAdd()
            ->allowDelete();
    }
}
