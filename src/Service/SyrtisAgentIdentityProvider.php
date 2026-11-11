<?php

namespace SyrtisSymfonyClient\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use SyrtisSymfonyClient\DependencyInjection\SyrtisSymfonyClientExtension;
use SyrtisSymfonyClient\Entity\SyrtisConversation;
use Wexample\SymfonyAiConversation\Interface\AgentIdentityProviderInterface;

/**
 * The agent of a Syrtis scenario, as the application names it and draws its
 * face (`syrtis_symfony_client.agent`), not as its qualified name reads.
 */
final readonly class SyrtisAgentIdentityProvider implements AgentIdentityProviderInterface
{
    /** @param array{label: string, src: ?string, emoji: ?string} $agent */
    public function __construct(
        #[Autowire(param: SyrtisSymfonyClientExtension::PARAMETER_AGENT)]
        private array $agent,
    ) {
    }

    public function identify(string $agentName): ?array
    {
        return str_starts_with($agentName, SyrtisConversation::AGENT_PREFIX) ? $this->identity() : null;
    }

    /** @return array{label: string, src: ?string, emoji: ?string} */
    public function identity(): array
    {
        return [
            'label' => $this->agent['label'],
            'src' => $this->agent['src'],
            'emoji' => $this->agent['src'] ? null : $this->agent['emoji'],
        ];
    }
}
