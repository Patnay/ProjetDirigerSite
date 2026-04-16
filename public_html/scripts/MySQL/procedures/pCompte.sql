/*
 Auteur Patrice Paul
 Derniere modification : 17 mars 2026
 Expemple d'utilisation :call creeCompte('Patnay','Patrice','Paul','jrcppaul@gmail.com','getManmanw_01', @idUSer);
 Explication -> param1 => alias| param2 => prenom | param3 => nom| param4 => email | param5 => mdp(non hasher)| param 6 => idJoueur
                La fonction prend les parametre et mes les autre colonne par DEFAUT et RETOURNE un idJoueur pour le autoConnect (en php)
*/
DROP PROCEDURE IF EXISTS creeCompte;
DELIMITER |

CREATE PROCEDURE creeCompte(
    IN  pAlias       VARCHAR(45),
    IN  pPrenom      VARCHAR(45),
    IN  pNom         VARCHAR(45),
    IN  pCourriel    VARCHAR(45),
    IN  pMotPasse    VARCHAR(255),  -- mot de passe en clair (haché DANS la procedure)
    OUT pIdJoueur    INT			-- Pour pouvoir montrer au joueur qu'il a bien cree un compte ( directement le connecter?)
)
BEGIN
    DECLARE vCount     INT DEFAULT 0;
    DECLARE vAlias     VARCHAR(45);
    DECLARE vCourriel  VARCHAR(45);
    DECLARE vHash      VARCHAR(64);   -- SHA2-256 
    DECLARE idJoueur INT;
    DECLARE vEstMage   TINYINT DEFAULT 0;
    DECLARE vEstAdmin  TINYINT DEFAULT 0;
    DECLARE vNbOr    INT DEFAULT 1000;
	DECLARE vNbArgent   INT DEFAULT 1000;
	DECLARE vNbBronze    INT DEFAULT 1000;

    DECLARE vPtVie    INT DEFAULT 50;
	DECLARE vImg VARCHAR(255) DEFAULT '../image/imageDefaut.jpg';

    /* Trim pour s'assurer d'enlever les espace avant et apres*/
    SET vAlias    = TRIM(pAlias);
    SET vCourriel = TRIM(pCourriel);

    /* Validations */
    IF vAlias IS NULL OR vAlias = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Alias obligatoire.';
    END IF;
    /*

        A Remettre pour un verfication de force du mdp

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
	END IF;*/
    

    /*L'alias est unique? */
    SELECT COUNT(*) INTO vCount FROM Joueurs WHERE alias = vAlias;
    IF vCount > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Alias déjà utilisé.';
    END IF;

    /*courriel est unique? */
    SELECT COUNT(*) INTO vCount FROM Joueurs WHERE courriel = vCourriel;
    IF vCount > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Courriel déjà utilisé.';
    END IF;

    /*Hachage du mot de passe (SHA-256)*/
    SET vHash = SHA2(pMotPasse, 256);

    /* Insertion */
    START TRANSACTION;
        INSERT INTO Joueurs (
            alias, prenom, nom, estMage, courriel, motDePasse,
            estAdmin, ptVie,img , nbOr,nbArgent,nbBronze
        ) VALUES (
            vAlias, pPrenom, pNom
			, vEstMage, vCourriel, vHash,
            vEstAdmin, vPtVie, vImg,
            vNbOr,vNbArgent,vNbBronze
        );

        SET pIdJoueur = LAST_INSERT_ID();
    COMMIT;
END
|
DELIMITER ;
SELECT * FROM Joueurs ;






