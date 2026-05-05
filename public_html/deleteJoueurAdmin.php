<?php
require_once("init.php");
$idJoueur = (int)$_GET["joueur"];
$stmtAdmin = $pdo->prepare("CALL suppCompte(?)");
$stmtAdmin->execute([$idJoueur]);
header("Location: admin.php");

 
?>