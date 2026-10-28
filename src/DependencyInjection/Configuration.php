<?php

namespace SyrtisSymfonyClient\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('syrtis_symfony_client');

        $treeBuilder->getRootNode()
            ->children()
            // Where Syrtis answers: the local one in development, the
            // platform in production.
            ->scalarNode('host')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('api_key')->isRequired()->cannotBeEmpty()->end()
            // The scenario a conversation of the app is opened on, by its id.
            // A scenario belongs to one Syrtis, so it differs with the host.
            ->scalarNode('scenario')->isRequired()->cannotBeEmpty()->end()
            // What the scenario sends back with every message it pushes to
            // the app, for the app to refuse what does not come from it.
            ->scalarNode('callback_secret')->isRequired()->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
}
