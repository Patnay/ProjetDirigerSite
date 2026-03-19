<?php
$stmt = $pdo->prepare("SELECT * FROM Joueurs WHERE idJoueur = ?");
$stmt->execute([$output]);
$joueur = $stmt->fetch();
$_SESSION["alias"] = $joueur["alias"];
$_SESSION["idJoueur"] = $joueur["idJoueur"];
header("Location: profil.php");
?>