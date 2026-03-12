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
          <img src="images/<?php echo $profile['image']; ?>" alt="">
          </div>
        </div>
      </aside>
    </main>
  </div>
</body>
</html>