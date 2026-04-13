<?php
require_once "init.php";
if (!isset($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}

// if ($isAjax) {
//     ob_start();
//     /*Ici faudra changer cette ligne Edi, pour tes elements du contenu d'énigme (oui c'est
//     un criss de copier collé de celui de la boutique, j'ai la flemme quoi) */
//     renderShopContent($produits, $categories, $filtreActif, $totalPages, $page, $prixMin, $prixMax, $etoileMin, $etoileMax);
//     echo ob_get_clean();
//     exit;
// }


/*Get Question*/

$sql = "CALL AfficherEnigmeJoueur(?,?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["idJoueur"],'X']);
$enigme = $stmt->fetchAll(PDO::FETCH_ASSOC);
$rep1 = $enigme(0)["contenu"];
$rep2 = $enigme(1)["contenu"];
$rep3 = $enigme(2)["contenu"];
$rep4 = $enigme(3)["contenu"];
$question = $enigme(4)["contenu"];



?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Énigme</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <style>
        .diff-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .diff-btn {
            padding: 10px 24px;
            border: none;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
            transition: 0.2s;
        }

        .diff-btn:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }

        .diff-F {
            background-color: #4caf50;
            color: white;
        }

        .diff-M {
            background-color: #ff9800;
            color: white;
        }

        .diff-D {
            background-color: #f44336;
            color: white;
        }

        .diff-X {
            background-color: #9c27b0;
            color: white;
        }

        .diff-active {
            filter: brightness(0.65);
            outline: 3px solid white;
            outline-offset: 2px;
        }
    </style>
</head>

<?php include "header.php"; ?>

<body>
    <main class="about-page">
        <section class="about-container">
            <h1>Enigma</h1>
            <div class="diff-buttons">
                <button class="diff-btn diff-F" onclick="location.href='enigme.php?diff=F'">Facile</button>
                <button class="diff-btn diff-M" onclick="location.href='enigme.php?diff=M'">Moyen</button>
                <button class="diff-btn diff-D" onclick="location.href='enigme.php?diff=D'">Difficile</button>
                <button class="diff-btn diff-X" onclick="location.href='enigme.php?diff=X'">Aléatoire</button>
            </div>
            <br>
            <div>
                <h3>La question:</h3>
                <p><?= $question ?></p>
                <div>
                    <!--<button>Réponse 1</button>
                <button>Réponse 2</button>
                <br>
                <button>Réponse 3</button>
                <button>Réponse 4</button>-->
                  <?php 
                  echo($rep1);
                    ?>
                </div>
            </div>
            <br>
            <aside>
                <section class="about-container">
                    <div><?= $message ?></div>
                </section>
            </aside>
            </div>
        </section>

    </main>
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

                filterForm.addEventListener("submit", function (e) {
                    e.preventDefault();
                    const url = "boutique.php?" + new URLSearchParams(new FormData(filterForm)).toString();
                    loadAjax(url);
                });
            }

            function attachPaginationListeners() {
                document.querySelectorAll(".pagination a").forEach(link => {
                    link.addEventListener("click", function (e) {
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