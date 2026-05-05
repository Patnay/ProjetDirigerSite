<?php
require_once "../../init.php";

if (!isset($_SESSION["idJoueur"])) {
    header("Location: ../../connexion.php");
    exit;
}

$idJoueurSession = (int)$_SESSION["idJoueur"];
$idItem          = (int)($_POST["idItem"]        ?? 0);
$idJoueurCible   = (int)($_POST["idJoueurCible"] ?? $idJoueurSession);

if ($idItem <= 0) {
    header("Location: ../../boutique.php");
    exit;
}

$stmtAdmin = $pdo->prepare("SELECT estAdmin FROM Joueurs WHERE idJoueur = ?");
$stmtAdmin->execute([$idJoueurSession]);
$isAdmin = (int)$stmtAdmin->fetchColumn() === 1;

// Un non-admin ne peut supprimer que son propre commentaire
if (!$isAdmin) {
    $idJoueurCible = $idJoueurSession;
}

$stmt = $pdo->prepare("DELETE FROM Evaluations WHERE idJoueur = ? AND idItem = ?");
$stmt->execute([$idJoueurCible, $idItem]);

header("Location: ../../detail.php?id=" . $idItem);
exit;