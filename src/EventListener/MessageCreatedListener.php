<?php

namespace SyrtisSymfonyClient\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use SyrtisSymfonyClient\Entity\SyrtisConversation;
use SyrtisSymfonyClient\Service\SyrtisConversationService;
use SyrtisSymfonyClient\Service\ToolResultMessage;
use Wexample\PhpApi\Exceptions\ApiException;
use Wexample\SymfonyConversation\Entity\ConversationMessage;
use Wexample\SymfonyConversation\Entity\UserConversationParticipant;
use Wexample\SymfonyConversation\Event\MessageCreatedEvent;
use Wexample\SymfonyConversation\Repository\MessageRepository;

/**
 * Answers the lines written in a Syrtis conversation by a person: what they
 * wrote goes to Syrtis, and so does what the page answered to a tool the
 * agent called. A conversation of another kind, a line of the agent's, are
 * not this listener's.
 *
 * Syrtis refusing the turn — no such scenario, a key it does not know — is
 * said in the conversation, as an error line, rather than failing the page
 * the person wrote from.
 */
#[AsEventListener]
final readonly class MessageCreatedListener
{
    public function __construct(
        private SyrtisConversationService $conversation,
        private MessageRepository $messages,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(MessageCreatedEvent $event): void
    {
        $message = $event->message;

        if (! $message->getConversation() instanceof SyrtisConversation
            || ! $message->getAuthor() instanceof UserConversationParticipant) {
            return;
        }

        try {
            if (ConversationMessage::TYPE_TEXT === $message->getType()) {
                $this->conversation->send($message);
            } elseif (ConversationMessage::TYPE_TOOL === $message->getType() && $result = ToolResultMessage::read($message)) {
                $this->conversation->send($message, ToolResultMessage::toSyrtis($result));
            }
        } catch (ApiException $exception) {
            $this->logger->error('Syrtis refused a turn of the conversation '.$message->getConversation()->getId().': '.$exception->getMessage());
            $this->messages->saveNewMessageAndPushEvent(
                conversation: $message->getConversation(),
                type: ConversationMessage::TYPE_ERROR,
                body: 'Syrtis could not take this message: '.$exception->getMessage(),
            );
        }
    }
}
