# Système de Gestion de Tickets - Symfony

Application de gestion de tickets développée avec Symfony 6.4 pour une agence web.

## 🚀 Démarrage Rapide

```bash
# 1. Cloner le projet
git clone [URL_DU_REPO]
cd ticket-manager

# 2. Installer les dépendances
composer install

# 3. Configurer la base de données
cp .env.example .env.local
# Puis éditer .env.local avec vos paramètres de base de données

# 4. Créer la base de données
php bin/console doctrine:database:create

# 5. Exécuter les migrations
php bin/console doctrine:migrations:migrate

# 6. Charger les données de test
php bin/console doctrine:fixtures:load

# 7. Démarrer le serveur
php -S localhost:8000 -t public
```

## 🔐 Comptes de Test

- **Administrateur :** `admin@agence.com` / `admin123`
- **Personnel :** `marie.martin@agence.com` / `staff123`
- **Personnel :** `pierre.durand@agence.com` / `staff123`

## ✨ Fonctionnalités

- ✅ Création de tickets par les visiteurs
- ✅ Interface d'administration complète (CRUD)
- ✅ Interface personnel pour gestion des tickets
- ✅ Système de rôles et permissions
- ✅ Design responsive avec Bootstrap 5
- ✅ Validation backend rigoureuse

## 📋 Structure

```
src/
├── Controller/         # Contrôleurs (Admin, Staff, Home, Security)
├── Entity/            # Entités Doctrine (User, Ticket, Category, Status)
├── Form/              # Formulaires Symfony
└── Repository/        # Repositories personnalisés

templates/
├── admin/             # Templates d'administration
├── staff/             # Templates personnel
├── home/              # Page d'accueil publique
└── security/          # Authentification
```

## 🛠️ Technologies

- **Framework :** Symfony 6.4
- **Base de données :** MySQL avec Doctrine ORM
- **Frontend :** Bootstrap 5 + Twig
- **Sécurité :** Symfony Security Bundle
- **Fixtures :** Doctrine Fixtures

## 📤 Déploiement sur GitHub

```bash
# 1. Initialiser Git (si pas déjà fait)
git init

# 2. Ajouter les fichiers
git add .

# 3. Premier commit
git commit -m "Initial commit: Système de gestion de tickets Symfony"

# 4. Ajouter le remote GitHub
git remote add origin https://github.com/VOTRE_USERNAME/ticket-manager.git

# 5. Pousser sur GitHub
git push -u origin main
```

**⚠️ Important :** Créez un fichier `.env.local` avec vos vraies informations de base de données (ce fichier ne sera pas envoyé sur GitHub).