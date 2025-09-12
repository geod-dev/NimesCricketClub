<?php

namespace App\Controller\Admin;

use App\Entity\News;
use App\Field\PublicImageField;
use App\Form\LinkType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class NewsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return News::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('titleFr', 'Title');
        yield SlugField::new('slug')->setTargetFieldName('titleFr');
        yield PublicImageField::new(News::UPLOAD_PATH);
        yield TextEditorField::new('contentFr', 'Content');
        yield BooleanField::new('isPublished', 'Published')
            ->setHelp('When published, news will be available and newsletter will be sent to subscribers.')
            ->onlyOnForms();
        yield CollectionField::new('links', 'Links')
            ->setEntryType(LinkType::class)
            ->allowAdd()
            ->allowDelete()
            ->hideOnIndex();
    }
}
