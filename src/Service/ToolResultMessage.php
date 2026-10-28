<?php

namespace SyrtisSymfonyClient\Service;

use Wexample\SymfonyAi\Entity\Message;

/**
 * A tool message of symfony-ai is one of two things, both json in its body:
 * what the agent called — `name`, `arguments` —, written when Syrtis pushes
 * the call; what the page answered — `tool`, `callId`, `results` —, written
 * when the page sends it. This reads the second, and writes it as Syrtis
 * takes it: the same shape the Syrtis manager sends back.
 */
final class ToolResultMessage
{
    final public const string NAME = 'tool_result';
    final public const string STAMP_TOOL_RESULT = 'tool_result';
    final public const string STAMP_HISTORY = 'history';

    /**
     * @return array{tool: string, callId: string, results: array}|null Null
     *     for a tool message that is a call, or says nothing readable.
     */
    public static function read(Message $message): ?array
    {
        $body = json_decode((string) $message->getBody(), true);

        if (! is_array($body) || ! is_string($body['tool'] ?? null) || ! is_string($body['callId'] ?? null)) {
            return null;
        }

        return [
            'tool' => $body['tool'],
            'callId' => $body['callId'],
            'results' => is_array($body['results'] ?? null) ? $body['results'] : [],
        ];
    }

    /**
     * @param array{tool: string, callId: string, results: array} $result
     *
     * @return array<string, mixed>
     */
    public static function toSyrtis(array $result): array
    {
        return [
            'content' => json_encode($result + ['count' => count($result['results'])], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'format' => 'json',
            'name' => self::NAME,
            'stamps' => [self::STAMP_TOOL_RESULT, 'tool:' . $result['tool'] . ':result', self::STAMP_HISTORY],
        ];
    }
}
