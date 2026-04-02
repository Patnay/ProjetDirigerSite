<?php   
require_once "init.php";
$isAjax = isset($_GET["ajax"]) && $_GET["ajax"] == "1";

if ($isAjax) {
    ob_start();
    /*Ici faudra changer cette ligne Edi, pour tes elements du contenu d'énigme (oui c'est
    un criss de copier collé de celui de la boutique, j'ai la flemme quoi) */
    renderShopContent($produits, $categories, $filtreActif, $totalPages, $page, $prixMin, $prixMax, $etoileMin, $etoileMax);
    echo ob_get_clean();
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Énigme</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/png" href="favicon.png">
</head>

<?php include "header.php"; ?>

<body>
<main class="about-page">
    <section class="about-container">

        <h1>Énigmes</h1>

        <div class="about-block">
            <h2>Pas finis</h2>
            <p>
                Cette page est toujours en construction,
                veuillez attendre que les godlike programmeurs
                finissent cette page, merci beaucoup de votre patience et profitez bien de la musique :3
            </p>
        </div>
    </section>
    

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
    <source src="musique/godskin.mp3" type="audio/mp3">
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
document.addEventListener("DOMContentLoaded", () => {
    const mainContainer = document.getElementById("shop-main");

    function loadAjax(url) {
        fetch(url + (url.includes("?") ? "&" : "?") + "ajax=1")
            .then(res => res.text())
            .then(html => {
                mainContainer.innerHTML = html;
                window.history.pushState({}, "", url);
                attachPaginationListeners();
                attachFilterListener();
            })
            .catch(err => console.error("Erreur AJAX :", err));
    }

    function attachFilterListener() {
        const filterForm = document.querySelector(".filters form");
        if (!filterForm) return;

        filterForm.addEventListener("submit", function(e) {
            e.preventDefault();
            const url = "boutique.php?" + new URLSearchParams(new FormData(filterForm)).toString();
            loadAjax(url);
        });
    }

    function attachPaginationListeners() {
        document.querySelectorAll(".pagination a").forEach(link => {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                loadAjax(this.href);
            });
        });
    }
    attachFilterListener();
    attachPaginationListeners();
});
</script>
</body>
</html>