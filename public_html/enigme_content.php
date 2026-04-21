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

$sqlStats = "SELECT ptVie, streak, nbEnigmeMage FROM Joueurs WHERE idJoueur = ?";
$stmtStats = $pdo->prepare($sqlStats);
$stmtStats->execute([$idJoueur]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

$ptVie = $stats["ptVie"];
$streak = $stats["streak"];
$mageProgress = $stats["nbEnigmeMage"];

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
