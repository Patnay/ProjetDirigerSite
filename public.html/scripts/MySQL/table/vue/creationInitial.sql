/*
 Auteur : Patrice Paul
 Date : 10 mars 2026
*/

CREATE TABLE Joueurs (
    idJoueur INT AUTO_INCREMENT PRIMARY KEY,
    alias VARCHAR(45),
    prenom VARCHAR(45),
    nom VARCHAR(45),
    age INT,
    type INT,
    bourse INT,
    estMage TINYINT,
    courriel VARCHAR(45),
    motDePasse VARCHAR(100),
    estAdmin TINYINT,
    points INT
);

CREATE TABLE Items (
    idItem INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(45),
    quantiteStock INT,
    prix INT,
    photo VARCHAR(100),
    typeItem CHAR(1),
    estDisponible TINYINT
);


CREATE TABLE Inventaires (
    idJoueur INT,
    idItem INT,
    quantiteInventaire INT,
    PRIMARY KEY (idJoueur, idItem),
    FOREIGN KEY (idJoueur) REFERENCES Joueurs(idJoueur),
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);


CREATE TABLE Paniers (
    idJoueur INT,
    idItem INT,
    quantitePanier INT,
    PRIMARY KEY (idJoueur, idItem),
    FOREIGN KEY (idJoueur) REFERENCES Joueurs(idJoueur),
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);


CREATE TABLE Evaluations (
    idJoueur INT,
    idItem INT,
    nbEtoiles INT,
    commentaire VARCHAR(45),
    PRIMARY KEY (idJoueur, idItem),
    FOREIGN KEY (idJoueur) REFERENCES Joueurs(idJoueur),
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);


CREATE TABLE Statistiques (
    idJoueur INT,
    nbQuetes INT,
    estReussi TINYINT,
    PRIMARY KEY (idJoueur, nbQuetes),
    FOREIGN KEY (idJoueur) REFERENCES Joueurs(idJoueur)
);


CREATE TABLE Reponses (
    idReponse INT AUTO_INCREMENT PRIMARY KEY,
    estBonneReponse TINYINT,
    reponse VARCHAR(45),
    idEnigme INT
);

CREATE TABLE Enigmes (
    idEnigme INT AUTO_INCREMENT PRIMARY KEY,
    enonce VARCHAR(300),
    idCategorie CHAR(1),
    difficulte CHAR(1),
    estPige TINYINT
);


CREATE TABLE Categories (
    idCategorie CHAR(1) PRIMARY KEY,
    nomCategorie VARCHAR(45)
);


CREATE TABLE Typesorts (
    typeSort CHAR(1) PRIMARY KEY,
    description VARCHAR(45),
    pVie INT,
    pDegat INT
);

CREATE TABLE Sorts (
    idItem INT PRIMARY KEY,
    estInstantane TINYINT,
    rarete TINYINT,
    typeSort CHAR(1),
    FOREIGN KEY (idItem) REFERENCES Items(idItem),
    FOREIGN KEY (typeSort) REFERENCES Typesorts(typeSort)
);


CREATE TABLE Armes (
    idItem INT PRIMARY KEY,
    efficacite VARCHAR(30),
    genre VARCHAR(45),
    description VARCHAR(70),
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);


CREATE TABLE Potions (
    idItem INT PRIMARY KEY,
    effet VARCHAR(45),
    duree INT,
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);


CREATE TABLE Armures (
    idItem INT PRIMARY KEY,
    matiere VARCHAR(45),
    taille VARCHAR(45),
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);

ALTER TABLE Enigmes
ADD FOREIGN KEY (idCategorie) REFERENCES Categories(idCategorie);

ALTER TABLE Reponses
ADD FOREIGN KEY (idEnigme) REFERENCES Enigmes(idEnigme);