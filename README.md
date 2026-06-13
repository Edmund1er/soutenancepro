markdown

# SoutenancePro

Plateforme de gestion des soutenances de fin d'etudes

## Description

SoutenancePro est une application web developpee avec Symfony permettant aux etablissements d'enseignement superieur de gerer l'ensemble du processus de soutenance des memoires de fin d'etudes. L'application offre une interface complete pour les administrateurs et un espace dedie pour les enseignants.

## Fonctionnalites

### Administrateur

- Gestion des etudiants (creation, modification, suppression, recherche)
- Gestion des enseignants (creation, modification, suppression, recherche)
- Gestion des salles (creation, modification, suppression)
- Gestion des soutenances (programmation, modification, annulation, recherche par date)
- Tableau de bord avec statistiques et graphiques
- Verification automatique des conflits de planning (intervalle d'une heure)
- Pagination (6 elements par page)
- Interface responsive avec template AblePro

### Enseignant

- Tableau de bord personnalise avec statistiques
- Consultation de ses soutenances (president, rapporteur, examinateur)
- Consultation de la composition des jurys
- Pagination (5 elements par page)

## Technologies utilisees

- Symfony 6.4
- PHP 8.2
- Doctrine ORM
- MySQL 8.0
- Twig
- Bootstrap 4 (AblePro)
- Chart.js / ApexCharts
- FontAwesome

## Installation

### Pre-requis

- PHP 8.2 ou superieur
- Composer
- MySQL 8.0
- Symfony CLI (optionnel)

### Etapes d'installation

1. Cloner le projet

```bash
git clone https://github.com/Edmund1er/soutenancepro.git
cd soutenance-pro

    Installer les dependances

bash

composer install

    Configurer la base de donnees

Copier le fichier .env et modifier les identifiants de connexion MySQL :
bash

cp .env .env.local

Modifier la variable DATABASE_URL :
text

DATABASE_URL="mysql://utilisateur:motdepasse@127.0.0.1:3306/soutenancepro?serverVersion=8.0"

    Creer la base de donnees

bash

php bin/console doctrine:database:create

    Executer les migrations

bash

php bin/console doctrine:migrations:migrate

    Charger les donnees de test (fixtures)

bash

php bin/console doctrine:fixtures:load

    Demarrer le serveur

bash

symfony server:start

ou
bash

php -S 127.0.0.1:8000 -t public

Acces par defaut
Role	Email	Mot de passe
Administrateur	admin@soutenancepro.com	admin123
Enseignant	jean.dupont@email.com	password123
Structure du projet
text

soutenance-pro/
├── src/
│   ├── Controller/
│   │   ├── Admin/
│   │   │   ├── EtudiantController.php
│   │   │   ├── EnseignantController.php
│   │   │   ├── SalleController.php
│   │   │   └── SoutenanceController.php
│   │   ├── Enseignant/
│   │   │   └── EnseignantDashboardController.php
│   │   ├── HomeController.php
│   │   └── SecurityController.php
│   ├── Entity/
│   │   ├── User.php
│   │   ├── Etudiant.php
│   │   ├── Enseignant.php
│   │   ├── Salle.php
│   │   └── Soutenance.php
│   ├── Form/
│   │   ├── EtudiantType.php
│   │   ├── EnseignantType.php
│   │   ├── SalleType.php
│   │   └── SoutenanceType.php
│   └── DataFixtures/
│       └── AppFixtures.php
├── templates/
│   ├── admin/
│   │   ├── dashboard.html.twig
│   │   ├── etudiant/
│   │   ├── enseignant/
│   │   ├── salle/
│   │   └── soutenance/
│   ├── enseignant_ui/
│   │   ├── dashboard.html.twig
│   │   ├── mes_soutenances.html.twig
│   │   └── mes_jurys.html.twig
│   ├── home/
│   │   └── index.html.twig
│   └── security/
│       └── login.html.twig
├── public/
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
├── config/
├── migrations/
└── composer.json

Regles de gestion

    Conflits de planning

        Une meme salle ne peut pas etre utilisee a la meme date et heure

        Un enseignant ne peut pas etre dans deux jurys different a la meme date et heure

        Verification sur un intervalle d'une heure

    Suppression protegee

        Un etudiant ayant une soutenance ne peut pas etre supprime

        Un enseignant membre d'un jury ne peut pas etre supprime

        Une salle avec des soutenances programmees ne peut pas etre supprimee

    Roles automatiques

        Un enseignant cree recoit automatiquement le role ROLE_ENSEIGNANT

