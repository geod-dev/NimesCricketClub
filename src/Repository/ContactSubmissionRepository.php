<?php

namespace App\Repository;

use App\Entity\ContactSubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactSubmission>
 *
 * @method ContactSubmission|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactSubmission|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactSubmission[]    findAll()
 * @method ContactSubmission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactSubmission::class);
    }

    //    /**
    //     * @return ContactSubmission[] Returns an array of ContactSubmission objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ContactSubmission
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
