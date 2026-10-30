<?php

namespace SyrtisSymfonyClient\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use SyrtisSymfonyClient\Entity\SyrtisConversationSession;
use SyrtisSymfonyClient\Service\SyrtisConversation;
use SyrtisSymfonyClient\Service\ToolResultMessage;
use Wexample\SymfonyAiConversation\Entity\ConversationMessage;
use Wexample\SymfonyAiConversation\Event\MessageCreatedEvent;

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

        if (! $message->getSession() instanceof SyrtisConversationSession) {
            return;
        }

        if (ConversationMessage::TYPE_USER === $message->getType()) {
            $this->conversation->send($message);
        } elseif (ConversationMessage::TYPE_TOOL === $message->getType() && $result = ToolResultMessage::read($message)) {
            $this->conversation->send($message, ToolResultMessage::toSyrtis($result));
        }
    }
}
