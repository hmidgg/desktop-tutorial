<?php
session_start();
require_once "class/Personne.php";
require_once "connexion_bd.php";
require_once "class/User.php";
$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login    = trim($_POST["login"]    ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($login) || empty($password)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        // Créer l'objet User avec les données saisies
        $user = new User($login, $password, '', '', '', '', $pdo);

        if ($user->Se_Connecter()) {
            // Récupérer les infos Personne
            $stmt = $pdo->prepare(
                "SELECT p.Nom, p.Prenom, p.Telephone
                 FROM User u
                 LEFT JOIN Personne p ON u.Email = p.Email
                 WHERE u.Email = ?"
            );
            $stmt->execute([$login]);
            $data = $stmt->fetch();

            // Stocker en session
            $_SESSION["role"]   = "user";
            $_SESSION["login"]  = $login;
            $_SESSION["nom"]    = $data["Nom"] ?? "";
            $_SESSION["prenom"] = $data["Prenom"] ?? "";
            $_SESSION["telephone"] = $data["Telephone"] ?? "";

            // Redirection vers dashboard
            header("Location: dashboard_user.php");
            exit;
        } else {
            $erreur = "Login ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Connexion Secrétaire</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      margin: 0; padding: 0;
      font-family: Arial, sans-serif;
      background-image: url('data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxASEBUSEBATEhUVEBAVFRUSFxIVEBUSFRUWFhUVFRYYHSggGBolGxUVITEhJSkrLi4uFx8zODMtNygtLisBCgoKDg0OGxAQGy0lICUtLS8vLS0tLS0tLS0tLy0tLS0tLS0tLS0tLS0uLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIALcBEwMBIgACEQEDEQH/xAAcAAAABwEBAAAAAAAAAAAAAAAAAQIDBAUGBwj');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }
    .overlay {
      background-color: rgba(255,255,255,0.85);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .login-container {
      max-width: 400px;
      width: 100%;
      padding: 30px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h2 { color: #003366; text-align: center; margin-bottom: 25px; }
    .btn-login { background-color: #003366; color: white; width: 100%; }
    .btn-login:hover { background-color: #005f73; }
  </style>
</head>
<body>
  <div class="overlay">
    <div class="login-container">
      <h2>Connexion Secrétaire</h2>

      <?php if (!empty($erreur)): ?>
        <div class="alert alert-danger text-center">
          <?= htmlspecialchars($erreur) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="connexion_admin.php">
        <div class="mb-3">
          <label for="login" class="form-label">Login</label>
          <input
            type="text"
            class="form-control"
            id="login"
            name="login"
            placeholder="Votre login"
            value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
          />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Mot de passe</label>
          <input
            type="password"
            class="form-control"
            id="password"
            name="password"
            placeholder="Mot de passe"
          />
        </div>
        <button type="submit" class="btn btn-login">Se connecter</button>
      </form>

    </div>
  </div>
</body>
</html>