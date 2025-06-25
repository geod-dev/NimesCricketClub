<?php

namespace App\Controller\Admin;

use App\Entity\News;
use App\Field\PublicImageField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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
        return [
            TextField::new('titleFr', 'Title'),
            SlugField::new('slug')->setTargetFieldName('titleFr'),
            PublicImageField::new(News::UPLOAD_PATH),
            TextEditorField::new('contentFr', 'Content'),
        ];
    }
}
