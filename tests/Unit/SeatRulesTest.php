<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SeatRulesTest extends TestCase
{
    private static function seats(string $row, int ...$numbers): array
    {
        return array_map(fn(int $n) => ['seatRow' => $row, 'seatNumber' => $n], $numbers);
    }

    #[DataProvider('acceptable')]
    public function testAcceptsValidSelections(array $seats): void
    {
        $this->assertNull(SeatRules::violation($seats));
    }

    public static function acceptable(): array
    {
        return [
            'single seat'             => [self::seats('A', 4)],
            'consecutive pair'        => [self::seats('B', 3, 4)],
            'unordered but adjacent'  => [self::seats('B', 5, 3, 4)],
            'exactly five'            => [self::seats('C', 1, 2, 3, 4, 5)],
        ];
    }

    public function testRejectsEmptySelection(): void
    {
        $this->assertSame('No seats selected.', SeatRules::violation([]));
    }

    public function testRejectsMoreThanFive(): void
    {
        $this->assertSame(
            'You can only select up to 5 seats.',
            SeatRules::violation(self::seats('A', 1, 2, 3, 4, 5, 6))
        );
    }

    public function testRejectsSeatsSpanningRows(): void
    {
        $seats = [...self::seats('A', 1), ...self::seats('B', 2)];

        $this->assertSame('Seats must be in the same row.', SeatRules::violation($seats));
    }

    public function testRejectsGaps(): void
    {
        $this->assertSame(
            'Seats must be consecutive in a horizontal line.',
            SeatRules::violation(self::seats('A', 1, 2, 4))
        );
    }

    public function testNumbersFromTheDatabaseAsStringsAreHandled(): void
    {
        $seats = [['seatRow' => 'A', 'seatNumber' => '3'], ['seatRow' => 'A', 'seatNumber' => '4']];

        $this->assertNull(SeatRules::violation($seats));
    }

    public function testCustomMaximum(): void
    {
        $this->assertNotNull(SeatRules::violation(self::seats('A', 1, 2, 3), max: 2));
    }
}
