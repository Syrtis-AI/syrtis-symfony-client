<?php

namespace SyrtisSymfonyClient\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;

class SyrtisSymfonyClientExtension extends AbstractWexampleSymfonyExtension
{
    final public const string PARAMETER_HOST = 'syrtis_symfony_client.host';
    final public const string PARAMETER_API_KEY = 'syrtis_symfony_client.api_key';
    final public const string PARAMETER_SCENARIO = 'syrtis_symfony_client.scenario';
    final public const string PARAMETER_CALLBACK_SECRET = 'syrtis_symfony_client.callback_secret';

    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(self::PARAMETER_HOST, $config['host']);
        $container->setParameter(self::PARAMETER_API_KEY, $config['api_key']);
        $container->setParameter(self::PARAMETER_SCENARIO, $config['scenario']);
        $container->setParameter(self::PARAMETER_CALLBACK_SECRET, $config['callback_secret']);

        $this->loadConfig(
            __DIR__,
            $container
        );
    }
}
