<?php

namespace SyrtisSymfonyClient\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SyrtisSymfonyClient\Repository\SyrtisSessionRepository;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyAi\Entity\Session;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;

/**
 * A conversation held by Syrtis: the account holding it here, the scenario it
 * runs on there, and the session Syrtis opened for it — null until the first
 * message is sent, which is when Syrtis is asked for one.
 */
#[PseudocodeExport(inherited: true)]
#[ORM\Entity(repositoryClass: SyrtisSessionRepository::class)]
#[ORM\Table(name: 'syrtis_session')]
class SyrtisSession extends Session
{
    #[ORM\ManyToOne(targetEntity: UserEntityInterface::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected UserEntityInterface $user;

    /** The scenario's id on the Syrtis the session was opened on. */
    #[ORM\Column(type: Types::STRING, length: 64)]
    protected string $scenario;

    /** The session's own id on Syrtis, the one its messages come back under. */
    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, unique: true)]
    protected ?string $remoteId = null;

    public function __construct(UserEntityInterface $user, string $scenario)
    {
        parent::__construct();

        $this->user = $user;
        $this->scenario = $scenario;
    }

    public function getUser(): UserEntityInterface
    {
        return $this->user;
    }

    public function getScenario(): string
    {
        return $this->scenario;
    }

    public function getRemoteId(): ?string
    {
        return $this->remoteId;
    }

    public function setRemoteId(?string $remoteId): self
    {
        $this->remoteId = $remoteId;

        return $this;
    }
}
