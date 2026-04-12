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

                            <div>
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

                                <div class="number" style="margin-bottom:12px;">
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

                                <div class="product-actions">
                                    <a href="detail.php?id=<?= (int)$produit['idItem'] ?>">Detail</a>

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

                    <form id="payerPanierForm" style="margin-bottom:12px;">
                        <button type="submit" class="connect">Payer votre panier</button>
                    </form>

                    <form action="scripts/php/viderPanier.php" method="POST">
                        <!-- AJOUT : bouton devient type="button" -->
                        <button type="button" id="viderPanierBtn" class="remove-item-btn" style="width:100%;">Vider le panier</button>
                    </form>
                </aside>

            </div>

        <?php endif; ?>

    </div>
</main>

<!-- L'alert pour vider tout le panier -->
<div id="viderPanierAlert" style="
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
        width:320px;
        color:white;
        font-family: 'Agmena Pro', serif;
    ">
        <h2 style="margin-bottom:15px; color:#d4af37;">Confirmation</h2>
        <p style="margin-bottom:25px;">
            Voulez-vous vraiment vider tout votre panier ?
        </p>

        <button onclick="document.getElementById('viderPanierAlert').style.display='none'"
            style="
                padding:10px 20px;
                margin-right:10px;
                background:#444;
                color:white;
                border:none;
                border-radius:8px;
                cursor:pointer;
            ">
            Annuler
        </button>

        <button id="confirmerViderPanier"
            style="
                padding:10px 20px;
                background:#d9534f;
                color:white;
                border:none;
                border-radius:8px;
                cursor:pointer;
            ">
            Vider le panier
        </button>
    </div>
</div>

<!-- Pour l'alert de suprrimer un item dans panier -->
<div id="supprimerItemAlert" style="
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
        width:320px;
        color:white;
        font-family: 'Agmena Pro', serif;
    ">
        <h2 style="margin-bottom:15px; color:#d4af37;">Confirmation</h2>
        <p style="margin-bottom:25px;">
            Voulez-vous vraiment supprimer cet item ?
        </p>

        <button onclick="document.getElementById('supprimerItemAlert').style.display='none'"
            style="
                padding:10px 20px;
                margin-right:10px;
                background:#444;
                color:white;
                border:none;
                border-radius:8px;
                cursor:pointer;
            ">
            Annuler
        </button>

        <button id="confirmerSupprimerItem"
            style="
                padding:10px 20px;
                background:#d9534f;
                color:white;
                border:none;
                border-radius:8px;
                cursor:pointer;
            ">
            Supprimer
        </button>
    </div>
</div>

<!-- Et celui l'a c'est pour la quantité (bon quand même un peu évident non?) -->
<div id="stockAlert" style="
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
        width:320px;
        color:white;
        font-family: 'Agmena Pro', serif;
    ">
        <h2 style="margin-bottom:15px; color:#d4af37;">Attention</h2>
        <p style="margin-bottom:25px;">
            La quantité demandée dépasse le stock disponible.
        </p>

        <button onclick="document.getElementById('stockAlert').style.display='none'"
            style="
                padding:10px 20px;
                background:#444;
                color:white;
                border:none;
                border-radius:8px;
                cursor:pointer;
            ">
            Fermer
        </button>
    </div>
</div>

<!-- Ici c,est lui pour les fonds insuffisants -->
<div id="fondsAlert" style="
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
        width:320px;
        color:white;
        font-family: 'Agmena Pro', serif;
    ">
        <h2 style="margin-bottom:15px; color:#d4af37;">Fonds insuffisants</h2>
        <p style="margin-bottom:25px;">
            Vous n’avez pas assez d'or pour effectuer cet achat.
        </p>

        <button onclick="document.getElementById('fondsAlert').style.display='none'"
            style="
                padding:10px 20px;
                background:#444;
                color:white;
                border:none;
                border-radius:8px;
                cursor:pointer;
            ">
            Fermer
        </button>
    </div>
</div>

<!-- Pour la réussite d'un achat -->
 <div id="achatReussiAlert" style="
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
        width:320px;
        color:white;
        font-family: 'Agmena Pro', serif;
    ">
        <h2 style="margin-bottom:15px; color:#d4af37;">Achat réussi</h2>
        <p style="margin-bottom:25px;">
            Vos items ont été ajoutés à votre inventaire.
        </p>

        <button onclick="location.reload()"
            style="
                padding:10px 20px;
                background:gold;
                color:black;
                border:none;
                border-radius:8px;
                cursor:pointer;
                font-weight:bold;
            ">
            Continuer
        </button>
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

<script>
document.getElementById("viderPanierBtn").addEventListener("click", function () {
    document.getElementById("viderPanierAlert").style.display = "flex";
});

document.getElementById("confirmerViderPanier").addEventListener("click", function () {
    document.querySelector("form[action='scripts/php/viderPanier.php']").submit();
});
</script>

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
            afficherStockInsuffisant();
        }
    }

    const totalElt = document.getElementById("prix-total");
    if (totalElt) {
        totalElt.textContent = data.total;
    }

    // Mise à jour du compteur dans le header
fetch("scripts/php/getPanierCount.php")
    .then(res => res.json())
    .then(obj => {
        const count = obj.count;
        const badge = document.getElementById("cart-count");

        if (!badge) return;

        if (count <= 0) {
            badge.style.display = "none";
        } else {
            badge.style.display = "inline-block";
            badge.textContent = (count > 99 ? "99+" : count);
        }
    });
}
</script>

<script>
let itemASupprimer = null;

function supprimerItem(idItem) {
    itemASupprimer = idItem;
    document.getElementById("supprimerItemAlert").style.display = "flex";
}

document.getElementById("confirmerSupprimerItem").addEventListener("click", function () {
    if (!itemASupprimer) return;

    fetch("scripts/php/supprimerItemPanier.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "idItem=" + encodeURIComponent(itemASupprimer)
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("supprimerItemAlert").style.display = "none";

        if (data.erreur) {
            document.getElementById("stockAlert").style.display = "flex";
            return;
        }

        const card = document.getElementById("card-" + itemASupprimer);
        if (card) card.remove();

        const totalElt = document.getElementById("prix-total");
        if (totalElt) totalElt.textContent = data.total;

        location.reload();        
    });

    itemASupprimer = null;
});

function afficherStockInsuffisant() {
    document.getElementById("stockAlert").style.display = "flex";
}
</script>

<script>
document.getElementById("payerPanierForm").addEventListener("submit", function(e) {
    e.preventDefault();

    fetch("scripts/php/payerPanier.php", {
        method: "POST"
    })
    .then(res => res.text())
    .then(data => {

        if (data.includes("FONDS_INSUFFISANTS")) {
            document.getElementById("fondsAlert").style.display = "flex";
            return;
        }

        if (data.includes("DEPASSE_STOCK")) {
            document.getElementById("stockAlert").style.display = "flex";
            return;
        }

        if (data.includes("PANIER_VIDE")) {
            alert("Votre panier est vide.");
            return;
        }

        if (data.includes("OK")) {
            document.getElementById("achatReussiAlert").style.display = "flex";
            return;
        }
    });
});
</script>

</body>
</html>
