<?php

namespace App\Repository\User;

use App\Entity\User\PlayerUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<PlayerUser>
 *
 * @method PlayerUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerUser[]    findAll()
 * @method PlayerUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerUserRepository extends ServiceEntityRepository
{
    const DEFAULT_LIMIT = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerUser::class);
    }

    public function save(PlayerUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PlayerUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByOrThrow(array $criteria): PlayerUser
    {
        $team = $this->findOneBy($criteria);
        if (empty($team)) {
            throw new NotFoundHttpException('Player not found', null, 404);
        }

        return $team;
    }

    public function findByCriteria(array $criteria, int $limit = self::DEFAULT_LIMIT): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('u')
            ->orderBy('u.id', 'ASC')
            ->setMaxResults($limit);

        foreach ($criteria as $field => $value) {
            if ($value !== '') {
                $queryBuilder
                    ->andWhere("u.$field = :$field")
                    ->setParameter($field, $value);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
