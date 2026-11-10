<?php

namespace SyrtisSymfonyClient\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use SyrtisSymfonyClient\Entity\SyrtisConversationSession;
use SyrtisSymfonyClient\Service\SyrtisConversation;
use SyrtisSymfonyClient\Service\ToolResultMessage;
use Wexample\PhpApi\Exceptions\ApiException;
use Wexample\SymfonyAiConversation\Entity\ConversationMessage;
use Wexample\SymfonyAiConversation\Event\MessageCreatedEvent;
use Wexample\SymfonyAiConversation\Repository\MessageRepository;

/**
 * Answers the turns written in a Syrtis session: what the account wrote goes
 * to Syrtis, and so does what the page answered to a tool the agent called.
 * A session of another engine is not this listener's.
 *
 * Syrtis refusing the turn — no such scenario, a key it does not know — is
 * said in the conversation, as an error turn, rather than failing the page
 * the account wrote from.
 */
#[AsEventListener]
final readonly class MessageCreatedListener
{
    public function __construct(
        private SyrtisConversation $conversation,
        private MessageRepository $messages,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(MessageCreatedEvent $event): void
    {
        $message = $event->message;

        if (! $message->getSession() instanceof SyrtisConversationSession) {
            return;
        }

        try {
            if (ConversationMessage::TYPE_USER === $message->getType()) {
                $this->conversation->send($message);
            } elseif (ConversationMessage::TYPE_TOOL === $message->getType() && $result = ToolResultMessage::read($message)) {
                $this->conversation->send($message, ToolResultMessage::toSyrtis($result));
            }
        } catch (ApiException $exception) {
            $this->logger->error('Syrtis refused a turn of the conversation '.$message->getSession()->getId().': '.$exception->getMessage());
            $this->messages->saveNewMessageAndPushEvent(
                $message->getSession(),
                ConversationMessage::TYPE_ERROR,
                'Syrtis could not take this message: '.$exception->getMessage(),
            );
        }
    }
}
