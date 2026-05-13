<?php
session_start();
require_once 'connexion_bd.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header('Location: connexion_patient.php');
    exit;
}

// Les tables Salle et Rendez_vous_suivi sont supposées déjà exister (via backup.sql)


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
                        // Génération d'ID robuste (format RDVxxxxxx)
                        $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(ID_RDV, 4) AS UNSIGNED)) AS max_num FROM Rendez_vous WHERE ID_RDV LIKE 'RDV%' FOR UPDATE");
                        $maxNum = (int)($stmt->fetch()['max_num'] ?? 0);
                        $nextId = 'RDV' . str_pad($maxNum + 1, 6, '0', STR_PAD_LEFT);

                        $stmt = $pdo->prepare('INSERT INTO Rendez_vous (ID_RDV, Matricule, ID_Med, DateHeure) VALUES (?, ?, ?, ?)');
                        $stmt->execute([$nextId, $matricule, $medecinSelectionne, $dateSql]);

                        $pdo->commit();
                        $succes = 'Rendez-vous enregistré avec succès.';
                        $medecinSelectionne = '';
                        $dateHeureSelectionnee = '';
                    } catch (PDOException $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        error_log("Erreur insertion RDV: " . $e->getMessage());
                        $erreur = 'Impossible d\'enregistrer le rendez-vous pour le moment.';
                    }
                }
            }
        }
    }
}

$stmt = $pdo->query(
    "SELECT m.ID_Med, p.Nom, p.Prenom,
            COALESCE(NULLIF(TRIM(m.Specialite), ''), sp.Nom) AS Specialite
     FROM Medecin m
     LEFT JOIN Personne p ON m.Email = p.Email
     LEFT JOIN Specialite sp ON m.ID_Spe = sp.ID_Spe
     ORDER BY p.Nom ASC, p.Prenom ASC"
);
$medecins = $stmt->fetchAll();

$stmt = $pdo->query("SELECT ID_Salle, Equipement FROM Salle WHERE Est_Disponible = 1 ORDER BY ID_Salle");
$sallesDisponibles = $stmt->fetchAll();

$stmt = $pdo->prepare(
    "SELECT r.ID_RDV, r.DateHeure,
            COALESCE(NULLIF(TRIM(m.Specialite), ''), sp.Nom) AS Specialite,
            p.Nom AS medecin_nom, p.Prenom AS medecin_prenom,
            COALESCE(rs.statut, 'attente') AS statut_rdv,
            s.ID_Salle, s.Equipement AS salle_equipement
     FROM Rendez_vous r
     LEFT JOIN Rendez_vous_suivi rs ON r.ID_RDV = rs.ID_RDV
     LEFT JOIN Salle s ON rs.ID_Salle = s.ID_Salle
     LEFT JOIN Medecin m ON r.ID_Med = m.ID_Med
     LEFT JOIN Specialite sp ON m.ID_Spe = sp.ID_Spe
     LEFT JOIN Personne p ON m.Email = p.Email
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
    #specialite_live {
      min-height: 1.5rem;
      color: #005f73;
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
              <p class="card-text mb-2">
                <span class="badge <?php echo ($rdv['statut_rdv'] ?? '') === 'valide' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                  <?php echo ($rdv['statut_rdv'] ?? '') === 'valide' ? 'Confirmé' : 'En attente de validation'; ?>
                </span>
              </p>
              <p class="card-text">
                Spécialité: <?php echo htmlspecialchars($rdv['Specialite'] ?? 'Non définie'); ?><br />
                Date: <?php echo date('d/m/Y', strtotime($rdv['DateHeure'])); ?><br />
                Heure: <?php echo date('H:i', strtotime($rdv['DateHeure'])); ?><br />
                <?php
                $salleId = $rdv['ID_Salle'] ?? null;
                $salleEq = $rdv['salle_equipement'] ?? '';
                if ($salleId !== null && $salleId !== '') {
                    $salleTxt = htmlspecialchars((string) $salleId);
                    if ($salleEq !== null && $salleEq !== '') {
                        $salleTxt .= ' — ' . htmlspecialchars((string) $salleEq);
                    }
                    echo 'Salle: <strong>' . $salleTxt . '</strong>';
                } elseif (($rdv['statut_rdv'] ?? '') === 'valide') {
                    echo 'Salle: <span class="text-muted">Aucune salle indiquée pour ce rendez-vous.</span>';
                } else {
                    echo 'Salle: <span class="text-muted">Affichée ici après validation et affectation par l\'accueil.</span>';
                }
                ?>
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
            <?php
            $spec = (string) ($medecin['Specialite'] ?? 'Non définie');
            $specData = htmlspecialchars($spec, ENT_QUOTES, 'UTF-8');
            ?>
            <option
              value="<?php echo htmlspecialchars((string) $medecin['ID_Med'], ENT_QUOTES, 'UTF-8'); ?>"
              data-specialite="<?php echo $specData; ?>"
              <?php echo (string) $medecinSelectionne === (string) $medecin['ID_Med'] ? 'selected' : ''; ?>
            >
              <?php echo htmlspecialchars('Dr. ' . trim(($medecin['Prenom'] ?? '') . ' ' . ($medecin['Nom'] ?? ''))); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="form-text mb-0 mt-2">Spécialité : <span id="specialite_live" aria-live="polite">—</span></p>
      </div>

      <div class="mb-3">
        <label for="specialite_medecin" class="form-label">Spécialité (récapitulatif)</label>
        <input type="text" id="specialite_medecin" class="form-control bg-light" readonly value="" autocomplete="off">
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

    <h2 class="mt-5">Salles disponibles</h2>
    <ul class="list-group">
      <?php foreach ($sallesDisponibles as $salle): ?>
        <li class="list-group-item"><?php echo htmlspecialchars($salle['ID_Salle'] . ' - ' . $salle['Equipement']); ?></li>
      <?php endforeach; ?>
      <?php if (empty($sallesDisponibles)): ?>
        <li class="list-group-item">Aucune salle disponible.</li>
      <?php endif; ?>
    </ul>
  </div>

  <script>
    (function () {
      const doctorSelect = document.getElementById('doctor');
      const specialiteInput = document.getElementById('specialite_medecin');
      const specialiteLive = document.getElementById('specialite_live');

      function updateSpecialite() {
        const selectedOption = doctorSelect.options[doctorSelect.selectedIndex];
        const raw = selectedOption && selectedOption.value !== ''
          ? (selectedOption.getAttribute('data-specialite') || selectedOption.dataset.specialite || '')
          : '';
        const text = raw.trim() === '' ? '—' : raw;
        specialiteInput.value = raw;
        specialiteLive.textContent = text;
      }

      doctorSelect.addEventListener('change', updateSpecialite);
      doctorSelect.addEventListener('input', updateSpecialite);
      updateSpecialite();
    })();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
