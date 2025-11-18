USE dwpcinemaDB;


INSERT INTO Game (gameName, gameGenre) VALUES
('League of Legends', 'MOBA'),
('Valorant', 'FPS'),
('Counter-Strike 2', 'FPS'),
('Dota 2', 'MOBA'),
('Rocket League', 'Sports/Arcade');

INSERT INTO Hall (hallName, totalSeats) VALUES
('Hall 1', 500),
('Hall 2', 500),
('Hall 3', 500),
('Hall 4', 500);


INSERT INTO Teams (teamName, teamDescription, teamGame, players, country) VALUES

('Gen.G Esports', 'Top-tier LCK team competing at Worlds 2025, known for strong macro and star mid-jungle synergy.', 'League of Legends', 'Kiin, Canyon, Chovy, Ruler, Duro', 'South Korea'),
('Hanwha Life Esports', 'LCK contender at Worlds 2025 with an aggressive early-game style.', 'League of Legends', 'Zeus, Peanut, Zeka, Viper, Delight', 'South Korea'),
('KT Rolster', 'Veteran LCK organization making a run at Worlds 2025.', 'League of Legends', 'PerfecT, Cuzz, Bdd, Noah, Peter, Deokdam', 'South Korea'),
('T1', 'Legendary LCK team with multi-time World Champions, present at Worlds 2025.', 'League of Legends', 'Doran, Oner, Faker, Gumayusi, Keria, Smash', 'South Korea'),
('Bilibili Gaming (LoL)', 'LPL team qualified for Worlds 2025, featuring a mix of veterans and up-and-comers.', 'League of Legends', 'Bin, Beichuan, Knight, Elk, ON, Shadow', 'China'),
('Anyone’s Legend', 'Emerging LPL team at Worlds 2025 with aggressive playstyle.', 'League of Legends', 'Flandre, Tarzan, Shanks, Hope, Kael', 'China'),
('Top Esports', 'Powerhouse LPL org competing at Worlds 2025.', 'League of Legends', '369, Kanavi, Creme, JackeyLove, Hang, FengYue', 'China'),
('G2 Esports (LoL)', 'Strong European LEC team at Worlds 2025.', 'League of Legends', 'BrokenBlade, SkewMond, Caps, Hans Sama, Labrov', 'Europe'),
('Fnatic (LoL)', 'Veteran LEC organization qualifying for Worlds 2025.', 'League of Legends', 'Oscarinin, Razork, Poby, Upset, Mikyx', 'Europe'),
('FlyQuest', 'North American team representing LTA North at Worlds 2025.', 'League of Legends', 'Bwipo, Inspired, Quad, Massu, Busio', 'USA'),
('100 Thieves', 'LTA North team in Worlds 2025 with a dedicated fan base.', 'League of Legends', 'Sniper, Dhokla, River, Quid, FBI, Eyla', 'USA'),
('CTBC Flying Oyster', 'Pacific (LCP) org competing at Worlds 2025.', 'League of Legends', 'Rest, Driver, JunJia, hongQ, Doggo, Kaiwing', 'Taiwan'),
('PSG Talon', 'LCP team making waves at Worlds 2025.', 'League of Legends', 'Azhi, Karsa, Mapie, Betty, Woody', 'Taiwan'),
('Team Secret Whales', 'Newer LCP organization qualified for Worlds 2025.', 'League of Legends', 'Hiro02, Steller, Pun, Hizto, Dire, Eddie, Taki', 'Taiwan'),
('Vivo Keyd Stars', 'South American (LTA South) representative at Worlds 2025.', 'League of Legends', 'Boal, Disamis, Mireu, Morttheus, Trymbi, Scramber', 'Brazil'),

('G2 Esports (VAL)', 'North American org qualified for Valorant Champions 2025.', 'Valorant', 'JonahP, trent, valyn, leaf, jawgemo', 'USA'),
('NRG Esports', 'American Valorant team at Champions 2025, strong in VCT.', 'Valorant', 's0m, mada, brawk, Ethan, skuba', 'USA'),
('Sentinels', 'Veteran NA org with history in Valorant, competing at Champions 2025.', 'Valorant', 'zekken, johnqt, Zellsis, bang, NARRATE', 'USA'),
('Paper Rex', 'Top Pacific region team at Champions 2025, known for bold plays.', 'Valorant', 'Jinggg, f0rsakeN, Chihi, Sheng, wx', 'Singapore / Malaysia'),
('Rex Regum Qeon (RRQ)', 'Indonesian Valorant org competing at Champions 2025.', 'Valorant', 'Jemkin, Monyet, Levi, Danang, QN', 'Indonesia'),
('Bilibili Gaming (VAL)', 'Chinese Valorant team at Champions 2025.', 'Valorant', 'Levius, Knight, whzy, rushia, Shadow', 'China'),
('XLG Esports', 'Chinese (or Asian) Valorant org qualified for Champions 2025.', 'Valorant', '––– (roster TBD)', 'China'),
('EDward Gaming (VAL)', 'Defending or returning Champions-level org, competing in Valorant Champions 2025.', 'Valorant', 'CHICHOO, ZmjjKK, nobody, S1Mon, Smoggy', 'China'),
('Team Heretics', 'European Valorant team at Champions 2025.', 'Valorant', '––– (use their 2025 roster)', 'Spain / Europe'),
('MIBR', 'Brazilian / Americas org competing in Champions 2025 Valorant.', 'Valorant', 'artziN, xenom, cortezia, aspas, Verno', 'Brazil'),
('DRX (VAL)', 'Pacific (or Korean) Valorant org at Champions 2025.', 'Valorant', 'MaKo, free1ng, Flashback, HYUNMIN, BeYN', 'South Korea'),
('Dragon Ranger Gaming', 'Valorant team qualified for Champions 2025.', 'Valorant', '––– (2025 roster)', 'Region TBD'),
('GIANTX', 'European Valorant org that made Champions 2025.', 'Valorant', 'Cloud, Ara, Flickless, Westside, Grubinho', 'Spain / UK'),

('Team Vitality (CS2)', 'French CS2 team, reigning BLAST Austin Major 2025 champions.', 'CS2', 'apEX, ZywOo, flameZ, mezii, ropz, XTQZZZ', 'France') ,
('The MongolZ (CS2)', 'Underdog breakout team in CS2, made deep run in major events.', 'CS2', 'bLitz, Techno4K, 910, mzinho, controlez', 'Mongolia') ,
('FaZe Clan (CS2)', 'Legendary global CS organization, competing in Budapest Major.', 'CS2', 'broky, karrigan, frozen, jcobbb, Twistzz', 'International') ,
('Natus Vincere (CS2)', 'CIS powerhouse in CS2, participating in the Budapest Major.', 'CS2', 'b1t, Aleksib, iM, w0nderful, makazze', 'CIS') ,
('Team Spirit (CS2)', 'Experienced CS2 org, known for consistent tier-1 performances.', 'CS2', 'chopper, donk, sh1ro, zweih, tN1R', 'Eastern Europe / Russia') ,
('G2 Esports (CS2)', 'European CS2 team with strong LAN pedigree.', 'CS2', 'huNter-, malbsMd, HeavyGod, SunPayus, matys', 'Germany / EU') ,
('MOUZ (CS2)', 'German / international squad in CS2 Major.', 'CS2', 'torzsi, xertioN, Jimpphat, Brollan, Spinx', 'Germany / Europe') ,
('Fnatic (CS2)', 'Veteran CS2 squad returning to Major stage.', 'CS2', 'KRIMZ, blameF, fear, jambo, jackasmo', 'UK / EU') ,
('PARIVISION (CS2)', 'Emerging CIS-region CS2 team, qualified for Budapest Major.', 'CS2', 'BELCHONOKK, Jame, nota, xiELO, AW', 'CIS') ,
('RED Canids (CS2)', 'Brazilian CS2 team making Major appearance.', 'CS2', 'venomzera, drop, kauez, history, chayJESUS', 'Brazil') ,
('Legacy Gaming (CS2)', 'Brazil-based team in CS2 Major.', 'CS2', 'latto, dumau, saadzin, n1ssim, lux', 'Brazil') ,
('FlyQuest (CS2)', 'International-Australian team competing in Budapest Major.', 'CS2', 'INS, Vexite, regali, nettik, jks', 'International / USA'),

('NRG Esports (RL)', 'Rocket League team that won RLCS 2025 World Championship.', 'Rocket League', 'Atomic, BeastMode, Daniel', 'USA'),
('Team Falcons (RL)', 'MENA (Middle East / North Africa) Valor-style Rocket League team, Worlds 2025 finalist.', 'Rocket League', 'Trk511, Rw9, Kiileerrz', 'MENA'),
('Karmine Corp (RL)', 'European Rocket League pro team, consistent RLCS contender.', 'Rocket League', 'Vatira, Atow, dralii', 'France'),
('Dignitas (RL)', 'Veteran Rocket League org, qualified for Worlds 2025.', 'Rocket League', 'stizzy, ApparentlyJack, Joreuz', 'USA / EU'),
('Geekay Esports (RL)', 'Asian / European Rocket League squad in Worlds 2025.', 'Rocket League', 'Archie, Joyo, oaly', 'Europe / Asia'),
('The Ultimates (RL)', 'North American Rocket League team, strong LAN presence.', 'Rocket League', 'Firstkiller, Lj, Chronic', 'USA'),
('Spacestation Gaming (RL)', 'US Rocket League org, major RLCS competitor.', 'Rocket League', 'Scrzbbles, reveal, kofyr', 'USA'),
('Wildcard (RL)', 'Rocket League team qualified via open / play-in for Worlds 2025.', 'Rocket League', 'Fever, Torsos, bananahead', 'Oceania / International'),
('FURIA Esports (RL)', 'South American Rocket League organization at Worlds 2025.', 'Rocket League', 'yANXNZ, Lostt, DRUFINHO', 'Brazil'),
('Team Secret (RL)', 'South American / European Rocket League team competing in Worlds 2025.', 'Rocket League', 'kv1, swiftt, Motta', 'Brazil / EU'),
('Twisted Minds (RL)', 'MENA Rocket League team, qualified for Worlds 2025.', 'Rocket League', 'Nwpo, rise., AtomiK', 'MENA'),
('Gen.G Mobil-1 Racing (RL)', 'Rocket League division of Gen.G, making Worlds 2025 appearance.', 'Rocket League', 'MaJicBear, CHEESE., justin', 'USA / South Korea'),
('TSM (Rocket League)', 'North American / global TSM Rocket League squad at Worlds 2025.', 'Rocket League', 'Superlachie, Amphis, kaka', 'USA'),
('Virtus.pro (RL)', 'Rocket League team from Eastern Europe / APAC qualified for Worlds.', 'Rocket League', 'Catalysm, sosa, Sphinx', 'Russia / Europe OR APAC'),
('FUT Esports (RL)', 'Sub-Saharan Africa Rocket League squad competing in Worlds 2025.', 'Rocket League', 'VKSailen, Leoro, TORRES8232', 'SSA (Sub-Saharan Africa)'),
('Shopify Rebellion (RL)', 'North American Rocket League team via last-chance qualifier for Worlds 2025.', 'Rocket League', 'Retals, 2Piece, Wahvey', 'Canada / USA'),
('Ninjas in Pyjamas (RL)', 'European Rocket League org, making play-in / main event at Worlds 2025.', 'Rocket League', 'Radosin, Oski, Nass', 'Sweden / Europe'),
('ROC Esports (RL)', 'MENA Rocket League team, qualified for Worlds via Last Chance.', 'Rocket League', 'Ghaazi, Abdullah, Twiz', 'MENA'),
('MIBR (RL)', 'Brazilian Rocket League org playing at Worlds 2025.', 'Rocket League', 'Brad, Droppz, Reysbull', 'Brazil');


INSERT INTO SeatTier (tierName, basePrice, description) VALUES
('BOX/Enterprise', 500.00, 'A top view within a box where service and customer experience is top level.'),
('VIP', 120.00, 'Front-row leather recliners with table service.'),
('Premium', 80.00, 'Center view, extra legroom.'),
('Standard', 50.00, 'Regular seating, great for general audience.');


INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 1,
       CHAR(65 + FLOOR((n-1)/20)),
       ((n-1)%20)+1,
       CASE WHEN n <= 100 THEN 2 ELSE 3 END
FROM (
    SELECT @row := @row + 1 AS n 
    FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
          UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t1,
         (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
          UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t2,
         (SELECT @row:=0) t0
    LIMIT 500
) AS seats;

INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 2,
       CHAR(65 + FLOOR((n-1)/20)),
       ((n-1)%20)+1,
       CASE WHEN n <= 100 THEN 2 ELSE 3 END
FROM (
    SELECT @r := @r + 1 AS n 
    FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
          UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t1,
         (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
          UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t2,
         (SELECT @r:=0) t0
    LIMIT 500
) AS seats;

INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 3,
       CHAR(65 + FLOOR((n-1)/20)),
       ((n-1)%20)+1,
       CASE WHEN n <= 100 THEN 2 ELSE 3 END
FROM (
    SELECT @c := @c + 1 AS n 
    FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
          UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t1,
         (SELECT 0 UNION ALL SELECT 0 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
          UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t2,
         (SELECT @c:=0) t0
    LIMIT 500
) AS seats;

INSERT INTO Seat (hallID, seatRow, seatNumber, tierID)
SELECT 4,
       CHAR(65 + FLOOR((n-1)/20)),
       ((n-1)%20)+1,
       CASE WHEN n <= 100 THEN 2 ELSE 3 END
FROM (
    SELECT @d := @d + 1 AS n 
    FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
          UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t1,
         (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
          UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t2,
         (SELECT @d:=0) t0
    LIMIT 500
) AS seats;


INSERT INTO Tournament (gameID, tournamentName, startDate, endDate, tournamentDescription) VALUES
(1, 'League of Legends: Worlds 2026', '2026-11-10', '2026-11-20', 'The grand finale of competitive LoL featuring global champions.'),
(2, 'Valorant Masters Chilé', '2026-07-15', '2026-07-25', 'The Twelve best teams in the world will face off for a Valorant Masters Trophy in Chilé'),
(3, 'CS2 Major: London 2026', '2026-09-01', '2026-09-10', 'The premier Counter-Strike 2 Major event of the year.'),
(4, 'Dota 2 International 2026', '2026-10-05', '2026-10-15', 'World’s biggest Dota 2 championship.'),
(5, 'Rocket League World Cup', '2026-06-20', '2026-06-25', 'Fast cars, high-flying goals, global teams.');

-- League of Legends: Worlds 2026 (tournamentID = 1, gameID = 1)
INSERT INTO `Match` (tournamentID, gameID, team1ID, team2ID, matchDate, matchTime, hallID) VALUES
(1, 1, 1, 2, '2026-11-10', '18:00:00', 1),
(1, 1, 3, 4, '2026-11-11', '18:00:00', 1),
(1, 1, 5, 6, '2026-11-12', '18:00:00', 1),
(1, 1, 7, 8, '2026-11-13', '18:00:00', 1),
(1, 1, 9, 10, '2026-11-14', '18:00:00', 1),
(1, 1, 11, 12, '2026-11-15', '18:00:00', 1),
(1, 1, 13, 14, '2026-11-16', '18:00:00', 1),
(1, 1, 15, 1, '2026-11-17', '18:00:00', 1);

-- Valorant Masters Chilé (tournamentID = 2, gameID = 2)
INSERT INTO `Match` (tournamentID, gameID, team1ID, team2ID, matchDate, matchTime, hallID) VALUES
(2, 2, 16, 17, '2026-07-16', '20:00:00', 2),
(2, 2, 18, 19, '2026-07-17', '20:00:00', 2),
(2, 2, 20, 21, '2026-07-18', '20:00:00', 2),
(2, 2, 22, 23, '2026-07-19', '20:00:00', 2),
(2, 2, 24, 25, '2026-07-20', '20:00:00', 2);

-- CS2 Major: London 2026 (tournamentID = 3, gameID = 3)
INSERT INTO `Match` (tournamentID, gameID, team1ID, team2ID, matchDate, matchTime, hallID) VALUES
(3, 3, 26, 27, '2026-09-02', '19:00:00', 3),
(3, 3, 28, 29, '2026-09-03', '19:00:00', 3),
(3, 3, 30, 31, '2026-09-04', '19:00:00', 3),
(3, 3, 32, 33, '2026-09-05', '19:00:00', 3),
(3, 3, 34, 35, '2026-09-06', '19:00:00', 3);

-- Dota 2 International 2026 (tournamentID = 4, gameID = 4)
INSERT INTO `Match` (tournamentID, gameID, team1ID, team2ID, matchDate, matchTime, hallID) VALUES
(4, 4, 36, 37, '2026-10-06', '18:00:00', 1),
(4, 4, 38, 39, '2026-10-07', '18:00:00', 1),
(4, 4, 40, 36, '2026-10-08', '18:00:00', 1),
(4, 4, 37, 38, '2026-10-09', '18:00:00', 1);

-- Rocket League World Cup 2026 (tournamentID = 5, gameID = 6)
INSERT INTO `Match` (tournamentID, gameID, team1ID, team2ID, matchDate, matchTime, hallID) VALUES
(5, 6, 41, 42, '2026-06-21', '17:00:00', 4),
(5, 6, 43, 44, '2026-06-22', '17:00:00', 4),
(5, 6, 45, 46, '2026-06-23', '17:00:00', 4),
(5, 6, 47, 48, '2026-06-24', '17:00:00', 4),
(5, 6, 49, 50, '2026-06-25', '17:00:00', 4);

INSERT INTO Showing (matchID, hallID, showingDate, showingTime) VALUES
-- League of Legends: Worlds 2026
(1, 1, '2026-11-10', '18:00:00'),
(2, 1, '2026-11-11', '18:00:00'),
(3, 1, '2026-11-12', '18:00:00'),
(4, 1, '2026-11-13', '18:00:00'),
(5, 1, '2026-11-14', '18:00:00'),
(6, 1, '2026-11-15', '18:00:00'),
(7, 1, '2026-11-16', '18:00:00'),
(8, 1, '2026-11-17', '18:00:00'),

-- Valorant Masters Chilé
(9, 2, '2026-07-16', '20:00:00'),
(10, 2, '2026-07-17', '20:00:00'),
(11, 2, '2026-07-18', '20:00:00'),
(12, 2, '2026-07-19', '20:00:00'),
(13, 2, '2026-07-20', '20:00:00'),

-- CS2 Major: London 2026
(14, 3, '2026-09-02', '19:00:00'),
(15, 3, '2026-09-03', '19:00:00'),
(16, 3, '2026-09-04', '19:00:00'),
(17, 3, '2026-09-05', '19:00:00'),
(18, 3, '2026-09-06', '19:00:00'),

-- Dota 2 International 2026
(19, 1, '2026-10-06', '18:00:00'),
(20, 1, '2026-10-07', '18:00:00'),
(21, 1, '2026-10-08', '18:00:00'),
(22, 1, '2026-10-09', '18:00:00');



INSERT INTO News (newsTitle, newsContent, newsAuthor, newsImage) VALUES
('Esports Cinema Expands!', 'We are proud to announce the opening of two new halls designed for esports tournaments and screenings.', 'Admin', 'https://via.placeholder.com/600x400'),
('Partnership with Riot Games', 'Our cinema will officially host the 2026 League of Legends Worlds screening events!', 'Admin', 'https://via.placeholder.com/600x400'),
('New VIP Lounges', 'Experience tournaments like never before with luxury seating, in-seat service, and exclusive viewing angles.', 'Admin', 'https://via.placeholder.com/600x400'),
('Community Event: Meet the Pros', 'Fans can meet professional players and streamers during special Q&A sessions.', 'Admin', 'https://via.placeholder.com/600x400'),
('Summer Lineup Announced', 'Valorant Masters and Rocket League World Cup confirmed for this summer!', 'Admin', 'https://via.placeholder.com/600x400');

