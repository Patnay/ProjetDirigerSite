<?php
require_once "../../init.php";

if (empty($_SESSION["idJoueur"])) {
    header("Location: ../../connexion.php");
    exit;
}

$idItem = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idItem = isset($_POST["idItem"]) ? (int)$_POST["idItem"] : 0;
} else {
    $idItem = isset($_GET["idItem"]) ? (int)$_GET["idItem"] : 0;
}

if ($idItem <= 0) {
    $_SESSION["message_erreur"] = "Potion invalide.";
    header("Location: ../../inventaire.php");
    exit;
}
$idJoueur = (int)$_SESSION["idJoueur"];



if ($idItem <= 0) {
    $_SESSION["message_erreur"] = "Potion invalide.";
    header("Location: ../../inventaire.php");
    exit;
}

try {
    $sql = "
        SELECT 
            i.idItem,
            i.nom,
            p.effet,
            inv.quantiteInventaire
        FROM Inventaires inv
        INNER JOIN Items i ON inv.idItem = i.idItem
        INNER JOIN Potions p ON i.idItem = p.idItem
        WHERE inv.idJoueur = ? AND inv.idItem = ?
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idJoueur, $idItem]);
    $potion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$potion) {
        $_SESSION["message_erreur"] = "Cette potion n'existe pas dans votre inventaire.";
        header("Location: ../../inventaire.php");
        exit;
    }

    if ((int)$potion["quantiteInventaire"] <= 0) {
        $_SESSION["message_erreur"] = "Vous n'avez plus cette potion.";
        header("Location: ../../inventaire.php");
        exit;
    }

    $effet = trim((string)$potion["effet"]);

    $pdo->beginTransaction();

    /* Enlever 1 potion de l'inventaire */
    if ((int)$potion["quantiteInventaire"] > 1) {
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

    /* Potion de vie : +5 HP max 50 */
    if (trim($effet) === "Boost la sagesse"){
        $stmt = $pdo->prepare("
            UPDATE Joueurs
            SET ptVie = LEAST(IFNULL(ptVie, 0) + 5, 50)
            WHERE idJoueur = ?
        ");
        $stmt->execute([$idJoueur]);

        $_SESSION["message_info"] = "Potion utilisée : +5 vie.";
    } else {
        /* Toutes les autres potions : 2 minutes */
        $stmt = $pdo->prepare("
            INSERT INTO EffetsJoueurs (idJoueur, effet, dateFin)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 2 MINUTE))
        ");
        $stmt->execute([$idJoueur, $effet]);

        $_SESSION["message_info"] = "Potion utilisée : " . $potion["nom"] . ".";
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION["message_erreur"] = "Impossible d'utiliser cette potion.";
}

header("Location: ../../inventaire.php");
exit;