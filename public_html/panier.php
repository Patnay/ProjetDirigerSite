<?php
require_once "init.php";

if (!isset($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}

$idJoueur = (int)$_SESSION["idJoueur"];

/* Produits du panier */
$sql = "SELECT i.idItem, i.nom, i.prix, i.photo, i.quantiteStock, p.quantitePanier
        FROM Items i
        INNER JOIN Paniers p ON i.idItem = p.idItem
        WHERE p.idJoueur = ?
        ORDER BY i.nom ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$idJoueur]);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Prix total */
$sql = "SELECT SUM(i.prix * p.quantitePanier) AS prixTotal
        FROM Items i
        INNER JOIN Paniers p ON i.idItem = p.idItem
        WHERE p.idJoueur = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$idJoueur]);
$prixTotal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($prixTotal['prixTotal']) || $prixTotal['prixTotal'] === null) {
    $prixTotal['prixTotal'] = 0;
}

$messageAlerte = "";
foreach ($produits as $produit) {
    if ((int)$produit["quantitePanier"] > (int)$produit["quantiteStock"]) {
        $messageAlerte = "Attention : une ou plusieurs quantités dans votre panier dépassent le stock disponible.";
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panier</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/panier.css">
    <link rel="icon" type="image/png" href="favicon.png">
</head>
<body>

<?php include "header.php"; ?>

<main class="shop-page">
    <div class="shop-container cart-page-wrapper">

        <?php if (!empty($messageAlerte)): ?>
            <div class="message-erreur" style="margin-bottom: 15px;">
                <?= htmlspecialchars($messageAlerte) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($produits)): ?>
            <article class="cart-empty">
                <h3>Votre panier est vide</h3>
                <p>Vous pouvez le remplir en allant dans la <a href="boutique.php">Boutique</a>.</p>
            </article>
        <?php else: ?>

            <div class="cart-layout">

                <section class="cart_container">
                    <?php foreach ($produits as $produit): ?>
                        <div class="product-card cart_cards" id="card-<?= (int)$produit['idItem'] ?>">

    <div class="product-image cart_img">
        <img src="images/<?= htmlspecialchars($produit['photo']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
    </div>

    <div class="cart-info">
        <h3><?= htmlspecialchars($produit['nom']) ?></h3>

        <p class="price"
           id="prix-<?= (int)$produit['idItem'] ?>"
           data-prix="<?= htmlspecialchars($produit['prix']) ?>">
            <?= number_format((float)$produit['prix'] * (int)$produit['quantitePanier'], 2) ?>
            (<?= number_format((float)$produit['prix'], 2) ?>/u)
        </p>

        <p class="stock">
            Stock boutique :
            <span id="stock-<?= (int)$produit['idItem'] ?>"><?= (int)$produit['quantiteStock'] ?></span>
        </p>

        <div class="number">
            <button type="button" onclick="modifierQuantite(<?= (int)$produit['idItem'] ?>, 'moins')">-</button>

            <input
                type="number"
                min="1"
                value="<?= (int)$produit['quantitePanier'] ?>"
                id="qte-<?= (int)$produit['idItem'] ?>"
                class="cart-qty-input"
                onchange="modifierQuantiteManuelle(<?= (int)$produit['idItem'] ?>)"
            >

            <button type="button" onclick="modifierQuantite(<?= (int)$produit['idItem'] ?>, 'plus')">+</button>
        </div>

        <div class="cart-actions">
            <a href="detail.php?id=<?= (int)$produit['idItem'] ?>" class="detail-link">Detail</a>

            <button type="button"
                    class="remove-item-btn"
                    onclick="supprimerItem(<?= (int)$produit['idItem'] ?>)">
                Supprimer
            </button>
        </div>
    </div>

</div>
                    <?php endforeach; ?>
                </section>

                <aside class="cart-summary">
                    <h2>Résumé</h2>

                    <p class="cart-total-label">Prix total</p>
                    <p class="cart-total-value">
                        <span id="prix-total"><?= number_format((float)$prixTotal['prixTotal'], 2) ?></span>
                    </p>

                    <form action="scripts/php/payerPanier.php" method="POST" style="margin-bottom:12px;">
                        <button type="submit" class="connect">Payer votre panier</button>
                    </form>

                    <form action="scripts/php/viderPanier.php" method="POST">
                        <button type="submit" class="remove-item-btn" style="width:100%;">Vider le panier</button>
                    </form>
                </aside>

            </div>

        <?php endif; ?>

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
    <source src="musique/limgrave.mp3" type="audio/mp3">
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

<script>
function modifierQuantite(idItem, action) {
    fetch("scripts/php/updatePanier.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "idItem=" + encodeURIComponent(idItem) + "&action=" + encodeURIComponent(action)
    })
    .then(res => res.json())
    .then(data => {
        if (data.erreur) {
            alert(data.erreur);
            return;
        }
        majCarte(idItem, data);
    });
}

function modifierQuantiteManuelle(idItem) {
    const input = document.getElementById("qte-" + idItem);
    let quantite = parseInt(input.value, 10);

    if (isNaN(quantite) || quantite < 1) {
        quantite = 1;
        input.value = 1;
    }

    fetch("scripts/php/updatePanier.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "idItem=" + encodeURIComponent(idItem) + "&quantite=" + encodeURIComponent(quantite)
    })
    .then(res => res.json())
    .then(data => {
        if (data.erreur) {
            alert(data.erreur);
            return;
        }
        majCarte(idItem, data);
    });
}

function supprimerItem(idItem) {
    if (!confirm("Supprimer cet item du panier ?")) return;

    fetch("scripts/php/supprimerItemPanier.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "idItem=" + encodeURIComponent(idItem)
    })
    .then(res => res.json())
    .then(data => {
        if (data.erreur) {
            alert(data.erreur);
            return;
        }

        const card = document.getElementById("card-" + idItem);
        if (card) card.remove();

        const totalElt = document.getElementById("prix-total");
        if (totalElt) totalElt.textContent = data.total;

        if (data.panierVide) {
            location.reload();
        }
    });
}

function majCarte(idItem, data) {
    if (data.quantite <= 0) {
        const card = document.getElementById("card-" + idItem);
        if (card) card.remove();
    } else {
        const qte = document.getElementById("qte-" + idItem);
        if (qte) qte.value = data.quantite;

        const prixElt = document.getElementById("prix-" + idItem);
        if (prixElt) {
            const prixUnitaire = parseFloat(prixElt.dataset.prix);
            prixElt.textContent =
                (prixUnitaire * data.quantite).toFixed(2) +
                " (" + prixUnitaire.toFixed(2) + "/u)";
        }

        if (data.depasseStock) {
            alert("La quantité demandée dépasse le stock disponible en boutique.");
        }
    }

    const totalElt = document.getElementById("prix-total");
    if (totalElt) {
        totalElt.textContent = data.total;
    }
}
</script>

</body>
</html>