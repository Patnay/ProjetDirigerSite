<?php
try {
    ini_set('display_errors', 'Off');
    ini_set('log_errors', 'On');
    error_reporting(E_ALL);
    session_start();
} catch (Exception) {

}
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
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include "header.php"; ?>

<div class="shop-page">
    <div class="profile-wrapper">

        <div class="profile-card">
            <h1>Profil</h1>

            <div class="profile-box">
                <div class="profile-image-box">
                    <?php if (!empty($joueur["img"])): ?>
                        <img src="images/<?= htmlspecialchars($joueur["img"]) ?>" alt="Image du profil" class="profile-image">
                    <?php else: ?>
                        <div class="profile-image-placeholder">👤</div>
                    <?php endif; ?>
                </div>

                <p><strong>Pseudo :</strong> <?= htmlspecialchars($joueur["alias"] ?? "") ?></p>
                <p><strong>Courriel :</strong> <?= htmlspecialchars($joueur["courriel"] ?? "") ?></p>
                <p><strong>Or :</strong> <?= (int)($joueur["nbOr"] ?? 0) ?></p>
                <p><strong>Argent :</strong> <?= (int)($joueur["nbArgent"] ?? 0) ?></p>
                <p><strong>Bronze :</strong> <?= (int)($joueur["nbBronze"] ?? 0) ?></p>
                <p><strong>Vie :</strong> <?= (int)($joueur["pVie"] ?? 0) ?></p>

                <div class="profile-inventory-link">
                    <a href="inventaire.php" class="icon-link" title="Inventaire">📦</a>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <h2>Modifier vos informations</h2>

            <?php if ($message !== ""): ?>
                <p class="message-info"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <?php if ($erreur !== ""): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreur) ?></p>
            <?php endif; ?>

            <form action="profil.php" method="POST" class="profile-form">
                <label for="alias">Pseudo :</label>
                <input type="text" id="alias" name="alias" required
                       value="<?= htmlspecialchars($joueur["alias"] ?? "") ?>">

                <label for="courriel">Courriel :</label>
                <input type="email" id="courriel" name="courriel" required
                       value="<?= htmlspecialchars($joueur["courriel"] ?? "") ?>">

                <label for="mp">Nouveau mot de passe :</label>
                <input type="password" id="mp" name="mp"
                       placeholder="Laisser vide pour ne pas changer">
                <a href="logout.php" class="logout-btn">Déconnexion</a>
                <button type="submit" class="filter-btn">Enregistrer les modifications</button>
            </form>
        </div>

    </div>
</div>

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
    <source src="musique/profil.mp3" type="audio/mp3">
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
</body>
</html>