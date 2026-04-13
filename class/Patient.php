<?php
class Patient extends Personne{
    public $Matricule;
    public $password;
    public function __construct($Matricule,$nom,$prenom,$Email,$Telephone,$password,$pdo){
        $this->Matricule=$Matricule;
        $this->password=$password;
        parent::__construct($nom,$prenom,$Email,$Telephone,$pdo);

    }
        public function inscrire(): void {
        $this->insererPersonne();
        $hash = password_hash($this->password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO Patient (Matricule, Email, password) VALUES (?, ?, ?)"
        );
        $stmt->execute([$this->Matricule, $this->Email, $hash]);
        echo "Compte patient créé avec succès.\n";
    }
 
    public function Se_Connecter(): void {
        $stmt = $this->pdo->prepare("SELECT * FROM Patient WHERE Email = ?");
        $stmt->execute([$this->Email]);
        $pat = $stmt->fetch();
 
        if ($pat && password_verify($this->password, $pat['password'])) {
            echo "Connexion réussie. Bienvenue, {$this->nom} !\n";
        } else {
            echo "Email ou mot de passe incorrect.\n";
        }
    }
    public function Annuler_RDV(int $rdv_id): void {
        if ($rdv_id <= 0) {
            echo "ID invalide.\n";
            return;
        }
        $stmt = $this->pdo->prepare(
            "DELETE FROM Rendez_vous WHERE ID_RDV = ? AND Matricule = ?"
        );
        $stmt->execute([$rdv_id, $this->Matricule]);
        echo "Rendez-vous #{$rdv_id} annulé.\n";
    }
 
    public function Voir_Historique_RDV(): void {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, m.Specialite
             FROM Rendez_vous r
             JOIN Medecin m ON r.ID_Med = m.ID_Med
             WHERE r.Matricule = ?
             ORDER BY r.DateHeure DESC"
        );
        $stmt->execute([$this->Matricule]);
        $rdvs = $stmt->fetchAll();
 
        echo "=== Historique RDV de {$this->nom} ===\n";
        foreach ($rdvs as $rdv) {
            echo "  RDV #{$rdv['ID_RDV']} | Date : {$rdv['DateHeure']} | Spécialité : {$rdv['Specialite']}\n";
        }
    }
    public function Prendre_RDV(int $id_medecin, string $dateHeure): void {
    // Vérification des données
    if ($id_medecin <= 0 || empty($dateHeure)) {
        echo "Données invalides pour le rendez-vous.\n";
        return;
    }

    // Vérifier si le médecin existe
    $stmt = $this->pdo->prepare("SELECT * FROM Medecin WHERE ID_Med = ?");
    $stmt->execute([$id_medecin]);
    $med = $stmt->fetch();

    if (!$med) {
        echo "Médecin introuvable.\n";
        return;
    }

    // Vérifier si le créneau est déjà pris
    $stmt = $this->pdo->prepare(
        "SELECT * FROM Rendez_vous WHERE ID_Med = ? AND DateHeure = ?"
    );
    $stmt->execute([$id_medecin, $dateHeure]);

    if ($stmt->fetch()) {
        echo "Ce créneau est déjà réservé.\n";
        return;
    }

    // Insérer le rendez-vous
    $stmt = $this->pdo->prepare(
        "INSERT INTO Rendez_vous (Matricule, ID_Med, DateHeure)
         VALUES (?, ?, ?)"
    );
    $stmt->execute([$this->Matricule, $id_medecin, $dateHeure]);

    echo "Rendez-vous pris avec succès le {$dateHeure}.\n";
}


}