<?php
$host    = '158.69.48.109'; // serveur MySQL
$db      = 'dbdarquest6';  // nom de la base de données
$user    = 'equipe6';  // utilisateur MySQL
$pass    = 'hx843s4s'; // mot de passe
$charset = 'utf8mb4'; 

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
    PDO::ATTR_EMULATE_PREPARES => false, 
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Connexion réussie à MySQL";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>