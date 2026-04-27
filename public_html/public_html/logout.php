<?php
try {
    ini_set('display_errors', 'Off');
    ini_set('log_errors', 'On');
    error_reporting(E_ALL);
    session_start();
} catch (Exception) {

}

/* Détruire la session */
session_unset();
session_destroy();

/* Redirection */
header("Location: connexion.php");
exit;