<?php

namespace App\Controller\Admin;

use App\Entity\CalendarEvent;
use App\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CalendarEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CalendarEvent::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['startAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title');
        yield DateTimeField::new('startAt', 'Starts at');
        yield DateTimeField::new('endAt', 'Ends at');
        yield ColorField::new('color');
        yield TextEditorField::new('description')->hideOnIndex();
        yield AssociationField::new('relatedNews', 'Related news')
            ->setRequired(false)
            ->setFormTypeOption('choice_label', fn (News $news) => $news->getTitleFr())
            ->hideOnIndex();
        yield DateTimeField::new('createdAt')->onlyOnDetail();
        yield DateTimeField::new('updatedAt')->onlyOnDetail();
    }
}
