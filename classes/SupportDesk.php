<?php
declare(strict_types=1);

/**
 * The support-ticket module: all rules about who may see and do what.
 * User-facing methods always take the acting user's id and refuse anything that
 * isn't theirs; admin-facing methods are only reachable behind the admin guard.
 *
 * Text is stored verbatim; callers escape on output.
 */
final class SupportDesk
{
    public const STATUSES = ['open', 'pending', 'resolved', 'closed'];
    public const PRIORITIES = ['low', 'medium', 'high'];
    public const MAX_CONSECUTIVE_USER_MESSAGES = 3;
    private const SUBJECT_MAX = 255;
    private const MESSAGE_MAX = 5000;

    private SupportModel $store;

    public function __construct(PDO $pdo)
    {
        $this->store = new SupportModel($pdo);
    }

    // ---- user side -------------------------------------------------------

    public function createTicket(int $userID, string $subject, string $message, string $priority = 'medium'): int
    {
        $subject = trim($subject);
        if ($subject === '' || mb_strlen($subject) > self::SUBJECT_MAX) {
            throw new SupportException('Subject is required (max ' . self::SUBJECT_MAX . ' characters).');
        }
        $message = $this->cleanMessage($message);
        $priority = in_array($priority, self::PRIORITIES, true) ? $priority : 'medium';

        $ticketID = $this->store->createTicket($userID, $subject, $priority);
        $this->store->addMessage($ticketID, $userID, 'user', $message);
        return $ticketID;
    }

    public function ticketsForUser(int $userID): array
    {
        return $this->store->getUserTickets($userID);
    }

    /** Returns the conversation and marks the admin's replies as read. */
    public function messagesForUser(int $ticketID, int $userID): array
    {
        $this->ownedTicket($ticketID, $userID);
        $this->store->markAdminMessagesRead($ticketID);
        return $this->store->getMessages($ticketID);
    }

    public function replyAsUser(int $ticketID, int $userID, string $message): void
    {
        $ticket = $this->ownedTicket($ticketID, $userID);
        $message = $this->cleanMessage($message);
        $this->assertOpen($ticket);
        if ($this->store->countTrailingUserMessages($ticketID) >= self::MAX_CONSECUTIVE_USER_MESSAGES) {
            throw new SupportException('Please wait for an admin reply.');
        }

        $this->store->addMessage($ticketID, $userID, 'user', $message);
    }

    public function unreadCountForUser(int $userID): int
    {
        return $this->store->countUnreadAdminMessages($userID);
    }

    // ---- admin side ------------------------------------------------------

    public function allTickets(): array
    {
        return $this->store->getAllTickets();
    }

    public function messagesForAdmin(int $ticketID): array
    {
        $this->existingTicket($ticketID);
        return $this->store->getMessages($ticketID);
    }

    public function replyAsAdmin(int $ticketID, int $adminID, string $message): void
    {
        $ticket = $this->existingTicket($ticketID);
        $message = $this->cleanMessage($message);
        $this->assertOpen($ticket);

        $this->store->addMessage($ticketID, $adminID, 'admin', $message);
    }

    public function setStatus(int $ticketID, string $status): void
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new SupportException('Unknown ticket status.');
        }
        $this->existingTicket($ticketID);
        $this->store->updateStatus($ticketID, $status);
    }

    public function statusOf(int $ticketID): ?string
    {
        return $this->store->getTicket($ticketID)['status'] ?? null;
    }

    // ---- internals -------------------------------------------------------

    /** Missing and not-yours are indistinguishable on purpose: don't reveal which ids exist. */
    private function ownedTicket(int $ticketID, int $userID): array
    {
        $ticket = $this->store->getTicket($ticketID);
        if ($ticket === null || (int)$ticket['userID'] !== $userID) {
            throw new SupportException('Ticket not found.');
        }
        return $ticket;
    }

    private function existingTicket(int $ticketID): array
    {
        return $this->store->getTicket($ticketID) ?? throw new SupportException('Ticket not found.');
    }

    private function assertOpen(array $ticket): void
    {
        if ($ticket['status'] === 'closed') {
            throw new SupportException('This ticket is closed.');
        }
    }

    private function cleanMessage(string $message): string
    {
        $message = trim($message);
        if ($message === '' || mb_strlen($message) > self::MESSAGE_MAX) {
            throw new SupportException('Message is required (max ' . self::MESSAGE_MAX . ' characters).');
        }
        return $message;
    }
}
