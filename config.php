<?php
declare(strict_types=1);

// Configuration de l'environnement
define('APP_ENV', 'dev'); // 'dev' ou 'prod'

// Configuration de l'authentification HTTP Basic
define('BASIC_USER', 'massi');
define('BASIC_PASS', 'randomizerpassword');

// Configuration de la base de données MySQL
// Note: À configurer selon votre environnement (localhost, IP serveur, etc.)
define('DB_HOST', 'localhost');    // Host de votre base de données
define('DB_NAME', 'mydatabase');   // Nom de la base
define('DB_USER', 'user');         // Utilisateur
define('DB_PASS', 'userpassword'); // Mot de passe

// Options PDO
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
]);
