<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
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

    /*public function findByCampusId(int $campusId):Paginator
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $queryBuilder
            ->addSelect('p')
            ->addSelect('c')
            ->leftJoin('s.organisateur', 'p')
            ->leftJoin('p.campus', 'c')
            ->andWhere('c.id = :campusId')
            ->setParameter('campusId', $campusId);

        $query = $queryBuilder->getQuery();

        return new Paginator($query);



    }*/

    public function findByFiltres(int $campusId,
                                  ?int $organisateurId,
                                  ?int $inscritID,
                                  ?int $pasInscritID,
                                  ?bool $termineesBool,
                                  ?\DateTimeImmutable $Debut,
                                  ?\DateTimeImmutable $Fin,
                                  ?string $nomContient,
                                  ? Participant $participant):Paginator
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $queryBuilder
            ->addSelect('o')
            ->addSelect('c')
            ->addSelect('p')
            ->addSelect('e')
            ->leftJoin('s.organisateur', 'o')
            ->leftJoin('o.campus', 'c')
            ->leftJoin('s.participants', 'p')
            ->leftJoin('s.etat', 'e')
            ->andWhere('c.id = :campusId')
            ->setParameter('campusId', $campusId);
            if($organisateurId!==null)
            {
                $queryBuilder
                ->andWhere('o.id = :organisateurId')
                ->setParameter('organisateurId', $organisateurId);
            }
            if($inscritID!==null)
            {
                $queryBuilder
                    ->andWhere('p.id = :inscritID')
                    ->setParameter('inscritID', $inscritID);
            }


            if($pasInscritID!==null)
            {
                $queryBuilder
                    ->andWhere(':participant NOT MEMBER OF s.participants')
                    ->setParameter('participant', $participant);
            }



            //sorties terminées
            if($termineesBool)
            {
                $queryBuilder
                    ->andWhere('e.id = :pasInscritID')
                    ->setParameter('pasInscritID', 5);
            }

        //dd($Debut,$Fin,$nomContient);
        if ($Debut !== null && $Fin !==null) {
            $queryBuilder
                ->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $Debut->setTime(0, 0, 0));
            $queryBuilder
                ->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $Fin->setTime(23, 59, 59));
        }


        if ($nomContient !== null) {
            $queryBuilder
                ->andWhere('s.nom LIKE :nomSortie')
                ->setParameter('nomSortie', '%' . $nomContient . '%');
        }


        $query = $queryBuilder->getQuery();
        //dd($query);

        return new Paginator($query);
    }

}
