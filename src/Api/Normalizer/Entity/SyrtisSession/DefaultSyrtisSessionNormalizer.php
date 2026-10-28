<?php

namespace SyrtisSymfonyClient\Api\Normalizer\Entity\SyrtisSession;

use ArrayObject;
use Wexample\SymfonyAi\Api\Normalizer\Entity\Session\DefaultSessionNormalizer;
use SyrtisSymfonyClient\Api\Dto\SyrtisSessionDto;
use SyrtisSymfonyClient\Entity\SyrtisSession;
use SyrtisSymfonyClient\Entity\Traits\Manipulator\SyrtisSessionEntityManipulatorTrait;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;

/**
 * A Syrtis session under the session envelope: the kind's own words added to
 * every session's, and the engine named in the row.
 */
class DefaultSyrtisSessionNormalizer extends DefaultSessionNormalizer
{
    use SyrtisSessionEntityManipulatorTrait;

    public function normalizeEntity(
        SyrtisSession|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return SyrtisSessionDto::fromEntity($entity);
    }
}
