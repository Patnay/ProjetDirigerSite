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

/* Récupérer stock et quantité actuelle */
$sql = "SELECT i.prix, i.quantiteStock, p.quantitePanier
        FROM Paniers p
        INNER JOIN Items i ON p.idItem = i.idItem
        WHERE p.idJoueur = ? AND p.idItem = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idJoueur, $idItem]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo json_encode(["erreur" => "Item introuvable dans le panier."]);
    exit;
}

$quantiteActuelle = (int)$item["quantitePanier"];
$stock = (int)$item["quantiteStock"];
$depasseStock = false;

if (isset($_POST["quantite"])) {
    $nouvelleQuantite = (int)$_POST["quantite"];
} elseif (isset($_POST["action"])) {
    $action = $_POST["action"];
    $nouvelleQuantite = $quantiteActuelle;

    if ($action === "plus") {
        $nouvelleQuantite++;
    } elseif ($action === "moins") {
        $nouvelleQuantite--;
    }
} else {
    echo json_encode(["erreur" => "Action invalide."]);
    exit;
}

if ($nouvelleQuantite < 1) {
    $nouvelleQuantite = 0;
}

if ($nouvelleQuantite > $stock) {
    $nouvelleQuantite = $stock;
    $depasseStock = true;
}

if ($nouvelleQuantite <= 0) {
    $stmt = $pdo->prepare("DELETE FROM Paniers WHERE idJoueur = ? AND idItem = ?");
    $stmt->execute([$idJoueur, $idItem]);
} else {
    $stmt = $pdo->prepare("UPDATE Paniers SET quantitePanier = ? WHERE idJoueur = ? AND idItem = ?");
    $stmt->execute([$nouvelleQuantite, $idJoueur, $idItem]);
}

/* Total mis à jour */
$stmt = $pdo->prepare("SELECT IFNULL(SUM(i.prix * p.quantitePanier), 0) AS total
                       FROM Paniers p
                       INNER JOIN Items i ON p.idItem = i.idItem
                       WHERE p.idJoueur = ?");
$stmt->execute([$idJoueur]);
$total = $stmt->fetchColumn();

echo json_encode([
    "quantite" => $nouvelleQuantite,
    "total" => number_format((float)$total, 2, ".", ""),
    "depasseStock" => $depasseStock
]);