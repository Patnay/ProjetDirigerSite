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

$question = "";
$reponses = [];
$resultat = "";
function GetQuestionReponse($diff){
    global $conn;
    $sql = "SELECT * FROM Enigmes
            WHERE difficulte = ? AND estPige = 0
            ORDER BY RAND() LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$diff]);
    $enigme = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$enigme){
        return null;
    }

    /*$update = $conn->prepare("UPDATE enigmes SET estPige = 1 WHERE idEnigme = ?");
    $update->execute([$enigme["idEnigme"]]);*/

    $sqlRep = "SELECT * FROM reponses WHERE idEnigme = ?";
    $stmtRep = $conn->prepare($sqlRep);
    $stmtRep->execute([$enigme["idEnigme"]]);
    $reponses = $stmtRep->fetchAll(PDO::FETCH_ASSOC);

    return[
        "question" => $enigme["enonce"],
        "reponses" => $reponses
    ];
}

if($_SERVER["REQUEST_METHOD"] === "POST"){
    if($_POST["bonne"] == 1){
        $message = "Bonne réponse!";
    } else {
        $message = "Mauvaise réponse...";
    }
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
    <h1>Enigma</h1>
    <div>
        <div>
           <h3>Choisissez votre difficulté de l'énigme</h3>
        <div>
           <button onclick="GetQuestionReponse(F)">Facile</button>
           <button onclick="GetQuestionReponse(M)">Moyen</button>
           <button onclick="GetQuestionReponse(D)">Difficile</button>
        </div>
    </div>
    <br>
        <div>
            <h3>La question:</h3>
            <p><?= $question?></p>
            <div>
                <!--<button>Réponse 1</button>
                <button>Réponse 2</button>
                <br>
                <button>Réponse 3</button>
                <button>Réponse 4</button>-->
                <?php foreach($reponses as $rep): ?>
                    <form>
                        <input type="hidden" name="bonne" value="<?= $rep["estBonneReponse"]?>">
                        <button type="submit"><?= $rep["reponse"]?></button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
        <br>
        <aside>
        <section class="about-container">
            <div><?= $message?></div>
        </section>
        </aside>
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