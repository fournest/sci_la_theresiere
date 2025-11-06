<?php

namespace App\Repository;

use App\Entity\CarouselImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CarouselImage>
 */
class CarouselImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarouselImage::class);
    }
/**
 * Récupère toutes les images du carrousel triées par la colonne 'ordre'.
 * @return CarouselImage[]
 */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.ordre', 'ASC') // Tri par le champ 'ordre' en ordre Ascendant
            ->getQuery()
            ->getResult();
    }
}
