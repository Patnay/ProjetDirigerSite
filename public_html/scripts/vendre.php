<?php
require_once "init.php";

if (empty($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];
$idItem = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($idItem <= 0) {
    header("Location: inventaire.php");
    exit;
}

$sql = "
SELECT 
    Items.idItem,
    Items.nom,
    Items.prix,
    Items.photo,
    Items.typeItem,
    Items.quantiteStock,
    Inventaires.quantiteInventaire,
    Sorts.rarete
FROM Inventaires
INNER JOIN Items ON Inventaires.idItem = Items.idItem
LEFT JOIN Sorts ON Items.idItem = Sorts.idItem
WHERE Inventaires.idJoueur = :idJoueur
  AND Inventaires.idItem = :idItem
LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":idJoueur" => $idJoueur,
    ":idItem" => $idItem
]);

$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    $_SESSION["message_erreur"] = "Item introuvable dans votre inventaire.";
    header("Location: inventaire.php");
    exit;
}

/* Flash messages */
$messageInfo = "";
$messageErreur = "";

$messageItemId = isset($_SESSION["message_item_id"]) ? (int)$_SESSION["message_item_id"] : 0;

if ($messageItemId === $idItem) {
    $messageInfo = $_SESSION["message_info"] ?? "";
    $messageErreur = $_SESSION["message_erreur"] ?? "";
}

unset($_SESSION["message_info"], $_SESSION["message_erreur"], $_SESSION["message_item_id"]);

/* Ratio vente */
$ratioVente = 0.60;

if (($item["typeItem"] ?? "") === "S") {
    $rarete = (int)($item["rarete"] ?? 0);

    if ($rarete === 1) {
        $ratioVente = 1.00;
    } elseif ($rarete === 2) {
        $ratioVente = 0.95;
    } elseif ($rarete === 3) {
        $ratioVente = 0.90;
    } else {
        $ratioVente = 0.60;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vendre un item</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/png" href="favicon.png">
</head>
<body>

<?php include "header.php"; ?>

<main class="shop-page">
    <div class="shop-container">

        <aside class="filters">
            <h2>Vendre</h2>

            <div class="filter-block">
                <p><strong>Item :</strong></p>
                <p><?= htmlspecialchars($item["nom"]) ?></p>
            </div>

            <div class="filter-block">
                <p><strong>Possédé :</strong></p>
                <p><?= (int)$item["quantiteInventaire"] ?></p>
            </div>

            <div class="filter-block">
                <p>
                    <strong>Total estimé :</strong><br>
                    <span id="gainTotal"><?= number_format((float)$item["prix"] * $ratioVente, 2) ?></span>
                </p>
            </div>

            <div class="filter-block">
                <a href="inventaire.php" class="reset-btn">Retour inventaire</a>
            </div>
        </aside>

        <section class="products-grid" style="grid-template-columns:1fr; max-width:500px;">
            <div class="product-card">

                <div class="product-image" style="height:220px;">
                    <img src="images/<?= htmlspecialchars($item['photo']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>">
                </div>

                <h3><?= htmlspecialchars($item["nom"]) ?></h3>

                <p class="price">
                    Prix boutique : <?= number_format((float)$item["prix"], 2) ?>
                </p>

                <p class="stock">
                    Quantité inventaire : <?= (int)$item["quantiteInventaire"] ?>
                </p>

                <?php if ($messageInfo !== ""): ?>
                    <p class="message-info"><?= htmlspecialchars($messageInfo) ?></p>
                <?php endif; ?>

                <?php if ($messageErreur !== ""): ?>
                    <p class="message-erreur"><?= htmlspecialchars($messageErreur) ?></p>
                <?php endif; ?>

                <form action="scripts/php/vendreItem.php" method="POST">
                    <input type="hidden" name="idItem" value="<?= (int)$item["idItem"] ?>">

                    <label for="qteVente" style="display:block; margin-bottom:10px; font-weight:bold;">
                        Quantité à vendre
                    </label>

                    <div class="number" style="margin-bottom:18px;">
                        <button type="button" onclick="changerQte(-1)">-</button>

                        <input
                            type="number"
                            id="qteVente"
                            name="qteVente"
                            min="1"
                            max="<?= (int)$item["quantiteInventaire"] ?>"
                            value="1"
                            class="cart-qty-input"
                            required
                        >

                        <button type="button" onclick="changerQte(1)">+</button>
                    </div>

                    <button type="submit" class="filter-btn" style="width:100%;">
                        Vendre
                    </button>
                </form>
            </div>
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
    <source src="musique/bloodyFinger.mp3" type="audio/mp3">
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

<script>
const prixItem = <?= json_encode((float)$item["prix"]) ?>;
const ratioVente = <?= json_encode((float)$ratioVente) ?>;
const inputQte = document.getElementById("qteVente");
const gainTotal = document.getElementById("gainTotal");

/* Mise à jour en direct (sans corriger la valeur) */
function updateGainLive() {
    let qte = parseInt(inputQte.value, 10);
    if (isNaN(qte) || qte < 1) qte = 1;

    const total = prixItem * ratioVente * qte;
    gainTotal.textContent = total.toFixed(2);
}

/* ici le fix haha */
function validateQuantity() {
    let qte = parseInt(inputQte.value, 10);

    if (isNaN(qte) || qte < 1) qte = 1;

    const max = parseInt(inputQte.max, 10);
    if (qte > max) qte = max;

    inputQte.value = qte;

    const total = prixItem * ratioVente * qte;
    gainTotal.textContent = total.toFixed(2);
}

/* Boutons + et - */
function changerQte(delta) {
    let value = parseInt(inputQte.value, 10);
    if (isNaN(value)) value = 1;

    value += delta;

    const min = parseInt(inputQte.min, 10);
    const max = parseInt(inputQte.max, 10);

    if (value < min) value = min;
    if (value > max) value = max;

    inputQte.value = value;
    validateQuantity();
}

/* Événements */
inputQte.addEventListener("input", updateGainLive);
inputQte.addEventListener("change", validateQuantity);

validateQuantity();
</script>

</body>
</html>
