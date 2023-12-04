<?php

namespace App\Services\Imap;

use DateTimeImmutable;
use Ddeboer\Imap\MessageIterator;
use Ddeboer\Imap\Search\Date\On;
use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Server;

class ImapParseEmailService
{
    private Server $server;

    private $connection;

    public function __construct(
        private string $imapHost,
        private string $userName,
        private string $password
    ) {
        $this->server = new Server($imapHost);
        $this->connection = $this->server->authenticate($userName, $password);
    }

    /**
     * Retrieves an array of messages from the specified mailbox.
     *
     * @param  string  $mailbox_name The name of the mailbox to retrieve messages from. Default is 'INBOX'.
     * @return array An array of messages.
     */
    public function getMessages(string $mailbox_name = 'INBOX'): array
    {
        $mailbox = $this->connection->getMailbox($mailbox_name);
        $messages = $mailbox->getMessages();
        $messagesData = [];
        foreach ($messages as $message) {
            $messagesData[] = $message;
        }

        return $messagesData;
    }

    /**
     * Retrieves messages from a specific mailbox by date.
     *
     * @param  string  $mailbox_name The name of the mailbox. Defaults to 'INBOX'.
     * @param  string  $date The date to filter the messages by. Defaults to null.
     * @return MessageIterator The iterator containing the retrieved messages.
     */
    public function getMessagesByDate(string $mailbox_name = 'INBOX', ?string $date = null): MessageIterator
    {
        $mailbox = $this->connection->getMailbox($mailbox_name);
        $search = new SearchExpression();
        if ($date) {
            $today = new DateTimeImmutable($date);
        } else {
            $today = new DateTimeImmutable();
        }
        $search->addCondition(new On($today));
        $messages = $mailbox->getMessages($search, null, false, 'utf-8');

        return $messages;
    }

    /**
     * Retrieves the mailboxes from the connection.
     *
     * @return array An array of mailboxes.
     */
    public function getMailboxes()
    {
        return $this->connection->getMailboxes();
    }
}
