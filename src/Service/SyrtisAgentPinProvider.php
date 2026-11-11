<?php

namespace SyrtisSymfonyClient\Service;

use SyrtisSymfonyClient\Interface\ScenarioResolverInterface;
use SyrtisSymfonyClient\Repository\SyrtisConversationRepository;
use Wexample\SymfonyAiConversation\Service\AgentConversationService;
use Wexample\SymfonyConversation\Repository\MessageRepository;
use Wexample\SymfonyConversation\Repository\UserParticipantRepository;
use Wexample\SymfonyConversation\Service\ConversationChatPropsService;
use Wexample\SymfonyConversationDs\Interface\ConversationPinProviderInterface;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;

/**
 * The conversation with the Syrtis agent, the first of the person's tabs: its
 * face and name as the application gives them, what is left unread of it, and
 * the agent's chat, the conversation opened the first time its tab is.
 */
final readonly class SyrtisAgentPinProvider implements ConversationPinProviderInterface
{
    public const string KEY = 'agent';

    public function __construct(
        private SyrtisConversationRepository $conversations,
        private SyrtisConversationService $syrtisConversations,
        private ScenarioResolverInterface $scenarios,
        private UserParticipantRepository $participants,
        private MessageRepository $messages,
        private ConversationChatPropsService $chatProps,
        private SyrtisAgentIdentityProvider $identity,
    ) {
    }

    public function getPinnedConversations(UserEntityInterface $user): array
    {
        $conversation = $this->conversations->findLastForUser($user, $this->scenarios->getScenario());
        $me = $conversation ? $this->participants->findOneByConversationAndUser($conversation, $user) : null;
        $face = $this->identity->identity();

        return [[
            'key' => self::KEY,
            'label' => $face['label'],
            'avatar' => $face,
            'unread' => $me ? $this->messages->countUnreadFor($me) : 0,
            'chat' => fn () => [
                'component' => AgentConversationService::CHAT_COMPONENT,
                'props' => $this->chatProps->props($conversation ?? $this->syrtisConversations->open($user), $user),
            ],
        ]];
    }
}
