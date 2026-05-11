<?php
session_start();
require_once "connexion_bd.php";

// Verifier si la secretaire est connectee
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "user") {
    header("Location: connexion_admin.php");
    exit;
}

// Salles en base : les secretaires gerent l'inventaire et la disponibilite pour les affectations RDV
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS Salle (
        ID_Salle VARCHAR(50) NOT NULL,
        Equipement VARCHAR(255) DEFAULT NULL,
        Est_Disponible TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (ID_Salle)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci"
);

// Table de suivi secretaire pour ne pas dependre d'une colonne statut inexistante
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS Rendez_vous_suivi (
        ID_RDV VARCHAR(50) NOT NULL,
        statut VARCHAR(20) NOT NULL DEFAULT 'attente',
        ID_Salle VARCHAR(50) DEFAULT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (ID_RDV),
        CONSTRAINT fk_suivi_rdv FOREIGN KEY (ID_RDV) REFERENCES Rendez_vous (ID_RDV) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_suivi_salle FOREIGN KEY (ID_Salle) REFERENCES Salle (ID_Salle) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci"
);

$message_ok = "";
$message_erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $rdv_id = trim($_POST["rdv_id"] ?? "");

    try {
        if ($action === "annuler" && $rdv_id !== "") {
            $stmt = $pdo->prepare("DELETE FROM Rendez_vous WHERE ID_RDV = ?");
            $stmt->execute([$rdv_id]);
            $message_ok = "Rendez-vous annule avec succes.";
        } elseif ($action === "valider" && $rdv_id !== "") {
            $stmt = $pdo->prepare(
                "INSERT INTO Rendez_vous_suivi (ID_RDV, statut)
                 VALUES (?, 'valide')
                 ON DUPLICATE KEY UPDATE statut = 'valide'"
            );
            $stmt->execute([$rdv_id]);
            $message_ok = "Rendez-vous valide.";
        } elseif ($action === "modifier" && $rdv_id !== "") {
            $nouvelle_date = trim($_POST["date_heure"] ?? "");
            $id_med = trim($_POST["id_medecin"] ?? "");

            if ($nouvelle_date === "" || $id_med === "") {
                throw new RuntimeException("Date/heure ou medecin manquant.");
            }

            $stmt = $pdo->prepare("SELECT ID_RDV FROM Rendez_vous WHERE ID_Med = ? AND DateHeure = ? AND ID_RDV <> ?");
            $stmt->execute([$id_med, $nouvelle_date, $rdv_id]);
            if ($stmt->fetch()) {
                throw new RuntimeException("Ce medecin a deja un rendez-vous a ce creneau.");
            }

            $stmt = $pdo->prepare("UPDATE Rendez_vous SET DateHeure = ?, ID_Med = ? WHERE ID_RDV = ?");
            $stmt->execute([$nouvelle_date, $id_med, $rdv_id]);
            $message_ok = "Rendez-vous modifie avec succes.";
        } elseif ($action === "affecter_salle" && $rdv_id !== "") {
            $id_salle = trim($_POST["id_salle"] ?? "");
            if ($id_salle === "") {
                throw new RuntimeException("Selectionnez une salle.");
            }

            $stmt = $pdo->prepare("SELECT ID_Salle FROM Salle WHERE ID_Salle = ? AND Est_Disponible = 1");
            $stmt->execute([$id_salle]);
            if (!$stmt->fetch()) {
                throw new RuntimeException("Salle indisponible.");
            }

            $stmt = $pdo->prepare("SELECT DateHeure FROM Rendez_vous WHERE ID_RDV = ?");
            $stmt->execute([$rdv_id]);
            $rdv = $stmt->fetch();
            if (!$rdv) {
                throw new RuntimeException("Rendez-vous introuvable.");
            }

            $stmt = $pdo->prepare(
                "SELECT r.ID_RDV
                 FROM Rendez_vous r
                 INNER JOIN Rendez_vous_suivi rs ON rs.ID_RDV = r.ID_RDV
                 WHERE rs.ID_Salle = ? AND r.DateHeure = ? AND r.ID_RDV <> ?"
            );
            $stmt->execute([$id_salle, $rdv["DateHeure"], $rdv_id]);
            if ($stmt->fetch()) {
                throw new RuntimeException("Cette salle est deja occupee a cette heure.");
            }

            $stmt = $pdo->prepare(
                "INSERT INTO Rendez_vous_suivi (ID_RDV, ID_Salle)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE ID_Salle = VALUES(ID_Salle)"
            );
            $stmt->execute([$rdv_id, $id_salle]);
            $message_ok = "Salle affectee avec succes.";
        } elseif ($action === "salle_ajouter") {
            $id_salle = trim($_POST["new_id_salle"] ?? "");
            $equipement = trim($_POST["new_equipement"] ?? "");
            $est_disp = isset($_POST["new_est_disponible"]) ? 1 : 0;
            if ($id_salle === "") {
                throw new RuntimeException("Identifiant de salle requis.");
            }
            if (strlen($id_salle) > 50) {
                throw new RuntimeException("Identifiant trop long (50 caracteres max).");
            }
            $stmt = $pdo->prepare("SELECT 1 FROM Salle WHERE ID_Salle = ?");
            $stmt->execute([$id_salle]);
            if ($stmt->fetch()) {
                throw new RuntimeException("Cet identifiant de salle existe deja.");
            }
            $stmt = $pdo->prepare("INSERT INTO Salle (ID_Salle, Equipement, Est_Disponible) VALUES (?, ?, ?)");
            $stmt->execute([$id_salle, $equipement === "" ? null : $equipement, $est_disp]);
            $message_ok = "Salle ajoutee.";
        } elseif ($action === "salle_modifier") {
            $id_salle = trim($_POST["id_salle"] ?? "");
            $equipement = trim($_POST["equipement"] ?? "");
            $est_disp = isset($_POST["est_disponible"]) ? 1 : 0;
            if ($id_salle === "") {
                throw new RuntimeException("Salle introuvable.");
            }
            $stmt = $pdo->prepare("SELECT 1 FROM Salle WHERE ID_Salle = ?");
            $stmt->execute([$id_salle]);
            if (!$stmt->fetch()) {
                throw new RuntimeException("Salle introuvable.");
            }
            $stmt = $pdo->prepare("UPDATE Salle SET Equipement = ?, Est_Disponible = ? WHERE ID_Salle = ?");
            $stmt->execute([$equipement === "" ? null : $equipement, $est_disp, $id_salle]);
            $message_ok = "Salle mise a jour.";
        } elseif ($action === "salle_supprimer") {
            $id_salle = trim($_POST["id_salle"] ?? "");
            if ($id_salle === "") {
                throw new RuntimeException("Salle introuvable.");
            }
            $stmt = $pdo->prepare("DELETE FROM Salle WHERE ID_Salle = ?");
            $stmt->execute([$id_salle]);
            if ($stmt->rowCount() === 0) {
                throw new RuntimeException("Salle introuvable.");
            }
            $message_ok = "Salle supprimee.";
        }
    } catch (Throwable $e) {
        $message_erreur = $e->getMessage();
    }
    $_SESSION["dashboard_user_ok"] = $message_ok;
    $_SESSION["dashboard_user_err"] = $message_erreur;
    header("Location: dashboard_user.php");
    exit;
}

if (!empty($_SESSION["dashboard_user_ok"])) {
    $message_ok = $_SESSION["dashboard_user_ok"];
    unset($_SESSION["dashboard_user_ok"]);
}
if (!empty($_SESSION["dashboard_user_err"])) {
    $message_erreur = $_SESSION["dashboard_user_err"];
    unset($_SESSION["dashboard_user_err"]);
}

// Filtres
$filtre_date = trim($_GET["date"] ?? "");
$rdv_a_modifier = trim($_GET["edit"] ?? "");

// Recuperer les rendez-vous
$whereDate = "";
$params = [];
if ($filtre_date !== "") {
    $whereDate = "WHERE DATE(r.DateHeure) = ?";
    $params[] = $filtre_date;
}

$stmt = $pdo->prepare("
    SELECT r.ID_RDV, r.DateHeure, r.ID_Med, r.Matricule,
           COALESCE(rs.statut, 'attente') AS statut,
           rs.ID_Salle,
           p.Nom as patient_nom, p.Prenom as patient_prenom,
           m.Nom as medecin_nom, m.Prenom as medecin_prenom,
           COALESCE(NULLIF(TRIM(med.Specialite), ''), spe.Nom) AS Specialite,
           s.Equipement AS salle_equipement
    FROM Rendez_vous r
    LEFT JOIN Rendez_vous_suivi rs ON r.ID_RDV = rs.ID_RDV
    LEFT JOIN Patient pat ON r.Matricule = pat.Matricule
    LEFT JOIN Personne p ON pat.Email = p.Email
    LEFT JOIN Medecin med ON r.ID_Med = med.ID_Med
    LEFT JOIN Specialite spe ON med.ID_Spe = spe.ID_Spe
    LEFT JOIN Personne m ON med.Email = m.Email
    LEFT JOIN Salle s ON rs.ID_Salle = s.ID_Salle
    $whereDate
    ORDER BY r.DateHeure ASC
");
$stmt->execute($params);
$rendez_vous = $stmt->fetchAll();

// Recuperer les medecins disponibles
$stmt = $pdo->prepare("
    SELECT m.ID_Med, p.Nom, p.Prenom,
           COALESCE(NULLIF(TRIM(m.Specialite), ''), sp.Nom) AS Specialite
    FROM Medecin m
    LEFT JOIN Personne p ON m.Email = p.Email
    LEFT JOIN Specialite sp ON m.ID_Spe = sp.ID_Spe
    ORDER BY p.Nom, p.Prenom
");
$stmt->execute();
$medecins = $stmt->fetchAll();

// Recuperer les patients enregistres
$stmt = $pdo->prepare("
    SELECT pat.Matricule, p.Nom, p.Prenom
    FROM Patient pat
    LEFT JOIN Personne p ON pat.Email = p.Email
    ORDER BY p.Nom, p.Prenom
");
$stmt->execute();
$patients = $stmt->fetchAll();

// Recuperer les salles disponibles (affectation RDV)
$stmt = $pdo->prepare("SELECT ID_Salle, Equipement FROM Salle WHERE Est_Disponible = 1 ORDER BY ID_Salle");
$stmt->execute();
$salles = $stmt->fetchAll();

// Inventaire complet pour la gestion
$stmt = $pdo->query("SELECT ID_Salle, Equipement, Est_Disponible FROM Salle ORDER BY ID_Salle ASC");
$toutes_salles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Secretaire</title>
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
  <h2>Tableau de bord - Secretaire</h2>

  <?php if ($message_ok !== ""): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message_ok); ?></div>
  <?php endif; ?>
  <?php if ($message_erreur !== ""): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($message_erreur); ?></div>
  <?php endif; ?>

  <!-- Gestion des salles (base Salle) -->
  <div class="mb-5">
    <h4 class="section-title">Gestion des salles</h4>
    <p class="text-muted small">Les salles marquees indisponibles n'apparaissent pas dans les listes d'affectation aux rendez-vous.</p>
    <div class="row g-4">
      <div class="col-lg-5">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">Ajouter une salle</div>
          <div class="card-body">
            <form method="post" class="row g-2">
              <input type="hidden" name="action" value="salle_ajouter">
              <div class="col-12">
                <label class="form-label">Identifiant (ex. S101)</label>
                <input type="text" name="new_id_salle" class="form-control" maxlength="50" required placeholder="ID unique">
              </div>
              <div class="col-12">
                <label class="form-label">Equipement / description</label>
                <input type="text" name="new_equipement" class="form-control" maxlength="255" placeholder="Optionnel">
              </div>
              <div class="col-12 form-check ms-2">
                <input class="form-check-input" type="checkbox" name="new_est_disponible" id="new_est_disponible" value="1" checked>
                <label class="form-check-label" for="new_est_disponible">Disponible pour les rendez-vous</label>
              </div>
              <div class="col-12 mt-2">
                <button type="submit" class="btn btn-primary">Enregistrer la salle</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Equipement</th>
                <th>Statut</th>
                <th style="width: 38%;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($toutes_salles as $s): ?>
              <tr>
                <td><?php echo htmlspecialchars($s["ID_Salle"]); ?></td>
                <td>
                  <form method="post" class="d-flex flex-column gap-1">
                    <input type="hidden" name="action" value="salle_modifier">
                    <input type="hidden" name="id_salle" value="<?php echo htmlspecialchars($s["ID_Salle"]); ?>">
                    <input type="text" name="equipement" class="form-control form-control-sm" value="<?php echo htmlspecialchars($s["Equipement"] ?? ""); ?>" maxlength="255" placeholder="Equipement">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="est_disponible" id="disp_<?php echo htmlspecialchars(preg_replace("/[^a-zA-Z0-9_-]/", "_", $s["ID_Salle"])); ?>" value="1" <?php echo !empty($s["Est_Disponible"]) ? "checked" : ""; ?>>
                      <label class="form-check-label" for="disp_<?php echo htmlspecialchars(preg_replace("/[^a-zA-Z0-9_-]/", "_", $s["ID_Salle"])); ?>">Disponible</label>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm align-self-start">Mettre a jour</button>
                  </form>
                </td>
                <td>
                  <?php if (!empty($s["Est_Disponible"])): ?>
                    <span class="badge bg-success">Disponible</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Indisponible</span>
                  <?php endif; ?>
                </td>
                <td>
                  <form method="post" class="d-inline" onsubmit="return confirm('Supprimer cette salle ? Les liaisons medecin-salle seront supprimees.');">
                    <input type="hidden" name="action" value="salle_supprimer">
                    <input type="hidden" name="id_salle" value="<?php echo htmlspecialchars($s["ID_Salle"]); ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($toutes_salles)): ?>
              <tr><td colspan="4" class="text-center text-muted">Aucune salle en base. Ajoutez-en une a gauche.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Section Rendez-vous -->
  <div>
    <div class="d-flex justify-content-between align-items-center">
      <h4 class="section-title mb-0">Gestion des rendez-vous</h4>
      <form method="get" class="d-flex gap-2">
        <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($filtre_date); ?>">
        <button class="btn btn-primary btn-sm" type="submit">Filtrer</button>
        <?php if ($filtre_date !== ""): ?>
          <a class="btn btn-secondary btn-sm" href="dashboard_user.php">Reset</a>
        <?php endif; ?>
      </form>
    </div>
    <table class="table table-bordered">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Patient</th>
          <th>Médecin</th>
          <th>Spécialité</th>
          <th>Date</th>
          <th>Heure</th>
          <th>Statut</th>
          <th>Salle</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rendez_vous as $rdv): ?>
        <tr>
          <td><?php echo htmlspecialchars($rdv['ID_RDV']); ?></td>
          <td><?php echo htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']); ?></td>
          <td><?php echo htmlspecialchars('Dr. ' . $rdv['medecin_prenom'] . ' ' . $rdv['medecin_nom']); ?></td>
          <td><?php echo htmlspecialchars($rdv['Specialite']); ?></td>
          <td><?php echo date('Y-m-d', strtotime($rdv['DateHeure'])); ?></td>
          <td><?php echo date('H:i', strtotime($rdv['DateHeure'])); ?></td>
          <td>
            <span class="badge <?php echo $rdv['statut'] === 'valide' ? 'bg-success' : 'bg-warning text-dark'; ?>">
              <?php echo htmlspecialchars($rdv['statut']); ?>
            </span>
          </td>
          <td>
            <?php if (!empty($rdv['ID_Salle'])): ?>
              <?php echo htmlspecialchars($rdv['ID_Salle'] . ' - ' . ($rdv['salle_equipement'] ?? '')); ?>
            <?php else: ?>
              <span class="text-muted">Non affectee</span>
            <?php endif; ?>
          </td>
          <td>
            <form method="post" style="display: inline;">
              <input type="hidden" name="rdv_id" value="<?php echo $rdv['ID_RDV']; ?>">
              <button type="submit" name="action" value="valider" class="btn btn-success btn-sm btn-action">Valider</button>
            </form>
            <form method="post" style="display: inline;">
              <input type="hidden" name="action" value="affecter_salle">
              <input type="hidden" name="rdv_id" value="<?php echo htmlspecialchars($rdv['ID_RDV']); ?>">
              <select name="id_salle" class="form-select form-select-sm d-inline-block" style="width: 170px;" required>
                <option value="" disabled selected>Salle...</option>
                <?php foreach ($salles as $salle): ?>
                  <option value="<?php echo htmlspecialchars($salle['ID_Salle']); ?>">
                    <?php echo htmlspecialchars($salle['ID_Salle'] . (isset($salle['Equipement']) && $salle['Equipement'] !== '' ? ' - ' . $salle['Equipement'] : '')); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-info btn-sm text-white btn-action">Affecter</button>
            </form>
            <a href="dashboard_user.php?date=<?php echo urlencode($filtre_date); ?>&edit=<?php echo urlencode($rdv['ID_RDV']); ?>" class="btn btn-primary btn-sm btn-action">Modifier</a>
            <form method="post" style="display: inline;">
              <input type="hidden" name="rdv_id" value="<?php echo $rdv['ID_RDV']; ?>">
              <button type="submit" name="action" value="annuler" class="btn btn-danger btn-sm btn-action" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">Annuler</button>
            </form>
          </td>
        </tr>
        <?php if ($rdv_a_modifier === (string) $rdv['ID_RDV']): ?>
          <tr class="table-light">
            <td colspan="9">
              <form method="post" class="row g-2">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="rdv_id" value="<?php echo htmlspecialchars($rdv['ID_RDV']); ?>">
                <div class="col-md-4">
                  <label class="form-label">Date et heure</label>
                  <input type="datetime-local" class="form-control" name="date_heure" value="<?php echo date('Y-m-d\TH:i', strtotime($rdv['DateHeure'])); ?>" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Medecin</label>
                  <select class="form-select" name="id_medecin" required>
                    <?php foreach ($medecins as $med): ?>
                      <option value="<?php echo htmlspecialchars($med['ID_Med']); ?>" <?php echo $med['ID_Med'] === $rdv['ID_Med'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars('Dr. ' . ($med['Prenom'] ?? '') . ' ' . ($med['Nom'] ?? '') . ' - ' . ($med['Specialite'] ?? '')); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary">Enregistrer modification</button>
                </div>
              </form>

              <form method="post" class="row g-2 mt-1">
                <input type="hidden" name="action" value="affecter_salle">
                <input type="hidden" name="rdv_id" value="<?php echo htmlspecialchars($rdv['ID_RDV']); ?>">
                <div class="col-md-8">
                  <label class="form-label">Affecter une salle</label>
                  <select class="form-select" name="id_salle" required>
                    <option value="" disabled selected>Choisir une salle</option>
                    <?php foreach ($salles as $salle): ?>
                      <option value="<?php echo htmlspecialchars($salle['ID_Salle']); ?>">
                        <?php echo htmlspecialchars($salle['ID_Salle'] . ' - ' . $salle['Equipement']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                  <button type="submit" class="btn btn-info text-white">Affecter salle</button>
                </div>
              </form>
            </td>
          </tr>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php if (empty($rendez_vous)): ?>
        <tr>
          <td colspan="9" class="text-center">Aucun rendez-vous pour ce filtre.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Section Médecins -->
  <div>
    <h4 class="section-title">Medecins disponibles</h4>
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
    <h4 class="section-title">Patients enregistres</h4>
    <ul class="list-group">
      <?php foreach ($patients as $pat): ?>
      <li class="list-group-item"><?php echo htmlspecialchars($pat['Prenom'] . ' ' . $pat['Nom']); ?></li>
      <?php endforeach; ?>
      <?php if (empty($patients)): ?>
      <li class="list-group-item">Aucun patient enregistré.</li>
      <?php endif; ?>
    </ul>
  </div>

</div>

<!-- Scripts Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
