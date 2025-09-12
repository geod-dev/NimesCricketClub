<?php

namespace App\Form;

use App\Entity\CustomerFormField;
use App\Utils\FieldTypeType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\SlugType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerFormFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        foreach (FieldTypeType::cases() as $case) {
            $choices[$case->name] = $case;
        }

        $builder
            ->add('nameFr')
            ->add('reference', SlugType::class, [
                'target' => 'nameFr',
            ])
            ->add('nameEn')
            ->add('type', ChoiceType::class, [
                'choices' => $choices
            ])
            ->add('isRequired');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerFormField::class,
        ]);
    }
}
