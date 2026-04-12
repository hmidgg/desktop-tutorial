<?php
class Personne{
    public $nom;
    public $prenom;
    public $Email;
    public $Telephone;
    public function __construct($nom,$prenom,$Email,$Telephone){
        $this->nom=$nom;
        $this->prenom=$prenom;
        $this->Email=$Email;
        $this->Telephone=$Telephone;
        }
    function Afficher_profil(): void{
        echo "nom".$this->nom;
        echo "prenom".$this->prenom;
        echo "Email".$this->Email;
        echo "Telephone".$this->Telephone;
    }
    function modifierProfile($nom, $prenom, $Email, $Telephone): void {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->Email = $Email;
        $this->Telephone = $Telephone;
    }
}
?>