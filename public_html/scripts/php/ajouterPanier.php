<?php

require_once "bd/connectionBd.php";

if (!isset($_SESSION['idJoueur'])) {
    die("Erreur : vous devez être connecté pour ajouter au panier.");
}
$idItem = $_GET['id'] ?? null;
if (!$idItem) {
    die("Erreur : aucun item sélectionné.");
}
$idJoueur = $_SESSION['idJoueur'];
$stmt = $pdo->prepare("INSERT INTO Panier (pIdJoueur, pIdItem) VALUES (?, ?)");
$stmt->execute([$idJoueur, $idItem]);
header("Location: ../../boutique.php");
exit;
?>