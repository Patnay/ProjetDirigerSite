<h3>Ajouter un Sort</h3>
<form action="admin.php" method="POST" enctype="multipart/form-data" class="profile-form">
    <input type="hidden" name="typeForm" value="sort">

    <label for="nom">Nom</label>
    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST["name"] ?? "") ?>">
    <label for="qtn">Quantité :</label>
    <input type="number" id="qtn" name="qtn" required value="<?= htmlspecialchars($_POST["qtn"] ?? "") ?>">
    <label for="img">Affiche</label>
    <input type="file" id="img" name="img" required value="<?= htmlspecialchars($_POST["img"] ?? "") ?>">
    <label for="prix">Prix</label>
    <input type="number" id="prix" name="prix" required value="<?= htmlspecialchars($_POST["prix"] ?? "") ?>">
    <label for="description">Description</label>
    <input type="text" id="description" name="description" required
        value="<?= htmlspecialchars($_POST["description"] ?? "") ?>">

    <label for="instantane">Instantané:</label>
    <input type="checkbox" name="instantane" id="instantane">
    <label for="rarete">Rareté:</label>
    <input type="number" max="3" min="1" name="rarete">
    <label for="type">Type:</label>
    <input type="text" name="type" id="type" maxlength="1" minlength="1">
    <select name="heal/damage" id="heal/damage">
        <option value="heal">Damage</option>
        <option value="damage">Heal</option>
    </select>
    <label for="points">Points de l'effect</label>
    <input type="number" name="points">



    <button type="submit" class="filter-btn" style="width:100%;">
        Publier le Sort
    </button>
</form>