<?php
 session_start();
?>
<!DOCTYPE html>
<html lang="fr">


<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/connexion.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription</title>
</head>

<body class="shop-page">
  <div>
    <header>
      <?php include "header.php" ?>
    </header>
    <main class="connect-container">
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

      <?php include("scripts/php/formulaire/scriptInscription.php")?>

      <button class="connect-button" onclick="window.location.href = 'connexion.php'">Déjà connecté? Connectez-vous</button>
    </main>
  </div>
</body>

</html>

<!--$confirmation = "https://app.mailjet.com/signup?lang=fr_FR";
    mail($courriel, 
          "Confirmation du compte",
          "Veuillez confirmer votre compte en cliquant sur: $confirmation");
    echo "Un courriel a été envoyé pour confirmer votre compte."; -->