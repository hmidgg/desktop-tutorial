<?php
require_once'Personne.php';
class User extends Personne{
    public $login;
    public $password;
    public function __construct($login,$password,$nom,$prenom,$Email,$Telephone,$pdo){
        $this->login=$login;
        $this->password=$password;
        parent::__construct($nom,$prenom,$Email,$Telephone,$pdo);
    }
    public function Se_Connecter(): bool {
        $stmt = $this->pdo->prepare("SELECT * FROM User WHERE Email = ?");
        $stmt->execute([$this->login]);
        $user = $stmt->fetch();

        if ($user && $this->password === $user['password']) {
            return true;
        } else {
            return false;
        }
    }


    // ─────────────────────────────────────────────
    // 2. CRÉER UN RENDEZ-VOUS
    // ─────────────────────────────────────────────
    public function Creer_rendezvous(
        int    $medecin_id,
        string $date_heure,
        int    $salle_id,
        string $motif = ''
    ): void {
        if (strtotime($date_heure) === false) {
            echo "Date invalide : $date_heure\n";
            return;
        }
        $id_rdv = uniqid("RDV_");
        $stmt = $this->pdo->prepare(
            "INSERT INTO Rendez_vous (ID_RDV, DateHeure, login, ID_Med)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$id_rdv, $date_heure, $this->login, $medecin_id]);
        echo "Rendez-vous créé avec succès. ID : $id_rdv\n";
    }


    // ─────────────────────────────────────────────
    // 3. MODIFIER UN RENDEZ-VOUS
    // ─────────────────────────────────────────────
    public function Modifier_rendezvous(
        int    $rdv_id,
        string $nouvelle_date,
        int    $nouvelle_salle,
        string $nouveau_motif = ''
    ): void {
        if (strtotime($nouvelle_date) === false) {
            echo "Date invalide : $nouvelle_date\n";
            return;
        }
        $stmt = $this->pdo->prepare(
            "UPDATE Rendez_vous SET DateHeure = ? WHERE ID_RDV = ? AND login = ?"
        );
        $stmt->execute([$nouvelle_date, $rdv_id, $this->login]);
        echo "Rendez-vous #{$rdv_id} modifié avec succès.\n";
    }


    // ─────────────────────────────────────────────
    // 4. ANNULER UN RENDEZ-VOUS
    // ─────────────────────────────────────────────
    public function Annuler_rendezvous(int $rdv_id): void {
        if ($rdv_id <= 0) {
            echo "ID de rendez-vous invalide.\n";
            return;
        }
        $stmt = $this->pdo->prepare(
            "DELETE FROM Rendez_vous WHERE ID_RDV = ? AND login = ?"
        );
        $stmt->execute([$rdv_id, $this->login]);
        echo "Rendez-vous #{$rdv_id} annulé avec succès.\n";
    }

    // ─────────────────────────────────────────────
    // 5. CONSULTER LES SALLES DISPONIBLES
    // ─────────────────────────────────────────────
    public function Consulter_SalleDisponible(string $date_heure): void {
        if (strtotime($date_heure) === false) {
            echo "Date invalide : $date_heure\n";
            return;
        }
        $stmt = $this->pdo->prepare("SELECT * FROM Salle WHERE Est_Disponible = 1");
        $stmt->execute();
        $salles = $stmt->fetchAll();

        echo "=== Salles disponibles le $date_heure ===\n";
        foreach ($salles as $salle) {
            echo "  [{$salle['ID_Salle']}] Équipement : {$salle['Equipement']}\n";
        }
    }


    // ─────────────────────────────────────────────
    // 6. CONSULTER L'HORAIRE MÉDICAL
    // ─────────────────────────────────────────────
    public function Consulter_HoraireMedecins(int $medecin_id, string $date): void {
        if (strtotime($date) === false) {
            echo "Date invalide : $date\n";
            return;
        }
        $stmt = $this->pdo->prepare(
            "SELECT * FROM Horaire WHERE ID_Med = ? AND Est_Disponible = 1"
        );
        $stmt->execute([$medecin_id]);
        $horaires = $stmt->fetchAll();

        echo "=== Horaires du médecin #$medecin_id — $date ===\n";
        foreach ($horaires as $h) {
            echo "  Jour : {$h['Jour']} | Heure : {$h['Heure']}\n";
        }
    }

}



?>