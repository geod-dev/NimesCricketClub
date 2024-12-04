<?php

namespace App\Field;

use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class PublicImageField
{
    static function new(string $path, string $label = "Image"): ImageField
    {
        return ImageField::new('image')
            ->setLabel($label)
            ->setUploadedFileNamePattern('[year]-[month]-[day]-[uuid].[extension]')
            ->setUploadDir('public/' . $path)
            ->setBasePath($path)
            ->setRequired(false);
    }
}
