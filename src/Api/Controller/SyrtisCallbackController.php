<?php

namespace SyrtisSymfonyClient\Api\Controller;

use DateTime;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use SyrtisSymfonyClient\DependencyInjection\SyrtisSymfonyClientExtension;
use SyrtisSymfonyClient\Repository\SyrtisSessionRepository;
use Wexample\SymfonyAi\Entity\Message;
use Wexample\SymfonyAi\Repository\MessageRepository;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyApi\Api\Controller\AbstractApiController;

/**
 * Where Syrtis pushes what a session produces: a node of the app's scenario
 * posts each message here, and it becomes a message of symfony-ai — which the
 * app's live topics carry to the browser from there.
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

    final public const string CONTENT_TYPE_TOOL_CALL = 'tool_call';
    final public const string CONTENT_TYPE_ERROR = 'error';

    #[Route(path: 'message', name: 'message', methods: [Request::METHOD_POST])]
    public function message(
        Request $request,
        SyrtisSessionRepository $sessions,
        MessageRepository $messages,
        #[Autowire(param: SyrtisSymfonyClientExtension::PARAMETER_CALLBACK_SECRET)]
        string $secret,
    ): ApiResponse {
        if (! hash_equals($secret, (string) $request->headers->get(self::HEADER_SECRET))) {
            return self::apiResponseError(message: 'Not from Syrtis.', code: Response::HTTP_FORBIDDEN);
        }

        $payload = $request->toArray();
        $remote = $payload['message'] ?? null;

        if (! is_string($payload['session'] ?? null) || ! is_array($remote) || ! is_string($remote['id'] ?? null)) {
            return self::apiResponseError(message: 'A message names its session and carries an id.');
        }

        $session = $sessions->findOneByRemoteId($payload['session']);

        // Already held — the account's own turn, or a message pushed twice.
        if (! $session
            || self::ORIGIN_USER === ($remote['origin'] ?? null)
            || $messages->findOneBy(['providerMessageIdentifier' => $remote['id']])) {
            return self::apiResponseSuccess(data: ['ignored' => true]);
        }

        $message = $messages->createNewMessage(
            $session,
            self::typeOf($remote),
            is_string($remote['content'] ?? null) ? $remote['content'] : json_encode($remote['content'] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        );
        $message->setProviderMessageIdentifier($remote['id']);

        if (is_string($remote['dateCreated'] ?? null)) {
            $message->setDateCreated(new DateTime($remote['dateCreated']));
        }

        $session->setDateLastMessage($message->getDateCreated());
        $session->setTurnCount($session->getTurnCount() + 1);
        $messages->save($message);

        return self::apiResponseSuccess(data: ['id' => (string) $message->getId()]);
    }

    /**
     * @param array<string, mixed> $remote
     */
    private static function typeOf(array $remote): string
    {
        return match ($remote['contentType'] ?? null) {
            self::CONTENT_TYPE_TOOL_CALL => Message::TYPE_TOOL,
            self::CONTENT_TYPE_ERROR => Message::TYPE_ERROR,
            default => Message::TYPE_ASSISTANT,
        };
    }
}
