<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SupportDeskTest extends TestCase
{
    private PDO $pdo;
    private SupportDesk $desk;
    private int $alice;
    private int $bob;
    private int $admin;

    protected function setUp(): void
    {
        $this->pdo = TestDatabase::create();
        $this->desk = new SupportDesk($this->pdo);
        $this->alice = Users::insert($this->pdo, 'alice@example.com', 'pw-alice-1')['userID'];
        $this->bob = Users::insert($this->pdo, 'bob@example.com', 'pw-bob-123')['userID'];
        $this->admin = Users::insert($this->pdo, 'admin@example.com', 'pw-admin-1', ['isAdmin' => 1])['userID'];
    }

    private function assertDeskRefuses(string $fragment, callable $action): void
    {
        try {
            $action();
            $this->fail('Expected a SupportException');
        } catch (SupportException $e) {
            $this->assertStringContainsString($fragment, $e->getMessage());
        }
    }

    public function testCreatingATicketStoresTheFirstMessageVerbatim(): void
    {
        $id = $this->desk->createTicket($this->alice, "Can't book <b>seats</b> & stuff", 'Help me please', 'high');

        $messages = $this->desk->messagesForUser($id, $this->alice);
        $this->assertSame('Help me please', $messages[0]['message']);
        $this->assertSame('user', $messages[0]['senderRole']);
        $tickets = $this->desk->ticketsForUser($this->alice);
        $this->assertSame("Can't book <b>seats</b> & stuff", $tickets[0]['subject']);
        $this->assertSame('high', $tickets[0]['priority']);
    }

    public function testCreatingValidatesInput(): void
    {
        $this->assertDeskRefuses('Subject', fn() => $this->desk->createTicket($this->alice, '  ', 'msg'));
        $this->assertDeskRefuses('Message', fn() => $this->desk->createTicket($this->alice, 'subj', ' '));
        $this->assertDeskRefuses('Subject', fn() => $this->desk->createTicket($this->alice, str_repeat('x', 256), 'msg'));
        $this->assertDeskRefuses('Message', fn() => $this->desk->createTicket($this->alice, 'subj', str_repeat('x', 5001)));
    }

    public function testUnknownPriorityFallsBackToMedium(): void
    {
        $this->desk->createTicket($this->alice, 'subj', 'msg', 'URGENT; DROP TABLE');

        $this->assertSame('medium', $this->desk->ticketsForUser($this->alice)[0]['priority']);
    }

    public function testUsersOnlySeeTheirOwnTickets(): void
    {
        $this->desk->createTicket($this->alice, 'alice ticket', 'a');
        $this->desk->createTicket($this->bob, 'bob ticket', 'b');

        $subjects = array_column($this->desk->ticketsForUser($this->alice), 'subject');

        $this->assertSame(['alice ticket'], $subjects);
    }

    public function testUserCannotReadSomeoneElsesMessages(): void
    {
        $id = $this->desk->createTicket($this->alice, 'private', 'secret details');

        $this->assertDeskRefuses('not found', fn() => $this->desk->messagesForUser($id, $this->bob));
    }

    public function testUserCannotReplyToSomeoneElsesTicket(): void
    {
        $id = $this->desk->createTicket($this->alice, 'private', 'secret');

        $this->assertDeskRefuses('not found', fn() => $this->desk->replyAsUser($id, $this->bob, 'hijack'));
        $this->assertCount(1, $this->desk->messagesForUser($id, $this->alice));
    }

    public function testMissingTicketLooksTheSameAsSomeoneElsesTicket(): void
    {
        $this->assertDeskRefuses('not found', fn() => $this->desk->messagesForUser(999999, $this->alice));
    }

    public function testUserCanReplyAfterAnAdminReply(): void
    {
        $id = $this->desk->createTicket($this->alice, 'subj', 'first');
        $this->desk->replyAsAdmin($id, $this->admin, 'we are on it');

        $this->desk->replyAsUser($id, $this->alice, 'thanks');

        $this->assertSame(['first', 'we are on it', 'thanks'], array_column($this->desk->messagesForUser($id, $this->alice), 'message'));
    }

    public function testUserCannotSendMoreThanThreeInARowWithoutAnAdminReply(): void
    {
        $id = $this->desk->createTicket($this->alice, 'subj', 'one');
        $this->desk->replyAsUser($id, $this->alice, 'two');
        $this->desk->replyAsUser($id, $this->alice, 'three');

        $this->assertDeskRefuses('wait for', fn() => $this->desk->replyAsUser($id, $this->alice, 'four'));
    }

    public function testCannotReplyToAClosedTicket(): void
    {
        $id = $this->desk->createTicket($this->alice, 'subj', 'one');
        $this->desk->setStatus($id, 'closed');

        $this->assertDeskRefuses('closed', fn() => $this->desk->replyAsUser($id, $this->alice, 'hello?'));
        $this->assertDeskRefuses('closed', fn() => $this->desk->replyAsAdmin($id, $this->admin, 'hello?'));
    }

    public function testReopeningAllowsRepliesAgain(): void
    {
        $id = $this->desk->createTicket($this->alice, 'subj', 'one');
        $this->desk->setStatus($id, 'closed');
        $this->desk->setStatus($id, 'open');

        $this->desk->replyAsAdmin($id, $this->admin, 'back');

        $this->assertCount(2, $this->desk->messagesForUser($id, $this->alice));
    }

    public function testRejectsEmptyReplies(): void
    {
        $id = $this->desk->createTicket($this->alice, 'subj', 'one');

        $this->assertDeskRefuses('Message', fn() => $this->desk->replyAsUser($id, $this->alice, '   '));
    }

    public function testRejectsUnknownStatuses(): void
    {
        $id = $this->desk->createTicket($this->alice, 'subj', 'one');

        $this->assertDeskRefuses('status', fn() => $this->desk->setStatus($id, 'banana'));
    }

    public function testUnreadCountCountsOnlyAdminMessagesOnMyTickets(): void
    {
        $mine = $this->desk->createTicket($this->alice, 'mine', 'a');
        $theirs = $this->desk->createTicket($this->bob, 'theirs', 'b');
        $this->desk->replyAsAdmin($mine, $this->admin, 'reply 1');
        $this->desk->replyAsAdmin($mine, $this->admin, 'reply 2');
        $this->desk->replyAsAdmin($theirs, $this->admin, 'for bob');

        $this->assertSame(2, $this->desk->unreadCountForUser($this->alice));
        $this->assertSame(1, $this->desk->unreadCountForUser($this->bob));
    }

    public function testReadingMessagesMarksAdminRepliesRead(): void
    {
        $id = $this->desk->createTicket($this->alice, 'mine', 'a');
        $this->desk->replyAsAdmin($id, $this->admin, 'reply');

        $this->desk->messagesForUser($id, $this->alice);

        $this->assertSame(0, $this->desk->unreadCountForUser($this->alice));
    }

    public function testReadingSomeoneElsesTicketDoesNotClearTheirUnreadCount(): void
    {
        $id = $this->desk->createTicket($this->alice, 'mine', 'a');
        $this->desk->replyAsAdmin($id, $this->admin, 'reply');

        try {
            $this->desk->messagesForUser($id, $this->bob);
        } catch (SupportException) {
        }

        $this->assertSame(1, $this->desk->unreadCountForUser($this->alice));
    }

    public function testAdminSeesAllTicketsAndAnyConversation(): void
    {
        $a = $this->desk->createTicket($this->alice, 'a', 'msg a');
        $this->desk->createTicket($this->bob, 'b', 'msg b');

        $this->assertCount(2, $this->desk->allTickets());
        $this->assertSame('msg a', $this->desk->messagesForAdmin($a)[0]['message']);
    }

    public function testAdminReadingDoesNotMarkUserMessagesOrAdminMessagesRead(): void
    {
        $id = $this->desk->createTicket($this->alice, 'a', 'msg');
        $this->desk->replyAsAdmin($id, $this->admin, 'reply');

        $this->desk->messagesForAdmin($id);

        $this->assertSame(1, $this->desk->unreadCountForUser($this->alice));
    }
}
