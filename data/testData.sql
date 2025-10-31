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
('Premium', 80.00, 'Center view, extra legroom.'),
('Standard', 50.00, 'Regular seating, great for general audience.');


INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 1, 
       CHAR(65 + FLOOR((n-1)/20)), 
       ((n-1)%20)+1, 
       CASE 
           WHEN n <= 40 THEN 1
           WHEN n <= 100 THEN 2 
           ELSE 3               
       END
FROM (
    SELECT @row := @row + 1 AS n FROM 
    (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
     UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t1,
    (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
     UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t2,
    (SELECT @row:=0) t0
    LIMIT 200
) AS seats;

INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 2,
       CHAR(65 + FLOOR((n-1)/10)),
       ((n-1)%10)+1,
       CASE WHEN n <= 20 THEN 1 ELSE 2 END
FROM (
    SELECT @r := @r + 1 AS n FROM 
    (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
     UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a1,
    (SELECT @r:=0) a0
    LIMIT 50
) AS seats;

INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 3,
       CHAR(65 + FLOOR((n-1)/15)),
       ((n-1)%15)+1,
       CASE 
           WHEN n <= 30 THEN 1
           WHEN n <= 90 THEN 2
           ELSE 3
       END
FROM (
    SELECT @c := @c + 1 AS n FROM 
    (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
     UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b1,
    (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
     UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b2,
    (SELECT @c:=0) b0
    LIMIT 150
) AS seats;

INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 4,
       CHAR(65 + FLOOR((n-1)/10)),
       ((n-1)%10)+1,
       CASE WHEN n <= 20 THEN 2 ELSE 3 END
FROM (
    SELECT @d := @d + 1 AS n FROM 
    (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
     UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) c1,
    (SELECT @d:=0) c0
    LIMIT 100
) AS seats;

INSERT INTO Tournament (gameID, tournamentName, startDate, endDate, tournamentDescription) VALUES
(1, 'League of Legends: Worlds 2026', '2026-11-10', '2026-11-20', 'The grand finale of competitive LoL featuring global champions.'),
(2, 'Valorant Masters Chilé', '2026-07-15', '2026-07-25', 'The Twelve best teams in the world will face off for a Valorant Masters Trophy in Chilé'),
(3, 'CS2 Major: London 2026', '2026-09-01', '2026-09-10', 'The premier Counter-Strike 2 Major event of the year.'),
(4, 'Dota 2 International 2026', '2026-10-05', '2026-10-15', 'World’s biggest Dota 2 championship.'),
(5, 'Rocket League World Cup', '2026-06-20', '2026-06-25', 'Fast cars, high-flying goals, global teams.');

INSERT INTO `Match` (tournamentID, gameID, matchDate, matchTime, hallID) VALUES
(1, 1, '2025-11-10', '18:00:00', 1),
(1, 1, '2025-11-12', '19:00:00', 1),
(2, 2, '2025-07-16', '20:00:00', 2),
(2, 2, '2025-07-20', '20:00:00', 2),
(3, 3, '2025-09-02', '19:00:00', 3),
(3, 3, '2025-09-08', '19:00:00', 3),
(4, 4, '2025-10-06', '18:00:00', 1),
(4, 4, '2025-10-10', '18:00:00', 1),
(5, 6, '2025-06-21', '17:00:00', 4),
(5, 6, '2025-06-24', '17:00:00', 4);


INSERT INTO Showing (matchID, hallID, showingDate, showingTime) VALUES
(1, 1, '2025-11-10', '18:00:00'),
(2, 1, '2025-11-12', '19:00:00'),
(3, 2, '2025-07-16', '20:00:00'),
(4, 2, '2025-07-20', '20:00:00'),
(5, 3, '2025-09-02', '19:00:00'),
(6, 3, '2025-09-08', '19:00:00'),
(7, 1, '2025-10-06', '18:00:00'),
(8, 1, '2025-10-10', '18:00:00'),
(9, 4, '2025-06-21', '17:00:00'),
(10, 4, '2025-06-24', '17:00:00');


INSERT INTO News (newsTitle, newsContent, newsAuthor, newsImage) VALUES
('Esports Cinema Expands!', 'We are proud to announce the opening of two new halls designed for esports tournaments and screenings.', 'Admin', 'https://via.placeholder.com/600x400'),
('Partnership with Riot Games', 'Our cinema will officially host the 2025 League of Legends Worlds screening events!', 'Admin', 'https://via.placeholder.com/600x400'),
('New VIP Lounges', 'Experience tournaments like never before with luxury seating, in-seat service, and exclusive viewing angles.', 'Admin', 'https://via.placeholder.com/600x400'),
('Community Event: Meet the Pros', 'Fans can meet professional players and streamers during special Q&A sessions.', 'Admin', 'https://via.placeholder.com/600x400'),
('Summer Lineup Announced', 'Valorant Masters and Rocket League World Cup confirmed for this summer!', 'Admin', 'https://via.placeholder.com/600x400');

