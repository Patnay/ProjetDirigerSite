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
    Items.typeItem,
    Inventaires.quantiteInventaire,
    Potions.effet
FROM Inventaires
INNER JOIN Items ON Inventaires.idItem = Items.idItem
LEFT JOIN Potions ON Items.idItem = Potions.idItem
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
    <link rel="icon" type="image/png" href="favicon.png">
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

                        <div class="product-actions" style="flex-wrap:wrap; gap:10px;">
    <a href="detail.php?id=<?= (int)$item['idItem'] ?>">Detail</a>

    <a href="vendre.php?id=<?= (int)$item['idItem'] ?>" class="add-link">
        Vendre
    </a>

    <?php if (!empty($item["effet"])): ?>
        <a href="scripts/php/utiliserPotion.php?idItem=<?= (int)$item['idItem'] ?>" class="add-link">
            Utiliser
        </a>
    <?php endif; ?>

    <?php if (($item["typeItem"] ?? "") === "S"): ?>
        <a href="scripts/php/utiliserSort.php?idItem=<?= (int)$item['idItem'] ?>" class="add-link">
            Utiliser
        </a>
    <?php endif; ?>
</div>

                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <p class="no-product">Votre inventaire est vide.</p>
            <?php endif; ?>
        </section>

    </div>
</main>
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
    <source src="musique/honor.mp3" type="audio/mp3">
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
<?php if (!empty($_SESSION["sort_popup"])): ?>
<script>
    alert(<?= json_encode($_SESSION["sort_popup"]) ?>);
</script>
<?php unset($_SESSION["sort_popup"]); ?>
<?php endif; ?>
</body>
</html>