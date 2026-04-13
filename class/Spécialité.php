<?php
class Spécialité {
    public $ID_Spec;
    public $Nom_Spec;
    protected $pdo;

    public function __construct($ID_Spec,$Nom_Spec,$pdo){
        $this->ID_Spec=$ID_Spec;
        $this->Nom_Spec=$Nom_Spec;
        $this->pdo=$pdo;
    }
        public function Afficher_Medcins(): void {
        $stmt = $this->pdo->prepare(
            "SELECT m.ID_Med, p.Nom, p.Prenom, p.Email, p.Telephone, m.Addresse
             FROM Medecin m
             JOIN Personne p ON m.Email = p.Email
             WHERE m.ID_Spe = ?"
        );
        $stmt->execute([$this->ID_Spe]);
        $medecins = $stmt->fetchAll();
 
        if (empty($medecins)) {
            echo "Aucun médecin trouvé pour la spécialité : {$this->Nom}\n";
            return;
        }
 
        echo "=== Médecins de la spécialité : {$this->Nom} ===\n";
        foreach ($medecins as $med) {
            echo "  ID      : {$med['ID_Med']}\n";
            echo "  Nom     : {$med['Nom']} {$med['Prenom']}\n";
            echo "  Email   : {$med['Email']}\n";
            echo "  Tél     : {$med['Telephone']}\n";
            echo "  Adresse : {$med['Addresse']}\n";
            echo "  ---\n";
        }
    }

}