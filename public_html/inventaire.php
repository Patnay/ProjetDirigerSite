<?php
session_start();
require_once("scripts/php/bd/connectionBd.php");

/* =========================
   PROTÉGER LA PAGE
========================= */
if (empty($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];

/* =========================
   RÉCUPÉRER INVENTAIRE
========================= */
$sql = "
SELECT 
    Items.idItem,
    Items.nom,
    Items.prix,
    Items.photo,
    Items.quantiteStock,
    Inventaires.quantiteInventaire
FROM Inventaires
INNER JOIN Items ON Inventaires.idItem = Items.idItem
WHERE Inventaires.idJoueur = :idJoueur
ORDER BY Items.nom ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([":idJoueur" => $idJoueur]);
$inventaire = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inventaire</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="favicon.ico">
</head>
<body>

<?php include "header.php"; ?>

<main class="shop-page">
    <div class="shop-container">

        <aside class="filters">
            <h2>Inventaire</h2>

            <div class="filter-block">
                <p>
                    Joueur :
                    <strong><?= htmlspecialchars($_SESSION["alias"] ?? "") ?></strong>
                </p>
            </div>

            <div class="filter-block">
                <a href="boutique.php" class="reset-btn">Retour boutique</a>
            </div>
        </aside>

        <section class="products-grid">
            <?php if (!empty($inventaire)): ?>

                <?php foreach ($inventaire as $item): ?>
                    <div class="product-card">

                        <div class="product-image">
                            <img src="images/<?= htmlspecialchars($item['photo']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>">
                        </div>

                        <h3><?= htmlspecialchars($item['nom']) ?></h3>

                        <p class="price">
                            <?= number_format((float)$item['prix'], 2) ?>
                        </p>

                        <p class="stock">
                            Quantité inventaire : <?= (int)$item['quantiteInventaire'] ?>
                        </p>

                        <p class="stock">
                            Stock boutique : <?= (int)$item['quantiteStock'] ?>
                        </p>

                        <div class="product-actions">
                            <a href="detail.php?id=<?= $item['idItem'] ?>">Detail</a>
                        </div>

                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <p class="no-product">Votre inventaire est vide.</p>
            <?php endif; ?>
        </section>

    </div>
</main>

</body>
</html>