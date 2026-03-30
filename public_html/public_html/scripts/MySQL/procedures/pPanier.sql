/*
 Auteur: Patrice Paul  
 Date dernier modif: 18 mars 2026
 Exemple : 
 Pour ajouterPanier =>  CALL ajouterPanier(idJoueur,idItem);

*/

DROP PROCEDURE IF EXISTS ajouterPanier;
DELIMITER |

CREATE PROCEDURE ajouterPanier(
    IN pIdJoueur   INT,
    IN pIdItem     INT
    )
BEGIN
    DECLARE vStock        INT DEFAULT 0;
    DECLARE vQuantite  INT ;
    
    SELECT quantitePanier INTO vQuantite FROM Paniers WHERE idItem = pIdItem AND idJoueur = pIdJoueur;
	SELECT quantiteStock INTO vStock FROM Items WHERE idItem = pIdItem;
    START TRANSACTION;
        /* On verrouille la ligne de l'article si on réserve le stock, pour cohérence. */
			
            /*check si la quantiter dmd est plus que le stock*/
            IF vQuantite + 1 > vStock THEN
                ROLLBACK;
                SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Stock insuffisant pour cet item.';
            END IF;
        /* essaie d'insert un item dans le panier 
        mais si il exite deja il vas augmenter la quantier a la place*/
        INSERT INTO Paniers (idJoueur, idItem, quantitePanier)
        VALUES (pIdJoueur, pIdItem, 1)
        ON DUPLICATE KEY UPDATE quantitePanier = quantitePanier + 1;
		/*https://dev.mysql.com/doc/refman/8.4/en/insert.html Consultee le 10 mars 2025*/
	COMMIT;
END
|

USE dbdarquest6;
DROP PROCEDURE IF EXISTS payerPanier;

DELIMITER $$

CREATE PROCEDURE payerPanier(IN pIdJoueur INT)
BEGIN
    /*------------- DECLARE -----------------*/
    DECLARE vNbLignes INT DEFAULT 0;
    DECLARE vPrixTotal INT;

    DECLARE vOr INT;
    DECLARE vArgent INT;
    DECLARE vBronze INT;

    DECLARE vResteOr INT;

    DECLARE done INT DEFAULT 0;
    DECLARE vIdItem INT;
    DECLARE vQte INT;
    DECLARE vStock INT;

    DECLARE vIdItem2 INT;
    DECLARE vQte2 INT;

    DECLARE cur CURSOR FOR
        SELECT idItem, quantitePanier FROM tmp_panier;

    DECLARE cur2 CURSOR FOR
        SELECT idItem, quantitePanier FROM tmp_panier;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

 /*------------CHECK PANIER-------------------*/
    SELECT COUNT(*) INTO vNbLignes
    FROM Paniers
    WHERE idJoueur = pIdJoueur;

    IF vNbLignes = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Panier vide.';
    END IF;

    START TRANSACTION;

    /* ----------TABLE TEMPORAIRE-------- */
	/*Creation de tables temporaire :https://dev.mysql.com/doc/refman/8.4/en/create-temporary-table.html Consulter 10 mars 2026 et 16 mars 2026*/
    DROP TEMPORARY TABLE IF EXISTS tmp_panier;

    CREATE TEMPORARY TABLE tmp_panier
    SELECT idItem, quantitePanier
    FROM Paniers
    WHERE idJoueur = pIdJoueur;

    /* --------VÉRIF STOCK -----------*/
    SET done = 0;
    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO vIdItem, vQte;
        IF done THEN LEAVE read_loop; END IF;

        SELECT quantiteStock INTO vStock
        FROM Items
        WHERE idItem = vIdItem
        FOR UPDATE;

        IF vQte > vStock THEN
            ROLLBACK;
            SIGNAL SQLSTATE '45001'
            SET MESSAGE_TEXT = 'Stock insuffisant';
        END IF;
    END LOOP;

    CLOSE cur;

    /*------------- UPDATE Items + Inventaires---------------- */
    SET done = 0;
    OPEN cur2;

    dec_loop: LOOP
        FETCH cur2 INTO vIdItem2, vQte2;
        IF done THEN LEAVE dec_loop; END IF;

        UPDATE Items
        SET quantiteStock = quantiteStock - vQte2
        WHERE idItem = vIdItem2;

        INSERT INTO Inventaires (idJoueur, idItem, quantiteInventaire)
        VALUES (pIdJoueur, vIdItem2, vQte2)
        ON DUPLICATE KEY UPDATE
        quantiteInventaire = quantiteInventaire + VALUES(quantiteInventaire);

    END LOOP;

    CLOSE cur2;
	/*Calculation prix*/
    /* PRIX TOTAL (nbOR) */
    SELECT SUM(i.prix * p.quantitePanier)
    INTO vPrixTotal
    FROM Items i
    JOIN Paniers p ON i.idItem = p.idItem
    WHERE p.idJoueur = pIdJoueur;

    IF vPrixTotal IS NULL THEN
        SET vPrixTotal = 0;
    END IF;

    /*Monnaie dans la bourse du joueur */
    SELECT nbOr, nbArgent, nbBronze
    INTO vOr, vArgent, vBronze
    FROM Joueurs
    WHERE idJoueur = pIdJoueur
    FOR UPDATE;

    /*CHECK fonds*/
    IF (vOr * 100 + vArgent * 10 + vBronze) < (vPrixTotal * 100) THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45002'
        SET MESSAGE_TEXT = 'Fonds insuffisants';
    END IF;

    /*Payment selon fond disponible */
    IF vOr >= vPrixTotal THEN

        /* paiement direct en or */
        SET vOr = vOr - vPrixTotal;

    ELSE

        /* utiliser tout l'or */
        SET vResteOr = vPrixTotal - vOr;
        SET vOr = 0;

        /* convertir en argent */
        SET vResteOr = vResteOr * 10;

        IF vArgent >= vResteOr THEN

            SET vArgent = vArgent - vResteOr;

        ELSE

            /* utiliser tout l'argent */
            SET vResteOr = vResteOr - vArgent;
            SET vArgent = 0;

            /* convertir en bronze */
            SET vResteOr = vResteOr * 10;

            IF vBronze >= vResteOr THEN

                SET vBronze = vBronze - vResteOr;

            ELSE
                ROLLBACK;
                SIGNAL SQLSTATE '45002'
                SET MESSAGE_TEXT = 'Fonds insuffisants';
            END IF;

        END IF;

    END IF;

    /*UPDATE Joueurs */
    UPDATE Joueurs
    SET nbOr = vOr,
        nbArgent = vArgent,
        nbBronze = vBronze
    WHERE idJoueur = pIdJoueur;

    /*VIDER Paniers*/
    DELETE FROM Paniers
    WHERE idJoueur = pIdJoueur;

    DROP TEMPORARY TABLE IF EXISTS tmp_panier;

    COMMIT;

END$$

DELIMITER ;
