<?php

namespace SyrtisSymfonyClient\Controller\Pages;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SyrtisSymfonyClient\Service\SyrtisConversationService;
use SyrtisSymfonyClient\Traits\SymfonyClientBundleClassTrait;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;

/**
 * The person's conversation with the app's agent: a page of its own, which
 * the app opens where it likes — in the panel beside the page, from a button
 * of its header.
 */
#[Route(path: '_syrtis/chat/', name: 'syrtis_chat_')]
#[IsGranted('IS_AUTHENTICATED')]
class ChatController extends AbstractPagesController
{
    use SymfonyClientBundleClassTrait;

    final public const string ROUTE_INDEX = self::DEFAULT_ROUTE_NAME_INDEX;

    #[Route(path: '', name: self::ROUTE_INDEX, options: self::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function index(SyrtisConversationService $conversations): Response
    {
        /** @var UserEntityInterface $user */
        $user = $this->getUser();

        return $this->renderPage(self::ROUTE_INDEX, [
            'conversation' => $conversations->current($user),
        ]);
    }
}
