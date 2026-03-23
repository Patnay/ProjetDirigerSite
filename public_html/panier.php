<?php
session_start();
if (!isset($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}
require_once("scripts/php/bd/connectionBd.php");


$sql = "SELECT i.nom,i.prix,i.photo,p.quantitePanier
        FROM Items i INNER JOIN 
        Paniers p ON i.idItem = p.idItem 
        WHERE p.idJoueur = ? ";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["idJoueur"]]);

$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT SUM(i.prix*p.quantitePanier) as prixTotal
        FROM Items i INNER JOIN 
        Paniers p ON i.idItem = p.idItem 
        WHERE p.idJoueur = ? ";
        
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["idJoueur"]]);
$prixTotal = $stmt->fetch(PDO::FETCH_ASSOC);
if($prixTotal['prixTotal'] === null) 
    $prixTotal['prixTotal'] = 0;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Boutique</title>
    <link rel="stylesheet" href="css/panier.css">
    <link rel="icon" type="favicon" href="favicon.ico" />
</head>

<body>

    <?php include "header.php"; ?>
    <main class="shop-page" style="max-height:700px;">

        <div class="shop-container">

            <section class="products-grid" 
            style="display: flex; 
                    flex-direction: column; 
                    overflow: scroll; 
                    height: min-content;
                     width: min-content;">


                    <?php foreach ($produits as $produit): ?>

                        <div class="product-card"  ">

                            <div class="product-image-panier">

                                <img src="images/<?= htmlspecialchars($produit['photo']) ?>" alt="">

                            </div>

                            <h3><?= htmlspecialchars($produit['nom']) ?></h3>

                            <p class="price">
                                <?=number_format($produit['prix']*$produit['quantitePanier'],2)?> 
                                
                                (<?= number_format($produit['prix'], 2) ?>/u)

                            </p>

                            <p class="number">
                                <button>+</button>
                                <?=number_format($produit['quantitePanier'])?>
                                <button>-</button>
                            </p>

                        </div>

                    <?php endforeach; ?>
                  

            </section>
              <aside style="background-color: white; height: 64px; width: 64px; ">
                        <p style=" color: black; font-family: Goudy Old Style, serif;"> Prix total:<?=number_format($prixTotal['prixTotal'])?> </p>
                        <form action="panier.php" method="POST">
                            <button type="submit" class="connect">Payer Votre Panier</button>
                        </form>
                        
                            <?php include("scripts/php/payerPanier.php") ?>
                    </aside>

        </div>

    </main>

</body>

</html>