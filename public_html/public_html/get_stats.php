<?php
require_once "init.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["idJoueur"])) {
    echo json_encode(["error" => "NOT_CONNECTED"]);
    exit;
}

$id = (int) $_SESSION["idJoueur"];

$stmt = $pdo->prepare("SELECT nbOr, nbArgent, nbBronze FROM Joueurs WHERE idJoueur = ?");
$stmt->execute([$id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stats) {
    echo json_encode(["error" => "NO_PLAYER"]);
    exit;
}

echo json_encode([
    "nbOr" => (int) $stats["nbOr"],
    "nbArgent" => (int) $stats["nbArgent"],
    "nbBronze" => (int) $stats["nbBronze"]
]);
