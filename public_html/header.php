<?php
$nbOrHeader = 0;
$nbArgentHeader = 0;
$nbBronzeHeader = 0;

$profilLink = "connexion.php";
$profilTitle = "Se connecter";
$profilImg = "";

if (isset($_SESSION["idJoueur"]) && isset($pdo)) {
    $sqlHeader = "SELECT * FROM Joueurs WHERE idJoueur = :idJoueur";
    $stmtHeader = $pdo->prepare($sqlHeader);
    $stmtHeader->execute([":idJoueur" => $_SESSION["idJoueur"]]);
    $joueurHeader = $stmtHeader->fetch(PDO::FETCH_ASSOC);

    if ($joueurHeader) {
        $nbOrHeader = (int)($joueurHeader["nbOr"] ?? 0);
        $nbArgentHeader = (int)($joueurHeader["nbArgent"] ?? 0);
        $nbBronzeHeader = (int)($joueurHeader["nbBronze"] ?? 0);

        $profilLink = "profil.php";
        $profilTitle = "Profil";
        $profilImg = trim($joueurHeader["img"] ?? "");
    }
}
?>

<header class="site-header">
    <div class="header-left">
        <a href="boutique.php" class="logo-link">
            <div class="logo">BFGD</div>
        </a>

        <a href="index.php" class="home-icon-link" title="Accueil">🏠</a>
    </div>

    <nav class="main-nav">
        <a href="enigme.php">Énigme</a>
        <a href="apropos.php">À propos</a>
    </nav>

    <div class="header-right">

        <div class="currency-group">
            <div class="currency gold">🪙 <span><?= $nbOrHeader ?></span></div>
            <div class="currency silver">🪙 <span><?= $nbArgentHeader ?></span></div>
            <div class="currency copper">🪙 <span><?= $nbBronzeHeader ?></span></div>
        </div>

        <div class="plus-menu-container">
            <button class="icon-btn" id="plusBtn" type="button">+</button>

            <div class="plus-dropdown" id="plusDropdown">
                <a href="demanderArgent.php">Demander argent</a>
                <a href="enigme.php">Aller à la page énigme</a>
                <a href="inventaire.php">Vendre item</a>
            </div>
        </div>

        <a href="panier.php" class="icon-link" id="cart-icon" title="Panier">🛒</a>

        <a href="<?= $profilLink ?>" class="icon-link" title="<?= $profilTitle ?>">
            <?php if ($profilImg !== ""): ?>
                <img src="images/<?= htmlspecialchars($profilImg) ?>" alt="Profil" class="header-profile-image">
            <?php else: ?>
                👤
            <?php endif; ?>
        </a>
    </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const plusBtn = document.getElementById("plusBtn");
    const plusDropdown = document.getElementById("plusDropdown");

    plusBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        plusDropdown.classList.toggle("show");
    });

    plusDropdown.addEventListener("click", function (e) {
        e.stopPropagation();
    });

    document.addEventListener("click", function () {
        plusDropdown.classList.remove("show");
    });
});
</script>