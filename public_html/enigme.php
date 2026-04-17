<?php
require_once "init.php";
if (!isset($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Énigme</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/enigme.css">
    <link rel="icon" type="image/png" href="favicon.png">
</head>

<?php include "header.php"; ?>

<body>
    <main id="enigme-container"></main>

    <!-- Bouton musique -->
    <img id="musicToggle" src="image/sonOff.jpg" style="
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

        // --- AJAX ---
        function loadEnigme(diff = "F") {
            fetch("enigme_content.php?diff=" + diff)
                .then(r => r.text())
                .then(html => {
                    document.getElementById("enigme-container").innerHTML = html;
                    attachListeners();
                });
        }

        function attachListeners() {

            // Changer difficulté
            document.querySelectorAll(".diff-btn").forEach(btn => {
                btn.addEventListener("click", () => {
                    loadEnigme(btn.dataset.diff);
                });
            });

            // Répondre à une énigme
            document.querySelectorAll(".rep-answer").forEach(btn => {
                btn.addEventListener("click", () => {

                    const formData = new FormData();
                    formData.append("idEnigme", btn.dataset.idenigme);
                    formData.append("idReponse", btn.dataset.idrep);

                    fetch("enigme_content.php?diff=" + btn.dataset.diff, {
                        method: "POST",
                        body: formData
                    })
                        .then(r => r.text())
                        .then(html => {

                            // Détection AVANT injection
                            const temp = document.createElement("div");
                            temp.innerHTML = html;

                            const statusDiv = temp.querySelector("#repStatus");

                            if (statusDiv) {
                                const isGood = statusDiv.dataset.status === "GOOD";

                                // 1. Jouer l’animation AVANT de charger la nouvelle question
                                showRepAnimation(isGood);

                                // 2. Attendre la fin de l’animation avant d’injecter la nouvelle question
                                setTimeout(() => {
                                    document.getElementById("enigme-container").innerHTML = html;
                                    attachListeners();
                                }, 1800);

                            } else {
                                // Pas de réponse → injection normale
                                document.getElementById("enigme-container").innerHTML = html;
                                attachListeners();
                            }

                        });
                });
            });
        }

        // Charger la première fois
        loadEnigme("F");
    </script>

    <!-- Script pour animation -->
    <script>
        function showRepAnimation(isGood) {
            const overlay = document.getElementById("repAnimation");
            const img = document.getElementById("repImage");
            const text = document.getElementById("repText");
            const main = document.getElementById("enigme-container");

            // Overlay toujours noir
            overlay.style.background = "rgba(0,0,0,0.65)";

            if (isGood) {
                img.src = "image/bonneRep.png";
                text.textContent = "Bonne réponse !!";
                main.style.transition = "background 0.4s";
                main.style.background = "rgba(0, 150, 0, 0.25)";
            } else {
                img.src = "image/mauvaiseRep.png";
                text.textContent = "Mauvaise réponse...";
                main.style.transition = "background 0.4s";
                main.style.background = "rgba(150, 0, 0, 0.25)";
            }

            overlay.style.display = "flex";

            setTimeout(() => {
                overlay.style.display = "none";
                main.style.background = "transparent";
            }, 1800);
        }
    </script>

    <!-- Overlay animation -->
    <div id="repAnimation" style="
        display:none;
        position:fixed;
        top:0; left:0;
        width:100%; height:100%;
        background:rgba(0,0,0,0.65);
        backdrop-filter: blur(4px);
        justify-content:center;
        align-items:center;
        flex-direction:column;
        z-index:99999;
    ">
        <img id="repImage" src="" style="width:220px; margin-bottom:20px;">
        <div id="repText" style="
            font-size:2rem;
            font-weight:bold;
            color:white;
            text-shadow:0 0 10px black;
            font-family:'Agmena Pro', serif;
        "></div>
    </div>

</body>
</html>
