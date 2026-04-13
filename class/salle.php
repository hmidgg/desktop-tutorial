<?php
class Salle {
    public $id;
    public $equipement;
    public $pdo;

    public function __construct($id, $equipement, $pdo) {
        $this->id = $id;
        $this->equipement = $equipement;
        $this->pdo = $pdo;
    }

        public function Est_Disponible(): void {
        $stmt = $this->pdo->prepare(
            "SELECT Est_Disponible FROM Salle WHERE ID_Salle = ?"
        );
        $stmt->execute([$this->id]);
        $row = $stmt->fetch();
 
        if (!$row) {
            echo "Salle introuvable.\n";
            return;
        }
 
        if ($row['Est_Disponible']) {
            echo "Salle {$this->id} est disponible.\n";
        } else {
            echo "Salle {$this->id} n'est pas disponible.\n";
        }
    }
 
    public function AfficherEquipement(): void {
        $stmt = $this->pdo->prepare(
            "SELECT Equipement FROM Salle WHERE ID_Salle = ?"
        );
        $stmt->execute([$this->id]);
        $row = $stmt->fetch();
 
        if (!$row) {
            echo "Salle introuvable.\n";
            return;
        }
 
        echo "=== Équipement de la Salle {$this->id} ===\n";
        echo "  " . $row['Equipement'] . "\n";
    }
}