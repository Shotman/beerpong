<?php

namespace App\Repository;

use App\Entity\TournamentResults;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TournamentResults>
 *
 * @method TournamentResults|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentResults|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentResults[]    findAll()
 * @method TournamentResults[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentResultsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentResults::class);
    }

//    /**
//     * @return TournamentResults[] Returns an array of TournamentResults objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TournamentResults
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
