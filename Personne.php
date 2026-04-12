<?php
class Personne{
    public $nom;
    public $prenom;
    public $Email;
    public $Telephone;
    public $pdo;

    public function __construct($nom,$prenom,$Email,$Telephone,$pdo){
        $this->nom=$nom;
        $this->prenom=$prenom;
        $this->Email=$Email;
        $this->Telephone=$Telephone;
        $this->pdo=$pdo;
        }
    function Afficher_profil(): void {
        $stmt = $this->pdo->prepare("SELECT * FROM Personne WHERE Email = ?");
        $stmt->execute([$this->Email]);
        $row = $stmt->fetch();
        if ($row) {
            echo "nom : "       . $row['Nom']       . "\n";
            echo "prenom : "    . $row['Prenom']    . "\n";
            echo "Email : "     . $row['Email']     . "\n";
            echo "Telephone : " . $row['Telephone'] . "\n";
        } else {
            echo "Personne introuvable.\n";
        }
    }


    function modifierProfile($nom, $prenom, $Email, $Telephone): void {
        $stmt = $this->pdo->prepare(
            "UPDATE Personne SET Nom=?, Prenom=?, Telephone=? WHERE Email=?"
        );
        $stmt->execute([$nom, $prenom, $Telephone, $this->Email]);
        $this->nom       = $nom;
        $this->prenom    = $prenom;
        $this->Email     = $Email;
        $this->Telephone = $Telephone;
    }

}
?>