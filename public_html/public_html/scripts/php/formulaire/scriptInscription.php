<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once "scripts/php/bd/connectionBd.php";

    $alias = trim($_POST['alias'] ?? '');
    $mp1 = $_POST['mp1'] ?? '';
    $mp2 = $_POST['mp2'] ?? '';
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $courriel = trim($_POST['courriel'] ?? '');
    $erreurs = [];

    if (strlen($alias) < 2 || strlen($alias) > 25) {
        $erreurs[] = "Votre pseudo doit contenir entre 2 et 25 caratères.";
    }

    if (strlen($mp1) < 8 || strlen($mp1) > 50) {
        $erreurs[] = "Votre mot de passe doit contenir entre 8 et 50 caratères.";
    }

    if ($mp1 != $mp2) {
        $erreurs[] = "Les mots de passe ne sont pas pareil.";
    }

    if (strlen($nom) < 2 || strlen($nom) > 50) {
        $erreurs[] = "Votre nom doit contenir entre 2 et 50 caratères.";
    }

    if (strlen($prenom) < 2 || strlen($prenom) > 50) {
        $erreurs[] = "Votre prenom doit contenir entre 2 et 50 caratères.";
    }

    if (strlen($courriel) < 6 || strlen($courriel) > 254) {
        $erreurs[] = "Votre courriel n'est pas valide.";
    }

    $stmt = $pdo->prepare("SELECT * FROM Joueurs where alias = ? ");
    $stmt->execute([$alias]);
    if ($stmt->fetch()) {
        $erreurs[] = "Cet alias existe déjà.";
    }

    if (!empty($erreurs)) {
        echo "<h2>Erreurs :</h2><ul>";
        foreach ($erreurs as $e)
            echo "<li>$e</li>";
        echo "</ul>";

        $_POST['psw'] = "";
        $_POST['psw2'] = "";
        $selection = $_POST['selection'];

    } else {

        $stmt = $pdo->prepare("CALL creeCompte(?,?,?,?,?, @output)");
        $stmt->execute([$alias, $prenom, $nom, $courriel, $mp1]);
        $result = $pdo->query("SELECT @output as output");
        $output = $result->fetch()['output'];
        echo $output;
        include("scripts/php/connect.php");
        echo "<h2>Compte créé avec succès !</h2>";
    }
    header("Location: boutique.php");
}
else{
    
        //echo "<h2>Pas encore envoyer</h2>";
}

//   if(!($erreurs)){
//     //$hash = password_hash($mp1, PASSWORD_DEFAULT); -> Il est hasher dans la procedure

//     $stmt = $pdo ->prepare("CALL creeCompte(?,?,?,?,?,?)");
//     $stmt -> execute([$alias, $prenom, $nom, $courriel, $mp1,$output]);
//     echo($output);
//     header("Location: boutique.php");
//     //  exit;
//   }

?>