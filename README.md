<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p> <p align="center"> <a href="https://github.com/tonpseudo/nom-du-projet/actions"><img src="https://github.com/tonpseudo/nom-du-projet/workflows/tests/badge.svg" alt="Build Status"></a> <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a> <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a> <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a> </p>
🛒 API de Gestion de Commandes
Une API construite avec Laravel et Laravel Sanctum pour gérer les commandes et les produits.

🚀 Fonctionnalités
✅ Gestion des Produits : CRUD complet sur les produits.
✅ Gestion des Commandes : Ajout, modification et suppression des commandes.
✅ Lignes de Commande : Permet d'associer plusieurs produits à une commande.
✅ Authentification Sécurisée : Utilisation de Laravel Sanctum.
✅ Documentation API : Générée avec L5 Swagger.

🛠 Technologies utilisées
Laravel (Framework PHP)

Laravel Sanctum (Authentification)

MySQL (Base de données)

Swagger (L5-Swagger) (Documentation API)

📌 Installation
1️⃣ Cloner le projet

bash
Copier
Modifier
git clone https://github.com/tonpseudo/nom-du-projet.git
cd nom-du-projet
2️⃣ Installer les dépendances

bash
Copier
Modifier
composer install
3️⃣ Configurer l'environnement

bash
Copier
Modifier
cp .env.example .env
php artisan key:generate
4️⃣ Configurer la base de données (Modifier .env)

5️⃣ Lancer les migrations et seeders

bash
Copier
Modifier
php artisan migrate --seed
6️⃣ Démarrer le serveur

bash
Copier
Modifier
php artisan serve
🔑 Authentification
L'authentification utilise Laravel Sanctum.
Chaque utilisateur doit s'enregistrer et obtenir un token pour accéder aux routes protégées.

📜 Documentation API
Swagger est utilisé pour documenter l'API.
Générez la documentation avec :

bash
Copier
Modifier
php artisan l5-swagger:generate
Ensuite, accédez à :
📌 http://127.0.0.1:8000/api/documentation
