# 🎓 SoutenancePro

> **Plateforme de gestion des soutenances de fin d'études.**

SoutenancePro est une application web moderne développée avec **Symfony** permettant aux établissements d'enseignement supérieur de gérer l'intégralité du processus de soutenance des mémoires.

---

## 📋 Description

L'application offre une interface complète pour les administrateurs et un espace dédié pour les enseignants, facilitant la coordination entre les étudiants, les jurys et l'administration.

## ✨ Fonctionnalités

### 👨‍💼 Espace Administrateur
- **Gestion des Étudiants** : Création, modification, suppression et recherche.
- **Gestion des Enseignants** : Gestion complète du corps professoral.
- **Gestion des Salles** : Optimisation de l'occupation des locaux.
- **Gestion des Soutenances** : Programmation intelligente avec **vérification automatique des conflits** (intervalle d'une heure).
- **Dashboard** : Statistiques et graphiques interactifs.
- **Pagination** : Navigation fluide (6 éléments par page).

### 👨‍🏫 Espace Enseignant
- **Dashboard Personnalisé** : Vue d'ensemble de ses activités.
- **Mes Soutenances** : Consultation des rôles (Président, Rapporteur, Examinateur).
- **Composition des Jurys** : Détails complets sur les membres du jury.
- **Pagination** : Optimisée pour la consultation (5 éléments par page).

---

## 🛠️ Technologies Utilisées

- ![Symfony](https://img.shields.io/badge/Symfony-6.4-black?style=flat-square&logo=symfony)
- ![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php)
- ![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
- **Frontend** : Twig, Bootstrap 4 (AblePro), Chart.js, FontAwesome.

---

## 🚀 Installation

### 📋 Pré-requis
- [PHP 8.2+](https://www.php.net/downloads)
- [Composer](https://getcomposer.org/)
- [MySQL 8.0+](https://dev.mysql.com/downloads/)
- [Symfony CLI](https://symfony.com/download) (optionnel)

### 🛠️ Étapes d'installation

1. **Cloner le projet**
   ```bash
   git clone https://github.com/votre-pseudo/soutenancepro.git
   cd soutenancepro
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Configurer l'environnement**
   Copiez le fichier `.env` pour créer votre configuration locale :
   ```bash
   cp .env .env.dev
   ```
   Éditez ensuite le fichier `.env.dev` pour configurer votre base de données :
   ```text
   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/soutenancepro?serverVersion=8.0"
   ```

4. **Initialiser la base de données**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate --no-interaction
   php bin/console doctrine:fixtures:load --no-interaction
   ```

5. **Lancer le serveur**
   ```bash
   symfony server:start
   ```
   *Ou avec PHP directement :*
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

---

## 🔐 Accès par Défaut

| Rôle | Email | Mot de passe |
| :--- | :--- | :--- |
| **Administrateur** | `admin@soutenancepro.com` | `admin123` |
| **Enseignant** | `jean.dupont@email.com` | `password123` |

---

## 📁 Structure du Projet

```text
soutenancepro/
├── src/
│   ├── Controller/      # Logique de navigation (Admin & Enseignant)
│   ├── Entity/          # Modèles de données (Soutenance, Salle, etc.)
│   ├── Repository/      # Requêtes personnalisées
│   └── DataFixtures/    # Jeu de données de test
├── templates/           # Vues Twig (Thème AblePro)
├── migrations/          # Historique de la base de données
└── assets/              # Fichiers CSS et JavaScript
```

---

## ⚖️ Règles de Gestion

### 🚫 Conflits de planning
- Une **salle** ne peut pas être occupée par deux soutenances simultanément.
- Un **enseignant** ne peut pas siéger dans deux jurys différents sur le même créneau.
- La vérification se fait sur un intervalle de **60 minutes**.

### 🛡️ Protections
- On ne peut pas supprimer un **étudiant** s'il a une soutenance programmée.
- On ne peut pas supprimer un **enseignant** s'il est membre d'un jury actif.
- Les **salles** sont verrouillées tant qu'elles ont des réservations.

---

© 2026 SoutenancePro - Tous droits réservés.
