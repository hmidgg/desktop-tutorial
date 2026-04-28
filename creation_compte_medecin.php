<?php
require_once 'connexion_bd.php';

$errors = [];
$success = false;

// Récupérer les spécialités
$stmt = $pdo->prepare('SELECT ID_Spe, Nom FROM Specialite');
$stmt->execute();
$specialites = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = trim($_POST['matricule'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $id_spe = trim($_POST['id_spe'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if ($matricule === '') {
        $errors[] = 'Veuillez saisir votre matricule.';
    }
    if ($nom === '') {
        $errors[] = 'Veuillez saisir votre nom.';
    }
    if ($prenom === '') {
        $errors[] = 'Veuillez saisir votre prénom.';
    }
    if ($email === '') {
        $errors[] = 'Veuillez saisir votre adresse email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adresse email invalide.';
    }
    if ($id_spe === '') {
        $errors[] = 'Veuillez sélectionner une spécialité.';
    }
    if ($password === '') {
        $errors[] = 'Veuillez saisir votre mot de passe.';
    }
    if ($confirmPassword === '') {
        $errors[] = 'Veuillez confirmer votre mot de passe.';
    }
    if ($password !== '' && $confirmPassword !== '' && $password !== $confirmPassword) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT ID_Med FROM Medecin WHERE ID_Med = ?');
        $stmt->execute([$matricule]);
        if ($stmt->fetch()) {
            $errors[] = 'Ce matricule est déjà enregistré.';
        }

        $stmt = $pdo->prepare('SELECT Email FROM Personne WHERE Email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Cet email est déjà utilisé.';
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO Personne (Nom, Prenom, Email, Telephone) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nom, $prenom, $email, null]);

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO Medecin (ID_Med, Addresse, Specialite, ID_Spe, Email, password) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$matricule, null, null, $id_spe, $email, $hash]);

            $pdo->commit();
            $success = true;
            header('Location: connexion_medecin.html');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Erreur lors de la création du compte. Veuillez réessayer.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Créer un compte - Médecin</title>
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
    .form-container {
      background-color: #f8f9fa;
      padding: 40px;
      border-radius: 10px;
      margin-top: 80px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      max-width: 600px;
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

  <div class="form-container">
    <h2 class="text-center" style="color:#003366;">Créer un compte médecin</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form id="createDoctorForm" action="" method="POST" onsubmit="return validateForm()">
      <div class="row">
        <div class="mb-3 col-md-6">
          <label for="matricule" class="form-label">Matricule</label>
          <input type="text" class="form-control" id="matricule" name="matricule" value="<?php echo htmlspecialchars($_POST['matricule'] ?? ''); ?>" />
        </div>
        <div class="mb-3 col-md-6">
          <label for="nom" class="form-label">Nom</label>
          <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" />
        </div>
      </div>

      <div class="row">
        <div class="mb-3 col-md-6">
          <label for="prenom" class="form-label">Prénom</label>
          <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" />
        </div>
        <div class="mb-3 col-md-6">
          <label for="email" class="form-label">Adresse Email</label>
          <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
        </div>
      </div>

      <div class="mb-3">
        <label for="id_spe" class="form-label">Spécialité</label>
        <select class="form-control" id="id_spe" name="id_spe">
          <option value="">-- Sélectionner une spécialité --</option>
          <?php foreach ($specialites as $spec): ?>
            <option value="<?php echo htmlspecialchars($spec['ID_Spe']); ?>" <?php echo ($_POST['id_spe'] ?? '') === $spec['ID_Spe'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($spec['Nom']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="row">
        <div class="mb-3 col-md-6">
          <label for="password" class="form-label">Mot de passe</label>
          <input type="password" class="form-control" id="password" name="password" />
        </div>
        <div class="mb-3 col-md-6">
          <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
          <input
            type="password"
            class="form-control"
            id="confirmPassword"
            name="confirmPassword"
          />
        </div>
      </div>

      <button type="submit" class="btn btn-blue-royal w-100">Créer mon compte</button>
    </form>

    <p class="mt-3 text-center">
      Vous avez déjà un compte ? 
      <a href="connexion_medecin.html" class="link-dark">Se connecter</a>
    </p>
  </div>

  <script>
    function validateForm() {
      const matricule = document.getElementById("matricule").value.trim();
      const nom = document.getElementById("nom").value.trim();
      const prenom = document.getElementById("prenom").value.trim();
      const email = document.getElementById("email").value.trim();
      const id_spe = document.getElementById("id_spe").value.trim();
      const password = document.getElementById("password").value;
      const confirmPassword = document.getElementById("confirmPassword").value;

      if (!matricule) {
        alert("Veuillez saisir votre matricule.");
        return false;
      }
      if (!nom) {
        alert("Veuillez saisir votre nom.");
        return false;
      }
      if (!prenom) {
        alert("Veuillez saisir votre prénom.");
        return false;
      }
      if (!email) {
        alert("Veuillez saisir votre adresse email.");
        return false;
      }
      if (!id_spe) {
        alert("Veuillez sélectionner une spécialité.");
        return false;
      }
      if (!password) {
        alert("Veuillez saisir votre mot de passe.");
        return false;
      }
      if (!confirmPassword) {
        alert("Veuillez confirmer votre mot de passe.");
        return false;
      }
      if (password !== confirmPassword) {
        alert("Les mots de passe ne correspondent pas.");
        return false;
      }
      return true;
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
