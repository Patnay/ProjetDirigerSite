/*
Auteur : Patrice Paul
Derniere maj : 14 avril 2026
*/

DROP TRIGGER IF EXISTS trg_devientMage;
DROP TRIGGER IF EXISTS trg_bonusStreak;
DROP TRIGGER IF EXISTS trg_BonusStreak_Mage;

/*
    Trigger unique BEFORE UPDATE sur Joueurs.
    BEFORE permet de modifier NEW directement sans faire
    un UPDATE sur la meme table (ce qui causerait une boucle infinie).

    Conditions :
      streak  3  -> +10 or
      streak  5  -> +20 or
      streak 10  -> +100 or
      nbEnigmesMage >= 3 -> estMage = 1
*/
DELIMITER $$

CREATE TRIGGER trg_BonusStreak_Mage
BEFORE UPDATE ON Joueurs
FOR EACH ROW
BEGIN
    -- Bonus streak or (seulement au moment ou le seuil est atteint)
    IF NEW.streak = 3 AND OLD.streak < 3 THEN
        SET NEW.nbOr = NEW.nbOr + 10;
    END IF;

    IF NEW.streak = 5 AND OLD.streak < 5 THEN
        SET NEW.nbOr = NEW.nbOr + 20;
    END IF;

    IF NEW.streak = 10 AND OLD.streak < 10 THEN
        SET NEW.nbOr = NEW.nbOr + 100;
    END IF;

    -- Devenir mage quand nbEnigmesMage atteint 3
    IF NEW.nbEnigmesMage >= 3 AND OLD.estMage = 0 THEN
        SET NEW.estMage = 1;
    END IF;
END $$

DELIMITER ;
