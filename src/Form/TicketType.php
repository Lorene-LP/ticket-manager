<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Ticket;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('authorEmail', EmailType::class, [
                'label' => 'Votre adresse e-mail',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'votre.email@example.com'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du problème',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Décrivez votre problème en détail (entre 20 et 250 caractères)...'
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'attr' => [
                    'class' => 'form-select'
                ],
                'placeholder' => 'Choisissez une catégorie...'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer le ticket',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}