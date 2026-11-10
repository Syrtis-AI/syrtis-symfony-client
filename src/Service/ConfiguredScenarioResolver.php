<?php

namespace SyrtisSymfonyClient\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use SyrtisSymfonyClient\DependencyInjection\SyrtisSymfonyClientExtension;
use SyrtisSymfonyClient\Interface\ScenarioResolverInterface;

/**
 * The scenario the configuration names.
 */
final readonly class ConfiguredScenarioResolver implements ScenarioResolverInterface
{
    public function __construct(
        #[Autowire(param: SyrtisSymfonyClientExtension::PARAMETER_SCENARIO)]
        private string $scenario,
    ) {
    }

    public function getScenario(): string
    {
        return $this->scenario;
    }
}
