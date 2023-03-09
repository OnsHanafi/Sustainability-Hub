<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Reclamation>
 *
 * @method Reclamation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reclamation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reclamation[]    findAll()
 * @method Reclamation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    public function add(Reclamation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Reclamation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Reclamation[] Returns an array of Reclamation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Reclamation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
//email
public function order_By_Email()
{
    return $this->createQueryBuilder('s')
        ->orderBy('s.email', 'ASC')
        ->getQuery()->getResult();
}
//nom
public function orderByName()
{
    return $this->createQueryBuilder('r')
        ->orderBy('r.nom', 'ASC')
        ->getQuery()
        ->getResult();
}
///////////////////recherche////////////////////////////
public function findBySearchTerm(string $searchTerm): array
    {
        $qb = $this->createQueryBuilder('r');

        $qb->where($qb->expr()->orX(
            $qb->expr()->like('r.contenu', ':searchTerm'),
            $qb->expr()->like('r.nom', ':searchTerm'),
            $qb->expr()->like('r.email', ':searchTerm'),
            $qb->expr()->like('r.prenom', ':searchTerm')
        ))
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $qb->getQuery()->getResult();
    }


}
