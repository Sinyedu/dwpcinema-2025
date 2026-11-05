<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BookingTest extends TestCase
{
    private PDO $pdo;
    private Cinema $cinema;
    private Booking $bookings;
    private int $alice;
    private int $bob;

    protected function setUp(): void
    {
        $this->pdo = TestDatabase::create();
        $this->cinema = Cinema::build($this->pdo);
        $this->bookings = new Booking($this->pdo);
        $this->alice = Users::insert($this->pdo, 'alice@example.com', 'pw-alice-1')['userID'];
        $this->bob = Users::insert($this->pdo, 'bob@example.com', 'pw-bob-123')['userID'];
    }

    private function bookingCount(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM Booking')->fetchColumn();
    }

    private function assertBookingFails(string $messageFragment, callable $attempt): void
    {
        try {
            $attempt();
            $this->fail('Expected a BookingException');
        } catch (BookingException $e) {
            $this->assertStringContainsString($messageFragment, $e->getMessage());
        }
    }

    public function testCreatesABookingAndTotalsThePrice(): void
    {
        $seats = $this->cinema->hallASeats('A', 1, 2); // two VIP seats at 25.00

        $id = $this->bookings->create($this->alice, $this->cinema->showingA, $seats);

        $details = $this->bookings->getDetailsForUser($id, $this->alice);
        $this->assertSame(50.0, (float)$details['totalAmount']);
        $this->assertCount(2, $details['seats']);
        $this->assertSame('A1', $details['seats'][0]['seatLabel']);
        $this->assertSame('Grand Final', $details['matchName']);
    }

    public function testDuplicateSeatIdsAreCollapsed(): void
    {
        $seat = $this->cinema->seatId($this->cinema->hallA, 'B', 1);

        $id = $this->bookings->create($this->alice, $this->cinema->showingA, [$seat, $seat]);

        $this->assertCount(1, $this->bookings->getDetailsForUser($id, $this->alice)['seats']);
    }

    public function testCannotBookASeatAlreadyTakenForTheSameShowing(): void
    {
        $seats = $this->cinema->hallASeats('B', 1, 2);
        $this->bookings->create($this->alice, $this->cinema->showingA, $seats);

        $this->assertBookingFails('already booked', fn() =>
            $this->bookings->create($this->bob, $this->cinema->showingA, $this->cinema->hallASeats('B', 2, 3)));
    }

    public function testFailedBookingLeavesNothingBehind(): void
    {
        $this->bookings->create($this->alice, $this->cinema->showingA, $this->cinema->hallASeats('B', 1, 2));
        $before = $this->bookingCount();

        try {
            $this->bookings->create($this->bob, $this->cinema->showingA, $this->cinema->hallASeats('B', 2, 3));
        } catch (BookingException) {
        }

        $this->assertSame($before, $this->bookingCount());
        $this->assertSame(2, (int)$this->pdo->query('SELECT COUNT(*) FROM Booking_Seat')->fetchColumn());
    }

    public function testTheSameSeatCanBeBookedForADifferentShowing(): void
    {
        $seats = $this->cinema->hallASeats('B', 1, 2);
        $this->bookings->create($this->alice, $this->cinema->showingA, $seats);

        $id = $this->bookings->create($this->bob, $this->cinema->showingA2, $seats);

        $this->assertGreaterThan(0, $id);
    }

    public function testRejectsSeatsFromAnotherHall(): void
    {
        $otherHallSeat = $this->cinema->seatId($this->cinema->hallB, 'A', 1);

        $this->assertBookingFails('not part of this showing', fn() =>
            $this->bookings->create($this->alice, $this->cinema->showingA, [$otherHallSeat]));
        $this->assertSame(0, $this->bookingCount());
    }

    public function testRejectsUnknownSeatIds(): void
    {
        $this->assertBookingFails('not part of this showing', fn() =>
            $this->bookings->create($this->alice, $this->cinema->showingA, [999999]));
    }

    public function testRejectsUnknownShowing(): void
    {
        $this->assertBookingFails('Showing not found', fn() =>
            $this->bookings->create($this->alice, 999999, $this->cinema->hallASeats('A', 1, 1)));
    }

    public function testEnforcesTheSeatRules(): void
    {
        $this->assertBookingFails('up to 5 seats', fn() =>
            $this->bookings->create($this->alice, $this->cinema->showingA, $this->cinema->hallASeats('B', 1, 6)));
        $this->assertBookingFails('same row', fn() =>
            $this->bookings->create($this->alice, $this->cinema->showingA, [
                $this->cinema->seatId($this->cinema->hallA, 'A', 1),
                $this->cinema->seatId($this->cinema->hallA, 'B', 2),
            ]));
        $this->assertBookingFails('consecutive', fn() =>
            $this->bookings->create($this->alice, $this->cinema->showingA, [
                $this->cinema->seatId($this->cinema->hallA, 'B', 1),
                $this->cinema->seatId($this->cinema->hallA, 'B', 3),
            ]));
        $this->assertBookingFails('No seats', fn() =>
            $this->bookings->create($this->alice, $this->cinema->showingA, []));
        $this->assertSame(0, $this->bookingCount());
    }

    public function testBookingsAreOnlyVisibleToTheirOwner(): void
    {
        $id = $this->bookings->create($this->alice, $this->cinema->showingA, $this->cinema->hallASeats('A', 1, 1));

        $this->assertNotNull($this->bookings->getDetailsForUser($id, $this->alice));
        $this->assertNull($this->bookings->getDetailsForUser($id, $this->bob), 'IDOR: Bob must not see Alice\'s booking');
    }

    public function testListingReturnsOnlyTheUsersOwnBookingsWithSeats(): void
    {
        $this->bookings->create($this->alice, $this->cinema->showingA, $this->cinema->hallASeats('A', 1, 2));
        $this->bookings->create($this->alice, $this->cinema->showingA2, $this->cinema->hallASeats('B', 1, 1));
        $this->bookings->create($this->bob, $this->cinema->showingA, $this->cinema->hallASeats('B', 5, 5));

        $list = $this->bookings->listDetailsForUser($this->alice);

        $this->assertCount(2, $list);
        $seatCounts = array_map(fn($b) => count($b['seats']), $list);
        sort($seatCounts); // bookings made in the same second have no stable order
        $this->assertSame([1, 2], $seatCounts);
    }

    public function testSeatMapMarksBookedSeats(): void
    {
        $this->bookings->create($this->alice, $this->cinema->showingA, $this->cinema->hallASeats('A', 1, 2));

        $map = $this->bookings->seatMap($this->cinema->showingA);

        $this->assertCount(20, $map);
        $byLabel = array_column($map, null, 'seatLabel');
        $this->assertTrue($byLabel['A1']['booked']);
        $this->assertTrue($byLabel['A2']['booked']);
        $this->assertFalse($byLabel['A3']['booked']);
    }

    public function testSeatMapIsPerShowing(): void
    {
        $this->bookings->create($this->alice, $this->cinema->showingA, $this->cinema->hallASeats('A', 1, 1));

        $byLabel = array_column($this->bookings->seatMap($this->cinema->showingA2), null, 'seatLabel');

        $this->assertFalse($byLabel['A1']['booked']);
    }

    public function testSeatMapForUnknownShowingIsEmpty(): void
    {
        $this->assertSame([], $this->bookings->seatMap(999999));
    }

    public function testShowingSummaryExposesWhatTheSeatPageNeeds(): void
    {
        $showing = $this->bookings->showingSummary($this->cinema->showingA);

        $this->assertSame('Grand Final', $showing['matchName']);
        $this->assertSame('The Final', $showing['tournamentName']);
        $this->assertSame('Hall A', $showing['hallName']);
        $this->assertNull($this->bookings->showingSummary(999999));
    }
}
