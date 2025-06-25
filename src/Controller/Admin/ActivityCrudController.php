<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use App\Field\PublicImageField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ActivityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Activity::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titleFr', 'Activity Name'),
            SlugField::new('slug')->setTargetFieldName('titleFr'),
            PublicImageField::new(Activity::UPLOAD_PATH),
            TextEditorField::new('contentFr', 'content'),
        ];
    }
}
