<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try{
        $idJoueur = $_SESSION['idJoueur'];
    $stmt = $pdo->prepare("CALL payerPanier(?)");
    $stmt->execute([$idJoueur]);
    //header("Location: panier.php");
    echo '<script>location.reload();</script>';
    }
    catch(PDOException){
        
    }
    finally{
        exit;
    }

}
?>