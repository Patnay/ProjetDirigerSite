<?php
require_once "init.php";
$isMage = false;

if (isset($_SESSION["idJoueur"])) {
    $sqlMage = "SELECT nbEnigmeMage FROM Joueurs WHERE idJoueur = ?";
    $stmtMage = $pdo->prepare($sqlMage);
    $stmtMage->execute([$_SESSION["idJoueur"]]);
    $mageData = $stmtMage->fetch(PDO::FETCH_ASSOC);

    if ($mageData && $mageData["nbEnigmeMage"] >= 3) {
        $isMage = true;
    }
}


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
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="css/styles.css">
</head>
<?php include "header.php"; ?>

<body>
<main class="shop-page">
<div class="shop-container">

    <div class="product-card" style="max-width:600px;margin:auto">

        <div class="product-image">
            <img src="images/<?= htmlspecialchars($produit['photo']) ?>" alt="">
        </div>

        <h2><?= htmlspecialchars($produit['nom']) ?></h2>

        <p class="price"><?= $produit['prix'] ?> 🪙</p>
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
            <a class="add-link add-to-cart-btn"
                href="#"
                data-id="<?= $produit['idItem'] ?>"
                data-type="<?= $type ?>"
                data-ismage="<?= $isMage ? '1' : '0' ?>">
                Ajouter au panier
            </a>

        <?php else: ?>
            <p style="color:red">Rupture de stock</p>
        <?php endif; ?>

    </div>

</div>
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
    <source src="musique/academy.mp3" type="audio/mp3">
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

        <!-- Celui pour le criss de message ... zzzz -->
<script>
document.querySelector(".add-to-cart-btn")?.addEventListener("click", function(e) {
    e.preventDefault();

    const isMage = this.dataset.ismage === "1";
    const type = this.dataset.type;
    const id = this.dataset.id;

    // Si c'est un sort et que le joueur n'est pas mage → popup Elden Ring
    if (type === "sort" && !isMage) {
        document.getElementById("sortMageAlert").style.display = "flex";
        return;
    }

    // Sinon → ajouter au panier normalement
    window.location.href = "scripts/php/ajouterPanier.php?id=" + id;
});
</script>

</main>
        <!-- Pour le message de non-mage RAAAAAAAAAAAAAH BIENTOT DORMIR MIMIMIMERNWNRKWQDEBEJWNDQKBEWQNRFEWQFKEWJDQWDBVFEKWJDQWBDJK-->
<div id="sortMageAlert" style="
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.75);
    backdrop-filter: blur(4px);
    justify-content:center;
    align-items:center;
    z-index:9999;
">
    <div style="
        background:#1a1a1a;
        border:2px solid gold;
        padding:30px;
        border-radius:12px;
        text-align:center;
        width:360px;
        color:white;
        font-family: 'Agmena Pro', serif;
    ">
        <h2 style="margin-bottom:15px; color:#d4af37;">Attention</h2>

        <p style="margin-bottom:25px;">
            Seuls les mages peuvent acheter des sorts.<br>
            Répondez à des énigmes de type mage pour le devenir.
        </p>

        <button onclick="document.getElementById('sortMageAlert').style.display='none'"
            style="
                padding:10px 20px;
                margin-right:10px;
                background:#444;
                color:white;
                border:none;
                border-radius:8px;
                cursor:pointer;
            ">
            Fermer
        </button>
        </button>
    </div>
</div>
</body>
</html>
