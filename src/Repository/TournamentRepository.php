<?php

namespace App\Repository;

use App\Entity\Championship;
use App\Entity\Tournament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Tournament>
 *
 * @method Tournament|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tournament|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tournament[]    findAll()
 * @method Tournament[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournament::class);
    }

    //save method
    public function save(Tournament $tournament): void
    {
        $this->_em->persist($tournament);
        $this->_em->flush();
    }

    public function findAllFiltered(?UserInterface $user = null, bool $seeAll = false)
    {
        $qb = $this->createQueryBuilder("t");
        $qb->where("t.public = 1");
        if(!$seeAll && !is_null($user)){
            $qb->orWhere("t.admin = :admin")
                ->setParameter('admin',$user->getId());
        }
        if($seeAll){
            $qb->where("1 == 1");
        }
        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Tournament[] Returns an array of Tournament objects
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

//    public function findOneBySomeField($value): ?Tournament
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function getCommingTournaments()
    {
        return $this->createQueryBuilder('t')
            ->where('t.date >= :val')
            ->andWhere('t.date <= :val2')
            ->setParameter('val', (new \DateTime('now'))->setTime(0,0))
            ->setParameter('val2', (new \DateTime('+2 weeks'))->setTime(23,59,59))
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
