<?php

try {
    ini_set('display_errors', 'Off');
    ini_set('log_errors', 'On');
    error_reporting(E_ALL);
    session_start();
} catch (Exception) {

}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <link href="css/styles.css" rel="stylesheet"/>
  <title>Non connecté</title>
</head>
<body class="shop-page">
  <div class="shop-container">
    <header>
      <?php include "include/header.php" ?>
    </header>
    <main>
      <h1>Erreur, vous n'êtes pas connecté.</h1>
      <br>
      <p style="color:red">Veuillez vous connectez.</p>
      <button href="connexion.php">Allez à la connection</button>
    </main>
  </div>
</body>
</html>