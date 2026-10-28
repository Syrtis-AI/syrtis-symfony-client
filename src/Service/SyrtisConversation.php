<?php

namespace SyrtisSymfonyClient\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use SyrtisClient\Common\SyrtisClient;
use SyrtisClient\Repository\SessionRepository as RemoteSessionRepository;
use SyrtisSymfonyClient\DependencyInjection\SyrtisSymfonyClientExtension;
use SyrtisSymfonyClient\Entity\SyrtisSession;
use SyrtisSymfonyClient\Repository\SyrtisSessionRepository;
use Wexample\SymfonyAi\Entity\Message;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;

/**
 * The conversation of an account with Syrtis: the session it is held in
 * here, and what is sent there.
 *
 * Syrtis is asked for a session of its own the first time something is sent,
 * not when the conversation is opened here: a conversation nobody spoke in
 * costs it nothing.
 */
final readonly class SyrtisConversation
{
    public function __construct(
        private SyrtisClient $client,
        private SyrtisSessionRepository $sessions,
        private EntityManagerInterface $entityManager,
        #[Autowire(param: SyrtisSymfonyClientExtension::PARAMETER_SCENARIO)]
        private string $scenario,
    ) {
    }

    /**
     * The conversation the chat opens on for an account: the one it last
     * spoke in, or a new one.
     */
    public function current(UserEntityInterface $user): SyrtisSession
    {
        return $this->sessions->findLastForUser($user, $this->scenario)
            ?? $this->open($user);
    }

    public function open(UserEntityInterface $user): SyrtisSession
    {
        $session = $this->sessions->createNewSyrtisSession($user, $this->scenario);
        $session->setDateCreated(new \DateTime());

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return $session;
    }

    /**
     * What the account wrote, sent to Syrtis as one turn of its session. What
     * the agent answers comes back on the callback, not here.
     *
     * @param array<string, mixed>|null $payload The message as Syrtis takes
     *     it, in place of the plain text: a tool result, with its stamps.
     */
    public function send(Message $message, ?array $payload = null): void
    {
        /** @var SyrtisSession $session */
        $session = $message->getSession();

        // What the account wrote is part of the conversation the agent reads
        // back: stamped `history`, as the scenario expects of a turn.
        $this->remoteSessions()->sendMessage(
            sessionSecureId: $this->remoteId($session),
            messages: [$payload ?? [
                'content' => $message->getBody(),
                'stamps' => [ToolResultMessage::STAMP_HISTORY],
            ]],
        );
    }

    private function remoteId(SyrtisSession $session): string
    {
        if (null === $session->getRemoteId()) {
            $remote = $this->remoteSessions()->createSession(
                scenarioSecureId: $session->getScenario(),
                title: $session->getName(),
            );

            $session->setRemoteId($remote->getId());
            $this->entityManager->flush();
        }

        return $session->getRemoteId();
    }

    private function remoteSessions(): RemoteSessionRepository
    {
        /** @var RemoteSessionRepository $repository */
        $repository = $this->client->getRepository('session');

        return $repository;
    }
}
