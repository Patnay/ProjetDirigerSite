<?php
require_once "init.php";

/* Vérifier connexion */
if (empty($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];
$message = "";
$messageErreur = "";

/* Récupérer joueur */
$stmt = $pdo->prepare("SELECT * FROM Joueurs WHERE idJoueur = ?");
$stmt->execute([$idJoueur]);
$joueur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joueur) {
    session_unset();
    session_destroy();
    header("Location: connexion.php");
    exit;
}

/* Compter les demandes */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM DemandesArgent WHERE idJoueur = ?");
$stmt->execute([$idJoueur]);
$nbDemandes = (int)$stmt->fetchColumn();

$maxEssais = 3;
$essaisRestants = $maxEssais - $nbDemandes;

/* Traitement seulement si formulaire soumis */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($essaisRestants > 0) {
        try {
            $pdo->beginTransaction();

            /* Ajouter les pièces */
            $stmt = $pdo->prepare("
                UPDATE Joueurs
                SET nbOr = IFNULL(nbOr, 0) + 200,
                    nbArgent = IFNULL(nbArgent, 0) + 200,
                    nbBronze = IFNULL(nbBronze, 0) + 200
                WHERE idJoueur = ?
            ");
            $stmt->execute([$idJoueur]);

            /* Ajouter une trace de la demande */
            $stmt = $pdo->prepare("
                INSERT INTO DemandesArgent (idJoueur)
                VALUES (?)
            ");
            $stmt->execute([$idJoueur]);

            $pdo->commit();

            $message = "Récompense reçue !";

            /* Recharger les données du joueur */
            $stmt = $pdo->prepare("SELECT * FROM Joueurs WHERE idJoueur = ?");
            $stmt->execute([$idJoueur]);
            $joueur = $stmt->fetch(PDO::FETCH_ASSOC);

            /* Recalculer les essais */
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM DemandesArgent WHERE idJoueur = ?");
            $stmt->execute([$idJoueur]);
            $nbDemandes = (int)$stmt->fetchColumn();
            $essaisRestants = $maxEssais - $nbDemandes;

        } catch (Exception $e) {
            $pdo->rollBack();
            $messageErreur = "Une erreur est survenue lors de la demande.";
        }

    } else {
        $messageErreur = "Vous avez atteint le maximum d'essais.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demander argent</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type = "image/png" href="favicon.png">
</head>
<body>

<?php include("header.php"); ?>

<main class="about-page">
    <section class="about-container">

        <h1>Demander de l'argent</h1>

        <div class="about-block">
            <h2>Votre compte</h2>
            <p><strong>Joueur :</strong> <?= htmlspecialchars($joueur["alias"] ?? "") ?></p>
            <p><strong>Or :</strong> <?= (int)($joueur["nbOr"] ?? 0) ?></p>
            <p><strong>Argent :</strong> <?= (int)($joueur["nbArgent"] ?? 0) ?></p>
            <p><strong>Bronze :</strong> <?= (int)($joueur["nbBronze"] ?? 0) ?></p>
        </div>

        <div class="about-block">
            <h2>Récompense</h2>
            <p>Chaque demande donne :</p>
            <p><strong>200 or</strong></p>
            <p><strong>200 argent</strong></p>
            <p><strong>200 bronze</strong></p>

            <p><strong>Essais restants :</strong> <?= $essaisRestants ?> / <?= $maxEssais ?></p>

            <?php if (!empty($message)): ?>
                <p class="message-info"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <?php if (!empty($messageErreur)): ?>
                <p class="message-erreur"><?= htmlspecialchars($messageErreur) ?></p>
            <?php endif; ?>

            <?php if ($essaisRestants > 0): ?>
                <form method="POST" action="demanderArgent.php">
                    <button type="submit" class="filter-btn">Recevoir l'argent</button>
                </form>
            <?php endif; ?>

        </div>

    </section>
    <!-- Bouton musique -->
<img id="musicToggle" 
     src="image/sonOff.jpg" 
     style="
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
        cursor: pointer;
        z-index: 9999;
     ">
<audio id="bgMusic" loop>
    <source src="musique/leyndell.mp3" type="audio/mp3">
</audio>
<script>
const music = document.getElementById("bgMusic");
const toggleBtn = document.getElementById("musicToggle");

let musicOn = false;

toggleBtn.addEventListener("click", () => {
    musicOn = !musicOn;

    if (musicOn) {
        music.play();
        toggleBtn.src = "image/sonOn.jpg";
    } else {
        music.pause();
        toggleBtn.src = "image/sonOff.jpg";
    }
});
</script>
</main>

</body>
</html>