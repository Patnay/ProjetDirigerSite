<?php
$stmt = $pdo->prepare("SELECT * FROM Joueurs WHERE idJoueur = ?");
$stmt->execute([$output]);
$joueur = $stmt->fetch();
$_SESSION["alias"] = $joueur["alias"];
$_SESSION["idJoueur"] = $joueur["idJoueur"];
$_SESSION["estMage"] = (int)$joueur["estMage"];
header("Location: profil.php");
?>
