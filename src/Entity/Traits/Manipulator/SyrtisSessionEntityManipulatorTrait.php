<?php

namespace SyrtisSymfonyClient\Entity\Traits\Manipulator;

use SyrtisSymfonyClient\Entity\SyrtisSession;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait SyrtisSessionEntityManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return SyrtisSession::class;
    }
}
