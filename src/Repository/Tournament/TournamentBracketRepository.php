<?php

namespace App\Repository\Tournament;

use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentBracket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TournamentBracket>
 *
 * @method TournamentBracket|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentBracket|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentBracket[]    findAll()
 * @method TournamentBracket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentBracketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentBracket::class);
    }

    public function save(TournamentBracket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TournamentBracket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find Bracket by Tournament and type
     * @throws NonUniqueResultException
     *
     */
    public function findBracketByTournament(Tournament $tournament, string $bracketType): ?TournamentBracket
    {
        $qb = $this->createQueryBuilder('b');
        $qb->where('b.name = :name')
            ->andWhere('b.tournament = :tournament')
            ->setParameter('name', $bracketType)
            ->setParameter('tournament', $tournament);

        return $qb->getQuery()->getOneOrNullResult();
    }


}
