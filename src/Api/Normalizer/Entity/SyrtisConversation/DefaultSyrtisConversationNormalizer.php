<?php

namespace SyrtisSymfonyClient\Api\Normalizer\Entity\SyrtisConversation;

use SyrtisSymfonyClient\Api\Dto\SyrtisConversationDto;
use SyrtisSymfonyClient\Entity\Traits\Manipulator\SyrtisConversationEntityManipulatorTrait;
use Wexample\SymfonyAiConversation\Api\Normalizer\Entity\AgentConversation\DefaultAgentConversationNormalizer;
use Wexample\SymfonyConversation\Api\Dto\ConversationDto;
use Wexample\SymfonyConversation\Entity\Conversation;

class DefaultSyrtisConversationNormalizer extends DefaultAgentConversationNormalizer
{
    use SyrtisConversationEntityManipulatorTrait;

    protected function createDto(Conversation $conversation, array $participants): ConversationDto
    {
        return SyrtisConversationDto::fromConversation($conversation, $participants);
    }
}
