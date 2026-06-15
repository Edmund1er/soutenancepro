<?php

namespace App\Validator;

use App\Entity\Soutenance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoSoutenanceCollisionValidator extends ConstraintValidator
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoSoutenanceCollision) {
            throw new UnexpectedTypeException($constraint, NoSoutenanceCollision::class);
        }

        if (null === $value || !$value instanceof Soutenance) {
            return;
        }

        $soutenance = $value;
        $date = $soutenance->getDate();
        $heure = $soutenance->getHeure();
        $salle = $soutenance->getSalle();
        $president = $soutenance->getPresident();
        $rapporteur = $soutenance->getRapporteur();
        $examinateur = $soutenance->getExaminateur();

        if (!$date || !$heure || !$salle || !$president) {
            return;
        }

        // 1. Vérification des doublons internes au jury
        $jury = array_filter([$president, $rapporteur, $examinateur]);
        $juryIds = array_map(fn($e) => $e->getId(), $jury);
        
        if (count($juryIds) !== count(array_unique($juryIds))) {
            $this->context->buildViolation($constraint->messageJuryDoublon)
                ->atPath('president')
                ->addViolation();
            return;
        }

        // Préparation de la requête pour les collisions temporelles (60 min)
        $heureTimestamp = $heure->getTimestamp();
        $heureDebut = (new \DateTime())->setTimestamp($heureTimestamp - 3600);
        $heureFin = (new \DateTime())->setTimestamp($heureTimestamp + 3600);

        $repo = $this->entityManager->getRepository(Soutenance::class);
        $qb = $repo->createQueryBuilder('s')
            ->where('s.date = :date')
            ->andWhere('s.heure BETWEEN :heureDebut AND :heureFin')
            ->setParameter('date', $date)
            ->setParameter('heureDebut', $heureDebut)
            ->setParameter('heureFin', $heureFin);

        if ($soutenance->getId()) {
            $qb->andWhere('s.id != :id')
               ->setParameter('id', $soutenance->getId());
        }

        // 2. Collision Salle
        $collisionSalle = (clone $qb)
            ->andWhere('s.salle = :salle')
            ->setParameter('salle', $salle)
            ->getQuery()
            ->getOneOrNullResult();

        if ($collisionSalle) {
            $this->context->buildViolation($constraint->messageSalleCollision)
                ->setParameter('%heure%', $collisionSalle->getHeure()->format('H:i'))
                ->atPath('salle')
                ->addViolation();
        }

        // 3. Collision Enseignants (tous rôles confondus)
        foreach ($jury as $enseignant) {
            $collisionEnseignant = (clone $qb)
                ->andWhere('s.president = :enseignant OR s.rapporteur = :enseignant OR s.examinateur = :enseignant')
                ->setParameter('enseignant', $enseignant)
                ->getQuery()
                ->getOneOrNullResult();

            if ($collisionEnseignant) {
                $role = 'examinateur';
                if ($collisionEnseignant->getPresident() === $enseignant) $role = 'président';
                elseif ($collisionEnseignant->getRapporteur() === $enseignant) $role = 'rapporteur';

                $this->context->buildViolation($constraint->messageEnseignantCollision)
                    ->setParameter('%nom%', $enseignant->getNom() . ' ' . $enseignant->getPrenom())
                    ->setParameter('%role%', $role)
                    ->setParameter('%heure%', $collisionEnseignant->getHeure()->format('H:i'))
                    ->atPath('president') // On met l'erreur sur le champ président par défaut ou le champ concerné ? 
                    // Idéalement on devrait cibler le champ qui cause le conflit dans l'objet actuel.
                    ->addViolation();
                break; // Une seule erreur d'enseignant suffit
            }
        }
    }
}
