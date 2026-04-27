<?php
session_start();

require_once "bd/connectionBd.php";

if (!isset($_SESSION["idJoueur"])) {
    header("Location: ../../connexion.php");
    exit;
}

$idItem = $_GET['id'] ?? null;

if (!$idItem) {
    die("Erreur : aucun item sélectionné.");
}

$idJoueur = $_SESSION['idJoueur'];

$stmt = $pdo->prepare("CALL ajouterPanier(?, ?)");
$stmt->execute([$idJoueur, $idItem]);

header("Location: ../../boutique.php");
exit;
?>