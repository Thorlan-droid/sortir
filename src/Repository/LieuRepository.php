<?php

namespace App\Repository;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lieu>
 *
 * @method Lieu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lieu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lieu[]    findAll()
 * @method Lieu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LieuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lieu::class);
    }

    public function save(Lieu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Lieu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


//    public function findLieuByVille() {
//        $qb = $this->createQueryBuilder('l');
//
//        $qb->where('l.ville = :ville')
//
//            ->orderBy('l.nom', 'ASC');
//
//        return $qb;
//    }
//    /**
//     * @return Lieu[] Returns an array of Lieu objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneByVille(Ville $ville): ?Lieu
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.ville = :ville')
//            ->setParameter('ville', $ville)
//            ->getQuery()
//            ->getResult()
//        ;
//    }
//    public function findWithVilleJoin($id)
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.id = :id')
//            ->leftJoin('l.ville', 'ville')
//            ->addSelect('ville')
//            ->setParameter('id', $id)
//            ->getQuery()
//            ->getOneOrNullResult();
//    }
}
