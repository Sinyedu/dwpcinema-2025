USE dwpcinemaDB;
SET default_storage_engine=InnoDB;

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
