<?php

namespace App\Repository;

use App\Entity\Categorie;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function isRoomAvailable(Categorie $categorie, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): bool
    {
        $qb = $this->createQueryBuilder('reservation');

        $reservationsEnConflit = $qb
            ->select('COUNT(reservation.id)')
            ->where('reservation.categorie = :categorie')
            ->andWhere('reservation.statut IN (:statuts)')
            ->andWhere(':dateDebut < reservation.dateResaFin')
            ->andWhere(':dateFin > reservation.dateResaDebut')
            ->setParameter('categorie', $categorie)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->setParameter('statuts', ['en_attente', 'validée'])
            ->getQuery()
            ->getSingleScalarResult();

        return $reservationsEnConflit === 0;
    }

    

    


    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
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

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
