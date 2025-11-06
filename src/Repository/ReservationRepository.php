<?php

namespace App\Repository;

use App\Entity\Categorie;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeInterface;
use App\Enum\ReservationStatus;


/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function isRoomAvailable(Categorie $categorie, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, ?int $currentReservationId = null): bool
    {
        $qb = $this->createQueryBuilder('reservation');

        $reservationsEnConflit = $qb
            ->select('COUNT(reservation.id)')
            ->where('reservation.categorie = :categorie')
            ->andWhere('reservation.statut IN (:statuts)')
            ->andWhere('reservation.dateResaFin >= :dateDebut')
            ->andWhere('reservation.dateResaDebut <= :dateFin')
            ->setParameter('categorie', $categorie)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->setParameter('statuts', ReservationStatus::getBlockingStatuses());
        if ($currentReservationId !== null) {
            $qb->andWhere('reservation.id != :currentId')
                ->setParameter('currentId', $currentReservationId);
        }
        $reservationsEnConflit = $qb->getQuery()->getSingleScalarResult();
        return $reservationsEnConflit === 0;
    }

    public function getUnavailableDates(Categorie $categorie, ?int $excludeReservationId = null): array
    {
        $qb = $this->createQueryBuilder('r');

        $qb->select('r.dateResaDebut, r.dateResaFin')
            ->where('r.categorie = :categorie')
            ->andWhere('r.statut IN(:statuts)')
            ->setParameter('categorie', $categorie)
           ->setParameter('statuts', ReservationStatus::getBlockingStatuses());

        if ($excludeReservationId !== null) {
            $qb->andWhere('r.id != :excludeId')
                ->setParameter('excludeId', $excludeReservationId);
        }

        $results = $qb->getQuery()->getResult();
        $dates = [];
        foreach ($results as $res) {
            $dates[] = [
                $res['dateResaDebut']->format('Y-m-d'),
                $res['dateResaFin']->format('Y-m-d'),
            ];
        }
        return $dates;
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
