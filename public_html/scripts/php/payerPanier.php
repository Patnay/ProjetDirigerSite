<?php
require_once "../../init.php";

if (!isset($_SESSION["idJoueur"])) {
    header("Location: ../../connexion.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../panier.php");
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];

/* Récupérer contenu panier */
$sql = "SELECT i.idItem, i.nom, i.prix, i.quantiteStock, p.quantitePanier
        FROM Paniers p
        INNER JOIN Items i ON p.idItem = i.idItem
        WHERE p.idJoueur = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idJoueur]);
$panier = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$panier) {
    echo "<script>alert('Votre panier est vide.'); window.location='../../panier.php';</script>";
    exit;
}

/* Vérifier stock */
foreach ($panier as $item) {
    if ((int)$item["quantitePanier"] > (int)$item["quantiteStock"]) {
        echo "DEPASSE_STOCK";
        exit;
    }
}

/* Calcul total */
$total = 0;
foreach ($panier as $item) {
    $total += ((float)$item["prix"] * (int)$item["quantitePanier"]);
}

/* Récupérer argent joueur */
$stmt = $pdo->prepare("SELECT nbOr, nbArgent, nbBronze FROM Joueurs WHERE idJoueur = ?");
$stmt->execute([$idJoueur]);
$joueur = $stmt->fetch(PDO::FETCH_ASSOC);

$argentTotal = ((int)$joueur["nbOr"] + (int)$joueur["nbArgent"] + (int)$joueur["nbBronze"]);

if ($argentTotal < $total) {
    echo "FONDS_INSUFFISANTS";
    exit;
}

try {
    $pdo->beginTransaction();

    /* Déduire d'abord bronze, puis argent, puis or */
    $reste = (int)ceil($total);

    $nbBronze = (int)$joueur["nbBronze"];
    $nbArgent = (int)$joueur["nbArgent"];
    $nbOr = (int)$joueur["nbOr"];

    $take = min($nbBronze, $reste);
    $nbBronze -= $take;
    $reste -= $take;

    $take = min($nbArgent, $reste);
    $nbArgent -= $take;
    $reste -= $take;

    $take = min($nbOr, $reste);
    $nbOr -= $take;
    $reste -= $take;

    $stmt = $pdo->prepare("UPDATE Joueurs SET nbOr = ?, nbArgent = ?, nbBronze = ? WHERE idJoueur = ?");
    $stmt->execute([$nbOr, $nbArgent, $nbBronze, $idJoueur]);

    /* Enlever stock et ajouter inventaire */
    foreach ($panier as $item) {
        $idItem = (int)$item["idItem"];
        $qte = (int)$item["quantitePanier"];

        $stmt = $pdo->prepare("UPDATE Items SET quantiteStock = quantiteStock - ? WHERE idItem = ?");
        $stmt->execute([$qte, $idItem]);

        $stmt = $pdo->prepare("SELECT quantiteInventaire FROM Inventaires WHERE idJoueur = ? AND idItem = ?");
        $stmt->execute([$idJoueur, $idItem]);
        $inv = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($inv) {
            $stmt = $pdo->prepare("UPDATE Inventaires SET quantiteInventaire = quantiteInventaire + ? WHERE idJoueur = ? AND idItem = ?");
            $stmt->execute([$qte, $idJoueur, $idItem]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO Inventaires (idJoueur, idItem, quantiteInventaire) VALUES (?, ?, ?)");
            $stmt->execute([$idJoueur, $idItem, $qte]);
        }
    }

    /* Vider panier */
    $stmt = $pdo->prepare("DELETE FROM Paniers WHERE idJoueur = ?");
    $stmt->execute([$idJoueur]);

    $pdo->commit();

    echo "OK";
    exit;
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<script>alert('Erreur pendant le paiement.'); window.location='../../panier.php';</script>";
    exit;
}
