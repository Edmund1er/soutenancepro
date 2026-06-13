<?php

namespace App\Form;

use App\Entity\Soutenance;
use App\Entity\Etudiant;
use App\Entity\Salle;
use App\Entity\Enseignant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SoutenanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Date'
            ])
            ->add('heure', TimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Heure'
            ])
            ->add('etudiant', EntityType::class, [
                'class' => Etudiant::class,
                'choice_label' => function(Etudiant $etudiant) {
                    return $etudiant->getNom() . ' ' . $etudiant->getPrenom() . ' (' . $etudiant->getEmail() . ')';
                },
                'attr' => ['class' => 'form-control'],
                'label' => 'Étudiant'
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => function(Salle $salle) {
                    return $salle->getCode() . ' - ' . $salle->getLocalisation() . ' (' . $salle->getCapacite() . ' places)';
                },
                'attr' => ['class' => 'form-control'],
                'label' => 'Salle'
            ])
            ->add('president', EntityType::class, [
                'class' => Enseignant::class,
                'choice_label' => function(Enseignant $enseignant) {
                    return $enseignant->getNom() . ' ' . $enseignant->getPrenom() . ' (' . $enseignant->getSpecialite() . ')';
                },
                'attr' => ['class' => 'form-control'],
                'label' => 'Président'
            ])
            ->add('rapporteur', EntityType::class, [
                'class' => Enseignant::class,
                'choice_label' => function(Enseignant $enseignant) {
                    return $enseignant->getNom() . ' ' . $enseignant->getPrenom() . ' (' . $enseignant->getSpecialite() . ')';
                },
                'attr' => ['class' => 'form-control'],
                'label' => 'Rapporteur'
            ])
            ->add('examinateur', EntityType::class, [
                'class' => Enseignant::class,
                'choice_label' => function(Enseignant $enseignant) {
                    return $enseignant->getNom() . ' ' . $enseignant->getPrenom() . ' (' . $enseignant->getSpecialite() . ')';
                },
                'attr' => ['class' => 'form-control'],
                'label' => 'Examinateur'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Soutenance::class,
        ]);
    }
}