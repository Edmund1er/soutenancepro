<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class NoSoutenanceCollision extends Constraint
{
    public $messageJuryDoublon = 'Un enseignant ne peut pas occuper plusieurs rôles dans le même jury.';
    public $messageSalleCollision = 'La salle est déjà occupée à %heure% (intervalle de 60 min).';
    public $messageEnseignantCollision = 'L\'enseignant %nom% est déjà occupé comme %role% à %heure% (intervalle de 60 min).';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
