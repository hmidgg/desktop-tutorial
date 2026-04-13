<?php
class Horaire {
    
    public $jour;
    public $heure;
    public $id_Medecin;
    public $pdo;

    public function __construct($jour, $heure, $id_Medecin, $pdo) {
        $this->jour = $jour;
        $this->heure = $heure;
        $this->id_Medecin = $id_Medecin;
        $this->pdo = $pdo;
    }
    
    /**
     * Vérifie si le créneau horaire est disponible
     */
    public function Est_Disponible() {
        $sql = "SELECT Est_Disponible FROM Horaire WHERE Jour = :jour AND Heure = :heure AND ID_Med = :id_med";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':jour' => $this->jour,
            ':heure' => $this->heure,
            ':id_med' => $this->id_Medecin
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result['Est_Disponible'] == 1;
        }
        return false;
    }
    
    /**
     * Affiche les informations du créneau horaire
     */
    public function Afficher():void {
        $sql = "SELECT h.*, m.ID_Med, m.Specialite 
                FROM Horaire h 
                JOIN Medecin m ON h.ID_Med = m.ID_Med 
                WHERE h.Jour = :jour AND h.Heure = :heure AND h.ID_Med = :id_med";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':jour' => $this->jour,
            ':heure' => $this->heure,
            ':id_med' => $this->id_Medecin
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "Jour: " . $result['Jour'] . "<br>";
            echo "Heure: " . $result['Heure'] . "h<br>";
            echo "Médecin: " . $result['ID_Med'] . "<br>";
            echo "Spécialité: " . $result['Specialite'] . "<br>";
            echo "Disponible: " . ($result['Est_Disponible'] ? "Oui" : "Non") . "<br>";
        } else {
            echo "Aucun horaire trouvé pour ce médecin à ce jour et heure.";
        }
        
        
    }
    
}
?>