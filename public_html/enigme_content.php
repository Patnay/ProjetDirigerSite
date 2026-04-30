<?php
require_once "init.php";

if (!isset($_SESSION["idJoueur"])) {
    exit("NOT_CONNECTED");
}

$idJoueur = (int) $_SESSION["idJoueur"];

$diff = $_GET["diff"] ?? "F";

function GetQuestionReponse($diff, $pdo)
{
    if ($diff === "D") {
        $sql = "SELECT * FROM Enigmes
                WHERE difficulte IN ('D','A') AND estPiege = 0
                ORDER BY RAND() LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([]);
    } elseif ($diff === "X") {
        $sql = "SELECT * FROM Enigmes
                WHERE estPiege = 0
                ORDER BY RAND() LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([]);
    } else {
        $sql = "SELECT * FROM Enigmes
                WHERE difficulte = ? AND estPiege = 0
                ORDER BY RAND() LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$diff]);
    }

    $enigme = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$enigme) return null;

    $sqlRep = "SELECT * FROM Reponses WHERE idEnigme = ?";
    $stmtRep = $pdo->prepare($sqlRep);
    $stmtRep->execute([$enigme[0]["idEnigme"]]);
    $reponsesEnigme = $stmtRep->fetchAll(PDO::FETCH_ASSOC);

    return [
        "question" => $enigme,
        "reponses" => $reponsesEnigme,
        "difficulte" => $enigme[0]["difficulte"]
    ];
}

$message = "";
$repStatus = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sql = "CALL RepondreEnigme(?,?,?,@output)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idJoueur, $_POST["idEnigme"], $_POST["idReponse"]]);
    $stmt->closeCursor();

    $result = $pdo->query("SELECT @output AS estBonne")->fetch(PDO::FETCH_ASSOC);

    $message = ($result["estBonne"] == 1)
        ? "Bonne réponse!!"
        : "Mauvaise réponse...";

    $repStatus = ($result["estBonne"] == 1) ? "GOOD" : "BAD";
}

$sqlStats = "SELECT COUNT(s.estReussi) AS enigmesReussi , j.ptVie, j.streak, j.nbEnigmeMage, e.difficulte
FROM Joueurs j 
INNER JOIN Statistiques s ON j.idJoueur = s.idJoueur
INNER JOIN Enigmes e ON s.idEnigme = e.idEnigme
WHERE j.idJoueur = ? && s.estReussi = 1 
GROUP BY e.difficulte;";
$stmtStats = $pdo->prepare($sqlStats);
$stmtStats->execute([$idJoueur]);
$stats = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

$ptVie = $stats[0]["ptVie"];
$streak = $stats[0]["streak"];
$mageProgress = $stats[0]["nbEnigmeMage"];
$facileReussi = $stats[0]["enigmesReussi" == "F"];
$moyenneReussi = $stats[0]["enigmesReussi" == "M"];
$difficileReussi = $stats[0]["enigmesReussi" == "D"];
$magieReussi = $stats[0]["enigmesReussi" == "A"];

// Si plus de vie → on sort tout de suite
if ($ptVie <= 0) {
    ?>
    <section class="about-container">

        <h1>Énigma</h1>

        <div class="nohp-panel">
            <h2>Vous n'avez plus de points de vie !</h2>
            <p>Vous devez vous soigner avant de continuer à jouer.</p>

            <div class="nohp-buttons">
                <a href="boutique.php" class="nohp-btn">Aller à la boutique</a>
                <a href="inventaire.php" class="nohp-btn">Aller à l’inventaire</a>
            </div>
        </div>

    </section>
    <?php
    exit;
}

// Recharger question après réponse (ou au premier chargement)
$data = GetQuestionReponse($diff, $pdo);
$question = $data["question"];
$reponses = $data["reponses"];
$difficulte = $data["difficulte"];
shuffle($reponses);
?>

<section class="about-container">

    <h1>Énigma</h1>

    <div class="diff-buttons">
        <button class="diff-btn diff-F" data-diff="F">Facile</button>
        <button class="diff-btn diff-M" data-diff="M">Moyen</button>
        <button class="diff-btn diff-D" data-diff="D">Difficile</button>
        <button class="diff-btn diff-X" data-diff="X">Aléatoire</button>
    </div>

    <div class="stats-panel">
        <h2>Statistiques</h2>
        <p><strong>❤️ Points de vie :</strong> <?= $ptVie ?></p>
        <p><strong>🔥 Streak :</strong> <?= $streak ?></p>

        <?php if ($mageProgress < 3): ?>
            <p><strong>✨ Progression mage :</strong> <?= $mageProgress ?>/3</p>
        <?php else: ?>
            <p class="mage-complete">✨ Vous êtes un mage !</p>
        <?php endif; ?>

    <section id="reponses">
    <h4>Nombre de bonnes réponses selon la difficulté:</h4>

    <svg width="100" height="100" xmlns="http://www.w3.org/2000/svg">
        <circle cx="100" cy="100" r="80" fill="none" stroke="#ddd" stroke-width="5" />

        <path data-legende="liRouge" class="getClass"
              d="M100,100 L100,20 A80,80 0 0,1 180,100 Z"
              fill="#FF5733" />

        <path data-legende="liJaune" class="getClass"
              d="M100,100 L130,25.7 A80,80 0 0,1 100,180 Z"
              fill="#FFC300" />

        <path data-legende="liBleu" class="getClass"
              d="M100,100 L180,100 A80,80 0 0,1 20,100 Z"
              fill="#3498DB" />

        <path data-legende="liVert" class="getClass"
              d="M100,100 L100,180 A80,80 0 0,1 100,20 Z"
              fill="#27AE60" />
    </svg>

    <ul>
        <li id="liVert"><span id="vert"></span> Facile</li>
        <li id="liBleu"><span id="bleu"></span> Moyenne</li>
        <li id="liJaune"><span id="jaune"></span> Difficile</li>
        <li id="liRouge"><span id="rouge"></span> Magie</li>
    </ul>
    </section>
    </div>

    <p>
        Difficulté: 
        <?php
        switch ($difficulte) {
            case 'F': echo "Facile"; break;
            case 'M': echo "Medium"; break;
            case 'D': echo "Difficile"; break;
            case 'A': echo "Mage"; break;
        }
        ?> 
    </p>

    <h3>La question:</h3>
    <p><?= $question[0]["enonce"] ?></p>

    <div class="rep_buttons">
        <?php foreach ($reponses as $rep): ?>
                <button class="rep-btn rep-answer"
                data-idenigme="<?= $question[0]["idEnigme"] ?>"
                data-idrep="<?= $rep["idReponse"] ?>"
                data-diff="<?= $diff ?>">
                <?= $rep["reponse"] ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($message)): ?>
        <div id="repStatus" data-status="<?= $repStatus ?>"></div>
    <?php endif; ?>

</section>
