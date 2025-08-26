<?php
declare(strict_types=1);

/**
 * Page formulaire protégée par HTTP Basic + enregistrement utilisateur en base.
 * Production-ready: variables lues via l'environnement (.env compose).
 */

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$APP_ENV = getenv('APP_ENV') ?: 'production';

$BASIC_USER = getenv('BASIC_USER') ?: 'admin';
$BASIC_PASS = getenv('BASIC_PASS') ?: 'change-me';

// ---- HTTP BASIC AUTH ----
$user = $_SERVER['PHP_AUTH_USER'] ?? null;
$pass = $_SERVER['PHP_AUTH_PW'] ?? null;

$authenticated = ($user === $BASIC_USER) && hash_equals((string)$BASIC_PASS, (string)$pass);

if (!$authenticated) {
    header('WWW-Authenticate: Basic realm="Accès restreint"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authentification requise.';
    exit;
}

// ---- Connexion PDO ----
$DB_HOST = getenv('DB_HOST') ?: 'db';
$DB_NAME = getenv('DB_NAME') ?: 'mydatabase';
$DB_USER = getenv('DB_USER') ?: 'user';
$DB_PASS = getenv('DB_PASS') ?: 'userpassword';

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $DB_HOST, $DB_NAME);

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    if ($APP_ENV !== 'production') {
        echo 'Erreur DB: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    } else {
        echo 'Erreur interne. Veuillez réessayer plus tard.';
    }
    exit;
}

// ---- Schéma minimal (idempotent) ----
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
            // Violation d’unicité ?
            if ((int)$e->getCode() === 23000) {
                $feedback = ['type' => 'error', 'msg' => 'Cet email est déjà enregistré.'];
            } else {
                $feedback = ['type' => 'error', 'msg' => ($APP_ENV !== 'production'
                    ? 'Erreur SQL: ' . $e->getMessage()
                    : 'Erreur interne. Réessayez plus tard.'
                )];
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Formulaire sécurisé</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/style.css">
</head>
<body>
  <main class="container">
    <h1>Formulaire sécurisé</h1>

    <?php if ($feedback): ?>
      <div class="alert <?= $feedback['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
        <?= htmlspecialchars($feedback['msg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <form method="post" class="card">
      <label for="nom">Nom</label>
      <input id="nom" name="nom" type="text" placeholder="Jean Dupont" required>

      <label for="email">Email</label>
      <input id="email" name="email" type="email" placeholder="jean.dupont@email.fr" required>

      <button type="submit">Envoyer</button>
    </form>

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
  </main>
</body>
</html>
