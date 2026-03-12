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
            <div class="currency gold">🟡 </div>
            <div class="currency silver">⚪ </div>
            <div class="currency copper">🟤 </div>
        </div>

        <div class="plus-menu-container">
            <button class="icon-btn" id="plusBtn" type="button">+</button>

            <div class="plus-dropdown" id="plusDropdown">
                <a href="demander_argent.php">Demander argent</a>
                <a href="enigme.php">Aller à la page énigme</a>
                <a href="inventaire.php">Vendre item</a>
            </div>
        </div>

        <a href="panier.php" class="icon-link" title="Panier">🛒</a>
        <a href="profil.php" class="icon-link" title="Profil">👤</a>
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