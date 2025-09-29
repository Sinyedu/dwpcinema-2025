DROP DATABASE IF EXISTS dwpcinemaDB;
CREATE DATABASE dwpcinemaDB;
USE dwpcinemaDB;
SET default_storage_engine=InnoDB;


CREATE TABLE Game (
    gameID int PRIMARY KEY AUTO_INCREMENT,
    gameName varchar(100) NOT NULL,
    gameGenre varchar(50)
);

CREATE TABLE News (
    newsID int PRIMARY KEY AUTO_INCREMENT,
    newsTitle varchar(255) NOT NULL,
    newsContent TEXT NOT NULL,
    newsAuthor varchar(100) NOT NULL,
    newsImage varchar(255),
    newsCreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE PostalCode (
    PostalCodeID varchar(20) PRIMARY KEY NOT NULL,
    City varchar(255)
);

CREATE TABLE Hall (
    hallID int PRIMARY KEY AUTO_INCREMENT,
    hallName varchar(100) NOT NULL,
    totalSeats int NOT NULL
);

CREATE TABLE `User` (
    userID int PRIMARY KEY AUTO_INCREMENT,
    firstName varchar(100) NOT NULL,
    lastName varchar(100) NOT NULL,
    userEmail varchar(100) NOT NULL UNIQUE,
    passwordHash varchar(255) NOT NULL,
    DOB DATE,
    postalCodeID varchar(20),
    FOREIGN KEY (postalCodeID) REFERENCES PostalCode(PostalCodeID)
);

CREATE TABLE Seat (
    seatID int PRIMARY KEY AUTO_INCREMENT,
    hallID int,
    seatRow varchar(10) NOT NULL,
    seatNumber int NOT NULL,
    FOREIGN KEY (hallID) REFERENCES Hall(hallID)
);

CREATE TABLE Tournament (
    tournamentID int PRIMARY KEY AUTO_INCREMENT,
    gameID int,
    tournamentName varchar(100) NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    tournamentDescription varchar(255),
    FOREIGN KEY (gameID) REFERENCES Game(gameID)
);

CREATE TABLE `Match` (
    matchID int PRIMARY KEY AUTO_INCREMENT,
    tournamentID int,
    gameID int,
    matchDate DATETIME NOT NULL,
    matchTime TIME NOT NULL,
    hallID int,
    FOREIGN KEY (hallID) REFERENCES Hall(hallID),
    FOREIGN KEY (tournamentID) REFERENCES Tournament(tournamentID),
    FOREIGN KEY (gameID) REFERENCES Game(gameID)
);

CREATE TABLE Showing (
    showingID int PRIMARY KEY AUTO_INCREMENT,
    matchID int NOT NULL,
    hallID int NOT NULL,
    showingDate DATETIME NOT NULL,
    showingTime TIME NOT NULL,
    FOREIGN KEY (hallID) REFERENCES Hall(hallID),
    FOREIGN KEY (matchID) REFERENCES `Match`(matchID)
);

CREATE TABLE Booking (
    bookingID int PRIMARY KEY AUTO_INCREMENT,
    userID int NOT NULL,
    showingID int NOT NULL,
    bookingDate DATETIME NOT NULL,
    FOREIGN KEY (userID) REFERENCES `User`(userID),
    FOREIGN KEY (showingID) REFERENCES Showing(showingID)
);

CREATE TABLE Booking_Seat (
    bookingID INT,
    seatID INT,
    CONSTRAINT PK_Booking_Seat PRIMARY KEY (bookingID, seatID),
    FOREIGN KEY (bookingID) REFERENCES Booking(bookingID),
    FOREIGN KEY (seatID) REFERENCES Seat(seatID)
);

CREATE TABLE Tournament_Hall (
    tournamentID INT,
    hallID INT,
    PRIMARY KEY (tournamentID, hallID),
    FOREIGN KEY (tournamentID) REFERENCES Tournament(tournamentID),
    FOREIGN KEY (hallID) REFERENCES Hall(hallID)
);