<?php

namespace SyrtisSymfonyClient\Entity\Traits\Manipulator;

use SyrtisSymfonyClient\Entity\SyrtisConversation;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait SyrtisConversationEntityManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return SyrtisConversation::class;
    }
}
