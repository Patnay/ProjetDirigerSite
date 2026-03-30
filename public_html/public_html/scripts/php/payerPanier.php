<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try{
        $idJoueur = $_SESSION['idJoueur'];
    $stmt = $pdo->prepare("CALL payerPanier(?)");
    $stmt->execute([$idJoueur]);
    //header("Location:inventaire.php");
    echo '<script>location.reload();</script>';
    }
    catch(PDOException){
        /*Doit implanter les erreur de manque de fonds
        
UPDATE Joueurs
SET nbOr=100,
	nbArgent=100,
    nbBronze=1000
WHERE alias = 'Pat';

        */
    }
    finally{
        exit;
    }

}
?>