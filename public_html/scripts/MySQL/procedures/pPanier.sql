/*
 Auteur: Patrice Paul  
 Date dernier modif: 6 avri 2026
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


/* ========= Vendre Items ============= */
USE dbdarquest6;
DROP PROCEDURE IF EXISTS vendreItem;
DELIMITER $$

CREATE PROCEDURE vendreItem(
    IN p_idJoueur INT,
    IN p_idItem INT,
    IN p_qtVente INT
)
BEGIN
    DECLARE v_prix INT;
    DECLARE v_typeItem CHAR(1);
    DECLARE v_rareté TINYINT;
    DECLARE v_prixVente DECIMAL(10,2);
    DECLARE v_qtInv INT;

    DECLARE v_or INT;
    DECLARE v_argent INT;
    DECLARE v_bronze INT;

    -- Gestion erreur
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    -- Est-ce que le joueurs le prossede ?
    IF NOT EXISTS (
        SELECT 1 FROM Inventaires 
        WHERE idJoueur = p_idJoueur AND idItem = p_idItem
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Erreur : item non possédé';
    END IF;

    -- Infos item
    SELECT prix, typeItem
    INTO v_prix, v_typeItem
    FROM Items
    WHERE idItem = p_idItem;
    
    SELECT quantiteInventaire INTO v_qtInv
    FROM Inventaires
    WHERE idItem = p_idItem AND idJoueur = p_idJoueur;

    -- isSort ?? getRareter for the price : 60%
    IF v_typeItem = 'S' THEN
        SELECT rarete INTO v_rareté
        FROM Sorts
        WHERE idItem = p_idItem;

        CASE v_rareté
            WHEN 1 THEN SET v_prixVente = v_prix * 1.00 * p_qtVente;
            WHEN 2 THEN SET v_prixVente = v_prix * 0.95 * p_qtVente;
            WHEN 3 THEN SET v_prixVente = v_prix * 0.90 * p_qtVente;
            ELSE SET v_prixVente = v_prix * 0.60 * p_qtVente;
        END CASE;
    ELSE
        SET v_prixVente = v_prix * 0.60 * p_qtVente;
    END IF;

    -- Separer or/argent/bronze
    SET v_or = FLOOR(v_prixVente) ;
    SET v_argent = FLOOR((v_prixVente - v_or) * 100);
    SET v_bronze = ROUND((((v_prixVente - v_or) * 100) - v_argent) * 100);
    -- Ajouter gains
    UPDATE Joueurs
    SET 
        nbOr = nbOr + v_or ,
        nbArgent = nbArgent + v_argent,
        nbBronze = nbBronze + v_bronze
    WHERE idJoueur = p_idJoueur;

    -- Retirer inventaire
    IF p_qtVente = v_qtInv THEN
        BEGIN
            DELETE FROM Inventaires
            WHERE idJoueur = p_idJoueur AND idItem = p_idItem;
        END;
    END IF;
    IF p_qtVente < v_qtInv THEN
        BEGIN
            UPDATE Inventaires
            SET quantiteInventaire = quantiteInventaire - p_qtVente
            WHERE idJoueur = p_idJoueur AND idItem = p_idItem;
        END;
    END IF;


    -- Remettre dans le stock
    UPDATE Items
    SET quantiteStock = quantiteStock + p_qtVente
    WHERE idItem = p_idItem;

    COMMIT;

   

END $$

DELIMITER ;

-- Exemple d'appel 
CALL vendreItem(
    13, -- idJoueur
    62, -- idItem
    1 -- qtVente
    );
SELECT * FROM Joueurs WHERE idJoueur = 13;
SELECT * FROM Inventaires WHERE idJoueur = 13 AND idItem = 62;
SELECT * FROM Items WHERE idItem = 62;