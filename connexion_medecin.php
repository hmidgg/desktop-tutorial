<?php
session_start();
require_once 'connexion_bd.php';

if (isset($_SESSION['role']) && $_SESSION['role'] === 'medecin') {
    header('Location: rendezvous_medecin.php');
    exit;
}

$erreur = '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($email);
    $password = trim($password);

    if ($email === '' || $password === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse email invalide.';
    } else {
        $stmt = $pdo->prepare('SELECT ID_Med, password FROM Medecin WHERE Email = ?');
        $stmt->execute([$email]);
        $medecin = $stmt->fetch();

        if ($medecin && password_verify($password, $medecin['password'])) {
            $_SESSION['role'] = 'medecin';
            $_SESSION['id_medecin'] = $medecin['ID_Med'];
            $_SESSION['email'] = $email;

            header('Location: rendezvous_medecin.php');
            exit;
        } else {
            $erreur = 'Email ou mot de passe incorrect.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Connexion Médecin</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <style>
    body {
      background-color: #ffffff;
      font-family: Arial, sans-serif;
      color: #333;
    }
    .navbar {
      background-color: #003366;
    }
    .navbar-brand {
      color: white !important;
      transition: 0.3s ease;
    }
    .navbar-brand:hover {
      color: #28a745 !important;
      text-decoration: underline;
    }
    .login-container {
      background-color: #f8f9fa;
      padding: 40px;
      border-radius: 10px;
      margin-top: 100px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      max-width: 450px;
      margin-left: auto;
      margin-right: auto;
    }
    .form-label {
      color: #003366;
    }
    .btn-blue-royal {
      background-color: #003366;
      color: white;
      border: none;
      transition: 0.3s;
    }
    .btn-blue-royal:hover {
      background-color: #28a745;
      color: white;
    }
    a.link-dark:hover {
      color: #28a745 !important;
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.html">Centre Médical</a>
    </div>
  </nav>

  <div class="login-container text-center">
    <h2 style="color: #003366;">Connexion Médecin</h2>

    <?php if ($erreur !== ''): ?>
      <div class="alert alert-danger text-start">
        <?php echo htmlspecialchars($erreur); ?>
      </div>
    <?php endif; ?>

    <form id="loginForm" action="connexion_medecin.php" method="POST">
      <div class="mb-3 text-start">
        <label for="email" class="form-label">Adresse Email</label>
        <input
          type="email"
          class="form-control"
          id="email"
          name="email"
          placeholder="exemple@medecin.com"
          value="<?php echo htmlspecialchars($email); ?>"
        />
      </div>
      <div class="mb-3 text-start">
        <label for="password" class="form-label">Mot de passe</label>
        <input
          type="password"
          class="form-control"
          id="password"
          name="password"
          placeholder="********"
        />
      </div>
      <button type="submit" class="btn btn-blue-royal w-100">Se connecter</button>
    </form>
    <p class="mt-3">
      Pas encore de compte ?
      <a href="creation_compte_medecin.php" class="link-dark">Créer un compte</a>
    </p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
