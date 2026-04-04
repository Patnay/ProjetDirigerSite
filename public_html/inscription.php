<?php
  session_start();
  include("scripts/php/formulaire/scriptInscription.php");
?>
<!DOCTYPE html>
<html lang="fr">


<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="favicon.png">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/connexion.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription</title>
</head>

<?php include "header.php" ?>

<body>
<main class="shop-page">
  <div class="connect-container">
      <h1>Se créer un compte</h1>
      <form action="inscription.php" method="POST">
        <fieldset class="connect-block">
          <label for="alias">Alias:</label>
          <input name="alias" id="alias" required > <!-- value="<?= htmlspecialchars($alias) ?>" -->
          <br>
          <br>
          <label for="mp1">Mot de passe:</label>
          <input type="password" name="mp1" id="mp1" required>
          <br>
          <br>
          <label for="mp2">Répéter le mot de passe:</label>
          <input type="password" name="mp2" id="mp2" required>
          <br>
          <br>
          <label for="nom">Nom:</label>
          <input name="nom" id="nom" required>
          <br>
          <br>
          <label for="prenom">Prenom:</label>
          <input name="prenom" id="prenom" required>
          <br>
          <br>
          <label for="courriel">Courriel:</label>
          <input name="courriel" id="courriel" required>
          <br>
          <br>
          <button type="submit" class="connect-button">S'inscrire</button>
        </fieldset>
        <br>
      </form>
    <?php
    if (!empty($_SESSION['erreurs_inscription'])) {
        echo '<div class="erreurs-inscription">';
        echo '<h2>Erreurs :</h2><ul>';
        foreach ($_SESSION['erreurs_inscription'] as $e) {
            echo "<li>$e</li>";
        }
        echo '</ul></div>';
        unset($_SESSION['erreurs_inscription']);
    }
    ?>
  <br>
      <button class="connect-button" onclick="window.location.href = 'connexion.php'">Déjà connecté? Connectez-vous</button>
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

<!--$confirmation = "https://app.mailjet.com/signup?lang=fr_FR";
    mail($courriel, 
          "Confirmation du compte",
          "Veuillez confirmer votre compte en cliquant sur: $confirmation");
    echo "Un courriel a été envoyé pour confirmer votre compte."; -->
