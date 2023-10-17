<?php

namespace App\Repository\Tournament;

use App\Entity\Tournament\TournamentRanking;
use Doctrine\Bundle\DoctrineBundle\Repository\{ServiceEntityRepository};
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<TournamentRanking>
 *
 * @method TournamentRanking|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentRanking|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentRanking[]    findAll()
 * @method TournamentRanking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentRankingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentRanking::class);
    }

    public function save(TournamentRanking $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TournamentRanking $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
