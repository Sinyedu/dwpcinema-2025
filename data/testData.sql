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

INSERT INTO News (newsTitle, newsContent, newsAuthor, newsImage) VALUES
('Cinema Upgrade', 'We’ve upgraded our cinema sound system for an even more immersive experience.', 'Admin', 'https://via.placeholder.com/600x400'),
('Community Event', 'Join our post-finals meetup for a chance to connect with fellow fans.', 'Admin', 'https://via.placeholder.com/600x400'),
('More Events Added', 'Additional esports events have been added to our 2025 schedule.', 'Admin', 'https://via.placeholder.com/600x400');
