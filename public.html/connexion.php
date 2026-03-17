<?php
  session_start();

  $message='';
        if(@$_SERVER['REQUEST_METHOD'] === 'POST'){
            $alias = $_POST['pseudo'] ?? '';
            $mp = $_POST['mp'] ?? '';


          if($pseudo=== ''|| $mp === ''){
            $message = "Veuillez remplir tous les champs.";
          }
          else{
            $stmt = $pdo -> prepare("SELECT * FROM Joueurs WHERE alias = ?");
            $stmt -> execute([$alias]);
            $user = $stmt -> fetch();
            if($user && password_verify($mp, $user['mp'])){
              $_SESSION['connecte'] = true;
              $_SESSION['alias'] = $user['alias'];
              header('Location: boutique.php');
              exit;
            }
            else{
              $message = "L'utilisateur ou le mot de passe est incorrect.";
            }
          }
        }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <link href="css/styles.css" rel="stylesheet"/>
  <title>Connexion</title>
</head>
<body class="shop-page">
  <div class="connect-container">
    <header>
      <?php include "include/header.php" ?>
    </header>
    <main>
      <h1>Connexion</h1>
      <?php if($message): ?><p style="color:red"><?= htmlspecialchars($message)?></p><?php endif; ?>
      <form action="connection.php", method="POST">
        <fieldset>
            <br>
            <legend>Veuillez vous connecter: </legend>
            <label for="pseudo">Pseudo:</label>
            <input type="text" id="pseudo" name="pseudo" required>
            <br>
            <br>
            <label for="mp">Mot de passe:</label>
            <input type="password" id="mp" name="mp" required>
            <br>
            <br>
        <button type="submit">Se connecter</button>
        </fieldset>
        <br>
      </form>
    </main>
    <aside>
     <?php include "include/aside.php"?>
    </aside>
    <footer>
      <?php include "include/footer.php" ?>
    </footer>
  </div>
</body>
</html>