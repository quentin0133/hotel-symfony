# Projet d'expertise évalué : « Projet PHP »

## Système de réservation hôtelière (CRUD MVC)

**Description :** Application web de type CRUD développée avec le framework Symfony. Il s'agit du système de réservation d'un groupe hôtelier disposant d'une centrale de réservation nationale.

**Équipe :**
* Quentin YAHIA - quentin.yahia.pro@gmail.com
* Hippolyte GAUTHERON - hippolyte.gauth@gmail.com

---

## Technologies utilisées

* **Back-end :** PHP 8.x, Framework Symfony
* **Front-end :** Twig, HTML5, CSS3, Bootstrap (Responsive Design)
* **Base de données :** MariaDB (SQL)
* **ORM :** Doctrine

---

## Fonctionnalités de l'application

Le site est divisé en trois espaces distincts avec des niveaux d'accès spécifiques :

### Espace Public
* **Accueil :** Moteur de recherche de chambres disponibles selon une date de début et une date de fin.
* **Réservation :** Possibilité de réserver une ou plusieurs chambres (sans module de paiement).
* **Authentification :** * Inscription et connexion requises pour finaliser une réservation.
    * *Note : Collecte de l'email et du numéro de téléphone lors de l'inscription en plus des données de base du MCD.*
    * Gestion des mots de passe oubliés.

### Espace Client (Connecté)
* **Tableau de bord :** Visualisation de l'historique et des réservations en cours.
* **Gestion des réservations :** Ajout de commentaires à une réservation existante (ex : demandes spéciales, ajout d’un lit bébé...).

### Espace Administrateur (Gestionnaire)
* **Gestion des Chambres (CRUD) :** Création, lecture, modification et suppression avec système de pagination et barre de recherche.
* **Gestion des Réservations (CRUD) :** * Pagination et recherche via le `numReservation`.
    * Vue détaillée d'une réservation affichant l'ensemble des chambres associées.
* **Gestion des Clients (CRUD) :** Pagination et recherche via nom ou email.

---

## 🚀 Installation et déploiement (Local)

### Prérequis
* PHP >= 8.1
* Composer
* Symfony CLI
* MariaDB

### Étapes d'installation

1. **Cloner le dépôt :**
   ```bash
   git clone [URL_DU_DEPOT]
   cd [NOM_DU_DOSSIER]
   ```

2. **Installer les dépendances PHP :**
   ```bash
   composer install
   ```

3. **Configuration de la base de données :**

Dupliquez le fichier `.env` en `.env.local` et configurez votre chaîne de connexion MariaDB :
   ```env
   DATABASE_URL="mysql://utilisateur:mot_de_passe@127.0.0.1:3307/nom_de_la_base?serverVersion=mariadb-10.x.x"
   ```

Dupliquez aussi le fichier `.env.test` en `.env.test.local`, la démarche est la même (seulement si vous voulez exécuter les tests) :

4. **Créer la base de données et appliquer les migrations :**
   ```bash
   php bin/console doctrine:scheme:create
   php bin/console doctrine:migrations:migrate
   ```
   
Vous pouvez faire une commande équivalente pour les tests :
   ```bash
   php bin/console doctrine:scheme:create --env=test
   ```

5. **(Optionnel) Charger les données de test (Fixtures) :**
   ```bash
   php bin/console doctrine:fixtures:load
   ```

6. **Lancer le serveur de développement :**
   ```bash
   symfony serve -d
   ```
   *Accédez à l'application via `http://localhost:8000`.*

---

## Envoi d'emails en développement (Mailpit)

Les emails (ex : réinitialisation de mot de passe) sont interceptés localement par [Mailpit](https://github.com/axllent/mailpit) — aucun mail n'est envoyé pour de vrai.

**Lancer Mailpit avec Docker :**
```bash
docker run -d -p 1025:1025 -p 8025:8025 axllent/mailpit
```

**Configurer le mailer dans `.env.local` :**
```env
MAILER_DSN=smtp://localhost:1025
```

**Consulter les emails reçus :** `http://localhost:8025`

Pour Sonarquebe, ajouter le fichier sonar-project.properties et configurer
le à l'aide de cette configuration :
```env
sonar.host.url=http://host.docker.internal:9000
sonar.token=VOTRE_TOKEN

sonar.projectKey=Hotel-symfony
sonar.projectName=Hotel symfony
sonar.sources=src

sonar.exclusions=vendor/**, var/**, public/**, tests/**
```

**Lancer Sonarqube avec Docker :**
```env
docker run -d --name sonarqube -e SONAR_ES_BOOTSTRAP_CHECKS_DISABLE=true -p 9000:9000 sonarqube:community
```

**Lancer une analyse :**
```env
sonar-scanner \
    -Dsonar.projectKey=nom_projet \
    -Dsonar.sources=. \
    -Dsonar.host.url=http://localhost:9000 \
    -Dsonar.token=token_sonarqube
```

**Consulter Sonarqube et ses analyses :** `http://localhost:9000`
