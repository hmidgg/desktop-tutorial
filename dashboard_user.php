<?php
session_start();
require_once "connexion_bd.php";
require_once "class/User.php";
require_once "class/Rendev_Vous.php";
require_once "class/Medecin.php";
require_once "class/Patient.php";

// Vérifier si l'admin est connecté
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "user") {
    header("Location: connexion_admin.php");
    exit;
}

// Gestion des actions sur les rendez-vous
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $rdv_id = $_POST["rdv_id"] ?? 0;

    if ($action === "annuler" && $rdv_id > 0) {
        $rdv = new Rendez_vous($rdv_id, "", "", "", "", $pdo);
        $rdv->Annuler();
    }
    // Redirection pour éviter la resoumission du formulaire
    header("Location: dashboard_user.php");
    exit;
}

// Récupérer les rendez-vous du jour
$stmt = $pdo->prepare("
    SELECT r.ID_RDV, r.DateHeure,
           p.Nom as patient_nom, p.Prenom as patient_prenom,
           m.Nom as medecin_nom, m.Prenom as medecin_prenom, med.Specialite
    FROM Rendez_vous r
    LEFT JOIN Patient pat ON r.Matricule = pat.Matricule
    LEFT JOIN Personne p ON pat.Email = p.Email
    LEFT JOIN Medecin med ON r.ID_Med = med.ID_Med
    LEFT JOIN Personne m ON med.Email = m.Email
    WHERE DATE(r.DateHeure) = CURDATE()
    ORDER BY r.DateHeure ASC
");
$stmt->execute();
$rendez_vous = $stmt->fetchAll();

// Récupérer les médecins disponibles
$stmt = $pdo->prepare("
    SELECT m.ID_Med, p.Nom, p.Prenom, m.Specialite
    FROM Medecin m
    LEFT JOIN Personne p ON m.Email = p.Email
");
$stmt->execute();
$medecins = $stmt->fetchAll();

// Récupérer les patients enregistrés
$stmt = $pdo->prepare("
    SELECT pat.Matricule, p.Nom, p.Prenom
    FROM Patient pat
    LEFT JOIN Personne p ON pat.Email = p.Email
");
$stmt->execute();
$patients = $stmt->fetchAll();

// Récupérer les salles disponibles
$stmt = $pdo->prepare("SELECT ID_Salle, Equipement FROM Salle WHERE Est_Disponible = 1");
$stmt->execute();
$salles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: url('https://images.unsplash.com/photo-1588776814546-ec6a973fb45f') no-repeat center center fixed;
      background-size: cover;
      backdrop-filter: brightness(95%);
    }
    .navbar {
      background-color: rgba(0, 51, 102, 0.8);
    }
    .navbar-brand, .nav-link {
      color: white !important;
    }
    .content-container {
      background-color: rgba(255, 255, 255, 0.95);
      border-radius: 10px;
      padding: 30px;
      margin-top: 30px;
    }
    h2 {
      color: #003366;
      margin-bottom: 25px;
    }
    .section-title {
      margin-top: 40px;
      color: #005f73;
      border-bottom: 2px solid #005f73;
      padding-bottom: 5px;
    }
    table {
      background-color: white;
    }
    .btn-action {
      margin: 2px;
    }
  </style>
</head>
<body>

<!-- Barre de navigation -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="#">Centre Médical - Admin</a>
    <div class="navbar-nav ms-auto">
      <span class="navbar-text text-white me-3">
        Bienvenue, <?php echo htmlspecialchars($_SESSION["prenom"] . " " . $_SESSION["nom"]); ?>
      </span>
      <a class="nav-link" href="logout.php">Déconnexion</a>
    </div>
  </div>
</nav>

<!-- Contenu principal -->
<div class="container content-container">
  <h2>Tableau de bord - Secrétaire</h2>

  <!-- Section Rendez-vous -->
  <div>
    <h4 class="section-title">📅 Rendez-vous du jour</h4>
    <table class="table table-bordered">
      <thead class="table-dark">
        <tr>
          <th>Patient</th>
          <th>Médecin</th>
          <th>Spécialité</th>
          <th>Date</th>
          <th>Heure</th>
          <th>Actions</th>
        </tr>
      </thead>
      </thead>
      <tbody>
        <?php foreach ($rendez_vous as $rdv): ?>
        <tr>
          <td><?php echo htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']); ?></td>
          <td><?php echo htmlspecialchars('Dr. ' . $rdv['medecin_prenom'] . ' ' . $rdv['medecin_nom']); ?></td>
          <td><?php echo htmlspecialchars($rdv['Specialite']); ?></td>
          <td><?php echo date('Y-m-d', strtotime($rdv['DateHeure'])); ?></td>
          <td><?php echo date('H:i', strtotime($rdv['DateHeure'])); ?></td>
          <td>
            <form method="post" style="display: inline;">
              <input type="hidden" name="rdv_id" value="<?php echo $rdv['ID_RDV']; ?>">
              <button type="submit" name="action" value="annuler" class="btn btn-danger btn-sm btn-action" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">Annuler</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($rendez_vous)): ?>
        <tr>
          <td colspan="6" class="text-center">Aucun rendez-vous prévu pour aujourd'hui.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Section Médecins -->
  <div>
    <h4 class="section-title">👨‍⚕️ Médecins disponibles</h4>
    <ul class="list-group">
      <?php foreach ($medecins as $med): ?>
      <li class="list-group-item"><?php echo htmlspecialchars('Dr. ' . $med['Prenom'] . ' ' . $med['Nom'] . ' - ' . $med['Specialite']); ?></li>
      <?php endforeach; ?>
      <?php if (empty($medecins)): ?>
      <li class="list-group-item">Aucun médecin enregistré.</li>
      <?php endif; ?>
    </ul>
  </div>

  <!-- Section Patients -->
  <div>
    <h4 class="section-title">🧑‍🦱 Patients enregistrés</h4>
    <ul class="list-group">
      <?php foreach ($patients as $pat): ?>
      <li class="list-group-item"><?php echo htmlspecialchars($pat['Prenom'] . ' ' . $pat['Nom']); ?></li>
      <?php endforeach; ?>
      <?php if (empty($patients)): ?>
      <li class="list-group-item">Aucun patient enregistré.</li>
      <?php endif; ?>
    </ul>
  </div>

  <!-- Section Salles disponibles -->
  <div>
    <h4 class="section-title">🏥 Salles disponibles</h4>
    <ul class="list-group">
      <?php foreach ($salles as $salle): ?>
      <li class="list-group-item"><?php echo htmlspecialchars($salle['Equipement']); ?></li>
      <?php endforeach; ?>
      <?php if (empty($salles)): ?>
      <li class="list-group-item">Aucune salle disponible.</li>
      <?php endif; ?>
    </ul>
  </div>

</div>

<!-- Scripts Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
    window.location.href = "connexion_admin.html"; // page rendez-vous médecin à créer
      return false;
</script>

</body>
</html>
