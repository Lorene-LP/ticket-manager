# Configuration de l'environnement de développement

## ⚠️ Mise en place des variables d'environnement

Après avoir cloné ce projet, vous devez créer vos propres fichiers de configuration d'environnement :

1. Copiez le fichier d'exemple :
   ```bash
   cp .env.example .env
   ```

2. Modifiez le fichier `.env` avec vos propres valeurs :
   - `APP_SECRET` : Générez une nouvelle clé secrète de 32 caractères
   - `DATABASE_URL` : Configurez votre base de données locale

3. Pour générer une nouvelle clé secrète APP_SECRET :
   ```bash
   php -r "echo bin2hex(random_bytes(16)) . PHP_EOL;"
   ```

## 🔒 Sécurité

Les fichiers `.env*` contiennent des informations sensibles et ne doivent JAMAIS être commitées dans Git.
Ils sont automatiquement ignorés par le fichier `.gitignore`.

## 📊 Base de données

N'oubliez pas de créer votre base de données locale et d'exécuter les migrations :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```