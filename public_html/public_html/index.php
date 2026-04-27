<?php   
require_once "init.php";
$profilLink = isset($_SESSION["idJoueur"]) ? "profil.php" : "connexion.php";
?>

<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <link rel="icon" type="image/png" href="favicon.png">
</head>
<style>
  @font-face {
  font-family: "Agmena Pro";
  src: url("https://assets.codepen.io/5896374/agmena-pro.woff2") format("woff2"),
    url("https://assets.codepen.io/5896374/agmena-pro.woff") format("woff"),
    url("https://assets.codepen.io/5896374/agmena-pro.ttf") format("truetype"),
    url("https://assets.codepen.io/5896374/agmena-pro.svg#AgmenaPro-Regular")
      format("svg");
  font-weight: normal;
  font-style: normal;
  font-display: swap;
}

* {
  box-sizing: border-box;
  cursor: url('https://cybersandbox.ca/resources/elden/er-cur.png'), auto;
}

::selection {
  background: black;
  color: white;
}

body {
  font-family: "Agmena Pro", sans-serif;
  margin: 0;
  min-height: 100vh;
  width: 100vw;
  font-size: 18px;
  line-height: 1.5em;
  background: black;
  color: #dcd5b8;
  overflow-x: hidden;
  position: relative;
  display: grid;
  align-content: end;
  text-align: center;
}

main {
  display: block;
  position: relative;
  width: 100%;
  min-height: 100vh;
}

img {
  max-width: 100%;
  user-select: none;
}

.ring {
  position: absolute;
  top: 0;
  left: 0;
  height: 75vh;
  width: 100%;
  max-width: unset;
  object-fit: contain;
  object-position: top center;
  z-index: 0;
  pointer-events: none;
  opacity: 0.6;
}

.menu {
  position: relative;
  z-index: 2;
}

.logo {
  margin: 25vh auto;
  max-width: 90%;
}

a {
  color: inherit;
  text-decoration: none;
}

.nav {
  margin: 10px auto;
  padding: 0;
  list-style: none;
  text-transform: uppercase;
  font-size: 1.1em;
  max-width: 90vw;
  width: 400px;
  height: 8em;
  overflow-y: scroll;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.nav::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

.nav li {
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav a {
  display: block;
  line-height: 2em;
  position: relative;
  outline: none;
  transition: all 0.1s ease;
}

.nav a:focus,
.nav a:hover {
  color: #fcfbf3;
  text-shadow: 0 0 6px #b6804a;
  font-weight: bold;
}

.nav a::before,
.nav a::after {
  content: "";
  display: block;
  position: absolute;
  pointer-events: none;
  left: 0;
  top: 0;
  height: 100%;
  width: 100%;
  background-size: 100% 100%;
  background-position: center center;
  z-index: -1;
  opacity: 0;
  transition: all 0.1s ease;
}
 
.nav a::before {
  background: radial-gradient(ellipse farthest-side at center center, #b5b2a5 0%, #c0ae78 25%, #66481e 50%, rgba(0,0,0,0) 100%);
}

.nav a:focus::before,
.nav a:hover::before {
  opacity: 0.5;
}

.nav a::after {
  background-image: url('https://cybersandbox.ca/resources/elden/er-grace.png');
  top: -25%;
  height: 150%;
}

.nav a:focus::after,
.nav a:hover::after {
  left: 0;
  width: 100%;
  opacity: 0.5;
  filter: saturate(1.25) hue-rotate(-5deg);
}

footer {
  font-size: 14px;
  position: absolute;
  bottom: 0;
  padding: 30px;
  opacity: 0.7;
  width: 100%;
  z-index: 2;
}
</style>
<main>
  <div class="menu">

    <img class="logo" src="https://cybersandbox.ca/resources/elden/er-logo.png">

    <ul class="nav">
      <li><a href="boutique.php">Boutique</a></li>
      <li><a href="#" id="newGameBtn">Inscription</a></li>
      <li><a href="<?= $profilLink ?>">Connexion</a></li>
      <li><a href="apropos.php">À propos</a></li>
      <li><a href="#">Quit Game</a></li>
    </ul>

  </div>
  <footer>2026 Projet Dirigé - B.F.G.D - Carl Patrice Antonio Paul, Emmanuel Douville, Edouard St-Martin, William Lavoie</footer>
<!--ELDEN RING &trade; &amp; &copy;2022 BANDAI NAMCO Entertainment Inc./ &copy;2022 FromSoftware, Inc.-->
  <img class="ring" src="https://cybersandbox.ca/resources/elden/er-glyph.jpg">
  <script>
document.getElementById("newGameBtn").addEventListener("click", function (e) {
    e.preventDefault();

    const isConnected = <?= isset($_SESSION["idJoueur"]) ? "true" : "false" ?>;

    if (!isConnected) {
        window.location.href = "inscription.php";
        return;
    }
    document.getElementById("newGameAlert").style.display = "flex";
});
</script>
<!-- Bouton musique -->
<img id="musicToggle" 
     src="image/sonOff.jpg" 
     style="
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
        cursor: pointer;
        z-index: 9999;
     ">
<audio id="bgMusic" loop>
    <source src="musique/eldenRing.mp3" type="audio/mp3">
</audio>
<script>
const music = document.getElementById("bgMusic");
const toggleBtn = document.getElementById("musicToggle");

let musicOn = false;

toggleBtn.addEventListener("click", () => {
    musicOn = !musicOn;

    if (musicOn) {
        music.play();
        toggleBtn.src = "image/sonOn.jpg";
    } else {
        music.pause();
        toggleBtn.src = "image/sonOff.jpg";
    }
});
</script>
</main>
<div id="newGameAlert" style="
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.75);
    backdrop-filter: blur(4px);
    justify-content:center;
    align-items:center;
    z-index:9999;
">
    <div style="
        background:#1a1a1a;
        border:2px solid gold;
        padding:30px;
        border-radius:12px;
        text-align:center;
        width:320px;
        color:white;
        font-family: 'Agmena Pro', serif;
    ">
        <h2 style="margin-bottom:15px; color:#d4af37;">Attention</h2>
        <p style="margin-bottom:25px;">
            Vous devez vous déconnecter pour créer une nouvelle connexion.
        </p>

        <button onclick="document.getElementById('newGameAlert').style.display='none'"
            style="
                padding:10px 20px;
                margin-right:10px;
                background:#444;
                color:white;
                border:none;
                border-radius:8px;
                cursor:pointer;
            ">
            Fermer
        </button>

        <button onclick="window.location.href='logout.php'"
            style="
                padding:10px 20px;
                background:#d9534f;
                color:white;
                border:none;
                border-radius:8px;
                cursor:pointer;
            ">
            Se déconnecter
        </button>
    </div>
</div>