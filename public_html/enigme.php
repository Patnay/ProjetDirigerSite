<?php
require_once "init.php";
if (!isset($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}

$question = "";
$reponses = [];
$resultat = "";
$idJoueur = (int) $_SESSION["idJoueur"];
$difficulte = "";

/* ici c'était inversé donc ça le brisait ... */
$diff = isset($_GET["diff"]) ? $_GET["diff"] : "F";

$data = GetQuestionReponse($diff, $pdo);

if ($data) {
    $question = $data['question'];
    $reponses = $data['reponses'];
    $difficulte = $data['difficulte'];
}

/*INSTANCIE TES VARIABLE STP.... $conn qui = null ne vas pa resussir a exectute shit*/
function GetQuestionReponse($diff, $pdo)
{
    $sql = "SELECT * FROM Enigmes
            WHERE difficulte = ? AND estPiege = 0
            ORDER BY RAND() LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$diff]);
    $enigme = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$enigme) {
        return null;
    }

    $sqlRep = "SELECT * FROM Reponses WHERE idEnigme = ?";
    $stmtRep = $pdo->prepare($sqlRep);
    $stmtRep->execute([$enigme["idEnigme"]]);
    $reponsesEnigme = $stmtRep->fetchAll(PDO::FETCH_ASSOC);

    return [
        "question" => $enigme["enonce"],
        "reponses" => $reponsesEnigme,
        "difficulte" => $diff
    ];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bonne = $_POST["bonne"];
    $diff = $_POST["diff"];

    if ($bonne == 1) {
        if ($diff == "F") {
            $sql = "UPDATE Joueurs SET nbBronze = nbBronze + 10 WHERE idJoueur = ?";
        } else if ($diff == "M") {
            $sql = "UPDATE Joueurs SET nbArgent = nbArgent + 10 WHERE idJoueur = ?";
        } else {
            $sql = "UPDATE Joueurs SET nbOr = nbOr + 10 WHERE idJoueur = ?";
        }

        /*  remplacer conn par pdo, rien à foutre fuck off */
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idJoueur]);

        $message = "Bonne réponse!!";
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

                <!--  $data est un tableau ... alors faut afficher ça criss $question -->
                <p><?= $question ?></p>

                <div>
                    <?php foreach ($reponses as $rep): ?>
                        <form method="POST" action="enigme.php?diff=<?= $difficulte ?>">
                            <input type="hidden" name="bonne" value="<?= $rep["estBonneReponse"] ?>">
                            <input type="hidden" name="diff" value="<?= $difficulte ?>">
                            <button type="submit"><?= $rep["reponse"] ?></button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>

            <br>

            <aside>
                <section class="about-container">
                    <div><?= $message ?? "" ?></div>
                </section>
            </aside>

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
</body>
</html>
