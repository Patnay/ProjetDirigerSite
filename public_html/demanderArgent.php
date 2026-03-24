<?php
require_once "init.php";

/* =========================
   VÉRIFIER SI CONNECTÉ
========================= */
if (empty($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];
$message = "";
$erreur = "";

/* =========================
   NOMBRE DE DEMANDES
========================= */
$sqlCount = "SELECT COUNT(*) FROM DemandesArgent WHERE idJoueur = :idJoueur";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute([":idJoueur" => $idJoueur]);
$nbDemandes = (int)$stmtCount->fetchColumn();

$restant = 3 - $nbDemandes;
if ($restant < 0) {
    $restant = 0;
}

/* =========================
   TRAITEMENT DU BOUTON
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($nbDemandes >= 3) {
        $erreur = "Vous avez déjà utilisé vos 3 essais.";
    } else {
        try {
            $pdo->beginTransaction();

            /* Ajouter une entrée dans l'historique */
            $sqlInsert = "
                INSERT INTO DemandesArgent (idJoueur, montantOr, montantArgent, montantBronze)
                VALUES (:idJoueur, 200, 200, 200)
            ";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([":idJoueur" => $idJoueur]);

            /* Ajouter l'argent au joueur */
            $sqlUpdate = "
                UPDATE Joueurs
                SET 
                    nbOr = IFNULL(nbOr, 0) + 200,
                    nbArgent = IFNULL(nbArgent, 0) + 200,
                    nbBronze = IFNULL(nbBronze, 0) + 200
                WHERE idJoueur = :idJoueur
            ";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([":idJoueur" => $idJoueur]);

            $pdo->commit();

            $message = "Vous avez reçu 200 or, 200 argent et 200 bronze.";

            /* Recalculer le nombre de demandes */
            $stmtCount->execute([":idJoueur" => $idJoueur]);
            $nbDemandes = (int)$stmtCount->fetchColumn();
            $restant = 3 - $nbDemandes;
            if ($restant < 0) {
                $restant = 0;
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            $erreur = "Une erreur est survenue lors de la demande.";
        }
    }
}

/* =========================
   RÉCUPÉRER LE JOUEUR
========================= */
$sqlJoueur = "SELECT alias, nbOr, nbArgent, nbBronze FROM Joueurs WHERE idJoueur = :idJoueur";
$stmtJoueur = $pdo->prepare($sqlJoueur);
$stmtJoueur->execute([":idJoueur" => $idJoueur]);
$joueur = $stmtJoueur->fetch(PDO::FETCH_ASSOC);

if (!$joueur) {
    session_unset();
    session_destroy();
    header("Location: connexion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demander argent</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="favicon.ico">
</head>
<body>

<?php include "header.php"; ?>

<main class="about-page">
    <section class="about-container">

        <h1>Demander de l'argent</h1>

        <div class="about-block">
            <h2>Votre compte</h2>
            <p><strong>Joueur :</strong> <?= htmlspecialchars($joueur["alias"]) ?></p>
            <p><strong>Or :</strong> <?= (int)$joueur["nbOr"] ?></p>
            <p><strong>Argent :</strong> <?= (int)$joueur["nbArgent"] ?></p>
            <p><strong>Bronze :</strong> <?= (int)$joueur["nbBronze"] ?></p>
        </div>

        <div class="about-block">
            <h2>Récompense</h2>
            <p>Chaque demande donne :</p>
            <p><strong>200 or</strong></p>
            <p><strong>200 argent</strong></p>
            <p><strong>200 bronze</strong></p>
            <p><strong>Essais restants :</strong> <?= $restant ?> / 3</p>

            <?php if ($message !== ""): ?>
                <p class="message-info"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <?php if ($erreur !== ""): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreur) ?></p>
            <?php endif; ?>

            <?php if ($restant > 0): ?>
                <form method="post" action="demander_argent.php">
                    <button type="submit" class="filter-btn">Recevoir l'argent</button>
                </form>
            <?php else: ?>
                <p class="message-erreur">Vous avez atteint la limite de 3 essais.</p>
            <?php endif; ?>
        </div>

    </section>
</main>

</body>
</html>