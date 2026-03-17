/*
 Auteur: Patrice Paul  
 Date dernier modif: 17 mars 2026
 Exemple : SELECT * FROM vDetailArmure WHERE idItem = ?;
*/
CREATE VIEW vDetailArmures AS
	SELECT i.nom,i.quantiteStock,i.prix,i.photo,i.estDisponible,
			a.matiere,taille
	FROM Items i INNER JOIN Armures a ON i.idItem = a.idItem;


CREATE VIEW vDetailArmes AS
	SELECT i.nom,i.quantiteStock,i.prix,i.photo,i.estDisponible,
			a.efficacite,a.genre,a.description
	FROM Items i INNER JOIN Armes a ON i.idItem = a.idItem;
    
CREATE VIEW vDetailPotions AS
	SELECT i.nom,i.quantiteStock,i.prix,i.photo,i.estDisponible,
		p.effet,p.duree
	FROM Items i INNER JOIN Potions p ON i.idItem = p.idItem;
    
CREATE VIEW vDetaiSorts AS
	SELECT i.nom,i.quantiteStock,i.prix,i.photo,i.estDisponible,
		s.rarete,s.estInstantane,t.typeSort, t.description,t.pVie,t.pDegat
	FROM Items i INNER JOIN Sorts s ON i.idItem = s.idItem
				 INNER JOIN Typesorts t ON s.typeSort = t.typeSort;

