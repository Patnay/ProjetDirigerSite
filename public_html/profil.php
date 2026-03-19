<?php
session_start();
require_once "scripts/php/bd/connectionBd.php";

/* =========================
   PROTÉGER LA PAGE
========================= */
if (!isset($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}

$idJoueur = (int) $_SESSION["idJoueur"];
$message = "";
$erreur = "";

/* =========================
   RÉCUPÉRER LE JOUEUR
========================= */
$sqlJoueur = "SELECT * FROM Joueurs WHERE idJoueur = :idJoueur";
$stmtJoueur = $pdo->prepare($sqlJoueur);
$stmtJoueur->execute([":idJoueur" => $idJoueur]);
$joueur = $stmtJoueur->fetch(PDO::FETCH_ASSOC);

if (!$joueur) {
    session_unset();
    session_destroy();
    header("Location: connexion.php");
    exit;
}

/* =========================
   TRAITEMENT DU FORMULAIRE
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nouvelAlias = trim($_POST["alias"] ?? "");
    $nouveauMotDePasse = trim($_POST["mp"] ?? "");

    if ($nouvelAlias === "") {
        $erreur = "Le pseudo ne peut pas être vide.";
    } else {
        $sqlVerif = "SELECT idJoueur FROM Joueurs WHERE alias = :alias AND idJoueur != :idJoueur";
        $stmtVerif = $pdo->prepare($sqlVerif);
        $stmtVerif->execute([
            ":alias" => $nouvelAlias,
            ":idJoueur" => $idJoueur
        ]);

        if ($stmtVerif->fetch()) {
            $erreur = "Ce pseudo est déjà utilisé.";
        } else {
            if ($nouveauMotDePasse !== "") {
                $hash = hash("sha256", $nouveauMotDePasse);

                $sqlUpdate = "
                    UPDATE Joueurs
                    SET alias = :alias, motDePasse = :motDePasse
                    WHERE idJoueur = :idJoueur
                ";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    ":alias" => $nouvelAlias,
                    ":motDePasse" => $hash,
                    ":idJoueur" => $idJoueur
                ]);
            } else {
                $sqlUpdate = "
                    UPDATE Joueurs
                    SET alias = :alias
                    WHERE idJoueur = :idJoueur
                ";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    ":alias" => $nouvelAlias,
                    ":idJoueur" => $idJoueur
                ]);
            }

            $_SESSION["alias"] = $nouvelAlias;

            /* Recharger les infos après modification */
            $stmtJoueur->execute([":idJoueur" => $idJoueur]);
            $joueur = $stmtJoueur->fetch(PDO::FETCH_ASSOC);

            $message = "Modifications enregistrées avec succès.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include "header.php"; ?>

<div class="shop-page">
    <div class="profile-wrapper">

        <div class="profile-card">
            <h1>Profil</h1>

            <div class="profile-box">
                <p><strong>Pseudo :</strong> <?= htmlspecialchars($joueur["alias"] ?? "") ?></p>
                <p><strong>Courriel :</strong> <?= htmlspecialchars($joueur["courriel"] ?? "") ?></p>
                <p><strong>Or:</strong> <?= (int)($joueur["nbOr"] ?? 0) ?> pièces</p>
                <p><strong>Argent:</strong> <?= (int)($joueur["nbArgent"] ?? 0) ?> pièces</p>
                <p><strong>Bronze:</strong> <?= (int)($joueur["nbBronze"] ?? 0) ?> pièces</p>
                <p><strong>Points :</strong> <?= (int)($joueur["ptVie"] ?? 0) ?></p>

                <div class="profile-inventory-link">
                    <a href="inventaire.php" class="icon-link" title="Inventaire">📦</a>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <h2>Modifier vos infos de connexion</h2>

            <?php if ($message !== ""): ?>
                <p class="message-info"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <?php if ($erreur !== ""): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreur) ?></p>
            <?php endif; ?>

            <form action="profil.php" method="POST" class="profile-form">
                <label for="alias">Pseudo :</label>
                <input
                    type="text"
                    id="alias"
                    name="alias"
                    required
                    value="<?= htmlspecialchars($joueur["alias"] ?? "") ?>"
                >

                <label for="mp">Nouveau mot de passe :</label>
                <input
                    type="password"
                    id="mp"
                    name="mp"
                    placeholder="Laisser vide pour ne pas changer"
                >
                <?php 
                // Pour le débugage mettre en commentaire pour la mise en production
                echo("Le id Joueur est = :".$_SESSION["idJoueur"]);
                ?>
                <button type="submit" class="filter-btn">
                    Enregistrer les modifications
                </button>
                <a  href="scripts/php/deconnect.php">Deconnecter</a>
            </form>
        </div>

    </div>
</div>

</body>
</html>