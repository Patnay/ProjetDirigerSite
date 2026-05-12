<?php
require_once "init.php";

/* =========================
   PROTECTION DE LA PAGE
========================= */
if (empty($_SESSION["idJoueur"])) {
    header("Location: connexion.php");
    exit;
}

$stmtAdmin = $pdo->prepare("SELECT estAdmin FROM Joueurs WHERE idJoueur = ?");
$stmtAdmin->execute([$_SESSION["idJoueur"]]);
$isAdmin = (int) $stmtAdmin->fetchColumn();

if ($isAdmin !== 1) {
    header("Location: boutique.php");
    exit;
}

/* =========================
   TRAITEMENT DU FORMULAIRE
========================= */
$message = "";
$erreur = "";

function uploadImage($fieldName)
{
    if (!isset($_FILES[$fieldName])) {
        return ["ok" => false, "error" => "Champ image introuvable dans la requête."];
    }
    $code = $_FILES[$fieldName]["error"];
    if ($code !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => "Fichier trop volumineux (limite php.ini).",
            UPLOAD_ERR_FORM_SIZE  => "Fichier trop volumineux (limite formulaire).",
            UPLOAD_ERR_PARTIAL    => "Le fichier n'a été que partiellement transféré.",
            UPLOAD_ERR_NO_FILE    => "Aucun fichier sélectionné.",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant sur le serveur.",
            UPLOAD_ERR_CANT_WRITE => "Impossible d'écrire le fichier sur le disque.",
            UPLOAD_ERR_EXTENSION  => "Upload bloqué par une extension PHP.",
        ];
        return ["ok" => false, "error" => $uploadErrors[$code] ?? "Erreur upload inconnue (code $code)."];
    }
    $ext = strtolower(pathinfo($_FILES[$fieldName]["name"], PATHINFO_EXTENSION));
    if (!in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"])) {
        return ["ok" => false, "error" => "Format invalide « $ext ». Formats acceptés : jpg, jpeg, png, gif, webp."];
    }
    $dir = __DIR__ . "/image/";
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return ["ok" => false, "error" => "Impossible de créer le dossier images/ sur le serveur."];
    }
    $filename = uniqid("item_", true) . "." . $ext;
    if (!move_uploaded_file($_FILES[$fieldName]["tmp_name"], $dir . $filename)) {
        return ["ok" => false, "error" => "Impossible de déplacer le fichier dans images/ (permission refusée?)."];
    }
    return ["ok" => true, "filename" => $filename];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $typeForm = $_POST["typeForm"] ?? "";

    if ($typeForm === "enigme" || isset($_POST["question"])) {
        $question     = trim($_POST["question"]      ?? "");
        $bonneReponse = trim($_POST["bonneReponse"]  ?? "");
        $valeurD      = trim($_POST["valeurD"]       ?? "");
        $mauvaise1    = trim($_POST["mauvaise1"]     ?? "");
        $mauvaise2    = trim($_POST["mauvaise2"]     ?? "");
        $mauvaise3    = trim($_POST["mauvaise3"]     ?? "");

        $champsVides = [];
        if ($question === "")     $champsVides[] = "Question";
        if ($valeurD === "")      $champsVides[] = "Difficulté";
        if ($bonneReponse === "") $champsVides[] = "Bonne réponse";
        if ($mauvaise1 === "")    $champsVides[] = "Mauvaise réponse 1";
        if ($mauvaise2 === "")    $champsVides[] = "Mauvaise réponse 2";
        if ($mauvaise3 === "")    $champsVides[] = "Mauvaise réponse 3";

        if (!empty($champsVides)) {
            $erreur = "Champs manquants : " . implode(", ", $champsVides) . ".";
        } else {
            try {
                $stmt = $pdo->prepare("CALL AjouterEnigme(?, 'A', ?, '0', ?, ?, ?, ?)");
                $stmt->execute([$question, $valeurD, $bonneReponse, $mauvaise1, $mauvaise2, $mauvaise3]);
                $stmt->closeCursor();
                $message = "Énigme ajoutée avec succès.";
            } catch (PDOException $e) {
                $erreur = "Impossible d'ajouter l'énigme.";
            }
        }

    } elseif ($typeForm === "arme") {
        $nom        = trim($_POST["nom"]         ?? "");
        $qtn        = (int)($_POST["qtn"]        ?? 0);
        $prix       = (int)($_POST["prix"]       ?? 0);
        $description = trim($_POST["description"] ?? "");
        $efficacite = trim($_POST["efficacite"]  ?? "");
        $genre      = $_POST["cars"]             ?? "one";

        if ($nom === "" || $description === "" || $efficacite === "") {
            $erreur = "Veuillez remplir tous les champs (nom, description, efficacité).";
        } elseif (empty($_FILES["img"]["name"])) {
            $erreur = "Veuillez choisir une image.";
        } else {
            $upload = uploadImage("img");
            if (!$upload["ok"]) {
                $erreur = "Image : " . $upload["error"];
            } else {
                try {
                    $stmt = $pdo->prepare("CALL ajouterArme(?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nom, $qtn, $prix, $upload["filename"], $description, $efficacite, $genre]);
                    $stmt->closeCursor();
                    $message = "Arme « $nom » ajoutée avec succès.";
                } catch (PDOException $e) {
                    $erreur = "Erreur BD (ajouterArme) : " . $e->getMessage();
                }
            }
        }

    } elseif ($typeForm === "sort") {
        $nom        = trim($_POST["nom"]         ?? "");
        $qtn        = (int)($_POST["qtn"]        ?? 0);
        $prix       = (int)($_POST["prix"]       ?? 0);
        $description = trim($_POST["description"] ?? "");
        $instantane = isset($_POST["instantane"]) ? 1 : 0;
        $rarete     = (int)($_POST["rarete"]     ?? 1);
        $type       = trim($_POST["type"]        ?? "");
        $healDamage = $_POST["heal/damage"]      ?? "damage";
        $points     = (int)($_POST["points"]     ?? 0);
        $pVie       = ($healDamage === "heal")   ? $points : 0;
        $pDegat     = ($healDamage === "damage") ? $points : 0;

        if ($nom === "" || $type === "" || $description === "") {
            $erreur = "Veuillez remplir tous les champs (nom, type, description).";
        } elseif (empty($_FILES["img"]["name"])) {
            $erreur = "Veuillez choisir une image.";
        } else {
            $upload = uploadImage("img");
            if (!$upload["ok"]) {
                $erreur = "Image : " . $upload["error"];
            } else {
                try {
                    $stmt = $pdo->prepare("CALL ajouterSort(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nom, $qtn, $prix, $upload["filename"], $instantane, $rarete, $type, $description, $pVie, $pDegat]);
                    $stmt->closeCursor();
                    $message = "Sort « $nom » ajouté avec succès.";
                } catch (PDOException $e) {
                    $erreur = "Erreur BD (ajouterSort) : " . $e->getMessage();
                }
            }
        }

    } elseif ($typeForm === "potion") {
        $nom   = trim($_POST["nom"]    ?? "");
        $qtn   = (int)($_POST["qtn"]  ?? 0);
        $prix  = (int)($_POST["prix"] ?? 0);
        $effet = trim($_POST["effect"] ?? "");
        $duree = (int)($_POST["time"]  ?? 0);

        if ($nom === "" || $effet === "") {
            $erreur = "Veuillez remplir tous les champs (nom, effet).";
        } elseif (empty($_FILES["img"]["name"])) {
            $erreur = "Veuillez choisir une image.";
        } else {
            $upload = uploadImage("img");
            if (!$upload["ok"]) {
                $erreur = "Image : " . $upload["error"];
            } else {
                try {
                    $stmt = $pdo->prepare("CALL ajouterPotion(?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nom, $qtn, $prix, $upload["filename"], $effet, $duree]);
                    $stmt->closeCursor();
                    $message = "Potion « $nom » ajoutée avec succès.";
                } catch (PDOException $e) {
                    $erreur = "Erreur BD (ajouterPotion) : " . $e->getMessage();
                }
            }
        }

    } elseif ($typeForm === "armure") {
        $nom    = trim($_POST["nom"]     ?? "");
        $qtn    = (int)($_POST["qtn"]   ?? 0);
        $prix   = (int)($_POST["prix"]  ?? 0);
        $matiere = trim($_POST["matiere"] ?? "");
        $taille = trim($_POST["taille"]  ?? "");

        if ($nom === "" || $matiere === "" || $taille === "") {
            $erreur = "Veuillez remplir tous les champs (nom, matière, taille).";
        } elseif (empty($_FILES["img"]["name"])) {
            $erreur = "Veuillez choisir une image.";
        } else {
            $upload = uploadImage("img");
            if (!$upload["ok"]) {
                $erreur = "Image : " . $upload["error"];
            } else {
                try {
                    $stmt = $pdo->prepare("CALL ajouterArmure(?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nom, $qtn, $prix, $upload["filename"], $matiere, $taille]);
                    $stmt->closeCursor();
                    $message = "Armure « $nom » ajoutée avec succès.";
                } catch (PDOException $e) {
                    $erreur = "Erreur BD (ajouterArmure) : " . $e->getMessage();
                }
            }
        }
    }
}

// Charger la liste des joueurs pour la section CheckUser
$joueurs = $pdo->query("SELECT idJoueur, alias, prenom, nom, ptVie, nbOr, nbArgent, nbBronze, estAdmin, estMage FROM Joueurs ORDER BY alias ASC")->fetchAll(PDO::FETCH_ASSOC);

if (($_POST["_ajax"] ?? "") === "1") {
    header('Content-Type: application/json');
    echo json_encode([
        "ok"      => $erreur === "" && $message !== "",
        "message" => $message,
        "error"   => $erreur,
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin - Ajouter une énigme</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="favicon.png">
</head>

<body>

    <?php include "header.php"; ?>

    <main class="shop-page">
        <div class="shop-container">

            <aside class="filters">
                <h2>Admin</h2>

                <div class="filter-block">
                    <p><strong>Section :</strong></p>
                    <select id="adminSection" onchange="changerSection()">
                        <option value="AddEnigme">Création d'énigmes</option>
                        <option value="AddItem">Ajouter item</option>
                        <option value="CheckUser">Surveillance Utilisateurs</option>
                    </select>
                </div>

                <div class="filter-block" id="typeItemBlock" style="display:none;">
                    <p><strong>Type d'item :</strong></p>
                    <select id="typeItem" onchange="changerTypeItem()">
                        <option value="A">Arme</option>
                        <option value="S">Sort</option>
                        <option value="P">Potion</option>
                        <option value="R">Armure</option>
                    </select>
                </div>

                <div class="filter-block" id="filtreUserBlock" style="display:none;">
                    <p><strong>Filtrer par rôle :</strong></p>
                    <select id="filtreUser" onchange="filtrerJoueurs()">
                        <option value="tous">Tous</option>
                        <option value="joueur">Joueurs</option>
                        <option value="admin">Admins</option>
                    </select>
                </div>

                <div class="filter-block">
                    <a href="boutique.php" class="reset-btn">Retour boutique</a>
                </div>
            </aside>

            <section id="mainProductGrid" class="products-grid" style="grid-template-columns:1fr; max-width:700px;">
                <div class="product-card">

                    <p id="adminMessage" style="display:none; padding:10px; border-radius:6px; font-weight:bold;"></p>

                    <div id="formAddEnigme">
                    <?php include("scripts/php/formulaire/addEnigmeForm.php"); ?>
                    </div>

                    <div id="formAddItemA" style="display:none;">
                    <?php include("scripts/php/formulaire/addItemBoutiqueA.php"); ?>
                    </div>

                    <div id="formAddItemS" style="display:none;">
                    <?php include("scripts/php/formulaire/addItemBoutiqueS.php"); ?>
                    </div>

                    <div id="formAddItemP" style="display:none;">
                    <?php include("scripts/php/formulaire/addItemBoutiqueP.php"); ?>
                    </div>

                    <div id="formAddItemR" style="display:none;">
                    <?php include("scripts/php/formulaire/addItemBoutiqueR.php"); ?>
                    </div>

                </div>
            </section>

            <!-- Section CheckUser — à droite de la sidebar, dans le shop-container -->
            <div id="formCheckUser" style="display:none; flex:1; padding:10px; min-width:0;">
            <h3 style="margin-bottom:10px;">Joueurs inscrits</h3>
                <table id="tableJoueurs" style="width:100%; border-collapse:collapse; font-size:0.85em;">
                    <thead>
                        <tr style="border-bottom:2px solid #f0c040; text-align:left; background:rgba(0,0,0,0.3);">
                            <th style="padding:6px 8px;">#</th>
                            <th style="padding:6px 8px;">Alias</th>
                            <th style="padding:6px 8px;">Nom complet</th>
                            <th style="padding:6px 8px;">Points</th>
                            <th style="padding:6px 8px;">🟡⚪🟤</th>
                            <th style="padding:6px 8px;">Rôle</th>
                            <th style="padding:6px 8px;">Classe</th>
                            <th style="padding:6px 8px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($joueurs as $j): ?>
                        <tr style="border-bottom:1px solid #444;" data-role="<?= (int)$j['estAdmin'] === 1 ? 'admin' : 'joueur' ?>">
                            <td style="padding:6px 8px;"><?= (int)$j['idJoueur'] ?></td>
                            <td style="padding:6px 8px;"><?= htmlspecialchars($j['alias']) ?></td>
                            <td style="padding:6px 8px; white-space:nowrap;"><?= htmlspecialchars($j['prenom'] . ' ' . $j['nom']) ?></td>
                            <td style="padding:6px 8px; text-align:center;"><?= (int)$j['ptVie'] ?></td>
                            <td style="padding:6px 8px; white-space:nowrap;">
                                🟡<?= (int)$j['nbOr'] ?> ⚪<?= (int)$j['nbArgent'] ?> 🟤<?= (int)$j['nbBronze'] ?>
                            </td>
                            <td style="padding:6px 8px; white-space:nowrap;">
                                <?= (int)$j['estAdmin'] === 1 ? '🛡️ Admin' : 'Joueur' ?>
                            </td>
                            <td style="padding:6px 8px; white-space:nowrap;">
                                <?= (int)$j['estMage'] === 1 ? '🧙 Mage' : 'Guerrier' ?>
                            </td>
                            <td style="padding:6px 4px;">
                                <div style="display:flex; flex-direction:column; gap:4px; align-items:flex-start;">
                                    <a href="inventaire.php?joueur=<?= (int)$j['idJoueur'] ?>"
                                       class="filter-btn" style="padding:4px 10px; font-size:0.85em; white-space:nowrap;">
                                        Inventaire
                                    </a>
                                    <?php if((int)$j['estAdmin'] === 0): ?>
                                    <a href="deleteJoueurAdmin.php?joueur=<?= (int)$j['idJoueur'] ?>"
                                       class="filter-btn" style="padding:4px 10px; font-size:0.85em; white-space:nowrap; background:#7a1a1a;">
                                        Supprimer
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
        </div>

        </div><!-- fin shop-container -->

    </main>
    <!-- Bouton musique -->
    <img id="musicToggle" src="image/sonOff.jpg" style="
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
        cursor: pointer;
        z-index: 9999;
     ">
    <audio id="bgMusic" loop>
        <source src="musique/FinaleUndertale.mp3" type="audio/mp3">
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

    <script>
        const allForms = ["formAddEnigme", "formAddItemA", "formAddItemS", "formAddItemP", "formAddItemR", "formCheckUser"];

        function changerSection() {
            const section          = document.getElementById("adminSection").value;
            const typeItemBlock    = document.getElementById("typeItemBlock");
            const filtreUserBlock  = document.getElementById("filtreUserBlock");
            const mainGrid         = document.getElementById("mainProductGrid");
            const checkUserBlock   = document.getElementById("formCheckUser");

            // Cacher tous les formulaires et le bloc CheckUser
            allForms.filter(id => id !== "formCheckUser")
                    .forEach(id => document.getElementById(id).style.display = "none");
            checkUserBlock.style.display  = "none";
            typeItemBlock.style.display   = "none";
            filtreUserBlock.style.display = "none";

            if (section === "AddEnigme") {
                mainGrid.style.display = "";
                document.getElementById("formAddEnigme").style.display = "";
            } else if (section === "AddItem") {
                mainGrid.style.display      = "";
                typeItemBlock.style.display = "";
                changerTypeItem();
            } else if (section === "CheckUser") {
                mainGrid.style.display        = "none";
                checkUserBlock.style.display  = "";
                filtreUserBlock.style.display = "";
                document.getElementById("filtreUser").value = "tous";
                filtrerJoueurs();
            } else {
                mainGrid.style.display = "";
            }
            document.getElementById("adminMessage").textContent = "";
            document.getElementById("adminMessage").className = "";
        }

        function filtrerJoueurs() {
            const filtre = document.getElementById("filtreUser").value;
            document.querySelectorAll("#tableJoueurs tbody tr").forEach(row => {
                const role = row.dataset.role; // "admin" ou "joueur"
                row.style.display = (filtre === "tous" || filtre === role) ? "" : "none";
            });
        }

        function changerTypeItem() {
            const type = document.getElementById("typeItem").value;
            const map = { A: "formAddItemA", S: "formAddItemS", P: "formAddItemP", R: "formAddItemR" };
            ["formAddItemA", "formAddItemS", "formAddItemP", "formAddItemR"]
                .forEach(id => document.getElementById(id).style.display = "none");
            document.getElementById(map[type]).style.display = "";
            document.getElementById("adminMessage").textContent = "";
            document.getElementById("adminMessage").className = "";
        }

        function afficherMessage(texte, succes) {
            const msgEl = document.getElementById("adminMessage");
            msgEl.textContent = texte;
            msgEl.style.display = "block";
            if (succes) {
                msgEl.style.background = "#1a4a1a";
                msgEl.style.color      = "#7eff7e";
                msgEl.style.border     = "1px solid #7eff7e";
            } else {
                msgEl.style.background = "#4a1a1a";
                msgEl.style.color      = "#ff7e7e";
                msgEl.style.border     = "1px solid #ff7e7e";
            }
        }

        document.querySelectorAll(".profile-form").forEach(form => {
            form.addEventListener("submit", async function (e) {
                e.preventDefault();

                const sectionSel  = document.getElementById("adminSection").value;
                const typeItemSel = document.getElementById("typeItem")?.value ?? null;

                const data = new FormData(this);
                data.append("_ajax", "1");

                
                const parentDiv = this.closest("div[id^='formAdd']");
                const typeMap = {
                    formAddEnigme : "enigme",
                    formAddItemA  : "arme",
                    formAddItemS  : "sort",
                    formAddItemP  : "potion",
                    formAddItemR  : "armure",
                };
                const typeForm = parentDiv ? (typeMap[parentDiv.id] ?? "") : "";
                data.set("typeForm", typeForm);

                afficherMessage("Envoi en cours…", true);

                try {
                    const res  = await fetch("admin.php", { method: "POST", body: data });
                    const text = await res.text();

                    let json;
                    try {
                        json = JSON.parse(text);
                    } catch (_) {
                        afficherMessage("Erreur serveur PHP : " + text.substring(0, 300), false);
                        return;
                    }

                    if (json.ok) {
                        afficherMessage(json.message, true);
                        this.reset();
                    } else {
                        afficherMessage(json.error || "Erreur inconnue.", false);
                    }
                } catch (err) {
                    afficherMessage("Erreur réseau : " + err.message, false);
                }

                document.getElementById("adminSection").value = sectionSel;
                changerSection();
                if (typeItemSel) {
                    document.getElementById("typeItem").value = typeItemSel;
                    changerTypeItem();
                }
            });
        });
    </script>

    <!-- Bouton Mario -->
    <img id="marioBtn" src="image/champignon.png" style="
        position: fixed;
        bottom: 20px;
        left: 20px;
        width: 60px;
        height: 60px;
        cursor: pointer;
        z-index: 9999;
     ">
    <script>
        document.getElementById("marioBtn").addEventListener("click", () => {
            window.location.href = "mario2.html";
        });
    </script>
</body>

</html>