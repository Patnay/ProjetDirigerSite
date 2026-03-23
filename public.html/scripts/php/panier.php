<?php
session_start();
if (!isset($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}
require_once("scripts/php/bd/connectionBd.php");

$sql = "SELECT i.idItem, i.nom, i.prix, i.photo, p.quantitePanier
        FROM Items i 
        INNER JOIN Paniers p ON i.idItem = p.idItem 
        WHERE p.idJoueur = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["idJoueur"]]);

$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT SUM(i.prix*p.quantitePanier) as prixTotal
        FROM Items i 
        INNER JOIN Paniers p ON i.idItem = p.idItem 
        WHERE p.idJoueur = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["idJoueur"]]);
$prixTotal = $stmt->fetch(PDO::FETCH_ASSOC);

if ($prixTotal['prixTotal'] === null) 
    $prixTotal['prixTotal'] = 0;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Boutique</title>
    <link rel="stylesheet" href="css/styles.css">
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

                    <div class="product-card" id="card-<?= $produit['idItem'] ?>">

                        <div class="product-image">
                            <img src="images/<?= htmlspecialchars($produit['photo']) ?>" alt="">
                        </div>

                        <h3><?= htmlspecialchars($produit['nom']) ?></h3>

                        <p class="price" 
                           id="prix-<?= $produit['idItem'] ?>" 
                           data-prix="<?= $produit['prix'] ?>">
                            <?= number_format($produit['prix'] * $produit['quantitePanier'], 2) ?>
                            (<?= number_format($produit['prix'], 2) ?>/u)
                        </p>

                        <p class="number">
                            <button onclick="modifierQuantite(<?= $produit['idItem'] ?>, 'plus')">+</button>
                            <span id="qte-<?= $produit['idItem'] ?>">
                                <?= $produit['quantitePanier'] ?>
                            </span>
                            <button onclick="modifierQuantite(<?= $produit['idItem'] ?>, 'moins')">-</button>
                        </p>

                    </div>

                <?php endforeach; ?>

            </section>

            <aside style="background-color: white; height: 64px; width: 64px;">
                <p style="color: black; font-family: Goudy Old Style, serif;">
                    Prix total: 
                    <span id="prix-total"><?= number_format($prixTotal['prixTotal']) ?></span>
                </p>

                <form action="panier.php" method="POST">
                    <button type="submit" class="connect">Payer Votre Panier</button>
                </form>

                <?php include("scripts/php/payerPanier.php") ?>
            </aside>

        </div>

    </main>

</body>

</html>

<script>
function modifierQuantite(idItem, action) {
    fetch("updatePanier.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "idItem=" + idItem + "&action=" + action
    })
    .then(res => res.json())
    .then(data => {

        if (data.quantite <= 0) {
            document.getElementById("card-" + idItem).remove();
        } else {
            document.getElementById("qte-" + idItem).textContent = data.quantite;

            const prixUnitaire = parseFloat(
                document.getElementById("prix-" + idItem).dataset.prix
            );

            document.getElementById("prix-" + idItem).textContent =
                (prixUnitaire * data.quantite).toFixed(2) +
                " (" + prixUnitaire.toFixed(2) + "/u)";
        }

        document.getElementById("prix-total").textContent = data.total;
    });
}
</script>
