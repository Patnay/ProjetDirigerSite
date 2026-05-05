<h3>Ajouter une énigme</h3>
<form action="admin.php" method="POST" class="profile-form">
    <input type="hidden" name="typeForm" value="enigme">

    <label for="question">Question :</label>
    <input type="text" id="question" name="question" required value="<?= htmlspecialchars($_POST["question"] ?? "") ?>">
    <label for="valeurD">Difficulter Lettre (F,M,D,A) :</label>
    <input type="text" id="valeurD" name="valeurD" required value="<?= htmlspecialchars($_POST["valeurD"] ?? "") ?>">
    <label for="bonneReponse">Mauvaise réponse 1 :</label>
    <input type="text" id="bonneReponse" name="bonneReponse" required
        value="<?= htmlspecialchars($_POST["bonneReponse"] ?? "") ?>">

    <label for="mauvaise1">Mauvaise réponse 2 :</label>
    <input type="text" id="mauvaise1" name="mauvaise1" required
        value="<?= htmlspecialchars($_POST["mauvaise1"] ?? "") ?>">

    <label for="mauvaise2">Mauvaise réponse 3 :</label>
    <input type="text" id="mauvaise2" name="mauvaise2" required
        value="<?= htmlspecialchars($_POST["mauvaise2"] ?? "") ?>">

    <label for="mauvaise3">Bonne réponse :</label>
    <input type="text" id="mauvaise3" name="mauvaise3" required
        value="<?= htmlspecialchars($_POST["mauvaise3"] ?? "") ?>">

    <button type="submit" class="filter-btn" style="width:100%;">
        Ajouter l'énigme
    </button>
</form>