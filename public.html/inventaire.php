<?php
require_once "scripts/php/bd/connectionBd.php";

$sql = "SELECT Inventaires.idItem, Items.nom, Items.image, FROM Items INNER JOIN 
Inventaires ON Inventaires.idItem = Items.idItem";
$stmt = $pdo->query($sql);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php 
 session_start();
 $alias = $_SESSION['alias'];
$stmt = $conn->prepare("SELECT  FROM Joueurs WHERE alias = ?");
$stmt->execute([$alias]);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
<meta charset="UTF-8">
<title>Boutique</title>
<link rel="stylesheet" href="css/styles.css">
</head>

<body>

<?php include "header.php"; ?>

<main class="shop-page">

<div class="shop-container">

<!-- FILTRE -->
<aside class="filters">

<h2>Filtrer la recherche</h2>

<div class="filter-block">
<label>Catégorie</label>
<select>
<option>Toutes</option>
<option>Armes</option>
<option>Armures</option>
<option>Potions</option>
</select>
</div>

<div class="filter-block">
<label>Prix</label>
<p>Min: __ Max: __</p>
</div>

<div class="filter-block">
<label>Évaluation</label>
<p>Min: __ Max: __</p>
</div>

</aside>


<!-- PRODUITS -->
<section class="products-grid">

<?php foreach($produits as $produit): ?>

<div class="product-card">

<div class="product-image">
<img src="images/<?php echo $produit['image']; ?>" alt="">
</div>

<h3><?php echo $produit['nom']; ?></h3>

<p class="price"><?php echo $produit['prix']; ?> $</p>

<p class="stock">
Stock : <?php echo $produit['qtStock']; ?>
</p>

<div class="product-actions">

<a href="detail.php?id=<?php echo $produit['idItem']; ?>">
Detail
</a>

<a class="add-link" href="ajouter_panier.php?id=<?php echo $produit['idItem']; ?>">
Ajouter
</a>

</div>

</div>

<?php endforeach; ?>

</section>

</div>

</main>

</body>
</html>