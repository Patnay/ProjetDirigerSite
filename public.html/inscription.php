<?php 
require_once "scripts/php/bd/connectionBd.php";
session_start();
?>
<?php
  $alias = trim($_POST['alias'] ?? '');
  $mp1 = $_POST['mp1'] ?? '';
  $mp2 = $_POST['mp2'] ?? '';
  $nom = trim($_POST['nom'] ?? '');
  $prenom = trim($_POST['prenom'] ?? '');
  $courriel = trim($_POST['courriel'] ?? '');
  $erreurs = [];

  if(strlen($alias) < 2 || strlen($alias) > 25){
    $erreurs[] = "Votre pseudo doit contenir entre 2 et 25 caratères.";
  }

  if(strlen($mp1) < 12 || strlen($mp1) > 50){
    $erreurs[] = "Votre mot de passe doit contenir entre 12 et 50 caratères.";
  }

  if($mp1 != $mp2){
    $erreurs[] = "Les mots de passe ne sont pas pareil.";
  }

  if(strlen($nom) < 2 || strlen($nom) > 50){
    $erreurs[] = "Votre nom doit contenir entre 2 et 50 caratères.";
  }

  if(strlen($prenom) < 2 || strlen($prenom) > 50){
    $erreurs[] = "Votre prenom doit contenir entre 2 et 50 caratères.";
  }

  if(strlen($courriel) < 6 || strlen($courriel) > 254){
    $erreurs[] = "Votre courriel n'est pas valide.";
  }

  $stmt = $pdo -> prepare("SELECT * FROM Joueurs where alias = ? ");
  $stmt -> execute([$alias]);
  if($stmt -> fetch()){
    $erreurs[] ="Cet alias existe déjà.";
  }
  
  if(!($erreurs)){
    $hash = password_hash($mp1, PASSWORD_DEFAULT);

    $stmt = $pdo ->prepare("INSERT into Joueurs(alias, prenom, nom, courriel, motDePasse) values(?, ?, ?, ?, ?, ?, ?)");
    $stmt -> execute([$alias, $prenom, $nom, $courriel, $hash]);

    $confirmation = "https://app.mailjet.com/signup?lang=fr_FR";
    mail($courriel, 
          "Confirmation du compte",
          "Veuillez confirmer votre compte en cliquant sur: $confirmation");
    echo "Un courriel a été envoyé pour confirmer votre compte.";
  }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <link rel="stylesheet" href="css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>
<body class="shop-page">
  <div>
    <header>
      <?php include "header.php" ?>
    </header>
    <main class="shop-container">
      <h1>Se créer un compte</h1>
      <form action="inscription.php", method="POST">
        <fieldset>
        <label for="alias">Alias:</label>
        <input name="alias" id="alias" required value="<?= htmlspecialchars($alias)?>">
        <br>
        <label for="mp1">Mot de passe:</label>
        <input type="password" name="mp1" id="mp1" required value="<?= htmlspecialchars($mp1)?>">
        <br>
        <label for="mp2">Répéter le mot de passe:</label>
        <input type="password" name="mp2" id="mp2" required value="<?= htmlspecialchars($mp2)?>">
        <br>
        <label for="nom">Nom:</label>
        <input name="nom" id="nom" required value="<?= htmlspecialchars($nom)?>">
        <br>
        <label for="prenom">Prenom:</label>
        <input name="prenom" id="prenom" required value="<?= htmlspecialchars($prenom)?>">
        <br>
        <label for="courriel">Courriel:</label>
        <input name="courriel" id="courriel" required value="<?= htmlspecialchars($courriel)?>">
        <br>
        <button type="submit">S'inscrire</button>
        </fieldset>
        <br>
      </form>
      <button class="connect" href="connexion.php">Déjà connecté? Connectez-vous</a>
    </main>
  </div>
</body>
</html>
<!--$mage = isset($_POST['mage']) ? 'mage' : 'non-mage';

<label for="mage">Est-ce que vous êtes un mage?: </label>
        <input type="checkbox" name="mage" ></label>
        <br>-->