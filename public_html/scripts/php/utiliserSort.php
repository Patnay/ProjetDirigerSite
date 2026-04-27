<?php
require_once "../../init.php";

if (empty($_SESSION["idJoueur"])) {
    header("Location: ../../connexion.php");
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];
$idItem = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idItem = isset($_POST["idItem"]) ? (int)$_POST["idItem"] : 0;
} else {
    $idItem = isset($_GET["idItem"]) ? (int)$_GET["idItem"] : 0;
}

if ($idItem <= 0) {
    $_SESSION["message_erreur"] = "Sort invalide.";
    header("Location: ../../inventaire.php");
    exit;
}

try {
    $sql = "
        SELECT 
            i.idItem,
            i.nom,
            inv.quantiteInventaire,
            t.pVie,
            t.pDegat
        FROM Inventaires inv
        INNER JOIN Items i ON inv.idItem = i.idItem
        INNER JOIN Sorts s ON i.idItem = s.idItem
        INNER JOIN Typesorts t ON s.typeSort = t.typeSort
        WHERE inv.idJoueur = ? AND inv.idItem = ?
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idJoueur, $idItem]);
    $sort = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sort) {
        $_SESSION["message_erreur"] = "Ce sort n'existe pas dans votre inventaire.";
        header("Location: ../../inventaire.php");
        exit;
    }

    if ((int)$sort["quantiteInventaire"] <= 0) {
        $_SESSION["message_erreur"] = "Vous n'avez plus ce sort.";
        header("Location: ../../inventaire.php");
        exit;
    }

    $nomSort = trim((string)$sort["nom"]);
    $degats = (int)($sort["pDegat"] ?? 0);

    $pdo->beginTransaction();

    /* Enlever 1 sort de l'inventaire */
    if ((int)$sort["quantiteInventaire"] > 1) {
        $stmt = $pdo->prepare("
            UPDATE Inventaires
            SET quantiteInventaire = quantiteInventaire - 1
            WHERE idJoueur = ? AND idItem = ?
        ");
        $stmt->execute([$idJoueur, $idItem]);
    } else {
        $stmt = $pdo->prepare("
            DELETE FROM Inventaires
            WHERE idJoueur = ? AND idItem = ?
        ");
        $stmt->execute([$idJoueur, $idItem]);
    }

    /* Sort spécial de soin */
    if ($nomSort === "Erdtree GreatHeal") {
        $stmt = $pdo->prepare("
            UPDATE Joueurs
            SET ptVie = IFNULL(ptVie, 0) + 15
            WHERE idJoueur = ?
        ");
        $stmt->execute([$idJoueur]);

        $_SESSION["message_info"] = "Sort utilisé : Erdtree GreatHeal (+15 vie).";
        unset($_SESSION["sort_popup"]);
    } else {
        /* Tous les autres sorts blessent le joueur */
        $stmt = $pdo->prepare("
            UPDATE Joueurs
            SET ptVie = GREATEST(IFNULL(ptVie, 0) - ?, 0)
            WHERE idJoueur = ?
        ");
        $stmt->execute([$degats, $idJoueur]);

        $_SESSION["message_info"] = "Sort utilisé : " . $nomSort . ".";
        $_SESSION["sort_popup"] = "Le sort " . $nomSort . " vous inflige " . $degats . " dégâts !";
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION["message_erreur"] = "Impossible d'utiliser ce sort.";
}

header("Location: ../../inventaire.php");
exit;