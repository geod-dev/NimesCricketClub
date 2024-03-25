<?php

namespace App\Form;

use App\Entity\ContactSubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('content', TextareaType::class)
            ->add('attachments', FileType::class, [
                'multiple' => true,
                'mapped' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactSubmission::class,

            // Time protection
            'antispam_time'     => true,
            'antispam_time_min' => 5, // seconds

            // Honeypot protection
            'antispam_honeypot'       => true,
            'antispam_honeypot_class' => 'hidden',
            'antispam_honeypot_field' => 'email-repeat',
        ]);
    }
}
