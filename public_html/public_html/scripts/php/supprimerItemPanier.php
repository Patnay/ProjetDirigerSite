<?php
require_once "../../init.php";
header("Content-Type: application/json");

if (!isset($_SESSION["idJoueur"])) {
    echo json_encode(["erreur" => "Vous devez être connecté."]);
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];
$idItem = isset($_POST["idItem"]) ? (int)$_POST["idItem"] : 0;

if ($idItem <= 0) {
    echo json_encode(["erreur" => "Item invalide."]);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM Paniers WHERE idJoueur = ? AND idItem = ?");
$stmt->execute([$idJoueur, $idItem]);

$stmt = $pdo->prepare("SELECT IFNULL(SUM(i.prix * p.quantitePanier), 0) AS total
                       FROM Paniers p
                       INNER JOIN Items i ON p.idItem = i.idItem
                       WHERE p.idJoueur = ?");
$stmt->execute([$idJoueur]);
$total = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM Paniers WHERE idJoueur = ?");
$stmt->execute([$idJoueur]);
$nb = (int)$stmt->fetchColumn();

echo json_encode([
    "total" => number_format((float)$total, 2, ".", ""),
    "panierVide" => ($nb === 0)
]);