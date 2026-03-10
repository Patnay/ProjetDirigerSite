<?php
/*
  session_start();
  $pseudo = $_SESSION['pseudo'];

  $pseudo = trim($_POST['pseudo'] ?? '');
  $mp1 = $_POST['mp1'] ?? '';
  $mp2 = $_POST['mp2'] ?? '';
  $nom = trim($_POST['negociable'] ?? '');
  $prenom = trim($_POST['image'] ?? '');
  $courriel = trim($_POST['courriel'] ?? '');
  $admin = isset($_POST['admin']) ? 'admin' : 'usager';
  $erreurs = [];

  if(strlen($pseudo) < 2 || strlen($pseudo) > 25){
    $erreurs[] = "Votre pseudo doit contenir entre 2 et 25 caratères.";
  }

  if(strlen($mp1) < 12 || strlen($mp1) > 50){
    $erreurs[] = "Votre mot de passe doit contenir entre 12 et 50 caratères.";
  }

  if($mp1 != $mp2){
    $erreurs[] = "Les mot de passe ne sont pas pareil.";
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

  $stmt = $pdo -> prepare("SELECT * FROM usager where pseudo = ? ");
  $stmt -> execute([$pseudo]);
  if($stmt -> fetch()){
    $erreurs[] ="Ce pseudo existe déjà.";
  }
  
  if(!($erreurs)){
    $hash = password_hash($mp1, PASSWORD_DEFAULT);

    $stmt = $pdo ->prepare("INSERT into usager(pseudo, mdp, nom, prenom, courriel, role) values(?, ?, ?, ?, ?, ?)");
    $stmt -> execute([$pseudo, $hash, $nom, $prenom, $courriel, $admin]);

    $confirmation = "https://app.mailjet.com/signup?lang=fr_FR";
    mail($courriel, 
          "Confirmation du compte",
          "Veuillez confirmer votre compte en cliquant sur: $confirmation");
    echo "Un courriel a été envoyé pour confirmer votre compte.";
  }
    */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>
<body>
  <div>
    <main>
      <h1>Se créer un compte</h1>
      <form action="inscription.php", method="POST">
        <fieldset>
        <label for="alias">Alias:</label>
        <input name="alias" id="alias" value="<?= htmlspecialchars($alias)?>" required>
        <br>
        <label for="mp1">Mot de passe:</label>
        <input type="password" name="mp1" id="mp1" value="<?= htmlspecialchars($mp1)?>" required>
        <br>
        <label for="mp2">Répéter le mot de passe:</label>
        <input type="password" name="mp2" id="mp2" value="<?= htmlspecialchars($mp2)?>" required>
        <br>
        <label for="nom">Nom:</label>
        <input name="nom" id="nom" value="<?= htmlspecialchars($nom)?>" required>
        <br>
        <label for="prenom">Prenom:</label>
        <input name="prenom" id="prenom" value="<?= htmlspecialchars($prenom)?>" required>
        <br>
        <label for="courriel">Courriel:</label>
        <input name="courriel" id="courriel" value="<?= htmlspecialchars($courriel)?>" required>
        <br>
        <button type="submit">S'inscrire</button>
        </fieldset>
        <br>
      </form>
    </main>
  </div>
</body>
</html>