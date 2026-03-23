<?php
session_start();

/* Détruire la session */
session_unset();
session_destroy();

/* Redirection */
header("Location: connexion.php");
exit;