
/*==== DONNEE PAR LE PROF ===*/

/*======Sort=======
 Exemple :call ajouterSort('NTM',21,60,'taMereLaDecapotable.jpg',1,3,'c');*/
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

/*======Arme=======
 Exemple : call ajouterArme('lahache2',21,60,'hache.jpg','hache','très fficace','deuxmain');*/
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
			commit;
end |




/*
 Auteur: Patrice Paul  
 Date dernier modif: 17 mars 2026
 Exemple : TO DO
*/

/*Armure
Exmple : call ajouterArmure('Armure des Tenebres',10,10,'pathImg.jpeg','Fer demoniaque', 'XL');
*/

drop procedure if exists ajouterArmure; 
delimiter | 
create  procedure ajouterArmure( 
    in pNom varchar(50), 
    in pQuantite int, 
    in pPrix int, 
    in pPhoto varchar(100),
    in pMatiere VARCHAR(45),
    in pTaille VARRCHAR(45)
) 
     
begin 
    declare pTypeItem char(1) default 'R'; 
    declare pidItem int; 
  start transaction; 
   insert into Items (nom, quantiteStock, prix, photo,typeItem)  
   values ( pNom, pQuantite, pPrix, pPhoto, ptypeItem); 
 
                select LAST_INSERT_ID() into pidItem; 
 
    insert into Armures (idItem, matiere, taille)  
                values (pidItem,pMatiere,pTaille); 
			commit;
end |

/*Potion*/
DROP PROCEDURE IF EXISTS ajouterPotion
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
    DECLARE pTypeItem Char(1) DEFAULT 'P'
    DECLARE pIdItem;
    START TRANSACTION;
        INSERT INTO Items(nom,quantiteStock,prix,photo,typeItem)
            VALUES(pNom,pQuantite,pPrix,pPhoto,pTypeItem);

            SELECT LAST_INSERT_ID() INTO pIdItem;
        INSET INTO Potion(idItem,effet,duree)
            VALUES(pIdItem,pEffet,pDuree)
    COMMIT;
END |