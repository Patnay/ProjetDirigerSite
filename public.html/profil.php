<?php
require_once "scripts/php/bd/connectionBd.php";
?>
<?php
  session_start();

  $message='';
        if(@$_SERVER['REQUEST_METHOD'] === 'POST'){
            $alias = $_POST['alias'] ?? '';
            $mp = $_POST['mp'] ?? '';


          if($alias=== ''|| $mp === ''){
            $message = "Veuillez remplir tous les champs.";
          }
          else{
          $hash = password_hash($mp1, PASSWORD_DEFAULT);

          $stmt = $pdo ->prepare("UPDATE alias, motDePasse FROM Joueurs WHERE alias = ? values(?, ?)");
          $stmt -> execute([$alias, $hash]);
              $message = "Modifications faites avec succès.";
              exit;
          }
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
<body>
  <div class="shop-page">
    <header>
      <?php include "header.php" ?>
    </header>
    <main class="shop-container">
      <h1>Profil</h1>
      <aside class="filters">
        <div class="filter-block">
          <h2 id="alias">Le profil</h2>
          <div class="product-image">
          <img src=<?php echo $profile['image']; ?> alt="">
          </div>
        </div>
      </aside>
      <div>
        <button onclick="window.location.href = 'inventaire.php';"></button>
      </div>
      <div class="shop-container">
         <form action="connection.php", method="POST">
        <fieldset>
            <br>
            <legend>Veuillez vous connecter: </legend>
            <label for="alias">Pseudo:</label>
            <input type="text" id="alias" name="alias" required>
            <br>
            <br>
            <label for="mp">Mot de passe:</label>
            <input type="password" id="mp" name="mp" required>
            <br>
            <br>
        <button type="submit">Enregistrer les modifications.</button>
        </fieldset>
        <br>
      </form>
      </div>
    </main>
  </div>
</body>
</html>