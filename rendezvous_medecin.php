<?php
session_start();
require_once 'connexion_bd.php';

// Vérifier si un médecin est connecté
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'medecin') {
    header('Location: connexion_medecin.php');
    exit;
}

$medecin_id = $_SESSION['id_medecin'] ?? '';
$filter_date = $_GET['date'] ?? '';

// Récupérer les rendez-vous du médecin
$sql = "
    SELECT r.ID_RDV, r.DateHeure,
           p.Nom as patient_nom, p.Prenom as patient_prenom,
           s.ID_Salle, s.Equipement
    FROM Rendez_vous r
    LEFT JOIN Patient pat ON r.Matricule = pat.Matricule
    LEFT JOIN Personne p ON pat.Email = p.Email
    LEFT JOIN Medecin_Salle ms ON r.ID_Med = ms.ID_Med
    LEFT JOIN Salle s ON ms.ID_Salle = s.ID_Salle
    WHERE r.ID_Med = ?
";

$params = [$medecin_id];

if ($filter_date) {
    $sql .= " AND DATE(r.DateHeure) = ?";
    $params[] = $filter_date;
}

$sql .= " ORDER BY r.DateHeure ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rendez_vous = $stmt->fetchAll();

// Récupérer l'info du médecin
$stmt = $pdo->prepare("
    SELECT m.*, p.Nom, p.Prenom
    FROM Medecin m
    LEFT JOIN Personne p ON m.Email = p.Email
    WHERE m.ID_Med = ?
");
$stmt->execute([$medecin_id]);
$medecin_info = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Rendez-vous Médecin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
      font-family: Arial, sans-serif;
      color: #003366;
      margin: 0;
      padding: 0;
    }

    .navbar {
      background-color: #003366;
      margin-bottom: 30px;
    }

    .navbar-brand {
      color: white !important;
    }

    h2 {
      text-align: center;
      margin-top: 40px;
      margin-bottom: 30px;
      color: #003366;
    }

    .table {
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    .table th {
      background-color: #003366;
      color: white;
    }

    .btn-custom {
      background-color: #003366;
      color: white;
      border: none;
    }

    .btn-custom:hover {
      background-color: #007b8a;
    }

    .date-section {
      background-color: #e6f0fa;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 30px;
    }

    @media (max-width: 768px) {
      h2 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="medecin.php">Centre Médical</a>
      <div class="navbar-nav ms-auto">
        <span class="navbar-text text-white me-3">
          Dr. <?php echo htmlspecialchars($medecin_info['Prenom'] . ' ' . $medecin_info['Nom']); ?>
        </span>
        <a class="nav-link text-white" href="logout.php">Déconnexion</a>
      </div>
    </div>
  </nav>

  <h2>Espace Médecin – Gestion des Rendez-vous</h2>

  <div class="container">
    <!-- Section de sélection de date -->
    <div class="row justify-content-center date-section">
      <div class="col-md-6">
        <form method="get" class="d-flex gap-2">
          <div class="flex-grow-1">
            <label for="dateConsultation" class="form-label">Sélectionner une date :</label>
            <input type="date" class="form-control" id="dateConsultation" name="date" value="<?php echo htmlspecialchars($filter_date); ?>" />
          </div>
          <div class="d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-custom">Filtrer</button>
            <?php if ($filter_date): ?>
              <a href="rendezvous_medecin.php" class="btn btn-secondary">Réinitialiser</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <!-- Tableau des rendez-vous -->
    <div class="table-responsive">
      <table class="table table-bordered text-center">
        <thead>
          <tr>
            <th>Nom du Patient</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Salle</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rendez_vous)): ?>
            <tr>
              <td colspan="4" class="text-center text-muted">Aucun rendez-vous <?php echo $filter_date ? 'pour cette date' : 'prévu'; ?></td>
            </tr>
          <?php else: ?>
            <?php foreach ($rendez_vous as $rdv): ?>
              <tr>
                <td><?php echo htmlspecialchars(($rdv['patient_prenom'] ?? 'N/A') . ' ' . ($rdv['patient_nom'] ?? 'N/A')); ?></td>
                <td><?php echo date('d/m/Y', strtotime($rdv['DateHeure'])); ?></td>
                <td><?php echo date('H:i', strtotime($rdv['DateHeure'])); ?></td>
                <td><?php echo htmlspecialchars($rdv['Equipement'] ?? 'Non assignée'); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
