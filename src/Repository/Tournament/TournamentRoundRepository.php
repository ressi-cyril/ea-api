<?php

namespace App\Repository\Tournament;

use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentBracket;
use App\Entity\Tournament\TournamentRound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TournamentRound>
 *
 * @method TournamentRound|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentRound|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentRound[]    findAll()
 * @method TournamentRound[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentRoundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentRound::class);
    }

    public function save(TournamentRound $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TournamentRound $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAllRoundsByTournament(Tournament $tournament): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('round')
            ->from('App\Entity\Tournament\TournamentRound', 'round')
            ->join('round.bracket', 'bracket')
            ->join('bracket.tournament', 'tournament')
            ->where('tournament = :tournament')
            ->setParameter('tournament', $tournament)
            ->orderBy('round.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getLastFinishedRoundFromBracket(TournamentBracket $bracket): TournamentRound
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.bracket = :bracket')
            ->andWhere('r.isFinish = true')
            ->setParameter('bracket', $bracket)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $roundName
     * @return float|int|mixed|string
     */
    public function findRoundsWithOneMatchFromBracket(string $roundName): mixed
    {
        $qb = $this->createQueryBuilder('r');

        $qb->leftJoin('r.matches', 'm');

        $qb->leftJoin('r.bracket', 'b');

        $qb->groupBy('r.id')
            ->having('COUNT(m.id) = 1')
            ->andWhere('b.name = :name')
            ->setParameter('name', $roundName);

        return $qb->getQuery()->getResult();
    }

}
