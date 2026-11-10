<?php

namespace SyrtisSymfonyClient\Service;

use Doctrine\ORM\EntityManagerInterface;
use SyrtisClient\Common\SyrtisClient;
use SyrtisClient\Repository\SessionRepository as RemoteSessionRepository;
use SyrtisSymfonyClient\Entity\SyrtisConversation;
use SyrtisSymfonyClient\Interface\ScenarioResolverInterface;
use SyrtisSymfonyClient\Repository\SyrtisConversationRepository;
use Wexample\SymfonyAiConversation\Service\AgentConversationService;
use Wexample\SymfonyConversation\Entity\ConversationMessage;
use Wexample\SymfonyConversation\Service\ConversationService;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;

/**
 * The conversations of people with Syrtis: the one held here, its two
 * participants — the person, the scenario's agent —, and what is sent there.
 *
 * Syrtis is asked for a session of its own the first time something is sent,
 * not when the conversation is opened here: a conversation nobody spoke in
 * costs it nothing.
 */
final readonly class SyrtisConversationService
{
    public function __construct(
        private SyrtisClient $client,
        private SyrtisConversationRepository $conversations,
        private ConversationService $conversationService,
        private AgentConversationService $agentConversations,
        private EntityManagerInterface $entityManager,
        private ScenarioResolverInterface $scenarios,
    ) {
    }

    /** The conversation the chat opens on for a person: the one they last spoke in, or a new one. */
    public function current(UserEntityInterface $user): SyrtisConversation
    {
        return $this->conversations->findLastForUser($user, $this->scenarios->getScenario())
            ?? $this->open($user);
    }

    public function open(UserEntityInterface $user): SyrtisConversation
    {
        $conversation = new SyrtisConversation($this->scenarios->getScenario());
        $this->entityManager->persist($conversation);
        $this->conversationService->join($conversation, $user);
        $this->agentConversations->agentParticipant($conversation);
        $this->entityManager->flush();

        return $conversation;
    }

    /**
     * What the person wrote, sent to Syrtis as one turn of its session. What
     * the agent answers comes back on the callback, not here.
     *
     * @param array<string, mixed>|null $payload The message as Syrtis takes
     *     it, in place of the plain text: a tool result, with its stamps.
     */
    public function send(ConversationMessage $message, ?array $payload = null): void
    {
        /** @var SyrtisConversation $conversation */
        $conversation = $message->getConversation();

        // What the person wrote is part of the conversation the agent reads
        // back: stamped `history`, as the scenario expects of a turn.
        $this->remoteSessions()->sendMessage(
            sessionSecureId: $this->remoteId($conversation),
            messages: [$payload ?? [
                'content' => $message->getBody(),
                'stamps' => [ToolResultMessage::STAMP_HISTORY],
            ]],
        );
    }

    private function remoteId(SyrtisConversation $conversation): string
    {
        if (null === $conversation->getRemoteId()) {
            $remote = $this->remoteSessions()->createSession(
                scenarioSecureId: $conversation->getScenario(),
                title: $conversation->getTitle(),
            );

            $conversation->setRemoteId($remote->getId());
            $this->entityManager->flush();
        }

        return $conversation->getRemoteId();
    }

    private function remoteSessions(): RemoteSessionRepository
    {
        /** @var RemoteSessionRepository $repository */
        $repository = $this->client->getRepository('session');

        return $repository;
    }
}
