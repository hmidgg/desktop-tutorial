<?php
class User{
    public $login;
    public $password;
    public function __construct($login,$password){
        $this->login=$login;
        $this->password=$password;
    }
    public function Se_Connecter(): void {
        if (!empty($this->login) && !empty($this->password)) {
            echo "Connexion réussie. Bienvenue, {$this->login} !\n";
        } else {
            echo "Login ou mot de passe vide.\n";
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

        $rdv = [
            'patient'    => $this->login,
            'medecin_id' => $medecin_id,
            'salle_id'   => $salle_id,
            'date_heure' => $date_heure,
            'motif'      => $motif,
            'statut'     => 'confirmé',
        ];

        echo "✅ Rendez-vous créé avec succès :\n";
        foreach ($rdv as $cle => $valeur) {
            echo "   $cle : $valeur\n";
        }
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

        echo "✏️  Rendez-vous #{$rdv_id} modifié :\n";
        echo "   Nouvelle date  : $nouvelle_date\n";
        echo "   Nouvelle salle : $nouvelle_salle\n";
        echo "   Nouveau motif  : $nouveau_motif\n";
    }

    // ─────────────────────────────────────────────
    // 4. ANNULER UN RENDEZ-VOUS
    // ─────────────────────────────────────────────
    public function Annuler_rendezvous(int $rdv_id): void {
        if ($rdv_id <= 0) {
            echo "ID de rendez-vous invalide.\n";
            return;
        }

        echo "❌ Rendez-vous #{$rdv_id} annulé avec succès.\n";
    }

    // ─────────────────────────────────────────────
    // 5. CONSULTER LES SALLES DISPONIBLES
    // ─────────────────────────────────────────────
    public function Consulter_SalleDisponible(string $date_heure): void {
        if (strtotime($date_heure) === false) {
            echo "Date invalide : $date_heure\n";
            return;
        }

        $salles_simulees = [
            ['id' => 1, 'nom' => 'Salle A', 'capacite' => 10, 'type' => 'Consultation'],
            ['id' => 2, 'nom' => 'Salle B', 'capacite' => 5,  'type' => 'Chirurgie'],
            ['id' => 3, 'nom' => 'Salle C', 'capacite' => 8,  'type' => 'Radiologie'],
        ];

        echo "=== Salles disponibles le $date_heure ===\n";
        foreach ($salles_simulees as $salle) {
            echo "  [{$salle['id']}] {$salle['nom']} | Capacité : {$salle['capacite']} | Type : {$salle['type']}\n";
        }
    }

    // ─────────────────────────────────────────────
    // 6. CONSULTER L'HORAIRE MÉDICAL
    // ─────────────────────────────────────────────
    public function Consulter_HoraireMedicale(int $medecin_id, string $date): void {
        if (strtotime($date) === false) {
            echo "Date invalide : $date\n";
            return;
        }

        $horaires_simules = [
            ['heure_debut' => '08:00', 'heure_fin' => '10:00'],
            ['heure_debut' => '14:00', 'heure_fin' => '16:00'],
        ];

        $creneaux_pris = ['08:30', '09:00'];

        echo "=== Horaires du médecin #$medecin_id — $date ===\n";
        foreach ($horaires_simules as $h) {
            echo "Plage : {$h['heure_debut']} → {$h['heure_fin']}\n";

            $debut = new DateTime("{$date} {$h['heure_debut']}");
            $fin   = new DateTime("{$date} {$h['heure_fin']}");

            while ($debut < $fin) {
                $creneau = $debut->format('H:i');
                $dispo   = in_array($creneau, $creneaux_pris) ? '❌ Pris' : '✅ Libre';
                echo "   $creneau — $dispo\n";
                $debut->modify('+30 minutes');
            }
        }
    }
}



?>