<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_user();

// Only reachable by submitting the seat form. A GET (refresh, bookmark) goes back to the showings.
if (!is_post()) {
    redirect('showings.php');
}
require_csrf();

$showingID = (int)($_POST['showingID'] ?? 0);
$seatIDs = array_filter((array)($_POST['seatIDs'] ?? []), 'is_scalar');

try {
    $bookingID = (new Booking(db()))->create($auth->userId(), $showingID, array_map('intval', $seatIDs));
    redirect('booking_success.php?bookingID=' . $bookingID);
} catch (BookingException $e) {
    // A refusal the user can act on (seat taken, too many seats, ...): send them back with the reason.
    $_SESSION['booking_error'] = $e->getMessage();
    redirect('view_showing.php?showingID=' . $showingID);
}
