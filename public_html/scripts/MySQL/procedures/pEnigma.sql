/*
 Auteur : Patrice Paul
 Deriere maj : 13  avril 2026 
*/

/* ============Ajouter Enigme============== */
-- FONCTIONNE
DROP PROCEDURE IF EXISTS AjouterEnigme ;
DELIMITER $$

CREATE PROCEDURE AjouterEnigme(
    -- Infos énigme
    IN p_enonce VARCHAR(300),
    IN p_idCategorie CHAR(1),
    IN p_difficulte CHAR(1),
    IN p_estPiege TINYINT,

    -- Reponses (la derniere est la bonne)
    IN p_rep1 VARCHAR(45),
    IN p_rep2 VARCHAR(45),
    IN p_rep3 VARCHAR(45),
    IN p_bonneRep VARCHAR(45)
)
BEGIN
    DECLARE v_idEnigme INT;

    -- erreur
   

    START TRANSACTION;

    -- Verifie si la categorie existe
    IF NOT EXISTS (SELECT 1 FROM Categories WHERE idCategorie = p_idCategorie) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Erreur : catégorie inexistante';
    END IF;

    -- Insert énigme
    INSERT INTO Enigmes (enonce, idCategorie, difficulte, estPiege)
    VALUES (p_enonce, p_idCategorie, p_difficulte, p_estPiege);

    -- Recuperer l'id generer
    SET v_idEnigme = LAST_INSERT_ID();

    -- Insert reponses
    INSERT INTO Reponses (reponse, estBonneReponse, idEnigme)
    VALUES 
        (p_rep1, 0, v_idEnigme),
        (p_rep2, 0, v_idEnigme),
        (p_rep3, 0, v_idEnigme),
        (p_bonneRep, 1, v_idEnigme);

    COMMIT;

    -- Retourne l'id pour confirmation
    SELECT v_idEnigme AS idNouvelleEnigme;

END $$

DELIMITER ;

-- Exemple d'appel
CALL AjouterEnigme(
    'Qui est jaune et qui att?', -- question
    'A', -- idCategorie 
    'M', -- difficulter
    0, -- est piger a Parler psq jsp cetait pk mais cetait dans le model du prof
    'Un chien', -- mauvaise rep
    'Bob', -- mauvaise rep
    'Gilenne Gagnion', -- mauvaise rep
    'Jaunathan'-- la bonne reponse (oui js psq comment ecrire ce vieux nom)
    );
    SELECT * FROM Enigmes;
    SELECT * FROM Reponses;

/* ============= GainOrEnigme ======= */
-- FONCTIONNE
DELIMITER $$

CREATE PROCEDURE GainsOrEnigme(
    IN p_idJoueur INT,
    IN p_nbOr INT,
    IN p_nbArgent INT,
    IN p_nbBronze INT
)
BEGIN
    -- Gestion d'erreur
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    -- Joueur existe ?
    IF NOT EXISTS (SELECT 1 FROM Joueurs WHERE idJoueur = p_idJoueur) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Erreur : joueur inexistant';
    END IF;

    -- Update
    UPDATE Joueurs
    SET 
        nbOr = nbOr + p_nbOr,
        nbArgent = nbArgent + p_nbArgent,
        nbBronze = nbBronze + p_nbBronze
    WHERE idJoueur = p_idJoueur;

    COMMIT;

END $$

DELIMITER ;
-- Exemple d'appel 
CALL GainsOrEnigme(13, 2000, 5, 10);
SELECT * FROM Joueurs WHERE idJoueur = 13 LIMIT 1;

/* ========= Perdre PV    =============*/
-- FONCTIONNE
DROP PROCEDURE IF EXISTS PerdreVieEnigme ;
DELIMITER $$

CREATE PROCEDURE PerdreVieEnigme(
    IN p_idJoueur INT,
    IN p_idEnigme INT
)
BEGIN
    DECLARE v_difficulte CHAR(1);
    DECLARE v_perte INT;

    -- Récupérer difficulté
    SELECT difficulte INTO v_difficulte
    FROM Enigmes
    WHERE idEnigme = p_idEnigme;

    -- Déterminer perte
    CASE v_difficulte
        WHEN 'F' THEN SET v_perte = 3;
        WHEN 'M' THEN SET v_perte = 6;
        WHEN 'D' THEN SET v_perte = 10;
        ELSE SET v_perte = 0;
    END CASE;

    -- Appliquer perte (éviter négatif)
    UPDATE Joueurs
    SET ptVie = GREATEST(0, ptVie - v_perte)
    WHERE idJoueur = p_idJoueur;

END $$
-- Exemple d'appel 
CALL PerdreVieEnigme(
    13, -- idJoueurs
    1  -- idEnigme
    );
SELECT * FROM Joueurs WHERE idJoueur = 13 LIMIT 1;
/*========== Gains PV ==================*/
-- Pour tester dois insert des items de soins
DROP PROCEDURE IF EXISTS UtiliserItemSoin ;
DELIMITER $$

CREATE PROCEDURE UtiliserItemSoin(
    IN p_idJoueur INT,
    IN p_idItem INT
)
BEGIN
    DECLARE v_typeItem CHAR(1);
    DECLARE v_soin INT;

    -- Verifier possession
    IF NOT EXISTS (
        SELECT 1 FROM Inventaires
        WHERE idJoueur = p_idJoueur AND idItem = p_idItem
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Item non possédé';
    END IF;

    -- GetTypeItem
    SELECT typeItem INTO v_typeItem
    FROM Items
    WHERE idItem = p_idItem;

    -- Potion
    IF v_typeItem = 'P' THEN
        SET v_soin = 5;

    -- Sort
    ELSEIF v_typeItem = 'S' THEN
        SELECT puissance INTO v_soin
        FROM Sorts
        WHERE idItem = p_idItem;

    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Item non utilisable pour soin';
    END IF;

    -- Appliquer soin
    UPDATE Joueurs
    SET pointsVie = pointsVie + v_soin
    WHERE idJoueur = p_idJoueur;

    -- Utilise l'item(diminue/supp dans inv joueur)
    DELETE FROM Inventaires
    WHERE idJoueur = p_idJoueur AND idItem = p_idItem
    LIMIT 1;

END $$

DELIMITER ;
-- Exemple d'appel 
CALL UtiliserItemSoin(
    1, -- idJoueurs
    12  -- idEnigme
    );


/* Repondre enigme*/
/* Fonctionne mais ne permet pas au joueurs de pouvoir refaire un enigme ...
    Doit modifier la table stats pour ajouter cette feature 
*/
USE dbdarquest6;
DROP PROCEDURE IF EXISTS RepondreEnigme;
DELIMITER $$

CREATE PROCEDURE RepondreEnigme(
    IN p_idJoueur INT,
    IN p_idEnigme INT,
    IN p_idReponse INT,
    OUT p_estBonne TINYINT
)
BEGIN
    DECLARE v_difficulte CHAR(1);
    DECLARE v_or INT DEFAULT 0;

    -- Vérifier bonne réponse
    SELECT estBonneReponse INTO p_estBonne
FROM Reponses
WHERE idReponse = p_idReponse AND idEnigme = p_idEnigme;

-- Ajouter ceci
IF p_estBonne IS NULL THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Erreur : réponse inexistante pour cette énigme';
END IF;

    -- Récuperer difficulté
    SELECT difficulte INTO v_difficulte
    FROM Enigmes WHERE idEnigme = p_idEnigme;

    -- Enregistrer dans Statistiques
    INSERT INTO Statistiques (idJoueur, idEnigme, estReussi)
    VALUES (p_idJoueur, p_idEnigme, p_estBonne);

    IF p_estBonne = 1 THEN
        -- Or selon difficulté
        CASE v_difficulte
            WHEN 'F' THEN SET v_or = 10;
            WHEN 'M' THEN SET v_or = 25;
            WHEN 'D' THEN SET v_or = 50;
            WHEN 'A' THEN SET v_or = 50;
        END CASE;

        CALL GainsOrEnigme(p_idJoueur, v_or, 0, 0);

        -- Incrémenter streak + nbEnigmesMage si question mage
        IF v_difficulte = 'A' THEN
            UPDATE Joueurs
            SET streak = streak + 1,
                nbEnigmesMage = nbEnigmesMage + 1
            WHERE idJoueur = p_idJoueur;
        ELSE
            UPDATE Joueurs
            SET streak = streak + 1
            WHERE idJoueur = p_idJoueur;
        END IF;
    ELSE
        CALL PerdreVieEnigme(p_idJoueur, p_idEnigme);

        -- Réinitialiser streak (nbEnigmesMage ne reset pas)
        UPDATE Joueurs
        SET streak = 0
        WHERE idJoueur = p_idJoueur;
    END IF;
END $$

DELIMITER ;

-- Appel
CALL RepondreEnigme(
	13,-- idJoueur
	9, -- idEnigme 
	34, -- idReponse
    @output -- Output
);

DELETE FROM Statistiques where idJoueur = 13;
SELECT @output;

/* Get random non used enigmes*/ 
DROP FUNCTION IF EXISTS EnigmeAleatoire;
DELIMITER $$

CREATE FUNCTION EnigmeAleatoire(
    p_idJoueur INT,
    p_difficulte CHAR(1)
)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE v_idEnigme INT;

    SELECT e.idEnigme INTO v_idEnigme
    FROM Enigmes e
    WHERE 
        -- Doit avoir des réponses
        EXISTS (
            SELECT 1 FROM Reponses r
            WHERE r.idEnigme = e.idEnigme
        )
        -- Jamais répondu
        AND NOT EXISTS (
            SELECT 1 FROM Statistiques s
            WHERE s.idJoueur = p_idJoueur 
            AND s.idEnigme = e.idEnigme
        )
        AND (p_difficulte = 'X' OR e.difficulte = p_difficulte)
    ORDER BY RAND()
    LIMIT 1;

    RETURN v_idEnigme;
END $$

DELIMITER ;

-- Vérifier les enigmes valides
SELECT e.idEnigme, e.enonce, COUNT(r.idReponse) AS nbReponses
FROM Enigmes e
LEFT JOIN Reponses r ON r.idEnigme = e.idEnigme
GROUP BY e.idEnigme;

/* Get enigme*/
/*Explication : En gros , la procedure prend le id joueurs avec la dificulter desirer et 
                                       donne un question avec les reponse deja melanger*/
DROP PROCEDURE IF EXISTS AfficherEnigmeJoueur;
DELIMITER $$

CREATE PROCEDURE AfficherEnigmeJoueur(
    IN p_idJoueur INT,
    IN p_difficulte CHAR(1)
)
BEGIN
    DECLARE v_idEnigme INT;

    SET v_idEnigme = EnigmeAleatoire(p_idJoueur, p_difficulte);

    IF v_idEnigme IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Aucune enigme disponible';
    END IF;

    -- Question + reponses dans un seul result set
    SELECT NULL AS idReponse, enonce AS contenu, 'QUESTION' AS type
    FROM Enigmes WHERE idEnigme = v_idEnigme

    UNION ALL

    SELECT idReponse, reponse AS contenu, 'REPONSE' AS type
    FROM Reponses
    WHERE idEnigme = v_idEnigme
    ORDER BY type DESC, RAND();

END $$

DELIMITER ;

-- Appel
CALL AfficherEnigmeJoueur(13, 'X');