<?php

namespace SyrtisSymfonyClient\Api\Dto;

use SyrtisSymfonyClient\Entity\SyrtisSession;
use Wexample\SymfonyAi\Api\Dto\SessionDto;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * What a Syrtis session says beyond what every session does: the scenario it
 * runs on, and whether Syrtis has opened it yet. The account is not said:
 * whoever reads the session is the account, or an administrator who knows.
 */
class SyrtisSessionDto extends SessionDto
{
    public string $scenario;

    public ?string $remoteId;

    /**
     * @param SyrtisSession $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->scenario = $entity->getScenario();
        $dto->remoteId = $entity->getRemoteId();

        return $dto;
    }
}
