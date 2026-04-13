/*
Auteur : Patrice Paul
Derniere maj : 6 avril
*/

ALTER TABLE Stats
ADD nbEnigmesMage INT DEFAULT 0,
ADD streak INT DEFAULT 0;


ALTER TABLE Enigmes
CHANGE estPige estPiege TINYINT;