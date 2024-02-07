<?php

namespace App\Field;

use App\Entity\Activity;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class PublicImageField
{
    static function new(string $path): ImageField
    {
        return ImageField::new('image')
            ->setUploadedFileNamePattern('[year]-[month]-[day]-[uuid].[extension]')
            ->setUploadDir('public/' . $path)
            ->setBasePath($path)
            ->setRequired(false);
    }
}