<?php
session_start();
include("scripts/php/bd/connectionBd.php");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alias = trim($_POST['pseudo'] ?? '');
    $motDePasse = $_POST['mp'] ?? '';

    if ($alias === '' || $motDePasse === '') {
        $message = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Joueurs WHERE alias = ?");
        $stmt->execute([$alias]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashSaisi = hash('sha256', $motDePasse);
            $hashBd = $user['motDePasse'] ?? '';

            if ($hashBd !== '' && hash_equals($hashBd, $hashSaisi)) {
                $_SESSION['connecte'] = true;
                $_SESSION['idJoueur'] = $user['idJoueur'];
                $_SESSION['alias'] = $user['alias'];

                header('Location: boutique.php');
                exit;
            } else {
                $message = "L'utilisateur ou le mot de passe est incorrect.";
            }
        } else {
            $message = "L'utilisateur ou le mot de passe est incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/connexion.css">
</head>
<?php include "header.php"; ?>

<body>

<main class="shop-page">
<div class="connect-container">
        <h1>Connexion</h1>

        <?php if ($message !== ''): ?>
            <p class="message-erreur"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form action="connexion.php" method="POST">
            <fieldset class="connect-block">

                <label for="pseudo">Pseudo :</label>
                <input
                    type="text"
                    id="pseudo"
                    name="pseudo"
                    value="<?= htmlspecialchars($_POST['pseudo'] ?? '') ?>"
                    required
                >

                <br><br>

                <label for="mp">Mot de passe :</label>
                <input
                    type="password"
                    id="mp"
                    name="mp"
                    required
                >

                <br><br>

                <button type="submit" class="connect-button">Se connecter</button>
            </fieldset>
        </form>

        <br>

        <p>
            Pas encore de compte ?
            <a href="inscription.php">Créer un compte</a>
        </p>
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
    <source src="musique/eldenRing.mp3" type="audio/mp3">
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