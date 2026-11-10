<?php

namespace SyrtisSymfonyClient\Interface;

/**
 * Which scenario of Syrtis a new conversation is opened on. By default the
 * one the configuration names (`syrtis_symfony_client.scenario`); an
 * application keeping that choice elsewhere — noted at each deployment —
 * aliases this interface to its own service.
 */
interface ScenarioResolverInterface
{
    /**
     * The scenario's id on the Syrtis the client talks to.
     */
    public function getScenario(): string;
}
