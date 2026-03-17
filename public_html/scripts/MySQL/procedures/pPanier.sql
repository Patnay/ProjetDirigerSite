/*
 Auteur: Patrice Paul  
 Date dernier modif: 10 mars 2026
 Exemple : TO DO
*/

DROP PROCEDURE IF EXISTS ajouterPanier;
DELIMITER |

CREATE PROCEDURE ajouterPanier(
    IN pIdJoueur   INT,
    IN pIdItem     INT
    )
BEGIN
    DECLARE vExisteJoueur INT DEFAULT 0;
    DECLARE vExisteItem   INT DEFAULT 0;
    DECLARE vStock        INT DEFAULT 0;

    /*- Validations de base -*/
    /*Quantite*/
    IF pQuantite IS NULL OR pQuantite <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantité invalide (doit être > 0).';
    END IF;

    SELECT COUNT(*) INTO vExisteJoueur
      FROM Joueurs
     WHERE idJoueur = pIdJoueur;
	/*Existance du joueur*/
    IF vExisteJoueur = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Joueur inexistant.';
    END IF;

    SELECT COUNT(*) INTO vExisteItem
      FROM Items
     WHERE idItem = pIdItem;
	/*si l'item existe*/
    IF vExisteItem = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Item inexistant.';
    END IF;

    START TRANSACTION;

        /* On verrouille la ligne de l'article si on réserve le stock, pour cohérence. */
			
            /*check si la quantiter dmd est plus que le stock*/
            IF pQuantite > vStock THEN
                ROLLBACK;
                SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Stock insuffisant pour cet item.';
            END IF;
        /* essaie d'insert un item dans le panier 
        mais si il exite deja il vas augmenter la quantier a la place*/
        INSERT INTO Paniers (idJoueur, idItem, quantitePanier)
        VALUES (pIdJoueur, pIdItem, 1)
        ON DUPLICATE KEY UPDATE quantitePanier = 1 + VALUES(quantitePanier);
		/*https://dev.mysql.com/doc/refman/8.4/en/insert.html Consultee le 10 mars 2025*/
	COMMIT;
END
|

DELIMITER ;


DROP PROCEDURE IF EXISTS payerPanier;
DELIMITER |

CREATE PROCEDURE payerPanier(
    IN pIdJoueur INT
)
BEGIN
    DECLARE vNbLignes INT DEFAULT 0;

    /*  Vérifier qu'il y a bien quelque chose à payer */
    SELECT COUNT(*) INTO vNbLignes
      FROM Paniers
     WHERE idJoueur = pIdJoueur;

    IF vNbLignes = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Panier vide.';
    END IF;
	 /*Creation de tables temporaire :https://dev.mysql.com/doc/refman/8.4/en/create-temporary-table.html Consulter 10 mars 2026*/
    START TRANSACTION;
		
        /*vérifiee les stocks  Utilisation de table temporaire pour avoir un table de de pluse pour facilite la tache */
        DROP TEMPORARY TABLE IF EXISTS tmp_panier;
        
        CREATE TEMPORARY TABLE tmp_panier
        SELECT p.idItem, p.quantitePanier
          FROM Paniers p
         WHERE p.idJoueur = pIdJoueur;  

        /* Verifie le stock pour chaque ligne (avec verrou sur Item pour avoir operation Atomique) */
        BEGIN
            DECLARE done INT DEFAULT 0;
            DECLARE vIdItem INT;
            DECLARE vQte INT;
            DECLARE vStock INT;

            DECLARE cur CURSOR FOR
                SELECT idItem, quantitePanier FROM tmp_panier;
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

            OPEN cur;
            read_loop: LOOP
                FETCH cur INTO vIdItem, vQte;
                IF done = 1 THEN LEAVE read_loop; END IF;

                /* Verrouille la ligne Items */
                SELECT quantiteStock INTO vStock
                  FROM Items
                 WHERE idItem = vIdItem
                 FOR UPDATE;

                IF vQte > vStock THEN
                    ROLLBACK;
                    SIGNAL SQLSTATE '45001' 
                    SET MESSAGE_TEXT = 'Stock insuffisant pour un item ';
                END IF;
            END LOOP;
            CLOSE cur;
        END;

        /* Diminuer le stock des Items + Mettre dans Inventaires  */
        BEGIN
            DECLARE done2 INT DEFAULT 0;
            DECLARE vIdItem2 INT;
            DECLARE vQte2 INT;

            DECLARE cur2 CURSOR FOR
                SELECT idItem, quantitePanier FROM tmp_panier;
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done2 = 1;

            OPEN cur2;
            dec_loop: LOOP
                FETCH cur2 INTO vIdItem2, vQte2;
                IF done2 = 1 THEN LEAVE dec_loop; END IF;

                /* Diminution de la quantiter dans items */
                UPDATE Items
                   SET quantiteStock = quantiteStock - vQte2
                 WHERE idItem = vIdItem2;

                /* Ajoute dans l'inventaires */
                INSERT INTO Inventaires (idJoueur, idItem, quantiteInventaire)
                VALUES (pIdJoueur, vIdItem2, vQte2)
                ON DUPLICATE KEY UPDATE quantiteInventaire = quantiteInventaire + VALUES(quantiteInventaire);
            END LOOP;
            CLOSE cur2;
        END;

        /* Vider le panier du joueur */
        DELETE FROM Paniers
         WHERE idJoueur = pIdJoueur;
         
         /*Destruction de la table temporaire*/
          DROP TEMPORARY TABLE IF EXISTS tmp_panier;

    COMMIT;
END
|
DELIMITER ;
