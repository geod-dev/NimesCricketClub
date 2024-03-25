<?php

namespace App\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AttachmentsField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): AttachmentsField
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)

            ->setTemplatePath('admin/field/attachments.html.twig')

            ->setFormType(TextType::class)
            ;
    }
}
