
/*==== DONNEE PAR LE PROF ===*/

/*======Sort=======
 Exemple :call ajouterSort('NTM',21,60,'taMereLaDecapotable.jpg',1,3,'c');*/
 call ajouterSort('NTM',21,60,'taMereLaDecapotable.jpg',1,3,'c','Jvais pull dans tes reve',4,5);
 drop procedure if exists ajouterSort; 
delimiter | 
CREATE  PROCEDURE ajouterSort( 
in pNom varchar(45), 
in pQuantite int, 
in pPrix int, 
in pPhoto varchar(100), 
in pInstantane tinyint, 
in pRarete tinyint, 
in pType char(1),
IN pDescription VARCHAR(1000),
IN pPVie INT,
IN pPDegat INT) 
begin 
declare pTypeItem char(1) default 'S'; 
declare pidItem int; 
start transaction; 
        INSERT INTO Items(nom,quantiteStock,prix,photo,typeItem,estDisponible)
            VALUES(pNom,pQuantite,pPrix,pPhoto,pTypeItem,1);
select LAST_INSERT_ID() into pidItem; 
INSERT INTO Typesorts(typeSort, description,pVie,pDegat)
	VALUES(pType,pDescription,pPVie,pPDegat);
insert into Sorts (idItem, estInstantane, rarete,TypeSort) 
values (pidItem, pInstantane,pRarete, pType); 
commit; 
end | 

/*======Arme=======
 Exemple : call ajouterArme('lahache2',21,60,'hache.jpg','hache','très fficace','deuxmain');*/
 call ajouterArme('lahache2',12,6,'hache.jpg','hache','très fficace','deuxmain');
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
        INSERT INTO Items(nom,quantiteStock,prix,photo,typeItem,estDisponible)
            VALUES(pNom,pQuantite,pPrix,pPhoto,pTypeItem,1);
 
                select LAST_INSERT_ID() into pidItem; 
 
    insert into Armes (idItem, description,efficacite, genre)  
                values (pidItem, pdescription,pEfficacite,  pGenreArme); 
			commit;
end |




/*
 Auteur: Patrice Paul  
 Date dernier modif: 18 mars 2026
 Exemple : TO DO
*/

/*Armure
Exemple : call ajouterArmure('Armure des Tenebres',10,10,'pathImg.jpeg','Fer demoniaque', 'XL');
*/
drop procedure if exists ajouterArmure; 
delimiter | 
create  procedure ajouterArmure( 
    in pNom varchar(50), 
    in pQuantite int, 
    in pPrix int, 
    in pPhoto varchar(100),
    in pMatiere VARCHAR(45),
    in pTaille VARCHAR(45)
) 
     
begin 
    declare pTypeItem char(1) default 'R'; 
    declare pidItem int; 
  start transaction; 
        INSERT INTO Items(nom,quantiteStock,prix,photo,typeItem,estDisponible)
            VALUES(pNom,pQuantite,pPrix,pPhoto,pTypeItem,1);
 
                select LAST_INSERT_ID() into pidItem; 
 
    insert into Armures (idItem, matiere, taille)  
                values (pidItem,pMatiere,pTaille); 
			commit;
end |

/*Potion*/
/* Exemple :CALL ajouterPotion('ntm',4,2,'imageDeTaMERE','mort',12);*/
DROP PROCEDURE IF EXISTS ajouterPotion;
DELIMITER |
CREATE PROCEDURE ajouterPotion(
    in pNom varchar(50), 
    in pQuantite int, 
    in pPrix int, 
    in pPhoto varchar(100),
    IN pEffet VARCHAR(45),
    IN pDuree INT
)
BEGIN
    DECLARE pTypeItem Char(1) DEFAULT 'P';
    DECLARE pIdItem INT;
    START TRANSACTION;
        INSERT INTO Items(nom,quantiteStock,prix,photo,typeItem,estDisponible)
            VALUES(pNom,pQuantite,pPrix,pPhoto,pTypeItem,1);

            SELECT LAST_INSERT_ID() INTO pIdItem;
        INSERT INTO Potions(idItem,effet,duree)
            VALUES(pIdItem,pEffet,pDuree);
    COMMIT;
END |