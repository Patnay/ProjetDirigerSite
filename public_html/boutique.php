<?php
try {
    ini_set('display_errors', 'Off');
    ini_set('log_errors', 'On');
    error_reporting(E_ALL);
    session_start();
} catch (Exception) {

}
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
   PAGINATION
========================= */

$itemsParPage = 12;
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) && $_GET["page"] > 0
    ? (int)$_GET["page"]
    : 1;

$offset = ($page - 1) * $itemsParPage;

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
   PAGINATION SEULEMENT SI AUCUN FILTRE
========================= */

$totalPages = 1;

if (!$filtreActif) {
    $sqlCount = "SELECT COUNT(*) FROM Items";
    $stmtCount = $pdo->query($sqlCount);
    $totalItems = (int)$stmtCount->fetchColumn();
    $totalPages = max(1, ceil($totalItems / $itemsParPage));

    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $itemsParPage;
    }

    $sql .= " LIMIT $itemsParPage OFFSET $offset";
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
  <link rel="icon" type="image/png" href="favicon.png">
</head>
<script src="https://cdn.jsdelivr.net/npm/animejs/dist/bundles/anime.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    if (typeof anime === "undefined") {
        console.error("Anime.js non chargé");
        return;
    }

    const { animate } = anime;
    const cartIcon = document.getElementById("cart-icon");
    const buttons = document.querySelectorAll(".add-to-cart-btn");

    if (!cartIcon) {
        console.error("Icône panier introuvable");
        return;
    }

    if (!buttons.length) {
        console.error("Aucun bouton .add-to-cart-btn trouvé");
        return;
    }

    buttons.forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            e.preventDefault();

            const card = btn.closest(".product-card");
            const img = card ? card.querySelector(".product-image img") : null;

            if (!img) {
                window.location.href = btn.href;
                return;
            }

            const imgRect = img.getBoundingClientRect();
            const cartRect = cartIcon.getBoundingClientRect();

            const clone = document.createElement("div");
            clone.className = "fly-cart-clone";
            clone.style.left = imgRect.left + "px";
            clone.style.top = imgRect.top + "px";
            clone.style.width = imgRect.width + "px";
            clone.style.height = imgRect.height + "px";

            const cloneImg = document.createElement("img");
            cloneImg.src = img.src;
            cloneImg.alt = img.alt || "";
            clone.appendChild(cloneImg);

            document.body.appendChild(clone);

            animate(clone, {
                left: cartRect.left + "px",
                top: cartRect.top + "px",
                width: "30px",
                height: "30px",
                opacity: [1, 0.3],
                scale: [1, 0.4],
                rotate: "1turn",
                duration: 800,
                ease: "out(3)",
                onComplete: function () {
                    clone.remove();

                    animate(cartIcon, {
                        scale: [1, 1.2, 1],
                        duration: 250,
                        ease: "out(3)",
                        onComplete: function () {
                            window.location.href = btn.href;
                        }
                    });
                }
            });
        });
    });
});
</script>
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
                                <?= number_format($produit['prix'], 2) ?> 🪙
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
                                    <a class="add-link add-to-cart-btn"
                                href="scripts/php/ajouterPanier.php?id=<?= $produit['idItem'] ?>"
                                data-id="<?= $produit['idItem'] ?>">
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
           <?php if (!$filtreActif && $totalPages > 1): ?>
        <div class="pagination">

        <?php if ($page > 1): ?>
            <a class="page-arrow" href="boutique.php?page=<?= $page - 1 ?>">←</a>
        <?php else: ?>
            <span class="page-arrow disabled">←</span>
        <?php endif; ?>

        <span class="page-number">Page <?= $page ?> / <?= $totalPages ?></span>

        <?php if ($page < $totalPages): ?>
            <a class="page-arrow" href="boutique.php?page=<?= $page + 1 ?>">→</a>
        <?php else: ?>
            <span class="page-arrow disabled">→</span>
        <?php endif; ?>

        </div>
        <?php endif; ?> 

<!-- Bouton musique -->
<img id="musicToggle" 
     src="image/sonOff.jpg" 
     style="
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
        cursor: pointer;
        z-index: 9999;
     ">
<audio id="bgMusic" loop>
    <source src="musique/roundtableHold.mp3" type="audio/mp3">
</audio>
<script>
const music = document.getElementById("bgMusic");
const toggleBtn = document.getElementById("musicToggle");

let musicOn = false;

toggleBtn.addEventListener("click", () => {
    musicOn = !musicOn;

    if (musicOn) {
        music.play();
        toggleBtn.src = "image/sonOn.jpg";
    } else {
        music.pause();
        toggleBtn.src = "image/sonOff.jpg";
    }
});
</script>        
    </main>
<svg width="0" height="0" style="position:absolute">
  <defs>
    <filter id="electric-border" x="-20%" y="-20%" width="140%" height="140%">
      <feTurbulence id="turb" type="turbulence" baseFrequency="0.02" numOctaves="3" seed="2" result="noise"/>
      <feDisplacementMap in="SourceGraphic" in2="noise" scale="25" />
    </filter>
  </defs>
</svg>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const turb = document.getElementById("turb");
    let t = 0;

    function animate() {
        t += 0.005;
        const bf = 0.015 + Math.sin(t) * 0.015;
        turb.setAttribute("baseFrequency", bf);
        requestAnimationFrame(animate);
    }

    animate();
});
</script>
</body>

</html>