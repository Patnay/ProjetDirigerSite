<?php
try {
    ini_set('display_errors', 'Off');
    ini_set('log_errors', 'On');
    error_reporting(E_ALL);
    session_start();
} catch (Exception) {

}
include("scripts/php/bd/connectionBd.php");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alias = trim($_POST['pseudo'] ?? '');
    $motDePasse = $_POST['mp'] ?? '';

    if ($alias === '' || $motDePasse === '') {
        $message = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Joueurs WHERE alias = ?");
        $stmt->execute([$alias]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashSaisi = hash('sha256', $motDePasse);
            $hashBd = $user['motDePasse'] ?? '';

            if ($hashBd !== '' && hash_equals($hashBd, $hashSaisi)) {
                $_SESSION['connecte'] = true;
                $_SESSION['idJoueur'] = $user['idJoueur'];
                $_SESSION['alias'] = $user['alias'];

                header('Location: boutique.php');
                exit;
            } else {
                $message = "L'utilisateur ou le mot de passe est incorrect.";
            }
        } else {
            $message = "L'utilisateur ou le mot de passe est incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="shop-page">

    <?php include "header.php"; ?>

    <div class="connect-container">
        <main>
            <h1>Connexion</h1>

            <?php if ($message !== ''): ?>
                <p class="message-erreur"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <form action="connexion.php" method="POST">
                <fieldset>
                    <legend>Veuillez vous connecter :</legend>

                    <label for="pseudo">Pseudo :</label>
                    <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($_POST['pseudo'] ?? '') ?>"
                        required>

                    <br><br>

                    <label for="mp">Mot de passe :</label>
                    <input type="password" id="mp" name="mp" required>

                    <br><br>

                    <button type="submit" class="connect">Se connecter</button>
                </fieldset>
            </form>

            <br>

            <p>
                Pas encore de compte ?
                <a href="inscription.php">Créer un compte</a>
            </p>
        </main>
    </div>

</body>

</html>