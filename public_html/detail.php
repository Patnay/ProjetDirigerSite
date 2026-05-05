<?php
require_once "init.php";

$isMage   = false;
$isAdmin  = false;

if (isset($_SESSION["idJoueur"])) {
    $sqlMage = "SELECT nbEnigmeMage, estAdmin FROM Joueurs WHERE idJoueur = ?";
    $stmtMage = $pdo->prepare($sqlMage);
    $stmtMage->execute([$_SESSION["idJoueur"]]);
    $mageData = $stmtMage->fetch(PDO::FETCH_ASSOC);

    if ($mageData) {
        if ($mageData["nbEnigmeMage"] >= 3) $isMage  = true;
        if ((int)$mageData["estAdmin"] === 1) $isAdmin = true;
    }
}

if (!isset($_GET["id"])) {
    header("Location: boutique.php");
    exit;
}

$idItem = (int)$_GET["id"];
$produit = null;
$type = "";

$views = [
    "armure" => "vDetailArmures",
    "arme" => "vDetailArmes",
    "potion" => "vDetailPotions",
    "sort" => "vDetailSorts"
];

foreach ($views as $typeTest => $view) {
    $stmt = $pdo->prepare("SELECT * FROM $view WHERE idItem = ?");
    $stmt->execute([$idItem]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produit) {
        $type = $typeTest;
        break;
    }
}

if (!$produit) {
    echo "Produit introuvable";
    exit;
}

/* ===== COMMENTAIRES ===== */
$messageCommentaire = "";
$erreurCommentaire = "";
$possedeItem = false;

if (isset($_SESSION["idJoueur"])) {
    $stmtPossede = $pdo->prepare("
        SELECT 1
        FROM Inventaires
        WHERE idJoueur = ? AND idItem = ?
        LIMIT 1
    ");
    $stmtPossede->execute([$_SESSION["idJoueur"], $idItem]);
    $possedeItem = (bool)$stmtPossede->fetchColumn();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ajouterCommentaire"])) {
    if (!$possedeItem) {
        $erreurCommentaire = "Vous devez posséder cet item pour commenter.";
    } else {
        $nbEtoiles = (int)($_POST["nbEtoiles"] ?? 0);
        $commentaire = trim($_POST["commentaire"] ?? "");

        if ($nbEtoiles < 1 || $nbEtoiles > 5) {
            $erreurCommentaire = "La note doit être entre 1 et 5.";
        } elseif ($commentaire === "") {
            $erreurCommentaire = "Le commentaire ne peut pas être vide.";
        } else {
            $stmtCom = $pdo->prepare("
                INSERT INTO Evaluations (idJoueur, idItem, nbEtoiles, commentaire)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    nbEtoiles = VALUES(nbEtoiles),
                    commentaire = VALUES(commentaire)
            ");
            $stmtCom->execute([
                $_SESSION["idJoueur"],
                $idItem,
                $nbEtoiles,
                $commentaire
            ]);

            $messageCommentaire = "Commentaire ajouté avec succès.";
        }
    }
}

$stmtCommentaires = $pdo->prepare("
    SELECT 
        e.idJoueur,
        e.nbEtoiles,
        e.commentaire,
        j.alias
    FROM Evaluations e
    INNER JOIN Joueurs j ON e.idJoueur = j.idJoueur
    WHERE e.idItem = ?
    ORDER BY e.idJoueur DESC
");
$stmtCommentaires->execute([$idItem]);
$commentaires = $stmtCommentaires->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail produit</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

<?php include "header.php"; ?>

<main class="shop-page">
    <div class="shop-container" style="flex-direction:column; align-items:center;">

        <div class="product-card" style="max-width:600px;margin:auto">

            <div class="product-image">
                <img src="image/<?= htmlspecialchars($produit['photo']) ?>" alt="">
            </div>

            <h2><?= htmlspecialchars($produit['nom']) ?></h2>

            <p class="price"><?= htmlspecialchars($produit['prix']) ?> 🪙</p>
            <p class="stock">Stock : <?= (int)$produit['quantiteStock'] ?></p>

            <hr style="margin:15px 0">

            <?php if ($type === "armure"): ?>
                <p><strong>Matière :</strong> <?= htmlspecialchars($produit['matiere']) ?></p>
                <p><strong>Taille :</strong> <?= htmlspecialchars($produit['taille']) ?></p>

            <?php elseif ($type === "arme"): ?>
                <p><strong>Efficacité :</strong> <?= htmlspecialchars($produit['efficacite']) ?></p>
                <p><strong>Genre :</strong> <?= htmlspecialchars($produit['genre']) ?></p>
                <p><strong>Description :</strong> <?= htmlspecialchars($produit['description']) ?></p>

            <?php elseif ($type === "potion"): ?>
                <p><strong>Effet :</strong> <?= htmlspecialchars($produit['effet']) ?></p>
                <p><strong>Durée :</strong> <?= htmlspecialchars($produit['duree']) ?></p>

            <?php elseif ($type === "sort"): ?>
                <p><strong>Rareté :</strong> <?= htmlspecialchars($produit['rarete']) ?></p>
                <p><strong>Instantané :</strong> <?= $produit['estInstantane'] ? "Oui" : "Non" ?></p>
                <p><strong>Type :</strong> <?= htmlspecialchars($produit['typeSort']) ?></p>
                <p><strong>Description :</strong> <?= htmlspecialchars($produit['description']) ?></p>
                <p><strong>Vie :</strong> <?= htmlspecialchars($produit['pVie']) ?></p>
                <p><strong>Dégâts :</strong> <?= htmlspecialchars($produit['pDegat']) ?></p>
            <?php endif; ?>

            <hr style="margin:15px 0">

            <?php if ((int)$produit['quantiteStock'] > 0): ?>
                <a class="add-link add-to-cart-btn"
                   href="#"
                   data-id="<?= (int)$produit['idItem'] ?>"
                   data-type="<?= $type ?>"
                   data-ismage="<?= $isMage ? '1' : '0' ?>">
                    Ajouter au panier
                </a>
            <?php else: ?>
                <p style="color:red">Rupture de stock</p>
            <?php endif; ?>

        </div>

        <div class="product-card comments-card" style="max-width:600px;margin:25px auto 0 auto;">

            <h2>Commentaires</h2>

            <?php if ($messageCommentaire !== ""): ?>
                <p class="message-info"><?= htmlspecialchars($messageCommentaire) ?></p>
            <?php endif; ?>

            <?php if ($erreurCommentaire !== ""): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreurCommentaire) ?></p>
            <?php endif; ?>

            <?php if ($possedeItem): ?>
                <button type="button" class="filter-btn" id="toggleCommentForm">
                    Ajouter un commentaire
                </button>

                <div id="commentFormContainer" style="display:none; margin-top:15px;">
                    <form method="POST" action="detail.php?id=<?= (int)$idItem ?>" class="comment-form">
                        <label for="nbEtoiles">Note :</label>
                        <select name="nbEtoiles" id="nbEtoiles" required>
                            <option value="5">5 étoiles</option>
                            <option value="4">4 étoiles</option>
                            <option value="3">3 étoiles</option>
                            <option value="2">2 étoiles</option>
                            <option value="1">1 étoile</option>
                        </select>

                        <label for="commentaire">Commentaire :</label>
                        <textarea name="commentaire" id="commentaire" required></textarea>

                        <button type="submit" name="ajouterCommentaire" class="filter-btn">
                            Envoyer
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <p class="stock">Vous devez posséder cet item pour ajouter un commentaire.</p>
            <?php endif; ?>

            <hr style="margin:20px 0">

            <div class="comments-scroll">
                <?php if (!empty($commentaires)): ?>
                    <?php foreach ($commentaires as $com): ?>
                        <div class="comment-box">
                            <p>
                                <strong><?= htmlspecialchars($com["alias"]) ?></strong>
                                — ⭐ <?= (int)$com["nbEtoiles"] ?>/5
                            </p>

                            <p><?= htmlspecialchars($com["commentaire"]) ?></p>

                            <?php
                            $estSonCommentaire = isset($_SESSION["idJoueur"]) && (int)$com["idJoueur"] === (int)$_SESSION["idJoueur"];
                            if ($estSonCommentaire || $isAdmin):
                            ?>
                                <form method="POST" action="scripts/php/supprimerCommentaire.php" style="margin-top:8px;">
                                    <input type="hidden" name="idItem"         value="<?= (int)$idItem ?>">
                                    <input type="hidden" name="idJoueurCible"  value="<?= (int)$com["idJoueur"] ?>">
                                    <button type="submit" class="delete-btn">
                                        <?= $isAdmin && !$estSonCommentaire ? "🗑 Supprimer (admin)" : "Supprimer" ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun commentaire pour cet item.</p>
                <?php endif; ?>
            </div>

        </div>

    </div>

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
</main>

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
        font-family: Arial, Helvetica, sans-serif;
    ">
        <h2 style="margin-bottom:15px; color:#d4af37;">Attention</h2>

        <p style="margin-bottom:25px;">
            Seuls les mages peuvent acheter des sorts.<br>
            Répondez à des énigmes de type mage pour le devenir.
        </p>

        <button onclick="document.getElementById('sortMageAlert').style.display='none'"
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

<script>
const music = document.getElementById("bgMusic");
const toggleBtn = document.getElementById("musicToggle");

let musicOn = false;

toggleBtn?.addEventListener("click", () => {
    musicOn = !musicOn;

    if (musicOn) {
        music.play();
        toggleBtn.src = "image/sonOn.jpg";
    } else {
        music.pause();
        toggleBtn.src = "image/sonOff.jpg";
    }
});

document.querySelector(".add-to-cart-btn")?.addEventListener("click", function(e) {
    e.preventDefault();

    const isMage = this.dataset.ismage === "1";
    const type = this.dataset.type;
    const id = this.dataset.id;

    if (type === "sort" && !isMage) {
        document.getElementById("sortMageAlert").style.display = "flex";
        return;
    }

    window.location.href = "scripts/php/ajouterPanier.php?id=" + id;
});

const btn = document.getElementById("toggleCommentForm");
const form = document.getElementById("commentFormContainer");

btn?.addEventListener("click", () => {
    form.style.display = form.style.display === "none" ? "block" : "none";
});
</script>

</body>
</html>