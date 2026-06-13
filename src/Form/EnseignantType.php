<?php

namespace App\Form;

use App\Entity\Enseignant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnseignantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Nom'
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Prénom'
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Email'
            ])
            ->add('password', PasswordType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Mot de passe',
                'required' => false,
                'mapped' => false,
                'help' => 'Laisser vide pour conserver le mot de passe actuel'
            ])
            ->add('specialite', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Spécialité'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Enseignant::class,
        ]);
    }
}