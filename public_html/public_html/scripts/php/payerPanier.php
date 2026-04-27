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

try {
    // 🔥 Appel direct à ta procédure SQL
    $stmt = $pdo->prepare("CALL payerPanier(?)");
    $stmt->execute([$idJoueur]);

    // 🔥 Si tout fonctionne
    echo "OK";
    exit;

} catch (PDOException $e) {

    $msg = $e->getMessage();

    // 🔥 Détection des erreurs SIGNAL renvoyées par ta procédure
    if (str_contains($msg, "Panier vide")) {
        echo "PANIER_VIDE";
        exit;
    }

    if (str_contains($msg, "Stock insuffisant")) {
        echo "DEPASSE_STOCK";
        exit;
    }

    if (str_contains($msg, "Fonds insuffisants")) {
        echo "FONDS_INSUFFISANTS";
        exit;
    }

    // 🔥 Erreur inconnue
    echo "ERREUR";
    exit;
}
?>