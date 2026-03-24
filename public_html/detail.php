<?php
require_once "init.php";

/* Vérifier ID */
if (!isset($_GET["id"])) {
    header("Location: boutique.php");
    exit;
}

$idItem = (int)$_GET["id"];
$produit = null;
$type = "";

/* ===== ARMURES ===== */
$sql = "SELECT * FROM vDetailArmures WHERE idItem = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idItem]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if ($produit) {
    $type = "armure";
}

/* ===== ARMES ===== */
if (!$produit) {
    $sql = "SELECT * FROM vDetailArmes WHERE idItem = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idItem]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produit) {
        $type = "arme";
    }
}

/* ===== POTIONS ===== */
if (!$produit) {
    $sql = "SELECT * FROM vDetailPotions WHERE idItem = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idItem]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produit) {
        $type = "potion";
    }
}

/* ===== SORTS ===== */
if (!$produit) {
    $sql = "SELECT * FROM vDetailSorts WHERE idItem = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idItem]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produit) {
        $type = "sort";
    }
}

/* Si rien trouvé */
if (!$produit) {
    echo "Produit introuvable";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail produit</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="shop-page">

<?php include "header.php"; ?>

<div class="shop-container">

    <div class="product-card" style="max-width:600px;margin:auto">

        <div class="product-image">
            <img src="images/<?= htmlspecialchars($produit['photo']) ?>" alt="">
        </div>

        <h2><?= htmlspecialchars($produit['nom']) ?></h2>

        <p class="price"><?= $produit['prix'] ?> $</p>
        <p class="stock">Stock : <?= $produit['quantiteStock'] ?></p>

        <hr style="margin:15px 0">

        <!-- ===== DETAILS SELON TYPE ===== -->

        <?php if ($type === "armure"): ?>
            <p><strong>Matière :</strong> <?= $produit['matiere'] ?></p>
            <p><strong>Taille :</strong> <?= $produit['taille'] ?></p>

        <?php elseif ($type === "arme"): ?>
            <p><strong>Efficacité :</strong> <?= $produit['efficacite'] ?></p>
            <p><strong>Genre :</strong> <?= $produit['genre'] ?></p>
            <p><strong>Description :</strong> <?= $produit['description'] ?></p>

        <?php elseif ($type === "potion"): ?>
            <p><strong>Effet :</strong> <?= $produit['effet'] ?></p>
            <p><strong>Durée :</strong> <?= $produit['duree'] ?></p>

        <?php elseif ($type === "sort"): ?>
            <p><strong>Rareté :</strong> <?= $produit['rarete'] ?></p>
            <p><strong>Instantané :</strong> <?= $produit['estInstantane'] ? "Oui" : "Non" ?></p>
            <p><strong>Type :</strong> <?= $produit['typeSort'] ?></p>
            <p><strong>Description :</strong> <?= $produit['description'] ?></p>
            <p><strong>Vie :</strong> <?= $produit['pVie'] ?></p>
            <p><strong>Dégâts :</strong> <?= $produit['pDegat'] ?></p>
        <?php endif; ?>

        <hr style="margin:15px 0">

        <!-- ACTION -->
        <?php if ($produit['quantiteStock'] > 0): ?>
            <a href="scripts/php/ajouterPanier.php?idItem=<?= $idItem ?>" class="add-link">
                Ajouter au panier
            </a>
        <?php else: ?>
            <p style="color:red">Rupture de stock</p>
        <?php endif; ?>

    </div>

</div>

</body>
</html>