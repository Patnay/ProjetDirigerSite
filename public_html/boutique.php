<?php
session_start();
require_once("scripts/php/bd/connectionBd.php");

/* =========================
   RÉCUPÉRATION DES FILTRES
========================= */

$prixMin = isset($_GET["prixMin"]) && $_GET["prixMin"] !== ""
    ? (float) $_GET["prixMin"]
    : null;

$prixMax = isset($_GET["prixMax"]) && $_GET["prixMax"] !== ""
    ? (float) $_GET["prixMax"]
    : null;

$etoileMin = isset($_GET["etoileMin"]) && $_GET["etoileMin"] !== ""
    ? (float) $_GET["etoileMin"]
    : null;

$etoileMax = isset($_GET["etoileMax"]) && $_GET["etoileMax"] !== ""
    ? (float) $_GET["etoileMax"]
    : null;

/* =========================
   CATÉGORIES
========================= */

$categories = $_GET["categories"] ?? [];
if (!is_array($categories)) {
    $categories = [];
}

$categoriesValides = ["Armures", "Armes", "Sorts", "Potions"];
$categories = array_values(array_intersect($categories, $categoriesValides));

/* =========================
   SAVOIR SI UN FILTRE EST ACTIF
========================= */

$filtreActif = (
    $prixMin !== null ||
    $prixMax !== null ||
    $etoileMin !== null ||
    $etoileMax !== null ||
    !empty($categories)
);

/* =========================
   REQUÊTE SQL
========================= */

$sql = "
SELECT 
    Items.idItem,
    Items.nom,
    Items.prix,
    Items.photo,
    Items.quantiteStock,
    IFNULL(AVG(Evaluations.nbEtoiles),0) AS etoile
FROM Items
LEFT JOIN Evaluations 
    ON Items.idItem = Evaluations.idItem
WHERE 1=1
";

$params = [];

/* =========================
   FILTRES PRIX
========================= */

if ($prixMin !== null) {
    $sql .= " AND Items.prix >= :prixMin";
    $params[":prixMin"] = $prixMin;
}

if ($prixMax !== null) {
    $sql .= " AND Items.prix <= :prixMax";
    $params[":prixMax"] = $prixMax;
}

/* =========================
   FILTRE CATÉGORIES MULTIPLES
========================= */

if (!empty($categories)) {
    $conditionsCategories = [];

    if (in_array("Armures", $categories, true)) {
        $conditionsCategories[] = "EXISTS (
            SELECT 1 FROM Armures WHERE Armures.idItem = Items.idItem
        )";
    }

    if (in_array("Armes", $categories, true)) {
        $conditionsCategories[] = "EXISTS (
            SELECT 1 FROM Armes WHERE Armes.idItem = Items.idItem
        )";
    }

    if (in_array("Sorts", $categories, true)) {
        $conditionsCategories[] = "EXISTS (
            SELECT 1 FROM Sorts WHERE Sorts.idItem = Items.idItem
        )";
    }

    if (in_array("Potions", $categories, true)) {
        $conditionsCategories[] = "EXISTS (
            SELECT 1 FROM Potions WHERE Potions.idItem = Items.idItem
        )";
    }

    if (!empty($conditionsCategories)) {
        $sql .= " AND (" . implode(" OR ", $conditionsCategories) . ")";
    }
}

/* =========================
   GROUP BY
========================= */

$sql .= "
GROUP BY 
    Items.idItem,
    Items.nom,
    Items.prix,
    Items.photo,
    Items.quantiteStock
";

/* =========================
   FILTRES ÉTOILES
========================= */

$having = [];

if ($etoileMin !== null) {
    $having[] = "IFNULL(AVG(Evaluations.nbEtoiles),0) >= :etoileMin";
    $params[":etoileMin"] = $etoileMin;
}

if ($etoileMax !== null) {
    $having[] = "IFNULL(AVG(Evaluations.nbEtoiles),0) <= :etoileMax";
    $params[":etoileMax"] = $etoileMax;
}

if (!empty($having)) {
    $sql .= " HAVING " . implode(" AND ", $having);
}

/* =========================
   TRI
========================= */

$sql .= " ORDER BY Items.nom";

/* =========================
   LIMITE SI AUCUN FILTRE
========================= */

if (!$filtreActif) {
    $sql .= " LIMIT 12";
}

/* =========================
   EXÉCUTION
========================= */

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <main class="shop-page">

        <div class="shop-container">

            <!-- =========================
                 FILTRES
            ========================= -->

            <aside class="filters">

                <h2>Filtrer la recherche</h2>

                <form method="get" action="boutique.php">

                    <div class="filter-block">
                        <label>Catégories</label>

                        <div>
                            <label>
                                <input type="checkbox" name="categories[]" value="Armures"
                                    <?= in_array("Armures", $categories, true) ? "checked" : "" ?>>
                                Armures
                            </label>
                        </div>

                        <div>
                            <label>
                                <input type="checkbox" name="categories[]" value="Armes"
                                    <?= in_array("Armes", $categories, true) ? "checked" : "" ?>>
                                Armes
                            </label>
                        </div>

                        <div>
                            <label>
                                <input type="checkbox" name="categories[]" value="Sorts"
                                    <?= in_array("Sorts", $categories, true) ? "checked" : "" ?>>
                                Sorts
                            </label>
                        </div>

                        <div>
                            <label>
                                <input type="checkbox" name="categories[]" value="Potions"
                                    <?= in_array("Potions", $categories, true) ? "checked" : "" ?>>
                                Potions
                            </label>
                        </div>
                    </div>

                    <div class="filter-block">
                        <label>Prix minimum</label>
                        <input type="number" step="0.01" name="prixMin" placeholder="Min"
                            value="<?= htmlspecialchars($_GET['prixMin'] ?? '') ?>">
                    </div>

                    <div class="filter-block">
                        <label>Prix maximum</label>
                        <input type="number" step="0.01" name="prixMax" placeholder="Max"
                            value="<?= htmlspecialchars($_GET['prixMax'] ?? '') ?>">
                    </div>

                    <div class="filter-block">
                        <label>Étoiles minimum</label>
                        <input type="number" step="0.1" min="0" max="5" name="etoileMin" placeholder="Min"
                            value="<?= htmlspecialchars($_GET['etoileMin'] ?? '') ?>">
                    </div>

                    <div class="filter-block">
                        <label>Étoiles maximum</label>
                        <input type="number" step="0.1" min="0" max="5" name="etoileMax" placeholder="Max"
                            value="<?= htmlspecialchars($_GET['etoileMax'] ?? '') ?>">
                    </div>

                    <div class="filter-buttons">

                        <button class="filter-btn" type="submit">
                            Filtrer
                        </button>

                        <a class="reset-btn" href="boutique.php">
                            Réinitialiser
                        </a>

                    </div>

                </form>

            </aside>

            <!-- =========================
                 PRODUITS
            ========================= -->

            <section class="products-grid">

                <?php if (count($produits) > 0): ?>

                    <?php foreach ($produits as $produit): ?>

                        <div class="product-card">

                            <div class="product-image">
                                <img src="images/<?= htmlspecialchars($produit['photo']) ?>" alt="">
                            </div>

                            <h3><?= htmlspecialchars($produit['nom']) ?></h3>

                            <p class="price">
                                <?= number_format($produit['prix'], 2) ?>
                            </p>

                            <p class="stars">
                                ⭐ <?= number_format($produit['etoile'], 1) ?> / 5
                            </p>

                            <p class="stock">
                                <?php if ($produit['quantiteStock'] > 0): ?>
                                    Stock : <?= $produit['quantiteStock'] ?>
                                <?php else: ?>
                                    Rupture de stock
                                <?php endif; ?>
                            </p>

                            <div class="product-actions">

                                <a href="detail.php?id=<?= $produit['idItem'] ?>">
                                    Detail
                                </a>

                                <?php if ($produit['quantiteStock'] > 0): ?>
                                    <a class="add-link" href="scripts/php/ajouterPanier.php?id=<?= $produit['idItem'] ?>">
                                        Ajouter
                                    </a>
                                <?php else: ?>
                                    <span class="add-link" style="opacity:0.5">
                                        Ajouter
                                    </span>
                                <?php endif; ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p class="no-product">
                        Aucun produit trouvé avec ces filtres.
                    </p>

                <?php endif; ?>

            </section>

        </div>

    </main>

</body>

</html>