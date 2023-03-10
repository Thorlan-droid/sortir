<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Form\Recherche\ModelFiltre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findFiltered(ModelFiltre $filters)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->Join('s.organisateur', 'o')
            ->addSelect('o')
            ->leftJoin('s.etat', 'etat')
            ->addSelect('etat')
            ->leftJoin('s.inscrits', 'ins')
            ->addSelect('ins')
            ->leftJoin('s.lieu', 'lieu')
            ->addSelect('lieu');

        if ($filters->getCampus()) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus',$filters->getCampus());
        }

        if ($filters->getNom()) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $filters->getNom() . '%');
        }

        if ($filters->getDateSortie()) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $filters->getDateSortie());
        }

        if ($filters->getDateCloture()) {
            $qb->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $filters->getDateCloture());
        }

        if ($filters->getSortieOrganisateur()) {
            $qb->join('s.user', 'u')
                ->andWhere('u.id = :user')
                ->setParameter('user', $filters->getSortieOrganisateur());
        }

        if ($filters->getSortieInscrit()) {
            $qb->leftJoin('s.users', 'p')
                ->andWhere('p.id = :inscrit')
                ->setParameter('inscrit', $filters->getSortieInscrit());
        }

        if ($filters->getSortiePasInscrit()) {
            $qb->leftJoin('s.users', 'p')
                ->andWhere('p.id != :nonInscrit OR p.id IS NULL')
                ->setParameter('nonInscrit', $filters->getSortiePasInscrit());
        }

        if ($filters->getSortiePasses()) {
            $qb->join('s.etat', 'e')
                ->andWhere('e.libelle = :libelleEtat')
                ->setParameter('libelleEtat', 'Passée');
        } else {
            $qb->join('s.etat', 'e')
                ->andWhere('e.libelle != :libelleEtat')
                ->setParameter('libelleEtat', 'Passée');
        }

        return $qb->getQuery()->getResult();
    }





//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findOldSorties(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->leftJoin('s.etat', 'etat')
            ->addSelect('etat')
            ->andWhere($qb->expr()->in('s.dateLimiteInscription', ':dates'))
            ->andWhere('s.etat != :etat')
            ->setParameter('dates', $date)
            ->setParameter('etat', 'Historisée');

        return $qb->getQuery()->getResult();
    }

    public function recherche(ModelFiltre $filters)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->Join('s.user', 'o')
            ->addSelect('o')
            ->leftJoin('s.etat', 'etat')
            ->addSelect('etat')
            ->leftJoin('s.users', 'ins')
            ->addSelect('ins')
            ->leftJoin('s.lieu', 'lieu')
            ->addSelect('lieu');





        if ($filters->getCampus()) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus',$filters->getCampus());
        }

        if ($filters->getNom()) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $filters->getNom() . '%');
        }

        if ($filters->getDateSortie()) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $filters->getDateSortie());
        }

        if ($filters->getDateCloture()) {
            $qb->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $filters->getDateCloture());
        }

        if ($filters->getSortieOrganisateur()) {
            $qb->join('s.user', 'u')
                ->andWhere('u.id = :user')
                ->setParameter('user', $filters->getSortieOrganisateur());
        }

        if ($filters->getSortieInscrit()) {
            $qb->leftJoin('s.users', 'p')
                ->andWhere('p.id = :inscrit')
                ->setParameter('inscrit', $filters->getSortieInscrit());
        }

        if ($filters->getSortiePasInscrit()) {
            $qb->leftJoin('s.users', 'p')
                ->andWhere('p.id != :nonInscrit OR p.id IS NULL')
                ->setParameter('nonInscrit', $filters->getSortiePasInscrit());
        }

        if ($filters->getSortiePasses()) {
            $qb->join('s.etat', 'e')
                ->andWhere('e.libelle = :libelleEtat')
                ->setParameter('libelleEtat', 'Passée');
        } else {
            $qb->join('s.etat', 'e')
                ->andWhere('e.libelle != :libelleEtat')
                ->setParameter('libelleEtat', 'Passée');
        }

        return $qb->getQuery()->getResult();
    }

}

