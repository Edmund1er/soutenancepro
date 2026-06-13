<?php

namespace App\DataFixtures;

use App\Entity\Enseignant;
use App\Entity\Etudiant;
use App\Entity\Salle;
use App\Entity\Soutenance;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ========== 1. CRÉATION DE L'ADMIN ==========
        $admin = new User();
        $admin->setEmail('admin@soutenancepro.com');
        $admin->setNom('Admin');
        $admin->setPrenom('Système');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // ========== 2. CRÉATION DES ENSEIGNANTS (7+) ==========
        $enseignants = [];
        $enseignantData = [
            ['Dupont', 'Jean', 'jean.dupont@email.com', 'Mathématiques Appliquées', 'Professeur Titulaire'],
            ['Martin', 'Sophie', 'sophie.martin@email.com', 'Informatique', 'Maître de Conférences'],
            ['Bernard', 'Pierre', 'pierre.bernard@email.com', 'Physique Quantique', 'Professeur'],
            ['Petit', 'Marie', 'marie.petit@email.com', 'Génie Civil', 'Maître de Conférences'],
            ['Robert', 'Philippe', 'philippe.robert@email.com', 'Droit des Affaires', 'Professeur Associé'],
            ['Richard', 'Catherine', 'catherine.richard@email.com', 'Marketing Digital', 'Enseignant Chercheur'],
            ['Durand', 'Michel', 'michel.durand@email.com', 'Finance', 'Professeur'],
            ['Lefebvre', 'Isabelle', 'isabelle.lefebvre@email.com', 'Ressources Humaines', 'Maître de Conférences'],
            ['Moreau', 'Nicolas', 'nicolas.moreau@email.com', 'Économie', 'Enseignant Vacataire'],
            ['Simon', 'Valérie', 'valerie.simon@email.com', 'Communication', 'Professeure'],
        ];

        foreach ($enseignantData as $data) {
            $enseignant = new Enseignant();
            $enseignant->setNom($data[0]);
            $enseignant->setPrenom($data[1]);
            $enseignant->setEmail($data[2]);
            $enseignant->setSpecialite($data[3]);
            $enseignant->setRoles(['ROLE_ENSEIGNANT']);
            $enseignant->setPassword($this->passwordHasher->hashPassword($enseignant, 'password123'));
            $manager->persist($enseignant);
            $enseignants[] = $enseignant;
        }

        // ========== 3. CRÉATION DES SALLES (7+) ==========
        $salles = [];
        $salleData = [
            ['A101', 30, 'Bâtiment A, 1er étage - Salle informatique'],
            ['A102', 25, 'Bâtiment A, 1er étage - Salle de cours'],
            ['A103', 20, 'Bâtiment A, 1er étage - Laboratoire'],
            ['B201', 50, 'Bâtiment B, 2ème étage - Amphithéâtre'],
            ['B202', 40, 'Bâtiment B, 2ème étage - Salle polyvalente'],
            ['C301', 35, 'Bâtiment C, 3ème étage - Salle de conférence'],
            ['C302', 20, 'Bâtiment C, 3ème étage - Bureau de soutenance'],
            ['D401', 15, 'Bâtiment D, 4ème étage - Salle de réunion'],
            ['D402', 45, 'Bâtiment D, 4ème étage - Grand amphithéâtre'],
        ];

        foreach ($salleData as $data) {
            $salle = new Salle();
            $salle->setCode($data[0]);
            $salle->setCapacite($data[1]);
            $salle->setLocalisation($data[2]);
            $manager->persist($salle);
            $salles[] = $salle;
        }

        // ========== 4. CRÉATION DES ÉTUDIANTS (50+ avec vraies données) ==========
        $filleres = [
            'Informatique de Gestion',
            'Réseaux et Télécoms', 
            'Génie Logiciel',
            'Sécurité Informatique',
            'Data Science',
            'Intelligence Artificielle',
            'Systèmes Embarqués'
        ];
        
        $themes = [
            'Développement d\'une application web de gestion de stock avec Symfony',
            'Implémentation d\'un système de recommandation IA pour e-commerce',
            'Sécurisation des API REST avec JWT et OAuth2',
            'Migration d\'une infrastructure legacy vers le cloud computing',
            'Analyse prédictive des ventes avec machine learning',
            'Développement d\'une application mobile cross-platform avec Flutter',
            'Optimisation des performances des bases de données NoSQL',
            'Mise en place d\'une architecture microservices avec Docker',
            'Développement d\'un chatbot intelligent pour le support client',
            'Analyse de données massives avec Hadoop et Spark',
            'Création d\'une plateforme e-learning interactive',
            'Système de gestion électronique de documents (GED)',
        ];

        $etudiants = [];
        $noms = ['Diop', 'Fall', 'Sow', 'Ndiaye', 'Ba', 'Diallo', 'Mbaye', 'Gueye', 'Sy', 'Kane'];
        $prenoms = ['Aliou', 'Fatou', 'Moussa', 'Aminata', 'Cheikh', 'Mariama', 'Papa', 'Aissatou', 'Ibrahima', 'Khady'];
        
        for ($i = 1; $i <= 50; $i++) {
            $etudiant = new Etudiant();
            $etudiant->setNom($noms[array_rand($noms)] . ($i % 10));
            $etudiant->setPrenom($prenoms[array_rand($prenoms)]);
            $etudiant->setEmail(strtolower($etudiant->getPrenom() . '.' . $etudiant->getNom() . $i . '@edu.sn'));
            $etudiant->setFiliere($filleres[array_rand($filleres)]);
            $etudiant->setThemeMemoire($themes[array_rand($themes)]);
            $manager->persist($etudiant);
            $etudiants[] = $etudiant;
        }

        $manager->flush();

        // ========== 5. CRÉATION DES SOUTENANCES (20+ sur différents jours) ==========
        $dates = [
            new \DateTime('+1 days 09:00:00'),
            new \DateTime('+1 days 11:00:00'),
            new \DateTime('+1 days 14:00:00'),
            new \DateTime('+2 days 10:00:00'),
            new \DateTime('+2 days 13:30:00'),
            new \DateTime('+2 days 15:00:00'),
            new \DateTime('+3 days 09:30:00'),
            new \DateTime('+3 days 11:30:00'),
            new \DateTime('+3 days 14:30:00'),
            new \DateTime('+4 days 10:00:00'),
            new \DateTime('+4 days 14:00:00'),
            new \DateTime('+5 days 09:00:00'),
            new \DateTime('+5 days 13:00:00'),
            new \DateTime('+5 days 15:30:00'),
            new \DateTime('+6 days 10:30:00'),
            new \DateTime('+6 days 14:00:00'),
            new \DateTime('+7 days 09:00:00'),
            new \DateTime('+7 days 11:00:00'),
            new \DateTime('+7 days 14:00:00'),
            new \DateTime('+8 days 10:00:00'),
            new \DateTime('+8 days 13:00:00'),
            new \DateTime('+9 days 09:30:00'),
            new \DateTime('+9 days 14:30:00'),
            new \DateTime('+10 days 10:00:00'),
            new \DateTime('+10 days 15:00:00'),
        ];

        // Créer 25 soutenances
        for ($i = 0; $i < 25; $i++) {
            $soutenance = new Soutenance();
            $soutenance->setEtudiant($etudiants[$i % count($etudiants)]);
            $soutenance->setDate($dates[$i % count($dates)]);
            $soutenance->setHeure($dates[$i % count($dates)]);
            $soutenance->setSalle($salles[$i % count($salles)]);
            $soutenance->setPresident($enseignants[$i % count($enseignants)]);
            
            // Ajout rapporteur (différent du président)
            $rapporteurIndex = ($i + 1) % count($enseignants);
            if ($rapporteurIndex != ($i % count($enseignants))) {
                $soutenance->setRapporteur($enseignants[$rapporteurIndex]);
            }
            
            // Ajout examinateur (différent des deux précédents)
            $examinateurIndex = ($i + 2) % count($enseignants);
            if ($examinateurIndex != ($i % count($enseignants)) && $examinateurIndex != $rapporteurIndex) {
                $soutenance->setExaminateur($enseignants[$examinateurIndex]);
            }
            
            $manager->persist($soutenance);
        }

        $manager->flush();
        
        // ========== 6. MESSAGES DE CONFIRMATION ==========
        echo "\nFixtures chargées avec succès !\n";
        echo "- Administrateur: 1\n";
        echo "- Enseignants: " . count($enseignants) . "\n";
        echo "- Salles: " . count($salles) . "\n";
        echo "- Étudiants: 50\n";
        echo "- Soutenances: 25\n";
        echo "\n Accès admin: admin@soutenancepro.com / admin123\n";
        echo "Accès enseignant: jean.dupont@email.com / password123\n";
    }
}