DROP DATABASE IF EXISTS dwpcinemaDB;
CREATE DATABASE dwpcinemaDB;
USE dwpcinemaDB;
SET default_storage_engine=InnoDB;


CREATE TABLE Game (
    gameID INT PRIMARY KEY AUTO_INCREMENT,
    gameName VARCHAR(100) NOT NULL,
    gameGenre VARCHAR(50)
);

CREATE TABLE OpeningHours (
    dayOfWeek ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') PRIMARY KEY,
    openTime TIME NOT NULL,
    closeTime TIME NOT NULL,
    isClosed BOOLEAN DEFAULT FALSE
);

CREATE TABLE Location (
    locationID INT AUTO_INCREMENT PRIMARY KEY,
    locationName VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postcode VARCHAR(20) NULL,
    country VARCHAR(50) DEFAULT 'Denmark',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    isHidden tinyint(1) DEFAULT 0,
    tournamentDescription VARCHAR(255),
    FOREIGN KEY (gameID) REFERENCES Game(gameID)
);

CREATE TABLE `Match` (
    matchID INT PRIMARY KEY AUTO_INCREMENT,
    tournamentID INT,
    gameID INT,
    matchName VARCHAR(100) NOT NULL,
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
    isAdmin BOOLEAN DEFAULT FALSE,
    passwordHash VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    dateCreated DATETIME DEFAULT CURRENT_TIMESTAMP,
    isActive BOOLEAN DEFAULT TRUE,
    lastActive DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    lastReservationAt DATETIME DEFAULT NULL
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

CREATE TABLE SupportTicket (
    ticketID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    status ENUM('open', 'pending', 'resolved', 'closed') DEFAULT 'open',
    priority ENUM('low','medium','high') DEFAULT 'medium',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES `User`(userID)
);

CREATE TABLE SupportMessage (
    messageID INT PRIMARY KEY AUTO_INCREMENT,
    ticketID INT NOT NULL,
    senderID INT NOT NULL,               
    senderRole ENUM('user','admin') NOT NULL,
    message TEXT NOT NULL,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticketID) REFERENCES SupportTicket(ticketID),
    FOREIGN KEY (senderID) REFERENCES `User`(userID)
);

CREATE TABLE SupportAttachment (
    attachmentID INT PRIMARY KEY AUTO_INCREMENT,
    messageID INT NOT NULL,
    filePath VARCHAR(255) NOT NULL,
    uploadedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (messageID) REFERENCES SupportMessage(messageID)
);

CREATE TABLE AboutUs (
    aboutID INT PRIMARY KEY AUTO_INCREMENT,
    aboutTitle VARCHAR(255) NOT NULL,
    aboutContent TEXT NOT NULL,
    aboutFooter VARCHAR(255),
    lastUpdated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE ContactForm (
    contactID INT PRIMARY KEY AUTO_INCREMENT,
    firstName VARCHAR(100) NOT NULL,
    lastName VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    category VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    submittedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    tournamentID INT,
    FOREIGN KEY (tournamentID) REFERENCES Tournament(tournamentID)
);


-- 1) Fast lookup of bookings by user
CREATE INDEX idx_booking_user ON Booking(userID);

-- 2) Fast lookup of booked seats (useful for seat availability checks)
CREATE INDEX idx_booking_seat_showing ON Booking_Seat(seatID);

DELIMITER $$

-- Trigger 1: update Booking.totalAmount when a seat is added
CREATE TRIGGER trg_bookingseat_after_insert
AFTER INSERT ON Booking_Seat
FOR EACH ROW
BEGIN
    DECLARE seatPrice DECIMAL(10,2);

    SELECT st.basePrice INTO seatPrice
    FROM Seat s
    JOIN SeatTier st ON s.tierID = st.tierID
    WHERE s.seatID = NEW.seatID;

    UPDATE Booking
    SET totalAmount = totalAmount + seatPrice
    WHERE bookingID = NEW.bookingID;
END$$

-- Trigger 2: update Booking.totalAmount when a seat is removed
CREATE TRIGGER trg_bookingseat_after_delete
AFTER DELETE ON Booking_Seat
FOR EACH ROW
BEGIN
    DECLARE seatPrice DECIMAL(10,2);

    SELECT st.basePrice INTO seatPrice
    FROM Seat s
    JOIN SeatTier st ON s.tierID = st.tierID
    WHERE s.seatID = OLD.seatID;

    UPDATE Booking
    SET totalAmount = totalAmount - seatPrice
    WHERE bookingID = OLD.bookingID;
END$$

DELIMITER ;


-- View 1:
CREATE OR REPLACE VIEW vw_user_bookings AS
SELECT b.bookingID, b.userID, b.showingID, b.bookingDate, b.totalAmount,
       s.showingDate, s.showingTime,
       h.hallName,
       GROUP_CONCAT(CONCAT(seatRow, seatNumber, ' (', st.tierName, ')') SEPARATOR ', ') AS seats
FROM Booking b
JOIN Showing s ON b.showingID = s.showingID
JOIN Hall h ON s.hallID = h.hallID
JOIN Booking_Seat bs ON b.bookingID = bs.bookingID
JOIN Seat seat ON bs.seatID = seat.seatID
JOIN SeatTier st ON seat.tierID = st.tierID
GROUP BY b.bookingID;

-- View 2
CREATE OR REPLACE VIEW vw_showing_occupancy AS
SELECT s.showingID, m.matchName, t.tournamentName, h.hallName,
       COUNT(bs.seatID) AS bookedSeats,
       h.totalSeats,
       ROUND(COUNT(bs.seatID)/h.totalSeats*100,2) AS occupancyPercent
FROM Showing s
JOIN `Match` m ON s.matchID = m.matchID
JOIN Tournament t ON m.tournamentID = t.tournamentID
JOIN Hall h ON s.hallID = h.hallID
LEFT JOIN Booking b ON b.showingID = s.showingID
LEFT JOIN Booking_Seat bs ON bs.bookingID = b.bookingID
GROUP BY s.showingID;


-- TEST DATA, MASSIVE BLOCK OF INSERTS AND TEST DATA -- 

USE dwpcinemaDB;


INSERT INTO Game (gameName, gameGenre) VALUES
('League of Legends', 'MOBA'),
('Valorant', 'FPS'),
('Counter-Strike 2', 'FPS'),
('Dota 2', 'MOBA'),
('Overwatch 2', 'Hero Shooter'),
('Rocket League', 'Sports/Arcade');

INSERT INTO Hall (hallName, totalSeats) VALUES
('Hall 1', 500),
('Hall 2', 500),
('Hall 3', 500),
('Hall 4', 500);


INSERT INTO SeatTier (tierName, basePrice, description) VALUES
('BOX/Enterprise', 500.00, 'A top view within a box where service and customer experience is top level.'),
('VIP', 120.00, 'Front-row leather recliners with table service.'),
('Standard', 80.00, 'Regular seating, great for general audience.');

-- Hall 1
INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 1,
       CHAR(65 + FLOOR((n-1)/20)),
       ((n-1)%20)+1,
       CASE WHEN n <= 100 THEN 2 ELSE 3 END
FROM (
    SELECT a.N + b.N*10 + c.N*100 + 1 AS n
    FROM 
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
         UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a,
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
         UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b,
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) c
) numbers
WHERE n <= 500;

-- Hall 2
INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 2,
       CHAR(65 + FLOOR((n-1)/20)),
       ((n-1)%20)+1,
       CASE WHEN n <= 100 THEN 2 ELSE 3 END
FROM (
    SELECT a.N + b.N*10 + c.N*100 + 1 AS n
    FROM 
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
         UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a,
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
         UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b,
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) c
) numbers
WHERE n <= 500;

-- Hall 3
INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 3,
       CHAR(65 + FLOOR((n-1)/20)),
       ((n-1)%20)+1,
       CASE WHEN n <= 100 THEN 2 ELSE 3 END
FROM (
    SELECT a.N + b.N*10 + c.N*100 + 1 AS n
    FROM 
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
         UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a,
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
         UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b,
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) c
) numbers
WHERE n <= 500;

-- Hall 4
INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 4,
       CHAR(65 + FLOOR((n-1)/20)),
       ((n-1)%20)+1,
       CASE WHEN n <= 100 THEN 2 ELSE 3 END
FROM (
    SELECT a.N + b.N*10 + c.N*100 + 1 AS n
    FROM 
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
         UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a,
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
         UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b,
        (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) c
) numbers
WHERE n <= 500;



INSERT INTO Tournament (gameID, tournamentName, startDate, endDate, tournamentDescription) VALUES
(1, 'League of Legends: Worlds 2026', '2026-11-10', '2026-11-20', 'The grand finale of competitive LoL featuring global champions.'),
(2, 'Valorant Masters Chilé', '2026-07-15', '2026-07-25', 'The Twelve best teams in the world will face off for a Valorant Masters Trophy in Chilé'),
(3, 'CS2 Major: London 2026', '2026-09-01', '2026-09-10', 'The premier Counter-Strike 2 Major event of the year.'),
(4, 'Dota 2 International 2026', '2026-10-05', '2026-10-15', 'World’s biggest Dota 2 championship.'),
(5, 'Rocket League World Cup', '2026-06-20', '2026-06-25', 'Fast cars, high-flying goals, global teams.');

INSERT INTO `Match` (tournamentID, gameID, matchName, matchDate, matchTime, hallID) VALUES
-- LoL Worlds 2026
(1, 1, 'LoL Worlds 2026 - Quarterfinals 1', '2026-11-10', '18:00:00', 1),
(1, 1, 'LoL Worlds 2026 - Quarterfinals 2', '2026-11-12', '19:00:00', 1),
(1, 1, 'LoL Worlds 2026 - Quarterfinals 3', '2026-11-13', '20:00:00', 1),
(1, 1, 'LoL Worlds 2026 - Quarterfinals 4', '2026-11-14', '20:00:00', 1),
(1, 1, 'LoL Worlds 2026 - Semifinals 1', '2026-11-15', '20:00:00', 1),
(1, 1, 'LoL Worlds 2026 - Semifinals 2', '2026-11-16', '20:00:00', 1),
(1, 1, 'LoL Worlds 2026 - Finals', '2026-11-20', '20:00:00', 1),

-- Valorant Masters Chilé
(2, 2, 'Valorant Masters - Quarterfinals 1', '2026-07-18', '20:00:00', 2),
(2, 2, 'Valorant Masters - Quarterfinals 2', '2026-07-19', '20:00:00', 2),
(2, 2, 'Valorant Masters - Quarterfinals 3', '2026-07-20', '20:00:00', 2),
(2, 2, 'Valorant Masters - Quarterfinals 4', '2026-07-21', '20:00:00', 2),
(2, 2, 'Valorant Masters - Semifinals 1', '2026-07-22', '20:00:00', 2),
(2, 2, 'Valorant Masters - Semifinals 2', '2026-07-23', '20:00:00', 2),
(2, 2, 'Valorant Masters - Finals', '2026-07-25', '20:00:00', 2),

-- CS2 Major: London 2026
(3, 3, 'CS2 Major - Quarterfinals 1', '2026-09-02', '19:00:00', 3),
(3, 3, 'CS2 Major - Quarterfinals 2', '2026-09-04', '19:00:00', 3),
(3, 3, 'CS2 Major - Quarterfinals 3', '2026-09-05', '19:00:00', 3),
(3, 3, 'CS2 Major - Quarterfinals 4', '2026-09-06', '19:00:00', 3),
(3, 3, 'CS2 Major - Semifinals 1', '2026-09-07', '19:00:00', 3),
(3, 3, 'CS2 Major - Semifinals 2', '2026-09-08', '19:00:00', 3),
(3, 3, 'CS2 Major - Finals', '2026-09-10', '19:00:00', 3),

-- Dota 2 International 2026
(4, 4, 'Dota 2 International - Quarterfinals 1', '2026-10-06', '18:00:00', 1),
(4, 4, 'Dota 2 International - Quarterfinals 2', '2026-10-07', '18:00:00', 1),
(4, 4, 'Dota 2 International - Quarterfinals 3', '2026-10-08', '18:00:00', 1),
(4, 4, 'Dota 2 International - Quarterfinals 4', '2026-10-09', '18:00:00', 1),
(4, 4, 'Dota 2 International - Semifinals 1', '2026-10-08', '18:00:00', 1),
(4, 4, 'Dota 2 International - Semifinals 2', '2026-10-09', '18:00:00', 1),
(4, 4, 'Dota 2 International - Finals', '2026-10-15', '18:00:00', 1),

-- Rocket League World Cup
(5, 6, 'Rocket League World Cup - Semifinals 1', '2026-06-21', '17:00:00', 4),
(5, 6, 'Rocket League World Cup - Semifinals 2', '2026-06-24', '17:00:00', 4),
(5, 6, 'Rocket League World Cup - Finals', '2026-06-25', '17:00:00', 4);

INSERT INTO Showing (matchID, hallID, showingDate, showingTime) VALUES
-- LoL Worlds 2026
(1, 1, '2026-11-10', '18:00:00'),
(2, 1, '2026-11-12', '19:00:00'),
(3, 1, '2026-11-13', '20:00:00'),
(4, 1, '2026-11-14', '20:00:00'),
(5, 1, '2026-11-15', '20:00:00'),
(6, 1, '2026-11-16', '20:00:00'),
(7, 1, '2026-11-20', '20:00:00'),

-- Valorant Masters Chilé
(8, 2, '2026-07-18', '20:00:00'),
(9, 2, '2026-07-19', '20:00:00'),
(10, 2, '2026-07-20', '20:00:00'),
(11, 2, '2026-07-21', '20:00:00'),
(12, 2, '2026-07-22', '20:00:00'),
(13, 2, '2026-07-23', '20:00:00'),
(14, 2, '2026-07-25', '20:00:00'),

-- CS2 Major: London 2026
(15, 3, '2026-09-02', '19:00:00'),
(16, 3, '2026-09-04', '19:00:00'),
(17, 3, '2026-09-05', '19:00:00'),
(18, 3, '2026-09-06', '19:00:00'),
(19, 3, '2026-09-07', '19:00:00'),
(20, 3, '2026-09-08', '19:00:00'),
(21, 3, '2026-09-10', '19:00:00'),

-- Dota 2 International 2026
(22, 1, '2026-10-06', '18:00:00'),
(23, 1, '2026-10-07', '18:00:00'),
(24, 1, '2026-10-08', '18:00:00'),
(25, 1, '2026-10-09', '18:00:00'),
(26, 1, '2026-10-08', '18:00:00'),
(27, 1, '2026-10-09', '18:00:00'),
(28, 1, '2026-10-15', '18:00:00'),

-- Rocket League World Cup
(29, 4, '2026-06-21', '17:00:00'),
(30, 4, '2026-06-24', '17:00:00'),
(31, 4, '2026-06-25', '17:00:00');



INSERT INTO News (newsTitle, newsContent, newsAuthor, newsImage) VALUES
('Esports Cinema Expands with Two New Dedicated Halls for Tournaments and Screenings', 'We are proud to announce the opening of two new halls designed for esports tournaments and screenings.', 'Admin', 'https://via.placeholder.com/600x400'),
('Partnership with Riot Games', 'Our cinema will officially host the 2026 League of Legends Worlds screening events!', 'Admin', 'https://via.placeholder.com/600x400'),
('New VIP Lounges', 'Experience tournaments like never before with luxury seating, in-seat service, and exclusive viewing angles.', 'Admin', 'https://via.placeholder.com/600x400'),
('Community Event: Meet the Pros', 'Fans can meet professional players and streamers during special Q&A sessions.', 'Admin', 'https://via.placeholder.com/600x400'),
('Summer Lineup Announced', 'Valorant Masters and Rocket League World Cup confirmed for this summer!', 'Admin', 'https://via.placeholder.com/600x400');


INSERT INTO OpeningHours (dayOfWeek, openTime, closeTime, isClosed) VALUES
('Monday', '10:00:00', '22:00:00', FALSE),
('Tuesday', '10:00:00', '22:00:00', FALSE),
('Wednesday', '10:00:00', '22:00:00', FALSE),
('Thursday', '10:00:00', '22:00:00', FALSE),
('Friday', '10:00:00', '23:00:00', FALSE),
('Saturday', '10:00:00', '23:00:00', FALSE),
('Sunday', '10:00:00', '21:00:00', FALSE);

INSERT INTO AboutUs (aboutTitle, aboutContent, aboutFooter) VALUES
('About Esports Cinema', 'Esports Cinema is the premier destination for esports enthusiasts, offering a unique blend of competitive gaming and cinematic experiences. Our state-of-the-art facilities are designed to provide fans with an immersive environment to watch live tournaments, screenings, and exclusive events.', '© 2024 Esports Cinema. All rights reserved.'),
('Our Mission', 'Our mission is to foster a vibrant esports community by providing top-notch viewing experiences, promoting local talent, and hosting world-class events. We strive to be the go-to venue for gamers and fans alike, delivering unforgettable moments in the world of esports.', 'Join us in celebrating the passion and excitement of competitive gaming!'),
('Our Team', 'Our dedicated team of esports enthusiasts, event organizers, and hospitality professionals work tirelessly to ensure that every visit to Esports Cinema is memorable. From curating exciting event lineups to providing exceptional customer service, we are committed to excellence in all that we do.', 'Meet the faces behind Esports Cinema and learn more about our journey.'),
('Community Engagement', 'At Esports Cinema, we believe in the power of community. We actively engage with local gaming groups, schools, and organizations to promote esports and create opportunities for aspiring players. Through workshops, tournaments, and outreach programs, we aim to inspire the next generation of esports talent.', 'Get involved and be a part of our growing community!'),
('Sustainability Initiatives', 'We are committed to sustainability and environmental responsibility. Esports Cinema implements eco-friendly practices such as energy-efficient lighting, waste reduction programs, and sustainable sourcing for our concessions. We believe that gaming and environmental stewardship can go hand in hand.', 'Learn more about our green initiatives and how we are making a difference.'),
('Future Plans', 'Looking ahead, Esports Cinema plans to expand our facilities, introduce new technologies, and host even more high-profile esports events. We are excited about the future of esports and are dedicated to staying at the forefront of this dynamic industry.', 'Stay tuned for upcoming announcements and developments!');
