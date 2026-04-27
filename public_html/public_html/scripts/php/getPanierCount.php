<?php
require_once "../../init.php";

header("Content-Type: application/json");

if (!isset($_SESSION["idJoueur"])) {
    echo json_encode(["count" => 0]);
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];

$stmt = $pdo->prepare("
    SELECT IFNULL(SUM(quantitePanier),0)
    FROM Paniers
    WHERE idJoueur = ?
");
$stmt->execute([$idJoueur]);

$count = (int)$stmt->fetchColumn();

echo json_encode(["count" => $count]);