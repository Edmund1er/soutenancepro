<?php

namespace App\Form;

use App\Entity\Enseignant;
use App\Entity\Etudiant;
use App\Entity\Salle;
use App\Entity\Soutenance;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SoutenanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Date',
                'required' => true,
            ])
            ->add('heure', TimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Heure',
                'required' => true,
            ])
            ->add('etudiant', EntityType::class, [
                'class' => Etudiant::class,
                'choice_label' => function(Etudiant $etudiant) {
                    return $etudiant->getNom() . ' ' . $etudiant->getPrenom() . ' (' . $etudiant->getFiliere() . ')';
                },
                'attr' => ['class' => 'form-control'],
                'label' => 'Etudiant',
                'required' => true,
                'query_builder' => function(EntityRepository $er) {
                    // Exclure les etudiants qui ont deja une soutenance
                    return $er->createQueryBuilder('e')
                        ->leftJoin('e.soutenance', 's')
                        ->where('s.id IS NULL');
                },
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => function(Salle $salle) {
                    return $salle->getCode() . ' (' . $salle->getCapacite() . ' places)';
                },
                'attr' => ['class' => 'form-control'],
                'label' => 'Salle',
                'required' => true,
            ])
            ->add('president', EntityType::class, [
                'class' => Enseignant::class,
                'choice_label' => function(Enseignant $enseignant) {
                    return $enseignant->getNom() . ' ' . $enseignant->getPrenom() . ' (' . $enseignant->getSpecialite() . ')';
                },
                'attr' => ['class' => 'form-control'],
                'label' => 'President du jury',
                'required' => true,
            ])
            ->add('rapporteur', EntityType::class, [
                'class' => Enseignant::class,
                'choice_label' => function(Enseignant $enseignant) {
                    return $enseignant->getNom() . ' ' . $enseignant->getPrenom() . ' (' . $enseignant->getSpecialite() . ')';
                },
                'attr' => ['class' => 'form-control'],
                'label' => 'Rapporteur (optionnel)',
                'required' => false,
                'placeholder' => '-- Aucun --',
            ])
            ->add('examinateur', EntityType::class, [
                'class' => Enseignant::class,
                'choice_label' => function(Enseignant $enseignant) {
                    return $enseignant->getNom() . ' ' . $enseignant->getPrenom() . ' (' . $enseignant->getSpecialite() . ')';
                },
                'attr' => ['class' => 'form-control'],
                'label' => 'Examinateur (optionnel)',
                'required' => false,
                'placeholder' => '-- Aucun --',
            ]);
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Soutenance::class,
            'csrf_protection' => true,
        ]);
    }
}