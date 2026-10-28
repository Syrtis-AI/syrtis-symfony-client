<?php

namespace SyrtisSymfonyClient\Repository;

use Wexample\SymfonyAi\Repository\SessionRepository;
use SyrtisSymfonyClient\Entity\SyrtisSession;
use SyrtisSymfonyClient\Entity\Traits\Manipulator\SyrtisSessionEntityManipulatorTrait;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;

/**
 * @method SyrtisSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method SyrtisSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method SyrtisSession[]    findAll()
 * @method SyrtisSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SyrtisSessionRepository extends SessionRepository
{
    use SyrtisSessionEntityManipulatorTrait;

    public function createNewSyrtisSession(UserEntityInterface $user, string $scenario): SyrtisSession
    {
        return new SyrtisSession($user, $scenario);
    }

    /**
     * The conversation the account last spoke in, on that scenario: the one
     * the chat opens on. Null before the first.
     */
    public function findLastForUser(UserEntityInterface $user, string $scenario): ?SyrtisSession
    {
        return $this->queryLastSpokenFirst()
            ->where($this->queryField('user').' = :user')
            ->andWhere($this->queryField('scenario').' = :scenario')
            ->setParameter('user', $user)
            ->setParameter('scenario', $scenario)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * The conversation Syrtis knows under that id, which is how a message it
     * pushes names the session it belongs to.
     */
    public function findOneByRemoteId(string $remoteId): ?SyrtisSession
    {
        return $this->findOneBy(['remoteId' => $remoteId]);
    }
}
