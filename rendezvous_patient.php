<?php
session_start();
require_once 'connexion_bd.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header('Location: connexion_patient.php');
    exit;
}

$matricule = $_SESSION['matricule'] ?? '';
$patientNomComplet = trim(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? ''));

$erreur = '';
$succes = '';
$medecinSelectionne = '';
$dateHeureSelectionnee = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medecinSelectionne = trim($_POST['id_medecin'] ?? '');
    $dateHeureSelectionnee = trim($_POST['date_heure'] ?? '');

    if ($medecinSelectionne === '' || $dateHeureSelectionnee === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $dateHeure = DateTime::createFromFormat('Y-m-d\TH:i', $dateHeureSelectionnee);
        if (!$dateHeure) {
            $erreur = 'Format de date invalide.';
        } elseif ($dateHeure < new DateTime()) {
            $erreur = 'La date du rendez-vous doit être dans le futur.';
        } else {
            $stmt = $pdo->prepare('SELECT ID_Med FROM Medecin WHERE ID_Med = ?');
            $stmt->execute([$medecinSelectionne]);
            $medecinExiste = $stmt->fetch();

            if (!$medecinExiste) {
                $erreur = 'Médecin introuvable.';
            } else {
                $dateSql = $dateHeure->format('Y-m-d H:i:s');

                $stmt = $pdo->prepare('SELECT ID_RDV FROM Rendez_vous WHERE ID_Med = ? AND DateHeure = ?');
                $stmt->execute([$medecinSelectionne, $dateSql]);
                $dejaPris = $stmt->fetch();

                if ($dejaPris) {
                    $erreur = 'Ce créneau est déjà réservé.';
                } else {
                    $pdo->beginTransaction();
                    try {
                        $stmt = $pdo->query(
                            "SELECT COALESCE(MAX(CAST(SUBSTRING(ID_RDV, 4) AS UNSIGNED)), 0) + 1 AS next_num
                             FROM Rendez_vous
                             WHERE ID_RDV REGEXP '^RDV[0-9]+$'
                             FOR UPDATE"
                        );
                        $nextNum = (int) ($stmt->fetch()['next_num'] ?? 1);
                        $idRdv = 'RDV' . str_pad((string) $nextNum, 6, '0', STR_PAD_LEFT);

                        $stmt = $pdo->prepare(
                            'INSERT INTO Rendez_vous (ID_RDV, Matricule, ID_Med, DateHeure, login) VALUES (?, ?, ?, ?, NULL)'
                        );
                        $stmt->execute([$idRdv, $matricule, $medecinSelectionne, $dateSql]);

                        $pdo->commit();
                        $succes = 'Rendez-vous enregistré avec succès.';
                        $medecinSelectionne = '';
                        $dateHeureSelectionnee = '';
                    } catch (PDOException $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        $erreur = 'Erreur SQL: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

$stmt = $pdo->query(
    "SELECT m.ID_Med, p.Nom, p.Prenom, m.Specialite
     FROM Medecin m
     LEFT JOIN Personne p ON m.Email = p.Email
     ORDER BY p.Nom ASC, p.Prenom ASC"
);
$medecins = $stmt->fetchAll();

$stmt = $pdo->prepare(
    "SELECT r.ID_RDV, r.DateHeure, m.Specialite,
            p.Nom AS medecin_nom, p.Prenom AS medecin_prenom,
            s.ID_Salle, s.Equipement
     FROM Rendez_vous r
     LEFT JOIN Medecin m ON r.ID_Med = m.ID_Med
     LEFT JOIN Personne p ON m.Email = p.Email
     LEFT JOIN Medecin_Salle ms ON m.ID_Med = ms.ID_Med
     LEFT JOIN Salle s ON ms.ID_Salle = s.ID_Salle
     WHERE r.Matricule = ?
     ORDER BY r.DateHeure DESC"
);
$stmt->execute([$matricule]);
$rendezVous = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Rendez-vous Patient</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #fff;
      font-family: Arial, sans-serif;
      color: #333;
    }
    .navbar {
      background-color: #003366;
    }
    .navbar-brand,
    .nav-link,
    .navbar-text {
      color: white !important;
    }
    .container-main {
      margin-top: 80px;
      margin-bottom: 60px;
    }
    h2 {
      color: #003366;
      margin-bottom: 30px;
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
    .card {
      margin-bottom: 15px;
    }
    .form-label {
      color: #003366;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.html">Centre Médical</a>
      <div class="navbar-nav ms-auto">
        <span class="navbar-text me-3"><?php echo htmlspecialchars($patientNomComplet !== '' ? $patientNomComplet : 'Patient'); ?></span>
        <a class="nav-link" href="logout.php">Déconnexion</a>
      </div>
    </div>
  </nav>

  <div class="container container-main">
    <h2>Mes Rendez-vous</h2>

    <?php if ($succes !== ''): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($succes); ?></div>
    <?php endif; ?>

    <?php if ($erreur !== ''): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>

    <div id="appointmentsList" class="mb-5">
      <?php if (empty($rendezVous)): ?>
        <div class="alert alert-secondary">Aucun rendez-vous enregistré pour le moment.</div>
      <?php else: ?>
        <?php foreach ($rendezVous as $rdv): ?>
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">
                Dr. <?php echo htmlspecialchars(trim(($rdv['medecin_prenom'] ?? '') . ' ' . ($rdv['medecin_nom'] ?? ''))); ?>
              </h5>
              <p class="card-text">
                Spécialité: <?php echo htmlspecialchars($rdv['Specialite'] ?? 'Non définie'); ?><br />
                Date: <?php echo date('d/m/Y', strtotime($rdv['DateHeure'])); ?><br />
                Heure: <?php echo date('H:i', strtotime($rdv['DateHeure'])); ?><br />
                Salle: <?php echo htmlspecialchars($rdv['ID_Salle'] ?? 'Non assignée'); ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <h2>Prendre un nouveau rendez-vous</h2>
    <form id="appointmentForm" method="post" action="rendezvous_patient.php">
      <div class="mb-3">
        <label for="doctor" class="form-label">Médecin</label>
        <select id="doctor" name="id_medecin" class="form-select" required>
          <option value="" disabled <?php echo $medecinSelectionne === '' ? 'selected' : ''; ?>>-- Choisir un médecin --</option>
          <?php foreach ($medecins as $medecin): ?>
            <option value="<?php echo htmlspecialchars((string)$medecin['ID_Med']); ?>" <?php echo (string)$medecinSelectionne === (string)$medecin['ID_Med'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars('Dr. ' . trim(($medecin['Prenom'] ?? '') . ' ' . ($medecin['Nom'] ?? '')) . ' - ' . ($medecin['Specialite'] ?? '')); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="date_heure" class="form-label">Date et heure du rendez-vous</label>
        <input
          type="datetime-local"
          id="date_heure"
          name="date_heure"
          class="form-control"
          value="<?php echo htmlspecialchars($dateHeureSelectionnee); ?>"
          min="<?php echo date('Y-m-d\TH:i'); ?>"
          required
        />
      </div>

      <button type="submit" class="btn btn-blue-royal">Valider le rendez-vous</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
