<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>À propos</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="favicon"href="favicon.ico"/>
</head>
<body>

<?php include "header.php"; ?>

<main class="about-page">
    <section class="about-container">

        <h1>À propos</h1>

        <div class="about-block">
            <h2>Présentation du jeu</h2>
            <p>
                Cette page a pour objectif de regrouper les règles du jeu,
                les noms des développeurs, les notes de xmise à jour
                ainsi que quelques informations intéressantes pour l’utilisateur.
            </p>
        </div>

        <div class="about-block">
            <h2>Règles du jeu</h2>
            <p>
                Le joueur peut acheter des items dans la boutique, gérer son inventaire,
                résoudre des énigmes et devenir un mage.
            </p>
            <p>
                Il faut surveiller l’argent disponible et le stock des objets.

            </p>
        </div>

        <div class="about-block">
            <h2>Développeurs</h2>
            <ul>
                <li>William</li>
                <li>Patrice</li>
                <li>Emmanuel</li>
                <li>Edouard</li>
            </ul>
        </div>

        <div class="about-block">
            <h2>Update notes</h2>
            <ul>
                <li>Ajout de la boutique connectée à la base de données.</li>
                <li>Ajout des filtres par prix et par étoiles.</li>
                <li>Ajout du header réutilisable en PHP.</li>
                <li>Ajout du panier, du profil et du menu popup.</li>
            </ul>
        </div>

        <div class="about-block">
            <h2>Petit mot pour l’utilisateur</h2>
            <p>
                Merci d’utiliser notre jeu. Nous espérons que l’expérience sera amusante
                et agréable. Bonne exploration !
            </p>
        </div>

    </section>
</main>

</body>
</html>