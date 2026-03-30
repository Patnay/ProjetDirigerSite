<?php
require_once "../../init.php";

if (!isset($_SESSION["idJoueur"])) {
    header("Location: ../../connexion.php");
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];

$stmt = $pdo->prepare("DELETE FROM Paniers WHERE idJoueur = ?");
$stmt->execute([$idJoueur]);

header("Location: ../../panier.php");
exit;