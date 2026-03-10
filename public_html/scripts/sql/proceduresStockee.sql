
/*==== DONNEE PAR LE PROF ===*/

/*--- Ajouter Item*/
delimiter | 
CREATE  PROCEDURE ajouterSort( 
in pNom varchar(45), 
in pQuantite int, 
in pPrix int, 
in pPhoto varchar(100), 
in pInstantane tinyint, 
in prarete tinyint, 
in ptype char(1)) 
begin 
declare pTypeItem char(1) default 'S'; 
declare pidItem int; 
start transaction; 
insert into Items (nom, quantiteStock, prix, photo,typeItem) 
values ( pNom, pQuantite, pPrix, pPhoto, ptypeItem); 
select LAST_INSERT_ID() into pidItem; 
insert into Sorts (idItem, estInstantane, rarete,TypeSort) 
values (pidItem, pInstantane,prarete, ptype); 
commit; 
end | 
use dbsaliha; 
drop procedure if exists ajouterArme; 
 
delimiter | 
create  procedure ajouterArme( 
    in pNom varchar(50), 
    in pQuantite int, 
    in pPrix int, 
    in pPhoto varchar(100), 
    in pDescription varchar(500), 
    in pEfficacite varchar(30), 
    in pGenreArme varchar(45)) 
     
begin 
    declare pTypeItem char(1) default 'A'; 
    declare pidItem int; 
  start transaction; 
   insert into Items (nom, quantiteStock, prix, photo,typeItem)  
   values ( pNom, pQuantite, pPrix, pPhoto, ptypeItem); 
 
                select LAST_INSERT_ID() into pidItem; 
 
    insert into Armes (idItem, description,efficacite, genre)  
                values (pidItem, pdescription,pEfficacite,  pGenreArme); 

/*--Trigger--*/
rop trigger CTRLInserItem; 
DELIMITER |; 
CREATE TRIGGER CTRLInserItem BEFORE INSERT  ON Items 
FOR EACH ROW 
begin 
declare minPrix int;  
declare maxPrix int;  
 -- si l'item inséré n'est pas un sort, vérifie le prix  -- minimum d'un sort 
if(new.typeItem <>'S') Then 
 select  min(prix)  into minPrix from Items where typeItem ='S' ; 
 if(new.Prix >=minPrix) then  
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'prix doit être bas';  
 end if;     
end if; 
 -- si l'item inséré est un sort, vérifie le prix  -- maximum des autres items 
if(new.typeItem='S') Then  
 select  max(prix)  into maxPrix from Items where typeItem <>'S' ; 
 if(new.Prix <=maxPrix) then  
SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'prix doit être haut';  
 end if; 
end if; 
 
END |;

/* ==Fait par Patrice== */
/*Le 10 mars 2025*/

DROP PROCEDURE IF EXISTS ajouterPanier;
DELIMITER |

CREATE PROCEDURE ajouterPanier(
    IN pIdJoueur   INT,
    IN pIdItem     INT,
    IN pQuantite   INT)
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
        VALUES (pIdJoueur, pIdItem, pQuantite)
        ON DUPLICATE KEY UPDATE quantitePanier = quantitePanier + VALUES(quantitePanier);
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

    /* 1) Vérifier qu'il y a bien quelque chose à payer */
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


DROP PROCEDURE IF EXISTS creeCompte;
DELIMITER |

CREATE PROCEDURE creeCompte(
    IN  pAlias       VARCHAR(45),
    IN  pPrenom      VARCHAR(45),
    IN  pNom         VARCHAR(45),
    IN  pAge         INT,
    IN  pType        INT,
    IN  pCourriel    VARCHAR(45),
    IN  pMotPasse    VARCHAR(255),  -- mot de passe en clair (haché DANS la procedure)
    IN  pEstMage     TINYINT,       -- défaut 0
    IN  pEstAdmin    TINYINT,       -- défaut 0
    OUT pIdJoueur    INT			-- Pour pouvoir montrer au joueur qu'il a bien cree un compte ( directement le connecter?)
)
BEGIN
    DECLARE vCount     INT DEFAULT 0;
    DECLARE vAlias     VARCHAR(45);
    DECLARE vCourriel  VARCHAR(45);
    DECLARE vHash      VARCHAR(64);   -- SHA2-256 
    DECLARE vEstMage   TINYINT DEFAULT 0;
    DECLARE vEstAdmin  TINYINT DEFAULT 0;
    DECLARE vBourse    INT DEFAULT 0;
    DECLARE vPoints    INT DEFAULT 0;

    /* Trim pour s'assurer d'enlever les espace avant et apres*/
    SET vAlias    = TRIM(pAlias);
    SET vCourriel = TRIM(pCourriel);
    SET vEstMage  = IFNULL(pEstMage, 0);
    SET vEstAdmin = IFNULL(pEstAdmin, 0);

    /* Validations */
    IF vAlias IS NULL OR vAlias = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Alias obligatoire.';
    END IF;
    /*Expression reguliere https://dev.mysql.com/doc/refman/8.4/en/regexp.html Consulte 10 mars 2025*/
    IF vCourriel IS NULL OR vCourriel = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Courriel obligatoire.';
    END IF;
	IF NOT REGEXP_LIKE(vCourriel, '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$', 'i') THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Le courriel ne respect pas l''expression reguliere';
	END IF;
    If !REGEXP_LIKE(pMotPasse,'[a-z]') THEN 
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Le mdp dois contenir une minuscule';
	END IF;
    If REGEXP_LIKE(pMotPasse,'[A-Z]') THEN 
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Le mdp dois contenir une Majuscule';
	END IF;
      If REGEXP_LIKE(pMotPasse,'[0-9]') THEN 
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Le mdp dois contenir un chiffre';
	END IF;
	IF pMotPasse IS NULL OR CHAR_LENGTH(pMotPasse) < 10 THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Le mdp dois contenir 10 charatere';
	END IF;
    

    /*L'alias est unique? */
    SELECT COUNT(*) INTO vCount FROM Joueurs WHERE alias = vAlias;
    IF vCount > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Alias déjà utilisé.';
    END IF;

    /* 3) Unicité courriel */
    SELECT COUNT(*) INTO vCount FROM Joueurs WHERE courriel = vCourriel;
    IF vCount > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Courriel déjà utilisé.';
    END IF;

    /*Hachage du mot de passe (SHA-256)*/
    SET vHash = SHA2(pMotPasse, 256);

    /* Insertion */
    START TRANSACTION;
        INSERT INTO Joueurs (
            alias, prenom, nom, age, type,
            bourse, estMage, courriel, motDePasse,
            estAdmin, points
        ) VALUES (
            vAlias, pPrenom, pNom, pAge, pType,
            vBourse, vEstMage, vCourriel, vHash,
            vEstAdmin, vPoints
        );

        SET pIdJoueur = LAST_INSERT_ID();
    COMMIT;
END
|
DELIMITER ;

