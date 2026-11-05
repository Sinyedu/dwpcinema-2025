<?php
declare(strict_types=1);

/** Builds an in-memory SQLite database mirroring the tables the app code queries. */
final class TestDatabase
{
    public static function create(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));
        $pdo->sqliteCreateFunction('CONCAT', fn(...$parts) => implode('', $parts));
        foreach (self::schema() as $sql) {
            $pdo->exec($sql);
        }
        return $pdo;
    }

    /** @return string[] */
    private static function schema(): array
    {
        return [
            'CREATE TABLE "User" (
                userID INTEGER PRIMARY KEY AUTOINCREMENT,
                firstName TEXT NOT NULL, lastName TEXT NOT NULL,
                userEmail TEXT NOT NULL UNIQUE,
                isAdmin INTEGER DEFAULT 0,
                passwordHash TEXT NOT NULL,
                avatar TEXT DEFAULT NULL,
                isActive INTEGER DEFAULT 1,
                lastActive TEXT DEFAULT NULL,
                lastReservationAt TEXT DEFAULT NULL
            )',
            'CREATE TABLE Game (gameID INTEGER PRIMARY KEY AUTOINCREMENT, gameName TEXT, gameGenre TEXT)',
            'CREATE TABLE Tournament (tournamentID INTEGER PRIMARY KEY AUTOINCREMENT, gameID INTEGER, tournamentName TEXT, startDate TEXT, endDate TEXT, isHidden INTEGER DEFAULT 0, tournamentDescription TEXT)',
            'CREATE TABLE "Match" (matchID INTEGER PRIMARY KEY AUTOINCREMENT, tournamentID INTEGER, gameID INTEGER, matchName TEXT, matchDate TEXT, matchTime TEXT, hallID INTEGER)',
            'CREATE TABLE Hall (hallID INTEGER PRIMARY KEY AUTOINCREMENT, hallName TEXT, totalSeats INTEGER)',
            'CREATE TABLE SeatTier (tierID INTEGER PRIMARY KEY AUTOINCREMENT, tierName TEXT, basePrice REAL)',
            'CREATE TABLE Seat (
                seatID INTEGER PRIMARY KEY AUTOINCREMENT, hallID INTEGER NOT NULL,
                seatRow TEXT NOT NULL, seatNumber INTEGER NOT NULL, tierID INTEGER NOT NULL
            )',
            'CREATE TABLE Showing (
                showingID INTEGER PRIMARY KEY AUTOINCREMENT, matchID INTEGER, hallID INTEGER NOT NULL,
                showingDate TEXT, showingTime TEXT
            )',
            'CREATE TABLE Booking (
                bookingID INTEGER PRIMARY KEY AUTOINCREMENT, userID INTEGER NOT NULL, showingID INTEGER NOT NULL,
                bookingDate TEXT DEFAULT CURRENT_TIMESTAMP, totalAmount REAL DEFAULT 0
            )',
            'CREATE TABLE Booking_Seat (bookingID INTEGER, seatID INTEGER, PRIMARY KEY (bookingID, seatID))',
            // Mirrors trg_bookingseat_after_insert in SQL/createDB.sql.
            'CREATE TRIGGER trg_bookingseat_after_insert AFTER INSERT ON Booking_Seat
             BEGIN
                UPDATE Booking SET totalAmount = totalAmount + (
                    SELECT st.basePrice FROM Seat s JOIN SeatTier st ON s.tierID = st.tierID WHERE s.seatID = NEW.seatID
                ) WHERE bookingID = NEW.bookingID;
             END',
            'CREATE TABLE SupportTicket (
                ticketID INTEGER PRIMARY KEY AUTOINCREMENT, userID INTEGER NOT NULL, subject TEXT NOT NULL,
                status TEXT DEFAULT "open", priority TEXT DEFAULT "medium",
                createdAt TEXT DEFAULT CURRENT_TIMESTAMP, updatedAt TEXT DEFAULT CURRENT_TIMESTAMP
            )',
            'CREATE TABLE SupportMessage (
                messageID INTEGER PRIMARY KEY AUTOINCREMENT, ticketID INTEGER NOT NULL, senderID INTEGER NOT NULL,
                senderRole TEXT NOT NULL, message TEXT NOT NULL, isRead INTEGER DEFAULT 0,
                createdAt TEXT DEFAULT CURRENT_TIMESTAMP
            )',
        ];
    }
}
