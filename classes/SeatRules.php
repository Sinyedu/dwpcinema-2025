<?php
declare(strict_types=1);

/** The cinema's rules for which seats may be booked together. Pure, no I/O. */
final class SeatRules
{
    public const MAX_SEATS = 5;

    /**
     * @param array<array{seatRow: string, seatNumber: int|string}> $seats
     * @return string|null a user-facing message for the first broken rule, or null if the selection is fine
     */
    public static function violation(array $seats, int $max = self::MAX_SEATS): ?string
    {
        if (count($seats) === 0) {
            return 'No seats selected.';
        }
        if (count($seats) > $max) {
            return "You can only select up to $max seats.";
        }
        if (count(array_unique(array_column($seats, 'seatRow'))) !== 1) {
            return 'Seats must be in the same row.';
        }

        $numbers = array_map('intval', array_column($seats, 'seatNumber'));
        sort($numbers);
        for ($i = 1; $i < count($numbers); $i++) {
            if ($numbers[$i] !== $numbers[$i - 1] + 1) {
                return 'Seats must be consecutive in a horizontal line.';
            }
        }

        return null;
    }
}
