# Déploiement production

EasyApply requiert PHP 8.2+, MySQL, un worker de queue persistant et un scheduler.

1. Configurez les secrets et `APP_ENV=production`, `APP_DEBUG=false`, HTTPS et `SESSION_SECURE_COOKIE=true`.
2. Installez les dépendances et les assets : `composer install --no-dev --optimize-autoloader` puis `npm ci && npm run build`.
3. Exécutez `php artisan migrate --force` et `php artisan optimize`.
4. Lancez sous Supervisor : `php artisan queue:work --tries=3 --timeout=120`.
5. Configurez une tâche cron toutes les minutes : `php artisan schedule:run`.

Ne déployez jamais le fichier `.env`, les fichiers privés ou le répertoire `storage` dans le dépôt Git.
