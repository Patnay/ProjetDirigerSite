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
    header("Location: ../../inventaire.php");
    exit;
}

try {
    $stmt = $pdo->prepare("CALL vendreItem(?, ?, ?)");
    $stmt->execute([$idJoueur, $idItem, $qteVente]);
    $stmt->closeCursor();

    $_SESSION["message_info"] = "Vente effectuée avec succès.";
} catch (PDOException $e) {
    $_SESSION["message_erreur"] = "Impossible de vendre cet item.";
}

header("Location: ../../vendre.php?id=" . $idItem);
exit;