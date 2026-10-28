<?php

namespace SyrtisSymfonyClient\Traits;

use SyrtisSymfonyClient\SyrtisSymfonyClientBundle;
use Wexample\SymfonyHelpers\Traits\BundleClassTrait;

trait SymfonyClientBundleClassTrait
{
    use BundleClassTrait;

    public static function getBundleClassName(): string
    {
        return SyrtisSymfonyClientBundle::class;
    }
}
