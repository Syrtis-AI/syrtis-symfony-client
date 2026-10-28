<?php

namespace SyrtisSymfonyClient;

use Wexample\SymfonyHelpers\Class\AbstractBundle;
use Wexample\SymfonyHelpers\Interface\LoaderBundleInterface;
use Wexample\SymfonyPseudocode\Interface\PseudocodeBundleInterface;

/**
 * Syrtis as the engine behind the conversations of symfony-ai.
 *
 * A session of the app is a session on Syrtis: what the account writes is sent
 * there, what the agent answers comes back on an endpoint of the app, as
 * messages of symfony-ai — which the app's own live topics then carry to the
 * browser. The front never speaks to Syrtis.
 */
class SyrtisSymfonyClientBundle extends AbstractBundle implements LoaderBundleInterface, PseudocodeBundleInterface
{
    /**
     * Named after the composer package: the alias the helper derives reads a
     * `Vendor\Package` namespace, which a Syrtis package does not have.
     */
    public static function getLoaderFrontPaths(): array
    {
        return [
            '@syrtis/symfony-client' => __DIR__.'/../assets/',
        ];
    }

    public static function getPseudocodeSourcePaths(): array
    {
        return [__DIR__.'/'];
    }
}
