<h3>Ajouter une Potion</h3>
<form action="admin.php" method="POST" enctype="multipart/form-data" class="profile-form">
    <input type="hidden" name="typeForm" value="potion">

    <label for="nom">Nom</label>
    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST["name"] ?? "") ?>">
    <label for="qtn">Quantité :</label>
    <input type="number" id="qtn" name="qtn" required value="<?= htmlspecialchars($_POST["qtn"] ?? "") ?>">
    
    <label for="prix">Prix</label>
    <input type="number" id="prix" name="prix" required value="<?= htmlspecialchars($_POST["prix"] ?? "") ?>">

    <label for="img">Affiche</label>
    <input type="file" id="img" name="img" required
        value="<?= htmlspecialchars($_POST["img"] ?? "") ?>">

    <label for="description">Description</label>
    <input type="text" id="description" name="description" required
        value="<?= htmlspecialchars($_POST["description"] ?? "") ?>">

    <label for="effect">Effect</label>
    <input type="text" id="effect" name="effect" required
        value="<?= htmlspecialchars($_POST["effect"] ?? "") ?>">

         <label for="time">Temps (en sec):</label>
    <input type="number" id="time" name="time" required
        value="<?= htmlspecialchars($_POST["matiere"] ?? "") ?>">

      

    <button type="submit" class="filter-btn" style="width:100%;">
        Publier la Potions
    </button>
</form>