<?php
if(!($_SESSION['connecte'])){
    header('Location: erreurConnection.php');
}
?>