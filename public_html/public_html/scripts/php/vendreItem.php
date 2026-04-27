<?php
require_once "../../init.php";

if (empty($_SESSION["idJoueur"])) {
    header("Location: ../../connexion.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../inventaire.php");
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];
$idItem = isset($_POST["idItem"]) ? (int)$_POST["idItem"] : 0;
$qteVente = isset($_POST["qteVente"]) ? (int)$_POST["qteVente"] : 0;

if ($idItem <= 0 || $qteVente <= 0) {
    $_SESSION["message_erreur"] = "Valeurs invalides pour la vente.";
    $_SESSION["message_item_id"] = $idItem;
    header("Location: ../../inventaire.php");
    exit;
}

/* Vérifier la quantité actuelle avant la vente */
$stmt = $pdo->prepare("
    SELECT quantiteInventaire
    FROM Inventaires
    WHERE idJoueur = ? AND idItem = ?
");
$stmt->execute([$idJoueur, $idItem]);
$qteActuelle = $stmt->fetchColumn();

if ($qteActuelle === false) {
    $_SESSION["message_erreur"] = "Item introuvable dans votre inventaire.";
    $_SESSION["message_item_id"] = $idItem;
    header("Location: ../../inventaire.php");
    exit;
}

$qteActuelle = (int)$qteActuelle;

try {
    $stmt = $pdo->prepare("CALL vendreItem(?, ?, ?)");
    $stmt->execute([$idJoueur, $idItem, $qteVente]);
    $stmt->closeCursor();

    $_SESSION["message_info"] = "Vente effectuée avec succès.";
    $_SESSION["message_item_id"] = $idItem;

    /* Si on a vendu toute la quantité, retour inventaire */
    if ($qteVente >= $qteActuelle) {
        header("Location: ../../inventaire.php");
        exit;
    }

    /* Sinon on reste sur la page vendre du même item */
    header("Location: ../../vendre.php?id=" . $idItem);
    exit;

} catch (PDOException $e) {
    $_SESSION["message_erreur"] = "Impossible de vendre cet item.";
    $_SESSION["message_item_id"] = $idItem;
    header("Location: ../../vendre.php?id=" . $idItem);
    exit;
}