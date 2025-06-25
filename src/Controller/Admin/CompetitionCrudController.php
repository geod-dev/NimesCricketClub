<?php

namespace App\Controller\Admin;

use App\Entity\Competition;
use App\Field\PublicImageField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CompetitionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Competition::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titleFr', 'Competition Name')->setColumns("col-md-9 col-lg-6"),

            FormField::addRow(),

            TextField::new("personalPoints")->setColumns("col-md-6 col-xl-3"),
            TextField::new("opponentPoints")->setColumns("col-md-6 col-xl-3"),

            FormField::addRow(),

            NumberField::new("personalOvers")->setColumns("col-md-6 col-xl-3"),
            NumberField::new("opponentOvers")->setColumns("col-md-6 col-xl-3"),

            FormField::addRow(),

            TextField::new('opponentName')->setColumns("col-md-6 col-xl-3"),
            PublicImageField::new(Competition::UPLOAD_PATH, "Opponent Logo")->setColumns("col-md-6 col-xl-3"),

            FormField::addRow(),

            DateField::new('eventDate')->setColumns(3),
            BooleanField::new('win')->setLabel("Did NCC win ?")->setColumns(3),

            FormField::addRow(),

            TextEditorField::new('contentFr', 'description'),
        ];
    }
}
