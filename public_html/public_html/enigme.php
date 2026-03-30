<?php   
require_once "init.php";
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
<main >

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

</main>
</body>
</html>