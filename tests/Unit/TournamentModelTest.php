<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TournamentModelTest extends TestCase
{
    public function testGetNameReturnsTheNameOrNull(): void
    {
        $pdo = TestDatabase::create();
        $cinema = Cinema::build($pdo);
        $tournaments = new Tournament($pdo);

        $this->assertSame('The Final', $tournaments->getName($cinema->tournament));
        $this->assertNull($tournaments->getName(999999));
    }
}
