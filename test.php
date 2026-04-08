
<?php

$host = '127.0.0.1';
$port = '3306'; // port MySQL
$db   = 'test';
$user = 'grouped';
$pass = 'grouped';
$charset = 'utf8mb4';
$options=[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES=>false
];
// Chaîne de connexion incluant le port
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    
    // Configuration des options d'erreur
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie !";
} catch (\PDOException $e) {
    error_log($e->getMessage());
    // Message d'erreur clair si la connexion échoue
    // echo "Impossible de se connecter à la base de données : " . $e->getMessage();
    // Optionnel : pour débogage détaillé
   
}
    

?>

