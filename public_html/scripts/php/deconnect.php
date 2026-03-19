<?php
session_start();
session_unset();
session_destroy();
header("Location: ../../boutique.php");
exit;

?>