/*
 Auteur: Patrice Paul  
 Date dernier modif: 17 mars 2026
 Exemple : SELECT * FROM vDetailArmure WHERE idItem = ?;
*/
USE dbdarquest6;
DROP VIEW vDetailArmures;
CREATE VIEW vDetailArmures AS
	SELECT i.nom,i.quantiteStock,i.prix,i.photo,i.estDisponible,i.idItem,
			a.matiere,a.taille
	FROM Items i INNER JOIN Armures a ON i.idItem = a.idItem;
/*Exemple : SELECT * FROM vDetailArmures WHERE idItem = 63;*/


DROP VIEW vDetailArmes;
CREATE VIEW vDetailArmes AS
	SELECT i.nom,i.quantiteStock,i.prix,i.photo,i.estDisponible,i.idItem,
			a.efficacite,a.genre,a.description
	FROM Items i INNER JOIN Armes a ON i.idItem = a.idItem;
/*Exemple :SELECT * FROM vDetailArmes WHERE idItem = 51 ;*/

DROP VIEW vDetailPotions;
CREATE VIEW vDetailPotions AS
	SELECT i.nom,i.quantiteStock,i.prix,i.photo,i.estDisponible,i.idItem,
		p.effet,p.duree
	FROM Items i INNER JOIN Potions p ON i.idItem = p.idItem;
/*Exemple : SELECT * FROM vDetailPotions WHERE idItem = 57; */

DROP VIEW vDetailSorts;
CREATE VIEW vDetailSorts AS
	SELECT i.nom,i.quantiteStock,i.prix,i.photo,i.estDisponible,i.idItem,
		s.rarete,s.estInstantane,t.typeSort, t.description,t.pVie,t.pDegat
	FROM Items i INNER JOIN Sorts s ON i.idItem = s.idItem
				 INNER JOIN Typesorts t ON s.typeSort = t.typeSort;
/*Exemple : SELECT * FROM vDetailSorts WHERE idItem = 5;*/