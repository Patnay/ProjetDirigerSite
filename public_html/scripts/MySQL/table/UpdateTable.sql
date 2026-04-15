/*
Auteur : Patrice Paul
Derniere maj : 14 avril 2026
*/

ALTER TABLE Stats
ADD nbEnigmesMage INT DEFAULT 0,
ADD streak INT DEFAULT 0;


ALTER TABLE Enigmes
CHANGE estPige estPiege TINYINT;


/* ===== Ajouter streak et nbEnigmesMage sur Joueurs ===== */
ALTER TABLE Joueurs
ADD COLUMN streak INT DEFAULT 0,
ADD COLUMN nbEnigmesMage INT DEFAULT 0;


/* ===== Refaire Statistiques avec PK auto increment ===== */
-- Supprime les FK qui pointent vers Statistiques (si existantes)
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS Statistiques;

CREATE TABLE Statistiques (
    idStatistique INT AUTO_INCREMENT PRIMARY KEY,
    idJoueur      INT NOT NULL,
    idEnigme      INT NOT NULL,
    estReussi     TINYINT NOT NULL,
    nbEnigmesMage INT DEFAULT 0,
    streak        INT DEFAULT 0,
    FOREIGN KEY (idJoueur) REFERENCES Joueurs(idJoueur),
    FOREIGN KEY (idEnigme) REFERENCES Enigmes(idEnigme)
);

SET FOREIGN_KEY_CHECKS = 1;