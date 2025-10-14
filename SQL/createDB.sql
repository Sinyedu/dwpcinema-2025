DROP DATABASE IF EXISTS dwpcinemaDB;
CREATE DATABASE dwpcinemaDB;
USE dwpcinemaDB;
SET default_storage_engine=InnoDB;


CREATE TABLE Game (
    gameID INT PRIMARY KEY AUTO_INCREMENT,
    gameName VARCHAR(100) NOT NULL,
    gameGenre VARCHAR(50)
);

CREATE TABLE Hall (
    hallID INT PRIMARY KEY AUTO_INCREMENT,
    hallName VARCHAR(100) NOT NULL,
    totalSeats INT NOT NULL
);

CREATE TABLE Tournament (
    tournamentID INT PRIMARY KEY AUTO_INCREMENT,
    gameID INT,
    tournamentName VARCHAR(100) NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    tournamentDescription VARCHAR(255),
    FOREIGN KEY (gameID) REFERENCES Game(gameID)
);

CREATE TABLE `Match` (
    matchID INT PRIMARY KEY AUTO_INCREMENT,
    tournamentID INT,
    gameID INT,
    matchDate DATE NOT NULL,
    matchTime TIME NOT NULL,
    hallID INT,
    FOREIGN KEY (hallID) REFERENCES Hall(hallID),
    FOREIGN KEY (tournamentID) REFERENCES Tournament(tournamentID),
    FOREIGN KEY (gameID) REFERENCES Game(gameID)
);

CREATE TABLE Showing (
    showingID INT PRIMARY KEY AUTO_INCREMENT,
    matchID INT NOT NULL,
    hallID INT NOT NULL,
    showingDate DATE NOT NULL,
    showingTime TIME NOT NULL,
    FOREIGN KEY (hallID) REFERENCES Hall(hallID),
    FOREIGN KEY (matchID) REFERENCES `Match`(matchID)
);


CREATE TABLE `User` (
    userID INT PRIMARY KEY AUTO_INCREMENT,
    firstName VARCHAR(100) NOT NULL,
    lastName VARCHAR(100) NOT NULL,
    userEmail VARCHAR(100) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    dateCreated DATETIME DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE SeatTier (
    tierID INT PRIMARY KEY AUTO_INCREMENT,
    tierName VARCHAR(50) NOT NULL,
    basePrice DECIMAL(10,2) NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE Seat (
    seatID INT PRIMARY KEY AUTO_INCREMENT,
    hallID INT NOT NULL,
    seatRow VARCHAR(10) NOT NULL,
    seatNumber INT NOT NULL,
    tierID INT NOT NULL,
    FOREIGN KEY (hallID) REFERENCES Hall(hallID),
    FOREIGN KEY (tierID) REFERENCES SeatTier(tierID)
);


CREATE TABLE Booking (
    bookingID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    showingID INT NOT NULL,
    bookingDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    totalAmount DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (userID) REFERENCES `User`(userID),
    FOREIGN KEY (showingID) REFERENCES Showing(showingID)
);

CREATE TABLE Booking_Seat (
    bookingID INT,
    seatID INT,
    PRIMARY KEY (bookingID, seatID),
    FOREIGN KEY (bookingID) REFERENCES Booking(bookingID),
    FOREIGN KEY (seatID) REFERENCES Seat(seatID)
);

CREATE TABLE News (
    newsID INT PRIMARY KEY AUTO_INCREMENT,
    newsTitle VARCHAR(255) NOT NULL,
    newsContent TEXT NOT NULL,
    newsAuthor VARCHAR(100) NOT NULL,
    newsImage VARCHAR(255),
    newsCreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
);
