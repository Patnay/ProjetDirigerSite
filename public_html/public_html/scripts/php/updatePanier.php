<?php
session_start();
require_once("scripts/php/bd/connectionBd.php");

if (!isset($_SESSION["idJoueur"])) {
    http_response_code(403);
    exit;
}

$idJoueur = $_SESSION["idJoueur"];
$idItem   = intval($_POST["idItem"]);
$action   = $_POST["action"]; // "plus" ou "moins"

// Récupérer la quantité actuelle
$sql = "SELECT quantitePanier FROM Paniers WHERE idJoueur = ? AND idItem = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idJoueur, $idItem]);
$ligne = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ligne) {
    echo json_encode(["error" => "Item non présent dans le panier"]);
    exit;
}

$quantite = $ligne["quantitePanier"];

if ($action === "plus") {
    $quantite++;
} elseif ($action === "moins") {
    $quantite--;
}

if ($quantite <= 0) {
    $sql = "DELETE FROM Paniers WHERE idJoueur = ? AND idItem = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idJoueur, $idItem]);

    $quantite = 0;
} else {
    $sql = "UPDATE Paniers SET quantitePanier = ? WHERE idJoueur = ? AND idItem = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$quantite, $idJoueur, $idItem]);
}

$sql = "SELECT SUM(i.prix * p.quantitePanier) AS total
        FROM Items i
        INNER JOIN Paniers p ON i.idItem = p.idItem
        WHERE p.idJoueur = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idJoueur]);
$total = $stmt->fetch(PDO::FETCH_ASSOC)["total"] ?? 0;

echo json_encode([
    "quantite" => $quantite,
    "total" => $total
]);