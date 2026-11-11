<?php

namespace SyrtisSymfonyClient\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SyrtisSymfonyClient\Repository\SyrtisConversationRepository;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyAiConversation\Entity\AgentConversation;

/**
 * A conversation Syrtis holds: the scenario it runs on there, and the session
 * Syrtis opened for it — null until the first message is sent, which is when
 * Syrtis is asked for one. The person talking to it and the scenario's agent
 * are its participants.
 */
#[PseudocodeExport(inherited: true)]
#[ORM\Entity(repositoryClass: SyrtisConversationRepository::class)]
#[ORM\Table(name: 'syrtis_conversation')]
class SyrtisConversation extends AgentConversation
{
    /** What the agent's qualified name starts with: Syrtis runs it. */
    final public const string AGENT_PREFIX = 'syrtis:';

    /** The scenario's id on the Syrtis the session was opened on. */
    #[ORM\Column(type: Types::STRING, length: 64)]
    protected string $scenario;

    /** The session's own id on Syrtis, the one its messages come back under. */
    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, unique: true)]
    protected ?string $remoteId = null;

    public function __construct(string $scenario)
    {
        parent::__construct();

        $this->scenario = $scenario;
        // The agent is the scenario: what the conversation names its agent by
        // when the app holds no record of it.
        $this->setAgentName(self::AGENT_PREFIX.$scenario);
    }

    public function getScenario(): string
    {
        return $this->scenario;
    }

    public function getRemoteId(): ?string
    {
        return $this->remoteId;
    }

    public function setRemoteId(?string $remoteId): static
    {
        $this->remoteId = $remoteId;

        return $this;
    }
}
