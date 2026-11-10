<?php

namespace SyrtisSymfonyClient\Api\Controller;

use DateTime;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use SyrtisSymfonyClient\DependencyInjection\SyrtisSymfonyClientExtension;
use SyrtisSymfonyClient\Repository\SyrtisConversationRepository;
use Wexample\SymfonyAiConversation\Service\AgentConversationService;
use Wexample\SymfonyConversation\Entity\ConversationMessage;
use Wexample\SymfonyConversation\Repository\MessageRepository;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyApi\Api\Controller\AbstractApiController;

/**
 * Where Syrtis pushes what a session produces: a node of the app's scenario
 * posts here — one `message`, or the `messages` of a turn, an answer and the
 * tools it calls —, and each becomes a line of the conversation written by its
 * agent, which the app's live topics carry to the browser from there.
 *
 * The call carries a secret the scenario was given, in place of a signature.
 * A session the app does not hold is answered as ignored, not as an error: the
 * same account runs other scenarios on Syrtis, and the scenario's node has no
 * use for a failure it cannot act on.
 */
#[Route(path: 'api/syrtis/', name: 'api_syrtis_')]
class SyrtisCallbackController extends AbstractApiController
{
    final public const string HEADER_SECRET = 'X-Syrtis-Secret';

    /** What Syrtis calls a message written by the account, which the app already holds. */
    final public const string ORIGIN_USER = 'user';

    final public const string CONTENT_TYPE_TOOL_CALL = 'core/tool_call';
    final public const string CONTENT_TYPE_ERROR = 'core/error';

    #[Route(path: 'message', name: 'message', methods: [Request::METHOD_POST])]
    public function message(
        Request $request,
        SyrtisConversationRepository $conversations,
        MessageRepository $messages,
        AgentConversationService $agentConversations,
        #[Autowire(param: SyrtisSymfonyClientExtension::PARAMETER_CALLBACK_SECRET)]
        string $secret,
    ): ApiResponse {
        if (! hash_equals($secret, (string) $request->headers->get(self::HEADER_SECRET))) {
            return self::apiResponseError(message: 'Not from Syrtis.', code: Response::HTTP_FORBIDDEN);
        }

        $payload = $request->toArray();
        $remotes = $payload['messages'] ?? [$payload['message'] ?? null];

        if (! is_string($payload['session'] ?? null) || ! is_array($remotes) || ! $remotes) {
            return self::apiResponseError(message: 'A push names its session and carries its messages.');
        }

        foreach ($remotes as $remote) {
            if (! is_array($remote) || ! is_string($remote['id'] ?? null)) {
                return self::apiResponseError(message: 'Each message carries an id.');
            }
        }

        $conversation = $conversations->findOneByRemoteId($payload['session']);

        if (! $conversation) {
            return self::apiResponseSuccess(data: ['ignored' => true]);
        }

        $agent = $agentConversations->agentParticipant($conversation);
        $ids = [];

        foreach ($remotes as $remote) {
            // Already held — the account's own turn, or a message pushed twice.
            if (self::ORIGIN_USER === ($remote['origin'] ?? null)
                || $messages->findOneBy(['providerMessageIdentifier' => $remote['id']])) {
                continue;
            }

            $message = $messages->createNewMessage(
                $conversation,
                self::typeOf($remote),
                is_string($remote['content'] ?? null) ? $remote['content'] : json_encode($remote['content'] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                $agent,
            );
            $message->setProviderMessageIdentifier($remote['id']);

            if (is_string($remote['dateCreated'] ?? null) && '' !== $remote['dateCreated']) {
                $message->setDateCreated(new DateTime($remote['dateCreated']));
            }

            $conversation->setDateLastMessage($message->getDateCreated());
            $conversation->setTurnCount($conversation->getTurnCount() + 1);
            $messages->save($message);
            $ids[] = (string) $message->getId();
        }

        return $ids
            ? self::apiResponseSuccess(data: ['ids' => $ids])
            : self::apiResponseSuccess(data: ['ignored' => true]);
    }

    /**
     * @param array<string, mixed> $remote
     */
    private static function typeOf(array $remote): string
    {
        return match ($remote['contentType'] ?? null) {
            self::CONTENT_TYPE_TOOL_CALL => ConversationMessage::TYPE_TOOL,
            self::CONTENT_TYPE_ERROR => ConversationMessage::TYPE_ERROR,
            default => ConversationMessage::TYPE_TEXT,
        };
    }
}
