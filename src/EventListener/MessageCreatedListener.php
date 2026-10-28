<?php

namespace SyrtisSymfonyClient\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Wexample\SymfonyAi\Entity\Message;
use Wexample\SymfonyAi\Event\MessageCreatedEvent;
use SyrtisSymfonyClient\Entity\SyrtisSession;
use SyrtisSymfonyClient\Service\SyrtisConversation;
use SyrtisSymfonyClient\Service\ToolResultMessage;

/**
 * Answers the turns written in a Syrtis session: what the account wrote goes
 * to Syrtis, and so does what the page answered to a tool the agent called.
 * A session of another engine is not this listener's.
 */
#[AsEventListener]
final readonly class MessageCreatedListener
{
    public function __construct(
        private SyrtisConversation $conversation,
    ) {
    }

    public function __invoke(MessageCreatedEvent $event): void
    {
        $message = $event->message;

        if (! $message->getSession() instanceof SyrtisSession) {
            return;
        }

        if (Message::TYPE_USER === $message->getType()) {
            $this->conversation->send($message);
        } elseif (Message::TYPE_TOOL === $message->getType() && $result = ToolResultMessage::read($message)) {
            $this->conversation->send($message, ToolResultMessage::toSyrtis($result));
        }
    }
}
