<?php

namespace SyrtisSymfonyClient\Repository;

use SyrtisSymfonyClient\Entity\SyrtisConversation;
use SyrtisSymfonyClient\Entity\Traits\Manipulator\SyrtisConversationEntityManipulatorTrait;
use Wexample\SymfonyAiConversation\Repository\AgentConversationRepository;
use Wexample\SymfonyConversation\Entity\UserConversationParticipant;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;

/**
 * @method SyrtisConversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method SyrtisConversation|null findOneBy(array $criteria, array $orderBy = null)
 */
class SyrtisConversationRepository extends AgentConversationRepository
{
    use SyrtisConversationEntityManipulatorTrait;

    /**
     * The conversation the person last spoke in, on that scenario: the one the
     * chat opens on. Null before the first.
     */
    public function findLastForUser(UserEntityInterface $user, string $scenario): ?SyrtisConversation
    {
        $alias = $this->getEntityQueryAlias();

        return $this->queryLastSpokenFirst()
            ->innerJoin(UserConversationParticipant::class, 'participant', 'ON', 'participant.conversation = '.$alias)
            ->where('participant.user = :user')
            ->andWhere($this->queryField('scenario').' = :scenario')
            ->setParameter('user', $user)
            ->setParameter('scenario', $scenario)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * The conversation Syrtis knows under that id, which is how a message it
     * pushes names the conversation it belongs to.
     */
    public function findOneByRemoteId(string $remoteId): ?SyrtisConversation
    {
        return $this->findOneBy(['remoteId' => $remoteId]);
    }
}
