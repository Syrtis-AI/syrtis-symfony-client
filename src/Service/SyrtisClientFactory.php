<?php

namespace SyrtisSymfonyClient\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use SyrtisClient\Common\SyrtisClient;
use SyrtisSymfonyClient\DependencyInjection\SyrtisSymfonyClientExtension;

/**
 * The one client to Syrtis the app holds, built from its configuration.
 */
final readonly class SyrtisClientFactory
{
    public function __construct(
        #[Autowire(param: SyrtisSymfonyClientExtension::PARAMETER_HOST)]
        private string $host,
        #[Autowire(param: SyrtisSymfonyClientExtension::PARAMETER_API_KEY)]
        private string $apiKey,
    ) {
    }

    public function create(): SyrtisClient
    {
        return new SyrtisClient(
            host: $this->host,
            apiKey: $this->apiKey,
        );
    }
}
