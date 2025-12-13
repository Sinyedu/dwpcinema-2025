<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/models/Booking.php';
require_once __DIR__ . '/controllers/BookingController.php';

$pdo = Database::getInstance();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['showingID'], $_POST['seatIDs'])) {
    $userID = $_SESSION['user_id'];
    $showingID = (int)$_POST['showingID'];
    $seatIDs = array_map('intval', $_POST['seatIDs']);
    $seatIDs = array_unique($seatIDs);

    if (count($seatIDs) === 0) {
        $error = "No seats selected.";
    } elseif (count($seatIDs) > 5) {
        $error = "You can only select up to 5 seats.";
    } else {
        try {
            $placeholders = implode(',', array_fill(0, count($seatIDs), '?'));
            $checkStmt = $pdo->prepare("
                SELECT bs.seatID
                FROM Booking_Seat bs
                JOIN Booking b ON bs.bookingID = b.bookingID
                WHERE b.showingID = ? AND bs.seatID IN ($placeholders)
            ");
            $checkStmt->execute(array_merge([$showingID], $seatIDs));
            $alreadyBooked = $checkStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($alreadyBooked)) {
                $error = "Some seats are already booked: " . implode(', ', $alreadyBooked);
            } else {
                $seatStmt = $pdo->prepare("
                    SELECT seatID, seatRow, seatNumber
                    FROM Seat
                    WHERE seatID IN ($placeholders)
                    ORDER BY seatRow, seatNumber
                ");
                $seatStmt->execute($seatIDs);
                $seats = $seatStmt->fetchAll(PDO::FETCH_ASSOC);

                $rows = array_column($seats, 'seatRow');
                $numbers = array_column($seats, 'seatNumber');

                if (count(array_unique($rows)) !== 1) {
                    $error = "Seats must be in the same row.";
                } else {
                    sort($numbers);
                    for ($i = 1; $i < count($numbers); $i++) {
                        if ($numbers[$i] !== $numbers[$i - 1] + 1) {
                            $error = "Seats must be consecutive in a horizontal line.";
                            break;
                        }
                    }
                }

                if (!$error) {
                    $bookingModel = new Booking($pdo);
                    $controller = new BookingController($bookingModel);
                    $bookingID = $controller->book($userID, $showingID, $seatIDs);

                    $success = "Booking successful! Your booking ID is $bookingID.";
                    header("Location: booking_success.php?bookingID=$bookingID");
                    exit;
                }
            }
        } catch (Exception $e) {
            $error = "Booking failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen">
    <div class="max-w-4xl mx-auto px-6 py-10">
        <?php if ($success): ?>
            <p class="mb-4 p-2 bg-green-100 text-green-800 rounded"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="mb-4 p-2 bg-red-100 text-red-800 rounded"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <a href="view_showing.php?showingID=<?= $showingID ?>" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Back to Seat Selection</a>
    </div>

    <script>
        const seatForm = document.getElementById('seatForm');
        if (seatForm) {
            seatForm.addEventListener('submit', function() {
                this.querySelector('button[type="submit"]').disabled = true;
            });
        }
    </script>
</body>

</html>