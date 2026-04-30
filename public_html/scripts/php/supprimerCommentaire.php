<?php
require_once "../../init.php";

if (!isset($_SESSION["idJoueur"])) {
    header("Location: ../../connexion.php");
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];
$idItem = (int)($_POST["idItem"] ?? 0);

if ($idItem <= 0) {
    header("Location: ../../boutique.php");
    exit;
}

$stmt = $pdo->prepare("
    DELETE FROM Evaluations
    WHERE idJoueur = ? AND idItem = ?
");
$stmt->execute([$idJoueur, $idItem]);

header("Location: ../../detail.php?id=" . $idItem);
exit;