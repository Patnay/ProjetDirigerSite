/*
Auteur : Patrice Paul 
Derniere maj : 6 avril
*/

/*==== Devenir Maj======*/
DELIMITER $$

CREATE TRIGGER trg_devientMage
AFTER UPDATE ON Statistiques
FOR EACH ROW
BEGIN
    IF NEW.nbEnigmesMage >= 3 THEN
        UPDATE Joueurs
        SET estMage = 1
        WHERE idJoueur = NEW.idJoueur;
    END IF;
END $$

DELIMITER ;

/*====== Bonus de streak =======*/

DELIMITER $$

CREATE TRIGGER trg_bonusStreak
AFTER UPDATE ON Statistiques
FOR EACH ROW
BEGIN
    -- Streak 3 -> bonus argent
    IF NEW.streak >= 3 AND OLD.streak < 3 THEN
        UPDATE Joueurs
        SET nbArgent = nbArgent + 50
        WHERE idJoueur = NEW.idJoueur;
    END IF;

    -- Streak 5 -> bonus or
    IF NEW.streak >= 5 AND OLD.streak < 5 THEN
        UPDATE Joueurs
        SET nbOr = nbOr + 2
        WHERE idJoueur = NEW.idJoueur;
    END IF;
    -- Ajouter ce que on vas parler en team ***********************************

END $$

DELIMITER ;

-- Comment augmenter sa steaks :
UPDATE Statistiques
SET 
    streak = streak + 1,
    nbEnigmesMage = nbEnigmesMage + 1
WHERE idJoueur = 1;
-- Remmetre a 0
UPDATE Statistiques
SET 
    streak = streak + 1,
    nbEnigmesMage = nbEnigmesMage + 1
WHERE idJoueur = 1;