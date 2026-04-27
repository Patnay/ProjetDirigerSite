/*
 Auteur : Patrice Paul
 Date : 10 mars 2026
 Modifier: 17 mars 2026
*/

CREATE TABLE Joueurs (
    idJoueur INT AUTO_INCREMENT PRIMARY KEY,
    alias VARCHAR(45) NOT NULL,
    prenom VARCHAR(45) NOT NULL,
    nom VARCHAR(45) NOT NULL,
    nbOr INT NOT NULL,
    nbArgent INT NOT NULL,
    nbBronze INT NOT NULL,
    estMage TINYINT NOT NULL,
    courriel VARCHAR(45) NOT NULL,
    motDePasse VARCHAR(100) NOT NULL,
    estAdmin TINYINT NOT NULL,
    points INT NOT NULL
);

CREATE TABLE Items (
    idItem INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(45) NOT NULL,
    quantiteStock INT NOT NULL,
    prix INT NOT NULL,
    photo VARCHAR(100) NOT NULL,
    typeItem CHAR(1) NOT NULL,
    estDisponible TINYINT NOT NULL
);


CREATE TABLE Inventaires (
    idJoueur INT ,
    idItem INT,
    quantiteInventaire INT NOT NULL,
    PRIMARY KEY (idJoueur, idItem),
    FOREIGN KEY (idJoueur) REFERENCES Joueurs(idJoueur),
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);


CREATE TABLE Paniers (
    idJoueur INT ,
    idItem INT,
    quantitePanier INT NOT NULL,
    PRIMARY KEY (idJoueur, idItem),
    FOREIGN KEY (idJoueur) REFERENCES Joueurs(idJoueur),
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);


CREATE TABLE Evaluations (
    idJoueur INT,
    idItem INT,
    nbEtoiles INT NOT NULL,
    commentaire VARCHAR(45),
    PRIMARY KEY (idJoueur, idItem),
    FOREIGN KEY (idJoueur) REFERENCES Joueurs(idJoueur),
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);


CREATE TABLE Statistiques (
    idJoueur INT,
    nbQuetes INT,
    estReussi TINYINT NOT NULL,
    PRIMARY KEY (idJoueur, nbQuetes),
    FOREIGN KEY (idJoueur) REFERENCES Joueurs(idJoueur)
);


CREATE TABLE Reponses (
    idReponse INT AUTO_INCREMENT PRIMARY KEY,
    estBonneReponse TINYINT NOT NULL,
    reponse VARCHAR(45),
    idEnigme INT NOT NULL,
    FOREIGN KEY (idEnigme) REFERENCES Enigmes(idEnigme)
);

CREATE TABLE Enigmes (
    idEnigme INT AUTO_INCREMENT PRIMARY KEY,
    enonce VARCHAR(300) NOT NULL,
    idCategorie CHAR(1) NOT NULL,
    difficulte CHAR(1) NOT NULL,
    estPige TINYINT
);


CREATE TABLE Categories (
    idCategorie CHAR(1) PRIMARY KEY,
    nomCategorie VARCHAR(45) NOT NULL
);


CREATE TABLE Typesorts (
    typeSort CHAR(1) PRIMARY KEY,
    description VARCHAR(45) NOT NULL,
    pVie INT NOT NULL,
    pDegat INT NOT NULL
);

CREATE TABLE Sorts (
    idItem INT PRIMARY KEY,
    estInstantane TINYINT NOT NULL,
    rarete TINYINT NOT NULL,
    typeSort CHAR(1) NOT NULL,
    FOREIGN KEY (idItem) REFERENCES Items(idItem),
    FOREIGN KEY (typeSort) REFERENCES Typesorts(typeSort)
);


CREATE TABLE Armes (
    idItem INT PRIMARY KEY,
    efficacite VARCHAR(30) NOT NULL,
    genre VARCHAR(45) NOT NULL,
    description VARCHAR(70) NOT NULL,
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);


CREATE TABLE Potions (
    idItem INT PRIMARY KEY,
    effet VARCHAR(45) NOT NULL,
    duree INT NOT NULL,
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);


CREATE TABLE Armures (
    idItem INT PRIMARY KEY,
    matiere VARCHAR(45) NOT NULL,
    taille VARCHAR(45) NOT NULL,
    FOREIGN KEY (idItem) REFERENCES Items(idItem)
);

ALTER TABLE Enigmes
ADD FOREIGN KEY (idCategorie) REFERENCES Categories(idCategorie);

ALTER TABLE Reponses
ADD FOREIGN KEY (idEnigme) REFERENCES Enigmes(idEnigme);