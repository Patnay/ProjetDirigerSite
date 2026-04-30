<h3>Ajouter une Arme</h3>
<form action="admin.php" method="POST" class="profile-form">

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

    <label for="efficacite">Éfficacité:</label>
    <input type="text" id="efficacite" name="efficacite" required
        value="<?= htmlspecialchars($_POST["efficacite"] ?? "") ?>">

      
    <label for="twoHanded">Two Handed weapon?</label>
<select id="cars" name="cars">
  <option value="one">One Handed weapon</option>
  <option value="two">Two Handed weapon</option>
</select>

    <button type="submit" class="filter-btn" style="width:100%;">
        Publier l'Arme
    </button>