<?php

namespace SyrtisSymfonyClient\Api\Dto;

use Wexample\SymfonyAiConversation\Api\Dto\AgentConversationDto;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * What a Syrtis conversation says beyond an agent's: the scenario it runs on,
 * and whether Syrtis has opened it yet.
 */
class SyrtisConversationDto extends AgentConversationDto
{
    public string $scenario;

    public ?string $remoteId;

    /**
     * @param \SyrtisSymfonyClient\Entity\SyrtisConversation $entity
     */
    public static function fromConversation(AbstractEntity $entity, array $participants): static
    {
        $dto = parent::fromConversation($entity, $participants);

        $dto->scenario = $entity->getScenario();
        $dto->remoteId = $entity->getRemoteId();

        return $dto;
    }
}
