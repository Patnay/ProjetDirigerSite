<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idJoueur = $_SESSION['idJoueur'];
    $stmt = $pdo->prepare("CALL payerPanier(?)");
    $stmt->execute([$idJoueur]);
    header("Location: inventaire.php"); 
}
?>