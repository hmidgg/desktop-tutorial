<?php
require_once'Personne.php';
class Medecin extends Personne{
    public $id_medecin;
    public $Adresse;
    public $Specialite;
    public $ID_Spe;
     public function __construct($ID_Med, $Adresse, $Specialite, $ID_Spe,
                                $nom, $prenom, $Email, $Telephone, $password, $pdo = null) {
        $this->ID_Med     = $ID_Med;
        $this->Adresse    = $Adresse;
        $this->Specialite = $Specialite;
        $this->ID_Spe     = $ID_Spe;
        $this->password   = $password;
        parent::__construct($nom, $prenom, $Email, $Telephone, $pdo);
    }
    public function Consulter_Planning(): void {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM Rendez_vous WHERE ID_Med = ? ORDER BY DateHeure ASC"
        );
        $stmt->execute([$this->ID_Med]);
        $rdvs = $stmt->fetchAll();

        echo "=== Planning du Dr. {$this->nom} ===\n";
        foreach ($rdvs as $rdv) {
            echo "  RDV #{$rdv['ID_RDV']} | Date : {$rdv['DateHeure']} | Patient : {$rdv['Matricule']}\n";
        }
    }
    public function Ajouter_Horaire($jour, $heure, $disponible = 1): void {
    if (empty($jour) || empty($heure)) {
        echo "Jour ou heure invalide.\n";
        return;
    }

    $stmt = $this->pdo->prepare(
        "INSERT INTO Horaire (Jour, Heure, ID_Med, Est_Disponible)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->execute([
        $jour,
        $heure,
        $this->ID_Med,
        $disponible
    ]);

    echo "Horaire ajouté avec succès pour Dr. {$this->nom}.\n";
}




}
?>