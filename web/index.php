<?php
declare(strict_types=1);

// Charge la configuration privée (hors /www)
require __DIR__ . '/../config.php';

// En-têtes de sécurité basiques
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ---- HTTP BASIC AUTH (en PHP, simple et portable) ----
$user = $_SERVER['PHP_AUTH_USER'] ?? null;
$pass = $_SERVER['PHP_AUTH_PW'] ?? null;

$authenticated = ($user === BASIC_USER) && hash_equals((string)BASIC_PASS, (string)$pass);
if (!$authenticated) {
    header('WWW-Authenticate: Basic realm="Accès restreint"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authentification requise.';
    exit;
}

// ---- Connexion PDO vers MySQL OVH ----
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
    // On s'assure de l'encodage
    $pdo->exec("SET NAMES utf8mb4");
} catch (Throwable $e) {
    http_response_code(500);
    echo (APP_ENV === 'dev')
        ? 'Erreur DB: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        : 'Erreur interne. Veuillez réessayer plus tard.';
    exit;
}

// ---- Schéma minimal (création si absent) ----
$pdo->exec("
    CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        email VARCHAR(190) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY idx_email_unique (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// ---- Traitement du formulaire ----
$feedback = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nom === '' || $email === '') {
        $feedback = ['type' => 'error', 'msg' => 'Tous les champs sont obligatoires.'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedback = ['type' => 'error', 'msg' => 'Adresse email invalide.'];
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, email) VALUES (:nom, :email)');
            $stmt->execute([':nom' => $nom, ':email' => $email]);
            $feedback = ['type' => 'success', 'msg' => 'Utilisateur enregistré avec succès.'];
        } catch (PDOException $e) {
            if ((int)$e->getCode() === 23000) { // contrainte d'unicité
                $feedback = ['type' => 'error', 'msg' => 'Cet email est déjà enregistré.'];
            } else {
                $feedback = ['type' => 'error', 'msg' =>
                    (APP_ENV === 'dev' ? 'Erreur SQL: ' . $e->getMessage() : 'Erreur interne. Réessayez plus tard.')
                ];
            }
        }
    }
}

?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Formulaire sécurisé</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/style.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Participer</h1>
      <p>Remplissez le formulaire ci-dessous pour nous rejoindre</p>
    </div>

    <?php if ($feedback): ?>
      <div class="message <?= $feedback['type'] === 'success' ? 'success' : 'error' ?>">
        <?= htmlspecialchars($feedback['msg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <div class="form-container">
      <form method="POST">
        <div class="form-group">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" maxlength="100" required placeholder="Votre nom">
        </div>
        
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" maxlength="190" required placeholder="votre.email@exemple.com">
        </div>
        
        <button type="submit" class="submit-btn">Soumettre</button>
      </form>
    </div>

    <section class="list">
      <h2>Utilisateurs récents</h2>
      <ul>
        <?php
          $rows = $pdo->query('SELECT nom, email, created_at FROM utilisateurs ORDER BY id DESC LIMIT 10')->fetchAll();
          if (!$rows) {
              echo '<li>Aucun enregistrement pour le moment.</li>';
          } else {
              foreach ($rows as $r) {
                  $nom = htmlspecialchars($r['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                  $email = htmlspecialchars($r['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                  $date = htmlspecialchars($r['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                  echo "<li><strong>{$nom}</strong> – {$email} <small>({$date})</small></li>";
              }
          }
        ?>
      </ul>
    </section>
  </div>
</body>
</html>
