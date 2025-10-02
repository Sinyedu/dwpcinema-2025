DROP DATABASE IF EXISTS dwpcinemaDB;
CREATE DATABASE dwpcinemaDB;
USE dwpcinemaDB;
SET default_storage_engine=InnoDB;

CREATE TABLE Game (
    gameID int PRIMARY KEY AUTO_INCREMENT,
    gameName varchar(100) NOT NULL,
    gameGenre varchar(50)
);

CREATE TABLE Hall (
    hallID int PRIMARY KEY AUTO_INCREMENT,
    hallName varchar(100) NOT NULL,
    totalSeats int NOT NULL
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
    matchDate DATE NOT NULL,
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
    showingDate DATE NOT NULL,
    showingTime TIME NOT NULL,
    FOREIGN KEY (hallID) REFERENCES Hall(hallID),
    FOREIGN KEY (matchID) REFERENCES `Match`(matchID)
);

CREATE TABLE Seat (
    seatID int PRIMARY KEY AUTO_INCREMENT,
    hallID int,
    seatRow varchar(10) NOT NULL,
    seatNumber int NOT NULL,
    FOREIGN KEY (hallID) REFERENCES Hall(hallID)
);

CREATE TABLE `User` (
    userID int PRIMARY KEY AUTO_INCREMENT,
    firstName varchar(100) NOT NULL,
    lastName varchar(100) NOT NULL,
    userEmail varchar(100) NOT NULL UNIQUE,
    passwordHash varchar(255) NOT NULL
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
    PRIMARY KEY (bookingID, seatID),
    FOREIGN KEY (bookingID) REFERENCES Booking(bookingID),
    FOREIGN KEY (seatID) REFERENCES Seat(seatID)
);

CREATE TABLE News (
    newsID int PRIMARY KEY AUTO_INCREMENT,
    newsTitle varchar(255) NOT NULL,
    newsContent TEXT NOT NULL,
    newsAuthor varchar(100) NOT NULL,
    newsImage varchar(255),
    newsCreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO Game (gameName, gameGenre) VALUES
('League of Legends', 'MOBA'),
('Valorant', 'FPS'),
('Counter-Strike 2', 'FPS');

INSERT INTO Hall (hallName, totalSeats) VALUES
('Main Hall', 200),
('VIP Hall', 50);

INSERT INTO Tournament (gameID, tournamentName, startDate, endDate, tournamentDescription) VALUES
(1, 'Worlds Finals 2025', '2025-11-10', '2025-11-20', 'League of Legends World Championship Finals'),
(2, 'Valorant Champions 2025', '2025-08-01', '2025-08-15', 'The ultimate Valorant tournament of the year'),
(3, 'CS2 Major 2025', '2025-09-05', '2025-09-12', 'Counter-Strike 2 International Major');

INSERT INTO `Match` (tournamentID, gameID, matchDate, matchTime, hallID) VALUES
(1, 1, '2025-11-10', '18:00:00', 1),
(1, 1, '2025-11-11', '18:00:00', 1),
(2, 2, '2025-08-01', '20:00:00', 2),
(2, 2, '2025-08-02', '20:00:00', 2),
(3, 3, '2025-09-05', '19:00:00', 1),
(3, 3, '2025-09-06', '19:00:00', 1);

INSERT INTO Showing (matchID, hallID, showingDate, showingTime) VALUES
(1, 1, '2025-11-10', '18:00:00'),
(2, 1, '2025-11-11', '18:00:00'),
(3, 2, '2025-08-01', '20:00:00'),
(4, 2, '2025-08-02', '20:00:00'),
(5, 1, '2025-09-05', '19:00:00'),
(6, 1, '2025-09-06', '19:00:00');

INSERT INTO Seat (hallID, seatRow, seatNumber)
SELECT 1, CHAR(65 + FLOOR((n-1)/10)), ((n-1)%10)+1
FROM (SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
      UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
      UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
      UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20) AS numbers;

INSERT INTO Seat (hallID, seatRow, seatNumber)
SELECT 2, CHAR(65 + FLOOR((n-1)/10)), ((n-1)%10)+1
FROM (SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) AS numbers;

INSERT INTO News (newsTitle, newsContent, newsAuthor, newsImage) VALUES
('Cinema Upgrade', 'We’ve upgraded our cinema sound system for an even more immersive experience.', 'Admin', 'https://via.placeholder.com/600x400'),
('Community Event', 'Join our post-finals meetup for a chance to connect with fellow fans.', 'Admin', 'https://via.placeholder.com/600x400'),
('More Events Added', 'Additional esports events have been added to our 2025 schedule.', 'Admin', 'https://via.placeholder.com/600x400');
