<?php
require_once "init.php";

/* =========================
   PROTECTION DE LA PAGE
========================= */
if (empty($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}

$stmtAdmin = $pdo->prepare("SELECT estAdmin FROM Joueurs WHERE idJoueur = ?");
$stmtAdmin->execute([$_SESSION["idJoueur"]]);
$isAdmin = (int)$stmtAdmin->fetchColumn();

if ($isAdmin !== 1) {
    header("Location: boutique.php");
    exit;
}

/* =========================
   TRAITEMENT DU FORMULAIRE
========================= */
$message = "";
$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $question = trim($_POST["question"] ?? "");
    $bonneReponse = trim($_POST["bonneReponse"] ?? "");
    $valeurD = trim($_POST["valeurD"] ?? "");
    $mauvaise1 = trim($_POST["mauvaise1"] ?? "");
    $mauvaise2 = trim($_POST["mauvaise2"] ?? "");
    $mauvaise3 = trim($_POST["mauvaise3"] ?? "");

    if (
    $question === "" ||
    $valeurD === "" ||
    $bonneReponse === "" ||
    $mauvaise1 === "" ||
    $mauvaise2 === "" ||
    $mauvaise3 === ""
    ) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $pdo->prepare("CALL AjouterEnigme(?, 'A', ?, '0', ?, ?, ?, ?)");
            $stmt->execute([
            $question,
            $valeurD,
            $bonneReponse,
            $mauvaise1,
            $mauvaise2,
            $mauvaise3
            ]);
            $stmt->closeCursor();

            $message = "Énigme ajoutée avec succès.";

            /* vider les champs après succès */
            $_POST = [];
        } catch (PDOException $e) {
            $erreur = "Impossible d'ajouter l'énigme.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Ajouter une énigme</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="favicon.png">
</head>
<body>

<?php include "header.php"; ?>

<main class="shop-page">
    <div class="shop-container">

        <aside class="filters">
            <h2>Admin</h2>

            <div class="filter-block">
                <p><strong>Section :</strong></p>
                <select>
                    <option value="AddEnigme">Création d'énigmes</option>
                    <option value="AddItem">Ajouter item</option>
                    <option value="CheckUser">Surveillance Utilisateurs</option>
                </select>
            </div>

            <div class="filter-block">
                <a href="boutique.php" class="reset-btn">Retour boutique</a>
            </div>
        </aside>

        <section class="products-grid" style="grid-template-columns:1fr; max-width:700px;">
            <div class="product-card">

                

                <?php if ($message !== ""): ?>
                    <p class="message-info"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>

                <?php if ($erreur !== ""): ?>
                    <p class="message-erreur"><?= htmlspecialchars($erreur) ?></p>
                <?php endif; ?>

                <?php 
                include("scripts/php/formulaire/addEnigmeForm.php");
                include("scripts/php/formulaire/addItemBoutiqueA.php");
                include("scripts/php/formulaire/addItemBoutiqueS.php");
                include("scripts/php/formulaire/addItemBoutiqueP.php");
                include("scripts/php/formulaire/addItemBoutiqueR.php");
                ?>

            </div>
        </section>

    </div>
</main>
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
    <source src="musique/FinaleUndertale.mp3" type="audio/mp3">
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

<!-- Bouton Mario -->
<img id="marioBtn"
     src="image/champignon.png"
     style="
        position: fixed;
        bottom: 20px;
        left: 20px;
        width: 60px;
        height: 60px;
        cursor: pointer;
        z-index: 9999;
     ">
<script>
document.getElementById("marioBtn").addEventListener("click", () => {
    window.location.href = "mario2.html";
});
</script>
</body>
</html>