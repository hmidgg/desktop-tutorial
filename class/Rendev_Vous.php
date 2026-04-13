<?php
class Rendez_vous {
    public $id_RDV;
    public $DateHeure;
    public $Matricule;
    public $login;
    public $Id_Medecin;
    public $pdo;
    
    public function __construct($id_RDV, $DateHeure, $Matricule, $login, $Id_Medecin, $pdo) {
        $this->id_RDV = $id_RDV;
        $this->DateHeure = $DateHeure;
        $this->Matricule = $Matricule;
        $this->login = $login;
        $this->Id_Medecin = $Id_Medecin;
        $this->pdo = $pdo;
    }
    
    /**
     * Valider un rendez-vous
     */
    public function Valider():void {
        $sql = "UPDATE Rendez_vous SET statut = 'valide' WHERE ID_RDV = :id_rdv";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_rdv' => $this->id_RDV]);
    }
    
    /**
     * Annuler un rendez-vous
     */
    public function Annuler():void{
        $sql = "DELETE FROM Rendez_vous WHERE ID_RDV = :id_rdv";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_rdv' => $this->id_RDV]);
    }
    
    /**
     * Modifier un rendez-vous
     */
    public function Modifier():void {
        $sql = "UPDATE Rendez_vous 
                SET DateHeure = :date_heure, Matricule = :matricule, login = :login, ID_Med = :id_med 
                WHERE ID_RDV = :id_rdv";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':date_heure' => $this->DateHeure,
            ':matricule' => $this->Matricule,
            ':login' => $this->login,
            ':id_med' => $this->Id_Medecin,
            ':id_rdv' => $this->id_RDV
        ]);
    }
}
?>